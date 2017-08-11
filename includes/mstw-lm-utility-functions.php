<?php
/*----------------------------------------------------------------------------
 * mstw-lm-utility-functions.php
 * 	"Helper functions" for MSTW League Manager plugin (front & back ends)
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2015-16 Mark O'Donnell (mark@shoalsummitsolutions.com)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.

 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
/*----------------------------------------------------------------------------
 *	MSTW-LM-UTILITY-FUNCTIONS
 *	These functions are included in both the front and back end.
 *  1.	 mstw_lm_get_sport_options - gets the options(settings) for a sport
 *	2    mstw_lm_get_defaults - returns the general default options
 *  2.1  mstw_lm_get_premier_league_defaults - returns the default options for Premier League Soccer
 *	2.2  mstw_lm_get_nhl_defaults - returns the default options for NHL hockey
 *	2.3  mstw_lm_get_ncaa_football_defaults - returns the default options for NCAA football
 *  2.9  mstw_lm_get_slider_defaults() - returns the defaults for league schedule sliders
 *  2.9.1 mstw_lm_get_ticker_defaults() - returns the defaults for league schedule tickers
 *  2.9.2 mstw_lm_get_team_slider_defaults() - returns the defaults for team schedule sliders
 *	3.   mstw_lm_get_team_name - builds the html string for the team name
 *  3.   mstw_lm_get_team_logo - returns the team logo URL
 *  3.1  mstw_lm_build_team_logo - returns the html string for the team logo image
 *  4.   mstw_lm_get_game_location - builds the html string for the game location
 *  4.1  mstw_lm_build_location_html - build the html string for a location (heavy lifting)
 *  4.2. mstw_lm_build_google_map_url - builds a google maps URL (in support of 4.1 above)
 *	5.   mstw_lm_get_game_time_result - builds the HTML string for the game time/result 
 *  6.   mstw_lm_build_venues_control - Echo select-option control of venues to screen
 *	6.1  mstw_lm_build_venues_list - Builds an array of venues as title=>slug pairs
 *  7.   mstw_lm_build_leagues_list - Build an array of leagues as title=>slug pairs
 *  7.1  mstw_lm_build_league_select - Outputs HTML for league select option
 *  8.   mstw_lm_build_sports_list - Builds an array of sports in league taxonomy
 *  8.1  mstw_lm_build_sport_select - Outputs select/option control for sports
 *  8.2  mstw_lm_get_sports_abbrevs - Builds an array sports abbreviations
 *  9    mstw_lm_build_seasons_list - Builds an array of seasons
 *  9.1  mstw_lm_build_season_select - Outputs select/option control for seasons
 *  10   mstw_lm_get_current_sport - gets the current sport from the options DB 
 *  10.1 mstw_lm_set_current_sport - sets the current sport in the options DB 
 *  11   mstw_lm_shortcode_handler - Handles all MSTW League Manager shortcodes
 *  12   mstw_lm_build_league_schedule_table - Builds the schedule table as a string
 *  12.1 mstw_lm_build_date_bounds - builds the first_dtg & last_dtg values
 *	13   mstw_lm_get_current_league - gets the current league from the options DB
 *  13.1 mstw_lm_set_current_league - sets the current league in the options DB 
 *  14   mstw_lm_get_league_current_season - gets the current season for a league
 *  14.1 mstw_lm_set_league_current_season - sets a league's current season
 *  15   mstw_lm_get_league_sport - gets the sport for a league
 *  15.1 mstw_lm_update_league_sport - updates the sport for a league
 *	16   mstw_lm_get_league_seasons - gets_seasons for a league 
 *	16.1 mstw_lm_update_league_seasons - gets the sport for a league
 *  17   mstw_lm_make_record_slug - makes record slug from league, season and team slugs
 *	18   mstw_lm_admin_notice - Displays LM admin notices (wraps mstw_admin_notice)
 *	18.1 mstw_lm_add_admin_notice - Adds admin notices (wraps mstw_add_admin_notice)
 *  19   mstw_lm_build_team_slug_array - returns an array of team slugs for a league
 *  20   mstw_lm_get_game_media - returns the media HTML including link
 *  21   mstw_lm_help_sidebar - sets the WP help sidebar for a screen
 *  22   mstw_lm_is_edit_page - check if the current page is a new post or edit post page
 *  23   mstw_lm_get_top_level_league - returns the argument it receives 
 *		 REMAINS FOR BACKWARD COMPATIBILITY
 *  24   mstw_lm_numeral_to_ordinal - Converts number to the corresponding ordinal
 *  25   mstw_lm_find_next_game - finds the next game for a team after the specified time
 *  25.1 mstw_lm_find_last_game - Finds the first game on a schedule before the specified time
 *	26   mstw_lm_get_venue_defaults - returns the default venue options
 *  27   mstw_lm_build_game_sport - Determines the sport of a given game
 *
 *  MOVED FROM MSTW_UTILITY_FUNCTIONS DUE TO TRANSLATION/LOCALIZATION
 *  40. mstw_lm_date_loc - handles localization for the PHP date function
 *  41. mstw_lm_build_admin_edit_field - Helper function for building HTML for all admin fields
 */


//------------------------------------------------------------------------------------
// 1. mstw_lm_get_sport_options - gets the options(settings) for a sport
//
//	ARGUMENTS: 
//		$sport_slug - sport whose options to retrieve.
//
//	RETURNS:
//		Array of options (see mstw_lm_get_defaults() for the format)
//		Will return the default options of $sport_slug is not found
//
if( !function_exists( 'mstw_lm_get_sport_options' ) ) {
	function mstw_lm_get_sport_options( $sport_slug = 'general' ) {
		//duolin( " In mstw_lm_get_sport_options ... $sport_slug" );
		
		$options = get_option( "lm-sport-options_" . $sport_slug );
		
		// for resets during dev & test
		//$options = false;
		
		if ( false === $options ) {
			// options for sport not found; go to default
			switch ( $sport_slug ) {
				case 'soccer-premier-league':
					$options = shortcode_atts( mstw_lm_get_defaults( ), mstw_lm_get_premier_league_defaults( ) );
					break;
					
				case 'football-ncaa':
					$options = shortcode_atts( mstw_lm_get_defaults( ), mstw_lm_get_ncaa_football_defaults( ) );
					break;
					
				case 'ice-hockey-nhl':
					$options = shortcode_atts( mstw_lm_get_defaults( ), mstw_lm_get_nhl_defaults( ) );
					break;
										
				case 'football-nfl': //coming soon
				case 'baseball-mlb': //coming soon
				
				case 'general':
				default:
					$options = mstw_lm_get_defaults( );
					break;
				
			}	
		}
		
		return $options;
		
	} //End: mstw_lm_get_sport_options()
}

//---------------------------------------------------------------------------------
//	1.1 mstw_lm_update_option( ) - sets or updates the options/settings for sport $sport_slug
//
function mstw_lm_update_option( $sport_slug, $options ) {
	//mstw_log_msg( 'in mstw_lm_update_option ...' );
	//mstw_log_msg( 'sport= ' . $sport_slug );
	//mstw_log_msg( 'options being saved: ' );
	//mstw_log_msg( $options );
	
	return update_option( "lm-sport-options_" . $sport_slug, $options );
	
} //End: mstw_lm_update_option{}
 
//---------------------------------------------------------------------------------
//	1.1 mstw_lm_get_defaults() - returns the general default options/settings
//		
if( !function_exists( 'mstw_lm_get_defaults' ) ) {
	function mstw_lm_get_defaults( ) {
		//mstw_log_msg( 'in mstw_lm_get_defaults ...' );
		
		$defaults = array(
				// THE FIRST THREE ARE NOT REALLY SETTINGS
				// They just make the shortcodes work
				'league'	=> '',
				'season'    => '',
				'team'		=> '',
				
				
				//
				// STANDINGS SETTINGS
				//
				// This supports last_game (game) that was added late.
				'last_game' => 0,
				
				// name|mascot|name_mascot
				'standings_name_format'    => 'name',
		
				// show team logo with name
				'standings_show_logo'	   => 1,
				
				// link from team name(s)
				// none | team-schedule | team-url
				'standings_team_link'      => 'none',
				
				// link from next game field
				// none | game-page
				'standings_next_game_link' => 'none',
							
				// standings sort order
				// percent|points|rank|name|games_behind
				// don't have name or games_behind yet
				'order_by'		=> 'rank',
				
				// secondary order ... not working yet
				'order_by_secondary' => 'goals_for',
				
				// default is a basic '3-point system'
				'points_rules'	=> array( 
						'wins'   => 3,
						'losses' => 0,
						'ties'   => 1,
						'otw'    => 0,
						'otl'    => 0,
						),
				
				//				
				// SCHEDULES SETTINGS
				//
				//time controls
				'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
				'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
				'interval_days'   => '',
				'future_days'     => '',
				'past_days'       => '',
				
				// only league games appear by default
				'show_nonleague' => 0,
				
				// for team schedules only
				'show_home_away' => 0,
				
				'star_home'   => '',
				'star_league' => '',
				
				'at_symbol'   => __( 'at', 'mstw-league-manager' ),
				'vs_symbol'   => __( 'vs', 'mstw-league-manager' ),
				 
				// name|mascot|name_mascot
				'schedules_name_format' => 'name',
	
				'schedules_show_logo'   => 1,
				
				// stadium|stadium_city|stadium_city_state
				'location_format' => 'stadium',
				
				// label for "TBD game time"
				// TBD|T.B.D.|TBA|T.B.A.
				'tbd_label'		=> 'TBA',
				
				// link from team name(s)
				// none | team-schedule | team-url
				'schedule_team_link'     => 'none',
				
				// link from location (venue)
				// none | venue-url | google-map
				'schedule_location_link' => 'none',
				
				// link from game time/score
				// none | game-page
				'schedule_time_link'     => 'none',
				
				'show_byes'     => 0,
				
				// defaults for the schedule tables			
				'date_format'	=> 'D, j M',
				'time_format'	=> 'g:i a',
	
				// defaults for the schedule gallery
				'gallery_date_format'  => 'l, j F', //'Y-m-d', //
				'gallery_time_format'  => 'g:i a', //'H:i', //'g:i a',

				// defaults for the league scoreboard
				'scoreboard_date_format'  => 'l, j F', //'Y-m-d', //
				'scoreboard_time_format'  => 'g:i a', //'H:i', //
				
				'live_updates_on'    => 0,
				'live_updates_start' => 0, //not using right now
				'live_updates_end'   => 3,
				
				//
				// STANDINGS FIELDS/COLUMNS
				//
				'standings_fields' => array(
						'rank' 	        => 1,
						'team' 	        => 1,
						'games_played'  => 0,
						'wins'		    => 1,
						'losses'        => 1,
						'wins-losses'   => 0,
						'ties'	        => 1,
						'otw' 	        => 0,
						'otl' 	        => 0,
						'percent'       => 0,
						'points'        => 0,
						'games_behind'  => 0,
						'goals_for'	    => 0,
						'goals_against' => 0,
						'goals_diff'	=> 0,
						'last_5'		=> 0,
						'last_10'	    => 0,
						'streak' 	    => 0,
						'home'	        => 0,
						'away' 	        => 0,
						'division'      => 0,
						'conference'	=> 0,
						'next_game'     => 0,
						'last_game'     => 0,
						),
				
									
				'standings_labels' => array(
					   'rank' 	       => __( 'Rank', 'mstw-league-manager' ),
					   'team' 	       => __( 'Team', 'mstw-league-manager' ),
					   'games_played'  => __( 'GP', 'mstw-league-manager' ),
					   'wins'		   => __( 'Wins', 'mstw-league-manager' ),
					   'losses'		   => __( 'Losses', 'mstw-league-manager' ),
					   'wins-losses'   => __( 'Overall', 'mstw-league-manager' ),
					   'ties'	       => __( 'Ties', 'mstw-league-manager' ),
					   'otw' 	       => __( 'OTW', 'mstw-league-manager' ),
					   'otl' 	       => __( 'OTL', 'mstw-league-manager' ),
					   'percent'       => __( 'PCT', 'mstw-league-manager' ),
					   'points'        => __( 'PTS', 'mstw-league-manager' ),
					   'games_behind'  => __( 'GB', 'mstw-league-manager' ),
					   'goals_for'	   => __( 'GF', 'mstw-league-manager' ),
					   'goals_against' => __( 'GA', 'mstw-league-manager' ),
					   'goals_diff'	   => __( 'GD', 'mstw-league-manager' ),
					   'last_5'		   => __( 'L5', 'mstw-league-manager' ),
					   'last_10'	   => __( 'L10', 'mstw-league-manager' ),
					   'streak' 	   => __( 'Strk', 'mstw-league-manager' ),
					   'home'	       => __( 'Home', 'mstw-league-manager' ),
					   'away' 	       => __( 'Road', 'mstw-league-manager' ),
					   'conference'	   => __( 'Conf', 'mstw-league-manager' ),
					   'division'      => __( 'Div', 'mstw-league-manager' ),
					   'next_game'     => __( 'Next', 'mstw-league-manager' ),
					   'last_game'     => __( 'Last', 'mstw-league-manager' ),
					   ),
								 
				'standings_order' => array ( 
					   'rank',
					   'team',
					   'wins',
					   'losses',
					   'ties',
					   
					   // Not visible by default
					   'games_played',
					   'wins-losses',
					   'otw',
					   'otl',
					   'percent',
					   'points',
					   'games_behind',
					   'goals_for',
					   'goals_against',
					   'goals_diff',
					   'streak',
					   'last_5',
					   'last_10',
					   'home',
					   'away',
					   'division',
					   'conference',
					   'next_game',
					   'last_game',
					   ),
				
				//
				// SCHEDULES FIELDS/COLUMNS
				//				
				'schedule_fields' => array (
						'date'         => 1,
						'home'         => 1,
						'visitor'      => 1,
						'time'	       => 1,
						'location'     => 1,
						'media'	       => 0,
						'gallery_game' => 0,
						'opponent'     => 0,
						),
				
				'schedule_labels' => array (
					   'date' 	      => __( 'Date', 'mstw-league-manager' ),
					   'time' 	      => __( 'Time/Score', 'mstw-league-manager' ),
					   'home'	      => __( 'Home', 'mstw-league-manager' ),
					   'visitor'      => __( 'Visitor', 'mstw-league-manager' ),
					   'opponent'     => __( 'Opponent', 'mstw-league-manager' ),
					   'location'     => __( 'Location', 'mstw-league-manager' ),
					   'media'        => __( 'Media', 'mstw-league-manager' ),
					   'gallery_game' => __( 'Matchup', 'mstw-league-manager' ),
					   ),
					   
				'schedule_order' => array ( 
					   'date',
					   // home, visitor (for league schedules), 
					   // and opponent (for team tables) are mutually exclusive
					   // in the code
					   'home',
					   'visitor',
					   'opponent',
					   'time',
					   'location',
					   		
					   // Not visible by default
					   'media',
					   'gallery_game',
					   ),
					   
		);
				
		return $defaults;
	
	} //End: mstw_lm_get_defaults()
}

