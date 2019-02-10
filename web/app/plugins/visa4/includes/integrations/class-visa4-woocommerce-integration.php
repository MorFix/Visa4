<?php
/**
 * Visa4 WooCommerce integration
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VISA4_WooCommerce_Integration
 */
class VISA4_WooCommerce_Integration {

    const VISA4_COUNTRY_META_KEY = 'visa4_country';
    const FORM_SUBMISSION_REQUEST_KEY = 'submission_id';
    const FORM_SUBMISSION_ID_META_KEY = 'form_submission_id';
    const VISA4_SOURCE_COUNTRY_META_KEY = 'visa4_source';

    /**
     * Init hooks
     */
    public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array ( __CLASS__, 'add_visa4_tab' ) );
		add_filter( 'woocommerce_product_data_panels', array ( __CLASS__, 'output_visa4_tab' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'visa4_add_cart_item_data' ) );
		add_filter( 'visa4_should_read_custom_css', function( ) { return is_product(); } );
        add_filter( 'woocommerce_order_item_display_meta_key', array( __CLASS__, 'visa4_order_item_display_meta_key' ), 10, 2 );
        add_filter( 'woocommerce_order_item_display_meta_value', array( __CLASS__, 'visa4_order_item_display_meta_value' ), 10, 2 );
        add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( __CLASS__, 'visa4_order_item_hide_form' ) );
        add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'visa4_get_cart_item_data' ), 10, 2 );

		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'visa4_validate_cart_item' ), 10, 6 );
		add_action( 'woocommerce_process_product_meta', array ( __CLASS__, 'save_country_meta' ) );
        add_action( 'woocommerce_checkout_create_order_line_item', array ( __CLASS__, 'visa4_save_cart_item_data' ), 10, 3 );

        remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
        add_action( 'woocommerce_variable_add_to_cart', array( __CLASS__, 'visa4_variable_add_to_cart' ) );
	}

    /**
     * Add the Visa4 tab to a product options
     *
     * @param $tabs
     * @return array - new tabs array
     */
	public static function add_visa4_tab( $tabs ) {
		$tabs['visa4'] = array(
			'label'		=> __( 'Visa4'  ),
			'target'	=> 'visa4_options_tab',
			'class'		=> array( 'show_if_simple', 'show_if_variable'  ),
		);

		return $tabs;
	}

    /**
     * Output the metadata tab
     */
	public static function output_visa4_tab() {
		echo '<div id="visa4_options_tab" class="panel woocommerce_options_panel">';

		$args = array(
			'id' => 'visa4_country',
			'label' => __( 'Visa4 connected country' ),
			'desc_tip' => true,
			'description' => __( 'Select which Visa4 country is connected to this product' ),
			'options' => array_merge( array ( '' => 'Select a country' ), Visa4()->countries->get_countries() )
		);

		woocommerce_wp_select( $args );

		echo '</div>';
	}

    /**
     * Save the Visa4 country product metadata
     *
     * @param $post_id
     */
	public static function save_country_meta( $post_id ) {
		$product = wc_get_product( $post_id );
		$value = isset( $_POST['visa4_country'] ) ? $_POST['visa4_country'] : '';

		if ( ! empty( $value ) && self::get_products_connected_to_country( $value, $post_id )->post_count > 0 ) {
            WC_Admin_Meta_Boxes::add_error( sprintf( __( 'There is already a product connected to country code <b>%s</b>' ), $value ) );

            // A redirect may happen after we add the error, so we need to save it
            update_option( 'woocommerce_meta_box_errors', WC_Admin_Meta_Boxes::$meta_box_errors );

			return;
		}

		$product->update_meta_data( self::VISA4_COUNTRY_META_KEY, esc_attr( $value ) );
		$product->save();
	}

    /**
     * Get countries that has a connected WooCommerce product
     *
     * @return array - All countries that are connected to a product
     */
    public static function get_countries_connected_to_product() {
        $meta_countries = array();

        $args = array (
            'post_type' => 'product',
            'meta_key' => 'visa4_country',
            'meta_value'   => array_keys( Visa4()->countries->get_countries() ),
            'meta_compare' => 'IN'
        );

        $result = new WP_Query( $args );
        while ( $result->have_posts() ) {
            $result->the_post();
            $meta_countries[] = get_post_meta(  $result->post->ID, self::VISA4_COUNTRY_META_KEY, true);
        }
        wp_reset_query();

        $countries = array();
        foreach ( Visa4()->countries->get_countries() as $country_code => $country_name) {
            if ( in_array( $country_code, $meta_countries ) ) {
                $countries[ $country_code ] = $country_name;
            }
        }

        return $countries;
    }

    /**
     * Get (all) products that are connected to a Visa4 Country
     *
     * @param string $country_code - Visa4 Country code
     * @param integer $exclude - country to exclude from query
     * @return WP_Query - The products that are connected to a country
     */
	public static function get_products_connected_to_country( $country_code = '', $exclude = null ) {
		$value = '';
		$compare = '!=';

		if ( ! empty( $country_code ) ) {
			$value = $country_code;
			$compare = '=';
		}

		$args = array (
			'post_type' => 'product',
			'meta_key' => 'visa4_country',
			'meta_value'   => $value,
			'meta_compare' => $compare
		);

		if ( ! empty( $exclude ) && is_numeric( $exclude ) ) {
			$args['post__not_in'] = array( absint($exclude) );
		}

		$query = new WP_Query( $args );
        wp_reset_query();

        return $query;
	}

    /**
     * Get the HTML for "Country already exists" error
     *
     * @param $country_code
     */
	public static function add_country_exists_error( $country_code ) {
		$text = sprintf( __( 'There is already a product connected to country code <b>%s</b>' ), $country_code );
		?><div class="notice notice-error"><p><?php echo $text; ?></p></div><?php
	}

    /**
     * Add visa4-related data to cart item.
     *
     * @param array $cart_item_data
     *
     * @return array
     */
    public static function visa4_add_cart_item_data( $cart_item_data ) {
        if ( is_numeric( $_REQUEST[ self::FORM_SUBMISSION_REQUEST_KEY ] ) ) {
            $cart_item_data[ self::FORM_SUBMISSION_REQUEST_KEY ] = (int) $_REQUEST[ self::FORM_SUBMISSION_REQUEST_KEY ];
        }

        // Does the country code exist ?
        if ( Visa4()->countries->get_countries()[ $_REQUEST[ self::VISA4_SOURCE_COUNTRY_META_KEY ] ] ) {
            $cart_item_data[ self::VISA4_SOURCE_COUNTRY_META_KEY ] = $_REQUEST[ self::VISA4_SOURCE_COUNTRY_META_KEY ];
        }

        return $cart_item_data;
    }

    /**
     * Using WooCommerce to get the user's current country
     *
     * @return string - The Visa4 country code
     */
    public static function get_current_country_code()
    {
        return WC_Geolocation::geolocate_ip()[ 'country' ];
    }

    /**
     * Overriding the add to cart button for a variable product
     */
    public static function visa4_variable_add_to_cart() {
        /**
         * @global WC_Product_Variable $product
         */
        global $product;

        if ( ! $product->is_type( 'variable' ) ) {
            return;
        }

        // Get Available variations?
        $get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

        // Load the template.
        wc_get_template( 'single-product/add-to-cart/variable.php', array(
            'available_variations' => $get_variations ? $product->get_available_variations() : false,
            'attributes'           => $product->get_variation_attributes(),
            'selected_attributes'  => $product->get_default_attributes(),
        ) );
    }

    /**
     * @param $cart_item_key
     * @param $product_id
     * @param $quantity
     * @param $variation_id
     * @param $variation
     * @param $cart_item_data
     */
    public static function visa4_validate_cart_item( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        // TODO: Check that source country is in Visa4 countries

        // TODO: Check that the submission's form is attached to the country
    }

    /**
     * Hook into the order item meta keys to correctly display visa4 properties
     *
     * @param string $display_key
     * @param WC_Meta_Data $meta
     *
     * @return string - The new display key
     */
    public static function visa4_order_item_display_meta_key( $display_key, $meta ) {
        if ( $meta->key === self::FORM_SUBMISSION_ID_META_KEY ) {
            return __( 'Form Submission ID' );
        }

        if ( $meta->key === self::VISA4_SOURCE_COUNTRY_META_KEY ) {
            return __( 'Source Country' );
        }

        return $display_key;
    }

    /**
     * Hook into the order item meta values to correctly display visa4 properties
     *
     * @param string $display_value
     * @param WC_Meta_Data $meta
     *
     * @return string - The new display value
     */
    public static function visa4_order_item_display_meta_value( $display_value, $meta ) {
        if ( $meta->key === self::VISA4_SOURCE_COUNTRY_META_KEY ) {
            return Visa4()->countries->get_countries()[ $meta->value ];
        }

        return $display_value;
    }

    /**
     * Hides the form submission id from client side order
     *
     * @param $formatted_data - The order item formatted meta data
     *
     * @return array - The new formatted meta data
     */
    public static function visa4_order_item_hide_form( $formatted_data ) {
        if ( is_admin() ) {
            return $formatted_data;
        }

        $data = array();
        foreach ( $formatted_data as $id => $meta ) {
            if ( $meta->key !== self::FORM_SUBMISSION_ID_META_KEY ) {
                $data[ $id ] = $meta;
            }
        }

        return $data;
    }

    /**
     * Save visa4-related data in a cart item.
     *
     * @param WC_Order_Item_Product $item
     * @param string                $cart_item_key
     * @param array                 $values
     */
    public static function visa4_save_cart_item_data( $item, $cart_item_key, $values ) {
        if ( !empty( $values[ self::FORM_SUBMISSION_REQUEST_KEY ] ) ) {
            $item->add_meta_data( self::FORM_SUBMISSION_ID_META_KEY , $values[ self::FORM_SUBMISSION_REQUEST_KEY ] );
        }

        if ( !empty( $values[ self::VISA4_SOURCE_COUNTRY_META_KEY ] ) ) {
            $item->add_meta_data( self::VISA4_SOURCE_COUNTRY_META_KEY, $values[ self::VISA4_SOURCE_COUNTRY_META_KEY ] );
        }
    }

    /**
     * Manipulate  the cart item data display to show the source country
     *
     * @param $item_data
     * @param $cart_item
     *
     * @return array - The new cart item data
     */
    public static function visa4_get_cart_item_data( $item_data, $cart_item ) {
        if ( !empty( $cart_item[ self::VISA4_SOURCE_COUNTRY_META_KEY ] ) ) {
            $item_data[] = array(
                'key' => __( 'Source Country' ),
                'value' => Visa4()->countries->get_countries()[ $cart_item[ self::VISA4_SOURCE_COUNTRY_META_KEY ] ]
            );
        }

        return $item_data;
    }
}

VISA4_WooCommerce_Integration::init();