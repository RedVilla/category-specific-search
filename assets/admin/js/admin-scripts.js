jQuery( document ).ready( function($) {
	 "use strict";
		jQuery( this ).on( "click", ".cssfw_radio_box label", function ( e ) {
			
			 
			 
		});
		
		$( document ).on( 'click', '.cssfw-notice-nux .notice-dismiss', function() {
			
			$.ajax({
				url : cssfw_loc.ajaxurl,
				type : 'post',
				data : {
					action 		: 'cssfw_dismiss_notice',
					nonce 		: cssfw_loc.nonce,
				}
			});		
			
		});

});