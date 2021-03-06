// lm-update-games.js
// JavaScript for the update games screen (mstw-lm-add-games-class.php)
//

jQuery(document).ready( function( $ ) {
		
	//
	// when the league is changed, update the current_league (WP option) 
	// and the season list  
	//
	$( '#main_league' ).change( function( event ) {
		//alert( 'in lm-update-games.js league changed ... id= ' + event.target.id );
		//alert( 'league: ' + this.value );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_league',
			  'page'          : 'update_games',
			  'league'        : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'seasons') && object.seasons ) {
				jQuery("select#main_season").html( object.seasons );
			}
			
		});
	
	});
	
	//
	// when the season is chanaged, update the current_season 
	// for the current_league (WP option)
	//
	$( '#main_season' ).change(function( event ) {
		//alert( 'in lm-update-games.js season changed ... id= ' + event.target.id );
		//alert( 'league: ' + this.value );
		//var league    = $( '#game_league' ).find( ":selected").val( );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_season',
			  'page'          : 'update_games',
			  'season'        : event.target.value,
			  'league'		  : $( '#main_league' ).find( ":selected").val( )
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
			//location.reload( );
			
		});
		
	});
});