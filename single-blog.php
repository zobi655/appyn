<?php get_header(); ?>
   	<div class="container">
		<div class="app-p">
         	<?php if( have_posts() ) : while( have_posts() ) : the_post(); ?>
               	<div class="box">
					<ol id="breadcrumbs">
						<li><a href="<?php bloginfo('url'); ?>">Home</a> /</li>
						<li><a href="<?php bloginfo('url'); ?>/blog/">Blog</a></li>
					</ol>
					<h1 class="box-title"><?php the_title(); ?></h1>
					<?php px_blog_postmeta(); ?>
					<?php do_action( 'px_social_buttons' ); ?>
					<div class="entry"><?php px_the_content(); ?></div>
				</div>
				<?php echo px_ads( 'ads_single_top' ); ?> 
				<?php 
				if( get_the_term_list( $post->ID, 'tblog' ) ) {
				?>
				<div id="tags" class="box tags">
				<h2 class="box-title"><?php echo __( 'TAGS', 'appyn' ); ?></h2>
				<?php echo get_the_term_list( $post->ID, 'tblog', '', ', ' ); ?>
				</div> 
				<?php } ?>
				<?php comments_template(); ?> 
			<?php endwhile; endif; ?>
		</div>
		<?php 
		if( appyn_options( 'blog_sidebar' ) ) {
			get_sidebar( 'blog' ); 
		} else { 
			get_sidebar();
		} ?>
	</div>
<?php get_footer(); ?>