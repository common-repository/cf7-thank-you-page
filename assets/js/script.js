jQuery( document ).ready( function( $ ) {
	$( document ).on( 'change', '.all_post_types', function() {
		$( '.divider' ).html( '<span class="spinner is-active"></span>' );
		$.post(
			cf7_redirect.ajaxurl,
			{
				action: 'cf7_redirect_action',
				selected_post_type: $( 'select[name="all_posts_type"]' ).val(),
				nonce: cf7_redirect.security_nonce
			},
			function( res ) {
				if ( res.status == 1 ) {
					$( 'select[name="all_pages"]' ).html( res.html );
				}
				$( '.divider' ).html( '<span class="dashicons dashicons-arrow-right-alt"></span>' );
			},
			'json'
		);
	} );

	// External url.
	$( document ).on( 'change', 'input[name="custom_link"]', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '.url-textbox' ).removeAttr( 'disabled' ).removeClass( 'cf7-redirect-hidden' );
			$( '.dynamic-url' ).addClass( 'cf7-redirect-hidden' ).find( 'select' ).attr( 'disabled', true );
		} else {
			$( '.url-textbox' ).attr( 'disabled', true ).addClass( 'cf7-redirect-hidden' );
			$( '.dynamic-url' ).removeClass( 'cf7-redirect-hidden' ).find( 'select' ).removeAttr( 'disabled' );
		}
	} );
	$( 'input[name="custom_link"]' ).change();

	// Enable redirection.
	$( document ).on( 'change', 'input[name="enable_redirect"]', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '.cf7-redirect-url' ).removeAttr( 'disabled' ).removeClass( 'cf7-redirect-hidden' );
		} else {
			$( '.cf7-redirect-url' ).attr( 'disabled', true ).addClass( 'cf7-redirect-hidden' );
		}
	} );
	$( 'input[name="enable_redirect"]' ).change();
} );