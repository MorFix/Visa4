<?php
/**
 * Visa4 setup
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Admin {
	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'includes' ), 0 );
		add_action( 'plugins_loaded', array( __CLASS__, 'integrations' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		include_once dirname( __FILE__ ) . '/class-visa4-admin-assets.php';
		include_once dirname( __FILE__ ) . '/class-visa4-admin-notices.php';
		include_once dirname( __FILE__ ) . '/class-visa4-admin-menus.php';
		include_once dirname( __FILE__ ) . '/class-visa4-admin-settings.php';
	}
}

VISA4_Admin::init();