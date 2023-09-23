</main>
<footer id="footer">
    <div class="container">
    	<?php 
		if( is_active_sidebar( 'sidebar-footer' ) ){
			echo '<ul>';
            dynamic_sidebar( 'sidebar-footer' );
			echo '</ul>';
		} ?>
        <div class="footer-bottom">
            <div class="copy"><?php echo stripslashes( get_option( 'appyn_footer_texto' ) ); ?>
            <?php wp_nav_menu( array( 'theme_location' => 'menu-footer', 'show_home' => false, 'container' => '', 'fallback_cb' => '' ) ); ?></div>
            <div class="logo"><?php echo px_logo(); ?></div>
        </div>    
    </div>
</footer>
</div>
</div>
<?php wp_footer(); ?>
</body>
</html>