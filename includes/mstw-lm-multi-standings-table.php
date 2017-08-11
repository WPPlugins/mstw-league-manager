<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-standings-table.php
 *	Contains the code for the MSTW League Manager multi standings table
 *		shortcode [mstw_multi_league_standings]
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

add_shortcode( 'mstw_multi_league_standings', 'mstw_lm_multi_league_standings' );

//-----------------------------------------------------------------------------
// MSTW_LM_MULTI_LEAGUE_STANDINGS
// 	Builds the html for the multi-league standings table
//
// ARGUMENTS
// 	$args: arguments passed to shortcode (leagues is the only one available )
//
// RETURNS
//	HTML for the standings table for the selected league & schedule, 
//	and the select league and schedule controls.
//
function mstw_lm_multi_league_standings( $args ) {
	mstw_log_msg( 'mstw_lm_multi_league_standings:' );
	//mstw_log_msg( 'args passed to function' );
	mstw_log_msg( $args );
	
	//check for the 'leagues' argument
	if( array_key_exists( 'leagues', $args ) ) {	
		mstw_log_msg( $args['leagues'] );
		$leagues = explode( ',', $args['leagues'] );
		mstw_log_msg( $leagues );	
	}
	else {
		$leagues = null;
	}
	
	$ret_html = '';
	
	//mstw_log_msg( 'Request method = ' . $_SERVER['REQUEST_METHOD'] );
	
	$league_cookie_name = 'mstw-lm-ml-league';
	
	$season_cookie_name = 'mstw-lm-ml-season';
	
	// set the current league
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$current_league = $_POST['current_league'];
	}
	else if ( array_key_exists( $league_cookie_name, $_COOKIE ) ) {
		$current_league = $_COOKIE[ $league_cookie_name ];
	}
	else {
		$current_league = mstw_lm_get_current_league( );
	}
	
	// set the current season
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$current_season = $_POST['current_season'];
	}
	else if ( array_key_exists( $season_cookie_name, $_COOKIE ) ) {
		$current_season = $_COOKIE[ $season_cookie_name ];
	}
	else {
		$current_season = mstw_lm_get_league_current_season( $current_league );
	}
	
	//$current_season = mstw_lm_get_league_current_season( $current_league );
	$url2 = plugin_dir_url( __FILE__ );
	$basename = basename( __FILE__ );
	$full_url = $url2 . $basename;
	
	/*if( !isset( $_COOKIE[ $league_cookie_name ] ) ) {
	  mstw_log_msg( "The cookie: '" . $league_cookie_name . "' is NOT set." );
	} else {
	  mstw_log_msg( "The cookie '" . $league_cookie_name . "' is set." );
	  mstw_log_msg( "Cookie is:  " . $_COOKIE[ $league_cookie_name ] );
	}
	
	if( !isset( $_COOKIE[ $season_cookie_name ] ) ) {
	  mstw_log_msg( "The cookie: '" . $season_cookie_name . "' is NOT set." );
	} else {
	  mstw_log_msg( "The cookie '" . $season_cookie_name . "' is set." );
	  mstw_log_msg( "Cookie is:  " . $_COOKIE[ $season_cookie_name ] );
	}
	*/
	ob_start( );
	?>
	
	<form id="multi-league-standings" method="POST" action="">
	  <div class="mstw-ms-controls">
		<div class="ms-title">
		<?php _e( 'League:', 'mstw-league-manager' ) ?>
		</div>
		<div class="ms-control">
		<?php mstw_lm_build_league_select( $current_league, $id = 'current_league', true, $leagues ) ?>
		</div>
			
		<div class="ms-title">
		<?php _e( 'Season:', 'mstw-league-manager' ) ?>
		</div>
		<div class="ms-control">
		<?php mstw_lm_build_season_select( $current_league, $current_season, $id = 'current_season' )?>
		</div>
		
		<div class="ms-control button">
		<input type="submit" class="secondary" id="lm-ml-submit" name="lm-ml-submit" value="Update Standings Table"/>
		</div>
				
	  </div> <!-- .leftalign actions -->
	</form>
	
	<?php echo do_shortcode( "[mstw_league_standings league='$current_league' season='$current_season']" ); ?>
	
	<?php 
	return ob_get_clean();

} //End: mstw_lm_multi_league_standings

// The action is set in the main plugin file
// add_action( 'init', 'mstw_lm_process_multi_league_standings');

function mstw_lm_process_multi_league_standings( ) {
	//mstw_log_msg( "mstw_lm_process_multi_league_standings:" );
	global $_POST;
	//mstw_log_msg( 'Request method = ' . $_SERVER['REQUEST_METHOD'] );
	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		if ( array_key_exists( 'lm-ml-submit', $_POST ) ) {
			$value = stripslashes( $_POST[ 'lm-ml-submit' ] );
			//mstw_log_msg( "mstw_lm_process_multi_league_standings:" );
			//mstw_log_msg( '$_POST:' );
			//mstw_log_msg( $_POST );
			$league_cookie_name = 'mstw-lm-ml-league';
			//mstw_log_msg( 'COOKIEPATH: ' . COOKIEPATH );
			//mstw_log_msg( 'COOKIE_DOMAIN: ' . COOKIE_DOMAIN );
			setcookie( $league_cookie_name, $_POST['current_league'], time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	
			$season_cookie_name = 'mstw-lm-ml-season';
			setcookie( $season_cookie_name, $_POST['current_season'], time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		}
	}
	
	//exit;
	
} //End: mstw_lm_process_multi_league_standings( )