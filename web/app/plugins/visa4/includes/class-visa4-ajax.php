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
        /**
         * @var $wp_query WP_Query
         */
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
            'save_admin_countries_settings' => false
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

    /**
     * Handle submissions from assets/js/admin/countries-settings.js Backbone model.
     */
    public static function save_admin_countries_settings() {
        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error( 'missing_capabilities' );
            wp_die();
        }

        if ( ! isset( $_POST['changes'] ) ) {
            wp_send_json_error( 'missing_fields' );
            wp_die();
        }

        $changes = $_POST['changes'];

        foreach ( $changes as $country ) {
            self::handle_country_change( $country );
        }

        wp_send_json_success(
            array(
                'countries' => Visa4()->countries_manager->get_countries_connected_to_product_full_data(),
            )
        );
    }

    private static function handle_country_change ( $country ) {
        // Checking for a valid country
        if ( !Visa4()->countries->get_countries()[ $country['country_code'] ] ) {
            wp_send_json_error( sprintf( __( 'Invalid destination country code: %s'  ), $country['country_code'] ) );
            wp_die();
        };

        $country_code = $country['country_code'];

        if ( isset( $country['deleted'] ) ) {
            self::delete_country( $country_code, isset( $country['newRow'] ) );

            return;
        }

        if ( isset( $country['source_countries'] ) && is_array( $country['source_countries'] ) ) {
            self::ensure_source_countries( $country['source_countries'] );
            $source_countries = $country['source_countries'];
        }

        if ( isset( $country['newRow'] ) ) {
            self::create_country( $country_code, isset( $source_countries ) ? $source_countries : null );

            return;
        }

        $post = Visa4()->countries_manager->get_product_by_country( $country_code );
        if ( !$post ) {
            self::send_country_error( $country_code );
        }

        if ( isset( $source_countries ) ) {
            update_post_meta( $post->ID, Visa4::SOURCE_COUNTRIES_META_KEY, $source_countries );
        }


        $error = Visa4()->countries_manager->update_form_country( absint( $country['form_id'] ), $country_code );
        if ( !empty( $error ) ) {
            wp_send_json_error( $error );
        }
    }

    /**
     * Tells the user we cannot find the specified country code
     *
     * @param $country_code - The country code
     */
    private static function send_country_error($country_code ) {
        $name = Visa4()->countries->get_countries()[ $country_code ];
        wp_send_json_error( sprintf( __( 'Cannot find product for %s' ), $name) );
        wp_die();
    }

    /**
     * Handling a deletion of a country
     *
     * @param $country_code - The request country code
     * @param $is_new - Whether the "newRow" flag is on
     */
    private static function delete_country( $country_code, $is_new )
    {
        // So the user added and deleted a new row.
        // That's fine, it's not in the database anyway. NEXT!
        if ( $is_new ) {
            return;
        }

        // Detaching this country from it's form
        Visa4()->countries_manager->detach_form( $country_code );

        // Removing the post
        $post = Visa4()->countries_manager->get_product_by_country( $country_code );
        if ( !empty( $post ) ) {
            wp_delete_post($post->ID, true);
        } else {
            self::send_country_error( $country_code );
        }

        return;
    }

    /**
     * Ensuring that all source countries are valid
     *
     * @param array $source_countries - The countries list
     */
    private static function ensure_source_countries( array $source_countries )
    {
        foreach ( $source_countries as $country_code ) {
            if (!Visa4()->countries->get_countries()[$country_code]) {
                wp_send_json_error(sprintf(__('Invalid source country code: %s'), $country_code));
                wp_die();
            }
        }
    }

    /**
     * Creating all we need for a new country
     *
     * @param $country_code - The requested country
     * @param $source_countries - The source countries that need visa for this destination
     */
    private static function create_country( $country_code, $source_countries = null )
    {
        $post = Visa4()->countries_manager->get_product_by_country( $country_code );
        if ($post) {
            $cc = get_post_meta( $post->ID, VISA4::COUNTRY_META_KEY, $country_code );
            $name = Visa4()->countries->get_countries()[$cc];

            wp_send_json_error(sprintf(__('Product already exists for %s'), $name));
        }

        /**
         * @var $result WP_Post|WP_Error
         */
        // TODO: Implement
        $id = Visa4()->countries_manager->create_product( $country_code );
        if ( is_wp_error( $id ) ) {
            wp_send_json_error($result->get_error_message());
        }

        if ( isset( $source_countries ) ) {
            update_post_meta( $id, Visa4::SOURCE_COUNTRIES_META_KEY, $source_countries );
        }
    }
}

VISA4_AJAX::init();