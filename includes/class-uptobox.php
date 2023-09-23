<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class TPX_UptoBox {

    var $token;
    var $file_id;
    var $folder_id;
    var $folder_name;
    
    public function __construct() {

        $this->token = appyn_options( 'uptobox_token', true );
    }

    public function uploadFile( $file_path, $file_name ) {

        $this->deleteFileDuplicate($file_name);

        $url = 'https://uptobox.com/api/upload';
        $data = [
            'token' => $this->token
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);
        curl_close($curl);

        $j = json_decode($result, true);
        $uploadl = $j['data']['uploadLink'];

        if(function_exists('curl_file_create')) {
            $cFile = curl_file_create($file_path);
        }else{
            $cFile = '@' . realpath($file_path);
        }

        $uploadRequest = array(
            'files' => $cFile
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $uploadRequest);
        curl_setopt($curl, CURLOPT_URL, "https:".$uploadl);      
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = json_decode(curl_exec($curl),true);
        if( curl_errno($curl) ) {
            throw new Exception(curl_error($curl));
            exit;
        }
        curl_close($curl);

        return array('url' => $result['files'][0]['url']);
    }

    private function deleteFileDuplicate($file_name) {
        $url = 'https://uptobox.com/api/user/files?token='.$this->token.'&path=//&limit=100&search='.$file_name.'&searchField=file_name';

        $data = file_get_contents($url);

        if( empty($data) ) return;

        $a = json_decode($data, true);

        if( count($a['data']['files']) == 0 ) return;
        
        foreach( $a['data']['files'] as $file ) {

            $url = 'https://uptobox.com/api/user/files';
            $data = [
                'token' => $this->token,
                'file_codes' => $file['file_code'],
            ];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_exec($curl);
            curl_close($curl);
        }
    }
}