/* global visa4_settings_params */
( function( $, ajaxurl ) {
	const navTabIdPrefix = 'nav-tab-';
	const navTabWrapperPrefix = 'tab-wrapper-';

	const selectInitialTab = () => {
		changeTab( $('.nav-tab:first').attr( 'id' ) );
	};

	const changeTab = tabIdAttr => {
		$('.tab-wrapper').hide();
		$('.nav-tab').removeClass( 'nav-tab-active' );

		const splittedId = tabIdAttr.split('-');
		const selectedTabId = splittedId[splittedId.length - 1];

		$(`#${navTabIdPrefix + selectedTabId}`).addClass( 'nav-tab-active' );
		$(`#${navTabWrapperPrefix + selectedTabId}`).show();
	};

	const save = event => {
		event.preventDefault();

		const button = $( '#save-btn' );
		const data = $( '#visa4_settings' ).serialize();

		$.post(ajaxurl, data)
			.done(() => {
				$('.notice-error').hide();
				$('.notice-success').show();
			})
			.fail(err => {
				$('.notice-success').hide();
				$('.error-content').text(err.responseText);
				$('.notice-error').show();
			})
			.always(() => button.removeAttr( 'disabled' ));

		button.attr('disabled', 'disabled')
	};

	$(document).ready(function () {
		selectInitialTab();

		$('.nav-tab').on('click', function (e) {
			e.preventDefault();

			changeTab( $(this).attr( 'id' ) );
		});

		$( '#visa4_settings' ).on('submit', save)
	});
	
})( jQuery, ajaxurl );
