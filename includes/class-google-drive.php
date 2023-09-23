<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class TPX_GoogleDrive {
    
    public function getClient() {

        $a = appyn_options( 'gdrive_client_id', true );
        $b = appyn_options( 'gdrive_client_secret', true );
    
        if( !$a && !$b ) return false;

        require_once TEMPLATEPATH . '/includes/google-api-php-client-master/vendor/autoload.php';
            
        $redirect_uri = add_query_arg( 'appyn_upload', 'gdrive', get_site_url() );

        $config = [
            'client_id' 	=> $a,
            'client_secret' => $b,
            'redirect_uri' 	=> $redirect_uri
        ];
        $client = new Google_Client($config);
        
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setPrompt('select_account consent');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if (get_option('appyn_gdrive_token')) {
            $accessToken = json_decode(get_option('appyn_gdrive_token'), true);
            $client->setAccessToken($accessToken);
        }
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } 
            $gat = ( $client->getAccessToken() ) ? $client->getAccessToken() : null;
            if( $gat ) 
                update_option('appyn_gdrive_token', json_encode($client->getAccessToken()));		
        }

        return $client;
    }
    
    public function createFolder( $folder_name, $parent_folder_id = null ) {
        $client = $this->getClient();

        $folder_list = $this->checkFolderExists( $folder_name );

        if( count( $folder_list ) == 0 ){
            $service = new Google_Service_Drive( $client );
            $folder = new Google_Service_Drive_DriveFile();
        
            $folder->setName( $folder_name );
            $folder->setMimeType('application/vnd.google-apps.folder');
            if( !empty( $parent_folder_id ) ){
                $folder->setParents( [ $parent_folder_id ] );        
            }
    
            $result = $service->files->create( $folder );
        
            $folder_id = null;
            
            if( isset( $result['id'] ) && !empty( $result['id'] ) ){
                $folder_id = $result['id'];
            }
        
            return $folder_id;
        }
    
        return $folder_list[0]['id'];
    }

    private function checkFolderExists( $folder_name ) {

        $client = $this->getClient();
        $service = new Google_Service_Drive($client);
    
        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and name='$folder_name' and trashed=false";
    
        $files = $service->files->listFiles($parameters);
    
        $op = [];
        foreach( $files as $k => $file ){
            $op[] = $file;
        }
    
        return $op;

    } 

    public function insertFileToDrive( $file_path, $file_name, $parent_file_id = null, $contents = '' ) {
        $client = $this->getClient();
        $service = new Google_Service_Drive($client);

        $file = new Google_Service_Drive_DriveFile();

        $file->setName( $file_name );

        if( !empty( $parent_file_id ) ){
            $file->setParents( [ $parent_file_id ] );        
        }

        try {
            $client->setDefer(true);
            $request = $service->files->create(
                $file,
                array(
                    'uploadType' => 'resumable',
                    'fields' => 'id',
                )
            );
            $chunkSizeBytes = 1 * 1024 * 1024;
            $media = new Google_Http_MediaFileUpload(
                $client,
                $request,
                'application/octet-stream',
                null,
                true,
                $chunkSizeBytes
            );

            $media->setFileSize(filesize($file_path));
          
            $status = false;
            $handle = fopen($file_path, "rb");
            while (!$status && !feof($handle)) {
                $chunk = $this->readFileChunk($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }
          
            $result = false;
            if ($status != false) {
                $result = $status;
            }
          
            fclose($handle);
            $client->setDefer(false);
            
        }
        catch (Exception $e) {
            return array('error' => json_decode($e->getMessage(), true)['error']['message']);
        }
        
        if( isset( $result['name'] ) && !empty( $result['name'] ) ){
            
            $newPermission = new Google_Service_Drive_Permission();
            $newPermission->setType('anyone');
            $newPermission->setRole('reader');
            try {
                $service->permissions->create($result['id'], $newPermission);
            } 
            catch (Exception $e) {
                return array('error' => json_decode($e->getMessage(), true)['error']['message']);
            }
        }

        $link_download = 'https://drive.google.com/uc?export=download&id='.$result['id'];

        return array('url' => $link_download);
        
    }
        
    private function readFileChunk($handle, $chunkSize) {
        $byteCount = 0;
        $gChunk = "";
        while( !feof($handle) ) {
            $chunk = fread($handle, 1 * 1024 * 1024);
            $byteCount += strlen($chunk);
            $gChunk .= $chunk;
            if( $byteCount >= $chunkSize ) {
                return $gChunk;
            }
        }
        return $gChunk;
    }
}