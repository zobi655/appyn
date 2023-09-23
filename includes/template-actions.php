<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

add_action( 'subheader', 'func_subheader' );

function func_subheader() {
	if( is_404() ) return;

	if( is_amp_px() ) return;
	
	if( is_home() ) { ?>
	<div id="subheader">
		<div class="imgbg">
			<?php echo cover_header(); ?>
		</div>
		<div class="subcontainer">
			<?php 
			$titulo_p = appyn_options( 'titulo_principal'); 
			if ( !empty( $titulo_p ) ) echo '<h1>'.$titulo_p.'</h1>'; 

			$descripcion_p = appyn_options( 'descripcion_principal'); 

			if ( !empty( $descripcion_p ) ) echo '<h2>'.$descripcion_p.'</h2>';

			get_template_part( 'template-parts/searchform' );
			
			echo px_header_social(); 
			?>
		</div>
	</div>
	<?php } else { ?>
	<div id="subheader" class="np">
		<div id="searchBox">
			<form action="<?php bloginfo('url'); ?>">
				<input type="text" name="<?php echo ( appyn_options( 'search_google_active', true ) ) ? 'q' : 's'; ?>" placeholder="<?php echo px_gte( 'bua' ); ?>" required autocomplete="off" id="sbinput" aria-label="Search" class="sb_search">
				<?php echo ( appyn_options( 'search_google_active', true ) ) ? '<input type="hidden" name="s">' :'' ?>
				<button type="submit" aria-label="Search" title="<?php echo px_gte( 'bua' ); ?>" class="sb_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
			</form>
			<ul></ul>
		</div>
		<?php echo px_header_social(); ?>
	</div>
	<?php }
}

add_action( 'subheader', 'func_action_home_pd' );

function func_action_home_pd() {
	global $post;

	if( ! is_front_page() || ! appyn_options( 'home_sp_checked' ) ) return;

	$hspc = appyn_options( 'home_sp_checked' );
	
	if( count($hspc) == 0 ) return;

	echo '<div id="featured_posts">';
	foreach( $hspc as $post_id ) {
		$post = get_post( $post_id ); 
		$datos_imagenes = appyn_gpm( $post_id, 'datos_imagenes' );
		$urlim = $datos_imagenes[0];
		if( strpos($urlim, 'googleusercontent.com') !== false ) {
			$re = '/([^=]+$)/m';
			$subst = "w400-rw";
			$urlim = preg_replace($re, $subst, $urlim);
		}
		echo '<div class="fp_box">
			<a href="'.get_the_permalink( $post ).'" '.( ( appyn_options( 'lazy_loading' ) && ! is_amp_px() ) ? 'class="lazyload" data-bgsrc="'.$urlim.'"' : 'style="background-image:url('.$urlim.');"' ).' title="'.get_the_title( $post ).'">
				<div class="fpb_a">'.px_post_thumbnail( 'miniatura', $post ) .'<div class="fpb_title">'.get_the_title( $post ).'</div></div>
			</a>
		</div>';
	}
	echo '</div>';
}

add_action( 'do_home', 'func_action_home_mq' );

function func_action_home_mq() {
	global $post;

	if( is_amp_px() ) return;
	
	$mas_calificadas = get_option('appyn_mas_calificadas');
	if(!empty($mas_calificadas)){
		$mas_calificadas_limite = get_option('appyn_mas_calificadas_limite');
		$mas_calificadas_limite = (empty($mas_calificadas_limite)) ? '5' : $mas_calificadas_limite;
		
		$args = array( 'posts_per_page' => $mas_calificadas_limite, 'meta_key' => 'new_rating_users', 'orderby' => 'meta_value_num', 'ignore_sticky_posts' => true );			

		$iamc = get_option( 'appyn_versiones_mostrar_inicio_apps_mas_calificadas', 0 );
		
		if( $iamc == 1 ) {
			$args['post_parent'] = 0;
		}
		
		$query = new WP_Query( $args );

		if( $query->have_posts() ): ?>
    	<div class="section">
            <div class="title-section"><?php echo px_gte( 'amc' ); ?></div>
        	<div id="slidehome" class="px-carousel pxcn">
				<div class="px-carousel-nav">
					<button type="button" class="px-prev" title="<?php echo __( 'Anterior', 'appyn' ); ?>"><i class="far fa-chevron-left"></i></button>
					<button type="button" class="px-next" title="<?php echo __( 'Siguiente', 'appyn' ); ?>"><i class="far fa-chevron-right"></i></button>
				</div>
				<div class="px-carousel-wrapper">
					<div class="px-carousel-container">
				<?php
				while( $query->have_posts() ) : $query->the_post();
					if( !$post ) continue; ?>

					<div class="px-carousel-item"><?php get_template_part('template-parts/loop/app-v2'); ?></div>
				<?php endwhile; ?>
					</div>
				</div>
            </div>
        </div>
		<?php endif; wp_reset_postdata(); 
	}
}

add_action( 'do_home', 'func_action_home' );

function func_action_home() {
	if( have_posts() ) : 
	$i = 1;

	if( appyn_options( 'home_hidden_posts') ) return;

	$aprpc = appyn_options( 'apps_per_row_pc', 6 );
	$aprmv = appyn_options( 'apps_per_row_movil', 2 );
?>
	<div class="section">
		<div class="title-section"><?php echo px_gte( 'uadnw' ); ?></div>
		<div class="baps" data-cols="<?php echo $aprpc; ?>">
			<?php
			$a = 0;
			while( have_posts() ) : the_post();
				get_template_part( 'template-parts/loop/app' );
				$s = 0;
				if( wp_is_mobile() ) {
					if( $i % $aprmv == 0 ) $s = 1;
				} else {
					if( $i % $aprpc == 0 ) $s = 1;
				}
				if( $s == 1 ) {
					echo '</div>'.(( $a == 0 ) ? px_ads( 'ads_home' ) : '').'<div class="baps" data-cols="'.$aprpc.'">';
					$a = 1;
				}
				$i++; 
			endwhile;
			?>
		</div>
		<?php paginador(); ?>
	</div>
<?php
	endif; wp_reset_query(); 	
}

add_action( 'do_home', 'func_action_home_blog' );

function func_action_home_blog() {	

	if( appyn_options( 'home_hidden_blog') ) return;

	$blog_posts_home_limite = get_option( 'appyn_blog_posts_home_limite' );
	$blog_posts_home_limite = ( empty( $blog_posts_home_limite ) ) ? '4' : $blog_posts_home_limite;
	$query = new WP_Query(array( 'post_type' => 'blog', 'posts_per_page' => $blog_posts_home_limite ) );
	if( $query->have_posts() ) : ?>
		<div class="section">
			<div class="title-section"><?php echo __( 'Blog', 'appyn' ); ?></div>
			<div class="bloque-blogs px-columns">
				<?php 
				while( $query->have_posts() ) : $query->the_post();
					get_template_part( 'template-parts/loop/blog-home' ); 
				endwhile; 
				?>
			</div>
			<?php if( $query->found_posts > $blog_posts_home_limite ):?>
				<p><a href="<?php echo get_post_type_archive_link( 'blog' ); ?>" class="more"><?php echo __( 'Ver más', 'appyn' ); ?></a></p>
			<?php endif; ?>
		</div>
	<?php
	endif;
	wp_reset_query(); 
}

add_action( 'do_home', 'func_action_home_categories' );

function func_action_home_categories() {
	global $wp_query;

	$categorias_home = get_option( 'appyn_categories_home' );
	if( !empty( $categorias_home ) ) { 
		$h = 1; 
		foreach( $categorias_home as $cat) :
			$cat = get_term( $cat, 'category' );
			if( function_exists( 'icl_object_id' ) ){ //WPML
				$cat_id_wpml = icl_object_id( $cat->term_id,'category',false,ICL_LANGUAGE_CODE);
				if( !empty( $cat_id_wpml ) )
					$cat = get_term_by( 'id', $cat_id_wpml, 'category' );
			}
			$i = 1;
			$categories_home_limite = get_option( 'appyn_categories_home_limite' );
			$categories_home_limite = ( empty( $categories_home_limite ) ) ? '10' : $categories_home_limite;

			$args = array( 'posts_per_page' => $categories_home_limite, 'cat' => $cat->term_id );			

			$categories_home_versiones = get_option( 'appyn_versiones_mostrar_inicio_categorias', 0 );

			if( $categories_home_versiones == 1 ) {
				$args['post_parent'] = 0;
			}

			query_posts($args);

			if( have_posts() ) : 
				$px_cat_icon = get_term_meta( $cat->term_id, "px_cat_icon", true );

				$ico = ( $px_cat_icon ) ? '<span class="icop '.$px_cat_icon.'"></span>' : '';
									
				$aprpc = appyn_options( 'apps_per_row_pc', 6 );
				$aprmv = appyn_options( 'apps_per_row_movil', 2 );
			?>
			<div class="section">
				<div class="title-section">
					<?php echo $ico; ?>
					<span><?php echo $cat->name; ?></span>
				</div>
				<div class="baps" data-cols="<?php echo $aprpc; ?>">
					<?php
					$a = 0;
					while( have_posts() ) : the_post();
						get_template_part( 'template-parts/loop/app' );
						$s = 0;
						if( wp_is_mobile() ) {
							if( $i % $aprmv == 0 ) $s = 1;
						} else {
							if( $i % $aprpc == 0 ) $s = 1;
						}
						if( $s == 1 ) {
							echo '</div>'.(( $a == 0 ) ? px_ads( 'ads_home' ) : '').'<div class="baps" data-cols="'.$aprpc.'">';
							$a = 1;
						}
						$i++; 
					endwhile;
					?>
				</div>
				<?php if( $wp_query->found_posts > $categories_home_limite ) { ?>
					<p><a href="<?php echo get_term_link( $cat->term_id, 'category' ); ?>" class="more"><?php echo __( 'Ver más', 'appyn' ); ?></a></p>
				<?php } ?>
			</div>
			<?php endif; wp_reset_query(); ?>
		<?php $h++; endforeach; ?>
   <?php } 
}

