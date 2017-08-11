<?php
 /*----------------------------------------------------------------------------
 * mstw-lm-team-cpt-admin.php
 *	Handles the mstw_lm_team custom post type admin screen(s).
 *	It is loaded conditioned on is_admin() in mstw-ss-admin.php 
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
 
//-----------------------------------------------------------------
// Testing - catch changes to team's league so we can straighten out records
//
//add_action( 'set_object_terms', 'mstw_lm_update_team_league', 10, 4 );

function mstw_lm_update_team_league( $team_id, $leagues, $league_ids, $taxonomy ) {
	mstw_log_msg( "mstw_lm_update_team_league:" );
	mstw_log_msg( "Taxonomy: $taxonomy" );
	mstw_log_msg( "Team ID: $team_id" );
	mstw_log_msg( get_the_title( $team_id ) );
	mstw_log_msg( "Leagues:" );
	mstw_log_msg( $leagues );
	mstw_log_msg( "League IDs: " );
	mstw_log_msg( $league_ids );
} //End: mstw_lm_update_team_league

//-----------------------------------------------------------------
// Add the meta box for the mstw_lm_team custom post type
//
add_action( 'add_meta_boxes_mstw_lm_team', 'mstw_lm_team_metaboxes' );

function mstw_lm_team_metaboxes( ) {
	//mstw_log_msg( 'in mstw_lm_team_metaboxes ...' );
	
	add_meta_box( 'mstw-lm-team-data-help', 
				  __( 'Add/Edit Team Help', 'mstw-league-manager' ), 
				  'mstw_lm_team_data_help_metabox', 
				  'mstw_lm_team', 
				  'normal', 
				  'high', 
				  null );
				  
	add_meta_box( 'mstw-lm-team-info',  
				  __( 'Team Information', 'mstw-league-manager' ), 
				  'mstw_lm_team_info_metabox', 
				  'mstw_lm_team', 
				  'normal', 
				  'high', 
				  null 
				);	
		
	remove_meta_box( 'slugdiv', 'mstw_lm_team', 'normal' );
	
} //End: mstw_lm_team_metaboxes{}

//-----------------------------------------------------------------
// Build the HELP meta box for the Manage Teams screen
//
function mstw_lm_team_data_help_metabox( $post ) {
	?>
	<p class='mstw-lm-admin-instructions'><?php _e( 'See the', 'mstw-league-manager') ?> <a href="http://shoalsummitsolutions.com/lm-edit-team" target="_blank"><?php _e( 'Edit Team man page', 'mstw-league-manager' ) ?></a> <?php _e( 'for more information.', 'mstw-league-manager') ?></p>
	
	<ul class='mstw-lm-admin-instructions'>
	<li><?php _e( 'Teams can be added in bulk using the CSV Import screen. If you have built a set of teams in the MSTW Schedules & Scoreboards plugin, they can be exported from there via the MSTW CSV Exporter, and then imported into the League Manager teams table.', 'mstw-league-manager' ) ?></li>
	<li><?php _e( 'The team title above is used throughout the admin screens; particularly in pulldowns. Choose it wisely. (It does not appear in any front end display.)', 'mstw-league-manager' ) ?></li>
	<li><?php _e( 'IMPORTANT: Use the MSTW Leagues metabox on the right to set the league(s) for the team.', 'mstw-league-manager' ) ?></li>
	</ul>
	
	<?php
}

// ----------------------------------------------------------------
// Creates the form for entering a Team Info on the Admin page
// Callback for: add_meta_box( 'mstw-lm-team-info', ...

function mstw_lm_team_info_metabox( $team ) {
	//mstw_log_msg( 'in mstw_lm_team_info_metabox' );
	
	//used by mstw_lm_save_team_meta for security (referrer check)
	wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_team_nonce' );
	
	// Retrieve the metadata values if they exist
	
	$mstw_ss_link = get_post_meta( $team->ID, 'mstw_ss_link', true );
	$name = get_post_meta( $team->ID, 'team_name', true );
	$short_name = get_post_meta( $team->ID, 'team_short_name', true );
	$team_link = get_post_meta( $team->ID, 'team_link', true );
	$mascot = get_post_meta( $team->ID, 'team_mascot', true );
	$home_venue = get_post_meta( $team->ID, 'team_home_venue', true );
	$team_logo = get_post_meta( $team->ID, 'team_logo', true );
	$team_alt_logo = get_post_meta( $team->ID, 'team_alt_logo', true );
	?>	
	
	<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="team_name" ><?php _e( 'Team Name:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="256" size="32" name="team_name"
			value="<?php echo esc_attr( $name ); ?>"/>
			<br/><span class="description"><?php _e( 'Full Team Name. Eg, "San Francisco" or "California"', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="team_short_name" ><?php _e( 'Team Short Name:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="4" size="4" name="team_short_name"
			value="<?php echo esc_attr( $short_name ); ?>"/>
			<br/><span class="description"><?php _e( 'Eg, "SF or "CAL". Used in results fields. Must be 4 chars or less. (Preferably 3 or 2.)', 'mstw-league-manager' ) ?></span>
		</td>		
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_mascot" ><?php _e( 'Team Mascot:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="256" size="32" name="team_mascot"
			value="<?php echo esc_attr( $mascot ); ?>"/>
			<br/><span class="description"><?php _e( 'E.g., Golden Bears or 49ers. Display of team mascot is controlled in display settings.', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="team_link" ><?php _e( 'Team Link:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="256" size="32" name="team_link"
			value="<?php echo esc_url( $team_link ); ?>"/>
			<br/><span class="description"><?php _e( 'Link to team website. Eg, http://calbears.com or http:49ers.com', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_home_venue" ><?php _e( 'Home Venue:', 'mstw-league-manager' ); ?></label></th>
		<td><?php mstw_lm_build_venues_control( $home_venue, 'team_home_venue' ) ?>
			<br/><span class="description"><?php _e( 'Used for team home games if no other venue choice is found.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
	<th scope="row"><label for="team_logo" ><?php _e( 'Small Team Logo:', 'mstw-league-manager' ) ?></label></th>
	
	<?php
		$args = array( 'type' => 'media-uploader',
					   'id'   => 'team_logo',
					   'name' => 'team_logo',
					   'desc' => __( 'Enter the full path to any file, or click the button to access the media library. Recommended size 41x28px.', 'mstw-league-manager' ),
					   'curr_value' => $team_logo,
					   'btn_label' => __( 'Upload from Media Library', 'mstw-league-manager' ),
					   'img_width' => 41,
					   'size' => 32,
					   'maxlength' => 256
				);
		mstw_lm_build_admin_edit_field( $args );
		?>
	</tr>
	
	<tr valign="top">
	<th scope="row"><label for="team_alt_logo" ><?php _e( 'Team Logo:', 'mstw-league-manager' ) ?></label></th>
	
	<?php
		$args = array( 'type' => 'media-uploader',
					   'id'   => 'team_alt_logo',
					   'name' => 'team_alt_logo',
					   'desc' => __( 'Enter the full path to any file, or click the button to access the media library. Recommended size 125x125px.', 'mstw-league-manager' ),
					   'curr_value' => $team_alt_logo,
					   'btn_label' => __( 'Upload from Media Library', 'mstw-league-manager' ),
					   'img_width' => 80,
					   'size' => 32,
					   'maxlength' => 256
				);
		mstw_lm_build_admin_edit_field( $args );
		?>
	</tr>
	</table>
	
<?php        	
} //End: mstw_lm_team_info_metabox

// ----------------------------------------------------------------
// Creates the form for entering a Team Record data on the Admin page
// Callback for: add_meta_box( 'mstw-lm-team-record', ...

function mstw_lm_team_record_metabox( $team ) {
	//mstw_log_msg( 'in mstw_lm_team_info_metabox' );
	
	//used by mstw_lm_save_team_meta for security (referrer check)
	//wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_team_nonce' );
	
	// Retrieve the metadata values if they exist
	$rank = get_post_meta( $team->ID, 'team_rank', true );
	$games_played = get_post_meta( $team->ID, 'team_games_played', true );
	$wins = get_post_meta( $team->ID, 'team_wins', true );
	$losses = get_post_meta( $team->ID, 'team_losses', true );
	$ties = get_post_meta( $team->ID, 'team_ties', true );
	$otw = get_post_meta( $team->ID, 'team_otw', true );
	$otl = get_post_meta( $team->ID, 'team_otl', true );
	$other = get_post_meta( $team->ID, 'team_other', true );
	$percent = get_post_meta( $team->ID, 'team_percent', true );
	$points = get_post_meta( $team->ID, 'team_points', true );
	$games_behind = get_post_meta( $team->ID, 'team_games_behind', true );
	?>

	<p class='mstw-lm-admin-instructions'><?php _e( 'Basic team record information. Several fields can be calculated automatically, based on plugin settings. Entries in these fields will override the default calculations.', 'mstw-league-manager' ) ?></p>	
	
	<table class="form-table mstw-lm-admin-table">
	<tr valign="top">
		<th scope="row"><label for="team_rank" ><?php _e( 'Rank (or Place):', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_rank"
			value="<?php echo esc_attr( $rank ); ?>"/>
			<br/><span class="description"><?php _e( 'This field can be used to order league standings.', 'mstw-league-manager' ) ?></span>
		</td>
			
		<th scope="row" class="mstw-admin-align-right"><label for="team_games_played" ><?php _e( 'Games Played:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_games_played"
			value="<?php echo esc_attr( $games_played ); ?>"/></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_wins" ><?php _e( 'Wins:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_wins"
			value="<?php echo esc_attr( $wins ); ?>"/></td>
	
		<th scope="row" class="mstw-admin-align-right"><label for="team_losses" ><?php _e( 'Losses:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_losses"
			value="<?php echo esc_attr( $losses ); ?>"/>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_ties" ><?php _e( 'Ties:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_ties"
			value="<?php echo esc_attr( $ties ); ?>"/></td>
	
		<th scope="row" class="mstw-admin-align-right"><label for="team_otw" ><?php _e( 'OT Wins:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_otw" id="team_otw"
			value="<?php echo esc_attr( $otw ); ?>"/>
			<br/><span class="description"><?php _e( 'This field is used for "Overtime Wins" (e.g., in the NHL).', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_otl" ><?php _e( 'OT Loses:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_otl" id="team_otl"
			value="<?php echo esc_attr( $otl ); ?>"/>
			<br/><span class="description"><?php _e( 'This field is used for "Overtime Loses" (e.g., in the NHL).', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="team_games_behind" ><?php _e( 'Games Behind:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_games_behind" id="team_games_behind"
			value="<?php echo esc_attr( $games_behind ); ?>"/>
			<br/><span class="description"><?php _e( 'Enter Games Behind if you want to display it. (It may be calculated in a future release.)', 'mstw-league-manager' ); ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_percent" ><?php _e( 'Win Percentage:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="4" name="team_percent" id="team_percent" readonly
			value="<?php echo number_format( floatval( $percent ), 3 ) ?>"/>
			<br/><span class="description"><?php _e( 'Normally left blank. If a value is entered here, it will be used instead of the automatic calculation.', 'mstw-league-manager' ) ?></span>
		</td>
	
		<th scope="row" class="mstw-admin-align-right"><label for="team_points" ><?php _e( 'Points:', 'mstw-league-manager' ); ?> </label></th>
		<td><input maxlength="32" size="4" name="team_points" id="team_points" readonly
			value="<?php echo esc_attr( $points ); ?>"/>
			<br/><span class="description"><?php _e( 'Normally left blank. If a value is entered here, it will be used instead of the automatic league calculation defined on the settings page.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	
	</table>
	
<?php        	
} //End: mstw_lm_team_record_metabox

// ----------------------------------------------------------------
// Creates the form for entering a Team Stats data on the Admin page
// Callback for: add_meta_box( 'mstw-lm-team-stats', ...

function mstw_lm_team_stats_metabox( $team ) {
	//mstw_log_msg( 'in mstw_lm_team_info_metabox' );
	
	//used by mstw_lm_save_team_meta for security (referrer check)
	//wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_team_nonce' );
	
	// Retrieve the metadata values if they exist
	$goals_for = get_post_meta( $team->ID, 'team_goals_for', true );
	$goals_against = get_post_meta( $team->ID, 'team_goals_against', true );	
	$last_10 = get_post_meta( $team->ID, 'team_last_10', true );
	$last_5 = get_post_meta( $team->ID, 'team_last_5', true );
	$streak = get_post_meta( $team->ID, 'team_streak', true );
	$home = get_post_meta( $team->ID, 'team_home', true );
	$away = get_post_meta( $team->ID, 'team_away', true );
	$division = get_post_meta( $team->ID, 'team_division', true );
	$conference = get_post_meta( $team->ID, 'team_conference', true );
	?>	
	
	<p class='mstw-lm-admin-instructions'><?php _e( 'Extra team record information. These fields may not be applicable to all leagues.', 'mstw-league-manager' ) ?></p>	
	
	<table class="form-table mstw-lm-admin-table">
	<tr valign="top">
		<th scope="row"><label for="team_goals_for" ><?php _e( 'Goals For:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="20" name="team_goals_for"
			value="<?php echo esc_attr( $goals_for ); ?>"/></td>

		<th scope="row" class="mstw-admin-align-right"><label for="team_goals_against" ><?php _e( 'Goals Against:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="20" name="team_goals_against"
			value="<?php echo esc_attr( $goals_against ); ?>"/>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_streak" ><?php _e( 'Streak:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_streak"
			value="<?php echo esc_attr( $streak ); ?>"/>
			<br/><span class="description"><?php _e( 'Current win or loss streak. E.g., W10 or L1.', 'mstw-league-manager' ) ?></span>
		</td>
	
		<th scope="row" class="mstw-admin-align-right"><label for="team_last_5" ><?php _e( 'Last 5:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_last_5"
			value="<?php echo esc_attr( $last_5 ); ?>"/>
			<br/><span class="description"><?php _e( 'Record in last 5 games. E.g., 4-1.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row" ><label for="team_last_10" ><?php _e( 'Last 10:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_last_10"
			value="<?php echo esc_attr( $last_10 ); ?>"/>
			<br/><span class="description"><?php _e( 'Record in last 10 games. E.g., 5-4-1.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_home" ><?php _e( 'Home:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_home"
			value="<?php echo esc_attr( $home ); ?>"/>
			<br/><span class="description"><?php _e( 'Record at home. E.g., 15-7-3.', 'mstw-league-manager' ) ?></span>
		</td>
	
		<th scope="row" class="mstw-admin-align-right"><label for="team_away" ><?php _e( 'Away:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_away"
			value="<?php echo esc_attr( $away ); ?>"/>
			<br/><span class="description"><?php _e( 'Record away from home. E.g., 10-12-5.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="team_conference" ><?php _e( 'Conference:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_conference"
			value="<?php echo esc_attr( $conference ); ?>"/>
			<br/><span class="description"><?php _e( 'Record within team\'s conference. Normally Leagues have Conferences, which have Divisions.', 'mstw-league-manager' ) ?></span>
		</td>
		<th scope="row" class="mstw-admin-align-right"><label for="team_division" ><?php _e( 'Division:', 'mstw-league-manager' ); ?></label></th>
		<td><input maxlength="32" size="10" name="team_division"
			value="<?php echo esc_attr( $division ); ?>"/>
			<br/><span class="description"><?php _e( 'Record within team\'s division. Normally Leagues have Conferences, which have Divisions.', 'mstw-league-manager' ) ?></span>
		</td>
	</tr>
	
	</table>
	
<?php        	
} //End: mstw_lm_team_stats_metabox

//-----------------------------------------------------------------
// SAVE THE MSTW_LM_TEAM CPT META DATA
//
add_action( 'save_post_mstw_lm_team', 'mstw_lm_save_team_meta', 20, 2 );

function mstw_lm_save_team_meta( $post_id, $post ) {
	//mstw_log_msg( 'in mstw_lm_save_team_meta ...' );
	
	// check if this is an auto save routine. 
	// If it is our form has not been submitted, so don't do anything
	//
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || $post->post_status == 'auto-draft' || $post->post_status == 'trash' ) {
		//mstw_log_msg( 'doing autosave ... nevermind!' );
		return; 
	}
	
	// Check that we are in the right context ... saving from edit team page
	// If NONCE is valid, process user input
	//
	if( isset($_POST['mstw_lm_team_nonce'] ) && 
		check_admin_referer( plugins_url(__FILE__), 'mstw_lm_team_nonce' ) ) {
		
		//
		// Do the fields that require only 'normal' validation
		//
		$data_fields = array( 'team_name',
							  'team_short_name',
							  'team_mascot', 
							  'team_link',
							  'team_home_venue',
							  'team_logo',
							  'team_alt_logo',
							);
							
		foreach( $data_fields as $field ) {
			$value = array_key_exists( $field, $_POST ) ? $_POST[ $field ] : '';
			update_post_meta( $post_id, $field, sanitize_text_field( esc_attr( $value ) ) );
		}
							
	}	
	else {
		mstw_log_msg( 'Oops! In mstw_lm_save_team_meta() team nonce not valid.' );
		
		if ( strpos( wp_get_referer( ), 'trash' ) === FALSE ) {
			mstw_log_msg( 'Oops! In mstw_lm_save_team_meta() team nonce not valid.' );
			//mstw_ss_add_admin_notice( 'error', __( 'Invalid referer. Contact system admin.', 'mstw-league-manager') );
		}		
	} //End: if( isset($_POST['mstw_lm_team_nonce'] ) && 
	
} //End: mstw_lm_save_team_meta()

// ----------------------------------------------------------------
// Set up the View All Teams table
//
add_filter( 'manage_edit-mstw_lm_team_columns', 'mstw_lm_edit_team_columns' ) ;

function mstw_lm_edit_team_columns( $columns ) {	
	//mstw_log_msg( 'in mstw_lm_save_team_meta ...' );
	//$options = get_option( 'mstw_lm_options' );

	$columns = array(
		'cb'                => '<input type="checkbox" />',
		'title'             => __( 'Title (not displayed)', 'mstw-league-manager' ),
		'team_name' 	    => __( 'Name', 'mstw-league-manager' ),
		'team_short_name' 	=> __( 'Short Name', 'mstw-league-manager' ),
		'team_mascot' 	    => __( 'Mascot', 'mstw-league-manager' ),
		'team_logo'         => __( 'Logo', 'mstw-league-manager' ),
		'team_home_venue'   => __( 'Home Venue', 'mstw-league-manager' ),
		//'team_link' 		=> __( 'Team Link', 'mstw-league-manager' ),
		'team_league'		=> __( 'League(s)', 'mstw-league-manager' ),
		);

	return $columns;
} //End: mstw_lm_edit_team_columns()

//-----------------------------------------------------------------
// Display the View All Teams table columns
// 
add_action( 'manage_mstw_lm_team_posts_custom_column',
			'mstw_lm_manage_team_columns', 10, 2 );

function mstw_lm_manage_team_columns( $column, $post_id ) {
	//mstw_log_msg( 'in mstw_lm_manage_team_columns ..' );
	
	global $post;
	
	switch( $column ) {	
		case 'team_name':
			$name = get_post_meta( $post_id, 'team_name', true );
			if( $name != '' )
				echo ( $name );
			else
				_e( 'No name.', 'mstw-league-manager' ); 
			break;

		case 'team_short_name':
			$name = get_post_meta( $post_id, 'team_short_name', true );
			if( $name != '' )
				echo ( $name );
			else
				_e( 'No short name.', 'mstw-league-manager' ); 
			break;
			
		case 'team_mascot':
			$mascot = get_post_meta( $post_id, 'team_mascot', true );
			if( $mascot != '' )
				echo ( $mascot );
			else
				_e( 'No Mascot', 'mstw-league-manager' ); 
			break;
			
		case 'team_logo':
			$logo_url = get_post_meta( $post_id, 'team_logo', true );
			if( '' != $logo_url )
				//echo( substr( strrchr( rtrim( $link, '/' ), '/' ), 1 ) );
				echo "<img width='41' src='$logo_url' />";
			else
				_e( 'Not set.', 'mstw-league-manager' ); 
			break;	
			
		case 'team_link':
			$link = get_post_meta( $post_id, 'team_link', true );
			if( $link != '' )
				//echo( substr( strrchr( rtrim( $link, '/' ), '/' ), 1 ) );
				echo $link;
			else
				_e( 'Not set.', 'mstw-league-manager' ); 
			break;
			
		case 'team_home_venue':
			//check game's location field; use it if not empty
			$team_home_venue = get_post_meta( $post_id, 'team_home_venue', true );
			
			if (  '' == $team_home_venue or -1 == $team_home_venue ) {
				_e( 'No home venue.', 'mstw-league-manager' );
			}
			else {
				//mstw_log_msg( '$team_home_venue = ' . $team_home_venue );
				$venue_obj = get_page_by_path( $team_home_venue, OBJECT, 'mstw_lm_venue' );
				
				echo get_the_title( $venue_obj->ID );
			}
			break;
			
		case 'team_league':
			$leagues = wp_get_post_terms( $post_id, 'mstw_lm_league', array("fields" => "names") );
			//print_r($term_list);
			
			if ( is_array( $leagues) ) {
				natsort( $leagues );
				
				$league_links = array( );
					
				$edit_link = site_url( '/wp-admin/', null ) . 'edit-tags.php?taxonomy=mstw_lm_league&post_type=mstw_lm_team';
				
				foreach( $leagues as $league ) {
					$league_links[] =  '<a href="' . $edit_link . '">' . $league . '</a>';
				}
				
				echo implode( ' | ', $league_links );
			}
			break;
			
		default :
			/* Just break out of the switch statement for everything else. */
			break;
	}
} //End: mstw_lm_manage_team_columns( )

