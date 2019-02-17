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
			<button type="submit" name="save" class="button button-primary visa4-countries-save" value="<?php esc_attr_e( 'Save countries' ); ?>" disabled>
				<?php esc_html_e( 'Save countries' ); ?>
			</button>
			<button class="button button-secondary visa4-country-add" href="#"><?php esc_html_e( 'Add Country' ); ?></button>
		</td>
	</tr>
	</tfoot>
	<tbody class="visa4-countries-rows"></tbody>
</table>

<script type="text/html" id="tmpl-visa4-country-row-blank">
	<tr>
		<td colspan="<?php echo absint( count( $visa4_countries_columns ) ); ?>">
			<p><?php esc_html_e( 'No countries have been mapped.' ); ?></p>
		</td>
	</tr>
</script>

<script type="text/html" id="tmpl-visa4-source-country">
    {{ data }}
</script>

<script type="text/html" id="tmpl-visa4-country-row">
	<tr data-id="{{ data.country_code }}">
		<?php
		foreach ( $visa4_countries_columns as $class => $heading ) {
			echo '<td class="' . esc_attr( $class ) . '">';
			switch ( $class ) {
				case 'visa4-destination-country':
					?>
					<div class="view">
						{{ data.name }}
						<div class="row-actions">
							<a class="visa4-country-edit" href="#">
                                <?php esc_html_e( 'Edit' ); ?></a> | <a href="#" class="visa4-country-delete"><?php esc_html_e( 'Remove' ); ?></a>
						</div>
					</div>
					<div class="edit">
                        {{ data.name }}
						<div class="row-actions">
							<a class="visa4-country-cancel-edit" href="#"><?php esc_html_e( 'Cancel changes' ); ?></a>
						</div>
					</div>
					<?php
					break;
				case 'visa4-source-countries':
					?>
					<div class="view">
                        <div class="source_countries"></div>
                    </div>
					<div class="edit">
                        <select name="source_countries[{{ data.country_code }}]" data-attribute="source_countries" multiple>
                            <?php foreach (Visa4()->countries->get_countries() as $country_code => $name) : ?>
                            <option value="<?= $country_code; ?>" select="source"><?= $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
					<?php
					break;
				case 'visa4-country-edit-link':
                    ?>
                    <a href="{{ decodeURIComponent( data.edit_link ) }}" target="_blank">
                        Edit "{{ data.product_name }}"
                    </a>
					<?php
					break;
				case 'visa4-country-view-link':
					?>
					<a href="{{ decodeURIComponent( data.view_link ) }}" target="_blank">
                        View "{{ data.product_name }}"
                    </a>
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
