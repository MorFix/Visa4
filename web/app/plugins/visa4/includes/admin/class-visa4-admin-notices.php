<?php
/**
 * Display notices in admin
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Admin_Notices {
	/**
	 * Stores notices.
	 */
	private static $notices = array();

	/**
	 * Constructor.
	 */
	public static function init() {
		self::ensure_dependencies();

		add_action( 'admin_print_styles', array( __CLASS__, 'print_notices' ) );
	}

	/**
	 * Printing admin notices
	 */
	public static function ensure_dependencies() {

	}

	/**
	 * Show a notice.
	 *
	 * @param string $name Notice name.
	 * @param string $message The content.
	 */
	public static function add_notice( $name, $message ) {
		self::$notices[$name] = $message;
	}

	/**
	 * Prints all notices
	 */
	public static function print_notices() {
		foreach (self::$notices as $message) {
			include dirname( __FILE__ ) . '/views/html-notice-error.php';
		}
	}
}

VISA4_Admin_Notices::init();