<?php 
if( is_amp_px() ) { 
	get_template_part( 'amp/footer' ); 
} else {
	get_footer( 'default' ); 
}