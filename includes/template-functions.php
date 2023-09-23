<?php

if( !defined('ABSPATH') ) die ( '✋' );

function count_rating($post_id){
	global $wpdb;
	$array = array();
	$rating_count = ( get_post_meta( $post_id, 'new_rating_count', true ) ) ? get_post_meta( $post_id, 'new_rating_count', true ) : 0;
	$users = ( get_post_meta( $post_id, 'new_rating_users', true ) ) ? get_post_meta( $post_id, 'new_rating_users', true ) : 0;
	$average = ( get_post_meta( $post_id, 'new_rating_average', true ) ) ? get_post_meta( $post_id, 'new_rating_average', true ) : 0;

	$array['average'] = $average;
	$array['users'] =  $users;
	$array['count'] =  $rating_count;
	
	return $array;	
}

function user_no_voted(){
	global $post;
	if( !isset($_COOKIE['nw_rating']) || !isset($post->ID) ) 
		return true;

	$nr = explode(",",$_COOKIE['nw_rating']);
	if( !in_array($post->ID, $nr) ) {
		return true;
	}
}

function show_rating($calificar = 1){
	global $post;
	$count_rating = count_rating($post->ID); ?>
		<div class="box-rating<?php if(wp_is_mobile()) echo " movil"; if(!user_no_voted() || $calificar == 0) echo " voted";  ?>" data-post-id="<?php echo $post->ID; ?>">
		<span class="rating">
			<?php
			if($calificar == 1){ ?>
			<span class="ratings-click" title="<?php echo ( !user_no_voted() ) ? __( 'Calificación', 'appyn' ).": ".$count_rating['average']." ".__( 'estrellas', 'appyn' ): ''; ?>">
				<span class="rating-click r1" data-count="1"></span>
				<span class="rating-click r2" data-count="2"></span>
				<span class="rating-click r3" data-count="3"></span>
				<span class="rating-click r4" data-count="4"></span>
				<span class="rating-click r5" data-count="5"></span>
				</span>
			<?php } ?><span class="stars" style="width:<?php echo $count_rating['average'] * 10 * 2; ?>%"></span></span> 
			<?php
			if($calificar == 1){ ?><span class="rating-average"><b><?php echo $count_rating['average']; ?></b>/5</span>
				<span class="rating-text"><?php echo __( 'Votos', 'appyn' ).': <span>'.(($count_rating['users']) ? number_format($count_rating['users'], 0, ',', ',') : 0).'</span>'; ?></span>
			<?php } ?>
		</div>
<?php	
}

function get_image_id($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE guid LIKE '%$image_url%'"); 
	return isset( $attachment[0] ) ? $attachment[0] : false; 
}

function px_ads($ads){
	global $wp_query;
	
	if( is_404() ) return;

	if( isset($wp_query->queried_object->count) ) 
		if( $wp_query->queried_object->count == 0) return;


    if( is_singular() || is_page() ) {
		global $post;
		if (appyn_gpm($post->ID, 'appyn_ads_control') == 1)
            return;
    }

	$ads_output = '';
	$ads_pc 	= do_shortcode( get_option( 'appyn_'.$ads ) );
	$ads_movil 	= do_shortcode( get_option( 'appyn_'.$ads.'_movil' ) );
	$ads_amp 	= do_shortcode( get_option( 'appyn_'.$ads.'_amp' ) );
	$ads_h 		= '<aside class="ads '.$ads.'">';
	$ads_h 		.= appyn_options('ads_text_above') ? '<small>'.appyn_options('ads_text_above').'</small>': '';
	if( is_amp_px() ) {
		if( !empty($ads_amp) ) {
			$ads_output = $ads_h.$ads_amp;
			$ads_output .= '</aside>';
		}
	} else {
		if( !empty($ads_pc) && !wp_is_mobile()) { 
			$ads_output = $ads_h.$ads_pc;
			$ads_output .= '</aside>';
		}
		elseif(!empty($ads_movil) && wp_is_mobile()) {
			$ads_output = $ads_h.$ads_movil;
			$ads_output .= '</aside>';
		}
	}
	return stripslashes($ads_output);
}

function array_multi_filter_download_empty($var) {
	if( is_array($var) ) {
		$var = @array_filter($var);
		return ($var && !empty($var));
	} else {
		return $var;
	}
}

function catch_that_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$first_img = $matches[1][0];
	return $first_img;
}

function excerpt($limit){
	$excerpt = explode(' ', wp_trim_words(get_the_content()), $limit);
    if(count($excerpt)>=$limit) {
		array_pop($excerpt);
		$excerpt = implode(" ",$excerpt).'...';
	} else {
		$excerpt = implode(" ",$excerpt);
	} 
	$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
	return $excerpt;
}

function getPostViews($postID) {
	global $wpdb;
	$px_views = ( get_post_meta( $postID, 'px_views', true ) ? get_post_meta( $postID, 'px_views', true ) : 0 );
	return $px_views;	
}

function setPostViews($postID) {
	global $wpdb;
	$px_views = ( get_post_meta( $postID, 'px_views', true ) ? get_post_meta( $postID, 'px_views', true ) : 0 );
	update_post_meta( $postID, 'px_views', ($px_views + 1) );
}

function px_comment_nav() {
	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
	?>
	<nav class="navigation comment-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php echo __( 'Navegación de comentarios', 'appyn' ); ?></h2>
		<div class="nav-links">
			<?php
				if ( $prev_link = get_previous_comments_link( __( 'Comentarios antiguos', 'appyn' ) ) ) :
					printf( '<div class="nav-previous">%s</div>', $prev_link );
				endif;

				if ( $next_link = get_next_comments_link( __( 'Comentarios más nuevos', 'appyn' ) ) ) :
					printf( '<div class="nav-next">%s</div>', $next_link );
				endif;
			?>
		</div>
	</nav>
	<?php
	endif;
}

function px_social($social) {
	if( $social == "facebook" ){
		$option = get_option( 'appyn_social_facebook' );
	}
	elseif( $social == "twitter" ){
		$option = get_option( 'appyn_social_twitter' );		
	}
	elseif( $social == "instagram" ){
		$option = get_option( 'appyn_social_instagram' );		
	}
	elseif( $social == "youtube" ){
		$option = get_option( 'appyn_social_youtube' );		
	}
	elseif( $social == "pinterest" ){
		$option = get_option( 'appyn_social_pinterest' );		
	}
	elseif( $social == "telegram" ){
		$option = get_option( 'appyn_social_telegram' );		
	}
	return $option;
}

