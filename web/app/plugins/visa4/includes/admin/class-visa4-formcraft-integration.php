<?php
/**
 * Visa4 Formcraft integration
 */

defined( 'ABSPATH' ) || exit;

class VISA4_Formcraft_Integration {
	public function __construct() {
		add_action( 'formcraft_addon_init' , array( $this, 'visa4_formcraft_addon' ));
	}

	public function visa4_formcraft_addon() {
		register_formcraft_addon( 'visa4_fc_addon_output', 0, 'Visa4 Countries', false );
	}

	public function output() {
		include ( dirname( __FILE__ ) . '/views/html-visa4-fc-addon.php' );
	}
}

$GLOBALS['integrator'] = new VISA4_Formcraft_Integration();

function visa4_fc_addon_output() {
	$GLOBALS['integrator']->output();
}