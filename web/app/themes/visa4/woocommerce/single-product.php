<?php

// TODO: Add custom CSS field to theme and use in header
get_header();
if ( have_posts() ) :
    $base_page = get_post( get_option( 'visa4_product_base_page' ) );
	while ( have_posts() ) : the_post(); ?>
		<div class="slideshow-bg">
			<?php $bg_imgs = get_post_meta( get_the_ID(), 'trav_gallery_imgs' );
			// TODO: Make the bg content option to disappear
			$bg_content = get_post_meta( $base_page->ID, 'trav_page_bg_content', true );
			if ( ! empty( $bg_imgs ) ) : ?>
				<div class="flexslider">
					<ul class="slides">
						<?php foreach ( $bg_imgs as $bg_img ) {
							$image_attributes = wp_get_attachment_image_src( $bg_img, 'full' );
							if ( $image_attributes ) { ?>
								<li><div class="slidebg" style="background-image: url(<?php echo $image_attributes[0] ?>);"></div></li>
							<?php }
						} ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $bg_content ) ) : ?>
				<div class="container">
					<div class="center-block-wrapper full-width">
						<div class="center-block">
							<?= do_shortcode( $bg_content ); ?>
                            <div id="visa4-form">
                                <?php
                                $form_id = Visa4()->countries_manager->get_form_id( get_post_meta( get_the_ID(), 'visa4_country' , true ) );

                                if ( $form_id ) {
                                    add_formcraft_form( '[fc id="' . $form_id . '" align="center"/]' );
                                }
                                ?>
                            </div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<section id="content">
			<div id="main" class="entry-content">
				<?php echo do_shortcode( $base_page->post_content ); ?>
			</div>
		</section>
	<?php endwhile;
endif;
get_footer();