function action_func_caja($name, $version = false) {
	
	$cvn = get_option( 'appyn_orden_cajas_disabled', array() );
	$get_download = get_query_var( 'download' );

	if( strpos($name, 'permanent_custom_box_') !== false ) {
		$re = '/permanent_custom_box_(.*)/ms';
		preg_match_all($re, $name, $matches, PREG_SET_ORDER, 0);
		$id = $matches[0][1];
		$pcb = get_option( 'permanent_custom_boxes' );
		if( $pcb ) {
			if( isset($pcb[$id]) ) {
				do_action( 'func_caja_permanent_custom_box', $id );
			}
		}
	}
	else {
		if( $version ) {
			do_action( 'func_caja_'.$name );
		} else {
			if( $name == 'versiones' ) {
				if (!in_array($name, $cvn) || $get_download )
					do_action( 'func_caja_'.$name, false );
			} else {
				if( !in_array($name, $cvn ) || $get_download ) 
					do_action( 'func_caja_'.$name );
			}
		}
	}
} 

add_action( 'seccion_cajas', 'func_seccion_cajas' );

function func_seccion_cajas() {
	global $post;
	$oc = get_option( 'appyn_orden_cajas', null );
	$get_download = get_query_var( 'download' );
	
	if( $post->post_parent != 0 ) {
		if( $oc ) {
			foreach( $oc as $k => $a ) {
				if( activate_versions_boxes($k) ) {
					action_func_caja($k, true);
				}
			}
		} else {
			order_default('versions');
		}

	} else {
		if( $oc ) {
			foreach( $oc as $k => $a ) {
				if( $get_download ) { 
					if( activate_internal_page_boxes($k) ) {
						action_func_caja($k);
					}
				} else {
					action_func_caja($k);
				}
			}
		 } else {
			order_default();
		}
	}
}

function order_default($t = '') {

	if( $t == "versions" ) {
		do_action( 'func_caja_versiones' );
			
		if( activate_versions_boxes('descripcion') ) {
			do_action( 'func_caja_descripcion' );
		}
			
		if( activate_versions_boxes('ads_single_center') ) {
			do_action( 'func_caja_ads_single_center' );
		}

		if( activate_versions_boxes('novedades') ) {
			do_action( 'func_caja_novedades' );
		}

		if( activate_versions_boxes('imagenes') ) {
			do_action( 'func_caja_imagenes' );
		}

		if( activate_versions_boxes('video') ) {
			do_action( 'func_caja_video' );
		}
		
		if( activate_versions_boxes('enlaces_descarga') ) {
			do_action( 'func_caja_enlaces_descarga' );
		}

		if( activate_versions_boxes('relacionadas') ) {
			do_action( 'func_caja_apps_relacionadas' );
		}
		
		if( activate_versions_boxes('apps_desarrollador') ) {
			do_action( 'func_caja_apps_desarrollador' );
		}

		if( activate_versions_boxes('cajas_personalizadas') ) {
			do_action( 'func_caja_cajas_personalizadas' );
		}

		if( activate_versions_boxes('tags') ) {
			do_action( 'func_caja_tags' );
		}

		if( activate_versions_boxes('comentarios') ) {
			do_action( 'func_caja_comentarios' );
		}
	} else {
		do_action( 'func_caja_versiones', false );
		do_action( 'func_caja_descripcion' );
		do_action( 'func_caja_ads_single_center' );
		do_action( 'func_caja_novedades' );
		do_action( 'func_caja_imagenes' );
		do_action( 'func_caja_video' );
		do_action( 'func_caja_enlaces_descarga' );
		do_action( 'func_caja_apps_relacionadas' );
		do_action( 'func_caja_apps_desarrollador' );
		do_action( 'func_caja_cajas_personalizadas' );
		do_action( 'func_caja_tags' );
		$pcb = get_option( 'permanent_custom_boxes' );
		if( $pcb ) {
			foreach( $pcb as $k => $p ) {
				do_action( 'func_caja_permanent_custom_box', $k );
			}
		}
		do_action( 'func_caja_comentarios' );
	}
}

add_action( 'func_caja_comentarios', 'func_caja_comentarios' );

function func_caja_comentarios() {
	global $post, $comments_single;

	if( $post->post_parent == 0 ) {
				
		if ( post_password_required() ) return;

		$comments_single = get_option('appyn_comments'); 

		if( $comments_single == "disabled" ) return;

		$get_download = get_query_var( 'download' );
		
		if( $get_download )
			if( !activate_internal_page_boxes('comentarios') ) return;
			
		comments_template();
	}
}

add_action( 'func_caja_enlaces_descarga', 'func_caja_enlaces_descarga' );

function func_caja_enlaces_descarga() {
	global $post;

	if( !is_download_links_normal() ) return;

	$datos_download = get_datos_download($post->ID);

	if( !is_array($datos_download) ) return;
	
	if( !isset($datos_download['option']) ) $datos_download['option'] = 'links';

	if( $datos_download['option'] == "direct-link" ) return;

	if( $datos_download['option'] == "direct-download" ) return;

	if( empty($datos_download['links_options'][0]) ) return;

	?>
	<div id="download" class="box">
		<h2 class="box-title"><?php echo __( 'Enlaces de descarga', 'appyn' ); ?></h2>
		<?php do_action( 'list_download_links' ); ?>
	</div>
	<?php
}

add_action( 'func_caja_descripcion', 'func_caja_descripcion' );

function func_caja_descripcion() {
	global $post;
	?>
	<div id="descripcion" class="box">
		<h2 class="box-title"><?php echo __( 'Descripción', 'appyn' ); ?></h2>
		<div class="entry">
			<div class="entry-limit">
				<?php px_the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
		</div>
	</div>
<?php
}

add_action( 'func_caja_ads_single_center', 'func_caja_ads_single_center' );

function func_caja_ads_single_center() {

	echo px_ads( 'ads_single_center' );
}

add_action( 'func_caja_versiones', 'func_caja_versiones', 10, 2 );

function func_caja_versiones($full = false, $cvn = array()) {
	global $wp_query, $wpdb, $post;

	$versiones_cantidad_post = get_option( 'appyn_versiones_cantidad_post', 5 );
	$args = array( 
			'post_parent' => $post->ID, 
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
	);

	if( $post->post_parent != 0 ) {
		$args['post_parent'] = $post->post_parent;
		$args['post__not_in'] = array($post->ID);
		$post_add = get_post($post->post_parent);
	}

	$versiones = new WP_Query( $args );
	
	if( $versiones->have_posts() || isset($post_add) ) : 
	?>
	<div id="versiones" class="box">
		<h2 class="box-title"><?php echo __( 'Versiones', 'appyn' ); ?></h2>
		<div class="box-content">
			<table style="margin:0;">
				<thead>
					<tr>
						<th><?php echo __( 'Versión', 'appyn' ); ?></th>
						<th><?php echo __( 'Peso', 'appyn' ); ?></th>
						<th><?php echo __( 'Requerimientos', 'appyn' ); ?></th>
						<th style="width:100px"><?php echo __( 'Fecha', 'appyn' ); ?></th>
					</tr>
				</thead>
				<tbody>
			<?php 
			$date_change = array(
				'enero' => '01',
				'febrero' => '02',
				'marzo' => '03',
				'abril' => '04',
				'mayo' => '05',
				'junio' => '06',
				'julio' => '07',
				'agosto' => '08',
				'setiembre' => '09',
				'octubre' => '10',
				'noviembre' => '11',
				'diciembre' => '12',
				' de ' => '-',
			);
			if( $post->post_parent != 0 ) {

				$inf = get_post_meta( $post_add->ID, 'datos_informacion', true );

				if( is_array($inf) ) {

					$link = get_permalink( $post_add->ID );
					$tb = '';

					if( appyn_options( 'version_download_link_direct' ) ) {
						$datos_download = get_datos_download( $post_add->ID );

						if( $link = px_show_first_dl() ) {
							$tb = ' target="_blank"';
						}
					}

				echo '<tr>
						<td><a href="'. $link .'"'.$tb.'>'.(( !empty($inf['version']) ) ? $inf['version'] : '-').'</a></td>
						<td>'.(( !empty($inf['tamano']) ) ? $inf['tamano'] : '-').'</td>
						<td>'.(( !empty($inf['requerimientos']) ) ? $inf['requerimientos'] : '-').'</td>
						<td>'.(( !empty($inf['fecha_actualizacion']) ) ? date_i18n( 'd/m/Y', strtotime(strtr($inf['fecha_actualizacion'], $date_change)) ) : '-').'</td>
					</tr>';		
				}	
			}	

			$i = 1;
			while( $versiones->have_posts() ) : $versiones->the_post();
			
				$inf = get_post_meta( $post->ID, 'datos_informacion', true );
				if( is_array($inf) ) {

					$link = get_permalink( $post->ID );
					$tb = '';
					if( appyn_options( 'version_download_link_direct' ) ) {
						$datos_download = get_datos_download( $post->ID );

						if( $link = px_show_first_dl() ) {
							$tb = ' target="_blank"';
						}
					}

					if( $i <= $versiones_cantidad_post || $full ) {
					echo '<tr>
							<td><a href="'. $link.'"'.$tb.'>'.(( !empty($inf['version']) ) ? $inf['version'] : '-').'</a></td>
							<td>'.(( !empty($inf['tamano']) ) ? $inf['tamano'] : '-').'</td>
							<td>'.(( !empty($inf['requerimientos']) ) ? $inf['requerimientos'] : '-').'</td>
							<td>'.(( !empty($inf['fecha_actualizacion']) ) ? date_i18n( 'd/m/Y', strtotime(strtr($inf['fecha_actualizacion'], $date_change)) ) : '-').'</td>
						</tr>';	
						$i++;
					}	
				} else {
					if( current_user_can('administrator') ) {
						echo '<tr>
							<td colspan="100%"><a href="'.get_edit_post_link( $post ).'"><i>'.__( 'Para esta versión falta completar los datos', 'appyn' ).'</i></a>
						</tr>';
					}
				}	
			endwhile; wp_reset_query(); ?>
				</tbody>
			</table>
		</div>
		<?php
		if( !$full ) {
		if( $versiones->found_posts > $versiones_cantidad_post ) { ?>
		<p style="margin-bottom:0;"><a href="<?php echo versions_permalink(); ?>" class="readmore"><?php echo __( 'Ver más versiones', 'appyn' ); ?></a></p>
		<?php } 
		} ?>
	</div>
	<?php endif; wp_reset_query(); 
} 

