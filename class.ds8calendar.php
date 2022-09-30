<?php

class DS8Calendar {

        private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
                        add_filter( 'load_textdomain_mofile', array('DS8Calendar','ds8calendar_textdomain'), 10, 2 );
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;
                self::set_locale();
                
                include_once( 'includes/class-ds8-post-types.php' );
                DS8_Post_types::register_post_types();
                DS8_Post_types::register_taxonomies();
                include_once( 'ds8-calendars.php' );
                
                add_action('wp_enqueue_scripts', array('DS8Calendar', 'ds8_calendar_javascript'), 10);
                add_shortcode( 'ds8calendar', array('DS8Calendar', 'ds8calendar_shortcode_fn') );
                add_filter( "next_post_link", array('DS8Calendar','calendar_link'), 10, 5 );
                add_filter( "previous_post_link", array('DS8Calendar','calendar_link'), 10, 5 );
	}
        
        /**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0
	 */
	private static function set_locale() {
		load_plugin_textdomain( 'ds8calendar', false, plugin_dir_path( dirname( __FILE__ ) ) . '/languages/' );

	}
        
        public static function ds8calendar_textdomain( $mofile, $domain ) {
                if ( 'ds8calendar' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
                        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
                        $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
                }
                return $mofile;
        }
        
        public static function calendar_link($output, $format, $link, $post, $rel){
          if ( ! $post ) {
              $output = '';
          } else {
              
              if ($post->post_type === 'calendar') {
                preg_match_all('/\b\d{4}\b/ui', $post->post_title, $matches);
                $string = '<a href="' . get_permalink( $post ) . '" rel="' . $rel . '">';
                $inlink = str_replace( '%link', $matches[0][0], $format );
                $output = $string . $inlink . '</a>';
              }
          }
          return $output;
        }
        
        public static function ds8calendar_shortcode_fn( $atts ) {
          
            $post_id = get_the_ID();
            
            $result = get_post_meta( $post_id, '_ds8_calendar_meta_value_key', true );
            $combined = json_decode($result,true);

            $atts = shortcode_atts( array(
                    'year' => '2022',
                    
            ), $atts, 'fdcalendarios' );

            $calendar = new FDCalendar($combined);
            return  $calendar->show($atts['year'], $post_id);
        }
        
        /**
	 * Check if plugin is active
	 *
	 * @since    1.0
	 */
	private static function is_plugin_active( $plugin_file ) {
		return in_array( $plugin_file, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

        public static function ds8_calendar_javascript(){
          
            wp_enqueue_style( 'jquery-ui-smoothness', // wrapped for brevity
                        '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', [], null );
            wp_enqueue_style('ds8calendar-css', plugin_dir_url( __FILE__ ) . '_inc/front-calendar.css', array(), DS8CALENDAR_VERSION);
            wp_register_script( 'front-ds8calendar.js', plugin_dir_url( __FILE__ ) . '_inc/front-calendar.js', array('jquery','jquery-ui-tooltip'), DS8CALENDAR_VERSION, true );
            wp_enqueue_script( 'front-ds8calendar.js' );
        }

        public static function view( $name, array $args = array() ) {
                $args = apply_filters( 'ds8calendar_view_arguments', $args, $name );

                foreach ( $args AS $key => $val ) {
                        $$key = $val;
                }

                load_plugin_textdomain( 'ds8calendar' );

                $file = DS8CALENDAR__PLUGIN_DIR . 'views/'. $name . '.php';

                include( $file );
	}
        
        public static function plugin_deactivation( ) {
            unregister_post_type( 'calendar' );
            flush_rewrite_rules();
        }

        /**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], DS8CALENDAR__MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'ds8calendar' );
                        
			$message = '<strong>'.sprintf(esc_html__( 'FD Estadisticas %s requires WordPress %s or higher.' , 'ds8calendar'), DS8CALENDAR_VERSION, DS8CALENDAR__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version, or <a href="%2$s">downgrade to version 2.4 of the Akismet plugin</a>.', 'ds8calendar'), 'https://codex.wordpress.org/Upgrading_WordPress', 'https://wordpress.org/extend/plugins/ds8calendar/download/');

			DS8Calendar::bail_on_activation( $message );
		} elseif ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
                        flush_rewrite_rules();
			add_option( 'Activated_DS8Calendar', true );
		}
	}

        private static function bail_on_activation( $message, $deactivate = true ) {
?>
<!doctype html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<style>
* {
	text-align: center;
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
}
p {
	margin-top: 1em;
	font-size: 18px;
}
</style>
</head>
<body>
<p><?php echo esc_html( $message ); ?></p>
</body>
</html>
<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$ds8calendar = plugin_basename( DS8CALENDAR__PLUGIN_DIR . 'ds8calendar.php' );
			$update  = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $ds8calendar ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit;
	}

}