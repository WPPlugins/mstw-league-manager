<?php
 /*---------------------------------------------------------------------------
 *	mstw-lm-venue-table.php
 *	Contains the code for the MSTW Schedules & Scoreboards venue table
 *		shortcode [mstw_venue_table]
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2014 Mark O'Donnell (mark@shoalsummitsolutions.com)
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

// --------------------------------------------------------------------------------------
// Add the shortcode handler, which will create the Venues table on the user side.
// Handles the shortcode parameters, if there were any, then calls 
// mstw_lm_build_venue_table( ) to create the output
//
// Used location instead of venue to avoid conflict with MSTW Schedules & Scoreboards
// 
add_shortcode( 'mstw_location_table', 'mstw_lm_venue_table_shortcode_handler' );

function mstw_lm_venue_table_shortcode_handler( $atts ){
	// get the options set in the admin display settings screen
	$options = get_option( 'mstw_lm_venue_options' );
	
	// merge those options with the default values
	$options = wp_parse_args( $options, mstw_lm_get_venue_defaults( ) );
	
	// then merge the args passed to the shortcode with the result
	$attribs = shortcode_atts( $options, $atts );
	
	return mstw_lm_build_venue_table( $attribs );
	
} //End: mstw_lm_venue_table_shortcode_handler()

//--------------------------------------------------------------------------------------
// MSTW_LM_BUILD_VENUE_TABLE
// 	Called by mstw_lm_venue_table_shortcode_handler( )
// 	Builds the Game Venues table as a string (to replace the [shortcode] in a page or post.)
// 	Loops through the Game Venue custom posts and formats them into a pretty table
//	as specified by the arguments.
// 	ARGUMENTS:
// 		$args - shortcode arguments, properly defaulted by 
//				mstw_lm_venue_table_shortcode_handler()
//	RETURNS
//		HTML for venue table display (as a string)
//
function mstw_lm_build_venue_table( $args ) {

	//mstw_log_msg( " In mstw_lm_venue_table ... " );
	//mstw_log_msg( $args );

	$output = ''; //This is the return string
	
	//Pull the $args[] array into individual variables
	extract( $args );
	
	//mstw_log_msg( '$venue_group: ' . $venue_group );
	
	$args = array( 'numberposts' => -1,
							  'post_type' => 'mstw_lm_venue',
							  'orderby' => 'title',
							  'order' => 'ASC',
							);
	
	if( !empty( $venue_group ) ) {
		$groups = explode( ',', $venue_group );
		
		//mstw_log_msg( $groups );
		
		$args['tax_query'] = array( array(  'taxonomy'=> 'mstw_lm_venue_group',
											'field' => 'slug',
											'terms' => $groups, 
											) );
		
		$posts = get_posts( array( 'numberposts' => -1,
								   'post_type' => 'mstw_lm_venue',
							       'orderby' => 'title',
								   'order' => 'ASC',
							       'tax_query' =>	array( 
													array(  'taxonomy'=> 'mstw_lm_venue_group',
															'field' => 'slug',
															'terms' => $groups, 
															) ),
								  ) );
	}
	else {
		$posts = get_posts( $args );	
	
	}
	
	
	// Get the game_location posts
	
							
	//mstw_log_msg( $posts );
	
	if( $posts ) {
		// Make table of posts
		// Start with some instructions at the top
		if ( $show_instructions ) {
			$output = '<p>' . $instructions . '</p>';
		}
		
		// Build the table's header; use first group for customization
		$group_css = '';
		if ( !empty( $groups ) ) {
			$group_css = 'mstw-venue-table-' . $groups[0];
		}
		$output .= '<table class="mstw-venue-table ' . $group_css . '">';
		$output .= '<thead class="mstw-venue-table-head"><tr>';
		
		// Always show the venue name (post title)
		$output .= '<th>' . $venue_label . '</th>';
		
		if ( $show_address ) {
			$output .= '<th>' . $address_label . '</th>';
		}
		
		if ( $show_map ) {
			$output .= '<th>' . $map_label . '</th>';
		}
		
		$output .= '</tr></thead>';
		
		// Loop through the posts and make the rows
		$even_and_odd = array('even', 'odd');
		$row_cnt = 1; // Keeps track of even and odd rows. Start with row 1 = odd.
		
		foreach( $posts as $post ){
			// set up some housekeeping to make styling in the loop easier
			$even_or_odd_row = $even_and_odd[$row_cnt]; 
			$row_class = 'mstw-venue-' . $even_or_odd_row;
			$row_tr = '<tr class="' . $row_class . '">';
			$row_td = '<td>'; 
			
			// create the row
			
			// column1: venue name (the CPT title) is always displayed
			// column1: location name to the map - 0 means don't build an image
			$venue_url = mstw_lm_build_venue_link( $show_venue_link, $post, 0 );
			
			$row_string = $row_tr . $row_td . $venue_url . '</td>';
			
			// column2: create the address in a pretty format
			if ( $show_address ) {
				$street = get_post_meta( $post->ID, 'venue_street', true );
				$street_string = ( $street != '' ? $street . '<br/>' : '' );
				$row_string .= $row_td . $street_string . 
					get_post_meta( $post->ID, 'venue_city', true ) . ', ' .
					get_post_meta( $post->ID, 'venue_state', true ) . '  ' . 
					get_post_meta( $post->ID, 'venue_zip', true ) . '</td>';
			}
			
			// column3: map image and link to map
			
			// look for a custom url, if none, build one
			if ( $show_map ) {
				$custom_url = trim( get_post_meta( $post->ID, 'venue_map_url', true) );
				
				if ( empty( $custom_url ) ) {  // build the url from the address fields
					$center_string = get_the_title( $post->ID );
					$venue_street = get_post_meta( $post->ID, 'venue_street', true );
					if( !empty( $venue_street ) ) {
						$center_string .= "," . $venue_street;
					}
					$venue_city = get_post_meta( $post->ID, 'venue_city', true );
					if( !empty( $venue_city ) ) {
						$center_string .= "," . $venue_city;
					}
					$venue_state = get_post_meta( $post->ID, 'venue_state', true );
					if( !empty( $venue_state ) ) {
						$center_string .= "," . $venue_state;
					}
					$venue_zip = get_post_meta( $post->ID, 'venue_zip', true );
					if( !empty( $venue_zip ) ) {
						$center_string .= "," . $venue_zip;
					}
						
					$href = '<a href="https://maps.google.com?q=' .  rawurlencode($center_string) . '" target="_blank" >';
					
					//mstw_log_msg( $center_string );
					
					$row_string .= $row_td . $href . '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . 
						$center_string . 
						'&markers=size:mid%7Ccolor:' . $marker_color . '%7C' . $center_string . 
						'&zoom=15&size=' . $map_icon_width . 'x' . $map_icon_height . '&maptype=roadmap&sensor=false" />' . '</a></td>';
				
				}
				else {  // use the custom map url
					$href = '<a href="' . $custom_url . '" target="_blank">';
					
					$row_string .= $row_td . $href . __( 'Custom Map', 'mstw-league-manager' ) . '</a></td>';
				}
			}
			
			$output .= $row_string;
			
			$row_cnt = 1- $row_cnt;  // Get the styles right
			
		} // end of foreach post
		$output .= '</table>';
	}
	else { // No posts were found
		$output = '<h3>' . __( 'No Game Venues Found.', 'mstw-league-manager' ) . '</h3>';
	}
	
	return $output;

} //End function mstw_lm_build_venue_table

//---------------------------------------------------------------
// Build the location link for the title & map
//
function mstw_lm_build_venue_link( $link_type, $post, $build_image ) { 
	//mstw_log_msg( "mstw_lm_build_venue_link:" );

	$venue = get_the_title( $post->ID ); 
	$venue_map_url = get_post_meta( $post->ID, 'venue_map_url', true );
	$venue_url = get_post_meta( $post->ID, 'venue_url', true );
	
	switch ( $link_type ) {
		case 1: 	//link to map
			if ( !empty( $venue_map_url ) ) {
				//use custom map url
				$ret_str = "<a href='" . $venue_map_url . "' target='_blank'>" . $venue . "</a>";
			} 
			else { //build google maps href from address
				$center_string = $venue . "," .
					get_post_meta( $post->ID, 'venue_street', true ) . ', ' .
					get_post_meta( $post->ID, 'venue_city', true ) . ', ' .
					get_post_meta( $post->ID, 'venue_state', true ) . ', ' . 
					get_post_meta( $post->ID, 'venue_zip', true );
					
				$href = '<a href="https://maps.google.com?q=' .$center_string . '" target="_blank" >'; 
				
				if ( $build_image ) {	
					if ( $map_icon_width == "" ) {
						$map_icon_width = 250;
					}
					if ( $map_icon_height == "" ) {
						$map_icon_height = 75;
					}
					if ( $marker_color == "" ) {
						$marker_color = 'blue';
					}
					
					$ret_str = $href . '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $center_string . 
						'&markers=size:mid%7Ccolor:' . $marker_color . '%7C' . $center_string . 
						'&zoom=15&size=' . $map_icon_width . 'x' . $map_icon_height . '&maptype=roadmap&sensor=false" />' . '</a>';
				} else {
					$ret_str = $href . $venue . '</a>';
				}
			}
			break;
		
		case 2:		//link to venue
			if ( !empty( $venue_url ) ) {
				$ret_str = "<a href='" . $venue_url . "' target='_blank'>" . $venue . "</a>";
			} else {
				$ret_str = $venue;
			}
		break;
		
		default: 	//no link
			$ret_str = $venue;
		break;
	}

	return $ret_str;
} //End: mstw_lm_build_venue_link( )
?>