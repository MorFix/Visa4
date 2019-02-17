/* global visa4_settings_params */
( function( $, ajaxurl ) {
	const navTabIdPrefix = 'nav-tab-';
	const navTabWrapperPrefix = 'tab-wrapper-';

	const selectInitialTab = () => {
		changeTab( $('.nav-tab:first').attr( 'id' ) );
	};

	const changeTab = tabIdAttr => {
		const saveButton = $( '#save-btn' );
		if ( tabIdAttr === 'nav-tab-countries' ) {
			saveButton.hide();
		} else {
			saveButton.show();
		}

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

		$( '#visa4_settings' ).on('submit', save);

		$( '.visa4_select_page' ).each(function () {
			const $tr = $( this );
			$tr.find( 'select' ).on( 'change', function() {
				$tr.find( '.visa4_edit_pgae a' )
				   .prop( 'href', $( this )
				   .find( 'option:selected' )
				   .data('editlink') );
			}).change();
		});
	});
	
})( jQuery, ajaxurl );
