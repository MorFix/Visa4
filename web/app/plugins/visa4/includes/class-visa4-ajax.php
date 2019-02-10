<?php
/**
 * Visa4 VISA4_AJAX. AJAX Event Handlers.
 *
 * @class    VISA4_AJAX
 */

defined( 'ABSPATH' ) || exit;

class VISA4_AJAX {
	const VISA4_AJAX_SLUG = 'visa4ajax';

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_visa4_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get Visa4 Ajax Endpoint.
	 *
	 * @param  string $request Optional.
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( self::VISA4_AJAX_SLUG, $request, home_url( '/', 'relative' ) ) );
	}

	/**
	 * Set Visa4 AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET[self::VISA4_AJAX_SLUG] ) ) {
			visa4_maybe_define_constant( 'DOING_AJAX', true );
			visa4_maybe_define_constant( 'VISA4_DOING_AJAX', true );

			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Check for Visa4 Ajax request and fire action.
	 */
	public static function do_visa4_ajax() {
		global $wp_query;

		if ( ! empty( $_GET[self::VISA4_AJAX_SLUG] ) ) {
			$wp_query->set( self::VISA4_AJAX_SLUG, sanitize_text_field( wp_unslash( $_GET[self::VISA4_AJAX_SLUG] ) ) );
		}

		$action = $wp_query->get( self::VISA4_AJAX_SLUG );

		if ( $action ) {
			self::visa4_ajax_headers();
			$action = sanitize_text_field( $action );
			do_action( self::VISA4_AJAX_SLUG . '_' . $action );
			wp_die();
		}
	}

	/**
	 * Send headers for Visa4 Ajax Requests.
	 */
	private static function visa4_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// VISA4_EVENT => isAlsoForUnauthorized.
		$ajax_events = array(
			'save_admin_settings' => false,
		);

		foreach ( $ajax_events as $ajax_event => $isAlsoForUnauthorized ) {
			add_action( 'wp_ajax_visa4_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $isAlsoForUnauthorized ) {
				add_action( 'wp_ajax_nopriv_visa4_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// VISA4 AJAX can be used for frontend ajax requests.
				add_action( self::VISA4_AJAX_SLUG . '_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Ajax actions start here
	 */

	/**
	 * Saving the admin settings
	 */
	public static function save_admin_settings() {
		if ( ! current_user_can('manage_options') ) {
			wp_die(-1);
		}

		VISA4_Admin_Settings::save();
	}
}

VISA4_AJAX::init();