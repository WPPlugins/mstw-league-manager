<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-standings-table.php
 *	Contains the code for the MSTW League Manager standings table
 *		shortcode [mstw_league_standings]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2015 Mark O'Donnell (mark@shoalsummitsolutions.com)
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
// Add the shortcode handler, which will create the League's Game standings
// table on the user side. Handles the shortcode parameters, if there were 
// any, then calls mstw_lm_build_league_standings( ) to create the output
// 

add_shortcode( 'mstw_league_standings', 'mstw_lm_shortcode_handler' );

//-----------------------------------------------------------------------------
// MSTW_LM_BUILD_LEAGUE_STANDINGS
// 	Builds the standings table for the specified league & season 
// 	Callback for by mstw_league_standings shorcode. Called from 
//	mstw_lm_shortcode_handler after it has processed the arguments & settings
//
// ARGUMENTS
// 	$atts  - the plugin settings and shortcode arguments; the critical atts are 
//				league and season
//
// RETURNS
//	HTML for schedule table as a string
//
function mstw_lm_build_league_standings( $atts ) {
	//mstw_log_msg( 'in mstw_lm_build_league_standings ...' );
	//mstw_log_msg( 'attributes passed to function' );
	//mstw_log_msg( $atts );
	
	// Check that we have a league and a season set
	// This is not currently necessary, but it allows for multiple leagues 
	// in the future
	$league_slugs = explode( ',', $atts['league'] );
	//mstw_log_msg( '$league_slugs[0] = ' . $league_slugs[0] );

	// CHECK FOR LEAGUE IN THE ATTRIBUTES; HAVE TO HAVE ONE
	// This is checked by mstw_lm_shortcode_handler as well
	if ( !empty( $league_slugs[0] ) ) {
		//CSS tag will be the FIRST league slug specified
		$css_tag = '_' . $league_slugs[0];
	}
	else {
		//Have to have at least one league slug
		return "<h3 class='mstw-lm-user-msg'>" . __( 'League not specified.', 'mstw-league-manager' ) . '</h3>';
	}
	
	// Check that we have a season set; else default to first season for league
	if ( array_key_exists( 'season', $atts ) && !empty( $atts['season'] ) ) {
		$season_slug = $atts['season'];
	}
	else {
		// There should always be a season for a league
		//mstw_log_msg( "No season specified in shortcode. Trying to find season for league: $league_slug" );
		$seasons_array = mstw_lm_build_seasons_list( $league_slugs[0] );
		if ( $seasons_array ) {
			$season_slug = reset( $seasons_array );
			if ( false === $season_slug ) {
				return "<h3 class='mstw-lm-user-msg'>" . __( 'Found no seasons for league:', 'mstw-league-manager' ) . $league_slugs[0] . "</h3>";	
			}
		}
	}
	
	// Figure out order by to sort properly
	switch ( $atts['order_by'] ) {
		case 'points':
			$sort_key = 'points';
			$sort_order = "DESC";
			break;
		case 'rank':
			$sort_key = 'rank';
			$sort_order = "ASC";
			break;
		case 'name':
			// This one will require some thought
		case 'percent':
		default:
			$sort_key = 'percent';
			$sort_order = "DESC";
			break;
	}
						
	$args = array( 'numberposts'   => -1,
				   'post_type'     => 'mstw_lm_team',
				   'tax_query' => array( 
										array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slugs[0],
											),
										),
				   );
				   
	$teams_in_league = get_posts( $args );
	
	$team_slugs = array();
	
	foreach( $teams_in_league as $team ) {
		$team_slugs[] = $team->post_name;
	}
	
	//mstw_log_msg( "teams in league " . $league_slugs[0] . ":" );
	//mstw_log_msg( $team_slugs );
	
	$args = array( 'numberposts'    => -1,
				   'post_type'      => 'mstw_lm_record',
				   
				   'tax_query' => array( 
										array(
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slugs[0],
											),
										),
				   
				  'meta_query' => array(
										
										'relation' => 'AND',
										array( 'key'     => 'team_slug',
											   'value'   => $team_slugs,
											   'compare' => 'IN',
											 ),
											 
										array( 'key'     => 'season_slug',
											   'value'   => $season_slug,
											   'compare' => '=',
											 ),
										),
										
				   'orderby'        => 'meta_value_num',
				   'meta_key'       => $sort_key,
				   'order'          => $sort_order, 
				  ); 
				  
	//mstw_log_msg( 'League: ' );
	//mstw_log_msg( $league_slugs );
	
	//mstw_log_msg( 'Season: ' . $season_slug );
						
	$records = get_posts( $args );
	//mstw_log_msg( '$records: ' . count( $records ) );
	
	if ( $records ) {
		// Make the standings table
		// Starting with the table header
		$output = "<table class='mstw-lm-table mstw-lm-standings mstw-lm-standings_$league_slugs[0]'>\n"; 
		$output .= "<thead><tr>\n";
			$output .= mstw_lm_build_standings_header( $atts );
		$output .= "</tr></thead>\n";
		
		$output .= "<tbody>\n";
		
		// Loop through the leagues teams and build the table rows
		foreach( $records as $record ) {
			
			//$terms = wp_get_post_terms( $record->ID, 'mstw_lm_league' );
			//mstw_log_msg( "Terms for " . $record->post_name );
			//mstw_log_msg( $terms );
			
			$output .= mstw_lm_build_standings_row( $record, $atts, $league_slugs[0], $season_slug );
			
		}
	}
	else {
		return "<h3 class='mstw-lm-user-msg'>" . sprintf( __( 'No records found in league %s', 'mstw-league-manager' ), $league_slugs[0] ) . '.</h3>';	
	}
	
	$output .= "</tbody></table>\n";
	
	return $output;
	
} //End: mstw_lm_build_league_standings()

