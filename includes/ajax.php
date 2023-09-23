<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

add_action( 'wp_ajax_ajax_searchbox', 'ajax_searchbox' );  
add_action( 'wp_ajax_nopriv_ajax_searchbox', 'ajax_searchbox' );

function ajax_searchbox() {
	global $wpdb, $post;
	$searchtext = trim($_POST['searchtext']);	
	$return = '';
	$args = array( 
		'post_type' => array('post', 'blog'), 
		'wpse18703_title' => $searchtext, 
		'posts_per_page' => -1, 
		'post_status' => 'publish'
	);
	if( get_option( 'appyn_versiones_mostrar_buscador') == 1 ) {
		$args['post_parent'] = 0;
	} 
	$query = new WP_Query( $args );
	if( $query->have_posts() ): 
		
		while( $query->have_posts() ): $query->the_post();
			$datos_informacion = get_post_meta($post->ID, 'datos_informacion', true);
			$return .= '<li><a href="'.get_the_permalink().'" style="display:flex">';
			$return .= px_post_thumbnail( 'miniatura', $post );
			$return .= '<div><div>'.get_the_title().'</div>';
			if( get_post_type() == 'blog' ) {
				$return .= '<span>Blog</span>';
			}
			if( !empty($datos_informacion['version']) ) {
				$return .= px_post_mod().'<span>';
				if( !empty($datos_informacion['version']) ) {
					$return .= __( 'Versión', 'appyn' ).': '.$datos_informacion['version'];
				}
				$dev_terms = wp_get_post_terms( $post->ID, 'dev', array('fields' => 'all'));
				if( !empty($dev_terms) ) { 
					$return .= '<br>'.__( 'Desarrollador', 'appyn' ).': '.$dev_terms[0]->name;
				}
				$return .= '</span>';
                
			}
			$return .= '</div></a></li>';
			endwhile;
		
	endif;
	echo json_encode($return);
	die(); 
}

add_action( 'wp_ajax_post_rating', 'post_rating' ); 
add_action( 'wp_ajax_nopriv_post_rating', 'post_rating' );

function post_rating() {
	global $wpdb;
	$post_id = $_POST['post_id'];
	$rating_count = round($_POST['rating_count']);
	if(user_no_voted()){
		$a = (get_post_meta( $post_id, 'new_rating_count', true ) ? get_post_meta( $post_id, 'new_rating_count', true ) : 0) + $rating_count; 
		$b = (get_post_meta( $post_id, 'new_rating_users', true ) ? get_post_meta( $post_id, 'new_rating_users', true ) : 0) + 1; 
		update_post_meta( $post_id, 'new_rating_users', $b );
		update_post_meta( $post_id, 'new_rating_count', $a );
		update_post_meta( $post_id, 'new_rating_average', number_format(($a / $b), 1, ".", "") );

		if( !isset($_COOKIE['nw_rating']) ) {
			setcookie("nw_rating", $post_id, time()+(24*365), "/");
		} else {
			$nr = explode(",",$_COOKIE['nw_rating']);
			$nr[] = $post_id;
			setcookie("nw_rating", implode(",", $nr), time()+(24*365), "/");
		}
	}

	if( function_exists('w3tc_flush_post') ) {
		w3tc_flush_post( $post_id );
	}
	if( function_exists( 'wp_cache_post_change' ) ) {
		wp_cache_post_change( $post_id );
	}
	if( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
		wpfc_clear_post_cache_by_id( $post_id );
	}
	if( defined('LSCWP_V') ) {
		do_action( 'litespeed_purge_post', $post_id );
	}
	if( function_exists( 'rocket_clean_post' ) ) {
		rocket_clean_post( $post_id );
	}
	
	$ar = count_rating($post_id);
	$ar['users'] = number_format($ar['users'], 0, ",", ",");
	echo json_encode($ar);	
	die();
}

add_action( 'wp_ajax_boxes_add', 'ajax_boxes_add' );

