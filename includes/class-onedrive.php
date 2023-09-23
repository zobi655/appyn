<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class TPX_OneDrive {

    var $client_id;
    var $client_secret;
    var $access_token;
    var $redirect_uri;
    var $refresh_token;
    var $file_id;
    var $folder_id;
    var $folder_name;
    
    public function __construct() {

        $this->client_id        = appyn_options( 'onedrive_client_id', true );
        $this->client_secret    = appyn_options( 'onedrive_client_secret', true );
        $this->access_token     = appyn_options( 'onedrive_access_token', true );
        $this->refresh_token    = appyn_options( 'onedrive_refresh_token', true );
        $this->folder_name      = appyn_options( 'onedrive_folder', true );
        $this->redirect_uri     = get_site_url();
    
        if( !$this->client_id || !$this->client_secret || !$this->access_token || !$this->refresh_token ) return false;

        $this->refreshToken();

        if( !empty( $this->folder_name ) ) {
            $this->folder_id = $this->createFolder( $this->folder_name );
            $this->folder_name = trailingslashit( appyn_options( 'onedrive_folder', true ) );
        }
    }

    public function ODConnect() {
            
        $redirect = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id='.$this->client_id.'&response_type=code&redirect_uri='.$this->redirect_uri.'&scope=files.read%20files.read.all%20files.readwrite%20files.readwrite.all%20offline_access%20sites.readwrite.all';

        return $redirect;

    }
    
    public function getToken($code) {
        
        $post = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'code' => $code,
            'grant_type' => 'authorization_code',
        );

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        $data = $this->_curl( 'https://login.microsoftonline.com/common/oauth2/v2.0/token', http_build_query($post), false, $headers );

        if( isset($data['error']) ) {
            echo '<strong>Error '.$data['error'].':</strong> '.$data['error_description'];
            exit;
        }

        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];
        
        update_option( 'appyn_onedrive_access_token', $this->access_token );
        update_option( 'appyn_onedrive_refresh_token', $this->refresh_token );

    }

    public function refreshToken() {

        $post = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'refresh_token' => $this->refresh_token,
            'grant_type' => 'refresh_token',
        );

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        
        $data = $this->_curl( 'https://login.microsoftonline.com/common/oauth2/v2.0/token', http_build_query($post), false, $headers );

        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];
        
        update_option( 'appyn_onedrive_access_token', $this->access_token );
        update_option( 'appyn_onedrive_refresh_token', $this->refresh_token );

    }

    private function createFolder( $name ) {

        $post = '{
            "name": "'.$name.'",
            "folder": { },
        }';
        $headers = array();
        $headers[] = 'Authorization: bearer '.$this->access_token;
        $headers[] = 'Content-Type: application/json';
        $data = $this->_curl( 'https://graph.microsoft.com/v1.0/me/drive/root/children', $post, false, $headers );

        return $data['id'];
    }

    private function createFile( $file_path, $file_name ) {

        $post = array(
            'file' => '@' .realpath($file_path)
        );
        $headers = array();
        $headers[] = 'Authorization: bearer '.$this->access_token;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Content-Length: 0';

        $data = $this->_curl( 'https://graph.microsoft.com/v1.0/me/drive/root:/'.$this->folder_name.$file_name.':/content', $post, true, $headers );

        return $data;
    }

    public function uploadFile( $file_path, $file_name ) {

        $uploadfile = $this->createFile( $file_path, $file_name );

        $this->file_id = $uploadfile['id'];

        $post = '{
            "@microsoft.graph.conflictBehavior": "rename | fail | replace",
            "description": "description",
            "fileSystemInfo": { "@odata.type": "microsoft.graph.fileSystemInfo" },
            "name": "'.$file_name.'"
        }';
            
        $headers = array();
        $headers[] = 'Authorization: bearer '.$this->access_token;
        $headers[] = 'Content-Type: application/json';

        $data = $this->_curl( 'https://graph.microsoft.com/v1.0/me/drive/items/'.$this->file_id.'/createUploadSession', $post, false, $headers );

        $fragSize = 1024 * 1024; 
        $fileSize = filesize($file_path);
        $numFragments = ceil($fileSize / $fragSize);
        $bytesRemaining = $fileSize;
        $i = 0;

        $ch = curl_init($data['uploadUrl']);

        while ($i < $numFragments) {
            $chunkSize = $numBytes = $fragSize;
            $start = $i * $fragSize;
            $end = $i * $fragSize + $chunkSize - 1;
            $offset = $i * $fragSize;
            if ($bytesRemaining < $chunkSize) {
                $chunkSize = $numBytes = $bytesRemaining;
                $end = $fileSize - 1;
            }
            if ($stream = fopen($file_path, 'r')) {
                $data = stream_get_contents($stream, $chunkSize, $offset);
                fclose($stream);
            }

            $content_range = " bytes " . $start . "-" . $end . "/" . $fileSize;

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = 'Authorization: bearer '.$this->access_token;
            $headers[] = 'Content-Length: '.$numBytes;
            $headers[] = 'Content-Range: '.$content_range;
        
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            $bytesRemaining = $bytesRemaining - $chunkSize;
            $i++;
        }
        curl_close($ch);

        return $this->downloadURL();
    }

    public function downloadURL() {

        $post = '{
            "type": "view",
            "scope": "anonymous"
        }';

        $headers = array();
        $headers[] = 'Authorization: bearer '.$this->access_token;
        $headers[] = 'Content-Type: application/json';

        $data = $this->_curl( 'https://graph.microsoft.com/v1.0/me/drive/items/'.$this->file_id.'/createLink', $post, false, $headers );

        if( isset($data['error']) ) {
            throw new Exception($data['error']['message']);
            exit;
        }
       
        return array('url' => $data['link']['webUrl']);
    }

    private function _curl( $url, $post, $put = false, $headers = array() ) {
        sleep(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  

        if( $put ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if( curl_errno($ch) ) {
            throw new Exception(curl_error($ch));
            exit;
        }
        curl_close($ch);
        $arr = json_decode($result, true);
        
        return $arr;
    }   
}