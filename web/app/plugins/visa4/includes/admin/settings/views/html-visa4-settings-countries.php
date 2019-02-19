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
    <div>{{ data }}</div>
</script>

<script type="text/html" id="tmpl-visa4-country-row">
	<tr data-id="{{ data.country_key || data.country_code }}">
		<?php
        $countries = Visa4()->countries->get_countries();
        $forms = Visa4()->countries_manager->get_forms();

		foreach ( $visa4_countries_columns as $class => $heading ) {
			echo '<td class="' . esc_attr( $class ) . '">';
			switch ( $class ) {
				case 'visa4-destination-country':
					?>
					<div class="view">
						{{ data.name }}
						<div class="row-actions">
							<a class="visa4-country-edit" href="#">
                                <?php esc_html_e( 'Edit' ); ?>
                            </a> | <a href="#" class="visa4-country-delete"><?php esc_html_e( 'Remove' ); ?></a>
						</div>
					</div>
					<div class="edit">
                        {{ data.name }}
						<div class="row-actions">
							<a class="visa4-country-cancel-edit" href="#"><?php esc_html_e( 'Cancel changes' ); ?></a>
						</div>
					</div>
                    <div class="add">
                        <select name="destination[{{ data.country_code }}]" data-attribute="country_code">
                            <?php foreach ($countries as $country_code => $name) : ?>
                                <option value="<?= $country_code; ?>"><?= $name; ?></option>
                            <?php endforeach; ?>
                        </select>
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
					<div class="edit add">
                        <select name="source_countries[{{ data.country_code }}]" data-attribute="source_countries" multiple>
                            <?php foreach ($countries as $country_code => $name) : ?>
                            <option value="<?= $country_code; ?>"><?= $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
					<?php
					break;
				case 'visa4-country-edit-link':
                    ?>
                    <div class="view edit">
                        <a href="{{ decodeURIComponent( data.edit_link ) }}" target="_blank">
                            Edit "{{ data.product_name }}"
                        </a>
                    </div>
					<?php
					break;
				case 'visa4-country-view-link':
					?>
                    <div class="view edit">
                        <a href="{{ decodeURIComponent( data.view_link ) }}" target="_blank">
                            View "{{ data.product_name }}"
                        </a>
                    </div>
					<?php
					break;
                case 'visa4-country-select-form':
                    ?>
                    <div class="view">
                        {{ data.allForms[data.form_id] ? data.allForms[data.form_id].name : 'No connected form' }}
                    </div>
                    <div class="edit add">
                        <select name="form_id[{{ data.country_code }}]" data-attribute="form_id">
                            <option value="">None</option>
                            <?php foreach ( $forms as $id => $form ) : ?>
                                <option value="<?= $id; ?>"><?= $form['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php
                    break;
                case 'visa4-country-edit-form':
                    ?>
                    <div class="view">
                        <a class="conditional-form" href="{{ decodeURIComponent( data.formsLink ) + '&id=' + data.form_id }}" target="_blank">
                            Edit custom form
                        </a>
                        <a class="conditional-no-form" href="{{ decodeURIComponent( data.formsLink ) }}" target="_blank">
                            Create custom form
                        </a>
                    </div>
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
