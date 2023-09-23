<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

include_once __DIR__.'/../admin/panel.php';

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

function load_custom_wp_admin_style() {
	wp_enqueue_style( 'style-admin', get_bloginfo("template_directory").'/admin/assets/css/style.css', false, VERSIONPX, 'all' ); 

	wp_enqueue_style( 'style-font-awesome', get_template_directory_uri().'/assets/css/font-awesome-6.4.0.min.css', false, false, 'all' ); 

	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_style( 'thickbox' );
}

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_scripts' );

function load_custom_wp_admin_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_enqueue_script( 'media-upload' );

	wp_enqueue_script( 'thickbox' );

	wp_enqueue_script( 'my-upload' );

	wp_register_script( 'custom-upload', get_bloginfo('template_url').'/admin/assets/js/upload.js',array('jquery','media-upload','thickbox') );

	wp_enqueue_script( 'custom-upload' );

	wp_enqueue_script( 'colorpicker-custom', get_bloginfo("template_directory").'/admin/assets/js/colorpicker.js', array( 'wp-color-picker' ), false, true );
	
	wp_enqueue_script( 'admin-js', get_bloginfo("template_directory").'/admin/assets/js/js.js', false, VERSIONPX, true ); 
    wp_localize_script( 'admin-js', 'ajax_var', array(
        'url'    => admin_url( 'admin-ajax.php' ),
        'nonce'  => wp_create_nonce( 'admin_panel_nonce' ),
        'action' => 'px_panel_admin',
		'error_text' => __( 'Ocurrió un error', 'appyn' ),
		
    ) );
	global $post;
	$pp = isset($post->post_parent) ? $post->post_parent : null;
	$am = '';
	if( $pp != 0 ) {
		$am = "\n".__( 'Importante: Este post es una versión antigua', 'appyn' );
	}
	wp_localize_script( 'admin-js', 'vars', array(
		'_img' => __( 'Imagen', 'appyn' ),
		'_title' => __( 'Título', 'appyn' ),
		'_version' => __( 'Versión', 'appyn' ),
		'_import_text' => __( 'Importar datos', 'appyn' ),
		'_confirm_update_text' => __( '¿Quiere actualizar la información de esta aplicación? Recuerda que reemplazará toda la información.', 'appyn' ).$am,
    ) );
    wp_localize_script( 'admin-js', 'importgp_nonce', array(
        'nonce'  => wp_create_nonce( 'importgp_nonce' )
    ) );
	wp_localize_script( 'admin-js', 'md', array(
        'px_limit_filesize' => MAX_DOWNLOAD_FILESIZE,
    ) );
}

add_action( 'admin_notices', 'admin_notice_update' );

function admin_notice_update() {
	$url = 'https://themespixel.net/api.php?theme='.THEMEPX;
	$data = get_remote_html( $url );
	$result = json_decode($data, TRUE);
	if( $result && str_replace('.', '', VERSIONPX) < str_replace('.', '', $result['version']) ) {
    ?><?php add_thickbox(); ?>
		<?php $user_locale = strstr(get_user_locale(), '_', true); ?>
    <div class="notice notice-success is-dismissible">
		<p><?php echo sprintf(__( 'Está disponible una nueva versión del theme %s', 'appyn' ), ucfirst(THEMEPX)); ?> . <a href="https://themespixel.net/changelog.php?theme=<?php echo THEMEPX; ?>&lang=<?php echo $user_locale; ?>&TB_iframe=true&width=550&height=450" class="thickbox"><?php echo sprintf(__( 'Revisa los detalles de la versión %s', 'appyn' ), $result['version']); ?></a>. <a href="https://themespixel.net/login/" target="_blank"><?php echo __( 'Descárgala ahora', 'appyn' ); ?></a>.</p>
    </div>
    <?php
	}
}

add_filter( 'px_process_convert_post_old_version', 'px_process_convert_post_old_version_callback', 10, 2 );

