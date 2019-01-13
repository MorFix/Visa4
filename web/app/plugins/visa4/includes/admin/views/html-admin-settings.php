<?php
/**
 * Admin View: Settings
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <div class="notice inline notice-success is-dismissible" style="display: none;">
        <p><?php _e( 'Settings saved successfully !' ); ?></p>
    </div>

    <div class="notice inline notice-error is-dismissible" style="display: none;">
        <p>
        <div>
            <b><?php _e( 'Error saving settings:' ); ?></b>
        </div>
        <div class="error-content"></div>
        </p>
    </div>

    <nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab ): ?>
            <a href="#" class="nav-tab" id="nav-tab-<?php echo esc_attr( $tab->id ); ?>">
				<?php echo esc_html( $tab->label ); ?>
            </a>
		<?php endforeach; ?>
    </nav>

    <form method="post" id="visa4_settings">
        <input type="hidden" name="action" value="visa4_save_admin_settings"/>
		<?php wp_nonce_field( 'visa4_settings' ); ?>
        <div class="tabs-wrapper">
			<?php foreach ( $tabs as $tab ) : ?>
                <div class="tab-wrapper" id="tab-wrapper-<?php echo esc_attr( $tab->id ); ?>" style="display: none;">
					<?php $tab->output(); ?>
                </div>
			<?php endforeach; ?>
        </div>

        <button type="submit" id="save-btn" class="button-primary" value="<?php esc_attr_e( 'Save changes' ); ?>">
			<?php esc_html_e( 'Save changes' ); ?>
        </button>
    </form>

    <p><!-- Margin --></p>
    <div class="notice inline notice-success is-dismissible" style="display: none;">
        <p><?php _e( 'Settings saved successfully !' ); ?></p>
    </div>

    <div class="notice inline notice-error is-dismissible" style="display: none;">
        <p>
        <div><b><?php _e( 'Error saving settings:' ); ?></b></div>
        <div class="error-content"></div>
        </p>
    </div>
</div>