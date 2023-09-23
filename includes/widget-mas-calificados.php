<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class mas_calificados_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'mas_calificados_widget', 
			'[Appyn] '.__( 'Apps más calificadas', 'appyn' ),
			array('description' => __( 'Muestra las apps más calificadas de tu sitio', 'appyn' ),)
		);
	}
	public function widget($args, $instance) {
		extract($args);
		$title = '';
		if( isset($instance['title']) ){
			$title = apply_filters('widget_title', $instance['title']);	
		} else {
			$title = __( 'Apps más calificadas', 'appyn' );
		}
		echo $before_widget; 
		echo $before_title.$title.$after_title; 
		echo '<div class="widget-content">';
		global $wpdb, $post;
		$n = 0;
		
		$args = array( 'meta_key' => 'new_rating_users', 'orderby' => 'meta_value_num', 'ignore_sticky_posts' => 1 );

		if( isset($instance['cantidad']) ) {
			$args['posts_per_page'] = ( $instance['cantidad'] ) ? $instance['cantidad'] : 5;
		}

		if( isset($instance['categoria']) ) {
			$args['cat'] = array($instance['categoria']);
		}

		if( appyn_options( 'versiones_mostrar_widgets') == 1 ) {
			$args['post_parent'] = 0;
		}

		$ms = new WP_Query($args);
		echo '<ul>';
		if( $ms->have_posts() ) :
			while( $ms->have_posts() ) : $ms->the_post();
				get_template_part('template-parts/loop/app-widget');
			endwhile; 
		else:
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
		$instance['categoria'] = $new_instance['categoria'];
		if(!is_numeric(strip_tags($new_instance['cantidad']))){
			$instance['cantidad'] = '0';
		}		
		return $instance;
	}
	public function form($instance) {		
        $defaults = array( 'title' => __( 'Apps más calificadas' , 'appyn' ), 'cantidad' => 5 );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		 <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Título', 'appyn' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
         </p> 
         <p>
            <label for="<?php echo $this->get_field_id('cantidad'); ?>"><?php echo __( 'De una categoría', 'appyn' ); ?>:</label>
            <select name="<?php echo $this->get_field_name('categoria'); ?>">
	            	<option value="0"><?php echo __( 'Ninguna', 'appyn' ); ?></option>
            	<?php $categories = get_categories();
				foreach($categories as $category) { 
					if( isset($instance['categoria']) ) {
						if( $instance['categoria'] == $category->term_id ) { ?>
	            		<option value="<?php echo $category->term_id; ?>" selected><?php echo $category->name; ?></option>
                    <?php }
					} else { ?>
	            		<option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
                    <?php } ?>
                <?php } ?>
            </select>
         </p> 
         <p>
            <label for="<?php echo $this->get_field_id('cantidad'); ?>"><?php echo __( 'Número de entradas a mostrar', 'appyn' ); ?>:</label>
            <input id="<?php echo $this->get_field_id('cantidad'); ?>" name="<?php echo $this->get_field_name('cantidad'); ?>" type="text" value="<?php echo esc_attr($instance["cantidad"]); ?>" size="3" maxlength="2" />
         </p> 
         <?php 
	}
}

add_action('widgets_init', function() { register_widget("mas_calificados_Widget"); });