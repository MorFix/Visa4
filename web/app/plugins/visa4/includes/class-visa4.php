<?php

/**
 * Visa4 setup
 */

defined( 'ABSPATH' ) || exit;

final class Visa4 {

	/**
	 * Countries instance.
	 *
	 * @var VISA4_Countries
	 */
	public $countries = null;

	/**
	 * The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Main Visa4 Instance.
	 *
	 * Ensures only one instance of Visa4 is loaded or can be loaded.
	 *
	 * @static
	 * @see Visa4()
	 * @return Visa4 - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Visa4 Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define Visa4 Constants.
	 */
	private function define_constants() {
		$this->define( 'VISA4_ABSPATH', dirname( VISA4_PLUGIN_FILE ) . '/' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Init Visa4 Plugin when WordPress Initialises.
	 */
	public function init() {

		// Set up localisation.
		$this->load_plugin_textdomain();

		$this->countries = new VISA4_Countries();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/visa4/visa4-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/visa4-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

		unload_textdomain( 'visa4' );
		load_textdomain( 'visa4', WP_LANG_DIR . '/visa4/visa4-' . $locale . '.mo' );
		load_plugin_textdomain( 'visa4', false, plugin_basename( dirname( VISA4_PLUGIN_FILE ) ) . '/i18n/languages' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {
		include_once VISA4_ABSPATH . 'includes/visa4-core-functions.php';
		include_once VISA4_ABSPATH . 'includes/visa4-formatting-functions.php';

		include_once VISA4_ABSPATH . 'includes/class-visa4-ajax.php';
		include_once VISA4_ABSPATH . 'includes/class-visa4-frontend-scripts.php';
		include_once VISA4_ABSPATH . 'includes/class-visa4-countries.php';

		if ( is_admin() ) {
			include_once VISA4_ABSPATH . 'includes/admin/class-visa4-admin.php';
		}
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( VISA4_PLUGIN_FILE ) );
	}
}