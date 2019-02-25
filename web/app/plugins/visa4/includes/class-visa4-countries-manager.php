<?php
/**
 * Visa4 Countries Manager Class
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VISA4_Countries_Manager
 */
class VISA4_Countries_Manager {

    /**
     * Get products that are completely valid - connected to a form and a country
     *
     * @return array - The products
     */
    public function get_valid_products() {
        $products = array();
        $args = array (
            'post_type' => 'product',
            'meta_key' => Visa4::COUNTRY_META_KEY,
            'meta_value'   => array_keys( $this->get_valid_countries() ),
            'meta_compare' => 'IN'
        );

        $query = new WP_Query( $args );
        while ( $query->have_posts() ): $query->the_post();

            $product = array();
            $product['post'] = $query->post;
            $product['country_code'] = get_post_meta( $product['post']->ID, VISA4::COUNTRY_META_KEY, true );

            $products[] = $product;

        endwhile;
        wp_reset_query();

        return $products;
    }

    /**
     * Get countries that are connected to a product (including all data)
     *
     * @return array - the valid countries
     */
    public function get_countries_connected_to_product_full_data() {
        $countries = array();
        $all_countries = Visa4()->countries->get_countries();

        $args = array (
            'post_type' => 'product',
            'meta_key' => 'visa4_country',
            'meta_value'   => array_keys( $all_countries ),
            'meta_compare' => 'IN'
        );

        $query = new WP_Query( $args );

        while ( $query->have_posts() ): $query->the_post();

            $country_code = get_post_meta( $query->post->ID, VISA4::COUNTRY_META_KEY , true );
            $source_countries = get_post_meta( $query->post->ID, VISA4::SOURCE_COUNTRIES_META_KEY, true );
            $form_id = get_post_meta( $query->post->ID, VISA4::FORM_META_KEY, true );

            $countries[ $country_code ] = array(
                'country_code' => $country_code,
                'name' => $all_countries[ $country_code ],
                'product_name' => $query->post->post_title,
                'source_countries' => !sizeof( $source_countries ) ? null : $source_countries,
                'edit_link' => html_entity_decode( get_edit_post_link( $query->post->ID ) ),
                'view_link' => html_entity_decode( get_permalink( $query->post->ID ) ),
                'form_id' => $form_id
            );

        endwhile;
        wp_reset_query();

        return $countries;
    }

    /**
     * Get product by country code
     *
     * @param $country_code
     * @return WP_Post|null
     */
    public function get_product_by_country( $country_code ) {
        $args = array (
            'meta_key' => VISA4::COUNTRY_META_KEY,
            'meta_value'   => $country_code,
        );

        return $this->get_product( $args );
    }

    /**
     * Get countries that are completely valid - connected to a form and a product
     *
     * @return array - the valid countries
     */
    public function get_valid_countries() {
        $with_product = array_keys( $this->get_countries_connected_to_product() );
        $with_form = array_keys( $this->get_countries_connected_to_form() );

        $all_countries = Visa4()->countries->get_countries();
        $countries = array();
        foreach ( $all_countries as $country_code => $country_name ) {
            if ( in_array( $country_code, $with_form ) && in_array( $country_code, $with_product )) {
                $countries[ $country_code ] = $country_name;
            }
        }

        return $countries;
    }

    /**
     * Get countries that has a connected WooCommerce product
     *
     * @return array - The Visa4 countries
     */
    public function get_countries_connected_to_product() {
        return VISA4_WooCommerce_Integration::get_countries_connected_to_product();
    }

    /**
     * Get countries that are ready to connect to a form (already has a product connected)
     *
     * @param string $current_form_id - (Optional) The form which we are checking for (to exclude it)
     * @return array - The Visa4 countries
     */
    public function get_countries_no_form( $current_form_id = '' ) {
        $valid_countries = self::get_countries_connected_to_product();
        $no_form_countries = array_keys( VISA4_FormCraft_Integration::get_countries_no_form( $current_form_id ) );

        $countries = array();
        foreach ( $no_form_countries as $no_form_country ) {
            if ( $valid_countries[ $no_form_country ] ) {
                $countries[ $no_form_country ] = $valid_countries[ $no_form_country ];
            }
        }

        return $countries;
    }

    /**
     * Get countries that are connected to a form
     *
     * @return array - The countries
     */
    public function get_countries_connected_to_form() {
        return VISA4_FormCraft_Integration::get_countries_connected_to_form();
    }

