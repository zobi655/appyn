<?php 
if( !is_amp_px() ) { 
    if( appyn_options( 'sidebar_active' ) == 0 ) {
        echo '<div id="sidebar"><ul>';
        dynamic_sidebar( 'sidebar-general' );
        echo '</ul></div>';
    }
} 
?>