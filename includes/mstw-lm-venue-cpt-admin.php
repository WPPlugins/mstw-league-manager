<?php
/*----------------------------------------------------------------------------
 * mstw-lm-venue-cpt-admin.php
 *	This portion of the MSTW League Manager Plugin admin handles the
 *		mstw_lm_venue custom post type.
 *	It is loaded conditioned on is_admin() in mstw-lm-admin.php 
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
 
// ----------------------------------------------------------------
// Add the meta box for the venues custom post type
//
add_action( 'add_meta_boxes_mstw_lm_venue', 'mstw_lm_venue_metaboxes' );

function mstw_lm_venue_metaboxes( ) {
	//mstw_log_msg( 'in mstw_lm_venue_metaboxes ...');	
	add_meta_box('mstw-lm-venue-meta',  
				 __( 'Venue Data', 'mstw-league-manager' ),
				 'mstw_lm_create_venues_ui', 
				 'mstw_lm_venue', 
				 'normal', 
				 'high', 
				 null );
				 
	remove_meta_box( 'slugdiv', 'mstw_lm_venue', 'normal' );
	
	//
	// If mstw_schedule_builder is active, add metabox with controls for venue schedule
	//
	
	// THIS HAS TO BE mstw-schedule-builder/mstw-schedule-builder.php
	if ( is_plugin_active( 'mstw-schedule-builder/mstw-schedule-builder.php' ) ) {
		add_meta_box('mstw-lm-venue-schedule',  
				 __( 'Venue Schedule', 'mstw-league-manager' ),
				 'mstw_lm_create_venue_schedules_ui', 
				 'mstw_lm_venue', 
				 'normal', 
				 'high', 
				 null );
	}			 
	
} //End: mstw_lm_venue_metaboxes()

//-----------------------------------------------------------------
// Build the meta box (controls) for the venues custom post type
//
function mstw_lm_create_venues_ui( $post ) {
	//mstw_log_msg( 'in mstw_lm_create_venues_ui ...');	
	
	wp_nonce_field( plugins_url(__FILE__), 'mstw_lm_venue_nonce' );

	// Retrieve the metadata values if they exist
	$venue_street = get_post_meta($post->ID, 'venue_street', true );
	$venue_city   = get_post_meta($post->ID, 'venue_city', true );
	$venue_state  = get_post_meta($post->ID, 'venue_state', true );
	$venue_zip    = get_post_meta($post->ID, 'venue_zip', true ); 
	$venue_url    = get_post_meta($post->ID, 'venue_url', true );						
	?>
	
	<table class='form-table'>	
		<?php //mstw_build_admin_edit_screen( $admin_fields ); ?>
	
	<p class='mstw-lm-admin-instructions'><?php _e( 'THE VENUE TITLE (ABOVE) MUST BE PRESENT. It appears on the front end as the venue name. No other information is required, unless you want the venue name to link to a map or the venue website. See the Settings screen for the available options.', 'mstw-league-manager' ) ?></p>
	
	<table class="form-table mstw-lm-admin-table">
		<!-- Row 1: Street address, City -->
		<tr valign="top">
			<th scope="row"><label for="venue_street" ><?php _e( 'Street Address:', 'mstw-league-manager' ); ?></label></th>
			<td><input type='text' maxlength="128" size="32" name="venue_street" id='venue_street' value="<?php echo $venue_street ?>" />
				<br/><span class="description"><?php _e( 'Used to create a (Google) map link, if set. Not displayed.', 'mstw-league-manager' ) ?></span>
			</td>
			<th scope="row" class="mstw-admin-align-right"><label for="venue_city" ><?php _e( 'City:', 'mstw-league-manager' ) ?></label></th>
			<td><input type='text' maxlength="128" size="32" name="venue_city" id="venue_city" value="<?php echo $venue_city ?>" />
				<br/><span class="description"><?php _e( 'Used to create a (Google) map link, and possibly displayed in the link.', 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
		
		<!-- Row 2: State, Zip -->
		<tr valign="top">
			<th scope="row"><label for="venue_state" ><?php _e( 'State:', 'mstw-league-manager' ); ?></label></th>
			<td><input type='text' maxlength="32" size="32" name="venue_state" id="venue_state" value="<?php echo $venue_state ?>" />
				<br/><span class="description"><?php _e( 'Used to create a (Google) map link, and possibly displayed with the link. Use 2 letter abbreviation for US states. Can include country, e.g, "CA, US", or use only country, e.g, "UK". Check what works best with Google Maps.', 'mstw-league-manager' ) ?></span>
			</td>
			<th scope="row" class="mstw-admin-align-right"><label for="venue_zip" ><?php _e( 'Zip or Postal Code:', 'mstw-league-manager' ) ?></label></th>
			<td><input type='text' maxlength="128" size="32" name="venue_zip" id="venue_zip" value="<?php echo $venue_zip ?>" />
				<br/><span class="description"><?php _e( 'Used to create a (Google) map link. Not displayed. Check what works best with Google Maps.', 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
		
		<!-- Row 3: Venue URL -->
		<tr valign="top">
			<th scope="row"><label for="venue_url" ><?php _e( 'Venue Website:', 'mstw-league-manager' ); ?></label></th>
			<td colspan="3"><input type='text' maxlength="256" size="64" name="venue_url" id="venue_url" value="<?php echo $venue_url ?>" />
				<br/><span class="description"><?php _e( 'Enter the URL for the venue website. Can be linked from venue text. See Settings screen.', 'mstw-league-manager' ) ?></span>
			</td>	
		</tr>
   
		<?php //mstw_build_admin_edit_screen( $admin_fields ); ?>
	
	</table>
	</table>
	
<?php 
}

//-----------------------------------------------------------------
// Build the meta box (controls) for the venue scheduling
//	ONLY IF MSTW LEAGUE SCHEDULER IS INSTALLED
//
function mstw_lm_create_venue_schedules_ui( ) {
	//mstw_log_msg( 'mstw_lm_create_venue_schedules_ui:' );
	
}

//-----------------------------------------------------------------
// SAVE & VALIDATE THE MSTW_lm_VENUE CPT META DATA
//
add_action( 'save_post_mstw_lm_venue', 'mstw_lm_save_venue_meta', 20, 2 );
//add_action( 'save_post_mstw_lm_venue', 'mstw_lm_validate_venue_meta', 10, 2 ); 

function mstw_lm_save_venue_meta( $post_id, $post ) {
	//mstw_log_msg( 'in mstw_lm_save_venue_meta ...' );
	
	// Check if this is an auto save call 
	// If so, the form has not been submitted, so don't do anything
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' || $post->post_status == 'trash' ) {
		//mstw_log_msg( 'In mstw_lm_save_venue_meta ... doing autosave ... nevermind!' );
		return; //$post_id;
	} 	
	
	if( isset( $_POST['mstw_lm_venue_nonce'] ) && 
		check_admin_referer( plugins_url(__FILE__), 'mstw_lm_venue_nonce' ) ) {
		
		// Post admin message title is not specified
		if ( !isset( $_POST['post_title'] ) || empty( $_POST['post_title'] ) ) {
			mstw_add_admin_notice( 'mstw_lm_admin_messages',
								   'error', 
								   __( 'A TITLE really is needed. Please enter one.', 'mstw-league-manager' ) 
								 );
			//return;
		}
		
		update_post_meta($post_id, 'venue_street', 
			sanitize_text_field( esc_attr( $_POST['venue_street'] ) ) );
			
		update_post_meta($post_id, 'venue_city',
			sanitize_text_field( esc_attr( $_POST['venue_city'] ) ) );

		update_post_meta($post_id, 'venue_state', 
			sanitize_text_field( esc_attr( $_POST['venue_state'] ) ) );
			
		update_post_meta($post_id, 'venue_zip',
			sanitize_text_field( esc_attr( $_POST['venue_zip'] ) ) );
			
		mstw_validate_url( $_POST, 'venue_map_url', $post_id, 'error', 
							  __( 'Invalid map URL:', 'mstw-league-manager' ) );
		
		mstw_validate_url( $_POST, 'venue_url', $post_id, 'error', 
							  __( 'Invalid venue URL:', 'mstw-league-manager' ) );

	} //End: verify nonce/context (valid nonce)
	else {
		if ( strpos( wp_get_referer( ), 'trash' ) === FALSE ) {
			mstw_log_msg( 'Oops! In mstw_lm_save_venue_meta() venue nonce not valid.' );
			mstw_add_admin_notice( 'mstw_lm_admin_messages',
								   'error', 
								   __( 'Invalid referer. Contact system admin.', 'mstw-league-manager') 
								 );
		}
	}
	
} //End: mstw_lm_save_venue_meta( )

// ----------------------------------------------------------------
// Remove Get Shortlink button for the mstw_gs_schedule CPT
//
//add_filter( 'pre_get_shortlink', 'mstw_lm_venue_remove_shortlink', 10, 2 );
	
function mstw_lm_venue_remove_shortlink( $false, $post_id ) {
	return 'mstw_lm_venue' === get_post_type( $post_id ) ? '' : $false;
}

// ----------------------------------------------------------------
// Set up the View All Venues table
//
add_filter( 'manage_edit-mstw_lm_venue_columns', 
			'mstw_lm_edit_venues_columns' ) ;

function mstw_lm_edit_venues_columns( $columns ) {	
	
	//$options = get_option( 'mstw_gs_options' );

	$columns = array(
		'cb' 			=> '<input type="checkbox" />',
		'title' 		=> __( 'Venue', 'mstw-league-manager' ),
		//'street' 		=> __( 'Street', 'mstw-league-manager' ),
		//'city' 			=> __( 'City', 'mstw-league-manager' ),
		//'state' 		=> __( 'State', 'mstw-league-manager' ),
		//'zip' 			=> __( 'Zip/Postal Code', 'mstw-league-manager' ),
		'address'       =>  __( 'Address', 'mstw-league-manager' ),
		'venue_url' 	=>  __( 'Venue URL', 'mstw-league-manager' ),
		'venue_groups'  =>  __( 'Groups', 'mstw-league-manager' ),
		);

	return $columns;
	
} //End: mstw_lm_edit_venues_columns()

// ----------------------------------------------------------------
// Display the Venues 'view all' columns
// 
add_action( 'manage_mstw_lm_venue_posts_custom_column',
			'mstw_lm_manage_venues_columns', 10, 2 );

function mstw_lm_manage_venues_columns( $column, $post_id ) {
	
	switch( $column ) {	
	
		/* If displaying address column, combine street, city, state, zip */
		case 'address':
			$venue_street = get_post_meta( $post_id, 'venue_street', true );
			$venue_city = get_post_meta( $post_id, 'venue_city', true );
			$venue_state = get_post_meta( $post_id, 'venue_state', true );
			$venue_zip = get_post_meta( $post_id, 'venue_zip', true );
			
			if ( '' == $venue_street && '' == $venue_city && 
			     '' == $venue_state && '' == $venue_zip ) {
				$html = __( 'No address info', 'mstw-league-manager' );
				_e( 'No address info', 'mstw-league-manager' );
					 
			 } else {
				 printf( '%s, %s, %s  %s', $venue_street, $venue_city, $venue_state, $venue_zip );
				 
			 }
			
			break;
			
		/* If displaying the 'street' column. */
		case 'street' :
			$venue_street = get_post_meta( $post_id, 'venue_street', true );
			if ( empty( $venue_street ) )
				echo __( 'No Street Address', 'mstw-league-manager' );
			else
				printf( '%s', $venue_street );
			break;

		/* If displaying the 'city' column. */
		case 'city' :
			$venue_city = get_post_meta( $post_id, 'venue_city', true );
			if ( empty( $venue_city ) )
				echo __( 'No City', 'mstw-league-manager' );
			else
				printf( '%s', $venue_city );
			break;
			
		/* If displaying the 'state' column. */
		case 'state' :
			$venue_state = get_post_meta( $post_id, 'venue_state', true );
		if ( empty( $venue_state ) )
				echo __( 'No State', 'mstw-league-manager' );
			else
				printf( '%s', $venue_state );

			break;	
			
		/* If displaying the 'zip' column. */
		case 'zip' :
			$venue_zip = get_post_meta( $post_id, 'venue_zip', true );
			if ( empty( $venue_zip ) )
				echo __( 'No Zip', 'mstw-league-manager' );
			else
				printf( '%s', $venue_zip );
			break;	

		/* If displaying the 'venue url' column. */
		case 'venue_url' :
			/* Get the post meta. */
			$venue_url = get_post_meta( $post_id, 'venue_url', true );

			if ( empty( $venue_url ) )
				echo __( 'No Venue URL', 'mstw-league-manager' );
			else
				printf( '%s', $venue_url );
			break;
			
		// If displaying the groups column
		case 'venue_groups':
			$groups = get_the_terms( $post_id, 'mstw_lm_venue_group' );
			$edit_link = site_url( '/wp-admin/', null ) . 'edit-tags.php?taxonomy=mstw_lm_venue_group&post_type=mstw_lm_venue';
			if ( is_array( $groups ) && !is_wp_error( $groups ) ) {
				//mstw_log_msg( 'In mstw_lm_manage_venues_columns ...' );
				//mstw_log_msg( $groups );
				foreach( $groups as $key => $group ) {	
					$groups[$key] = '<a href="' . $edit_link . '">' . $group->name . '</a>';
				}
				echo implode( ', ', $groups );
			}
			else {
				echo '<a href="' . $edit_link . '">' . __( 'None', 'mstw-league-manager' ) . '</a>';
			}
			break;				
			
		// Just break out of the switch statement for everything else.
		default :
			break;
	}
	
} //End: mstw_lm_manage_schedules_columns( )

