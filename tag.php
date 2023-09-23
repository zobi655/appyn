<?php get_header(); ?>
	<div class="container">
        <div class="app-p">
            <div class="section">
                <div class="title-section"><?php echo __( 'TAG', 'appyn' ); ?>: <?php echo single_tag_title("", false); ?></div>
                <?php if( !empty(tag_description()) ) { ?>
                <div class="ct_description"><?php echo tag_description(); ?></div>
                <?php
                }
                $i = 1;
                if( have_posts() ):
                    $aprpc = appyn_options( 'apps_per_row_pc', 6 );
                ?> 
                <div class="baps" data-cols="<?php echo $aprpc; ?>">
                    <?php
                    while( have_posts() ) : the_post();
                        get_template_part( 'template-parts/loop/app' );
                        $aprpc = appyn_options( 'apps_per_row_pc', 6 );
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