// ----------------------------------------------------------------
// Add a filter to the all teams screen based on the league taxonomy
//
add_action('restrict_manage_posts','mstw_lm_restrict_teams_by_league');

function mstw_lm_restrict_teams_by_league( ) {
	global $typenow;
	//global $wp_query;
	
	if( $typenow == 'mstw_lm_team' ) {
		
		$taxonomy_slugs = array( 'mstw_lm_league' );
		
		foreach ( $taxonomy_slugs as $tax_slug ) {
			//retrieve the taxonomy object for the tax_slug
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			
			$terms = get_terms( $tax_slug );
				
			//output the html for the drop down menu
			echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
            echo "<option value=''>". __( 'Show All Leagues', 'mstw-league-manager') . "</option>";
			
			//output each select option line
            foreach ($terms as $term) {
                //check against the last $_GET to show the current selection
				if ( array_key_exists( $tax_slug, $_GET ) ) {
					$selected = ( $_GET[$tax_slug] == $term->slug )? ' selected="selected"' : '';
				}
				else {
					$selected = '';
				}
                echo '<option value=' . $term->slug . $selected . '>' . $term->name . '</option>'; // (' . $term->count . ')</option>';
            }
            echo "</select>"; 
		}	
	}
} //End: mstw_lm_restrict_teams_by_league( )

