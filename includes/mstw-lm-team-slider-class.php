<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-team-schedule-slider-class.php
 *	Contains the class for the MSTW League Manager Team Schedule Slider
 *  shortcode [mstw_team_schedule_slider]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2016 Mark O'Donnell (mark@shoalsummitsolutions.com)
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. All rights 
 * 	reserved.
 *-------------------------------------------------------------------------*/

//---------------------------------------------------------------------------
// Add the shortcode handler, which will create the Team Schedule Slider
// on the user side. Handles the shortcode parameters, if there were 
// any, then calls mstw_lm_build_schedule_slider( ) to create the output
// 

class MSTW_TEAM_SCHEDULE_SLIDER {
	
	public function __construct( ) {
		add_filter( 'get_team_schedule_slider_instance', [$this, 'get_instance'] );
	}
	
	public function get_instance( ) {
		return $this; //return the object
	}
	
	//
	// Handles the shortcode inline arguments and merges them with options DB
	//
	public function team_schedule_slider_handler( $atts, $content = null, $shortcode ) {
		//mstw_log_msg( "MSTW_TEAM_SCHEDULE_SLIDER:team_schedule_slider_handler: shortcode= $shortcode " );
		//mstw_log_msg( 'shortcode: ' . $shortcode );
		
		$plugin_dir = dirname( plugin_dir_path( __FILE__ ) );
		

		// get the options set in the admin settings screen
		$args = mstw_lm_get_team_slider_defaults( );

		// then merge the parameters passed to the shortcode 
		$attribs = shortcode_atts( $args, $atts );
		
		//
		// Must have A VALID TEAM (SLUG)
		//
		//mstw_log_msg( "team slug: " . $attribs['team'] );
		
		if ( !array_key_exists( 'team', $attribs ) or '' == $attribs['team'] ) {
			//Have to have a teams slug
			return "<h3 class='mstw-lm-user-msg'>" . __( 'No team specified.', 'mstw-league-manager' ) . '</h3>';
		} /*else {
			$teams = explode( ',', $attribs['team'] );
			$team_obj = get_page_by_path( $attribs['team'], OBJECT, 'mstw_lm_team' );
			if ( null === $team_obj ) {
				return "<h3 class='mstw-lm-user-msg'>" . __( 'Team not found.', 'mstw-league-manager' ) . '</h3>';
			}
		}*/
		
		// If no school or conference or sport, set type to "all"
		// *** NOT CURRENTLY USED TYPE IS JUST ALL ***
		//
		if ( '' != $attribs['sport'] ) {	
			$type = 'sport';
			$attribs['type'] = 'sport';
		} 
		
		else if ( '' != $attribs['school'] ) {
			$type = 'school';
			$attribs['type'] = 'school';
			
		} else if ( '' != $attribs['conference'] ) {
			$type = 'conference';
			$attribs['type'] = 'conference';
			
		} else {
			$type = 'all';
			$attribs['type'] = 'all';
			
		}
		
		//mstw_log_msg( "Merged options/settings");
		//mstw_log_msg( $attribs );
		
		$games = $this -> build_games_list( $attribs, $type );
		//mstw_log_msg( "Number of games found: " . count( $games ) );
		
		if ( $games ) {
			return $this -> build_shortcode( $shortcode, $attribs, $games );
			
		} else {
			return ( '<h3 class="mstw-lm-user-msg">' . __( 'No games found for specified league(s) & season(s).', 'mstw-league-manager' ) . '</h3>' );
		}
		
		
	} //End: team_schedule_slider_handler( )
	
