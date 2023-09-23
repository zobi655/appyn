<div class="px-col">
	<div class="bloque-blog">
		<div class="bb-image">
			<a href="<?php the_permalink(); ?>"><?php echo px_post_thumbnail(); ?></a>
		</div>
		<div class="bb-c">
			<a href="<?php the_permalink(); ?>" class="title"><?php the_title(); ?></a>
			<span class="date"><i class="far fa-calendar" aria-hidden="true"></i> <?php the_time('j M, Y') ?></span>
			<div class="excerpt"><?php echo excerpt(10); ?></div>
		</div>
	</div>
</div>