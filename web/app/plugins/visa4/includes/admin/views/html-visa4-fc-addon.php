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
		<select ng-model='Addons.Visa4FC.country' style='width: 120px'>
			<option value=''>None</option>
			<?php foreach (Visa4()->countries->get_countries_with_visa_setting() as $country_code => $country_name): ?>
				<option value="<?php echo $country_code; ?>"><?php echo $country_name; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</label>
<!-- End Visa4 -->