	//-----------------------------------------------------------------------------
	// build_games_list - Builds the list of games based on the slider atts 
	// 	Called from team_schedule_slider_handler( ) after it has processed 
	//	the arguments & settings
	//
	// ARGUMENTS
	// 	$atts  - the plugin settings and shortcode arguments
	//	$type - the type/format of the slider (don't know if we need this)
	//
	// RETURNS
	//	A list of games based on the provided atts, or null if there's a problem
	//
	function build_games_list( $atts, $type = 'all' ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_games_list:' );
		//mstw_log_msg( '$atts:' );
		//mstw_log_msg( $atts );
		
		//
		// Can have multiple teams (why? JV & Varsity? Boys & Girls?)
		//
		$team_slugs = explode( ',', $atts['team'] );
		if ( !$team_slugs ) {
			//Have to have at least one league slug
			return "<h3 class='mstw-lm-user-msg'>" . __( 'No team specified.', 'mstw-league-manager' ) . '</h3>';
		} 
		
		//		
		// Can have multiple leagues, but must have one
		//
		$league_slugs = explode( ',', $atts['league'] );
		//mstw_log_msg( 'league slug = ' . $league_slugs[0] );
		
		if ( !$league_slugs ) {
			//Have to have at least one league slug
			return "<h3 class='mstw-lm-user-msg'>" . __( 'No league specified.', 'mstw-league-manager' ) . '</h3>';
		} 
		
		//		
		// Have to have a season. If not, default to current year.
		//
		$season_slugs = explode( ',', $atts['season'] );
		//mstw_log_msg( '$season_slugs:' ) ;
		//mstw_log_msg( $season_slugs );
		
		if ( empty( $season_slugs ) ) {
			$season_slugs[0] = date( 'Y' );
		}
		//$season = empty( $atts['season'] ) ? date( 'Y' ) : $atts['season'];
		
		//
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
		
		if ( 'now' == $atts['first_dtg'] ) {
			$first_dtg = current_time( 'timestamp' );
			
		} else {
			$first_dtg = strtotime( $atts['first_dtg'] );
			
		} 

		// strtotime returned -1 on error before version 5.1.0
		if ( false === $first_dtg || -1 == $first_dtg || $first_dtg < 0 ) {
			$first_dtg = 1;
		}
		
		$base_dtg  = $first_dtg;  // used for layout of intervals maybe
		
		if ( $interval_days = $atts['interval_days'] ) {
			if ( is_numeric( $interval_days ) ) {
				$first_dtg = $base_dtg - $interval_days * DAY_IN_SECONDS;
				$last_dtg  = $base_dtg + $interval_days * DAY_IN_SECONDS;
				$first_dtg = ( $first_dtg < 1 ) ? 1 : $first_dtg;
				$last_dtg  = ( $last_dtg > PHP_INT_MAX ) ? PHP_INT_MAX : $last_dtg;
				
			} else {
				$last_dtg = PHP_INT_MAX;
				mstw_log_msg( "MSTW_TEAM_SCHEDULE_SLIDER.build_games_list: interval_days is not numeric." );
				
			}	
			
		} else {
			$last_dtg = strtotime( $atts['last_dtg'] );
			if ( false === $last_dtg || $last_dtg <= $first_dtg ) {
				$last_dtg = PHP_INT_MAX;
			}
		}
			
		/*
		 * TIME ARGS DEBUG CODE
		 *
		if ( isset( $base_dtg ) ) {
			mstw_log_msg( "base_dtg: $base_dtg" );
			mstw_log_msg( "base_dtg: " . date( 'Y-m-d', $base_dtg ) );
		}
		if ( isset( $first_dtg ) ) {
			mstw_log_msg( "first_dtg: $first_dtg" );
			mstw_log_msg( "first_dtg: " . date( 'Y-m-d', $first_dtg ) );
		}
		if ( isset( $last_dtg ) ) {
			mstw_log_msg( "last_timestamp: $last_dtg" );
			mstw_log_msg( "last_dtg: " . date( 'Y-m-d', $last_dtg ) );
		}
		if ( isset( $interval_days ) ) {
			mstw_log_msg( "interval_days: $interval_days" );
		}
		*/
				
		// Right now, this ain't going to change; 
		// it was for last_dtg == now in MSTW Schedules & Scoreboards
		$sort_order = 'ASC';
		
		// Allow non-league games to appear on schedule
		$show_nonleague = array_key_exists( 'show_nonleague', $atts ) ? $atts['show_nonleague'] : 0;
		
		if ( $show_nonleague ) {
			//mstw_log_msg( "Show nonleague games" );
			$meta_query = array( 
							//'relation'       => 'AND',
							array( 'key'     => 'game_season',
								   'value'   => $season_slugs, //$season_slugs[0],
								   'compare' => 'IN', //'=',
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
								   'value'   => array( $first_dtg,
													   $last_dtg ),
								   'type'    => 'NUMERIC',
								   'compare' => 'BETWEEN'
								 )	 
							);										
		}
		else {
			//mstw_log_msg( "Show league games ONLY" );
			$meta_query = array( 
						'relation' => 'AND',
						array( 
							'key'     => 'game_season',
							'value'   => $season_slugs,  //$season_slugs[0],
							'compare' => 'IN', 
							 ),
						array( 
							'key'     => 'game_nonleague',
							'value'   => 0,
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
							'value'   => array( $first_dtg,
												$last_dtg ),
							'type'    => 'NUMERIC',
							'compare' => 'BETWEEN'
						   )	
					);
		}
		
		//mstw_log_msg( $meta_query );
		//mstw_log_msg( 'PHP_INT_MAX = '. PHP_INT_MAX );
		//mstw_log_msg( 'PHP_INT_MAX: ' . date( 'Y-m-d', PHP_INT_MAX ) );
		//mstw_log_msg( 'first_dtg: ' . date( 'Y-m-d', $first_dtg ) );
		//mstw_log_msg( 'last_dtg: ' . date( 'Y-m-d', $last_dtg ) );
		
		// Get the games posts
		$games = get_posts( 
					array( 'numberposts' => 100, //limit for computer resources
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
						   'orderby'     => 'meta_value', 
						   'meta_key'    => 'game_unix_dtg',
						   'orderby'     => 'meta_value_num',
						   'order'       => $sort_order 
						   ) 
					);	

		mstw_log_msg( "Games found: " . count( $games ) );
		
		return $games;

	} //End: build_games_list( )	
	