//---------------------------------------------------------------------------------
//	2.1 mstw_lm_get_premier_league_defaults() - returns the default options for 
//			the Premier League soccer
//		
if( !function_exists( 'mstw_lm_get_premier_league_defaults' ) ) {
	function mstw_lm_get_premier_league_defaults( ) {
		//mstw_log_msg( 'in mstw_lm_get_premier_league_defaults ...' );
		
		$defaults = array(
				//
				// STANDINGS SETTINGS
				//
				// This supports last_game (game) that was added late.
				'last_game' => 0,
				
				// name|mascot|name_mascot
				'standings_name_format'  => 'name',
		
				'standings_show_logo'	   => 1,
				
				// link from team name(s)
				// none | team-schedule | team-url
				'standings_team_link'      => 'none',
				
				// link from next game field
				// none | game-page
				'standings_next_game_link' => 'none',				
	
				// standings sort order percent|points|rank|name|games_behind
				// don't have name or games_behind yet
				'order_by'		=> 'points',
				
				// secondary order ... not working yet
				'order_by_secondary' => 'goals_diff',
				
				// default is a basic '3-point system'
				'points_rules'	=> array( 
						'wins'   => 3,
						'losses' => 0,
						'ties'   => 1,
						'otw'    => 0,
						'otl'    => 0,
						),
									
				//				
				// SCHEDULES SETTINGS
				//
				//time controls
				'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
				'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
				'interval_days'   => '',
				'future_days'     => '',
				'past_days'       => '',
				
				// only league games appear by default
				'show_nonleague' => 0,

				// for team schedules only
				'show_home_away' => 0,				
				
				'star_home'   => '',
				'star_league' => '',

				'at_symbol'   => __( 'at', 'mstw-league-manager' ),
				'vs_symbol'   => __( 'vs', 'mstw-league-manager' ),				

				// name|mascot|name_mascot
				'schedules_name_format' => 'name',
	
				'schedules_show_logo'		   => 1,
						
				// stadium|stadium_city|stadium_city_state
				'location_format' => 'stadium',
				
				// label for "TBD game time"
				// TBD|T.B.D.|TBA|T.B.A.
				'tbd_label'		=> 'TBA',
				
				// link from team name(s)
				// none | team-schedule | team-url
				'schedule_team_link'     => 'none',
				
				// link from location (venue)
				// none | venue-url | google-map
				'schedule_location_link' => 'none',
				
				// link from game time/score
				// none | game-page
				'schedule_time_link'     => 'none',
				
				'show_byes'     => 0,
				
				// defaults for the schedule tables			
				'date_format'	=> 'D, j M',
				'time_format'	=> 'H:i',
	
				// defaults for the schedule gallery
				'gallery_date_format'  => 'l, j F', //'Y-m-d', //
				'gallery_time_format'  => 'g:i a', //'H:i', //'g:i a',

				// defaults for the league scoreboard
				'scoreboard_date_format'  => 'l, j F', //'Y-m-d', //
				'scoreboard_time_format'  => 'g:i a',  //'H:i', //
				
				'live_updates_on'    => 0,
				'live_updates_start' => 0, //not using right now
				'live_updates_end'   => 3,
				
				//
				// STANDINGS FIELDS/COLUMNS
				//
				'standings_fields' => array (
						'rank' 	        => 1,
						'team' 	        => 1,
						'games_played'  => 1,
						'wins'		    => 1,
						'losses'        => 1,
						'wins-losses'   => 0,
						'ties'	        => 1,
						'otw' 	        => 0,
						'otl' 	        => 0,
						'percent'       => 0,
						'points'        => 1,
						'games_behind'  => 0,
						'goals_for'	    => 1,
						'goals_against' => 1,
						'goals_diff'	=> 1,
						'last_5'		=> 0,
						'last_10'	    => 0,
						'streak' 	    => 0,
						'home'	        => 0,
						'away' 	        => 0,
						'division'      => 0,
						'conference'	=> 0,
						'next_game'     => 0,
						'last_game'     => 0,
						),
				
									
				'standings_labels' => array (
					   'rank' 	       => __( 'Pos', 'mstw-league-manager' ),
					   'team' 	       => __( 'Club', 'mstw-league-manager' ),
					   'games_played'  => __( 'P', 'mstw-league-manager' ),
					   'wins'		   => __( 'W', 'mstw-league-manager' ),
					   'losses'		   => __( 'L', 'mstw-league-manager' ),
					   'wins-losses'   => __( 'Overall', 'mstw-league-manager' ),
					   'ties'	       => __( 'D', 'mstw-league-manager' ),
					   'otw' 	       => __( 'OTW', 'mstw-league-manager' ),
					   'otl' 	       => __( 'OTL', 'mstw-league-manager' ),
					   'percent'       => __( 'PCT', 'mstw-league-manager' ),
					   'points'        => __( 'PTS', 'mstw-league-manager' ),
					   'games_behind'  => __( 'GB', 'mstw-league-manager' ),
					   'goals_for'	   => __( 'GF', 'mstw-league-manager' ),
					   'goals_against' => __( 'GA', 'mstw-league-manager' ),
					   'goals_diff'	   => __( 'GD', 'mstw-league-manager' ),
					   'last_5'		   => __( 'L5', 'mstw-league-manager' ),
					   'last_10'	   => __( 'L10', 'mstw-league-manager' ),
					   'streak' 	   => __( 'Strk', 'mstw-league-manager' ),
					   'home'	       => __( 'Home', 'mstw-league-manager' ),
					   'away' 	       => __( 'Road', 'mstw-league-manager' ),
					   'conference'	   => __( 'Conf', 'mstw-league-manager' ),
					   'division'      => __( 'Div', 'mstw-league-manager' ),
					   'next_game'     => __( 'Next', 'mstw-league-manager' ),
					   'last_game'     => __( 'Last', 'mstw-league-manager' ),
					   ),
				
									 
				'standings_order' => array ( 
									   'rank',
									   'team',
									   'games_played',
									   'wins',
									   'ties',
									   'losses',
									   'goals_for',
									   'goals_against',
									   'goals_diff',
									   'points',
									   
									   'wins-losses',
									   'otw',
									   'otl',
									   'percent',
									   'games_behind',
									   'streak',
									   'last_5',
									   'last_10',
									   'home',
									   'away',
									   'conference',
									   'division',
									   'next_game',
									   'last_game',
									   ),
									   
				//
				// SCHEDULES FIELDS/COLUMNS
				//				
				'schedule_fields' => array (
						'date'         => 1,
						'time'	       => 1,
						'gallery_game' => 1,
						'home'         => 1,
						'visitor'      => 1,
						'location'     => 1,
						'media'	       => 1,
						'opponent'     => 0,
						),
				
				'schedule_labels' => array (
					   'date' 	      => __( 'Date', 'mstw-league-manager' ),
					   'time' 	      => __( 'Time/Score', 'mstw-league-manager' ),
					   'home'	      => __( 'Home', 'mstw-league-manager' ),
					   'visitor'      => __( 'Visitor', 'mstw-league-manager' ),
					   'opponent'     => __( 'Opponent', 'mstw-league-manager' ),
					   'location'     => __( 'Location', 'mstw-league-manager' ),
					   'media'        => __( 'Media', 'mstw-league-manager' ),
					   'gallery_game' => __( 'Matchup', 'mstw-league-manager' ),
					   ),

				'schedule_order' => array ( 
					   'date',
					   'time',
					   'gallery_game',
					   // home, visitor (for league schedules), 
					   // and opponent (for team tables) are mutually exclusive
					   // in the code
					   'home',
					   'visitor',
					   'opponent',
					   'media',
					   'location',
					   ),					   
				
		);
				
		return $defaults;
	
	} //End: mstw_lm_get_premier_league_defaults()
}

//---------------------------------------------------------------------------------
//	2.2 mstw_lm_get_nhl_defaults() - returns the default options for 
//			NHL ice hockey
//		
if( !function_exists( 'mstw_lm_get_nhl_defaults' ) ) {
	function mstw_lm_get_nhl_defaults( ) {
		//mstw_log_msg( 'in mstw_lm_get_nhl_defaults ...' );
		
		$defaults = array(
				//
				// STANDINGS SETTINGS
				//
				// This supports last_game (game) that was added late.
				'last_game' => 0,
				
				// name|mascot|name_mascot
				'standings_name_format'  => 'name',
		
				'standings_show_logo'	   => 1,
				
				// link from team name(s)
				// none | team-schedule | team-url
				'standings_team_link'      => 'none',
				
				// link from next game field
				// none | game-page
				'standings_next_game_link' => 'none',				
	
				// standings sort order percent|points|rank|name|games_behind
				// don't have name or games_behind yet
				'order_by'		=> 'points',
				
				// secondary order ... not working yet
				'order_by_secondary' => 'goals_for',
				
				// default is a basic '3-point system'
				'points_rules'	=> array( 
									'wins'   => 2,
								    'losses' => 0,
								    'ties'   => 0,
								    'otw'    => 2,
								    'otl'    => 1,
								    ),
									
				//				
				// SCHEDULES SETTINGS
				//
				//time controls
				'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
				'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
				'interval_days'   => '',
				'future_days'     => '',
				'past_days'       => '',
				
				// only league games appear by default
				'show_nonleague' => 0,
				
				'star_home'   => '',
				'star_league' => '',
				
				'at_symbol'   => __( 'at', 'mstw-league-manager' ),
				'vs_symbol'   => __( 'vs', 'mstw-league-manager' ),
				
				// for team schedules only
				'show_home_away' => 0,
				 							
				// name|mascot|name_mascot
				'schedules_name_format' => 'name',
	
				'schedules_show_logo'		   => 1,
						
				// stadium|stadium_city|stadium_city_state
				'location_format' => 'stadium_city_state',
				
				// label for "TBD game time"
				// TBD|T.B.D.|TBA|T.B.A.
				'tbd_label'		=> 'TBA',
				
				// link from team name(s)
				// none | team-schedule | team-url
				'schedule_team_link'     => 'none',
				
				// link from location (venue)
				// none | venue-url | google-map
				'schedule_location_link' => 'none',
				
				// link from game time/score
				// none | game-page
				'schedule_time_link'     => 'none',
				
				'show_byes'     => 0,
				
				// default date & time formats (in php date formats)				
				'date_format'	=> 'D, j M',
				//'date_format'	=> 'Y-m-d',
				//'date_format'  => 'l, j F',
				'time_format'	=> 'g:i a',
				//'time_format'  => 'H:i',
				
				// defaults for the schedule gallery
				'gallery_date_format'  => 'l, j F', //'Y-m-d', //
				'gallery_time_format'  => 'g:i a', //'H:i', //'g:i a',

				// defaults for the league scoreboard
				'scoreboard_date_format'  => 'l, j F', //'Y-m-d', //
				'scoreboard_time_format'  => 'g:i a', //'H:i', //
				
				//
				// STANDINGS FIELDS/COLUMNS
				//							 
				'standings_fields' => array (
						'rank' 	        => 1,
						'team' 	        => 1,
						'games_played'  => 1,
						'wins'		    => 1,
						'losses'        => 1,
						'ties'	        => 0,
						'wins-losses'   => 0,
						'otw' 	        => 0,
						'otl' 	        => 1,
						'percent'       => 0,
						'points'        => 1,
						'games_behind'  => 0,
						'goals_for'	    => 1,
						'goals_against' => 1,
						'goals_diff'	=> 1,
						'last_5'		=> 0,
						'last_10'	    => 1,
						'streak' 	    => 1,
						'home'	        => 1,
						'away' 	        => 1,
						'division'      => 1,
						'conference'	=> 0,
						'next_game'     => 0,
						'last_game'	    => 0,
						),
				
									
				'standings_labels' => array (
					   'rank' 	       => __( 'Rank', 'mstw-league-manager' ),
					   'team' 	       => __( 'Team', 'mstw-league-manager' ),
					   'games_played'  => __( 'GP', 'mstw-league-manager' ),
					   'wins'		   => __( 'W', 'mstw-league-manager' ),
					   'losses'		   => __( 'L', 'mstw-league-manager' ),
					   'wins-losses'   => __( 'Overall', 'mstw-league-manager' ),
					   'ties'	       => __( 'D', 'mstw-league-manager' ),
					   'otw' 	       => __( 'OTW', 'mstw-league-manager' ),
					   'otl' 	       => __( 'OTL', 'mstw-league-manager' ),
					   'percent'       => __( 'PCT', 'mstw-league-manager' ),
					   'points'        => __( 'P', 'mstw-league-manager' ),
					   'games_behind'  => __( 'GB', 'mstw-league-manager' ),
					   'goals_for'	   => __( 'GF', 'mstw-league-manager' ),
					   'goals_against' => __( 'GA', 'mstw-league-manager' ),
					   'goals_diff'	   => __( 'DIFF', 'mstw-league-manager' ),
					   'last_5'		   => __( 'L5', 'mstw-league-manager' ),
					   'last_10'	   => __( 'L10', 'mstw-league-manager' ),
					   'streak' 	   => __( 'Strk', 'mstw-league-manager' ),
					   'home'	       => __( 'Home', 'mstw-league-manager' ),
					   'away' 	       => __( 'Away', 'mstw-league-manager' ),
					   'conference'	   => __( 'Conf', 'mstw-league-manager' ),
					   'division'      => __( 'S/O', 'mstw-league-manager' ),
					   'next_game'     => __( 'Next', 'mstw-league-manager' ),
					   'last_game'     => __( 'Last', 'mstw-league-manager' ),
					   ),
				
									 
				'standings_order' => array ( 
									   'rank',
									   'team',
									   'games_played',
									   'wins',
									   'losses',
									   'otl',
									   'points',
									   'goals_for',
									   'goals_against',
									   'goals_diff',
									   'home',
									   'away',
									   'division',
									   'last_10',
									   'streak',
									   
									   'percent',
									   'ties',
									   'wins-losses',
									   'games_behind',
									   'last_5',
									   'conference',
									   'next_game',
									   'last_game',
									   ),
				
				//
				// SCHEDULES FIELDS/COLUMNS
				//	
				'schedule_fields' => array (
						'date'     => 1,
						'time'	   => 1,
						'home'     => 1,
						'visitor'  => 1,
						'opponent' => 0,
						'location' => 0,
						'media'	   => 1,
						'gallery_game' => 0,
						),
				
				'schedule_labels' => array (
					   'date' 	      => __( 'Date', 'mstw-league-manager' ),
					   'time' 	      => __( 'Time/Score', 'mstw-league-manager' ),
					   'home'	      => __( 'Home Team', 'mstw-league-manager' ),
					   'visitor'      => __( 'Visiting Team', 'mstw-league-manager' ),
					   'opponent'     => __( 'Opponent', 'mstw-league-manager' ),
					   'location'     => __( 'Location', 'mstw-league-manager' ),
					   'media'        => __( 'Network', 'mstw-league-manager' ),
					   'gallery_game' => __( 'Matchup', 'mstw-league-manager' ),
					   ),
				
				'schedule_order' => array ( 
					   'date',
					   // home, visitor (for league schedules), 
					   // and opponent (for team tables) are mutually exclusive
					   // in the code
					   'visitor',
					   'home',
					   'opponent',
					   'time',
					   'media',
							
					   // Not visible by default
					   'location',
					   'gallery_game',
					   ),
		);
					   
		return $defaults;
	
	} //End: mstw_lm_get_nhl_defaults()
}