add_action( 'func_caja_novedades', 'func_caja_novedades' );

function func_caja_novedades() {
	global $post;

	$datos_informacion = get_post_meta($post->ID, 'datos_informacion', true);

	if( empty($datos_informacion['novedades']) ) return;

	?>
	<div id="novedades" class="box">
		<h2 class="box-title"><?php echo __( 'Novedades', 'appyn' ); ?></h2>
		<div class="box-content entry">
			<?php echo wpautop( $datos_informacion['novedades'] ); ?>
		</div>
	</div>
	<?php
} 

add_action( 'func_caja_imagenes', 'func_caja_imagenes' );

function func_caja_imagenes() { 
	global $post;

	$datos_imagenes = get_post_meta( $post->ID, 'datos_imagenes', true );
	if( !isset($datos_imagenes) && empty($datos_imagenes) || @!is_array($datos_imagenes) ) return;
	$datos_imagenes = @array_map('trim', $datos_imagenes); 
	$datos_imagenes = @array_filter($datos_imagenes, function($a) { return $a!==""; });

	if( !is_array($datos_imagenes) ) return;
	
	if(count($datos_imagenes) == 0 ) return;

	?>
	<div class="box imagenes">
		<h2 class="box-title"><?php echo __( 'Imágenes', 'appyn' ); ?></h2>
		<div id="slideimages" class="px-carousel" data-title="<?php the_title(); ?>">
			<?php 
			if( is_amp_px() ) { ?>
			<amp-carousel height="300" controls layout="fixed-height" type="slides">
			<?php
			$i = 0; 
			foreach($datos_imagenes as $imagen) {
				if(strpos($imagen, 'googleusercontent.com') !== false || strpos($imagen, 'ggpht.com') !== false) {
					$last_pos = strrpos($imagen, '=');
					$imagen= substr($imagen, 0, $last_pos)."=h305";
					$imagen_big = substr($imagen, 0, $last_pos)."=h650";
				} else {
					$imagen_id = get_image_id($imagen);
					if(empty($imagen_id)){
						$imagen_big = $imagen;
						$imagen = $imagen;
					} else {
						$imagen_big = $imagen;
						$imagen = wp_get_attachment_image_src($imagen_id, 'medium');	
						$imagen = $imagen[0];
					}
				}
				?>
				<amp-img src="<?php echo $imagen; ?>" layout="fill" height="300" alt="a sample image"></amp-img>
			<?php } ?>
			</amp-carousel>
			<?php
			} else { ?>
			<div class="px-carousel-nav disabled"><button type="button" class="px-prev disabled" title="<?php echo __( 'Anterior', 'appyn' ); ?>"><i class="far fa-chevron-left"></i></button><button type="button" class="px-next disabled" title="<?php echo __( 'Siguiente', 'appyn' ); ?>"><i class="far fa-chevron-right"></i></button></div>
			<div class="px-carousel-wrapper">
				<div class="px-carousel-container">
					<?php $i = 0; 
					foreach($datos_imagenes as $imagen) {
						if(strpos($imagen, 'googleusercontent.com') !== false || strpos($imagen, 'ggpht.com') !== false) {
							$last_pos = strrpos($imagen, '=');
							$imagen= substr($imagen, 0, $last_pos)."=h305";
							$imagen_big = substr($imagen, 0, $last_pos)."=h650";
						} else {
							$imagen_id = get_image_id($imagen);
							if(empty($imagen_id)){
								$imagen_big = $imagen;
								$imagen = $imagen;
							} else {
								$imagen_big = $imagen;
								$imagen = wp_get_attachment_image_src($imagen_id, 'medium');	
								$imagen = $imagen[0];
							}
						}
						$appyn_lazy_loading = ( get_option('appyn_lazy_loading') ) ? get_option('appyn_lazy_loading') : NULL;
						if( $appyn_lazy_loading == 1 ) {
							$image_blank = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAI4AAACNAQMAAABbp9DlAAAAA1BMVEX///+nxBvIAAAAGUlEQVRIx+3BMQEAAADCIPunNsU+YAAA0DsKdwABBBTMnAAAAABJRU5ErkJggg==";
							$color_theme = get_option( 'appyn_color_theme' );
							$color_theme_principal = get_option( 'appyn_color_theme_principal' );
							if( is_dark_theme_active() ) {
								$image_blank = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAI4AAACNAQMAAABbp9DlAAAAA1BMVEUUHCkYkPNHAAAAGUlEQVRIx+3BMQEAAADCIPunNsU+YAAA0DsKdwABBBTMnAAAAABJRU5ErkJggg==";
							}
							echo '<div class="px-carousel-item"><img class="lazyload" src="'.$image_blank.'" data-src="'.$imagen.'" width="100%" height="100%" data-big-src="'.$imagen_big.'" alt="'.get_the_title().' '.($i + 1).'" referrerpolicy="no-referrer"></div>';
						} else {
							echo '<div class="px-carousel-item"><img src="'.$imagen.'" width="100%" height="100%" data-big-src="'.$imagen_big.'" alt="'.get_the_title().' '.($i + 1).'" referrerpolicy="no-referrer"></div>'; 
						}
						$i++;
					}
					?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div> 
<?php 
} 

add_action( 'func_caja_video', 'func_caja_video' );

function func_caja_video() {
	global $post,$datos_video;

	$datos_video = get_post_meta($post->ID, 'datos_video', true); 

	if( empty($datos_video['id']) ) return;

	?>
	<div class="box">
		<h2 class="box-title"><?php echo __( 'Video', 'appyn' ); ?></h2>
		<div class="iframeBoxVideo" data-id="<?php echo $datos_video['id']; ?>">
		<?php
		if( is_amp_px() ) {
			echo '<amp-youtube data-videoid="'.$datos_video['id'].'" layout="responsive" width="560" height="315"></amp-youtube>';
		} else {
			$appyn_lazy_loading = ( get_option('appyn_lazy_loading') ) ? get_option('appyn_lazy_loading') : NULL;
			if( $appyn_lazy_loading == 1 ) {
			?>
				<iframe width="730" height="360" src="" data-src="https://www.youtube.com/embed/<?php echo $datos_video['id']; ?>" style="border:0; overflow:hidden;" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="lazyload"></iframe>
			<?php } else { ?>
				<iframe width="730" height="360" src="https://www.youtube.com/embed/<?php echo $datos_video['id']; ?>" style="border:0; overflow:hidden;" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			<?php }
		} ?>
		</div>
	</div>
	<?php
}

add_action( 'func_caja_apps_relacionadas', 'func_caja_apps_relacionadas' );

function func_caja_apps_relacionadas() {
	global $post;
	
	$args = array(
		'post_type' => 'post',
		'posts_per_page' => 5, 
		'post__not_in' => array($post->ID), 
		'post_parent' => 0, 
		'orderby' => 'relevance' 
	);

	
	if( appyn_options( 'width_page' )  ) {
		$args['posts_per_page'] = 8;
	}

	$apps_related_type = get_option( 'appyn_apps_related_type', array() ); 

	if( !is_array($apps_related_type) ) return;
	
	if( in_array('cat', $apps_related_type) || empty($apps_related_type) ) {
		$cats = get_the_category($post->ID);
		$list_cats_id = array();
		foreach( $cats as $c ) {
			$list_cats_id[] = $c->term_id;
		}
		$args['category__in'] = $list_cats_id;
	} 
	if( in_array('tag', $apps_related_type) ) {
		$tags = get_the_tags($post->ID);
		$list_tags_id = array();
		if( is_array($tags) ) {
			foreach( $tags as $t ) {
				$list_tags_id[] = $t->term_id;
			}
			$args['tag__in'] = $list_tags_id;
		}
	} 
	if( in_array('title', $apps_related_type) ) {
		$args['s'] = get_the_title();
	} 
	if( in_array('random', $apps_related_type) ) {
		$args['orderby'] = 'rand';
	}

	$aprpc = appyn_options( 'apps_per_row_pc', 6 );
	
	$query = new WP_Query( $args );
	if( $query->have_posts() ) : ?>
		<div class="box rlat">
			<h2 class="box-title"><?php echo __( 'Apps relacionadas', 'appyn' ); ?></h2>
			<div class="baps">
			<?php while( $query->have_posts() ) : $query->the_post();
			get_template_part( 'template-parts/loop/app-related' ); 
			endwhile; ?>
			</div>
		</div>			
	<?php
	endif;
	wp_reset_query();
} 

add_action( 'func_caja_cajas_personalizadas', 'func_caja_cajas_personalizadas' );

function func_caja_cajas_personalizadas() {
	global $post;

	$custom_boxes = get_post_meta( $post->ID, 'custom_boxes', true );

	if( empty($custom_boxes) ) return;

	foreach($custom_boxes as $box_key => $box_value) { 
		if( !empty( $box_value['title'] ) || !empty( $box_value['content'] ) ) { ?>
			<div id="box-<?php echo $box_key; ?>" class="box personalizadas">
				<h2 class="box-title"><?php echo $box_value['title']; ?></h2>
				<div class="box-content"><?php echo apply_filters('the_content', px_content_filter($box_value['content']) ); ?></div>
			</div>
	<?php } 
	}
}

add_action( 'func_caja_apps_desarrollador', 'func_caja_apps_desarrollador' );

function func_caja_apps_desarrollador() { 
	global $post;

	$dev_terms = wp_get_post_terms( $post->ID, 'dev', array('fields' => 'all'));

	if( !isset($dev_terms[0]->slug) ) return;

	$query = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 5, 'post__not_in' => array($post->ID), 'post_parent' => 0, 'tax_query' => array(
		array(
			'taxonomy' => 'dev',
			'field'    => 'slug',
			'terms'    => $dev_terms[0]->slug,
		),
	) ) );
	if( $query->have_posts() ) { ?>
	<div class="box rlat">
		<h2 class="box-title"><?php echo __( 'Apps del desarrollador', 'appyn' ); ?></h2>
		<div class="baps">
		<?php while( $query->have_posts() ) : $query->the_post();
		get_template_part( 'template-parts/loop/app-related' ); 
		endwhile; ?>
		</div>
	</div>
	<?php } 
	wp_reset_query(); 
} 

