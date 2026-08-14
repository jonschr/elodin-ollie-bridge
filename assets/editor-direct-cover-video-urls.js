( function ( root ) {
	function isDirectMp4Url( value ) {
		try {
			const trimmed = value.trim();
			if ( /["'<>\s]/.test( trimmed ) ) {
				return false;
			}

			const url = new URL( trimmed );
			return /^https?:$/.test( url.protocol ) && ! url.username && ! url.password && /\.mp4$/i.test( url.pathname );
		} catch ( error ) {
			return false;
		}
	}

	if ( 'object' === typeof module && module.exports ) {
		module.exports = isDirectMp4Url;
	}

	const { document, wp } = root;
	if ( ! document || ! wp || ! wp.data ) {
		return;
	}

	document.addEventListener(
		'click',
		function ( event ) {
			const button = event.target.closest && event.target.closest( 'button.components-button.is-primary' );
			const dialog = button && button.closest( '[role="dialog"]' );
			const input = dialog && dialog.querySelector( 'input[type="url"]' );
			if ( ! input || ! isDirectMp4Url( input.value ) ) {
				return;
			}

			const editor = wp.data.select( 'core/block-editor' );
			const clientId = editor && editor.getSelectedBlockClientId();
			const block = clientId && editor.getBlock( clientId );
			if ( ! block || 'core/cover' !== block.name ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();

			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( clientId, {
				url: input.value.trim(),
				backgroundType: 'video',
				dimRatio: undefined === block.attributes.url && 100 === block.attributes.dimRatio ? 50 : block.attributes.dimRatio,
				id: undefined,
				focalPoint: undefined,
				hasParallax: undefined,
				isRepeated: undefined,
				useFeaturedImage: undefined,
			} );

			const cancel = dialog.querySelector( 'button.components-button.is-tertiary' );
			if ( cancel ) {
				cancel.click();
			}
		},
		true
	);
} )( 'undefined' === typeof window ? globalThis : window );
