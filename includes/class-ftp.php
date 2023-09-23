<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class FTP {
    
    private $ftp_server;
    private $ftp_port;
    private $ftp_user;
    private $ftp_pass;
    private $ftp_directory;
    private $ftp_url;
    private $conn_id;
    
    public function __construct() {

        $this->ftp_server   = appyn_options( 'ftp_name_ip', true );
		$this->ftp_port     = appyn_options( 'ftp_port', true ) ? appyn_options( 'ftp_port', true ) : 21;
		$this->ftp_user 	= appyn_options( 'ftp_username', true );
		$this->ftp_pass 	= appyn_options( 'ftp_password', true );
		$this->ftp_directory= appyn_options( 'ftp_directory', true ) ? trailingslashit(appyn_options( 'ftp_directory', true )) : '';
		$this->ftp_url		= untrailingslashit( appyn_options( 'ftp_url', true ) );
    }

    public function Upload( $file_path, $filename ) {

        $this->conn_id = @ftp_connect( $this->ftp_server , $this->ftp_port, 30 );
        
        if( !$this->conn_id ) {
            return array('error' => sprintf( __( 'No se pudo conectar a "%s". Verifique nuevamente', 'appyn' ), $this->ftp_server ) );
        }

        if( @ftp_login( $this->conn_id, $this->ftp_user, $this->ftp_pass ) ) {

			ftp_pasv($this->conn_id, true) or die( __( 'No se puede cambiar al modo pasivo', 'appyn' ) );
                        
            $ret = ftp_nb_put($this->conn_id, $this->ftp_directory.$filename, $file_path, FTP_BINARY);

            while( $ret == FTP_MOREDATA ) {
                $ret = ftp_nb_continue($this->conn_id);
            }
            if( $ret != FTP_FINISHED ) {
                return array('error' => __( 'No se pudo subir el archivo', 'appyn' ). ' - ' . error_get_last()['message']);
            } else {
                $link_download = $this->ftp_url.'/'.$filename;
                return array('url' => $link_download);
            }
        } else {
            return array('error' => __( 'Datos del servidor incorrectos. Verifique nuevamente', 'appyn' ) );
        }
        
        ftp_close($this->conn_id);
    }
}