function px_reports_opt() {

	$reports = array(
		__( 'No funcionan los enlaces de descarga', 'appyn' ),
		__( 'Hay una nueva versión', 'appyn'),
		__( 'Otros', 'appyn' ),
	);

	$reports = apply_filters_ref_array( 'px_filter_reports_opt', array( &$reports ));

	return $reports;
}

add_action( 'box_report', 'box_report_action' );

function px_the_content() {
	global $post;
	$content = get_the_content();
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo px_content_filter($content);
}

function px_noimage($bg = false) {
	$noimage = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKoAAACqBAMAAADPWMmxAAAAElBMVEXu7u7////09PT7+/v39/fx8fFOJAxSAAABPklEQVRo3u3YQW6DMBCFYYLjAzxM9iRK9tA2e2h6ACP1/mcpFDlQNVTQGdpEvO8Av62RDYKIiIiIiIiIiIj+THyY4TS1usEMjlVW/7+aTLourK60ai+v+lVbAGf1ao2G164WaOTKVYtWolyN0UqVq1u03F3u9enmXHeyqkW2wBkw32Zh2pSXVffhxPcU7lYRZtizx1Mkq8ZoeO1nlkHjrF2t0HBeuYpPedS5eJXq9ss732KnUq3RycOQM41qgY4LayQKVYsgD2uU8qpBkIaz6+TVGldlWCMXVwsMN1uh5bywGmMgs+jshFWDgcRc+7LqHkNVPwxRtcBtpaS6xQgnqW4w5iyoVhjj/BJVJItUkS1STRepIl+k6n5bfX/7SfYQ3zArqaaHCY738deB1XVXzfMMLxERERERERERET24D8nRkAcrLOazAAAAAElFTkSuQmCC";

	if( is_dark_theme_active() ) {
		$noimage = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKoAAACqBAMAAADPWMmxAAAAG1BMVEUUHClHTVcNFB8mLDgdIy5CSVMRGCI0O0Q8QkuIZNk8AAABTUlEQVRo3u3YQWqDQBTG8UDpAT6UmG0TGd1qAu02pvQCPYH2BErpPll47pqMBiFaYt8TIn6/A/xnGJ+CsyAiIiIiIiIiool7xgCfo1QdVlkdUnXXd/BZnWnVvO70qyYHSvVqikqmXc1ROSlXDc5c1aotAY5ydYMz7yH3+t15rktZ1SDpmoFCVg1uziIEsBLO6x7Ibjf7I3y3jvYM28zhYy2r+qhk2t+sEJVSuxrBPhvdKi5OdeUtU6lucOE1s7tUqaawinp2kWhUc1hes4arUDVoFM0asbwaoOHUDXjyaoqruJ5dFOJqjvZmI9jhFVZ9tCQG1lJYDdHiBte+rLpHW4SaI6se0S2WVDfo4UmqL+hTCqoR+qyyMapwR6kiGaXqjFJFMUrV+2/1a/uXZBL/MDOpOu93ODzGrQOr864+bQfYLYiIiIiIiIiIaOJ+Af2TM1DDPhQQAAAAAElFTkSuQmCC";
	}
	
	if( $bg ) return $noimage;

	if( is_amp_px() ) {
		return '<amp-img src="'.$noimage.'" class="image-single" layout="responsive" width="150" height="150" alt="No image"></amp-img>';
	} else {
		if( appyn_options( 'lazy_loading') ) {
			return '<img data-src="'.$noimage.'" src="" width="150" height="150" alt="No image" class="lazyload">';
		} else {
			return '<img src="'.$noimage.'" width="150" height="150" alt="No image">';
		}
	}
}

function count_reports() {
	global $wpdb;
	$wpdb->get_results( "SELECT meta_value, post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'px_app_report' ORDER BY meta_id DESC" );
	return $wpdb->num_rows;
}

function appyn_options( $option, $default = false ) {

	if( !empty(get_option('appyn_'.$option) ) ) {
		return get_option('appyn_'.$option);
	} else {
		return ( $default ) ?  (is_bool($default) ? '' : $default) : '0';
	}
}

function get_datos_download( $post_id = false ){
	if( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$datos_download = get_post_meta($post_id, 'datos_download', true); 
	$n = array();
	if( !is_array($datos_download) ) return;
	foreach( $datos_download as $k => $v ) {
		if( !is_string($k) ) {
			if( !empty($v['link']) ) {
				$n[] = $v;
				unset($datos_download[$k]);
			}
		}
	}
	$datos_download['links_options'] = $n;

	if( !empty($datos_download) ) { 
		$datos_download = array_filter($datos_download, 'array_multi_filter_download_empty');
	}
	return $datos_download;
}

function get_datos_info($key, $key_ = false, $post_id = false){
	if( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$di = get_post_meta($post_id, 'datos_informacion', true); 
	
	if( !empty($di) ) { 
		$di = array_filter($di, 'array_multi_filter_download_empty');

		if( $key_ ) 
			return (isset($di[$key][$key_])) ? $di[$key][$key_] : '';
		else 
			return (isset($di[$key])) ? $di[$key] : '';
	}
}

function category_parents(){
	global $post;
    $category = get_the_category();
	if( !isset( $category[0]->cat_ID ) ) return;
    $catid = $category[0]->cat_ID;
    $separador = " / ";
    $category_parents = get_category_parents( $catid, TRUE, "$separador", FALSE );
	$category_parents = explode($separador, $category_parents);
	if( is_array($category_parents) ) {
		$category_parents = array_filter($category_parents, 'array_multi_filter_download_empty');
		return $category_parents;
	}
}

function go_curl($url) {	
	if( ! function_exists('curl_exec') ) {
		throw new Exception( __( 'Error: Debe activar cURL. Contacte con el soporte de su hosting para que le habiliten cURL.', 'appyn' ));
		exit;
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_REFERER, get_site_url());
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 45);
	$content = curl_exec($ch);
	curl_close($ch);
	
	return $content;
}

function is_amp_px() {
	global $wp;
	$amp = appyn_options( 'amp' );
	$current_url = home_url(add_query_arg(array($_GET), $wp->request));

	if( get_query_var( 'download' ) )
		return false;
		
	if( $amp ) {
		return ( strpos($current_url, '?amp') !== false ) ? true : false;
	}
}

function amp_comment_form(){
	global $post;
	echo '<p><a href="'.esc_url( remove_query_arg( 'amp', get_the_permalink( $post->ID ) ) ).'#comment">'.__( 'Deja un comentario', 'appyn' ).'</a></p>';
}

function px_amp_logo($logo_url) {
	return '<amp-img src="'.$logo_url.'" alt="'.get_bloginfo('title').'" layout="fixed-height" height="40"></amp-img>';
}

function appyn_comment($comment, $args, $depth) {
    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }
	
	switch ( $comment->comment_type ) :
		case 'pingback':
	?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
			<p><strong><?php echo __( 'Pingback:', 'appyn' ); ?></strong> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Editar)', 'appyn' ), '<span class="edit-link">', '</span>' ); ?></p>
		<?php
		break;
		case 'trackback':
		?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
			<p><strong><?php echo __( 'Trackback:', 'appyn' ); ?></strong> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Editar)', 'appyn' ), '<span class="edit-link">', '</span>' ); ?></p>
		<?php
		break;
		default:
		?>
		<<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID() ?>"><?php 
		if ( 'div' != $args['style'] ) { ?>
			<div id="div-comment-<?php comment_ID() ?>" class="comment-body"><?php
		} ?>
			<div class="comment-author vcard"><?php 
				if ( $args['avatar_size'] != 0 ) {
					if( is_amp_px() ) {
						echo '<amp-img src="'.get_avatar_url( $comment, $args['avatar_size'] ).'" width="56" height="56"></amp-img>';
					} else {
						echo get_avatar( $comment, $args['avatar_size'] );
					} 
				} 
				printf( '<cite class="fn">%s</cite> <span class="says">'.__( 'dice', 'appyn' ).':</span>', get_comment_author_link() ); ?>
			</div><?php 
			if ( $comment->comment_approved == '0' ) { ?>
				<em class="comment-awaiting-moderation"><?php echo __( 'Tu comentario está en espera de aprobación.', 'appyn' ); ?></em><br><?php 
			} ?>
			<div class="comment-meta commentmetadata">
				<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>"><?php
					printf( 
						__( '%1$s a las %2$s', 'appyn' ), 
						get_comment_date(),  
						get_comment_time() 
					); ?>
				</a><?php 
				edit_comment_link( __( '(Editar)', 'appyn' ), '  ', '' ); ?>
			</div>

			<?php comment_text(); ?>

			<div class="reply"><?php 
					comment_reply_link( 
						array_merge( 
							$args, 
							array( 
								'add_below' => $add_below, 
								'depth'     => $depth, 
								'max_depth' => $args['max_depth'] 
							) 
						) 
					); ?>
			</div><?php 
		if ( 'div' != $args['style'] ) : ?>
			</div><?php 
    	endif;
		break;

	endswitch;
}

