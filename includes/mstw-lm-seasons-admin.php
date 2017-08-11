<?php
/* ------------------------------------------------------------------------
 * 	MSTW League Manager Seasons Class
 *		UI to manages seasons for leagues
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
 
class MSTW_LM_SEASONS {
	
	// Don't need this anymore
	var $log = array();
	
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
		//mstw_log_msg( 'in MSTW_LM_MANAGE_SEASONS.form ...' );
		?>

		<h2><?php _e( 'Manage Seasons', 'mstw-league-manager' )?></h2>
	
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

		// start form HTML 
		
		mstw_lm_admin_notice( );
			
		$current_league = mstw_lm_get_current_league( );
		//mstw_log_msg( '$current_league' . $current_league );
		
		?>	
		
		<ul class='mstw-lm-admin-instructions'>
		 <li><?php _e( 'Read the contextual help on the top right of this screen.', 'mstw-league-manager' ) ?></li>
		</ul>

		<!-- begin main wrapper for page content -->
		<div class="wrap">
		 
		  <div class='tablenav top'>
		   <form id="set-league" class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
				<div class="alignleft actions">
					<?php

					$hide_empty_leagues = false;
					
					$ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty_leagues );
					if ( -1 == $ret ) {
						_e( 'No leagues found.', 'mstw-league-manager' );
					}
					else {
						?>
						<input type="submit" name="submit" id="select_league" class="button" value="Select League"  />
					<?php } ?>
				</div>
			</form>
		   </div> <!-- .tablenav -->	
		
		   <?php
		   $seasons = mstw_lm_get_league_seasons( $current_league );
		   $this-> build_right_column( $current_league, $seasons );
		   $this-> build_left_column( $current_league );
		   ?>
		
		 </div> <!-- end #col-container -->
		</div> <!-- end .wrap -->
		<!-- end of form HTML -->
	<?php
	} //End of function form()
	
	//-------------------------------------------------------------
	//	build_right_column - builds the HTML for the list of seasons
	//-------------------------------------------------------------
	function build_right_column ( $current_league, $seasons ) {
		//mstw_log_msg( 'in build_right_column ... ' );
		//mstw_log_msg( '$current_league:' );
		//mstw_log_msg( $current_league );
		?>
		<div id="col-right">
		 <div class="col-wrap">
		  <?php
		  $league_obj = get_term_by( 'slug', $current_league, 'mstw_lm_league', OBJECT, 'raw' );
		
		  if ( $league_obj ) {
			$league_name = $league_obj->name;
			//mstw_log_msg( '$league_obj:' );
			//mstw_log_msg( $league_obj );
		    ?>
		
		    <h3><?php echo __( 'Seasons for', 'mstw-league-manager' ) . ' ' . $league_name ?></h3>
		    <?php 
		    if ( count( $seasons ) > 0 ) { 
		    ?>
			  <form id="seasons-list" method="post">
			   <input class="hidden" id="current_league" name="current_league" value="<?php echo $current_league ?>"/>
			   <table class='wp-list-table widefat auto striped posts'>
				 <?php 
				 $this->build_seasons_table_header( );
				 $this->build_seasons_table_body( $current_league, $seasons );
				 ?>
			   </table>
			   <div class="tablenav bottom" >
			     <input type="submit" name="submit" id="delete-seasons" class="button action" value="Delete Selected" />
				 <br/><span class='mstw-lm-admin-instructions'><?php _e( 'You cannot delete all the seasons for a league. Delete the league instead.', 'mstw-league-manager' ) ?></span>
			   </div>
			  </form>
		    <?php 
		    } 
			else { 
		    ?>
			  <p class='mstw-lm-admin-instructions'><?php _e( 'No seasons found.', 'mstw-league-manager' ) ?></p>
		    <?php 
		    }
		  } 	  
		  ?>
		 </div> <!-- end .col-wrap -->
		</div> <!-- end #col-right -->
		<?php
	} //End: build_right_column()
	
	//-------------------------------------------------------------
	//	build_left_column - builds the HTML for the add season column
	//-------------------------------------------------------------
	function build_left_column ( $current_league ) {
		?>
		<div id="col-left">
		 <div class="col-wrap">
		  <?php
		  if ( -1 == $current_league or '' == $current_league ) {
			?>
			<p class='mstw-lm-admin-instructions'>Select a league.</p>
		  <?php
		  }
		  else {
          ?>		  
		  <div class="form-wrap">
		   <h3><?php _e( 'Add New Season', 'mstw-league-manager' )?></h3>
		   <form id="add-new-season" method="post" class="validate">
			 <input class="hidden" id="current_league" name="current_league" value="<?php echo $current_league ?>"/>
			 <div class="form-field form-required term-name-wrap">
				<label for="season_name"><?php _e( 'Name', 'mstw-league-manager' )?></label>
				<input name="season_name" id="season_name" type="text" value size="40">
				<p><?php _e( 'The name is how the season appears on your site. Typically "2015" or "2015-16".', 'mstw-league-manager' )?><p>
			 </div>
			 <!--
			 <div class="form-field form-required term-name-wrap">
				<label for="season-slug"><?php _e( 'Slug', 'mstw-league-manager' )?></label>
				<input name="season-slug" id="season-slug" type="text" value size="40">
				<p><?php _e( 'The slug is the URL-friendly version of the name, and the unique idenifier for the season. Typically left empty; it will be created automatically from the season name.', 'mstw-league-manager' )?><p>
			 </div>
			 -->
			 <p class="submit">
			  <input type="submit" name="submit" id="submit" class="button button-primary" value="Add New Season"/>
			 </p>
			 
		   </form>
		  </div> <!-- end .form-wrap -->
		  <?php 
		  }
		  ?>
		 </div> <!-- end .col-wrap -->
		</div> <!-- end #col-left -->
		<?php
	}
	
	//-------------------------------------------------------------
	//	build_seasons_table_header - outputs the HTML for the 
	//		seasons table header
	//-------------------------------------------------------------
	function build_seasons_table_header ( ) {
		//mstw_log_msg( 'in build_seasons_table_header ...' );
		?>
		<thead>
		  <tr>
		  <!-- this tag is janky due to the WP admin CSS -->
		   <td id="header-checkbox" class="check-column">
		    <input type="checkbox" name="delete_all" id="delete_all" value="1"/>
		   </td>
		   <th><?php _e( 'Season', 'mstw-league-manager' ) ?></th>
		   <th><?php _e( 'Slug', 'mstw-league-manager' ) ?></th>
		  </tr>
		</thead>
					
		<?php
	} //End: build_seasons_table_header()

	//-------------------------------------------------------------
	//	build_seasons_table_body - returns HTML for the update games table
	//-------------------------------------------------------------
	function build_seasons_table_body ( $current_league, $seasons ) {
		//mstw_log_msg( 'in build_seasons_table_body ...' );
		//mstw_log_msg( $seasons );
		?>
		<tbody>
			<?php
			if ( is_array( $seasons ) ) {
				natsort( $seasons );
				foreach( $seasons as $key=>$name ) {
					?>
					<tr>
					 <?php $this->season_delete_cell( $key ) ?>
					 <td><?php echo $name ?></td>
					 <td><?php echo $key ?></td>
					</tr>
				<?php
				}
			}
			?>
		
		</tbody>
		
	<?php	
	} //End: build_seasons_table_body()
	
	//-------------------------------------------------------------
	//	season_delete_cell - outputs the HTML for the delete cells
	//-------------------------------------------------------------
	function season_delete_cell( $key ) {
		//mstw_log_msg( 'in season_delete_cell ...' );
		$css_tag = "delete_$key";
		?>
		<!-- This tag is janky due to the WP admin CSS. It also 
		  -- gives us the JavaScript for the checkbox column for free. -->
		<th id="<?php echo $key?>" class="check-column">
			<input type="checkbox" name="<?php echo $css_tag ?>" id="<?php echo $css_tag ?>" value="1"/>
		</th>
	<?php	
	} //End: season_delete_cell()

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
		
		switch( $options['submit_value'] ) {
			case __( 'Select League', 'mstw-league-manager' ):
				//mstw_log_msg( 'In post(): updating league selection ...' );
				//mstw_log_msg( 'Setting transient: ' . $_POST['main_league'] );
				mstw_lm_set_current_league( $_POST['main_league'] );
				//$msg_str is only used in summary messages
				$msg_str =array( __( 'leagues', 'mstw-league-manager' ),  __( 'league', 'mstw-league-manager' ) );
				break;
				
			case __( 'Add New Season', 'mstw-league-manager' ):
				//mstw_log_msg( 'In post() method: Adding new season ...' );
				
				if ( array_key_exists( 'current_league', $_POST ) 
					 && !empty( $_POST['current_league'] )
					 && array_key_exists( 'season_name', $_POST )
					 && !empty( $_POST['season_name'] ) ) { 
					
					$current_league = $_POST['current_league'];
					$season_slug = sanitize_title( $_POST['season_name'] );
					
					$league_seasons = mstw_lm_get_league_seasons( $current_league );
					//mstw_log_msg( 'league seasons before:' );
					//mstw_log_msg( $league_seasons );
					
					//mstw_log_msg( 'in mstw_seasons_admin.post ... calling mstw_lm_update_league_seasons' );
					//mstw_log_msg( "current league: " . $_POST['current_league'] .", season slug: $season_slug" . ", season name: " . $_POST['season_name'] );
					
					mstw_lm_update_league_seasons( $current_league, $season_slug, $_POST['season_name'] );
					
					$league_seasons = mstw_lm_get_league_seasons( $current_league );
					natsort( $league_seasons );
					//mstw_log_msg( 'league seasons after:' );
					//mstw_log_msg( $league_seasons );
					
				}
				else {
					// Set up some error messages
				}
				
				break;
			
			case __( 'Delete Selected', 'mstw-league-manager' ):
				//mstw_log_msg( 'Delete Selected' );
				
				// Delete the selected seasons
				// Delete the records for those seasons
				// Don't need to delete the games (do that when league is deleted)
				if ( array_key_exists( 'current_league', $_POST ) 
					 && !empty( $_POST['current_league'] ) ) { 
				 
					$current_league = $_POST['current_league'];
					
					$league_seasons = mstw_lm_get_league_seasons( $current_league );
					
					//mstw_log_msg( $league_seasons );
					//mstw_log_msg( 'league: ' . $current_league );
					//mstw_log_msg( 'deleting selected seasons: ' );
					//mstw_log_msg( $_POST );
					//mstw_log_msg( '# of seasons: ' . count( $league_seasons ) );
					
					// Count for admin notice
					$nbr_deleted = 0;
					
					foreach ( $_POST as $key => $value ) {
						// Do not delete the last season
						if ( count( $league_seasons ) < 2 ) {
							//mstw_log_msg( 'Down to one season, not deleting another' );
							//mstw_log_msg( "key= $key, value= $value" );
							if ( 0 == $nbr_deleted ) {
								$season = reset( $league_seasons );
								$msg = sprintf( __( "Season %s not deleted. League must have at least one season.", 'mstw-league-manager' ), $season );
								mstw_lm_add_admin_notice( 'error', $msg );
							}
							break;
						}
						else if ( 'delete_all' != $key and false !== strpos( $key, 'delete' ) ) {
							
							//mstw_log_msg( "deleting $key" );
							$pos = strpos( $key, '_' );
							$nk = substr( $key, $pos + 1 );
							//mstw_log_msg( "unsetting $nk" );
							unset( $league_seasons[ $nk ] );
							
							$nbr_deleted++;
							
							$args = array(  'posts_per_page' => -1,
											'post_type' => 'mstw_lm_record',
										 );
							$records = get_posts( $args );
							foreach ( $records as $record ) {
								$record_title = get_the_title( $record );
								//mstw_log_msg( 'record title: ' . $record_title );
								//mstw_log_msg( 'season: ' . $nk );
								
								if ( false !== strpos( $record_title, $current_league . '_' ) ) {
									if ( false !== strpos( $record_title, $nk . '_' ) ) {
										//mstw_log_msg( 'delete record: ' . $record_title );
										// bypass trash & just delete
										$result = wp_delete_post( $record -> ID );
										if ( false === $result ) {
											//mstw_log_msg( $record->ID . ' not deleted ... problemo' );
										}
									}
									
								}
							}
						}
					}
					
					// Reset the seasons option for the league
					//mstw_log_msg( '$league_seasons after: ' );
					//mstw_log_msg( $league_seasons );
					update_option( "lm-league-seasons_" . $current_league, $league_seasons );
					
					if( $nbr_deleted > 0 ){
						mstw_lm_add_admin_notice( 'updated', sprintf( __( "%s season(s) deleted.", 'mstw-league-manager' ), $nbr_deleted ) );
					}
					
				}
				else {
					// Set up some error messages
				}
			

				break;
				
			default:
				//$this->log['notice'][] = sprintf( __( 'No function found for %s.)', 'mstw-league-manager' ), $options['submit_value'] );
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $options['submit_value'] . '. Exiting' );
				return;
				break;
		}
			
	} //End: post( )
	
	function process_game( $league, $game_date, $game_time, $game_tba, $home_team, $visitor, $location ) {
		//mstw_log_msg( 'in process_game ...' );
		if ( $home_team == $visitor ) {
			return 0;
		}
		
		//mstw_log_msg( '$league = ' . $league );
		
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
			
			$ret = wp_set_object_terms( $game_id, $league, 'mstw_lm_league', false );
			//mstw_log_msg( 'wp_set_object_terms:' );
			//mstw_log_msg( $ret );
			
			//mstw_log_msg( '$game_date = ' . $game_date );
			//mstw_log_msg( '$game_time = ' . $game_time  );
			//mstw_log_msg( "game_dtg = $game_date $game_time" );
			$game_dtg = strtotime( "$game_date $game_time" );
			
			update_post_meta( $game_id, 'game_unix_dtg', $game_dtg );
			
			//mstw_log_msg( '$game_tba = ' . $game_tba );
			update_post_meta( $game_id, 'game_is_tba', $game_tba );
			
			//mstw_log_msg( '$home_team = ' . $home_team );
			update_post_meta( $game_id, 'game_home_team', $home_team );
			
			//mstw_log_msg( '$visitor = ' .  $visitor );
			update_post_meta( $game_id, 'game_away_team', $visitor );
			
			//mstw_log_msg( '$location = ' . $location );
			update_post_meta( $game_id, 'game_location', $location );
		}
		else {
			mstw_log_msg( "wp_insert_post_failed $game_date, $home_team, $visitor" );
		}
		
		return 1;
		
	} // End: process_game( )
	
	//-------------------------------------------------------------
	// add help - outputs HTML for the contextual help area of the screen
	//		callback for action load-$add_seasons_page, set in mstw-lm-admin
	//		
	// ARGUMENTS:
	//	 None 
	//   
	// RETURNS:
	//	 Outputs HTML to the contextual help aree of the screen
	//-------------------------------------------------------------
	
	function add_help( ) {
		//mstw_log_msg( "in seasons add_help" );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
				
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'seasons-overview',
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
			case 'seasons-overview':
				?>
				<p><?php _e( 'This screen allows you to add (or delete) seasons to (from) a league. This allows each league to have schedules and standings for multiple years.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Season names are can not be editted after entry. Season slugs will be assigned automatically.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'When a season is deleted, all records for that season will be deleted.', 'mstw-league-manager' ) ?></p>
				<p><?php _e( 'Every league must have at least one season. You cannot delete all seasons for a league. Instead delete the league, which will delete all seasons.', 'mstw-league-manager' ) ?> </p>
				
				<p><a href="http://shoalsummitsolutions.com/lm-seasons/" target="_blank"><?php _e( 'See the Seasons man page for more details.', 'mstw-league-manager' ) ?></a></p>
				
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()
	
} //End: class MSTW_LM_UPDATE_GAMES
?>