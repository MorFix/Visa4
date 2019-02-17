<?php
/**
 * Load assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'VISA4_Admin_Assets', false ) ) {
	return new VISA4_Admin_Assets();
}

/**
 * VISA4_Admin_Assets Class.
 */
class VISA4_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {

	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
        wp_register_script( 'jquery-blockui', Visa4()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'visa4-countries-settings', Visa4()->plugin_url() . '/assets/js/admin/countries-settings.js', array( 'jquery', 'wp-util', 'backbone', 'underscore', 'jquery-ui-sortable', 'jquery-blockui' ), VISA4_VERSION, true );
	}
}

return new VISA4_Admin_Assets();
