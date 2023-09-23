<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class Twitter_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'twitter_widget', 
			'[Appyn] Twitter',
			array('description' => __( 'Coloca tu nombre de usuario de Twitter y comenzarán a seguirte', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = __( 'Síguenos en Twitter', 'appyn' );	
		}
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		$twitter_usuario =  $instance['twitter_usuario']; 
		if($twitter_usuario) { ?>
		<div style="text-align:center; padding:15px;">
			<a href="https://twitter.com/<?php echo $twitter_usuario; ?>" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @<?php echo $twitter_usuario; ?></a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		</div>
<?php
		}
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['twitter_usuario'] = strip_tags($new_instance['twitter_usuario']);	
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Síguenos en Twitter' , 'appyn' ), 'twitter_usuario' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
        </p>
       	<p>
            <label for="<?php echo $this->get_field_id('twitter_usuario'); ?>"><?php echo __( 'Usuario', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('twitter_usuario'); ?>" name="<?php echo $this->get_field_name('twitter_usuario'); ?>" type="text" value="<?php echo esc_attr($instance["twitter_usuario"]); ?>" placeholder="@username" />
         </p> 
         <?php 
	}
}

add_action('widgets_init', function() { register_widget("Twitter_Widget"); });