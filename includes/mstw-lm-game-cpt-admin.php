<?php
/*----------------------------------------------------------------------------
 * mstw-lm-game-cpt-admin.php
 *	Handles the admin screen(s) for the mstw_lm_game CPT.
 *	It is loaded conditioned from the admin_init hook. 
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
 
 //-----------------------------------------------------------------
 // Move the meta boxes ahead of the content (game info)
 //
 add_action('edit_form_after_title', 'mstw_lm_build_game_screen' );

 if( !function_exists( 'mstw_lm_build_game_screen' ) ) {
	function mstw_lm_build_game_screen( ) {
		global $post, $wp_meta_boxes;
		//mstw_log_msg( "mstw_lm_build_game_screen:" );
		//mstw_log_msg( "post type: " . get_post_type( $post ) );
		
		// first make sure we're on the right screen ...
		if ( get_post_type( $post ) == 'mstw_lm_game' ) {
			//mstw_log_msg( "doing meta boxes ...");
			do_meta_boxes( get_current_screen( ), 'advanced', $post );
			unset( $wp_meta_boxes[get_post_type($post)]['advanced'] );
			echo "<p class='league-info-admin-head'>" . __( 'Game Information:', 'mstw-league-manager' ) . "</p>";
		}
	}  //End: mstw_lm_build_game_screen
 }

//-----------------------------------------------------------------
// Add the meta boxes for the mstw_lm_game custom post type
//
add_action( 'add_meta_boxes_mstw_lm_game', 'mstw_lm_game_metaboxes' );

function mstw_lm_game_metaboxes( ) {
	//mstw_log_msg( 'mstw_lm_add_metaboxes:' );
	
	//mstw_lm_admin_notice( );
	
	//
	// Removes the league taxonomy meta box from the sidebar
	//
	remove_meta_box( 'mstw_lm_leaguediv', 'mstw_lm_game', 'side');
	
	add_meta_box( 'mstw-lm-game-data-help', 
				  __( 'Add/Edit Game Help', 'mstw-league-manager' ), 
				  'mstw_lm_game_data_help_metabox', 
				  'mstw_lm_game', 
				  'advanced', 
				  'high', 
				  null );
				  
	add_meta_box( 'mstw-lm-game-data', 
				  __( 'Game Data', 'mstw-league-manager' ), 
				  'mstw_lm_game_data_metabox', 
				  'mstw_lm_game', 
				  'advanced', 
				  'high', 
				  null );
				  
	add_meta_box( 'mstw-lm-game-status', 
				  __( 'Game Status', 'mstw-league-manager' ), 
				  'mstw_lm_game_status_metabox', 
				  'mstw_lm_game', 
				  'advanced', 
				  'high', 
				  null );
				  
					
} //End: mstw_lm_game_metaboxes( )

//-----------------------------------------------------------------
// Build the HELP meta box for the Manage Games screen
//
function mstw_lm_game_data_help_metabox( $post ) {
	?>
	<p class='mstw-lm-admin-instructions'><?php _e( 'To quickly add games to a league schedule, use the Add Games screen, or CSV Import games. You can quickly update all games in a league using the Update screen. This screen provides finer control of individual games (which you may or may not ever need!) You can hide this information using the arrow at the top right.', 'mstw-league-manager' ) ?></p>
	
	<p class='mstw-lm-admin-instructions'><?php _e( 'See the', 'mstw-league-manager') ?> <a href="http://shoalsummitsolutions.com/lm-edit-game" target="_blank"><?php _e( 'Edit Game man page', 'mstw-league-manager' ) ?></a> <?php _e( 'for more information.', 'mstw-league-manager') ?></p>
	
	
	<h2>Game Data</h2>
	<ul class='mstw-lm-admin-instructions'>
	<li><?php _e( 'The game title does not appear on the front end displays. It is used to create the default game slug, and can help with searching and sorting on the All Games admin screen. Choose wisely.', 'mstw-league-manager' ) ?></li>
	<li><?php _e( 'If you change the Nonleague Game checkbox, the teams lists will be changed to display ALL teams in the database. Changing this setting may cause the Home and Visiting Team selections to change.', 'mstw-league-manager' ) ?></li>
	</ul>
	
	<h2>Game Status</h2>
	<ul class='mstw-lm-admin-instructions'>
	<li> <?php _e( 'This section is for the game status, or the game final results if the "Game Is Final" box is checked. It appears on the league scoreboard and single game displays.', 'mstw-league-manager' ) ?></li>
	<li><?php _e( 'If the "Game Is Open" checkbox is checked, the game status will be available for public updates. Games may be opened and closed for public updates automatically based on the plugin Settings. If the "Game Is Final" box is checked, the game is closed to further public updates.', 'mstw-league-manager' ) ?></li>
	</ul>
	
	<?php
	
}
//-----------------------------------------------------------------
// Build the meta box (controls) for the Games custom post type
//
function mstw_lm_game_data_metabox( $post ) {
	//mstw_log_msg( 'in mstw_lm_game_data_metabox ...' );
	
	wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_game_nonce' );
	
	//$options = wp_parse_args( get_option( 'mstw_lm_options' ),  mstw_lm_get_defaults( ) );
	
	//mstw_lm_set_current_league( 'pac-12' );
	
	// Retrieve the metadata values if they exist
	$game_league = get_post_meta( $post->ID, 'game_league', true );
	$game_season = get_post_meta( $post->ID, 'game_season', true );
	//$top_level_league = mstw_lm_get_top_level_league( $game_league );
	$game_unix_dtg = get_post_meta( $post->ID, 'game_unix_dtg', true );
	$game_is_tba = get_post_meta( $post->ID, 'game_is_tba', true );
	$game_home_team = get_post_meta( $post->ID, 'game_home_team', true );
	$game_away_team = get_post_meta( $post->ID, 'game_away_team', true );
	$game_location = get_post_meta( $post->ID, 'game_location', true );
	$game_media = get_post_meta( $post->ID, 'game_media', true );
	$game_media_link = get_post_meta( $post->ID, 'game_media_link', true );
	$game_nonleague = get_post_meta( $post->ID, 'game_nonleague', true );
	
	if( '' == $game_league ) {
		// NEW POST
		$game_league = mstw_lm_get_current_league( );
		//mstw_log_msg( );
		//$top_level_league = mstw_lm_get_top_level_league( $game_league );
		$game_season = mstw_lm_get_league_current_season( $game_league );
		
		//mstw_log_msg( "mstw_lm_game_data_metabox: new game with league= $game_league, season= $game_season" );
	}
	
	//mstw_log_msg( "league: $game_league | season: $game_season");
	
	//want to pull from settings eventually
	if ( '' != $game_unix_dtg ) {
		$game_time_str = date( 'H:i', $game_unix_dtg );   
		$game_date_str = date( 'Y-m-d', $game_unix_dtg );;
	}
	else {
		$game_time_str = "13:00"; //want to pull from settings
		$game_date_str = current_time( 'Y-m-d', 0 );
	}
	
	$std_length = 128;
	$std_size = 30;
	?>
	
   <table class="form-table mstw-lm-admin-table">
	<!-- Row 1: League, Season -->
	<tr valign="top">
		<th scope="row"><label for="game_league" ><?php _e( 'Game League:', 'mstw-league-manager' ); ?></label></th>
		
		<td><?php mstw_lm_build_league_select( $game_league, 'game_league' ) ?>
			<!--<input id="publish" type="submit" value="Update League" class="button button-secondary button-small" name="update_league"></input>-->
			<br/><span class="description"><?php _e( 'Changing the league, will reset the seasons and teams lists. Only leagues with at least one team are shown.', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="game_season" ><?php _e( 'Game Season:', 'mstw-league-manager' ) ?></label></th>
		<td><?php mstw_lm_build_season_select( $game_league, $game_season, 'game_season' )?>
			<!--<input id="publish" type="submit" value="Update Season" class="button button-secondary button-small" name="update_season"></input>
			<br/><span class="description"><?php //_e( 'Seasons are displayed and can only be set for top level leagues. Be sure to press the update button after changing the season.', 'mstw-league-manager' ) ?></span>-->
		</td>	
	</tr>
	
	<!-- Row 2: Nonleague game -->
	<tr valign="top">
	 <th scope="row"><label for="game_nonleague" >
	  <?php _e( 'Nonleague game:', 'mstw-league-manager' ) ?></label>
	 </th>
	 <td>
	  <input type='checkbox' name="game_nonleague" id="game_nonleague" value=1 <?php checked( $game_nonleague, 1, true ) ?> />
	  <br/><span class="description"><?php _e( 'Check if nonleague game. Will reset the home and visiting teams lists.', 'mstw-league-manager' ) ?></span>
	 </td>
	</tr>
	
	<!-- Row 3: Home team, Away Team -->
	<tr valign="top">
		<?php 
		$new_league = ( $game_nonleague ) ? "all_leagues" : $game_league ;
		//mstw_log_msg( "mstw_lm_game_metabox: new_league = $new_league" );
		?>
		<th scope="row"><label for="game_home_team" ><?php _e( 'Home Team:', 'mstw-league-manager' ); ?></label></th>
		<td><?php mstw_lm_build_teams_list( $new_league, $game_home_team, 'game_home_team' ) ?>
			<!--<br/><span class="description"><?php //_e( 'Home Team (create first on Teams admin screen).', 'mstw-league-manager' ) ?></span>-->
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="game_away_team" ><?php _e( 'Visiting Team:', 'mstw-league-manager' ) ?></label></th>
		<td><?php mstw_lm_build_teams_list( $new_league, $game_away_team, 'game_away_team' ) ?>
			<!--<br/><span class="description"><?php //_e( 'Visiting Team (create first on Teams admin screen).', 'mstw-league-manager' ) ?></span>-->
		</td>	
	</tr>
	
	<!-- Row 4: Game date, Game location -->
	<tr valign="top">
		<th scope="row"><label for="game_date" ><?php _e( 'Game Date:', 'mstw-league-manager' ); ?></label></th>
		<td><input type='text' maxlength="256" size="32" id='game_date' name="game_date" id="game_date" value="<?php echo $game_date_str ?>"/>
			<br/><span class="description"><?php _e( 'Game date (in format 2015-09-11).', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="game_location" ><?php _e( 'Game Location:', 'mstw-league-manager' ) ?></label></th>
		<td><?php mstw_lm_build_venues_control( $game_location, 'game_location' ) ?>
			<br/><span class="description"><?php _e( "Location defaults to home team's venue. Use this field for neutral site games.", 'mstw-league-manager' ) ?></span>
		</td>	
	</tr>
	
	<!-- Row 5: Game time, Game TBA -->
	<tr valign="top">
		<th scope="row"><label for="game_time" ><?php _e( 'Game Time:', 'mstw-league-manager' ) ?></label></th>
		<!--<td><input type='text' maxlength="256" size="32" name="game_time" id="game_time" class="hasTimepicker" 
			value="<?php //echo $game_time_str ?>"/> -->
		<td><input type='text' name="game_time" id="game_time" class="time ui-timepicker-input" autocomplete="off"
			value="<?php echo $game_time_str ?>"/>
			<br/><span class="description"><?php _e( 'Game time (in format 13:30).', 'mstw-league-manager' ) ?></span>
		</td>
		
		<th scope="row" class="mstw-admin-align-right"><label for="game_is_tba" ><?php _e( 'Game Time TBA:', 'mstw-league-manager' ) ?></label></th>
		<td><input type='checkbox' name="game_is_tba" id="game_is_tba" value=1 <?php checked( $game_is_tba, 1, true ) ?> />
			<br/><span class="description"><?php _e( 'Check if game time is not announced. "TBA" string from settings page will be displayed in place of game time.', 'mstw-league-manager' ) ?></span>
		</td>
		</tr>
		
		<!-- Row 6: Media -->
		<tr valign="top">
		 <th scope="row">
		  <label for="game_media" ><?php _e( 'Media Label:', 'mstw-league-manager' ) ?></label>
		 </th>
		 <td>
		  <input type='text' maxlength="256" size="32" name="game_media" id="game_media" value="<?php echo $game_media ?>"/><br/>
		  <span class="description"><?php _e( 'Game media is a free form field that can be used a a link to pretty much anything.', 'mstw-league-manager' ) ?></span>
		 </td>
		 
		 <th scope="row" class="mstw-admin-align-right">
		  <label for="game_media_link" ><?php _e( 'Media Link (URL):', 'mstw-league-manager' ) ?></label>
		 </th>
		 <td>
		  <input type='text' maxlength="256" size="32" name="game_media_link" id="game_media_link" value="<?php echo $game_media_link ?>"/><br/>
		  <span class="description"><?php _e( 'URL where the game media link will go.', 'mstw-league-manager' ) ?></span>
		 </td>
		</tr>
		
	</table>
	
<?php        	
} //End: mstw_lm_game_data_metabox()

//-----------------------------------------------------------------
// Build the meta box (controls) for the Game (CPT) status
//
function mstw_lm_game_status_metabox( $post ) {
	//mstw_log_msg( 'in mstw_lm_game_status_metabox ...' );
	//wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_game_nonce' );
	
	// Game status
	$game_home_score     = get_post_meta( $post->ID, 'game_home_score', true );
	$game_away_score     = get_post_meta( $post->ID, 'game_away_score', true );
	$game_time_remaining = get_post_meta( $post->ID, 'game_time_remaining', true );
	$game_period         = get_post_meta( $post->ID, 'game_period', true );
	$game_is_final       = get_post_meta( $post->ID, 'game_is_final', true );
	$game_is_open        = get_post_meta( $post->ID, 'game_is_open', true );
	?>
	
	<table class="form-table mstw-lm-admin-table">
		<!-- Row 1: Home score, Away score -->
		<tr valign="top">
			<th scope="row"><label for="game_home_score" ><?php _e( 'Home Score:', 'mstw-league-manager' ); ?></label></th>
			<td><input type='text' maxlength="32" size="32" name="game_home_score" id='game_home_score' value="<?php echo $game_home_score ?>" />
				<br/><span class="description"><?php _e( "Home team's score.", 'mstw-league-manager' ) ?></span>
			</td>
			<th scope="row" class="mstw-admin-align-right"><label for="game_away_score" ><?php _e( 'Visitor Score:', 'mstw-league-manager' ) ?></label></th>
			<td><input type='text' maxlength="32" size="32" name="game_away_score" id="game_away_score" value="<?php echo $game_away_score ?>" />
				<br/><span class="description"><?php _e( "Visiting team's score.", 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
		
		<!-- Row 2: Time remaining, Period -->
		<tr valign="top">
			<th scope="row"><label for="game_time_remaining" ><?php _e( 'Game Time:', 'mstw-league-manager' ); ?></label></th>
			<td><input type='text' maxlength="32" size="32" name="game_time_remaining" id="game_time_remaining" value="<?php echo $game_time_remaining ?>" />
				<br/><span class="description"><?php _e( 'Current game time (remaining or elapsed, depending on sport).', 'mstw-league-manager' ) ?></span>
			</td>
			<th scope="row" class="mstw-admin-align-right"><label for="game_period" ><?php _e( 'Period:', 'mstw-league-manager' ) ?></label></th>
			<td><input type='text' maxlength="32" size="32" name="game_period" id="game_period" value="<?php echo $game_period ?>" />
				<br/><span class="description"><?php _e( 'Current period of game.', 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
		
		<!-- Row 3: Game is final, Game is open (for public updates) -->
		<tr valign="top">
			<th scope="row"><label for="game_is_final" ><?php _e( 'Game Is Final:', 'mstw-league-manager' ); ?></label></th>
			<td><input type='checkbox' name="game_is_final" id="game_is_final" value=1 <?php checked( $game_is_final, 1, true ) ?> />
				<br/><span class="description"><?php _e( 'Check when game is over. Will close game to prevent further public updates to the game.', 'mstw-league-manager' ) ?></span>
			</td>
			<th scope="row" class="mstw-admin-align-right"><label for="game_is_open" ><?php _e( 'Game Is Open:', 'mstw-league-manager' ) ?></label></th>
			<td><input type='checkbox' name="game_is_open" id="game_is_open" value=1 <?php checked( $game_is_open, 1, true ) ?> />
				<br/><span class="description"><?php _e( 'Game is open for public updates. Automatic open and close times may be set on the Settings screen.', 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
   
		<?php //mstw_build_admin_edit_screen( $admin_fields ); ?>
	
	</table>
	
<?php        	
} //End: mstw_lm_game_status_metabox()


//-----------------------------------------------------------
//	mstw_lm_build_teams_list - Build (echoes to output) the 
//		select-option control of team names	
//		
// ARGUMENTS:
//	$current_league: league to use for teams list ("all_leagues" to show all leagues)
//	$current_team: current team (selected in control)
//	$id: string used as the select-option control's "id" and "name"
//
// RETURNS:
//	0 if there are no teams in the database
//	1 if select-option control was built successfully
//
function mstw_lm_build_teams_list( $current_league = "all_leagues", $current_team, $id ) {
	//mstw_log_msg( 'mstw_lm_build_teams_list:' );
	
	$team_slugs = mstw_lm_build_team_slug_array( $current_league );
	
	$teams = get_posts(array( 'numberposts' => -1,
					  'post_type'      => 'mstw_lm_team',
					  'post_status'    => 'published',
					  'orderby'        => 'title',
					  'post_name__in'   => $team_slugs,
					  //'mstw_lm_league' => $current_league,
					  /*'meta_query'     => array( 
											'key'     => 'team_slug',
											'value'   => $team_slugs,
											'compare' => 'IN',
											),*/
					  'order'          => 'ASC' 
					));	

	if( $teams ) {	
	?>
		<select id=<?php echo $id ?> name=<?php echo $id ?> >
			<?php
			foreach( $teams as $team ) {
				$selected = ( $current_team == $team->post_name ) ? 'selected="selected"' : '';
				echo "<option value='" . $team->post_name . "'" . $selected . ">" . get_the_title( $team->ID ) . "</option>";
			}
			?>
		</select>
	<?php
		return 1;
	} 
	else { // No teams found
		return 0;
	}
	
} //End: mstw_lm_build_teams_list( )