	//-----------------------------------------------------------------------------
	// build_shortcode - processes arguments and returns shortcode HTML
	// 	
	// ARGUMENTS
	//	$shortcode - the shortcode being called
	// 	$atts  - the plugin settings and shortcode arguments
	//	$games - the list of games already built based on the shortcode args
	//
	// RETURNS
	//	HTML for the schedule slider as a string
	//
	function build_shortcode( $shortcode, $atts, $games ) { 
		//mstw_log_msg( "MSTW_TEAM_SCHEDULE_SLIDER.build_shortcode: $shortcode" );
		
		//mstw_log_msg( 'attributes passed to function' );
		//mstw_log_msg( $atts );
		
		// This has already been checked, but what the heck
		if ( !$games ) {
			return ( '<h3 class="mstw-lm-user-msg">' . __( 'No games found for specified league(s) & season(s).', 'mstw-league-manager' ) . '</h3>' );
		}
		
		$nbr_of_games = count( $games );
			
		// # games to show comes from the shortcode atts; show 3 by default
		$games_to_show = $atts['games_to_show'];
		
		$games_to_show = ( '' == $games_to_show or -1 == $games_to_show ) ?
			3 : $games_to_show;
		
		// find the next game
		
		$next_game = $this -> get_next_game( $games, current_time( 'timestamp' ) );
		
		//mstw_log_msg( "Next Game: " );
		//mstw_log_msg( $next_game );
		//mstw_log_msg( "Home: " );
		//mstw_log_msg( get_post_meta( $next_game['id'] ) );
		//mstw_log_msg( "Away: " . get_post_meta( $next_game['id'], 'home_team' true );
		
		// Only for testing 
		//$next_game = $this -> get_next_game( $games, strtotime( '2016-04-06 10:00' ) );
		
		$next_game_number = $next_game['number'];
		$next_game_id     = $next_game['id'];
		
		if ( -2 == $next_game_id ) {
			return ( '<h3 class="mstw-lm-user-msg">' . __( 'No games found for specified league(s) & season(s).', 'mstw-league-manager' ) . '</h3>' );
		} 
		else if ( -1 == $next_game_id ) {
			// Current time is past last game time, so show as late as possible
			$next_game_number = $nbr_of_games - $games_to_show;
		}
		
		//Ya never know when there's only 2 games on a schedule
		$next_game_number = max( 0, min( $next_game_number, $nbr_of_games - $games_to_show ) );
	
		$game_number = $next_game_number + 1;
		
		//
		// Build the slider HTML
		//
		if ( 'mstw_team_schedule_slider' == $shortcode ) {
			$output = $this -> build_slider_html( $games, $games_to_show, $game_number, $atts );
			
		}
		
		return $output;
		
	} //End: build_shortcode( );
	
	//-----------------------------------------------------------------------------
	// build_slider_html - returns the team schedule slider HTML
	// 	
	// ARGUMENTS
	// 	$games  - list of games (objects/CPTs)
	//	$games_to_show - number of games shown in one "view width"
	//  $game_number - number of game in $games that goes furthest right (visible)
	//  $atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	$output - html for the slider
	//
	function build_slider_html( $games, $games_to_show, $game_number, $atts ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_slider_html:' );
		//mstw_log_msg( 'games:' );
		//mstw_log_msg( $games );
		//mstw_log_msg( 'atts:' );
		//mstw_log_msg( $atts );
		//mstw_log_msg( "games_to_show: $games_to_show" );
		//mstw_log_msg( "game_number: $game_number" );
		
		// This has already been error checked
		$league_slugs = explode( ',', $atts['league'] );
		$css_tag = '_' . $league_slugs[0];
		
		// HAS THIS BEEN DONE TO CREATE $GAME_NUMBER *BEFORE* THIS FUNCTION IS CALLED??
		// Figure out the first game visible on the right
		// Already checked that we have games in $games
		$nbr_of_games = sizeof( $games );
		
		//mstw_log_msg( "Orig game_number: $game_number" );
		//mstw_log_msg( "nbr_of_games: $nbr_of_games" );
		//mstw_log_msg( "games_to_show: $games_to_show" );
		
		$game_number = min( $game_number, $nbr_of_games - $games_to_show + 1 );
		
		//mstw_log_msg( "New game_number: $game_number" );
		
		// NEED TO CALCULATE AND SET THE LEFT OFFSET FROM $GAME_NUMBER!!
		
		// calculate widths from $games_to_show
		//mstw_log_msg( "games_to_show: $games_to_show" );
		
		$game_block_width = 187; //THIS IS TOO WIDE
		 
		$view_width = $games_to_show * $game_block_width;
		//mstw_log_msg( "view_width= $view_width " );
		
		//view_width + 2 arrow controls + one extra game pad
		$container_width = $view_width + 40 + 5;
		//mstw_log_msg( "container_width= $container_width " );
		
		$schedule_ticker_offset = ( $game_number > 0 ? (-1) * ( $game_number - 1 ) * $game_block_width : 0) . 'px';
		
		//mstw_log_msg( "schedule_ticker_offset: $schedule_ticker_offset" );
		
		//container for entire slider
		$output = "\n<div class='lmts-container' id='lmts-container$css_tag' style='width: {$container_width}px;' >\n";
		
			//slider header
			if( $atts['show_header'] ) {
				$output .= "<div class='lmts-header' id='lmts-header$css_tag'>\n";
				$output .= $this -> build_ticker_header( $atts );
				$output .= "</div> <!-- .lmts-header --> \n";
			}
			// holds next and prev buttons and slider content (game blocks)
			$output .= "<div class='lmts-holder'>\n";
			
				$output .= "<div class='lmts-prev' id='lmts-prev$css_tag'>\n";
				$output .= "</div> <!-- .lmts-prev --> \n";

				$output .= "<div class='lmts-viewport lmts-viewport$css_tag' style='width: {$view_width}px;'>\n";
				// contains the slider content (game blocks)
				  $output .= "<div class='lmts-content' id='lmts-content$css_tag'>\n";
				    $output  .= "<ul style='left: {$schedule_ticker_offset}'>\n";

				    foreach( $games as $game ) {
					  //mstw_log_msg( $game );
					
					  $game_status = $this -> get_game_status( $game );
					  $output .= "<li class=lmts-list-item>\n";
					
					  $output .= $this -> build_ticker_game_header( $atts, $game, $game_status );
					
					  $output .= $this -> build_ticker_sport_line( $atts, $game );
					
					  $output .= $this -> build_ticker_team_line( 'away', $game, $game_status, $atts );
					
					  $output .= $this -> build_ticker_team_line( 'home', $game, $game_status, $atts );
					
					  $output .= "</li>\n";
				
				    } 
		
				    $output  .= "</ul>\n";
				  $output .= "</div> <!-- .lmts-content --> \n";
				
				$output .= "</div> <!-- .lmts-viewport --> \n";
				
				$output .= "<div class='lmts-next' id='lmts-next$css_tag'>\n";
				$output .= "</div> <!-- .lmts-next --> \n";
				
			
			$output .= "</div> <!-- .lmts-holder --> \n";
		
		$output .= "</div> <!-- .lmts-container --> \n";
		
		return $output;
		
	} //End: build_slider_html
	
