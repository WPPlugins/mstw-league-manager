<?php
/* ------------------------------------------------------------------------
 * 	MSTW League Manager Add Games Class
 *		UI to add multiple games for a selected league on one screen
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
 *--------------------------------------------------------------------------*/
 
class MSTW_LM_ADD_GAMES {
	
	// Don't need this anymore
	var $log = array();
	
	private $table_size = 10;

	//-------------------------------------------------------------
	// process_option - checks/cleans up the $_POST values
	//-------------------------------------------------------------
	function process_option( $name, $default, $params, $is_checkbox ) {
		//checkboxes which if unchecked do not return values in $_POST
		if ( $is_checkbox and !array_key_exists( $name, $params ) ) {
			$params[ $name ] = $default;	
		}
		
		if ( array_key_exists( $name, $params ) ) {
			$value = stripslashes( $params[ $name ] );
			
		} elseif ( $is_checkbox ) {
			//deal with unchecked checkbox value
		
		} else {
			$value = null;
		}
		
		return $value;
		
	} //End function process_option()

	//-------------------------------------------------------------
	//	forms - builds the user interface for the Update Games screen
	//-------------------------------------------------------------
	function form( ) {
		//mstw_log_msg( 'in MSTW_LM_ADD_GAMES.form ...' );
		
		?>

		<h2><?php _e( 'Add Games', 'mstw-league-manager' )?></h2>
		
		<p class='mstw-lm-admin-instructions'>
		 <?php _e( 'Read the contextual help on the top right of this screen.', 'mstw-league-manager' ) ?> 
		</p>
		
		<?php
		// Check & cleanup the returned $_POST values
		$submit_value = $this->process_option( 'submit', 0, $_POST, false );
		
		//mstw_log_msg( 'request method: ' . $_SERVER['REQUEST_METHOD'] );
		//mstw_log_msg( '$_POST =' );
		//mstw_log_msg( $_POST );
		
		//
		// We do the heavy lifting in the post( ) method
		//
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->post( compact( 'submit_value' ) );
		}
		
		//
		// Heavy lifting done; now build the HTML UI/form
		//
		mstw_lm_admin_notice( );
	
		$current_league = mstw_lm_get_current_league( );
		//mstw_log_msg( '$current_league' . $current_league );
		
		$current_season = mstw_lm_get_league_current_season( $current_league );
		//mstw_log_msg( '$current_season' . $current_season );
		
		?>	

		<!-- begin main wrapper for page content -->
		<div class="wrap">
			
		<form id="add-games" class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
		  <!--<div class='tablenav top'>-->
		  <!-- tablenav top changes the select control & button layout -->
		  <input type='hidden' name='table_size' value="<?php echo $this->table_size ?>"/>
		  