function mstw_lm_remove_updated_message( $messages ) {
	//mstw_log_msg( 'in mstw_lm_remove_updated_message' );
    return array();
}

//-----------------------------------------------------------------
// SAVE THE MSTW_LM_GAME CPT META DATA
//
add_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );

function mstw_lm_save_game_meta( $post_id, $post ) {
	//mstw_log_msg( 'in mstw_lm_save_game_meta ...' );
	//mstw_log_msg( '$post_id = ' . $post_id );
	//mstw_log_msg( $_POST );
	
	// check if this is an auto save routine. 
	// If it is our form has not been submitted, so don't do anything
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' || $post->post_status == 'trash' ) {
		//mstw_log_msg( 'in mstw_lm_save_game_meta ... doing autosave ... nevermind!' );
		return; //$post_id;
	}
	
	//check that we are in the right context ... saving from edit page
	// If NONCE is valid, process user input
	//
	if( isset($_POST['mstw_lm_game_nonce'] ) && 
		check_admin_referer( plugins_url(__FILE__), 'mstw_lm_game_nonce' ) ) {
			
		// These are the 'standard' fields. Sanitize and store.
		$data_fields = array( //'game_unix_dtg', // Gotta build this from date & time
							  
							  'game_league',
							  'game_season',
							  
							  'game_home_team', 
							  'game_away_team',
							  
							  'game_is_tba',
							  //'game_time', handled in game_is_tba
							  //'game_date', handled in game_is_tba
							  
							  'game_nonleague',

							  'game_location',
							  
							  'game_media',
							  'game_media_link',
							  
							  'game_home_score',
							  'game_away_score',
							  'game_time_remaining',
							  'game_period',
							  
							  'game_is_final',
							  //'game_is_open' ... handled in game_is_final
							);
							
		foreach( $data_fields as $field ) {
			//mstw_log_msg( 'updating field: ' . $field );
			//if ( 'team_link' == $field ) {
				//mstw_log_msg( sanitize_text_field( esc_attr( $_POST[ $field ] ) ) );
			//}
			switch( $field ) {
				case 'game_nonleague':
					$game_nonleague = mstw_safe_ref( $_POST, $field );
					$game_nonleague = ( 1 == $game_nonleague ) ? 1 : 0;
	
					//mstw_log_msg( "saving game ID: " . $post_id );
					//mstw_log_msg( "non-league game: $game_nonleague " );
					
					update_post_meta( $post_id, $field, $game_nonleague );
					break;
					
				case 'game_is_tba':
					// Build the game date & time, game_unix_dtg
					// first handle the TBA field
					$game_is_tba = mstw_safe_ref( $_POST, $field );
					$game_is_tba = ( 1 == $game_is_tba ) ? 1 : 0;
					update_post_meta( $post_id, $field, $game_is_tba );
					
					// if game is tba, set (fake) game time as noon, otherwise use $_POST['game_time']
					//$game_time = ( $game_is_tba ) ? '12:00' : mstw_safe_ref( $_POST, 'game_time' );
					$game_time = mstw_safe_ref( $_POST, 'game_time' );
					
					// figure out the game date in unix seconds
					// should always be set to a valid date, but defaults to current date
					$game_date = mstw_safe_ref( $_POST, 'game_date' );
					
					if( '' == $game_date ) {
						$game_date = date( 'Y-m-d', current_time( 'timestamp') );
					}
					
					$game_dtg_str = "$game_date $game_time";
					//mstw_log_msg( "game_dtg_str = $game_dtg_str" );
					
					$game_unix_dtg = strtotime( $game_dtg_str );
					
					update_post_meta( $post_id, 'game_unix_dtg', $game_unix_dtg );
					break;
				
				case 'game_is_final':
					// update checkboxes for final and open
					// if game is marked final, close it
					//
					$game_is_final = mstw_safe_ref( $_POST, $field );
					$game_is_final = ( $game_is_final == 1 ) ? 1 : 0;
					update_post_meta( $post_id, $field, $game_is_final );
					
					if( $game_is_final ) {
						if ( mstw_safe_ref( $_POST, 'game_is_open' ) ) {
							//post an admin message that game is closing
							mstw_lm_add_admin_notice( 'updated', __( 'Game is final so it was automatically closed.', 'mstw-league-manager') );
						}
						update_post_meta( $post_id, 'game_is_open', 0 );
					}
					else {
						$game_is_open = mstw_safe_ref( $_POST, 'game_is_open' );
						$game_is_open = ( $game_is_open == 1 ) ? 1 : 0;
						update_post_meta( $post_id, 'game_is_open', $game_is_open );
					}
					break;
					
				case 'game_media_link':
					update_post_meta( $post_id, $field, sanitize_text_field( esc_url( $_POST[ $field ] ) ) );
					break;
					
				default:
					update_post_meta( $post_id, $field, sanitize_text_field( esc_attr( $_POST[ $field ] ) ) );
					break;
			}
			
		}
		
		// Game League add taxonomy term for selected league & all parents
		// 1. Get the league that was set
		$game_league = $_POST[ 'game_league' ];
		
		if ( -1 != $game_league && '' != $game_league ) {
			
			// 1. Set the league 
			// One league only: Don't append, overwrite
			$term_tax_ids = wp_set_object_terms( $post_id, $game_league, 'mstw_lm_league', false );
			
			//mstw_log_msg( "Set term tax ids:" );
			//mstw_log_msg( $term_tax_ids );
		
			// 2. Get all the ancestors of that league, converted to slugs
			
			$game_term = get_term_by( 'slug', $game_league, 'mstw_lm_league', OBJECT, 'raw' );
			
		} //End: if ( -1 != $game_league )
		
	}
	else  if ( strpos( wp_get_referer( ), 'trash' ) === FALSE ) {	
		mstw_log_msg( 'Oops! In mstw_lm_save_game_meta() game nonce not valid' );
		mstw_lm_add_admin_notice( 'error', __( 'Invalid referrer. Contact system admin.', 'mstw-league-manager') );
	}
	
	/*
	$term_slugs = wp_get_object_terms( $post_id, 'mstw_lm_league', array( 'fields' => 'slugs' ) );
	mstw_log_msg( "mstw_lm_save_game_meta: leagues for $post_id: " );
	mstw_log_msg( $term_slugs );
	*/
	
} //End: mstw_lm_save_game_meta