    /**
     * Get a FormCraft form that is connected to a requested country code
     *
     * @param $country_code - The desired country code
     *
     * @return array - The form or null
     */
    public function get_form_by_country($country_code ) {
        return VISA4_FormCraft_Integration::get_form_by_country( $country_code );
    }

    /**
     * Using WooCommerce to get the user's current country
     *
     * @return string - The Visa4 country code
     */
    public function get_current_country_code() {
        $countries = Visa4()->countries->get_countries();
        $country_code = VISA4_WooCommerce_Integration::get_current_country_code();

        if ( !empty ( $countries[$country_code] ) ) {
            return $country_code;
        }

        return '';
    }

    /**
     * Get all forms
     *
     * @return array
     */
    public function get_forms() {
        return VISA4_FormCraft_Integration::get_forms();
    }

    /**
     * Get a form by ID
     *
     * @param int $id - Form ID
     *
     * @return array|null - The form
     */
    public function get_form( $id ) {
        return VISA4_FormCraft_Integration::get_form( $id );
    }

    /**
     * Get connected Visa4 Country code by form ID
     *
     * @param int $form_id - The form
     *
     * @return string|null;
     */
    public static function get_country_by_form( $form_id ) {
        return VISA4_FormCraft_Integration::get_country_by_form( $form_id );
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
        return VISA4_FormCraft_Integration::update_country_in_form( $form_id, $country_code );
    }

    /**
     * Get a single product by form ID
     *
     * @param $form_id - The requested form ID
     * @return WP_Post
     */
    public function get_product_by_form( $form_id )
    {
        $args = array (
            'meta_key' => VISA4::FORM_META_KEY,
            'meta_value'   => absint( $form_id ),
        );

        return $this->get_product( $args );
    }

    /**
     * Get single product by arguments
     *
     * @param $args - arguments
     * @return WP_Post|null
     */
    private function get_product( $args ) {
        $defaults = array (
            'post_type' => 'product',
            'posts_per_page' => '1'
        );

        $result = get_posts( array_merge( $defaults, $args ) );

        if ( is_wp_error( $result ) || !$result[0] ) {
            return null;
        }

        return $result[0];
    }

    /**
     * Detaching a form from it's country
     *
     * @param $country_code - The requested country code
     * @return string|null - Might be an error string
     */
    public function detach_form( $country_code ) {
        $form = $this->get_form_by_country( $country_code );
        if ( empty( $form ) ) {
            return null;
        }

        return $this->update_country_in_form( $form['id'], null );
    }

    /**
     * Attaching a country to a form
     *
     * @param int $form_id - The form ID
     * @param $new_country_code - The country to attach
     *
     * @return string|null - Might be an error string
     */
    public function update_form_country( $form_id , $new_country_code )
    {
        if ( !is_numeric( $form_id ) ) {
            return __( 'Invalid form ID' );
        }

        // Now detaching the previous form of the new country it's country
        $error = $this->detach_form( $new_country_code );
        if ( !empty( $error ) ) {
            return $error;
        }

        // Deleting the form meta that is attached to the previous product
        $cc = $this->get_country_by_form( $form_id );
        if ( !empty( $cc ) ) {
            $previous_country = $this->get_product_by_country( $cc );
            delete_post_meta( $previous_country->ID, Visa4::FORM_META_KEY );
        }

        // Connecting the product meta to the new form OR removing meta if there is no new form
        $new_country = $this->get_product_by_country( $new_country_code );
        if ( empty( $form_id ) ) {
            delete_post_meta( $new_country->ID, VISA4::FORM_META_KEY );

            return null;
        }

        update_post_meta( $new_country->ID, VISA4::FORM_META_KEY, $form_id );

        // Updating the country in the form
        return $this->update_country_in_form( $form_id, $new_country_code );
    }

    /**
     * Creating a new product
     *
     * @param $country_code - The requested country code
     * @return int|WP_Error
     */
    public function create_product( $country_code )
    {
        // TODO: Check country exists
        // TODO: Check no another product exists for this code
        // TODO: Create product
        // TODO: Link to country code
        // TODO: Set virtual
        // TODO: Create attributes
        // TODO: Generate variations

        $args = array(
            'title' => ''
        );

        //$id = wp_insert_post( $args );
    }
}