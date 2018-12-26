<?php
/**
 * Admin View: Custom Error
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="notice notice-error">
	<?php echo wpautop(sanitize_text_field($message)); ?>
</div>