	//---------------------------------------------------------------------------
	// build_ticker_header
	// 	Builds the league schedule slider header 
	//
	// ARGUMENTS:
	//	$atts - the combined defaults, settings, and shortcode atts
	//
	// RETURNS
	//	Schedule slider content as an HTML string
	//

	function build_ticker_header( $atts ) {
		//mstw_log_msg( "build_ticker_header:" );
		
		$ret = '';
		
		if( $atts['show_header'] ) {
			$ret .= "<div class=lmts-title>\n";
				$ret .= $atts['title'];	
			$ret .= "</div> <!-- .lmts-title -->\n";
			
			$ret .= "<div class=lmts-link>\n";
				if( '' != $atts['link_label'] ) {
					if( '' != $atts['link'] ) {
						$ret .= "<a href=" . $atts['link'] . " target='_blank'>";
						$ret .= $atts['link_label'] . '</a>';
					}
					else {
						$ret .= $atts['link_label'];
					}
				}
			$ret .= "</div> <!-- .lmts-link -->\n"; 
			
			if( $atts['show_message'] ) {
				if ( '' == $atts['message'] ) {
					$msg = date( $atts['msg_date_format'], current_time( 'timestamp' ) );
				}
				else {
					$msg = $atts['message'];
				}
				$ret .= "<div class=lmts-message>$msg</div>";
			}
			
		}
		
		return $ret;
		
	} //End: build_ticker_header()
	
	
	//---------------------------------------------------------------------------
	// build_ticker_game_header
	// 	Builds the header for the league schedule slider game block 
	//
	// ARGUMENTS:
	//	$atts - the combined defaults, settings, and shortcode atts
	//  $game - a League Manager game object ( lm_game CPT )
	//	$game_status - array of game status elements ( see get_game_status( ) )
	//
	// RETURNS
	//	Schedule slider content as an HTML string
	//
	function build_ticker_game_header( $atts, $game, $game_status ) {
		//mstw_log_msg( "build_ticker_game_header:" );
		
		$ret = "<div class=lmts-game-header> \n";
		$ret .= "<p class='lmts-status'>\n";
		
		// If game is final => show "FINAL"
		// If game is in progress (not final but period set) => show period & time
		// If game has not started
		//		if today, show time
		//		else, show date
		//
		if( 'is_final' == $game_status['status'] ) {
			$ret .= __( 'Final', 'mstw-league-manager' );
		
		} else if ( 'in_progress' == $game_status['status'] ) {
			$ret .= $this -> numeral_to_ordinal( $game_status['period'] ) . '  ' . $game_status['time_remaining'];
			
		} else if ( 'not_started' == $game_status['status'] ) {
			if ( date ( 'Ymd', current_time( 'timestamp' ) ) == date ( 'Ymd', $game_status['start_dtg'] ) ) {
				$ret .= date( $atts['time_format'], $game_status['start_dtg'] );	
			} else {
				$ret .= date( $atts['date_format'], $game_status['start_dtg'] );	
			}	
		}
			
		$ret .= "</p>\n";
		$ret .= "</div><!--.lmts-game-header-->\n";		
	
		return $ret;
		
	} //End: build_ticker_game_header()
	
