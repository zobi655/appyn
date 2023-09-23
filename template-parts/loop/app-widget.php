<li><a href="<?php echo the_permalink(); ?>">
    <div class="bghover"></div>
    <?php echo px_post_thumbnail('miniatura'); ?>
    <div class="bav bav2 wb">
        <div class="title"><?php the_title(); ?></div>
        <?php echo app_version(); ?>
        <?php echo app_developer(); ?>
        <?php echo app_date(); ?>
        <div class="px-postmeta">
            <?php show_rating(0); ?>
        </div>
    </div>
</a></li>