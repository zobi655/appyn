<?php get_header(); ?>
	<div class="container">
    	<div class="section error404">
        	<h1>404</h1>
            <h2><?php echo __( 'La página que estás buscando no existe.', 'appyn' ); ?></h2>
        	<?php get_template_part( 'template-parts/searchform' ); ?>
        </div>
    </div>
<?php get_footer(); ?>