//---------------------------------------------------------------------------------
//	2.3 mstw_lm_get_ncaa_football_defaults() - returns the 'concensus' defaults  
//			for NCAA football leagues
//		
if( !function_exists( 'mstw_lm_get_ncaa_football_defaults' ) ) {
	function mstw_lm_get_ncaa_football_defaults( ) {
		//mstw_log_msg( 'in mstw_lm_get_ncaa_football_defaults ...' );
		
		$defaults = array(
				//
				// STANDINGS SETTINGS
				//
				// This supports last_game (game) that was added late.
				'last_game' => 0,
				
				// name|mascot|name_mascot
				'standings_name_format'  => 'name',
				
				'standings_show_logo'	   => 1,
				
				// link from team name(s)
				// none | team-schedule | team-url
				'standings_team_link'      => 'none',
				
				// link from next game field
				// none | game-page
				'standings_next_game_link' => 'none',
	
				// standings sort order percent|points|rank|name|games_behind
				// don't have name or games_behind yet
				'order_by'		=> 'rank',
				
				// secondary order ... not working yet
				'order_by_secondary' => 'percent',
									
				//				
				// SCHEDULES SETTINGS
				//
				//time controls
				'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
				'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
				'interval_days'   => '',
				'future_days'     => '',
				'past_days'       => '',
				
				// only league games appear by default
				'show_nonleague' => 0,

				// for team schedules only
				'show_home_away' => 0,	

				'star_home'   => '',
				'star_league' => '',

				'at_symbol'   => __( 'at', 'mstw-league-manager' ),
				'vs_symbol'   => __( 'vs', 'mstw-league-manager' ),				
				
				// name|mascot|name_mascot
				'schedules_name_format' => 'name_mascot',
				'schedules_show_logo'		   => 0,
						
				// stadium|stadium_city|stadium_city_state
				'location_format' => 'stadium_city_state',
				
				// label for "TBD game time"
				// TBD|T.B.D.|TBA|T.B.A.
				'tbd_label'		=> 'TBA',
				
				// link from team name(s)
				// none | team-schedule | team-url
				'schedule_team_link'     => 'none',
				
				// link from location (venue)
				// none | venue-url | google-map
				'schedule_location_link' => 'none',
				
				// link from game time/score
				// none | game-page
				'schedule_time_link'     => 'none',
				
				'show_byes'     => 0,
				
				// defaults for the schedule tables			
				'date_format'	=> 'D, j M',
				'time_format'	=> 'g:i a',
	
				// defaults for the schedule gallery
				'gallery_date_format'  => 'l, j F', //'Y-m-d', //
				'gallery_time_format'  => 'g:i a', //'H:i', //'g:i a',

				// defaults for the league scoreboard
				'scoreboard_date_format'  => 'l, j F', //'Y-m-d', //
				'scoreboard_time_format'  => 'g:i a', //'H:i', //
				
				//
				// STANDINGS FIELDS/COLUMNS
				//
				'standings_fields' => array (
						'rank' 	        => 0,
						'team' 	        => 1,
						'games_played'  => 0,
						'wins'		    => 0,
						'losses'        => 0,
						'wins-losses'   => 1,
						'ties'	        => 0,
						'otw' 	        => 0,
						'otl' 	        => 0,
						'percent'       => 0,
						'points'        => 0,
						'games_behind'  => 0,
						'goals_for'	    => 1,
						'goals_against' => 1,
						'goals_diff'	=> 0,
						'last_5'		=> 0,
						'last_10'	    => 0,
						'streak' 	    => 1,
						'home'	        => 1,
						'away' 	        => 1,
						'division'      => 0,
						'conference'	=> 1,
						'next_game'     => 0,
						'last_game'     => 0,
						),
				
									
				'standings_labels' => array (
					   'rank' 	       => __( 'Rank', 'mstw-league-manager' ),
					   'team' 	       => __( 'Team', 'mstw-league-manager' ),
					   'games_played'  => __( 'GP', 'mstw-league-manager' ),
					   'wins'		   => __( 'W', 'mstw-league-manager' ),
					   'losses'		   => __( 'L', 'mstw-league-manager' ),
					   'wins-losses'   => __( 'Overall', 'mstw-league-manager' ),
					   'ties'	       => __( 'T', 'mstw-league-manager' ),
					   'otw' 	       => __( 'OTW', 'mstw-league-manager' ),
					   'otl' 	       => __( 'OTL', 'mstw-league-manager' ),
					   'percent'       => __( 'PCT', 'mstw-league-manager' ),
					   'points'        => __( 'PTS', 'mstw-league-manager' ),
					   'games_behind'  => __( 'GB', 'mstw-league-manager' ),
					   'goals_for'	   => __( 'PF', 'mstw-league-manager' ),
					   'goals_against' => __( 'PA', 'mstw-league-manager' ),
					   'goals_diff'	   => __( 'GD', 'mstw-league-manager' ),
					   'last_5'		   => __( 'L5', 'mstw-league-manager' ),
					   'last_10'	   => __( 'L10', 'mstw-league-manager' ),
					   'streak' 	   => __( 'Strk', 'mstw-league-manager' ),
					   'home'	       => __( 'Home', 'mstw-league-manager' ),
					   'away' 	       => __( 'Road', 'mstw-league-manager' ),
					   'conference'	   => __( 'Conf', 'mstw-league-manager' ),
					   'division'      => __( 'Div', 'mstw-league-manager' ),
					   'next_game'     => __( 'Next', 'mstw-league-manager' ),
					   'last_game'     => __( 'Last', 'mstw-league-manager' ),
					   ),
				
									 
				'standings_order' => array ( 
									   'rank',
									   'team',
									   'conference',
									   'wins-losses',
									   'goals_for',
									   'goals_against',
									   'home',
									   'away',
									   'streak',
									   
									   'wins',
									   'losses',
									   
									   'ties',
									   'goals_diff',
									   'points',
									   
									   'games_played',
									   'otw',
									   'otl',
									   'percent',
									   'games_behind',
									   
									   'last_5',
									   'last_10',
									   
									   
									   'division',
									   'next_game',
									   'last_game',
									   ),
				
		);
				
		return $defaults;
	
	} //End: mstw_lm_get_ncaa_football_defaults()
}

//---------------------------------------------------------------------------------
//	2.9 mstw_lm_get_slider_defaults() - returns the defaults for league schedule sliders
//		
if( !function_exists( 'mstw_lm_get_slider_defaults' ) ) {
	function mstw_lm_get_slider_defaults( ) {
		//mstw_log_msg( 'mstw_lm_get_slider_defaults:' );
		
		$defaults = array( 
						'type'            => '',    // all|conference|sport|school
					    'school'          => '',
					    'conference'      => '',
					    'sport'           => '',
						
						'title'           => __( 'Schedule', 'mstw-league-manager' ),
						
						'link_label'      => __( 'Link', 'mstw-league-manager' ),
						'link'            => '',
						
						'show_nonleague'  => 1,
						'games_to_show'   => 3,
						
						//slider time controls
						'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
						'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
						'interval_days'   => '',
						'future_days'     => '',
						'past_days'       => '',
						
						'league'          => '',
						'season'          => date( 'Y' ),
					   
					    'date_format'     => 'D, d M',   // Any valid PHP date format
					    'time_format'     => 'g:i a',     // Any valid PHP time format
					   
					    'show_sport'      => 1,
					   
					    'show_logos'      => 1,
					    'name_format'     => 'name',    // name|short-name|mascot|name-mascot|hide
					   
					    'location_format' => 'stadium_city_state', // stadium|stadium_city|stadium_city_state|hide
					    );
		
		return $defaults;
		
	} //End: mstw_lm_get_slider_defaults()
}

//---------------------------------------------------------------------------------
//	2.9.1 mstw_lm_get_ticker_defaults() - returns the defaults for league schedule tickers
//		
if( !function_exists( 'mstw_lm_get_ticker_defaults' ) ) {
	function mstw_lm_get_ticker_defaults( ) {
		//mstw_log_msg( 'mstw_lm_get_ticker_defaults:' );
		
		$defaults = array( 
						'type'            => '',    // all|conference|sport|school
					    'school'          => '',
					    'conference'      => '',
					    'sport'           => '',
						
						// Need a league (for games) will try to default season
						'league'          => '',
						'season'          => date( 'Y' ),
						
						// Essentially defines ticker width
						'games_to_show'   => 8,
						
						// Ticker Header args
						'show_header'     => 1,
						
						'title'           => __( 'Schedule', 'mstw-league-manager' ),
						
						'link_label'      => '',
						'link'            => '',
						
						'show_message'    => 1,
						'message'         => '',
						'msg_date_format' => 'D, d M g:i a',
						
						// Ticker time controls
						'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
						'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
						'interval_days'   => '',
						'future_days'     => '',
						'past_days'       => '',
						
						// Do we need/want this one?
						'show_nonleague'  => 1,
						
						// Game block formatting
					    'date_format'     => 'd M',   // Any valid PHP date format
					    'time_format'     => 'g:i a', // Any valid PHP time format
					   
					    'show_sport'      => 1,
					   
					    // Want to show logos, show short name, or MAYBE BOTH
					    'show_logos'      => 1,
						'name_format'     => 'short', // short | hide
					    
					    );
		
		return $defaults;
		
	} //End: mstw_lm_get_ticker_defaults()
}

//---------------------------------------------------------------------------------
//	2.9.2 mstw_lm_get_team_slider_defaults() - returns the defaults for team schedule sliders
//		
if( !function_exists( 'mstw_lm_get_team_slider_defaults' ) ) {
	function mstw_lm_get_team_slider_defaults( ) {
		//mstw_log_msg( 'mstw_lm_get_team_slider_defaults:' );
		
		$defaults = array( 
					'type'            => '',    // all|conference|sport|school
					'school'          => '',
					'conference'      => '',
					'sport'           => '',
					
					'team'            => '',
					
					//
					// Slider header
					//
					'show_header'     => 1,  //show the header = might not survive
					
					'title'           => __( 'Team Schedule', 'mstw-league-manager' ),
					
					'show_link'       => 0,
					'link_label'      => __( 'Link', 'mstw-league-manager' ),
					'link'            => '',
					
					'show_message'    => 0,
					'message_text'    => '',
					
					'show_nonleague'  => 1,
					'games_to_show'   => 3,
					
					'show_date'       => 1,
					
					//slider time controls
					'first_dtg'       => '1970:01:01 00:00:00',	// first php dtg
					'last_dtg'        => '2038:01:19 00:00:00', // last php dtg 
					'interval_days'   => '',
					'future_days'     => '',
					'past_days'       => '',
					
					'league'          => '',
					'season'          => date( 'Y' ),
				   
					'date_format'     => 'D, d M',   // Any valid PHP date format
					'time_format'     => 'g:i a',     // Any valid PHP time format
				   
					//shows game as "Sport vs" or "Sport @"
					'show_sport'      => 1,
				   
					'show_logos'      => 1,
					'name_format'     => 'name',    // name|short-name|mascot|name-mascot|hide
				   
					'location_format' => 'stadium_city_state', // stadium|stadium_city|stadium_city_state|hide
					);
		
		return $defaults;
		
	} //End: mstw_lm_get_team_slider_defaults()
}

//---------------------------------------------------------------------------------
//	3. mstw_lm_get_team_name - get team name from a game
//			Builds the necessary data and passes it to mstw_lm_get_team_name_from_team( )
//
if( !function_exists( 'mstw_lm_get_team_name' ) ) {
	function mstw_lm_get_team_name( $game, $team = 'home', $options, $name_format = 'name' ) {
		//mstw_log_msg( 'mstw_lm_get_team_name:' );
		//mstw_log_msg( "team: $team" );
		//mstw_log_msg( "name format: $name_format" );
		//mstw_log_msg( "options:" );
		//mstw_log_msg( $options );
		
		$ret_str = __( 'Name not found.', 'mstw-league-manager' );
		
		$team_field = ( 'visitor' == $team || 'away' == $team ) ? 'game_away_team' :  'game_home_team';
		
		$team_slug = get_post_meta( $game->ID, $team_field, true );
		
		if ( $team_slug ) {
			$team_obj = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
			if ( $team_obj ) {
				$team_link = ( array_key_exists( 'schedule_team_link', $options ) ) ? $options['schedule_team_link'] : 'none';
				
				$league_slug = get_post_meta( $game -> ID, 'game_league', true );
				$season_slug = get_post_meta( $game -> ID, 'game_season', true );
				
				$ret_str = mstw_lm_get_team_name_from_team( $team_obj, $name_format, $team_link, $league_slug, $season_slug );
			} else {
				mstw_log_msg( "mstw_lm_get_team_name: No team object found from $team_slug." );
			}
			
		} else {
			mstw_log_msg( "mstw_lm_get_team_name: No team slug passed." );
		}
															
		return $ret_str;
		
	} //End: mstw_lm_get_team_name()
}

//---------------------------------------------------------------------------------
//	3.1 mstw_lm_get_team_name_from_team - the team name based on the settings
//
//
if( !function_exists( 'mstw_lm_get_team_name_from_team' ) ) {
	function mstw_lm_get_team_name_from_team( 
							$team_obj, 
							$name_format = 'name',
							$name_link = 'none', 
							$league_slug = null,
							$season_slug = null ) {
								
		//mstw_log_msg( 'in mstw_lm_get_team_name_from_team ...' );
		//$name = get_the_title( $team_obj->ID );
		//mstw_log_msg( 'team name = ' . $name );
		
		$ret_str = '';
		
		if ( $team_obj ) {
			//
			// Build the name
			//
			switch ( $name_format ) {
				case 'short':
					$ret_str = get_post_meta( $team_obj->ID, 'team_short_name', true );
					if ( '' == $ret_str ) {
						// No short name so use first 3 chars of team name
						$ret_str = get_post_meta( $team_obj->ID, 'team_name', true );
						if ( '' != $ret_str ) {
							$ret_str = strtoupper( substr( $ret_str, 0, 3) );
						}
						else {
							// team has no short name and no name
							$ret_str = "UNK";
						}
					}
					break;
				
				case 'name_mascot':
					$ret_str = get_post_meta( $team_obj->ID, 'team_name', true ) . ' ' . get_post_meta( $team_obj->ID, 'team_mascot', true );
					break;
					
				case 'mascot':
					$ret_str = get_post_meta( $team_obj->ID, 'team_mascot', true );
					break;
					
				case 'name':
				default:
					$ret_str = get_post_meta( $team_obj->ID, 'team_name', true );
					break;
			}
			
			//
			// Add the link
			//
			if ( 'short' != $name_format ) {
				if ( 'team-url' == $name_link ) {
					$team_link = get_post_meta( $team_obj -> ID, 'team_link', true );
					if ( '' != $team_link ) {
						//
						//removed target='_blank' for NPF, may make an option eventually
						//based on feedback ... it's really six of one, half dozen of the other
						//
						//$ret_str = "<a href = '$team_link' target='_blank'>$ret_str</a>";
						$ret_str = "<a href = '$team_link'>$ret_str</a>";
						
					}
				} else if ( 'team-schedule' == $name_link ) {
					// need team_slug, league_slug, season_slug
					if ( null !== $season_slug and null !== $league_slug ) {
						$link = site_url() . "/league/$league_slug/?season=$season_slug&team=" . $team_obj -> post_name;
						//mstw_log_msg( "link: $link" );
						$ret_str = "<a href = '$link'>$ret_str</a>";
					}
				}
			}
			
		} else {
			// No team object. This is a mistake.
			mstw_log_msg( "mstw_lm_get_team_name_from_team: No team object passed." );
			$ret_str = __( 'Name not found.', 'mstw-league-manager' );	
			
		} //End: if ( $team_obj )
		
		return $ret_str;
		
	} //End: mstw_lm_get_team_name_from_team()
}

//---------------------------------------------------------------------------------
//	3. mstw_lm_get_team_logo - returns the team logo URL
//
//
if( !function_exists( 'mstw_lm_get_team_logo' ) ) {
	function mstw_lm_get_team_logo( $team_slug, $size = 'large' ) {
		//mstw_log_msg( 'in mstw_lm_get_team_logo ...' );
		
		// default return value
		$ret_val = '';
		
		if ( $team_slug ) {
			$team_obj = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
			if ( $team_obj ) {
				$field = ( 'large' == $size ) ? 'team_alt_logo' : 'team_logo';
				$logo_url = get_post_meta( $team_obj-> ID, $field, true );
				$ret_val = ( empty( $logo_url ) ) ? null : $logo_url;
			}
		}
		
		return $ret_val;
	
	} //End: mstw_lm_get_team_logo()
}

//---------------------------------------------------------------------------------
//	3.1 mstw_lm_build_team_logo - returns the html string for the team logo image
//		Calls mstw_lm_get_team_logo for the image file URL, then builds the HTML
//		for the <img> including any links (based on the settings)

//	ARGUMENTS: 
//		$team_slug: slug for a team (mstw_lm_team CPT) in the Teams table
//		$size: 'large' for 125x125 logo, 'small' for 41x28 logo
//
//	RETURNS:
//		Logo URL or '' if none found 
//
if( !function_exists( 'mstw_lm_build_team_logo' ) ) {
	function mstw_lm_build_team_logo( $team_slug, $size = 'large' ) {
		//mstw_log_msg( "in mstw_lm_build_team_logo ... $team_slug" );
	
		// default return value
		$ret_str = '';
		
		$logo_url = mstw_lm_get_team_logo( $team_slug, $size );
		
		if ( !empty( $logo_url ) ) {
			$ret_str = "<img src='$logo_url' />";
		}
		
		return $ret_str;
	
	} //End: mstw_lm_build_team_logo()
}

//---------------------------------------------------------------------------------
//	4. mstw_lm_get_game_location - builds the game location string
//		If the game location is set in the game, use it (normally neutral sites)
//		else if the game location is set for the home team, use it
//		else send no location found message
//
if( !function_exists( 'mstw_lm_get_game_location' ) ) {
	function mstw_lm_get_game_location( $game, $options, $format = null ) {
		//mstw_log_msg( 'mstw_lm_get_game_location:' );
		//mstw_log_msg( 'Location format (in):' . $options['location_format'] );
		//mstw_log_msg( 'Location link (in):' . $options['schedule_location_link'] );
		
		if ( 'hide' == $format or 'hide' == $options['location_format'] ) {
			return '';
		}
		
		if ( null == $format ) {
			$format = $options['location_format'];
		}
		else if ( $format != 'stadium' &&  $format != 'stadium_city' && $format != 'stadium_city_state' ) {	  
			$format = 'stadium';	
		}
		
		//mstw_log_msg( 'Location format (processed):' . $format );
		
		$location_link = ( array_key_exists( 'schedule_location_link', $options ) ) ? $options['schedule_location_link'] : 'none';

		//mstw_log_msg( 'Location link (processed):' . $location_link );

		$ret_val = __( 'No location found.', 'mstw-league-manager' );
		
		// if the game location is set in the game, use it
		$game_location = get_post_meta( $game->ID, 'game_location', true );
		
		//mstw_log_msg( "Game ID: " . $game->ID . ", Game location: " . $game_location );
		
		if ( !empty( $game_location ) and -1 != $game_location ) {
			$venue_obj = get_page_by_path( $game_location, OBJECT, 'mstw_lm_venue' );
			if( $venue_obj ) {
				//$ret_val = get_the_title( $venue_obj->ID );
				$ret_val = mstw_lm_build_location_html( $venue_obj, $format, $location_link );
			}				
		}
		else {
			// if the game location is set for the home team, use it
			$home_team_slug = get_post_meta( $game->ID, 'game_home_team', true );
			if ( $home_team_slug ) {
				$home_team_obj = get_page_by_path( $home_team_slug, OBJECT, 'mstw_lm_team' );
				if ( $home_team_obj ) {
					$home_venue_slug = get_post_meta( $home_team_obj->ID, 'team_home_venue', true );
					if ( $home_venue_slug ) {
						$home_venue_obj = get_page_by_path( $home_venue_slug, OBJECT, 'mstw_lm_venue' );
						if ( $home_venue_obj ) {
							//$ret_val = get_the_title( $home_venue_obj->ID );
							$ret_val = mstw_lm_build_location_html( $home_venue_obj, $format, $location_link );
						}
					}
				}
			}	
		}
		
		return $ret_val;
	
	} //End: mstw_lm_get_game_location()
}