function px_process_convert_post_old_version_callback( $post_id, $return = '' ) {
	global $wpdb;

	$post = get_post( $post_id );
 
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	if (isset( $post ) && $post != null) {
 
		$info = get_post_meta( $post->ID, 'datos_informacion', true );
		$cb = get_post_meta( $post->ID, 'custom_boxes', true );
		$post_title = $post->post_title;
		if( !empty( $info['version'] ) ) {
			$post_title .= ' '.$info['version'];
		}
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',
			'post_title'     => $post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
			'post_parent'	 => $post_id,
		);

		$new_post_id = wp_insert_post( $args );
		
		$p_name = wp_unique_post_slug( sanitize_title( $post_title ), $new_post_id, 'publish', 'post', $post->post_parent );

		wp_update_post( array(
			'ID' => $new_post_id,
			'post_date' => $post->post_date,
			'post_date_gmt' => $post->post_date_gmt,
			'post_name' => $p_name,
		));
		wp_update_post( array(
			'ID' => $post->ID,
			'post_date' => the_date('', '', '', FALSE),
			'post_date_gmt' => the_date('', '', '', FALSE),
		));
	
		update_post_meta( $post->ID, "custom_boxes", $cb );
		update_post_meta( $new_post_id, "custom_boxes", $cb );
 
		$taxonomies = get_object_taxonomies($post->post_type); 
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if( $meta_key == '_wp_old_slug' ) continue;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
 
		if( $return == 'redirect' ) {
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $post_id ) );
			exit;
		} else {
			return $post_id;
		}
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}

add_action( 'admin_action_px_convert_post_old_version', 'px_convert_post_old_version' );

function px_convert_post_old_version(){
	global $wpdb;
	
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'px_convert_post_old_version' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}
 
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
		return;
		
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

	apply_filters( 'px_process_convert_post_old_version', $post_id, 'redirect' );
}

add_filter( 'page_row_actions', 'px_duplicate_post_link', 10, 2 );

function px_duplicate_post_link( $actions, $post ) {
	if( current_user_can('edit_posts') && $post->post_parent == 0 && $post->post_status == "publish" && $post->post_type == "post" ) {

		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=px_convert_post_old_version&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="'.__( 'Convertir en versión anterior', 'appyn' ).'" rel="permalink">'.__( 'Convertir en versión anterior', 'appyn' ).'</a>';
	}
	return $actions;
}

add_action( 'admin_bar_menu', 'toolbar_admin_reports', 999 );

function toolbar_admin_reports( $wp_admin_bar ) {
	$args = array(
		'id'    => 'menu_reports',
		'title' => '<span class="ab-icon"></span><span class="ab-label">'.count_reports().'</span>',
		'href'  => admin_url().'admin.php?page=appyn_reports',
		'meta'  => array( 'class' => 'tbap-report'),
		'parent' => false, 
	);
	$wp_admin_bar->add_node( $args );
}

add_action( 'admin_bar_menu', 'toolbar_admin_px', 999 );

function toolbar_admin_px( $wp_admin_bar ) {
	$post_id = ( isset($_GET['post']) ) ? $_GET['post'] : NULL;
	$args = array(
		'id'    => 'appyn_importar_contenido_gp',
		'title' => '<span class="ab-icon"></span><span class="ab-label">'.__( 'Importar contenido (Google Play)', 'appyn' ).' </span>',
		'href'  => admin_url().'admin.php?page=appyn_importar_contenido_gp',
		'meta'  => array( 'class' => 'tbap-ipcgp'),
		'parent' => false, 
	);
	$wp_admin_bar->add_node( $args );
	$args = array(
		'id'    => 'appyn_mod_apps',
		'title' => '<span class="ab-icon"></span><span class="ab-label">'.__( 'Apps modificadas', 'appyn' ).' </span>',
		'href'  => admin_url().'admin.php?page=appyn_mod_apps',
		'meta'  => array( 'class' => 'tbap-ipcmda'),
		'parent' => false, 
	);
	$wp_admin_bar->add_node( $args );
	if( $post_id ) {
		if( get_post_type($post_id) == "post" ) {
			$args = array(
				'id'    => 'appyn_actualizar_informacion',
				'title' => '<span class="ab-icon"></span><span class="ab-label">'.__( 'Actualizar información', 'appyn' ).'</span>',
				'href'  => 'javascript:void(0)',
				'meta'  => array( 
					'class' => 'tbap-update', 
				),
				'parent' => false, 
			);
			$wp_admin_bar->add_node( $args );	
		}

		if( wp_get_post_parent_id($post_id) ) {
			$args = array(
				'id'    => 'appyn_view_post_parent',
				'title' => '<span class="ab-label">'.__( 'Ver post padre', 'appyn' ).'</span>',
				'href'  => get_edit_post_link( wp_get_post_parent_id($post_id) ),
				'parent' => false, 
			);
			$wp_admin_bar->add_node( $args );	
		}
	}

	$args = array(
		'id'    => 'appyn_updated_apps',
		'title' => '<span class="ab-icon"></span><span class="ab-label">'.__( 'Apps por actualizar', 'appyn' ).' <span>('. px_count_update_apps() .')</span></span>',
		'href'  => admin_url().'admin.php?page=appyn_updated_apps',
		'meta'  => array( 'class' => 'tbap-ipaua'),
		'parent' => false, 
	);
	$wp_admin_bar->add_node( $args );
}

