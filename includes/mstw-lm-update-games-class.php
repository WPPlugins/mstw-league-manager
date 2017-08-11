<?php
/* ------------------------------------------------------------------------
 * 	MSTW League Manager Update Games Class
 *	UI to update all games for a selected league on one screen
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
 *--------------------------------------------------------------------------*/
 
class MSTW_LM_UPDATE_GAMES {
	
	// Don't need this anymore
	var $log = array();

	
	//-------------------------------------------------------------
	//	forms - builds the user interface for the Update Games screen
	//-------------------------------------------------------------
	function form( ) {
		//mstw_log_msg( 'in MSTW_LM_UPDATE_GAMES.form ...' );
		?>
		
		<h2><?php _e( 'Update Games', 'mstw-league-manager' )?></h2>
		
		<p class='mstw-lm-admin-instructions'>
		 <?php _e( 'Read the contextual help tab on the top right of this screen.', 'mstw-league-manager' ) ?> 
		</p>
		
		<?php
		// Check & cleanup the returned $_POST values
		$submit_value = $this->process_option( 'submit', 0, $_POST, false );
		
		//mstw_log_msg( 'request method: ' . $_SERVER['REQUEST_METHOD'] );
		//mstw_log_msg( '$_POST =' );
		mstw_log_msg( $_POST );
		
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
		//mstw_log_msg( '$current_league: ' . $current_league );
		
		$current_season = mstw_lm_get_league_current_season( $current_league );
		//mstw_log_msg( '$current_season: ' . $current_season );
		?>	

		<!-- begin main wrapper for page content -->
		<div class="wrap">
			
		<form id="update-games" class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
		  <div class="alignleft actions mstw-lm-controls">
		    <?php		 
		    // Select League control
		    $hide_empty = true; // Need a league with at least one team in it
		    $ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty );
		