//---------------------------------------------------------------------------------
//	4.1 mstw_lm_build_location_html - builds the game location string based on the
//			attribs and a venue (mstw_lm_venue CPT)
//
//		If the game location is set in the game, use it (normally neutral sites)
//		else if the game location is set for the home team, use it
//		else send no location found message
//
if( !function_exists( 'mstw_lm_build_location_html' ) ) {
	function mstw_lm_build_location_html( $venue_obj, $format = 'stadium', $link_type = 'none' ) {
		//mstw_log_msg( 'mstw_lm_build_location_html:' );
		//mstw_log_msg( "location link: $link_type" );
		//mstw_log_msg( "location format: $format" );
		//mstw_log_msg( "Location Object:" );
		//mstw_log_msg( $venue_obj );
		
		$name   = get_the_title( $venue_obj -> ID );
		$street = get_post_meta( $venue_obj->ID, 'venue_street', true );
		$city   = get_post_meta( $venue_obj->ID, 'venue_city', true );
		$state  = get_post_meta( $venue_obj->ID, 'venue_state', true );
		$zip    = get_post_meta( $venue_obj->ID, 'venue_zip', true );
		
		$ret_str = $name;

		if ( 'stadium_city' == $format || 'stadium_city_state' == $format ) {
			//$city = get_post_meta( $venue_obj->ID, 'venue_city', true );
			$ret_str .= " ($city";
			if ( 'stadium_city_state' == $format ) {
				//$state = get_post_meta( $venue_obj->ID, 'venue_state', true );
				$ret_str .= ", $state";
			}
			$ret_str .= ")";
		}
		
		//
		// Add a link if necessary
		//
		if ( 'venue-url' == $link_type ) {
			$venue_url = get_post_meta( $venue_obj -> ID, 'venue_url', true );
			if ( '' != $venue_url ) {
				$ret_str = "<a href=" . $venue_url . " target='_blank'>$ret_str</a>";
			}
			
		} else if ( 'google-map' == $link_type ) {
			$google_url = mstw_lm_build_google_map_url( $name, $street, $city, $state, $zip );
			$ret_str = "<a href='" . $google_url . "' target='_blank'>$ret_str</a>";
			
		}
		
		return $ret_str;
		
	} //End: mstw_lm_build_location_html()
}

// ------------------------------------------------------------------------------
// 4.2. mstw_lm_build_google_map_url - builds a google maps URL	
// 
if ( !function_exists( 'mstw_lm_build_google_map_url' ) ) {
	function mstw_lm_build_google_map_url( $name, $street, $city, $state, $zip ) {
		//mstw_log_msg( 'mstw_lm_build_google_map_url:' );
		//mstw_log_msg( "name= $name, street=$street, city= $city, state=$state, zip=$zip" );
		//don't want to add commas after blanks
		$name = ( $name == '' ) ? '' : "$name,";
		$street = ( $street == '' ) ? '' : "$street,";
		$city = ( $city == '' ) ? '' : "$city,";
		$state = ( $state == '' ) ? '' : "$state,";
		$zip = ( $zip == '' ) ? '' : "$zip";
		
		$google_url = "https://maps.google.com?q=$name $street $city $state $zip";
		
		//mstw_log_msg( "Returning: $google_url" );
		
		return $google_url;
	} //End: mstw_lm_build_google_map_url()
} 

//---------------------------------------------------------------------------------
//	5. mstw_lm_get_game_time_result - builds the game time/result string based on
//		the $game CPT and the plugin settings as follows:
// 		if game is final, use the score,
//		else if game is tbd, use the tbd label setting,
//		else use the game time
//	
if( !function_exists( 'mstw_lm_get_game_time_result' ) ) {
	function mstw_lm_get_game_time_result( $game, $options, $format = 'table', $sched_team_field = null ) {
		//mstw_log_msg( 'in mstw_lm_get_game_time_result ...' );
		
		if( get_post_meta( $game->ID, 'game_is_final', true ) ) {
			$home_score = get_post_meta( $game->ID, 'game_home_score', true );
			$away_score = get_post_meta( $game->ID, 'game_away_score', true );
			
			if ( 'team_table' == $format && null !== $sched_team_field ) {
				// Don't need team names, using format W 21-10
				if ( $home_score == $away_score ) {
					$ret_str = __( 'T', 'mstw-league-manager' );
					$ret_str .= " $home_score-$away_score";
					
				} else if ( 'game_home_team' == $sched_team_field ) {
					$ret_str = ( $home_score > $away_score ) ? __( 'W', 'mstw-league-manager' ) : __( 'L', 'mstw-league-manager' );
					$ret_str .= " $home_score-$away_score";
					
				} else { // schedule_team is away team
					$ret_str = ( $home_score > $away_score ) ? __( 'L', 'mstw-league-manager' ) : __( 'W', 'mstw-league-manager' );
					$ret_str .= " $away_score-$home_score";
				}
				
				
				
				
			} else {
				$home_name = mstw_lm_get_team_name( $game, 'home', $options, 'short' );
				$away_name = mstw_lm_get_team_name( $game, 'visitor', $options, 'short' );
				
				
				if( (int)$home_score >= (int)$away_score ) {
					$ret_str = "$home_name $home_score ,  $away_name $away_score";
				}
				else {
					$ret_str = "$away_name $away_score ,  $home_name $home_score";
				}
			}			
		}
		
		else if ( get_post_meta( $game->ID, 'game_is_tba', true ) ) {
			$ret_str = $options['tbd_label'];
		}
		
		else {
			switch ( $format ) {
				case 'gallery':
					$time_format = $options['gallery_time_format'];
					break;
					
				case 'scoreboard':
					$time_format = $options['scoreboard_time_format'];
					break;
					
				case 'table':
				case 'team_table':
				default:
					$time_format = $options['time_format'];
					break;
			}	
			//mstw_log_msg( "Time format: $time_format" );
			
			$game_dtg = get_post_meta( $game->ID, 'game_unix_dtg', true );
			
			$ret_str = date( $time_format, (int)$game_dtg );	
		}
		
		//
		// Add the link if necessary
		//
		
		if ( array_key_exists( 'schedule_time_link', $options ) ) {
			$href = get_site_url( ) . "/" . $game->post_name;
			if ( 'game-page' == $options['schedule_time_link'] ) {
				$ret_str = "<a href='$href'>$ret_str</a>";
			}
			
		}
		
		return $ret_str;
	
	} //End: mstw_lm_get_game_time_result()
}

//---------------------------------------------------------------------------------
// 6. mstw_lm_build_venues_control - Build (echoes to output) the 
//		select-option control of venues
//		
// ARGUMENTS:
//	$current_venue: current venue (selected in control)
//	$id: string used as the select-option control's "id" and "name"
//
// RETURNS:
//	0 if there are no teams in the database
//	1 if select-option control was built successfully
//
if ( !function_exists( 'mstw_lm_build_venues_control' ) ) {
	function mstw_lm_build_venues_control( $current_venue, $id ) {
		
		$venues = get_posts(array( 'numberposts' => -1,
					  'post_type' => 'mstw_lm_venue',
					  'orderby' => 'title',
					  'order' => 'ASC' 
					));						

		if( $venues ) {
		?>
		<select id=<?php echo $id ?> name=<?php echo $id ?> >
			<option value="-1">----</option>
			<?php
			foreach( $venues as $venue ) {
				$selected = ( $current_venue == $venue->post_name ) ? 'selected="selected"' : '';
				echo "<option value='" . $venue->post_name . "'" . $selected . ">" . get_the_title( $venue->ID ) . "</option>";
			}
			?>
		</select>
		<?php
			return 1;
		} 
		else { // No venues found
			return 0;
		}
		
	} //End: mstw_lm_build_venues_control()
}

//---------------------------------------------------------------------------------
// 6.1 mstw_lm_build_venues_list - Builds an array of venues as
//		 title=>slug pairs for use in a select-option control
//		
// ARGUMENTS:
//	None
//
// RETURNS:
//	An array of options, which will at least contain '----' => -1
//
if ( !function_exists( 'mstw_lm_build_venues_list' ) ) {
	function mstw_lm_build_venues_list( ) {
		//mstw_log_msg( 'in mstw_lm_build_venues_list()' );
		
		// return value
		$options = array( '----' => -1 );
		
		$venues = get_posts(
						array( 'numberposts' => -1,
							   'post_type' => 'mstw_lm_venue',
							   'orderby' => 'title',
							   'order' => 'ASC' 
							  )
						   );
						   
		//mstw_log_msg( '$venues:' );
		//mstw_log_msg( $venues );
		
		if ( $venues ) {
			foreach( $venues as $venue ) {
				$options[$venue->post_title] = $venue->post_name ;
			}
		}

		return $options;
		
	} //End: mstw_lm_build_venues_list()
}

// ------------------------------------------------------------------------------
// 7. mstw_lm_build_leagues_list - Builds an array of leagues in league taxonomy
//		 name=>slug pairs for use in a select-option control
//
//	ARGUMENTS:
//		$top_level_only: display only the top level terms in the league hierarchy 
//	
//	RETURNS:
//		Array of leagues in name=>slug format
//		
//
if ( !function_exists( 'mstw_lm_build_leagues_list' ) ) {
	function mstw_lm_build_leagues_list( $top_level_only = true ) {
		//mstw_log_msg( 'in mstw_lm_build_leagues_list ...' );
		
		// All leagues are now top level
		//$top_level = ( $top_level_only ) ? 0 : '';
		$top_level = true;

		$args = array( 'hide_empty'   => 0,
					   'hierarchical' => 1, //true,
					   //'orderby'      => 'name',
					   'taxonomy'     => 'mstw_lm_league',  
					 );
		
		$leagues = get_categories( $args );
		
		//mstw_log_msg( 'leagues list:' );
		//mstw_log_msg( $leagues );
			
		$options = array( );
		
		if ( $leagues ) {
			//$options['----'] = -1;
			foreach( $leagues as $league ) {
				$options[$league->name] = $league->slug ;
			}
		} 
		
		return $options;
	
	} //End: mstw_lm_build_leagues_list()
}


// ------------------------------------------------------------------------------
// 7.1 mstw_lm_build_league_select - Outputs select/option control for leagues
//
//	ARGUMENTS: 
//		$current_league: (slug for) league that's selected in control
//		$id: the id and name attributes of the control
//		$hide_empty: hide leagues with no teams (default: true/1)
//		$leagues: a list of league SLUGS to use in select control (default is ALL non-empty leagues)
//	
//	RETURNS:
//		Outputs the HTML control  ONLY SHOWS LEAGUES WITH AT LEAST ONE TEAM
//		Returns the number of leagues, or -1 if no leagues are found
//		
if ( !function_exists( 'mstw_lm_build_league_select' ) ) {
	function mstw_lm_build_league_select( $current_league = '', $id = 'main_league', $hide_empty = true, $leagues = null ) {
		//mstw_log_msg( 'in mstw_lm_build_league_select ...' );
		//mstw_log_msg( "id = $id " );
		//mstw_log_msg( '$current_league = ' . $current_league );
		//mstw_log_msg( "hide_empty = $hide_empty " );
		
		//if $leagues array is null, use all the leagues
		if ( null === $leagues ) {
			$league_count = get_terms( 'mstw_lm_league', array( 'fields' => 'count', 'hide_empty' => $hide_empty ) );
			$included_leagues = '';
			
		} else {
			$league_ids = array();
			//sort( $leagues ); doesn't really matter, puts in order of IDs, not slugs
			foreach ( $leagues as $league ) {
				$league_term = get_term_by( 'slug', $league, 'mstw_lm_league', OBJECT, 'raw' );
				if ( false !== $league_term ) {
					$league_ids[] = (int)$league_term -> term_id;
				}
			}
			$included_leagues = $league_ids;
			$league_count = count( $league_ids );
		}
		
		//mstw_log_msg( "League count= $league_count" );
		
		if ( $league_count ) {
			$retval = $league_count;
			$current_league_term = get_term_by( 'slug', $current_league, 'mstw_lm_league', OBJECT, 'raw' );
			
			//mstw_log_msg( '$current_league_term = ' );
			//mstw_log_msg( $current_league_term );
			
			$current_league_id = ( false === $current_league_term ) ? -1 : $current_league_term->term_id;
			
			//mstw_log_msg( '$current_league_id = ' . $current_league_id );

			$args = array(  
						'hide_empty'        => $hide_empty,
						'name'              => $id,
						'id'                => $id,
						'class'             => '',
						'selected'          => $current_league,
						'taxonomy'          => 'mstw_lm_league',
						'value_field'       => 'slug',
						'hierarchical'      => 0,
						'include'           => $included_leagues //array of league IDs
						);
						 
			wp_dropdown_categories( $args );
			
		}
		else { // No leagues found
			$retval = -1;
		}
		
		//mstw_log_msg( "returning $retval" );
		
		return $retval;
		
	} //End: mstw_lm_build_league_select()
}

// ------------------------------------------------------------------------------
// 8. mstw_lm_build_sports_list - Builds an array of sports in league taxonomy
//		 title=>slug pairs for use in a select-option control
//
//	ARGUMENTS: None
//	
//	RETURNS: Array of sports in slug => title pairs 
//			(used by mstw_lm_build_sports_select
//		
//
if ( !function_exists( 'mstw_lm_build_sports_list' ) ) {
	function mstw_lm_build_sports_list( ) {
		//mstw_log_msg( 'in mstw_lm_build_sports_list ...' );
		
		$options = array( //'badminton'    => __( 'Badminton', 'mstw-league-manager' ),
						  'baseball'      => __( 'Baseball', 'mstw-league-manager' ),
						  'baseball-mlb'  => __( 'Baseball-MLB', 'mstw-league-manager' ),
						  'basketball'    => __( 'Basketball', 'mstw-league-manager' ),
						  'basketball-boys'  => __( 'Boys Basketball', 'mstw-league-manager' ),
						  'basketball-girls' => __( 'Girls Basketball', 'mstw-league-manager' ),
						  'bowling'       => __( 'Bowling', 'mstw-league-manager' ),
						  'cheer'         => __( 'Competitive Cheer', 'mstw-league-manager' ),
						  'x-country'     => __( 'Cross Country', 'mstw-league-manager' ),
						  'x-country-boys'     => __( 'Boys Cross Country', 'mstw-league-manager' ),
						  'x-country-girls'     => __( 'Girls Cross Country', 'mstw-league-manager' ),
						  'fencing'       => __( 'Fencing', 'mstw-league-manager' ),
						  'field-hockey'  => __( 'Field Hockey', 'mstw-league-manager' ),
						  'football'      => __( 'Football', 'mstw-league-manager' ),
						  'football-ncaa' => __( 'Football-NCAA', 'mstw-league-manager' ),
						  'football-nfl'  => __( 'Football-NFL', 'mstw-league-manager' ),
						  'golf'          => __( 'Golf', 'mstw-league-manager' ),
						  'golf-boys'     => __( 'Boys Golf', 'mstw-league-manager' ),
						  'golf-girls'    => __( 'Girls Golf', 'mstw-league-manager' ),
						  'gymnastics'    => __( 'Gymnastics', 'mstw-league-manager' ),
						  'ice-hockey'    => __( 'Ice Hockey', 'mstw-league-manager' ),
						  'ice-hockey-nhl' => __( 'Ice Hockey-NHL', 'mstw-league-manager' ),
						  'lacrosse'      => __( 'Lacrosse', 'mstw-league-manager' ),
						  'lacrosse-boys' => __( 'Lacrosse', 'mstw-league-manager' ),
						  'lacrosse-girls' => __( 'Lacrosse', 'mstw-league-manager' ),
						  'rowing'        => __( 'Rowing', 'mstw-league-manager' ),
						  'rugby'         => __( 'Rugby', 'mstw-league-manager' ),
						  'ski-snow'      => __( 'Skiing & Snowboarding', 'mstw-league-manager' ),
						  'soccer'        => __( 'Soccer', 'mstw-league-manager' ),
						  'soccer-boys'   => __( 'Boys Soccer', 'mstw-league-manager' ), 'soccer-girls'   => __( 'Girls Soccer', 'mstw-league-manager' ),
						  'soccer-premier-league' => __( 'Soccer-Premier League', 'mstw-league-manager' ),
						  'softball'      => __( 'Softball', 'mstw-league-manager' ),
						  'swim-dive'     => __( 'Swimming & Diving', 'mstw-league-manager' ),
						  'swim-boys'     => __( 'Boys Swimming', 'mstw-league-manager' ),
						  'swim-girls'     => __( 'Girls Swimming', 'mstw-league-manager' ),
						  'tennis'        => __( 'Tennis', 'mstw-league-manager' ),
						  'tennis-boys'   => __( 'Boys Tennis', 'mstw-league-manager' ),
						  'tennis-girls'  => __( 'Girls Tennis', 'mstw-league-manager' ),
						  'track-field'   => __( 'Track & Field', 'mstw-league-manager' ),
						  'track-boys'   => __( 'Boys Track', 'mstw-league-manager' ),
						  'track-girls'   => __( 'Girls Track', 'mstw-league-manager' ),
						  'volleyball'    => __( 'Volleyball', 'mstw-league-manager' ),
						  'boys-volleyball' => __( 'Boys Volleyball', 'mstw-league-manager' ),
						  'girls-volleyball' => __( 'Girls Volleyball', 'mstw-league-manager' ),
						  'water-polo'    => __( 'Water Polo', 'mstw-league-manager' ),
						  'wrestling'     => __( 'Wrestling', 'mstw-league-manager' ),
						  'wrestling-boys'     => __( 'Boys Wrestling', 'mstw-league-manager' ),
						  'wrestling-girls'     => __( 'Girls Wrestling', 'mstw-league-manager' ),
						);
		
		// add a filter
		
		return apply_filters( 'mstw_lm_sports_list', $options );
	
	} //End: mstw_lm_build_sports_list( )
}

