jQuery(document).ready(function() {
    const addToCartForm = jQuery('#visa4-add-to-cart');
    const visa4FormWrapper = jQuery('#visa4-form');
    const visa4FormDialog = visa4FormWrapper.dialog({
        autoOpen: false,
        modal: true,
        dialogClass: 'wp-dialog',
        buttons: {
            Close: function () {
                visa4FormDialog.dialog( 'close' );
            }
        }
    });

    function registerAddToCartFormHandler() {
        addToCartForm.on('submit', function(event) {
            event.preventDefault();
            visa4FormDialog.dialog( 'open' );
        });
    }

    function onAfterFormSubmit(result) {
        if (!result || !result.success) {
            return;
        }

        jQuery('#submission_id').val(result.submission_id);

        addToCartForm.off('submit');
        addToCartForm.submit();
        registerAddToCartFormHandler();
    }

    registerAddToCartFormHandler();

    const visa4Form = visa4FormWrapper.find('.fc-form');

    // Detach the default FormCraft handler
    visa4Form.off('submit');

    visa4Form.on('submit', function (event) {
        event.preventDefault();
        FormCraftSubmitForm(jQuery(this), 'all', onAfterFormSubmit);
    });
});