// ----------------------------------------------------------------
// Set up the View All Games table
//
add_filter( 'manage_edit-mstw_lm_game_columns', 
			'mstw_lm_edit_game_columns' ) ;

function mstw_lm_edit_game_columns( $columns ) {	

	//$options = wp_parse_args( (array)get_option( 'mstw_lm_options' ), mstw_lm_get_defaults( ) );
		
	//$new_options = wp_parse_args( (array)$options, mstw_lm_get_defaults( ) );

	$columns = array(
		'cb' 			  => '<input type="checkbox" />',
		'title' 		  => __( 'Title', 'mstw-league-manager' ),
		'game_date' 	  => __( 'Date', 'mstw-league-manager' ),
		'game_time' 	  => __( 'Time', 'mstw-league-manager' ),
		'game_home_team'  => __( 'Home', 'mstw-league-manager' ),
		'game_away_team'  => __( 'Away', 'mstw-league-manager' ),
		'game_location'   => __( 'Location', 'mstw-league-manager' ),
		'game_league' 	  => __( 'League(s)', 'mstw-league-manager' ),
		'game_season'	  => __( 'Season', 'mstw-league-manager' ),
		'game_nonleague'  => __( 'Nonleague', 'mstw-league-manager' ),
		);

	return $columns;
} //End: mstw_lm_edit_game_columns( )

