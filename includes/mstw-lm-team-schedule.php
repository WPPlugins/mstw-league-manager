<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-team-schedule.php
 *	Contains the code for the MSTW League Manager team schedule table
 *		shortcode [mstw_team_schedule]
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
 *-------------------------------------------------------------------------*/

//---------------------------------------------------------------------------
// Add the shortcode handler, which will create the League's Game Schedule
// table on the user side. Handles the shortcode parameters, if there were 
// any, then calls mstw_lm_build_league_schedule_table( ) to create the output
// 

add_shortcode( 'mstw_team_schedule', 'mstw_lm_shortcode_handler' );

//--------------------------------------------------------------------------------------
// MSTW_LM_BUILD_TEAM_SCHEDULE
// 	Called by mstw_lm_build_league_schedule_table( )
// 	Builds the schedule table with a date column (to replace the [shortcode] in a page
//
// ARGUMENTS:
//  $games - array of mstw_lm_game objects, built by mstw_lm_build_league_schedule_table
// 	$atts  - the display settings and shortcode arguments
//	$league_slug - the league to be displayed
//
// RETURNS
//	HTML for schedule table as a string
//
function mstw_lm_build_team_schedule( $atts ) {
	//mstw_log_msg( 'in mstw_lm_build_team_schedule ...' );
	//mstw_log_msg( $atts );
	
	// Check that we have a team set
	// This is not currently necessary, but it was there and 
	// it allows for multiple teams in the future
	//
	$team_slugs = explode( ',', $atts['team'] );
	//mstw_log_msg( '$team_slugs[0] = ' . $team_slugs[0] );
	
	// CHECK THERE IS A TEAM IN THE ATTRIBUTES; HAVE TO HAVE ONE
	if ( !empty( $team_slugs[0] ) ) {
		//CSS tag will be the FIRST team slug specified
		$team_slug = $team_slugs[0];
		$css_tag = '_' . $team_slug;
	}
	else {
		//Have to have at least one team slug
		return "<h3 class='mstw-lm-user-msg'>" . __( 'No TEAM specified in shortcode.', 'mstw-league-manager' ) . '</h3>';
	}
	
	// Check that we have a league set. 
	// There could be more than one.
	//
	$league_slugs = explode( ',', $atts['league'] );
	//mstw_log_msg( '$league_slugs[0] = ' . $league_slugs[0] );
	
	// CHECK THERE IS A LEAGUE IN THE ATTRIBUTES; HAVE TO HAVE ONE
	// This is checked by mstw_lm_shortcode_handler as well
	if ( !empty( $league_slugs[0] ) ) {
		//CSS tag will be the FIRST team slug specified
		$league_slug = $league_slugs[0];
		$league_css_tag = '_' . $league_slug;
	}
	else {
		//Have to have a league slug
		return "<h3 class='mstw-lm-user-msg'>" . __( 'No LEAGUE specified in shortcode.', 'mstw-league-manager' ) . '</h3>';
	}
	
	//mstw_log_msg( "league slug: " . $league_slug );
	

	// Check that we have a season set or default to first season for league
	if ( array_key_exists( 'season', $atts ) && !empty( $atts['season'] ) ) {
		$season_slug = $atts['season'];
	}
	else {
		// There should always be a season for a league
		mstw_log_msg( "No season specified in shortcode. Trying to find season for league: $league_slug" );
		$seasons_array = mstw_lm_build_seasons_list( $league_slug );
		if ( $seasons_array ) {
			$season_slug = reset( $seasons_array );
			if ( false === $season_slug ) {
				return "<h3 class='mstw-lm-user-msg'>Found seasons for league slug $league_slug  </h3>";	
			}
		}
	}
	
	//mstw_log_msg( "season slug: " . $season_slug );
	
	//return( "<h3 class='mstw-lm-user-msg'>Have team, league, and season.  </h3>" );
	
	//This changes if and only if last_dtg == now
	$sort_order = 'ASC';
	
	//
	// first_dtg and last_dtg can be set in shortcode
	//
	$dtgs = mstw_lm_build_date_bounds( $atts );
	
	$first_dtg = $dtgs['first'];
	$last_dtg  = $dtgs['last'];
	
	//mstw_log_msg( '$first_dtg: ' . date( 'Y-m-d h:m', $first_dtg ) );	
	//mstw_log_msg( '$last_dtg: ' . date( 'Y-m-d h:m', $last_dtg ) );
	
	$teams = get_posts( 
				array( 'numberposts'  => -1,
						'post_type'   => 'mstw_lm_team',
					    'tax_query'   => array( 
										 array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slugs,
											),
										 ),
					 )
	
	);
	
	$team_slugs = array();
	
	if ( $teams ) {
		foreach( $teams as $team ) {
			//mstw_log_msg( $team -> post_name );
			$team_slugs[] = $team -> post_name ;
		}
		//mstw_log_msg( $team_slugs );
	} //else {
		//mstw_log_msg( "No teams found in leagues:" );
		//mstw_log_msg( $league_slugs );
	//}
	
	// Get the games posts
	$games = get_posts( 
				array( 'numberposts'    => -1,
					   'post_type'      => 'mstw_lm_game',
					   /*'mstw_lm_league' => $league_slug,*/
					   'tax_query' => array( 
										array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slugs,
											),
										),
					   'meta_query' => array(
										'relation' => 'AND',
										array(
											'key'     => 'game_season',
											'value'   => $season_slug,
											'compare' => '=',
											),
										array(
										   'key'     => 'game_unix_dtg',
										   'value'   => array( $first_dtg,
															   $last_dtg ),
										   'type'    => 'NUMERIC',
										   'compare' => 'BETWEEN'
										   ),
										array(
										   'relation' => 'OR',
											array(
											   'key'     => 'game_home_team',
											   'value' => $team_slug,
											   //'value'   => $team_slugs,
											   //'compare' => 'IN',
											   ),
											array(
											   'key'     => 'game_away_team',
											   'value'   => $team_slug,
											   //'value'   => $team_slugs,
											   //'compare' => 'IN',
											   ),
											),
										),
						'orderby' => 'meta_value', 
						'meta_key' => 'game_unix_dtg',
						'orderby' => 'meta_value_num',
						'order' => $sort_order 
						) 
				);
	
	//
	// Team Schedules do not show these fields
	//
	$show_fields = $atts['schedule_fields'];
	$show_fields['visitor'] = 0;
	$show_fields['home'] = 0;
	$show_fields['gallery_game'] = 0;
	
	$atts['schedule_fields'] = $show_fields;
	
	$output = ''; 
	
	if ( $games ) {
		// Make table of games
		// Starting with the table header
		$output = "<table class='mstw-lm-table mstw-lm-team-schedule mstw-lm-team-schedule_$team_slug mstw-lm-team-schedule_" . $league_slug . "'>\n"; 
		$output .= "<thead><tr>\n";
			$output .= mstw_lm_build_team_schedule_header( $atts );
		$output .= "</tr></thead>\n";

		$output .= "<tbody>\n";
		
		// Loop through the posts and make the rows
		foreach( $games as $game ) {

			$output .= mstw_lm_build_team_schedule_row( $game, $atts, $team_slug );
			
		} // end of foreach game
		
		$output .= "</tbody></table>\n";
	}
	else {
		$output = "<h3 class='mstw-lm-user-msg'>" . __( 'No games found for ', 'mstw-league-manager' ) . $team_slug . '</h3>';
	}
	
	return $output;

} //End: mstw_lm_build_schedule_table()

