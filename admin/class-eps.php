<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class EPS {
    
    public $post_id;
    public $url_app;
    public $nonce;
    private $bot_info;
    private $info_app;
    private $info;
    private $options;
    private $term_id;
    private $dca;
    private $output;

    public function __construct() {

        $this->check_APIKey();
    }

    private function check_APIKey() {
        if( empty(appyn_options( 'apikey', true )) ) {
            $output = array();
            $output['info'] = __( 'Error: La API Key es inválida.', 'appyn' ).' <a href="https://themespixel.net/en/docs/appyn/api-key/" target="_blank">'.__( 'Más información', 'appyn' ).'</a>';
            die(json_encode($output));
        }
    }

    private function check_url_app() {

        if( empty($this->url_app) ){
            $output = array();
            $output['info'] = __( 'Error: No hay URL de Playstore en este post', 'appyn' );
            $output['error_field'] = 'consiguelo';
            die(json_encode($output));
        }
    }

    private function check_if_exist_url() {

        if( ! get_http_response_code( $this->url_app ) ) {
            $output = array();
            $output['info'] = __( 'Error: Al parecer la URL no existe. Verifique nuevamente.', 'appyn' );
            $output['error_field'] = 'consiguelo';
            die(json_encode($output));
        }
    }

    private function getData($get_apk = true) {
        
        $this->options['edcgp_sapk'] = appyn_options( 'edcgp_sapk', true );

        $body = array( 
            'apikey' 	=> appyn_options( 'apikey', true ),
            'website'	=> get_site_url(),
            'app'		=> trim($this->url_app)
        );

        if( !$this->options['edcgp_sapk'] && $get_apk == true ) {
            $body['apk'] = "true";
        }
        
        $output = array();

        $url = API_URL.'/v2/gplay';

        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 30,
            'blocking'    => true,
            'sslverify'   => false,
            'headers'     => array(
                'Referer' 		=> get_site_url(),
                'Cache-Control' => 'max-age=0',
                'Expect' 		=> '',
            ),
            'body' => $body,
        ) );

        if ( ! is_wp_error( $response ) ) {
            $bot = json_decode($response['body'], true);
            if( $bot['status'] == 'error' ) {
                $output['info'] = 'Error: '. $bot['response'];
                die( json_encode($output) );
            }
            return $bot;
        } else {
            $output['info'] = $response->get_error_message();
            die( json_encode($output) );
        }
    }

    public function showData( $url ) {
        $this->url_app = $url;
        $bot = $this->getData(false);

        return $bot['app'];

    }

    private function import_process() {

        $bot = $this->getData();

        $this->bot_info = $bot['app'];

        $this->info_app = array();
        $this->info_app['nombre']  				= $this->bot_info['title'];
        $this->info_app['contenido']  			= $this->bot_info['content'];
        $this->info_app['descripcion']  		= $this->bot_info['description'];
        $this->info_app['fecha_actualizacion']  = $this->bot_info['date'];
        $this->info_app['last_update'] 		 	= $this->bot_info['last_update'];
        $this->info_app['version']  			= $this->bot_info['version'];
        $this->info_app['requerimientos']  		= $this->bot_info['requires'];
        $this->info_app['novedades']  			= $this->bot_info['whats_new'];
        $this->info_app['imagecover']  			= $this->bot_info['icon'];
        $this->info_app['video']  				= $this->bot_info['video'];
        $this->info_app['tamano']  				= $this->bot_info['size'];
        $this->info_app['categoria']  			= $this->bot_info['cat'];
        $this->info_app['categoria_app']  		= $this->bot_info['app_cat'];
        $this->info_app['desarrollador']  		= $this->bot_info['developer'];
        $this->info_app['pago']  				= $this->bot_info['is_pay'];
        $this->info_app['descargas']  			= $this->bot_info['downloads'];
        $this->info_app['app_id']               = $this->bot_info['app_id'];
        
        $this->options = array();
        $this->options['edcgp_post_status']     = appyn_options( 'edcgp_post_status' );
        $this->options['edcgp_create_category'] = appyn_options( 'edcgp_create_category' );
        $this->options['edcgp_create_tax_dev'] 	= appyn_options( 'edcgp_create_tax_dev' );
        $this->options['edcgp_extracted_images']= appyn_options( 'edcgp_extracted_images' );
        $this->options['edcgp_sapk']			= appyn_options( 'edcgp_sapk' );
        $this->options['edcgp_mc']              = appyn_options( 'edcgp_mc' );
        $this->options['edcgp_eaa']             = appyn_options( 'edcgp_eaa' );

        $n = 0;
        foreach($this->bot_info['screenshots'] as $screenshot) { 
            if( $n < $this->options['edcgp_extracted_images'] ) {
                $this->info_app['imagenes'][$n] = $screenshot;
            }
            $n++;
        }	
        
        if( $this->options['edcgp_create_category'] != 1 ) {
            require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
            $this->term_id = term_exists($this->info_app['categoria'], 'category');
        
            if( !$this->term_id ) {
                $cat_defaults = array(
                    'cat_ID' => 0,
                    'cat_name' => $this->info_app['categoria'],
                    'taxonomy' => 'category'
                );
                $this->term_id = wp_insert_category($cat_defaults);
            }
        }
    }

    public function createPost( $url_app ) {
        
        $this->url_app = trim($url_app);

        $this->check_url_app();
        
        $this->check_if_exist_url();

        $this->checkExists();

        $this->import_process();
                
        $my_post = array(
            'post_title'    => wp_strip_all_tags( $this->info_app['nombre'] ),
            'post_content'  => $this->info_app['contenido'],
            'post_author'   => get_current_user_id(),
        );
        if( $this->options['edcgp_post_status'] == 1 ) {
            $my_post['post_status'] = 'publish';
        } else {
            $my_post['post_status'] = 'draft';
        }

        if( $this->options['edcgp_create_category'] != 1 ) {
            $my_post['post_category'] = array($this->term_id);
        }

        $this->post_id = wp_insert_post( $my_post );

        if( $this->post_id ) {
            $this->output['post_id'] = $this->post_id;
            $this->info = __( 'Información importada.', 'appyn' )."\n";
            $this->output['info_text'] = '<i class="fa fa-check"></i> '.sprintf(__( 'Entrada "%s" creada.', 'appyn' ), $this->info_app['nombre']).' <a href="'.get_edit_post_link($this->post_id).'" target="_blank">'.__( 'Ver post', 'appyn' ).'</a>';
        }

        return $this->after_process( 'create' );
    }

    public function updatePost( $post_id ) {

        if( appyn_options( 'edcgp_update_post', true ) && ! wp_get_post_parent_id($post_id) ) {
            $post_id = apply_filters( 'px_process_convert_post_old_version', $post_id );
        }

        $this->post_id = $post_id;
                
        $this->url_app = trim(get_datos_info('consiguelo', false, $this->post_id));

        $this->check_url_app();

        $this->check_if_exist_url();

        $this->import_process();

        $my_post = array(
            'ID'       		=> $this->post_id,
            'post_title'    => wp_strip_all_tags( $this->info_app['nombre'] ),
            'post_content'  => $this->info_app['contenido'],
            'post_author'   => get_current_user_id(),
        );
        $my_post['post_status'] = get_post_status($this->post_id);
        
        if( $this->options['edcgp_create_category'] != 1 && $this->options['edcgp_mc'] != 1 ) {
            $my_post['post_category'] = array($this->term_id);
        }
        
        $this->dca = appyn_options( 'dedcgp_descamp_actualizar', true ) ? appyn_options( 'dedcgp_descamp_actualizar', true ) : array();
        
        if( in_array('app_title', $this->dca) )
            unset($my_post['post_title']);
    
        if( in_array('app_content', $this->dca) )
            unset($my_post['post_content']);
        
        $cb = get_post_meta( $this->post_id, "custom_boxes", true );
        $ac = appyn_gpm( $this->post_id, 'appyn_ads_control' );

        wp_update_post( $my_post, true );

        update_post_meta( $this->post_id, "custom_boxes", $cb );
        update_post_meta( $this->post_id, "appyn_ads_control", $ac );
        
        if( !is_wp_error($this->post_id) ) {
            $this->output['post_id'] = $this->post_id;
            $this->info = __( 'Información actualizada.', 'appyn' )."\n";
            $this->output['info_text'] = '<i class="fa fa-check"></i> '.sprintf(__( 'Entrada "%s" actualizada.', 'appyn' ), $this->info_app['nombre']);
        }

        return $this->after_process( 'update' );
        
    }
    
    private function after_process( $type = 'create' ) {

        update_post_meta( $this->post_id, "px_app_id", $this->info_app['app_id'] );
        
        if( ($type == 'create' && $this->options['edcgp_create_category'] != 1) || ($type == 'update' && $this->options['edcgp_mc'] != 1) ) {
            wp_set_post_terms($this->post_id, $this->term_id, 'category');
        }
        
        if( $this->options['edcgp_create_tax_dev'] != 1 ) {
            $post_datos_informacion = str_replace(',', '', $this->info_app['desarrollador']);
            wp_insert_term( $post_datos_informacion, 'dev' );
            $this->term_id = term_exists( $post_datos_informacion, 'dev' );
            wp_set_post_terms( $this->post_id, $post_datos_informacion, 'dev' );
        }
        
        if( $type == 'update' )
            if( ! in_array('app_ico', $this->dca) ) {
                $eidcgp = appyn_options( 'eidcgp_update_post' );
                if( $eidcgp == 1 ) {
                    $attachment_id = get_post_thumbnail_id( $this->post_id );
                    if( $attachment_id ) {
                        $attachment_id = get_post_thumbnail_id( $this->post_id );
                        wp_delete_attachment( $attachment_id, true );
                        delete_post_thumbnail( $this->post_id );
                    }
                }
                        
                if( $eidcgp == 1 ) {
                    global $post;
                    $ppt = new WP_Query( array('post_parent' => $this->post_id) );
                    if( $ppt->have_posts() ) {
                        while( $ppt->have_posts() ) { $ppt->the_post();
                            $attachment_id = get_post_thumbnail_id( $post->ID );
                            wp_delete_attachment( $attachment_id, true );
                            delete_post_thumbnail( $post->ID );
                            set_post_thumbnail( $post->ID, $attachment_id );
                        }
                    }
                }
                $attach_id = px_upload_image( $this->info_app, $this->post_id );
            }
        
        if( $type == 'create' )
            $attach_id = px_upload_image( $this->info_app, $this->post_id );

        $datos_informacion = array(
            'descripcion' 			=> $this->info_app['descripcion'],
            'version' 				=> $this->info_app['version'],
            'tamano' 				=> $this->info_app['tamano'],
            'fecha_actualizacion' 	=> $this->info_app['fecha_actualizacion'],
            'last_update'		 	=> $this->info_app['last_update'],
            'requerimientos' 		=> $this->info_app['requerimientos'],
            'novedades' 			=> $this->info_app['novedades'],
            'app_status'	 		=> 'updated',
            'consiguelo' 			=> $this->url_app,
            'categoria_app'			=> $this->info_app['categoria_app'],
            'descargas'			    => $this->info_app['descargas'],
            'os'					=> 'ANDROID',
        );
    
        if( $type == 'update' )
            if( is_array($this->dca) )
                if( in_array('app_description', $this->dca) )
                    $datos_informacion['descripcion'] = get_datos_info('descripcion', false, $this->post_id);

        if( $type == 'update' && ! isset($this->bot_info['apk']) )
            $datos_informacion['tamano'] = get_datos_info('tamano', false, $this->post_id);

        if( $type == 'create' )
            $datos_informacion['app_status'] = 'new';
        
        if( $this->bot_info['is_pay'] )
            $datos_informacion['offer']['price'] = 'pago';

        if( $type == 'update' && empty( $this->bot_info['downloads'] ) )
            $datos_informacion['descargas'] = get_datos_info('descargas', false, $this->post_id);
        
        update_post_meta($this->post_id, "datos_informacion", $datos_informacion);
        update_post_meta($this->post_id, "datos_video", array('id' => $this->info_app['video']));
        if( isset($this->info_app['imagenes']) ) 
            update_post_meta($this->post_id, "datos_imagenes", $this->info_app['imagenes']);
        
        if( get_option( 'appyn_edcgp_rating' ) ) {
            
            $rating = $this->bot_info['rating'];
        
            update_post_meta($this->post_id, "new_rating_users", ((isset($rating['users'])) ? $rating['users'] : ''));
            update_post_meta($this->post_id, "new_rating_count", ((isset($rating['total'])) ? $rating['total'] : ''));
            update_post_meta($this->post_id, 'new_rating_average', ((isset($rating['average'])) ? $rating['average'] : ''));
        }

        if( $type == 'update' ) {

            $re = '/(?<=[?&]id=)[^&]+/m';
            preg_match_all($re, $this->url_app, $matches, PREG_SET_ORDER, 0);
            $get_app_id = $matches[0][0];
            
            $posts = get_option( 'trans_updated_apps' );

            unset($posts[$get_app_id]);

            update_option( 'trans_updated_apps', $posts );
            
            set_transient( 'trans_count_updated_apps', count($posts) );
            
            if( !$this->options['edcgp_sapk'] && !in_array('app_download_links', $this->dca) )
                $this->processAPK();

            if( $this->options['edcgp_eaa'] ) {
                $datos_download = get_datos_download( $this->post_id );

                foreach( $datos_download['links_options'] as $lo ) {

                    if( !isset($lo['link']) ) continue;

                    $attach_id = attachment_url_to_postid( $lo['link'] );
                    if( ! $attach_id ) {
                        $pi = pathinfo($lo['link']);
                        $i = 0;
                        do {
                            $i++;
                            $file = $pi['dirname'].'/'.$pi['filename'].'-part'.$i.'.'.$pi['extension'];
                            $attach_id = attachment_url_to_postid( $file );

                            if( ! $attach_id ) continue;

                            $fif = str_replace( $pi['filename'].'-part'.$i.'.'.$pi['extension'], $pi['filename'].'.'.$pi['extension'], get_attached_file($attach_id));

                            if( file_exists($fif) ) {
                                unlink( $fif );
                            }
                            wp_delete_attachment($attach_id, true);
                        } while( $i < 5 );
                        continue;
                    } else {
                        wp_delete_attachment($attach_id, true);
                    }
                }
            }
        } elseif( $type == 'create' ) {

            if( !$this->options['edcgp_sapk'] )
                $this->processAPK();

        }

        $this->output['info'] = $this->info;

        return json_encode($this->output);
    }

    private function processAPK() {

        if( $this->bot_info['apk'] ) {
        
            $re = '/(?<=[?&]id=)[^&]+/m';
            $str = $this->url_app;
            preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
            $idps = $matches[0][0];
    
            $this->output['apk_info'] = array(
                'post_id' => $this->post_id,
                'idps' 	  => $idps,
                'date' 	  => $this->bot_info['last_update'],
                'total_size' 	  => ( isset($this->bot_info['total_size']) ) ? $this->bot_info['total_size'] : '',
            );
    
            $this->output['apk_info']['url'] = $this->bot_info['apk'];
    
            $size = ( isset($this->bot_info['total_size']) ) ? px_btoc( $this->bot_info['total_size'] ) : '';
    
            $tsr = px_option_selected_upload(); 
    
            if( px_check_apk_obb($this->bot_info['apk']) ) {
                $this->output['apk_info']['text']['step1'] = '<i class="fa fa-file" aria-hidden="true"></i> '. __( 'Se encontró un archivo APK y OBB', 'appyn' );
                $this->output['apk_info']['text']['step2'] = '<i class="fa fa-download" aria-hidden="true"></i> '. __( 'Descargando archivo...', 'appyn' ).' '.$size.' (<span class="percentage" style="display:inline-block;">'. __( 'En proceso...', 'appyn' ).'</span>)';
                $this->output['apk_info']['text']['step3'] = '<i class="fa fa-spinner" aria-hidden="true"></i> '. sprintf( __( 'Subiendo a %s en ZIP', 'appyn' ), $tsr);
                
            } else {
                $this->output['apk_info']['text']['step1'] = '<i class="fa fa-file" aria-hidden="true"></i> '. __( 'Se encontró un archivo APK', 'appyn' );
                if( array_key_exists('zip', $this->bot_info['apk']) ) {
                    $this->output['apk_info']['text']['step1'] = '<i class="fa fa-file" aria-hidden="true"></i> '. __( 'Se encontró un archivo ZIP', 'appyn' );
                }
                $this->output['apk_info']['text']['step2'] = '<i class="fa fa-download" aria-hidden="true"></i> '. __( 'Descargando archivo...', 'appyn' ).' '.$size.' (<span class="percentage" style="display:inline-block;">'. __( 'En proceso...', 'appyn' ).'</span>)';
                $this->output['apk_info']['text']['step3'] = '<i class="fa fa-spinner" aria-hidden="true"></i> '. sprintf( __( 'Subiendo a %s', 'appyn' ), $tsr);
            }
        }
    }

    private function checkExists() {
        global $wpdb;
        if( appyn_options('edcgp_appd') != 1 ) {
            $results = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS  {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts  INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1  AND (( {$wpdb->prefix}postmeta.meta_key = 'datos_informacion' AND {$wpdb->prefix}postmeta.meta_value LIKE '%:\"{$this->url_app}\";%' )) AND {$wpdb->prefix}posts.post_type = 'post' AND (({$wpdb->prefix}posts.post_status = 'publish' OR {$wpdb->prefix}posts.post_status = 'future' OR {$wpdb->prefix}posts.post_status = 'draft')) GROUP BY {$wpdb->prefix}posts.ID ORDER BY {$wpdb->posts}.post_date DESC LIMIT 0, 10");
            
            if( count($results) != 0 )  {
                $output['info'] = sprintf(__( 'Error: La aplicación que desea importar ya existe. %s', 'appyn' ), '<a href="'.get_edit_post_link($results[0]->ID).'" target="_blank">'.__( 'Ver entrada', 'appyn' ).'</a>');
                echo json_encode($output);
                exit;
            }
        }
    }

}