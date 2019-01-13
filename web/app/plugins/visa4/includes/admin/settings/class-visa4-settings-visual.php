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
				'title'   => __( 'Product page slider template' ),
				'desc'    => __( 'This is the content inside the slider in the product page' ),
				'id'      => 'visa4_product_slider_template',
				'default' => '',
				'type'    => 'editor',
				'desc_tip' => true
			),

			array(
				'title'   => __( 'Product page template' ),
				'desc'    => __( 'This is the content inside a product page' ),
				'id'      => 'visa4_product_template',
				'default' => '',
				'type'    => 'editor',
				'desc_tip' => true
			),

			array(
				'title'             => __( 'Product page custom CSS' ),
				'desc'              => __( 'A custom CSS code inside a product page' ),
				'id'                => 'visa4_product_css',
				'default'           => '',
				'type'              => 'textarea',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'rows'  => 10,
					'cols' => 100
				)
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