add_action( 'admin_head', 'css_admin_bar' );
add_action( 'wp_head', 'css_admin_bar' );

function css_admin_bar() { 

	if( ! is_user_logged_in() ) return; 

	echo '
	<style type="text/css">
		.tbap-report .ab-icon::before,
		.tbap-update .ab-icon::before,
		.tbap-ipcgp .ab-icon::before,
		.tbap-ipaua .ab-icon::before {
			content: "\f534";
			display: inline-block;
			-webkit-font-smoothing: antialiased;
			font-family: "dashicons";
			font-display: "swap";
			vertical-align: middle;
			position: relative;
			top: -3px;
		}
		#wpadminbar #wp-admin-bar-appyn_importar_contenido_gp a {
			background: rgba(255,255,255,0.2);
		} 
		#wpadminbar #wp-admin-bar-appyn_importar_contenido_gp,
		#wpadminbar #wp-admin-bar-appyn_updated_apps {
			display: block;
		}
		.tbap-ipcgp .ab-icon::before,
		.tbap-ipcmda .ab-icon::before {
			content: "\f3ab";
			font-family: "Font Awesome 6 Brands";
			font-size: 17px;
			top: -2px;
		}
		.tbap-ipcmda .ab-icon::before {		
			content: "\f1c9";
			font-family: "Font Awesome 6 Free"	;
		}
		.tbap-update .ab-icon::before {
			content: "\f463";
            height: 19px;
		}
		.tbap-ipaua .ab-icon::before {
			content: "\f469";
            height: 19px;
		}
        .tbap-update.wait .ab-icon {
            animation: infinite-spinning 2s infinite;
            -webkit-animation: infinite-spinning 2s infinite;
            -webkit-animation-timing-function: linear;
            animation-timing-function: linear;
        }
		@media (max-width:768px) {
			.tbap-ipcgp .ab-icon::before,
			.tbap-ipaua .ab-icon::before {
				line-height: 1.33333333;
				height: 46px!important;
				text-align: center;
				width: 52px;
				font-size: 33px;
				vertical-align: inherit;
				top: 0;
			}
			.tbap-ipcgp .ab-icon::before {
				top: -5px;
			}
		}
        @keyframes infinite-spinning {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
	</style>';
}

add_action( 'admin_menu', 'px_admin_menu_edit_u' );

function px_admin_menu_edit_u() {
	global $submenu;
	$submenu['edit.php'][5][2] = add_query_arg( 'post_type', 'post', $submenu['edit.php'][5][2] );
}

add_action( 'admin_footer', function() {
	$screen = get_current_screen();
    if( $screen->parent_base == 'edit' && isset($_GET['post']) ) { 
		global $post;
		if( $post->post_parent == 0 ) { ?>
        <script>
			(function($) {
				if( $('.page-title-action').length ) { 
					$('.page-title-action').after('<a href="<?php echo wp_nonce_url('admin.php?action=px_convert_post_old_version&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ); ?>" class="page-title-action"><?php echo __( 'Convertir a versión anterior', 'appyn' ); ?></a>');
				}	
				setTimeout(() => {
					if( $('.edit-post-header__settings').length ) { 
						$('.edit-post-header__settings').prepend('<a href="<?php echo wp_nonce_url('admin.php?action=px_convert_post_old_version&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ); ?>" class="components-button is-tertiary"><?php echo __( 'Convertir a versión anterior', 'appyn' ); ?></a>');
					}	
				}, 1000);

				
			})(jQuery);
		</script>
<?php
		}
    }
});

add_action( 'admin_init', 'admin_init_url_action' );

