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
	 * @override
	 *
	 * Outputting the screen
	 */
	public function output() {
		wp_localize_script(
			'visa4-countries-settings', 'visa4CountriesSettingsParams', array(
				'countries'          => Visa4()->countries_manager->get_countries_connected_to_product_full_data(),
				'defaultCountry'    => array(
                    'source_countries' => array(),
				),
				'allCountries' => Visa4()->countries->get_countries(),
				'allForms' => Visa4()->countries_manager->get_forms(),
				'formsLink' => admin_url( 'admin.php?page=formcraft-dashboard' ),
				'strings'                   => array(
				    'some_prices_missing'     => __( 'Some prices are missing' ),
				    'no_prices'               => __( 'NO PRICES!' ),
				    'delete_country_confirm'  => __( 'Are you sure you want to delete {0}?' ),
					'unload_confirmation_msg' => __( 'Your changed data will be lost if you leave this page without saving' ),
					'save_failed'             => __( 'Your changes were not saved. Please retry.' ),
				),
			)
		);
		wp_enqueue_script( 'visa4-countries-settings' );

		// Extendable columns to show on the countries screen.
        /** @noinspection PhpUnusedLocalVariableInspection */
        $visa4_countries_columns = array(
			'visa4-destination-country' => __( 'Destination Country' ),
			'visa4-source-countries'    => __( 'Source Countries' ),
			'visa4-country-edit-link'   => __( 'Edit product' ),
			'visa4-country-view-link'   => __( 'View product' ),
            'visa4-country-select-form' => __( 'Custom form' ),
            'visa4-country-edit-form'   => __( 'Edit custom form' ),
		);

		include_once dirname( __FILE__ ) . '/views/html-visa4-settings-countries.php';
	}

	/**
	 * @override
	 *
	 * Preparing options to save
	 *
	 * @param $data - The POSTed data
	 * @return array
	 */
	public function get_options_to_update( $data ) {
		return [];
	}
}

return new VISA4_Settings_Countries();
