<?php
/**
 * Admin View: Settings
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as  $id => $label ): ?>
			<a href="#" class="nav-tab" id="nav-tab-<?php echo esc_attr( $id ); ?>">
			    <?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
    </nav>

    <div class="tabs-wrapper">
	    <?php foreach ($tabs as $id => $label ) : ?>
            <div class="tab-wrapper" id="tab-<?php echo esc_attr($id); ?>">
                <?php do_action('visa4_settings_' . $id); ?>
            </div>
	    <?php endforeach; ?>
    </div>

	<button name="save" class="button-primary" value="<?php esc_attr_e( 'Save changes' ); ?>">
        <?php esc_html_e( 'Save changes' ); ?>
    </button>
</div>