// ------------------------------------------------------------------------------
// 8.1 mstw_lm_build_sport_select - Outputs select/option control for sports
//
//	ARGUMENTS: 
//		$current_sport: (slug for) sport that's selected in control
//		$id:            the id and name attributes of the control
//	
//	RETURNS:
//		Outputs the HTML control and returns the number of sports found
//		Otherwise, returns -1 if no sports are found
//		
if ( !function_exists( 'mstw_lm_build_sport_select' ) ) {
	function mstw_lm_build_sport_select( $current_sport = '', $id = '' ) {
		//mstw_log_msg( 'in mstw_lm_build_sport_select ...' );	
		
		// get sports as a slug=> name array
		$sports = mstw_lm_build_sports_list( );
		
		// Return -1 if no sports are found
		$retval = -1; 
		
		if ( $sports ) {
		?>
		<select name=<?php echo $id ?> id=<?php echo $id ?> >
		<?php foreach ( $sports as $slug => $name ) { 
			$selected = selected( $slug, $current_sport, false );
			?>
			<option value=<?php echo "$slug $selected" ?>><?php echo $name ?> </option>
			
			<?php 
			$retval++;
		} ?>
		</select>
		
		<?php
		}
		
		return $retval;
		
	} //End: mstw_lm_build_sport_select()
}

// ------------------------------------------------------------------------------
// 8.2 mstw_lm_get_sports_abbrevs - Builds an array sports abbreviations
//
//	ARGUMENTS: None
//
//	Abbreviations are limited to 8 characters
//	
//	RETURNS: Array of sports in slug => abbreviation pairs 	
//
if ( !function_exists( 'mstw_lm_get_sports_abbrevs' ) ) {
	function mstw_lm_get_sports_abbrevs( ) {
		//mstw_log_msg( 'in mstw_lm_get_sports_abbrevs ...' );
		
		$options = array( //'badminton'    => __( 'Badminton', 'mstw-league-manager' ),
						  'baseball'      => __( 'Baseball', 'mstw-league-manager' ),
						  'baseball-mlb'  => __( 'MLB', 'mstw-league-manager' ),
						  'basketball'    => __( 'Bsk-ball', 'mstw-league-manager' ),
						  'basketball-boys'  => __( 'B-Bball', 'mstw-league-manager' ),
						  'basketball-girls' => __( 'G-Bball', 'mstw-league-manager' ),
						  'bowling'       => __( 'Bowling', 'mstw-league-manager' ),
						  'cheer'         => __( 'Cheer', 'mstw-league-manager' ),
						  'x-country'     => __( 'XCntry', 'mstw-league-manager' ),
						  'x-country-boys'  => __( 'B X-Cnty', 'mstw-league-manager' ),
						  'x-country-girls' => __( 'G X-Cnty', 'mstw-league-manager' ),
						  'fencing'       => __( 'Fencing', 'mstw-league-manager' ),
						  'field-hockey'  => __( 'F-Hockey', 'mstw-league-manager' ),
						  'football'      => __( 'Football', 'mstw-league-manager' ),
						  'football-ncaa' => __( 'NCAA FB', 'mstw-league-manager' ),
						  'football-nfl'  => __( 'NFL', 'mstw-league-manager' ),
						  'golf'          => __( 'Golf', 'mstw-league-manager' ),
						  'golf-boys'     => __( 'B Golf', 'mstw-league-manager' ),
						  'golf-girls'    => __( 'G Golf', 'mstw-league-manager' ),
						  'gymnastics'    => __( 'Gym', 'mstw-league-manager' ),
						  'ice-hockey'    => __( 'I-Hockey', 'mstw-league-manager' ),
						  'ice-hockey-nhl' => __( 'NHL', 'mstw-league-manager' ),
						  'lacrosse'      => __( 'Lax', 'mstw-league-manager' ),
						  'lacrosse-boys'  => __( 'B Lax', 'mstw-league-manager' ),
						  'lacrosse-girls' => __( 'G Lax', 'mstw-league-manager' ),
						  'rowing'        => __( 'Crew', 'mstw-league-manager' ),
						  'rugby'         => __( 'Rugby', 'mstw-league-manager' ),
						  'ski-snow'      => __( 'Skiing & Snowboarding', 'mstw-league-manager' ),
						  'soccer'        => __( 'Soccer', 'mstw-league-manager' ),
						  'soccer-boys'   => __( 'B Soc', 'mstw-league-manager' ),
						  'soccer-girls'  => __( 'G Soc', 'mstw-league-manager' ),
						  'soccer-premier-league' => __( 'Soccer-Premier League', 'mstw-league-manager' ),
						  'softball'      => __( 'Softball', 'mstw-league-manager' ),
						  'swim-dive'     => __( 'Swimming', 'mstw-league-manager' ),
						  'swim-dive-boys'  => __( 'B Swim', 'mstw-league-manager' ),
						  'swim-dive-girls' => __( 'G Swim', 'mstw-league-manager' ),
						  'tennis'        => __( 'Tennis', 'mstw-league-manager' ),
						  'tennis-boys'   => __( 'G Tennis', 'mstw-league-manager' ),
						  'tennis-girls'  => __( 'B Tennis', 'mstw-league-manager' ),
						  'track-field'   => __( 'Track', 'mstw-league-manager' ),
						  'track-field-boys'  => __( 'B Track', 'mstw-league-manager' ),
						  'track-field-girls' => __( 'G Track', 'mstw-league-manager' ),
						  'volleyball'    => __( 'V-ball', 'mstw-league-manager' ),
						  'volleyball-boys'  => __( 'B V-ball', 'mstw-league-manager' ),
						  'volleyball-girls' => __( 'G V-ball', 'mstw-league-manager' ),
						  'water-polo'    => __( 'H2O Polo', 'mstw-league-manager' ),
						  'wrestling'     => __( 'Wrest', 'mstw-league-manager' ),
						  'wrestling-boys'  => __( 'B Wrest', 'mstw-league-manager' ),
						  'wrestling-girls' => __( 'G Wrest', 'mstw-league-manager' ),
						);
		
		// add a filter
		
		return apply_filters( 'mstw_lm_sports_abbrevs', $options );
	
	} //End: mstw_lm_get_sports_abbrevs( )
}

// ------------------------------------------------------------------------------
// 9. mstw_lm_build_seasons_list - Builds an array of seasons for the specified
//		 league as title=>slug pairs for use in a select-option control
//
//	ARGUMENTS: 
//		$league_slug - the league (slug) for which to build the seasons
//	
//	RETURNS: Array of seaons in slug => title pairs 
//			(used by mstw_lm_build_seasons_select)
//		
//
if ( !function_exists( 'mstw_lm_build_seasons_list' ) ) {
	function mstw_lm_build_seasons_list( $league_slug ) {
		//mstw_log_msg( "in mstw_lm_build_seasons_list with league= $league_slug" );
		
		$options = array( );
		
		// Get the seasons from the league slug
		$seasons = mstw_lm_get_league_seasons( $league_slug );
		//mstw_log_msg( "seasons for league: $league_slug" );
		//mstw_log_msg( $seasons );
		
		if ( $seasons ) {
			foreach( $seasons as $slug => $name ) {
				$options[$slug] = $name ;
			}
		} 
		//mstw_log_msg( ' returning $options:' );
		//mstw_log_msg( $options );
		
		return $options;
	
	} //End: mstw_lm_build_seasons_list( )
}

// ------------------------------------------------------------------------------
// 9.1 mstw_lm_build_season_select - Outputs select/option control for seasons
//
//	ARGUMENTS: 
//		$current_league: (slug for) league for which to build seasons control
//		$current_season: (slug for) selected season in control
//		$id:             the id and name attributes of the control
//	
//	RETURNS:
//		Outputs the HTML control and returns the number of seasons found
//		Otherwise, returns -1 if no seaons are found
//		
if ( !function_exists( 'mstw_lm_build_season_select' ) ) {
	function mstw_lm_build_season_select( $current_league = '', $current_season = '', $id = '' ) {
		//mstw_log_msg( 'in mstw_lm_build_season_select ...' );
		
		//mstw_log_msg( "current league= $current_league, current season= $current_season, id= $id" );
		
		// Return -1 if no seasons are found
		$retval = -1; 
		
		$seasons = mstw_lm_build_seasons_list( $current_league );
		
		if ( $seasons ) {
			?>
			<select name=<?php echo $id ?> id=<?php echo $id ?> >
			<?php 
				foreach ( $seasons as $slug => $name ) { 
					$selected = selected( $slug, $current_season, false );
					?>
					<option value=<?php echo "$slug $selected" ?>><?php echo $name ?> </option>
					
					<?php 
					$retval++;
				} ?>
			</select>
			
			<?php
		}
		else {
			//printf( __( 'No seasons found for league: %s', 'mstw-league-manager' ), $current_league );
			mstw_log_msg( "mstw_lm_build_season_select found no seasons for league: $current_league" );
		}
		
		return $retval;
		
	} //End: mstw_lm_build_season_select()
}

//------------------------------------------------------------------------------------
// 10. mstw_lm_get_current_sport - gets the current sport from the options DB 
//
//	ARGUMENTS: 
//		None
//
//	RETURNS:
//		The current sport (slug) or the empty string if a current sport has not been set
//
if( !function_exists( 'mstw_lm_get_current_sport' ) ) {
	function mstw_lm_get_current_sport( ) {
		//mstw_log_msg( " In mstw_lm_get_current_sport ... " );
		
		return get_option( 'lm-current-sport', '' );
		
	} //End: mstw_lm_get_current_sport()
}

//------------------------------------------------------------------------------------
// 10.1 mstw_lm_set_current_sport - sets the current sport in the options DB 
//
//	ARGUMENTS: 
//		Current sport slug
//
//	RETURNS:
//		True of current sport is updated, false if not or if update fails
//
if( !function_exists( 'mstw_lm_set_current_sport' ) ) {
	function mstw_lm_set_current_sport( $sport_slug = '' ) {
		//mstw_log_msg( " In mstw_lm_set_current_sport ... " );
		
		return update_option( 'lm-current-sport', $sport_slug );
		
	} //End: mstw_lm_set_current_sport()
}

//--------------------------------------------------------------------------------------
// 11. mstw_lm_shortcode_handler - Handles all MSTW League Manager shortcodes
//
if ( !function_exists( 'mstw_lm_shortcode_handler' ) ) {
	function mstw_lm_shortcode_handler( $atts, $content = null, $shortcode ) {
	    //mstw_log_msg( "mstw_lm_shortcode_handler: shortcode= $shortcode " );
		//mstw_log_msg( 'shortcode: ' . $shortcode );
		//mstw_log_msg( 'plugin slug 1: ' . plugin_dir_path( __FILE__ ) );
		
		// MUST have a league to proceed
		if( !is_array( $atts ) or !array_key_exists( 'league', $atts ) ) {	
			return '<h3 class="mstw-lm-user-msg">' . __( 'No LEAGUE specified in shortcode.', 'mstw-league-manager' ) . '</h3>';
		}
		$league_slugs = explode( ',', $atts['league'] );
		
		$sport_slug = mstw_lm_get_league_sport( $league_slugs[0] );
		
		//mstw_log_msg( 'league = ' . $league_slugs[0] );
		//mstw_log_msg( '$sport = ' . $sport_slug );
		
		// get the options set in the admin settings screen
		$args = mstw_lm_get_sport_options( $sport_slug );
		
		//mstw_log_msg( "Sport atts" );
		//mstw_log_msg( $args );
		
		// and merge them with the defaults
		// 1st arg overrides values in second arg
		// $args = wp_parse_args( $options, mstw_lm_get_sport_options( $sport_slug ) );
			
		// then merge the parameters passed to the shortcode 								
		$attribs = shortcode_atts( $args, $atts );
		
		//
		//  this is a hack since first_dtg and last_dtg were added late
		//
		if ( array_key_exists( 'first_dtg', $atts ) ) {
			$attribs['first_dtg'] = $atts['first_dtg'];
		}
		if ( array_key_exists( 'last_dtg', $atts ) ) {
			$attribs['last_dtg'] = $atts['last_dtg'];
		}
		if ( array_key_exists( 'interval_days', $atts ) ) {
			$attribs['interval_days'] = $atts['interval_days'];
		}		
		
		// mstw_lm_build_schedule_table() is shared with the schedule table
		// and the schedule gallery so it's in mstw-lm-utility-functions.php
		//
		switch ( $shortcode ) {
			case 'mstw_league_schedule_table':
				$ret_str = mstw_lm_build_league_schedule_table( $attribs, 'date_column' );
				break;
				
			case 'mstw_league_schedule_gallery':
				$ret_str = mstw_lm_build_league_schedule_table( $attribs, 'no_date_column' );
				break;
				
			case 'mstw_league_scoreboard':
				//$ret_str = mstw_lm_build_league_scoreboard( $attribs );
				$ret_str = '[mstw_league_scoreboard] is not currently supported.';
				break;
				
			case 'mstw_league_standings':
				// this is a hack since last_game was added late
				if ( array_key_exists( 'last_game', $atts ) ) {
					$attribs['last_game'] = $atts['last_game'];
					$show_fields = $attribs['standings_fields'];
					//if ( !array_key_exists( 'last_game', $show_fields ) ) {
						$show_fields['last_game'] = 1;
						$attribs['standings_fields'] = $show_fields;
					//}
					
					$labels = $attribs['standings_labels'];
					if ( !array_key_exists( 'last_game', $labels ) ) {
						$labels['last_game'] = __( 'Last', 'mstw-league-manager' );
						$attribs['standings_labels'] = $labels;
					}
					
					$fields = $attribs['standings_order'];
					if ( !in_array( 'last_game', $fields ) ) {	
						$fields[] = 'last_game';
						$attribs['standings_order'] = $fields;
					}
				}
				$ret_str = mstw_lm_build_league_standings( $attribs );
				break;
				
			case 'mstw_team_schedule':
			    // this is a hack since star_home and star_league were added late
				if ( array_key_exists( 'star_home', $atts ) ) {
					$attribs['star_home'] = $atts['star_home'];
				}
				
				if ( array_key_exists( 'star_league', $atts ) ) {
					$attribs['star_league'] = $atts['star_league'];
				}
				
				$ret_str = mstw_lm_build_team_schedule( $attribs );
				break;
				
			case 'mstw_multi_league_standings':
				$ret_str = mstw_lm_multi_league_standings( $attribs );
				break; 
			default:
				break;
		}
		
		return $ret_str;
		
	} //End: mstw_lm_shortcode_handler()	
}

