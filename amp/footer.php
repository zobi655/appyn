</main>
<footer id="footer">
    <div class="container">
        <div class="footer-bottom">
            <div class="copy"><?php echo stripslashes(get_option( 'appyn_footer_texto' )); ?>
            <?php wp_nav_menu(array('theme_location' => 'menu-footer', 'show_home' => false, 'container' => '', 'fallback_cb' => '') ); ?></div>
            <div class="logo">
                <?php echo px_amp_logo( get_option( 'appyn_logo' ) ); ?>
            </div>
        </div>
    </div>
</footer>
</div>
</div>
<?php do_action( 'wp_footer_amp' ); ?>
</body>
</html>