<?php
/**
 * Shortcodes
 */

defined('ABSPATH') || exit;

/**
 * Visa4 Shortcodes class.
 */
class VISA4_Shortcodes
{

    /**
     * Init shortcodes.
     */
    public static function init()
    {
        $shortcodes = array(
            'visa4_search' => 'search',
        );

        foreach ($shortcodes as $shortcode => $function) {
            add_shortcode($shortcode, array(__CLASS__, $function));
        }
    }

    /**
     * Search box shortcode.
     *
     * @return string
     */
    public static function search()
    {
        /**
         * @var WC_Product $product
         */
        global $product;

        ob_start();

        if (is_product()) {
            wp_enqueue_script('visa4-add-to-cart-handler', Visa4()->plugin_url() . '/assets/js/add-to-cart-handler.js', array('jquery', 'jquery-ui-dialog', 'fc-form-js'), Visa4()->version, true);
            wp_enqueue_style('wp-jquery-ui-dialog');
        }

        $action = !is_product() ? get_permalink() : add_query_arg('add-to-cart', get_the_ID(), wc_get_cart_url());
        ?>
        <form role="search" method="post" id="visa4-add-to-cart" action="<?php echo esc_url($action); ?>">
            <input type="hidden" name="submission_id" id="submission_id" value=""/>
            <div class="search-box-wrapper no-padding style1">
                <div class="search-box container">
                    <div class="search-tab-content">
                        <div class="row">
                            <div class="form-group col-md-<?= self::is_variable_product() ? '6' : '4' ?>">
                                <h4 class="title"><?php _e('Destination'); ?></h4>
                                <label for="visa4_destination"><?php _e('Where are you traveling to?'); ?></label>
                                <?php if (is_product()): ?>
                                    <h4 class="title">
                                        <?php
                                        $country_code = esc_attr(get_post_meta(get_the_ID(), VISA4::COUNTRY_META_KEY, true));
                                        echo Visa4()->countries->get_countries()[$country_code];
                                        ?>
                                    </h4>
                                <?php else: ?>
                                    <div class="selector">
                                        <select class="full-width" name="visa4_destination">
                                            <option value=""><?php _e('Select country'); ?></option>
                                            <?php foreach (Visa4()->countries_manager->get_valid_products() as $product): ?>
                                                <option value="<?php echo $product['post']->ID; ?>">
                                                    <?php echo Visa4()->countries->get_countries()[$product['country_code']]; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group col-md-<?= self::is_variable_product() ? '6' : '4' ?>">
                                <h4 class="title"><?php _e('Home'); ?></h4>
                                <label for="visa4_source"><?php _e('Where are you from?') ?></label>
                                <div class="selector">
                                    <select class="full-width" name="visa4_source" id="visa4_source">
                                        <option value=""><?php _e('Select country'); ?></option>
                                        <?php
                                        $visa4_countries = Visa4()->countries->get_countries();

                                        // On a single product page, Get the source from $_GET or fallback to Geo-locate
                                        $source_country_code = is_product() && isset($_GET['visa4_source']) && $visa4_countries[esc_attr($_GET['visa4_source'])]
                                            ? esc_attr($_GET['visa4_source'])
                                            : Visa4()->countries_manager->get_current_country_code();

                                        foreach ($visa4_countries as $country_code => $country_name):
                                            $selected = $country_code === $source_country_code ? ' selected="selected"' : '';
                                            ?>
                                            <option value="<?php echo $country_code; ?>"<?php echo $selected; ?>>
                                                <?php echo $country_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <?php if (!self::is_variable_product()): ?>
                                <div class="form-group col-md-4 fixheight">
                                    <label class="hidden-xs">&nbsp;</label>
                                    <button type="submit" class="full-width red uppercase animated"
                                            data-animation-type="bounce"
                                            data-animation-duration="1"><?php _e('APPLY NOW') ?></button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (self::is_variable_product()) {
                do_action('woocommerce_variable_add_to_cart');
            }
            ?>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Checking whether the current page is a variable product page
     *
     * @return bool
     */
    private static function is_variable_product()
    {
        /**
         * @global WC_Product $product
         */
        global $product;

        return $product && $product->is_type('variable');
    }
}

?>