//--------------------------------------------------------------------------------------
// 12. mstw_lm_build_league_schedule_table - Builds the schedule table as a string 
//			(to replace the [shortcode] in a page or post)
// 	Called by mstw_lm_schedule_table_shortcode_handler( ) & mstw_lm_schedule_gallery_handler()
// 	
// ARGUMENTS:
// 	$atts - the display settings and shortcode arguments, properly combined by
//	mstw_lm_schedule_table_shortcode_handler()
// RETURNS
//	HTML for schedule table as a string
//
if( !function_exists( 'mstw_lm_build_league_schedule_table' ) ) {
	function mstw_lm_build_league_schedule_table( $atts, $format ) {
		//mstw_log_msg( " In mstw_lm_build_league_schedule_table ... " );
		//mstw_log_msg( '$atts:' );
		//mstw_log_msg( $atts );
		
		// This is not currently used, only one league slug is used 
		// But it allows for multiple leagues in the future
		$league_slugs = explode( ',', $atts['league'] );
		//mstw_log_msg( 'league slug = ' . $league_slugs[0] );
		
		//
		// CHECK THERE ARE A LEAGUE IN THE ATTRIBUTES, HAVE TO HAVE A LEAGUE
		//
		if ( !empty( $league_slugs[0] ) ) {
			//CSS tag will be the FIRST league slug specified
			$css_tag = '_' . $league_slugs[0];
		}
		else {
			//Have to have at least one league slug
			return "<h3 class='mstw-ls-user-msg'>" . __( 'No league specified.', 'mstw-league-manager' ) . '</h3>';
		} 
		
		//		
		// We have a league. Check that we have a season set or default to 
		// first season for league
		//
		if ( array_key_exists( 'season', $atts ) && !empty( $atts['season'] ) ) {
			$season_slug = $atts['season'];
			//mstw_log_msg( 'season argument = ' . $season_slug );
			
			// This is a convenience for the user so he can use "2015"
			// instead of "season-2015"
			//if ( 0 !== strpos( $season_slug, "season-" ) ) {
			//	$season_slug = "season-" . $season_slug;
			//}
		}
		else {
			$season_slug = '';
		}
		
		$league_season_slugs = mstw_lm_get_league_seasons( $league_slugs[0] );
		//mstw_log_msg( "league " . $league_slugs[0] . " has seasons:" );
		//mstw_log_msg( $league_season_slugs );
		
		if( $league_season_slugs ) {
			//search for $season_slug in $league_season_slugs
			if( !array_key_exists( $season_slug, $league_season_slugs ) ) {
				// use the first season found; somewhat random but whatever
				reset( $league_season_slugs );
				$season_slug = key( $league_season_slugs );
			}
		}
		else {
			mstw_log_msg( "No seasons for for league " . $league_slugs[0] . "... should not happen." );
			return "<h3 class='mstw-ls-user-msg'>" . sprintf( __( 'No seasons found for league: %s', 'mstw-league-manager' ), $league_slugs[0] ) . '</h3>';
		}	
		
		// Right now, this ain't going to change; 
		// it was for last_dtg == now in MSTW Schedules & Scoreboards
		$sort_order = 'ASC';
		
		$dtgs = mstw_lm_build_date_bounds( $atts );
		
		$first_dtg = $dtgs['first'];
		$last_dtg  = $dtgs['last'];
		
		// Allow non-league games to appear on schedule
		$show_nonleague = array_key_exists( 'show_nonleague', $atts ) ? $atts['show_nonleague'] : 0;
		
		if ( $show_nonleague ) {
			//mstw_log_msg( "Show nonleague games" );
			$meta_query = array(
							//array(
							//'relation' => 'AND',
							array( 'key' => 'game_season',
								   'value'   => $season_slug,
								   'compare' => '=',
							     ),
							array(
								   'key'     => 'game_unix_dtg',
								   'value'   => array( $first_dtg,
													   $last_dtg ),
								   'type'    => 'NUMERIC',
								   'compare' => 'BETWEEN'
								 )
							//)								 
						  );
												
		}
		else {
			//mstw_log_msg( "Show league games ONLY" );
			$meta_query = array ( //'relation' => 'AND',
								  
								  array( 'key'     => 'game_season',
										 'value'   => $season_slug,
										 'compare' => '=',
										),
								  array( 'key' => 'game_nonleague',
									     'value'   => 0,
									     'compare' => '=',
									    ),
								  array( 'key'     => 'game_unix_dtg',
										 'value'   => array( $first_dtg,
													         $last_dtg ),
										 'type'    => 'NUMERIC',
										 'compare' => 'BETWEEN'
										)		
								);
		}
		
		//mstw_log_msg( "right before get posts: " );
		//mstw_log_msg( "first_dtg= $first_dtg" );
		//mstw_log_msg( "first_dtg= " . date( 'Y-m-d h:i', $first_dtg ) );
		//mstw_log_msg( "last_dtg= $last_dtg" );
		//mstw_log_msg( "last_dtg= " . date( 'Y-m-d h:i', $last_dtg ) );
		//mstw_log_msg( "........");
		
		// Get the games posts
		$games = get_posts( 
					array( 'numberposts' => -1, //$atts['games_to_show'],
						   'post_type'   => 'mstw_lm_game',
						   //'mstw_lm_league' => $league_slugs[0],
						   'tax_query'   => array(
											array( 
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slugs,
											'operator' => 'IN'
											),
						                    ),
						   'meta_query'  => $meta_query,					

							'orderby' => 'meta_value_num',
							//'orderby' => 'meta_value', 
							'meta_key' => 'game_unix_dtg',
							
							'order' => $sort_order 
							) 
					);						
		
		if ( $games ) {
			//mstw_log_msg( "Games found: " . count( $games ) );
			if ( 'date_column' == $format ) {	
				return mstw_lm_build_schedule_table( $games, $atts, $league_slugs[0] );	
			}
			else { // Default to 'gallery' format
				return mstw_lm_build_schedule_gallery( $games, $atts, $league_slugs[0] );
			}
		}
		else { // No games were found
			$output =  '<h3>' . sprintf( __( 'No games found for league %s in season %s', 'mstw-league-manager' ), $league_slugs[0], $season_slug ) . '.</h3>';	
		}
		
		return $output;

	} //End function mstw_lm_build_league_schedule_table()
}

//------------------------------------------------------------------------------------
// 12.1 mstw_lm_build_date_bounds - builds the first_dtg & last_dtg values
//
//	ARGUMENTS: 
//		$atts: the display settings and shortcode arguments
//
//	RETURNS:
//		$dtgs: array of timestamps $dtgs['first'], $dtg['last']
//
if( !function_exists( 'mstw_lm_build_date_bounds' ) ) {
	function mstw_lm_build_date_bounds( $atts ) {
		//mstw_log_msg( "mstw_lm_build_date_bounds:" );

		// HANDLE THE TIME OPTIONS
		//  Have to have a first_dtg, create one if necessary
		//
		//	*if first_dtg is 'now', use the current time
		//		* if there's any problem with first_dtg, set it to 0
		//		
		//	*if interval_days is set, get posts 
		//		between first_dtg - interval_days and first_dtg + interval_days
		//
		//  *elseif last_dtg is set
		//		get posts between first and last
		//		if there's a problem with last_dtg, set it to PHP_INT_MAX
		//
		
		// These keys should be there. This is a patch for older installs
		if ( !array_key_exists( 'first_dtg', $atts ) ) {
			$atts['first_dtg'] = '';
		}
		
		if ( !array_key_exists( 'interval_days', $atts ) ) {
			$atts['interval_days'] = '';
		}
		
		if ( !array_key_exists( 'last_dtg', $atts ) ) {
			$atts['last_dtg'] = '';
		}
		
		// Set the first_dtg 
		if ( 'now' == $atts['first_dtg'] ) {
			$first_dtg = current_time( 'timestamp' );
			
		} else {
			$first_dtg = strtotime( $atts['first_dtg'] );
			
		} 
		
		if ( false === $first_dtg || $first_dtg < 0 ) {
			$first_dtg = 0;
		} 
		
		//mstw_log_msg( '...........' );
		//mstw_log_msg( "first_dtg= $first_dtg" );
		//mstw_log_msg( "first_dtg= " . date( 'Y-m-d h:i', $first_dtg ) );
		
		$base_dtg  = $first_dtg;  // may need to layout intervals
		
		// if it exists, use interval_days to create last_dtg
		if ( $interval_days = $atts['interval_days'] ) {
			if ( is_numeric( $interval_days ) ) {
				$first_dtg = $base_dtg - $interval_days * DAY_IN_SECONDS;
				$last_dtg  = $base_dtg + $interval_days * DAY_IN_SECONDS;
				$first_dtg = ( $first_dtg < 1 ) ? 1 : $first_dtg;
				$last_dtg  = ( $last_dtg > PHP_INT_MAX ) ? PHP_INT_MAX : $last_dtg;
				
			} else {
				$last_dtg = PHP_INT_MAX;
				//mstw_log_msg( "mstw_lm_build_league_schedule_table: interval_days is not numeric." );
				
			}

			//mstw_log_msg( "interval_days= $interval_days");
			//mstw_log_msg( "first_dtg= $first_dtg" );
			//mstw_log_msg( "first_dtg= " . date( 'Y-m-d h:i', $first_dtg ) );
			//mstw_log_msg( "last_dtg= $last_dtg" );
			//mstw_log_msg( "last_dtg= " . date( 'Y-m-d h:i', $last_dtg ) );
			
		} else {
			// use the last_dtg ...
			// if the first char of $last_dtg == '+', add the number of days
			if ( '' != $atts['last_dtg'] && 0 === strpos( $atts['last_dtg'], "+" )) {
				$days = substr( $atts['last_dtg'], 1 );
				if ( is_numeric( $days ) ) {
					$last_dtg = $first_dtg + $days*DAY_IN_SECONDS;
					
				} else {
					$last_dtg = PHP_INT_MAX;
				}
				//mstw_log_msg( "+days= $days" );
				
			} else {
				$last_dtg = strtotime( $atts['last_dtg'] );
				// strtotime returned -1 on error before version 5.1.0
				if ( false === $last_dtg || -1 == $last_dtg || $last_dtg <= $first_dtg ) {
					$last_dtg = PHP_INT_MAX;
				}
			}
			
			//mstw_log_msg( "last_dtg= $last_dtg" );
			//mstw_log_msg( "last_dtg= " . date( 'Y-m-d h:i', $last_dtg ) );
		
		} //End: if ( array_key_exists( 'interval_days', $atts ) ) {
			
		$dtgs = array( 'first' => $first_dtg,
					   'last'  => $last_dtg
					 );
					
		return $dtgs;
		
	} //End: mstw_lm_build_date_bounds( )
}

//------------------------------------------------------------------------------------
// 13. mstw_lm_get_current_league - gets the current league from the options DB 
//
//	ARGUMENTS: 
//		None
//
//	RETURNS:
//		The current league (slug) or '' if a current league has not been set
//
if( !function_exists( 'mstw_lm_get_current_league' ) ) {
	function mstw_lm_get_current_league( ) {
		//mstw_log_msg( " In mstw_lm_get_current_league ... " );
		
		//for testing only
		//mstw_lm_set_current_league( '' );
		
		$current_league = get_option( 'lm-current-league', '' );
		
		// We should get a current league, but in case we don't we'll take the
		// first one find, set it, and set a season for it too!
		
		if ( '' == $current_league or -1 == $current_league ) {
			$league_objs = get_terms( 'mstw_lm_league', array( 'hide_empty' => false, 'number' => '1' ) );
			if ( $league_objs ) {
				//mstw_log_msg( 'leagues: ' );
				//mstw_log_msg( $league_objs );
				$current_league = $league_objs[0]->slug;
				//mstw_log_msg( "setting current league to $current_league" );
				mstw_lm_set_current_league( $current_league );
				// if the current season is not set, get_current_league_season 
				// will try to set it for the current_league
				//
				mstw_lm_get_league_current_season( $current_league );
			}
		}
		
		//mstw_log_msg( "returning current league: $current_league " );
		
		return $current_league;
		
	} //End: mstw_lm_get_current_league()
}

//------------------------------------------------------------------------------------
// 13.1 mstw_lm_set_current_league - sets the current league in the options DB 
//
//	ARGUMENTS: 
//		Current league slug
//
//	RETURNS:
//		True of current league is updated, false if not or if update fails
//
if( !function_exists( 'mstw_lm_set_current_league' ) ) {
	function mstw_lm_set_current_league( $league_slug = '' ) {
		//mstw_log_msg( " In mstw_lm_set_current_league ... " );
		mstw_lm_get_league_current_season( $league_slug );
		
		return update_option( 'lm-current-league', $league_slug );
		
	} //End: mstw_lm_set_current_league()
}

//------------------------------------------------------------------------------------
// 14. mstw_lm_get_league_current_season - gets the current season for a league
//		from the options DB 
//
//	ARGUMENTS: 
//		$league_slug: the league(slug) for which to get the current season
//
//	RETURNS:
//		The current season (slug), or '' if $league_slug has no seasons
//
if( !function_exists( 'mstw_lm_get_league_current_season' ) ) {
	function mstw_lm_get_league_current_season( $league_slug = '' ) {
		//mstw_log_msg( "divider" );
		//mstw_log_msg( "In mstw_lm_get_league_current_season ..." );
		
		//$league_slug = ( '' == $league_slug ) ? mstw_lm_get_current_league( ) : $league_slug ;
		
		if ( '' == $league_slug ) {
			//mstw_log_msg( "mstw_lm_get_league_current_season: Received empty league slug" );
			$current_season = '';
		}
		else {
			$option = 'lm-league-current-season_' . $league_slug;
			
			$current_season = get_option( $option, '' );
			
			// We should get a current season, but if we don't, try to take the
			// first one we can find & set it as the league's current season
			if ( '' == $current_season ) {
				$seasons = mstw_lm_get_league_seasons( $league_slug );
				
				if ( $seasons ) {
					reset( $seasons );
					$current_season = key( $seasons );
					mstw_lm_set_league_current_season( $league_slug, $current_season );
					//mstw_log_msg( "mstw_lm_get_league_current_season: Set current season for $league_slug to $current_season" );
				}
				else {
					//mstw_log_msg( "mstw_lm_get_league_current_season: No season found for $league_slug" );
					$current_season = '';
				}	
			}
		}
		//mstw_log_msg( "returning $current_season" );
		//mstw_log_msg( "divider" );
		
		return $current_season;
		
		
	} //End: mstw_lm_get_league_current_season()
}

//------------------------------------------------------------------------------------
// 14.1 mstw_lm_set_league_current_season - sets a league's current season 
//		in the options DB 
//
//	ARGUMENTS: 
//		$league_slug: the league(slug) whose current season to update
//		$season_slug: the current season(slug) for the league
//
//	RETURNS:
//		true if league's current season is updated, false if not (or if update fails)
//
if( !function_exists( 'mstw_lm_set_league_current_season' ) ) {
	function mstw_lm_set_league_current_season( $league_slug = '', $season_slug = '' ) {
		//mstw_log_msg( "divider" );
		//mstw_log_msg( " In mstw_lm_set_league_current_season with league= $league_slug, season= $season_slug" );
		//mstw_log_msg( "divider" );
		//mstw_log_msg( "league_slug: $league_slug  season_slug: $season_slug" );
		
		if( '' == $league_slug ) {
			return false;
		}
		
		$option = 'lm-league-current-season_' . $league_slug;
		
		return update_option( $option, $season_slug );
		
	} //End: mstw_lm_set_league_current_season()
}

//------------------------------------------------------------------------------------
// 15. mstw_lm_get_league_sport - gets the sport for a league 
//
//	ARGUMENTS: 
//		$league_slug: The league's slug
//		$get_name: If true, return the league's name. Else return the league's slug
//
//	RETURNS:
//		If $get_name = false ( the default ), returns the league's sport slug
//		If $get_name = true, returns the league's sport name
//		Returns  -1 if no sport is set for the league
//
if( !function_exists( 'mstw_lm_get_league_sport' ) ) {
	function mstw_lm_get_league_sport( $league_slug, $get_name = false ) {
		//mstw_log_msg( " In mstw_lm_get_league_sport ... " );
		
		$option = 'lm-league-sport_' . $league_slug;
		
		$retval = $sport_slug = get_option( $option, -1 );
		
		if ( $get_name ) {
			if ( -1 == $sport_slug ) {
				$retval = "No sport";
			}
			else {
				$sports_array = mstw_lm_build_sports_list( );
			
				if ( $sports_array && array_key_exists( $sport_slug, $sports_array ) ) {
					$retval = $sports_array[$sport_slug];	
				}
				else {
					$retval = $sports_array[-1];
				}
			}
			
		}
		
		return $retval;
		
	} //End: mstw_lm_get_league_sport( )
}

