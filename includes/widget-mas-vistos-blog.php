<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class mas_vistos_blog_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'mas_vistos_blog_widget', 
			'[Appyn] '.__( 'Posts más vistos', 'appyn' ).' - BLOG',
			array('description' => __( 'Muestra los posts más vistos de tu sitio', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);
		} else {
			$title = __( 'Posts más vistos', 'appyn' );	
		}
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		echo '<div class="widget-content">';
		global $wpdb, $post;

		$args = array( 'post_type' => 'blog', 'meta_key' => 'px_views', 'orderby' => 'meta_value_num', 'ignore_sticky_posts' => 1 );

		if( isset($instance['cantidad']) ) {
			$args['posts_per_page'] = ( $instance['cantidad'] ) ? $instance['cantidad'] : 5;
		}

		$mv = new WP_Query($args);

		echo '<ul>';
		if( $mv->have_posts() ) :
			while( $mv->have_posts() ) : $mv->the_post();
				get_template_part('template-parts/loop/blog-widget');
			endwhile;
		else :
			echo '<span class="noentry">'.__( 'No hay entradas', 'appyn' ).'</span>';
		endif;
		wp_reset_query();
		echo '</ul></div>';
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['cantidad'] = strip_tags($new_instance['cantidad']);
		if(!is_numeric(strip_tags($new_instance['cantidad']))){
			$instance['cantidad'] = '0';
		}		
		return $instance;
	}
	public function form($instance) {
        $defaults = array( 'title' => __( 'Posts más vistos' , 'appyn' ), 'cantidad' => 5 );
		$instance = wp_parse_args( (array) $instance, $defaults );	
		?>
		 <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
         </p>
         <p>
            <label for="<?php echo $this->get_field_id('cantidad'); ?>"><?php echo __( 'Número de entradas a mostrar', 'appyn' ); ?>:</label>
            <input id="<?php echo $this->get_field_id('cantidad'); ?>" name="<?php echo $this->get_field_name('cantidad'); ?>" type="text" value="<?php echo esc_attr($instance["cantidad"]); ?>" size="3" maxlength="2" />
         </p> 
         <?php 
	}
}

add_action('widgets_init', function() { register_widget("mas_vistos_blog_Widget"); });