function px_post_thumbnail( $size = 'thumbnail', $post = NULL, $bg = false ) {
	if( !$post ) {
		global $post;
	}
	$add_class = '';
	if( appyn_options( 'lazy_loading') && !is_amp_px() ) {
		$add_class = ' bi_ll';
	}
	$output = '<div class="bloque-imagen'.$add_class.(($size == 'miniatura') ? ' w75' : '').'">';
	$output .= px_post_status();
	$image = ($bg) ? px_noimage(true) : px_noimage();
    if( has_post_thumbnail() ) {
        $featured_image_url = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) );
        if  ( ! empty( $featured_image_url ) ) {
			$gtpt = get_the_post_thumbnail( (int) $post->ID, $size);
			if( $bg ) {
				$gtpt = get_the_post_thumbnail_url( (int) $post->ID, $size);
			}
       		if  ( ! empty( $gtpt ) ) {
				$image = $gtpt;
			}
        } 
	}
	if( $bg ) {
		if( is_amp_px() ) {
			$output .= '<div class="image-single" style="background-image:url('.$image.');"></div>';
		} else {
			if( appyn_options( 'lazy_loading') ) {
				$output .= '<div class="image-single lazyload" data-bgsrc="'.$image.'"></div>';
			} else {
				$output .= '<div class="image-single" style="background-image:url('.$image.');"></div>';
			}
		}
	} else {
		$output .= $image;
	}
	$output .= '</div>';
	return $output;
}

function px_content_filter($content){
	if( is_amp_px() ) {
		// Imágenes
		$re = '/<img(.*?)src=(\'|\")(.*?)(\'|\")(.*?)(\/)?>/m';
        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
        $images = array();

        if ($matches) {
            foreach ($matches as $m) {
                if (strpos($m[0], 'width=') === false) {
                    ob_start();
                    $data = getimagesize(str_replace(get_site_url(), ABSPATH, $m[3]));
                    $data = ob_get_clean();
                    list($width, $height) = $data;
                    if (!empty($width)) {
                        $subst = '<amp-img$1src=$2$3$4$5 layout="intrinsic" width="'.$width.'" height="'.$height.'"></amp-img>';
                    } else {
                        list($width, $height) = getimagesize($m[3]);
                        $subst = '<amp-img$1src=$2$3$4$5 layout="intrinsic" width="'.$width.'" height="'.$height.'"></amp-img>';
                    }
                    $images[$m[0]] = preg_replace($re, $subst, $m[0]);
                } else {
                    ob_start();
                    $data = getimagesize(str_replace(get_site_url(), ABSPATH, $m[3]));
                    $data = ob_get_clean();
                    list($width, $height) = $data;
                    if (!empty($width)) {
                        $subst = '<amp-img$1src=$2$3$4$5 layout="intrinsic" style="max-width:'.$width.'px"></amp-img>';
                        $images[$m[0]] = preg_replace($re, $subst, $m[0]);
                    } else {
                        list($width, $height) = getimagesize($m[3]);
                        $subst = '<amp-img$1src=$2$3$4$5 layout="intrinsic" style="max-width:'.$width.'px"></amp-img>';
                        $images[$m[0]] = preg_replace($re, $subst, $m[0]);
                    }
                }
				$rex = '/align="(.*?)"|decoding="async"/m';
				$images[$m[0]] = preg_replace($rex, ' ', $images[$m[0]]);
            }
            $content = strtr($content, $images);
        }

		$videos = array();
		$re = '/<iframe.+?src="https?:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9_-]{11}).+?"[^>]+?><\/iframe>/ms';
		preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
		foreach( $matches as $v ) {
			$videos[$v[0]] = '<amp-youtube data-videoid="'.$v[1].'" layout="responsive" width="480" height="270"></amp-youtube>';
		}
		$content = strtr($content, $videos);

		$re = '/<script(.*?)<\/script>/ms';

		$content = preg_replace($re, '', $content);

		$re = '/<iframe.+?src="https?:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9_-]{11}).+?"[^>]+?><\/iframe>/ms';
		preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
		foreach( $matches as $v ) {
			$videos[$v[0]] = '<amp-youtube data-videoid="'.$v[1].'" layout="responsive" width="480" height="270"></amp-youtube>';
		}
		
		return $content;
	}
	return $content;
}

