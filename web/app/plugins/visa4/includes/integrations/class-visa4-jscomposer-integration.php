<?php
/**
 * Visa4 WPBakery (Js Composer) integration
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VISA4_JSComposer_Integration
 */
class VISA4_JSComposer_Integration {

    /**
     * Initialize hooks
     */
	public static function init() {
		add_action( 'wp_head' , array( __CLASS__, 'visa4_product_read_page_css' ));
	}

    /**
     * Re-Add broken custom CSS
     */
	public static function visa4_product_read_page_css() {
	    if ( !apply_filters( 'visa4_should_read_custom_css', true ) ) {
	        return;
        }

        $vc = Vc_Manager::getInstance()->vc();
        $base_page_id = get_option( 'visa4_product_base_page' );

        $vc->addPageCustomCss( $base_page_id );
        $vc->addShortcodesCustomCss( $base_page_id );
	}
}

VISA4_JSComposer_Integration::init();