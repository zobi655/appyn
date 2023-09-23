<?php get_header(); ?>
	<div class="container">
   		<div class="app-p" style="width:100%;">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
       		<div class="box">
        		<div id="breadcrumbs">
            		<a href="<?php bloginfo('url'); ?>">Home</a> /
				</div>
        		<h1 class="box-title"><?php the_title(); ?></h1>
        		<div class="px-postmeta">
        			<span><i class="far fa-calendar"></i> <?php the_time('j M, Y') ?></span> <span><i class="fa fa-user"></i> <?php the_author_link(); ?></span> <span><i class="fas fa-comments"></i> <?php comments_number(); ?></span>
        		</div>
        		<?php do_action( 'px_social_buttons' ); ?>
        		<div class="entry"><?php px_the_content(); ?></div>
       		</div>        
			<?php comments_template(); ?> 
			<?php endwhile; endif; ?>
   		</div>
   		<?php get_sidebar(); ?>
  </div>
<?php get_footer(); ?>