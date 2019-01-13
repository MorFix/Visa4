<?php
/**
 * Visa4 Settings Page/Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'VISA4_Settings_Tab', false ) ) {
	return;
}

/**
 * VISA4_Settings_Tab.
 */
abstract class VISA4_Settings_Tab {
	/**
	 * Setting tab id.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Setting tab label.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'visa4_settings_tabs_array', array( $this, 'add_settings_tab' ), 20 );
		add_action( 'visa4_settings_' . $this->id, array( $this, 'output' ) );
	}

	/**
	 * Get settings tab ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings tab label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this tab to settings.
	 *
	 * @param array $tabs
	 * @return mixed
	 */
	public function add_settings_tab( $tabs ) {
		$tabs[ $this->id ] = $this->label;

		return $tabs;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'visa4_get_settings_' . $this->id, array() );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		VISA4_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Get the WordPress options to update
	 *
	 * @param $data - The POSTed data
	 * @return bool|array
	 */
	public function get_options_to_update( $data ) {
		$settings = $this->get_settings();

		return VISA4_Admin_Settings::get_options_to_update( $settings, $data );
	}
}