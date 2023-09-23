<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">	
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<div class="wrapper-page">
		<div id="menu-mobile" style="display:none;">
			<?php px_nav_menu('mobile'); ?>
		</div>
	<div class="wrapper-inside">
	<header id="header">
		<div class="container">
			<div class="logo">
				<a href="<?php bloginfo('url'); ?>"><?php px_logo(); ?></a>
			</div>
			<?php px_nav_menu(); ?>
		</div>
	</header>
	<?php 
	if( ! isset($args['ws']) ) {
		do_action( 'subheader' );
		echo px_ads( 'ads_header' ); 
	} ?> 
	<main id="main-site">