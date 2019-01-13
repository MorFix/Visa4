<?php
/**
 * Load assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'VISA4_Admin_Assets', false ) ) {
	return;
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
		wp_register_script( 'visa4-countries-settings', Visa4()->plugin_url() . '/assets/js/admin/countries-settings.js', array( 'jquery', 'wp-util', 'backbone' ), VISA4_VERSION );
	}
}

return new WC_Admin_Assets();
