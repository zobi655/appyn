<?php 
if( appyn_options( 'view_apps' ) ) {
	get_template_part( 'template-parts/loop/app-v2' );
} else {
?>
<div class="bav bav1">
	<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
		<?php echo px_post_thumbnail(); ?>
		<span class="title"><?php the_title(); ?></span>
		<?php echo app_version(); ?>
		<?php echo app_developer(); ?>
		<?php echo app_date(); ?>
        <div class="px-postmeta">
            <?php show_rating(0); ?>
        </div>
	</a>
</div>
<?php }