add_action( 'func_caja_tags', 'func_caja_tags' );

function func_caja_tags() { 
	global $post;

	$post_tags = wp_get_post_tags( $post->ID );
	
	if( empty($post_tags) ) return;

	?>
	<div id="tags" class="box tags">
		<h2 class="box-title"><?php echo __( 'TAGS', 'appyn' ); ?></h2>
		<?php the_tags( '', '' ); ?>
	</div> 
	<?php
}

add_action( 'func_caja_permanent_custom_box', 'func_caja_permanent_custom_box', 10, 2 );

function func_caja_permanent_custom_box( $id ) { 
	global $post;

	$pcb = get_option( 'permanent_custom_boxes' );
	
	if( empty($pcb) ) return;
	
	if( empty($pcb[$id]['title']) || empty($pcb[$id]['content']) ) return;
	?>
	<div id="pcb-<?php echo $id; ?>" class="box personalizadas">
		<h2 class="box-title"><?php echo $pcb[$id]['title']; ?></h2>
		<div class="box-content"><?php echo apply_filters('the_content', px_content_filter($pcb[$id]['content']) ); ?></div>
	</div> 
	<?php
}

add_action( 'init', 'px_verify_return_gdrive' );

function px_verify_return_gdrive() {
	$code = isset($_GET['code']) ? $_GET['code'] : null;
	$appyn_upload = isset($_GET['appyn_upload']) ? $_GET['appyn_upload'] : null;

	if( $code && $appyn_upload == 'gdrive' ) {

		if( ! current_user_can('administrator') ) return;

		$gdrive = new TPX_GoogleDrive();
		if( $gdrive->getClient() ) {
			$token = $gdrive->getClient()->fetchAccessTokenWithAuthCode($code);

			$gdrive->getClient()->setAccessToken($token);

			update_option('appyn_gdrive_token', json_encode($token));
			header("Location: ".admin_url('admin.php?page=appyn_panel#edcgp'));
			exit;
		}
	}
}

add_action( 'init', 'px_verify_return_onedrive' );

function px_verify_return_onedrive() {
	$code = isset($_GET['code']) ? $_GET['code'] : null;

	if( $code && ( strpos( $_SERVER['HTTP_REFERER'], 'login.microsoftonline.com' ) !== false || strpos( $_SERVER['HTTP_REFERER'], 'account.live.com' ) !== false  || strpos( $_SERVER['HTTP_REFERER'], 'login.live.com' ) !== false ) ) {

		if( ! current_user_can('administrator') ) return;

		$onedrive = new TPX_OneDrive();

		$onedrive->getToken($code);

		header("Location: ".admin_url('admin.php?page=appyn_panel#edcgp'));
		exit;
	}
}

add_action( 'init', 'px_verify_return_dropbox' );

function px_verify_return_dropbox() {
	$code = isset($_GET['code']) ? $_GET['code'] : null;
	$appyn_upload = isset($_GET['appyn_upload']) ? $_GET['appyn_upload'] : null;
	$dropbox_app_key = appyn_options( 'dropbox_app_key' );
	$dropbox_app_secret = appyn_options( 'dropbox_app_secret' );
	
	if( $code && $appyn_upload == 'dropbox' ) {

		if( ! current_user_can('administrator') ) return;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "code=".$_GET['code']."&grant_type=authorization_code&redirect_uri=".add_query_arg('appyn_upload', 'dropbox', get_bloginfo('url')));
		curl_setopt($ch, CURLOPT_USERPWD, $dropbox_app_key.':'.$dropbox_app_secret);

		$headers = array();
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			die('Error:' . curl_error($ch));
		}
		if( $result ) {
			$j = json_decode($result, true);
			if( isset($j['access_token']) ) {
				update_option( 'appyn_dropbox_result', $result );
				update_option( 'appyn_dropbox_expires', (time()+$j['expires_in']) );
				header("Location: ".admin_url('admin.php?page=appyn_panel#edcgp'));
				exit;
			}
		}
	}
}

add_action( 'wp', 'redirect_download_link' );

function redirect_download_link() {
	$dl = get_query_var( 'download_link' );
	if( $dl ) {
		$dl = px_encrypt_decrypt( 'decrypt', $dl );
		wp_redirect($dl);
		exit;
	}
}

add_action( 'wp_footer', 'px_backtotop' );

function px_backtotop() {
	echo '<div id="backtotop"><i class="far fa-chevron-up"></i></div>';
}

add_action( 'wp_head', 'px_clsa', 9999 );

function px_clsa() {

	if( ! httuachl() ) {
        echo '<style>
		.imgload {
			animation:0.5s ease 0.5s normal forwards 1 fadein;
			-webkit-animation:0.5s ease 0.5s normal forwards 1 fadein;
		}
		.bloque-imagen.bi_ll.bi_ll_load {
			animation:0.5s ease 0.5s normal forwards 1 fadeingb;
			-webkit-animation:0.5s ease 0.5s normal forwards 1 fadeingb;
		}
		#subheader .imgbg img {
			animation: subheaderimg 20s linear infinite;
		}
		@media (max-width: 500px) {
			#subheader .imgbg img {
				animation: subheaderimg_ 20s linear infinite;
			}
		}
		</style>';
    } else {
		echo '<style>
			.lazyload {
				opacity: 1;
			}';
		if( is_dark_theme_active() ) { 
			echo '
			html body, .wrapper-inside,
			html .app-s .rating-average b, .ratingBoxMovil .rating-average b,
			html a, html #header nav .menu > li.menu-item-has-children > .sub-menu::before, html .section .bloque-blog a.title:hover, html .section.blog .bloques li a.title:hover, html .app-s .box .entry a, html .app-s .box .box-content a, html .app-p .box .entry a, html .app-s .rating-average b, html .app-s .data-app span a, html .rlat .bav1 a:hover .title, html .ratingBoxMovil .rating-average b, html #comments ol.comment-list .comment .comment-body .reply a, html #wp-calendar td a, html .trackback a, html .pingback a, html .pxtd h3 i, html .spinvt .snt {
				color: #FFF;
			}
			html .wb .developer, 
			html .wb .app-date, 
			html .wb .version,
			html #breadcrumbs,
			html #breadcrumbs a,
			html .app-spe .version,
			html .bav .version, 
			html .bav .developer, 
			html .bav .app-date,
			html .box .box-title,
			html #footer .widget ul li .wb .developer, 
			html .footer-bottom .copy {
				color: #e2e2e2;
			}';
		} else {
			echo '#breadcrumbs a,
			html .app-spe .version,
			html .bav .version, 
			html .bav .developer, 
			html .bav .app-date,
			html .box .box-title {
				color: #6e6e6e;
			}';
		}
		echo '
		html .widget.widget_tag_cloud a {
			padding: 10px;
		}
		html .footer-bottom .copy {
			color: #FFF;
		}
		html .buttond {
			background-color: #0f856c;
		}
		</style>';
	}
}

add_filter( 'script_loader_tag', 'remove_jquery_tag', 10, 3 );

function remove_jquery_tag( $tag, $handle, $src ) {
	if( is_admin() || ! httuachl() ) return $tag;
    if ( 'jquery' == $handle || 'jquery-core' == $handle || 'jquery-migrate' == $handle  ) {
        return '';
    }
    return $tag;
}