function mstw_lm_build_team_schedule_row( $game, $atts, $schedule_team_slug ) {
	//mstw_log_msg( "mstw_lm_build_team_schedule_row:" );
	
	$output = '';
	
	if ( $game ) {
		$fields      = $atts['schedule_order'];
		$show_fields = $atts['schedule_fields'];
		
		$opp_field = mstw_lm_get_opponent_field( $game, $schedule_team_slug );
		$team_field = ( 'game_home_team' == $opp_field ) ? 'game_away_team' : 'game_home_team';
		
		//
		// This tells you whether the opponent is the home team or the visitor
		//
		$home_css = ( 'game_home_team' == $opp_field ) ? 'away-game' : 'home-game';
		
		$output .= "<tr class='$home_css'>\n";
			
		foreach ( $fields as $field ) {
			//mstw_log_msg( "field: $field" );
			//mstw_log_msg( "show: " . $show_fields[ $field ]);
			
			if ( $show_fields[ $field ] ) {
				$output .= '<td>';
				
				switch( $field ) {
					case 'time':
						// Create the time/results entry
						$output .=  mstw_lm_get_game_time_result( $game, $atts, 'team_table', $team_field );	
						break;
						
					case 'date':
						//Build the game date in a specified format
						$output .= mstw_lm_date_loc( $atts['date_format'], (int)get_post_meta( $game->ID, 'game_unix_dtg', true ) );	
						break;
						
					case 'opponent':
						//
						// Create the opponent entry SHOULD ALWAYS BE SHOWN
						//	but will allow user to screw it up
						//
						$opp_logo_html = '';
						
						if ( $atts['schedules_show_logo'] ) {
							$opp_logo_html = mstw_lm_build_team_logo( get_post_meta( $game->ID, $opp_field, true), 'small' );	
						}
						
						//
						// This tells you whether the opponent is the home team 
						//	or the visitor
						//
						$opp_tag = ( 'game_home_team' == $opp_field ) ? 'home' : 'visitor' ;
						
						$show_home_away = ( array_key_exists( 'show_home_away', $atts ) ) ? $atts['show_home_away'] : 0;

						//
						// Asterisk the home games. This may seem backwards, but it ain't.
						//
						$home_tag = '';
						if ( array_key_exists( 'star_home', $atts ) && '' != $atts['star_home'] ) {
							if ( 1 == $show_home_away && 'visitor' == $opp_tag ) {
									//$home_tag = __( '*', 'mstw-league-manager' );
									$home_tag = $atts['star_home'];
							}
						}
						
						$league_tag = '';
						if ( array_key_exists( 'star_league', $atts ) && '' != $atts['star_league'] ) {
							$nonleague_game = get_post_meta( $game->ID, 'game_nonleague', true );
							//$league_tag = ( $nonleague_game ) ? '' : __( '*', 'mstw-league-manager' ) ;
							$league_tag = ( $nonleague_game ) ? '' : $atts['star_league'];
						}
						
						$output .= $opp_logo_html . mstw_lm_get_team_name( $game, $opp_tag, $atts, $atts['schedules_name_format'] ) . $home_tag . $league_tag;
						break;
						
					case 'location':
						// create the location entry
						$output .= mstw_lm_get_game_location( $game, $atts );
						break;
						
					case 'media':
						$output .= mstw_lm_get_game_media( $game, $atts );
						break;
						
					case 'home':
					case 'visitor':
					case 'gallery_game':
					default:
						break;
					
				} //End: switch( $field )
				
				$output .= "</td>\n";
			
			} // End: if ( $show_fields[ $field ] )
				
		} //End: foreach ( $fields as $field )
		
		$output .= "</tr>\n";
		
	} //End: if ( $game )
		
	else {
		mstw_log_msg( 'mstw_lm_build_team_schedule_row:: No game found for game' );
	}
	
	return $output;
	
} //End: mstw_lm_build_team_schedule_row()

