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
            'meta_key' => 'visa4_country',
            'meta_value'   => array_keys( $this->get_valid_countries() ),
            'meta_compare' => 'IN'
        );

        $query = new WP_Query( $args );
        while ( $query->have_posts() ): $query->the_post();

            $product = array();
            $product['post'] = $query->post;
            $product['country_code'] = get_post_meta( $product['post']->ID, 'visa4_country' , true );

            $products[] = $product;

        endwhile;
        wp_reset_query();

        return $products;
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
     * Get countries that are ready to connect to a form
     *
     * @param string $current_form_id - (Optional) The form which we are checking for
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
     * Get a FormCraft form ID that is connected to a requested country code
     *
     * @param $country_code - The desired country code
     * @return int - The form Id or null
     */
    public function get_form_id( $country_code ) {
        return VISA4_FormCraft_Integration::get_form_id( $country_code );
    }

    /**
     * Using WooCommerce to get the user's current country
     *
     * @return string - The Visa4 country code
     */
    public function get_current_country_code()
    {
        $countries = Visa4()->countries->get_countries();
        $country_code = VISA4_WooCommerce_Integration::get_current_country_code();

        if ( !empty ( $countries[$country_code] ) ) {
            return $country_code;
        }

        return '';
    }
}