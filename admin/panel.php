<?php

if( !defined('ABSPATH') ) die ( '✋' );

add_action('admin_menu', 'appyn_theme');

function appyn_theme() {

	$roles = appyn_options( 'edcgp_roles' );
	if( $roles == "0" ) {
		$roles = 'administrator';
	}
	if( $roles == 'administrator' ) {
		$capability = 'manage_options';
	} 
	if( $roles == 'editor' ) {
		$capability = 'moderate_comments';
	} 
	if( $roles == 'author' ) {
		$capability = 'publish_posts';
	}
	if( $roles == 'contributor' ) {
		$capability = 'read';
	}
	
	add_menu_page( 'Appyn', 'Appyn', 'manage_options', 'appyn_panel', 'appyn_settings', get_template_directory_uri().'/admin/assets/images/ico-panel.png', 81 );
	
	add_submenu_page( 'appyn_panel', 'Panel', 'Panel', 'manage_options', 'appyn_panel' );
	
	add_submenu_page( 'appyn_panel', __( 'Importar contenido', 'appyn' ), __( 'Importar contenido', 'appyn' ), 'manage_options', 'appyn_importar_contenido_gp', 'appyn_importar_contenido_gp' );
	
	add_submenu_page( 'appyn_panel', __( 'Apps modificadas', 'appyn' ), __( 'Apps modificadas', 'appyn' ), $capability, 'appyn_mod_apps', 'appyn_mod_apps' );

	$hook = add_submenu_page( 'appyn_panel', __( 'Apps por actualizar', 'appyn' ), __( 'Apps por actualizar', 'appyn' ).' <span class="awaiting-mod" style="position:absolute;margin-left:5px"><span class="pending-count">'.px_count_update_apps(true).'</span></span>', 'manage_options', 'appyn_updated_apps', 'appyn_updated_apps' );
	
	add_submenu_page( 'appyn_panel', __( 'Reportes', 'appyn' ), __( 'Reportes', 'appyn' ).' <span class="awaiting-mod"><span class="pending-count">'.appyn_count_reports().'</span></span>', 'manage_options', 'appyn_reports', 'appyn_reports' );
	
	add_action( "load-$hook", 'px_screen_option' );

	add_submenu_page( 'appyn_importar_contenido_gp', __( 'Importar contenido (Google Play)', 'appyn' ), __( 'Importar contenido (Google Play)', 'appyn' ), $capability, 'appyn_importar_contenido_gp', 'appyn_importar_contenido_gp' );

	add_submenu_page( 'appyn_updated_apps', __( 'Apps por actualizar', 'appyn' ), __( 'Apps por actualizar', 'appyn' ), $capability, 'appyn_updated_apps', 'appyn_updated_apps' );

	add_submenu_page( 'appyn_panel', __( 'Registro de cambios', 'appyn' ), __( 'Registro de cambios', 'appyn' ), 'manage_options', 'appyn_changelog', 'appyn_changelog' );
	
	add_submenu_page( 'appyn_panel', __( 'Documentación', 'appyn' ), __( 'Documentación', 'appyn' ) , 'manage_options', 'appyn_docs', 'appyn_docs' );
	
}

function appyn_mod_apps() {
	echo '<div class="wrap"><h1>'.__( 'Buscar apps modificadas', 'appyn' ).'</h1><br>';

	echo '<form id="search_mod_apps">
		<input type="search" placeholder="'.__( 'Escribir...', 'appyn' ).'" value="" class="regular-text"><input type="submit" class="button" value="'.__( 'Buscar', 'appyn' ).'"><span class="spinner"></span>
	</form>';

	echo '<div id="box_info_mod_apps">
		<p><strong>'.__( 'Importante:', 'appyn' ).'</strong></p>'
		.__( '* La información que se importa es el título, información de la app, versión, enlace de descarga y peso del archivo. Todos los demás campos serán rellenados con la información obtenida de Google Play.', 'appyn' ).'<br>'
		.__( '* El contenido solo está disponible en idioma inglés.', 'appyn' ).'<br>'
		.__( '* Si no está disponible un enlace de descarga no se hará la importación.', 'appyn' ).'
	</div>';

	echo '<div id="sma_results"></div></div>';
}

function appyn_importar_contenido_gp() {

	echo '<h3>'.__( 'Importar contenido (Google Play)', 'appyn' ).'</h3>';
	echo '<div class="extract-box">
			<form id="form-import">
				<input type="text" class="widefat" id="url_googleplay" name="" value="" placeholder="URL GooglePlay" spellcheck="false"><input type="submit" class="button button-primary button-large" id="importar" value="'.__( 'Importar datos', 'appyn' ).'"><span class="spinner"></span>
			</form>
		</div>';
	echo '
	<p><em>'.__( 'Al hacer clic en "Importar datos" se creará un post con toda la información importada basándose en las opciones mostradas a continuación...', 'appyn' ).'</em></p>
	<h3>'.__( 'Opciones al importar datos', 'appyn' ).'</h3>
	<ul>
		<li>'.__( 'Estado del post', 'appyn' ).':</strong> <strong>'.( (appyn_options('edcgp_post_status') == 1 ) ? __( 'Publicado', 'appyn' ) : __( 'Borrador', 'appyn' ) ).'</strong></li>
		<li>'.__( 'Crear categoría si no existe', 'appyn' ).':</strong> <strong>'.( (appyn_options('edcgp_create_category') == 1 ) ? __( 'No', 'appyn' ) : __( 'Sí', 'appyn' ) ) .'</strong></li>		
 		<li>'.__( 'Crear taxonomía <i>Desarrollador</i> si no existe', 'appyn' ).': <strong>'.( (appyn_options('edcgp_create_tax_dev') == 1 ) ? __( 'No', 'appyn' ) : __( 'Sí', 'appyn' ) ) .'</strong></li>		
		<li>'.__( 'Obtener APK', 'appyn' ).': <strong>'.( (appyn_options('edcgp_sapk') ) ? __( 'No', 'appyn' ) : __( 'Sí', 'appyn' ) ) .'</strong></li>';
		
		if( appyn_options('edcgp_sapk') == 0 ) {
			echo '<li>'.__( 'Servidor de subida', 'appyn' ).': <strong>'. px_option_selected_upload() .'</strong></li>';
		}
		if( appyn_options('edcgp_sapk_shortlink') ) {
			echo '<li>'.__( 'Acortar enlace', 'appyn' ).': <strong>'. ucfirst(appyn_options( 'edcgp_sapk_shortlink' )) .'</strong></li>';
		}
		echo '
		<li>'.__( 'Cantidad de imágenes importadas', 'appyn' ).': <strong>'. appyn_options('edcgp_extracted_images') .'</strong></li>
		<li>'.__( 'Rating', 'appyn' ).': <strong>'.( (appyn_options('edcgp_rating') ) ? __( 'Sí', 'appyn' ) : __( 'No', 'appyn' ) ) .'</strong></li>
		<li>'.__( 'Apps duplicadas', 'appyn' ).': <strong>'.( (appyn_options('edcgp_appd') == 1 ) ? __( 'Sí', 'appyn' ) : __( 'No', 'appyn' ) ) .'</strong></li>
	</ul>';
	echo '<p><a href="'.admin_url().'admin.php?page=appyn_panel#edcgp">'.__( 'Cambiar opciones', 'appyn' ).'</a></p>';
}

function appyn_count_reports() {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT meta_value, post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'px_app_report' ORDER BY meta_id DESC");
	return $wpdb->num_rows;
}

