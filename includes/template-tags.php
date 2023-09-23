<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

function px_tag_breadcrumbs() {
    global $post;
    ?>
    <ol id="breadcrumbs">
    <?php 
    $separador = " / ";
    $list_cats = category_parents();
    ?>
    <li><a href="<?php bloginfo('url'); ?>" title="<?php echo bloginfo('title'); ?>">Home</a> <?php echo $separador; ?> </li>
    <?php
    if( !empty($list_cats) ) { 
        foreach($list_cats as $cat) {
            echo "<li>$cat ".(end($list_cats) != $cat ? $separador : '' )."</li>";
        }
        if( $post->post_parent ) {
            echo '<li><a href="'.get_the_permalink( $post->post_parent ).'">'.$separador.''.get_the_title( $post->post_parent ).'</a></li>';
        }
    }
    ?></ol><div></div>
<?php 
}

function px_tag_social_buttons() {
    global $post;
    if( appyn_options('single_hide_social_buttons') ) return;

	if( is_page() ) {
		$hsb = appyn_gpm( $post->ID, 'appyn_hidden_social_buttons' );
		if( $hsb ) return;
	}

    $color_botones_sociales = appyn_options('social_single_color');

    $output = '<div class="app-spe s2 box-social">';

    $output .= '<ul class="botones_sociales' . ( ($color_botones_sociales == "color") ? ' color' : '' ) .' ">';
    
    if( is_amp_px() ) {
        $output .= '<li><a target="_blank" href="http://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink() . '" class="facebook" rel="noopener"><i class="fab fa-facebook-f" aria-hidden="true"></i> Facebook</a></li>';

        $output .= '<li><a target="_blank" href="http://www.twitter.com/share?url=' . urlencode(get_the_title() . ': ' . get_the_permalink()) . '" class="twitter" rel="noopener"><i class="fab fa-twitter" aria-hidden="true"></i> Twitter</a></li>';
        
        $output .= '<li><a target="_blank" href="http://pinterest.com/pin/create/button/?url=' . get_the_permalink() . '" class="pinterest" rel="noopener"><i class="fab fa-pinterest" aria-hidden="true"></i> Pinterest</a></li>';
        
        $output .= '<li class="tg"><a href="tg://msg_url?url=' . get_the_permalink() . '" class="telegram" rel="noopener"><i class="fab fa-telegram" aria-hidden="true"></i> Telegram</a></li>';
        
        $output .= '<li class="ws"><a href="whatsapp://send?text=' . urlencode(get_the_title().': '.get_permalink()) . '" data-action="share/whatsapp/share" class="whatsapp" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i> Whatsapp</a></li>';

    } else {
         $output .= '<li><a href="http://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink() . '"  data-width="700" data-height="550" class="facebook" rel="noopener"><i class="fab fa-facebook-f" aria-hidden="true"></i> Facebook</a></li>';

        $output .= '<li><a href="http://www.twitter.com/share?url=' . urlencode(get_the_title() . ': ' . get_the_permalink()) . '" data-width="645" data-height="573" class="twitter"><i class="fab fa-twitter" aria-hidden="true"></i> Twitter</a></li>';

        $output .= '<li><a href="http://pinterest.com/pin/create/button/?url=' . get_the_permalink() . '" data-width="770" data-height="573" class="pinterest"><i class="fab fa-pinterest" aria-hidden="true"></i> Pinterest</a></li>';

        $output .= '<li class="tg"><a href="tg://msg_url?url=' . get_the_permalink() . '" class="telegram"><i class="fab fa-telegram" aria-hidden="true"></i> Telegram</a></li>';

        $output .= '<li class="ws"><a href="whatsapp://send?text=' . urlencode(get_the_title().': '.get_permalink()) .'" data-action="share/whatsapp/share" class="whatsapp"><i class="fab fa-whatsapp" aria-hidden="true"></i> Whatsapp</a></li>';
    }
    $output .= '</ul></div>';

    echo $output;
}

function px_header_social() {

    $output = '<ul class="social">';

	$px_social_facebook = px_social('facebook');
    $output .= ( !empty( $px_social_facebook ) ? '<li><a href="'.$px_social_facebook.'" class="facebook" title="Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>': $px_social_facebook );
		
	$px_social_twitter = px_social('twitter');
    $output .= ( !empty( $px_social_twitter ) ? '<li><a href="'.$px_social_twitter.'" class="twitter" title="Twitter" target="_blank" rel="noopener"><i class="fab fa-twitter" aria-hidden="true"></i></a></li>': $px_social_twitter );
		
	$px_social_instagram = px_social('instagram');
    $output .= ( !empty( $px_social_instagram ) ? '<li><a href="'.$px_social_instagram.'" class="instagram" title="Instagram" target="_blank" rel="noopener"><i class="fab fa-instagram" aria-hidden="true"></i></a></li>': $px_social_instagram );
		           
	$px_social_youtube = px_social('youtube');
    $output .= ( !empty( $px_social_youtube ) ? '<li><a href="'.$px_social_youtube.'" class="youtube" title="YouTube" target="_blank" rel="noopener"><i class="fab fa-youtube" aria-hidden="true"></i></a></li>': $px_social_youtube );
		    
	$px_social_pinterest = px_social('pinterest');
    $output .= ( !empty( $px_social_pinterest ) ? '<li><a href="'.$px_social_pinterest.'" class="pinterest" title="Pinterest" target="_blank" rel="noopener"><i class="fab fa-pinterest" aria-hidden="true"></i></a></li>': $px_social_pinterest );
		    
	$px_social_telegram = px_social('telegram');
    $output .= ( !empty( $px_social_telegram ) ? '<li><a href="'.$px_social_telegram.'" class="telegram" title="Telegram" target="_blank" rel="noopener"><i class="fab fa-telegram" aria-hidden="true"></i></a></li>': $px_social_telegram );
    
    $output .= '</ul>';

    return $output;
}

function app_developer() {
	global $post;
	$developer = get_datos_info( 'desarrollador', false, $post->ID );
    $output = '';
	if( !empty($developer) ) {
		$output = '<span class="developer">'.$developer.'</span>';
	} else {
		$dev_terms = wp_get_post_terms( $post->ID, 'dev', array('fields' => 'all'));
		if( !empty($dev_terms) ) {
			$output = '<span class="developer">'.$dev_terms[0]->name.'</span>';
		}
	}
	return $output;
}

function app_date() {
	global $post;
	$appyn_post_date = appyn_options( 'post_date' );
	$appyn_post_date_type = appyn_options( 'post_date_type' );
	if( !$appyn_post_date && $post->post_type != 'blog' ) return; 

	$date = get_the_date( get_option( 'date_format' ), $post->ID);
	if( $appyn_post_date_type == 1  ) {
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
		$fa = get_datos_info( 'fecha_actualizacion', false, $post->ID );
		if( !empty($fa) ) {
			$date = date_i18n( get_option( 'date_format' ), strtotime(strtr($fa, $date_change)));
		}
	}
	$output = '<span class="app-date">'.$date.'</span>';
	return $output;
}

function app_version() {
	global $post;
	$version = get_datos_info( 'version', false, $post->ID );
	
	return $version ? '<span class="version">'.px_post_mod().'<span>'.$version.'</span></span>' : '';
}