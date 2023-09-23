<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

if( wp_is_mobile() ) return;

class Facebook_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'facebook_widget', 
			'[Appyn] Facebook',
			array('description' => __( 'Muestra la caja de tu página de Facebook', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = 'Facebook';
		}
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		$face_url =  $instance['face_url']; 
		?>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/<?php echo get_locale(); ?>/sdk.js#xfbml=1&version=v16.0"></script>
<?php
		echo '<div class="fb-page" data-href="'.$face_url.'" data-tabs="" data-width="280" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="'.$face_url.'" class="fb-xfbml-parse-ignore"><a href="'.$face_url.'">Facebook</a></blockquote></div>';
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['face_url'] = strip_tags($new_instance['face_url']);		
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Facebook' , 'appyn' ), 'face_url' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );	
?>
		 <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
         </p> 
       	<p>
            <label for="<?php echo $this->get_field_id('face_url'); ?>">URL:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('face_url'); ?>" name="<?php echo $this->get_field_name('face_url'); ?>" type="text" value="<?php echo esc_attr($instance["face_url"]); ?>" />
         </p> 
         <?php 
	}
}

add_action('widgets_init', function() { register_widget("Facebook_Widget"); });