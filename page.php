<?php get_header(); ?>
  	<div class="container">
   		<div class="app-p"<?php echo ( appyn_gpm( $post->ID, 'appyn_hidden_sidebar' ) ) ? ' style="margin-right:0;"' : ''; ?>>
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="box">
				<div id="breadcrumbs">
					<a href="<?php bloginfo('url'); ?>">Home</a> /
				</div>
				<h1 class="box-title"><?php the_title(); ?></h1>
				<?php 
				if( ! appyn_gpm( $post->ID, 'appyn_hidden_post_meta' ) ) { ?>
				<div class="px-postmeta">
					<span><i class="far fa-calendar"></i> <?php the_time('j M, Y') ?></span> <span><i class="fa fa-user"></i> <?php the_author_link(); ?></span> <span><i class="fas fa-comments"></i> <?php comments_number(); ?></span>
				</div>
				<?php } ?>
				<?php do_action( 'px_social_buttons' ); ?>
				<div class="entry"><?php px_the_content(); ?></div>
			</div>        
			<?php comments_template(); ?> 
			<?php endwhile; endif; ?>
   		</div>
		<?php if( ! appyn_gpm( $post->ID, 'appyn_hidden_sidebar' ) ) get_sidebar(); ?>
  	</div>
<?php get_footer(); ?>