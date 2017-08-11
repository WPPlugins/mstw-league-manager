<?php
/*---------------------------------------------------------------------------
 *	mstw-lm-schedule-gallery.php
 *	Contains the code for the MSTW Schedules & Scoreboards scoreboard
 *		gallery display. shortcode [mstw_lm_scoreboard format=gallery]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2014 Mark O'Donnell (mark@shoalsummitsolutions.com)
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
// Add the shortcode handler, which will create the League's Schedule
// gallery on the user side. Handles the shortcode parameters, if there were 
// any, then calls mstw_lm_build_league_schedule_table( ) to create the $games array
// and the $league_slug that are passed to mstw_lm_build_schedule_gallery
// 
add_shortcode( 'mstw_league_schedule_gallery', 'mstw_lm_shortcode_handler' );

//---------------------------------------------------------------------------
// MSTW_LM_BUILD_SCHEDULE_GALLERY
// 	Called by mstw_lm_league_schedule_gallery_shortcode_handler( )
// 	Builds the schedule gallery as a string to replace the [shortcode] in a page.
// 	Loop through the games in a league and format them into a pretty HTML gallery.
//
// ARGUMENTS:
// 	$args - the display settings and shortcode arguments, properly combined & defaulted
//		 by mstw_lm_league_schedule_gallery_shortcode_handler( )
// RETURNS
//	HTML for schedule gallery as a string
//

function mstw_lm_build_schedule_gallery( $games, $atts, $league_slug ) {
	//mstw_log_msg( " In mstw_lm_build_scoreboard_gallery ... " );
	//mstw_log_msg( $atts );
	
	$output = ''; 
	
	$curr_dtg = -1;
	$i        = 0;  //must be a way to avoid this nonsense
	
	// $games should not be empty - already checked
	foreach ( $games as $game ) {
		//need only the date for date header; must ignore the rest.
		$date_stamp = strtotime( date("Y-m-d", get_post_meta( $game->ID, 'game_unix_dtg', true ) ) );
		
		if ( $curr_dtg < $date_stamp ) {
			$curr_dtg = get_post_meta( $game->ID, 'game_unix_dtg', true ) ;
			//big date container for all games on a date
			//if it's not the first date, close the last date's div
			if( $i > 0 ) {
				//$output .= "</div> <!-- .lm-games-container --> \n";
				$output .= "</tbody></table> \n";
			}
			$output .= "<table class='mstw-lm-table mstw-lm-table_$league_slug mstw-lm-gallery-table'>\n"; 
			$output .= "<caption>" . date( $atts['gallery_date_format'], $curr_dtg ) . "</caption>";
			$output .= mstw_lm_build_gallery_header( $atts );
			$output .= "<tbody>";
		}
		
		// create the game row
		$output .= "<tr>";
			$output .= "<td>" . mstw_lm_build_team_html( $game, $atts, 'home' ) . "</td>\n";
			$output .= "<td class='visitor-cell'>" . mstw_lm_build_team_html( $game, $atts, 'visitor' ) . "</td>\n";
			$output .= "<td>" . mstw_lm_get_game_time_result( $game, $atts ) . "</td>\n";
			$output .= "<td>" . mstw_lm_get_game_location( $game, $atts ) . "</td>\n";
		$output .= "</tr>";
		
		
		$i++;  //there must be a way to avoid this nonsense!?!?
		
	} //End: foreach ( $games as $game ) {
		
	$output .= "</tbody></table> \n";

	
	return $output;

} //End function mstw_lm_build_schedule_gallery( )

function mstw_lm_build_gallery_header( $atts ) {
	//mstw_log_msg( " In mstw_lm_build_gallery_header ... " ); 	
	$ret_str = '<thead><tr>';
	
	// this is the home column, which will appear to contain both teams
	$ret_str .= '<th colspan="2">'. $atts['schedule_labels']['gallery_game'] . '</th>';
	//// this is the @ column
	//$ret_str .= '<th>@</th>';
	//this is the visitor column
	//$ret_str .= '<th></th>'; 
	// the time/results column
	$ret_str .= '<th>'. $atts['schedule_labels']['time'] . '</th>';
	// the location column
	$ret_str .= '<th>'. $atts['schedule_labels']['location'] . '</th>';
	
	return $ret_str . '</tr></thead>';
	
} //End: mstw_lm_build_gallery_header()

//--------------------------------------------------------------------------------------
// MSTW_SS_BUILD_SCHEDULE_TABLE
// $team = 'home' or 'visitor'

function mstw_lm_build_team_html( $game, $atts, $team ) {
	//mstw_log_msg( " In mstw_lm_build_team_html ... " ); 
	
	$ret_str = '';
	
	$data_field = ( 'visitor' == $team ) ? 'game_away_team' : 'game_home_team';
	
	if ( $atts['schedules_show_logo'] ) {
		$ret_str .= mstw_lm_build_team_logo( get_post_meta( $game->ID, $data_field, true), 'small' );
	}
	
	$ret_str .= mstw_lm_get_team_name( $game, $team, $atts, $atts['schedules_name_format'] );
	
	return $ret_str;
	
} //End: mstw_lm_build_team_html()

