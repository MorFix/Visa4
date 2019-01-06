<?php

function has_any_sidebar(array $sidebars) {
	return array_reduce($sidebars, function($is_any_active, $sidebar) {
		return $is_any_active || is_active_sidebar( $sidebar );
	});
}

function visa4_search_shortcode( $atts, $content = null ) {
    ob_start(); ?>

    <div class="search-box-wrapper no-padding">
		<div class="search-box container">
			<div class="search-tab-content">
				<!-- TODO: Set right action - to the country -->
				<form role="search" method="post" action="<?php echo esc_url( get_post_type_archive_link( 'car' ) ); ?>">
					<div class="row">
						<div class="form-group col-md-4">
                            <div class="selector">
                                <select class="full-width" name="car_types">
                                    <option value=""><?php _e( 'Select source country' ); ?></option>
									<?php
									$all_car_types = get_terms( 'car_type', array('hide_empty' => 0) );
									foreach ( $all_car_types as $each_car_type ) {
										echo '<option value="' . esc_attr( $each_car_type->term_id ) . '">' . esc_html( $each_car_type->name ) . '</option>';
									}
									?>
                                </select>
                            </div>
						</div>
                        <div class="form-group col-md-4">
                            <div class="selector">
                                <select class="full-width" name="car_types">
                                    <option value=""><?php _e( 'Select destination country' ); ?></option>
									<?php
									$all_car_types = get_terms( 'car_type', array('hide_empty' => 0) );
									foreach ( $all_car_types as $each_car_type ) {
										echo '<option value="' . esc_attr( $each_car_type->term_id ) . '">' . esc_html( $each_car_type->name ) . '</option>';
									}
									?>
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
	</div>

    <?php $output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode( 'visa4_search', 'visa4_search_shortcode' );

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