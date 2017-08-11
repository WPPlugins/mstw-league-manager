<?php
 /*----------------------------------------------------------------------------
 * MSTW League Manager Settings Class ( mstw-lm-settings-class.php)
 *	All functions for the MSTW Schedules & Scoreboards Plugin settings.
 *		Loaded conditioned on is_admin() in mstw-ss-admin.php 
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
 
class MSTW_LM_SETTINGS {
	
	// Don't need this anymore?
	var $log = array();
	
	// Could be useful for pagination?
	private $table_size = 10;
	
	//-------------------------------------------------------------
	//	form - builds the user interface for the Update Games screen
	//-------------------------------------------------------------
	function form( ) {
		//mstw_log_msg( 'in MSTW_LM_SETTINGS->form ...' );
		
		global $pagenow;
		
		//
		// We do the heavy lifting in the post( ) method
		//
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			// Check & cleanup the returned $_POST values
			$submit_value = $this->process_option( 'submit', 0, $_POST, false );
			$this->post( compact( 'submit_value' ) );
		}
		//mstw_log_msg( 'request method: ' . $_SERVER['REQUEST_METHOD'] );
		//mstw_log_msg( '$_POST =' );
		//mstw_log_msg( $_POST );
		
		mstw_lm_admin_notice( );
		//do_action( 'admin_notices' );
		
		//
		// Heavy lifting done; now build the HTML UI/form
		//
		$current_sport = mstw_lm_get_current_sport( );
		
		?>
		
		
		<div class="wrap">
		 <h2><?php _e( 'League Manager Settings', 'mstw-league-manager' )?></h2>
	
		 
		 <?php 
		 $this -> build_sport_select_control( ); 
		 
		 //$current_tab = 'standings-tab';
		 
		 if ( isset( $_GET ) && array_key_exists( 'tab', $_GET ) ) {
			 $current_tab = $_GET['tab'];
		 }
		 else {
			 $current_tab = 'standings-tab';
		 }
		 
		$this -> build_tab_controls( $current_tab );
			 
		 switch( $current_tab ) {
			case 'schedules-table-tab':
				$this -> build_schedules_table_tab( );
				break;
			case 'schedules-tab':
				$this -> build_schedules_tab( );
				break;
			case 'standings-table-tab':
				$this -> build_standings_table_tab( );
				break;
			case 'standings-tab':
			default:
				$this -> build_standings_tab( );
				break;
		 }
		 ?>
 
		</div> <!-- end .wrap -->
		<!-- end of form HTML -->
			
		<?php
	} //End of function form( )
	
	//-------------------------------------------------------------------------------
	// Output the HTML for the sport selection control
	//
	
	function build_sport_select_control ( ) {
		//mstw_log_msg( 'in build_sport_select_control ...' );
		?>
		<form id="main-sport" method="post" enctype="multipart/form-data" action="">
		  <div class='tablenav top'>
				<?php
				// Select sport select control
				$current_sport = mstw_lm_get_current_sport( );
				//mstw_log_msg( '$current_sport: ' . $current_sport );
				$ret = mstw_lm_build_sport_select( $current_sport, 'main_sport' );
				
				if ( -1 != $ret ) {
					// Add the submit button
				?>
					<input type="submit" name="submit" id="select_sport" class="button" 
					value="<?php _e( 'Select Sport', 'mstw-league-manager' ) ?>" />
				<?php
				}
				else { //No sports were found ... should NOT HAPPEN.
					?>
					<p class='mstw-lm-admin-instructions'>
					 <?php _e( 'No sports found. See your system admin.', 'mstw-league-manager' ) ?>
					</p>
				<?php
				}
				?>
		  </div> <!-- .tablenav top -->
		 </form>
	<?php	 
	} //End: build_sport_select_control( )
	
	//-------------------------------------------------------------------------------
	// Create admin page tabs
	//
	function build_tab_controls( $current_tab = 'standings-tab' ) {
		//mstw_log_msg( 'in build_tabs ...' );
		
		//mstw_log_msg( "current_tab = $current_tab" );
		
		$tabs = $this -> get_settings_tabs( );
		//mstw_log_msg( '$tabs:' );
		//mstw_log_msg( $tabs );
		?>
		<h2 class="nav-tab-wrapper">
		 <?php
		 foreach( $tabs as $tab => $name ) {
			$class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
			?>
			<a class = "nav-tab<?php echo $class?>" 
			   href  = "admin.php?page=mstw-lm-settings&tab=<?php echo $tab ?>">
			   <?php echo $name ?></a>	
         <?php
		 }
		 ?>
		</h2>
		
	<?php
	} //End: build_tab_controls( )
	
	
	//-------------------------------------------------------------------------------
	// get_settings_tabs - returns array of tabs in slug => label format 
	//

	function get_settings_tabs( ) {
		//mstw_log_msg( " In get_settings_tabs ... " );
		
		return array( 'standings-tab'       => __( 'Standings', 'mstw-league-manager' ),
					  'standings-table-tab' => __( 'Standings Tables', 'mstw-league-manager' ),
					  'schedules-tab'       => __( 'Schedules', 'mstw-league-manager' ),
					  'schedules-table-tab' => __( 'Schedule Tables', 'mstw-league-manager' ),	
					);
		
	} //End: get_settings_tabs()
	//-------------------------------------------------------------------------------
	// Create the (form in the) schedules settings tab
	//
	function build_standings_tab( ) {
		//mstw_log_msg( 'in build_schedules_tab ...' );
		
		$sport = mstw_lm_get_current_sport( );
		//mstw_log_msg( "Current sport: $sport" );
		?>
		
		<!--<h2>Standings Tab</h2>-->
		
		<?php
		if ( -1 == $sport ) {
		?>
			<p class="mstw-lm-admin-instructions"> <?php _e( 'Select a sport before continuing.', 'mstw-league-manager' ) ?></p>
		<?php
		}
		else {
			$options = mstw_lm_get_sport_options( $sport );
			//$options = mstw_lm_get_nhl_defaults( );
			
			$standings_name_format    = $options['standings_name_format'];
			$standings_show_logo      = $options['standings_show_logo'];
			
			$order_by                 = $options['order_by'];
			$order_by_secondary       = $options['order_by_secondary'];
			
			$points_rules             = $options['points_rules'];
			
			// To handle older sites and options created before these existed.
			$standings_team_link      = array_key_exists( 'standings_team_link', $options ) ? $options['standings_team_link'] : 'none';
			
			$standings_next_game_link = array_key_exists( 'standings_next_game_link', $options ) ? $options['standings_next_game_link'] : 'none';
			
		?>
			<!--<ul class='mstw-lm-admin-instructions'>
			 <li> <?php _e( 'Do we have any instructions?', 'mstw-league-manager' ) ?> </li>
			</ul>-->
			<!--<div id="lm-settings standings-settings" >-->
			<form class='lm-settings' id="standings-settings" method="post" enctype="multipart/form-data" action="">
			 <input type="hidden" name="settings" value="standings" />
			 
			 <table id="standings-settings" class="form-table mstw-lm-admin-table widefat" >
			  
			  <tbody>
			   <tr> <!-- First section heading -->
			     <th colspan="2"><div class='heading'>
				  <?php _e( 'TABLE FORMAT', 'mstw-league-manager' ) ?>
				 </div></td>
			   </tr>
			   
			   <tr> <!-- Team name and logo formats -->
				<th><div class="col-indent-1"><?php _e( 'Team Name Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				 <?php
				 $args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => 'standings_name_format', 
							'default'	 => 'name',
							'curr_value' => $standings_name_format,
							'options' 	 => array( 
											'Team Name (only)'   => 'name',
											'Team Mascot (only)' => 'mascot',
											'Team Name & Mascot' => 'name_mascot'
											), 
							);
				 mstw_lm_build_admin_edit_field( $args );
				 ?>
			   </td>
			   
			   <th class="right"><?php _e( 'Show Team Logo:', 'mstw-league-manager' ) ?></th>
			    <?php
				$args = $defaults = array(
							'type'		 => 'checkbox',
							'id'      	 => 'standings_show_logo',
							'default'    => '0',
							'curr_value' => $standings_show_logo,
							'desc'       => __( 'Show team logo with team name.', 'mstw_league_manager' )
							);
				?>
			    <td><?php mstw_lm_build_admin_edit_field( $args )?> </td>
			   </tr>
			   
			   <tr> <!-- Team name and next game links -->
				<th><div class="col-indent-1"><?php _e( 'Team Name Link:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				 <?php
				 $this -> build_team_link_control( 'standings_team_link', $standings_team_link );
				 ?>
			    </td>
			   
			    <th class="right"><?php _e( 'Next Game Link:', 'mstw-league-manager' ) ?></th>
			    <td>
			     <?php
				 $this -> build_next_game_link_control( 'standings_next_game_link', $standings_next_game_link ); ?>
				</td>
			   </tr>
			   
			   <tr> <!-- Standings order -->
			    <th><div class="col-indent-1"><?php _e( 'Standings Order:', 'mstw-league-manager') ?></div></th>
			    <td>
				<?php
				 $args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => 'order_by',
							'default'    => 'rank',
							'curr_value' => $order_by,
							'options' 	 => array( 
											'Rank'   => 'rank',
											'Win Percentage' => 'percent',
											'Points' => 'points'
											), 
							);
				 mstw_lm_build_admin_edit_field( $args );
				 ?>
				 </td>
			   </tr>
			   
			   <tr>
			    <th colspan="2"><?php _e( 'POINTS RULES', 'mstw-league-manager' ) ?></td>
			   </tr>
			   <tr>
			    <th><div class="col-indent-1">
				<?php _e( 'Wins:', 'mstw-league-manager' ) ?>
				</div></th>
			    <td><input type="text" value="<?php echo $points_rules['wins']?>" id="pts_win" name="pts_win"/></td>
			   </tr>
			   <tr>
				<th><div class="col-indent-1">
				<?php _e( 'Losses:', 'mstw-league-manager' ) ?>
				</div></th>
			    <td><input type="text" value="<?php echo $points_rules['losses']?>" id="pts_loss" name="pts_loss"/></td>
			   </tr>
			   <tr>
			    <th><div class="col-indent-1">
				<?php _e( 'Ties:', 'mstw-league-manager' ) ?>
				</div></th>
			    <td><input type="text" value="<?php echo $points_rules['ties']?>" id="pts_tie" name="pts_tie"/></td>
			   </tr>
			   <tr>
				<th><div class="col-indent-1">
				<?php _e( 'Overtime Wins:', 'mstw-league-manager' ) ?>
				</div></th>
			    <td><input type="text" value="<?php echo $points_rules['otw']?>" id="pts_otw" name="pts_otw"/></td>
			   </tr>
			   <tr>
				<th><div class="col-indent-1">
				<?php _e( 'Overtime Losses:', 'mstw-league-manager' ) ?>
				</div></th>
			    <td><input type="text" value="<?php echo $points_rules['otl']?>" id="pts_otl" name="pts_otl"/></td>
			   </tr>
			   <tr>
			    <td>
			      <?php submit_button( "Save Changes", "primary", "submit", false, null ); ?>
				</td>
			   </tr>
			  </tbody>
			 </table>
			 
			</form>
		
		<?php
		} //End: else
	} //End: build_standings_tab( )
	
	//-------------------------------------------------------------------------------
	// Create the (form in the) schedules settings tab
	//
	function build_schedules_tab( ) {
		//mstw_log_msg( 'in build_schedules_tab ...' );
		
		$sport = mstw_lm_get_current_sport( );
		//mstw_log_msg( "Current sport: $sport" );
		?>
		
		<?php
		if ( -1 == $sport ) {
		?>
			<p class="mstw-lm-admin-instructions"> <?php _e( 'Select a sport before continuing.', 'mstw-league-manager' ) ?></p>
		<?php
		}
		else {
			//delete_option( "$sport" . "_options");
			//$default_options = mstw_lm_get_sport_options( 'general' );
			//mstw_log_msg( 'default options:' );
			//mstw_log_msg( $default_options );
			
			$options   = mstw_lm_get_sport_options( $sport );
			//mstw_log_msg( 'ncaa football options:' );
			//mstw_log_msg( $options );
			
			$schedules_name_format  = $options['schedules_name_format'];
			$schedules_show_logo    = $options['schedules_show_logo'];
			
			$location_format        = $options['location_format'];
			
			$tbd_label              = $options['tbd_label'];
			
			$schedule_team_link		= $options['schedule_team_link'];
			$schedule_location_link	= $options['schedule_location_link'];
			$schedule_time_link		= $options['schedule_time_link'];
			
			//$show_byes              = $options['show_byes'];
			
			$date_format            = $options['date_format'];
			$time_format            = $options['time_format'];
			
			$gallery_date_format    = $options['gallery_date_format'];
			$gallery_time_format    = $options['gallery_time_format'];
			
			$scoreboard_date_format = $options['scoreboard_date_format'];
			$scoreboard_time_format = $options['scoreboard_time_format'];
			
			$live_updates_on        = $options['live_updates_on'];
			
			$live_updates_end       = $options['live_updates_end'];
		
		?>
			<form class='lm-settings' id="schedules-settings" method="post" enctype="multipart/form-data" action="">
			 <input type="hidden" name="settings" value="schedules" />
			 
			 <table id="schedules-settings" class="form-table mstw-lm-admin-table widefat" >
			  <tbody>
			    <tr>
			     <th colspan="2"><?php _e( 'NAME & LOCATION FORMATS', 'mstw-league-manager' ) ?></td>
			    </tr>
				
			    <tr>
				<th><div class="col-indent-1"><?php _e( 'Team Name Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				 <?php
				 $args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => 'schedules_name_format', 
							'default'	 => 'name',
							'curr_value' => $schedules_name_format,
							'options' 	 => array( 
											'Team Name (only)'   => 'name',
											'Team Mascot (only)' => 'mascot',
											'Team Name & Mascot' => 'name_mascot'
											), 
							);
				 mstw_lm_build_admin_edit_field( $args );
				 ?>
			   </td>
			   <th class="right"><?php _e( 'Show Team Logo:', 'mstw-league-manager' ) ?></th>
				<?php
				$args = $defaults = array(
							'type'		 => 'checkbox',
							'id'      	 => 'schedules_show_logo',
							'default'    => '0',
							'curr_value' => $schedules_show_logo,
							'desc'       => __( 'Show team logo with team name.', 'mstw_league_manager' )
							);
				?>
				<td><?php mstw_lm_build_admin_edit_field( $args )?> </td>
			   </tr>
			   
			   <tr>
			    <th><div class="col-indent-1"><?php _e( 'Location Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				<?php
				 $args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => 'location_format',
							'default'    => 'stadium',
							'curr_value' => $location_format,
							'options' 	 => array( 
											__( 'Stadium Only', 'mstw_league_manager' ) => 'stadium',
											__( 'Stadium (City)', 'mstw_league_manager' ) => 'stadium_city',
											__( 'Stadium (City, State)', 'mstw_league_manager' ) => 'stadium_city_state', 
											),
							);
				 mstw_lm_build_admin_edit_field( $args );
				 ?>
				 </td>
				 <th class="right"><?php _e( 'TBA Format:', 'mstw-league-manager' ) ?></th>

				 <td>
				<?php
				 $tbd_options = apply_filters( 'mstw_lm_tbd_list', 
												array(	__( 'TBA', 'mstw_league_manager' )    => 'TBA',
														__( 'T.B.A.', 'mstw_league_manager' ) => 'T.B.A.',
														__( 'TBD', 'mstw_league_manager' )    => 'TBD',
														__( 'T.B.D.', 'mstw_league_manager' ) => 'T.B.D.',
														)
											  );
											
				 $args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => 'tbd_label',
							'default'    => 'TBD',
							'curr_value' => $tbd_label,
							'options' 	 => $tbd_options,
							);
				 mstw_lm_build_admin_edit_field( $args );
				 ?>
				 </td>
			   </tr>
			   
			   <tr> <!-- Team Name & Schedule Links -->
			    <th><div class="col-indent-1"><?php _e( 'Team Name Link:', 'mstw-league-manager' ) ?></div></th>
				<td>
				 <?php
				 $this -> build_team_link_control( 'schedule_team_link', $schedule_team_link );
				 ?>
				</td>
				<th class="right"><?php _e( 'Location Link:', 'mstw-league-manager' ) ?></th>
				<td>
				 <?php
				 $this -> build_location_link_control( 'schedule_location_link', $schedule_location_link );
				 ?>
				</td>
			   </tr>
			    
			   <!--
			   <tr>
			    <th><div class="col-indent-1"><?php //_e( 'Show BYEs:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				 <?php
				 /*
				 $args = $defaults = array(
							'type'		 => 'checkbox',
							'id'      	 => 'show_byes',
							'default'    => '0',
							'curr_value' => $show_byes,
							'desc'       => __( 'If checked, open dates will be shown as "Team v. BYE". If unchecked, open dates are not shown.', 'mstw_league_manager' )
							);
				 mstw_lm_build_admin_edit_field( $args );
				 */
				 ?>
				</td>
			   </tr>
			   -->
			   <tr>
			   <th colspan="2"><?php _e( 'DATE & TIME FORMATS', 'mstw-league-manager' ) ?></td>
			   </tr>
			   <tr>
			    <th><div class="col-indent-1"><?php _e( 'Table Date Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				<?php
				 $this -> build_date_format_control( 'date_format', $date_format );
				 ?>
				</td>
				<th class="right"><?php _e( 'Table Time Format:', 'mstw-league-manager' ) ?></th>
				 <td>
				 <?php
				 $this -> build_time_format_control( 'time_format', $time_format );
				 ?>
				 </td>
			   </tr>
			   
			   <tr>
			    <th><div class="col-indent-1"><?php _e( 'Gallery Date Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				<?php
				 $this -> build_date_format_control( 'gallery_date_format', $gallery_date_format );
				 ?>
				</td>
				<th class="right"><?php _e( 'Gallery Time Format:', 'mstw-league-manager' ) ?></th>
				 <td>
				 <?php
				 $this -> build_time_format_control( 'gallery_time_format', $gallery_time_format );
				 ?>
				 </td>
			   </tr>
			   
			   <tr>
			    <th><div class="col-indent-1"><?php _e( 'Scoreboard Date Format:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				<?php
				 $this -> build_date_format_control( 'scoreboard_date_format', $scoreboard_date_format );
				 ?>
				</td>
				<th class="right"><?php _e( 'Scoreboard Time Format:', 'mstw-league-manager' ) ?></th>
				 <td>
				 <?php
				 $this -> build_time_format_control( 'scoreboard_time_format', $scoreboard_time_format );
				 ?>
				 </td>
			   </tr>
			   
			    <tr> <!-- Link from Time/Score field -->
			    <th><div class="col-indent-1"><?php _e( 'Link from Time:', 'mstw-league-manager' ) ?></div></th>
			    <td>
				 <?php
				 $this -> build_time_link_control( 'schedule_time_link', $schedule_time_link );
				 ?>
				</td>
			   </tr>
			   
			   <th colspan="2"><?php _e( 'LIVE GAME UPDATES', 'mstw-league-manager' ) ?></td>
			   <tr>
			    <th><div class="col-indent-1"><?php _e( 'Allow Live Updates:', 'mstw-league-manager' ) ?></div></th>
				<?php
				$args = $defaults = array(
							'type'		 => 'checkbox',
							'id'      	 => 'live_updates_on',
							'default'    => '0',
							'curr_value' => $live_updates_on,
							'desc'       => __( 'Check to allow live updates of game status via mobile devices.', 'mstw-league-manager' ),
							);
				?>
				<td><?php mstw_lm_build_admin_edit_field( $args )?> </td>
			   
			    <th class="right"><?php _e( 'End Live Updates:', 'mstw-league-manager' ) ?></th>
			    <?php
				$args = $defaults = array(
							'type'		 => 'text',
							'id'      	 => 'live_updates_end',
							'default'    => '0',
							'curr_value' => $live_updates_end,
							'desc'       => __( 'Hours after game START to end live updates. If game starts at 7pm, enter 3 to end live updates at 10pm.', 'mstw-league-manager' ),
							);
				?>
				<td><?php mstw_lm_build_admin_edit_field( $args )?> </td>
			   </tr>
			   <tr>
			    <td>
				 <?php 
			 submit_button( "Save Changes", "primary", "submit", false, null );
			 ?>
				</td>
			   
			   </tr>
			   
			  </tbody>
			 </table>
			 
			</form>
			
		<?php
		}
	} //End: build_schedules_tab( )
	
	//-------------------------------------------------------------------------------
	// Create the (form in the) standings settings tab
	//
	function build_standings_table_tab( ) {
		//mstw_log_msg( 'in build_standings_table_tab ...' );
		
		// Get the current sport
		$sport = mstw_lm_get_current_sport( );
		//mstw_log_msg( "Current sport: $sport" );
		
		if ( -1 == $sport ) { 
		?>
			<p class="mstw-lm-admin-instructions"> <?php _e( 'Select a sport before continuing.', 'mstw-league-manager' ) ?></p>	
		<?php 
		} 
		else { 
			// Get the current sport's options
			$options = mstw_lm_get_sport_options( $sport );
			//$options   = mstw_lm_get_nhl_defaults( );
			//mstw_log_msg( '$options' );
			//mstw_log_msg( $options );
			
			$labels    = $options['standings_labels'];
			$order     = $options['standings_order'];
			$show_hide = $options['standings_fields'];
			
			// last_game was added late ... older systems might not recognize it
			if ( !array_key_exists( 'last_game', $labels ) ) {
				$labels['last_game'] = __( 'Last Game', 'mstw-league-manager' );
			}
			if ( !array_key_exists( 'last_game', $order ) ) {
				$order[24] = 'last_game';
			}
			if ( !array_key_exists( 'last_game', $show_hide ) ) {
				$show_hide['last_game'] = 0;
			}
			
			
			/*
			mstw_log_msg( "labels:" );
			mstw_log_msg( $labels );
			mstw_log_msg( "order:" );
			mstw_log_msg( $order );
			mstw_log_msg( "show_hide:" );
			mstw_log_msg( $show_hide );
			*/
			
			$generics  = $this -> get_field_names( 'standings' );
			
			$wins_position = array_search('wins', $order );
			
		?>
			<form class='lm-settings' name='standings-settings' id="standings-settings" method="post" enctype="multipart/form-data" action="">
			 <input type="hidden" name="settings" value="standings-table" />
			 <table id="standings-settings" class="widefat mstw-lm-admin-table">
				 <?php $this -> build_table_header( $table = 'standings' ); ?>
				 <tbody>
				  <?php
				  foreach ( $labels as $slug=>$label ) {
					  $this -> build_settings_table_row( $slug, $generics, $labels, $show_hide, $order );
					 
				  }
				  ?>
				 </tbody>
			 </table>
			 <?php 
			 submit_button( "Save Changes", "primary", "submit", false, null );
			 ?>
			</form>
		
		<?php
		}
		
	} //End: build_standings_table_tab( )
	
	//-------------------------------------------------------------------------------
	// Create the (form in the) schedules settings tab
	//
	function build_schedules_table_tab( ) {
		//mstw_log_msg( 'in build_schedules_table_tab ...' );
		
		// Get the current sport
		$sport = mstw_lm_get_current_sport( );
		//mstw_log_msg( "Current sport: $sport" );
		?>
		<?php 
		if ( -1 == $sport ) { 
		?>
			<p class="mstw-lm-admin-instructions"> <?php _e( 'Select a sport before continuing.', 'mstw-league-manager' ) ?></p>	
		<?php 
		} 
		else { 
			// Get the current sport's options
			$options   = mstw_lm_get_sport_options( $sport );
			$labels    = $options['schedule_labels'];
			$order     = $options['schedule_order'];
			$show_hide = $options['schedule_fields'];
			
			$generics  = $this -> get_field_names( 'schedules' );
			
			//mstw_log_msg( '$options' );
			//mstw_log_msg( $options );
			?>
			
			<form class='lm-settings' id="schedules-settings" method="post" enctype="multipart/form-data" action="">
			 <input type="hidden" name="settings" value="schedules-table" />
			 <table id="standings-settings" class="widefat mstw-lm-admin-table">
				 <?php $this -> build_table_header( $table = 'standings' ); ?>
				 <tbody>
				  <?php
				  foreach ( $labels as $slug=>$label ) {
					  $this -> build_settings_table_row( $slug, $generics, $labels, $show_hide, $order );
					 
				  }
				  ?>
				 </tbody>
			 </table> 
			 <?php 
			 // Get the options for the given sport
			 submit_button( "Save Changes", "primary", "submit", false, null );
			 ?>
			</form>

		<?php
		}
		
	} //End: build_schedules_table_tab( )
	
	
	//-------------------------------------------------------------------------------
	// Build one row of the standings settings table
	//
	function build_settings_table_row( $slug, $generic_names, $labels, $show_hide, $order ) {
		//mstw_log_msg( "build_settings_table_row:" );
		
		$disabled = array ( 'visitor', 'opponent', 'gallery_game' );
		
		$position = array_search( $slug, $order ) + 1;
		
		$nbr_of_fields = count( $generic_names );
		
		$nbrs = range( 1, count( $generic_names ) );
		
		//mstw_log_msg( 'order/position: ' . $position );
		//mstw_log_msg( 'nbrs range:' );
		//mstw_log_msg( $nbrs );
		
		?>
		<tr>
		 <td class="row-head" >
		  <?php echo $generic_names[$slug] ?> 
		 </td>
		 <td>
		  <input type="text" name="<?php echo $slug . '_label' ?>" id="<?php echo $slug . '_label' ?>" value="<?php echo $labels[ $slug ] ?>"/>
		 </td>
		 <td>
		  <?php
		  if ( in_array( $slug, $disabled ) ) {
		  ?>
			  <input type="checkbox" name="<?php echo $slug . '_show' ?>"value="1" <?php checked( 1, $show_hide[$slug] ) ?> disabled/> 
          <?php	
		  }
		  else {
			//mstw_log_msg( "slug: $slug show_hide: " . $show_hide[$slug] );
		  ?>
			  <input type="checkbox" name="<?php echo $slug . '_show' ?>"value="1" <?php checked( 1, $show_hide[$slug] ) ?> "/> 
          <?php			  
		  }
		  ?>
		 </td>
		 <td>
		  <select autocomplete="off" name="<?php echo $slug . '_order' ?>">
		  <?php
		    
			foreach( $nbrs as $nbr ) {
				?>
				<option value="<?php echo $nbr ?>" <?php selected( (int)$nbr, (int)$position ) ?>><?php echo $nbr ?></option>
				<?php
				//mstw_log_msg( 'field: ' . $slug );
				//mstw_log_msg( 'value: ' . $nbr . ' position: ' . $position . ' selected: ' . selected( $nbr, $position) );
				//mstw_log_msg( 'position: ' . $position );
				//mstw_log_msg( 'selected: ' . selected( $nbr, $position ) );
			
			}
		  ?>
		  
		  </select>
		  
		 </td>
		</tr>
	
	<?php	
	}
	
	
	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------
	function post( $options ) {
		//mstw_log_msg( 'In post method ... ' );
		//mstw_log_msg( '$_POST:' );
		//mstw_log_msg( $_POST );
		//mstw_log_msg( '$options:' );
		//mstw_log_msg( $options );
		
		if ( !$options ) {
			mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
			return;
		}
		
		//$options['submit_value'] = 'foo';
		
		switch( $options['submit_value'] ) {
			case __( 'Select Sport', 'mstw-league-manager' ):
				//mstw_log_msg( 'Updating sport selection ...' . $_POST['main_sport'] );
				if ( array_key_exists( 'main_sport', $_POST ) ) {
					mstw_lm_set_current_sport( $_POST['main_sport'] );
					mstw_lm_add_admin_notice( 'updated', __( 'Sport updated.', 'mstw-league-manager' ) );
				}
				else {
					mstw_lm_add_admin_notice( 'error', __( 'Error occurred; sport not updated.', 'mstw-league-manager' ) );
				}
				break;
				
			case __( 'Save Changes', 'mstw-league-manager' );
				//mstw_log_msg( 'Saving changes ' );
				if ( isset( $_POST ) && array_key_exists( 'settings', $_POST ) ) {
					//mstw_log_msg( "settings= " . $_POST['settings'] );
					$current_sport = mstw_lm_get_current_sport( );
					//mstw_log_msg( 'current sport: ' . $current_sport );
					if( '' != $current_sport or -1 != $current_sport ) {
						$updated = $this->update_settings( $_POST['settings'], $current_sport );
						if( $updated ) {
							mstw_lm_add_admin_notice( 'updated', __( 'Settings saved', 'mstw-league-manager' ) );
						}
						else {
							mstw_lm_add_admin_notice( 'error', __( 'Settings not saved. Did you change anything?', 'mstw-league-manager' ) );
						}
					}
					else {
						mstw_lm_add_admin_notice( 'error', __( 'Error with sport. Settings not saved.', 'mstw-league-manager' ) );
					}
				}
				else {
					mstw_lm_add_admin_notice( 'error', __( 'Error encountered. Settings not saved', 'mstw-league-manager' ) );
					mstw_log_msg( 'Saving changes to settings; $_POST[\'settings\'] not found.' );
				}
				break;
				
			default:
				//$this->log['notice'][] = sprintf( __( 'No function found for %s.)', 'mstw-league-manager' ), $options['submit_value'] );
				mstw_lm_add_admin_notice( 'error', __( 'Error encountered. Settings not saved', 'mstw-league-manager' ) );
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $options['submit_value'] . '. Exiting' );
				return;
				break;
		}
			
	} //End: post( )
	
	//-------------------------------------------------------------------------------
	// update_settings - Saves settings for a given tab
	//
	function update_settings( $tab, $sport ) {
		//mstw_log_msg( "in update_settings ... tab= $tab; sport= $sport" );
		//mstw_log_msg( '$_POST:' );
		//mstw_log_msg( $_POST );
		
		$retval = true;
		
		switch ( $tab ) {
			case 'standings':
				//mstw_log_msg( "updating standings tab" );
				$retval = $this -> update_standings( $sport );
				break;
				
			case 'standings-table':
				//mstw_log_msg( "updating standings table tab" );
				$retval = $this -> update_standings_table( $sport );
				break;
				
			case 'schedules':
				$retval = $this -> update_schedules( $sport );
				//mstw_log_msg( "updating schedules tab" );
				break;
				
			case 'schedules-table':
				//mstw_log_msg( "updating schedules table tab" );
				$retval = $this -> update_schedules_table( $sport );
				break;
			
			default:
				//mstw_log_msg( "$tab is no bueno.");
				$retval = false;
				break;	
		}
		
		return $retval;
		
	} //End: update_settings( )
	
	//-------------------------------------------------------------------------------
	// update_standings - Updates standings settings from $_POST; does the heavy lifting
	//
	function update_standings( $sport_slug = null ) {
		//mstw_log_msg( 'in update_standings ... ' . $sport_slug );
		
		//mstw_log_msg( $_POST );
		
		if ( null === $sport_slug or empty( $sport_slug ) or -1 == $sport_slug ) {
			$retval = false;
		}
		else {
			$options = mstw_lm_get_sport_options( $sport_slug );
			//mstw_log_msg( $options );
			
			if ( array_key_exists( 'standings_name_format', $_POST ) ) {
				$options['standings_name_format'] = $_POST['standings_name_format'];
			}
			
			$show_logo = (  array_key_exists( 'standings_show_logo', $_POST ) ) ? 1 : 0 ;
			$options['standings_show_logo'] = $show_logo;
			
			if ( array_key_exists( 'standings_team_link', $_POST ) ) {
				$options['standings_team_link'] = $_POST['standings_team_link'];
			}
			
			if ( array_key_exists( 'standings_next_game_link', $_POST ) ) {
				$options['standings_next_game_link'] = $_POST['standings_next_game_link'];
			}
			
			if ( array_key_exists( 'order_by', $_POST ) ) {
				$options['order_by'] = $_POST['order_by'];
			}
			
			$points_rules = $options['points_rules'];
			
			if ( array_key_exists( 'pts_win', $_POST ) ) {
				$points_rules['wins'] = $_POST['pts_win'];
			}
			if ( array_key_exists( 'pts_win', $_POST ) ) {
				$points_rules['losses'] = $_POST['pts_loss'];
			}
			if ( array_key_exists( 'pts_tie', $_POST ) ) {
				$points_rules['ties'] = $_POST['pts_tie'];
			}
			if ( array_key_exists( 'pts_otw', $_POST ) ) {
				$points_rules['otw'] = $_POST['pts_otw'];
			}
			if ( array_key_exists( 'pts_otl', $_POST ) ) {
				$points_rules['otl'] = $_POST['pts_otl'];
			}
			
			$options['points_rules'] = $points_rules;
			
			//mstw_log_msg( $_POST );
			
			
			// just hides the details of building the option name
			// may want to bring inot this class (?)
			$retval = mstw_lm_update_option( $sport_slug, $options );
			
			$options = mstw_lm_get_sport_options( $sport_slug );
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			/* FOR ADMIN DEBUG/CLEANUP ONLY
			if ( delete_option( $sport_slug . '_options' ) ) {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			else {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			*/
		}
		
		return $retval;
		
	} //End: update_standings( )
	
	//-------------------------------------------------------------------------------
	// update_schedules - Updates schedules settings from $_POST; does the heavy lifting
	//
	function update_schedules( $sport_slug = null ) {
		//mstw_log_msg( 'in update_schedules ... ' . $sport_slug );
		
		//mstw_log_msg( $_POST );
		
		if ( null === $sport_slug or empty( $sport_slug ) or -1 == $sport_slug ) {
			$retval = false;
		}
		else {
			$options = mstw_lm_get_sport_options( $sport_slug );
			//mstw_log_msg( "orig options:" ) ;
			//mstw_log_msg( $options );
			
			// checkboxes need special handling	
			$options['schedules_show_logo'] = (  array_key_exists( 'schedules_show_logo', $_POST ) ) ? 1 : 0 ;
			
			//$options['show_byes'] = (  array_key_exists( 'show_byes', $_POST ) ) ? 1 : 0 ;
			
			$options['live_updates_on'] = (  array_key_exists( 'live_updates_on', $_POST ) ) ? 1 : 0 ;
			
			foreach ( $_POST as $key => $value ) {
				switch ( $key ) {
					case 'schedules_name_format':
					case 'location_format':
					case 'tbd_label':
					case 'schedule_team_link':
					case 'schedule_location_link':
					case 'date_format':
					case 'time_format':
					case 'gallery_date_format':
					case 'gallery_time_format':
					case 'scoreboard_date_format':
					case 'scoreboard_time_format':
					case 'schedule_time_link':
					case 'live_updates_end':
						if ( array_key_exists( $key, $_POST ) ) {
							$options[$key] = $value;
						}
						break;
					default:
						break;
				}
				
			} //End: foreach( $_POST )
			
			$options['show_nonleague'] = 0;
			
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			//return true;
			
			// just hides the details of building the option name
			// may want to bring inot this class (?)
			$retval = mstw_lm_update_option( $sport_slug, $options );
			
			//$options = mstw_lm_get_sport_options( $sport_slug );
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			/* FOR ADMIN DEBUG/CLEANUP ONLY
			if ( delete_option( $sport_slug . '_options' ) ) {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			else {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			*/
		}
		
		return $retval;
		
	} //End: update_schedules( )
	
	//-------------------------------------------------------------------------------
	// update_standings_table - Updates standings table settings from $_POST; does the heavy lifting
	//
	function update_standings_table( $sport_slug = null ) {
		//mstw_log_msg( 'in update_standings_table ... ' . $sport_slug );
		// Build three arrays 
		//  $rank, $label, $show_hide, $order
		
		//mstw_log_msg( "POST:" );
		//mstw_log_msg( $_POST );
		
		
		if ( null === $sport_slug or empty( $sport_slug ) or -1 == $sport_slug ) {
			$retval = false;
		}
		else {
			//
			// SHOW checkboxes need special handling 
			// (because unchecked don't show in $_POST[])
			//
			$label_keys = array ( 'rank',
								  'team',
								  'games_played',
								  'wins',
								  'losses',
								  'wins-losses',
								  'ties',
								  'otw',
								  'otl',
								  'percent',
								  'points',
								  'games_behind',
								  'goals_for',
								  'goals_against',
								  'goals_diff',
								  'last_5',
								  'last_10',
								  'streak',
								  'home',
								  'away',
								  'division',
								  'conference',
								  'next_game',
								  'last_game'
								);
								
			$show_hide = array( );
			
			foreach ( $label_keys as $key ) {
				$show_hide[ $key ] = ( array_key_exists( $key . "_show", $_POST ) ) ? 1 : 0;
			}
			
			//mstw_log_msg( '$show_hide array ' );
			//mstw_log_msg( $show_hide );
			
			$labels = array( );
			$order  = array( );
			
			foreach ( $_POST as $key => $value ) {
				
				if( $us = strpos( $key, '_l' ) ) {
					$labels[ substr( $key, 0, $us ) ] = $value;
				}
				else if ( $us = strpos( $key, '_o' ) ) {
					$order[ substr( $key, 0, $us ) ] = $value;
				}
				/*switch ( $key ) {
					case 'rank_label':
						$new_key = substr( $key, 0, strpos( $key, '_' ) );
						$labels[ $new_key ] = $value;
						break;
					case 'rank_order':
						$new_key = substr( $key, 0, strpos( $key, '_' ) );
						$order[ $new_key ] = $value;
						break;
					default:
						break;	
				} //End: switch ( $key )
				*/
				
			} //End: foreach( $_POST as $key => $value )
			
			//mstw_log_msg( "order array: " );
			//mstw_log_msg( $order );
				
			asort( $order, SORT_NUMERIC );
			
			//mstw_log_msg( "labels array: " );
			//mstw_log_msg( $labels );
			//mstw_log_msg( "order array: " );
			//mstw_log_msg( $order );
			
			$order_keys = array_keys( $order );
			//mstw_log_msg( 'new order array:' );
			//mstw_log_msg( $order_keys );
			
			$options = mstw_lm_get_sport_options( $sport_slug );
			
			if ( null !== $order_keys ){
				$options['standings_order'] = $order_keys;
			}
			if ( null !== $labels ) {
				$options['standings_labels'] = $labels;	
			}
			if ( null !== $show_hide ) {
				$options['standings_fields'] = $show_hide;	
			}
			
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			// just hides the details of building the option name
			// may want to bring inot this class (?)
			$retval = mstw_lm_update_option( $sport_slug, $options );
			
			/* FOR ADMIN DEBUG/CLEANUP ONLY
			if ( delete_option( $sport_slug . '_options' ) ) {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			else {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			*/
		}
			
		//return $this -> update_standings_table_options( $sport, $order_keys, $labels, $show );
		
		return $retval;
		
	} //End: update_standings_table( )
	
	//-------------------------------------------------------------------------------
	// update_schedules_table - Updates schedule table settings from $_POST; does the heavy lifting
	//
	function update_schedules_table( $sport_slug = null ) {
		//mstw_log_msg( 'in update_schedules_table ... ' . $sport_slug );
		// Build three arrays 
		//  $rank, $label, $show_hide, $order
		
		
		if ( null === $sport_slug or empty( $sport_slug ) or -1 == $sport_slug ) {
			$retval = false;
		}
		else {
			//
			// SHOW checkboxes need special handling 
			// (because unchecked don't show in $_POST[])
			//
			$show_hide_fields = array ( 'date',
								        'time',
								        'games_played',
									    'location',
								        'media',
									  
								        // not using 'result'
									  
									    // the SW figures these out
								        //'home',
								        //'visitor',
								        //'opponent',
								        //'gallery_game',
									  );
								
			$show_hide = array( );
			
			foreach ( $show_hide_fields as $key ) {
				$show_hide[ $key ] = ( array_key_exists( $key . "_show", $_POST ) ) ? 1 : 0;
				// these are always on
				$show_hide['home'] = 1;
				$show_hide['visitor'] = 1;
				$show_hide['opponent'] = 1;
				$show_hide['gallery_game'] = 1;
				
			}
			
			//mstw_log_msg( '$show_hide array ' );
			//mstw_log_msg( $show_hide );
			
			$labels = array( );
			$order  = array( );
			
			foreach ( $_POST as $key => $value ) {
				
				if( $us = strpos( $key, '_l' ) ) {
					$labels[ substr( $key, 0, $us ) ] = $value;
				}
				else if ( $us = strpos( $key, '_o' ) ) {
					$order[ substr( $key, 0, $us ) ] = $value;
				}
				/*switch ( $key ) {
					case 'rank_label':
						$new_key = substr( $key, 0, strpos( $key, '_' ) );
						$labels[ $new_key ] = $value;
						break;
					case 'rank_order':
						$new_key = substr( $key, 0, strpos( $key, '_' ) );
						$order[ $new_key ] = $value;
						break;
					default:
						break;	
				} //End: switch ( $key )
				*/
				
			} //End: foreach( $_POST as $key => $value )
			
			//mstw_log_msg( "order array: " );
			//mstw_log_msg( $order );
				
			asort( $order, SORT_NUMERIC );
			
			//mstw_log_msg( "labels array: " );
			//mstw_log_msg( $labels );
			//mstw_log_msg( "order array: " );
			//mstw_log_msg( $order );
			
			$order_keys = array_keys( $order );
			//mstw_log_msg( 'new order array:' );
			//mstw_log_msg( $order_keys );
			
			$options = mstw_lm_get_sport_options( $sport_slug );
			
			if ( null !== $order_keys ){
				$options['schedule_order'] = $order_keys;
			}
			if ( null !== $labels ) {
				$options['schedule_labels'] = $labels;	
			}
			if ( null !== $show_hide ) {
				$options['schedule_fields'] = $show_hide;	
			}
			
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			// just hides the details of building the option name
			// may want to bring inot this class (?)
			$retval = mstw_lm_update_option( $sport_slug, $options );
			
			/* FOR ADMIN DEBUG/CLEANUP ONLY
			if ( delete_option( $sport_slug . '_options' ) ) {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			else {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			*/
		}
			
		//return $this -> update_schedule_table_options( $sport, $order_keys, $labels, $show );
		
		return $retval;
		
	} //End: update_schedules_table( )
	
	
	//-------------------------------------------------------------------------------
	// update_standings_table_options - updates the standings table options for a given sport
	//	(see mstw-lm-utility-functions, mstw-lm-get-defaults() for array formats)
	//	ARGUMENTS:
	//		$sport_slug:   sport to save (required)
	//		$order:        order for columns/fields (optional)
	//		$labels: 	   column labels (optional)
	//		$show-hide:    fields/columns to show/hide (optional)
	//
	//	RETURNS:
	//		true on success; false on error
	//
	function update_standings_table_options( 
					$sport_slug = null, 
					$order      = null, 
					$labels     = null,
					$show_hide  = null ) {
						
		//mstw_log_msg( 'in update_standings_table_options ... ' );
		//mstw_log_msg( "sport= " . $sport_slug );
		//mstw_log_msg( "division label= " . $labels['division'] );
		//mstw_log_msg( "division show= " . $show_hide['division'] );
		//mstw_log_msg( "division order= " );
		//mstw_log_msg( $order );
		
		if ( null === $sport_slug ) {
			$retval = false;
		} 
		else {
			// get the existing options and update them as necessary
			
			$options = mstw_lm_get_sport_options( $sport_slug );
			
			if ( null !== $order ){
				$options['standings_order'] = $order;
			}
			if ( null !== $labels ) {
				$options['standings_labels'] = $labels;	
			}
			if ( null !== $show_hide ) {
				$options['standings_fields'] = $show_hide;	
			}
			
			//mstw_log_msg( "new options:" ) ;
			//mstw_log_msg( $options );
			
			// just hides the details of building the option name
			// may want to bring inot this class (?)
			$retval = mstw_lm_update_option( $sport_slug, $options );
			
			/* FOR ADMIN DEBUG/CLEANUP ONLY
			if ( delete_option( $sport_slug . '_options' ) ) {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			else {
				mstw_log_msg( 'options for ' . $sport_slug . ' deleted' );
			}
			*/
		}
		
		return $retval;
		
	} //End: update_standings_table_options( )
	
	//-------------------------------------------------------------------------------
	// process_option - checks/cleans up the $_POST values
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
	
	//-------------------------------------------------------------------------------
	// build_table_header - Outputs the HTML for the settings table headers
	//
	function build_table_header( $table = 'standings' ) {
		//mstw_log_msg( 'in build_table_header ...' );
		?>
		<thead>
		 <tr>
		  <th><?php _e( 'Data Field/Column (back end use only)', 'mstw-league-manager' ) ?></th>
		  <th><?php _e( 'Column Label (front end only)', 'mstw-league-manager' ) ?></th>
		  <th><?php _e( 'Show/Hide Column', 'mstw-league-manager' ) ?></th>
		  <th><?php _e( 'Column Order', 'mstw-league-manager' ) ?></th>
		  
		 </tr>
		</thead>
	<?php
	} //End: build_table_header()
	
	//-------------------------------------------------------------------------------
	// Returns an array of the back end field names in slug=>name format
	//
	function get_field_names( $table = 'standings' ) {
		//mstw_log_msg( 'in get_field_names ...' );
		
		if ( 'schedules' == $table ) {
			$retval = array(
						'date' 	       => __( 'Date', 'mstw-league-manager' ),
						'time' 	       => __( 'Time', 'mstw-league-manager' ),
						'home' 	       => __( 'Home', 'mstw-league-manager' ),
						'visitor' 	   => __( 'Vistor', 'mstw-league-manager' ),
						'opponent' 	   => __( 'Opponent', 'mstw-league-manager' ),
						'location' 	   => __( 'Location', 'mstw-league-manager' ),
						'media' 	   => __( 'Media', 'mstw-league-manager' ),
						'gallery_game' => __( 'Matchup', 'mstw-league-manager' ),
						);
		}
		else {  //'standings' == $table ) {
			$retval = array (
					   'rank' 	       => __( 'Rank', 'mstw-league-manager' ),
					   'team' 	       => __( 'Team', 'mstw-league-manager' ),
					   'games_played'  => __( 'Games Played', 'mstw-league-manager' ),
					   'wins'		   => __( 'Wins', 'mstw-league-manager' ),
					   'losses'		   => __( 'Losses', 'mstw-league-manager' ),
					   'wins-losses'   => __( 'Record as W-L', 'mstw-league-manager' ),
					   'ties'	       => __( 'Ties', 'mstw-league-manager' ),
					   'otw' 	       => __( 'Overtime Wins', 'mstw-league-manager' ),
					   'otl' 	       => __( 'Overtime Losses', 'mstw-league-manager' ),
					   'percent'       => __( 'Win Percentage', 'mstw-league-manager' ),
					   'points'        => __( 'Points', 'mstw-league-manager' ),
					   'games_behind'  => __( 'Games Behind', 'mstw-league-manager' ),
					   'goals_for'	   => __( 'Goals For', 'mstw-league-manager' ),
					   'goals_against' => __( 'Goals Against', 'mstw-league-manager' ),
					   'goals_diff'	   => __( 'Goal Differential', 'mstw-league-manager' ),
					   'last_5'		   => __( '(Record in) Last 5 Games', 'mstw-league-manager' ),
					   'last_10'	   => __( '(Record in) Last 10 Games', 'mstw-league-manager' ),
					   'streak' 	   => __( 'Win or Loss Streak', 'mstw-league-manager' ),
					   'home'	       => __( 'Win-Loss Record at Home', 'mstw-league-manager' ),
					   'away' 	       => __( 'Win-Loss Record Away from Home', 'mstw-league-manager' ),
					   'conference'	   => __( 'Conference Record', 'mstw-league-manager' ),
					   'division'      => __( 'Division Record', 'mstw-league-manager' ),
					   'next_game'     => __( 'Next Game', 'mstw-league-manager' ),
					   'last_game'     => __( 'Last Game', 'mstw-league-manager' ),
					   );
			
		}
		
		return $retval;
	
	} // End: get_field_names()
	
	function build_time_format_control( $id_name = 'time-control', $curr_value ) {
		//mstw_log_msg( 'in build_time_format_control ...' );
		
		$options = array ( __( '08:00 (24hr)', 'mstw-league-manager' ) => 'H:i',
						   __( '8:00 (24hr)', 'mstw-league-manager' )  => 'G:i',
						   __( '08:00 am', 'mstw-league-manager' ) 	   => 'h:i a',
						   __( '8:00 am', 'mstw-league-manager' )      => 'g:i a',
						   __( '08:00 AM', 'mstw-league-manager' ) 	   => 'h:i A',
						   __( '8:00 AM', 'mstw-league-manager' ) 	   => 'g:i A',
						 );
		
		$options = apply_filters( 'mstw_lm_time_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							//'default'	 => 'h:i A',
							'curr_value' => $curr_value,
							'options' 	 => $options,
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_time_format_control()
	
	function build_team_link_control( $id_name = 'team-link', $curr_value ) {
		//mstw_log_msg( 'build_team_link_control:' );
		
		$options = array ( __( 'None', 'mstw-league-manager' )           =>  'none',
						   __( 'Team Schedule', 'mstw-league-manager' )  =>  'team-schedule',
						   __( 'Team URL', 'mstw-league-manager' )       =>  'team-url',
						 );
		
		//$options = apply_filters( 'mstw_lm_time_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							//'default'	 => 'h:i A',
							'curr_value' => $curr_value,
							'options' 	 => $options,
							'desc'       => __( 'Where to link from the Team names.', 'mstw-league-manager' ),
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_team_link_control( )
	
	function build_next_game_link_control( $id_name = 'next-game-link', $curr_value ) {
		//mstw_log_msg( "build_next_game_link_control:" );
		
		$options = array ( __( 'None', 'mstw-league-manager' )      => 'none',
						   /*__( 'Team Schedule', 'mstw-league-manager' ) => 'team-schedule',*/
						   __( 'Game Page', 'mstw-league-manager' ) => 'game-page',
						 );
		
		//$options = apply_filters( 'mstw_lm_time_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							'curr_value' => $curr_value,
							'options' 	 => $options,
							'desc'       => __( 'Where to link from the next game field.', 'mstw-league-manager' ),
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_next_game_link_control( )
	
	function build_location_link_control( $id_name = 'location-link', $curr_value ) {
		//mstw_log_msg( 'build_location_link_control:' );
		
		$options = array ( __( 'None', 'mstw-league-manager' )      => 'none',
						   __( 'Venue URL', 'mstw-league-manager' ) => 'venue-url',
						   __( 'Google Map', 'mstw-league-manager' ) => 'google-map',
						 );
		
		//$options = apply_filters( 'mstw_lm_time_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							//'default'	 => 'h:i A',
							'curr_value' => $curr_value,
							'options' 	 => $options,
							'desc'       => __( 'Where to link from the Location/Venue.', 'mstw-league-manager' ),
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_location_link_control( )
	
	function build_time_link_control( $id_name = 'time-link', $curr_value ) {
		//mstw_log_msg( 'build_time_link_control:' );
		
		$options = array ( __( 'None', 'mstw-league-manager' )      => 'none',
						   __( 'Game Page', 'mstw-league-manager' ) => 'game-page',
						 );
		
		//$options = apply_filters( 'mstw_lm_time_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							//'default'	 => 'h:i A',
							'curr_value' => $curr_value,
							'options' 	 => $options,
							'desc'       => __( 'Where to link from the Time/Score field.', 'mstw-league-manager' ),
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_time_link_control( )
	
	function build_date_format_control( $id_name = 'date-control', $curr_value ) {
		//mstw_log_msg( 'in build_date_format_control ...' );
		
		$options = array ( '2013-04-07' => 'Y-m-d',
						   '13-04-07' => 'y-m-d',
						   '04/07/13' => 'm/d/y',
						   '4/7/13' => 'n/j/y',
						   __( '07 Apr 2013', 'mstw-league-manager' ) => 'd M Y',
						   __( '7 Apr 2013', 'mstw-league-manager' ) => 'j M Y',
						   __( 'Tues, 07 Apr 2013', 'mstw-league-manager' ) => 'D, d M Y',
						   __( 'Tues, 7 Apr 13', 'mstw-league-manager' ) => 'D, j M y',
						   __( 'Tuesday, 7 Apr', 'mstw-league-manager' ) => 'l, j M',
						   __( 'Tuesday, 07 April 2013', 'mstw-league-manager' ) => 'l, d F Y',
						   __( 'Tuesday, 7 April 2013', 'mstw-league-manager' ) => 'l, j F Y',
						   __( 'Tues, 07 Apr', 'mstw-league-manager' ) => 'D, d M',
						   __( 'Tues, 7 Apr', 'mstw-league-manager' ) => 'D, j M',
						   __( '07 Apr', 'mstw-league-manager' ) => 'd M',
						   __( '7 Apr', 'mstw-league-manager' ) => 'j M',
						 );
		
		$options = apply_filters( 'mstw_lm_date_formats', $options );
		
		$args = $defaults = array(
							'type'		 => 'select-option',
							'id'      	 => $id_name, 
							'default'	 => 'h:i A',
							'curr_value' => $curr_value,
							'options' 	 => $options,
							);
		mstw_lm_build_admin_edit_field( $args );
		
	} //End: build_date_format_control()
	
	function add_help( ) {
		//mstw_log_msg( "in settings add_help" );
		$screen = get_current_screen( );
		
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		if ( null === $screen ) {
			mstw_log_msg( "current screen not defined in settings add_help()" );
		}
		else {
			//mstw_log_msg( "current screen:" );
			//mstw_log_msg( $screen );
			
			mstw_lm_help_sidebar( $screen );
			
			$tabs = array(
						array(
							'title'    => __( 'Overview', 'mstw-league-manager' ),
							'id'       => 'settings-overview',
							'callback'  => array( $this, 'add_help_tab' ),
							),
						array(
							'title'    => __( 'Standings', 'mstw-league-manager' ),
							'id'       => 'standings-help',
							'callback'  => array( $this, 'add_help_tab' ),
							),
							
						array(
							'title'    => __( 'Standings Tables', 'mstw-league-manager' ),
							'id'       => 'standings-tables-help',
							'callback'  => array( $this, 'add_help_tab' ),
							),
							
						array(
							'title'		=> __( 'Schedules', 'mstw-league-manager' ),
							'id'		=> 'schedules-help',
							'callback'	=> array( $this, 'add_help_tab' ),
							),
							
						array(
							'title'		=> __( 'Schedules Tables', 'mstw-league-manager' ),
							'id'		=> 'schedules-tables-help',
							'callback'	=> array( $this, 'add_help_tab' ),
							),
							
					);

			foreach( $tabs as $tab ) {
				$screen->add_help_tab( $tab );
			}
			
		}
		
	} //End: add_help( )
	
	function add_help_tab( $screen, $tab ) {
		//mstw_log_msg( "in add_help_tab ... " );
		
		if( !array_key_exists( 'id', $tab ) ) {
			return;
		}
			
		switch ( $tab['id'] ) {
			case 'settings-overview':
				?>
				<p><?php _e( 'Note that all settings are based on SPORT, not LEAUGUE or TEAM. All leagues with the same sport will have the same settings.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Special settings (which may be customized) are provided for NCAA Football, NHL Hockey, and Premier League Soccer.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-settings/" target="_blank"><?php _e( 'See the Settings man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			
			case 'standings-help':
				?>
				<h3><strong>
				 <?php _e( 'Standings Settings', 'mstw-league-manager' ) ?>
				</strong></h3>
				<p><?php _e( 'Provides control of various Standings Table features including team name/logo format, table order, and custom points calculations.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-settings-standings/" target="_blank"><?php _e( 'See the Settings - Standings man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			case 'standings-tables-help':
				?>
				<h3><strong>
				 <?php _e( 'Standings Tables Settings', 'mstw-league-manager' ) ?>
				</strong></h3>
				<p><?php _e( ' Provides control of the columns (data fields) of the Standings Tables. On this screen you can show/hide columns, change the column headers/labels, and change the column order.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-settings-standings-tables/" target="_blank"><?php _e( 'See the Settings - Standings Tables man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			case 'schedules-help':
				?>
				<h3><strong>
				<?php _e( 'Schedules Settings', 'mstw-league-manager' ) ?>
				</strong></h3>
				<p><?php _e( 'Provides control of various Schedule Table and Gallery features including team name/logo format, venue/location format, and date & time formats.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-settings-schedules/" target="_blank"><?php _e( 'See the Settings - Schedules man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
				
			case 'schedules-tables-help':
				?>
				<h3><strong>
				 <?php _e( 'Schedules Tables Settings', 'mstw-league-manager' ) ?></strong></h3>
				 <p><?php _e( 'Provides control of the columns (data fields) of the Schedule Tables and Galleries. On this screen you can show/hide columns, change the column headers/labels, and change the column order.', 'mstw-league-manager' ) ?></p>
				 <p><a href="http://shoalsummitsolutions.com/lm-settings-schedule-tables/" target="_blank"><?php _e( 'See the Settings - Schedule Tables man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()
	
} //End: class MSTW_LM_UPDATE_GAMES