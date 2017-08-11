<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-schedule-table.php
 *	Contains the code for the MSTW League Manager schedule table
 *		shortcode [mstw_league_schedule]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2015-6 Mark O'Donnell (mark@shoalsummitsolutions.com)
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

add_shortcode( 'mstw_league_schedule_table', 'mstw_lm_shortcode_handler' );


//--------------------------------------------------------------------------------------
// MSTW_LM_BUILD_SCHEDULE_TABLE
// 	Called by mstw_lm_build_league_schedule_table( )
// 	Builds the schedule table HTML (to replace the [shortcode] in a page
//	
//
// ARGUMENTS:
//  $games - array of mstw_lm_game objects, built by mstw_lm_build_league_schedule_table; Know you have games, or you would not get here
// 	$atts  - the display settings and shortcode arguments
//	$league_slug - the league to be displayed
//
// RETURNS
//	HTML for schedule table as a string
//
function mstw_lm_build_schedule_table( $games, $atts, $league_slug ) {
	//mstw_log_msg( 'mstw_lm_build_schedule_table:' );
	//mstw_log_msg( 'fields to show:' );
	//mstw_log_msg( $atts['schedule_fields'] );
	//mstw_log_msg( 'fields order:' );
	//mstw_log_msg( $atts['schedule_order'] );
	
	/* THIS IS JUST FOR TESTING 
	mstw_log_msg( 'all options:' );
	$all_options = wp_load_alloptions();
	$option_names = array();
	foreach ( $all_options as $name => $value ) {
		$option_names[] = $name;
	}
	natsort( $option_names );
	mstw_log_msg( $option_names );
	*/
	
	// Make table of games
	// Start with the table header
	
	$output = "<table class='mstw-lm-table mstw-lm-schedules mstw-lm-table_$league_slug'>\n"; 
	$output .= "<thead><tr>\n";
		$output .= mstw_lm_build_league_schedule_header( $atts );
	$output .= "</tr></thead>\n";
	
	$output .= "<tbody>\n";
	
	// Loop through the posts and make the rows
	foreach( $games as $game ) {

		$output .= mstw_lm_build_league_schedule_row( $game, $atts );
		
	} // end of foreach game
	
	$output .= "</tbody></table>\n";
	
	return $output;
	
} //End: mstw_lm_build_schedule_table()

//-----------------------------------------------------------------------------
// mstw_lm_build_league_schedule_header - returns HTML for league schedule 
//		table header
//
function mstw_lm_build_league_schedule_header( $atts ) {
	//mstw_log_msg( "in mstw_lm_build_league_schedule_header ..." );
	
	$fields      = $atts['schedule_order'];
	$show_fields = $atts['schedule_fields'];
	$show_fields['opponent'] = 0;
	$show_fields['gallery_game'] = 0;
	$labels      = $atts['schedule_labels'];
	
	$output = '';
	
	foreach ( $fields as $field ) {
		if( $show_fields[ $field] ) {
			$output .= '<th>' . $labels[ $field ] . '</th>';
		}
	}
	
	return $output;
	
} //End: mstw_lm_build_league_schedule_header( )

//-----------------------------------------------------------------------------
// mstw_lm_build_league_schedule_row - returns HTML for one league schedule table row
//
function mstw_lm_build_league_schedule_row( $game, $atts ) {
	//mstw_log_msg( "mstw_lm_build_league_schedule_row:" );
	//mstw_log_msg( 'game:' );
	//mstw_log_msg( $game );
	//mstw_log_msg( 'atts[location_format]: ' . $atts['location_format'] );
	//mstw_log_msg( $atts );
	
	if ( $game ) {
		$fields      = $atts['schedule_order'];
		$show_fields = $atts['schedule_fields'];
		
		//
		// These fields are not shown in league schedules
		//
		$show_fields['opponent'] = 0;
		$show_fields['gallery_game'] = 0;
			
		$output = '<tr>';
		
		foreach ( $fields as $field ) {
			//mstw_log_msg( "field: $field" );
			//mstw_log_msg( "show: " . $show_fields[ $field ]);
			if( $show_fields[ $field ] ) {
				
				$output .= '<td>';
				
				switch( $field ) {
					case 'time':
						$output .= mstw_lm_get_game_time_result( $game, $atts );
						break;
						
					case 'date':	
						$output .=  mstw_lm_date_loc( $atts['date_format'], (int)get_post_meta( $game->ID, 'game_unix_dtg', true ) );	
						break;
						
					case 'home':
					case 'visitor':
						// figure out the team logos
						if ( $atts['schedules_show_logo'] ) {
							$game_team = ( 'home' == $field ) ? 'game_home_team' : 'game_away_team';
							
							$logo_html = mstw_lm_build_team_logo( get_post_meta( $game->ID, $game_team, true), 'small' );	
						}
						else {
							$logo_html = '';
						}
						
						// figure out the team name
						$output .=  $logo_html . mstw_lm_get_team_name( $game, $field, $atts, $atts['schedules_name_format'] );
						
						break;
						
					case 'location':
						// if game CPT HAS a game_location, pull it from the 
						// venues table, else try to pull it from the home team's
						// CPT, else it's blank
						$output .= mstw_lm_get_game_location( $game, $atts );
						
						break;
						
					case 'media':
						$output .= get_post_meta( $game->ID, 'game_media', true );
						break;
					
					case 'opponent':
					case 'gallery_game':
					default:
						break;
				}

				$output .= '</td>';
				
			} //End: if( $show_fields[ $field ] ) {
			
		} //End: foreach ( $fields as $field )
		
		$output .= '</tr>';
		
		//mstw_log_msg( "output html: $output" );
		
	}
	else {
		$output = '';
		mstw_log_msg( 'mstw_lm_build_league_schedule_row: No game found for game' );
	}

	return $output;
	
} //End: mstw_lm_build_league_schedule_row()


