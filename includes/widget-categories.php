<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class px_custom_categories_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'px_custom_categories_widget', 
			'[Appyn] '.__( 'Categorías', 'appyn' ),
			array('description' => __( 'Muestra las categorías', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = __( 'Categorías', 'appyn' );	
		}
		$sc = ( isset($instance['select_category']) ) ? $instance['select_category'] : array();  
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		echo '<div class="widget-content">';
		$categories = get_categories();
		echo '<ul class="pxccat columns-'.((isset($instance['columns'])) ? $instance['columns'] : 1).'">';
		foreach($categories as $key => $category) {
			if( count($sc) > 0 )
				if( ! in_array( $category->term_id, $sc ) ) continue;
				
			$icon = get_term_meta( $category->term_id, 'px_cat_icon', true );

			echo '<li><a href="'.get_term_link($category->term_id).'"><span class="icop '.$icon.'"></span><span>'.$category->name.'</span></a></li>';
		}
		echo '</ul></div>';
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['columns'] = strip_tags($new_instance['columns']);
		$instance['select_category'] = isset($new_instance['select_category']) ? $new_instance['select_category'] : array();
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Categorías' , 'appyn' ), 'columns' => 1, 'select_category' => array() );
		$instance = wp_parse_args( (array) $instance, $defaults );	

		?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
		</p>
		<p>
            <label for="<?php echo $this->get_field_id('columns'); ?>"><?php echo __( 'Columnas', 'appyn' ); ?>:</label>
            <input id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>" type="number" value="<?php echo esc_attr($instance["columns"]); ?>" size="1" min="1" max="2" maxlength="1" />
		</p>
		<p>
            <label for="<?php echo $this->get_field_id('sc'); ?>"><?php echo __( 'Escoger categorías', 'appyn' ); ?>:</label>
			<button type="button" id="select_all_categories" class="button"><?php echo __( 'Seleccionar todos', 'appyn' ); ?></button>
			<ul class="px-scat">
			<?php
			$categories = get_categories();
			foreach( $categories as $key => $category ) {
				echo '<li><label><input type="checkbox" name="'.$this->get_field_name('select_category[]').'" value="'.$category->term_id.'" '. ( in_array( $category->term_id, $instance['select_category'] ) ? ' checked' : '' ) .'> '.$category->name.'</label></li>';
			}
			?>
			</ul>
		</p>
		<?php 
	}
}

add_action('widgets_init', function() { register_widget("px_custom_categories_Widget"); });