//-----------------------------------------------------------------------------
// mstw_lm_build_standings_header - returns HTML for standings table header
//
function mstw_lm_build_standings_header( $atts ) {
	//mstw_log_msg( "in mstw_lm_build_standings_header ..." );
	
	// return string
	$output = '';
	
	$fields      = $atts['standings_order'];
	$show_fields = $atts['standings_fields'];
	$labels      = $atts['standings_labels'];
	
	//mstw_log_msg( "Labels:" );
	//mstw_log_msg( $labels );
	
	foreach ( $fields as $field ) {
		if( $show_fields[ $field] ) {
			$output .= '<th>' . $labels[ $field ] . '</th>';
		}
	}
	
	return $output;
	
} //End: mstw_lm_build_standings_header

//-----------------------------------------------------------------------------
// mstw_lm_build_standings_row - returns HTML for one standings table row
//
function mstw_lm_build_standings_row( $record, $atts, $league_slug = null, $season_slug = null ) {
	//mstw_log_msg( "in mstw_lm_build_standings_row ..." );
	//mstw_log_msg( '$record:' );
	//mstw_log_msg( $record );
	
	if ( $record ) {
		$fields      = $atts['standings_order'];
		$show_fields = $atts['standings_fields'];
		
		//mstw_log_msg( '$fields:' );
		//mstw_log_msg( $fields );

		$output = '<tr>';
		
		foreach ( $fields as $field ) { 
			if( $show_fields[ $field ] ) {
				$output .= '<td>';
				switch ( $field ) {					
					case 'wins-losses':
						$wins = get_post_meta( $record->ID, 'wins', true );
						$losses = get_post_meta( $record->ID, 'losses', true );
						$output .= "$wins-$losses";
						break;
						
					case 'team':
						if ( $team_slug = get_post_meta( $record->ID, 'team_slug', true ) ) {
							//mstw_log_msg( '$team_slug: ' . $team_slug );
							$team = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
							//$team = get_post( $team_id, OBJECT, 'raw' );
							if( $team ) {
								if ( $atts['standings_show_logo'] ) {
									$output .= mstw_lm_build_team_logo( $team->post_name, 'small' );
								}
								$name_format = ( array_key_exists( 'standings_name_format', $atts ) ) 
												 ? $atts['standings_name_format']
												 : 'name';
								$team_link = ( array_key_exists( 'standings_team_link', $atts ) ) 
												 ? $atts['standings_team_link']
												 : 'none';
												 
								$output .= mstw_lm_get_team_name_from_team( $team,$name_format, $team_link, $league_slug, $season_slug );
								
							} // End if ( $team )
							else {
								$output .= __( 'No team object.', 'mstw-league-manager' );
							}
						} //End: if ( $team_id )
						else {
							$output .= __( "No team slug.", 'mstw-league-manager' );
						}
						break;
						
					case 'percent':
						$percent = get_post_meta( $record->ID, $field, true );
						$output .= number_format( (float)$percent, 3 );
						break;
						
					case 'next_game':
						//[post_name] => nhl_2015-16_anaheim-ducks
						$slugs = explode( '_', $record -> post_name );
						
						$next_game = mstw_lm_find_next_game( $slugs, current_time( 'timestamp' ) );
						
						$last_game = mstw_lm_find_last_game( $slugs, current_time( 'timestamp' ) );
						
						if ( null === $next_game ) {
							$output .= '---';
							
						} else {
							$next_game_html = '';
							
							$row_team = get_post_meta( $record->ID, 'team_slug', true );
							
							if ( $row_team == get_post_meta( $next_game -> ID, 'game_home_team', true ) ) {
								$next_game_html .= " vs ";
								$away_team_obj = get_page_by_path( get_post_meta( $next_game -> ID, 'game_away_team', true ), OBJECT, 'mstw_lm_team' );
								$away_team_short_name = get_post_meta( $away_team_obj -> ID, 'team_short_name', true );
								$next_game_html .= $away_team_short_name;
							} else {
								$next_game_html .= " @ ";
								$home_team_obj = get_page_by_path( get_post_meta( $next_game -> ID, 'game_home_team', true ), OBJECT, 'mstw_lm_team' );
								$home_team_short_name = get_post_meta( $home_team_obj -> ID, 'team_short_name', true );
								$next_game_html .= $home_team_short_name;
							}
							
							$next_game_html .= " " . mstw_lm_date_loc( 'D, M j', get_post_meta( $next_game -> ID, 'game_unix_dtg', true ) );
							
							if ( array_key_exists( 'standings_next_game_link', $atts ) ) {
								if ( 'game-page' == $atts['standings_next_game_link'] ) {
									$link = get_site_url( ) . '/lm_game/' . $next_game -> post_name;
									$next_game_html = "<a href='$link'>$next_game_html</a>";
								}
							}

							$output .= $next_game_html;
						}
						break;
					case 'last_game':
						//mstw_log_msg( "Building last game" );
						$slugs = explode( '_', $record -> post_name );
						
						$row_team = get_post_meta( $record->ID, 'team_slug', true );
						
						$last_game = mstw_lm_find_last_game( $slugs, current_time( 'timestamp' ) );
	
						if ( null === $last_game ) {
							$output .= '---';
							
						} else {
							$output .= mstw_lm_build_last_game( $row_team, $last_game );
							
						}
						break;
						
					default:
						//$field_tag = 'team_' . $field;
						//mstw_log_msg( 'field_tag = ' . $field_tag );
						$output .= get_post_meta( $record->ID, $field, true );
						break;
						
				}
				
				$output .= '</td>';
			}
		}
		$output . '</tr>';
	} //End: if ( $record_obj )
		
	else {
		$output = '';
		mstw_log_msg( 'No record found for: ' . $record->post_name );
	}
	
	return $output;
	
} //End: mstw_lm_build_standings_row( )

