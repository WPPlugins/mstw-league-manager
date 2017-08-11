// lm-manage-games-ajax.js
// JavaScript for the AJAX calls in add/edit game screen (mstw-lm-game-cpt-admin.php)
//
jQuery(document).ready( function( $ ) {
	
	//
	// Set up the date and time pickers
	//
	$('#game_date').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('#game_time').timepicker({
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
	
	$( '#game_nonleague' ).change( function( event ) {
		//alert( 'in lm-manage-games-ajax.js nonleague changed ... id= ' + event.target.id );
		
		var league    = $( '#game_league' ).find( ":selected").val( );
		var home_team = $( '#game_home_team' ).find( ":selected").val( );
		var away_team = $( '#game_away_team' ).find( ":selected").val( );
		//alert ( "league = " + league );
		//alert ( "league = " + league + " home = " + home_team + " away = " + away_team );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_nonleague',
			  'page'          : 'manage_games',
			  'selected'      : league,
			  'home_selected' : home_team,
			  'away_selected' : away_team,
			  'nonleague'     : event.target.checked
			  };
		
		//mstw_lm_ajax_object.ajax_url
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'home_teams') && object.home_teams ) {
				jQuery("select#game_home_team").html( object.home_teams );
				jQuery("select#game_away_team").html( object.home_teams );
			}
		});
		
	});	
	
	$( '#game_league' ).change( function( event ) {	
		//alert( 'league changed ... id= ' + event.target.value );
		//alert( 'league: ' + this.value );
		//alert( 'in lm-manage-games-ajax.js league changed ... id= ' + event.target.id );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_league',
			  'page'   : 'manage_games',
			  //'value' : 'gameleague',
			  //'awayID' : awayID,
			  'league' : event.target.value
			  };
		
		//mstw_lm_ajax_object.ajax_url
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( 'Error: ' + object.error );
			}
			else if ( object.hasOwnProperty( 'teams') && object.teams ) {
				jQuery("select#game_season").html( object.seasons );
				jQuery("select#game_home_team").html( object.teams );
				jQuery("select#game_away_team").html( object.teams );
			}
			
		});
		
	});	
	
});