		  <div class="alignleft actions mstw-lm-controls">
			<?php		 
			// Select League control
			$hide_empty = true;
			$ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty );
			
			if ( -1 == $ret ) { //No leagues found
				?>
				<ul class='mstw-lm-admin-instructions'>
				<li><?php _e( 'Create a league (with at least two teams in it) to use this function.', 'mstw-league-manager' ) ?></li>
				</ul>
				<?php
			}
			else {
				// Select Season control
				//$top_level_league = mstw_lm_get_top_level_league( $current_league );
				$ret = mstw_lm_build_season_select( $current_league, $current_season, 'main_season' );
				
				if ( -1 == $ret ) { //No seasons found for league
					$term_obj = get_term_by( 'slug', $current_league, 'mstw_lm_league', OBJECT, 'raw' );
					if ( $term_obj ) {
						$name = $term_obj->name;
					?>
					<div class='mstw-lm-admin-instructions'><?php printf( __( 'No seasons found for league %s.', 'mstw-league-manager' ), $name ) ?></div>
					<?php
					}
				}
				else {
					// HANDLED BY JAVASCRIPT. DON'T NEED A BUTTON
					//<input type="submit" name="submit" id="select_season" class="button" value="<?php _e( 'Select Season', 'mstw-league-manager' ) "  />
				}
			} ?>	
		  </div> <!-- .leftalign actions -->
		  <!--</div>  .tablenav top -->
						
		  <?php
		  if ( -1 != $ret ) {
			//mstw_log_msg( "current league: $current_league, current season: $current_season" );
			
			//
			// RECORDS LIST
			//
			if ( -1 != $current_league &&  !empty( $current_league ) &&
				 -1 != $current_season && !empty( $current_season ) ) {
					 
				// get the Teams in the league
				$args = array( 'numberposts'    => -1,
							   'post_type'      => 'mstw_lm_team',
							   'mstw_lm_league' => $current_league,
							   'meta_key'		=> 'team_name',
							   'orderby'        => 'meta_value',
							   'order' => 'ASC' 
							  );
						  
				$teams = get_posts( $args );			  
				//mstw_log_msg( '$teams: ' );
				//mstw_log_msg( $teams );
				
				$patriarch = mstw_lm_get_top_level_league( $current_league );
				///mstw_log_msg( "Top level league for $current_league is: $patriarch ");
					
				if ( count( $teams ) > 1 ) {
					// this is the (hidden) select-option for non-league games
					//$this -> build_all_teams_select( );
					//$this -> build_all_teams_select( $teams );
					?>
					<table class='wp-list-table widefat auto striped posts'>
						<?php 
						echo $this->update_games_table_header( );
						echo $this->update_games_table_body( $teams );
						?>
						<tr> <!-- Submit button -->
							<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Add Games', 'mstw-league-manager' ); ?>"/></td>
						</tr>
					</table> 
					<?php 	
				}
				else {
					// There are less than two teams in the selected league
					echo "<h3>" . __( 'Select a league containing at least two teams to create games. ', 'mstw-league-manager' ) . "</h3>";
					//mstw_lm_add_admin_notice( 'error', __( 'Select a league containing at least two teams to create games. ', 'mstw-league-manager' ) );					
				}
			}
			else {
				// A league & season must be selected
				echo "<h3>" . __( 'Select a league and a season.', 'mstw-league-manager' ) . "</h3>";
			}
		  } //End: if ( -1 != $retval )
		  ?>
		</form>		
		</div><!-- end wrap -->
		<!-- end of form HTML -->
		
	<?php
	} //End of function form()
	
	//-------------------------------------------------------------
	//	update_games_table_header - builds the HTML for the 
	//		update games table header
	//-------------------------------------------------------------
	function update_games_table_header ( ) {
		
		$ret_html = "<thead>\n<tr>\n";
		$ret_html .= $this->build_table_th( __( 'Date', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Non-league', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Time', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Time TBA', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Home', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Visitor', 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Location', 'mstw-league-manager' ) );
		$ret_html .= "</thead>\n</tr>\n";
					
		return $ret_html;
		
	}

	/*-------------------------------------------------------------
	 *	build_table_th - simple utility to add <th></th> tags
	 *-----------------------------------------------------------*/	
	function build_table_th ( $title ) {
		return "<th>" . $title . "</th>\n";
	}
	
	//-------------------------------------------------------------
	//	update_games_table_body - returns HTML for the update games table
	//-------------------------------------------------------------
	function update_games_table_body ( $teams ) {
		//mstw_log_msg( 'in update_games_table_body ...' );
		//mstw_log_msg( $teams );
		
		$venues = mstw_lm_build_venues_list( );
		
		//mstw_log_msg( 'Venues: ' );
		//mstw_log_msg( $venues );
		
		
		$ret_html = "<tbody>\n";
		
		for ( $i = 0; $i < $this->table_size; $i++ ) {
			$ret_html .= $this->update_games_table_row( $i, $teams, $venues, null );
		}
		
		$ret_html .= "</tbody>\n";
					
		return $ret_html;
		
	} //End: update_games_table_body()
	
	//-------------------------------------------------------------
	//	update_games_table_row - returns HTML one row (game) 
	//		in the update games table
	//-------------------------------------------------------------
	function update_games_table_row( $row_nbr, $teams, $venues, $options = null ) {
		//mstw_log_msg( 'in update_games_table_row ...' );
		
		$ret_html = "<tr>\n";
		
		
		
		// Game Date
		$format = ( array_key_exists( 'date_format', (array)$options ) && !empty( $options['date_format'] ) ) ? $options['date_format'] : 'Y-m-d'; 
		
		$ret_html .= $this->game_date_cell( null, $format, $row_nbr );
		
		// Non-league Game
		$css_tag = 'nonleague_' . $row_nbr;
		
		$ret_html .= "<td><input type='checkbox' size='20' class='nonleague' name='$css_tag' id='$css_tag' value='1'/></td>\n";
		
		// Game Time
		$format = ( array_key_exists( 'time_format', (array)$options ) && !empty( $options['time_format'] ) ) ? $options['time_format'] : 'h:i a'; 
		
		$ret_html .= $this->game_time_cell( null, $format, $row_nbr );
		
		// TBA
		
		$ret_html .= $this->game_time_tba_cell( $row_nbr );
		
		// Home Team
		$ret_html .= $this->team_name_cell( $teams, $row_nbr, 'game_home_team' );
		
		// Visitor
		$ret_html .= $this->team_name_cell( $teams, $row_nbr, 'game_away_team' );
		
		// Location
		$ret_html .= $this->location_cell( $venues, $row_nbr );
		
		$ret_html .= "</tr>\n";
		
		return $ret_html;
		
	} //End: update_games_table_row()
	
	//-------------------------------------------------------------
	//	game_date_cell - returns HTML for the Date column
	//-------------------------------------------------------------
	function game_date_cell( $dtg_stamp = null, $format = 'Y-m-d', $row_nbr=0 ) {
		
		$cell = "<td><input type='hidden' id='row_$row_nbr' value=$row_nbr />";
		
		$dtg_stamp = ( null == $dtg_stamp ) ? current_time( 'timestamp' ) : $dtg_stamp;
		
		$css_tag = 'game_date_' . $row_nbr;
		
		$cell .= "<input type='text' size='10' class='game_date' name='$css_tag' id='$css_tag' value='" . date( $format, $dtg_stamp) . "'/></td>\n";
			
		//$cell = "<td>" . date( $format, $dtg_stamp ) . "</td>\n";
		
		return $cell;
		
	} //End: game_date_cell()
	
	//-------------------------------------------------------------
	//	time_cell - returns HTML for the Time column
	//-------------------------------------------------------------
	function game_time_cell( $dtg_stamp = null, $format = 'h:i a', $row_nbr=0 ) {
		
		$dtg_stamp = ( null == $dtg_stamp ) ? current_time( 'timestamp' ) : $dtg_stamp;
		
		// This is the format that we use with the JS timepicker( )
		$format = 'H:i';
		
		//mstw_log_msg( 'in game_time_cell: current_time= ' . date( $format, $dtg_stamp ) );
		
		$css_tag = 'game_time_' . $row_nbr;
		
		$cell = "<td><input type='text' size='6' class='game_time' name='$css_tag' id='$css_tag' value='" . date( $format, $dtg_stamp) . "'/></td>\n";
		
		return $cell;
		
	} //End: game_time_cell()
	
	//-------------------------------------------------------------
	//	game_time_tba_cell - returns HTML for the TBA column
	//-------------------------------------------------------------
	function game_time_tba_cell( $row_nbr=0 ) {
		
		$css_tag = 'time_tba_' . $row_nbr;
		
		$cell = "<td><input type='checkbox' size='20' class='time_tba' name='$css_tag' id='$css_tag' value='1'/></td>\n";
		
		return $cell;
		
	} //End: game_time_tba_cell()
	
	//-------------------------------------------------------------
	//	team_name_cell - returns HTML for both Home & Visitor columns
	//-------------------------------------------------------------
	function team_name_cell( $teams, $row_nbr, $tag = 'game_home_team' ) {
		//mstw_log_msg( 'in team_name_cell ...' );
		
		$cell = '';
		
		//$team_slug = get_post_meta( $game->ID, $team, true );
		//mstw_log_msg( $team_slug );
		
		//if ( $team_slug ) {
			//$team_obj = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
			//if ( $team_obj ) {
				//mstw_log_msg( $team_obj );
				//$cell = get_post_meta( $team_obj->ID, 'team_name', true );
				//$mascot = get_post_meta( $team_obj->ID, 'team_mascot', true );
				//$cell = "$name $mascot";
			//}
		//}
		
		$css_tag = $tag . '_' . $row_nbr;
		
		$cell = "<td id=$css_tag><select name='$css_tag' id='$css_tag' class='$tag'>\n";
		//$cell .= "<option value='-1'>----</option>\n";	
		foreach ( $teams as $team ) {
			$cell .= "<option value='" . $team->post_name . "'>" . $team->post_title . "</option>\n";					
		}
				
		$cell .= "</select></td>\n";
		
		return $cell;
		
	} //End: team_name_cell()
	
	//-------------------------------------------------------------
	//	build_all_teams_select - returns HTML for the hidden all teams
	//		select-option, which is used for non-league games
	//-------------------------------------------------------------
	function build_all_teams_select( $teams = null ) {
		//mstw_log_msg( 'in team_name_cell ...' );
		
		if ( null === $teams ) {
			// get ALL teams in the DB
			$args = array( 'numberposts'    => -1,
						   'post_type'      => 'mstw_lm_team',
						   //'mstw_lm_league' => $current_league,
						   'meta_key'		=> 'team_name',
						   'orderby'        => 'meta_value',
						   'order' => 'ASC' 
						  );
					  
			$teams = get_posts( $args );
			
			$name = "all_teams";
		}
		else {
			$name = "league_teams";
		}
		
		?>
		<select style="display:none" name="<?php echo $name ?>" id="<?php echo $name ?>">
		<?php	
		 foreach ( $teams as $team ) {
			?>
			<option value="<?php echo $team->post_name ?>"><?php echo $team->post_title ?></option>
			<?php			
		 }
		
		?>		
		</select>
		<?php
		
	} //End: build_all_teams_select( )
	
	//-------------------------------------------------------------
	//	location_cell - returns HTML for the Period column
	//-------------------------------------------------------------
	function location_cell( $venues, $id ) {
		//mstw_log_msg( 'in location_cell ...' );
		//mstw_log_msg( $venues);
		
		$css_tag = 'game_location_' . $id;
		
		$cell = "<td id=$css_tag><select name='$css_tag' id='$css_tag' class='game_location postform'>\n";
		//$cell .= "<option value='-1'>----</option>\n";	
		foreach ( $venues as $title => $slug ) { 									
			$cell .= "<option value='$slug'>$title</option>\n";					
		}
				
		$cell .= "</select></td>\n";
		
		return $cell;
		
	} //End: location_cell()
	
	
	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------
	function post( $options ) {
		mstw_log_msg( 'In add games post method ... ' );
		//mstw_log_msg( $options );
		//mstw_log_msg( $_FILES );
		//mstw_log_msg( '$_POST:' );
		//mstw_log_msg( $_POST );
		
		if ( !$options ) {
			mstw_lm_add_admin_notice( 'error', __( 'Problem encountered. Exiting.', 'mstw-league-manager' ) );
			mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
			return;
		}
		
		switch( $options['submit_value'] ) {
			
			case __( 'Add Games', 'mstw-league-manager' ):
				//mstw_log_msg( 'In post() method: Adding games ...' );
				//$nbr_fields = count( $_POST ) - 2;
				
				$league = mstw_lm_get_current_league( );
				$season = mstw_lm_get_league_current_season( $league );
				
				//mstw_log_msg( '$nbr_fields = ' . $nbr_fields );
				
				// Tag indicating a new post_id, or not
				$last_id = -1;
				
				// Count for admin notice
				$nbr_updated = 0;
				
				while ( $element = each( $_POST ) ) {
					//mstw_log_msg( 'element: ' );
					//mstw_log_msg( 'value = ' . $element['value'] );
					//mstw_log_msg( 'key = ' . $element['key'] );
					
					if ( $pos = strpos( $element['key'], 'date' ) ) {
						//mstw_log_msg( 'found date: ' . substr( $element['key'], $pos + 5 ) );
						
						$game_date = $element['value'];
						//mstw_log_msg( '$game_date = ' );
						//mstw_log_msg( $game_date );
						
						$element = each( $_POST );
						//mstw_log_msg( $element );
						// special handling for the game tba checkbox
						if ( strpos( $element['key'], 'nonleague' ) !== false ) {
							// update game_tba & move the array pointer to home_game
							$game_nonleague = '1'; //$element['value'];
							$element = each( $_POST );
						} else {
							// update game_tba, but don't move array pointer from home_game
							$game_nonleague = '0';
						}
						//mstw_log_msg( "game_nonleague = $game_nonleague" );
						
						//$element = each( $_POST );
						$game_time = $element['value'];
						//mstw_log_msg( '$game_time = ' );
						//mstw_log_msg( $game_time );
						
						$element = each( $_POST );
						//mstw_log_msg( $element );
						// special handling for the game tba checkbox
						if ( strpos( $element['key'], 'time_tba' ) !== false ) {
							// update game_tba & move the array pointer to home_game
							$game_tba = '1'; //$element['value'];
							$element = each( $_POST );
						} else {
							// update game_tba, but don't move array pointer from home_game
							$game_tba = '0';
						}
						//mstw_log_msg( '$game_tba = ' );
						//mstw_log_msg( $game_tba );

						
						//$element = each( $_POST );
						$home_team = $element['value'];
						//mstw_log_msg( '$home_team = ' );
						//mstw_log_msg( $home_team );
						
						$element = each( $_POST );
						$visitor = $element['value'];
						//mstw_log_msg( '$visitor = ' );
						//mstw_log_msg( $visitor );
						
						$element = each( $_POST );
						$location = $element['value'];
						//mstw_log_msg( '$location = ' );
						//mstw_log_msg( $location );
						
						$result = $this->process_game( $league, $season, $game_date, $game_nonleague, $game_time, $game_tba, $home_team, $visitor, $location );
						
						if ( 0 == $result ) {
							//mstw_log_msg( 'teams match ... we can exit' );
							break;
						}
						else {
							//mstw_log_msg( 'teams do not match ... processed new game' );
							$nbr_updated++;
						}
					}
				}
				
				mstw_lm_add_admin_notice( 'updated', sprintf( __( '%s games updated.', 'mstw-league-manager' ), $nbr_updated ) );
				
				break;
				
			default:
				mstw_lm_add_admin_notice( 'error', __( 'Error encountered. Exiting.', 'mstw-league-manager' ) );
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $submit_value . '. Exiting' );
				
				break;
		}
		
		return;
			
	} //End: post( )
	
	function process_game( $league, $season, $game_date, $game_nonleague, $game_time, $game_tba, $home_team, $visitor, $location ) {
		//mstw_log_msg( 'in process_game ...' );
		if ( $home_team == $visitor ) {
			return 0;
		}
		
		//mstw_log_msg( '$league = ' . $league );
		//mstw_log_msg( '$season = ' . $season );
		//mstw_log_msg( '$home_team = ' . $home_team );
		//mstw_log_msg( '$visitor = ' . $visitor );
		
		$home_obj = get_page_by_path( $home_team, OBJECT, 'mstw_lm_team' );
		$home_name = get_post_meta( $home_obj->ID, 'team_name', true );
		
		$away_obj = get_page_by_path( $visitor, OBJECT, 'mstw_lm_team' );
		$away_name = get_post_meta( $away_obj->ID, 'team_name', true );
		
		$game_args = array( 'post_title' => "$game_date $away_name @ $home_name",
							'post_type'  => 'mstw_lm_game',
							'post_status' => 'publish',
							'tax_input' => array( 'mstw_lm_league' => $league ),
					);
					
		remove_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );	
		$game_id = wp_insert_post( $game_args );
		add_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );
		
		if ( $game_id ) {
			// insert successful, add league term & meta data
			//mstw_log_msg( "wp_insert_post succeeded. New game ID $game_id " );
			
			//
			// Add league taxonomy
			//
			$ret = wp_set_object_terms( $game_id, $league, 'mstw_lm_league', false );
			//mstw_log_msg( 'wp_set_object_terms:' );
			//mstw_log_msg( $ret );
			
			//mstw_log_msg( '$game_date = ' . $game_date );
			//mstw_log_msg( '$game_time = ' . $game_time  );
			//mstw_log_msg( "game_dtg = $game_date $game_time" );
			$game_dtg = strtotime( "$game_date $game_time" );
			
			update_post_meta( $game_id, 'game_unix_dtg', $game_dtg );
			
			//mstw_log_msg( '$game_nonleague = ' . $game_nonleague );
			update_post_meta( $game_id, 'game_nonleague', $game_nonleague );
			
			//mstw_log_msg( '$game_tba = ' . $game_tba );
			update_post_meta( $game_id, 'game_is_tba', $game_tba );
			
			//mstw_log_msg( '$home_team = ' . $home_team );
			update_post_meta( $game_id, 'game_home_team', $home_team );
			
			//mstw_log_msg( '$visitor = ' .  $visitor );
			update_post_meta( $game_id, 'game_away_team', $visitor );
			
			//mstw_log_msg( '$location = ' . $location );
			update_post_meta( $game_id, 'game_location', $location );
			
			//mstw_log_msg( '$league = ' . $league );
			update_post_meta( $game_id, 'game_league', $league );
			
			//mstw_log_msg( '$season = ' . $season );
			update_post_meta( $game_id, 'game_season', $season );
		}
		else {
			mstw_log_msg( "wp_insert_post_failed $game_date, $home_team, $visitor" );
		}
		
		return 1;
		
	} // End: process_game( )
	
	//-------------------------------------------------------------
	// add help - outputs HTML for the contextual help area of the screen
	//		
	// ARGUMENTS:
	//	 None 
	//   
	// RETURNS:
	//	 Outputs HTML to the contextual help aree of the screen
	//-------------------------------------------------------------
	
	function add_help( ) {
		//mstw_log_msg( "in add games add_help" );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
		
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'add-games-overview',
						'callback'  => array( $this, 'add_help_tab' ),
						),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	} //End: add_help( )
	
	function add_help_tab( $screen, $tab ) {
		//mstw_log_msg( "in add_help_tab ... " );
		//mstw_log_msg( "screen:" );
		//mstw_log_msg( $screen );
		//mstw_log_msg( "tab:" );
		//mstw_log_msg( $tab );
		
		if( !array_key_exists( 'id', $tab ) ) {
			return;
		}
			
		switch ( $tab['id'] ) {
			case 'add-games-overview':
				?>
				<p><?php _e( 'This screen allows addition of multiple games in a league and season. A LEAGUE and a SEASON must be selected. Games will be added to that league for that season.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Selecting "Non-league" will change the teams lists from league teams to all teams in the database, so non-league games can be added. However, any existing Home and Visitor Selections may be changed.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'GAMES WITH THE SAME HOME AND VISITOR WILL BE IGNORED. This ends the processing of the list of games.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-add-games/" target="_blank"><?php _e( 'See the Add Games man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()
	
} //End: class MSTW_LM_UPDATE_GAMES
?>