function mstw_lm_build_last_game( $row_team, $last_game = null ) {
	//mstw_log_msg( "mstw_lm_build_last_game:" );
	
	$ret_html = '';
	
	if ( null === $last_game ) {
		$ret_html .= '---';
		
	} else {
		//$row_team = get_post_meta( $record->ID, 'team_slug', true );
		
		$home_slug = get_post_meta( $last_game->ID, 'game_home_team', true );
		
		$away_slug = get_post_meta( $last_game->ID, 'game_away_team', true );
		
		//mstw_log_msg( "home_slug: $home_slug" );
		//mstw_log_msg( "away_slug: $away_slug" );
		
		$short_names = mstw_lm_build_team_short_names( $home_slug, $away_slug );
		
		$home_score = get_post_meta( $last_game->ID, 'game_home_score', true );
		
		$away_score = get_post_meta( $last_game->ID, 'game_away_score', true );
		
		if ( $row_team == $home_slug ) {
			//Row is for home team
			$ret_html = $short_names['away'] . " ";
			
			if ( $home_score != '' and $away_score != '' ) {
				if ( $home_score > $away_score ) {
					 $ret_html .= __( 'W', 'mstw-league-manager' );
					 
				} else if ( $away_score > $home_score ) {
					$ret_html .= __( 'L', 'mstw-league-manager' );
					
				} else {
					$ret_html .= __( 'T', 'mstw-league-manager' );
					
				}
				
				$ret_html .= " $home_score-$away_score";
			}
			
		} else {
			// Row is for away team
			$ret_html = $short_names['home'] . " ";
			
			if ( $home_score != '' and $away_score != '' ) {
				if ( $home_score > $away_score ) {
					 $ret_html .= __( 'L', 'mstw-league-manager' );
					 
				} else if ( $away_score > $home_score ) {
					$ret_html .= __( 'W', 'mstw-league-manager' );
					
				} else {
					$ret_html .= __( 'T', 'mstw-league-manager' );
					
				}
				
				$ret_html .= " $away_score-$home_score";
			}
			
		}
		
		//$ret_html = $short_names['away'] . '-' . $away_score . ' ' . $home_score . '-' . $short_names['home'];
		
		/*
		$home_score = get_post_meta( $record->ID, 'game_home_score', true );
		
		$away_score = get_post_meta( $record->ID, 'game_home_score', true );
		
		if ( $row_team == get_post_meta( $last_game -> ID, 'game_home_team', true ) ) {
			$next_game_html .= " vs ";
			$away_team_obj = get_page_by_path( get_post_meta( $next_game -> ID, 'game_away_team', true ), OBJECT, 'mstw_lm_team' );
			$away_team_short_name = get_post_meta( $away_team_obj -> ID, 'team_short_name', true );
			$next_game_html .= $away_team_short_name;
		} else {
			$next_game_html .= " @ ";
			$home_team_obj = get_page_by_path( get_post_meta( $next_game -> ID, 'game_home_team', true ), OBJECT, 'mstw_lm_team' );
			$home_team_short_name = get_post_meta( $home_team_obj -> ID, 'team_short_name', true );
			$next_game_html .= $home_team_short_name;
		}
		
		$next_game_html .= " " . mstw_lm_date_loc( 'D, M j', get_post_meta( $next_game -> ID, 'game_unix_dtg', true ) );
		
		if ( array_key_exists( 'standings_next_game_link', $atts ) ) {
			if ( 'game-page' == $atts['standings_next_game_link'] ) {
				$link = get_site_url( ) . '/lm_game/' . $next_game -> post_name;
				$next_game_html = "<a href='$link'>$next_game_html</a>";
			}
		}
		*/
	}
	
	return $ret_html;
	
} //End: mstw_lm_build_last_game( )

function mstw_lm_build_team_short_names( $home_slug, $away_slug ) {
	//mstw_log_msg( "mstw_lm_build_team_short_names:" );
	
	if ( $home_slug ) {
		$home_obj = get_page_by_path( $home_slug, OBJECT, 'mstw_lm_team' );
		if ( null !== $home_obj ) {
			$home_name = get_post_meta( $home_obj -> ID, 'team_short_name', true );
			
		} else {
			$home_name = __( 'HOME', 'mstw-league-manager' );
		}
	} else {
		$home_name = __( 'HOME', 'mstw-league-manager' );
	}
	
	if ( $away_slug ) {
		$away_obj = get_page_by_path( $away_slug, OBJECT, "mstw_lm_team" );
		if ( null !== $away_obj ) {
			$away_name = get_post_meta( $away_obj -> ID, 'team_short_name', true );
			
		} else {
			$away_name = __( 'AWAY', 'mstw-league-manager' );
		}
	} else {
		$away_name = __( 'AWAY', 'mstw-league-manager' );
	}
	
	return array( 'home' => $home_name,
				  'away' => $away_name, 
				);
						 
} //End: mstw_lm_build_team_short_names( )
