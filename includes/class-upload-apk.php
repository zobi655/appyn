<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class UploadAPK {
    
    private $urlapk;
    private $post_id;
    private $idps;
    private $datos_download;
    private $uploaddir;
    private $files;
    private $lsa;
    private $da;
    private $op;
    private $curl;
    private $range;
    private $no_size;
    private $filename;
    private $filename_original;
    private $filesize;
    private $total_parts;
    private $filenames_parts;
    private $uploadfile;
    private $uploadfile_original;
    private $uploadURL;
    public $filetype;
    private $direct_url;
    private $temp_file;
    
    public function __construct( $post_id, $idps, $apk, $update, $range, $total_parts, $part = 0, $no_size = false ) {

        $this->urlapk           = $apk;
        $this->post_id          = $post_id;
        $this->idps             = $idps;
        $this->datos_download   = array();
        $this->uploaddir        = wp_upload_dir();
        $this->lsa              = px_last_slug_apk();
        $this->da               = "-".( !empty($update) ? date('d-m-Y', $update) : 1);
        $this->op               = 1;
        $this->range            = $range;
        $this->no_size          = $no_size;

        if( $this->checkAPK_OBB() ) {

            $ext = '.zip';
            $this->temp_file     = tempnam(sys_get_temp_dir(), "zip");

        } else {

            $n = array_keys($this->urlapk);
            $ext = ".apk";
            $this->filetype = $n[0];
			if( $this->filetype == "zip" ) {
				$ext = ".zip";
			}

            $this->direct_url    = $this->urlapk[$n[0]];
            $this->temp_file     = $this->direct_url;

        }
        
        $this->filename = sanitize_title($this->idps)."{$this->da}{$this->lsa}{$ext}";
        $this->filename_original = $this->filename;

        if( $total_parts >= 2 && $part > 0 ) {
            $this->filename     = sanitize_title($this->idps)."{$this->da}{$this->lsa}-part{$part}{$ext}";
            $this->total_parts  = $total_parts;
            $this->filenames_parts = array();
            for( $i=1; $i<=$total_parts; $i++ ) {
                $this->filenames_parts[] = $this->uploaddir['path'] . '/' . sanitize_title($this->idps)."{$this->da}{$this->lsa}-part{$i}{$ext}";
            }
        }
        $this->uploadfile_original   = $this->uploaddir['path'] . '/' . $this->filename_original;

        $this->uploadfile   = $this->uploaddir['path'] . '/' . $this->filename;

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) {
            if( ! appyn_options( 'shortlink_'.appyn_options( 'edcgp_sapk_shortlink' ), true ) ) {
                throw new Exception( __( 'ERROR: El acortador seleccionado no tiene la API Key...', 'appyn' ) );
                exit;
            }
        }
    }
    
    private function uploadTo() {

        $edcgpss = get_option( 'appyn_edcgp_sapk_server', 1 );

        switch( $edcgpss ) {
            case 1:
                $upload = $this->uploadToWP();
                break;
            case 2:
                $upload = $this->uploadToGDrive();
                break;
            case 3:
                $upload = $this->uploadToDropbox();
                break;
            case 4:
                $upload = $this->uploadToFTP();
                break;
            case 5:
                $upload = $this->uploadTo1Fichier();
                break;
            case 6:
                $upload = $this->uploadToOneDrive();
                break;
            case 7:
                $upload = $this->uploadToUptoBox();
                break;
        }
        
        sleep(5);
        delete_option( 'file_progress' );
            
        return $upload;
    }

    private function getFileSizeHeaders( $url = null ) {
        
        if( !$url ) {
            $url = $this->direct_url;
        }
        
        if( file_exists($this->uploadfile) )
            unlink( $this->uploadfile );

        if( $headers = get_headers($url, 1) ) {
            $headers = array_change_key_case($headers);
            if( isset($headers['content-length']) ){
                return $headers['content-length'];
            }
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_exec($ch);
        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        if( curl_errno($ch) ) {
            throw new Exception("ERROR: " .curl_error($ch));
            $this->deleteFile();
            exit;
        }

        if( isset($content_length) ){
            return $content_length;
        } else {
            return $this->getFileSizeHeaders( $url );
        }
    }

    private function combine_parts() {

        $fp = fopen($this->uploadfile_original, 'w');
        foreach( $this->filenames_parts as $fnp ) {
            $fgc = file_get_contents($fnp);
            fwrite( $fp, $fgc );
        }
        fclose($fp);

        $this->filesize = filesize($this->uploadfile_original);
    }

    public function uploadFile() {

        if( $this->checkAPK_OBB() ) {

            $this->saveFilesToZip();

            copy( $this->temp_file, $this->uploadfile );

            $upload = $this->uploadTo();

        } else {
            if( ! $this->no_size ) {
                $filesize = $this->getFileSizeHeaders();
            }
            $a = array();

            if( isset($this->filenames_parts) ) {
                foreach ($this->filenames_parts as $f) {
                    $a['files'][] = array(
                        'name'      => $f,
                    );
                }
                
                if( ! $this->no_size ) {
                    $a['totalsize'] = (is_array($filesize)) ? end($filesize) : $filesize;
                }
            } else {
                $a['name'] = $this->uploadfile;
                if( ! $this->no_size ) {
                    $a['filesize'] = (is_array($filesize)) ? end($filesize) : $filesize;
                }
            }

            update_option( 'file_progress', $a );

            $args = array( 'timeout' => 99999, 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0', 'stream' => true,  'filename' => $this->uploadfile);

            if( ! $this->no_size ) {
                $args['headers'] = array(
                    "Range" => "bytes=".$this->range[0]."-".$this->range[1],
                    'accept-ranges' => 'bytes');
            }

            if( wp_remote_get( $this->temp_file, $args) ) {

                $filesize_ = filesize($this->uploadfile);
                $a['filesize'] = $filesize_;
                update_option( 'file_progress', $a );
            }
            
            if( ! $this->no_size ) {
                if( $this->range[1] != $filesize ) {
                    exit;
                }
            }
            
            if( $this->total_parts > 0 )
                $this->combine_parts();
            
            if( ! $this->no_size ) {
                if( filesize($this->uploadfile_original) != $filesize ) {
                    $this->deleteFile();
                    throw new Exception('Error: Size does not match....');
                    exit;
                }
            }

            $upload = $this->uploadTo();
        }

        if( isset($upload['error']) ) {
            if( file_exists($this->uploadfile_original) )
               unlink( $this->uploadfile_original );
            return array('error' => sprintf( __( 'Error: %s', 'appyn' ), $upload['error']) );
        }
        
        if( ! $upload || ! $upload['url'] ) {
            if( file_exists($this->uploadfile_original) )
                unlink( $this->uploadfile_original );
            return array('error' => sprintf( __( 'Error: %s', 'appyn' ), __( 'Ocurrió un problema con el servidor de carga', 'appyn') ) );
        }
        
        $this->uploadURL = $upload['url'];
        
        $this->updatePostMeta();  		
        
        return $this->uploadURL;
    }

    private function checkAPK_OBB() {
            
        if( count($this->urlapk) < 2 ) return false;

        return ( array_key_exists('apk', $this->urlapk) && ( array_key_exists('obb', $this->urlapk) || array_key_exists('apk_2', $this->urlapk) ) ) ? true : false;
    }

    private function checkFileSize() {

        $filesize = (int) filesize($this->uploadfile_original);
        
        if( $filesize == 0 ) {
            unlink($this->uploadfile_original);
            return array('error' => __( 'El archivo pesa 0KB. No fue descargado ni subido', 'appyn' ) );
        } else {
            return $filesize;
        }

    }

    private function uploadToWP() {
        $attach_id = attachment_url_to_postid( $this->uploaddir['url'] . '/' . $this->filename_original );

        $wp_filetype = wp_check_filetype(basename($this->filename_original), null );

        $attachment = array(
            'post_mime_type'    => strip_tags($wp_filetype['type']),
            'post_title'        => $this->filename_original,
            'post_content'      => '',
            'post_status'       => 'inherit'
        );

        if( $attach_id ) {
            $attach_id = wp_update_attachment_metadata( $attach_id, $attachment );
        } else {
            $attach_id = wp_insert_attachment( $attachment, $this->uploadfile_original, $this->post_id );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToWP();
            }
        }

        if( $this->total_parts > 0 ) {
            sleep(5);
            foreach( $this->filenames_parts as $fnp ) {
                unlink($fnp);
            }
        }

        $url = $this->uploaddir['url'] . '/' . $this->filename_original;

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $url = px_shorten_download_link($url);
        
        return array( 'url' => $url );

    }

    private function uploadToDropbox() {

        if( !get_option('appyn_dropbox_result', null) ) {
            return array('error' => __( 'Falta la conexión con Dropbox', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToDropbox();
            }
        }

        $dropbox = new TPX_Dropbox();
        $upload = $dropbox->Upload( $this->uploadfile_original );

        $this->deleteFile();
        
        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );
        
        return $upload;

    }

    private function uploadToGDrive() {

        if( !get_option('appyn_gdrive_token', null) ) {
            
            return array('error' => __( 'No ha realizado la conexión a Google Drive', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToGDrive();
            }
        }

        $gdrive = new TPX_GoogleDrive();

        $d = appyn_options( 'gdrive_folder', true );
        $folder_id = ($d) ? $gdrive->createFolder( $d ) : null;

        $upload = $gdrive->insertFileToDrive( $this->uploaddir['path'].'/'.$this->filename_original, $this->filename_original, $folder_id );

        $this->deleteFile();

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );

        return $upload;

    }

    private function deleteFile() {
        
        if( $this->total_parts > 0 ) {
            foreach( $this->filenames_parts as $fnp ) {
                unlink($fnp);
            }
        }
        unlink($this->uploadfile_original);
    }

    private function uploadToFTP() {

        if( !appyn_options( 'ftp_name_ip', true ) || !appyn_options( 'ftp_username', true ) || !appyn_options( 'ftp_password', true ) || !appyn_options( 'ftp_url', true ) ) {
            return array('error' => __( 'Complete los campos para la conexión FTP', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToFTP();
            }
        }

        $ftp = new FTP();
        $upload = $ftp->Upload( $this->uploadfile_original, $this->filename_original );

        $this->deleteFile();

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );

        return $upload;
        
    }

    private function uploadTo1Fichier() {

        if( !appyn_options( '1fichier_apikey', true ) ) {
            return array('error' => __( 'Coloque el API Key de 1Fichier', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadTo1Fichier();
            }
        }

        try {
            $fichier = new fichier();
            $upload = $fichier->Upload( $this->uploadfile_original, $this->filename_original );
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }

        $this->deleteFile();

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );

        return $upload;
    }

    private function uploadToOneDrive() {

        if( !get_option('appyn_onedrive_access_token', null) ) {
            
            return array('error' => __( 'No ha realizado la conexión a OneDrive', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToOneDrive();
            }
        }

        try {
            $onedrive = new TPX_OneDrive();
            $upload = $onedrive->uploadFile( $this->uploaddir['path'].'/'.$this->filename_original, $this->filename_original );
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }

        $this->deleteFile();

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );

        return $upload;
    }
    
    private function uploadToUptoBox() {

        if( !get_option('appyn_uptobox_token', null) ) {
            
            return array('error' => __( 'No existe el token de acceso', 'appyn' ) );
        }

        $fileContent = $this->checkFileSize();

        if( isset($fileContent['error']) ) {
            if( $this->op > 2 ) {
                return $fileContent;
            } else {
                sleep(2000);
                $this->op++;
                return $this->uploadToUptoBox();
            }
        }

        try {
            $uptobox = new TPX_UptoBox();
            $upload = $uptobox->uploadFile( $this->uploaddir['path'].'/'.$this->filename_original, $this->filename_original );
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }

        $this->deleteFile();

        if( appyn_options( 'edcgp_sapk_shortlink', true ) ) $upload['url'] = px_shorten_download_link( $upload['url'] );

        return $upload;
    }

    private function updatePostMeta() {

        $this->datos_download['option']     = 'links';
        $this->datos_download[0]['link']    = $this->uploadURL;
        $this->datos_download[0]['texto']   = 'Link';

        if( $this->checkAPK_OBB() ) {
            $this->datos_download['type']   = 'apk_obb';
        } else {
            $this->datos_download['type']   = $this->filetype;
        }

        update_post_meta( $this->post_id, 'datos_download', $this->datos_download );

    }

    private function saveFilesToZip() {

		$zip = new ZipArchive();
		$zip->open($this->temp_file, ZipArchive::OVERWRITE);
		$apk = $this->urlapk;
        $arr_files = array();
        $filesize = 0;
        $sum_file_size = 0;
        $i__ = 0;

        foreach ($apk as $type => $f) {

            $fname = sanitize_title($this->idps);

            if( $n = strstr($type, '_') ) {
                $fname .= $n;
                $type = str_replace($n, '', $type);
            }
            
            $fname .= ".{$type}";

            $filesize = $this->getFileSizeHeaders($f);

            $arr_files['files'][$i__] = array(
                "name" => $this->uploaddir['path'] . '/' . $fname,
                "size" => ( is_array($filesize) ) ? end($filesize) : $filesize
            );
            $sum_file_size += ( is_array($filesize) ) ? end($filesize) : $filesize;
            $i__++;
        }
        $arr_files['totalsize'] = $sum_file_size;

        $this->files = array();

        update_option( 'file_progress', $arr_files );
        
        foreach ($apk as $type => $f) {

            $fname = sanitize_title($this->idps);

            if( $n = strstr($type, '_') ) {
                $fname .= $n;
                $type = str_replace($n, '', $type);
            }
            
            $fname .= ".{$type}";

            $this->files[] = $this->uploaddir['path'] . '/' . $fname;
            
            $fp = fopen($this->uploaddir['path'] . '/' . $fname, 'w+');
            $this->curl = curl_init($f);
            curl_setopt($this->curl, CURLOPT_FILE, $fp); 
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);  
	        curl_setopt($this->curl, CURLOPT_RANGE, 'start-stop');
            curl_exec($this->curl);
                    
            if( curl_errno($this->curl) ) {
                throw new Exception(curl_error($this->curl));
                exit;
            }
            
            $zip->addFile($this->uploaddir['path'] . '/' . $fname, $fname);
        }

		$zip->close();

        return $arr_files;

    }
}