//-----------------------------------------------------------------
// Display the View All Games table columns
// 
add_action( 'manage_mstw_lm_game_posts_custom_column',
			'mstw_lm_manage_game_columns', 10, 2 );

function mstw_lm_manage_game_columns( $column, $post_id ) {
	
	//Need the admin time and date formats
	//$options = wp_parse_args( get_option( 'mstw_lm_dtg_options' ), mstw_lm_get_dtg_defaults( ) );
	
	$game_timestamp = get_post_meta( $post_id, 'game_unix_dtg', true );
	
	switch( $column ) {	
		case 'game_date' :
			// Build from unix timestamp
			if ( empty( $game_timestamp ) ) {
				_e( 'No Game Date', 'mstw-league-manager' );
			}
			else {
				// should check setting eventually
				$date_format = 'Y-m-d';
				echo( date( $date_format, intval( $game_timestamp ) ) );
			}
			break;
		
		case 'game_time' :
			// First, check for TBA, which overrides all
			$game_is_tba = get_post_meta( $post_id, 'game_is_tba', true );

			if ( $game_is_tba ) {
				// should check on setting eventually.
				//printf( '%s', 'TBA' );
				_e( 'TBA', 'mstw-league-manager' );
			}
			else { // Look for a custom format, if none, use the regular format
				$time_format = 'H:i';
				echo( date( $time_format, intval( $game_timestamp ) ) );
			}
			break;
			
		case 'game_league':
			$edit_link = site_url( '/wp-admin/', null ) . 'edit-tags.php?taxonomy=mstw_lm_league&post_type=mstw_lm_game';
			
			$leagues = get_the_terms( $post_id, 'mstw_lm_league' );
			
			if ( is_array( $leagues) ) {
				foreach( $leagues as $key => $league ) {
					$leagues[$key] =  '<a href="' . $edit_link . '">' . $league->name . '</a>';
				}
					echo implode( ' | ', $leagues );
			}
			break;

		case 'game_location' :
			//check game's location field; use it if not empty
			$game_location = get_post_meta( $post_id, 'game_location', true );
			
			//mstw_log_msg( "game location: $game_location" );
			
			if (  '' == $game_location or -1 == $game_location ) {
				_e( 'No location.', 'mstw-league-manager' );
			}
			else {
				$location_obj = get_page_by_path( $game_location, OBJECT, 'mstw_lm_venue' );
				if ( $location_obj ) {
				echo get_the_title( $location_obj->ID );
				}
				else {
					_e( 'No location.', 'mstw-league-manager' );
					mstw_log_msg( "game: $post_id location foobar: $game_location" );
				}
			}
			break;

		case 'game_home_team':
			// Get the post meta
			$game_home_team = get_post_meta( $post_id, 'game_home_team', true );
			
			// if there's a team DB entry, use it
			// else if there's an opponent entry, use it
			if ( !empty( $game_home_team ) and ( $game_home_team != -1 ) ) {
				$team = get_page_by_path( $game_home_team, OBJECT, 'mstw_lm_team' );
				printf( '%s', get_the_title( $team->ID ) );
			}
			else
				_e( 'No Home Team.', 'mstw-league-manager' );
			break;
		
		case 'game_away_team':
			// Get the post meta
			$game_away_team = get_post_meta( $post_id, 'game_away_team', true );
			
			// if there's a team DB entry, use it
			// else if there's an opponent entry, use it
			if ( !empty( $game_away_team ) and ( $game_away_team != -1 ) ) {
				$team = get_page_by_path( $game_away_team, OBJECT, 'mstw_lm_team' );
				printf( '%s', get_the_title( $team->ID ) );
			}
			else
				_e( 'No Home Team.', 'mstw-league-manager' );
			break;
			
		case 'game_season':
			$season_slug = get_post_meta( $post_id, 'game_season', true );
			
			$leagues = get_the_terms( $post_id, 'mstw_lm_league' );
			
			if ( is_array( $leagues ) ) {
				$league = reset( $leagues );
				//mstw_log_msg( "league: " );
				//mstw_log_msg( $league );
				$league_seasons = mstw_lm_get_league_seasons( $league -> slug );
				//mstw_log_msg( "seasons: " );
				//mstw_log_msg( $league_seasons );
				if ( array_key_exists( $season_slug, $league_seasons ) ) {
					echo $league_seasons[$season_slug];	
				}
				else {
					_e( "Not found.", 'mstw-league-manager' );
				}
				
				//foreach( $leagues as $key => $league ) {
				//	$leagues[$key] =  '<a href="' . $edit_link . '">' . $league->name . '</a>';
				//}
				//	echo implode( ' | ', $leagues );
			}
			
			//echo $season_slug;
			break;
		
		case 'game_nonleague':
			// First, check for TBA, which overrides all
			$game_nonleague = get_post_meta( $post_id, 'game_nonleague', true );

			if ( $game_nonleague ) {
				// should check on setting eventually.
				//printf( '%s', 'TBA' );
				_e( 'Yes', 'mstw-league-manager' );
			}
			break;
			
		case '1game_scoreboards':
			$sbs = get_the_terms( $post_id, 'mstw_lm_scoreboard' );
			
			$edit_link = site_url( '/wp-admin/', null ) . 'edit-tags.php?taxonomy=mstw_lm_scoreboard&post_type=mstw_lm_game';
			
			if ( is_array( $sbs ) && !is_wp_error( $sbs ) ) {
				$scoreboards = array( );
				foreach( $sbs as $key => $sb ) {
					
					$sbs[$key] = '<a href="' . $edit_link . '">' . $sb->name . '</a>';
				}
				echo implode( ', ', $sbs );
			}
			else {
				echo '<a href="' . $edit_link . '">' . __( 'None', 'mstw-league-manager' ) . '</a>';
			}
			break;
	
		default :
			/* Just break out of the switch statement for everything else. */
			break;
	}
} //End: mstw_lm_manage_game_columns( ) 