add_action( 'wp_head', 'print_jquery', 1 );

function print_jquery() {
	if( is_admin() || ! httuachl() ) return;
    global $wp_scripts;
    $jquery_handle = 'jquery-core';
    $jquery_src = $wp_scripts->registered[$jquery_handle]->src;
    $jquery_content = file_get_contents(ABSPATH.$jquery_src);
    echo "<script>".$jquery_content."</script>";
	
    $jquery_handle = 'jquery-migrate';
    $jquery_src = $wp_scripts->registered[$jquery_handle]->src;
    $jquery_content = file_get_contents(ABSPATH.$jquery_src);
    echo "<script>".$jquery_content."</script>";
}

add_action( 'wp_head', 'prlvf', 1 );

function prlvf() {
	global $wp_scripts;

	if( is_rtl() ) {
		echo '
		<link rel="preload" href="'.get_stylesheet_directory_uri().'/rtl.css?ver='.VERSIONPX.'">';
	}

	global $post;

	if( is_single() && has_post_thumbnail($post->ID) ) {
		echo '<link rel="preload" href="'.get_the_post_thumbnail_url($post->ID, 'thumbnail').'" as="image">';
	}

    if( ! httuachl() ) {
		ob_start();
		include (__DIR__."/../assets/css/open-sans.css");
		$fontcss = ob_get_clean();
		echo '<style>'.$fontcss.'</style>';
	}

	foreach ($wp_scripts->queue as $handle) {
		$script = $wp_scripts->registered[$handle];

        if( $script->src && $handle != "admin-bar" ) {
            $source = $script->src . ($script->ver ? "?ver={$script->ver}" : "");

            echo '<link rel="preload" as="script" href="'.$source.'">';
        }
	}
	
	global $wp_styles;
	foreach ($wp_styles->queue as $handle) {
		$style = $wp_styles->registered[$handle];
        if( $style->src && $handle != "admin-bar" ) {
			if( $style->src )
            $source = $style->src . ($style->ver ? "?ver={$style->ver}" : "");

            echo '<link rel="preload" as="style" href="'.$source.'">';
        }
	}
}

add_action('wp_head', function(){
	global $image_random_cover;

	if( @file_exists(__DIR__."/../images/".pathinfo($image_random_cover)['filename'].".webp") ) {	
		$image_random_cover = pathinfo($image_random_cover)['dirname']."/".pathinfo($image_random_cover)['filename'].".webp";
	}
	
	echo '<link rel="preload" href="'.$image_random_cover.'" as="image">';

}, 1);
 
add_action( 'wp', function(){
	global $wp_version;

	if( httuachl() ) return;

	$jquery_handle = (version_compare($wp_version, '3.6-alpha1', '>=') ) ? 'jquery-core' : 'jquery';
	$jq = $GLOBALS['wp_scripts']->registered[$jquery_handle]->ver;
	$jquery_migrate_handle = (version_compare(wp_version_check(), '3.6-alpha1', '>=') ) ? 'jquery-core' : 'jquery-migrate';
	$jqm = $GLOBALS['wp_scripts']->registered[$jquery_migrate_handle]->ver;
	add_action( 'wp_head', function()  use ($jq, $jqm) {
		echo '<link rel="preload" as="script" href="'.includes_url().'js/jquery/jquery.min.js?ver='.$jq.'">';
		echo '<link rel="preload" as="script" href="'.includes_url().'js/jquery/jquery-migrate.min.js?ver='.$jqm.'">';
	}, 1, 1);

	global $image_random_cover;

	for($n=1;$n<=5;$n++){
		$option = appyn_options( 'image_header'.$n);
		if( !empty($option) )
			$arrayimgs[] = appyn_options( 'image_header'.$n);	
	}

	if( empty($arrayimgs) ) return;
	
	$image_random_cover = $arrayimgs[rand(0,(count($arrayimgs) - 1))];
});

add_action( 'list_download_links', 'func_list_download_links', 10 );

function func_list_download_links($post_id = false, $get_opt = false, $get_dl = false) {
	global $post;

	if( $post_id ) 
		$post = get_post($post_id);
	
	$datos_download = get_datos_download($post->ID);
	
	$adl = get_option( 'appyn_download_links', null );

	$class = '';

	if( $adl != 3 ) {
		$type = appyn_options( 'download_links_design', true );
		if( $type == 1 ) {
			$class = ' ldl-b';
		} elseif( $type == 2 ) {
			$class = ' ldl-c';
		}
	} else {
		$class = ' ldl-d';
	}

	if( ! $get_opt && ! $get_dl ) {
		$get_opt = get_query_var( 'opt' );
		$get_dl = get_query_var( 'download' );
	}

	$a = get_option( 'appyn_download_timer' );
	$download_timer = ( isset($a) ) ? get_option( 'appyn_download_timer' ) : 5;

	if( $get_dl ) {
		if( $adl && $get_opt ) {
			echo '<div class="bxt'. $class .'">'.__( 'Enlace de descarga', 'appyn' ).' - '. $datos_download['links_options'][($get_opt-1)]['texto'] .'</div>';
		} else {
			echo '<div class="bxt'. $class .'">'.__( 'Enlaces de descarga', 'appyn' ).'</div>';
		}
	}

	if( count($datos_download['links_options']) > 0 ) { 

		$design_timer = appyn_options( 'design_timer' );

		if( $download_timer && !is_amp_px() ) {
			if( $design_timer == 1 ) {
				echo '<div class="sdl-bar" data-timer="'.$download_timer.'"><div style="transition: all 1s cubic-bezier(1, 1, 1, 1) 0s; width: 0%;"></div></div>';
			} else {
				echo '<div class="spinvt'. $class .'"><div class="snv"></div><div class="snt">'.$download_timer.'</div></div>';
			}
		}

		if( $adl == "0" )
			echo '<script>var noptcon = true;</script>';

		echo '<div '. ( ( $download_timer != "0" ) ? 'class="show_download_links" data-timer="'.$download_timer.'"' : '').' '.( ( $download_timer && !is_amp_px() ) ? 'style="display:none;"': '').'>';

		echo '<ul id="list-downloadlinks" class="'. $class .'">';

		if( $adl == 2 && $get_opt ) {

			foreach( $datos_download['links_options'] as $value => $element ) : 

				if( $value != ($get_opt - 1) ) continue;

				if( !is_string($value) ) :

					echo '<li><a href="'.px_download_link( $element['link'] ).'" target="_blank"'.((isset($element['follow'])) ? ' rel="follow"' : ' rel="nofollow"').' class="downloadAPK dapk_b"><i class="fa fa-download"></i>'.__( 'Descargar', 'appyn' ).'</a></li>';

				endif; 

			endforeach; 

		} else {

			foreach( $datos_download['links_options'] as $value => $element ) : 

				if( empty($element['texto']) || empty($element['link']) ) continue;
					
				if( !is_string($value) ) :
					$link = px_download_link( $element['link'] );
					$tb = false;
					if( $adl == 2 ) {
						$link = add_query_arg('opt', ($value+1), remove_query_arg('amp') );
						$tb = false;
					}
					if( $adl == 1 || $adl == 3 )
						$tb = true;

					echo '<li><a href="'.$link.'" '.( ($tb || empty($get_dl)) ? 'target="_blank"' : '' ).''.((isset($element['follow'])) ? ' rel="follow"' : ' rel="nofollow"').' class="buttond downloadAPK dapk_b"><i class="fa fa-download"></i> '.$element['texto'].'</a></li>';
				endif; 
			endforeach;
		}
			
		echo '</ul>';

		if( $dlvb = appyn_options( 'download_links_verified_by', true ) ) {
			echo '<div class="dl-verified"'. ( appyn_options( 'download_links_verified_by_p', true ) == 1 ? ' style="text-align: center;"' : '' ) .'><i class="fas fa-shield-alt"></i> <span>'. $dlvb .'</span></div>';
		}

		echo '</div>';
	}

	if( $dltbu = appyn_options( 'download_links_telegram_button_url', true ) ) {
		$dltbt = appyn_options( 'download_links_telegram_button_text', true );
		echo '<p style="text-align: center;"><a href="'. $dltbu .'" target="_blank" id="dl-telegram" class="buttond "><i class="fab fa-telegram-plane"></i> '. ( ( ! $dltbt ) ? __( 'ÚNETE A NUESTRO GRUPO DE TELEGRAM', 'appyn' ) : $dltbt ) .'</a></p>';
	}

	echo px_info_install();
}

add_action( 'init', function(){
	$dnau = appyn_options( 'disabled_notif_apps_update' );

	if( $dnau )	wp_clear_scheduled_hook( 'appyn_send_apps' );
});

if ( ! wp_next_scheduled( 'appyn_send_apps' ) ) {
	
	wp_schedule_event( time(), 'hourly', 'appyn_send_apps' );
}

add_action( 'appyn_send_apps', 'px_appyn_hook_send_apps' );