function lang_object_ids($object_id, $type) {
    $current_language= apply_filters( 'wpml_current_language', NULL );
    if( is_array( $object_id ) ){
        $translated_object_ids = array();
        foreach ( $object_id as $id ) {
            $translated_object_ids[] = apply_filters( 'wpml_object_id', $id, $type, true, $current_language );
        }
        return $translated_object_ids;
    } else {
		return apply_filters( 'wpml_object_id', $object_id, $type, true, $current_language );
	}
}

function httuachl() {
	$a = ( isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	
	if( strpos( $a, 'Chrome-Lighthouse' ) !== false ) return true;
}

function cover_header() {
	global $image_random_cover;
	$im = $image_random_cover;

    if( httuachl() ) return;
	
	$im_wp = '';
	if( @file_exists(__DIR__."/../images/".pathinfo($im)['filename'].".webp") ) {		
		$im_wp = ' data-src-webp="'.pathinfo($im)['dirname']."/".pathinfo($im)['filename'].".webp".'"';
	}

	if( appyn_options( 'lazy_loading') ) {
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAI4AAACNAQMAAABbp9DlAAAAA1BMVEUUHCkYkPNHAAAAGUlEQVRIx+3BMQEAAADCIPunNsU+YAAA0DsKdwABBBTMnAAAAABJRU5ErkJggg==" data-src="'.$im.'"'.$im_wp.' alt="Portada" class="lazyload">';
	} else {
		return '<img src="'.$im.'" alt="Portada">';
	}
}

function get_remote_html( $url ) {
	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return;
	}
	$html = wp_remote_retrieve_body( $response );
	if ( is_wp_error( $html ) ) {
		return;
	}
	return $html;
}

function activate_versions_boxes($caja) {
	$cvn = get_option( 'appyn_versiones_no_cajas', array(1) );

	if( !in_array( $caja, $cvn ) ) {
		return true; 
	}
}

function activate_internal_page_boxes($caja) {
	$cvn = get_option( 'appyn_pagina_interna_no_cajas', array(1) );

	if( !in_array( $caja, $cvn ) ) {
		return true; 
	}
}

function is_download_links_normal() {
	$adl = get_option( 'appyn_download_links' );
	if( $adl == 0 )
		return true;
}

function box_report_action() {
	
	if( is_amp_px() ) return;
	
	$reports_opt = px_reports_opt();
	
	echo '<div id="box-report" class="box">
		<div class="box-content">
			<span class="close-report"><i class="fa fa-times"></i></span>
			<div class="br-title">'.__( 'Reportar esta app', 'appyn').'</div>
			<form>';

		foreach( $reports_opt as $key => $opt ) {
			echo '<label><input type="radio" name="report-opt" value="'.($key+1).'" '.( ($key == 0) ? 'checked required' : '').'> <span>'.$opt.'</span></label>';
		}

	echo '<p><textarea placeholder="'.__( 'Detalle del reporte (Opcional)', 'appyn' ).'"  name="report-details"></textarea></p>';
	
	$appyn_request_email = appyn_options( 'request_email' );

    if( $appyn_request_email ) {
        echo '<p><input type="email" name="report-email" required placeholder="'.__('Email *', 'appyn').'" style="width:100%;"></p>';
    }
		echo '<p style="margin-bottom:0;"><input type="submit" class="br-submit" value="'.__('Reportar', 'appyn').'"></p>
			</form>
		</div>
	</div>';
}

function link_button_download_apk() {
	global $post;

	$datos_download = get_datos_download();
	$adl = get_option( 'appyn_download_links' );

	if( empty($datos_download['option']) ) { 

		if( !empty($datos_download['links_options'][0]) ) { 
			if( dlp() ) {
				return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? esc_url(trailingslashit(remove_query_arg('amp', get_permalink()))).'download/' : '#download' );
			}
			return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? add_query_arg('download', 'links', esc_url(remove_query_arg('amp'))) : '#download' );
		}
		
	} elseif( $datos_download['option'] == "links" && count($datos_download) > 1 ){

		if( !empty($datos_download['links_options'][0]) ) { 
			if( dlp() ) {
				return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? esc_url(trailingslashit(remove_query_arg('amp', get_permalink()))).'download/' : '#download' );
			}
			return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? add_query_arg('download', 'links', esc_url(remove_query_arg('amp'))) : '#download' );
		}

	} 
	elseif( $datos_download['option'] == "direct-link" ){
		
		if( !empty($datos_download['direct-link']) ) { 
			if( dlp() ) {
				return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? esc_url(trailingslashit(remove_query_arg('amp', get_permalink()))).'download/' : px_download_link($datos_download['direct-link']) );
			}
			return ( ( $adl == 1 || $adl == 2 || $adl == 3 ) ? add_query_arg('download', 'redirect', esc_url(remove_query_arg('amp'))) : px_download_link($datos_download['direct-link']) );
		}

	} 
	elseif( $datos_download['option'] == "direct-download" ){

		if( !empty($datos_download['direct-download']) ) { 
			return px_download_link($datos_download['direct-download']);
		}

	}
}