// ----------------------------------------------------------------
// Add a filter to sort all games table on the schedule id & game date columns
//
add_filter("manage_edit-mstw_lm_game_sortable_columns", 'mstw_lm_games_columns_sort');

function mstw_lm_games_columns_sort( $columns ) {
	$custom = array(
		'game_sched_id' => 'game_sched_id',
		'game_date' 	=> 'game_date'
	);
	return wp_parse_args( $custom, $columns );
}

//-----------------------------------------------------------------
// Sort show all games by schedule by columns. See:
// http://scribu.net/wordpress/custom-sortable-columns.html#comment-4732
//
add_filter( 'request', 'mstw_lm_games_column_order' );

function mstw_lm_games_column_order( $vars ) {
	if ( isset( $vars['orderby'] ) && 'game_sched_id' == $vars['orderby'] ) {
		$custom = array( 'meta_key' => 'game_sched_id',
							 //'orderby' => 'meta_value_num', // does not work
							 'orderby' => 'meta_value'
							 //'order' => 'asc' // don't use this; blocks toggle UI
							);
		$vars = array_merge( $vars, $custom );
	}
	else if ( isset( $vars['orderby'] ) && 'game_date' == $vars['orderby'] ) {
		$custom = array( 'meta_key' => 'game_unix_dtg',
							 //'orderby' => 'meta_value_num', // does not work
							 'orderby' => 'meta_value'
							 //'order' => 'asc' // don't use this; blocks toggle UI
							);
		$vars = array_merge( $vars, $custom );
	}
	
	return $vars;
	
} //End mstw_lm_games_column_order( )

