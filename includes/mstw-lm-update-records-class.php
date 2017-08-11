<?php
/* ------------------------------------------------------------------------
 * 	MSTW League Manager Update Records Class
 *		UI to manage updating of team records
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
 
class MSTW_LM_UPDATE_RECORDS {
	
	// Don't need this anymore
	var $log = array();
	
	private $table_size = 10;
	
	//-------------------------------------------------------------
	//	forms - builds the user interface for the Update Games screen
	//-------------------------------------------------------------
	function form( ) {
		//mstw_log_msg( 'in MSTW_LM_UPDATE_RECORDS.form ...' );
		?>

		<h2><?php _e( 'Update Team Records', 'mstw-league-manager' )?></h2>
	
		<p class='mstw-lm-admin-instructions'>
		 <?php _e( 'Read the contextual help on the top right of this screen.', 'mstw-league-manager' ) ?> 
		</p>

		<?php
		// Check & cleanup the returned $_POST values
		$submit_value = $this->process_option( 'submit', 0, $_POST, false );
		
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
		//mstw_log_msg( '$current_league= ' . $current_league );
		
		$current_season = mstw_lm_get_league_current_season( $current_league );
		//mstw_log_msg( '$current_season= ' . $current_season );
		?>
		
		<!-- begin main wrapper for page content -->
		<div class="wrap">
		  
		<form id="set-league" class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
		  <div class="alignleft actions mstw-lm-controls">
			<?php		 
			// Select League control
			$hide_empty = true; // Need a league with at least one team in it
			$ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty );
			
			if ( -1 == $ret ) { //No leagues found
				?>
				<ul class='mstw-lm-admin-instructions'>
				<li><?php _e( 'Create a league (with at least one team in it) to use this function.', 'mstw-league-manager' ) ?></li>
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
					<a href="<?php  echo admin_url( 'admin.php?page=mstw-lm-update-records' )?>" class="button mstw-lm-control-button"><?php _e( 'Update Records Table', 'mstw-league-manager' ) ?></a>
				<?php 
				} 
			} ?>
		  </div> <!-- .leftalign actions -->
				
		  <?php
		  if ( -1 != $ret ) {
			//mstw_log_msg( "current league: $current_league, current season: $current_season" );
			
			//
			// RECORDS LIST
			//
			if ( -1 != $current_league &&  !empty( $current_league ) &&
				 -1 != $current_season && !empty( $current_season ) ) {
					 
				// get the teams in the league
				$args = array( 'numberposts'    => -1,
							   'post_type'      => 'mstw_lm_team',
							   'mstw_lm_league' => $current_league,
							   'meta_key'		=> 'team_name',
							   'orderby'        => 'meta_value',
							   'order'          => 'ASC' 
							  );
							  
				$teams = get_posts( $args );
				//mstw_log_msg( '$teams: ' );
				//mstw_log_msg( $teams );
				
				if ( count( $teams ) > 0 ) {
					?>
					<table class='wp-list-table widefat auto striped posts'>
						<?php 
						$this->build_records_table_header( );
						$this->build_records_table_body( $teams, $current_league, $current_season );
						?>
						<tr> <!-- Submit button -->
							<td colspan="2" class="submit">
								<input type="submit" class="button" name="submit" value="<?php _e( 'Update Records', 'mstw-league-manager' ); ?>"/>
							</td>
						</tr>
					</table> 
				<?php 	
				}
				else {
					// No teams found for league
					echo "<h3>" . __( 'No teams found for league:', 'mstw-league-manager' ) . " $current_league.</h3>";	
				}
			}
			else {
				// A league & season must be selected
				echo "<h3>" . __( 'Select a league and a season.', 'mstw-league-manager' ) . "</h3>";
			}
		  } //End: if ( -1 != $retval )
		  ?>
		</form>	 
		</div> <!-- end .wrap -->
		<!-- end of form HTML -->
			
	<?php
	} //End of function form()
	
	//-------------------------------------------------------------
	//	build_records_table_header - outputs the HTML for the 
	//		records table header
	//-------------------------------------------------------------
	function build_records_table_header ( ) {
		//mstw_log_msg( 'in build_records_table_header ...' );
		?>
		<thead>
		  <tr>
		   <th><?php _e( 'Team', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Rank', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Games', 'mstw-league-manager' ) ?><br/>Played</th>
		   <th><?php _e( 'Wins', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Losses', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Ties', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'OT', 'mstw-league-manager' ) ?><br/>
		       <?php _e( 'Win', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'OT', 'mstw-league-manager' ) ?><br/>
		       <?php _e( 'Loss', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Goals', 'mstw-league-manager' ) ?><br/>
		       <?php _e( 'For', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Goals', 'mstw-league-manager' ) ?><br/>
		       <?php _e( 'Against', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Games', 'mstw-league-manager' )?><br/>
		       <?php _e( 'Behind', 'mstw-league-manager' )?></th>
		   <th><?php _e( 'Streak', 'mstw-league-manager' )?><br/></th>
		   <th><?php _e( 'Last', 'mstw-league-manager' ) ?><br/>5</th>
		   <th><?php _e( 'Last', 'mstw-league-manager' ) ?><br/>10</th>
		   <th><?php _e( 'Home', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Away', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Conf', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Div', 'mstw-league-manager' ) ?></th>
		  </tr>
		</thead>
					
		<?php
	} //End: build_records_table_header()

	//-------------------------------------------------------------
	//	build_seasons_table_body - returns HTML for the update games table
	//-------------------------------------------------------------
	function build_records_table_body ( $teams, $league, $season ) {
		//mstw_log_msg( 'in build_records_table_body ...' );
		//mstw_log_msg( '$league=' . $league );
		//mstw_log_msg( '$season=' . $season );
		
		$table_size = count( $teams );
		if ( $table_size ) {
		?>
		<tbody>
			<?php
			for ( $i = 0; $i < $table_size; $i++ ) {
				$this->build_records_table_row( $i, $teams, $league, $season );
			}
			?>
		
		</tbody>
		
		<?php 
		} //End: if ( $table_size )
			
	} //End: build_records_table_body()
	
	//-------------------------------------------------------------
	//	build_records_table_row - returns HTML for the update games table
	//-------------------------------------------------------------
	function build_records_table_row ( $i, $teams, $league, $season ) {
		//mstw_log_msg( 'in build_records_table_row ...' );
		//mstw_log_msg( "row= $i" );
		//mstw_log_msg( '$league=' . $league );
		//mstw_log_msg( '$season=' . $season );
		//mstw_log_msg( 'team ID= ' . $teams[ $i ]->ID );
		
		$team_id =   $teams[ $i ] -> ID;
		$team_slug = $teams[ $i ] -> post_name;
		
		$record_slug = $league . '_' . $season . '_' . $team_slug;
		
		$record_obj = get_page_by_path( $record_slug, OBJECT, 'mstw_lm_record' );
		
		$rank = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'rank', true ) : '';
		$games_played = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'games_played', true ) : '';
		$wins = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'wins', true ) : '';
		$losses = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'losses', true ) : '';
		$ties = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'ties', true ) : '';
		$otw = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'otw', true ) : '';
		$otl = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'otl', true ) : '';
		
		$goals_for = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'goals_for', true ) : '';
		$goals_against = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'goals_against', true ) : '';
		$games_behind = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'games_behind', true ) : '';
		
		$streak = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'streak', true ) : '';
		$last_5 = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'last_5', true ) : '';
		$last_10 = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'last_10', true ) : '';
		
		$home = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'home', true ) : '';
		$away = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'away', true ) : '';
		$conference = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'conference', true ) : '';
		$division = ( $record_obj ) ? get_post_meta( $record_obj->ID, 'division', true ) : '';
		
		$team_id = $teams[ $i ] -> ID;
		
		$fields = array( array( "text", "2", "rank", $rank ),
						 array( "text", "2", "games_played", $games_played ),
						 array( "text", "2", "wins", $wins ),
						 array( "text", "2", "losses", $losses ),
						 array( "text", "2", "ties", $ties ),
						 array( "text", "2", "otw", $otw ),
						 array( "text", "2", "otl", $otl ),
						 array( "text", "2", "goals_for", $goals_for ),
						 array( "text", "2", "goals_against", $goals_against ),
						 array( "text", "4", "games_behind", $games_behind ),
						 array( "text", "4", "streak", $streak ),
						 array( "text", "4", "last_5", $last_5 ),
						 array( "text", "4", "last_10", $last_10 ),
						 array( "text", "4", "home", $home ),
						 array( "text", "4", "away", $away ),
						 array( "text", "4", "conference", $conference ),
						 array( "text", "4", "division", $division ),
						);
		?>
		<tr>
		 <td> <!-- Team Name column --> 
			<?php echo get_post_meta( $teams[ $i ]->ID, 'team_name', true ) ?>
		 </td>
		 
		 <?php
		 foreach ( $fields as $values ) {
			 // mstw_log_msg( '$values[0] = ' . $values[0] );
			 //mstw_log_msg( '$values[1] = ' . $values[1] );
			 //mstw_log_msg( '$values[2] = ' . $values[2] );
			 //mstw_log_msg( '$values[3] = ' . $values[3] );
			 //mstw_log_msg( 'css tag: ' . $values[2] . '_' );
			?>
			<td>
			  <?php $this->build_input_control( $values[0], $values[1], $values[2] . '_' . $team_id, $values[3] ); ?>
			</td>
			<?php
		 }
		 ?>
		
		</tr>
		
		<?php 	
	} //End: build_records_table_row()
	
	//-------------------------------------------------------------
	//	build_input_control - outputs the HTML for an input control
	//-------------------------------------------------------------
	function build_input_control( $type, $size, $name_id, $value ) {
		//mstw_log_msg( 'in build_input_control ...' );
		?>
		<input type="<?php echo $type ?>" size="<?php echo $size ?>" name="<?php echo $name_id ?>" id="<?php echo $name_id ?>" value="<?php echo $value ?>"/>
		
	<?php	
	} //End: build_input_control()
	
	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------
	function post( $options ) {
		//mstw_log_msg( 'In post method ... ' );
		//mstw_log_msg( '$_POST:' );
		//mstw_log_msg( $_POST );
		//mstw_log_msg( '$options:' );
		//mstw_log_msg( $options );
		//$records = get_posts( array( 'post_type' => 'mstw_lm_record', 'posts_per_page' => -1 ) );
		//mstw_log_msg( $records );
		
		if ( !$options ) {
			mstw_lm_add_admin_notice( 'error', __( 'Problem encountered. Exiting.', 'mstw-league-manager' ) );
			mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
			return;
		}
		
		switch( $options['submit_value'] ) {
			case __( 'Select League', 'mstw-league-manager' ):
				//mstw_log_msg( 'In post(): updating league selection ...' );
				//mstw_log_msg( 'Setting option: ' . $_POST['main_league'] );
				mstw_lm_set_current_league( $_POST['main_league'] );
				break;
				
			case __( 'Select Season', 'mstw-league-manager' ):
				//mstw_log_msg( 'In post(): updating league selection ...' );
				//mstw_log_msg( 'Setting option: ' . $_POST['main_league'] );
				mstw_lm_set_league_current_season( $_POST['main_league'], $_POST['main_season'] );
				break;
				

			case __( 'Update Records', 'mstw-league-manager' ):
				//mstw_log_msg( 'Updating records ...' );
				//mstw_log_msg( $_POST );
				
				//Records will always 'be in' the top level league
				$league_slug = $_POST['main_league'];
				//mstw_log_msg( "Top level league slug: $league_slug" );
				
				$season_slug = $_POST['main_season'];
				//mstw_log_msg( "Season slug: $season_slug" );
				
				//
				//FOR DEBUG/CLEANUP. DELETES ALL TEAM RECORDS.
				//	Probably want to remark out the while loop below
				//
				//$this -> delete_all_records( );
				//
				
				$records_processed = 0;
				while( $element = each( $_POST ) ) {
					//mstw_log_msg( $element );
					if( ( $pos = strpos( $element['key'], 'rank' ) ) !== false ) {
						//mstw_log_msg( 'new data record ' . $i++ );
						
						// 1. GET THE TEAM ID AND SLUG
						
						$pos = strpos( $element['key'], '_' );
						$team_id = substr( $element['key'], $pos + 1 );
						//mstw_log_msg( 'team id = ' . $team_id );
						
						$team_obj = get_post( $team_id, OBJECT, 'raw' );
						$team_slug = $team_obj->post_name;
						//mstw_log_msg( 'team slug = ' . $team_slug );
						
						$record_slug = $league_slug . '_' . $season_slug . '_' . $team_slug;
						//mstw_log_msg( 'record slug: ' . $record_slug );
						
						// 1. UPDATE THE TEAM'S team_record meta data field
						
						// Update the team CPT record meta data
						update_post_meta( $team_id, $league_slug . '_' . $season_slug, 1 );
						
						// Have to back up because while has advanced the array pointer
						// past rank with each()
						$element = prev( $_POST ); 
						
						// 2. UPDATE/CREATE the mstw_lm_record CPT
						$record = $this->update_record_cpt( $record_slug, $league_slug, $season_slug, $team_id );
						
						//mstw_log_msg( "Record $record updated:" );
						
						//if ( $record > 0 ) {
							//$data = get_post_meta( $record );
							//mstw_log_msg( $data );
						//}
						
						$records_processed++;

					}
				} //End: while( $element = each( $_POST ) )
					
				//mstw_log_msg( "Processed $records_processed records" );
				mstw_lm_add_admin_notice( 'updated', sprintf( __( 'Updated %s records successfully.', 'mstw-league-manager' ), $records_processed ) );

				//$records = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'mstw_lm_record' ) );
				
				//mstw_log_msg( 'nbr of records: ' . count( $records ) );
				
				/* check for multiple meta data values
				$args = array( 'meta_query' => array(
									'relation' => 'AND',
									array(
										'key'     => 'record_league',
										'value'   => 'league-slug',
										'compare' => '='
									),
									
									array(
										'key'     => 'record_season',
										'value'   => 'season-slug',
										'compare' => '='
									),

									array(
										'key'     => 'record_team',
										'value'   => 'team-slug',
										'compare' => '='
									)
								)
							);
				*/
				
				//$record = array( 'rank' => $_POST['rank']);
				
				break;
				
			default:
				//$this->log['notice'][] = sprintf( __( 'No function found for %s.)', 'mstw-league-manager' ), $options['submit_value'] );
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $options['submit_value'] . '. Exiting' );
				mstw_lm_add_admin_notice( 'error', sprintf( __( 'Error encountered. See system admin.', 'mstw-league-manager' ), $options['submit_value'] ) );
				return;
				break;
		}
			
	} //End: post( )
	
	//-------------------------------------------------------------
	// update_record_cpt - updates team record CPT
	// ARGUMENTS:
	//	$record_slug: string in format league-slug_season-slug_team-slug
	//	$league_slug: passed for convenience to set tax_input for wp_insert_post
	// RETURNS:
	// 	$record_id if record CPT was created and updated
	//  0 if record CPT exists but meta data could not be updated
	// -1 if record CPT could not be created
	//-------------------------------------------------------------
	function update_record_cpt( $record_slug, $league_slug, $season_slug, $team_id ) {
		//mstw_log_msg( 'in update_record_cpt ...' );
		
		//mstw_log_msg( '$record_slug: ' . $record_slug );
		
		// $_POST is an auto-global
		
		// See if there is a record with the $record_slug, if not, create one
		$args = array( 'post_type' => 'mstw_lm_record',
					   'name'      => $record_slug,
					   );
					   
		$records = get_posts( $args );
		
		//mstw_log_msg( 'records found: ' );
		//mstw_log_msg( $records );
		
		if( !$records ) {
			//mstw_log_msg( 'We do NOT have a record CPT ...' );
			// Create a record CPT with the right slug
			$args = array( 'post_name' => $record_slug,
						   'post_title' => $record_slug,
						   'post_status' => 'publish',
						   'post_type' => 'mstw_lm_record',
						   'tax_input' => array( 'mstw_lm_league' => $league_slug ),
						 );
						 
			// insert post should not trigger an save_post actions
			$record_id = wp_insert_post( $args, false );
			
			//if( $record_id ) {
				//mstw_log_msg( 'Created new record CPT with ID ' . $record_id );
			//}
		}
		else {
			//mstw_log_msg( 'We have a record CPT with ID ' . $records[0]->ID );
			$record_id = $records[0]->ID;
		}
		
		if ( $record_id ) {
			
			$record_meta = $this->build_record_meta( $league_slug, $season_slug, $team_id );
			
			//mstw_log_msg( "record_meta:" );
			//mstw_log_msg( $record_meta );
			
			if( $record_meta ) {
				//mstw_log_msg( 'updating record: ' . $record_id );
				foreach ( $record_meta as $key => $value ) {
					if ( 'points' == $key ) {
						$value = apply_filters( 'mstw_lm_points_calc', $value, $record_meta );
					}
					if ( 'percent' == $key ) {
						$value = apply_filters( 'mstw_lm_percent_calc', $value, $record_meta );
					}
					update_post_meta( $record_id, $key, $value );
					//mstw_log_msg( "record id: $record_id key: $key value: $value" );
					
				}
				
				$ret = wp_set_object_terms( $record_id, $league_slug, 'mstw_lm_league', false );
				//mstw_log_msg( "updated " . $record_id . " taxonomy with " . $league_slug );
				//mstw_log_msg( "returned: " );
				//mstw_log_msg( $ret );
				
				$retval = $record_id;
			}
			else {
				//mstw_log_msg( 'record meta data not updated for ' . $record_id );
				$retval = 0;
			}
		}
		else {
			//mstw_log_msg( 'record not created for ' . $record_slug );
			$retval = -1;	
		}
		
		return $retval;
		
	} //End: update_record_cpt()
	
	//-------------------------------------------------------------
	// build_record_meta - pulls one set of meta data from $_POST[]
	//		Uses the auto-global $_POST & assumes array pointer is pointed at the 'rank' element
	// ARGUMENTS:
	//	 $league_slug:
	//   $season_slug:
	//	 $team_id: 
	//   
	// RETURNS:
	//	 array of $key=>$value pairs for the record CPT
	//-------------------------------------------------------------
	function build_record_meta( $league_slug, $season_slug, $team_id ) {
		//mstw_log_msg( 'build_record_meta:' );
		
		// $_POST is an auto-global
		//mstw_log_msg( '$_POST:' );
		//mstw_log_msg( $_POST );
		
		// return value
		$meta_data = array( );
		
		// Collect the meta data from the $_POST array
		$test = each( $_POST );
		//mstw_log_msg( $test );
		$meta_data['rank'] = $test['value'];
		$meta_data['games_played'] = each( $_POST )['value'];
		$meta_data['wins'] = each( $_POST )['value'];
		$meta_data['losses'] = each( $_POST )['value'];
		$meta_data['ties'] = each( $_POST )['value'];
		$meta_data['otw'] = each( $_POST )['value'];
		$meta_data['otl'] = each( $_POST )['value'];
		$meta_data['goals_for'] = each( $_POST )['value'];
		$meta_data['goals_against'] = each( $_POST )['value'];
		$meta_data['games_behind'] = each( $_POST )['value'];
		$meta_data['streak'] = each( $_POST )['value'];
		$meta_data['last_5'] = each( $_POST )['value'];
		$meta_data['last_10'] = each( $_POST )['value'];
		$meta_data['home'] = each( $_POST )['value'];
		$meta_data['away'] = each( $_POST )['value'];
		$meta_data['conference'] = each( $_POST )['value'];
		$meta_data['division'] = each( $_POST )['value'];
		
		$sport_slug = mstw_lm_get_league_sport( $league_slug );
		$options = mstw_lm_get_sport_options( $sport_slug );
		
		$meta_data['points'] = 
				$meta_data['wins'] * $options['points_rules']['wins'] +
				$meta_data['losses'] * $options['points_rules']['losses'] +
				$meta_data['ties'] * $options['points_rules']['ties'] +
				$meta_data['otw'] * $options['points_rules']['otw'] +
				$meta_data['otl'] * $options['points_rules']['otl'];
				
		$meta_data['goals_diff'] = 
				$meta_data['goals_for'] - $meta_data['goals_against'];
		
		if ( $meta_data['games_played'] > 0 ) {
		$meta_data['percent'] = 
				( $meta_data['wins'] + 0.5 * $meta_data['ties'] ) /
				$meta_data['games_played'];
		}
		else {
			$meta_data['percent'] = '--';
		}
				
		// Add the league, season, and team
		$meta_data['league_slug'] = $league_slug;
		$meta_data['season_slug'] = $season_slug;
		$meta_data['team_slug'] = get_post( $team_id ) -> post_name;
		
		//mstw_log_msg( '$meta_data[]:' );
		//mstw_log_msg( $meta_data );
		
		return $meta_data;
		
	} //End: build_record_meta()
	
	//-------------------------------------------------------------------------------
	// For development/clean-up only. Deletes ALL team records.
	//
	function delete_all_records( ) {
		mstw_log_msg( 'in delete_all_records ...' );
		
		$records = get_posts( array( 'posts_per_page' => -1, 'post_type' =>'mstw_lm_record') );
		
		mstw_log_msg( "Deleting " . count( $records ) . " team records." );
		
		$records_deleted = 0;
		foreach( $records as $record ) {
			//delete, bypass trash
			if ( wp_delete_post( $record->ID ) ) {
				mstw_log_msg( 'Deleted record ' . $record->post_name  );
				$records_deleted++;
			}
		}
		mstw_log_msg( $records_deleted . ' records deleted.' );
		
		$teams = get_posts( array( 'posts_per_page' => -1, 'post_type' =>'mstw_lm_team') );
		
		mstw_log_msg( 'Removing record meta data from ' . count( $teams ) . ' teams.' );
		
		$meta_deleted = 0;
		foreach ( $teams as $team ) {
			if ( delete_post_meta( $team->ID, 'team_record', '' ) ) {
				mstw_log_msg( 'Deleted record meta for ' . $team->post_name );
				$meta_deleted++;
			} 	
		}
		mstw_log_msg( 'Deleted record meta for ' . $meta_deleted . ' teams.' );
		
	} //End: delete_all_records()
	
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
	// add help - outputs HTML for the contextual help area of the screen
	//		
	// ARGUMENTS:
	//	 None 
	//   
	// RETURNS:
	//	 Outputs HTML to the contextual help aree of the screen
	//-------------------------------------------------------------
	
	function add_help( ) {
		//mstw_log_msg( "in update records add_help" );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
				
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'records-overview',
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
			case 'records-overview':
				?>
				<p><?php _e( 'This screen allows update of the records of all teams in a league for a selected season. A LEAGUE and a SEASON must be selected. Teams for that league will be displayed, and records for that season will be updated.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'First select a LEAGUE and a SEASON, and press the Update Records Table button. The records for the selected league and season will be displayed (and updated).', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'The Win Percentage, Points, and Goal Differential fields are not available because they are calculated automatically.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-update-records/" target="_blank"><?php _e( 'See the Update Records man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()

	
} //End: class MSTW_LM_UPDATE_GAMES