function px_appyn_hook_send_apps() {

	global $post;

	if( apply_filters( 'px_appyn_filter_stop_send_apps', false ) ) return; 

	$psa = get_option( 'px_status_apikey', null );

	if( ! isset($psa['status']) ) return;
	
	$query = new WP_Query( array( 'posts_per_page' => -1, 'post_parent' => 0, 'suppress_filters' => true, 'cache_results'  => false ) );

	if( $query->have_posts() ) :

		$list_ids = array();

		while( $query->have_posts() ) : $query->the_post();

			if(  $post->ID == null ) continue;

			$url = get_datos_info( 'consiguelo' );

			if( empty($url) ) continue;

			if( strpos($url, 'https://play.google.com/store/') === false ) continue;

			if( $post->post_parent != 0 ) continue;

			if( appyn_gpm( $post->ID, 'app_type' ) == 1 ) continue;

			$re = '/(?<=[?&]id=)[^&]+/m';
			preg_match_all($re, $url, $matches, PREG_SET_ORDER, 0);
			$app_id = $matches[0][0];

			if( !in_array_r($app_id, $list_ids) ) {
				$list_ids[] = array(
					'id' => $app_id,
					'post_id' => $post->ID,
				);
			}

		endwhile;

		if( count($list_ids) > 0 ) {

			$result = apply_filters( 'remote_post_check_apps', $list_ids );

			if( !empty($result) ) {
				
				if( ! is_array($result) ) 
					$e = json_decode($result, true);
				else 
					$e = $result;

				if( ! isset($e['error']) ) {

					update_option( 'trans_updated_apps', $e['results'] );
					px_process_list_apps();
				} 
			}
		}
	endif;	
}

add_action( 'post_updated', 'px_process_apps_to_update', 10, 1 );

function px_process_apps_to_update( $post_id ) {

	if( get_post_type($post_id) == "post" ) {
		px_process_list_apps($post_id);
	}
}

function px_process_list_apps($post_id = null) {
	
	$updated_apps = get_option( 'trans_updated_apps', null );

	if (! is_array($updated_apps)) {
		return;
	}

	if( $post_id ) {

		foreach( $updated_apps as $key => $p ) {

			if( !isset($p['version']) ) continue;

			if( $p['post_id'] == $post_id ) {

				$di = get_post_meta( $post_id, 'datos_informacion', true );
				$fa = (isset($di['fecha_actualizacion'])) ? $di['fecha_actualizacion'] : 0;
				$dd = strtotime($fa);
				$last_update = ( !empty( $di['last_update'] ) ) ? $di['last_update'] : $dd;
				$updated_apps[$key]['post_title'] = get_the_title($p['post_id']);

				$version = (isset($di['version'])) ? $di['version'] : '';
				if( strtotime(date('Y-m-d', $last_update). "+1 day") >= strtotime(date('Y-m-d', strtotime($p['update']))) || $version == $p['version'] ) {
					unset($updated_apps[$key]);
				}
			}
			
		}
	} else {

		foreach( $updated_apps as $key => $p ) {

			if( !isset($p['version']) ) continue;

			$di = get_post_meta( $p['post_id'], 'datos_informacion', true );
			$fa = (isset($di['fecha_actualizacion'])) ? $di['fecha_actualizacion'] : 0;
			$dd = strtotime($fa);
			$last_update = ( !empty( $di['last_update'] ) ) ? $di['last_update'] : $dd;
			$updated_apps[$key]['post_title'] = get_the_title($p['post_id']);

			$version = (isset($di['version'])) ? $di['version'] : '';
			if( strtotime(date('Y-m-d', $last_update). "+1 day") >= strtotime(date('Y-m-d', strtotime($p['update']))) || $version == $p['version'] ) {
				unset($updated_apps[$key]);
			}
		}
	}
	
	update_option( 'trans_updated_apps', $updated_apps );
	set_transient( 'trans_count_updated_apps', count($updated_apps) );
}

function removeElementWithValue($array, $key, $value){
	foreach($array as $subKey => $subArray){
		 if($subArray[$key] == $value){
			  unset($array[$subKey]);
		 }
	}
	return $array;
}

if ( ! wp_next_scheduled( 'appyn_check_apikey' ) ) {
    wp_schedule_event( time(), 'hourly', 'appyn_check_apikey' );
}

add_action( 'appyn_check_apikey', 'px_appyn_hook_check_apikey' );

function px_appyn_hook_check_apikey() {
	$url = API_URL."/check/apikey";

	$response = wp_remote_post( $url, array(
		'method'      => 'POST',
		'timeout'     => 30,
		'blocking'    => true,
		'headers'     => array(
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Referer' => get_site_url(),
			'Cache-Control' => 'max-age=0',
        	'Expect' => '',
		),
		'body' => array( 
			'apikey' => appyn_options( 'apikey', true ), 
			'website'	=> get_site_url(),
		),
	) );

	if ( ! is_wp_error( $response ) ) {
		update_option( 'px_status_apikey', json_decode($response['body'], true) );
	}
}

add_action( 'init', 'px_cron_init' );
	
function px_cron_init() {
	if( ! get_option( 'run_first_time_cron_apikey' ) ) {
		px_appyn_hook_check_apikey();
		update_option( 'run_first_time_cron_apikey', 1 );
	}
	if( ! get_option( 'run_first_time_cron' ) ) {
		px_appyn_hook_send_apps();
		update_option( 'run_first_time_cron', 1 );
	}
}

add_action( 'wp_head_amp', 'px_add_title_head' );

function px_add_title_head() {

	$title = apply_filters( 'px_filter_amp_title', wp_get_document_title() );
	echo '<title>'. $title .'</title>'."\n";
}

add_action( 'wp_head_amp', 'px_add_description' );

function px_add_description() {

	$desc = get_bloginfo('description');
	if( is_single() ) {
		global $post;
		add_filter( 'excerpt_more', '__return_false' );
		$desc = get_the_excerpt();
	}
	$desc = apply_filters( 'px_filter_amp_description', $desc );
	echo '<meta name="description" content="'. $desc .'">'."\n";
}

add_action( 'wp_head_amp', 'px_add_meta' );

function px_add_meta() {
	echo '
	<meta charset="'.get_bloginfo( 'charset' ).'">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="canonical" href="'.get_bloginfo('url').strtok($_SERVER['REQUEST_URI'], '?'),'">';
}

add_action( 'wp_head_amp', 'px_add_style' );

function px_add_style() {
	echo '<style amp-custom>';
	ob_start();
	include (__DIR__."/../amp/style.min.css");
	$css = ob_get_clean();
	$css = str_replace(array("\n", "\t"), '', $css);
	$re = '/(\/\*.*?\*\/)|(\!important)|(  )|(@charset "utf-8";)/m';
	$result = preg_replace($re, "", str_replace("fonts/", get_template_directory_uri()."/fonts/", 
	str_replace("images/", get_template_directory_uri()."/images/", $css)));
	echo $result;
	ob_start();
	px_css_bottom_menu();
	$bottom = ob_get_clean();
	echo str_replace(array('<style>', '</style>'), '', $bottom);
	if( is_amp_px() && is_rtl() ) {
		ob_start();
		include (__DIR__."/../rtl.css");
		$css = ob_get_clean();
		$css = str_replace(array("\n", "\t"), '', $css);
		$re = '/(\/\*.*?\*\/)|(\!important)|(  )|(@charset "utf-8";)/m';
		$result = preg_replace($re, "", str_replace("fonts/", get_template_directory_uri()."/fonts/", 
		str_replace("images/", get_template_directory_uri()."/images/", $css)));
		echo $result;
	}
	echo str_replace(array('<style>', '</style>'), '', px_add_css(true));
	?><?php add_color_theme(); ?>
	<?php 
	echo '</style>';
}

if( class_exists('WPSEO_Options') ) {

	add_filter( 'px_filter_amp_title', 'px_add_yoast_seo_title' );
	
	function px_add_yoast_seo_title() {
	
		return YoastSEO()->meta->for_current_page()->title;
	}

	add_filter( 'px_filter_amp_description', 'px_add_yoast_seo_description' );

	function px_add_yoast_seo_description() {

		global $post; 
		add_filter( 'excerpt_more', '__return_false' );
		$ysmfd = YoastSEO()->meta->for_current_page()->description;
		$yd = $ysmfd ? $ysmfd : get_the_excerpt();
		return $yd;
	}
}

if( class_exists( 'RankMath' ) ) {

	add_filter( 'px_filter_amp_description', 'px_add_rankms_description' );

	function px_add_rankms_description() {

		global $post;
		$desc = RankMath\Post::get_meta( 'description', $post->ID );
		if ( ! $desc ) {
			$desc = RankMath\Helper::get_settings( "titles.pt_{$post->post_type}_description" );
			if ( $desc ) {
				$desc = RankMath\Helper::replace_vars( $desc, $post );
			}
		}
		return $desc;
	}
}

add_action( 'wp', function(){
	if( remove_ldl() )
		remove_action( 'list_download_links', 'func_list_download_links' );
});

add_action( 'list_download_links', 'func_list_download_links_recaptcha', 10 );

function func_list_download_links_recaptcha() {
	global $post;

	$get_opt 	= get_query_var( 'opt' ) ? get_query_var( 'opt' ) : 0;
	$get_dl 	= get_query_var( 'download' ) ? get_query_var( 'download' ) : 0;
	$adl 		= get_option( 'appyn_download_links', null );

	if( $get_dl || $adl == 0 ) {

		if( remove_ldl() ) {
			$siv2 = appyn_options('recaptcha_v2_site');
			echo '<form action="" id="recaptcha_download_links" method="post">
			<div class="g-recaptcha" data-sitekey="'.$siv2.'" data-callback="recaptcha_callback"></div>
			<input type="hidden" name="action" value="px_recaptcha_download_links">
			<input type="hidden" name="post_id" value="'.$post->ID.'">
			<input type="hidden" name="get_opt" value="'.$get_opt.'">
			<input type="hidden" name="get_dl" value="'.$get_dl.'">
			<input type="hidden" id="rec_token" name="token" value="">
			'. wp_nonce_field( 'rdl_nonce', 'rdl_nonce', true, false ) .'
			<input type="submit" id="dasl" value="'.__('Mostrar enlaces', 'appyn').'" disabled>
			</form>';
		}
	}
}