// ----------------------------------------------------------------
// Add a filter to the all games screen based on the league taxonomy
//
add_action('restrict_manage_posts','mstw_lm_restrict_games_by_league');

function mstw_lm_restrict_games_by_league( ) {
	//mstw_log_msg( 'mstw_lm_restrict_games_by_league:' );
	global $typenow;
	global $wp_query;
	
	//mstw_log_msg( 'mstw_lm_restrict_games_by_league: $_GET' );
	//mstw_log_msg( $_GET );
	
	if( $typenow == 'mstw_lm_game' ) {
		
		$league = ( array_key_exists( 'filter_by_league', $_GET ) ) ? $_GET['filter_by_league'] : '' ;
		
		//mstw_log_msg( "mstw_lm_restrict_games_by_league: _GET" );
		//mstw_log_msg( $_GET );
		
		$taxonomy_slugs = array( 'mstw_lm_league' );
		
		foreach ( $taxonomy_slugs as $tax_slug ) {
			//retrieve the taxonomy object for the tax_slug
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			
			$terms = get_terms( $tax_slug );
				
			//output the html for the drop down menu
			?>
			<select name="<?php echo $tax_slug ?>" id="<?php echo $tax_slug ?>" class="postform">
             <option value=""><?php _e( 'Show All Leagues', 'mstw-league-manager')?> </option>
			
			 <?php
			 //output each select option line
             foreach ($terms as $term) {
                //check against the last $_GET to show the current selection
				$get_tax_slug = ( array_key_exists( $tax_slug, $_GET ) ) ? $_GET[ $tax_slug ] : '';
                ?>
				<option value='<?php echo $term->slug ?>' <?php selected( $get_tax_slug, $term -> slug ) ?> > <?php echo $term->name ?></option>
			 <?php
             }
			 ?>
            </select>
		<?php
		}	
	}
} //End: mstw_lm_restrict_games_by_league( )

