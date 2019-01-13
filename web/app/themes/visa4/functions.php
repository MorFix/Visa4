<?php

function has_any_sidebar(array $sidebars) {
	return array_reduce($sidebars, function($is_any_active, $sidebar) {
		return $is_any_active || is_active_sidebar( $sidebar );
	});
}

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