// ----------------------------------------------------------------
// Add a filter the All Venues screen based on the Venue Group
add_action('restrict_manage_posts','mstw_lm_restrict_venues_by_group');

function mstw_lm_restrict_venues_by_group( ) {
	global $typenow;
	global $wp_query;
	
	if( $typenow == 'mstw_lm_venue' ) {
		
		$taxonomy_slugs = array( 'mstw_lm_venue_group' );
		
		foreach ( $taxonomy_slugs as $tax_slug ) {
			//retrieve the taxonomy object for the tax_slug
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			
			$terms = get_terms( $tax_slug );
				
			//output the html for the drop down menu
			echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
            echo "<option value=''>". __( 'Show All Groups', 'mstw-league-manager') . "</option>";
			
			//output each select option line
            foreach ($terms as $term) {
                //check against the last $_GET to show the current selection
				if ( array_key_exists( $tax_slug, $_GET ) ) {
					$selected = ( $_GET[$tax_slug] == $term->slug )? ' selected="selected"' : '';
				}
				else {
					$selected = '';
				}
                echo '<option value=' . $term->slug . $selected . '>' . $term->name . ' (' . $term->count . ')</option>';
            }
            echo "</select>"; 
		}	
	}
} //End: mstw_lm_restrict_venues_by_group( )

