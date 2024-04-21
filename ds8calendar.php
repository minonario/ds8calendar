<?php
/**
 * @package DS8Calendar
 */
/*
Plugin Name: DS8 Calendar
Plugin URI: https://deseisaocho.com/
Description: Country <strong>Calendar</strong>
Version: 1.0
Author: JLMA
Author URI: https://deseisaocho.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: ds8calendar
*/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'DS8CALENDAR_VERSION', '1.1' );
define( 'DS8CALENDAR__MINIMUM_WP_VERSION', '5.0' );
define( 'DS8CALENDAR__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'DS8Calendar', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'DS8Calendar', 'plugin_deactivation' ) );

require_once( DS8CALENDAR__PLUGIN_DIR . 'class.ds8calendar.php' );

add_action( 'init', array( 'DS8Calendar', 'init' ) );

if ( is_admin() ) {
	require_once( DS8CALENDAR__PLUGIN_DIR . 'class.ds8calendar-admin.php' );
	add_action( 'init', array( 'DS8Calendar_Admin', 'init' ) );
}