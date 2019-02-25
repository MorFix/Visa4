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
        add_action( 'wp_ajax_formcraft3_form_save', array( __CLASS__, 'visa4_formcraft_form_save' ), 1 );
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
        /** @noinspection PhpUnusedLocalVariableInspection */
        $visa4_fc_addon = self::VISA4_FC_ADDON;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $visa4_fc_addon_country_key = self::VISA4_FC_ADDON_COUNTRY_KEY;

		include(dirname(__FILE__) . '/views/html-visa4-fc-addon.php');
	}

    /**
     * Hook into the admin save action of formcraft so we can update a product when a form is changed
     */
    public static function visa4_formcraft_form_save() {
        global $fc_meta;
        if ( !current_user_can( $fc_meta['user_can'] ) || !ctype_digit( $_POST['id'] ) ) {
            return;
        }

        $addons = json_decode( stripslashes( $_POST['addons'] ), true );
        if ( !$addons[ self::VISA4_FC_ADDON ] ) {
            return;
        }

        $form = self::get_form_by_country( $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] );
        if ( $form !== null && absint( $form['id'] ) !== absint( $_POST['id'] ) ) {
            echo json_encode( array( 'failed' => __( 'Form is already connected to another country' ) ) );
            die();
        }

        $country_code = $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ];

        /**
         * @var $post WP_Post
         */

        // Are we detaching a form from a product ?
        if ( !$country_code ) {
            $post = Visa4()->countries_manager->get_product_by_form( absint( $_POST['id'] ) );

            // The form was not attached to any product
            if ( !$post ) {
                return;
            }

            delete_post_meta( $post->ID, VISA4::FORM_META_KEY );

            return;
        }

        $post = Visa4()->countries_manager->get_product_by_country( $country_code );

        if ( !$post ) {
            echo json_encode( array( 'failed' => __( 'Invalid country selected' ) ) );
            die();
        }

        update_post_meta( $post->ID, Visa4::FORM_META_KEY, esc_sql( $_POST['id'] ) );
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
     * Get a FormCraft form that is connected to a requested country code
     *
     * @param $country_code - The desired country code
     * @return array - The form or null
     */
    public static function get_form_by_country( $country_code ) {
        global $wpdb, $fc_forms_table;

        $forms = $wpdb->get_results( "SELECT id, name, addons FROM $fc_forms_table", ARRAY_A );

        foreach ($forms as $form) {
            $addons = json_decode( stripcslashes( $form[ 'addons' ] ) , 1 );

            if ( !empty ( $addons[ self::VISA4_FC_ADDON ] ) &&
                 $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] === $country_code ) {
                return $form;
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

    /**
     * Get all forms
     *
     * @return array
     */
    public static function get_forms()
    {
        global $wpdb, $fc_forms_table;

        $forms = $wpdb->get_results( "SELECT id, name FROM " . $fc_forms_table, ARRAY_A );

        $all_forms = array();
        foreach ( $forms as $form ) {
            $all_forms[$form['id']] = $form;
        }

        return $all_forms;
    }

    /**
     * Get a form by ID
     *
     * @param int $id - Form ID
     *
     * @return array|null - The form
     */
    public static function get_form( $id )
    {
        global $wpdb, $fc_forms_table;

        if ( !is_numeric( $id ) ) {
            return null;
        }

        $forms = $wpdb->get_results( "SELECT id, name, addons FROM " . $fc_forms_table . " WHERE id = " . absint( $id ) . " LIMIT 1", ARRAY_A );
        if ( !$forms[0] ) {
            return null;
        }

        return $forms[0];
    }

    /**
     * Get connected Visa4 Country code by form ID
     *
     * @param int $form_id - The form
     *
     * @return string|null;
     */
    public static function get_country_by_form( $form_id ) {
        $form = self::get_form( absint( $form_id ) );
        if ( !$form ) {
            return null;
        }

        $addons = json_decode( stripcslashes( $form[ 'addons' ] ) , 1 );

        if ( empty( $addons[ self::VISA4_FC_ADDON ] ) ||
             empty( $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ] )) {
            return null;
        }

        return $addons[ self::VISA4_FC_ADDON ][ self::VISA4_FC_ADDON_COUNTRY_KEY ];
    }

    /**
     * Change country attached to a form
     *
     * @param int $form_id - The form
     * @param $country_code - The new country
     *
     * @return string|null - Might be an error string;
     */
    public static function update_country_in_form( $form_id, $country_code ) {
        global $wpdb, $fc_forms_table;

        $form = self::get_form( absint( $form_id ) );
        if ( empty( $form ) ) {
            return __( 'Cannot find form' );
        }

        if ( !empty( $country_code ) && empty( Visa4()->countries->get_countries()[ $country_code ] ) ) {
            return __( 'Cannot find country' );
        }

        $addons = json_decode( stripcslashes( $form[ 'addons' ] ) , 1 );
        $addons[self::VISA4_FC_ADDON][self::VISA4_FC_ADDON_COUNTRY_KEY] = $country_code;
        $addons = esc_sql( stripslashes( json_encode( $addons ) ) );

        $result = $wpdb->update(
            $fc_forms_table,
            array( 'addons' => $addons ),
            array( 'id' => $form_id )
        );

        if ( !$result ) {
            return __( 'An error has occurred while updating the form' );
        }

        return null;
    }
}

VISA4_FormCraft_Integration::init();