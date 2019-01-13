<?php
// TODO: Add custom CSS field to theme and use in header
get_header();
if ( have_posts() ) :
	while ( have_posts() ) : the_post(); ?>
		<div class="slideshow-bg">
			<?php $bg_imgs = get_post_meta( get_the_ID(), 'trav_gallery_imgs' );
			// TODO: Make the bg content option to disappear
			$bg_content = get_option( 'visa4_product_slider_template' );
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
							<?php echo do_shortcode( $bg_content ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<section id="content">
            <style type="text/css">
                <?php echo esc_attr( get_option( 'visa4_product_css' ) ) ?>
            </style>
			<div id="main" class="entry-content">
				<?php echo do_shortcode( get_option( 'visa4_product_template' ) ); ?>
			</div>
		</section>
	<?php endwhile;
endif;
get_footer();