add_action( 'wp_footer', function(){

	if( ! is_single() ) return;
	
	$sev2 = appyn_options( 'recaptcha_v2_secret' ); 
	$siv2 = appyn_options( 'recaptcha_v2_site' );
	
	if( $sev2 && $siv2 ) {
		echo '<script>
		var recaptcha_callback = function (token) {
			document.getElementById("dasl").removeAttribute("disabled");
			document.getElementById("rec_token").value = token;
		}
		</script>
		';
	}
}, 999);

add_action( 'template_redirect', function(){

	$adl = get_option( 'appyn_download_links', null );
	$get_dl = get_query_var( 'download' );

	if( $adl == 3 && is_single() && $get_dl ) {
		get_template_part( 'template-parts/template-download' );
		exit;
	}
});

add_action( 'wp_head', function(){

	if( is_single() ) {
		global $post;

		$rating = count_rating($post->ID);
		if( $rating['average'] > 0 ) {
			echo '<script type="text/javascript">var px_rating = '.json_encode($rating).';</script>';
		}
	}
});

add_action( 'px_data_app_single', 'pxdas_developer' );

function pxdas_developer() {
	global $post;
    $desarrollador = get_datos_info( 'desarrollador' ); 

	$output = '';
            
	if( !empty($desarrollador) ) {
		$output .= '<span><b>'.__( 'Desarrollador', 'appyn' ).'</b><br>';
		$output .= $desarrollador;
		$output .= '</span>';
	} else {
		$dev_terms = wp_get_post_terms( $post->ID, 'dev', array('fields' => 'all'));
		if( !empty($dev_terms) ) {
			$output .= '<span><b>'.__( 'Desarrollador', 'appyn' ).'</b><br>';
			$output .= '<a href="'.get_term_link($dev_terms[0]->term_id).'">'.$dev_terms[0]->name.'</a>';
			$output .= '</span>';
		}
	}

	echo $output;
}

add_action( 'px_data_app_single', 'pxdas_update', 10 );

function pxdas_update() {
    $fecha_actualizacion = get_datos_info( 'fecha_actualizacion' ); 
	
	echo (!empty($fecha_actualizacion)) ? '<span><b>'.__( 'Actualización', 'appyn' ).'</b><br>'.$fecha_actualizacion.'</span>' : '';
}

add_action( 'px_data_app_single', 'pxdas_size', 20 );

function pxdas_size() {
    $tamano = get_datos_info( 'tamano' );

	echo (!empty($tamano)) ? '<span><b>'.__( 'Tamaño', 'appyn' ).'</b><br>'.$tamano.'</span>' : '';
}

add_action( 'px_data_app_single', 'pxdas_version', 30 );

function pxdas_version() {
    $version = get_datos_info( 'version' );

	echo (!empty($version)) ? '<span><b>'.__( 'Versión', 'appyn' ).'</b><br>'.$version.'</span>' : ''; 
}

add_action( 'px_data_app_single', 'pxdas_requirements', 40 );

function pxdas_requirements() {
    $requerimientos = get_datos_info( 'requerimientos' ); 

	echo (!empty($requerimientos)) ? '<span><b>'.__( 'Requerimientos', 'appyn' ).'</b><br>'.$requerimientos.'</span>' : ''; 
}

add_action( 'px_data_app_single', 'pxdas_downloads', 50 );

function pxdas_downloads() {
    $descargas = get_datos_info( 'descargas' );

	echo (!empty($descargas)) ? '<span><b>'.__( 'Descargas', 'appyn' ).'</b><br>'.$descargas.'</span>' : '';
}

add_action( 'px_data_app_single', 'pxdas_getin_on', 60 );

function pxdas_getin_on() {
    $consiguelo = get_datos_info( 'consiguelo' );
	$imggp = get_store_app();
	
	echo (!empty($consiguelo)) ? '<span><b>'.__( 'Consíguelo en', 'appyn' ).'</b><br> <a href="'.$consiguelo.'" target="_blank">'.$imggp.'</a></span>' : ''; 
}

add_action( 'category_edit_form_fields', 'px_cat_icon_field', 10, 2 ); 

function px_cat_icon_field( $cat ) {  
	$catsapp = px_cats_app();
    $px_cat_icon = get_term_meta( $cat->term_id, "px_cat_icon", true );
?>  
	<tr class="form-field">  
		<th scope="row" valign="top">  
			<label for="px_cat_icon"><?php echo __( 'Ícono', 'appyn') ; ?></label>  
		</th>  
		<td>
			<ul class="icossss">
			<?php 
			echo '<li><label><input type="radio" name="px_cat_icon" id="px_cat_icon" value=""'.( empty($px_cat_icon) ? ' checked' : '').'><span style="font-size:12px;"> ' . __( 'Ninguno', 'appyn' ) . '</span></label></li>';
			echo '<li><label><input type="radio" name="px_cat_icon" id="px_cat_icon" value="default"'.( ($px_cat_icon == 'default') ? ' checked' : '').'><span class="cccc"></span><span style="font-size:12px;">' . __( 'Por defecto', 'appyn' ) . '</span></label></li>';
			foreach( $catsapp as $key => $c ) {
				$key = str_replace('_', '-', (strtolower($key)));
				echo '<li><label><input type="radio" name="px_cat_icon" id="px_cat_icon" value="'.$key.'"'.( ($px_cat_icon == $key) ? ' checked' : '').'><span class="cccc '.$key.'"></span><span style="font-size:12px;">' . $c . '</span></label></li>';
			}
			?>
			</ul>
		</td>  
	</tr>  
<?php  
}  

add_action( 'edited_category', 'px_cat_icon_field_save', 10, 2 ); 

function px_cat_icon_field_save( $term_id ) {  
    if ( isset( $_POST['px_cat_icon'] ) ) {  
        $t_id = $term_id;  
        $term_meta = get_term_meta( $t_id, "px_cat_icon", true );
        if ( isset( $_POST['px_cat_icon'] ) ){  
            $term_meta = $_POST['px_cat_icon'];  
        }  
        update_term_meta( $t_id, "px_cat_icon", $term_meta );
    }  
}

add_shortcode( 'px_short_download_links', 'px_short_download_links_func' );

function px_short_download_links_func( ) {
    ob_start();
    do_action( 'list_download_links' );
	return ob_get_clean();
}

add_action( 'wp_head', 'px_add_css' );

function px_add_css( $echo = false ) {

	$css = '<style>';

	if( appyn_options( 'sticky_header' ) ) {

		$css .= '
		#header {
			position: relative;
		}
		#subheader.np {
			padding-top: 15px;
		}
		#subheader {
			padding-top: 20px;
		}';
	}

	if( appyn_options( 'title_2_lines' ) ) {

		$css .= '
		.section .bav1 .title, 
		.rlat .bav1 .title,
		.baps .bav2 .title {
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			height: 40px;
			white-space: normal;
		}';
	}

	if( appyn_options( 'design_rounded' ) ) {

		$css .= '
		.sb_search[type=text],
		.sb_submit[type=submit],
		.ratingBoxMovil button,
		.widget.widget_tag_cloud a,
		#main-site .error404 form input[type=text] {
			border-radius: 50px;
		}
		#header nav .menu>li>a::before,
		#header nav ul li.current-menu-item a,
		.section .bav2 a,
		.bloque-imagen,
		.section .bav1 a,
		.section .bav1 a::before,
		.app-s .buttond,
		.buttond,
		#list-downloadlinks li a.dapk_b,
		.app-s .s2 .meta-cats a,
		.app-s .readmore,
		#comments input[type=submit],
		.app-s .box h2.box-title::after,
		.app-p .box h2.box-title::after,
		.app-p .box, 
		.app-s .box, 
		.section.blog, 
		.single-product .product,
		.entry.bx-info-install,
		.widget,
		.widget .wp-block-search .wp-block-search__input,
		.widget .search-form input[type=submit], 
		.widget .wp-block-search .wp-block-search__button,
		.entry .wp-caption a, 
		.section .bav2 img, 
		.section.blog .bloques li a .bloque-imagen img,
		#box-report input[type=submit],
		#box-report .box-content,
		.botones_sociales span,
		.botones_sociales a,
		.tags a,
		#dasl,
		#comments input[type="text"], 
		#comments input[type="email"], 
		#comments input[type="url"], 
		#comments textarea,
		.app-s .box-data-app,
		.bloque-blog,
		.bloque-blog .bloque-imagen img,
		.gsc-control-cse,
		.app-s .s2 .amount-app li,
		.b-type {
			border-radius: 20px;
		}
		.section .bav1 .px-postmeta,
		#subheader.np {
			border-radius: 0 0 20px 20px;
		}
		.pagination .current, .pagination a {
			padding: 9px 15px;
			border-radius: 50px;
		}
		.section a.more {
			border-radius: 50px;
		}
		.app-s .image-single {
			border-radius: 25px;
		}
		#list-downloadlinks li a.dapk_b {
			padding-left: 56px;
			padding-right: 25px;
		}
		#slideimages .px-carousel-item img,
		#box-report textarea {
			border-radius: 10px;
		}
		table {
			border-radius: 20px;
			overflow: hidden;
		}
		#box-report .close-report {
			padding: 10px;
		}
		.amp-carousel-button {
			border-radius: 50px;
		}
		.pxccat, .widget > div:not(.wp-block-group) > ul {
			border-radius: 0 0 20px 20px;
			overflow: hidden;
		}
		.buttond i::before,
		.app-s .bx-download ul li a i::before {
			position: relative;
			left: 2px;
		}';
	}
	
	if( appyn_options( 'og_sidebar' ) ) {
			
		$css .= '
		#slidehome .px-carousel-item {
			width: 50%;
		}';
	}

	if( appyn_options( 'sticky_header' ) ) {
		
		$css .= '
		.pxtd {
			padding-top: 40px;
		}';
	}

	$css .= '</style>';

	$a = str_replace(array("\n", "\t", "  "), '', $css);

	if( ! $echo ) {
		echo $a;
	} else{
		return $a;
	}
}