//------------------------------------------------------------------------------------
// 15.1 mstw_lm_update_league_sport - updates the sport for a league 
//
//	ARGUMENTS: 
//		$league_slug: League to update
//		$sport_slug:  Sport with which to update league
//
//	RETURNS:
//		True if league's sport is updated, false if not (or if update fails)
//
if( !function_exists( 'mstw_lm_update_league_sport' ) ) {
	function mstw_lm_update_league_sport( $league_slug, $sport_slug = '' ) {
		//mstw_log_msg( " In mstw_lm_update_league_sport ... " );
		//mstw_log_msg( "league_slug: $league_slug  sport_slug: $sport_slug" );
		
		if ( '' == $league_slug ) {
			return false;
		}
		
		$option = 'lm-league-sport_' . $league_slug;
		
		//mstw_log_msg( "mstw_lm_update_league_sport: updating $option with sport: $sport_slug" );
		
		return update_option( $option, $sport_slug );
		
	} //End: mstw_lm_update_league_sport()
}

//------------------------------------------------------------------------------------
// 16 mstw_lm_get_league_seasons - returns the seasons for a league 
//
//	ARGUMENTS: 
//		$league_slug: League to update
//
//	RETURNS:
//		Array of seasons for league, or false if bad arguments
//
if( !function_exists( 'mstw_lm_get_league_seasons' ) ) {
	function mstw_lm_get_league_seasons( $league_slug ) {
		//mstw_log_msg( " In mstw_lm_get_league_seasons ... " );
		
		$option  = "lm-league-seasons_$league_slug";
		
		return get_option( $option, '' );
		
	} //End: mstw_lm_get_league_seasons()
}

//------------------------------------------------------------------------------------
// 16.1 mstw_lm_update_league_seasons - updates the seasons for a league 
//
//	ARGUMENTS: 
//		$league_slug: The league's slug
//		$season_slug: Slug for season being added OR array of seasons to add
//		$season_name: Name of season being added
//
//	RETURNS:
//		True if league's seasons are updated, false if not (or if update fails )
//		
if( !function_exists( 'mstw_lm_update_league_seasons' ) ) {
	function mstw_lm_update_league_seasons( $league_slug = '', $season_slug = '', $season_name = '' ) {
		//mstw_log_msg( " In mstw_lm_update_league_seasons: league= $league_slug, season= $season_slug" );
		
		if ( '' !== $league_slug and '' !== $season_slug  ) {
			
			$option  = "lm-league-seasons_$league_slug";
			$seasons = get_option( $option );
			
			if ( false === $seasons ) {
				// Create the first season
				$seasons = array( );	
			}
			
			if ( !is_array( $season_slug ) ) {
				$season_name = ( '' == $season_name ) ? $season_slug : $season_name ;
				$season = array( $season_slug => $season_name );
				$new_seasons = $seasons + $season;
				//mstw_log_msg( " mstw_lm_update_league_seasons: updating option to:" );
				//mstw_log_msg( $new_seasons );
				//return update_option( $option, $new_seasons );
			}
			else {
				$new_seasons = array_merge( $seasons, $season_slug );
				//return update_option( $option, $new_seasons );
			}
			
			//mstw_log_msg( "mstw_lm_update_league_seasons: Updating seasons option with: " );
			//mstw_log_msg( $new_seasons );
			
			return update_option( $option, $new_seasons );
			
		}
		else {
			mstw_log_msg( "mstw_lm_update_league_seasons: Bad args, $league_slug, $season_slug, $season_name" );
			return false;
		}
		
	} //End: mstw_lm_update_league_seasons( )
}

//------------------------------------------------------------------------------------
// 17 mstw_lm_make_record_slug - makes record slug from league, season and team slugs 
//
//	ARGUMENTS: 
//		$league_slug, $season_slug, $team_slug
//
//	RETURNS:
//		Slug for record
//
if( !function_exists( 'mstw_lm_make_record_slug' ) ) {
	function mstw_lm_make_record_slug( $league_slug, $season_slug, $team_slug ) {
		//mstw_log_msg( " In mstw_lm_make_record_slug ... " );
		
		return "$league_slug_$season_slug_$team_slug";
		
	} //End: mstw_lm_make_record_slug()
}

//----------------------------------------------------------------
// 18 mstw_lm_admin_notice - Displays LM admin notices (wraps mstw_admin_notice)
//		Callback for admin_notices action 
//
//	ARGUMENTS: 	None
//
//	RETURNS:	None. Displays all messages in the 'mstw_lm_admin_messages' transient
//
if ( !function_exists ( 'mstw_lm_admin_notice' ) ) {
	function mstw_lm_admin_notice( ) {
		//mstw_log_msg( 'in mstw_lm_admin_notice ... ' );
		mstw_admin_notice( 'mstw_lm_admin_messages' );
		
	} //End: mstw_lm_admin_notice( )
}


//----------------------------------------------------------------
// 18.1 mstw_lm_add_admin_notice - Adds admin notices (wraps mstw_add_admin_notice) 
//			for display on admin_notices hook
//
//	ARGUMENTS: 	$type - type of notice [updated|error|update-nag|warning]
//				$notice - notice text
//
//	RETURNS:	None. Stores notice and type in transient for later 
//				display on admin_notices hook
//
if ( !function_exists ( 'mstw_lm_add_admin_notice' ) ) {
	function mstw_lm_add_admin_notice( $type = 'updated', $notice = '' ) {
		//default type to 'updated'
		
		mstw_add_admin_notice( 'mstw_lm_admin_messages', $type, $notice );
		
	} //End: function mstw_lm_add_admin_notice( )
}


//-----------------------------------------------------------------------------
// 19 mstw_lm_build_team_slug_array - returns an array of team slugs for a league
//
if( !function_exists( 'mstw_lm_build_team_slug_array' ) ) {
	function mstw_lm_build_team_slug_array( $league_slug = "all_leagues" ) {
		//mstw_log_msg( "mstw_lm_build_team_slug_array:" );
		
		if ( "all_leagues" == $league_slug ) {
			// Get all teams in the DB (for non-league games)
			$teams = get_posts( array( 'numberposts'    => -1,
									   'post_status'    => 'published',
									   'post_type'      => 'mstw_lm_team',					
									  ) 
							  );
		} else {
			// Get the specified league's teams
			$teams = get_posts( array( 'numberposts'    => -1,
									   'post_status'    => 'published',
									   'post_type'      => 'mstw_lm_team',
									   'mstw_lm_league' => $league_slug, 
									  ) 
							  );
			
		}
								 
		//mstw_log_msg( 'Found ' . count( $teams ) . ' teams in ' . $league_slug );
		
		$retval = array( );
		
		foreach ( $teams as $team ) {
			$retval[] = $team->post_name;	
		}
		
		//mstw_log_msg( "Returning array for $league_slug:" );
		//mstw_log_msg( $retval );
		
		return $retval;
		
	} //End: mstw_lm_build_team_slug_array()
}

//---------------------------------------------------------------------------------
// 20 mstw_lm_get_game_media - returns the media HTML including link
//
if( !function_exists( 'mstw_lm_get_game_media' ) ) {
	function mstw_lm_get_game_media( $game, $options ) {
		//mstw_log_msg( 'in mstw_lm_get_game_media ...' );	

		$media_label = get_post_meta( $game->ID, 'game_media', true );
		//mstw_log_msg( "media label: $media_label" );
		$media_link = get_post_meta( $game->ID, 'game_media_link', true );
		//mstw_log_msg( "media link: $media_link" );
		
		if ( empty( $media_label ) ) {
			$ret_val = __( 'No media found.', 'mstw-league-manager' );		
		}
		else if ( !empty( $media_link ) ) {
			// we have a label and a link
			$ret_val = "<a href='$media_link' target='_blank'>$media_label</a>";	
		}
		else {
			$ret_val = $media_label;
		}
		
		return $ret_val;
	
	} //End: mstw_lm_get_game_media()
}

