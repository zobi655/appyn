<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class YouTube_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'video_widget', 
			'[Appyn] YouTube',
			array('description' => __( 'Muestra cualquier video de YouTube colocando solo el ID', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = 'YouTube';	
		}
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		$youtubeid =  $instance['youtubeid'];
		if($youtubeid){
			echo '<div class="video_container"><iframe src="https://www.youtube.com/embed/'.$youtubeid.'?feature=oembed" width="300" height="210" allowfullscreen style="overflow:hidden; border:none"></iframe></div>'; 
		}
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['youtubeid'] = strip_tags($new_instance['youtubeid']);		
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Youtube' , 'appyn' ), 'youtubeid' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );	
?>
		 <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
         </p> 
       	<p>
            <label for="<?php echo $this->get_field_id('youtubeid'); ?>">ID:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('youtubeid'); ?>" name="<?php echo $this->get_field_name('youtubeid'); ?>" type="text" value="<?php echo esc_attr($instance["youtubeid"]); ?>" />
         </p> 
         <?php 
	}
}

add_action('widgets_init', function() { register_widget("YouTube_Widget"); });