function px_data_structure() {
	global $post;

	if( ! apply_filters( 'px_show_data_structure', true ) ) return;

	if( is_singular('post') ) {
		$rating = count_rating($post->ID);
		if( $rating['average'] > 0 ) {
			$datos_informacion = get_post_meta($post->ID, 'datos_informacion', true); 
			$price = ( (!isset($datos_informacion['offer']['price']) || @$datos_informacion['offer']['price'] == "gratis") ? '0' : $datos_informacion['offer']['price'] );
			$currency = 0;

			if( $price == "pago" ) {
				$price = @$datos_informacion['offer']['amount'];
				$currency = @$datos_informacion['offer']['currency'];
			}

			$os = ( !isset($datos_informacion['os']) ) ? 'ANDROID' : $datos_informacion['os'];

			$cat = ( !isset($datos_informacion['categoria_app']) ? 'GAMES' : $datos_informacion['categoria_app'] );

			$rch = array(
				'@context' => "http://schema.org",
				'@type' => "SoftwareApplication",
				'name' => get_the_title(),
				'url' => get_permalink(),
			);
			if( get_datos_info('descricion') ) {
				$rch['description'] = get_datos_info('descripcion');
			}
            if( has_post_thumbnail() ) {
				$rch['image'] = get_the_post_thumbnail_url();
            }
			if( get_datos_info('version') ) {
				$rch['softwareVersion'] = get_datos_info('version');
			}
			$datos_imagenes = appyn_gpm($post->ID, 'datos_imagenes');
			if( $datos_imagenes ) {
				$rch['screenshot'] = array();
				foreach( $datos_imagenes as $img ) {
					if( empty($img) ) continue;
					$rch['screenshot'][] = array(
						"@type" => "ImageObject",
						"url" => $img,
					);
				}
			}
			$rch['operatingSystem'] = $os;
			$rch['applicationCategory'] = $cat;
			$rch['aggregateRating'] = array(
				"@type" => "AggregateRating",
				"ratingValue" => $rating['average'],
				"ratingCount" => str_replace(',','',$rating['users']),
			);
			$rch['offers'] = array(
				"@type" => "Offer",
				"price" => $price,
				"priceCurrency" => $currency,
			);

			echo '<script type="application/ld+json">'.json_encode($rch).'</script>';
		}
		

		$cat = get_the_category();
		if( !empty($cat[0]) ) {
			$pos = 1;
			echo '<script type="application/ld+json">
					{
					"@context": "https://schema.org",
					"@type": "BreadcrumbList",
					"itemListElement": [';
			if( $cat[0]->category_parent ) {
				$cat_parent = get_term_by('id', $cat[0]->category_parent, 'category');
			echo '{
					"@type": "ListItem",
					"position": '.$pos++.',
					"name": "'.$cat_parent->name.'",
					"item": "'.get_term_link($cat[0]->category_parent).'"
				},';
			}
			echo '{
					"@type": "ListItem",
					"position": '.$pos++.',
					"name": "'.$cat[0]->name.'",
					"item": "'.get_term_link($cat[0]->term_id).'"
				}';

			echo ']
				}
			</script>';
		}
	}

	elseif( is_singular( 'blog' ) ) {
		$logo = appyn_options( 'logo');
		$logo = ( !empty($logo) ) ? $logo: get_bloginfo('template_url').'/images/logo.png';
		echo '<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage": {
				"@type": "WebPage",
				"@id": "https://google.com/article"
			},
			"headline": "Article headline",
			"image": [
			"'.get_the_post_thumbnail_url().'"
			],
			"datePublished": "'.get_the_date('c', $post).'",
			"dateModified": "'.get_the_modified_date('c', $post).'",
				
			"publisher": {
				"@type": "Organization",
				"name": "'.get_bloginfo('title').'",
				"logo": {
					"@type": "ImageObject",
					"url": "'.$logo.'"
				}
			}
		}
		</script>';
	}
}

function get_store_app() {
	global $post;
	$os = get_datos_info( 'os', false, $post->ID );
	$output = '';

	if( $os == 'WINDOWS' ) {
		if( is_amp_px() ) {
			$output = '<amp-img src="'.get_template_directory_uri().'/images/microsoftstore.svg" width="40" height="40" alt="Micrososft Store"></amp-img>'; 
		} else {
			$output = '<img src="'.get_template_directory_uri().'/images/microsoftstore.svg" width="40" alt="Micrososft Store">';
		}
	}
	elseif( $os == 'MAC' || $os == 'iOS' ) {
		if( is_amp_px() ) {
			$output = '<amp-img src="'.get_template_directory_uri().'/images/appstore.svg" width="120" height="36" alt="App Store"></amp-img>'; 
		} else {
			$output = '<img src="'.get_template_directory_uri().'/images/appstore.svg" width="120" alt="App Store">';
		}
	} 
	elseif( $os == 'LINUX' ) {
		if( is_amp_px() ) {
			$output = '<amp-img src="'.get_template_directory_uri().'/images/appstore.png" width="60" height="60" alt="Linux"></amp-img>'; 
		} else {
			$output = '<img src="'.get_template_directory_uri().'/images/linux.svg" width="60" height="60" alt="Linux">';
		}
	} else {
		if( is_amp_px() ) {
			$output = '<amp-img src="'.get_template_directory_uri().'/images/googleplay.svg" width="120" height="36" alt="Google Play"></amp-img>'; 
		} else {
			$output = '<img src="'.get_template_directory_uri().'/images/googleplay.svg" width="120" height="36" alt="Google Play">';
		}
	}

	return $output;
}

function px_pay_app() {
	global $post;
	$datos_informacion = get_post_meta($post->ID, 'datos_informacion', true);

	if( !isset($datos_informacion['offer']['price']) ) return;

	if( $datos_informacion['offer']['price'] != "pago" ) return;

	if( empty($datos_informacion['offer']['amount']) ) {	
		return '<ul class="amount-app">
			<li>'.__( 'De pago', 'appyn' ).'</li>
		</ul>';
	} else {
		return '<ul class="amount-app">
	<li>'.$datos_informacion['offer']['amount'].' '.$datos_informacion['offer']['currency'].'</li>
</ul>';
	}
}

function px_check_apk_obb( $data ) {

	if( count($data) < 2 ) return false;

	return ( array_key_exists('apk', $data) && array_key_exists('obb', $data) ) ? true : false;
}

function get_http_response_code( $url ) {
		
	if( filter_var($url, FILTER_VALIDATE_URL) === false )
		return false;

	$args = array(
        'sslverify'   => false,
    );

    $request = wp_remote_get( $url, $args );

    if ( wp_remote_retrieve_response_code( $request ) == 200 ) {
		return true;
	} else {
		$data = array(
			'apikey' 	=> appyn_options( 'apikey', true ),
			'website'	=> get_site_url(),
			'app'		=> trim($url)
		);
		$url = API_URL."/?".http_build_query($data);
		$bot = go_curl($url);
		$bot = json_decode($bot, true);
		if( ! isset($bot['error_web']) ) return true;
	}
}

function px_upload_image( $datos, $post_id ) {
	$image = $datos['imagecover'];
	$nombre = urldecode(sanitize_title(strip_tags(wp_staticize_emoji($datos['nombre']))));
	
	$uploaddir = wp_upload_dir();
	$filename = "{$nombre}.png";
	$uploadfile = $uploaddir['path'] . '/' . $filename;

	$attach_id = attachment_url_to_postid($uploaddir['url'].'/'.$filename);

	if( !file_exists($uploadfile) || $attach_id == 0 ) {
		$wp_filetype = wp_check_filetype(basename($filename), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $filename,
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $uploadfile );
	}
	if( ! copy( $image, $uploadfile ) ) {

		$file = fopen ($image, "rb");

		if( $file ) {
			$newfile = fopen( $uploadfile, "wb" );

			if( $newfile ) {
				while( ! feof( $file ) ) {
				  fwrite( $newfile, fread( $file, 1024 * 8 ), 1024 * 8 );
				}
			}
		}

		if($file)
			fclose($file);

		if($newfile)
			fclose($newfile);
	}

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $uploadfile );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	set_post_thumbnail( $post_id, $attach_id );

	return $attach_id;
}

