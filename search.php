<?php get_header(); ?>
	<div class="container">
		<div class="sections">
			<?php px_content_search_page(); ?>
		</div>
        <?php 
        if( appyn_options( 'og_sidebar' ) ) {
            get_sidebar( 'general' ); 
        } ?>
   </div>
<?php get_footer(); ?>