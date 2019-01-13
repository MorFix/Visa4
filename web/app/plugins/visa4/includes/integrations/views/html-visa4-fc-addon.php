<?php
/**
 * Admin View: Formcraft Plugin integration
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Visa4 -->
<label class='single-option'>
	<h3><?php _e('Link to a Visa4 country' ); ?></h3>
	<div class='option-description'><?php _e('Select a country to attach the form to' ); ?>
		<select ng-model='Addons.<?php echo $visa4_fc_addon . '.' . $visa4_fc_addon_country_key; ?>'>
			<option value=''>None</option>
			<?php foreach (Visa4()->countries_manager->get_countries_no_form( esc_attr( $_GET[ 'id' ] ) ) as $country_code => $country_name): ?>
				<option value="<?php echo $country_code; ?>"><?php echo $country_name; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</label>
<!-- End Visa4 -->