		    if ( -1 == $ret ) { //No leagues found
				?>
				<ul class='mstw-lm-admin-instructions'>
				<li><?php _e( 'Create a league (with games in it) to use this function.', 'mstw-league-manager' ) ?></li>
				</ul>
				<?php
			}
		    else {
				// Select Season control
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
					?>
					<a href="<?php  echo admin_url( 'admin.php?page=mstw-lm-update-games' )?>" class="button mstw-lm-control-button"><?php _e( 'Update Games Table', 'mstw-league-manager' ) ?></a>	
				<?php 
				} 
		    } ?>
	      </div> <!-- .leftalign actions -->
 			
		  <?php
		   if ( -1 != $ret ) {
			//mstw_log_msg( "current league: $current_league, current season: $current_season" );
			
			//
			// GAMES LIST
			//
		    if ( -1 != $current_league &&  !empty( $current_league ) &&
				 -1 != $current_season && !empty( $current_season ) ) {
					 
				// get the games in the league
				$args = array( 'numberposts'    => -1,
							   'post_type'      => 'mstw_lm_game',
							   'mstw_lm_league' => $current_league,
							   'meta_query'     => array( 'key'     => 'game_season',
														  'value'   => $current_season,
														  'compare' => '=',
														),
							   'meta_key'		=> 'game_unix_dtg',
							   'orderby'        => 'meta_value_num',
							   'order' => 'ASC' 
							  );
							  
				$games = get_posts( $args );
				
				if ( count( $games ) > 0 ) {
					?>
					<table class='wp-list-table widefat fixed striped posts'>
						<?php 
						echo $this->update_games_table_header( ); 
						echo $this->update_games_table_body( $games );
						?>
						<tr> <!-- Submit button -->
							<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Update Games', 'mstw-league-manager' ); ?>"/></td>
						</tr>
					</table> 
				<?php 	
				}
				else {
					// No games for league $league
					echo "<p><h3>" . sprintf( __( 'No games found for %s and %s.', 'mstw-league-manager' ), $current_league, $current_season ) . "</h3></p>";
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
		$ret_html .= $this->build_table_th( __( 'Date' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Home' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Home Score' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Visitor' , 'mstw-league-manager' ) );;
		$ret_html .= $this->build_table_th( __( 'Visitor Score' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Time Remaining' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Period' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Is Open' , 'mstw-league-manager' ) );
		$ret_html .= $this->build_table_th( __( 'Is Final' , 'mstw-league-manager' ) );

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
	function update_games_table_body ( $games ) {
		//mstw_log_msg( 'in update_games_table_body ...' );
		
		$ret_html = "<tbody>\n";
		
		foreach ( $games as $game ) {
			$ret_html .= $this->update_games_table_row( $game );
		}
		
		$ret_html .= "</tbody>\n";
					
		return $ret_html;
		
	} //End: update_games_table_body()
	
	//-------------------------------------------------------------
	//	update_games_table_row - returns HTML one row (game) 
	//		in the update games table
	//-------------------------------------------------------------
	function update_games_table_row( $game ) {
		//mstw_log_msg( 'in update_games_table_row ...' );
		
		$game_id = $game->ID; //for convenience
		
		$ret_html = "<tr>\n";
		// Game Date
		$ret_html .= $this->game_date_cell( $game );
		
		//"<td>" . get_post_meta( $game->ID, 'game_unix_dtg', true ) . "</td>\n";
		
		$ret_html .= $this->team_name_cell( $game, 'game_home_team' );
		$ret_html .= $this->team_score_cell( $game, 'game_home_score' );
		
		$ret_html .= $this->team_name_cell( $game, 'game_away_team' );
		$ret_html .= $this->team_score_cell( $game, 'game_away_score' );
		
		$ret_html .= $this->time_remaining_cell( $game );
		
		$ret_html .= $this->period_cell( $game );
		
		$ret_html .= $this->is_open_cell( $game );
		
		$ret_html .= $this->is_final_cell( $game );
		
		$ret_html .= "</tr>\n";
		
		return $ret_html;
		
	} //End: update_games_table_row()
	
	//-------------------------------------------------------------
	//	game_date_cell - returns HTML for the Date column
	//-------------------------------------------------------------
	function game_date_cell( $game, $format = 'Y-m-d' ) {
		
		$date_cell = "<td>" . date( $format, get_post_meta( $game->ID, 'game_unix_dtg', true ) ) . "</td>\n";
		
		return $date_cell;
		
	} //End: game_date_cell()
	
	//-------------------------------------------------------------
	//	team_name_cell - returns HTML for both Home & Visitor columns
	//-------------------------------------------------------------
	function team_name_cell( $game, $team = 'game_home_team' ) {
		
		$cell = '';
		//mstw_log_msg( $game );
		$team_slug = get_post_meta( $game->ID, $team, true );
		//mstw_log_msg( $team_slug );
		
		if ( $team_slug ) {
			$team_obj = get_page_by_path( $team_slug, OBJECT, 'mstw_lm_team' );
			if ( $team_obj ) {
				//mstw_log_msg( $team_obj );
				$cell = get_post_meta( $team_obj->ID, 'team_name', true );
				//$mascot = get_post_meta( $team_obj->ID, 'team_mascot', true );
				//$cell = "$name $mascot";
			}
		}
		
		return '<td>' . $cell . "</td>";
		
	} //End: team_name_cell()
	
	//-------------------------------------------------------------
	//	team_score_cell - returns HTML for both Score columns
	//-------------------------------------------------------------
	function team_score_cell( $game, $field = 'game_home_score' ) {
		$score = get_post_meta( $game->ID, $field, true );
		
		$css_id =  $field . '_' . $game->ID;
		
		$cell = "<td>" . "<input type='text' name=$css_id id=$css_id size='5' maxlength='16' value='$score' />" . "</td>\n";

		return $cell;
		
	} //End: team_score_cell()
	
	//-------------------------------------------------------------
	//	time_remaining_cell - returns HTML for the Time column
	//-------------------------------------------------------------
	function time_remaining_cell( $game ) {
		$time_remaining = get_post_meta( $game->ID, 'game_time_remaining', true );
		
		$css_id = 'game_time_remaining_' . $game->ID;
		
		$cell = "<td>" . "<input type='text' name=$css_id id=$css_id size='5' maxlength = '16' value='$time_remaining' />" . "</td>\n";
		
		//$cell = "<td>" . get_post_meta( $game->ID, 'game_time_remaining', true ) . "</td>\n";
		return $cell;
		
	} //End: time_remaining_cell()
	
	//-------------------------------------------------------------
	//	period_cell - returns HTML for the Period column
	//-------------------------------------------------------------
	function period_cell( $game ) {
		$period = get_post_meta( $game->ID, 'game_period', true );
		
		$css_id = 'game_period_' . $game->ID;
		
		$cell = "<td>" . "<input type='text' name=$css_id id=$css_id size='3' maxlength = '16' value='$period' />" . "</td>\n";
		
		//$cell = "<td>" . get_post_meta( $game->ID, 'game_period', true ) . "</td>\n";
		return $cell;
	} //End: period_cell()
	
	//-------------------------------------------------------------
	//	is_final_cell - returns HTML for the Final column
	//-------------------------------------------------------------
	function is_final_cell( $game ) {
		$is_final = get_post_meta( $game->ID, 'game_is_final', true );
		
		$checked = checked( 1, $is_final, false );
		
		$css_id = 'game_is_final_' . $game->ID;
		
		$cell = "<td>" . "<input type='checkbox' name=$css_id id=$css_id value='1' $checked />" . "</td>\n";
		
		//$cell = "<td>" . get_post_meta( $game->ID, 'game_is_final', true ) . "</td>\n";
		return $cell;
	} //End: is_final_cell()
	
	//-------------------------------------------------------------
	//	is_open_cell - returns HTML for the Open column
	//-------------------------------------------------------------
	function is_open_cell( $game ) {
		$is_open = get_post_meta( $game->ID, 'game_is_open', true );
		
		$checked = checked( 1, $is_open, false );
		
		$css_id = 'game_is_open_' . $game->ID;
		
		$cell = "<td>" . "<input type='checkbox' name=$css_id id=$css_id value='1' $checked />" . "</td>\n";
		
		//$cell = "<td>" . get_post_meta( $game->ID, 'game_is_open', true ) . "</td>\n";
		return $cell;
		
	} //End: is_open_cell()
	
	//-------------------------------------------------------------
	//	print_messages - prints the admin message log
	//-------------------------------------------------------------
	function print_messages() {

		if (!empty($this->log)) {
			// messages HTML {{{
			?>
			<div class="wrap">
			<?php if (!empty($this->log['error'])): ?>

			<div class="error">

				<?php foreach ($this->log['error'] as $error): ?>
					<p><?php echo $error; ?></p>
				<?php endforeach; ?>

			</div>

			<?php endif; ?>

			<?php if (!empty($this->log['notice'])): ?>

			<div class="updated fade">

				<?php foreach ($this->log['notice'] as $notice): ?>
					<p><?php echo $notice; ?></p>
				<?php endforeach; ?>

			</div>

			<?php endif; ?>
			</div><!-- end wrap -->

			<?php
			// end messages HTML }}}

			$this->log = array();
		}
	} //End function print_messages()

	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------
	function post( $options ) {
		//mstw_log_msg( 'In post method ... ' );
	    //mstw_log_msg( '$options:' );
		//mstw_log_msg( $options );
		//mstw_log_msg( $_FILES );
		//mstw_log_msg( $_POST );
		
		if ( !$options ) {
			mstw_lm_add_admin_notice( 'error', __( 'Problem encountered. Exiting.', 'mstw-league-manager' ) );
			mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
			return;
		}
		
		//mstw_log_msg( "Submit value: = " . $options['submit_value'] );
		
		switch( $options['submit_value'] ) {
			case __( 'Select League', 'mstw-league-manager' ):
				//mstw_log_msg( 'post(): Refresh Table ...' );
				//mstw_log_msg( 'Setting option: ' . $_POST['main_league'] );
				mstw_lm_set_current_league( $_POST['main_league'] );
				mstw_lm_set_current_league_season( $_POST['main_league'], $_POST['main_season'] );
				break;
				
			case __( 'Update Games', 'mstw-league-manager' ):
				//mstw_log_msg( 'post(): Update Schedule ...' );
				//return;
				$nbr_fields = count( $_POST ) - 3;
				//mstw_log_msg( "Updating {$nbr_fields} game data fields." );
				
				// Tag indicating a new post_id, or not
				//$last_id = -1;
				
				$games_processed = 0;
				foreach ( $_POST  as $key => $value ) {
					if ( false !== strpos( $key, 'game_home_score' ) ) {
						$games_processed++;
						//mstw_log_msg( 'found new game ' . $games_processed );
						// get the post id
						$last_us = strrchr( $key, '_' );
						//mstw_log_msg( "last underscore string: $last_us" );
						$post_id = substr( $last_us, 1 );
						//mstw_log_msg( "post ID: $post_id" );
						
						$game_home_score = $_POST[ 'game_home_score_' . $post_id ]; 
						//mstw_log_msg( "home_score= $game_home_score" );
						update_post_meta( $post_id, 'game_home_score', $game_home_score );
						
						if ( array_key_exists( 'game_away_score_' . $post_id, $_POST ) ) {
							$game_away_score = $_POST['game_away_score_' . $post_id ];
							//mstw_log_msg( "away_score= " . $game_away_score );
							update_post_meta( $post_id, 'game_away_score', $game_away_score );
						}
						
						if ( array_key_exists( 'game_time_remaining_' . $post_id, $_POST ) ) {
							$game_time_remaining = $_POST['game_time_remaining_' . $post_id ];
							//mstw_log_msg( "time remaining= " . $game_time_remaining );
							update_post_meta( $post_id, 'game_time_remaining', $game_time_remaining );
						}
						
						if ( array_key_exists( 'game_period_' . $post_id, $_POST ) ) {
							$game_period = $_POST['game_period_' . $post_id ];
							//mstw_log_msg( "period= " . $game_period );
							update_post_meta( $post_id, 'game_period', $game_period );
						}
						
						$game_is_final = ( array_key_exists( 'game_is_final_' . $post_id, $_POST ) ) ? 1 : 0;
						//mstw_log_msg( "game is final= " . $game_is_final );
						update_post_meta( $post_id, 'game_is_final', $game_is_final );
						
						// If game is final it can't be open
						if ( 1 == $game_is_final ) {
							$game_is_open = 0;
							update_post_meta( $post_id, 'game_is_open', 0 );
						}
						else { // Otherwise deal with the game is open field
							$game_open_field = 'game_is_open';
							$game_open_key = 'game_is_open_' . $post_id;
							$game_is_open = ( array_key_exists( $game_open_key, $_POST ) ) ? 1 : 0;
							update_post_meta( $post_id, $game_open_field, $game_is_open );
						}
						//mstw_log_msg( "game is open= " . $game_is_open );
						
					} //End: if ( false !== strpos( $key, 'game_home_score' ) ) {	
					
				} //End: foreach ( $_POST  as $key => $value ) {
			
				mstw_lm_add_admin_notice( 'updated', sprintf( __( 'Updated %s games.', 'mstw-league-manager' ), $games_processed ) );
				
				break;
				
			default:
				//$this->log['notice'][] = sprintf( __( 'No function found for %s.)', 'mstw-league-manager' ), $options['submit_value'] );
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $submit_value . '. Exiting' );
				return;
				break;
		}
			
	} //End: post( )
	
	//-------------------------------------------------------------
	// process_option - checks/cleans up the $_POST values
	//-------------------------------------------------------------
	function process_option( $name, $default, $params, $is_checkbox ) {
		//mstw_log_msg( "process_option:" );
		//mstw_log_msg( "name = $name" );
		//mstw_log_msg( "default = $default" );
		//mstw_log_msg( "is_checkbox = $is_checkbox" );
		//mstw_log_msg( "params:" );
		//mstw_log_msg( $params );
		
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
	// add help - outputs HTML for the contextual help area of the screen
	//		
	// ARGUMENTS:
	//	 None 
	//   
	// RETURNS:
	//	 Outputs HTML to the contextual help area of the screen
	//-------------------------------------------------------------
	
	function add_help( ) {
		//mstw_log_msg( "in update games add_help" );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		mstw_lm_help_sidebar( $screen );
		
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'update-games-overview',
						'callback'  => array( $this, 'add_help_tab' ),
						),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	} //End: add_help( )
	
	function add_help_tab( $screen, $tab ) {
		//mstw_log_msg( "in update games add_help_tab ... " );
		
		if( !array_key_exists( 'id', $tab ) ) {
			return;
		}
			
		switch ( $tab['id'] ) {
			case 'update-games-overview':
				?>
				<p><?php _e( 'This screen allows updating the status of all games in a league and season.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Select a LEAGUE and SEASON then press the Update Games Table button.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Enter the status in information for each game.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-update-games/" target="_blank"><?php _e( 'See the Update Games man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()
	
} //End: class MSTW_LM_UPDATE_GAMES
?>