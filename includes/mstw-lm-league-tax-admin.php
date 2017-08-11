<?php
/*---------------------------------------------------------------------------
 *	mstw-lm-league-tax-admin.php
 *		Adds data fields to the default taxonomy window
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2016 Mark O'Donnell (mark@shoalsummitsolutions.com)
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
 

 // ----------------------------------------------------------------
 // Remove the row actions
 //	
 add_filter( 'mstw_lm_league_row_actions', 'mstw_lm_league_row_actions' ); //, 10, 2 );

if( !function_exists( 'mstw_lm_league_row_actions' ) ) {
	function mstw_lm_league_row_actions( $actions ) { //, $post ) {
		//mstw_log_msg( 'in mstw_lm_league_row_actions( ) ... ' );
		
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
		//unset( $actions['delete'] );
		//unset( $actions['edit'] );
		
		return $actions;

	} //End: mstw_lm_league_row_actions( )
 }

 //----------------------------------------------------------------------
 // Add Sport & Season fields to league taxonomy add & edit screens
 // 
 add_action( 'mstw_lm_league_add_form_fields', 'mstw_lm_league_add_form', 10, 2 );
 add_action ( 'mstw_lm_league_edit_form_fields', 'mstw_lm_league_edit_form', 10, 2 );

 if( !function_exists( 'mstw_lm_league_add_form' ) ) {
	function mstw_lm_league_add_form( ) {
		//mstw_log_msg( 'in mstw_lm_league_add_form_fields( ) ... ' );
		?>
		
		<div class="form-field">
		 <label for="league-season"><?php _e( 'Add Season for League' , 'mstw-league-manager' ) ?></label>
		 <input type="text" name="league-season" id="league-season" value="<?php echo date("Y") ?>"/>
		 <p class="description"> <?php _e( 'Every league must have at least one season. The default is the current year.', 'mstw-league-manager' ) ?> <?php _e( 'Seasons can be changed in the Seasons admin screen.', 'mstw-league-manager' ) ?> </p>
		</div> <!--form-field> -->
		
		<div class="form-field">
		 <label for="test"><?php _e( 'Select Sport for League' , 'mstw-league-manager' ) ?></label>
		 <?php
		 $result = mstw_lm_build_sport_select( '', 'mstw-lm-league-sport' );
		 ?>
		 <p class="description"> <?php _e( 'The sport for a league drives the layout of the front end tables via the Settings screen.', 'mstw-league-manager' ) ?> </p>
		</div> <!--form-field> -->
		
		<script type="text/javascript">
		jQuery(document).ready( function($) {
			$('#tag-description').parent().remove();
			//$('.term-slug-wrap').remove( ); //innerHTML('Will default');
			$('.term-slug-wrap').find( 'p' ).text(' Unless you know what you are doing, let the Slug automatically default based on the Name field.');
			$('.term-parent-wrap').remove( );
		});
		</script>

	<?php
	} //End: mstw_lm_league_add_form( )
 }
 
 
 if( !function_exists( 'mstw_lm_league_edit_form' ) ) {
	function mstw_lm_league_edit_form( $league_obj ) {
		//mstw_log_msg( 'in mstw_lm_league_edit_form ... ' );
		//mstw_log_msg( '$league_obj: ' );
		//mstw_log_msg( $league_obj );
		
		/*
		mstw_log_msg( 'all options:' );
		$all_options = wp_load_alloptions();
		$option_names = array();
		foreach ( $all_options as $name => $value ) {
			if( false !== strpos( $name, 'lm' ) ) {
				$option_names[] = $name;
			}
		}
		natsort( $option_names );
		mstw_log_msg( $option_names );
		*/
			
		//find the league's sport
		$league_sport = mstw_lm_get_league_sport( $league_obj->slug );
		//mstw_log_msg( '$league_sport = ' . $league_sport );
		//$league_season = mstw_lm_get_league_season( $league_obj->slug )
		
		?>
		<tr class="form-field">
		 <th scope="row">
		  <label for="league-season"><?php _e( 'Season' , 'mstw-league-manager' ) ?></label>
		 </th>
		  <td><!-- Instructions -->
			<span class='mstw-lm-admin-instructions'>
			 <?php _e( 'Use the Seasons admin screen to manage league seasons.', 'mstw-league-manager' ) ?>
			</span>
		 </td>
		</tr> <!--form-field> -->
		
		<tr class="form-field">
		 <th scope="row">
			<label for="mstw-lm-league-sport"><?php _e( 'Select Sport for League' , 'mstw-league-manager' ) ?></label>
		 </th>
		 <td>
			<?php
			$result = mstw_lm_build_sport_select( $league_sport, 'mstw-lm-league-sport' );
			?>
			<br/>
			<span class="description"> <?php _e( 'The sport for a top level league drives the layout of the front end tables via the Settings screen.', 'mstw-league-manager' ) ?><br/></span>
		 </td>
		</tr> <!-- .form-field> -->
		
		<script type="text/javascript">
		jQuery(document).ready( function($) {
			$('#description').closest('.form-field').remove();
			$('.term-slug-wrap').find('p' ).text(' Unless you know what you are doing, let the Slug automatically default based on the Name field.');
		});
		</script>
		
	<?php	
	} //End: mstw_lm_league_edit_form( )
 }