	//---------------------------------------------------------------------------
	// build_ticker_sport_line
	// 	Builds the header for the league schedule slider game block 
	//
	// ARGUMENTS:
	//	$atts - the combined defaults, settings, and shortcode atts
	//  $game - a League Manager game object ( lm_game CPT )
	//
	// RETURNS
	//	Schedule slider content as an HTML string
	//

	function build_ticker_sport_line( $atts, $game ) {
		//mstw_log_msg( "build_ticker_sport_line:" );
		
		$ret = '';
		
		if ( $atts['show_sport'] ) {
			$ret .= "<div class='lmts-sport'>\n";
			//$ret .= "<p class='lmts-sport'>\n";
				$sport_slug = $this -> build_game_sport( $game, false );
				
				$abbrevs = mstw_lm_get_sports_abbrevs( );
				
				$abbrev = ( array_key_exists( $sport_slug, $abbrevs ) ) ? $abbrevs[$sport_slug] : '' ;
				
				$ret .= $abbrev;
			//$ret .= "</p>\n";
			$ret .= "</div>\n";
		}

		return $ret;
		
	} //End: build_ticker_sport_line( )
	
	//-----------------------------------------------------------------------------
	// build_ticker_team_line - returns the HTML for a slider team line
	// 	
	// ARGUMENTS
	// 	$team - team line identifier home | away
	//	$game  - a game object (mstw_lm_game CPT)
	//	$game_status - array of status_fields (see get_game_status() )
	//	$atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_ticker_team_line( $team = 'home', $game, $status, $atts ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_ticker_team_line:' );
		
		$winner_css = ( $team == $status['is_winner'] ) ? 'winner' : '';
		
		$team_slug = get_post_meta( $game -> ID, "game_{$team}_team", true );
			
		$output = "<div class='team team_$team $winner_css'>";
		
		// show_logos = 0 | 1
		// name_format = short | hide
		
		if ( $atts['show_logos'] ) {
			$output .= mstw_lm_build_team_logo( $team_slug, 'small' );
		}
		
		if ( 'short' == $atts['name_format'] ) {
			$output .= "<p class='team-name'>";
			$output .= mstw_lm_get_team_name( $game, $team, $atts, 'short' );
			$output .= "</p>\n";
		}
		
		if ( 'is_final' == $status['status'] || 'in_progress' == $status['status'] ) {
			$output .= "<p class='team-score'>";
			$output .= $status[ $team . '_score' ];
			$output .= "</p>\n";
			
		}
		
		$output .= "</div> <!--End: .team -->";
		
