<li><a href="<?php echo the_permalink(); ?>" class="scim"><?php echo px_post_thumbnail(); ?></a>
	<div class="s2">
		<div class="bca">
			<a href="<?php echo the_permalink(); ?>" class="title"><?php the_title(); ?></a>
			<?php px_blog_postmeta(); ?>
		</div>
		<div class="excerpt"><?php echo excerpt(10); ?></div>
	</div>
</li>