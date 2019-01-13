<?php
/**
 * Handle frontend forms.
 */

defined( 'ABSPATH' ) || exit;

/**
 * VISA4_Form_Handler class.
 */
class VISA4_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'move_to_country_page' ) );
	}

	/**
	 * Move the user to the desired country page
	 */
	public static function move_to_country_page() {
        if ( is_product() ||
            !isset( $_REQUEST['visa4_destination']) ||
            !is_numeric( $_REQUEST['visa4_destination'] ) ||
            !isset( $_REQUEST['visa4_source'] ) ) {
            return;
        }

        $url = get_permalink( absint( $_REQUEST['visa4_destination'] ) );
        $url = add_query_arg( 'visa4_source', $_REQUEST['visa4_source'], $url );

        wp_safe_redirect( esc_url( $url ) );
        exit;
	}
}

VISA4_Form_Handler::init();
