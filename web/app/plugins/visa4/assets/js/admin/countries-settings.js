/* global visa4CountriesSettingsParams, wp, ajaxurl */
( function( $, data, wp, ajaxurl ) {
    $( function() {
        const $tbody          = $( '.visa4-countries-rows' ),
              $save_button    = $( '.visa4-countries-save' ),
              $row_template   = wp.template( 'visa4-country-row' ),
              $source_country_template = wp.template('visa4-source-country'),
              $blank_template = wp.template( 'visa4-country-row-blank' ),

            // Backbone model
            Country       = Backbone.Model.extend({
                changes: {},
                logChanges: function( changedRows ) {
                    const changes = this.changes || {};

                    _.each( changedRows, function( row, country_code ) {
                        changes[ country_code ] = _.extend( changes[ country_code ] || { country_code }, row );
                    } );

                    this.changes = changes;

                    if (Object.keys(this.changes).length) {
                        this.trigger( 'change:countries' );
                    }
                },
                save: function() {
                    if ( _.size( this.changes ) ) {
                        $.post( ajaxurl + ( ajaxurl.indexOf( '?' ) > 0 ? '&' : '?' ) + 'action=visa4_save_admin_countries_settings', {
                            wc_shipping_classes_nonce : data.wc_shipping_classes_nonce,
                            changes                 : this.changes
                        }, this.onSaveResponse.bind(this), 'json' );
                    } else {
                        this.trigger( 'saved:countries' );
                    }
                },
                discardChanges: function( id ) {
                    var changes      = this.changes || {};

                    // Delete all changes
                    delete changes[ id ];

                    // No changes? Disable save button.
                    if ( 0 === _.size( this.changes ) ) {
                        countryView.clearUnloadConfirmation();
                    }
                },
                onSaveResponse: function( response, textStatus ) {
                    if ( 'success' === textStatus ) {
                        if ( response.success ) {
                            this.set( 'countries', response.data.countries );
                            this.trigger( 'change:countries' );
                            this.changes = {};
                            this.trigger( 'saved:countries' );
                        } else if ( response.data ) {
                            window.alert( response.data );
                        } else {
                            window.alert( data.strings.save_failed );
                        }
                    }
                    countryView.unblock();
                }
            } ),

            // Backbone view
            CountryView = Backbone.View.extend({
                rowTemplate: $row_template,
                sourceCountryTemplate: $source_country_template,
                initialize: function() {
                    this.listenTo( this.model, 'change:countries', this.setUnloadConfirmation );
                    this.listenTo( this.model, 'saved:countries', this.clearUnloadConfirmation );
                    this.listenTo( this.model, 'saved:countries', this.render );
                    $tbody.on( 'change', { view: this }, this.updateModelOnChange );
                    $( window ).on( 'beforeunload', { view: this }, this.unloadConfirmation );
                    $save_button.on( 'click', { view: this }, this.onSubmit );
                    $( document.body ).on( 'click', '.visa4-country-add', { view: this }, this.onAddNewRow );
                    $( document.body ).on( 'click', '.visa4-country-save-changes', { view: this }, this.onSubmit );
                },
                block: function() {
                    $( this.el ).block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                },
                unblock: function() {
                    $( this.el ).unblock();
                },
                render: function() {
                    var countries       = _.indexBy( this.model.get( 'countries' ), 'country_code' ),
                        newCountries    = Object.keys(this.model.changes)
                                                .filter(key => this.model.changes[key].newRow)
                                                .reduce((obj, key) => {
                                                    obj[key] = this.model.changes[key];

                                                    return obj;
                                                }, {});
                        view        = this;

                    this.$el.empty();
                    this.unblock();

                    if ( _.size( countries ) || _.size( newCountries ) ) {
                        // Sort countries
                        countries = _.sortBy( countries, function( country ) {
                            return country.name;
                        } );

                        const self = this;
                        // Populate $tbody with the current countries
                        $.each( countries, function( id, rowData ) {
                            view.renderRow( Object.assign( {}, rowData, self.model.changes[ rowData.country_code ] ) );
                        } );

                        $.each( newCountries, function( id, rowData ) {
                            view.renderRow( Object.assign( {}, rowData, { country_key: id || rowData.country_code } ) );
                        } );
                    } else {
                        view.$el.append( $blank_template );
                    }
                },
                createRowTemplate: function( rowData ) {
                    const row = $( this.rowTemplate( Object.assign({}, rowData, {
                        formsLink: data.formsLink,
                        allForms: data.allForms
                    }) ) );
                    if ( rowData.form_id ) {
                        row.find('.conditional-no-form').hide();
                    } else {
                        row.find('.conditional-form').hide();
                    }

                    const sourceCountries = row.find('.source_countries');
                    const self = this;

                    rowData.source_countries && rowData.source_countries.forEach(function( country ) {
                        const elem = $( document.createElement('div') );
                        elem.html( self.sourceCountryTemplate( visa4CountriesSettingsParams.allCountries[country] ) );

                        sourceCountries.append( elem );
                    });

                    return row;
                },
                renderRow: function( rowData ) {
                    const view = this;
                    const row = this.createRowTemplate( rowData );

                    view.$el.append( row );
                    view.initRow( rowData );
                },
                initRow: function( rowData ) {
                    var view = this;
                    var $tr = view.$el.find( 'tr[data-id="' + (rowData.country_key || rowData.country_code) + '"]');

                    // Support regular select boxes
                    $tr.find( 'select:not([multiple])' ).each( function() {
                        const attribute = $( this ).data( 'attribute' );
                        $( this ).find( 'option[value="' + rowData[ attribute ] + '"]' ).prop( 'selected', true );
                    } );

                    // Support multi select boxes
                    $tr.find( 'select[multiple]' ).each( function() {
                        const attribute = $( this ).data( 'attribute' );
                        $( this ).find( 'option' ).each( function () {
                            const option = $( this );
                            if ( rowData[attribute] && rowData[attribute].includes( option.val() ) ) {
                                option.prop( 'selected', true );
                            }
                        } );
                    } );

                    // Make the rows function
                    $tr.find( '.edit, .add' ).hide();
                    $tr.find( '.view' ).show();
                    $tr.find( '.visa4-country-edit' ).on( 'click', { view: this }, this.onEditRow );
                    $tr.find( '.visa4-country-delete' ).on( 'click', { view: this }, this.onDeleteRow );
                    $tr.find( '.editing .visa4-country-edit' ).trigger('click');
                    $tr.find( '.visa4-country-cancel-edit' ).on( 'click', { view: this }, this.onCancelEditRow );

                    // Editing ?
                    if ( rowData.editing ) {
                        $tr.find( '.visa4-country-edit' ).trigger( 'click' );
                    // Adding new ?
                    } else if ( rowData.newRow ) {
                        this.initNewRow( $tr, rowData );
                    }
                },
                onSubmit: function( event ) {
                    event.data.view.block();
                    event.data.view.model.save();
                    event.preventDefault();
                },
                initNewRow: function( $tr, rowData ) {
                    $tr.find('.view, .edit').hide();
                    $tr.find('.add').show();
                    this.model.trigger( 'change:countries' );

                    // Setting the real value of the country code select field (like triggering a change)
                    const selector = 'select[name="destination[' + rowData.country_key + ']"]';
                    const changes = {};

                    changes[ rowData.country_key ] = {
                        'country_code': $tr.find(selector).val(),
                    };

                    this.model.logChanges( changes )
                },
                onAddNewRow: function( event ) {
                    event.preventDefault();

                    const view    = event.data.view,
                        model   = view.model,
                        countries   = _.indexBy( model.get( 'countries' ), 'country_code' ),
                        changes = {},
                        size    = _.size( countries ),
                        newRow  = Object.assign( {}, data.defaultCountry, {
                            country_code: 'new-' + size + '-' + Date.now(),
                            newRow : true
                        } );

                    changes[ newRow.country_code ] = newRow;

                    model.logChanges( changes );
                    view.render();
                },
                onEditRow: function( event ) {
                    const $tr = $( this ).closest('tr');

                    event.preventDefault();
                    $tr.addClass('editing');
                    $tr.find('.view, .add').hide();
                    $tr.find('.edit').show();

                    const id = $tr.data( 'id' );
                    _.indexBy( event.data.view.model.get( 'countries' ), 'country_code' )[id]['editing'] = true;

                    event.data.view.model.trigger( 'change:countries' );
                },
                onDeleteRow: function( event ) {
                    var view    = event.data.view,
                        model   = view.model,
                        countries = _.indexBy( model.get( 'countries' ), 'country_code' ),
                        changes = {},
                        country_code = $( this ).closest('tr').data('id');

                    event.preventDefault();

                    if ( countries[ country_code ] ) {
                        delete countries[ country_code ];
                        changes[ country_code ] = _.extend( changes[ country_code ] || {}, { deleted : 'deleted' } );
                        model.set( 'countries', countries );

                        model.logChanges( changes );
                    }

                    view.render();
                },
                onCancelEditRow: function( event ) {
                    var view    = event.data.view,
                        model   = view.model,
                        row     = $( this ).closest('tr'),
                        country_code = $( this ).closest('tr').data('id'),
                        countries = _.indexBy( model.get( 'countries' ), 'country_code' );

                    event.preventDefault();
                    model.discardChanges( country_code );

                    // Cancelling edit an existing country ?
                    if ( countries[ country_code ] ) {
                        delete countries[ country_code ].editing;
                        row.after( view.createRowTemplate( countries[ country_code ] ) );
                        view.initRow( countries[ country_code ] );
                    }

                    row.remove();
                    view.render();
                },
                setUnloadConfirmation: function() {
                    this.needsUnloadConfirm = true;
                    $save_button.removeAttr( 'disabled' );
                },
                clearUnloadConfirmation: function() {
                    this.needsUnloadConfirm = false;
                    $save_button.attr( 'disabled', 'disabled' );
                },
                unloadConfirmation: function( event ) {
                    if ( event.data.view.needsUnloadConfirm ) {
                        event.returnValue = data.strings.unload_confirmation_msg;
                        window.event.returnValue = data.strings.unload_confirmation_msg;
                        return data.strings.unload_confirmation_msg;
                    }
                },
                updateModelOnChange: function( event ) {
                    var model     = event.data.view.model,
                        $target   = $( event.target ),
                        country_code   = $target.closest( 'tr' ).data( 'id' ),
                        attribute = $target.data( 'attribute' ),
                        value     = $target.val(),
                        countries   = _.indexBy( model.get( 'countries' ), 'country_code' ),
                        changes = {};

                    if ( ! countries[ country_code ] || !_.isEqual( countries[ country_code ][ attribute ], value ) ) {
                        changes[ country_code ] = {};
                        changes[ country_code ][ attribute ] = value;
                    }

                    model.logChanges( changes );
                }
            } ),

            countryModel = new Country({
                countries: data.countries
            } ),

            countryView = new CountryView({
                model:    countryModel,
                el:       $tbody
            } );

        countryView.render();
    });
})( jQuery, visa4CountriesSettingsParams, wp, ajaxurl );