<?php
/**
 * Countries mapping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2>
	<?php esc_html_e( 'Visa4 mappings'  ); ?>
</h2>
<p>
	<?php _e( 'Use this screen to map between destination countries and the countries that are required to use visa for the given destination' ); ?>
</p>

<table class="widefat">
	<thead>
	<tr>
		<?php foreach ( $visa4_countries_columns as $class => $heading ) : ?>
			<th class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $heading ); ?></th>
		<?php endforeach; ?>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="<?php echo absint( count( $visa4_countries_columns ) ); ?>">
			<button type="submit" name="save" class="button button-primary visa4-country-save" value="<?php esc_attr_e( 'Save countries' ); ?>" disabled>
				<?php esc_html_e( 'Save countries' ); ?>
			</button>
			<a class="button button-secondary visa4-country-add" href="#"><?php esc_html_e( 'Add Country' ); ?></a>
		</td>
	</tr>
	</tfoot>
	<tbody class="visa4-countries-rows"></tbody>
</table>

<script type="text/html" id="tmpl-visa4-country-row-blank">
	<tr>
		<td colspan="<?php echo absint( count( $shipping_class_columns ) ); ?>">
			<p><?php esc_html_e( 'No countries have been mapped.' ); ?></p>
		</td>
	</tr>
</script>

<script type="text/html" id="tmpl-visa4-country-row">
	<tr data-id="{{ data.term_id }}">
		<?php
		foreach ( $shipping_class_columns as $class => $heading ) {
			echo '<td class="' . esc_attr( $class ) . '">';
			switch ( $class ) {
				case 'wc-shipping-class-name':
					?>
					<div class="view">
						{{ data.name }}
						<div class="row-actions">
							<a class="visa4-country-edit" href="#"><?php esc_html_e( 'Edit' ); ?></a> | <a href="#" class="visa4-country-delete"><?php esc_html_e( 'Remove' ); ?></a>
						</div>
					</div>
					<div class="edit">
						<input type="text" name="name[{{ data.term_id }}]" data-attribute="name" value="{{ data.name }}" placeholder="<?php esc_attr_e( 'Shipping class name' ); ?>" />
						<div class="row-actions">
							<a class="visa4-country-cancel-edit" href="#"><?php esc_html_e( 'Cancel changes' ); ?></a>
						</div>
					</div>
					<?php
					break;
				case 'wc-shipping-class-slug':
					?>
					<div class="view">{{ data.slug }}</div>
					<div class="edit"><input type="text" name="slug[{{ data.term_id }}]" data-attribute="slug" value="{{ data.slug }}" placeholder="<?php esc_attr_e( 'Slug' ); ?>" /></div>
					<?php
					break;
				case 'wc-shipping-class-description':
					?>
					<div class="view">{{ data.description }}</div>
					<div class="edit"><input type="text" name="description[{{ data.term_id }}]" data-attribute="description" value="{{ data.description }}" placeholder="<?php esc_attr_e( 'Description for your reference' ); ?>" /></div>
					<?php
					break;
				case 'wc-shipping-class-count':
					?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&product_shipping_class=' ) ); ?>{{data.slug}}">{{ data.count }}</a>
					<?php
					break;
				default:
					break;
			}
			echo '</td>';
		}
		?>
	</tr>
</script>
