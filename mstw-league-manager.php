<?php
/*
Plugin Name: MSTW League Manager
Plugin URI: https://wordpress.org/plugins/mstw-league-manager/
Description: Manages multiple sports leagues. Displays league schedules and standings.
Version: 1.4
Author: Mark O'Donnell
Author URI: http://shoalsummitsolutions.com
Text Domain: mstw-league-manager
*/

/*---------------------------------------------------------------------------
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
 *	GNU General Public License for more details. <http://www.gnu.org/licenses/>
*-------------------------------------------------------------------------*/

//------------------------------------------------------------------------
// Initialize the plugin ... include files, define globals, register CPTs
//
add_action( 'init', 'mstw_lm_init' );

add_action( 'init', 'mstw_lm_process_multi_league_standings' );

function mstw_lm_init( ) {
	//------------------------------------------------------------------------
	// "Helper functions" used throughout the MSTW plugin family
	//
	require_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-utility-functions.php' );
	
	//mstw_log_msg( 'in mstw_lm_init ...' );
	//mstw_log_msg( 'required once mstw-utility-functions.php' );
	
	// ----------------------------------------------------------------
	// Set up translation/localization with the 'mstw-league-manager'
	// This means the WP language packs will be used exclusively
	//
	load_plugin_textdomain( 'mstw-league-manager' );
	
	//mstw_log_msg( 'loaded mstw-league-manager text domain' );
	
	//--------------------------------------------------------------------------------
	// REGISTER THE MSTW LEAGUE MANAGER CUSTOM POST TYPES & TAXONOMIES
	//	mstw_lm_team, mstw_lm_league
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-cpts.php' );
	mstw_lm_register_cpts( );

	//-----------------------------------------------------------------
	// If on an admin screen, load the admin functions (gotta have 'em)
	//
	if ( is_admin( ) ) {
		require_once ( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-admin.php' );
	}
	
	//------------------------------------------------------------------------
	// Functions for MSTW league schedule table shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-schedule-table.php' );
	
	//------------------------------------------------------------------------
	// Functions for the mstw_league_standings_2 shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-league-schedule-class.php' );
	$league_schedule = new MSTWLeagueSchedule;
	
	//------------------------------------------------------------------------
	// Functions for MSTW league schedule gallery shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-schedule-gallery.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW league scoreboard shortcode
	//
	//include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-league-scoreboard.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW league standings shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-standings-table.php' );
	
	
	//------------------------------------------------------------------------
	// Functions for MSTW team schedule shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-team-schedule.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW venue table shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-venue-table.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW league slider shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-league-slider-class.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW team schedule slider shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-team-slider-class.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW multi-league standings shortcode
	//
	include_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-multi-standings-table.php' );
	
	//------------------------------------------------------------------------
	// "Helper functions" that are MSTW Schedules & Scoreboards specific
	//
	require_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-lm-utility-functions.php' );
	
	// Enqueue the plugin's stylesheet and any scripts
	
	add_action( 'wp_enqueue_scripts', 'mstw_lm_enqueue_scripts' );
	
	/*
	//------------------------------------------------------------------------
	// Functions for MSTW venue table shortcode
	//
	include_once( MSTW_lm_INCLUDES_DIR . '/mstw-lm-venue-table.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW countdown timer shortcode
	//
	include_once( MSTW_lm_INCLUDES_DIR . '/mstw-lm-countdown-timer.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW schedule slider shortcode
	//
	include_once( MSTW_lm_INCLUDES_DIR . '/mstw-lm-schedule-slider.php' );
	
	//------------------------------------------------------------------------
	// Functions for MSTW scoreboard shortcode
	//
	include_once( MSTW_lm_INCLUDES_DIR . '/mstw-lm-scoreboard.php' );

	//------------------------------------------------------------------------
	// If an admin screen, load the admin functions (gotta have 'em)
	
	if ( is_admin( ) )
		include_once ( MSTW_lm_INCLUDES_DIR . '/mstw-lm-admin.php' );
		
		
	//mstw_log_msg( 'in mstw_lm_init ... taxonomies:' );
	//mstw_log_msg( get_taxonomies( ) );
	*/
} //End: mstw_lm_init( )

// ----------------------------------------------------------------
// add ajax action for manage games screen
//
add_action( 'wp_ajax_manage_games', 'mstw_lm_ajax_callback' );

// ----------------------------------------------------------------
// add ajax action for manage games screen
//
add_action( 'wp_ajax_multi_league', 'mstw_lm_ml_ajax_callback' );
add_action( 'wp_ajax_nopriv_multi_league', 'mstw_lm_ml_ajax_callback' );

// Want to do this with wp_localize_script, but there are "issues" with that
// "preferred" approach
//add_action( 'wp_head', 'mstw_lm_add_ajax_library' );

function mstw_lm_add_ajax_library( ) {
	//mstw_log_msg( 'mstw_lm_add_ajax_library:' );
	
	 $html = '<script type="text/javascript">';
        $html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
    $html .= '</script>';
 
    echo $html; 
	
} //End: mstw_lm_add_ajax_library( )

// ----------------------------------------------------------------
// filter so single-player template does  not need to be in the theme directory
//
add_filter( "single_template", "mstw_lm_single_game_template" );

function mstw_lm_single_game_template( $single_template ) {
	 global $post;

	 if ($post->post_type == 'mstw_lm_game') {
		  $single_template = dirname( __FILE__ ) . '/templates/single-game.php';  
	 }
	
	 return $single_template;
	 
} //End: mstw_lm_single_game_template()
	
//-----------------------------------------------------------------
// find the taxonomy_league template in the plugin's directory
//
add_filter( "taxonomy_template", "mstw_lm_taxonomy_league_template" );
	
function mstw_lm_taxonomy_league_template( $template ) {
	//mstw_log_msg( "mstw_lm_taxonomy_league_template: $template" );
	
	//mstw_log_msg( "taxonomy: " . get_query_var( 'taxonomy' ) );
	
	if ( 'mstw_lm_league' == get_query_var( 'taxonomy' ) ) {	
		$custom_taxonomy_template = get_stylesheet_directory( ) . '/taxonomy-league.php';
		$plugin_taxonomy_template = dirname( __FILE__ ) . '/templates/taxonomy-league.php';
		//mstw_log_msg( "plugin template: $plugin_taxonomy_template" );
		if ( file_exists( $custom_taxonomy_template ) ) {
			$template = $custom_taxonomy_template;
		}
		else if ( file_exists( $plugin_taxonomy_template ) ) {
			$template = $plugin_taxonomy_template;
		}	
	}
	//mstw_log_msg( "returning template: $template" );	 
	return $template;
		 
 } //End: mstw_lm_taxonomy_league_template( )

//------------------------------------------------------------------------
// Check for the right version of WP on plugin activation
//
//register_activation_hook( MSTW_lm_PLUGIN_FILE, 'mstw_lm_register_activation_hook' );

function mstw_lm_register_activation_hook( ) {
	include_once( MSTW_lm_INCLUDES_DIR . '/mstw-utility-functions.php' );
	mstw_requires_wordpress_version( '4.0' );
	mstw_lm_add_user_roles( );
} //End: mstw_lm_register_activation_hook( )

//------------------------------------------------------------------------
// Creates the MSTW Schedules & Scoreboards roles and adds the MSTW capabilities
//		to those roles and the WP administrator and editor roles
//
function mstw_lm_add_user_roles( ) {
	//include_once( MSTW_lm_INCLUDES_DIR . '/mstw-utility-functions.php' );
	
	//
	// mstw_admin role - can do everything in all MSTW plugins
	//
	
	//mstw_log_msg( "in mstw_lm_add_user_roles ..." );
	
	//This allows a reset of capabilities for development
	remove_role( 'mstw_admin' );
	
	$result = 	add_role( 'mstw_admin',
						  __( 'MSTW Admin', 'mstw-league-manager' ),
						  array( 'manage_mstw_plugins'  => true,
								 'edit_posts' => true
								 //true allows; use false to deny
								) 
						 );
						 
	if ( $result != null ) {
		$result->add_cap( 'view_mstw_menus' );
		mstw_lm_add_caps( $result, null, 'schedule', 'schedules' );
		mstw_lm_add_caps( $result, null, 'team', 'teams' );
		mstw_lm_add_caps( $result, null, 'game', 'games' );
		mstw_lm_add_caps( $result, null, 'sport', 'sports' );
		mstw_lm_add_caps( $result, null, 'venue', 'venues' );
	}
	else 
		mstw_log_msg( "Oops, failed to add MSTW Admin role. Already exists?" );
	
	//
	// mstw_lm_admin role - can do everything in Schedules & Scoreboards plugin
	//
	
	//This allows a reset of capabilities for development
	remove_role( 'mstw_lm_admin' );
	
	$result = 	add_role( 'mstw_lm_admin',
						  __( 'MSTW Schedules & Scoreboards Admin', 'mstw-league-manager' ),
						  array( 'manage_mstw_schedules'  => true, 
								  'read' => true
								  //true allows; use false to deny
								) 
						 );
	
	if ( $result != null ) {
		$result->add_cap( 'view_mstw_lm_menus' );
		mstw_lm_add_caps( $result, null, 'schedule', 'schedules' );
		mstw_lm_add_caps( $result, null, 'team', 'teams' );
		mstw_lm_add_caps( $result, null, 'game', 'games' );
		mstw_lm_add_caps( $result, null, 'sport', 'sports' );
		mstw_lm_add_caps( $result, null, 'venue', 'venues' );
	}
	else {
		mstw_log_msg( "Oops, failed to add MSTW Schedules & Scoreboards Admin role. Already exists?" );
	}
	
	//
	// site admins can play freely
	//
	$role = get_role( 'administrator' );
	
	//mstw_log_msg( " adding capabilities to admin role ..." );
	
	mstw_lm_add_caps( $role, null, 'schedule', 'schedules' );
	mstw_lm_add_caps( $role, null, 'team', 'teams' );
	mstw_lm_add_caps( $role, null, 'game', 'games' );
	mstw_lm_add_caps( $role, null, 'sport', 'sports' );
	mstw_lm_add_caps( $role, null, 'venue', 'venues' );
	
	//
	// site editors can play freely
	//
	$role = get_role( 'editor' );
	
	mstw_lm_add_caps( $role, null, 'schedule', 'schedules' );
	mstw_lm_add_caps( $role, null, 'team', 'teams' );
	mstw_lm_add_caps( $role, null, 'game', 'games' );
	mstw_lm_add_caps( $role, null, 'sport', 'sports' );
	mstw_lm_add_caps( $role, null, 'venue', 'venues' );
	
} //End: mstw_lm_add_user_roles( )

//------------------------------------------------------------------------
// Adds the MSTW capabilities to either the $role_obj or $role_name using
//		the custom post type names (from the capability_type arg in
//		register_post_type( )
//
//	ARGUMENTS:
//		$role_obj: a WP role object to which to add the MSTW capabilities. Will
//					be used of $role_name is none (the default)
//		$role_name: a WP role name to which to add the MSTW capabilities. Will
//					be used if present (not null)
//		$cpt: the custom post type for the capabilities 
//				( map_meta_cap is set in register_post_type() )
//		$cpt_s: the plural of the custom post type
//				( $cpt & $cpt_s must match the capability_type argument
//					in register_post_type( ) )
//	RETURN: none
//
function mstw_lm_add_caps( $role_obj = null, $role_name = null, $cpt, $cpt_s ) {
	$cap = array( 'edit_', 'read_', 'delete_' );
	$caps = array( 'edit_', 'edit_others_', 'publish_', 'read_private_', 'delete_', 'delete_published_', 'delete_others_', 'edit_private_', 'edit_published_' );
	
	if ( $role_name != null ) {
		$role_obj = get_role( $role_name );
	}
	
	if( $role_obj != null ) {
		//'singular' capabilities
		foreach( $cap as $c ) {
			$role_obj -> add_cap( $c . $cpt );
		}
		
		//'plural' capabilities
		foreach ($caps as $c ) {
			$role_obj -> add_cap( $c . $cpt_s );
		}
		
		$role_obj -> add_cap( 'read' );
	}
	else {
		$role_name = ( $role_name == null ) ? 'null' : $role_name;
		mstw_log_msg( 'Bad args passed to mstw_lm_add_caps( ). $role_name = ' . $role_name . ' and $role_obj = null' );
	}
	
} //End: mstw_lm_add_caps( )

//------------------------------------------------------------------------
// Queue up the necessary JS & CSS  
//

function mstw_lm_enqueue_scripts( ) {
	
	// Find the full path to the plugin's css file 
	$mstw_lm_style_url = plugins_url( '/css/mstw-lm-styles.css', __FILE__ );
	//mstw_log_msg( "stylesheet url: $mstw_lm_style_url" );
	//Do we really want to make links relative? Probably not.
	//mstw_log_msg( "relative stylesheet url: $mstw_lm_style_url" );
	
	wp_register_style( 'mstw_lm_style', $mstw_lm_style_url );
	
	//$mstw_lm_style_file = WP_PLUGIN_DIR . '/game-schedules/css/mstw-gs-styles.css';
	$mstw_lm_style_file = plugin_dir_path( __FILE__ ) . 'css/mstw-lm-styles.css';

	// If stylesheet exists, enqueue the style
	if ( file_exists( $mstw_lm_style_file ) ) {	
		wp_enqueue_style( 'mstw_lm_style' );			
	} 

	$mstw_lm_custom_stylesheet = get_stylesheet_directory( ) . '/mstw-lm-custom-styles.css';
	
	//mstw_log_msg( 'custom stylesheet path: ' . $mstw_lm_custom_stylesheet );
	
	if ( file_exists( $mstw_lm_custom_stylesheet ) ) {
		$mstw_lm_custom_stylesheet_url = get_stylesheet_directory_uri( ) . '/mstw-lm-custom-styles.css';
		//mstw_log_msg( 'custom stylesheet uri: ' . $mstw_lm_custom_stylesheet_url );
		wp_register_style( 'mstw_lm_custom_style', $mstw_lm_custom_stylesheet_url );
		wp_enqueue_style( 'mstw_lm_custom_style' );
	}
	
	//javascript for league schedule slider next and prev arrows
	$script_file = plugins_url( 'js/lm-slider.js',  __FILE__  );
	wp_enqueue_script( 'lm-slider', $script_file, array('jquery'), false, true );
	
	//javascript for league schedule ticker next and prev arrows
	$script_file = plugins_url( 'js/lm-ticker.js',  __FILE__  );
	wp_enqueue_script( 'lm-ticker', $script_file, array('jquery'), false, true );
	
	//
	//Need Ajax script for multi-standings
	//
	$script_file = plugins_url( 'js/lm-multi-standings-ajax.js',  __FILE__  );
	//mstw_log_msg( 'dirname: ' . dirname( __FILE__ ) );
	//mstw_log_msg( 'plugins_url: ' . plugins_url( ) );
	//mstw_log_msg( 'Enqueing: ' . $script_file );
	wp_enqueue_script( 'multi-standings-ajax', 
					   $script_file, 
					   array( 'jquery-ui-core' ), 
					   false, 
					   true );
					   
					   
	//wp_localize_script( 'multi-standings-ajax', 'lm-multi-standings-ajax.js', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_localize_script( 'multi-standings-ajax', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );	
							   
	// in JavaScript, object properties are accessed as 
	// mstw_lm_ajax_object.ajax_url, mstw_lm_ajax_object.some_string, etc. 
	// NOTE: can't use '-' in JavaScript object 'mstw_lm_ajax_object'
	/*
	$data_array = array( 
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'some_string' => __( 'String to translate', 'mstw-league-manager' ),
					'a_value' => '10',
					 );
	wp_localize_script( 'lm-manage-games-ajax', 'mstw_lm_ajax_object', $data_array );
	*/
} //end mstw_lm_enqueue_styles( )

//------------------------------------------------------------------------
// Add some links to the plugins page
//
//add_filter( 'plugin_action_links', 'mstw_lm_plugin_action_links', 10, 2 );

function mstw_lm_plugin_action_links( $links, $file ) {
	static $this_plugin;

    if ( !$this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }

    if ( $file == $this_plugin ) {
        // The "page" query string value must be equal to the slug
        // of the Settings admin page we defined earlier
		
		$site_url = site_url( '/wp-admin/edit.php?post_type=mstw_lm_game&page=mstw-lm-settings' );
		
		$settings_link = "<a href='$site_url'>Settings</a>";
		
        array_unshift( $links, $settings_link );
    }

    return $links;
}

/*
 *   Need to enqueue the Ajax stuff
 */
 //-----------------------------------------------------------------
 // mstw_lm_ml_ajax_callback - callback for ALL AJAX posts in the plugin
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: JSON response to the AJAX post (including error messages)
 //
 function mstw_lm_ml_ajax_callback ( ) {
	//mstw_log_msg( 'mstw_lm_ml_ajax_callback:' );
	global $wpdb;  //this provides access to the WP DB
	
	//mstw_log_msg( 'received data: $_POST[]' );
	//mstw_log_msg( $_POST );
	
	if ( array_key_exists( 'real_action', $_POST ) ) {
		
		$action = $_POST['real_action'];
		//mstw_log_msg( 'action= ' . $action );
		
		switch( $action ) {
			case 'change_league':
				$response = mstw_lm_ml_ajax_change_league( );
				break;
				
			default:
				mstw_log_msg( "Error: Invalid action, $action, on page: " . $_POST['page'] );
				$response['error'] = __( 'AJAX Error: invalid action.', 'mstw-league-manager' );
				break;
		}
	}
	else {
		//mstw_log_msg( "AJAX Error: no action found." );
		$response['error'] = __( 'AJAX Error: no action found.', 'mstw-league-manager' );
		//wp_die( );
	}
	
	//mstw_log_msg( $response );
	
	echo json_encode( $response );
	
	wp_die( ); //gotta have this to keep server straight
	
 } //End: mstw_lm_ml_ajax_callback( )
 
 //-----------------------------------------------------------------
 // mstw_lm_ml_ajax_change_league - builds response (a new seasons list)
 //	when league select-option is changed on the front end of the 
 // multi-standings table plugin
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: HTML for the options list(s) or error message.
 //
 function mstw_lm_ml_ajax_change_league( ) {
	//mstw_log_msg( 'in mstw_lm_ml_ajax_change_league ...' );
	//$_POST should be global
	
	$response = array( 'response'   => 'league',
					   'seasons'    => '',
					   'error'      => ''
					 );
		
	if ( array_key_exists( 'league', $_POST ) ) {
		
		$top_level_league = $_POST['league']; // the name is a remnant
		
		if ( array_key_exists( 'season', $_POST ) ) {
			$current_season = $_POST['season'];
		} else {
			$current_season = '';
		}
		
		// We're going to build the seasons first, then the teams HTML
		$seasons = mstw_lm_build_seasons_list( $top_level_league, false );
		
		if ( $seasons ) {
			// we have a list of seasons so we will always get a current season
			//$current_season = mstw_lm_get_league_current_season( $top_level_league );
			$seasons_html = '';
			foreach ( $seasons as $slug => $name ) { 
				$selected = selected( $slug, $current_season, false );
				$seasons_html .= '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';
			}
			
			$response['seasons'] = $seasons_html;
					
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
	
 } //End: mstw_lm_ml_ajax_change_league( )
?>