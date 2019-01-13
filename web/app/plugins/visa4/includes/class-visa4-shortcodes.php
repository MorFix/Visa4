<?php
/**
 * Shortcodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Visa4 Shortcodes class.
 */
class VISA4_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'visa4_search'  => 'search',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( __CLASS__, $function ) );
		}
	}

	/**
	 * Search box shortcode.
	 *
	 * @return string
	 */
	public static function search() {
		ob_start();
	?>
        <div class="search-box-wrapper no-padding">
        <div class="search-box container">
            <div class="search-tab-content">
                <!-- TODO: Set right action - to the country -->
                <form role="search" method="post" action="<?php echo esc_url( get_post_type_archive_link( 'car' ) ); ?>">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <div class="selector">
                                <select class="full-width" name="visa4_destination">
                                    <option value=""><?php _e( 'Where are you traveling to?' ); ?></option>
                                    <?php foreach ( Visa4()->countries_manager->get_valid_products() as $product ): ?>
                                    <option value="<?php echo $product[ 'post' ]->ID; ?>">
                                        <?php echo Visa4()->countries->get_countries()[ $product[ 'country_code' ] ]; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="selector">
                                <select class="full-width" name="visa4_source">
                                    <option value=""><?php _e( 'Where are you from?' ); ?></option>
                                    <?php foreach ( Visa4()->countries->get_countries() as $country_code => $country_name ): ?>
                                        <option value="<?php echo $country_code; ?>">
                                            <?php echo $country_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <button type="submit" class="full-width red uppercase animated" data-animation-type="bounce" data-animation-duration="1"><?php _e( 'APPLY NOW' ) ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><?php
		return ob_get_clean();
	}
}