		return $output;
		
	} //End: build_ticker_team_line( )
	
	//-----------------------------------------------------------------------------
	// build_game_block - returns the next game in $games after $time
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//	$atts - shortcode arguments (combined with settings)
	//	$css_tag - convenience ( _first-league-slug )
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_game_block( $game, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_game_block:' );
		//mstw_log_msg( 'Game ID: ' . $game -> ID );
		
		// have to match options with the mstw_lm_get_game_location( ) function
		// str_replace( '-', '_', $atts['location_format'])
		
		$output = "<div class='game-block game_block$css_tag'>";
		
		  $output .= $this -> build_date_line( $game, $atts, $css_tag );
		  $output .= $this -> build_sport_line( $game, $atts, $css_tag );
		  $output .= $this -> build_matchup_block( $game, $atts, $css_tag );
		  $output .= $this -> build_venue_line( $game, $atts, $css_tag );
		  //$output .= $this -> build_time_status_line( $game, $atts, $css_tag );
		
		$output .= "</div>";
		
		return $output;
		
	} //End: build_game_block( )
	
	//-----------------------------------------------------------------------------
	// build_date_line - returns the HTML for a slider game block's date line
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//	$atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_date_line( $game, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_date_line:' );
		
		// $atts should always have a date_format field, but ...
		$date_format = ( array_key_exists( 'date_format', $atts ) ) ? $atts['date_format'] : 'Y-m-d';
		
		$output = "<div class='date date$css_tag lm-pad'>";
		
		$output .= mstw_date_loc( $date_format, (int)get_post_meta( $game->ID, 'game_unix_dtg', true ) );
		$output .= "</div> <!--end .date-->\n";
		
		//mstw_log_msg( "returning $output" );
		
		return $output;
	} //End: build_date_line( ) 
	
	//-----------------------------------------------------------------------------
	// build_sport_line - returns the HTML for a slider game block's sport line
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//	$atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_sport_line( $game, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_sport_line:' );
		
		$output = '';
		if ( $atts['show_sport'] ) {
			$output .= "<div class='game-sport game-sport$css_tag lm-pad'>";	
				$output .= $this -> build_game_sport( $game );				
			$output .= "</div> <!--end .game-sport -->\n";
		}
		
		return $output;
		
	} //End: build_sport_line( )
	
	function build_game_sport ( $game, $name_or_slug = true ) {
		$league_slug = get_post_meta( $game -> ID, 'game_league', true );
		$sport_name = mstw_lm_get_league_sport( $league_slug, $name_or_slug );
		
		return $sport_name;
		
	} //End: build_game_sport( )
	
	//-----------------------------------------------------------------------------
	// build_matchup_block - returns the HTML for a slider game block's team lines
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//	$atts - shortcode arguments (combined with settings)
	//  $css_tag - css tag to apply to div's ( with leading '_' )
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_matchup_block( $game, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_matchup_block:' );
		
		$status = $this -> get_game_status( $game );
		//mstw_log_msg( "Game Status:" );
		//mstw_log_msg( $status );
		
		$output = "<div class='matchup matchup$css_tag'>";
		// Build the away line
		$output .= $this -> build_team_line( 'away', $game, $status, $atts, $css_tag );
		
		$output .= $this -> build_time_status_line( $status, $atts, $css_tag );
		
		// Build the home line
		$output .= $this -> build_team_line( 'home', $game, $status, $atts, $css_tag );
		
		$output .= "</div> <!--end .matchup -->\n";
		
		return $output;
		
	} //End: build_matchup_block( )
	
	//-----------------------------------------------------------------------------
	// build_team_line - returns the HTML for a slider game block's team lines
	// 	
	// ARGUMENTS
	// 	$team - team line identifier home | away
	//	$game  - a game object (mstw_lm_game CPT)
	//	$game_status - array of status_fields (see get_game_status() )
	//	$atts - shortcode arguments (combined with settings)
	//  $css_tag - css tag to apply to div's ( with leading '_' )
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_team_line( $team = 'home', $game, $status, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_team_line:' );
		
		$winner_css = ( $team == $status['is_winner'] ) ? 'winner' : '';
			
		$output = "<div class='team team_$team team$css_tag $winner_css'>";
		
		//$output .= "<div class='team-name'>\n";
		
		$team_slug = get_post_meta( $game -> ID, "game_{$team}_team", true );
		
		if ( $atts['show_logos'] ) {
			//get the logo
			$logo = mstw_lm_build_team_logo( $team_slug, 'small' );
		} else {
			$logo = '';
		}
		
		if ( 'hide' == $atts['name_format'] ) {
			$name == '';
			
		} else {
			// Want to pull the format from the $atts eventually
			$name = mstw_lm_get_team_name( $game, $team, $atts, 'name' ); 
		}
		
		//$output .= $logo . $name;
		
		//$output .= "</div>\n";
		
		if ( '' != $score = get_post_meta( $game -> ID, "game_{$team}_score", true ) ) {
			//$output .= "<div class='score'>$score</div>";
			$score = "<span class='score'>$score</span>";
			
		} else {
			//$output .= "<div class='score'></div>";
			//$output .= "<div class='score'></div>";
			$score = "";
			
		}
		
		$output .= $logo . $name . $score;
		
		$output .= "</div> <!--end .team -->\n";
		
		return $output;
		
	} //End: build_team_line( )
	
	//-----------------------------------------------------------------------------
	// get_game_status - returns the game status as an array
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//
	// RETURNS
	//	$status array   'status' => is_final | not_started | in_progress
	//					'start_time' => PHP timestamp
	//					'time_remaining' => string
	//					'period' => 
	//					'home_score' => 
	//					'away_score' =>
	//
	function get_game_status( $game ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.get_game_status:' );
		
		if ( get_post_meta( $game -> ID, 'game_is_final', true ) ) {
			$status = 'is_final';
			
		} else if ( '' != get_post_meta( $game -> ID, 'game_period', true ) ) {
			$status = 'in_progress';
			
		} else {
			$status = 'not_started';
			
		}
		
		$home_score = get_post_meta( $game -> ID, 'game_home_score', true );
		$away_score = get_post_meta( $game -> ID, 'game_away_score', true );
		
		if ( 'is_final' == $status ) {
			if ( (float)$home_score > (float)$away_score ) {
				$is_winner = 'home';
			} else if ( (float)$home_score < (float)$away_score ) {
				$is_winner = 'away';
			} else {
				$is_winner = 'tie';
			}	
		} else {
			$is_winner = 'none';
		}
		
		$ret_array = array( 'status'         => $status,
		
							'time_is_tba'    => get_post_meta( $game -> ID, 'game_is_tba', true ),
		
							'start_dtg'      => get_post_meta( $game -> ID, 'game_unix_dtg', true ),
							
							'time_remaining' => get_post_meta( $game -> ID, 'game_time_remaining', true ),
							
							'period'         => get_post_meta( $game -> ID, 'game_period', true ),
							
							'home_score'     => $home_score,
							
							'away_score'     => $away_score,
							
							'is_winner'      => $is_winner,
							
						  );
		
		return $ret_array;
		
	} //End: get_game_status( )
	
	//-----------------------------------------------------------------------------
	// build_venue_line - returns the HTML for a slider game block's venue line
	// 	
	// ARGUMENTS
	// 	$game  - a game object (mstw_lm_game CPT)
	//	$atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_venue_line( $game, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_venue_line:' );
		
		$output = "<div class='location location$css_tag lm-pad'>\n";
		  $output .= mstw_lm_get_game_location( $game, $atts );  
		$output .= "</div> <!--end .location-->\n";
		
		return $output;
		
	} //End: build_venue_line( )
	
	//-----------------------------------------------------------------------------
	// build_time_status_line - returns the HTML for a slider game block's time/result line
	// 	
	// ARGUMENTS
	// 	$game_status  - array of game status fields (See get_game_status().)
	//	$atts - shortcode arguments (combined with settings)
	//	$css_tag - tag to identify line/fields (with leading '_')
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_time_status_line( $game_status, $atts, $css_tag ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_time_status_line:' );
		
		$time_format = ( array_key_exists( 'time_format', $atts ) ) ? $atts['time_format'] : 'H:i';
		
		$output = "<div class='time-status time-status$css_tag lm-pad'>\n";
		
		if ( 'is_final' == $game_status['status'] ) {
			$output .= __( 'FINAL', 'mstw-league-manager' );
			
		} else if ( 'in_progress' == $game_status['status'] ) {
			$title = __( 'Period', 'mstw-league-manager' );
			$period = $game_status['period'];
			$output .= "$title: $period";
			
			if ( '' != $game_status['time_remaining'] ) {
				$title = __( 'Time', 'mstw-league-manager' );
				$time = $game_status['time_remaining'];
				$output .= " $title: $time"; 
			}
			
		} else {
			// game has not started, show time
			$output .= date( $time_format, $game_status['start_dtg'] );
			
		}
		
		$output .= "</div> <!--end .time-status -->\n";
		
		return $output;
		
	} //End: build_time_status_line( )
	
	//-----------------------------------------------------------------------------
	// build_scroll_controls - returns the HTML for the slider scroll controls
	// 	
	// ARGUMENTS
	// 	$game_status  - array of game status fields (See get_game_status().)
	//	$atts - shortcode arguments (combined with settings)
	//	$css_tag - tag to identify line/fields (with leading '_')
	//
	// RETURNS
	//	HTML for the game block
	//
	function build_scroll_controls( $css_tag, $slider_view_height ) {
		//mstw_log_msg( "MSTW_TEAM_SCHEDULE_SLIDER.build_scroll_controls:")
	
		$output =  "<div class='lm-clear'></div>\n
					<div class='lm-slider-right-arrow' id='lm-slider-right-arrow{$css_tag}' style='height:$slider_view_height; line-height:$slider_view_height;'>
					  &rsaquo;
					</div>\n
					<div class='lm-slider-left-arrow' id='lm-slider-left-arrow{$css_tag}'  style='height:$slider_view_height; line-height:$slider_view_height;'>
					  &lsaquo;
					</div>\n";
		
		return $output;

	} //End: build_scroll_controls( )
	
	//-----------------------------------------------------------------------------
	// get_next_game - returns the next game in $games after $time
	// 	
	// ARGUMENTS
	// 	$games  - list of game (objects/CPTs)
	//	$dtg - "current" time as a PHP timestamp
	//
	// RETURNS
	//	$next_game[] array:
	//	  id 
	//		WP ID for next game if found, otherwise
	//		-1 if no game was found with a start DTG after the dtg argument
	//		-2 if $games is empty
	//	  number
	//		position of next game in $games list [ 0 to sizeof($games)-1 ] 
	//		(if found, otherwise see id)
	//	  dtg
	//		PHP time stamp (date-time-group) for next game 
	//		(if found, otherwise see id)
	//
	function get_next_game( $games, $dtg = null ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.get_next_game:' );
		//mstw_log_msg( $games );
		
		$dtg = ( null === $dtg ) ? current_time( 'timestamp' ) : $dtg;
		
		// No game has been found (yet)
		$next_game = array( 'id' 	  => -1,
						    'number'	  => -1,
						    'dtg'	  => -1,
						  ); 

		// loop thru the game posts to find the first game in the future
		$next_game_number = 0;
		
		if ( $games ) {
			foreach( $games as $game ) {
				// Find first game time after the current time, and (just to be sure) has no result
				if ( get_post_meta( $game->ID, 'game_unix_dtg', true ) > $dtg ) {
					// Ding, ding, ding, we have a winner
					// Grab the data needed and stop looping through the games
					$next_game['id']     = $game->ID;
					$next_game['number'] = $next_game_number;
					$next_game['dtg']    = get_post_meta( $game->ID, 'game_unix_dtg', true );
					break;
				}
				$next_game_number++;
			}
		} else {  
			$next_game['id'] =  -2; 
		}
		
		return $next_game;
		
	} //End: get_next_game( )
	
	//-----------------------------------------------------------------------------
	// numeral_to_ordinal - Converts number to the corresponding ordinal 
	//
	//	ARGUMENTS: 
	//		$nbr - numeral to convert (should be a positive integer)
	//
	//	RETURNS:
	//		Corresponding ordinal as a string
	//
	function numeral_to_ordinal( $nbr ) {
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
		
	} //End: numeral_to_ordinal( )
	
	//-----------------------------------------------------------------------------
	// build_slider_html_old - returns the league schedule slider HTML
	// 	
	// ARGUMENTS
	// 	$games  - list of games (objects/CPTs)
	//	$games_to_show - number of games shown in one "slider width"
	//  $game_number - number of game in $games that goes furthest right (visible)
	//  $atts - shortcode arguments (combined with settings)
	//
	// RETURNS
	//	$output - html for the slider
	//
	function build_slider_html_old( $games, $games_to_show, $game_number, $atts ) {
		//mstw_log_msg( 'MSTW_TEAM_SCHEDULE_SLIDER.build_slider_html:' );
		//mstw_log_msg( $games );
		
		// This has already been error checked
		$league_slugs = explode( ',', $atts['league'] );
		$css_tag = '_' . $league_slugs[0];
		
		$slider_title = $atts['title'];
		$show_slider_title = ( '' == $slider_title ) ? 0 : 1;
		
		if ( '' == $atts['link'] or '' == $atts['link_label'] ) {
			$show_slider_schedule_link = 0;
		}
		else {
			$show_slider_schedule_link = 1;
			$slider_link = $atts['link'];
			$slider_link_label = $atts['link_label'];
		}
		
		$game_block_width = 167;
		$slider_view_width = 584; //DEFAULT. CALCULATED BELOW BASED ON GAMES_TO_SHOW

		$nbr_of_games = sizeof( $games );
		
		$games_to_show = ( '' == $games_to_show or -1 == $games_to_show ) ? 3 : $games_to_show;
		
		$slider_view_width = $games_to_show * $game_block_width + 20 . 'px';
		
		$slider_view_height = '200px'; //'250px'; //( $atts['show_logos'] == 'name-only' ? '197px' : '250px' );
		
		// this is the entire width the 10 accounts for the size of the right arrow bar
		$schedule_slider_width = $nbr_of_games * $game_block_width + 10 . 'px';
		
		//mstw_log_msg( "game_number: $game_number" );
		//mstw_log_msg( '$nbr_of_games - $games_to_show + 1: ' .  $nbr_of_games - $games_to_show + 1 );
		
		
		$game_number = min( $game_number, $nbr_of_games - $games_to_show + 1 );
		
		//mstw_log_msg( "new game_number: $game_number" );
		
		$schedule_slider_offset = ( $game_number > 0 ? (-1) * ( $game_number - 1 ) * $game_block_width : 0) . 'px';
		
		//mstw_log_msg( "schedule_slider_offset: $schedule_slider_offset" );
		
		$output = "
			<div class='lm-slider-area lm-slider-area$css_tag' style='width:$slider_view_width;'>
			  <div class='lm-slider lm-one-edge-shadow lm-one-edge-shadow$css_tag'>
			    <div class='border border$css_tag'>
				<div class='box box$css_tag'>";
				  
				  if ( $show_slider_title ) { 
					$output .=  "<div class='title titlecss_tag'>\n";
					$output .= $slider_title;  
					$output .= "</div> <!-- .title -->\n";
						
					if ( $show_slider_schedule_link ) { 
						$output .="<div class='full-schedule-link full-schedule-link$css_tag'>\n";
						$output .= "<a href='$slider_link' target='_blank' >$slider_link_label</a>\n";
						$output .= "</div>  <!--.full-schedule-link --> \n";
					} 

					$output .= "	
					<div class='lm-clear'></div>
					<div class='lm-divider lm-divider$css_tag'></div>\n";
		
			      } //End: if ( show_slider_title 
				  
				  
		$output .= "<div class='content content$css_tag'>				
				  <div class='lm-schedule-slider lm-schedule-slider$css_tag' style='width:$schedule_slider_width; left: $schedule_slider_offset; position:absolute;'>\n";
				  
				  //$i = 0;
				  foreach( $games as $game ) {
					  //mstw_log_msg( "Game: $i" );
					  $output .= $this -> build_game_block( $game, $atts, $css_tag );
					  //$i++;
				  }
				 
		$output .= "</div> <!-- .lm-schedule-slider -->";
		
		// Add the scroll controls - right and left arrows
		$output .= $this -> build_scroll_controls( $css_tag, $slider_view_height );
		
			
		$output .= "
				  </div> <!-- .content -->
				</div> <!-- .box -->  
				</div> <!-- .border -->
			  </div> <!-- .lm-slider -->
			</div> <!-- .lm-slider-area -->
		";
		
		return $output;
		
	} //End: build_slider_html_old( )
	
	
	
} //End: class MSTW_TEAM_SCHEDULE_SLIDER


//---------------------------------------------------------------------------
// Create an instance of the class, and have the shortcode hander call
// to create the output
//
$team_slider = new MSTW_TEAM_SCHEDULE_SLIDER;

add_shortcode( 'mstw_team_schedule_slider', array( $team_slider, 'team_schedule_slider_handler' ) );