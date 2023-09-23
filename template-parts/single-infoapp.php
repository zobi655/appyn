<?php
$get_download = get_query_var( 'download' );
$version = get_datos_info( 'version' );
$descripcion = get_datos_info( 'descripcion' );
$consiguelo = get_datos_info( 'consiguelo' );
?>
<div class="app-spe s2">
    <?php echo px_post_mod(); ?>
    <?php do_action( 'px_breadcrumbs' ); ?>
    <?php echo single_info_title( $version ); ?>
    <div class="clear"></div>
    <?php if( $get_download ) {
        $get_download = get_query_var( 'download' );
        $get_opt = get_query_var( 'opt' );
        $adl = get_option( 'appyn_download_links', null );

        $a = get_option("appyn_download_timer");
        $download_timer = ( isset($a) ) ? get_option( "appyn_download_timer" ) : 5;

        $option = get_datos_download()['option'];

        switch( $option ) { 	
            case "direct-link":
                $datos_download = get_datos_download();
                if( !isset( $datos_download['direct-link'] ) ) {
                    echo '<p>'.__( 'No hay enlace de redirección...', 'appyn' ).'</p>';
                } else {
                    echo '<div class="bx-download">
                    <div class="bxt">'.__( 'Será redireccionado en unos segundos...', 'appyn' ).'</div>
                    <p>'.__( 'Si la redirección no funciona, haga clic', 'appyn' ).' <a href="'.((strlen($datos_download['direct-link'])>0) ? $datos_download['direct-link'] : 'javascript:alert_download()').'" rel="nofollow">'.__( 'aquí', 'appyn' ).'</a>.</p>
                </div>'; 
                echo px_info_install();
                }
            break;

            case "links":
                echo px_ads( 'ads_download_1' );
                echo '<div class="bx-download">';

                do_action( 'list_download_links' );

                echo '</div>';
                echo px_ads( 'ads_download_2' );
            break;

            default :
                echo px_ads( 'ads_download_1' );
                echo '<div class="bx-download">';

                do_action( 'list_download_links' );

                echo '</div>';
                echo px_ads( 'ads_download_2' );
            break;
        }
    } else {
        echo '<div class="meta-cats">'.px_pay_app().''.get_the_category_list().'</div>';
        echo (!empty($descripcion)) ? '<div class="descripcion">'.$descripcion.'</div>' : '';
    } ?>
</div>
<?php 
$lbda = link_button_download_apk();
echo '<div class="app-icb s1">';

    echo px_post_thumbnail('thumbnail', $post, true);

    if( $get_download ) {
    
        if ($adl == 2 && $get_opt) {

            echo '<a href="'. esc_url(add_query_arg('download', 'links', remove_query_arg( array('amp', 'opt'), get_the_permalink()))).'" class="buttond"><i class="fa fa-chevron-left" aria-hidden="true"></i> '.__('Regresar', 'appyn').'</a>';

        } else {

            echo '<a href="'.get_the_permalink().'" class="buttond"><i class="fa fa-chevron-left" aria-hidden="true"></i> '.__('Regresar', 'appyn').'</a>';
        }

        if( $lbda ) {
            if( $post->post_parent ) {
                echo '<a href="'.get_permalink( $post->post_parent ).'" class="buttond downloadAPK danv" rel="nofollow" title="'. __('Última versión', 'appyn').'"'.((is_amp_px()) ? ' on="tap:download.scrollTo(duration=400)"' : '').'><i class="fas fa-sync-alt"></i> '. __('Última versión', 'appyn').'</a>';
            }
        } 
    } else {
        if( $lbda ) {
            if( isset($consiguelo) ) {
                if( strpos($consiguelo, 'microsoft.com') !== false || empty($consiguelo)) {
                    echo '<a href="'.$lbda.'" class="buttond downloadAPK" rel="nofollow" title="'. __( 'Descargar', 'appyn' ).'"'.((is_amp_px()) ? ' on="tap:download.scrollTo(duration=400)"' : '').'><i class="fa fa-download"></i> '. __( 'Descargar', 'appyn' ).'</a>';
                } else {
                    $gte = appyn_options( 'general_text_edit', true );
                    $text = ( !empty($gte['bda'] ) ) ? __( $gte['bda'], 'appyn' ) : __( 'Descargar APK', 'appyn' );
                    echo '<a href="'.$lbda.'" class="buttond downloadAPK" rel="nofollow" title="'.$text.'"'.((is_amp_px()) ? ' on="tap:download.scrollTo(duration=400)"' : '').'><i class="fa fa-download"></i> '.$text.'</a>';
                }
            }
    
            if( $post->post_parent ) {
                echo '<a href="'.get_permalink( $post->post_parent ).'" class="buttond danv" rel="nofollow" title="'. __('Última versión', 'appyn').'"'.((is_amp_px()) ? ' on="tap:download.scrollTo(duration=400)"' : '').'><i class="fas fa-sync-alt"></i> '. __('Última versión', 'appyn').'</a>';
            }
        } 
    }

    if( appyn_options( 'single_show_telegram_button', true ) ) {
        echo '<a href="'.appyn_options( 'social_telegram', true ).'" class="buttond t" rel="nofollow" target="_blank" title="Telegram"><i class="fab fa-telegram-plane"></i>TELEGRAM</a>';
    }

    show_rating();
    if( ! is_amp_px() ) {
        echo '<div class="link-report"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> '.__( 'Reportar', 'appyn' ).'</div>';
    }
?>
</div>
<?php 
if( !$get_download ) {
    echo '<div class="app-spe s2">
    <div class="box-data-app">
        <div class="app-icb data-app">';
            do_action( 'px_data_app_single' );
    echo '</div>
    </div>
    </div>';
}