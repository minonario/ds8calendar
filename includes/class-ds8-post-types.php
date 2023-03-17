<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies
 *
 * @class       DS8_Post_types
 * @version     1.0
 * @package     DS8/Classes/Calendar
 * @category    Class
 * @author      Jose Luis Morales
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DS8_Post_types Class
 */
class DS8_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
                add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {

		if ( post_type_exists('calendar') ) {
			return;
		}

		$permalinks = get_option( 'ds8calendar_permalinks' );
		$calendar_permalink = empty( $permalinks['calendar_base'] ) ? _x( 'calendar', 'slug', 'ds8calendar' ) : $permalinks['calendar_base'];

		register_post_type( 'calendar',
			apply_filters( 'ds8calendar_register_post_type_calendar',
				array(
                                    'labels'  => array(
                                                    'name'               => __( 'Calendars', 'ds8calendar' ),
                                                    'singular_name'      => __( 'Calendar', 'ds8calendar' ),
                                                    'menu_name'          => _x( 'Calendars', 'Admin menu name', 'ds8calendar' ),
                                                    'add_new'            => __( 'Add new', 'ds8calendar' ),
                                                    'add_new_item'       => __( 'Add new Calendar', 'ds8calendar' ),
                                                    'edit'               => __( 'Edit', 'ds8calendar' ),
                                                    'edit_item'          => __( 'Edit Calendar', 'ds8calendar' ),
                                                    'new_item'           => __( 'New Calendar', 'ds8calendar' ),
                                                    'view'               => __( 'View', 'ds8calendar' ),
                                                    'view_item'          => __( 'View Calendar', 'ds8calendar' ),
                                                    'search_items'       => __( 'Search Calendar', 'ds8calendar' ),
                                                    'not_found'          => __( 'Not found Calendar', 'ds8calendar' ),
                                                    'not_found_in_trash' => __( 'Not found Calendar in trash', 'ds8calendar' ),
                                                    'parent'             => __( 'Parent Calendar', 'ds8calendar' )
						),
                                    'description'         => __( 'This is where you can add new Calendars.', 'ds8calendar' ),
                                    'public'              => true,
                                    'show_ui'             => true,
                                    'show_in_menu'        => true,
                                    'show_in_nav_menus'   => true,
                                    'capability_type'     => 'post',
                                    'map_meta_cap'        => true,
                                    'publicly_queryable'  => true,
                                    'exclude_from_search' => false,
                                    'hierarchical'        => false,
                                    'menu_icon'           => 'dashicons-calendar-alt',
                                    //'taxonomies'          => array('category'),
                                    'rewrite'             => $calendar_permalink ? array( 'slug' => untrailingslashit( $calendar_permalink ), 'with_front' => false, 'feeds' => false ) : false,
                                    'query_var'           => true,
                                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'author' ),
                                    'has_archive'         => false
				)
			)
		);
	}
        
        public static function register_taxonomies() {
            
		if ( taxonomy_exists( 'calendar_cat' ) ) {
			return;
		}
                
                $permalinks = get_option( 'ds8calendar_permalinks' );
                
		register_taxonomy( 'calendar_cat',
                                 'calendar',
			apply_filters( 'ds8_taxonomy_args_calendar_cat', array(
				'hierarchical'          => true,
                                //'update_count_callback' => '_sc_term_recount',
                                //'has_archive'           => true,
				'label'                 => __( 'Calendar Categories', 'ds8calendar' ),
				'labels' => array(
						'name'              => __( 'Calendar Categories', 'ds8calendar' ),
						'singular_name'     => __( 'Calendar Category', 'ds8calendar' ),
                                                'menu_name'         => _x( 'Categories', 'Admin menu name', 'ds8calendar' ),
						'search_items'      => __( 'Search Calendar Category', 'ds8calendar' ),
						'all_items'         => __( 'All Categories', 'ds8calendar' ),
						'parent_item'       => __( 'Parent Calendar Category', 'ds8calendar' ),
						'parent_item_colon' => __( 'Parent Calendar Category:', 'ds8calendar' ),
						'edit_item'         => __( 'Edit Category', 'ds8calendar' ),
						'update_item'       => __( 'Update Category', 'ds8calendar' ),
						'add_new_item'      => __( 'Add new Calendar Category', 'ds8calendar' ),
						'new_item_name'     => __( 'New Calendar Category', 'ds8calendar' )
					),
				'show_ui'               => true,
				'query_var'             => true,
                                /*,
				'capabilities'          => array(
					'manage_terms' => 'manage_product_terms',
					'edit_terms'   => 'edit_product_terms',
					'delete_terms' => 'delete_product_terms',
					'assign_terms' => 'assign_product_terms',
				),*/
				'rewrite'               => array(
					'slug'         => empty( $permalinks['category_base'] ) ? _x( 'calendar-category', 'slug', 'ds8calendar' ) : $permalinks['category_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			) )
		);
	}

}

DS8_Post_types::init();
