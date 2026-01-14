/**
 * WP SEO Pilot Breadcrumbs Block
 *
 * @package WPSEOPilot
 */

( function( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	const { registerBlockType } = blocks;
	const { createElement: el, Fragment } = element;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, ToggleControl, SelectControl, TextControl, Placeholder } = components;
	const ServerSideRender = serverSideRender;
	const { __ } = i18n;

	registerBlockType( 'wpseopilot/breadcrumbs', {
		edit: function( props ) {
			const { attributes, setAttributes } = props;
			const { showHome, homeLabel, separator, showCurrent, linkCurrent, stylePreset } = attributes;
			const blockProps = useBlockProps();

			const separatorOptions = [
				{ value: '', label: __( 'Default (from settings)', 'wp-seo-pilot' ) },
				{ value: '>', label: '>' },
				{ value: '/', label: '/' },
				{ value: '|', label: '|' },
				{ value: '-', label: '-' },
				{ value: 'arrow', label: __( 'Arrow', 'wp-seo-pilot' ) + ' (→)' },
				{ value: 'chevron', label: __( 'Chevron', 'wp-seo-pilot' ) + ' (»)' },
			];

			const styleOptions = [
				{ value: '', label: __( 'Default (from settings)', 'wp-seo-pilot' ) },
				{ value: 'default', label: __( 'Default', 'wp-seo-pilot' ) },
				{ value: 'minimal', label: __( 'Minimal', 'wp-seo-pilot' ) },
				{ value: 'rounded', label: __( 'Rounded', 'wp-seo-pilot' ) },
				{ value: 'pills', label: __( 'Pills', 'wp-seo-pilot' ) },
				{ value: 'none', label: __( 'No Styling', 'wp-seo-pilot' ) },
			];

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Breadcrumb Settings', 'wp-seo-pilot' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Separator', 'wp-seo-pilot' ),
							value: separator,
							options: separatorOptions,
							onChange: function( value ) {
								setAttributes( { separator: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'Style Preset', 'wp-seo-pilot' ),
							value: stylePreset,
							options: styleOptions,
							onChange: function( value ) {
								setAttributes( { stylePreset: value } );
							},
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Home Link', 'wp-seo-pilot' ), initialOpen: false },
						el( ToggleControl, {
							label: __( 'Show Home Link', 'wp-seo-pilot' ),
							checked: showHome,
							onChange: function( value ) {
								setAttributes( { showHome: value } );
							},
						} ),
						showHome && el( TextControl, {
							label: __( 'Custom Home Label', 'wp-seo-pilot' ),
							value: homeLabel,
							placeholder: __( 'Home', 'wp-seo-pilot' ),
							onChange: function( value ) {
								setAttributes( { homeLabel: value } );
							},
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Current Page', 'wp-seo-pilot' ), initialOpen: false },
						el( ToggleControl, {
							label: __( 'Show Current Page', 'wp-seo-pilot' ),
							checked: showCurrent,
							onChange: function( value ) {
								setAttributes( { showCurrent: value } );
							},
						} ),
						showCurrent && el( ToggleControl, {
							label: __( 'Link Current Page', 'wp-seo-pilot' ),
							checked: linkCurrent,
							onChange: function( value ) {
								setAttributes( { linkCurrent: value } );
							},
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( Placeholder, {
						icon: 'admin-links',
						label: __( 'SEO Breadcrumbs', 'wp-seo-pilot' ),
						instructions: __( 'Breadcrumb navigation will be displayed here based on the current page context.', 'wp-seo-pilot' ),
					},
					el(
						'div',
						{ className: 'wpseopilot-breadcrumbs-preview' },
						el( 'span', null, __( 'Home', 'wp-seo-pilot' ) ),
						el( 'span', { className: 'wpseopilot-breadcrumbs-preview__sep' }, ' ' + ( separator || '>' ) + ' ' ),
						el( 'span', null, __( 'Category', 'wp-seo-pilot' ) ),
						el( 'span', { className: 'wpseopilot-breadcrumbs-preview__sep' }, ' ' + ( separator || '>' ) + ' ' ),
						el( 'span', { style: { opacity: 0.7 } }, __( 'Current Page', 'wp-seo-pilot' ) )
					) )
				)
			);
		},

		save: function() {
			// Dynamic block - rendered via PHP
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
);
