<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class Fixed_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'fixed_widget', 
			'[Appyn] '.__( 'Widget Fijado', 'appyn' ),
			array('description' => __( 'Muestra un widget que sigue al usuario cuando hace scroll en la página', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = __( 'Widget Fijado', 'appyn' );
		}
		echo $before_widget; 
		echo ( !empty($title) ) ? $before_title.$title.$after_title : ''; 
		echo '<div class="widget-content">'.$instance['content'].'</div>';
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['content'] = $new_instance['content'];		
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Widget Fijado', 'appyn' ), 'content' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );	
	?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
         </p> 
       	<p>
			<label for="<?php echo $this->get_field_id('content'); ?>"><?php echo __( 'Contenido', 'appyn' ); ?>:</label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>" placeholder="<?php echo __('Se permite HTML también...', 'appyn' ); ?>" style="height: 100px; overflow: hidden; overflow-wrap: break-word; resize: none"><?php echo $instance['content']; ?></textarea>
		</p> 
        <?php 
	}
}
add_action('widgets_init', function() { register_widget("Fixed_Widget"); });