function mstw_lm_get_opponent_field( $game, $schedule_team_slug ) {
	//mstw_log_msg( 'in mstw_lm_get_opponent_field ... ' );
	$ret_val = null;
	
	$home = get_post_meta( $game->ID, 'game_home_team', true );
	
	$ret_val = ( $home == $schedule_team_slug ) ? 'game_away_team' : 'game_home_team';
	
	return $ret_val;
	
} //End: mstw_lm_get_opponent_field()

function mstw_lm_build_team_schedule_header( $atts ) {
	//mstw_log_msg( 'in mstw_lm_build_team_schedule_header ...' );
	//mstw_log_msg( $atts );
	
	$fields      = $atts['schedule_order'];
	$show_fields = $atts['schedule_fields'];
	//$show_fields['home'] = 0;
	//$show_fields['visitor'] = 0;
	$show_fields['gallery_game'] = 0;
	$labels      = $atts['schedule_labels'];
	
	$output = '';
	
	foreach ( $fields as $field ) {
		if( $show_fields[ $field] ) {
			$output .= '<th>' . $labels[ $field ] . '</th>';
		}
	}
	
	return $output;
	
	/*
	$output = '';
	
	if( $atts['schedule_fields']['date'] ) { 
		$output .= '<th>' . $atts['schedule_labels']['date'] . '</th>';
	}
	
	// Always output an Opponent field
	// if ( $atts['schedule_fields']['opponent'] ) {
		$output .= '<th>'. $atts['schedule_labels']['opponent'] . '</th>';
	//}
	
	if ( $atts['schedule_fields']['time'] ) {
		$output .= '<th>'. $atts['schedule_labels']['time'] . '</th>';
	}
	
	if( $atts['schedule_fields']['location'] ) {
		$output .= '<th>'. $atts['schedule_labels']['location'] . '</th>';
	}
	
	
	if ( $atts['schedule_fields']['media'] ) {
		$output .= '<th>'.  $atts['schedule_labels']['media'] . '</th>';
	}
	
	return $output;
	*/
	
} //End: mstw_lm_build_team_schedule_header
