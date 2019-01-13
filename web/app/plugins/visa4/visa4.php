<?php
/*
Plugin Name: Visa4
Description: The Visa4 Custom plugin
Author: Mor Cohen
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define VISA4_PLUGIN_FILE.
if ( ! defined( 'VISA4_PLUGIN_FILE' ) ) {
	define( 'VISA4_PLUGIN_FILE', __FILE__ );
}

// Include the main Visa4 class.
if ( ! class_exists( 'Visa4' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-visa4.php';
}

/**
 * Main instance of Visa4.
 *
 * Returns the main instance of Visa4
 *
 * @return Visa4
 */
function visa4() {
	return Visa4::instance();
}

visa4();