// ----------------------------------------------------------------
// Add a filter to the all teams screen based on the league taxonomy
//

// This action only fired when post is delted from trash
// add_action('before_delete_post','mstw_lm_delete_team_cleanup');

//add_action('wp_trash_post','mstw_lm_delete_team_cleanup');

function mstw_lm_delete_team_cleanup( $post_id ) {
	
	//mstw_log_msg( "mstw_lm_delete_team_cleanup ... team ID= $post_id" );
	
	//mstw_log_msg( "post type= ". get_post_type( $post_id ) );
	
} //End: mstw_lm_delete_team_cleanup()


//-----------------------------------------------------------------
// Contextual help callback. Action set in mstw-lm-admin.php
// 
function mstw_lm_teams_help( ) {
	//mstw_log_msg( "mstw_lm_teams_help" );
	if ( array_key_exists( 'post_type', $_GET ) and 'mstw_lm_team' == $_GET['post_type'] ) {
		//mstw_log_msg( 'got the right post type, show the help' );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
				
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'teams-overview',
						'callback'  => 'mstw_lm_add_teams_overview' ),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	}
}

function mstw_lm_add_teams_overview( $screen, $tab ) {
	if( !array_key_exists( 'id', $tab ) ) {
		return;
	}
		
	switch ( $tab['id'] ) {
		case 'teams-overview':
			?>
			<p><?php _e( 'Add, edit, and delete teams on this screen.', 'mstw-league-manager' ) ?></p>
			<p><?php _e( 'Roll over a team title to edit or delete a team. NOTE that deleting a team moves the team to the Trash, but does not completely remove it from the database. Go to the trash and empty it to delete teams permanently.', 'mstw-league-manager' ) ?></p>
			<p><?php _e( 'Use the Leagues filter to filter the list by league. Click the Title column header to sort the list by Title. Click again to reverse the sort.', 'mstw-league-manager' ) ?></p>
			<p><?php _e( 'Teams (and logo files) may be imported in bulk via the CSV Import screen.', 'mstw-league-manager' ) ?></p>
			 
			<p><a href="http://shoalsummitsolutions.com/lm-teams/" target="_blank"><?php _e( 'See the Teams man page for more details.', 'mstw-league-manager' ) ?></a></p>
			
			<?php				
			break;
		
		default:
			break;
	} //End: switch ( $tab['id'] )

} //End: add_help_tab()