function ajax_boxes_add(){
	
	$content = ( isset($_POST['content']) ) ? $_POST['content'] : '';
	$box_key = ( isset($_POST['keycount']) ) ? $_POST['keycount'] : 0;
	echo '<div class="boxes-a">
		<p><input type="text" id="custom_boxes-title-'.$box_key.'" class="widefat" name="custom_boxes['.$box_key.'][title]" value="" placeholder="'.__( 'Título para la caja', 'appyn' ).'"></p>
		<p>'; ?>
	<?php
	wp_editor($content, 'custom_boxes-'.$box_key, array('textarea_name' => 'custom_boxes['.$box_key.'][content]', 'textarea_rows' => 5,'quicktags' => array('buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'))
); ?>
	<?php echo '</p>
		<p><a href="javascript:void(0)" class="delete-boxes button">'.__( 'Borrar caja', 'appyn' ).'</a></p>
		</div>';
	die();
}

add_action( 'wp_ajax_permanent_boxes_add', 'ajax_permanent_boxes_add' );

function ajax_permanent_boxes_add(){

	$content = ( isset($_POST['content']) ) ? $_POST['content'] : '';
	$box_key = ( isset($_POST['keycount']) ) ? $_POST['keycount'] : 0;
	echo '<div class="boxes-a">
		<h4>'. sprintf( __( 'Caja permanente %s', 'appyn' ), '#'.($box_key+1) ) .'</h4>
		<p><input type="text" id="permanent_custom_boxes-title-'.$box_key.'" class="widefat" name="permanent_custom_boxes['.$box_key.'][title]" value="" placeholder="'.__( 'Título para la caja', 'appyn' ).'"></p>
		<p>'; ?>
	<?php wp_editor($content, 'permanent_custom_boxes-'.$box_key, array('textarea_name' => 'permanent_custom_boxes['.$box_key.'][content]', 'textarea_rows' => 5,'quicktags' => array('buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close')) ); ?>
	<?php echo '</p>
		<p><a href="javascript:void(0)" class="delete-boxes button">'.__( 'Borrar caja', 'appyn' ).'</a></p>
		</div>';
	die();
}

add_action( 'wp_ajax_app_report', 'px_app_report' );  
add_action( 'wp_ajax_nopriv_app_report', 'px_app_report' );

function px_app_report() {
	parse_str( $_POST['serialized'], $output );
	
	$continue = false;
	$recaptcha_site = get_option( 'appyn_recaptcha_site' );
	$recaptcha_secret = get_option( 'appyn_recaptcha_secret' );	
	if( $recaptcha_site && $recaptcha_secret ) {
		$secret = $recaptcha_secret;
		$token = $output['token'];
		$ch = curl_init("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$token);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		if( $response['success'] === true && $response['action'] == $output['action'] ) {
			$continue = true;
		}
	} else {
		$continue = true;
	}
	$info_new = array(
		'option' => $output['report-opt'],
		'details' => $output['report-details'],
		'email' => $output['report-email'],
	);
	if( $continue ) {
		$url = wp_get_referer();
		$post_id = url_to_postid( $url ); 
		$info = array();
		$info_db = get_post_meta( $post_id, 'px_app_report', true );
		if( $info_db ) {
			$info = json_decode( $info_db, true );
		}
		$info[] = $info_new;

		update_post_meta( $post_id, 'px_app_report', wp_slash(json_encode($info, JSON_UNESCAPED_UNICODE)) );

		$appyn_send_report_to_admin = appyn_options( 'send_report_to_admin' );

		$ropt = px_reports_opt();
        if( $appyn_send_report_to_admin ) {
            $admin_email = get_option( 'admin_email' );
            $subject = __( 'Post reportado', 'appyn' ).' - ' . get_bloginfo( 'name' );
            $message = '<p>'.__( 'Hola, tienes un post reportado', 'appyn' ). ':</p>';
            $message .= '<p><strong>'.__( 'Post', 'appyn' ).'</strong>: <a href="'.get_permalink( $post_id ).'">'.get_permalink( $post_id ).'</a></p>';
            $message .= '<p><strong>'.__( 'Asunto', 'appyn' ).'</strong>: '.$ropt[($info_new['option']-1)].'</p>';
            if( $info_new['details'] ) {
                $message .= '<p><strong>'.__( 'Detalles', 'appyn' ).'</strong>: '.wpautop( wp_strip_all_tags( $info_new['details'] ) ).'</p>';
            }
			$appyn_request_email = appyn_options( 'request_email' );
            if( $appyn_request_email ) {
                $message .= '<p><strong>'.__( 'Email', 'appyn' ).'</strong>: '.$info_new['email'].'</p>';
            }
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($admin_email, $subject, $message, $headers);
        }

		echo 1;
	} else {
		echo 0;
	}
	die();
}

