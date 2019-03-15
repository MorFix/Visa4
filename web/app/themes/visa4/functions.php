<?php

/**
 * Whether any of the sidebars is active
 *
 * @param array $sidebars
 * @return bool
 */
function has_any_sidebar(array $sidebars) {
	return array_reduce($sidebars, function($is_any_active, $sidebar) {
		return $is_any_active || is_active_sidebar( $sidebar );
	});
}

/**
 * Fixing a css problem with a full-width testimonials container
 */
function hide_overflowing_main() {
    $custom_css = 'div#main {overflow:hidden;}';

    wp_add_inline_style( 'trav_style_custom', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'hide_overflowing_main', 20 );

/**
 * Override the meta boxes structure to add slider also in product page
 *
 * @param $metaboxes - Original meta boxes
 * @return array - new meta boxes
 */
function visa4_register_posts_metadata( $metaboxes ) {
    foreach ( $metaboxes as $key => $box ) {
        if ( $box['id'] === 'trav_page' ) {
            $metaboxes[$key]['pages'][] = 'product';
            break;
        }
    }

    return $metaboxes;
}
add_filter( 'trav_register_post_meta_boxes', 'visa4_register_posts_metadata' );

/**
 * Add the CSS from the product base page
 */
function add_custom_product_css() {
    if ( !is_product() ) {
        return;
    }

    $css = get_post_meta( get_option( 'visa4_product_base_page' ) , 'trav_page_custom_css', true );

    wp_add_inline_style( 'trav_style_custom', $css );
}
add_action( 'wp_enqueue_scripts', 'add_custom_product_css', 20 );

/**
 * WooCommerce single-product JS uses the same CSS class names as Travelo and that ruins things
 */
function remove_colliding_wc_script() {
    if ( !is_product() ) {
        return;
    }

    wp_dequeue_script( 'wc-single-product' );
}
add_action( 'wp_enqueue_scripts', 'remove_colliding_wc_script', 20 );

/**
 * Add Visa4 them general script
 */
function add_general_script() {
    wp_enqueue_script( 'visa4_general_script', get_stylesheet_directory_uri() . '/js/general.js', array( 'jquery' ), '', true );
}
add_action( 'wp_enqueue_scripts', 'add_general_script', 20 );