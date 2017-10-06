/*
 * WPImmo Admin JavaScript
 *
 * Copyright (c) 2015 CVMH solutions
 * http://www.agence-web-cvmh.fr
 *
 * Depends:
 *   jquery.ui.core.js
 */
var wpimmo = {
    ajaxurl: '',
    l10n: {},
    fields: {},
    taxonomies: {}
};

jQuery( document ).ready( function ( $ ) {
        
    /*
     * Confirm dialog box
     */
    $( ".confirm-link" ).click( function( e ) {
        e.preventDefault();
        var targetUrl = $( this ).attr( "href" );
        var textConfirm = $( this ).data( "txt" );
        
        if ( confirm( textConfirm ) ) {
            window.location.href = targetUrl;
        } else {
            return false;
        }
    });
    
    /* 
     * Slide toggle for main settings
     */
    $( ".toggle-checkbox" ).on( 'change', function( e ) {
        var targetClass = $( this ).data( "toggle" );
        $( '.'+targetClass ).slideToggle();
    });
    
    /*
     * Date picker for property edit
     */
    if ( $( '.datepicker' ).length > 0 ) {
        $( '.datepicker' ).datepicker({
            dateFormat: "dd-mm-yy"
        });
    }

    /*
     * Ajax process for import and delete
     */
    if ( $( "#post-body" ).data( 'tab' ) === "import" || $( "#post-body" ).data( 'tab' ) === "delete" ) {
        var wpi_feed = $( "#wpi_feed" ).html();
        var wpi_ids = JSON.parse( "[" + $( "#wpi_ids" ).html() + "]" );
        var wpi_total = wpi_ids.length;
        var wpi_refs = JSON.parse( $( "#wpi_refs" ).html() );
        var wpi_existing_refs = JSON.parse( $( "#wpi_existing_refs" ).html() );
        var wpi_refs_to_delete = JSON.parse( $( "#wpi_refs_to_delete" ).html() );
        var wpi_count = 1;
        var wpi_timestart = new Date().getTime();
        var wpi_timeend = 0;
        var wpi_totaltime = 0;
        var wpi_resulttext = '';
        var wpi_continue = true;
        var wpi_text_finish = $( "#wpi_text_finish" ).html().toString();
        if ( $( "#post-body" ).data( 'tab' ) === "import" ) {
            var wpi_text_saving = wpimmo.l10n.saving;
            var wpi_text_tab = wpimmo.l10n.import_completed;
            var wpi_text_error_process = wpimmo.l10n.import_error;
        } else if ( $( "#post-body" ).data( 'tab' ) === "delete" ) {
            var wpi_text_saving = wpimmo.l10n.deleting;
            var wpi_text_tab = wpimmo.l10n.deletion_completed;
            var wpi_text_error_process = wpimmo.l10n.delete_error;
        }

        // Create the progress bar
        $( "#wpimmo-bar" ).progressbar();
        $( "#wpimmo-bar-percent" ).html( "0%" );

        // Stop button
        $( "#wpimmo-stop" ).click( function() {
            wpi_continue = false;
            $( '#wpimmo-stop' ).val( wpimmo.l10n.stopping );
        });

        // Called after each process. Updates debug information and the progress bar.
        function WPImmoProcessUpdateStatus( id, success, response ) {
            $( "#wpimmo-bar" ).progressbar( "value", ( wpi_count / wpi_total ) * 100 );
            $( "#wpimmo-bar-percent" ).html( Math.round( ( wpi_count / wpi_total ) * 1000 ) / 10 + "%" );
            wpi_count = wpi_count + 1;
            $( "#wpimmo-current-process" ).html( wpi_text_saving + ' ' + wpi_refs[ wpi_ids[ 0 ] ] + '...' );
        }

        // Called when all items have been processed. Shows the results and cleans up.
        function WPImmoFinishUp() {
            wpi_timeend = new Date().getTime();
            wpi_totaltime = Math.round( ( wpi_timeend - wpi_timestart ) / 1000 );
            $( "#wpimmo-current-process" ).html('');

            $( '#wpimmo-stop' ).hide();
            
            $( '#wpimmo-process-tab' ).html( wpi_text_tab );

            $( "#message" ).html( "<p><strong>" + wpi_text_finish + "</strong></p>" );
            $( "#message" ).show();
        }

        // Process a specific item via AJAX
        function WPImmoProcessItem( id ) {
            $.ajax({
                type: 'POST',
                url: wpimmo.ajaxurl,
                data: { action: "wpimmo_" + $("#post-body").data('tab'), feed: wpi_feed, id: id, remain: wpi_ids.length, existing: wpi_existing_refs, delete: wpi_refs_to_delete },
                success: function( response ) {
                    if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
                        response = new Object;
                        response.success = false;
                        response.error = wpi_text_error_process;
                    }

                    if ( response.success ) {
                        WPImmoProcessUpdateStatus( id, true, response );
                    } else {
                        WPImmoProcessUpdateStatus( id, false, response );
                    }

                    if ( wpi_ids.length && wpi_continue ) {
                        WPImmoProcessItem( wpi_ids.shift() );
                    } else {
                        WPImmoFinishUp();
                    }
                },
                error: function( response ) {
                    WPImmoProcessUpdateStatus( id, false, response );

                    if ( wpi_ids.length && wpi_continue ) {
                        WPImmoProcessItem( wpi_ids.shift() );
                    } else {
                        WPImmoFinishUp();
                    }
                }
            });
        }

        WPImmoProcessItem( wpi_ids.shift() );
    }
    
    /* 
     * Image management
     */
    // Set all variables to be used in scope
    var wpiFrame,
        wpiMetaBox = $( '#wpimmo_images.postbox' ),
        wpiAddImgLink = wpiMetaBox.find( '.wpimmo-button-add' ),
        wpiImgContainer = wpiMetaBox.find( '.wpimmo-img-container' ),
        wpiImgIdsInput = wpiMetaBox.find( '.wpimmo-img-ids' ),
        wpiImgButtons = '<a class="wpimmo-button-delete ir" href="javascript:void(0);">' + wpimmo.l10n.delete + '</a>';
        
    // Sort images
    if ( wpiImgContainer.find( 'li' ).length > 0 ) {
        wpiImgContainer.sortable({
            update: function(){
                var ids = [];
                $( '.wpimmo-img' ).each( function() {
                    ids.push( $( this ).attr( 'id' ).substr( 4 ) );
                });
                wpiImgIdsInput.val( ids.join(',') );
            }
        });
    }
        
    // Add image link
    wpiAddImgLink.on( 'click', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( wpiFrame ) {
          wpiFrame.open();
          return;
        }

        // Create a new media frame
        wpiFrame = wp.media({
            title    : wpimmo.l10n.select,
            button   : { text: wpimmo.l10n.use_selection },
            library  : { type: 'image' },
            multiple : true
        });

        // When an image is selected in the media frame...
        wpiFrame.on( 'select', function() {
            // Get media attachment details from the frame state
            var selection = wpiFrame.state().get( 'selection' );
            selection.map( function( attachment ) {
                attachment = attachment.toJSON();
                // Send the attachment URL to our custom image input field.
                if ( typeof attachment.sizes.thumbnail === 'undefined' ) {
                    image = attachment.url;
                } else {
                    image = attachment.sizes.thumbnail.url;
                }
                wpiImgContainer.append( '<li id="img-' + attachment.id + '" class="wpimmo-img">' + wpiImgButtons + '<img src="' + image + '" alt="" style="max-width:100%;"/></li>' );
                // Send the attachment id to our hidden input
                var ids = wpiImgIdsInput.val().split(',');
                if( ids[0] === '' )
                    ids.splice(0,1);
                ids.push( attachment.id );
                wpiImgIdsInput.val( ids.join(',') );
            });
        });

        // Finally, open the modal on click
        wpiFrame.open();
    });
    
    // Delete image link
    wpiImgContainer.on( 'click', '.wpimmo-button-delete', function( event ) {

        event.preventDefault();

        var ids = [],
            selection = $( this ).parent( 'li' );

        // Clear out the preview image
        selection.remove();

        // Update the hidden input
        $( '.wpimmo-img' ).each( function() {
            ids.push( $( this ).attr( 'id' ).substr( 4 ) );
        });
        wpiImgIdsInput.val( ids.join( ',' ) );

    });

    /*
     * Toggle search field_form
     */
    function toggle_field_form( field ) {
        if (field.children( '.field_form' ).is( ":hidden" )) {
            field.addClass( 'form_open' );
            field.find( '.genericon' ).removeClass( 'genericon-downarrow' );
            field.find( '.genericon' ).addClass( 'genericon-uparrow' );
        } else {
            field.removeClass( 'form_open' );
            field.find( '.genericon' ).removeClass( 'genericon-uparrow' );
            field.find( '.genericon' ).addClass( 'genericon-downarrow' );
        }
        field.children( '.field_form' ).slideToggle();
    }
    $( '.wpimmo-admin-form-search' ).on( 'click', '.toggle-field-form', function() {
        toggle_field_form( $( this ).closest( '.field' ) );
    });
    
    /*
     * Sort search/groups fields
     */
    $( '.fields' ).sortable({
        axis: "y",
        handle: "td.field-position",
        update: function() {
            reset_fields_position( $( this ) );
        }
    });
    
    /*
     * Reset order
     */
    function reset_fields_position( fields ){
        var count = 1;
        fields.find( '.circle' ).each( function() {
            $( this ).html( count );
            count++;
        });
    }
    
    /*
     * Add search field
     */
    $( '.wpimmo-admin-form-search' ).on( 'click', '.wpimmo-button-add', function() {
        var fields = $( this ).siblings( '.fields' );
        // clone last tr
        var new_field = fields.children( '.field_clone' ).clone();
        //rename inputs for unicity
        setTimeout(function(){
            rename_field( new_field, fields, 'search' );
        }, 500);
        // show
        new_field.removeClass( 'field_clone' );
        // append to table
        fields.children( '.field_clone' ).before( new_field );
        toggle_field_form( new_field );
        // reset positions
        reset_fields_position( fields );
    });
    
    /**
     * Rename search/group field for unicity
     * @param {type} field
     * @param {type} fields
     * @param {type} type
     * @returns {undefined}
     */
    function rename_field( field, fields, type ) {
        var group = fields.data( 'group' );
        var t = new Date();
        var unique = t.getTime();
        field.data( 'unique', unique );
        field.attr( 'data-unique', unique);
        field.find( '.field_form .field-input' ).each( function(){
            var key = $( this ).data( 'key' );
            $( this ).attr( 'name', 'wpimmo_' + type + '[' + group + '][' + unique + '][' + key + ']' );
        });
    }
    
    /**
     * Change search field key
     * 
     * @param {type} item
     * @param {type} choice
     * @returns {undefined}
     */
    function change_field_key( item, choice ) {
        var field = item.closest( '.field' );
        var field_key = field.find('.field_meta td.field-key a');
        var field_type = field.find( 'select.field-type' );
        var field_type_choice = field_type.find( 'option:selected' ).val();
        field_key.html( choice );
        if ( wpimmo.fields[ choice ].type === 'taxonomy' ) {
            field_type.children( 'option' ).attr( 'disabled', 'disabled' );
            field_type.children( 'option' ).filter( '[value="select"]' ).removeAttr( 'disabled' );
            field_type.children( 'option' ).prop( 'selected', false ).filter( '[value="select"]' ).prop( 'selected', true );
        } else {
            field_type.children( 'option' ).removeAttr( 'disabled' );
        }
        change_field_type( field_type, field_type_choice );
    }
    $( '.wpimmo-admin-form-search' ).on( 'change', 'select.field-key', function() {
        change_field_key( $( this ), this.value );
    });
    
    /**
     * Change search field type
     * 
     * @param {type} item
     * @param {type} choice
     * @returns {undefined}
     */
    function change_field_type( item, choice ) {
        var field = item.closest( '.field' );
        var field_type = field.find('.field_meta td.field-type');
        var field_key = field.find( '.field-key option:selected' ).val();
        var tax_name = field.find( '.field-taxonomy-name td span' );
        var tax_key = field.find( '.field-taxonomy-name td input' );
        if ( field_key != '' && wpimmo.fields[ field_key ].type === 'taxonomy' ) {
            var taxonomy_key = wpimmo.fields[ field_key ].taxonomy;
            tax_name.html( wpimmo.taxonomies[ taxonomy_key ].labels.singular_name );
            tax_key.val( taxonomy_key );
            field.find( '.field-taxonomy' ).show();
            choice = 'select';
        } else {
            field.find( '.field-taxonomy' ).hide();
            tax_name.html( "" );
            tax_key.val( "" );
        }
        field_type.html( wpimmo.l10n[ choice ] );
        switch( choice ) {
            case 'text':
                field.find( '.field-data' ).show();
                field.find( '.field-text' ).show();
                field.find( '.field-select' ).hide();
                field.find( '.field-interval' ).hide();
                field.find( '.field-submit' ).hide();
                break;
            case 'select':
                field.find( '.field-data' ).show();
                field.find( '.field-text' ).hide();
                field.find( '.field-select' ).show();
                field.find( '.field-interval' ).hide();
                field.find( '.field-submit' ).hide();
                break;
            case 'interval':
                field.find( '.field-data' ).show();
                field.find( '.field-text' ).hide();
                field.find( '.field-select' ).hide();
                field.find( '.field-interval' ).show();
                field.find( '.field-submit' ).hide();
                break;
             case 'submit':
                field.find('select.field-key').val( "" );
                field.find( '.field-data' ).hide();
                field.find( '.field-text' ).hide();
                field.find( '.field-select' ).hide();
                field.find( '.field-interval' ).hide();
                field.find( '.field-submit' ).show();
                break;
       }
    }
    $( '.wpimmo-admin-form-search' ).on( 'change', 'select.field-type', function() {
        change_field_type( $( this ), this.value );
    });
    
    /**
     * Remove search field
     */
    $( '.wpimmo-admin-form-search' ).on( 'click', '.wpimmo-button-delete', function() {
        var fields = $( this ).closest( '.fields' );
        $( this ).closest( '.field' ).remove();
        reset_fields_position( fields );
    });
 
    /*
     * Sort groups
     */
    $( '.groups' ).sortable({
        axis: "y",
        handle: "h3.group-handle"
    });
    
    /**
     * Add group
     */
    $( '.wpimmo-admin-form-groups' ).on( 'click', '.wpimmo-button-add-group', function() {
        var groups = $( this ).siblings( '.groups' );
        var new_group = groups.children( '.group_clone' ).clone();
        setTimeout( function (){
            rename_group( new_group, groups );
        }, 500 );
        new_group.removeClass( 'group_clone' );
        groups.children( '.group_clone' ).before( new_group );
    });
    
    /**
     * Rename group for unicity
     * @param {type} group
     * @param {type} groups
     * @returns {undefined}
     */
    function rename_group( group, groups ) {
        var t = new Date();
        var unique_field = t.getTime();
        var unique_group = 'group_' + unique_field;
        group.data( 'unique', unique_group );
        group.attr( 'data-unique', unique_group);
        var fields = group.find( '.fields' );
        fields.data( 'group', unique_group );
        fields.attr( 'data-group', unique_group);
        fields.find( '.field' ).each( function(){
            rename_field( $( this ), fields, 'group' );
        });
    }
    
    /**
     * Add group field
     */
    $( '.wpimmo-admin-form-groups' ).on( 'click', '.wpimmo-button-add', function() {
        var fields = $( this ).siblings( '.fields' );
        // clone last tr
        var new_field = fields.children( '.field_clone' ).clone();
        //rename inputs for unicity
        setTimeout(function(){
            rename_field( new_field, fields, 'groups' );
        }, 500);
        // show
        new_field.removeClass( 'field_clone' );
        // append to table
        fields.children( '.field_clone' ).before( new_field );
        toggle_field_form( new_field );
        // reset positions
        reset_fields_position( fields );
    });
    
    /**
     * Toggle groups field_form
     */
    $( '.wpimmo-admin-form-groups' ).on( 'click', '.toggle-field-form', function() {
        toggle_field_form( $( this ).closest( '.field' ) );
    });

    /**
     * Remove group
     */
    $( '.wpimmo-admin-form-groups' ).on( 'click', 'h3 .wpimmo-button-delete', function() {
        var groups = $( this ).closest( '.groups' );
        $( this ).closest( '.group' ).remove();
    });

    /**
     * Change group field key
     */
    $( '.wpimmo-admin-form-groups' ).on( 'change', 'select.field-key', function() {
        var field = $( this ).closest( '.field' );
        var field_key = field.find('.field_meta td.field-key a');
        field_key.html( this.value );
    });

    /**
     * Remove group field
     */
    $( '.wpimmo-admin-form-groups' ).on( 'click', '.form-table .wpimmo-button-delete', function() {
        var fields = $( this ).closest( '.fields' );
        $( this ).closest( '.field' ).remove();
        reset_fields_position( fields );
    });
    
});