// ----------------------------------------------------------------
// Add a filter to the all games screen based on the team
//
add_action( 'restrict_manage_posts','mstw_lm_restrict_games_by_team' );

function mstw_lm_restrict_games_by_team( ) {
	//mstw_log_msg( 'in mstw_lm_restrict_games_by_team...' );
	global $wpdb;
	global $typenow;
	
	if( isset( $typenow ) && $typenow != "" && $typenow == "mstw_lm_game" ) {
		$meta_values = $wpdb->get_col("
			SELECT DISTINCT meta_value
			FROM ". $wpdb->postmeta ."
			WHERE meta_key = 'game_home_team' OR meta_key = 'game_away_team'
			ORDER BY meta_value
		");
		//mstw_log_msg( '$meta_values from query');
		//mstw_log_msg( $meta_values );
		?>
		<select name="filter_by_team" id="filter_by_team">
			<option value=""><?php _e( 'Show All Teams', 'mstw-league-manager' ) ?></option>
			
			<?php 
			foreach ( $meta_values as $meta_value ) { 
				if ( $meta_value != '' ) {
					$team_entry = $meta_value;
					
					$team_obj = get_page_by_path( $meta_value, OBJECT, 'mstw_lm_team' );
					if ( $team_obj ) {
						//$team_name = get_post_meta( $team_obj->ID, 'team_name', true );
						//$team_mascot = get_post_meta( $team_obj->ID, 'team_mascot', true );
						$team_title = get_the_title( $team_obj );
						$team_entry = ( empty( $team_title ) ) ? $team_entry : $team_title;
					}
				?>
					<option value="<?php echo esc_attr( $meta_value ) ?>" 
					<?php 
					if( isset( $_GET['filter_by_team'] ) && !empty( $_GET['filter_by_team'] ) ) 
						selected( $_GET['filter_by_team'], $meta_value ); 
					?>
					>
						<?php echo $team_entry ?>
					</option>
			<?php 
				} //End: if ( $meta_value != '' ) 
			} //End: foreach ( $meta_values as $meta_value )
			?>
		</select>
	<?php
	}
}  //End of mstw_lm_restrict_games_by_team( )


// ----------------------------------------------------------------
// Modify query on all games screen based on the team
//
add_filter('parse_query','mstw_lm_parse_query_for_team');

function mstw_lm_parse_query_for_team( $query ) {
	//mstw_log_msg( 'mstw_lm_parse_query_for_team:' );
    global $pagenow;
	
	if( is_admin( ) AND isset( $query->query['post_type'] ) AND $query->query['post_type'] == 'mstw_lm_game' ) {
		//grab a reference to the $wp_query's query_vars
		$qv = &$query->query_vars;
		
		//mstw_log_msg( $qv );
		//mstw_log_msg( '$_GET:' );
		//mstw_log_msg( $_GET );

		if( isset( $_GET['filter_by_team'] ) AND !empty( $_GET['filter_by_team'] ) ) {
			//mstw_log_msg( 'reset QV with ' . $_GET['filter_by_team'] );
			$qv['meta_query'][] = array(
									'field' => 'game_home_team',
									'value' => $_GET['filter_by_team'],
									'compare' => '=',
									'type' => 'CHAR'
									);
		    
			//mstw_log_msg( 'mstw_lm_parse_query_for_team: new QV=' );
			//mstw_log_msg( $qv );
		  
		}
	}
} //End: mstw_lm_parse_query_for_team( )

//-----------------------------------------------------------------
// Contextual help callback. Action set in mstw-lm-admin.php
// 
function mstw_lm_games_help( ) {
	//mstw_log_msg( "mstw_lm_games_help" );
	if ( array_key_exists( 'post_type', $_GET ) and 'mstw_lm_game' == $_GET['post_type'] ) {
		//mstw_log_msg( 'got the right post type, show the help' );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
				
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'games-overview',
						'callback'  => 'mstw_lm_add_games_overview' ),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	}
} //End: mstw_lm_games_help( )

function mstw_lm_add_games_overview( $screen, $tab ) {
	if( !array_key_exists( 'id', $tab ) ) {
		return;
	}
		
	switch ( $tab['id'] ) {
		case 'games-overview':
			?>
			<p><?php _e( 'Add, edit, and delete games on this screen.', 'mstw-league-manager' ) ?></p>
			<p><?php _e( 'Roll over a Game Title to edit or delete a game. NOTE that deleting a game moves the game to the Trash, but does not completely remove it from the database. Go to the trash and empty it to delete games permanently.', 'mstw-league-manager' ) ?></p>
			<p><?php _e( 'Use the Leagues and Teams filters to filter the list by league and/or team. Click the Title column header to sort the list by Title. Click again to reverse the sort.', 'mstw-league-manager' ) ?></p>
			 
			<p><a href="http://shoalsummitsolutions.com/lm-games/" target="_blank"><?php _e( 'See the Games man page for more details.', 'mstw-league-manager' ) ?></a></p>
			
			<?php				
			break;
		
		default:
			break;
	} //End: switch ( $tab['id'] )

} //End: mstw_lm_add_games_overview( )
