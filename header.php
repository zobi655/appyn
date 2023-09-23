<?php 
if( is_amp_px() ) { 
	get_template_part( 'amp/header' ); 
} else {
	get_header( 'default' ); 
}