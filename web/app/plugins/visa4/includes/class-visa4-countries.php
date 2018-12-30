<?php
/**
 * Visa4 Countries Class
 */

defined( 'ABSPATH' ) || exit;

/**
 * @property array countries
 * @property array states
 */
class VISA4_Countries {
	/**
	 * Auto-load in-accessible properties on demand.
	 *
	 * @param  mixed $key Key.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( 'countries' === $key ) {
			return $this->get_countries();
		} elseif ( 'states' === $key ) {
			return $this->get_states();
		}
	}

	/**
	 * Get all countries.
	 *
	 * @return array
	 */
	public function get_countries() {
		if ( empty( $this->countries ) ) {
			$this->countries = include VISA4()->plugin_path() . '/i18n/countries.php';
		}

		return $this->countries;
	}

	/**
	 * Get the states for a country.
	 *
	 * @param  string $country_code Country code.
	 *
	 * @return false|array of states
	 */
	public function get_states( $country_code = null ) {
		if ( empty( $this->states ) ) {
			$this->load_states_for_countries();
		}

		if ( ! is_null( $country_code ) ) {
			return isset( $this->states[ $country_code ] ) ? $this->states[ $country_code ] : false;
		} else {
			return $this->states;
		}
	}

	/**
	 * Load the states.
	 */
	public function load_states_for_countries() {
		global $states;

		// States set to array() are blank i.e. the country has no use for the state field.
		$states = array(
			'AF' => array(),
			'AT' => array(),
			'AX' => array(),
			'BE' => array(),
			'BH' => array(),
			'BI' => array(),
			'CZ' => array(),
			'DE' => array(),
			'DK' => array(),
			'EE' => array(),
			'FI' => array(),
			'FR' => array(),
			'GP' => array(),
			'GF' => array(),
			'IS' => array(),
			'IL' => array(),
			'IM' => array(),
			'KR' => array(),
			'KW' => array(),
			'LB' => array(),
			'LU' => array(),
			'MQ' => array(),
			'MT' => array(),
			'NL' => array(),
			'NO' => array(),
			'PL' => array(),
			'PT' => array(),
			'RE' => array(),
			'SG' => array(),
			'SK' => array(),
			'SI' => array(),
			'LK' => array(),
			'SE' => array(),
			'VN' => array(),
			'YT' => array(),
		);

		foreach ( new DirectoryIterator( VISA4()->plugin_path() . '/i18n/states' ) as $file ) {
			if ( ! $file->isDot() ) {
				/** @noinspection PhpIncludeInspection */
				include $file->getPathname();
			}
		}

		$this->states = $states;
	}

	/**
	 * Outputs the list of countries and states for use in dropdown boxes.
	 *
	 * @param string $selected_country Selected country.
	 * @param string $selected_state   Selected state.
	 * @param bool   $escape           If should escape HTML.
	 */
	public function country_dropdown_options( $selected_country = '', $selected_state = '', $escape = false ) {
		if ( $this->countries ) {
			foreach ( $this->countries as $key => $value ) {
				$states = $this->get_states( $key );
				if ( $states ) {
					echo '<optgroup label="' . esc_attr( $value ) . '">';
					foreach ( $states as $state_key => $state_value ) {
						echo '<option value="' . esc_attr( $key ) . ':' . esc_attr( $state_key ) . '"';

						if ( $selected_country === $key && $selected_state === $state_key ) {
							echo ' selected="selected"';
						}

						echo '>' . esc_html( $value ) . ' &mdash; ' . ( $escape ? esc_js( $state_value ) : $state_value ) . '</option>'; // WPCS: XSS ok.
					}
					echo '</optgroup>';
				} else {
					echo '<option';
					if ( $selected_country === $key && '*' === $selected_state ) {
						echo ' selected="selected"';
					}
					echo ' value="' . esc_attr( $key ) . '">' . ( $escape ? esc_js( $value ) : $value ) . '</option>'; // WPCS: XSS ok.
				}
			}
		}
	}

	public function get_countries_with_visa_setting() {
		return array(
			'IL' => 'Israel'
		);
	}
}