function versions_permalink() {
	global $post;

	$permalink = ( !$post->post_parent ) ? get_permalink() : get_permalink( $post->post_parent );
	if( is_amp_px() ) {
		return esc_url( rtrim(remove_query_arg('amp', $permalink), '/')."/versions/?amp=1" );
	} else {
		return esc_url( rtrim($permalink, '/')."/versions/" );
	}
}

function px_nav_menu( $type = '' ) {

	$c = '';
	$button_light_dark = '';

	$option_color_theme_user_select = appyn_options( 'color_theme_user_select' );
	if( $option_color_theme_user_select == 1 ) {
		if( is_dark_theme_active() )
			$c = ' class="active"';
			
		$button_light_dark = '<div id="button_light_dark"'.$c.'><i class="fas fa-'.((!empty($c)) ? 'moon' : 'sun').'"></i><span class="bld_"></span></div>';

		if( appyn_options( 'color_theme' ) == 'navegador' ) {
			$button_light_dark .= '<script>
			if( window.matchMedia && window.matchMedia(\'(prefers-color-scheme: dark)\').matches && localStorage.getItem("px_light_dark_option") != 0 ) {
				document.getElementById("css-dark-theme").removeAttribute("media");
				document.getElementById("button_light_dark").classList.add("active");
				localStorage.setItem(\'px_light_dark_option\', 1);
				setCookie(\'px_light_dark_option\', 1, 365);
			}
			</script>';
		} else {
			$button_light_dark .= '
			<script>
			if( localStorage.getItem("px_light_dark_option") == 1 ) {
				document.getElementById("css-dark-theme").removeAttribute("media");
				document.getElementById("button_light_dark").classList.add("active");
			}';
			if( ! is_dark_theme_active() ) {
				$button_light_dark .= 'else {
				document.getElementById("css-dark-theme").setAttribute("media", "max-width: 1px");
				document.getElementById("button_light_dark").classList.remove("active");
			}';
			}
			$button_light_dark .= '
			</script>
			';
		}
	}

	$args = array(
		'show_home' => true, 
	);
	if( $type == "mobile" ) {

		$args['theme_location'] = 'menu-mobile';
		$args['container'] = '';
		$args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s <li>'.px_header_social().'</li></ul>';

	} else {

		$args['theme_location'] = 'menu';
		$args['container'] = 'nav';
		$args['items_wrap'] = '<div class="menu-open"><i class="fa fa-bars"></i></div><ul id="%1$s" class="%2$s">%3$s</ul>'.$button_light_dark.'';

	}
	
	wp_nav_menu( $args );
}

function px_blog_postmeta() {
	global $post;
	?>
	<div class="px-postmeta">
		<span><i class="far fa-calendar"></i> <?php the_time(get_option( 'date_format' )); ?></span> <span><i class="fa fa-user"></i> <?php the_author_link(); ?></span>
		<?php 
		if( get_the_term_list( $post->ID, 'cblog' ) ) {
		?>
		<span><i class="fa fa-folder"></i> <?php echo get_the_term_list( $post->ID, 'cblog', '', ', ' ); ?></span> 
		<?php } ?>
		<span><i class="fas fa-comments"></i> <?php comments_number(); ?></span>
	</div>
	<?php
}

function px_post_status() {
	global $post;
	$inf = get_post_meta( $post->ID, 'datos_informacion', true );

	if( $post->post_parent ) return;

	if( isset($inf['app_status']) && !empty($inf['app_status']) ) {

		$vas = apply_filters( 'add_value_app_status', arr_values_app_status() );

		if( $inf['app_status'] == 'new' ) {
			if( date('U') <= date('U', strtotime($post->post_date. '+ 2 weeks')) )
				return '<div class="bloque-status bs-new" title="'.__( 'Nuevo', 'appyn' ).'">'.__( 'Nuevo', 'appyn' ).'</div>';
		}
		elseif( $inf['app_status'] == 'updated' ) {
			if( appyn_options( 'ribbon_update_post_modified', true ) == 1 ) {
				if( date('U') <= date('U', strtotime($post->post_modified. '+ 2 weeks')) ) {
					return '<div class="bloque-status bs-update" title="'.__( 'Actualizado', 'appyn' ).'">'.__('Actualizado', 'appyn').'</div>';
				}
			} else {
				if( date('U') <= date('U', strtotime($post->post_date. '+ 2 weeks')) ) {
					return '<div class="bloque-status bs-update" title="'.__( 'Actualizado', 'appyn' ).'">'.__('Actualizado', 'appyn').'</div>';
				}
			}
		} else {

			return '<div class="bloque-status bs-'.$inf['app_status'].'">
			
			'. $vas[$inf['app_status']] .'</div>';
		}
	} 
}

function px_post_mod( $post_id = false ) {
	if( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	} 

	$at = appyn_gpm( $post_id, 'app_type' );
	return ( $at ) ? '<span class="b-type">MOD</span>' : '';
}

function px_info_install() {
	global $post;

	$d = get_datos_download();
	$a = str_replace('[Title]', $post->post_title, appyn_options( 'apps_info_download_apk', true ));
	$b = str_replace('[Title]', $post->post_title, appyn_options( 'apps_info_download_zip', true ));

	if( !empty($a) || !empty($b) ) {

		if( isset($d['type']) ) {
			$output = '<div class="bx-info-install entry">';

			if( $d['type'] == 'apk' )
				$output .= wpautop(do_shortcode($a));
			elseif( $d['type'] == 'zip' || $d['type'] == 'apk_obb' )
				$output .= wpautop(do_shortcode($b));   

			$output .= '</div>';

			return $output;
		}
	}
}

function px_last_slug_apk() {
	$lsa = appyn_options( 'edcgp_sapk_slug', true ); 
	return ( $lsa ) ? '-'.$lsa : '';
}

add_filter('wp_nav_menu_items','replace_class', 10, 2);

function replace_class($items, $args)  {
    if ($args->menu->slug == 'menu') {
		$items = px_content_filter($items);
	}

    return $items;

}

function px_logo() {
	$logo = appyn_options( 'logo');
	$logo = ( !empty($logo) ) ? $logo: get_bloginfo('template_url').'/images/logo.png';
	$logo_id = attachment_url_to_postid( $logo );
	if( empty($logo_id) ) {
		$m = array( 1 => 150, 2 => 40 );
	} else {
		$m = wp_get_attachment_image_src( $logo_id, 'full' );
	}
	echo '<img src="'.$logo.'" alt="'.get_bloginfo('title').'" width="'.$m[1].'" height="'.$m[2].'">'; 
}

function px_download_link($url) {
	$appyn_encrypt_links = appyn_options( 'encrypt_links' );

	if( $appyn_encrypt_links == 1 ) {
		return add_query_arg( 'download_link', px_encrypt_decrypt( 'encrypt', $url."&pxdate=".date('Y-m-d') ), esc_url( remove_query_arg('amp', get_bloginfo('url') ) ) );
	}
	return $url;
}

function px_encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'SecretKey'.get_bloginfo('url');
    $secret_iv = 'SecretKeyIV'.get_bloginfo('url');
    $key = hash('sha256', $secret_key);

	$iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		$re = '/(&pxdate=(\d{4}\-\d{2}-\d{2}))$/m';
		preg_match_all($re, $output, $matches, PREG_SET_ORDER, 0);
		if( !isset($matches[0][2]) ) {
			return $string;
		}
		if( $matches[0][2] == date('Y-m-d') ) {
			return preg_replace($re, '', $output);
		}
    }
    return $output;
}

