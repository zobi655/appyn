<?php get_header(); ?>
	<div class="container">
        <div class="app-p">
            <div class="section">
                <div class="title-section">
                    <?php
                    $cat = get_queried_object();
                    $px_cat_icon = get_term_meta( $cat->term_id, "px_cat_icon", true );
                    $ico = ( $px_cat_icon ) ? '<span class="icop '.$px_cat_icon.'"></span>' : '';
                    echo $ico; 
                    ?>
                    <span><?php echo single_cat_title("", false); ?></span>
                </div>
                <div class="ct_description"><?php echo @category_description(); ?></div>
                <?php
                $i = 1;
                if( have_posts() ):
                    $aprpc = appyn_options( 'apps_per_row_pc', 6 );
                ?> 
                <div class="baps" data-cols="<?php echo $aprpc; ?>">
                    <?php
                    while( have_posts() ) : the_post();
                        get_template_part( 'template-parts/loop/app' );
                        if( $i == $aprpc ) {
                            if( !wp_is_mobile( ) )
                                echo '</div>'.px_ads( 'ads_home' ).'<div class="baps" data-cols="'.$aprpc.'">';
                        }
                        $i++; 
                    endwhile;
                    ?>
                </div>
                <?php paginador();
                else: 
                    echo '<div class="no-entries"><p>'.__( 'No hay entradas', 'appyn' ).'</p></div>';
                endif; ?>
            </div>
        </div>
        <?php 
        if( appyn_options( 'og_sidebar' ) ) {
            get_sidebar( 'general' ); 
        } ?>
   </div>
<?php get_footer(); ?>