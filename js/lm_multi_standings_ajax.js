// lm-multi-standings.js
// JavaScript for the multi-standings shortcode (mstw-lm-multi-standings-table.php)
//

jQuery(document).ready( function( $ ) {
		
	//
	// when the league is changed, update the current_league and the season list  
	//
	$( '#current_league' ).change( function( event ) {
		//alert( 'lm-multi-standings.js: league changed ... id= ' + event.target.id );
		//alert( 'league: ' + this.value );
		
		var e = document.getElementById( 'current_season' );
		var selected_season = e.options[e.selectedIndex].value;
		
		alert( 'selected_season: ' + selected_season );
		
		var data = {
			  'action'        : 'multi_league', //same for all
			  'real_action'   : 'change_league',
			  'season'        : selected_season,
			  'league'        : event.target.value
			  };
			  
		alert( 'sending data: ' + data );
			  
		jQuery.post( ajaxurl, data, function( response ) {
			alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'seasons') && object.seasons ) {
				jQuery("select#current_season").html( object.seasons );
			}
			
		});
	
	});
	
});