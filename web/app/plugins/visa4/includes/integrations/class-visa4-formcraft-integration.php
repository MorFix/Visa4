<?php
/**
 * Visa4 FormCraft integration
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VISA4_FormCraft_Integration
 */
class VISA4_FormCraft_Integration {

    const VISA4_FC_ADDON = 'Visa4FC';
    const VISA4_FC_ADDON_COUNTRY_KEY = 'country';

    /**
     * Initialize hooks
     */
	public static function init() {
		add_action( 'formcraft_addon_init' , array( __CLASS__, 'visa4_formcraft_addon' ));
	}

    /**
     * Init the addon into FormCraft3
     */
	public static function visa4_formcraft_addon() {
		register_formcraft_addon( array( __CLASS__, 'output' ), 0, 'Visa4 Countries', false );
	}

    /**
     * Output the select box
     */
	public static function output() {
	    $visa4_fc_addon = self::VISA4_FC_ADDON;
	    $visa4_fc_addon_country_key = self::VISA4_FC_ADDON_COUNTRY_KEY;

		include(dirname(__FILE__) . '/views/html-visa4-fc-addon.php');
	}

    /**
     * Get all Visa4 countries that are connected to a form
     *
     * @return array - The countries
     */
    public static function get_countries_connected_to_form() {
        global $wpdb, $fc_forms_table;

        $forms = $wpdb->get_results( "SELECT id, addons FROM $fc_forms_table", ARRAY_A );
        $visa4_countries = Visa4()->countries->get_countries();

        $countries = array();
        foreach ($forms as $form) {
            $addons = json_decode( stripcslashes( $form[ 'addons' ] ) , 1 );

            if ( self::is_addons_contains_valid_country( $addons ) ) {
                $countries[ $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] ] =
                    $visa4_countries[ $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] ];
            }
        }

        return $countries;
    }

    /**
     * Get all Visa4 countries that are not connected to a form
     *
     * @param string $current_form_id - (Optional) The form which we are checking for
     * @return array - The countries
     */
    public static function get_countries_no_form($current_form_id = '' )
    {
        global $wpdb, $fc_forms_table;

        $form_entries = $wpdb->get_results( "SELECT id, addons FROM $fc_forms_table", ARRAY_A );

        $countries_in_forms = array();
        foreach ($form_entries as $form_entry) {
            $addons = json_decode( stripcslashes( $form_entry[ 'addons' ] ) , 1 );

            if ( ( empty( $current_form_id ) || absint( $form_entry[ 'id' ] ) !== absint( $current_form_id ) ) &&
                 self::is_addons_contains_valid_country( $addons ) ) {
                $countries_in_forms[] = $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ];
            }
        }

        $countries = array();
        foreach ( Visa4()->countries->get_countries() as $country_code => $country_name ) {
            if ( !in_array( $country_code, $countries_in_forms ) ) {
                $countries[ $country_code ] = $country_name;
            }
        }

        return $countries;
    }

    /**
     * Get a FormCraft form ID that is connected to a requested country code
     *
     * @param $country_code - The desired country code
     * @return int - The form Id or null
     */
    public static function get_form_id( $country_code ) {
        global $wpdb, $fc_forms_table;

        $forms = $wpdb->get_results( "SELECT id, addons FROM $fc_forms_table", ARRAY_A );

        foreach ($forms as $form) {
            $addons = json_decode( stripcslashes( $form[ 'addons' ] ) , 1 );

            if ( !empty ( $addons[ self::VISA4_FC_ADDON ] ) &&
                 $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] === $country_code ) {
                return $form['id'];
            }
        }

        return null;
    }

    /**
     * Is form addons contains a valid Visa4 country?
     *
     * @param $addons
     * @return bool
     */
    private static function is_addons_contains_valid_country($addons ) {
        $country_codes = array_keys( Visa4()->countries->get_countries() );

        return !empty ( $addons[ self::VISA4_FC_ADDON ] ) &&
               !empty ( $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] &&
               in_array( $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ], $country_codes ));
    }
}

VISA4_FormCraft_Integration::init();