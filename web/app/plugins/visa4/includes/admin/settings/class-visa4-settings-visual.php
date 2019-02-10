<?php
/**
 * Visa4 Visual Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VISA4_Settings_Visual', false ) ) {
	return new VISA4_Settings_Visual();
}

/**
 * VISA4_Settings_Visual.
 */
class VISA4_Settings_Visual extends VISA4_Settings_Tab {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'visual';
		$this->label = __( 'Visual' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array(
			array(
				'title' => __( 'Single Country page' ),
				'type'  => 'title',
				'desc'  => __( 'Customize the single country page' ),
				'id'    => 'single_country',
			),

			array(
				'title'   => __( 'Product Base page' ),
				'desc'    => __( 'This is the page which every product (country) page relies on it\'s template' ),
				'id'      => 'visa4_product_base_page',
				'default' => '',
				'type'    => 'single_select_page',
				'desc_tip' => true
			),

			array(
				'type' => 'sectionend',
				'id'   => 'single_country',
			)
		);

		return apply_filters( 'visa4_get_settings_' . $this->id, $settings );
	}
}

return new VISA4_Settings_Visual();
