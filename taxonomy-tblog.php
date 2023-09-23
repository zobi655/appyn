<?php get_header(); ?>
	<div class="container">
        <div class="app-p">
            <div class="section blog">
                <?php
                if( have_posts() ): ?>
                <div class="title-section"><?php echo __( 'TAG', 'appyn' ); ?>: <?php echo single_tag_title("", false); ?></div>
                <?php if( !empty(tag_description()) ) { ?>
                <div class="ct_description"><?php echo tag_description(); ?></div>
                <?php
                }
                ?>
                <ul class="bloques">
                <?php 
                $i = 1;
                while( have_posts() ): the_post(); 
                    get_template_part( 'template-parts/loop/blog' );
                    if($i == 6) { 
                        if( !wp_is_mobile() ) echo px_ads( "ads_home" );
                    }
                    $i++; 
                endwhile; 
                ?>
                </ul>
                <?php paginador();
                else: 
                    echo '<div class="no-entries"><p>'.__( 'No hay entradas', 'appyn' ).'</p></div>';
                endif; ?>
            </div>
        </div>
        <?php 
        if( appyn_options( 'blog_sidebar' ) ) {
            get_sidebar( 'blog' ); 
        } else { 
            get_sidebar();
        } ?>
   </div>
<?php get_footer(); ?>