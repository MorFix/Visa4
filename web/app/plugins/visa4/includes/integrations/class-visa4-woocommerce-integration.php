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

    /**
     * Init hooks
     */
    public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array ( __CLASS__, 'add_visa4_tab' ) );
		add_filter( 'woocommerce_product_data_panels', array ( __CLASS__, 'output_visa4_tab' ) );
		add_action( 'woocommerce_process_product_meta', array ( __CLASS__, 'save_country_meta' ) );
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

		return new WP_Query( $args );
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
}

VISA4_WooCommerce_Integration::init();