//-----------------------------------------------------------------
// Set up sorting for the columns
// 
/*
add_filter( 'manage_edit-mstw_lm_schedule_sortable_columns', 'mstw_lm_schedule_sortable_columns' );

function mstw_lm_schedule_sortable_columns( $columns ) {
    //$columns['schedule_name'] = 'schedule_name';
	//$columns['schedule_id'] = 'schedule_id';
	$columns['schedule_team'] = 'schedule_team';
 
    //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
 
    return $columns;
}
*/

//-----------------------------------------------------------------
// Contextual help callback. Action set in mstw-lm-admin.php
// 
function mstw_lm_venues_help( ) {
	//mstw_log_msg( "mstw_lm_venue_help" );
	if ( array_key_exists( 'post_type', $_GET ) and 'mstw_lm_venue' == $_GET['post_type'] ) {
		//mstw_log_msg( 'got the right post type, show the help' );
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		//mstw_log_msg( "current screen:" );
		//mstw_log_msg( $screen );
		
		mstw_lm_help_sidebar( $screen );
				
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-league-manager' ),
						'id'       => 'venues-overview',
						'callback'  => 'mstw_lm_add_venues_overview' ),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	}
}

function mstw_lm_add_venues_overview( $screen, $tab ) {
	if( !array_key_exists( 'id', $tab ) ) {
		return;
	}
		
	switch ( $tab['id'] ) {
		case 'venues-overview':
			?>
			<p><?php _e( 'This screen provides management of game venues (or locations). Home venues for teams and neutral sites should be set here. Otherwise, locations for games can not be set.', 'mstw-league-manager' ) ?></p>
			<p><a href="http://shoalsummitsolutions.com/lm-venues/" target="_blank"><?php _e( 'See the Venues man page for more details.', 'mstw-league-manager' ) ?></a></p>
			
			<?php				
			break;
		
		default:
			break;
	} //End: switch ( $tab['id'] )

} //End: add_help_tab()
?>