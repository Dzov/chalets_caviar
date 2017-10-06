<?php
/**
 * Plugin Name: WP Immo
 * Description: Manage your real estate in WordPress.
 * Version: 1.1.3
 * Author: CVMH solutions
 * Author URI: http://www.agence-web-cvmh.fr
 * Text Domain: wp-immo
 */

defined( 'ABSPATH' ) or exit;

define( 'WPIMMO_PLUGIN_NAME', 'wp-immo');
define( 'WPIMMO_POST_TYPE', 'property');

require_once( plugin_dir_path( __FILE__ ) . 'includes/wpimmo.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/wpimmo-widgets.php' );

register_deactivation_hook( __FILE__, array( 'WPImmo', 'deactivation' ) );
register_uninstall_hook(    __FILE__, array( 'WPImmo', 'uninstall' ) );

add_action( 'init', array( 'WPImmo', 'init' ) );