<?php
/**
 * Visa4 Countries Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VISA4_Settings_Countries', false ) ) {
	return new VISA4_Settings_Countries();
}

/**
 * VISA4_Settings_Countries.
 */
class VISA4_Settings_Countries extends VISA4_Settings_Tab {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'countries';
		$this->label = __( 'Countries' );

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
				'title' => __( 'Visa Mappings' ),
				'type'  => 'title',
				'desc'  => __( 'Use this to add or remove mappings between countries that need visa' ),
				'id'    => 'visa_mappings',
			),

			array(
				'title'    => __( 'Country / State' ),
				'desc'     => __( 'The country and state or province, if any, in which your business is located.' ),
				'id'       => 'visa4_country',
				'default'  => 'GB',
				'type'     => 'single_select_country',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Postcode / ZIP' ),
				'desc'     => __( 'The postal code, if any, in which your business is located.' ),
				'id'       => 'visa4_zip',
				'css'      => 'min-width:50px;',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'type' => 'sectionend',
				'id'   => 'visa_mappings',
			),

			array(
				'title' => __( 'General options' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'general_options',
			),

			array(
				'title'    => __( 'Selling location(s)' ),
				'desc'     => __( 'This option lets you limit which countries you are willing to sell to.' ),
				'id'       => 'woocommerce_allowed_countries',
				'default'  => 'all',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width: 350px;',
				'desc_tip' => true,
				'options'  => array(
					'all'        => __( 'Sell to all countries' ),
					'all_except' => __( 'Sell to all countries, except for&hellip;' ),
					'specific'   => __( 'Sell to specific countries' ),
				),
			),

			array(
				'title'   => __( 'Sell to all countries, except for&hellip;' ),
				'desc'    => '',
				'id'      => 'woocommerce_all_except_countries',
				'css'     => 'min-width: 350px;',
				'default' => '',
				'type'    => 'multi_select_countries',
			),

			array(
				'title'   => __( 'Sell to specific countries' ),
				'desc'    => '',
				'id'      => 'woocommerce_specific_allowed_countries',
				'css'     => 'min-width: 350px;',
				'default' => '',
				'type'    => 'multi_select_countries',
			),


			array(
				'title'    => __( 'Enable taxes' ),
				'desc'     => __( 'Enable tax rates and calculations' ),
				'id'       => 'woocommerce_calc_taxes',
				'default'  => 'no',
				'type'     => 'checkbox',
				'desc_tip' => __( 'Rates will be configurable and taxes will be calculated during checkout.' ),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_options',
			)
		);

		return apply_filters( 'visa4_get_settings_' . $this->id, $settings );
	}
}

return new VISA4_Settings_Countries();
