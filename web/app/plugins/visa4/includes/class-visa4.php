<?php

/**
 * Visa4 setup
 */

defined( 'ABSPATH' ) || exit;

final class Visa4 {

    /**
     * String constants
     */
    const COUNTRY_META_KEY = 'visa4_country';
    const SOURCE_COUNTRIES_META_KEY = 'visa4_source_countries';
    const FORM_META_KEY = 'visa4_form_id';

	/**
	 * Visa4 version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Countries instance.
	 *
	 * @var VISA4_Countries
	 */
	public $countries = null;

    /**
     * Countries Manager
     *
     * @var VISA4_Countries_Manager
     */
    public $countries_manager = null;

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
		$this->define( 'VISA4_VERSION', $this->version );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( 'VISA4_Shortcodes', 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'integrations' ) );
	}

	/**
	 * Init Visa4 Plugin when WordPress Initialises.
	 */
	public function init() {

		// Set up localisation.
		$this->load_plugin_textdomain();

		$this->countries = new VISA4_Countries();
		$this->countries_manager = new VISA4_Countries_Manager();
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
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}

		return false;
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {
		include_once VISA4_ABSPATH . 'includes/visa4-core-functions.php';
		include_once VISA4_ABSPATH . 'includes/visa4-formatting-functions.php';

		include_once VISA4_ABSPATH . 'includes/class-visa4-ajax.php';
		include_once VISA4_ABSPATH . 'includes/class-visa4-countries.php';
		include_once VISA4_ABSPATH . 'includes/class-visa4-shortcodes.php';

		if ( $this->is_request( 'admin' ) ) {
			include_once VISA4_ABSPATH . 'includes/admin/class-visa4-admin.php';
		}

		if ( $this->is_request( 'frontend' ) ) {
			include_once VISA4_ABSPATH . 'includes/class-visa4-frontend-scripts.php';
            include_once VISA4_ABSPATH . 'includes/class-visa4-form-handler.php';
		}
	}

    /**
     * Include integrations with other plugins.
     */
    public static function integrations() {
        // FormCraft 3 Integration
        if ( function_exists( 'register_formcraft_addon' ) ) {
            include_once VISA4_ABSPATH . 'includes/integrations/class-visa4-formcraft-integration.php';
        }

        // WooCommerce Integration
        if ( function_exists( 'WC' ) ) {
            include_once VISA4_ABSPATH . 'includes/integrations/class-visa4-woocommerce-integration.php';
        }

        // WPBakery (JS Composer) Integration
        if ( class_exists( 'Vc_Manager' ) ) {
            include_once VISA4_ABSPATH . 'includes/integrations/class-visa4-jscomposer-integration.php';
        }

        include_once VISA4_ABSPATH . 'includes/class-visa4-countries-manager.php';
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
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', VISA4_PLUGIN_FILE ) );
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