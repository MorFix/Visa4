<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.1
 */

defined('ABSPATH') || exit;

global $product;
?>
<div class="search-box-wrapper no-padding style1">
    <div class="search-box container">
        <div class="search-tab-content">
            <div class="row">
                <?php if (empty($available_variations) && false !== $available_variations): ?>
                    <h4 class="title"
                        style="text-align: center;"><?php esc_html_e('This product is currently unavailable.'); ?></h4>
                <?php else:
                    $cols = (int)(8 / count($attributes));
                    foreach ($attributes as $attribute_name => $options) :
                        ?>
                        <div class="form-group col-md-<?= $cols; ?>">
                            <h4 class="title"><?php echo wc_attribute_label($attribute_name); ?></h4>
                            <label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
                                <?php echo wc_attribute_label($attribute_name); ?>
                            </label>
                            <div class="selector">
                                <?php
                                wc_dropdown_variation_attribute_options(array(
                                    'options' => $options,
                                    'attribute' => $attribute_name,
                                    'product' => $product,
                                    'class' => 'full-width'
                                ));
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-group col-md-4 fixheight">
                        <label class="hidden-xs">&nbsp;</label>
                        <button type="submit" class="full-width red uppercase animated" data-animation-type="bounce"
                                data-animation-duration="1"><?php _e('APPLY NOW') ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>