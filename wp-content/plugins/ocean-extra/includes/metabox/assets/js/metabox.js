( function( $ ) {
	"use strict";

	$( document ).on( 'ready', function() {

		// Show/hide header options
		var headerField          	= $( '#butterbean-control-ocean_display_header .buttonset-input' ),
			headerMainSettings   	= $( '#butterbean-control-ocean_header_custom_menu' );

		if ( $( '#butterbean-control-ocean_display_header #butterbean_oceanwp_mb_settings_setting_ocean_display_header_off' ).is( ':checked' ) ) {
			headerMainSettings.hide();
		} else {
			headerMainSettings.show();
		}

		headerField.change( function () {

			if ( $( this ).val() === 'off' ) {
				headerMainSettings.hide();
			} else {
				headerMainSettings.show();
			}

		} );

		// Show/hide title options
		var titleField          	= $( '#butterbean-control-ocean_disable_title .buttonset-input' ),
			titleMainSettings   	= $( '#butterbean-control-ocean_post_title, #butterbean-control-ocean_post_subheading, #butterbean-control-ocean_post_title_style' ),
			titleStyleField     	= $( '#butterbean-control-ocean_post_title_style select' ),
			titleStyleFieldVal  	= titleStyleField.val(),
			pageTitleBgSettings 	= $( '#butterbean-control-ocean_post_title_background, #butterbean-control-ocean_post_title_bg_image_position, #butterbean-control-ocean_post_title_bg_image_attachment, #butterbean-control-ocean_post_title_bg_image_repeat, #butterbean-control-ocean_post_title_bg_image_size, #butterbean-control-ocean_post_title_height, #butterbean-control-ocean_post_title_bg_overlay, #butterbean-control-ocean_post_title_bg_overlay_color' ),
			solidColorElements  	= $( '#butterbean-control-ocean_post_title_background_color' );

		pageTitleBgSettings.hide();
		solidColorElements.hide();

		if ( titleStyleFieldVal === 'background-image' ) {
			pageTitleBgSettings.show();
		} else if ( titleStyleFieldVal === 'solid-color' ) {
			solidColorElements.show();
		}

		if ( $( '#butterbean-control-ocean_disable_title #butterbean_oceanwp_mb_settings_setting_ocean_disable_title_on' ).is( ':checked' ) ) {
			titleMainSettings.hide();
			pageTitleBgSettings.hide();
			solidColorElements.hide();
		} else {
			titleMainSettings.show();
		}

		titleField.change( function () {

			if ( $( this ).val() === 'on' ) {
				titleMainSettings.hide();
				pageTitleBgSettings.hide();
				solidColorElements.hide();
			} else {
				titleMainSettings.show();
				var titleStyleFieldVal = titleStyleField.val();

				if ( titleStyleFieldVal === 'background-image' ) {
					pageTitleBgSettings.show();
				} else if ( titleStyleFieldVal === 'solid-color' ) {
					solidColorElements.show();
				}
			}

		} );

		titleStyleField.change( function () {

			pageTitleBgSettings.hide();
			solidColorElements.hide();

			if ( $( this ).val() == 'background-image' ) {
				pageTitleBgSettings.show();
			} else if ( $( this ).val() === 'solid-color' ) {
				solidColorElements.show();
			}

		} );

		// Show/hide breadcrumbs options
		var breadcrumbsField        = $( '#butterbean-control-ocean_disable_breadcrumbs .buttonset-input' ),
			breadcrumbsSettings   	= $( '#butterbean-control-ocean_breadcrumbs_color, #butterbean-control-ocean_breadcrumbs_separator_color, #butterbean-control-ocean_breadcrumbs_links_color, #butterbean-control-ocean_breadcrumbs_links_hover_color' );

		if ( $( '#butterbean-control-ocean_disable_breadcrumbs #butterbean_oceanwp_mb_settings_setting_ocean_disable_breadcrumbs_off' ).is( ':checked' ) ) {
			breadcrumbsSettings.hide();
		} else {
			breadcrumbsSettings.show();
		}

		breadcrumbsField.change( function () {

			if ( $( this ).val() === 'off' ) {
				breadcrumbsSettings.hide();
			} else {
				breadcrumbsSettings.show();
			}

		} );

	} );

} ) ( jQuery );