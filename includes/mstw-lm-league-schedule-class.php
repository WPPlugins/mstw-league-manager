<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-league-schedule-class.php
 *	Contains the code for the MSTW League Manager new league schedule
 *		shortcode [mstw_league_schedule_2]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2016 Mark O'Donnell (mark@shoalsummitsolutions.com)
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
 
 class MSTWLeagueSchedule {
	public function __construct()
    {
		//mstw_log_msg( "MSTWLeagueScheduleClass constructor:" );
        add_shortcode( 'mstw_league_schedule_2', array( $this, 'league_schedule_handler' ) );
    }
    
	//
	// handles the arguments and defaults (settings)
	//
    public function league_schedule_handler( $atts, $content = null, $shortcode )
    {
		//mstw_log_msg( "league_schedule_handler:" );
		//mstw_log_msg( 'shortcode: ' . $shortcode );
		//mstw_log_msg( '$atts=' );
		//mstw_log_msg( $atts );
		
		// MUST have a league to proceed
		if( !is_array( $atts ) or !array_key_exists( 'league', $atts ) ) {	
			return '<h3 class="mstw-lm-user-msg">' . sprintf( __( 'No LEAGUE specified in %s shortcode.', 'mstw-league-manager' ), $shortcode ) . '</h3>';
		}
		
		// DEFAULT season to current year ... worth a shot
		if ( !is_array( $atts ) || !array_key_exists( 'season', $atts ) ) {
			$atts['season'] = date( 'Y', current_time( 'timestamp' ) );
		}
		
		$league_slugs = explode( ',', $atts['league'] );
		
		// sport settings are driven by FIRST league in list
		$sport_slug = mstw_lm_get_league_sport( $league_slugs[0] );
		
		$args = mstw_lm_get_sport_options( $sport_slug );
		
		// merge the sport settings with the shortcode arguments			
		$attribs = shortcode_atts( $args, $atts );
		
		// these are hacks because date/time stuff was added late
		if ( array_key_exists( 'first_dtg', $atts ) ) {
			$attribs['first_dtg'] = $atts['first_dtg'];
		}
		if ( array_key_exists( 'last_dtg', $atts ) ) {
			$attribs['last_dtg'] = $atts['last_dtg'];
		}
		if ( array_key_exists( 'interval_days', $atts ) ) {
			$attribs['interval_days'] = $atts['interval_days'];
		}
				
		return $this -> build_league_schedule( $attribs, 'table' );
		
    }
	
	//------------------------------------------------------------------------
	// BUILD_LEAGUE_SCHEDULE
	// 	Builds the schedule table HTML (to replace the [shortcode] in a page
	//	
	// ARGUMENTS:
	// 	$atts  - the display settings and shortcode arguments
	//	$view - build a table view ('table' - default ) or gallery ('gallery')
	//		not currently used. no gallery view in league_schedule_2
	//
	// RETURNS
	//	HTML for schedule table as a string
	//
	function build_league_schedule( $atts, $view = 'table' ) {
		//mstw_log_msg( 'build_league_schedule:' );
		
		$league_slugs = explode( ',', $atts['league'] );
		
		$games = $this -> get_games( $atts );
		
		$output = '';
		
		if ( $games ) {
			// Make table of games
			// Starting with the table header
			$output .= "<table class='mstw-lm-table mstw-lm-schedules mstw-lm-table_" . $league_slugs[0] . "'>\n"; 
			$output .= "<thead><tr>\n";
				$output .= $this -> build_schedule_header( $atts );
			$output .= "</tr></thead>\n";
			
			$output .= "<tbody>\n";
			
			// Loop through the posts and make the rows
			foreach( $games as $game ) {
				$output .= $this -> build_schedule_row( $game, $atts );
			} // end of foreach game
			
			$output .= "</tbody></table>\n";
		}
		else {
			//$output .= "<h2>No games found for league(s) " . $atts['league'] . " and season " . $atts['season'] . "</h2>";
			$output = "<h3 class='mstw-lm-user-msg'>" . sprintf(  __( 'No games found for league(s) %s and season %s', 'mstw-league-manager' ), $atts['league'], $atts['season'] ) . "</h3>";
		}
			
		return $output;
		
	} //End: build_league_schedule( )
	
	//------------------------------------------------------------------------
	// GET_GAMES
	// 	Pulls all the games for a league (or leagues) and season
	//	
	// ARGUMENTS:
	// 	$atts  - the display settings and shortcode arguments
	//
	// RETURNS
	//	List of games (lm_game_objects)
	//
	function get_games( $atts ) {
		//mstw_log_msg( 'get_games:' );
		
		$league_slugs = explode( ',', $atts['league'] );
		
		//mstw_log_msg( "league_slugs: " );
		//mstw_log_msg( $league_slugs );
		
		$teams = get_posts( 
					array( 
						'numberposts' => -1,
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
				 
		//mstw_log_msg( "Teams found: " . count( $teams ) );
				 
		// if no teams were found, there will be no games
		if ( !$teams ) {
			//mstw_log_msg( "build_league_schedule: No teams found in leagues " . $atts['league'] );
			return 0;
		}
		
		// Build an array of team slugs from the teams (objects) found
		$team_slugs = array();

		foreach( $teams as $team ) {
			//mstw_log_msg( $team -> post_name );
			$team_slugs[] = $team -> post_name ;
		}
		//mstw_log_msg( $team_slugs );
		
		$dtgs = mstw_lm_build_date_bounds( $atts );
		//mstw_log_msg( $dtgs );
		//mstw_log_msg( 'first_dtg: ' . date( 'Y-m-d', $dtgs['first'] ) );
		//mstw_log_msg( 'last_dtg: ' . date( 'Y-m-d', $dtgs['last'] ) );
		
		$meta_query = $this -> build_meta_query( $atts, $team_slugs, $dtgs );
		//mstw_log_msg( 'league-schedule-class: $meta_query' );
		//mstw_log_msg( $meta_query );
		
		// Get the games posts
		$games = get_posts( 
					array( 'numberposts' => -1,
						   'post_type'   => 'mstw_lm_game',
						   
						   /*'tax_query' => array( 
											array(
												'taxonomy' => 'mstw_lm_league',
												'field'    => 'slug',
												'terms'    => $league_slugs,
												//'terms' => array( 'pac-12' ),
												),
											),
							*/
											
						   'meta_query' => $meta_query,
											
							'orderby'  => 'meta_value_num',
							'meta_key' => 'game_unix_dtg',
							'order'    => 'ASC', 
							) 
					);
		
		//mstw_log_msg( "Games found: " . count( $games ) ) ;
		return $games;
		
	} // End: get_games( )
	
	//-------------------------------------------------------------------------------
	// build_meta_query - builds the meta-query string for league schedules
	//
	//	ARGUMENTS: 
	//		$atts: the display settings and shortcode arguments
	//		$team_slugs: list of teams in league (can be home or away team)
	//		$dtgs: array of first and last dtgs (php timestamps) 
	//
	//	RETURNS:
	//		The meta query array built based on:
	//			 show_nonleague, season, first_dtg, and last_dtg
	//
	function build_meta_query( $atts, $team_slugs, $dtgs ) {
		//mstw_log_msg( "build_meta_query:" );
		
		// Allow non-league games to appear on schedule
		$show_nonleague = array_key_exists( 'show_nonleague', $atts ) ? $atts['show_nonleague'] : 0;
		
		$first_dtg = $dtgs['first'];
		$last_dtg  = $dtgs['last'];
		
		if ( $show_nonleague ) {
			//mstw_log_msg( "Show nonleague games" );
			$meta_query = array(
							'relation' => 'AND',
							array( 
								'key' => 'game_season',
								'value'   => $atts['season'],
								'compare' => '=',
								),
							array(
							   'relation' => 'OR',
								array(
								    'key'     => 'game_home_team',
								    'value'   => $team_slugs,
								    'compare' => 'IN',
								    ),
								array(
								    'key'     => 'game_away_team',
									'value'   => $team_slugs,
									'compare' => 'IN',
									)
								),
							array(
								'key'     => 'game_unix_dtg',
								'value'   =>  array( $first_dtg,
												     $last_dtg ),
								'type'    => 'NUMERIC',
								'compare' => 'BETWEEN'
								)								 
							);
												
		}
		else {
			//mstw_log_msg( "Show league games ONLY" );
			$meta_query = array ( 
							'relation' => 'AND',  
							array( 'key'     => 'game_season',
								'value'   => $atts['season'],
								'compare' => '=',
								),
							array(
							    'relation' => 'OR',
								array(
									'key'     => 'game_home_team',
									'value'   => $team_slugs,
									'compare' => 'IN',
									),
								array(
									'key'     => 'game_away_team',
									'value'   => $team_slugs,
									'compare' => 'IN',
									)
								),
							array( 
								'key'     => 'game_nonleague',
								'value'   => 0,
								'compare' => '=',
								),
							array( 
								'key'     => 'game_unix_dtg',
								'value'   => array( $first_dtg,
													$last_dtg ),
								'type'    => 'NUMERIC',
								'compare' => 'BETWEEN'
								)		
							);
		}
		
		return $meta_query;
		
	} //End: build_meta_query( )
	
	//------------------------------------------------------------------------
	// BUILD_SCHEDULE_HEADER
	// 	Builds the league schedule table header HTML
	//
	// ARGUMENTS:
	// 	$atts: the display settings and shortcode arguments
	//
	// RETURNS
	//	HTML for league schedule table header (cells)
	//
	function build_schedule_header( $atts ) {
		//mstw_log_msg( 'build_schedule_header:' );
		//mstw_log_msg( $atts );
		
		$fields      = $atts['schedule_order'];
		$labels      = $atts['schedule_labels'];
		$show_fields = $atts['schedule_fields'];
		$show_fields['opponent'] = 0;
		$show_fields['gallery_game'] = 0;
		
		$output = '';
		
		foreach ( $fields as $field ) {
			if( $show_fields[ $field] ) {
				$output .= '<th>' . $labels[ $field ] . '</th>';
			}
		}
		
		return $output;
	
	} //End: build_schedule_header()
	
	//------------------------------------------------------------------------
	// BUILD_SCHEDULE_ROW
	// 	Builds the HTML for a single league schedule table row (game)
	//
	// ARGUMENTS:
	//	$game: the row's game (an mstw_lm_game object)
	// 	$atts: the display settings and shortcode arguments
	//
	// RETURNS
	//	HTML for a single league schedule table row
	//
	function build_schedule_row( $game, $atts ) {
		//mstw_log_msg( 'build_schedule_row:' );
		//mstw_log_msg( $atts );
		
		if ( !$game ) {
			//mstw_log_msg( 'build_schedule_row: No game provided' );
			return '';
		}
		
		$output = "<tr>\n";
		
		$fields      = $atts['schedule_order'];
		$show_fields = $atts['schedule_fields'];
		
		//
		// These fields are not shown in league schedules
		//
		$show_fields['opponent'] = 0;
		$show_fields['gallery_game'] = 0;
		
		foreach ( $fields as $field ) {
			if ( $show_fields[ $field ] ) {
				
				$output .= "<td>";
				
				switch ( $field ) {
					case 'date':
						$output .= mstw_lm_date_loc( $atts['date_format'], (int)get_post_meta( $game->ID, 'game_unix_dtg', true ) );	
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
						$output .= mstw_lm_get_game_location( $game, $atts );
						break;
						
					case 'time':
						$output .= mstw_lm_get_game_time_result( $game, $atts );
						break;
					
					default:
						break;
					
					
				} //End: switch ( $field ) {
				
				$output .= '</td>';
				
			} //End: if ( $show_fields[ $field ] ) {
			
		} //End: foreach ( $fields as $field ) {
	
		$output .= "</tr>\n";
		
		return $output;
		
		
	} //End: build_schedule_row( )
	
 } //End: MSTWLeagueScheduleClass