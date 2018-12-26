<?php
/**
 * Handle frontend scripts
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by Visa4.
	 *
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles localized by Visa4.
	 *
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function register_script( $handle, $path, $deps = array(), $version = VISA4_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array(), $version = VISA4_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		/*global $post;

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		self::register_scripts();
		self::register_styles();

		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			self::enqueue_script( 'wc-add-to-cart' );
		}
		if ( is_cart() ) {
			self::enqueue_script( 'wc-cart' );
		}
		if ( is_cart() || is_checkout() || is_account_page() ) {
			self::enqueue_script( 'selectWoo' );
			self::enqueue_style( 'select2' );

			// Password strength meter. Load in checkout, account login and edit account page.
			if ( ( 'no' === get_option( 'woocommerce_registration_generate_password' ) && ! is_user_logged_in() ) || is_edit_account_page() || is_lost_password_page() ) {
				self::enqueue_script( 'wc-password-strength-meter' );
			}
		}
		if ( is_checkout() ) {
			self::enqueue_script( 'wc-checkout' );
		}
		if ( is_add_payment_method_page() ) {
			self::enqueue_script( 'wc-add-payment-method' );
		}
		if ( is_lost_password_page() ) {
			self::enqueue_script( 'wc-lost-password' );
		}

		// Load gallery scripts on product pages only if supported.
		if ( is_product() || ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ) {
			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				self::enqueue_script( 'zoom' );
			}
			if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
				self::enqueue_script( 'flexslider' );
			}
			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
				self::enqueue_script( 'photoswipe-ui-default' );
				self::enqueue_style( 'photoswipe-default-skin' );
				add_action( 'wp_footer', 'woocommerce_photoswipe' );
			}
			self::enqueue_script( 'wc-single-product' );
		}

		if ( 'geolocation_ajax' === get_option( 'woocommerce_default_customer_address' ) ) {
			$ua = strtolower( wc_get_user_agent() ); // Exclude common bots from geolocation by user agent.

			if ( ! strstr( $ua, 'bot' ) && ! strstr( $ua, 'spider' ) && ! strstr( $ua, 'crawl' ) ) {
				self::enqueue_script( 'wc-geolocation' );
			}
		}

		// Global frontend scripts.
		self::enqueue_script( 'woocommerce' );
		self::enqueue_script( 'wc-cart-fragments' );

		// CSS Styles.
		$enqueue_styles = self::get_styles();
		if ( $enqueue_styles ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				if ( ! isset( $args['has_rtl'] ) ) {
					$args['has_rtl'] = false;
				}

				self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
			}
		}

		// Placeholder style.
		wp_register_style( 'woocommerce-inline', false );
		wp_enqueue_style( 'woocommerce-inline' );

		if ( true === wc_string_to_bool( get_option( 'woocommerce_checkout_highlight_required_fields', 'yes' ) ) ) {
			wp_add_inline_style( 'woocommerce-inline', '.woocommerce form .form-row .required { visibility: visible; }' );
		} else {
			wp_add_inline_style( 'woocommerce-inline', '.woocommerce form .form-row .required { visibility: hidden; }' );
		}*/
	}

	private static function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'visa4':
				return array(
					'ajax_url'    => Visa4()->ajax_url(),
					'visa4_ajax_url' => VISA4_AJAX::get_endpoint( '%%endpoint%%' ),
				);

			default:
				return false;
		}
	}

	/**
	 * Localize a Visa4 script once.
	 *
	 * @param string $handle Script handle the data will be attached to.
	 */
	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
			$data = self::get_script_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name                        = str_replace( '-', '_', $handle ) . '_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, $data );
		}
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}

VISA4_Frontend_Scripts::init();