add_action( 'wp_footer', 'px_automatic_results', 99 );

function px_automatic_results(){
	if( appyn_options( 'search_google_active' ) || appyn_options( 'automatic_results' ) ) {
		echo '<script>(function($) {
			$("#searchBox input[type=text]").off("keyup");
		})(jQuery);</script>';
	}
}

add_action( 'init', 'redirect_download_pages_amp' );

function redirect_download_pages_amp() {
	if( get_query_var( 'download' ) && is_amp_px() ) { 
		wp_redirect(remove_query_var('amp'));
		exit;
	}
}

add_action( 'wp', 'redirect_download' );

function redirect_download() {
	global $wp, $post;

	if( ! is_single() ) return;

	if( ! get_query_var('download') ) return;

	$current_url = home_url(add_query_arg(array($_GET), $wp->request));
	$l = array_filter(explode('/', $current_url));

	if( appyn_options( 'download_links_permalinks' ) == 1 ) {
		
		$option = get_datos_download()['option'];

		if( end($l) == "download" && ($option != "links" && $option != "direct-link") ) {
			wp_redirect(dirname($current_url));
			exit;
		}

		$pu = parse_url($current_url);

		if( ! isset($pu['query']) ) return;

		if( $pu['query'] == 'download=links' || $pu['query'] == 'download=redirect' ) {
			wp_redirect(remove_query_arg('download').'/download/');
			exit;
		}
	} else {
		if( end($l) == "download" ) {
			wp_redirect(add_query_arg('download', 'links', dirname($current_url)));
			exit;
		}
	}
}

add_action( 'wp_head', 'px_css_bottom_menu' );

function px_css_bottom_menu() {

	if( ! appyn_options( 'bottom_menu' ) ) return;

	echo '<style>
	#px-bottom-menu { 
		display:none; 
	}
	@media (max-width: 640px) {
		#px-bottom-menu {
			display: block;
			position: fixed;
			bottom: 0;
			width: 100%;
			z-index: 999;
			background: #1d222d;
			box-shadow: 0 -5px 10px rgba(0,0,0,.1);
			border-top: 2px solid #1bbc9b;
		}
		#px-bottom-menu ul {
			display: flex;
			justify-content: center;
		}
		#px-bottom-menu ul li {
			flex: 1;
			display: flex;
			align-items: end;
		}
		#px-bottom-menu ul li a {
			text-align: center;
			padding: 9px 5px;
			color: #FFF;
			display: block;
			width: 100%;
			font-size: 12px;
			border-bottom: 4px solid transparent;
		}
		#px-bottom-menu ul li a i {
			font-size: 23px;
			display: block;
			margin: 2px 0 5px;
		}
		#footer {
			padding-bottom: 65px;
		}
	}
	</style>';
}

add_action( 'wp_footer', 'px_html_bottom_menu' );
add_action( 'wp_footer_amp', 'px_html_bottom_menu' );

function px_html_bottom_menu() {

	if( ! appyn_options( 'bottom_menu' )  ) return;

	echo '<div id="px-bottom-menu">'.wp_nav_menu(array('theme_location' => 'menu-fixed-bottom', 'show_home' => false, 'container' => '', 'echo' => false) ).'</div>';
	
}

add_action( 'wp_head', 'px_width_page', 1 );

function px_width_page() {

	if( ! appyn_options( 'width_page' )  ) return;

	echo '<style>
		.full-width .container,
		.full-width #subheader.np {
			width: 100%;
		}
		@media (min-width: 1280px) {
			.full-width .rlat .bav1 {
				width: 12.5%;
			}
		}
		.full-width #slideimages .px-carousel-item img {
			max-height: 300px;
		}
		.full-width .full-width .app-s .data-app span {
			width: 25%;
		}
	</style>';
}

add_action( 'px_breadcrumbs', 'px_tag_breadcrumbs' );
add_action( 'px_social_buttons', 'px_tag_social_buttons' );

add_action( 'box_single_app', 'pt' );

function pt() {
	global $post;
	$get_download = get_query_var( 'download' );

	echo '<div class="box'.(($get_download == "true" || $get_download == "redirect" || $get_download == "links") ? ' box-download': '').'">';
		get_template_part( 'template-parts/single-infoapp' );
		do_action( 'px_social_buttons' );
	echo '</div>';

	echo do_action( 'box_report' );
	echo px_ads( 'ads_single_top' ); 
	echo do_action( 'seccion_cajas' );
}

add_action( 'wp_head', function(){
	
	$aprpc = appyn_options( 'apps_per_row_pc', 6 );
	$aprpm = appyn_options( 'apps_per_row_movil', 2 );
	
	echo '<style>
	:root {
		--columns: '.$aprpc.';
	}
	.section .baps .bav {
		width: calc(100% / '.$aprpc.');
	}

	@media( max-width: 1100px) {
		.section .baps[data-cols="8"] .bav2,
		.section .baps[data-cols="7"] .bav2,
		.section .baps[data-cols="6"] .bav2,
		.section .baps[data-cols="5"] .bav2 {
			width: calc(100% / 4);
		}
	}

	@media( max-width: 950px) {
		.section .baps[data-cols="8"] .bav,
		.section .baps[data-cols="7"] .bav,
		.section .baps[data-cols="6"] .bav {
			width: calc(100% / 4);
		}
		.section .baps[data-cols="8"] .bav2,
		.section .baps[data-cols="7"] .bav2,
		.section .baps[data-cols="6"] .bav2 {
			width: calc(100% / 3);
		}
	}
	@media( max-width: 750px) {
		.section .baps[data-cols="8"] .bav2,
		.section .baps[data-cols="7"] .bav2,
		.section .baps[data-cols="6"] .bav2,
		.section .baps[data-cols="5"] .bav2,
		.section .baps[data-cols="6"] .bav {
			width: calc(100% / 3);
		}
	}';

	if( $aprpm == 3 ) {
		echo '
		@media( max-width: 650px) {
			.section .baps[data-cols="8"] .bav,
			.section .baps[data-cols="7"] .bav,
			.section .baps[data-cols="6"] .bav,
			.section .baps[data-cols="5"] .bav,
			.section .baps[data-cols="4"] .bav,
			.section .baps[data-cols="3"] .bav {
				width: calc(100% / 3);
			}
		}';
	} else {
		echo '
		@media( max-width: 650px) {
			.section .baps[data-cols="7"] .bav {
				width: calc(100% / 3);
			}
			.section .baps[data-cols="5"] .bav,
			.section .baps[data-cols="4"] .bav,
			.section .baps[data-cols="3"] .bav {
				width: calc(100% / 2);
			}
		}

		@media( max-width: 480px) {
			.section .baps[data-cols="8"] .bav,
			.section .baps[data-cols="7"] .bav,
			.section .baps[data-cols="6"] .bav,
			.section .baps[data-cols="5"] .bav,
			.section .baps[data-cols="4"] .bav,
			.section .baps[data-cols="3"] .bav {
				width: calc(100% / '.$aprpm.');
			}
		}';
	}
	echo '
	</style>';
}, 1);

add_action( 'in_widget_form', 'px_widget_add_option', 10, 3 );

function px_widget_add_option( $widget, $return, $instance ) {

	if( $widget->id_base == 'fixed_widget' ) return;
    ?>
    <p>
        <label>
            <input type="checkbox" name="<?php echo $widget->get_field_name( 'fixed_widget' ); ?>" value="1" <?php checked( isset( $instance['fixed_widget'] ) ? $instance['fixed_widget'] : '', '1' ); ?>> <?php echo __( 'Widget Fijado', 'appyn' ); ?> 
        </label>
    </p>
    <?php
}

add_filter( 'widget_update_callback', 'px_save_custom_widget_option', 10, 3 );

function px_save_custom_widget_option( $instance, $new_instance, $old_instance ) {
    $instance['fixed_widget'] = ! empty( $new_instance['fixed_widget'] ) ? 1 : 0;
    return $instance;
}

add_filter( 'dynamic_sidebar_params', 'add_custom_class_to_widgets' );

function add_custom_class_to_widgets( $params ) {
    global $wp_registered_widgets;
    $widget_id = $params[0]['widget_id'];
    $widget_obj = $wp_registered_widgets[$widget_id];
    $widget_opt = get_option($widget_obj['callback'][0]->option_name);
    $widget_number = $widget_obj['params'][0]['number'];
    $widget_custom_option = isset($widget_opt[$widget_number]['fixed_widget']) ? $widget_opt[$widget_number]['fixed_widget'] : '';
    if ( $widget_custom_option ) {
        $params[0]['before_widget'] = str_replace( 'class="', 'class="widget_fixed_widget wfm ', $params[0]['before_widget'] );
    }
    return $params;
}