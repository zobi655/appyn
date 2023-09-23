<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class TPX_Dropbox {

    var $result;
    var $access_token;
    var $filename; 
    var $max_upload; 
    var $content;
    var $filesize;
    var $session_id;

    public function __construct() {

        $this->result = json_decode(appyn_options( 'dropbox_result', true ), true);
        
        if( appyn_options( 'dropbox_expires', true ) < time() ) {

            $dropbox_app_key = appyn_options( 'dropbox_app_key' );
            $dropbox_app_secret = appyn_options( 'dropbox_app_secret' );
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token=".$this->result['refresh_token']);
            curl_setopt($ch, CURLOPT_USERPWD, $dropbox_app_key.':'.$dropbox_app_secret);

            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $output['error'] = curl_error($ch);
                return $output;
            }
            curl_close($ch);

            if( $result ) {
                $j = json_decode($result, true);
                if( isset($j['access_token']) ) {
                    update_option( 'appyn_dropbox_result', $result );
                    update_option( 'appyn_dropbox_expires', (time()+$j['expires_in']) );
                    $this->result = $j;
                }
            }
        }

        $this->access_token = $this->result['access_token'];

        $this->max_upload = 150 * 1024 * 1024;
    }

    private function checkIfExists( $path_display ) {

        $curl_url = 'https://api.dropboxapi.com/2/sharing/list_shared_links';
        
        $content = array(
            'path' => $path_display,
        );
        $result = $this->curlInit( $curl_url, json_encode( $content ), false, 'json' );

        $r = json_decode( $result, true );
        
        if( count($r['links']) > 0 ) {
            return str_replace('?dl=0', '?dl=1', $r['links'][0]['url']);
        }
    }

    public function Upload( $filename ) {

        $this->filename     = $filename;
        $fp                 = fopen($this->filename, 'rb');
        $this->filesize     = filesize($this->filename);

        if( $this->filesize < 150 * 1024 * 1024 ) {
           
            $this->content = fread($fp, $this->filesize);

            $dropbox_api_arg = array(
                "path"=> '/'.basename( $this->filename ),
                "mode" => "add",
                "autorename" => true,
                "mute" => false,
                "strict_conflict" => false
            );
            
            $curl_url = 'https://content.dropboxapi.com/2/files/upload';
            $result = $this->curlInit( $curl_url, $this->content, $dropbox_api_arg );
            
            $r = json_decode($result, true);

            if( isset($r['error_summary']) ) {
                $output['error'] = $r['error_summary'];
                return $output;
            }

            $checkIfExists = $this->checkIfExists($r['path_display']);
    
            if( $checkIfExists ) {
                $output['url'] = $checkIfExists;
            } else {
                $dpd  = $this->pathDisplay($r);
                $output['url'] = $dpd['url'];
            }
            return $output;

        } else {
            $offset = $this->max_upload;
            $tosend = $this->filesize;
            
            $content = fread($fp, $offset);

            $tosend -= $this->max_upload;

            $this->uploadStart($content);

            $offset = 0;
            
            while ($tosend > $this->max_upload) {
                
                $content = fread($fp, $this->max_upload);

                $offset += $this->max_upload;

                $tosend -= $this->max_upload;                

                $this->uploadAppend($offset, $content);

            }

            $offset += $this->max_upload;

            $content = fread($fp, $tosend);

            fclose($fp);
            
            $result = $this->uploadFinish($offset, $content);
            
            return $result;
        }

    }

    private function curlInit( $curl_url, $content = '', $dropbox_api_arg = '', $type = 'octet-stream' ) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if( !empty($content) ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        
        $headers = array();
        $headers[] = 'Authorization: Bearer '. $this->access_token;

        if( $dropbox_api_arg ) 
            $headers[] = 'Dropbox-Api-Arg: '. json_encode($dropbox_api_arg);

        if( $type == 'octet-stream' ) 
            $headers[] = 'Content-Type: application/octet-stream';
        elseif( $type == 'json' ) 
            $headers[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $output['error'] = "curl: ". curl_error($ch);
            return $output;
        }

        curl_close ($ch);

        return $result;

    }

    private function uploadStart($content = '') {
        
        $curl_url = 'https://content.dropboxapi.com/2/files/upload_session/start';
        $dropbox_api_arg = array(
            "close"=> false,
        );
        
        $result = $this->curlInit( $curl_url, $content, $dropbox_api_arg );
        

        $r = json_decode($result, true);
        
        $this->session_id = $r['session_id'];

        if( ! $this->session_id || $this->session_id == 'None' ) {
            $output['error'] = __( 'Error: Token de acceso incorrecto', 'appyn' );
            return $output;
        }
        return $r;

    }

    private function uploadFinish( $offset, $content ) {

        $output = array();
        $curl_url = 'https://content.dropboxapi.com/2/files/upload_session/finish';
        $dropbox_api_arg = array(
            "cursor" => array(
                "session_id" => $this->session_id,
                "offset" => $offset,
            ),
            "commit" => array(
                "path"=> '/'.basename($this->filename),
                "mode" => "add",
                "autorename" => true,
                "mute" => false,
                "strict_conflict" => false
            )
        );

        $result = $this->curlInit( $curl_url, $content, $dropbox_api_arg );

        $r = json_decode($result, true);

        if( isset($r['error_summary']) ) {
            $output['error'] = $r['error_summary'];
            return $output;
        }
        
        $dpd  = $this->pathDisplay($r);
        $output['url'] = $dpd['url'];

        return $output;  

    }

    private function uploadAppend( $offset, $content ) {

        $curl_url = 'https://content.dropboxapi.com/2/files/upload_session/append_v2';
        $dropbox_api_arg = array(
            "cursor" => array(
                "session_id" => $this->session_id,
                "offset" => $offset,
            ),
            "close" => false,
        );

        $this->curlInit( $curl_url, $content, $dropbox_api_arg );

    }

    private function pathDisplay($p) {

        $curl_url = 'https://api.dropboxapi.com/2/sharing/list_shared_links';
        $content = array(
            'path' => $p['id'],
        );

        $result = $this->curlInit( $curl_url, json_encode( $content ), false, 'json' );
        $r = json_decode($result, true);

        if( isset($r['links'][0]) ) {
            return str_replace('?dl=0', '?dl=1', $r['links'][0]);
        }

        $curl_url = 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings';

        $content = array(
            'path' => $p['path_display'],
            "settings" => array(
                "requested_visibility" => "public",
                "audience" => "public",
                "access" => "viewer",
            ),
        );

        $result = $this->curlInit( $curl_url, json_encode( $content ), false, 'json' );
        $r = json_decode($result, true);

        return $r;

    }
}