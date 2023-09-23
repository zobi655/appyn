<div id="searchBox">
    <form action="<?php bloginfo("url"); ?>" method="get" target="_top">
        <input type="text" name="<?php echo ( appyn_options( 'search_google_active', true ) ) ? 'q' : 's'; ?>" placeholder="<?php echo px_gte( 'bua' ); ?>" required autocomplete="off" id="sbinput" aria-label="Search" class="sb_search">
        <?php echo ( appyn_options( 'search_google_active', true ) ) ? '<input type="hidden" name="s">' :'' ?>
        <button type="submit" aria-label="Search" title="<?php echo px_gte( 'bua' ); ?>" class="sb_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
    </form>
    <ul></ul>
</div>