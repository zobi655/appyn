<div class="bav bav2">
    <a href="<?php the_permalink(); ?>">
        <?php echo px_post_thumbnail( 'miniatura' ); ?>
        <div class="bap-c">
            <div class="title"><?php the_title(); ?></div>
            <?php echo app_version(); ?>
            <?php echo app_developer(); ?>
            <?php echo app_date(); ?>
            <div class="px-postmeta">
                <?php show_rating(0); ?>
            </div>
        </div>
    </a>
</div>