function appyn_reports() {
	global $wpdb, $post;
	if( isset($_POST['appyn_reports']) ) {
		foreach( $_POST['posts_id'] as $id ) {
			delete_post_meta( $id, 'px_app_report' );
		}
	}
	$results = $wpdb->get_results( "SELECT meta_value, post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'px_app_report' ORDER BY meta_id DESC");
?>
	<div class="wrap">
		<h1><?php echo __( 'Reportes', 'appyn' ); ?></h1>
		<p><?php printf( _nx( __( '%s entrada reportada', 'appyn' ), __( '%s entradas reportadas', 'appyn' ), $wpdb->num_rows, 'group of people', 'appyn' ), $wpdb->num_rows ); ?></p>
		<form action="" method="post" class="form-report">
			<table class="wp-list-table widefat fixed striped table-report">
				<thead>
					<tr>
						<td class="check-column"><input type="checkbox" class="tr-checkall"></td>
						<th class="manage-column" style="width: 35%;"><?php echo __( 'Título', 'appyn' ); ?></th>
						<th class="manage-column" style="width: 45%;"><?php echo __( 'Detalles', 'appyn' ); ?></th>
						<th class="manage-column" style="width: 15%;"><?php echo __( 'Acción', 'appyn' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
		
					if( $results ) : foreach( $results as $post ) :
					$info = get_post_meta( $post->post_id, 'px_app_report', true );
					$info = json_decode( $info, true );
					?>
					<tr>
						<td><input type="checkbox" name="posts_id[]" value="<?php echo $post->post_id; ?>"></td>
						<td><a href="<?php echo get_edit_post_link( $post->post_id ); ?>" target="_blank" title="<?php echo __( 'Editar post', 'appyn' ); ?>"><?php echo get_the_title( $post->post_id ); ?></a></td>
						<td>
						<ol style="margin-top:0;">
						<?php 
						$info = array_reverse($info);
						$reports_opt = px_reports_opt();

						foreach( $info as $f ) {
							foreach( $reports_opt as $key => $opt ) {
								if( $f['option'] != ($key+1)  ) continue;

								echo "<li>".$opt."<br>";
								echo nl2br(stripslashes($f['details']))."<br>";
								echo ( isset($f['email']) ) ? $f['email'].'<br>' : ''."</li>";
							}
						}
						?>
						</ol>
						<td><a href="<?php echo get_the_permalink( $post->post_id ); ?>" target="_blank"><?php echo __( 'Ver post', 'appyn' ); ?></a></td>
					</tr>
					<?php endforeach;	
					else :
					?>
					<tr>
						<td colspan="100%" style="text-align: center;"><?php echo __( 'No hay reportes', 'appyn' ); ?></td>
					</tr>
					<?php
					endif; ?>
				</tbody>
			</table>
			<?php if( $results ) : ?>
			<p><input type="submit" class="button button-primary button-large" value="<?php echo __( 'Borrar reportes', 'appyn' ); ?>" name="appyn_reports"></p>
			<?php endif; ?>
		</form>
	</div>
<script type="text/javascript">
	jQuery(function($) {
		$('.tr-checkall').on('click', function() {
			if( $(this).is(':checked') ) {
				$('.table-report tbody input[type="checkbox"]').prop('checked', true);
			} else {
				$('.table-report tbody input[type="checkbox"]').prop('checked', false);
			}
		});
		$('.table-report tbody input[type="checkbox"]').on('click', function() {
			var is_checked_all = true;
			$('.table-report tbody input[type="checkbox"]').each(function(index, item){
				if( !$(this).is(':checked') ) {
					is_checked_all = false;
				}
			});
			if( is_checked_all ) {
				$('.tr-checkall').prop('checked', true);
			} else {
				$('.tr-checkall').prop('checked', false);
			}
		});
		$('.form-report').on('submit', function(e){
			var is_checked = false;
			$('.table-report tbody input[type="checkbox"]').each(function(index, item){
				if( $(this).is(':checked') ) {
					is_checked = true;
				}
			});
			if( !is_checked ) {
				e.preventDefault();
			}
		});
	});
</script>
<?php
}

function appyn_updated_apps() {
	?>
	<div class="wrap">
		<h1><?php echo __( 'Apps por actualizar', 'appyn' ); ?></h1>
		<?php
		$apps_to_update = new List_Table_ATUL();
		$apps_to_update->prepare_items();
		?>
		<form id="nds-user-list-form" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>">
			<?php 
			$apps_to_update->search_box( __( 'Buscar', 'appyn' ), 'nds-user-find');
			?>					
		</form>
		<?php $apps_to_update->display(); ?>
	</div>
<?php 
}

function appyn_changelog() { 
	echo '<script type="text/javascript">window.location="https://themespixel.net/'.( (lang_wp() == "en") ? 'en/' : '' ).'changelog/appyn/";</script>';
}

function appyn_docs() { 
	echo '<script type="text/javascript">window.location="https://themespixel.net/'.( (lang_wp() == "en") ? 'en/' : '' ).'docs/appyn/";</script>';
}

function lang_wp() {
	if( function_exists( 'icl_object_id' ) ){ //WPML
		$lang = ICL_LANGUAGE_CODE;
	} else {
		$lang = strstr(get_user_locale(), '_', true);
	}
	return $lang;
}

function appyn_settings() { ?> 
<div id="panel_theme_tpx">
    <div class="pttbox">
    	<form method="post" id="form-panel">
    		<div id="menu">
     			<ul>
      				<li style="background:#FFF; padding:10px 15px;"><b><?php echo __( 'Theme', 'appyn' ); ?>: </b>Appyn<br>
						  <b><?php echo __( 'Versión', 'appyn' ); ?>: </b><?php echo VERSIONPX; ?><br>
					</li>						  
                    <li>
						<a href="#general"><i class="fa fa-cog"></i> <?php echo __( 'Opciones generales', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#home"><i class="fa fa-home"></i> <?php echo __( 'Home', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#single"><i class="fa fa-file"></i> <?php echo __( 'Single', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#edcgp"><i class="fa-brands fa-google-play"></i> <?php echo __( 'Importador de contenido', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#servers"><i class="fa-solid fa-server"></i> <?php echo __( 'Servidores externos', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#shorteners"><i class="fas fa-link"></i> <?php echo __( 'Acortadores', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#historial_versiones"><i class="fa fa-history"></i> <?php echo __( 'Historial de versiones', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#blog"><i class="fa-solid fa-blog"></i> <?php echo __( 'Blog', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#color"><i class="fa-solid fa-palette"></i> <?php echo __( 'Colores', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#sidebar"><i class="fa fa-list-ul"></i> <?php echo __( 'Sidebar', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#ads"><i class="fa-solid fa-dollar-sign"></i> <?php echo __( 'Anuncios', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#amp"><i class="fa fa-bolt"></i> <?php echo __( 'AMP', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#footer"><i class="fa fa-terminal"></i> <?php echo __( 'Footer', 'appyn' ); ?></a>
					</li>
                    <li>
						<a href="#info"><i class="fa fa-info-circle"></i> <?php echo __( 'Info', 'appyn' ); ?></a>
					</li>
					
                    <li><div class="submit" style="clear:both">
                        <input type="submit" name="Submit" class="button-primary" value="<?php echo __( 'Guardar cambios', 'appyn' ); ?>">
                        <input type="hidden" name="appyn_settings" value="save">
                        </div></li>
     			</ul>
    		</div>
			
            <div class="section active" data-section="general">
				<h2><?php echo __( 'Opciones generales', 'appyn' ); ?></h2>
				<table class="table-main">
					<tr>
						<td>
							<h3><?php echo __( 'Logo', 'appyn' ); ?></h3>
								<div class="descr"><?php echo __( 'La imagen debe tener un límite de 60px de altura.', 'appyn' ); ?></div>
						</td>
						<td>
							<div class="regular-text-download df">
								<input type="text" name="logo" id="logo" value="<?php $logo = get_option( 'appyn_logo' ); echo (!empty($logo)) ? $logo : get_bloginfo('template_url').'/images/logo.png'; ?>" class="regular-text upload">
								<input class="upload_image_button" type="button" value="&#xf093;">
							</div>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Favicon', 'appyn' ); ?></h3></td>
						<td>
							<?php
							$favicon = appyn_options( 'favicon', true ) ? appyn_options( 'favicon', true ) : get_bloginfo('template_url').'/images/favicon.ico';
							?>
							<div class="regular-text-download">
								<p class="df"><input type="text" name="favicon" id="favicon" value="<?php echo $favicon; ?>" class="regular-text upload">
								<input class="upload_image_button" type="button" value="&#xf093;"></p>
							</div>
							<p><?php echo sprintf( __( 'Sube tus favicons de distintos tamaños para todos los dispositivos. %s', 'appyn' ), '<a href="https://themespixel.net/blog/genera-favicons-con-realfavicongenerator/" target="_blank">'.__( 'Leer tutorial', 'appyn' ).'</a>' ); ?></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Redes sociales', 'appyn' ); ?></h3></td>
						<td><?php $color_botones_sociales = appyn_options( 'social_single_color', 'default' ); ?>
							<table class="sub-table">
								<tr>
									<td><?php echo __( 'Color', 'appyn' ); ?></td>
									<td>
										<label><input type="radio" name="social_single_color" value="default"<?php checked($color_botones_sociales, 'default'); ?>> <?php echo __( 'Gris', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?> </label> &nbsp;
										<label><input type="radio" name="social_single_color" value="color"<?php checked($color_botones_sociales, 'color'); ?>> <?php echo __( 'Color', 'appyn' ); ?> </label>
									</td>
								</tr>
								<tr>
									<td>Facebook</td>
									<td><input type="text" name="social_facebook" value="<?php echo appyn_options( 'social_facebook', true ); ?>" class="regular-text text2"></td>
								</tr>
								<tr>
									<td>Twitter</td>
									<td><input type="text" name="social_twitter" value="<?php echo appyn_options( 'social_twitter', true ); ?>" class="regular-text text2"></td>
								</tr>
								<tr>
									<td>Instagram</td>
									<td><input type="text" name="social_instagram" value="<?php echo appyn_options( 'social_instagram', true ); ?>" class="regular-text text2"></td>
								</tr>
								<tr>
									<td>Youtube</td>
									<td><input type="text" name="social_youtube" value="<?php echo appyn_options( 'social_youtube', true ); ?>" class="regular-text text2"></td>
								</tr>
								<tr>
									<td>Pinterest</td>
									<td><input type="text" name="social_pinterest" value="<?php echo appyn_options( 'social_pinterest', true ); ?>" class="regular-text text2"></td>
								</tr>
								<tr>
									<td>Telegram</td>
									<td><input type="text" name="social_telegram" value="<?php echo appyn_options( 'social_telegram', true ); ?>" class="regular-text text2"></td>
								</tr>
							</table>
							
							
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Textos', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Modifica algunos textos por defecto', 'appyn' ); ?></div></td>
						<td>
							<?php
							$gte = appyn_options( 'general_text_edit', true );
							?>
							<p><?php echo __( 'Aplicaciones más calificadas', 'appyn' ); ?></p>
							<p><input type="text" name="general_text_edit[amc]" value="<?php echo ( isset($gte['amc']) ) ? $gte['amc'] : ''; ?>" class="regular-text"></p>

							<p><?php echo __( 'Últimas aplicaciones de nuestra web', 'appyn' ); ?></p>
							<p><input type="text" name="general_text_edit[uadnw]" value="<?php echo ( isset($gte['uadnw']) ) ? $gte['uadnw'] : ''; ?>" class="regular-text"></p>

							<p><?php echo __( 'Buscar una aplicación', 'appyn' ); ?></p>
							<p><input type="text" name="general_text_edit[bua]" value="<?php echo ( isset($gte['bua']) ) ? $gte['bua'] : ''; ?>" class="regular-text"></p>

							<p><?php echo __( 'Botón descargar APK', 'appyn' ); ?></p>
							<p><input type="text" name="general_text_edit[bda]" value="<?php echo ( isset($gte['bda']) ) ? $gte['bda'] : ''; ?>" class="regular-text"></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Comentarios', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Puedes mostrar los comentarios de Wordpress y también los de Facebook.', 'appyn' ); ?></div></td>
						<td>
							<?php $comments = get_option( 'appyn_comments' ); ?>
							<p><input type="radio" name="comments" value="wp" id="comments_wp" <?php checked( $comments, 'wp', true); ?> checked> 
							<label for="comments_wp"><?php echo __( 'Comentarios de Wordpress', 'appyn' ); ?></label></p>

							<p><input type="radio" name="comments" value="fb" id="comments_fb" <?php checked( $comments, 'fb', true); ?>> 
							<label for="comments_fb"><?php echo __( 'Comentarios de Facebook', 'appyn' ); ?></label></p>

							<p><input type="radio" name="comments" value="wpfb" id="comments_wpfb" <?php checked( $comments, 'wpfb', true); ?>> 
							<label for="comments_wpfb"><?php echo __( 'Comentarios de Wordpress y Facebook', 'appyn' ); ?></label></p>

							<p><input type="radio" name="comments" value="disabled" id="comments_disabled" <?php checked( $comments, 'disabled', true); ?>> 
							<label for="comments_disabled"><?php echo __( 'Desactivar comentarios', 'appyn' ); ?></label></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Códigos header', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca los códigos en el header, como: Google Analytics, Webmasters, Alexa, etc.', 'appyn' ); ?></div></td>
						<td><textarea spellcheck="false" name="header_codigos" class="widefat" rows="8"><?php echo stripslashes(get_option( 'appyn_header_codigos' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Códigos Recaptcha v3', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Para poder evitar que robots envíen los reportes automáticamente se recomienda usar recaptcha.', 'appyn' ); ?><br><a href="https://www.google.com/recaptcha/admin" target="_blank"><?php echo __( 'Obtener claves', 'appyn' ); ?></a></div></td>
						<td><?php
							$recaptcha_secret = get_option( 'appyn_recaptcha_secret' );
							$recaptcha_site = get_option( 'appyn_recaptcha_site' );
							?>
							<table class="sub-table">
								<tr>
									<td><?php echo __( 'Clave del sitio', 'appyn' ); ?></td>
									<td><input type="text" name="recaptcha_site" value="<?php echo $recaptcha_site; ?>" class="regular-text"></td>
								</tr>
								<tr>
									<td><?php echo __( 'Clave secreta', 'appyn' ); ?></td>
									<td><input type="text" name="recaptcha_secret" value="<?php echo $recaptcha_secret; ?>" class="regular-text"></td>
								</tr>
							</table>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Códigos Recaptcha v2', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Complete los códigos de reCaptcha v2.', 'appyn' ); ?><br><a href="https://www.google.com/recaptcha/admin" target="_blank"><?php echo __( 'Obtener claves', 'appyn' ); ?></a></div></td>
						<td><?php
							$recaptcha_v2_secret = get_option( 'appyn_recaptcha_v2_secret' );
							$recaptcha_v2_site = get_option( 'appyn_recaptcha_v2_site' );
							?>
							<table class="sub-table">
								<tr>
									<td><?php echo __( 'Clave del sitio', 'appyn' ); ?></td>
									<td><input type="text" name="recaptcha_v2_site" value="<?php echo $recaptcha_v2_site; ?>" class="regular-text"></td>
								</tr>
								<tr>
									<td><?php echo __( 'Clave secreta', 'appyn' ); ?></td>
									<td><input type="text" name="recaptcha_v2_secret" value="<?php echo $recaptcha_v2_secret; ?>" class="regular-text"></td>
								</tr>
							</table>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Lazy loading', 'appyn' ); ?> <?php echo px_label_help( __( 'Con esta función podrás retrasar la carga de imágenes. Esto hará que la web en general cargue más rápido debido a que las imágenes no cargarán inicialmente.', 'appyn' ) ); ?></h3>
							<div class="descr"><?php echo __( 'Retrasar carga de imágenes', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_lazy_loading = appyn_options( 'lazy_loading' );
							?>
							<p><label class="switch"><input type="checkbox" name="lazy_loading" value="1" <?php checked( $appyn_lazy_loading, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
				
					<tr>
						<td><h3><?php echo __( 'Apps de versiones anteriores', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Controla la aparición de apps de versiones anteriores en diferentes partes. Solo aparecerán las últimas versiones.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_vmh = appyn_options( 'versiones_mostrar_inicio' );
							?>
							<table class="sub-table">
								<tr>
									<td style="width:165px"><?php echo __( 'Inicio', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_inicio" value="0" <?php checked( $appyn_vmh, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmhc = appyn_options( 'versiones_mostrar_inicio_categorias' );
								?>
								<tr>
									<td><?php echo __( 'Categorías (Inicio)', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_inicio_categorias" value="0" <?php checked( $appyn_vmhc, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmhamc = appyn_options( 'versiones_mostrar_inicio_apps_mas_calificadas' );
								?>
								<tr>
									<td><?php echo __( 'Apps más calificadas (Inicio)', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_inicio_apps_mas_calificadas" value="0" <?php checked( $appyn_vmhamc, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmb = appyn_options( 'versiones_mostrar_buscador' );
								?>
								<tr>
									<td><?php echo __( 'Buscador', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_buscador" value="0" <?php checked( $appyn_vmb, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmtd = appyn_options( 'versiones_mostrar_tax_desarrollador' );
								?>
								<tr>
									<td><?php echo __( 'Taxonomía desarrollador', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_tax_desarrollador" value="0" <?php checked( $appyn_vmtd, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmc = appyn_options( 'versiones_mostrar_categorias' );
								?>
								<tr>
									<td><?php echo __( 'Categorías', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_categorias" value="0" <?php checked( $appyn_vmc, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmt = appyn_options( 'versiones_mostrar_tags' );
								?>
								<tr>
									<td><?php echo __( 'Etiquetas', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_tags" value="0" <?php checked( $appyn_vmt, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
								<?php
								$appyn_vmw = appyn_options( 'versiones_mostrar_widgets' );
								?>
								<tr>
									<td><?php echo __( 'Widgets', 'appyn' ); ?></td>
									<td><label class="switch"><input type="checkbox" name="versiones_mostrar_widgets" value="0" <?php checked( $appyn_vmw, 0 ); ?>><span class="swisr"></span></label></td>
								</tr>
							</table>
						</td>
					</tr>
								
					<tr>
						<td><h3><?php echo __( 'Fecha de cada post', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Muestra la fecha en que fue creado el post en todas partes (Inicio, Widgets,etc.)', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_post_date = appyn_options( 'post_date' );
							?>
							<p><label class="switch"><input type="checkbox" name="post_date" value="1" <?php checked( $appyn_post_date, 1 ); ?>><span class="swisr"></span></label></p>

							<?php
							$appyn_post_date_type = appyn_options( 'post_date_type' );
							?>
							<p><?php echo __( 'Tipo de fecha', 'appyn' ); ?></p>
							
							<p><label><input type="radio" name="post_date_type" value="0" <?php checked( $appyn_post_date_type, "0" ); ?>> <?php echo __( 'Fecha de creación del post', 'appyn' ); ?></label></p>
							
							<p><label><input type="radio" name="post_date_type" value="1" <?php checked( $appyn_post_date_type, 1 ); ?>> <?php echo __( 'Fecha de última actualización de la app', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Apps relacionadas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoger la manera de mostrar apps relacionadas', 'appyn' ); ?>.</div></td>
						<td>
							<?php 
							$art = get_option( 'appyn_apps_related_type', array() ); 
							?>
							<p><input type="checkbox" name="apps_related_type[]" value="cat" id="apps_related_type_cat" <?php echo ( is_array($art) && in_array('cat', $art) || empty($art) ) ? 'checked' : ''; ?>> 
							<label for="apps_related_type_cat"><?php echo __( 'Por categoría(s)', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>

							<p><input type="checkbox" name="apps_related_type[]" value="tag" id="apps_related_type_tag" <?php echo ( is_array($art) && in_array('tag', $art) ) ? 'checked' : ''; ?>> 
							<label for="apps_related_type_tag"><?php echo __( 'Por etiqueta(s)', 'appyn' ); ?></label></p>

							<p><input type="checkbox" name="apps_related_type[]" value="title" id="apps_related_type_title" <?php echo ( is_array($art) && in_array('title', $art) ) ? 'checked' : ''; ?>> 
							<label for="apps_related_type_title"><?php echo __( 'Por título similar', 'appyn' ); ?></label></p>

							<p><input type="checkbox" name="apps_related_type[]" value="random" id="apps_related_type_random" <?php echo ( is_array($art) && in_array('random', $art) ) ? 'checked' : ''; ?>> 
							<label for="apps_related_type_random"><?php echo __( 'Al azar', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Información para descargar los archivos', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escribe los pasos que servirán de ayuda para el usuario.', 'appyn' ); ?>
							<p><a href="javascript:void(0);" class="autocomplete_info_download_apk_zip"><?php echo __( 'Autocompletar texto por defecto', 'appyn' ); ?></a></p>
							</div>
							<div id="default_apps_info_download_apk" style="display:none;"><?php echo get_option( 'appyn_apps_default_info_download_apk' ); ?></div>
							<div id="default_apps_info_download_zip" style="display:none;"><?php echo get_option( 'appyn_apps_default_info_download_zip' ); ?></div>
						</td>
						<td>
							<?php
							$aida = appyn_options( 'apps_info_download_apk', true );
							$aidz = appyn_options( 'apps_info_download_zip', true );
							?>
							<p>[Title] = <?php echo __( 'Título de la aplicación', 'appyn' ); ?></p>
							<p><b><?php echo __( 'Para archivos APK', 'appyn' ); ?></b></p>
							<?php wp_editor( $aida, 'apps_info_download_apk', array( 'media_buttons' => false, 'textarea_rows' => 8, 'quicktags' => true ) ); ?><br>

							<p><b><?php echo __( 'Para archivos ZIP', 'appyn' ); ?></b></p>
							<p><em><?php echo __( 'Algunos ZIP pueden obtener archivos APK y OBB necesarios para la instalación, por ello se debe recomendar usar una aplicación para instalar ese tipo de archivos, nosotros recomendamos "Split APKs Installer".', 'appyn' ); ?><a href="https://play.google.com/store/apps/details?id=com.aefyr.sai" target="_blank"><?php echo __( 'Ver aplicación', 'appyn' ); ?></a></em></p>
							<?php wp_editor( $aidz, 'apps_info_download_zip', array( 'media_buttons' => false, 'textarea_rows' => 8, 'quicktags' => true ) ); ?>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Encriptar enlaces', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Con esta función podrás encriptar los enlaces de descarga.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_encrypt_links = appyn_options( 'encrypt_links' );
							?>
							<p><label class="switch"><input type="checkbox" name="encrypt_links" value="1" <?php checked( $appyn_encrypt_links, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Solicitar correo electrónico en reportes', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Activa esta opción si quieres que se le solicite el correo electrónico al usuario que realiza el reporte.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_request_emails = appyn_options( 'request_email' );
							?>
							<p><label class="switch"><input type="checkbox" name="request_email" value="1" <?php checked( $appyn_request_emails, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Enviar reporte por correo electrónico', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Activa esta opción si quieres que el reporte enviado por el usuario sea enviado a tu email de administrador.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$appyn_send_report_to_admin = appyn_options( 'send_report_to_admin' );
							?>
							<p><label class="switch"><input type="checkbox" name="send_report_to_admin" value="1" <?php checked( $appyn_send_report_to_admin, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Cinta "actualizado" desde la modificación del post', 'appyn' ); ?> <?php echo px_label_help( __( 'La cinta de "Actualizado" se muestra en los posts durante 15 días de creado el post. Al activar esta opción se basará en los 15 días de modificado el post.', 'appyn' ) ); ?></h3>
							<div class="descr"><?php echo __( 'Opción que permitirá mostrar la cinta desde la modificación del post', 'appyn' ); ?></div></td>
						<td>
							<?php
							$rupm = appyn_options( 'ribbon_update_post_modified' );
							?>
							<p><label class="switch"><input type="checkbox" name="ribbon_update_post_modified" value="1" <?php checked( $rupm, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Desactivar notificaciones de apps por actualizar', 'appyn' ); ?></h3></td>
						<td>
							<?php
							$dnau = appyn_options( 'disabled_notif_apps_update' );
							?>
							<p><label class="switch"><input type="checkbox" name="disabled_notif_apps_update" value="1" <?php checked( $dnau, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Cabecera fija', 'appyn' ); ?></h3></td>
						<td>
							<?php
							$dsth = appyn_options( 'sticky_header' );
							?>
							<p><label class="switch"><input type="checkbox" name="sticky_header" value="0" <?php checked( $dsth, "0" ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Apps por fila (Pc)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge la cantidad de apps por fila para la versión pc.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$aprpc = appyn_options( 'apps_per_row_pc', 6 );
							?>
							<select name="apps_per_row_pc">
								<option value="3"<?php selected(3, $aprpc); ?>>3</option>
								<option value="4"<?php selected(4, $aprpc); ?>>4</option>
								<option value="5"<?php selected(5, $aprpc); ?>>5</option>
								<option value="6"<?php selected(6, $aprpc); ?>>6 <?php echo __( '(Por defecto)', 'appyn'); ?></option>									
								<option value="7"<?php selected(7, $aprpc); ?>>7</option>
								<option value="8"<?php selected(8, $aprpc); ?>>8</option>
							</select>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Apps por fila (Móvil)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge la cantidad de apps por fila para la versión móvil.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$apr = appyn_options( 'apps_per_row_movil', 2 );
							?>
							<select name="apps_per_row_movil">
								<option value="1"<?php selected(1, $apr); ?>>1</option>									
								<option value="2"<?php selected(2, $apr); ?>>2 <?php echo __( '(Por defecto)', 'appyn'); ?></option>									
								<option value="3"<?php selected(3, $apr); ?>>3</option>
							</select>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Título en 2 líneas', 'appyn' ); ?><?php echo px_label_help( '<img src="'.get_template_directory_uri().'/admin/assets/images/title-2-lines.png" height="230">', true ); ?></h3>
							<div class="descr"><?php echo __( 'Por defecto el título se muestra en una sola línea. Con esta opción podrá mostrarlo en dos líneas.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$t2l = appyn_options( 'title_2_lines' );
							?>
							<p><label class="switch"><input type="checkbox" name="title_2_lines" value="1" <?php checked( $t2l, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Versión redondeada', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Diseño que mostrará las esquinas redondeadas de las cajas, botones y enlaces del tema.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$dro = appyn_options( 'design_rounded' );
							?>
							<p><label class="switch"><input type="checkbox" name="design_rounded" value="1" <?php checked( $dro, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Resultados automáticos', 'appyn' ); ?><?php echo px_label_help( '<img src="'.get_template_directory_uri().'/admin/assets/images/autosearch.gif" height="150">', true ); ?></h3>
							<div class="descr"><?php echo __( 'Activa o desactiva los resultados automáticos cuando el usuario realiza una búsqueda.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$ar = appyn_options( 'automatic_results' );
							?>
							<p><label class="switch"><input type="checkbox" name="automatic_results" value="0" <?php checked( $ar, "0" ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
						
					<tr>
						<td><h3><?php echo __( 'Sidebar', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Sidebar para la página de inicio, categorías, buscador, etiquetas, etc.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$ogs = appyn_options( 'og_sidebar' );
							?>
							<p><label class="switch"><input type="checkbox" name="og_sidebar" value="1" <?php checked( $ogs, 1 ); ?>><span class="swisr"></span></label></p>
							<p><a href="<?php bloginfo('url'); ?>/wp-admin/widgets.php"><?php echo __( 'Agregar Widgets', 'appyn' ); ?></a></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Ancho de página', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Por defecto el ancho de la página es de 1100px', 'appyn' ); ?></div></td>
						<td>
							<?php $wp = appyn_options( 'width_page' ); ?>
							
							<p><label><input type="radio" name="width_page" value="0" <?php checked( $wp, "0" ); ?> <?php checked( $wp, '' ); ?>> <?php echo __( 'Normal', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="width_page" value="1" <?php checked( $wp, 1 ); ?>> 100%</label></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Vista de apps', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge entre el diseño vertical y horizontal para las listas de apps', 'appyn' ); ?></div></td>
						<td>
							<?php $vap = appyn_options( 'view_apps' ); ?>
							
							<p><label><input type="radio" name="view_apps" value="0" <?php checked( $vap, "0" ); ?> <?php checked( $vap, '' ); ?>> <?php echo __( 'Vertical', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="view_apps" value="1" <?php checked( $vap, 1 ); ?>> <?php echo __( 'Horizontal', 'appyn' ); ?></label></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Menú inferior fijo (Móvil)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Activa esta opción para que aparezca un menu inferior solo en versión móvil', 'appyn' ); ?></div></td>
						<td>
							<?php
							$bm = appyn_options( 'bottom_menu' );
							?>
							<p><label class="switch"><input type="checkbox" name="bottom_menu" value="1" <?php checked( $bm, 1 ); ?>><span class="swisr"></span></label></p>
							<p><a href="<?php bloginfo('url'); ?>/wp-admin/nav-menus.php"><?php echo __( 'Crear menú', 'appyn' ); ?></a></p>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Buscador de Google', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca el ID del buscador de Google', 'appyn' ); ?></div></td>
						<td>
							<?php
							$sga = appyn_options( 'search_google_active' );
							?>
							<p><label class="switch"><input type="checkbox" name="search_google_active" value="1" <?php checked( $sga, 1 ); ?>><span class="swisr"></span></label></p>
							<p><input type="text" name="search_google_id" value="<?php echo appyn_options( 'search_google_id', true ); ?>" class="widefat">
							<p><a href="https://programmablesearchengine.google.com/controlpanel/all" target="_blank"><?php echo __( 'Crea tu buscador', 'appyn' ); ?></a></p>
						</td>
					</tr>
				</table>
            </div>

            <div class="section" data-section="home">
				<h2><?php echo __( 'Home', 'appyn' ); ?></h2>

				<table class="table-main">			

					<tr>
						<td><h3><?php echo __( 'Título', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca el título del Home', 'appyn' ); ?>.</div></td>
						<td>
						<input type="text" name="titulo_principal" value="<?php echo get_option( 'appyn_titulo_principal' ); ?>" class="widefat"></td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Descripción', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca la descripción del Home', 'appyn' ); ?>.</div></td>
						<td><textarea spellcheck="false" name="descripcion_principal" class="widefat" rows="5" spellcheck="false"><?php echo stripslashes(get_option( 'appyn_descripcion_principal' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Imágenes de Portada', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Sube hasta 5 imágenes para que aparezca en el Home aleatoriamente. De preferencia imágenes mayores de 1300px de ancho y 300px de altura', 'appyn' ); ?>.</div></td>
						<td>
							<table class="sub-table">
								<?php for($n=1;$n<=5;$n++) { ?>
								<tr>
									<td>
										<div class="regular-text-download df">
											<input type="text" name="image_header<?php echo $n; ?>" value="<?php echo get_option('appyn_image_header'.$n); ?>" class="regular-text">
											<input class="upload_image_button" type="button" value="&#xf093;">
										</div>
									</td>
								</tr>
								<?php } ?>
							</table>
						</td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Apps más calificadas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Slider con las aplicaciones más descargadas.', 'appyn' ); ?></div>
						</td>
						<td>
							<?php
							$mas_calificadas = get_option( 'appyn_mas_calificadas' );
							$mas_calificadas_limite = get_option( 'appyn_mas_calificadas_limite' );
							$mas_calificadas_limite = (empty($mas_calificadas_limite)) ? '5' : $mas_calificadas_limite;
							?>
							<p><label class="switch"><input type="checkbox" name="mas_calificadas" value="1" <?php checked( $mas_calificadas, 1 ); ?>><span class="swisr"></span></label></p>
							<?php
							$mas_calificadas_limite = get_option( 'appyn_mas_calificadas_limite' );
							$mas_calificadas_limite = (empty($mas_calificadas_limite)) ? '5' : $mas_calificadas_limite;
							echo '<p><input type="number" name="mas_calificadas_limite" size="2" value="'.$mas_calificadas_limite.'" class="input_number" required> '.__( 'Entradas', 'appyn' ).'</p>';							
							?>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Ocultar entradas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Oculta las entradas del inicio.', 'appyn' ); ?></div>
						</td>
						<td>
							<?php $h = appyn_options( 'home_hidden_posts' ); ?>
							<p><label class="switch"><input type="checkbox" name="home_hidden_posts" value="1" <?php checked( $h, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Entradas por página', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Límite de entradas.', 'appyn' ); ?></div>
						</td>
						<td><?php
							$home_limite = get_option( 'appyn_home_limite' );
							$home_limite = (empty($home_limite)) ? '12' : $home_limite;
							echo '<input type="number" name="home_limite" size="2" value="'.$home_limite.'" class="input_number" required> '.__( 'Entradas', 'appyn' ) ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Orden de las entradas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Ordena las entradas del Home por fecha, por modificación o aleatorio.', 'appyn' ); ?></div>
						</td>
						<td><?php $home_posts_orden = get_option( 'appyn_home_posts_orden' ); ?>
						
							<p><label><input type="radio" name="home_posts_orden" value="0" <?php checked( $home_posts_orden, "0" ); ?> <?php checked( $home_posts_orden, '' ); ?>> <?php echo __( 'Por fecha', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="home_posts_orden" value="modified" <?php checked( $home_posts_orden, 'modified' ); ?>> <?php echo __( 'Por modificación', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="home_posts_orden" value="rand" <?php checked( $home_posts_orden, 'rand' ); ?>> <?php echo __( 'Aleatorio', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Posts Categorías Home', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge qué categorías y cuántos de estos quieres que aparezca en el Home debajo de los últimos posts', 'appyn' ); ?>.</div>
						</td>
						<td><div style="overflow:auto; max-height:233px; margin-bottom:15px;"><?php
							$categorias_home = get_option( 'appyn_categories_home' );
							if( function_exists( 'icl_object_id' ) ){ //WPML
								$categorias_home = lang_object_ids($categorias_home,'category');
							}
                            $categories = get_categories(array( 'hide_empty'=> 0));

							foreach( $categories as $cat ) {
								if( !empty($categorias_home) ){
									if( $cat->count == 0 ) continue;
									if (@in_array($cat->term_id, $categorias_home) ){
										echo '<label><input type="checkbox" name="categories_home[]" value="'.$cat->term_id.'" checked> '.$cat->name .'('.$cat->count.')</label><br>';
									} else {
										echo '<label><input type="checkbox" name="categories_home[]" value="'.$cat->term_id.'"> '.$cat->name.' ('.$cat->count.')</label><br>';
									}
								} else {
									echo '<label><input type="checkbox" name="categories_home[]" value="'.$cat->term_id.'"> '.$cat->name.' ('.$cat->count.')</label><br>';
								}
							}?></div>
                            <?php
							$categories_home_limite = get_option( 'appyn_categories_home_limite' );
							$categories_home_limite = (empty($categories_home_limite)) ? '6' : $categories_home_limite;
							echo '<p><input type="number" name="categories_home_limite" size="2" value="'.$categories_home_limite.'" class="input_number" required> ' . __( 'Entradas', 'appyn' ).'</p>';
							?>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Ocultar blog', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Oculta las entradas del blog', 'appyn' ); ?></div>
						</td>
						<td>
							<?php $h = appyn_options( 'home_hidden_blog' ); ?>
							<p><label class="switch"><input type="checkbox" name="home_hidden_blog" value="1" <?php checked( $h, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Publicaciones destacadas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Muestra hasta 5 posts destacados que solo aparecen en el home', 'appyn' ); ?></div>
						</td>
						<td>
							<?php $hspc = appyn_options( 'home_sp_checked' ); ?>
							<p><input type="search" name="" class="widefat" id="search_posts" placeholder="<?php echo __( 'Buscar posts...', 'appyn' ); ?>"></p>
							<div id="sp_results"></div>
							<div id="sp_checked"><ul><?php
							if( $hspc ) {
								foreach( $hspc as $h ) {
									echo '<li><input type="checkbox" name="home_sp_checked[]" value="'.$h.'" checked style="display:none;">'.get_the_title($h).' <a href="javascript:void(0);" class="delete">×</a></li>';
								}	
							}
							?></ul></div>
						</td>
					</tr>
				</table>
			</div>

            <div class="section" data-section="edcgp">
				<h2><?php echo __( 'Importador de contenido', 'appyn' ); ?></h2>
					
				
				<table class="table-main">
					<tr>
						<td colspan="2"><p><strong>(*)</strong> <?php echo sprintf( __( 'Aplica para la sección %s', 'appyn' ), '<a href="'.admin_url('admin.php?page=appyn_mod_apps').'">'.__( 'apps modificadas', 'appyn' ).'</a>' ); ?></p></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'API Key', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Clave única que se necesita para poder utilizar el importador de contenido.', 'appyn' ); ?> <a href="https://themespixel.net/docs/appyn/api-key/" target="_blank"><?php echo __( '¿Cómo obtengo el mio?', 'appyn' ); ?></a></div>
						</td>
						<td><input type="text" name="apikey" id="apikey" value="<?php echo appyn_options( 'apikey', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Roles', 'appyn' ); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( 'Tipo de usuario a quienes aparecerá el importador de contenido.', 'appyn' ); ?> 
							</div>
						</td>
						<td>
							<?php 
							$roles = appyn_options( 'edcgp_roles' );
							?>
							<select name="edcgp_roles">
								<option value="administrator"<?php selected(0, $roles); ?><?php selected('administrator', $roles); ?>><?php echo __( 'Administrador', 'appyn'); ?></option>
								<option value="editor"<?php selected('editor', $roles); ?>><?php echo __( 'Administrador y Editor', 'appyn'); ?></option>
								<option value="author"<?php selected('author', $roles); ?>><?php echo __( 'Administrador, Editor y Autor', 'appyn'); ?></option>
								<option value="contributor"<?php selected('contributor', $roles); ?>><?php echo __( 'Administrador, Editor, Autor y Colaborador', 'appyn'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Estado del post', 'appyn'); ?> <strong>(*)</strong></h3></td>
						<td><?php $edcgp_post_status = appyn_options( 'edcgp_post_status' ); ?>
							
							<p><label><input type="radio" name="edcgp_post_status" value="0" <?php checked( $edcgp_post_status, 0 ); ?>> <?php echo __( 'Borrador', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="edcgp_post_status" value="1" <?php checked( $edcgp_post_status, 1 ); ?>> <?php echo __( 'Publicado', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Crear categoría', 'appyn'); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( 'Crea la categoría si no existe', 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_create_category = appyn_options( 'edcgp_create_category' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_create_category" value="0" <?php checked( $edcgp_create_category, "0"); ?>><span class="swisr"></span></label>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Crear taxonomía 'Desarrollador'", 'appyn'); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( "Crea la taxonomía 'Desarrollador' si no existe", 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_create_tax_dev = appyn_options( 'edcgp_create_tax_dev' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_create_tax_dev" value="0" <?php checked( $edcgp_create_tax_dev, "0"); ?>><span class="swisr"></span></label>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Obtener APK", 'appyn'); ?></h3></td>
						<td><?php 
							$edcgp_sapk = appyn_options( 'edcgp_sapk' );
							$edcgp_sapk_server = appyn_options( 'edcgp_sapk_server' ); 
							$edcgp_sapk_slug = appyn_options( 'edcgp_sapk_slug', true ); 
							$edcgp_sapk_shortlink = appyn_options( 'edcgp_sapk_shortlink', true );
							?>						
							<label class="switch switch-show" data-sshow="edcgp_sapk_server" data-svalue="0"><input type="checkbox" name="edcgp_sapk" value="0" <?php checked( $edcgp_sapk, "0"); ?>><span class="swisr"></span></label>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Servidor de subida", 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( "Seleccionar servidor para subir los archivos APK.", 'appyn' ); ?></div>
						</td>
						<td><select name="edcgp_sapk_server">
								<option value="1"<?php echo selected($edcgp_sapk_server, 1); ?>><?php echo __( 'Mi servidor', 'appyn' ); ?></option>
								<option value="2"<?php echo selected($edcgp_sapk_server, 2); ?>><?php echo __( 'Google Drive', 'appyn' ); ?></option>
								<option value="3"<?php echo selected($edcgp_sapk_server, 3); ?>><?php echo __( 'Dropbox', 'appyn' ); ?></option>
								<option value="4"<?php echo selected($edcgp_sapk_server, 4); ?>><?php echo __( 'FTP', 'appyn' ); ?></option>
								<option value="5"<?php echo selected($edcgp_sapk_server, 5); ?>><?php echo __( '1Fichier', 'appyn' ); ?></option>
								<option value="6"<?php echo selected($edcgp_sapk_server, 6); ?>><?php echo __( 'OneDrive', 'appyn' ); ?></option>
								<option value="7"<?php echo selected($edcgp_sapk_server, 7); ?>><?php echo __( 'UptoBox', 'appyn' ); ?></option>
							</select></td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Acortador", 'appyn' ); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( "Seleccionar acortador de preferencia para los enlaces de descarga.", 'appyn' ); ?></div>
						</td>
						<td><select name="edcgp_sapk_shortlink">
								<option value=""><?php echo __( 'None' ); ?></option>
								<option value="ouo"<?php echo selected($edcgp_sapk_shortlink, 'ouo'); ?>>Ouo.io</option>
								<option value="shrinkearn"<?php echo selected($edcgp_sapk_shortlink, 'shrinkearn'); ?>>ShrinkEarn.com</option>
								<option value="shorte"<?php echo selected($edcgp_sapk_shortlink, 'shorte'); ?>>Shorte.st</option>
								<option value="clicksfly"<?php echo selected($edcgp_sapk_shortlink, 'clicksfly'); ?>>ClicksFly.com</option>
								<option value="oke"<?php echo selected($edcgp_sapk_shortlink, 'oke'); ?>>Oke.io</option>
							</select><br><br>
							<p><i><?php echo sprintf(__( 'Importante: Coloque el API Key del acortador seleccionado en la sección %s.', 'appyn' ), '<strong><u>'.__( 'Acortadores', 'appyn' ).'</u></strong>' ); ?></i></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Slug", 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( "Agrega automáticamente un texto al final del nombre del archivo.", 'appyn' ); ?></div>
						</td>
						<td><input type="text" name="edcgp_sapk_slug" value="<?php echo $edcgp_sapk_slug; ?>" class="widefat"><br>
							<p><em><?php echo __( 'Ejemplo', 'appyn' ); ?>: com-file-<b><?php echo ( $edcgp_sapk_slug ) ? $edcgp_sapk_slug : 'example'; ?></b>.apk</em></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Capturas de pantalla", 'appyn'); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( 'Cantidad de capturas de pantalla de la aplicación.', 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_extracted_images = appyn_options( 'edcgp_extracted_images' ); ?>
							<input type="number" name="edcgp_extracted_images" value="<?php echo $edcgp_extracted_images; ?>" class="input_number"> <?php echo __( 'Capturas', 'appyn'); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( "Rating", 'appyn'); ?> <strong>(*)</strong></h3>
							<div class="descr"><?php echo __( 'Tomar las calificaciones de la aplicación.', 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_rating = appyn_options( 'edcgp_rating' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_rating" value="1" <?php checked( $edcgp_rating, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
					<tr>
						<td><h3><?php echo __( "Apps duplicadas", 'appyn'); ?></h3>
							<div class="descr"><?php echo __( 'Poder importar aplicaciones duplicadas', 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_appd = appyn_options( 'edcgp_appd' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_appd" value="1" <?php checked( $edcgp_appd, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
				</table><br>

				<h2><?php echo __( 'Al actualizar información de una app', 'appyn' ); ?></h2>
				
				<table class="table-main tmnb">
					<tr>
						<td><h3><?php echo __( "Crear una nueva versión", 'appyn'); ?></h3>
							<div class="descr"><?php echo __( 'Crea un post con la versión nueva cuando actualiza una app.', 'appyn' ); ?></div>
						</td>
						<td><?php $edcgp_up = appyn_options( 'edcgp_update_post' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_update_post" value="1" <?php checked( $edcgp_up, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
					<tr>
						<td><h3><?php echo __( 'Deshabilitar campos', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Campos a deshabilitar al momento de actualizar la información de una aplicación.', 'appyn' ); ?></div>
						</td>
						<td><?php 
							$dca = get_option( 'appyn_dedcgp_descamp_actualizar', array() );
							?>
							<p><label><input type="checkbox" name="dedcgp_descamp_actualizar[]" value="app_title" <?php echo (is_array($dca) && in_array('app_title', $dca) ) ? 'checked' : ''; ?>> <span><?php echo __( 'Título', 'appyn' ); ?></span></label></p>

							<p><label><input type="checkbox" name="dedcgp_descamp_actualizar[]" value="app_description" <?php echo (is_array($dca) && in_array('app_description', $dca) ) ? 'checked' : ''; ?>> <span><?php echo __( 'Descripción', 'appyn' ); ?></span></label></p>

							<p><label><input type="checkbox" name="dedcgp_descamp_actualizar[]" value="app_content" <?php echo (is_array($dca) && in_array('app_content', $dca) ) ? 'checked' : ''; ?>> <span><?php echo __( 'Contenido', 'appyn' ); ?></span></label></p>

							<p><label><input type="checkbox" name="dedcgp_descamp_actualizar[]" value="app_ico" <?php echo (is_array($dca) && in_array('app_ico', $dca) ) ? 'checked' : ''; ?>> <span><?php echo __( 'Ícono', 'appyn' ); ?></span></label></p>

							<p><label><input type="checkbox" name="dedcgp_descamp_actualizar[]" value="app_download_links" <?php echo (is_array($dca) && in_array('app_download_links', $dca) ) ? 'checked' : ''; ?>> <span><?php echo __( 'Enlaces de descarga', 'appyn' ); ?></span></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Eliminar imagen destacada antigua', 'appyn'); ?></h3>
							<div class="descr"><?php echo __( 'Elimina la imagen destacada antigua del post. Afectará también a las versiones antiguas.', 'appyn' ); ?></div>
						</td>
						<td><?php $eid = appyn_options( 'eidcgp_update_post' ); ?>
							<label class="switch"><input type="checkbox" name="eidcgp_update_post" value="1" <?php checked( $eid, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
					<tr>
						<td><h3><?php echo __( "Mantener categorías", 'appyn'); ?></h3>
							<div class="descr"><?php echo __( 'Conserva las mismas categorías cuando un post es actualizado.', 'appyn' ); ?></div>
						</td>
						<td><?php $mc = appyn_options( 'edcgp_mc' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_mc" value="1" <?php checked( $mc, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
					<tr>
						<td><h3><?php echo __( "Eliminar archivos antiguos", 'appyn'); ?></h3>
 							<div class="descr"><?php echo __( 'Cuando actualiza una app, el archivo antiguo será eliminado.', 'appyn' ); ?></div>
						</td>
						<td><?php $eaa = appyn_options( 'edcgp_eaa' ); ?>
							<label class="switch"><input type="checkbox" name="edcgp_eaa" value="1" <?php checked( $eaa, 1 ); ?>><span class="swisr"></span></label></td>	
					</tr>
				</table><br>
			</div>

			<div class="section" data-section="servers">
				<h2><?php echo __( 'Servidores externos', 'appyn' ); ?></h2>
				
				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/api-google-drive-obtener-id-de-cliente-y-secreto-para-almacenamiento/' : 'https://themespixel.net/en/google-drive-api-get-client-id-and-secret-for-storage/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( 'Google Drive', 'appyn' ); ?></h2>	
						<?php echo __( 'Crea una API de Google Drive y coloca el ID de cliente y código secreto en los siguientes campos.', 'appyn' ); ?> <?php echo sprintf( __( 'Siga el %s para crear la API.', 'appyn' ), '<a href="'.$url.'" target="_blank">'. __( 'Tutorial', 'appyn' ). '</a>' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'ID de cliente', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="gdrive_client_id" id="gdrive_client_id" value="<?php echo appyn_options( 'gdrive_client_id', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Secreto del cliente', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="gdrive_client_secret" id="gdrive_client_secret" value="<?php echo appyn_options( 'gdrive_client_secret', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Carpeta', 'appyn' ); ?><?php echo px_label_help( __('Coloca el nombre de una carpeta que se creará automáticamente y se subirán allí todos los archivos.', 'appyn' )); ?></h3></td>
						<td><input type="text" name="gdrive_folder" id="gdrive_folder" value="<?php echo appyn_options( 'gdrive_folder', true ); ?>" class="widefat"></td>
					</tr>
					<?php 
					$gdt = appyn_options( 'gdrive_token' );
					?>
					<tr>
						<td><?php 
							if( $gdt ) {
								echo '<a href="'.admin_url('admin.php?page=appyn_panel&action=new_gdrive_info#edcgp').'">'.__( 'Conectar nueva cuenta', 'appyn'). '</a>'.px_label_help( __('Haga clic aquí solo si ha añadido un nuevo ID de cliente y Secreto. Aparecerá nuevamente el botón de conectar que deberá pulsar.', 'appyn' ) );
							}
							?>
						</td>
						<td>
							<?php
							if( $gdt && appyn_options( 'gdrive_client_secret', true )  && appyn_options( 'gdrive_client_id', true ) ) {
								echo '<strong style="color:#50b250"><i class="fa fa-check"></i> '.__( 'Conectado a Google Drive', 'appyn').'</strong>';
							} else {
								echo '<p id="alert_test_gdrive" style="display:none; font-weight:bold;">'. __( 'Recuerda guardar los cambios antes de realizar la conexión', 'appyn' ). '</p>';
								echo '<a class="button" id="button_google_drive_connect" href="'. admin_url(). 'admin.php?page=appyn_panel&action=google_drive_connect">'.__( 'Conectar a Google Drive', 'appyn' ).'</a>';
							}
							?>	
						</td>
					</tr>
				</table><br>

				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/crear-app-de-dropbox-para-almacenamiento-de-archivos/' : 'https://themespixel.net/en/create-dropbox-app-for-file-storage/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( 'Dropbox', 'appyn' ); ?></h2>	
						<?php echo __( 'Crea una app en Dropbox y coloca el app key y app secret en los siguientes campos.', 'appyn' ); ?> <?php echo sprintf( __( 'Siga el %s.', 'appyn' ), '<a href="'.$url.'" target="_blank">'.__( 'tutorial', 'appyn' ). '</a>' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'App key', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="dropbox_app_key" id="dropbox_app_key" value="<?php echo appyn_options( 'dropbox_app_key', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'App secret', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="dropbox_app_secret" id="dropbox_app_secret" value="<?php echo appyn_options( 'dropbox_app_secret', true ); ?>" class="widefat"></td>
					</tr>
					<?php 
					$dbr = appyn_options( 'dropbox_result' );
					?>
					<tr>
						<td><?php 
							if( $dbr ) {
								echo '<a href="'.admin_url('admin.php?page=appyn_panel&action=new_dropbox_info#edcgp').'">'.__( 'Conectar nueva cuenta', 'appyn'). '</a>'.px_label_help( __('Haga clic aquí solo si ha añadido un nuevo app key y app secret. Aparecerá nuevamente el botón de conectar que deberá pulsar.', 'appyn' ) );
							}
							?>
						</td>
						<td>
							<?php
							if( $dbr && appyn_options( 'dropbox_app_key', true )  && appyn_options( 'dropbox_app_secret', true ) ) {
								echo '<strong style="color:#50b250"><i class="fa fa-check"></i> '.__( 'Conectado a Dropbox', 'appyn').'</strong>';
							} else {
								echo '<p id="alert_test_dropbox" style="display:none; font-weight:bold;">'. __( 'Recuerda guardar los cambios antes de realizar la conexión', 'appyn' ). '</p>';
								echo '<a class="button" id="button_dropbox_connect" href="'. admin_url(). 'admin.php?page=appyn_panel&action=dropbox_connect">'.__( 'Conectar a Dropbox', 'appyn' ).'</a>';
							}
							?>	
						</td>
					</tr>
				</table><br>

				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/crear-app-de-dropbox-para-almacenamiento-de-archivos/' : 'https://themespixel.net/en/create-dropbox-app-for-file-storage/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( 'FTP', 'appyn' ); ?></h2>	
						<?php echo __( '¿Tienes un servidor externo? Realiza la conexión vía FTP para que los archivos importados sea subido a su propio servidor.', 'appyn' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Nombre o IP del servidor', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="ftp_name_ip" id="ftp_name_ip" value="<?php echo appyn_options( 'ftp_name_ip', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Puerto', 'appyn' ); ?></h3></td>
						<td><input type="text" name="ftp_port" id="ftp_port" value="<?php echo appyn_options( 'ftp_port', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Usuario', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="ftp_username" id="ftp_username" value="<?php echo appyn_options( 'ftp_username', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Contraseña', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="ftp_password" id="ftp_password" value="<?php echo appyn_options( 'ftp_password', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Directorio', 'appyn' ); ?>*</h3>
							<div class="descr"><?php echo __( 'Ubica la ruta exacta donde se guardarán los archivos', 'appyn' ); ?></div></td>
						<td><input type="text" name="ftp_directory" id="ftp_directory" value="<?php echo appyn_options( 'ftp_directory', true ); ?>" class="widefat"><br>
						<div style="font-style:italic">public_html<br>
						/website.com/<br>
						/web/website.com/public_html/</div></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'URL', 'appyn' ); ?>*</h3>
							<div class="descr"><?php echo __( 'Coloca la dirección con la que se accederá a tus archivos', 'appyn' ); ?></div></td>
						<td><input type="text" name="ftp_url" id="ftp_url" value="<?php echo appyn_options( 'ftp_url', true ); ?>" class="widefat" placeholder="https://website.com"></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<?php
							echo '<p id="alert_test_ftp" style="display:none; font-weight:bold;">'. __( 'Recuerda guardar los cambios para probar la conexión', 'appyn' ). '</p>';
							echo '<a class="button" id="button_ftp_connect" href="'. admin_url(). 'admin.php?page=appyn_panel&action=ftp_connect" target="_blank">'.__( 'Probar conexión FTP', 'appyn' ).'</a>';
							?>	
						</td>
					</tr>
				</table><br>

				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/genera-una-api-key-en-1fichier/' : 'https://themespixel.net/en/generate-an-api-key-in-1fichier/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( '1Fichier', 'appyn' ); ?></h2>	
						<?php echo __( 'Genera una API Key en 1Fichier', 'appyn' ); ?>. <?php echo sprintf( __( 'Siga el %s.', 'appyn' ), '<a href="'.$url.'" target="_blank">'.__( 'tutorial', 'appyn' ). '</a>' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'API Key', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="1fichier_apikey" id="1fichier_apikey" value="<?php echo appyn_options( '1fichier_apikey', true ); ?>" class="widefat"></td>
					</tr>
				</table><br>

				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/crear-app-de-onedrive-para-almacenamiento-de-archivos/' : 'https://themespixel.net/en/create-onedrive-app-for-file-storage/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( 'OneDrive', 'appyn' ); ?></h2>	
						<?php echo __( 'Crea una app en OneDrive', 'appyn' ); ?>. <?php echo sprintf( __( 'Siga el %s.', 'appyn' ), '<a href="'.$url.'" target="_blank">'.__( 'tutorial', 'appyn' ). '</a>' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'ID de cliente', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="onedrive_client_id" id="onedrive_client_id" value="<?php echo appyn_options( 'onedrive_client_id', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Secreto del cliente', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="onedrive_client_secret" id="onedrive_client_secret" value="<?php echo appyn_options( 'onedrive_client_secret', true ); ?>" class="widefat"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Carpeta', 'appyn' ); ?></h3></td>
						<td><input type="text" name="onedrive_folder" id="onedrive_folder" value="<?php echo appyn_options( 'onedrive_folder', true ); ?>" class="widefat"></td>
					</tr>
					<?php 
					$odat = appyn_options( 'onedrive_access_token' );
					?>
					<tr>
						<td><?php 
							if( $odat ) {
								echo '<a href="'.admin_url('admin.php?page=appyn_panel&action=new_onedrive_info#edcgp').'">'.__( 'Conectar nueva cuenta', 'appyn'). '</a>'.px_label_help( __('Haga clic aquí solo si ha añadido un nuevo ID de cliente y Secreto. Aparecerá nuevamente el botón de conectar que deberá pulsar.', 'appyn' ) );
							}
							?>
						</td>
						<td>
							<?php
							if( $odat && appyn_options( 'onedrive_client_secret', true )  && appyn_options( 'onedrive_client_id', true ) ) {
								echo '<strong style="color:#50b250"><i class="fa fa-check"></i> '.__( 'Conectado a OneDrive', 'appyn').'</strong>';
							} else {
								echo '<p id="alert_test_onedrive" style="display:none; font-weight:bold;">'. __( 'Recuerda guardar los cambios antes de realizar la conexión', 'appyn' ). '</p>';
								echo '<a class="button" id="button_onedrive_connect" href="'. admin_url(). 'admin.php?page=appyn_panel&action=onedrive_connect">'.__( 'Conectar a OneDrive', 'appyn' ).'</a>';
							}
							?>	
						</td>
					</tr>
				</table><br>

				<?php
				$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/genera-un-token-en-uptobox-para-la-subida-de-archivos/' : 'https://themespixel.net/en/generate-a-token-in-uptobox-for-uploading-files/';
				?>
				<table class="table-main">
					<tr>
						<td colspan="2">
						<h2 style="padding:0;"><?php echo __( 'UptoBox', 'appyn' ); ?></h2>
						<?php echo __( 'Crea tu cuenta y obtén el token en UptoBox', 'appyn' ); ?>. <?php echo sprintf( __( 'Siga el %s.', 'appyn' ), '<a href="'.$url.'" target="_blank">'.__( 'tutorial', 'appyn' ). '</a>' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Token', 'appyn' ); ?>*</h3></td>
						<td><input type="text" name="uptobox_token" id="uptobox_token" value="<?php echo appyn_options( 'uptobox_token', true ); ?>" class="widefat"></td>
					</tr>
				</table>
			</div>

            <div class="section" data-section="single">
				<h2><?php echo __( 'Single', 'appyn' ); ?></h2>
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Leer más', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __("Muestra todo el contenido del post o déjalo con el botón 'leer más'", 'appyn'); ?>.</div>
						</td>
						<td><?php $readmore_single = get_option( 'appyn_readmore_single' ); ?>
							<p><input type="radio" name="readmore_single" value="0" id="readmore_default" <?php checked( $readmore_single, 0, true); ?> checked> 
								<label for="readmore_default"><?php echo __( 'Leer más (defecto)', 'appyn' ); ?></label></p>
							<p><input type="radio" name="readmore_single" value="1" id="readmore_all" <?php checked( $readmore_single, 1, true); ?>> 
								<label for="readmore_all"><?php echo __( 'Mostrar todo', 'appyn' ); ?></label></p></td>
					</tr>
					<?php $download_links = get_option( 'appyn_download_links' ); ?>
					<tr>
						<td><h3><?php echo __( 'Enlaces de descarga', 'appyn' ); ?></h3>
							<div class="descr"><a href="https://themespixel.net/en/docs/appyn/panel/#doc3" target="_blank"><?php echo __( '¿Cómo funciona esto?', 'appyn' ); ?></a></div>	
						</td>
						<td><p><label><input type="radio" name="download_links" value="0" <?php checked( $download_links, '', true); checked( $download_links, 0, true); ?>> <?php echo __( 'Normal', 'appyn' ); ?></label>
							</p>
                        	<p><label><input type="radio" name="download_links" value="1" <?php checked( $download_links, 1, true); ?>> <?php echo __( 'Página interna', 'appyn' ); ?></label></p>
                        	<p><label><input type="radio" name="download_links" value="2" <?php checked( $download_links, 2, true); ?>> <?php echo __( 'Página interna con doble paso', 'appyn' ); ?></label></p>
                        	<p><label><input type="radio" name="download_links" value="3" <?php checked( $download_links, 3, true); ?>> <?php echo __( 'Página única', 'appyn' ); ?></label></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Enlaces de descarga', 'appyn' ); ?> (<?php echo __( 'Permalinks' ); ?>)</h3></td>
						<td>
							<?php
							$dlp = appyn_options( 'download_links_permalinks' );
							?>
							<p><label><input type="radio" name="download_links_permalinks" value="0" <?php checked( $dlp, '', true); checked( $dlp, 0, true); ?>> web.com/post/?download=links <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>
							<p><label><input type="radio" name="download_links_permalinks" value="1" <?php checked( $dlp, '', true); checked( $dlp, 1, true); ?>> web.com/post/download/</label></p>

							<p><i><?php echo sprintf( __( 'Cuando realiza un cambio de opción debe refrescar los enlaces permanentes. Para ello vaya a %s y guarde los cambios. Recuerde que debe estar seleccionada la estructura "%s"', 'appyn'), '<a href="'.admin_url('options-permalink.php').'">'.__( 'Permalinks' ).'</a>', '<strong>'.__( 'Post name' ).'</strong>' ); ?></i></p>
						</td>
					</tr>
					
					<tr>
						<td><h3><?php echo __( 'Completar reCaptcha', 'appyn' ); ?><?php echo px_label_help( sprintf( __( 'Tiene que completar los códigos de reCaptcha v2 en los campos requeridos en %s', 'appyn' ), '<b>'.__( 'Opciones generales', 'appyn' ).'</b>' ) ); ?></h3>
							<div class="descr"><?php echo __( 'Opción que solicitará al usuario completar el reCaptcha para ver los enlaces de descarga.', 'appyn' ); ?></div></td>
						<td>
							<?php
							$asdr = appyn_options( 'active_show_dl_recaptcha' );
							?>
							<p><label class="switch"><input type="checkbox" name="active_show_dl_recaptcha" value="1" <?php checked( $asdr, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>

					<?php $download_links_d = get_option( 'appyn_download_links_design' ); ?>
					<tr>
						<td><h3><?php echo __( 'Estilo de los enlaces de descarga', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge el estilo de los enlaces de descarga', 'appyn' ); ?></div>	
						</td>
						<td>
							<p><label><input type="radio" name="download_links_design" value="0" <?php checked( $download_links_d, '', true); checked( $download_links_d, 0, true); ?>> <?php echo __( 'En fila', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="download_links_design" value="1" <?php checked( $download_links_d, 1, true); checked( $download_links_d, 1, true); ?>> <?php echo __( 'En fila centrado', 'appyn' ); ?></label></p>

                        	<p><label><input type="radio" name="download_links_design" value="2" <?php checked( $download_links_d, 2, true); ?>> <?php echo __( 'En columna', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<?php 
					$download_links_vb = get_option( 'appyn_download_links_verified_by' );
					$download_links_vbp = get_option( 'appyn_download_links_verified_by_p' ); 
					?>
					<tr>
						<td><h3><?php echo __( 'Verificado por...', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Texto alternativo que se mostrará debajo de los enlaces de descarga', 'appyn' ); ?></div>	
						</td>
						<td>
							<p><input type="text" name="download_links_verified_by" value="<?php echo $download_links_vb; ?>" class="widefat" placeholder="<?php echo 'Verified by Sitename Protect'; ?>"></p>
							<p><label><input type="checkbox" name="download_links_verified_by_p" value="1"<?php checked( $download_links_vbp, 1 ); ?>> <?php echo __( 'Centrado', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<?php 
					$dltbu = get_option( 'appyn_download_links_telegram_button_url' ); 
					$dltbt = get_option( 'appyn_download_links_telegram_button_text' ); 
					?>
					<tr>
						<td><h3><?php echo __( 'Únete a nuestro grupo de telegram', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Botón para unirse a Telegram que aparecerá debajo de "Verificado por..."', 'appyn' ); ?></div>	
						</td>
						<td>
							<p><input type="text" name="download_links_telegram_button_url" value="<?php echo $dltbu; ?>" class="widefat" placeholder="<?php echo 'https://t.me/xxxxxxxxxxx'; ?>"></p>
							<p><input type="text" name="download_links_telegram_button_text" value="<?php echo $dltbt; ?>" class="widefat" placeholder="<?php echo __( 'ÚNETE A NUESTRO GRUPO DE TELEGRAM', 'appyn' ); ?>"></p>
						</td>
					</tr>
					<?php $redirect_timer = appyn_options( 'redirect_timer' ); ?>
					<tr>
						<td><h3><?php echo __( 'Temporizador de redirección', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Temporizador para la redirección del enlace de descarga', 'appyn' ); ?>.</div>	
						</td>
						<td>
							<p><label><input type="number" name="redirect_timer" min="0" max="999" value="<?php echo (isset($redirect_timer)) ? $redirect_timer : 5; ?>" class="input_number"> <?php echo __( 'segundos', 'appyn' ); ?></label></p>
							<p>
								<a href="https://demo.themespixel.net/appyn/lords-mobile-guerra-de-reinos-batalla-mmo-rpg/?download=redirect" target="_blank"><?php echo __( 'Ejemplo', 'appyn' ); ?> 1</a>
							</p>
						</td>
					</tr>
					<?php $download_timer = appyn_options( 'download_timer' ); ?>
					<tr>
						<td><h3><?php echo __( 'Temporizador de descarga', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Temporizador para mostrar los enlaces de descarga', 'appyn' ); ?>.</div>		
						</td>
						<td>
							<p><label><input type="number" name="download_timer" min="0" max="60" value="<?php echo (isset($download_timer)) ? $download_timer : 5; ?>" class="input_number"> <?php echo __( 'segundos', 'appyn' ); ?></label></p>
							<p>
								<a href="https://demo.themespixel.net/appyn/facebook/?download=links" target="_blank"><?php echo __( 'Ejemplo', 'appyn' ); ?> 1</a>
							</p>
						</td>
					</tr>
					<?php $design_timer = appyn_options( 'design_timer' ); ?>
					<tr>
						<td><h3><?php echo __( 'Diseño de temporizador', 'appyn' ); ?></h3></td>
						<td>
							<div><label><input type="radio" name="design_timer" value="0" <?php checked( $design_timer, '', true); checked( $design_timer, 0, true); ?>> <?php echo __( 'Tipo 1', 'appyn' ); ?> <?php echo __( '(Por defecto)', 'appyn' ); ?></label><?php echo px_label_help( '<img src="'.get_template_directory_uri().'/admin/assets/images/dl-1.gif" height="60">', true ); ?></div><br>
                        	<div><label><input type="radio" name="design_timer" value="1" <?php checked( $design_timer, 1, true); ?>> <?php echo __( 'Tipo 2', 'appyn' ); ?></label><?php echo px_label_help( '<img src="'.get_template_directory_uri().'/admin/assets/images/dl-2.gif" height="38">', true ); ?></div>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Ordenar cajas', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Modifica el orden de las cajas a tu gusto', 'appyn' ); ?>.</div>	
						</td>
						<td><?php
							$oc_default = array( 
								'descripcion' => __( 'Descripción', 'appyn' ), 
								'ads_single_center' => __( 'ADS Single Center', 'appyn' ),
								'novedades' => __( 'Novedades', 'appyn' ), 
								'versiones' => __( 'Versiones', 'appyn' ), 
								'video' => __( 'Video', 'appyn' ), 
								'imagenes' => __( 'Imágenes', 'appyn' ), 
								'enlaces_descarga' => __( 'Enlaces de descarga', 'appyn' ), 
								'apps_relacionadas' => __( 'Apps relacionadas', 'appyn' ), 
								'apps_desarrollador' => __( 'Apps desarrollador', 'appyn' ),
								'cajas_personalizadas' => __( 'Cajas personalizadas', 'appyn' ), 
								'tags' => __( 'Etiquetas', 'appyn' ),
							);
							$pcb = get_option( 'permanent_custom_boxes' );
							if( $pcb && is_array($pcb) ) {
								foreach( $pcb as $k => $p ) {
									$oc_default['permanent_custom_box_'.$k] = sprintf( __( 'Caja permanente %s', 'appyn' ), '#'.($k+1) );
								}
							}
							
							$oc_default['comentarios'] = __( 'Comentarios', 'appyn' );
							$oc = get_option( 'appyn_orden_cajas', $oc_default );
							$oc = array_merge($oc, $oc_default);
							$cvn = get_option( 'appyn_orden_cajas_disabled', array() ); 
							?>
							<ul class="px-orden-cajas">
							<?php
							foreach( $oc as $k => $o ) {
								if( array_key_exists( $k, $oc_default ) ) {
							?>
								<li><label><input type="checkbox" name="orden_cajas[<?php echo $k; ?>]" value="<?php echo $oc[$k]; ?>" checked style="display:none;"><input type="checkbox" name="orden_cajas_disabled[]" value="<?php echo $k; ?>" <?php echo (in_array($k, $cvn) ) ? 'checked' : ''; ?>> <span><?php echo $oc[$k]; ?></span></label></li>
							<?php }
							} ?>
							</ul></td>
					</tr>
					
					<tr>
 						<td><h3><?php echo __( 'Cajas a quitar en la página de (descarga / redirección) interna ', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Marca las cajas que no quieres que aparezca en la página de descarga o redirección interna.', 'appyn' ); ?></div>
						</td>
						<td><?php 
							$cvn = get_option( 'appyn_pagina_interna_no_cajas', array() ); 
							foreach( $oc as $k => $o ) {
								if( array_key_exists( $k, $oc_default ) ) {
									echo '<p><label><input type="checkbox" name="pagina_interna_no_cajas[]" value="'.$k.'" '. ((in_array($k, $cvn) ) ? 'checked' : '') .'> <span>'. __( $o, 'appyn' ) .'</span></label></p>';
								}
							}
							?>
						</td>
					</tr>
					
					<tr>
 						<td><h3><?php echo __( 'Ocultar botones sociales', 'appyn' ); ?></h3>
							<div class="descr"></div>
						</td>
						<td>
							<?php
							$shsb = appyn_options( 'single_hide_social_buttons' );
							?>
							<p><label class="switch"><input type="checkbox" name="single_hide_social_buttons" value="1" <?php checked( $shsb, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					
					<tr>
 						<td><h3><?php echo __( 'Botón Telegram', 'appyn' ); ?><?php echo px_label_help( '<img src="'.get_template_directory_uri().'/admin/assets/images/telegram-button.png" height="120">', true ); ?></h3><div class="descr"><?php echo __( 'Mostrar botón Telegram debajo del botón de descarga en el post.', 'appyn' ); ?></div>
						</td>
						<td>
							<?php
							$sstb = appyn_options( 'single_show_telegram_button' );
							?>
							<p><label class="switch"><input type="checkbox" name="single_show_telegram_button" value="1" <?php checked( $sstb, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
				</table>
					
			</div>
            <div class="section" data-section="historial_versiones">
				<h2><?php echo __( 'Historial de versiones', 'appyn' ); ?></h2>
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Cantidad en la entrada', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoge la cantidad de versiones que aparecerá en la caja de la entrada', 'appyn' ); ?>.</div>
						</td>
						<td><p><input type="number" name="versiones_cantidad_post" size="2" value="<?php $cvp = get_option( 'appyn_versiones_cantidad_post' ); echo ($cvp) ? $cvp : 5;  ?>" min="1" max="100" class="input_number" required> <?php echo __( 'Entradas', 'appyn' ); ?></p></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Cajas a quitar de la entrada de versión antigua', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Marca las cajas que no quieres que aparezca en las entradas de historial de versiones.', 'appyn' ); ?></div>
						</td>
						<td><?php 
							$cvn = get_option( 'appyn_versiones_no_cajas', array() ); 
							foreach( $oc as $k => $o ) {
								if( array_key_exists( $k, $oc_default ) ) {
									if( $k == 'versiones' ) continue;
									echo '<p><label><input type="checkbox" name="versiones_no_cajas[]" value="'.$k.'" '. ((in_array($k, $cvn) ) ? 'checked' : '') .'> <span>'. __( $o, 'appyn' ) .'</span></label></p>';
								}
							}
							?>
							</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Mostrar enlace directo de descarga', 'appyn' ); ?> <?php echo px_label_help( __( 'Esta opción permitirá mostrar el enlace de descarga de la versión antigua sin necesidad que el usuario acceda al post a descargar. Mostrará el primer enlace de descarga. En caso no exista, el enlace será hacia el post.', 'appyn' ) ); ?></h3>
							<div class="descr"><?php echo __( 'Permitir ir al primer enlace de descarga directamente', 'appyn' ); ?></div></td>
						<td><?php
							$vdld = appyn_options( 'version_download_link_direct' );
							?>
							<p><label class="switch"><input type="checkbox" name="version_download_link_direct" value="1" <?php checked( $vdld, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
				</table>
			</div>

            <div class="section" data-section="sidebar">
				<h2>Sidebar</h2>
				
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Activo', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Activar o desactivar el sidebar.', 'appyn' ); ?></div>
						</td>
						<td>
							<?php 
							$s = appyn_options( 'sidebar_active' ); ?>
							<p><label class="switch"><input type="checkbox" name="sidebar_active" value="0" <?php checked( $s, "0" ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Posición del sidebar', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoja la ubicación del sidebar.', 'appyn' ); ?></div>
						</td>
						<td>
							<?php $sidebar_ubicacion = appyn_options( 'sidebar_ubicacion' ); ?>
							<p><label><input type="radio" name="sidebar_ubicacion" value="derecha" <?php checked( $sidebar_ubicacion, "derecha" ); ?> <?php checked( $sidebar_ubicacion, "0" ); ?>> <?php echo __( 'Derecha', 'appyn' ); ?></label></p>

							<p><label><input type="radio" name="sidebar_ubicacion" value="izquierda" <?php checked( $sidebar_ubicacion, "izquierda" ); ?>> <?php echo __( 'Izquierda', 'appyn' ); ?></label></p>
						</td>
					</tr>
				</table>
            </div>

            <div class="section" data-section="color">
				<h2><?php echo __( 'Colores', 'appyn' ); ?></h2>
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Estilo de color', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoja el tipo de tono que tendrá el tema.', 'appyn' ); ?></div>							
						</td>
						<td><?php $color_theme = appyn_options( 'color_theme' ); ?>					
							<p><label><input type="radio" name="color_theme" value="claro" <?php checked( $color_theme, "claro" ); ?> <?php checked( $color_theme, "0" ); ?>> <?php echo __( 'Claro', 'appyn' ); ?></label></p>
							
							<p><label><input type="radio" name="color_theme" value="oscuro" <?php checked( $color_theme, "oscuro" ); ?>> <?php echo __( 'Oscuro', 'appyn' ); ?></label></p>
							
							<p><label><input type="radio" name="color_theme" value="navegador" <?php checked( $color_theme, "navegador" ); ?>> <?php echo __( 'Navegador', 'appyn' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Que el usuario escoja color', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Permitir que el usuario seleccione el color del tema.', 'appyn' ); ?></div>							
						</td>
						<td>
							<?php $c = appyn_options( 'color_theme_user_select' ); ?>
							<p><label class="switch"><input type="checkbox" name="color_theme_user_select" value="1" <?php checked( $c, 1 ); ?>><span class="swisr"></span></label></p>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Color principal', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Escoja el color principal del tema.', 'appyn' ); ?></div>				
						</td>
						<td><?php $color_theme_principal = appyn_options( 'color_theme_principal', '#1bbc9b' );
						echo '<input name="color_theme_principal" class="colorpicker" value="'.$color_theme_principal.'" data-default-color="#1bbc9b">'; ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Color botón de descarga', 'appyn' ); ?></h3>
						</td>
						<td><?php $color_download_button = appyn_options( 'color_download_button', '#1bbc9b' );
						echo '<input name="color_download_button" class="colorpicker" value="'.$color_download_button.'" data-default-color="#1bbc9b">'; ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Color cinta nuevo', 'appyn' ); ?></h3>
						</td>
						<td><?php $color_new_ribbon = appyn_options( 'color_new_ribbon', '#d22222' );
						echo '<input name="color_new_ribbon" class="colorpicker" value="'.$color_new_ribbon.'" data-default-color="#d22222">'; ?></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Color cinta actualizado', 'appyn' ); ?></h3>
						</td>
						<td><?php $color_update_ribbon = appyn_options( 'color_update_ribbon', '#19b934' );
						echo '<input name="color_update_ribbon" class="colorpicker" value="'.$color_update_ribbon.'" data-default-color="#19b934">'; ?></td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Color estrellas', 'appyn' ); ?></h3>
						</td>
						<td><?php $color_stars = appyn_options( 'color_stars', '#f9bd00' );
						echo '<input name="color_stars" class="colorpicker" value="'.$color_stars.'" data-default-color="#f9bd00">'; ?></td>
					</tr>

					<tr>
						<td><h3><?php echo __( 'Color etiqueta MOD', 'appyn' ); ?></h3>
						</td>
						<td><?php $color_tag_mod = appyn_options( 'color_tag_mod', '#20a400' );
						echo '<input name="color_tag_mod" class="colorpicker" value="'.$color_tag_mod.'" data-default-color="#20a400">'; ?></td>
					</tr>

					



				</table>
			</div>
			
			
            <div class="section" data-section="blog">
				<h2>Blog</h2>
				
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Sección Blog en Home', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloque la cantidad de posts que debe aparecer en el home.', 'appyn' ); ?></div>							
						</td>
						<td><?php
							$blog_posts_home_limite = get_option( 'appyn_blog_posts_home_limite' );
							$blog_posts_home_limite = (empty($blog_posts_home_limite)) ? '4' : $blog_posts_home_limite;
							echo '<input type="number" name="blog_posts_home_limite" size="2" value="'.$blog_posts_home_limite.'" class="input_number" required> '.__( 'Entradas', 'appyn' );	
							?>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Blog Page', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloque la cantidad de posts que debe aparecer en la página blog', 'appyn' ); ?></div>
						</td>
						<td><?php
							$blog_posts_limite = get_option( 'appyn_blog_posts_limite' );
							$blog_posts_limite = (empty($blog_posts_limite)) ? '10' : $blog_posts_limite;
							echo '<input type="number" name="blog_posts_limite" size="2" value="'.$blog_posts_limite.'" class="input_number" required> '.__( 'Entradas', 'appyn' );					
							?>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Blog Sidebar', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Activa esta opción para que puedas agregar widgets al sidebar exclusivo del área de blog', 'appyn' ); ?></div></td>
						<td>
							<?php
							$bsd = appyn_options( 'blog_sidebar' );
							?>
							<p><label class="switch"><input type="checkbox" name="blog_sidebar" value="1" <?php checked( $bsd, 1 ); ?>><span class="swisr"></span></label></p>
							<p><a href="<?php bloginfo('url'); ?>/wp-admin/widgets.php"><?php echo __( 'Agregar Widgets', 'appyn' ); ?></a></p>
						</td>
					</tr>
				</table>
            </div>
			
			<div class="section" data-section="amp">
				<h2><?php echo __( 'AMP', 'appyn' ); ?></h2>
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'AMP', 'appyn' ); ?></h3>
							<div class="descr">Accelerated Mobile Pages.<br>
							<a href="https://support.google.com/google-ads/answer/7496737" target="_blank"><?php echo __( 'Más información', 'appyn' ); ?></a></div></td>
						<td>
							<?php
							$appyn_amp = appyn_options( 'amp' );
							?>
							<label class="switch"><input type="checkbox" name="amp" value="1" <?php checked( $appyn_amp, 1 ); ?>><span class="swisr"></span></label>
						</td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Google Analytics', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca el ID de Analytics', 'appyn' ); ?></div></td>
						<td><input type="text" name="analytics_amp" class="regular-text" value="<?php echo appyn_options( 'analytics_amp', true ); ?>" placeholder="UA-XXXXXXXX-XX"></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Códigos header', 'appyn' ); ?></h3>
							<div class="descr"><?php echo sprintf( __( 'Coloca los códigos en el header (dentro de la etiqueta %s)', 'appyn' ), '&lt;head&gt;&lt;/head&gt;' ); ?></div></td>
						<td><textarea spellcheck="false" name="header_codigos_amp" class="widefat" rows="8"><?php echo stripslashes(get_option( 'appyn_header_codigos_amp' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Códigos body', 'appyn' ); ?></h3>
							<div class="descr"><?php echo sprintf( __( 'Coloca códigos debajo de la etiqueta %s', 'appyn' ), '&lt;body&gt;' ); ?></div></td>
						<td><textarea spellcheck="false" name="body_codigos_amp" class="widefat" rows="8"><?php echo stripslashes(get_option( 'appyn_body_codigos_amp' )); ?></textarea></td>
					</tr>
						<td><h3>ADS Header</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la versión AMP', 'appyn' ); ?>.</div>
						</td>
						<td><textarea spellcheck="false" name="ads_header_amp" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_header_amp' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Home, Page, Search, Categories, Tags, etc.</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para esas secciones en la versión AMP.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_home_amp" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_home_amp' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Single Top</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page en la versión AMP.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_top_amp" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_top_amp' )); ?></textarea></td>
					</tr>
					
					<tr>
						<td><h3>ADS Single Center</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page en la parte central en la versión AMP.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_center_amp" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_center_amp' )); ?></textarea></td>
					</tr>
					
					<tr>
						<td><h3>ADS Download 1<br>
							<?php echo __( '(Página interna)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte superior de los enlaces de descarga en la página interna de la versión AMP.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_1_amp" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_1_amp' )); ?></textarea></td>
					</tr>
				</table>
			</div>

            <div class="section" data-section="ads">
				<h2><?php echo __( 'Anuncios', 'appyn' ); ?></h2>
				
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Texto encima de cada anuncio (Opcional)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca un texto encima de cada anuncio.', 'appyn' ); ?></div>
						</td>
						<td><input type="text" name="ads_text_above" class="regular-text" value="<?php echo stripslashes(get_option( 'appyn_ads_text_above' )); ?>"></td>
					</tr>
					<tr>
						<td><h3>ADS Header</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio debajo del header.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_header" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_header' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Header [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la versión móvil', 'appyn' ); ?>.</div>
						</td>
						<td><textarea spellcheck="false" name="ads_header_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_header_movil' )); ?></textarea></td>
					</tr>
						
					<tr>
						<td><h3>ADS Home, Page, Search, Categories, Tags, etc.</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para esas secciones.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_home" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_home' )); ?></textarea></td>
					</tr>
					
					<tr>
						<td><h3>ADS Home, Page, Search, Categories, Tags, etc. [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para esas secciones en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_home_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_home_movil' )); ?></textarea></td>
					</tr>
					
					<tr>
						<td><h3>ADS Single Top</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_top" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_top' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Single Top [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_top_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_top_movil' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Single Center</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page en la parte central.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_center" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_center' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3>ADS Single Center [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para el single y page en la parte central en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_single_center_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_single_center_movil' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 1<br>
							<?php echo __( '(Página interna)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte superior de los enlaces de descarga en la página interna.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_1" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_1' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 2<br>
							<?php echo __( '(Página interna)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página interna.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_2" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_2' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 1<br>
							<?php echo __( '(Página interna)', 'appyn' ); ?> [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte superior de los enlaces de descarga en la página interna en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_1_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_1_movil' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 2<br>
							<?php echo __( '(Página interna)', 'appyn' ); ?> [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página interna en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_2_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_2_movil' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 1<br>
							<?php echo __( '(Página única)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte superior de los enlaces de descarga en la página única.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_1" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_1' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 2<br>
							<?php echo __( '(Página única)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página única.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_2" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_2' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 3<br>
							<?php echo __( '(Página única)', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página única.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_3" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_3' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 1<br>
							<?php echo __( '(Página única)', 'appyn' ); ?> [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte superior de los enlaces de descarga en la página única en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_1_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_1_movil' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 2<br>
							<?php echo __( '(Página única)', 'appyn' ); ?> [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página única en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_2_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_2_movil' )); ?></textarea></td>
					</tr>

					<tr>
						<td><h3>ADS Download 3<br>
							<?php echo __( '(Página única)', 'appyn' ); ?> [<?php echo __( 'Móvil', 'appyn' ); ?>]</h3>
							<div class="descr"><?php echo __( 'Añade el código de anuncio para la parte inferior de los enlaces de descarga en la página única en la versión móvil.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="ads_download_u_3_movil" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_ads_download_u_3_movil' )); ?></textarea></td>
					</tr>
				</table>
            </div>

			<?php
			$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/acortadores-de-enlaces-para-generar-ganancias-extras/' : 'https://themespixel.net/en/link-shorteners-to-generate-extra-earnings/';
			?>
            <div class="section" data-section="shorteners">
				<h2><?php echo __( 'Acortadores', 'appyn' ); ?></h2>
				
				<table class="table-main">
					<tr>
						<td colspan="100%"><p><?php echo sprintf( __( 'Coloca el API Key del acortador que prefieras. %s', 'appyn' ), '<a href="'.$url.'" target="_blank">'.__( 'Leer tutorial', 'appyn' ).'</a>' ); ?></p></td>
					</tr>
					<tr>
						<td><h3>Ouo.io <a href="https://ouo.io" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></h3>
							<div class="descr"></div>
						</td>
						<td><input type="text" name="shortlink_ouo" class="regular-text" value="<?php echo appyn_options( 'shortlink_ouo', true ); ?>" placeholder="API Key..."></td>
					</tr>
					<tr>
						<td><h3>ShrinkEarn.com <a href="https://shrinkearn.com" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></h3>
							<div class="descr"></div>
						</td>
						<td><input type="text" name="shortlink_shrinkearn" class="regular-text" value="<?php echo appyn_options( 'shortlink_shrinkearn', true ); ?>" placeholder="API Key..."></td>
					</tr>
					<tr>
						<td><h3>Shorte.st <a href="https://shorte.st" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></h3>
							<div class="descr"></div>
						</td>
						<td><input type="text" name="shortlink_shorte" class="regular-text" value="<?php echo appyn_options( 'shortlink_shorte', true ); ?>" placeholder="API Key..."></td>
					</tr>
					<tr>
						<td><h3>ClicksFly.com <a href="https://clicksfly.com" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></h3>
							<div class="descr"></div>
						</td>
						<td><input type="text" name="shortlink_clicksfly" class="regular-text" value="<?php echo appyn_options( 'shortlink_clicksfly', true ); ?>" placeholder="API Key..."></td>
					</tr>
					<tr>
						<td><h3>Oke.io <a href="https://oke.io" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></h3>
							<div class="descr"></div>
						</td>
						<td><input type="text" name="shortlink_oke" class="regular-text" value="<?php echo appyn_options( 'shortlink_oke', true ); ?>" placeholder="API Key..."></td>
					</tr>
				</table>
			</div>
            
            <div class="section" data-section="footer">
				<h2>Footer</h2>
				
				<table class="table-main">
					<tr>
						<td><h3><?php echo __( 'Texto Footer', 'appyn' ); ?></h3>
							<div class="descr"><?php echo __( 'Coloca cualquier texto en el footer. Permite HTML.', 'appyn' ); ?></div>
						</td>
						<td><textarea spellcheck="false" name="footer_texto" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_footer_texto' )); ?></textarea></td>
					</tr>
					<tr>
						<td><h3><?php echo __( 'Códigos Footer', 'appyn' ); ?></h3></td>
						<td><textarea spellcheck="false" name="footer_codigos" class="widefat" rows="7"><?php echo stripslashes(get_option( 'appyn_footer_codigos' )); ?></textarea></td>
					</tr>
				</table>
    		</div>
            
            <div class="section" data-section="info">
				<h2><?php echo __( 'Info', 'appyn'); ?></h2>

				<?php 
				$auf = ini_get('allow_url_fopen'); 
				$met = ini_get('max_execution_time');
				$mit = ini_get('max_input_time'); 
				$mli = ini_get('memory_limit'); 
				$pms = ini_get('post_max_size'); 
				$umf = ini_get('upload_max_filesize'); 

				function sss($mli, $d) {
					
					if( $mli == -1 ) {
						return 999999;
					}
					if (preg_match('/^(\d+)(.)$/', $mli, $matches)) {
						if ($matches[2] == 'G') {
							$ml = $matches[1] * 1024 * 1024 * 1024;
						}
						else if ($matches[2] == 'M') {
							$ml = $matches[1] * 1024 * 1024;
						} else if ($matches[2] == 'K') {
							$ml = $matches[1] * 1024;
						}
					}

					return ($ml >= $d * 1024 * 1024);
				}
				?>
				
				<table class="table-main">
					<tr>
						<?php 
						$url = ( lang_wp() == "es" ) ? 'https://themespixel.net/aumentar-los-valores-de-configuracion-de-php/' : 'https://themespixel.net/en/increase-php-configuration-values/';
						?>
						<td colspan="100%"><?php echo __( 'Estos valores son importantes para poder descargar y/o subir el APK de gran peso a su servidor o servidores externos.', 'appyn' ); ?> <a href="<?php echo $url; ?>" target="_blank"><?php echo __( '¿Cómo cambiar estos valores?', 'appyn' ); ?></a>
						</td>
					</tr>		
					<tr>
						<td>allow_url_fopen</td>
						<td><?php echo ($auf == 1) ? '<b style="color:#35c835;">'.__( 'Activado', 'appyn' ).'</b>' : '<b style="color:red;">'.__( 'Desactivado', 'appyn' ).'</b>'; ?></td>
					</tr>
					<tr>
						<td>max_execution_time</td>
						<td><?php echo ($met >= 300 || $met <= 0) ? '<b style="color:#35c835;">'.( ( $met <= 0 ) ? 'No limit' : $met ).'</b>' : '<b style="color:red;">'.$met.'</b> <em>/ '.__( 'Se recomienda mayor o igual a 300', 'appyn' ).'</em>'; ?></td>
					</tr>
					<tr>
						<td>max_input_time</td>
						<td><?php echo ($mit >= 300) ? '<b style="color:#35c835;">'.$mit.'</b>' : '<b style="color:red;">'.$mit.'</b> <em>/ '.__( 'Se recomienda mayor o igual a 300', 'appyn' ).'</em>'; ?></td>
					</tr>
					<tr>
						<td>memory_limit</td>
						<td><?php echo (sss($mli, 3000)) ? '<b style="color:#35c835;">'.( ( $mli <= 0 ) ? 'No limit' : $mli ).'</b>' : '<b style="color:red;">'.$mli.'</b> <em>/ '.__( 'Se recomienda mayor o igual a 4G', 'appyn' ).'</em>'; ?></td>
					</tr>
					<tr>
						<td>post_max_size</td>
						<td><?php echo (sss($pms, 3000)) ? '<b style="color:#35c835;">'.$pms.'</b>' : '<b style="color:red;">'.$pms.'</b> <em>/ '.__( 'Se recomienda mayor o igual a 4G', 'appyn' ).'</em>'; ?></td>
					</tr>
					<tr>
						<td>upload_max_filesize</td>
						<td><?php echo (sss($umf, 3000)) ? '<b style="color:#35c835;">'.$umf.'</b>' : '<b style="color:red;">'.$umf.'</b> <em>/ '.__( 'Se recomienda mayor o igual a 4G', 'appyn' ).'</em>'; ?></td>
					</tr>
				</table>
    		</div>
    	</form>
    </div>
</div>
<?php }

add_action( 'wp_ajax_px_panel_admin', 'px_panel_admin' ); 
add_action( 'wp_ajax_nopriv_px_panel_admin', 'px_panel_admin' );

function px_panel_admin() {
	global $wpdb;

	$nonce = sanitize_text_field( $_POST['nonce'] );

    if ( ! wp_verify_nonce( $nonce, 'admin_panel_nonce' ) ) die ( '✋');

	if( ! isset( $_POST['serializedData'] ) ) exit;

	parse_str($_POST['serializedData'], $output);

	$options = array(
		'logo',
		'favicon', 
		'titulo_principal',
		'descripcion_principal',
		'image_header1',
		'image_header2',
		'image_header3',
		'image_header4',
		'image_header5',
		'social_single_color', 
		'social_facebook', 
		'social_twitter',
		'social_instagram', 
		'social_youtube', 
		'social_pinterest',
		'social_telegram',
		'mas_calificadas',
		'mas_calificadas_limite',
		'home_limite',
		'home_posts_orden',
		'categories_home',
		'categories_home_limite',
		'comments',
		'readmore_single',
		'header_codigos',
		'header_codigos_amp',
		'analytics_amp',
		'body_codigos_amp',
		'download_links',		
		'download_links_permalinks',				
		'blog_posts_home_limite',
		'blog_posts_limite', 
		'blog_sidebar',
		'ads_text_above',           
		'ads_header',
		'ads_header_movil',
		'ads_header_amp',
		'ads_home',
		'ads_home_movil',
		'ads_home_amp',
		'ads_single_top',
		'ads_single_top_movil',
		'ads_single_top_amp',
		'ads_single_center',
		'ads_single_center_movil',	
		'ads_single_center_amp',	
		'ads_download_1',				
		'ads_download_2',	
		'ads_download_1_movil',				
		'ads_download_2_movil',	
		'ads_download_1_amp',
		'ads_download_u_1',
		'ads_download_u_2',
		'ads_download_u_3',
		'ads_download_u_1_movil',
		'ads_download_u_2_movil',
		'ads_download_u_3_movil',
		'shortlink_shorte',
		'shortlink_shrinkearn',
		'shortlink_ouo',
		'shortlink_clicksfly',
		'shortlink_oke',
		'color_theme',
		'color_theme_user_select',
		'color_theme_principal',
		'color_download_button',
		'color_new_ribbon',
		'color_update_ribbon',
		'color_stars',
		'color_tag_mod',
		'sidebar_active',						
		'sidebar_ubicacion',			
		'footer_texto',
		'footer_codigos',
		'versiones_cantidad_post',
		'versiones_no_cajas',
		'orden_cajas',
		'orden_cajas_disabled',
		'version_download_link_direct',
		'recaptcha_secret',
		'recaptcha_site',
		'recaptcha_v2_secret',
		'recaptcha_v2_site',
		'lazy_loading',
		'versiones_mostrar_inicio',
		'versiones_mostrar_inicio_categorias',
		'versiones_mostrar_inicio_apps_mas_calificadas',
		'versiones_mostrar_buscador',
		'versiones_mostrar_tax_desarrollador',
		'versiones_mostrar_categorias',
		'versiones_mostrar_tags',
		'versiones_mostrar_widgets',
		'edcgp_post_status',
		'edcgp_create_category',
		'edcgp_create_tax_dev',
		'edcgp_extracted_images',
		'edcgp_sapk',
		'edcgp_sapk_server',
		'edcgp_sapk_shortlink',
		'edcgp_sapk_slug',
		'edcgp_rating',
		'edcgp_appd',
		'edcgp_update_post',
		'eidcgp_update_post',
		'edcgp_mc',
		'edcgp_eaa',
		'dedcgp_descamp_actualizar',
		'edcgp_roles',
		'download_timer',
		'redirect_timer',
		'design_timer',
		'pagina_interna_no_cajas',
		'single_hide_social_buttons',
		'single_show_telegram_button',
		'amp',
		'post_date',
		'post_date_type',
		'apps_related_type',
		'apikey',
		'home_hidden_posts',
		'home_hidden_blog',
		'home_sp_checked',
		'gdrive_client_id',
		'gdrive_client_secret',
		'gdrive_folder',
		'apps_info_download_apk',
		'apps_info_download_zip',
		'encrypt_links',
		'dropbox_app_key',
		'dropbox_app_secret',
		'request_email',
		'send_report_to_admin',
		'ftp_name_ip',
		'ftp_port',
		'ftp_username',
		'ftp_password',
		'ftp_directory',
		'ftp_url',
		'1fichier_apikey',
		'onedrive_client_id',
		'onedrive_client_secret',
		'onedrive_folder',
		'uptobox_token',
		'general_text_edit',
		'ribbon_update_post_modified',
		'download_links_design',
		'active_show_dl_recaptcha',
		'download_links_verified_by',
		'download_links_verified_by_p',
		'download_links_telegram_button_url',
		'download_links_telegram_button_text',
		'disabled_notif_apps_update',
		'sticky_header',
		'apps_per_row_pc',
		'apps_per_row_movil',
		'title_2_lines',
		'design_rounded',
		'automatic_results',
		'og_sidebar',
		'width_page',
		'view_apps',
		'bottom_menu',
		'search_google_active',
		'search_google_id',
	);

	foreach( $options as $key => $opt ) {

		if( ! in_array( $opt, $options) ) continue;

		if( $opt == "versiones_no_cajas" && empty( $output["versiones_no_cajas"] ) ) {
			delete_option( 'appyn_versiones_no_cajas' );
			continue;
		}
		if( $opt == "pagina_interna_no_cajas" && empty( $output["pagina_interna_no_cajas"] ) ) {
			delete_option( 'appyn_pagina_interna_no_cajas' );
			continue;
		}
		if( $opt == "categories_home" && empty( $output["categories_home"] ) ) {
			delete_option( 'appyn_categories_home' );
			continue;
		}
		if( $opt == "versiones_mostrar_inicio" ||
			$opt == "versiones_mostrar_inicio_categorias" ||
			$opt == "versiones_mostrar_inicio_apps_mas_calificadas" ||
			$opt == "versiones_mostrar_tax_desarrollador" ||
			$opt == "versiones_mostrar_buscador" ||
			$opt == "versiones_mostrar_categorias" ||
			$opt == "versiones_mostrar_tags" ||
			$opt == "versiones_mostrar_widgets" || 
			$opt == "edcgp_create_tax_dev" ||
			$opt == "edcgp_sapk" ||
			$opt == "edcgp_create_category" ||
			$opt == "sidebar_active" ||
			$opt == "automatic_results" ||
			$opt == "sticky_header"
			) {
			if( ! isset( $output[$opt] ) ) {
				update_option( 'appyn_'.$opt, 1 );
			} else {
				update_option( 'appyn_'.$opt, stripslashes_deep($output[$opt]) );
			}
			continue;
		}

		if( !isset($output[$opt]) ) {
			delete_option( 'appyn_'.$opt );
			continue;
		}

		update_option( 'appyn_'.$opt, @stripslashes_deep($output[$opt]) );

	}
	die();
}

function px_screen_option() {

	$option = 'per_page';
	$args   = [
		'label'   => __( 'Número de elementos por página', 'appyn' ).': ',
		'default' => 20,
		'option'  => 'apps_to_update_per_page'
	];

	add_screen_option( $option, $args );
}

add_filter( 'set-screen-option', 'px_set_screen', 10, 3 );

function px_set_screen( $status, $option, $value ) {
	return $value;
}