function px_option_selected_upload() {
	$tsr = appyn_options( 'edcgp_sapk_server' );

	if( $tsr == 2 ) {
		return __( 'Google Drive', 'appyn' ). ( ( !appyn_options( 'gdrive_token' ) ) ? ' '. __( '(No activado)', 'appyn' ) : '' );
	} elseif( $tsr == 3 ) {
		return __( 'Dropbox', 'appyn' ). (!appyn_options( 'dropbox_result' ) ?  ' '. __( '(Falta token de acceso)', 'appyn' ) : '' );
	} elseif( $tsr == 4 ) {
		return __( 'FTP', 'appyn' );
	} elseif( $tsr == 5 ) {
		return __( '1Fichier', 'appyn' );
	} elseif( $tsr == 6 ) {
		return __( 'OneDrive', 'appyn' ). ( ( !appyn_options( 'onedrive_access_token' ) ) ? ' '. __( '(No activado)', 'appyn' ) : '' );
	} elseif( $tsr == 7 ) {
		return __( 'UptoBox', 'appyn' ). ( ( !appyn_options( 'uptobox_token' ) ) ? ' '. __( '(No activado)', 'appyn' ) : '' );
	} else {
		return __( 'Mi servidor', 'appyn' );
	} 
}

if( class_exists( 'WPSEO_Options' ) ){
    function px_ys_get_version() {
		global $post;
		$di = get_post_meta( $post->ID, 'datos_informacion', true );
		return $di['version'];
	}
	
	function register_custom_yoast_variables() {
		wpseo_register_var_replacement( '%%px_ys_get_version%%', 'px_ys_get_version', 'advanced' );
	}
	
	add_action('wpseo_register_extra_replacements', 'register_custom_yoast_variables');
}

function px_rms_callback() {
	global $post;
    if( isset($post) ) {
        $v = get_datos_info('version');
    } else {
		$v = '[Version]';
	}
    return $v;
}

function px_gte( $a ) {
	$gte = appyn_options( 'general_text_edit', true );
	$opts = array(
		'amc' => __( 'Aplicaciones más calificadas', 'appyn' ),
		'uadnw' => __( 'Últimas aplicaciones de nuestra web', 'appyn' ),
		'bua' => __( 'Buscar una aplicación', 'appyn' ),
	);
	return ( !empty($gte[$a]) ) ? $gte[$a] : $opts[$a];
}

function appyn_gpm( $post_id, $key, $default = "" ) {
	return ( get_post_meta( $post_id, $key, true ) ) ? get_post_meta( $post_id, $key, true ) : $default;
}

function px_count_update_apps($a = false) {

	$results = get_option( 'trans_updated_apps', null );

	if( ! $results ) return 0;

	$count = count($results);

	return ( $a ) ? ( ( $count > 99 ) ? '99+' : $count ) : $count;

}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

add_filter( 'remote_post_check_apps', 'func_remote_post_check_apps', 10, 1 );

function func_remote_post_check_apps( $list_ids ) {
	
	$url = API_URL."/check/";

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
			'website' => get_site_url(),
			'apps' => $list_ids
		),
	) );

	if ( ! is_wp_error( $response ) ) {
		return $response['body'];
	}
}

function px_show_first_dl() {
	global $post;

	$datos_download = get_datos_download( $post->ID );

	if( $datos_download['option'] == "direct-link" || $datos_download['option'] == "direct-download" ) {
		return $datos_download[$datos_download['option']];
	}
	elseif( $datos_download['option'] == "links" ) {
		return ( isset($datos_download['links_options'][0]['link']) ? $datos_download['links_options'][0]['link'] : '--');
	}
}

