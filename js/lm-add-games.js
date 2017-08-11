// lm-add-games.js
// JavaScript for the add-games screen (mstw-lm-add-games-class.php)

jQuery(document).ready( function( $ ) {
	var currentDateCell = 0;
	var currentTimeCell = 0;
	
	//
	// Set up the date and time pickers
	//
	$('.game_date').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('.game_time').timepicker({
		'timeFormat': 'H:i',
		'step'      : '5',
		//hourText: 'Hour',
		//minuteText: 'Minute',
		amPmText: ['am', 'pm'],
		//showPeriodLabels: true,
		//timeSeparator: ':',
		showPeriod: true,
		showLeadingZero: true,
		//nowButtonText: 'Maintenant',
		//showNowButton: true,
		//closeButtonText: 'Done',
		//showCloseButton: true,
		//deselectButtonText: 'Désélectionner',
		//showDeselectButton: true
	});
	
	//
	// Need to store the cell ID when a date cell gains focus
	//
	$( '.game_date' ).focus( function( event ) {
		//alert( "focus to cell= " + event.target.id );
		currentDateCell = event.target.id;
	});
	
	//
	// When a date is changed, change all the cells below it
	//
	$( '.game_date' ).change( function( event ) {
		
		var tableSize = parseInt( $('[name=table_size]').val( ) );
		//alert( "tableSize=" + tableSize );
		
		var selectedCellValue = this.value;
		//alert( "selectedCellValue: " + selectedCellValue );
		
		var cellID = currentDateCell;
		//mstw_log_msg( event.target );
		//alert( "currentDateCell: " + cellID );
		
		// have to pick the number off the end of the id
		// var place = id.lastIndexOf( "_" );
		// place is always 11; 'game_date_' is fixed
		var newID = parseInt( cellID.substring( 10 ) );
		//alert( "newID = " + newID );

		for ( var i = newID + 1; i < tableSize; i++ ) {
			//alert( "tableSize = " + tableSize ); 
			var cssID = "#game_date_" + i;
			//alert ( "cssID = " + cssID );
			//alert( $( cssID ).val( ) );
			$( cssID ).val( selectedCellValue );
			
		}
	});
	
	//
	// Need to store the cell ID when a time cell gains focus
	//
	$( '.game_time' ).focus( function( event ) {
		//alert( "focus to cell= " + event.target.id );
		currentTimeCell = event.target.id;
	});
	
	//
	// When a time is changed, change all the cells below it
	//
	$( '.game_time' ).change( function( event ) {
		
		var tableSize = parseInt( $('[name=table_size]').val( ) );
		//alert( "tableSize=" + tableSize );
		
		var selectedCellValue = this.value;
		//alert( "selectedCellValue: " + selectedCellValue );
		
		var cellID = currentTimeCell;
		//alert( "currentTimeCell: " + cellID );
		
		// have to pick the number off the end of the id
		var newID = parseInt( cellID.substring( 10 ) );
		//alert( "newID = " + newID );

		for ( var i = newID + 1; i < tableSize; i++ ) {
			//alert( "tableSize = " + tableSize ); 
			var cssID = "#game_time_" + i;
			//alert ( "cssID = " + cssID );
			//alert( $( cssID ).val( ) );
			$( cssID ).val( selectedCellValue );
			
		}
	});
	
	//
	// When non-league checkbox is changed, change the teams list
	//
	$( '.nonleague' ).change( function( event ) {
		//alert( 'in lm-add-games.js nonleague changed ... id= ' + event.target.id );
		
		var id     = event.target.id;
		var newID  = parseInt( id.substring( 10 ) );
		var homeID = "game_home_team_" + newID;
		var awayID = "game_away_team_" + newID;
		
		var league    = $( '#main_league' ).find( ":selected").val( );
		var home_team = $( '#'+  homeID ).find( ":selected").val( );
		var away_team = $( '#' + awayID ).find( ":selected").val( ); 
		
		//alert( 'league= ' + league + ' home= ' + home_team + ' away= ' + away_team );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_nonleague',
			  'page'          : 'add_games',
			  'selected'      : league,
			  'home_selected' : home_team,
			  'away_selected' : away_team,
			  'nonleague'     : event.target.checked
			  };
		
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'home_teams') && object.home_teams ) {
				jQuery("select#" + homeID ).html( object.home_teams );
				jQuery("select#" + awayID ).html( object.home_teams );
			}
		});
		
	});	
	
		
	//
	// when the league is changed, update the current_league (WP option) 
	// and the season list  
	//
	$( '#main_league' ).change( function( event ) {
		//alert( 'in lm-add-games.js league changed ... id= ' + event.target.id );
		//alert( 'league: ' + this.value );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_league',
			  'page'    : 'add_games',
			  //'value' : 'gameleague',
			  //'awayID' : awayID,
			  'league' : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'teams') && object.teams ) {
				jQuery("select#main_season").html( object.seasons );
				jQuery("select.game_home_team").html( object.teams );
				jQuery("select.game_away_team").html( object.teams );
			}
			
		});
	
	});
	
	
	//
	// when the season is chanaged, update the current_season 
	// for the current_league (WP option)
	//
	$( '#main_season' ).change(function( event ) {
		//alert( 'in lm-add-games.js season changed ... id= ' + event.target.id );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_season',
			  'page'          : 'add_games',
			  'season'        : event.target.value,
			  'league'		  : $( '#main_league' ).find( ":selected").val( )
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
		});
		
	});
	
});