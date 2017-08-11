<?php
/* ------------------------------------------------------------------------
 * 	CSV League Manager Importer Class
 *		- Modified from CSVImporter by Denis Kobozev (d.v.kobozev@gmail.com)
 *		- All rights flow through under GNU GPL.
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
 
	class MSTW_LM_CSV_IMPORTER {
		var $defaults = array(
			'csv_post_title'      => null,
			'csv_post_post'       => null,
			'csv_post_type'       => null,
			'csv_post_excerpt'    => null,
			'csv_post_date'       => null,
			'csv_post_tags'       => null,
			'csv_post_categories' => null,
			'csv_post_author'     => null,
			'csv_post_slug'       => null,
			'csv_post_parent'     => 0,
		);

		var $log = array();

		//
		// process_option checks/cleans up the $_POST values
		//
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
		// Builds the user interface for CSV Import screen
		//
		function form( ) {
			
			// check & cleanup the returned $_POST values
			$submit_value = $this->process_option( 'submit', 0, $_POST, false );
			
			//$opt_sched_id = $this->process_option( 'csv_importer_sched_id', 0, $_POST, false );
				
			$csv_move_logos = $this->process_option( 'csv_move_logos', 0, $_POST, true );
			
			$csv_import_league = $this->process_option( 'csv_import_league', 0, $_POST, false );
			
			//mstw_log_msg( 'in MSTW_LM_CSV_IMPORTER.form() ...' );
			//mstw_log_msg( 'request method: ' . $_SERVER['REQUEST_METHOD'] );
			//mstw_log_msg( '$_POST = ' );
			//mstw_log_msg( $_POST );
			
			//
			// We do the heavy lifting in the post( ) method
			//
			if ('POST' == $_SERVER['REQUEST_METHOD']) {
				$options = compact( 'submit_value', 'csv_move_logos', 'csv_import_league' );
				//mstw_log_msg( $options );
				$this->post( compact( 'submit_value', 'csv_move_logos', 'csv_import_league' ) );
			}

			// start form HTML {{{
			?>

			<div class="wrap">
				<?php //echo get_screen_icon(); ?>
				<h2><?php _e( 'Import CSV Files', 'mstw-league-manager' ); ?></h2>
				
				<p class='mstw-lm-admin-instructions'><?php _e( 'Venues (or locations) and teams may be imported from the MSTW Schedules & Scoreboards database, or from user created CSV files. Sample CSV files are provided in the plugin /csv-examples/ directory.', 'mstw-league-manager' ) ?></p>
				
				<!-- VENUES (LOCATIONS) import form -->
				<form class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
					<!-- Enter the league ID via text ... for now -->
					<table class='form-table'>
						<thead><tr><th><?php echo __( 'Venues (or Game Locations)', 'mstw-league-manager' ) ?></th></tr></thead>
						
						<tr>  <!-- CSV file selection field -->
							<td><label for="csv_venues_import"><?php _e( 'Venues CSV File:', 'mstw-league-manager' ); ?></label></td>
							<td><input name="csv_venues_import" id="csv_venues_import" type="file" value="" aria-required="true" /></td>
						</tr>
						<tr> <!-- Submit button -->
						<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Import Venues', 'mstw-league-manager' ); ?>"/></td>
						</tr>
					</table>
				</form>
				
				<!-- TEAMS import form -->
				<form class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
					<table class='form-table'>
						<thead><tr><th colspan=2><?php echo __( 'Teams', 'mstw-league-manager' ) ?><br/>
						<span class='mstw-lm-admin-instructions'><?php printf( __( 'The importer will use the "league" column in the CSV file to assign teams to leagues if that column is not empty.%s Otherwise, the team will be assigned to the league selected in the "Select League to Import" dropdown. %sOtherwise, the team will be imported but will not be assigned to any league, which is generally not a good practice.', 'mstw-league-manager' ), '<br/>', '<br/>' ) ?></span>
						</th></tr></thead>
						<tbody>
						<tr>  <!-- Team (to import) selection field -->
							<td><label for="csv_import_league"><?php _e( 'Select league to Import:', 'mstw-league-manager' ) ?></label></td>
							<td>
							<?php 
							$leagues = mstw_lm_build_leagues_list( );
							
							if( $leagues ) {
								//echo "Found leagues - build list";
								?>
								<select name="csv_import_league" id="csv_import_league">
								<?php foreach ( $leagues as $title => $slug ) { ?>
									<option value=<?php echo $slug ?>><?php echo $title ?> </option>
									
								<?php } ?>
								</select>
							<?php
							}
							else {
								_e( 'No leagues found.', 'mstw-league-manager' );
							}
							?>
							
							<br/>
							<span class='description' ><?php _e( 'This league will be used as the default if there is no entry for in the league column in the imported CSV file.', 'mstw-league-manager' ) ?></span>
							</td>
						</tr>
						
						<tr>  <!-- CSV file selection field -->
							<td><label for="csv_teams_import"><?php _e( 'Teams CSV File:', 'mstw-league-manager' ); ?></label></td>
							<td><input name="csv_teams_import" id="csv_teams_import" type="file" value="" aria-required="true" /></td>
						</tr>
						
						<tr>
							<td><label for="csv_move_logos"><?php _e( 'Move Team Logos:', 'mstw-league-manager') ?></label></td>
							<td><input name="csv_move_logos" id="csv_move_logos" type="checkbox" value="1" />
							<br/>
							<span class='description' ><?php _e( 'If checked, team logo files will be imported from their current locations to the media library. If unchecked, logos files will remain in their current locations.', 'mstw-league-manager' ) ?><br/>
							<?php _e( 'This option is only necessary when moving data to a new WP site.', 'mstw-league-manager' ) ?></span>
							</td>	
						</tr>
						
						<tr> <!-- Submit button -->
						<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Import Teams', 'mstw-league-manager' ); ?>"/></td>
						</tr>
						</tbody>
					</table>
				</form>
				
				<!-- GAMES import form
				<form class="add:the-list: validate" method="post" enctype="multipart/form-data">
					<!-- Enter the league ID via text ... for now
					<table class='form-table'>
					<thead>
						<tr><th colspan=2>
							<?php _e( 'Games', 'mstw-team-rosters' ) ?>
							<br/>
							<span class='description' style='font-weight: normal'><?php printf( __( 'The importer will use the "league-slug" column in the CSV file to assign games to leagues if that column is not empty.%s Otherwise, the game will be assigned to the league selected in the "Select League to Import" dropdown. %sOtherwise, the game will be imported but will not be assigned to a league, which is generally not a good practice.', 'mstw-league-manager' ), '<br/>', '<br/>' ) ?></span>
						</th></tr>
					</thead>
						
					<tbody>
						<tr>  <!-- Team (to import) selection field
							<td><label for="csv_import_league"><?php _e( 'Select league to Import:', 'mstw-league-manager' ) ?></label></td>
							<td>
							<?php 
							$leagues = mstw_lm_build_leagues_list( );
							
							if( $leagues ) {
								//echo "Found leagues - build list";
								?>
								<select name="csv_import_league" id="csv_import_league">
								<?php foreach ( $leagues as $title => $slug ) { ?>
									<option value=<?php echo $slug ?>><?php echo $title ?> </option>
									
								<?php } ?>
								</select>
							<?php
							}
							else {
								_e( 'No leagues found.', 'mstw-league-manager' );
							}
							?>
							<br/>
							<span class='description' ><?php _e( 'This league will be used as the default if there is no entry for in the game_sched_id column in the imported CSV file.', 'mstw-league-manager' ) ?></span>
							</td>
						</tr>
						<tr>  <!-- CSV file selection field 
							<td><label for="csv_games_import"><?php _e( 'Games CSV file:', 'mstw-league-manager' ); ?></label></td>
							<td><input name="csv_games_import" id="csv_games_import" type="file" value="" aria-required="true" /></td>
						</tr>
							
						<tr> <!-- Submit button 
						<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Import Games', 'mstw-league-manager' ); ?>"/></td>
						</tr>
						
					</tbody>
					
					</table>
				</form>
				-->
			</div><!-- end wrap -->
			<!-- end of form HTML -->
		<?php
		} //End of function form()
		
		/*-------------------------------------------------------------
		 *	Print Message Log
		 *-----------------------------------------------------------*/
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

		/*-------------------------------------------------------------
		 * Handle POST submission
		 *-----------------------------------------------------------*/
		function post( $options ) {
			//mstw_log_msg( 'In post method ... ' );
			//mstw_log_msg( $options );
			//mstw_log_msg( $_FILES );
			//mstw_log_msg( $_POST );
			if ( !$options ) {
				mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
				return;
			}
			
			// want to get rid of extract()
			//extract( $options );
			//$submit_value -> table to import
			
			switch( $options['submit_value'] ) {
				case __( 'Import Venues', 'mstw-league-manager' ):
					//mstw_log_msg( 'In post() method: Importing Venues ...' );
					$file_id = 'csv_venues_import';
					//$msg_str is only used in summary messages
					$msg_str =array( __( 'venue', 'mstw-league-manager' ),  __( 'venues', 'mstw-league-manager' ) );
					break;
					
				case __( 'Import Teams', 'mstw-league-manager' ):
					//mstw_log_msg( 'In post() method: Importing Teams ...' );
					$file_id = 'csv_teams_import';
					
					//$msg_str is only used in summary messages
					$msg_str =array( __( 'team', 'mstw-league-manager' ),  __( 'teams', 'mstw-league-manager' ) );
					break;
					
				/*
				case __( 'Import Games', 'mstw-league-manager' ):
					//mstw_log_msg( 'In post() method: Importing Games ...' );
					$file_id = 'csv_games_import';
					//$msg_str is only used in summary messages
					$msg_str =array( __( 'game', 'mstw-league-manager' ),  __( 'games', 'mstw-league-manager' ) );
					// Check that a file has been uploaded			
					break;
				*/
				
				default:
					//mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $submit_value . '. Exiting' );
					return;
					break;
			}
			
			if ( empty( $_FILES[$file_id]['tmp_name'] ) ) {
				//mstw_log_msg( 'In post() method: Looks like no file has been specified ?' );
				$this->log['error'][] = __( 'Select a CSV file to import. Exiting.', 'mstw-league-manager' );
				$this->print_messages();
				return;
			}

			if ( !class_exists( 'MSTW_CSV_DataSource' ) ) {
				require_once 'MSTWDataSource.php';
			}
			
			$time_start = microtime( true );
			$csv = new MSTW_CSV_DataSource;
			
			$file = $_FILES[$file_id]['tmp_name'];
			$this->stripBOM( $file );
			
			//echo '<p> Loading file ' . $_FILES[$file_id]['name'] . ' ... </p>';
			//mstw_log_msg( 'Loading file ' . $_FILES[$file_id]['name'] . ' ... ' );
			// Check that .csv file can be loaded
			if ( !$csv->load( $file ) ) {
				mstw_log_msg( 'Failed to load file. ' . $_FILES[$file_id]['name'] . '. Exiting.' );
				$this->log['error'][] = sprintf( __( 'Failed to load file %s. Exiting.', 'mstw-league-manager' ), $_FILES[$file_id]['name'] );
				$this->print_messages( );
				return;
			}

			//var_export( $csv->getHeaders( ) );
			//mstw_log_msg( $csv->getHeaders() );

			// pad shorter rows with empty values
			$csv->symmetrize( );

			// WordPress sets the correct timezone for date functions 
			// somewhere in the bowels of wp_insert_post(). We need 
			// strtotime() to return correct time before the call to
			// wp_insert_post().
			// mstw_set_wp_default_timezone( ); 

			$skipped = 0;
			$imported = 0;
			$comments = 0;
			foreach ( $csv->connect( ) as $csv_data ) {
				//mstw_log_msg( '$csv_data ... ' );
				//mstw_log_msg( $csv_data );
				
				if ( empty( $csv_data ) or !$csv_data ) {
					mstw_log_msg( 'No CSV data. $csv_data is empty.' );
				}
				
				// First try to create the post from the row
				if ( $post_id = $this->create_post( $csv_data, $options, $imported+1 ) ) {
					$imported++;
					//Insert the custom fields, which is most everything
					switch ( $file_id ) {
						case 'csv_venues_import':
							$this->create_venue_fields( $post_id, $csv_data );
							break;
						case 'csv_games_import':
							$this->create_game_fields( $post_id, $csv_data, $options );
							break;
						case 'csv_teams_import':
							$this->create_team_fields( $post_id, $csv_data, $options );
							break;
						case 'csv_leagues_import':
							$this->create_league_fields( $post_id, $csv_data );
							break;
						case 'csv_sports_import':
							$this->create_sport_fields( $post_id, $csv_data );
							break;
						default:
							mstw_log_msg( 'Oops, something went wrong with file ID: ' . $file_id );
							break;
					}
				} else {
					$skipped++;
				}
			}

			if ( file_exists($file) ) {
				@unlink( $file );
			}

			$exec_time = microtime( true ) - $time_start;

			if ($skipped) {
				$this->log['notice'][] = sprintf( __( 'Skipped %s %s (most likely due to empty title.)', 'mstw-league-manager' ), $skipped, $msg_str[1] );
				//$this->log['notice'][] = "<b>Skipped {$skipped} posts (most likely due to empty title, body and excerpt).</b>";
			}
			
			$this->log['notice'][] = sprintf( __( 'Imported %s %s to database in %.2f seconds.','mstw-league-manager' ), $imported, $msg_str[1], $exec_time );
			//$this->log['notice'][] = sprintf("<b>Imported {$imported} posts to {$term->slug} in %.2f seconds.</b>", $exec_time);
			
			$this->print_messages();
		} //End: post()
		
		/*-------------------------------------------------------------
		 *	Build a post from a row of CSV data
		 *-----------------------------------------------------------*/
		function create_post( $data, $options, $cntr ) {
			//mstw_log_msg( 'In create_post() ... $data' );
			//mstw_log_msg( $data );
			//mstw_log_msg( $options );

			$data = array_merge( $this->defaults, $data );
			
			// figure out what custom post type we're importing
			//switch ( $submit_value ) {
			switch( $options['submit_value'] ) {
				case __( 'Import Venues', 'mstw-league-manager' ) :
					//mstw_log_msg( ' We are importing venues ... ' );
					$type = 'mstw_lm_venue';
					//this is used to add_action/remove_action below
					$save_suffix = 'venue_meta';
					
					// need a venue title to proceed
					if ( isset( $data['venue_title'] ) && !empty( $data['venue_title'] ) ) {
						$temp_title = $data['venue_title'];
					}
					else { //no title => skip this entry
						mstw_log_msg( 'Skipping entry ... no title' );
						return false;
					}
					
					// slug should come from CSV file; else will default to sanitize_title()
					$temp_slug = ( isset( $data['venue_slug'] ) && !empty( $data['venue_slug'] ) ) ? $data['venue_slug'] : sanitize_title( $temp_title, __( 'No title imported', 'mstw-league-manager' ) );;
					break;
					
				case __( 'Import Teams', 'mstw-league-manager' ) :
					//mstw_log_msg( ' We are importing teams ... ' );
					$type = 'mstw_lm_team';
					//this is used to add_action/remove_action below
					$save_suffix = 'team_meta';
					
					// team title should come from CSV file; else try to create from team name and mascot
					if ( isset( $data['team_title'] ) && !empty( $data['team_title'] ) ) {
						$temp_title = $data['team_title'];
					}
					else { //no team title => try to create from team name and mascot
						$temp_title = 'No team title';
						if ( isset( $data['team_name'] ) ) {
							$temp_title = $data['team_name'];
						}
						if ( isset( $data['team_mascot'] ) ) {
							$temp_title .= ' ' . $data['team_mascot'];
						}
					}
					
					// slug should come from CSV file; else will default to sanitize_title()
					$temp_slug = ( isset( $data['team_slug'] ) && !empty( $data['team_slug'] ) ) ? $data['team_slug'] : sanitize_title( $temp_title, __( 'No title imported', 'mstw-league-manager' ) );
					break;
		
				case __( 'Import Games', 'mstw-league-manager' ) :
					//mstw_log_msg( ' We are importing games ... ' );
					$type = 'mstw_lm_game';
					//this is used to add_action/remove_action below
					$save_suffix = 'game_meta';
						
					// game title should come from CSV file; else create from slug
					if ( isset( $data['game_title'] ) && !empty( $data['game_title'] ) ) {
						$temp_title = $data['game_title'];
					}
					else { //no game title => create from game slug, which we already know exists
						$temp_title = __( 'No title imported', 'mstw-league-manager' );
						//$temp_title .= $data['game_slug'];
					}
					
					// If no game slug is provided, create slug from game title
					// slug should come from CSV file; else will default to sanitize_title()
					$temp_slug = ( isset( $data['venue_slug'] ) && !empty( $data['venue_slug'] ) ) ? $data['venue_slug'] : sanitize_title( $temp_title, __( 'No title imported', 'mstw-league-manager' ) );
					
					if ( !isset( $data['game_slug'] ) or empty( $data['game_slug'] ) ) {
						// convert title to slug
						$temp_slug = sanitize_title( $temp_title, __( 'No title imported', 'mstw-league-manager' ) );
					}
					else {
						$temp_slug = $data['game_slug'];
					}
					
					//
					// Added for compatibility ... __DIR__ was not defined until WP 5.3
					//
					if ( !defined( '__DIR__' ) ) {
					   define( '__DIR__', dirname( __FILE__ ) );
					}
					
					break;
				
				default:
					mstw_log_msg( 'Whoa horsie ... $submit_value = ' . $options['submit_value'] );
					$this->log['error']["type-{$type}"] = sprintf(
						__( 'Unknown import type "%s".', 'mstw-league-manager' ), $type );
					return false;
					break;
			}

			// Build the (mostly empty) post
			$new_post = array(
				'post_title'   => convert_chars( $temp_title ),
				'post_content' => '', //wpautop(convert_chars($data['Bio'])),
				'post_status'  => 'publish',
				'post_type'    => $type,
				'post_name'    => $temp_slug,
			);
			
			// create it
			
			remove_action( 'save_post_' . $type, 'mstw_lm_save_' . $save_suffix, 20, 2 );
			$post_id = wp_insert_post( $new_post );
			add_action( 'save_post_' . $type, 'mstw_lm_save_' . $save_suffix, 20, 2 );
			
			return $post_id;
			
		} //End function create_post()

		
		/*-------------------------------------------------------------
		 *	Add the fields from a row of CSV game data to a newly created post
		 *-----------------------------------------------------------*/
		function create_game_fields( $post_id, $data, $options ) {
			//mstw_log_msg( 'in create_game_fields with $options =' );
			//mstw_log_msg( $options );
			
			foreach ( $data as $k => $v ) {
				$k = strtolower( $k );
				switch (  $k ) {
					case 'game_title':
					case 'game_slug':
						//created with the post; nothing else to do here
						break;
						
					// BASIC GAME DATA
					// special handling for checkbox fields
					case 'game_is_final':
					case 'game_is_home_game':
						// have to convert home and empty string to 1 and 0
						$v = ( empty( $v ) ) ? 0 : 1;
						// fallthru is intentional
					case 'game_time_tba':
					case 'game_unix_dtg':

					case 'game_opponent_team': //from team DB
					case 'game_gl_location': //from venues DB - neutral sites
					
					//LEGACY STUFF (DEPRECATED)	
					case 'game_opponent':
					case 'game_opponent_link':						
					case 'game_location':
					case 'game_location_link':
					
					//GAME STATUS STUFF
					case 'game_our_score':
					case 'game_opp_score':
					case 'game_curr_period':
					case 'game_curr_time':
					case 'game_result':	
					
					//MEDIA STUFF
					case 'game_media_label_1':
					case 'game_media_label_2':
					case 'game_media_label_3':
					case 'game_media_url_1':
					case 'game_media_url_2':
					case 'game_media_url_3':
						$ret = update_post_meta( $post_id, $k, $v );
						break;
						
					case 'game_scoreboard':
						if( !empty( $v ) ) {
							//mstw_log_msg( 'CSV Game Scoreboard string: ' . $v );
							$scoreboards = array_filter( str_getcsv( $v, ';', '"' ) );
							$result = wp_set_object_terms( $post_id, $scoreboards, 'mstw_lm_scoreboard', false );
							//mstw_log_msg( $result );
						}
						break;
						
					case 'game_dtg':
						// Need to convert to a UNIX dtg stamp and store
						$k = 'game_unix_dtg';
						$v = strtotime( $v );
						$v = ( $v < 0 or $v === false ) ? current_time( 'timestamp' ) : $v ;
						$ret = update_post_meta( $post_id, $k, $v );
						break;
						
					case 'game_sched_id':
					    // if the 'game_sched_id' value is not empty(in the CSV file) use it; else use the
						//	league from the control on the admin screen
						//if( empty( $v ) && array_key_exists( 'csv_import_league', $options ) && $options['csv_import_league'] ) {
						if( empty( $v ) && array_key_exists( 'csv_import_league', $options ) ) {							
							$v = $options['csv_import_league'];
						}
						$ret = update_post_meta( $post_id, $k, $v );
							
						break;

					default:
						// bad column header
						mstw_log_msg( 'Unrecognized game data field: ' . $k );
						break;
						
				}
			}
		} //End of function create_game_fields()
		
		//-------------------------------------------------------------
		//	Add the fields from a row of CSV venue data to a newly created post
		//-------------------------------------------------------------
		function create_venue_fields( $post_id, $data ) {

			foreach ( $data as $k => $v ) {
				// anything that doesn't start with csv_ is a custom field
				if (!preg_match('/^csv_/', $k) && $v != '') {
					switch ( strtolower( $k ) ) {
						case 'venue_title':
						case 'venue_slug':
							//created with the post; nothing else to do here
							break;
						case 'venue_street':
						case 'venue_city':	
						case 'venue_state':
						case 'venue_zip':	
						case 'venue_url':
							$k = strtolower( $k );
							$ret = update_post_meta( $post_id, $k, $v );
							break;
						default:
							// bad column header
							mstw_log_msg( 'Unrecognized venue data field: ' . $k );
							break;
					}
					
					
				}
			}
		} //End of function create_venue_fields()
			
		//-------------------------------------------------------------
		//	Add the fields from a row of CSV team data to a newly created post
		//-------------------------------------------------------------
		function create_team_fields( $post_id, $data, $options ) {
			//mstw_log_msg( 'in create_team_fields ...' );
			//mstw_log_msg( $data );
			//mstw_log_msg( $options );
			
			//
			// Handle the team_league field. 
			// If it's there, use it. If not, try to use the option.
			//
			if( key_exists( 'team_league', $data ) && '' != $data['team_league'] ) {
				$v = $data['team_league'];
				//mstw_log_msg( '$v = ' . $v );
				$leagues = array_filter( str_getcsv( $v, ';', '"' ) );
				$result = wp_set_object_terms( $post_id, $leagues, 'mstw_lm_league', false );
				//mstw_log_msg( $result );
			}
			else { // Try to use the league option
				if ( key_exists( 'csv_import_league', $options ) ) {
					if ( -1 != $options['csv_import_league'] && '' != $options['csv_import_league'] ) {
						$result = wp_set_object_terms( $post_id, $options['csv_import_league'], 'mstw_lm_league', false );
					}
				}
			}
			
			foreach ( $data as $k => $v ) {
				
				// new key to adjust database names from S&S to LM
				$nk = '';
				switch ( strtolower( $k ) ) {
					case 'team_title':
					case 'team_slug':
						//handled when creating the post
						break;
					case 'team_league':
						if( !empty( $v ) ) {
							//mstw_log_msg( 'CSV Game Scoreboard string: ' . $v );
							$leagues = array_filter( str_getcsv( $v, ';', '"' ) );
							$result = wp_set_object_terms( $post_id, $leagues, 'mstw_lm_league', false );
							//mstw_log_msg( $result );
						}
						break;
					case 'team_full_name':
						$nk = 'team_name';
						//mstw_log_msg( 'changing team_full_name to team_name' );
					
					case 'team_full_mascot':
						$nk = ( empty( $nk ) ) ? 'team_mascot' : $nk;
						//mstw_log_msg( 'changing team_full_mascot to team_mascot' );
					
					case 'team_name':       //ok	
					case 'team_short_name':	//ok
					case 'team_mascot':     //ok					
					case 'team_home_venue': //ok
					case 'team_link':       //ok
						$nk = ( empty( $nk ) ) ? $k : $nk;
						//$k = strtolower( $k );
						//mstw_log_msg( "updating $nk => $v" );
						$ret = update_post_meta( $post_id, strtolower( $nk ), $v );
						break;
						
					// need some special handling for the logos
					case 'team_logo':      // Small logo (for tables) 
					case 'team_alt_logo':  // Large logo (for galleries)
						$nk = ( empty( $nk ) ) ? 'team_alt_logo' : $nk;
						//$url will eventually be $v (logos not moved)
						//	or a file in the media library (logos moved)
						$url = '';
						if( !empty( $v ) ) {
							if( array_key_exists( 'csv_move_logos', $options ) and $options['csv_move_logos'] ) {
								//Going to move logos from another server
								//Try to download the logo file
								$temp_logo = download_url( $v );
								
								//Check for download errors
								if( is_wp_error( $temp_logo ) ) {
									mstw_log_msg( "Error downloading: $v" );
									mstw_log_msg( $temp_logo );
								}
								else { //Successfully downloaded file
									$file_array = array( 'name' => basename( $v ),
														'tmp_name' => $temp_logo,
													  );
									//Try to add file to media library & attach to team (CPT)
									$id = media_handle_sideload( $file_array, 0 );
									
									//Check for sideload errors
									if( is_wp_error( $id ) ) {
										mstw_log_msg( "Error loading file to media library: $temp_logo" );
										mstw_log_msg( $id );	
									} 
									else {
										//Successful sideload to media library. Update the team CPT
										$url = wp_get_attachment_url( $id );
										
									} //End: successful sideload to media library
								} //End: successfully downloaded file
							} //End: if( array_key_exists( 'csv_move_logos', $options )
							else {
								$url = $v;	
							}
							
							//Finally we're going to update the DB
							$k = strtolower( $k );
							
							if( empty( $url ) or mstw_is_valid_url( $url ) ) {
								// url is empty or is valid, so update the DB
								update_post_meta( $post_id, $k, esc_url( $url ) );
								//mstw_log_msg( "updated post: $post_id, $k" );
							}
							else {
								mstw_log_msg( 'in mstw_validate_url ... got a bad URL: ' . $url );
								// url is not valid, display an error message (don't update DB)
								$notice .= ' ' . $url;			
								if ( function_exists( 'mstw_add_admin_notice' ) ) {
									mstw_add_admin_notice( 'mstw_admin_messages', 'error', "Invalid url: $url" );
								}
							}
							
						} // End: if( !empty( $v ) )
						break;
						
					default:
						// bad column header
						mstw_log_msg( 'Unrecognized team data field: ' . $k );
						break;
						
				} //End: switch ( strtolower( $k ) )	
			} //End: foreach( $data as $k => $v )
		} //End: create_team_fields( )


		/*-------------------------------------------------------------
		 *	Add the fields from a row of CSV data to a newly created post
		 *-----------------------------------------------------------*/
		function stripBOM($fname) {
			$res = fopen($fname, 'rb');
			if (false !== $res) {
				$bytes = fread($res, 3);
				if ($bytes == pack('CCC', 0xef, 0xbb, 0xbf)) {
					$this->log['notice'][] = 'Getting rid of byte order mark...';
					fclose($res);

					$contents = file_get_contents($fname);
					if (false === $contents) {
						trigger_error('Failed to get file contents.', E_USER_WARNING);
					}
					$contents = substr($contents, 3);
					$success = file_put_contents($fname, $contents);
					if (false === $success) {
						trigger_error('Failed to put file contents.', E_USER_WARNING);
					}
				} else {
					fclose($res);
				}
			} else {
				$this->log['error'][] = 'Failed to open file, aborting.';
			}
		} //End: stripBOM( )
		
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
			//mstw_log_msg( "in CSV Importer add_help" );
			
			$screen = get_current_screen( );
			// We are on the correct screen because we take advantage of the
			// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
			
			//mstw_log_msg( "current screen:" );
			//mstw_log_msg( $screen );
			
			mstw_lm_help_sidebar( $screen );
			
			$tabs = array( array(
							'title'    => __( 'Overview', 'mstw-league-manager' ),
							'id'       => 'csv-importer-overview',
							'callback'  => array( $this, 'add_help_tab' ),
							),
						 );
						 
			foreach( $tabs as $tab ) {
				$screen->add_help_tab( $tab );
			}
			
		} //End: add_help( )

		function add_help_tab( $screen, $tab ) {
			//mstw_log_msg( "in CSV Importer add_help_tab ... " );
			
			if( !array_key_exists( 'id', $tab ) ) {
				return;
			}
				
			switch ( $tab['id'] ) {
				case 'csv-importer-overview':
					?>
					<p><?php _e( 'This screen allows import of Venues and Teams from files in CSV format.', 'mstw-league-manager' ) ?></p>
					<p><?php _e( 'Importing Venues is straightforward, simply choose a properly formatted CSV file. Several options offer more flexibility in Importing Teams, so this can be a bit more complicated.', 'mstw-league-manager' ) ?></p>
					<p><a href="http://shoalsummitsolutions.com/lm-csv-import/" target="_blank"><?php _e( 'See the CSV Import man page for more details on using this page and CSV file formats.', 'mstw-league-manager' ) ?></a></p>
					<?php				
					break;
				
				default:
					break;
			} //End: switch ( $tab['id'] )

		} //End: add_help_tab()
	} //End of class
?>