<?php
/**
 * Setup menus in WP admin.
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Admin_Menus {

	/**
	 * Hooks in tabs
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'settings_menu' ), 9 );
	}

	/**
	 * Add menu items
	 */
	public static function settings_menu() {
		$page = add_menu_page( __('Visa4'), __('Visa4'), 'manage_options', 'visa4-settings', array( __CLASS__, 'settings_page' ), null, 5);
		add_action( 'load-' . $page, array( 'VISA4_Admin_Settings', 'get_settings_pages' ) );
	}

	/**
	 * Init the settings page.
	 */
	public static function settings_page() {
		VISA4_Admin_Settings::output();
	}
}

VISA4_Admin_Menus::init();