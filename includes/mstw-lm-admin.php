<?php
/*	mstw-lm-admin.php
 *	Main file for the admin portion of the MSTW League Manager Plugin
 *	Loaded conditioned on is_admin() 
 */

/*---------------------------------------------------------------------
Copyright 2015  Mark O'Donnell  (email : mark@shoalsummitsolutions.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

Code from the CSV Importer plugin was modified under that plugin's 
GPLv2 (or later) license from Smackcoders. 

Code from the File_CSV_DataSource class was re-used unchanged under
that class's MIT license & copyright (2008) from Kazuyoshi Tlacaelel. 
-----------------------------------------------------------------------*/

 //-----------------------------------------------------------------
 // Set-up Action and Filter Hooks for the Settings on the admin side
 //-----------------------------------------------------------------

 //-----------------------------------------------------------------
 // Load the stuff admin needs
 // This is called from the init hook in mstw-league-manager.php
 //
 if ( is_admin( ) ) {
	add_action( 'admin_enqueue_scripts', 'mstw_lm_admin_enqueue_scripts' );
	
	// Clean up seasons (options) when league is deleted
	// mstw_lm_cleanup_league_meta is in mstw-lm-league-tax-admin.php
	add_action( 'delete_term_taxonomy', 'mstw_lm_cleanup_league_meta', 10, 1 );
	//add_action( 'delete_term_relationships', 10, 2 );
		
	// Add a menu item for the Admin pages
	add_action('admin_menu', 'mstw_lm_admin_menu');
	
	// Add custom admin messages for CPTs (Adding/editting CPTs)
	add_filter('post_updated_messages', 'mstw_lm_updated_messages');

	// Add custom admin bulk messages for CPTs (deleting & restoring CPTs)
	add_filter( 'bulk_post_updated_messages', 'mstw_lm_bulk_post_updated_messages', 10, 2 );

	// Hide the publishing actions on the edit and new CPT screens
	add_action( 'admin_head-post.php', 'mstw_lm_hide_publishing_actions' );
	add_action( 'admin_head-post-new.php', 'mstw_lm_hide_publishing_actions' );

	// Hide the list icons on the CPT edit (all) screens
	add_action( 'admin_head-edit.php', 'mstw_lm_hide_list_icons' );	

	// Remove Quick Edit Menu
	add_filter( 'post_row_actions', 'mstw_lm_remove_quick_edit', 10, 2 );
 
	// Remove edit from the Bulk Actions pull-down
	add_filter( 'bulk_actions-edit-mstw_lm_team', 'mstw_lm_remove_bulk_edit' );
	add_filter( 'bulk_actions-edit-mstw_lm_game', 'mstw_lm_remove_bulk_edit' );
	add_filter( 'bulk_actions-edit-mstw_lm_venue', 'mstw_lm_remove_bulk_edit' );
	add_filter( 'bulk_actions-edit-mstw_lm_record', 'mstw_lm_remove_bulk_edit' );
	
	// Remove delete from the Bulk Actions pull-down
	add_filter( 'bulk_actions-edit-mstw_lm_league', 'mstw_lm_remove_bulk_delete' );
	
	// Testing the plugin's mstw_lm_sports_list filter
	//add_filter( 'mstw_lm_tbd_list', 'mstw_lm_upgrade_tbd_list', 10, 1 );
	
	// Testing the plugin's mstw_lm_points_calc filter
	//add_filter( 'mstw_lm_date_formats', 'mstw_lm_upgrade_date_formats', 10, 1 );
	
	// Testing the plugin's mstw_lm_points_calc filter
	//add_filter( 'mstw_lm_time_formats', 'mstw_lm_upgrade_time_formats', 10, 1 );
	
	//
	// Add custom admin messages for adding/editting custom taxonomy terms
	//
	add_filter( 'term_updated_messages', 'mstw_lm_updated_term_messages');
	
	//
	// include the necessary files all admin screens
	//
	include_once 'mstw-lm-settings-class.php';
	include_once 'mstw-lm-team-cpt-admin.php';	
	include_once 'mstw-lm-game-cpt-admin.php';	
	include_once 'mstw-lm-venue-cpt-admin.php';
		
	include_once 'mstw-lm-league-tax-admin.php';
	
	include_once 'mstw-lm-seasons-admin.php';

	include_once 'mstw-lm-csv-import-class.php';
	include_once 'mstw-lm-update-games-class.php';
	include_once 'mstw-lm-update-records-class.php';
	include_once 'mstw-lm-add-games-class.php';
	
 } else {
	die( __( 'You is no admin. You a cheater!', 'mstw-league-manager' ) );
 } //End: if ( is_admin() )
	

 //----------------------------------------------------------------
 // Hide the publishing actions on the edit and new CPT screens
 // Callback for admin_head-post.php & admin_head-post-new.php actions
 //
 function mstw_lm_hide_publishing_actions( ) {
	//mstw_log_msg( 'in ... mstw_lm_hide_publishing_actions' );
	//mstw_log_msg( $post_type );
	
	$post_type = mstw_get_current_post_type( );

	if( 'mstw_lm_team'    == $post_type or 
		 'mstw_lm_game'   == $post_type or
		 'mstw_lm_venue'  == $post_type or
		 'mstw_lm_record' == $post_type ) {	
		?>
			<style type="text/css">
				#misc-publishing-actions,
				#minor-publishing-actions{
					display:none;
				}
				div.view-switch {
					display: none;
				}
				div.tablenav-pages.one-page {
					display: none;
				}
				
			</style>
		<?php					
	}
} //End: mstw_lm_hide_publishing_actions( )
	
 //----------------------------------------------------------------
 // Hide the list icons on the CPT edit (all) screens
 // Callback for admin_head-edit action
 function mstw_lm_hide_list_icons( ) {
	//mstw_log_msg( 'in ... mstw_lm_hide_list_icons' );
	//mstw_log_msg( $post_type );
	
	$post_type = mstw_get_current_post_type( );
	
	if ( 'mstw_lm_team'   == $post_type or
		 'mstw_lm_game'   == $post_type or
		 'mstw_lm_venue'  == $post_type or
		 'mstw_lm_record' == $post_type ) {
		?>
			<style type="text/css">
				select#filter-by-date,
				div.view-switch {
					display: none;
				}	
			</style>
		<?php
	}
	if ( 'mstw_lm_venue' == $post_type ) {
		?>
		<style type="text/css">
			input#post-query-submit.button {
				display: none;
			}	
		</style>
	<?php		
	}
		
 } //End: mstw_lm_hide_list_icons( )
	
 //-----------------------------------------------------------------	
 // Add admin scripts and CSS stylesheets: 
 //		datepicker & timepicker for schedules (datepicker as a dependency)
 //		media-upload & another-media for loading team logos 
 //		lm-add-games for the add games screen
 //		lm-update-games for the update games screen
 //		lm-manage-games for the Add/Edit Game (CPT) screen
 //

 function mstw_lm_admin_enqueue_scripts( $hook_suffix ) {
	//mstw_log_msg( 'in mstw_lm_admin_enqueue_scripts ...' );
	
	global $typenow;
	global $pagenow;
	
	//mstw_log_msg( 'enqueueing: ' . plugins_url( 'css/mstw-lm-admin-styles.css',dirname( __FILE__ ) ) );	
	wp_enqueue_style( 'lm-admin-styles', plugins_url( 'css/mstw-lm-admin-styles.css', dirname( __FILE__ ) ), array(), false, 'all' );
	
	// Load the scrips and styles for the media manager
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_media( );
	
	// this is the custom media stuff for team logos
	wp_enqueue_script( 'another-media', 
						plugins_url( 'js/another-media.js', dirname( __FILE__ ) ),
						null, 
						false, 
						true 
					 );
	
	wp_enqueue_style('thickbox');

	// If it's the add games screen, enqueue the datepicker & timepicker scripts & stylesheets  
	if ( 'league-manager_page_mstw-lm-add-games' == $hook_suffix  ) {
		//mstw_log_msg( 'enqueueing script: ' . plugins_url( 'js/lm-add-games.js', dirname( __FILE__ ) ) );
		wp_enqueue_script( 'lm-add-games', 
						   plugins_url( 'js/lm-add-games.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core', 'jquery-ui-datepicker' ), 
						   false, 
						   true );
						   
		//mstw_log_msg( 'enqueueing script: ' . plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ) );
		wp_enqueue_script( 'jquery-timepicker', 
						   plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
		
		//mstw_log_msg( 'enqueueing stylesheet: ' . plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ) );		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );
							   
		//mstw_log_msg( 'enqueueing stylesheet: ' . plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ) );			
		wp_enqueue_style( 'jquery-time-picker-style', 
						  plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ), 
						  array(), 
						  false, 
						  'all' );
			
	}
	
	//
	// If it's the update games screen, enqueue the update games script
	//	
	if ( 'league-manager_page_mstw-lm-update-games' == $hook_suffix  ) {
		//mstw_log_msg( '$typenow = ' . $typenow );
		//mstw_log_msg( '$hook_suffix = ' . $hook_suffix );
		//mstw_log_msg( 'enqueueing script: ' . plugins_url( 'js/lm-update-games.js', dirname( __FILE__ ) ) );
		wp_enqueue_script( 'lm-update-games', 
						   plugins_url( 'js/lm-update-games.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
		
		//mstw_log_msg( 'enqueueing stylesheet: ' . plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ) );		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );		
	}
	
	//
	// If it's the update records screen, enqueue the update records script
	//	
	if ( 'league-manager_page_mstw-lm-update-records' == $hook_suffix  ) {
		//mstw_log_msg( '$typenow = ' . $typenow );
		//mstw_log_msg( '$hook_suffix = ' . $hook_suffix );
		//mstw_log_msg( 'enqueueing script: ' . plugins_url( 'js/lm-update-records.js', dirname( __FILE__ ) ) );
		
		wp_enqueue_script( 'lm-update-records', 
						   plugins_url( 'js/lm-update-records.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
		
		//mstw_log_msg( 'enqueueing stylesheet: ' . plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ) );		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );		
	}
	
	//
	// Unfortunately post.php/post-new.php are the available hooks
	// They are handled by mstw_lm_is_edit_page( )
	//
	if ( mstw_lm_is_edit_page( null ) ) {
		//enqueue the datepicker script & stylesheet if it's the game edit page 
		if( 'mstw_lm_game' == $typenow ) {
			//mstw_log_msg( 'enqueueing: ' . plugins_url( 'js/lm-manage-games-ajax.js', dirname( __FILE__ ) ) );
			wp_enqueue_script( 'lm-manage-games-ajax', 
							   plugins_url( 'js/lm-manage-games-ajax.js', dirname( __FILE__ ) ), 
							   array( 'jquery-ui-core', 'jquery-ui-datepicker' ), 
							   false, 
							   true );
							   
			// in JavaScript, object properties are accessed as 
			// mstw_lm_ajax_object.ajax_url, mstw_lm_ajax_object.some_string, etc. 
			// NOTE: can't use '-' in JavaScript object 'mstw_lm_ajax_object'
			$data_array = array( 
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'some_string' => __( 'String to translate', 'mstw-league-manager' ),
							'a_value' => '10',
							 );
			wp_localize_script( 'lm-manage-games-ajax', 'mstw_lm_ajax_object',
            $data_array );
							   			   
			//mstw_log_msg( 'enqueueing script: ' . plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ) );
			wp_enqueue_script( 'jquery-timepicker', 
						   plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
							   
			//mstw_log_msg( 'enqueueing: ' . plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ) );
			wp_enqueue_style( 'jquery-style', 
							  plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
							  array(), 
							  false, 
							  'all' );
							  
			//wp_enqueue_style( 'jquery-ui-datepicker' );
			
			//mstw_log_msg( 'enqueueing: ' . plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ) );			
			wp_enqueue_style( 'jquery-timepicker-style', 
							  plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ), 
							  array(), 
							  false, 
							  'all' );				   
								
		}
		
	} //End: if( 'post.php' == $hook_suffix 

 } //End: mstw_lm_admin_enqueue_scripts( )

 //-----------------------------------------------------------------
 // mstw_lm_ajax_callback - callback for ALL AJAX posts in the plugin
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: JSON response to the AJAX post (including error messages)
 //
 function mstw_lm_ajax_callback ( ) {
	//mstw_log_msg( 'in mstw_lm_ajax_callback ...' );
	global $wpdb;  //this provides access to the WP DB
	
	//mstw_log_msg( 'received data: $_POST[]' );
	//mstw_log_msg( $_POST );
	
	if ( array_key_exists( 'real_action', $_POST ) ) {
		
		$action = $_POST['real_action'];
		//mstw_log_msg( 'action= ' . $action );
		
		switch( $action ) {
			case 'change_league':
				$response = mstw_lm_ajax_change_league( );
				break;
				
			case 'change_nonleague':
				$response = mstw_lm_ajax_change_nonleague( );
				break;
				
			case 'change_season':
				mstw_lm_set_league_current_season( $_POST['league'], $_POST['season'] );
				//$current_league = mstw_lm_get_current_league( );
				//mstw_log_msg( 'current_league= ' . $current_league );
				//mstw_log_msg( 'current_season= ' . mstw_lm_get_league_current_season( $current_league ) );
				$response = array( 'response'   => 'change_season', 
								   'error'      => ''
								  );
				break;
				
			default:
				mstw_log_msg( "Error: Invalid action, $action, on page: " . $_POST['page'] );
				$response['error'] = __( 'AJAX Error: invalid action.', 'mstw-league-manager' );
				break;
		}
	}
	else {
		mstw_log_msg( "AJAX Error: no action found." );
		$response['error'] = __( 'AJAX Error: no action found.', 'mstw-league-manager' );
		//wp_die( );
	}
	
	//mstw_log_msg( $response );
	
	echo json_encode( $response );
	
	wp_die( ); //gotta have this to keep server straight
	
 } //End: mstw_lm_ajax_callback( )

 // ----------------------------------------------------------------
 // mstw_lm_ajax_change_nonleague - builds response (options html) when non-league
 //		checkbox is changed.
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: HTML for the options list or error message.
 //
 function mstw_lm_ajax_change_nonleague( ) {
	//mstw_log_msg( 'in mstw_lm_ajax_change_nonleague ...' );
	//$_POST should be global
	
	//		
	// Only doing one set of options for now
	//
	$home = ( array_key_exists( 'home_selected', $_POST ) ) ? $_POST['home_selected'] : null;
	
	//$away = ( array_key_exists( 'away_selected', $_POST ) ) ? $_POST['away_selected'] : null;
	
	$selected = ( 'true' == $_POST['nonleague'] ) ? null : $_POST['selected'];
	
	$home_html = mstw_lm_build_teams_options( $selected, $home );
	//$away_html = mstw_lm_build_teams_options( $selected, $away );
	
	if ( !empty( $home_html ) ) {
		$response = array( 'response'   => 'change_nonleague', 
						   'home_teams' => $home_html,
						   //'away_teams' => $away_html,
						   'error'      => ''
						  );
	}
	else {
		// no teams? return error and do nothing
		//mstw_log_msg( "in mstw_lm_ajax_callback ... no teams found ");
		$response = array( 'response'   => 'nonleague', 
						   'home_teams' => '',
						   //'away_teams' => '',
						   'error'      => 'AJAX Error: no teams found.'
						  );
	}
	
	return $response;
	
 } //End: mstw_lm_ajax_change_nonleague( )

 //-----------------------------------------------------------------
 // mstw_lm_ajax_change_league - builds response when league select-option is changed.  
 //		Builds seasons list, and teams list if needed.
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: HTML for the options list(s) or error message.
 //
 function mstw_lm_ajax_change_league( ) {
	//mstw_log_msg( 'in mstw_lm_ajax_change_league ...' );
	//$_POST should be global
	
	$response = array( 'response'   => 'league',
					   'seasons'    => '',
					   'teams'      => '',
					   'error'      => ''
					 );
		
	if ( array_key_exists( 'league', $_POST ) ) {
		
		$top_level_league = $_POST['league']; // the name is a remnant
		
		// We're going to build the seasons first, then the teams HTML
		$seasons = mstw_lm_build_seasons_list( $top_level_league, false );
		
		if ( $seasons ) {
			// we have a list of seasons so we will always get a current season
			$current_season = mstw_lm_get_league_current_season( $top_level_league );
			
			$seasons_html = '';
			foreach ( $seasons as $slug => $name ) { 
				$selected = selected( $slug, $current_season, false );
				$seasons_html .= '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';
			}
			
			$response['seasons'] = $seasons_html;
			
			// Done with seasons on to teams
			// why are we doing this only on the manage games screen?
			if ( array_key_exists( 'page', $_POST ) && 
				'manage_games' != $_POST['page'] ) {
				mstw_lm_set_current_league( $top_level_league );
			}
			
			if ( array_key_exists( 'page', $_POST ) && ('add_games' == $_POST['page'] or 'manage_games' == $_POST['page'] ) ) {
				
				$teams_html = mstw_lm_build_teams_options( $top_level_league, null );
				
				if ( !empty( $teams_html ) ) {
					$response['teams'] = $teams_html;
				}
				else {
					mstw_log_msg( "AJAX Error: No teams found for league: " . $_POST['league'] ); 
					$response['error'] = sprintf( __( 'AJAX Error: No teams found for league: %s', 'mstw-league-manager' ), $_POST['league'] );
				}
				
			} //End: if ( array_key_exists( 'page', $_POST ) ...
					
		} //End: if( $seasons )
			
		else {
			mstw_log_msg( "AJAX Error: League $top_level_league has no seasons" );
			$response['error'] = sprintf( __( 'AJAX Error: No seasons for league: %s', 'mstw-league-manager' ), $top_level_league );

		} //End: else for if ( $seasons )
		
	} //End: if ( array_key_exists( 'league', $_POST ) )
	
	else {
		// got a problem
		mstw_log_msg( "AJAX Error: No league provided to handler." ); 
		$response['error'] = __( 'AJAX Error: No league provided to handler.', 'mstw-league-manager' );	
		
	}
	
	return $response;
	
 } //End: mstw_lm_ajax_change_league( )

 //-----------------------------------------------------------------
 // mstw_lm_build_teams_options - builds options for select-option control of teams
 //	ARGUMENTS: 
 //		$league: league (slug)to get teams for. Defaults to null => all teams in DB
 //		$selected: team (slug ) that's selected in control 
 //			(defaults to first option )
 //	
 //	RETURNS:
 //		HTML for the options list 
 //		Returns the number of leagues, or -1 if no leagues are found
 //	
 function mstw_lm_build_teams_options( $league = null, $selected = null ) {
	//mstw_log_msg( 'in mstw_lm_build_teams_options ...' );
	//mstw_log_msg( "league = $league / selected = $selected" );
	
	if ( null === $league ) {
		// grab all teams in DB
		$args = array( 'numberposts'    => -1,
					   'post_type'      => 'mstw_lm_team',
				       //'mstw_lm_league' => $league,
				       'meta_key'		=> 'team_name',
				       'orderby'        => 'meta_value',
				       'order' => 'ASC' 
				     );
	}
	else {
		// grab all teams in specified league
		$args = array( 'numberposts'    => -1,
					   'post_type'      => 'mstw_lm_team',
				       'mstw_lm_league' => $league,
				       'meta_key'		=> 'team_name',
				       'orderby'        => 'meta_value',
				       'order' => 'ASC' 
				     );
		
	}
	
	$teams = get_posts( $args );
	
	//mstw_log_msg( 'got teams: ' );
	//mstw_log_msg( $teams );
	
	$options = '';
	
	if ( $teams ) {
		
		foreach ( $teams as $team ) { 
			$options .= '<option value="' . $team->post_name . '" ' . selected( $selected, $team -> post_name, false ) . '>' .  $team->post_title . '</option>';
		}
	
	}
	
	return $options;
	
 } // End: mstw_lm_build_teams_options( )
	
 //-----------------------------------------------------------------
 // Remove Quick Edit Menu	
 //
 function mstw_lm_remove_quick_edit( $actions, $post ) {
	//mstw_log_msg( 'in mstw_lm_remove_quick_edit ... ' );
	
	if( 'mstw_lm_team'   == $post->post_type or
		'mstw_lm_venue'  == $post->post_type or
		'mstw_lm_record' == $post->post_type ) {
			
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
		
	}
	else if ( 'mstw_lm_game' == $post->post_type ) {
		
		unset( $actions['inline hide-if-no-js'] );
		
	}
	
	return $actions;
		
 } //End: mstw_lm_remove_quick_edit()
	
 //-----------------------------------------------------------------
 // Remove the Bulk Actions edit option
 //	actions for mstw_lm_game, _team, _venue, _record
 //	
 function mstw_lm_remove_bulk_edit( $actions ){
		unset( $actions['edit'] );
		return $actions;
 } //End: mstw_lm_remove_bulk_edit()
 
 //-----------------------------------------------------------------
 // Remove the Bulk Actions delete option, which entirely removes
 //	the pulldown and button for the mstw_lm_league taxonomy 
 //
 function mstw_lm_remove_bulk_delete( $actions ){
		//unset( $actions['edit'] );
		unset( $actions['delete'] );
		return $actions;
 } //End: mstw_lm_remove_bulk_delete( )

 //-----------------------------------------------------------------
 // Sets up the plugin menus and adds the actions for WP contextual help
 //
 function mstw_lm_admin_menu( ) {
	//mstw_log_msg( 'in mstw_lm_admin_menu' );
	
	//
	// Main League Manager Page (provides getting started summary)
	//
	$manage_leagues_page = add_menu_page( 
			__( 'League Manager', 'mstw-league-manager' ),    //$page_title, 
			__( 'League Manager', 'mstw-league-manager' ), //$menu_title, 
		   'read',                                         //$capability,
		   'league-manager-page',                          //menu page slug
		   'mstw_lm_league_manager_page',				   //callback function	   
		   plugins_url( 'images/mstw-admin-menu-icon.png', dirname( __FILE__ ) ),   //$menu_icon
		   "58.85" //menu position
			);
		 
	//
	// Leagues (Taxonomy)
	//	
	$manage_leagues_page = add_submenu_page( 	
			'league-manager-page',
			__( 'Manage Leagues', 'mstw-league-manager' ),           //page title
			__( 'Leagues', 'mstw-league-manager' ),                  //menu title
			'read',                                                         // Capability required to see this option.
			'edit-tags.php?taxonomy=mstw_lm_league&post_type=mstw_lm_game', // Slug name to refer to this menu
			null                                                            // Callback to output content							
		   );
		   
	add_action( "load-edit-tags.php", 'mstw_lm_add_leagues_help' );
	
	//
	// Seasons
	//
	if ( class_exists( 'MSTW_LM_SEASONS' ) ) {
		$plugin = new MSTW_LM_SEASONS;
		
		$add_seasons_page = add_submenu_page(
				'league-manager-page',  //parent page, 
				__( 'Manage Seasons', 'mstw-league-manager' ), //page title
				__( 'Seasons', 'mstw-league-manager' ), //menu title
				'read',  // Capability required to see this option.
				'mstw-lm-seasons',     // Slug name for this menu
				array( $plugin, 'form' )  // Callback to output content						
				); 
			
		add_action( "load-$add_seasons_page", array( $plugin, 'add_help' )  );
	}
	else {
		mstw_log_msg( 'MSTW_LM_SEASONS class does not exist' );
	}
	
	//
	// Venues (CPT)
	//			
	$venues_page = add_submenu_page( 
			'league-manager-page',  //parent page
			__( 'Manage Venues', 'mstw-league-manager' ), //page title
			__( 'Venues', 'mstw-league-manager' ), //menu title
			'read', // Capability required to see this option.
			'edit.php?post_type=mstw_lm_venue', // Slug name to refer to this menu
			null	// Callback to output content					
			);
	//mstw_log_msg( '$venues_page= ' . $venues_page );	
	add_action( "load-edit.php", 'mstw_lm_venues_help' );
		 
	//
	// Teams (CPT)
	//			
	$teams_page = add_submenu_page( 
			'league-manager-page',  //parent page
			__( 'Manage Teams', 'mstw-league-manager' ), //page title
			__( 'Teams', 'mstw-league-manager' ), //menu title
			'read', // Capability required to see this option.
			'edit.php?post_type=mstw_lm_team', // Slug name to refer to this menu
			null							
			); // Callback to output content
			
	//mstw_log_msg( $teams_page );
	add_action( "load-edit.php", 'mstw_lm_teams_help' );
	
						
	
	//
	// Update all records in a league/season
	//
	if ( class_exists( 'MSTW_LM_UPDATE_RECORDS' ) ) {
		$plugin = new MSTW_LM_UPDATE_RECORDS;
		
		$update_records_page = add_submenu_page(
			'league-manager-page',  //parent page, 
			__( 'Update Records', 'mstw-league-manager' ), //page title
			__( 'Update Records', 'mstw-league-manager' ), //menu title
			'read',                    // Capability required to see this option.
			'mstw-lm-update-records',  // Slug name for this menu
			array( $plugin, 'form' )   // Callback to output content	
			);

			add_action( "load-$update_records_page", array( $plugin, 'add_help' ) );
	}
	else {
		mstw_log_msg( 'MSTW_LM_UPDATE_RECORDS class does not exist' );
	}
	
	//
	// Manage existing games
	//
	$manage_games_page = add_submenu_page( 	
			'league-manager-page', 
			__( 'Manage Games', 'mstw-league-manager' ), //page title
			__( 'Manage Games', 'mstw-league-manager' ), //menu title
			'read', // Capability required to see this option.
			'edit.php?post_type=mstw_lm_game', // Slug name to refer to this menu
			null	// Callback to output content						
			);
			
	add_action( "load-edit.php", 'mstw_lm_games_help' );
	
	//
	// Add multiple games to a league-season
	//
	if ( class_exists( 'MSTW_LM_ADD_GAMES' ) ) {
		$plugin = new MSTW_LM_ADD_GAMES;
		
		$add_games_page = add_submenu_page(
				'league-manager-page',  //parent page, 
				__( 'Add Games', 'mstw-league-manager' ), //page title
				__( 'Add Games', 'mstw-league-manager' ), //menu title
				'read',  // Capability required to see this option.
				'mstw-lm-add-games',     // Slug name for this menu
				array( $plugin, 'form' )  // Callback to output content						
				); 
			
		add_action( "load-$add_games_page", array( $plugin, 'add_help' ) );
	}
	else {
		mstw_log_msg( 'MSTW_LM_ADD_GAMES class does not exist' );
	}
	
	//
	// Update all games in a league/season
	//
	if ( class_exists( 'MSTW_LM_UPDATE_GAMES' ) ) {
		$plugin = new MSTW_LM_UPDATE_GAMES;
		
		$update_page = add_submenu_page(
			'league-manager-page',  //parent page, 
			__( 'Update Games', 'mstw-league-manager' ), //page title
			__( 'Update Games', 'mstw-league-manager' ), //menu title
			'read',  // Capability required to see this option.
			'mstw-lm-update-games',     // Slug name for this menu
			array( $plugin, 'form' )  // Callback to output content						
			); 
			
		add_action( "load-$update_page", array( $plugin, 'add_help' ) );
	}
	else {
		mstw_log_msg( 'MSTW_LM_UPDATE_GAMES class does not exist' );
	}
	
	//
	// Settings
	//
	if ( class_exists( 'MSTW_LM_SETTINGS' ) ) {
		$plugin = new MSTW_LM_SETTINGS;	
		
		$settings_page = add_submenu_page( 	
				'league-manager-page', 
				__( 'Settings', 'mstw-league-manager' ), //page title
				__( 'Settings', 'mstw-league-manager' ), //menu title
				'read', //capability required to see this menu item
				'mstw-lm-settings', //slug name to refer to this menu
				array( $plugin, 'form' )   // Callback to output content						
				); 
				
		add_action( "load-$settings_page", array( $plugin, 'add_help' ) );
	}
	else {
		mstw_log_msg( 'MSTW_LM_SETTINGS class does not exist.' );
	}

	//add_action( "load-$settings_page", 'mstw_lm_settings_help' );
	
	//
	// CSV File Import
	//
	if ( class_exists( 'MSTW_LM_CSV_IMPORTER' ) ) {
		$plugin = new MSTW_LM_CSV_IMPORTER;
		
		$csv_importer_page = add_submenu_page(
			'league-manager-page',  //parent page, 
			__( 'CSV Import', 'mstw-league-manager' ), //page title
			__( 'CSV Import', 'mstw-league-manager' ), //menu title
			'read', // Capability required to see this option.
			'mstw-lm-csv-import', // Slug name for this menu
			array( $plugin, 'form' )	// Callback to output content						
			); 
			
		add_action( "load-$csv_importer_page", array( $plugin, 'add_help' )  );
	}
	else {
		mstw_log_msg( 'MSTW_LS_CSV_IMPORTER class does not exist' );
	}				

 } //End: mstw_lm_admin_menu()
	
 //-----------------------------------------------------------------------
 // Add custom admin messages for CPTs (Adding/editting CPTs
 //
 function mstw_lm_updated_messages( $messages ) {	
	// Don't need mstw_lm_record - custom screen for record CPT

	$messages['mstw_lm_team'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Team updated.', 'mstw-league-manager' ),
		2  => __( 'Custom field updated.', 'mstw-league-manager'),
		3  => __( 'Custom field deleted.', 'mstw-league-manager' ),
		4  => __( 'Team updated.', 'mstw-league-manager' ),
		5  => __( 'Team restored to revision', 'mstw-league-manager' ),
		6  => __( 'Team published.', 'mstw-league-manager' ),
		7  => __( 'Team saved.', 'mstw-league-manager' ),
		8  => __( 'Team submitted.', 'mstw-league-manager' ),
		9  => __( 'Team scheduled for publication.', 'mstw-league-manager' ),
		10 => __( 'Team draft updated.', 'mstw-league-manager' ),
	);
	
	$messages['mstw_lm_game'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Game updated.', 'mstw-league-manager' ),
		2  => __( 'Custom field updated.', 'mstw-league-manager'),
		3  => __( 'Custom field deleted.', 'mstw-league-manager' ),
		4  => __( 'Game updated.', 'mstw-league-manager' ),
		5  => __( 'Game restored to revision', 'mstw-league-manager' ),
		6  => __( 'Game published.', 'mstw-league-manager' ),
		7  => __( 'Game saved.', 'mstw-league-manager' ),
		8  => __( 'Game submitted.', 'mstw-league-manager' ),
		9  => __( 'Game scheduled for publication.', 'mstw-league-manager' ),
		10 => __( 'Game draft updated.', 'mstw-league-manager' ),
	);
	
	$messages['mstw_lm_venue'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Venue updated.', 'mstw-league-manager' ),
		2  => __( 'Custom field updated.', 'mstw-league-manager'),
		3  => __( 'Custom field deleted.', 'mstw-league-manager' ),
		4  => __( 'Venue updated.', 'mstw-league-manager' ),
		5  => __( 'Venue restored to revision', 'mstw-league-manager' ),
		6  => __( 'Venue published.', 'mstw-league-manager' ),
		7  => __( 'Venue saved.', 'mstw-league-manager' ),
		8  => __( 'Venue submitted.', 'mstw-league-manager' ),
		9  => __( 'Venue scheduled for publication.', 'mstw-league-manager' ),
		10 => __( 'Venue draft updated.', 'mstw-league-manager' ),
	);
	
	return $messages;
		
 } //End: mstw_lm_updated_messages( )
 
 //-----------------------------------------------------------------------
 // Add custom admin bulk messages for CPTs (deleting & restoring CPTs)
 //
 function mstw_lm_bulk_post_updated_messages( $messages, $bulk_counts ) {
		
	// Don't need mstw_lm_record - custom screen for record CPT

	$messages['mstw_lm_team'] = array(
		'updated'   => _n( '%s team updated.', '%s teams updated.', $bulk_counts['updated'], 'mstw-league-manager' ),
		'locked'    => _n( '%s team not updated, somebody is editing it.', '%s teams not updated, somebody is editing them.', $bulk_counts['locked'], 'mstw-league-manager' ),
		'deleted'   => _n( '%s team permanently deleted.', '%s teams permanently deleted.', $bulk_counts['deleted'], 'mstw-league-manager' ),
		'trashed'   => _n( '%s team moved to the Trash.', '%s teams moved to the Trash.', $bulk_counts['trashed'], 'mstw-league-manager' ),
		'untrashed' => _n( '%s team restored from the Trash.', '%s teams restored from the Trash.', $bulk_counts['untrashed'], 'mstw-league-manager' ),
	);
	
	$messages['mstw_lm_game'] = array(
	'updated'   => _n( '%s game updated.', '%s games updated.', $bulk_counts['updated'], 'mstw-league-manager' ),
	'locked'    => _n( '%s game not updated, somebody is editing it.', '%s games not updated, somebody is editing them.', $bulk_counts['locked'], 'mstw-league-manager' ),
	'deleted'   => _n( '%s game permanently deleted.', '%s games permanently deleted.', $bulk_counts['deleted'], 'mstw-league-manager' ),
	'trashed'   => _n( '%s game moved to the Trash.', '%s games moved to the Trash.', $bulk_counts['trashed'], 'mstw-league-manager' ),
	'untrashed' => _n( '%s game restored from the Trash.', '%s games restored from the Trash.', $bulk_counts['untrashed'], 'mstw-league-manager' ),
	);
	
	$messages['mstw_lm_venue'] = array(
	'updated'   => _n( '%s venue updated.', '%s venues updated.', $bulk_counts['updated'], 'mstw-league-manager' ),
	'locked'    => _n( '%s venue not updated, somebody is editing it.', '%s venues not updated, somebody is editing them.', $bulk_counts['locked'], 'mstw-league-manager' ),
	'deleted'   => _n( '%s venue permanently deleted.', '%s venues permanently deleted.', $bulk_counts['deleted'], 'mstw-league-manager' ),
	'trashed'   => _n( '%s venue moved to the Trash.', '%s venues moved to the Trash.', $bulk_counts['trashed'], 'mstw-league-manager' ),
	'untrashed' => _n( '%s venue restored from the Trash.', '%s venues restored from the Trash.', $bulk_counts['untrashed'], 'mstw-league-manager' ),
	);
	
	return $messages;
		
 } //End: mstw_lm_bulk_post_updated_messages( )
 
 //-----------------------------------------------------------------------
 // Add custom admin messages for adding/editting custom taxonomy terms
 //
 
 function mstw_lm_updated_term_messages( $messages ) {
	//mstw_log_msg( 'in mstw_lm_updated_term_messages ... ' );
	//mstw_log_msg( $messages );
	
	$messages['mstw_lm_league'] = array(
				0 => '',
				1 => __( 'League added.', 'mstw-league-manager' ),
				2 => __( 'League deleted.', 'mstw-league-manager' ),
				3 => __( 'League updated.', 'mstw-league-manager' ),
				4 => __( 'League not added.', 'mstw-league-manager' ),
				5 => __( 'League not updated.', 'mstw-league-manager' ),
				6 => __( 'Leagues deleted.', 'mstw-league-manager' ),
			);

	return $messages;
	
 } //End: mstw_lm_updated_term_messages( )
 

 //-----------------------------------------------------------------
 // TESTING FILTERS
 //-----------------------------------------------------------------
 function mstw_lm_upgrade_tbd_list( $tbd_list ) {
	
	$tbd_list['dbt'] = 'DBT';
	
	natsort( $tbd_list );
	
	return $tbd_list;
	
 }

 function mstw_lm_upgrade_date_formats( $date_formats ) {
	
	$date_formats['dbt'] = 'DBT';
	
	//natsort( $date_formats );
	
	return $date_formats;
	
 }

 function mstw_lm_upgrade_time_formats( $time_formats ) {
	
	$time_formats['dbt'] = 'DBT';
	
	//natsort( $date_formats );
	
	return $time_formats;
	
 }

 function mstw_lm_upgrade_sports_list( $sports_list ) {
	
	$sports_list['badminton'] = 'Badminton';
	$sports_list['beach-volleyball'] = 'Beach Volleyball';
	$sports_list['rifle'] = 'Rifle';
	natsort( $sports_list );
	
	return $sports_list;
	
 }

 function mstw_lm_modify_points_calc( $points, $record ) {
	//mstw_log_msg( 'in mstw_lm_modify_points_calc ...' );
	
	if ( array_key_exists( 'wins', $record ) ) {
		$points = $record['wins'] * 5;
	}
	
	return $points;
	
 }

 function mstw_lm_modify_percent_calc( $percent, $record ) {
	//mstw_log_msg( 'in mstw_lm_modify_percent_calc ...' );
	
	if ( array_key_exists( 'wins', $record ) && array_key_exists( 'losses', $record ) ) {
		if ( $games = $record['wins'] + $record['losses'] ) {
			$percent = $record['wins'] / $games;
		}
	}
	
	return $percent;
	
 }

 //-----------------------------------------------------------------
 // MSTW LEAGUE MANAGER QUICK START PAGE (hooked to the main menu item)
 //-----------------------------------------------------------------
 function mstw_lm_league_manager_page( ) {
	//mstw_log_msg( 'in mstw_lm_league_manager_page...' );
	global $pagenow;
	
	?>
	<div class="wrap">
		<h2><?php _e( 'League Manager - Quick Start', 'mstw-league-manager') ?></h2>
		<p>The following is a quick summary of the steps required to get League Manager up and running quickly.</p>
		<ol>
		<li><a href="<?php echo admin_url( '/edit-tags.php?taxonomy=mstw_lm_league&post_type=mstw_lm_game' ) ?>">LEAGUES</a>. Add at least one league on the Leagues screen.<br/>
			 Each League MUST have at least one season. If the Season field is blank, it will default to the current year.</br>
     		 Assign the correct sport to your league because the front end display settings are defined by SPORT, not by LEAGUE. These settings may be changed for each sport on the Settings admin screen.</li>
		<li><a href="<?php echo admin_url( '/edit.php?post_type=mstw_lm_venue' ) ?>">VENUES</a>. Add the team venues. You will want the venues in place when you create teams, and then games; so assign venues right away, rather than going back to do it later. (Note that the venues can be imported from the MSTW Schedules & Scoreboards plugin via the CSV Import screen.)</li>
		<li><a href="<?php echo admin_url( '/edit.php?post_type=mstw_lm_team' ) ?>">TEAMS</a>. Add teams to your league using the Teams admin screen. (Note that the teams, including their logos, can be imported from the MSTW Schedules & Scoreboards plugin via the CSV Import screen.)</li>
		<li><a href="<?php echo admin_url( '/admin.php?page=mstw-lm-update-records' ) ?>">UPDATE RECORDS</a>. To display league standings, add team records for a league & season.</li>
		<li><a href="<?php echo admin_url( '/admin.php?page=mstw-lm-add-games' ) ?>">ADD GAMES</a>. To display league and/or team schedules, add games to a league & season using the Add Games screen. Games can be added one at a time via the Manage Games screen, but the Add Games screen is much faster. Use the Manage Games screen to change individual games later.</li>
		<li><a href="<?php echo admin_url( '/admin.php?page=mstw-lm-update-games' ) ?>">UPDATE GAMES</a>. Update game status on the Update Games screen. This can also be done one game at time on the Manage Games screen, but the Update Games screen is much faster.</li>
		<li><a href="<?php echo admin_url( '/admin.php?page=mstw-lm-settings' ) ?>">SETTINGS</a>. Provides a rich set of controls for the LEAGUE STANDINGS and LEAGUE SCHEDULES plugin displays. Note that all settings are based on SPORT, not LEAGUE. All leagues with the same sport will have the same settings. Defaults are provided for NCAA football, Premier League Soccer, and NHL Hockey.</li>
		<li><a href="<?php echo admin_url( '/admin.php?page=mstw-lm-csv-import' ) ?>">CSV IMPORT</a>. Provides the ability to upload Venues and Teams (including team logos) from CSV formatted files. Note that these CSV files can generated from the MSTW Team Rosters and MSTW Schedules & Scoreboards plugin using the MSTW CSV Exporter.</li>
		
		</ol>
		
		<h3>DISPLAYING LEAGUE STANDINGS</h3>
		<p>League standings may displayed using the <code>[mstw_league_standings]</code> shortcode. The basic shortcode format is:
		<blockquote><code>[mstw_league_standings league=your-league-slug season=your-season-slug]</code></blockquote>
		<a href="http://shoalsummitsolutions.com/lm-shortcodes/" target="_blank">See the Shortcodes man page</a> for complete details.
		</p>
		
		<h3>DISPLAYING LEAGUE SCHEDULES</h3>
		<p>League schedules may displayed as tables or galleries using the shortcodes <code>[mstw_league_schedule_table]</code> and the <code>[mstw_league_schedule_gallery]</code>. The basic shortcode formats are:
		<blockquote><code>[mstw_league_schedule_table league=your-league-slug season=your-season-slug]</code> and <code>[mstw_league_schedule_gallery league=your-league-slug season=your-season-slug]</code></blockquote>
		<a href="http://shoalsummitsolutions.com/lm-shortcodes/" target="_blank">See the Shortcodes man page</a> for complete details.
		</p>
		
		<h3>DISPLAYING TEAM SCHEDULES</h3>
		<p>Team schedules may displayed using the <code>[mstw_team_schedule]</code> shortcode. The basic shortcode format is:
		<blockquote><code>[mstw_team_schedule team=your-team-slug season=your-season-slug]</code></blockquote>
		<a href="http://shoalsummitsolutions.com/lm-shortcodes/" target="_blank">See the Shortcodes man page</a> for complete details.
		</p>
		
	</div?
 <?php	
 }
 
 //-----------------------------------------------------------------
 // register_uninstall_hook(__FILE__, 'mstw_lm_delete_plugin_options');

 //-----------------------------------------------------------------
 // Callback for: register_uninstall_hook(__FILE__, 'mstw_lm_delete_plugin_options')
 //-----------------------------------------------------------------
 // It runs when the user deactivates AND DELETES the plugin. 
 // It deletes the plugin options DB entry, which is an array storing all the plugin options
 //-----------------------------------------------------------------
 //function mstw_lm_delete_plugin_options() {
 //	delete_option('mstw_lm_options');
 //
 
 //-----------------------------------------------------------------
 // mstw_lm_delete_plugin_options() - callback for uninstall hook
 //