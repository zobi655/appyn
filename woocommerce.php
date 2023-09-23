<?php get_header(); ?>
	<div class="container">
        <div class="app-p">
            <div class="section">
				<?php woocommerce_content(); ?>
            </div>
        </div>
        <?php 
        if( appyn_options( 'og_sidebar' ) ) {
            get_sidebar( 'general' ); 
        } ?>
   </div>
<?php get_footer(); ?>