add_action( 'wp_ajax_action_upload_apk', 'y_action_upload_apk' );

function y_action_upload_apk() {

	if( !isset($_POST['nonce']) ) exit;

	$nonce = sanitize_text_field( $_POST['nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'importgp_nonce' ) ) die ( '✋');
	
	$post_id 	= $_POST['post_id'];
	$idps 		= $_POST['idps'];
	$apk 		= $_POST['apk'];
	$update 	= $_POST['date'];
	$size_offset= $_POST['size_offset'];
	$size_init 	= $_POST['size_init'];
	$part 		= $_POST['part'];
	$total_parts= $_POST['total_parts'];
	$no_size	= ( isset($_POST['no_size']) ) ? filter_var($_POST['no_size'], FILTER_VALIDATE_BOOLEAN) : false;

	$range = array($size_init, $size_offset);
	
    try {
        $uploadAPK = new UploadAPK($post_id, $idps, $apk, $update, $range, $total_parts, $part, $no_size);
        $upload = $uploadAPK->uploadFile();
    } catch (Exception $e) {
		echo json_encode(array( 'error' => $e->getMessage() ));
		die;
	}

	if( isset($upload['error']) ) {
		$info = array( 'error' => $upload['error'] );
	} else {
		$info = array('info' => '<i class="fa fa-check"></i> '.__( 'Archivo subido y asignado al post.', 'appyn' ));
	}
	echo json_encode($info);
	die;
}

add_action( 'wp_ajax_action_get_filesize', 'y_action_get_filesize' );
add_action( 'wp_ajax_nopriv_action_get_filesize', 'y_action_get_filesize' );

function y_action_get_filesize() {

	$file = get_option( 'file_progress', null );

	$output = __( 'En proceso...', 'appyn' );

	if( isset($file['files']) ) {
		
		$size_total = (int) $file['totalsize'];

		if( $size_total ) {
			$actual_size = 0;
			foreach( $file['files'] as $file ) {
				$actual_size += file_exists($file['name']) ? (int) filesize($file['name']) : null;
			}
			if( $actual_size ) 
				$output = number_format( (($actual_size * 100) / $size_total ), 2, '.', '' ).'%';
		}
		echo $output;

	} else {

		if( !isset($file['name']) ) return;

		if( isset( $file['filesize'] ) ) {

			$size = (int) $file['filesize'];
			$actual_size = file_exists($file['name']) ? (int) filesize($file['name']) : null;

			if( $actual_size ) 
				$output = number_format( (($actual_size * 100) / $size ), 2, '.', '' ).'%';
		} else {

			$output = file_exists($file['name']) ? (int) filesize($file['name']) : null;
			if( $output > 0 ) {
				$output = px_btoc($output);
			} else {
				$output = __( 'En proceso...', 'appyn' );
			}
		}
		echo $output;
	}

	die;
}

add_action( 'wp_ajax_action_eps', 'eps_function' );
add_action( 'wp_ajax_nopriv_action_eps', 'eps_function' );

function eps_function() {
	global $wpdb;

	$type = $_POST['type'];
	$nonce = sanitize_text_field( $_POST['nonce'] );
	
	if ( ! wp_verify_nonce( $nonce, 'importgp_nonce' ) ) die ( '✋');

	$eps = new EPS();

	if( $type == "update" ) {
		
		$post_id = $_POST['post_id'];
		echo $eps->updatePost( $post_id );
	}
	elseif( $type == "create" ) {
		$url_app = $_POST['url_app'];
		echo $eps->createPost( $url_app );
	}

	exit;
}

add_action( 'wp_ajax_px_recaptcha_download_links', 'px_recaptcha_download_links' );
add_action( 'wp_ajax_nopriv_px_recaptcha_download_links', 'px_recaptcha_download_links' );

function px_recaptcha_download_links() {
	if( !isset($_POST['rdl_nonce']) ) exit;

	$nonce = sanitize_text_field( $_POST['rdl_nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'rdl_nonce' ) ) die ( '✋');

	$get_opt = $_POST['get_opt'];
	$get_dl = $_POST['get_dl'];
	$post_id = $_POST['post_id'];
	
	$sev2 		= appyn_options( 'recaptcha_v2_secret' ); 
	$siv2 		= appyn_options( 'recaptcha_v2_site' );

	$continue = false;

	if( $sev2 && $siv2 ) {
		$token = $_POST['token'];
		$ch = curl_init("https://www.google.com/recaptcha/api/siteverify?secret=".$sev2."&response=".$token);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);   
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);

		if( $response['success'] === true ) {
			$continue = true;
		}
	} else {
		$continue = true;
	}

	if( $continue ) {
		func_list_download_links($post_id, $get_opt, $get_dl);
	}

	exit;
}

add_action( 'wp_ajax_search_posts', 'search_posts' ); 

function search_posts() {
	global $post;
	$s = $_POST['s'];

	$query = new WP_Query(array( 'post_status' => 'publish', 'post_type' => 'post', 's' => $s, 'post_parent' => 0, 'posts_per_page' => 50));
	if( $query->have_posts() ) :
		echo '<ul>';
		while( $query->have_posts() ) : $query->the_post();
			echo '<li data-post-id="'.$post->ID.'">'.$post->post_title.'</li>';
		endwhile;
		echo '</ul>';
	else :
		echo '<div style="padding:5px 10px">'.__( 'No hay entradas', 'appyn' ).'</div>';
	endif;

	exit;
}

add_action( 'wp_ajax_search_mod_apps', 'search_mod_apps' );

function search_mod_apps() {

	$s = $_POST['s'];
	$url = API_URL.'/v2/mod/search/'.urlencode($s);

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
		'body' => array( 
			'apikey' 	=> appyn_options( 'apikey', true ), 
			'website'	=> get_site_url(),
		),
	) );

	if ( ! is_wp_error( $response ) ) {
		global $post;

		$query = new WP_Query( array( 'posts_per_page' => -1, 'post_parent' => 0, 'suppress_filters' => true, 'cache_results'  => false, 'meta_key' => 'mod_app_id' ) );

		$list_ids = array();

		if( $query->have_posts() ) :
			while( $query->have_posts() ) : $query->the_post();
				$mod_app_id = get_post_meta( $post->ID, 'mod_app_id', true );
				$list_ids[$mod_app_id] = $post->ID;
			endwhile;
		endif;

		$response_body = json_decode( $response['body'], true );

		foreach( $response_body['results'] as $keyr => $rb ) {
			foreach( $rb as $key => $r ) {
				if( array_key_exists( $r['u'], $list_ids ) ) {
					$r['impt'] = '<a href="'.get_edit_post_link( $list_ids[$r['u']] ).'" target="blank" title="'.__('View post').'"><i class="fas fa-file-import"></i></a>';
				}
				$response_body['results'][$keyr][$key] = $r;
			}
		}
		echo json_encode($response_body);
	} else {
		$error_string = $response->get_error_message();
		echo $error_string;
	}
	exit;
}

