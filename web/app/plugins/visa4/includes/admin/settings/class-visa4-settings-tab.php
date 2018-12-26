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
	protected $id = '';

	/**
	 * Setting tab label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'visa4_settings_tabs_array', array( $this, 'add_settings_tab' ), 20 );
		add_action( 'visa4_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'visa4_settings_save_' . $this->id, array( $this, 'save' ) );
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
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		VISA4_Admin_Settings::save_fields( $settings );
	}
}