//----------------------------------------------------------------------
// Define the MSTW Team taxonomy custom columns
//
add_filter( 'manage_edit-mstw_lm_league_columns', 'mstw_lm_manage_league_columns');

if ( !function_exists( 'mstw_lm_manage_league_columns' ) ) { 
	function mstw_lm_manage_league_columns( $columns ) {
	
	$new_columns = array(
        //'cb' 			  => '<input type="checkbox" />',
        'name' 			  => __( 'League Name', 'mstw-league-manager' ),
		'slug' 			  => __( 'Slug', 'mstw-league-manager' ),
		'league-teams'    => __( 'Teams', 'mstw-league-manager' ),
		'league-sport' 	  => __( 'Sport', 'mstw-league-manager' ),
		'league-season'   => __( 'Seasons', 'mstw-league-manager' ),
        //'description'   => __('Description', 'mstw-league-manager' ),
        );
	
	return $new_columns;
	
	} //End: mstw_lm_manage_league_columns()
}

 //-----------------------------------------------------------------
 // Fill the data in the MSTW League taxonomy custom columns
 //
 add_filter( 'manage_mstw_lm_league_custom_column', 'mstw_lm_fill_league_custom_columns', 10, 3 );

 if ( !function_exists( 'mstw_lm_fill_league_custom_columns' ) ) { 
	function mstw_lm_fill_league_custom_columns( $out, $column_name, $league_id ) {
		//mstw_log_msg( 'in mstw_lm_fill_league_custom_columns ... ');
		//mstw_log_msg( '$league_id= ' . $league_id );
		//mstw_log_msg( '$column_name= ' . $column_name );
		
		// load league metadata
		$league_obj  = get_term( $league_id, 'mstw_lm_league' );
		$league_slug = $league_obj->slug;
		
		if ( 'league-teams' == $column_name ) {
			$args = array( 'post_type'   => 'mstw_lm_team', 
						   'numberposts' => -1,
						   'taxonomy'    => 'mstw_lm_league',
						   'term'        => $league_slug, 
						 );
			$teams = get_posts( $args );
		
			echo count( $teams );
			
		} else if ( 'league-sport' == $column_name ) {
			echo mstw_lm_get_league_sport( $league_slug, $get_name = true );
			
			
		}
		else if( 'league-season' == $column_name) {
			$seasons = mstw_lm_build_seasons_list( $league_slug );
			$seasons_str = '';
			if ( array_key_exists( '-1', $seasons ) ) {
				unset( $seasons[-1] );
			}
			//mstw_log_msg( $seasons );
			
			echo implode( ' | ', $seasons );
			
			//return 'seasons';
		}
	
	} //End: mstw_lm_fill_league_custom_columns()
}

 //-----------------------------------------------------------------
 // Save the LEAGUE taxonomy meta data elements
 //
 add_action( 'edited_mstw_lm_league', 'mstw_lm_save_league_meta');
 // editted_{taxonomy} fires AFTER the taxonomy/term has been updated
 add_action( 'create_mstw_lm_league', 'mstw_lm_save_league_meta' );
 // create_{taxonomy} fires AFTER a new term is created

 if ( !function_exists( 'mstw_lm_save_league_meta' ) ) { 
	function mstw_lm_save_league_meta( $term_id ) {
		//mstw_log_msg( 'divider' );
		//mstw_log_msg( 'in ... mstw_lm_save_league_meta' );
		//mstw_log_msg( '$term_id= ' . $term_id );
		//mstw_log_msg( '$_POST' );
		//mstw_log_msg( $_POST );
		
		$term_obj = get_term( $term_id, 'mstw_lm_league', OBJECT, 'raw' );
		//mstw_log_msg( '$term:' );
		//mstw_log_msg( $term_obj );
		
		$league_slug = $term_obj->slug;
		//mstw_log_msg( "mstw_lm_save_league_meta: league_slug: $league_slug" );
		
		//
		// WHAT WILL WE HAVE TO DO FOR CSV IMPORT
		//
		/*
		if ( array_key_exists( 'slug', $_POST ) && '' != $_POST['slug'] ) {
			$league_slug = $_POST['slug'];
		}
		else {
			$league_slug = sanitize_title( $_POST['name'] );
			//remove_filter( 'mstw_lm_sports_list', 'mstw_lm_upgrade_sports_list', 10, 1 );
			//wp_update_term( $term_id, 'mstw_lm_league', array( 'slug' => $league ) );
			//add_filter( 'mstw_lm_sports_list', 'mstw_lm_upgrade_sports_list', 10, 1 );
		}
		*/
		
		//$sport_slug = mstw_lm_get_league_sport( $league_slug );
		//mstw_log_msg( "sport for league $league_slug is $sport_slug ");
		
		//
		// Should always have some sport input to process
		//
		if ( isset( $_POST['mstw-lm-league-sport'] ) ) {
			 /*&& -1 != $_POST['mstw-lm-league-sport']*/ 
			
			$sport_slug = $_POST['mstw-lm-league-sport'];
				
			// SAVE/UPDATE LEAGUE SPORT 
			if ( mstw_lm_update_league_sport( $league_slug, $sport_slug ) ) {
				//mstw_log_msg( "updated league $league_slug with sport $sport_slug " );
			}
			else {
				mstw_log_msg( "failed to update league $league_slug with sport $sport_slug " );	
			}
			
		} //End: if ( isset( $_POST['mstw-lm-league-sport'] )
		else {
			mstw_log_msg( "No sport is set for league. Should go to default?");
		}
			
		if( array_key_exists( 'league-season', $_POST ) and !empty( $_POST['league-season'] ) and -1 != $_POST['league-season'] ) {
			$season_name = $_POST['league-season'];	
		}
		else {
			//mstw_log_msg( "No season is set for league so we're setting one as the current year.");
			// we're going to set one
			$season_name = date( 'Y' );
		}
		
		$season_slug = sanitize_title( $season_name );
		//mstw_log_msg( 'name= ' . $season_name . ' / ' . $season_slug );
		
		mstw_lm_update_league_seasons( $league_slug, $season_slug, $season_name );
		
	} //End: mstw_lm_save_league_meta()
 }
 
 //-------------------------------------------------------------
 // add help - outputs HTML for the contextual help area of the screen
 //			   callback for load-edit-tags.php action set in mstw_lm_admin.php
 //		
 // ARGUMENTS:
 //	 None 
 //   
 // RETURNS:
 //	 Outputs HTML to the contextual help aree of the screen
 //-------------------------------------------------------------
 if ( !function_exists ( 'mstw_lm_add_leagues_help' ) ) {	
	function mstw_lm_add_leagues_help( ) {
		//mstw_log_msg( "mstw_lm_add_leagues_help:" );
		
		if ( array_key_exists( 'taxonomy', $_GET ) and 'mstw_lm_league' == $_GET['taxonomy'] ) {
		
			$screen = get_current_screen( );
			// We are on the correct screen because we take advantage of the
			// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
			
			//mstw_log_msg( "current screen:" );
			//mstw_log_msg( $screen );
			
			mstw_lm_help_sidebar( $screen );
					
			$tabs = array( array(
							'title'    => __( 'Overview', 'mstw-league-manager' ),
							'id'       => 'leagues-overview',
							'callback' => 'mstw_lm_add_league_help_tabs',
							),
						 );
						 
			foreach( $tabs as $tab ) {
				$screen->add_help_tab( $tab );
			}
		
		}
		
	} //End: mstw_lm_manage_leagues_help( )
 }
 
	function mstw_lm_add_league_help_tabs( $screen, $tab ) {
		//mstw_log_msg( "mstw_lm_add_league_help_tabs ... " );
		//mstw_log_msg( "screen:" );
		//mstw_log_msg( $screen );
		//mstw_log_msg( "tab:" );
		//mstw_log_msg( $tab );
		
		if( !array_key_exists( 'id', $tab ) ) {
			return;
		}
			
		switch ( $tab['id'] ) {
			case 'leagues-overview':
				?>
				<p><?php _e( 'The Leagues screen is the first stop when using the plugin. Without at least one league, nothing happens. Leagues are not “hierarchical” on this screen, but design of the site’s leagues should be considered carefully. Based on the chosen setup, league standings and schedules can then be displayed very flexibly since each shortcode accepts multiple leagues. Generally speaking, add only the lowest level leagues. For example, one might add the lowest level leagues for the NHL: Atlantic, Metropolitan, Central, and Pacific. Schedules can then be displayed for each of those leagues, the Eastern and Western Conferences, and the entire NHL.', 'mstw-league-manager' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-leagues/" target="_blank"><?php _e( 'See the Leagues man page for more details.', 'mstw-league-manager' ) ?></a></p>
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab( )
	
 //-------------------------------------------------------------
 // mstw_lm_cleanup_league_meta - callback for delete_term_taxonomy action
 //		added in mstw_lm_admin.php
 //		
 // ARGUMENTS:
 //	 $league_id: ID of deleted term
 //  
 //  Remove the following options:
 //		lm-league-seasons_(league-slug)
 //		lm-league-sport_(league-slug)
 //		lm-league-current-season_(league-slug)
 //
 //	 Delete ALL the games in that league
 //
 //	 WordPress handles removing the term from all teams, records, and games.
 // 
 // RETURNS:
 //	 None
 //-------------------------------------------------------------
if ( !function_exists( 'mstw_lm_cleanup_league_meta' ) ) { 
  function mstw_lm_cleanup_league_meta( $league_id ) {
	//have to delete league seasons and sport method
	//mstw_log_msg( 'divider' );
	//mstw_log_msg( 'mstw_lm_cleanup_league_meta:' );
	//mstw_log_msg( '$league_id = ' . $league_id );
	//mstw_log_msg( 'term_ids:' );
	//mstw_log_msg( $term_ids );
	
	$league_obj = get_term( $league_id, 'mstw_lm_league', OBJECT, 'raw' );
	if ( $league_obj ) {
		//mstw_log_msg( '$league_obj: ' );
		//mstw_log_msg( $league_obj );
		
		// Get the league slug
		$league_slug = $league_obj->slug;
		
		//
		// Delete the league's seasons
		//
		$option = 'lm-league-seasons_' . $league_slug;
		
		if( delete_option( $option ) ) {
			//mstw_log_msg( "option $option deleted" );
		} 
		else {
			//mstw_log_msg( "option $option NOT deleted" );
		}
		
		//
		// Delete the league's sport
		//
		$option = 'lm-league-sport_' . $league_slug;

		if( delete_option( $option ) ) {
			//mstw_log_msg( "option $option deleted" );
		} 
		else {
			//mstw_log_msg( "option $option NOT deleted" );
		}
		
		//
		// Delete the league's current season
		//
		$option = 'lm-league-current-season_' . $league_slug;

		if( delete_option( $option ) ) {
			//mstw_log_msg( "option $option deleted" );
		} 
		else {
			//mstw_log_msg( "option $option NOT deleted" );
		}
		
		//
		// Delete all the league's records
		//
		$args = array( 'posts_per_page' => -1, 
					    'post_type'		=> 'mstw_lm_record',
						);
						
		$records = get_posts( $args );
		
		foreach ( $records as $record ) {
			$title = $record -> post_title;
			//mstw_log_msg( "Record title: $title" );
			if ( false !== strpos( $title, $league_slug ) ) {
				//mstw_log_msg( "deleting $title for $league_slug" );
				wp_delete_post( $record -> ID, true );
			}	
		}
		
		//
		// Delete all the league's games
		//
		// This is messy because the action fires too late;
		// post-term links have already been reset for deleted term
		// Soooo ....
		// 		Get all the remaining terms (ids)
		//		Is delete term in there? Don't want it
		//		Delete all the posts without one of those terms set
		//
		$args = array ( 'exclude' => $league_obj -> term_id,
						'fields'  => 'ids',
						);
						
		$terms = get_terms( 'mstw_lm_league', $args );
		
		//mstw_log_msg( "terms: " );
		//mstw_log_msg( $terms );
		
		$args = array( 'posts_per_page' => -1, 
					   'post_type'      => 'mstw_lm_game',
					  
					   'tax_query'      => array( array (
											'taxonomy' => 'mstw_lm_league',
											'field'    => 'term_id',
											'terms'    => $terms,
											'operator' => 'NOT IN',
											), ),
					    
					 );
						
		$games = get_posts( $args );
		
		$nbr_games = count( $games );
		
		/*
		if ( $nbr_games > 0 ) {
			$notice = sprintf( __( '%s games deleted', 'mstw-league-manager' ), $nbr_games );
			mstw_lm_add_admin_notice( 'updated', $notice );
		}
		*/
		
		foreach ( $games as $game ) {
			wp_delete_post( $game -> ID );
		}
		
		mstw_log_msg( "$nbr_games games for league slug: $league_slug" );
		//mstw_log_msg( $games );
		//mstw_log_msg( 'divider' );
		
	}
	return;	
  } //End: mstw_lm_cleanup_league_meta() 
}