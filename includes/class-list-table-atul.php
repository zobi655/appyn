<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class List_Table_ATUL extends WP_List_Table {

	var $total_posts;
	var $posts;
	var $has_title = false;

	public function __construct() {

		parent::__construct( [
			'singular' => 'app_to_update',
			'plural'   => 'apps_to_update',
			'ajax'     => false
		] );
		$posts = get_option( 'trans_updated_apps', null );

		if( ! $posts ) return;

		foreach( $posts as $k => $p ) {
			if( ! isset($p['post_title']) ) continue;

			$this->has_title = true;
		}

		if( !$this->has_title ) {
			px_process_list_apps();
			$posts = get_option( 'trans_updated_apps' );
		}

		$s = ( isset($_GET['s']) ) ? $_GET['s'] : '';
		if( $s ) {
			foreach( $posts as $k => $p ) {
				if( strpos(strtolower($p['post_title']), $s) === false ) {
					unset($posts[$k]);
				}
			}
		}
		 
		$this->posts = $posts;

		$this->total_posts = ($posts) ? count($posts) : 0;
	}

	public function get_apps_to_update( $per_page = 20, $page_number = 1 ) {

		$posts = $this->posts;

		if( !$posts ) return;

		foreach( $posts as $key => $p ) {
			$posts[$key]['app_id'] = $key;
		}
		
		$offset = ($page_number - 1) * $per_page;
		usort($posts, function($a, $b) {
			return $b['update'] <=> $a['update'];
		});
		
        if( ! empty( $_REQUEST['orderby'] ) ) {
			if( $_REQUEST['orderby'] == "updated_date" ) {
				if( $_REQUEST['order'] == "asc" ) {
					usort($posts, function($a, $b) {
						return $a['update'] <=> $b['update'];
					});
				}
			}
        }

		$posts = array_slice($posts, $offset, $per_page);

		return $posts;
	}

	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'apps_to_update',  $item['post_id']
        );
    }
	public function no_items() {
		echo __( 'No hay apps para actualizar', 'appyn' );
	}

	public function column_default( $item, $column_name ) {
        
		switch ( $column_name ) {

			case 'app_image':
				return '<img src="'.get_the_post_thumbnail_url( $item['post_id'], 'miniatura' ).'" width="50" style="display:inline-block; vertical-align:middle;">';

			case 'post_title':
				return '<a href="'.get_edit_post_link( $item['post_id'] ).'" target="_blank" title="'. __( 'Editar post', 'appyn' ) .'">'.$item[$column_name].'</a> <a href="https://play.google.com/store/apps/details?id='.$item['app_id'].'" target="_blank" title="'. __( 'Ver en Google Play', 'appyn' ) .'"><img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAQCAMAAAD+iNU2AAAAyVBMVEUAAAAAzf8A/i//2AAA/2L/Plj/MjMA4P8Ayf8AxP8A/yv/MTAA9EwA0v8A+ScA0v/+Q03/My3/1QD/0AD/0wD/3AAA/wD/CgMA/yn/MjMA/y7/MDAA/zf/JTH/1gAA/wD/CAP/0wAA/y7/MTEA/y0Azv8Ay/8A+EoAxf8A/wD/CwP/1gAA5/8A5f8A2v//Pkn/OkP/0QAAwf8A1/0A1fsA/yoA/0H/DTz/KTj/NzP/MzP/AA7/9wD/6QDx6QD02QD/ygD/xAD/swCt0I1nAAAAJXRSTlMA8f0t/v708fHx7OLe29rY1s7EurWtrKGShXZoWEpAKiMdFxAJTZezOAAAAItJREFUCNdNzlcWgyAQBdBJTO+990RMIIKY3qP7X5QoeGD+7jvToFlbglll5Aw2hjN75BxGO207DrIT7SS4VGapVXBtLFLLkR/vWMq2T3PfPyGkK42Ofj58c8L7wjHP3usefHpr0S+JH7e2Jfch9+ThZ3Wu7gkyXJiCqpJLGR7r/4uUDbeaUG+tDEEEzLgTqZH0pKcAAAAASUVORK5CYII=" alt="Google Play" style="height:10px;"></a>';
				break;
			
			case 'current_version':
				$di = get_post_meta( $item['post_id'], 'datos_informacion', true );
				$current_version = isset($di['version']) ? $di['version'] : '';
				return $current_version;
				break;
		
			case 'version':
				return $item[$column_name];
				break;

            case 'updated_date':
                $results = get_option( 'trans_updated_apps' );

                foreach( $results as $app ) {
                    if( $app['post_id'] == $item['post_id'] )
                        return date( 'Y/m/d H:i', strtotime($app['update']) );
                }
				break;

			case 'update':
				return '<div class="atul_update_app"><a href="javascript:void(0);" data-id="'.$item['post_id'].'" class="button button-secondary">'.__( 'Actualizar', 'appyn' ).'</a></div>';
				break;


			default:
				return '';
		}
	}

	public function get_columns() {
		$columns = [
            'cb'    => '<input type="checkbox" />',
            'id'    => 'ID',
			'app_image'		=> '',
			'post_title'    => __( 'App', 'appyn' ),
			'current_version'    => __( 'Versión actual', 'appyn' ),
			'version'    => __( 'Nueva Versión', 'appyn' ),
			'updated_date'    => __( 'Fecha de actualización', 'appyn' ),
			'update'    => '',
		];

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'updated_date' => array( 'updated_date', true )
		);

		return $sortable_columns;
	}

    public function get_bulk_actions() {
        return array(
			'update' => __( 'Actualizar', 'appyn' ),
        );
    }

    public function search_box( $text, $input_id ) {
 
        $input_id = $input_id . '-search-input';
 
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['detached'] ) ) {
            echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
        }
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
        <?php
    }
	
	public function prepare_items() {

        $columns = $this->get_columns(); 
        $hidden   = array( 'id' );
        $sortable =  $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable ); 

		$per_page     = $this->get_items_per_page( 'apps_to_update_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->total_posts ? $this->total_posts : 0;

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		] );

		echo '<ul class="subsubsub">
			<li class="all"><a href="'.remove_query_arg( 's' ).'"'.(! get_query_var('s') ? ' class="current"' : '').'>'.__( 'Todos', 'appyn' ).' <span class="count">('.$total_items.')</span></a></li>
		</ul>';
		
		$this->items = self::get_apps_to_update( $per_page, $current_page );
	}

}