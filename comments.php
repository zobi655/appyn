<?php

$comments_single = get_option('appyn_comments'); 

if ( comments_open() ) :

	if( empty($comments_single) || $comments_single == "wp" || $comments_single == "wpfb" || is_amp_px() ) { ?>
	<div id="comments" class="box comments-area">
		<?php if ( have_comments() ) : ?>
			<h2 class="comments-title box-title">
				<?php
					printf( _nx(__( 'Un comentario en', 'appyn' ).' "%2$s"', '%1$s '.__( 'comentarios en', 'appyn' ).' "%2$s"', get_comments_number(), 'comments title'), number_format_i18n( get_comments_number() ), get_the_title() );
				?>
			</h2>
			<?php px_comment_nav(); ?>

			<ol class="comment-list">
				<?php
					wp_list_comments( array(
						'avatar_size' => 56,
						'callback' => 'appyn_comment',
					) );
				?>
			</ol>
		
			<?php px_comment_nav(); ?>

		<?php endif; ?>
		<?php 
		if( is_amp_px() ) {
			amp_comment_form();
		} else {
			comment_form(array('title_reply_before' => '<h2 id="reply-title" class="comment-reply-title box-title">'));
		} ?>
	</div>
	<?php 
	} if( ($comments_single == "fb" || $comments_single == "wpfb") && !is_amp_px() ) { ?>
		<div class="box comments-area">
			<h2 id="reply-title" class="comment-reply-title box-title"><?php echo __( 'Comentarios de Facebook', 'appyn' ); ?></h2>
			<div id="fb-root"></div>
			<script async defer crossorigin="anonymous" src="https://connect.facebook.net/<?php echo get_locale(); ?>/sdk.js#xfbml=1&version=v11.0" nonce="5oHNp4ST"></script>
			<?php 
			$color_theme = get_option('appyn_color_theme');
			if( is_dark_theme_active() ) { ?>
			<div class="fb-comments" data-colorscheme="dark" data-lazy="true" loading="lazy" data-href="<?php the_permalink(); ?>" data-width="100%" data-numposts="5"></div>
			<?php } else { ?>
			<div class="fb-comments" data-lazy="true" loading="lazy" data-href="<?php the_permalink(); ?>" data-width="100%" data-numposts="5"></div>
			<?php } ?>
		</div>	
	<?php } 
endif;