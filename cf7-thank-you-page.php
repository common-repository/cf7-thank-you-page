<?php
/**
 * Plugin Name:       CF7 Thank You Page
 * Plugin URI:
 * Description:       Configure CF7 Thank You Page for each form
 * Version:           1.0
 * Author:            KrishaWeb
 * Author URI:        https://www.krishaweb.com/
 * Text Domain:       cf7-thank-you-page
 * Domain Path:       /languages
 *
 * @package           Contact_Form_7_Redirect
 * @subpackage        Contact_Form_7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CF7_REDIRECT_VERSION', '1.0' );

// Include File for check contact form is present and active.
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Include class file.
require_once 'includes/class-cf7-thank-you-page.php';

/**
 * Plugin activation.
 */
function cf7_redirect_activation() {
	// Code here...
}
register_activation_hook( __FILE__, 'cf7_redirect_activation' );

/**
 * Plugin deactivation.
 */
function cf7_redirect_deactivation() {
	// Code here...
}
register_deactivation_hook( __FILE__, 'cf7_redirect_deactivation' );

/**
 * Plugin uninstallation.
 */
function cf7_redirect_uninstallation() {
	// Code here...
}
register_uninstall_hook( __FILE__, 'cf7_redirect_uninstallation' );

/**
 * Initialize.
 */
function cf7_redirect_initialize() {
	// Register taxtdomain.
	load_plugin_textdomain( 'cf7-thank-you-page', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Init hooks.
	$cf7_redirect = new Contact_Form_7_Redirect();
}
add_action( 'plugins_loaded', 'cf7_redirect_initialize' );