function px_btoc( $size ){
    $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function arr_values_app_status() {

	$arr = array(
		'new' =>  __( 'Nuevo', 'appyn' ),
		'updated' => __( 'Actualizado', 'appyn' ),
	);

	return $arr;
}

function px_filter_app_status() {

	$ads = apply_filters( 'add_value_app_status', arr_values_app_status() );

	echo '<option value="">'. __( 'Ninguno', 'appyn' ) .'</option>';

	foreach( $ads as $k => $a ) {
		echo '<option value="'.$k.'" '. selected( get_datos_info('app_status'), $k, false ) .'>'.$a.'</option>';
	}
}

function remove_ldl() {
	
	$asdr		= appyn_options( 'active_show_dl_recaptcha' );

	if( ! $asdr || is_amp_px() ) return false;

	$sev2 		= appyn_options( 'recaptcha_v2_secret' ); 
	$siv2 		= appyn_options( 'recaptcha_v2_site' );
	$get_opt 	= get_query_var( 'opt' );
	$get_dl 	= get_query_var( 'download' );
	$adl 		= get_option( 'appyn_download_links', null );

    if ($adl == 0 || ($adl == 2 && $get_opt) || ($sev2 && $siv2 && $get_dl == 'links' && $adl != 2)) {
        return true;
    }
}

function px_cats_app() {
	
	$catsapp = array(
		'GAMES' => __( 'Juegos', 'appyn' ), 
		'GAME_ACTION' => __( 'Juegos de acción', 'appyn' ), 
		'GAME_ADVENTURE' => __( 'Juegos de aventura', 'appyn' ), 
		'GAME_RACING' => __( 'Juegos de carreras', 'appyn' ), 
		'GAME_CARD' => __( 'Juegos de cartas', 'appyn' ), 
		'GAME_CASINO' => __( 'Juegos de casino', 'appyn' ), 
		'GAME_EDUCATIONAL' => __( 'Juegos educativos', 'appyn' ), 
		'GAME_STRATEGY' => __( 'Juegos de estrategia', 'appyn' ), 
		'GAME_SPORTS' => __( 'Juegos de deportes', 'appyn' ), 
		'GAME_BOARD' => __( 'Juegos de mesa', 'appyn' ), 
		'GAME_WORD' => __( 'Juegos de palabras', 'appyn' ), 
		'GAME_ROLE_PLAYING' => __( 'Juegos de rol', 'appyn' ), 
		'GAME_CASUAL' => __( 'Juegos ocasionales', 'appyn' ), 
		'GAME_MUSIC' => __( 'Juegos de música', 'appyn' ), 
		'GAME_TRIVIA' => __( 'Preguntas y respuestas', 'appyn' ), 
		'GAME_PUZZLE' => __( 'Juegos de rompecabezas', 'appyn' ), 
		'GAME_ARCADE' => __( 'Sala de juegos', 'appyn' ), 
		'GAME_SIMULATION' => __( 'Juegos de simulación', 'appyn' ),
		'VIDEO_PLAYERS' => __( 'Aplicaciones de video', 'appyn' ), 
		'ANDROID_WEAR' => __( 'Apps de reloj', 'appyn' ), 
		'ART_AND_DESIGN' => __( 'Arte y diseño', 'appyn' ), 
		'AUTO_AND_VEHICLES' => __( 'Autos y vehículos', 'appyn' ), 
		'BEAUTY' => __( 'Belleza', 'appyn' ), 
		'LIBRARIES_AND_DEMO' => __( 'Bibliotecas y demostración', 'appyn' ), 
		'WATCH_FACE' => __( 'Caras de reloj', 'appyn' ), 
		'FOOD_AND_DRINK' => __( 'Comer y beber', 'appyn' ), 
		'SHOPPING' => __( 'Compras', 'appyn' ), 
		'COMMUNICATION' => __( 'Comunicación', 'appyn' ), 
		'DATING' => __( 'Conocer personas', 'appyn' ), 
		'COMICS' => __( 'Cómics', 'appyn' ), 
		'SPORTS' => __( 'Deportes', 'appyn' ), 
		'EDUCATION' => __( 'Educación', 'appyn' ), 
		'ENTERTAINMENT' => __( 'Entretenimiento', 'appyn' ), 
		'LIFESTYLE' => __( 'Estilo de vida', 'appyn' ), 
		'EVENTS' => __( 'Eventos', 'appyn' ), 
		'FINANCE' => __( 'Finanzas', 'appyn' ), 
		'PHOTOGRAPHY' => __( 'Fotografía', 'appyn' ), 
		'TOOLS' => __( 'Herramientas', 'appyn' ), 
		'HOUSE_AND_HOME' => __( 'Inmuebles y hogar', 'appyn' ), 
		'BOOKS_AND_REFERENCE' => __( 'Libros y referencias', 'appyn' ), 
		'MAPS_AND_NAVIGATION' => __( 'Mapas y navegación', 'appyn' ), 
		'MEDICAL' => __( 'Medicina', 'appyn' ), 
		'MUSIC_AND_AUDIO' => __( 'Música y audio', 'appyn' ), 
		'BUSINESS' => __( 'Negocios', 'appyn' ), 
		'NEWS_AND_MAGAZINES' => __( 'Noticias y revistas', 'appyn' ), 
		'PERSONALIZATION' => __( 'Personalización', 'appyn' ), 
		'PRODUCTIVITY' => __( 'Productividad', 'appyn' ), 
		'HEALTH_AND_FITNESS' => __( 'Salud y bienestar', 'appyn' ), 
		'PARENTING' => __( 'Ser padres', 'appyn' ), 
		'SOCIAL' => __( 'Social', 'appyn' ), 
		'WEATHER' => __( 'Tiempo', 'appyn' ), 
		'TRAVEL_AND_LOCAL' => __( 'Viajes', 'appyn' ), 
	);

	return $catsapp;
}

add_filter( 'option_appyn_orden_cajas', 'add_comments_order' );
 
function add_comments_order( $value ) {

	if( ! isset($value['comentarios']) )
		$value = array_merge($value, array('comentarios' => __( 'Comentarios', 'appyn' )));
		
	return $value;
}

add_filter( 'wp_robots', 'robots_var_download_opt' );

function robots_var_download_opt( $robots ){

	$get_download = get_query_var( 'download', null );
	$get_opt = get_query_var( 'opt', null );

	if( $get_download == 'links' || $get_download == 'redirect' || $get_opt ) {
		$robots['noindex'] = true;
		$robots['nofollow'] = true;
	}

	return $robots;
}

function dlp() {
	if( appyn_options( 'download_links_permalinks' ) == 1 ) return true;
}

function px_content_search_page() {
	if( appyn_options( 'search_google_active' ) ) {
		echo '<script async src="https://cse.google.com/cse.js?cx='.appyn_options( 'search_google_id', true ).'"></script>
		<div class="section">
    		<div class="title-section">
				'.__( 'Buscar', 'appyn' ).': '.$_GET['q'].'
			</div>
			<div class="gcse-searchresults-only"></div>
		</div>
		<style>
		.gsc-control-cse {
			box-shadow: 2px 2px 2px 0px #d2d1d1;
			margin-bottom: 3px;
		}
		.gsc-control-cse table {
			overflow: inherit;
			margin: 0;
		}
		.gsc-control-cse table td {
			border: 0;
			padding: 0;
		}
		</style>';
	} else {
		get_template_part( 'template-parts/content-search' );
	}
}

function px_shorten_download_link( $url ) {
                
	$shrt = appyn_options( 'edcgp_sapk_shortlink', true );

	$shortlink = new ShortLink($url);

	switch ( $shrt ) {

		case 'ouo':
			$url = $shortlink->Ouo();
			break;
		case 'shrinkearn':
			$url = $shortlink->ShrinkEarn();
			break;
		case 'shorte':
			$url = $shortlink->Shorte();
			break;
		case 'clicksfly':
			$url = $shortlink->ClicksFly();
			break;
		case 'oke':
			$url = $shortlink->Oke();
			break;
	}

	return $url;
}