//--------------------------------------------------------------------------------------
// 21 mstw_lm_help_sidebar - sets the WP help sidebar for a screen
//
//	ARGUMENTS: 
//		$screen - WP screen object for which to set the help sidebar
//
//	RETURNS:
//		Builds sidebar HTML and sets it for $screen
//
if ( !function_exists( 'mstw_lm_help_sidebar' ) ) {
	function mstw_lm_help_sidebar( $screen ) {
		//mstw_log_msg( "in mstw_lm_help_sidebar ..." );
		
		$sidebar = '<p><strong>' . __( 'For more information:', 'mstw-league-manager' ) . '</strong></p>' .
		'<p><a href="http://shoalsummitsolutions.com/category/users-manuals/lm-plugin/" target="_blank">' . __( 'MSTW League Manager Users Manual', 'mstw-league-manager' ) . '</a></p>' .
		'<p><a href="http://dev.shoalsummitsolutions.com/league-manager/" target="_blank">' . __( 'See MSTW League Manager in Action', 'mstw-league-manager' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/plugins/mstw-league-manager/" target="_blank">' . __( 'MSTW League Manager on WordPress.org', 'mstw-league-manager' ) . '</a></p>';
		
		$screen->set_help_sidebar( $sidebar );
		
	} //End: mstw_lm_help_sidebar( )
}

//--------------------------------------------------------------------------------------
// 22 mstw_lm_is_edit_page - check if the current page is a new post or edit post page
//
//	ARGUMENTS: 
//		$new_edit - page type to check for "new"|"edit"|null
//					null, the default, will check for either
//	GLOBAL:
//		$pagenow
//
//	RETURNS:
//		true or false based on value of $new_edit
//
function mstw_lm_is_edit_page( $new_edit = null ) {
    global $pagenow;
    //make sure we are on the backend
    if ( !is_admin( ) ) return false;

    if ( "edit" == $new_edit ) { //check for edit post page
		return in_array( $pagenow, array( 'post.php' ) );
	}    
    elseif ( "new" == $new_edit ) { //check for new post page
        return in_array( $pagenow, array( 'post-new.php' ) );
	}
    else { //check for either edit or new
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
	}
}

//------------------------------------------------------------------------------------
// 23 mstw_lm_get_top_level_league - all leagues are now top level, so this just returns
//		the argument it received. REMAINS FOR BACKWARD COMPATIBILITY
//
//	ARGUMENTS: 
//		$league_slug: league for which to find patriarch (branch of the taxonomy tree)
//
//	RETURNS:
//		$league_slug
//
if( !function_exists( 'mstw_lm_get_top_level_league' ) ) {
	function mstw_lm_get_top_level_league( $league_slug = '' ) {
		//mstw_log_msg( " In mstw_lm_get_top_level_league ... " );
		//mstw_log_msg( '$league_slug = ' . $league_slug );
		
		// All leagues are now top level
		return $league_slug;
		
	} //End: mstw_lm_get_top_level_league()
}

//--------------------------------------------------------------------------------------
// 24 mstw_lm_numeral_to_ordinal - Converts number to the corresponding ordinal 
//
//	ARGUMENTS: 
//		$nbr - numeral to convert (should be a positive integer)
//
//	RETURNS:
//		Corresponding ordinal as a string
//
if ( !function_exists ( 'mstw_lm_numeral_to_ordinal' ) ) {
	function mstw_lm_numeral_to_ordinal( $nbr ) {
		// no negative numbers allowed
		if ( $nbr < 0 ) {
			$nbr = -$nbr;
		}
		
		// integers oanly
		$nbr = floor( $nbr );
		
		if( 10 < $nbr && $nbr < 20 ) {
			$ret = $nbr . __( 'th', 'mstw-league-manager' );
		}
		else {
			$mod_nbr = $nbr % 10;
			
			switch ( $nbr % 10 ) {
				case 1:
					$ret = $nbr . __( 'st', 'mstw-league-manager' );
					break;
				case 2:
					$ret = $nbr . __( 'nd', 'mstw-league-manager' );
					break;
				case 3:
					$ret = $nbr . __( 'rd', 'mstw-league-manager' );
					break;
				default: // 0,4,5,6,7,8,9
					$ret = $nbr . __( 'th', 'mstw-league-manager' );
					break;				
			}
		}
		
		return $ret;
		
	} //End: mstw_lm_numeral_to_ordinal()
}


//--------------------------------------------------------------------------------------
// 25 mstw_lm_find_next_game - Finds the next game on a schedule after the specified time
//
//	ARGUMENTS: 
//		$slugs: array containing league, season, and team slugs, in that order
//		$timestamp: UNIX timestamp; usually now or current/last game
//		
//	RETURNS:
//		$next_game: as an lm_game CPT object or null if none found
//		
//
if ( !function_exists ( 'mstw_lm_find_next_game' ) ) {
	function mstw_lm_find_next_game( $slugs, $timestamp = null ) {
		
		//mstw_log_msg( "mstw_lm_find_next_game" );
		//mstw_log_msg( "$slugs[0] / $slugs[1] / $slugs[2]" );
		
		$games = get_posts( 
				array( 'numberposts'    => -1,
					   'post_type'      => 'mstw_lm_game',
					   /*'mstw_lm_league' => $league_slug,*/
					   /*'tax_query' => array( 
										array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $slugs[0],
											),
										),*/
					   'meta_query' => array(
										'relation' => 'AND',
										array(
											'key'     => 'game_season',
											'value'   => $slugs[1],
											'compare' => '=',
											),
										array(
										   'relation' => 'OR',
											array(
											   'key'     => 'game_home_team',
											   'value'   => $slugs[2],
											   //'compare' => 'IN',
											   ),
											array(
											   'key'     => 'game_away_team',
											   'value'   => $slugs[2],
											   //'type'    => 'NUMERIC',
											   //'compare' => 'BETWEEN'
											   )
											),
										),
						'orderby'  => 'meta_value', 
						'meta_key' => 'game_unix_dtg',
						'orderby'  => 'meta_value_num',
						'order'    => 'ASC' 
						) 
				);
		
		/*
		if ( 'cal-bears' == $slugs[2] ) { 
			mstw_log_msg( "$slugs[0] / $slugs[1] / $slugs[2]" );
			mstw_log_msg( "Cal Games found: " );
			mstw_log_msg( $games );
		}
		*/
								  
		// Default timestamp to now
		$current_dtg = ( null == $timestamp ) ? current_time( 'timestamp' ) : $timestamp ;
		
		/*
		if ( 'cal-bears' == $slugs[2] ) {
			mstw_log_msg( "Current DTG: " . date( "Y-m-d h:m" ) );
		}
		*/
		
		$next_game = null;
		
		// loop thru the game posts to find the first game in the future
		foreach( $games as $game ) {
			// Find first game time after the current time, and (just to be sure) has no result
			
			/*
			if ( 'cal-bears' == $slugs[2] ) {
				mstw_log_msg( "Game DTG: " . date( "Y-m-d h:m", get_post_meta( $game->ID, 'game_unix_dtg', true ) ) );
			}
			*/
					
			if ( get_post_meta( $game->ID, 'game_unix_dtg', true ) > $current_dtg ) { //&& 
					//get_post_meta( $game->ID, 'game_result', true ) == '' ) {
				//if ( get_post_meta( $game->ID, 'game_is_home_game', true ) ) {
					// Ding, ding, ding, we have a winner!
					// Grab the data needed and stop looping through the games
					$next_game = $game;
					//$game_time_tba = get_post_meta( $game->ID, 'game_time_tba', true );
					// and stop looping through the games
					break; 
				//}
			}
		}
		
		return $next_game;
		
	} //End: mstw_lm_find_next_game()
}

//--------------------------------------------------------------------------------------
// 25.1 mstw_lm_find_last_game - Finds the first game on a schedule before the specified time
//
//	ARGUMENTS: 
//		$slugs: array containing league, season, and team slugs, in that order
//		$timestamp: UNIX timestamp; usually now or 'current' game
//		$final_only: finds only games that are marked final
//		
//	RETURNS:
//		$next_game: as an lm_game CPT object or null if none found
//		
//
if ( !function_exists ( 'mstw_lm_find_last_game' ) ) {
	function mstw_lm_find_last_game( $slugs, $timestamp = null, $final_only = 0 ) {
		//mstw_log_msg( "mstw_lm_find_last_game" );
		//mstw_log_msg( "$slugs[0] / $slugs[1] / $slugs[2]" );
		
		// Default timestamp to now
		$timestamp = ( null == $timestamp ) ? current_time( 'timestamp' ) : $timestamp ;
		
		$games = get_posts( 
				array( 'numberposts'    => 1,
					   'post_type'      => 'mstw_lm_game',
					   /*'mstw_lm_league' => $league_slug,*/
					   /*
					   'tax_query' => array( 
										array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $slugs[0],
											),
										),
						*/
					   'meta_query' => array(
										'relation' => 'AND',
										array(
											'key'     => 'game_season',
											'value'   => $slugs[1],
											'compare' => '=',
											),
										array(
										   'relation' => 'OR',
											array(
											   'key'     => 'game_home_team',
											   'value'   => $slugs[2],
											   //'compare' => 'IN',
											   ),
											array(
											   'key'     => 'game_away_team',
											   'value'   => $slugs[2],
											   //'type'    => 'NUMERIC',
											   //'compare' => 'BETWEEN'
											   )
											),
										array( 'key' => 'game_unix_dtg',
										       'value' => $timestamp,
											   'compare' => '<', 
											  )
										),
						'orderby'  => 'meta_value', 
						'meta_key' => 'game_unix_dtg',
						'orderby'  => 'meta_value_num',
						'order'    => 'DESC' 
						) 
				);

		//mstw_log_msg( "Games found: " . count( $games ) );
		//mstw_log_msg( $games );
		
		if ( $games ) {
			$last_game_dtg = get_post_meta( $games[0] -> ID, 'game_unix_dtg', true );
			//mstw_log_msg( "Last Game DTG: " . date( 'Y-m-d H:i', $last_game_dtg ) );
			return $games[0];
		} else {
			return null;
		}
								  
		
		/* NOT USED */
		
		$next_game = null;
		
		// loop thru the game posts to find the first game in the future
		foreach( $games as $game ) {
			// Find first game time after the current time, and (just to be sure) has no result
					
			if ( get_post_meta( $game->ID, 'game_unix_dtg', true ) > $timestamp ) { //&& 
					//get_post_meta( $game->ID, 'game_result', true ) == '' ) {
				//if ( get_post_meta( $game->ID, 'game_is_home_game', true ) ) {
					// Ding, ding, ding, we have a winner!
					// Grab the data needed and stop looping through the games
					$next_game = $game;
					//$game_time_tba = get_post_meta( $game->ID, 'game_time_tba', true );
					// and stop looping through the games
					break; 
				//}
			}
		}
		
		return $next_game;
		
	} //End: mstw_lm_find_last_game()
}

//---------------------------------------------------------------------------------
// 26. mstw_lm_get_venue_defaults - returns the default venue options 
//
if ( !function_exists( 'mstw_lm_get_venue_defaults' ) ) {
	function mstw_lm_get_venue_defaults( ) {
		$defaults = array(	'instructions'		=> __( 'Click on map to view driving directions.', 'mstw-league-manager' ),
							'show_instructions'	=> 0,
							'venue_label'		=> __( 'Venue', 'mstw-league-manager' ),
							'show_venue_link'	=> 0,
							'show_address'		=> 1,
							'address_label'		=> __( 'Address', 'mstw-league-manager' ),
							'show_map'			=> 1,
							'custom_map_url'	=> '',
							'map_label'			=> __( 'Map (Click for larger view.)', 'mstw-league-manager' ),
							'marker_color'		=> 'blue',
							'map_icon_width'	=> 250,
							'map_icon_height'	=> 75,
							'venue_group'		=> null,
							);		
		return $defaults;
	} //End: mstw_lm_get_venue_defaults( )
}

//--------------------------------------------------------------------------------
// 27. mstw_lm_build_game_sport - Determines the sport of a given game
// 	ARGUMENTS:
// 		$game - a game OBJECT

// 	RETURNS
//		HTML for the sport string "Baseball" or "Girl's Lacrosse"
//
if ( !function_exists ( 'mstw_lm_build_game_sport' ) ) {
	function mstw_lm_build_game_sport( $game ) {
		//mstw_log_msg( "in mstw_lm_build_game_sport( ) ... ");
		//return variable
		$sport = null;
		
		//from the game, find the game's league
		//from the league, find the sport
		
		$sched_slug = get_post_meta( $game->ID, 'game_sched_id', true );
		$sched_obj = get_page_by_path( $sched_slug, OBJECT, 'mstw_lm_schedule' );
		if ( null !== $sched_obj ) {
			//from the schedule, find the game's (home) team
			$team_slug = get_post_meta( $sched_obj->ID, 'schedule_team', true );
			$team_obj = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
			if ( null !== $team_obj ) {
				//from the (home) team, find the sport
				//mstw_log_msg( 'team = ' . get_the_title( $team_obj ) );
				$sport_slug = get_post_meta( $team_obj->ID, 'team_sport', true );
				//mstw_log_msg( 'sport for ' . get_the_title( $team_obj ) . ' ' . $sport_slug );
				$sport_obj = get_page_by_path( $sport_slug, OBJECT, 'mstw_lm_sport' );
				if ( null != $sport_obj ) {
					$sport = get_the_title( $sport_obj );
					//mstw_log_msg( 'Found a sport = ' . $sport );
				}
				if ( null === $sport_slug or '' == $sport_slug ) {
					// didn't find a sport, so try using the team name
					//mstw_log_msg( "did not find a sport ... default to " . get_the_title( $team_obj ) ); 
					$sport = get_the_title( $team_obj );
				}
			}
		} //End: if ( null !== $sched_obj )
		
		$vs_or_at = __( 'vs', 'mstw-league-manager' );
		if ( !get_post_meta($game->ID, 'game_is_home_game', true ) ) {
			$vs_or_at = __( '@', 'mstw-league-manager' );
		}
		
		$sport .= " $vs_or_at";
					
		return $sport;
		
	} //End: mstw_lm_build_game_sport( )
}

//-------------------------------------------------------------------------------
// 40. mstw_lm_date_loc - handles localization for the PHP date function
//
// This is a modification of the PHP date function for use
// in WP internationalization/localization. If you have created a translation file
// for the plugin and set the WP_LANG variable in the wp-config.php file, 
// this function will work (at least for most formats). 
//
// If you don't understand WordPress internationalization, you would
// be well advised to read the codex before jumping in to this pool.
//
if ( !function_exists( 'mstw_lm_date_loc' ) ) {
	function mstw_lm_date_loc($format, $timestamp = null) {
		$param_D = array( '', 
							__( 'Mon', 'mstw-league-manager' ), 
							__( 'Tue', 'mstw-league-manager' ), 
							__( 'Wed', 'mstw-league-manager' ), 
							__( 'Thr', 'mstw-league-manager' ), 
							__( 'Fri', 'mstw-league-manager' ), 
							__( 'Sat', 'mstw-league-manager' ), 
							__( 'Sun', 'mstw-league-manager' ) );
		
		$param_l = array( '', 
							__( 'Monday', 'mstw-league-manager' ), 
							__( 'Tuesday', 'mstw-league-manager' ), 
							__( 'Wednesday', 'mstw-league-manager' ), 
							__( 'Thursday', 'mstw-league-manager' ), 
							__( 'Friday', 'mstw-league-manager' ), 
							__( 'Saturday', 'mstw-league-manager' ), 
							__( 'Sunday', 'mstw-league-manager' ) );
							
		$param_F = array( '', 
							__( 'January', 'mstw-league-manager' ), 
							__( 'February', 'mstw-league-manager' ), 
							__( 'March', 'mstw-league-manager' ), 
							__( 'April', 'mstw-league-manager' ), 
							__( 'May', 'mstw-league-manager' ), 
							__( 'June', 'mstw-league-manager' ),
							__( 'July', 'mstw-league-manager' ),
							__( 'August', 'mstw-league-manager' ),
							__( 'September', 'mstw-league-manager' ),
							__( 'October', 'mstw-league-manager' ),
							__( 'November', 'mstw-league-manager' ),
							__( 'December', 'mstw-league-manager' ) );
							
		$param_M = array( '', 
							__( 'Jan', 'mstw-league-manager' ), 
							__( 'Feb', 'mstw-league-manager' ), 
							__( 'Mar', 'mstw-league-manager' ), 
							__( 'Apr', 'mstw-league-manager' ), 
							__( 'May', 'mstw-league-manager' ), 
							__( 'Jun', 'mstw-league-manager' ),
							__( 'Jul', 'mstw-league-manager' ),
							__( 'Aug', 'mstw-league-manager' ),
							__( 'Sep', 'mstw-league-manager' ),
							__( 'Oct', 'mstw-league-manager' ),
							__( 'Nov', 'mstw-league-manager' ),
							__( 'Dec', 'mstw-league-manager' ) );
		
		$return = '';
		
		if ( is_null( $timestamp ) ) { 
			$timestamp = current_time( 'timestamp' ); 
		}
		
		for( $i = 0, $len = strlen( $format ); $i < $len; $i++ ) {
			switch($format[$i]) {
				case '\\' : // double.slashes
					$i++;
					$return .= isset($format[$i]) ? $format[$i] : '';
					break;
				case 'D' :
					$return .= $param_D[date('N', $timestamp)];
					break;
				case 'l' :
					$return .= $param_l[date('N', $timestamp)];
					break;
				case 'F' :
					$return .= $param_F[date('n', $timestamp)];
					break;
				case 'M' :
					$return .= $param_M[date('n', $timestamp)];
					break;
				default :
					$return .= date($format[$i], $timestamp);
					break;
			}
		}
		
		return $return;
		
	}
}

//-------------------------------------------------------------------------------
// 41. mstw_lm_build_admin_edit_field - Helper function for building HTML for all admin 
//								form fields ... ECHOES OUTPUT
//
//	ARGUMENTS: $args = array(
//		'type'       => $type,
//		'id'         => $id,
//		'desc'       => $desc,
//		'curr_value' => current field value,
//		'options'    => array of options in key=>value pairs
//			e.g., array( __( '08:00 (24hr)', 'mstw-league-manager' ) => 'H:i', ... )
//		'label_for'  => $id,
//		'class'      => $class,
//		'name'		 => $name,
//	);
//		
//
if( !function_exists( 'mstw_lm_build_admin_edit_field' ) ) {
	function mstw_lm_build_admin_edit_field( $args ) {
	
		$defaults = array(
				'type'		 => 'text',
				'id'      	 => 'default_field', // the ID of the setting in our options array, and the ID of the HTML form element
				'title'   	 => __( 'Default Field', 'mstw-league-manager' ), // the label for the HTML form element
				'label'   	 => __( 'Default Label', 'mstw-league-manager' ), // the label for the HTML form element
				'desc'   	 => '', // the description displayed under the HTML form element
				'default'	 => '',  // the default value for this setting
				'type'    	 => 'text', // the HTML form element to use
				'options' 	 => array(), // (optional): the values in radio buttons or a drop-down menu
				'name' 		 => '', //name of HTML form element. should be options_array[option]
				'class'   	 => '',  // the HTML form element class. Also used for validation purposes!
				'curr_value' => '',  // the current value of the setting
				'maxlength'	 => '',  // maxlength attrib of some input controls
				'size'	 	 => '',  // size attrib of some input controls
				'img_width'  => 60,
				'btn_label'  => 'Upload from Media Library',
				);
		
		// "extract" to be able to use the array keys as variables in our function output below
		$args = wp_parse_args( $args, $defaults );
	
		extract( $args );
		
		// default name to id
		$name = ( !empty( $name ) ) ? $name : $id;
		
		// pass the standard value if the option is not yet set in the database
		//if ( !isset( $options[$id] ) && $options[ != 'checkbox' && ) {
		//	$options[$id] = ( isset( $default ) ? $default : 'default_field' );
		//}
		
		// Additional field class. Output only if the class is defined in the $args()
		$class_str = ( !empty( $class ) ) ? "class='$class'" : '' ;
		$maxlength_str = ( !empty( $maxlength ) ) ? "maxlength='$maxlength'" : '' ;
		$size_str = ( !empty( $size ) ) ? "size='$size'" : '' ;
		$attrib_str = " $class_str $maxlength_str $size_str ";

		// switch html display based on the setting type.
		switch ( $args['type'] ) {
			//TEXT & COLOR CONTROLS
			case 'text':	// this is the default type
			case 'color':  	// color field is just a text field with associated JavaScript
			?>
				<input type="text" id="<?php echo $id ?>" name="<?php echo $name ?>" value="<?php echo $curr_value ?>" <?php echo $attrib_str ?> />
			<?php
				echo ( !empty( $desc ) ) ? "<br /><span class='description'>$desc</span>\n" : "";
				break;
				
			//SELECT OPTION CONTROL
			case 'select-option':
				//not sure why this is needed given the extract() above
				//but without it you get an extra option with the 
				//'option-name' displayed (huh??)
				$options = $args['options'];
					
				echo "<select id='$id' name='$name' $attrib_str >";
					foreach( $options as $key=>$value ) {
						$selected = ( $curr_value == $value ) ? 'selected="selected"' : '';
						echo "<option value='$value' $selected>$key</option>";
					}
				echo "</select>";
				echo ( !empty( $desc ) ) ? "<br /><span class='description'>$desc</span>" : "";
				break;
			
			// CHECKBOX
			case 'checkbox':
				echo "<input class='checkbox $class_str' type='checkbox' id='$id' name='$name' value=1 " . checked( $curr_value, 1, false ) . " />";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";	
				break;
				
			// LABEL
			case 'label':
				echo "<span class='description'>" . $curr_value . "</span>";
				echo ( '' != $desc ) ? "<br /><span class='description'>$desc</span>" : "";
				break;
				
			// MEDIA UPLOADER
			case 'media-uploader':
				?>
				<td class="uploader">
					<input type="text" name="<?php echo $id  ?>" id="<?php echo $id ?>" class="mstw_logo_text" size="32" value="<?php echo $curr_value ?>"/>
					<?php echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : ""; ?>
				</td>
				
				<td class="uploader">
				  <input type="button" class="button" name="<?php echo $id . '_btn'?>" id="<?php echo $id . '_btn'?>" value="<?php echo $btn_label ?>" />
				<!-- </div> -->
				</td>
				<td>
				<img id="<?php echo $id . '_img' ?>" width="<?php echo $img_width ?>" src="<?php echo $curr_value ?>" />
				</td>
		<?php
				break;
				

			//---------------------------------------------------------------
			// THE FOLLOWING CASES HAVE NOT BEEN TESTED/USED
			
			case "multi-text":
				foreach($options as $item) {
					$item = explode("|",$item); // cat_name|cat_slug
					$item[0] = esc_html__($item[0], 'wptuts_textdomain');
					if (!empty($options[$id])) {
						foreach ($options[$id] as $option_key => $option_val){
							if ($item[1] == $option_key) {
								$value = $option_val;
							}
						}
					} else {
						$value = '';
					}
					echo "<span>$item[0]:</span> <input class='$field_class' type='text' id='$id|$item[1]' name='" . $wptuts_option_name . "[$id|$item[1]]' value='$value' /><br/>";
				}
				echo ($desc != '') ? "<span class='description'>$desc</span>" : "";
			break;
			
			case 'textarea':
				$options[$id] = stripslashes($options[$id]);
				$options[$id] = esc_html( $options[$id]);
				echo "<textarea class='textarea$field_class' type='text' id='$id' name='" . $wptuts_option_name . "[$id]' rows='5' cols='30'>$options[$id]</textarea>";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : ""; 		
			break;

			case 'select2':
				echo "<select id='$id' class='select$field_class' name='" . $wptuts_option_name . "[$id]'>";
				foreach($options as $item) {
					
					$item = explode("|",$item);
					$item[0] = esc_html($item[0], 'wptuts_textdomain');
					
					$selected = ($options[$id]==$item[1]) ? 'selected="selected"' : '';
					echo "<option value='$item[1]' $selected>$item[0]</option>";
				}
				echo "</select>";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
			break;

			case "multi-checkbox":
				foreach($options as $item) {
					
					$item = explode("|",$item);
					$item[0] = esc_html($item[0], 'wptuts_textdomain');
					
					$checked = '';
					
					if ( isset($options[$id][$item[1]]) ) {
						if ( $options[$id][$item[1]] == 'true') {
							$checked = 'checked="checked"';
						}
					}
					
					echo "<input class='checkbox$field_class' type='checkbox' id='$id|$item[1]' name='" . $wptuts_option_name . "[$id|$item[1]]' value='1' $checked /> $item[0] <br/>";
				}
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
			break;
			
			default:
				mstw_log_msg( "CONTROL TYPE $type NOT RECOGNIZED." );
				echo "CONTROL TYPE $type NOT RECOGNIZED.";
			break;
			
		}	
	} //End: mstw_lm_build_admin_edit_field()
}