function admin_init_url_action() {

	$action = isset($_GET['action']) ? $_GET['action'] : NULL;
	
	if( $action == "google_drive_connect" ) {
		$gdrive = new TPX_GoogleDrive();
		if( $gdrive->getClient() ) {
			if (!get_option('appyn_gdrive_token')) {
				header("Location: ".$gdrive->getClient()->createAuthUrl());
				exit;
			}
		}
	}

	if( $action == "new_gdrive_info" ) {
		delete_option( 'appyn_gdrive_token' );
		header("Location: ". admin_url('admin.php?page=appyn_panel#edcgp'));
		exit;
	}

	if( $action == "dropbox_connect" ) {
		$dropbox_app_key = appyn_options( 'dropbox_app_key' );
		header("Location: https://www.dropbox.com/oauth2/authorize?client_id={$dropbox_app_key}&redirect_uri=".add_query_arg('appyn_upload', 'dropbox', get_bloginfo('url'))."&response_type=code&token_access_type=offline"); 
		exit;
	}

	if( $action == "new_dropbox_info" ) {
		delete_option( 'appyn_dropbox_result' );
		header("Location: ". admin_url('admin.php?page=appyn_panel#edcgp'));
		exit;
	}
	
	if( $action == "ftp_connect" ) {

		$name_ip 	= appyn_options( 'ftp_name_ip', true );
		$port 		= appyn_options( 'ftp_port', true ) ? appyn_options( 'ftp_port', true ) : 21;
		$username 	= appyn_options( 'ftp_username', true );
		$password 	= appyn_options( 'ftp_password', true );
		$directory	= appyn_options( 'ftp_directory', true ) ? trailingslashit(appyn_options( 'ftp_directory', true )) : '';
		$url		= untrailingslashit( appyn_options( 'ftp_url', true ) );

		$conn_id = @ftp_connect( $name_ip , $port, 30 ) or die( sprintf( __( 'No se pudo conectar a "%s". Verifique nuevamente', 'appyn' ), $name_ip ) ); 
		
		if( !$url ) die( __( 'Complete el campo URL', 'appyn' ) );

		if( @ftp_login( $conn_id, $username, $password ) ) {
			ftp_pasv($conn_id, true) or die( __( 'No se puede cambiar al modo pasivo', 'appyn' ) );

			$filename = 'test-file.txt';
			$contents = 'Hello World';
			$tmp = tmpfile();
			fwrite($tmp, $contents);
			rewind($tmp);
			$tmpdata = stream_get_meta_data($tmp);

			if( @ftp_put( $conn_id, $directory.$filename, $tmpdata['uri'], FTP_ASCII ) ) {
				echo '<p><b>'.__( '¡Se ha creado el archivo "test-file.txt" en su servidor!', 'appyn' ).'</b></p>';
				
				if( !$url ) {
					echo '<p>'.__( 'Es importante que coloque el campo URL ya que esa será la dirección con la que los usuarios accederán a descargar los archivos. Complete el campo y realice nuevamente el test de conexión.', 'appyn' ).'</p>';
				} else {
                    echo '<p>'.sprintf( __( 'Accede al archivo a través de este %s', 'appyn' ), '<a href="'.$url.'/'.$filename.'" target="_blank">'. __( 'enlace', 'appyn' ).'</a>').'. '. __( 'Si no puede acceder al enlace debe colocar el campo URL de manera correcta.', 'appyn' ).'</p>';
					echo '<p>'. __( 'Si accedió al enlace y apareció el texto "Hello World" entonces la conexión fue exitosa.', 'appyn' ).'</p>';
                }
			} else {
				echo __( 'No se pudo generar el archivo de prueba. Es posible que el directorio que ha colocado no exista.', 'appyn' ) . ' - ' . error_get_last()['message'];
			}
			fclose($tmp);
		} else {
			echo __( 'Datos del servidor incorrectos. Verifique nuevamente', 'appyn' );
		}
		ftp_close($conn_id);  

		exit;
	}
	
	if( $action == "onedrive_connect" ) {
		$onedrive = new TPX_OneDrive();
		header( "Location: ". $onedrive->ODConnect() );
		exit;
	}

	if( $action == "new_onedrive_info" ) {
		delete_option( 'appyn_onedrive_access_token' );
		header("Location: ". admin_url('admin.php?page=appyn_panel#edcgp'));
		exit;
	}
}