add_action( 'wp_ajax_mod_app_import', 'mod_app_import' );

function mod_app_import() {

	$id = $_POST['id'];
	$u = $_POST['u'];
	$url = API_URL.'/v2/mod/import/'.$id.'/'.$u;

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
		'body' => array( 
			'apikey' 	=> appyn_options( 'apikey', true ), 
			'website'	=> get_site_url(),
		),
	) );

	if ( ! is_wp_error( $response ) ) {

		$data = json_decode($response['body'], true);

		if( $data['status'] == 'error' ) die(json_encode($data));

		$app_url = ( $data['appid'] ) ? 'https://play.google.com/store/apps/details?id='.$data['appid'].'&hl='.get_option('WPLANG','en_US') : false;

		$di = array();
		
		if( $app_url ) {
			$eps = new EPS();
			$showData = $eps->showData( $app_url );
			$di['descripcion'] = $showData['description'];
			$di['descargas'] = $showData['downloads'];
			$di['requerimientos'] = $showData['requires'];
			$di['categoria_app'] = $showData['app_cat'];
			$di['fecha_actualizacion'] = $showData['date'];
			$di['last_update'] = $showData['last_update'];

			if( appyn_options( 'edcgp_create_category') != 1 ) {
				require_once( ABSPATH . '/wp-admin/includes/taxonomy.php' );
				$term_id = term_exists( $showData['cat'], 'category' );
			
				if( !$term_id ) {
					$cat_defaults = array(
						'cat_ID' => 0,
						'cat_name' => $showData['cat'],
						'taxonomy' => 'category'
					);
					$term_id = wp_insert_category( $cat_defaults );
				}
			}
		}

		$di['version'] = $data['version'];
		$di['tamano'] = $data['size'];
		$di['consiguelo'] = $app_url;
		$di['app_id'] = $data['appid'];

		$postarr = array(
			'post_title' => $data['title'],
			'post_content' => $data['content'],
			'post_status' => 'draft',
		);

		if( appyn_options( 'edcgp_create_category') != 1 ) $postarr['post_category'] = array($term_id);

		if( appyn_options( 'edcgp_post_status' ) == 1 ) $postarr['post_status'] = 'publish';

		$post_id = wp_insert_post($postarr);

		if( $app_url ) {
			
			$screenshots = array_slice( $showData['screenshots'], 0, appyn_options( 'edcgp_extracted_images' ) );

			update_post_meta( $post_id, 'datos_imagenes', $screenshots );
			update_post_meta( $post_id, 'datos_video', array('id' => $showData['video']) );
			
			if( appyn_options( 'edcgp_rating' ) ) {
				update_post_meta( $post_id, "new_rating_users", ((isset($showData['rating']['users'])) ? $showData['rating']['users'] : '') );
				update_post_meta( $post_id, "new_rating_count", ((isset($showData['rating']['total'])) ? $showData['rating']['total'] : '') );
				update_post_meta( $post_id, 'new_rating_average', ((isset($showData['rating']['average'])) ? $showData['rating']['average'] : '') );
			}
			
			if( appyn_options( 'edcgp_create_tax_dev' ) != 1 ) {
				$post_datos_informacion = str_replace(',', '', $showData['developer']);
				wp_insert_term( $post_datos_informacion, 'dev' );
				wp_set_post_terms(  $post_id, $post_datos_informacion, 'dev' );
			}

			px_upload_image( array('imagecover' => $showData['icon'], 'nombre' => $showData['title']), $post_id );
		}
			
		update_post_meta( $post_id, 'app_type', 1 );

		update_post_meta( $post_id, 'datos_informacion', $di );

		update_post_meta( $post_id, 'mod_app_id', $u );
			
		wp_set_post_terms( $post_id, $term_id, 'category' );

		$dd = array(
			array(
				'link' => px_shorten_download_link( $data['link'] ),
				'texto' => $data['text'],
			)
		);

		update_post_meta( $post_id, 'datos_download', $dd );

		echo json_encode(array('edit_link' => '<a href="'.get_edit_post_link( $post_id ).'" target="_blank">'.__( 'Ver post', 'appyn' ).'</a>'));

	} else {
		$error_string = $response->get_error_message();
		echo $error_string;
	}
	exit;
}