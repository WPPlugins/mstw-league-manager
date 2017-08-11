<?php
/*---------------------------------------------------------------------------
 *	mstw-lm-cpts.php
 *		Registers the custom post types & taxonomies for MSTW League Manager
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
 *-------------------------------------------------------------------------*/
 
// ----------------------------------------------------------------
// Register the MSTW League Manager Custom Post Types & Taxonomies
// 		mstw_lm_player, mstw_lm_team
//
if( !function_exists( 'mstw_lm_register_cpts' ) ) {
	function mstw_lm_register_cpts( ) {

		$menu_icon_url = plugins_url( 'images/mstw-admin-menu-icon.png', dirname( __FILE__ ) );
		
		$capability = 'read';
		
		//----------------------------------------------------------------------------
		// register mstw_lm_venue post type - replacement for game_location CPT
		//
		
		$args = array(
			'public' 			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> false, //default is value of show_ui
			'show_in_admin_bar' => false, //default is value of show_in_menu
			
			'menu_icon'     	=> $menu_icon_url,
			
			'query_var' 		=> true, //default is mstw_lm_venue
			
			'rewrite' 			=> array(
											'slug' => 'mstw-lm-venue',
											'with_front' => false,
										),
			
			'supports' 			=> array( 'title' ),
			
			//post is the default capability type
			//'capability_type'	=> array( 'venue', 'venues' ),

			//'map_meta_cap' 		=> true,		
			
			'labels' 			=> array(
				'name' 				 => __( 'Venues', 'mstw-league-manager' ),
				'singular_name' 	 => __( 'Venue', 'mstw-league-manager' ),
				'all_items' 		 => __( 'All Venues', 'mstw-league-manager' ),
				'add_new' 			 => __( 'Add New Venue', 'mstw-league-manager' ),
				'add_new_item' 		 => __( 'Add Venue', 'mstw-league-manager' ),
				'edit_item' 		 => __( 'Edit Venue', 'mstw-league-manager' ),
				'new_item' 			 => __( 'New Venue', 'mstw-league-manager' ),
				'view_item' 		 => null, //'View Venue needs a custom page template that is of little value.
				'search_items' 		 => __( 'Search Venues', 'mstw-league-manager' ),
				'not_found' 		 => __( 'No Venues Found', 'mstw-league-manager' ),
				'not_found_in_trash' => __( 'No Venues Found In Trash', 'mstw-league-manager' ),
				),
		);
	
		register_post_type( 'mstw_lm_venue', $args );
		
	//
	// Register the venue taxonomy ... acts like a tag
	//
	$labels = array( 
				'name' => __( 'MSTW League Manager Venue Groups', 'mstw-league-manager' ),
				'singular_name' =>  __( 'Venue Group', 'mstw-league-manager' ),
				'search_items' => __( 'Search Venue Groups', 'mstw-league-manager' ),
				'popular_items' => null, //removes tagcloud __( 'Popular Scoreboards', 'mstw-league-manager' ),
				'all_items' => __( 'All Venue Groups', 'mstw-league-manager' ),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __( 'Edit Venue Group', 'mstw-league-manager' ), 
				'update_item' => __( 'Update Venue Group', 'mstw-league-manager' ),
				'add_new_item' => __( 'Add New Venue Group', 'mstw-league-manager' ),
				'new_item_name' => __( 'New Venue Group Name', 'mstw-league-manager' ),
				'separate_items_with_commas' => __( 'Add venue to one or more venue groups (separate groups with commas).', 'mstw-league-manager' ),
				'add_or_remove_items' => __( 'Add or Remove Venue Groups', 'mstw-league-manager' ),
				'choose_from_most_used' => __( 'Choose from the most used venue groups', 'mstw-league-manager' ),
				'not_found' => __( 'No venue groups found', 'mstw-league-manager' ),
				'menu_name'  => __( 'Venue Groups', 'mstw-league-manager' ),
			  );
			  
	$args = array( 
			//'label'				=> 'MSTW Scoreboards', //overridden by $labels->name
			'labels'				=> $labels,
			'public'				=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_tagcloud'			=> false,
			//'meta_box_cb'			=> null, provide callback fcn for meta box display
			'show_admin_column'		=> true, //allow automatic creation of taxonomy column in associated post-types table.
			'hierarchical' 			=> false, //behave like tags
			//'update_count_callback'	=> '',
			'query_var' 			=> true, 
			'rewrite' 				=> true,
			'show_tagcloud' 		=> false
		);
		
	register_taxonomy( 'mstw_lm_venue_group', 'mstw_lm_venue', $args );
	register_taxonomy_for_object_type( 'mstw_lm_venue_group', 'mstw_lm_venue' );
			
		//----------------------------------------------------------------------------
		// register mstw_lm_team post type
		//
		
		$args = array(
			'public' 			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> false, //default is value of show_ui
			'show_in_admin_bar' => false, //default is value of show_in_menu
			'menu_icon'     	=> $menu_icon_url,
			//'show_in_menu' 		=> 'edit.php?post_type=scheduled_games',
			
			'query_var' 		=> true, //default is mstw_lm_team
			'rewrite' 			=> array(
				'slug' 			=> 'lm-team',
				'with_front' 	=> false,
			),
			
			'supports' 			=> array( 'title' ),
			
			'taxonomies' 		=> array( 'mstw_lm_league' ),
			
			//post is the default capability type
			//'capability_type'	=> array( 'team', 'teams' ), 
			
			//'map_meta_cap' 		=> true,
			
			'labels' 			=> array(
										'name' => __( 'Teams', 'mstw-league-manger' ),
										'singular_name' => __( 'Team', 'mstw-league-manger' ),
										'all_items' => __( 'All Teams', 'mstw-league-manger' ),
										'add_new' => __( 'Add New Team', 'mstw-league-manger' ),
										'add_new_item' => __( 'Add Team', 'mstw-league-manger' ),
										'edit_item' => __( 'Edit Team', 'mstw-league-manger' ),
										'new_item' => __( 'New Team', 'mstw-league-manger' ),
										//'View Game Schedule' needs a custom page template that is of no value.
										'view_item' => null, 
										'search_items' => __( 'Search Teams', 'mstw-league-manger' ),
										'not_found' => __( 'No Teams Found', 'mstw-league-manger' ),
										'not_found_in_trash' => __( 'No Teams Found In Trash', 'mstw-league-manger' ),
										)
			);
			
		register_post_type( 'mstw_lm_team', $args);
		
		
		//----------------------------------------------------------------------------
		// register mstw_lm_record post type
		//
		
		$args = array(
			'public' 			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> false, //default is value of show_ui
			'show_in_admin_bar' => false, //default is value of show_in_menu
			'menu_icon'     	=> $menu_icon_url,
			//'show_in_menu' 		=> 'edit.php?post_type=scheduled_games',
			
			'query_var' 		=> true, //default is mstw_lm_team
			'rewrite' 			=> array(
				'slug' 			=> 'lm-team',
				'with_front' 	=> false,
			),
			
			'supports' 			=> array( 'title' ),
			
			'taxonomies' 		=> array( 'mstw_lm_league' ),
			
			//post is the default capability type
			//'capability_type'	=> array( 'team', 'teams' ), 
			
			//'map_meta_cap' 		=> true,
			
			'labels' 			=> array(
										'name' => __( 'Records', 'mstw-league-manger' ),
										'singular_name' => __( 'Record', 'mstw-league-manger' ),
										'all_items' => __( 'All Records', 'mstw-league-manger' ),
										'add_new' => __( 'Add New Record', 'mstw-league-manger' ),
										'add_new_item' => __( 'Add Record', 'mstw-league-manger' ),
										'edit_item' => __( 'Edit Record', 'mstw-league-manger' ),
										'new_item' => __( 'New Record', 'mstw-league-manger' ),
										//'View Game Schedule' needs a custom page template that is of no value.
										'view_item' => null, 
										'search_items' => __( 'Search Records', 'mstw-league-manger' ),
										'not_found' => __( 'No Records Found', 'mstw-league-manger' ),
										'not_found_in_trash' => __( 'No Records Found In Trash', 'mstw-league-manger' ),
										)
			);
			
		register_post_type( 'mstw_lm_record', $args);

		
		//----------------------------------------------------------------------------
		// register mstw_lm_game post type
		//
		
		$args = array(
			'label'				=> __( 'Games', 'mstw-league-manger' ),
			'description'		=> __( 'CPT for games in MSTW League Manager Plugin', 'mstw-league-manger' ),
			
			'public' 			=> true,
			'exclude_from_search'	=> true, //default is opposite value of public
			'publicly_queryable'	=> true, //default is value of public
			'show_ui'			=> true,
			'show_in_nav_menus'	=> false, //default is value of public
			//going to build own admin menu
			'show_in_menu'		=> false, //default is value of show_ui
			'show_in_admin_bar' => false, //default is value of show_in_menu
			//only applies if show_in_menu is true
			//'menu_position'		=> 25, //25 is below comments, which is the default
			'menu_icon'     	=> $menu_icon_url,
			
			//'capability_type'	=> 'post' //post is the default
			//'capabilities'		=> null, //array default is constructed from capability_type
			//'map_meta_cap'	=> null, //null is the default
		
			//'hierarchical'	=> false, //false is the default
			
			'rewrite' 			=> array(
				'slug' 			=> 'lm-game',
				'with_front' 	=> false,
			),
			
			'supports' 			=> array( 'title', 'editor' ),
			
			//post is the default capability type
			//'capability_type'	=> array( 'game', 'games' ), 
		
			//'map_meta_cap' 		=> true,
			
			//'register_meta_box_cb'	=> no default for this one
			
			'taxonomies' 		=> array( 'mstw_lm_league' ),
			
			// Note that is interacts with exclude_from_search
			//'has_archive'		=> false, //false is the default
			
			'query_var' 		=> true, //default is mstw_lm_team
			'can_export'		=> true, //default is true
			
			'labels' 			=> array(
										'name' => __( 'Games', 'mstw-league-manger' ),
										'singular_name' => __( 'Game', 'mstw-league-manger' ),
										'all_items' => __( 'All Games', 'mstw-league-manger' ),
										'add_new' => __( 'Add New Game', 'mstw-league-manger' ),
										'add_new_item' => __( 'Add Game', 'mstw-league-manger' ),
										'edit_item' => __( 'Edit Game', 'mstw-league-manger' ),
										'new_item' => __( 'New Game', 'mstw-league-manger' ),
										//'View Game Schedule' needs a custom page template that is of no value.
										'view_item' => null, 
										'search_items' => __( 'Search Games', 'mstw-league-manger' ),
										'not_found' => __( 'No Games Found', 'mstw-league-manger' ),
										'not_found_in_trash' => __( 'No Games Found In Trash', 'mstw-league-manger' ),
										)
			);
			
		register_post_type( 'mstw_lm_game', $args);
		
		//
		// Register the league taxonomy ... acts like a tag
		//
		$labels = array( 
					'name' => __( 'Leagues', 'mstw-league-manager' ),
					'singular_name' =>  __( 'League', 'mstw-league-manager' ),
					'search_items' => __( 'Search Leagues', 'mstw-league-manager' ),
					'popular_items' => null, //removes tagcloud __( 'Popular Leagues', 'mstw-league-manager' ),
					'all_items' => __( 'All Leagues', 'mstw-league-manager' ),
					'parent_item' => null,
					'parent_item_colon' => null,
					'edit_item' => __( 'Edit League', 'mstw-league-manager' ), 
					'update_item' => __( 'Update League', 'mstw-league-manager' ),
					'add_new_item' => __( 'Add New League', 'mstw-league-manager' ),
					'new_item_name' => __( 'New League Name', 'mstw-league-manager' ),
					'separate_items_with_commas' => __( 'Add Player to one or more Leagues (separate Leagues with commas).', 'mstw-league-manager' ),
					'add_or_remove_items' => __( 'Add or Remove Leagues', 'mstw-league-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used Leagues', 'mstw-league-manager' ),
					'not_found' => __( 'No Leagues Found', 'mstw-league-manager' ),
					'menu_name'  => __( 'Leagues', 'mstw-league-manager' ),
				  );
				  
		$args = array( 
				//'label'				=> 'MSTW Leagues', //overridden by $labels->name
				'labels'				=> $labels,
				'public'				=> true,
				'show_ui'				=> true,
				'show_in_nav_menus'		=> true,
				'show_in_menu'			=> true,
				'show_tagcloud'			=> false,
				//'meta_box_cb'			=> null, provide callback fcn for meta box display
				'show_admin_column'		=> true, //allow automatic creation of taxonomy column in associated post-types table.
				'hierarchical' 			=> true, //behave like tags
				//'update_count_callback'	=> '',
				'query_var' 			=> true, 
				'rewrite' 				=> /*true,*/
											array(
												'slug' 			=> 'league',
												'with_front' 	=> false,
												),
				//'capabilities'			=> array( ),
				/*'capabilities'			=> array(
												'manage_terms' => 'manage_lm_teams',
												'edit_terms' => 'manage_lm_teams',
												'delete_terms' => 'manage_lm_teams',
												'assign_terms' => 'manage_lm_teams',
												),*/
				//'sort'					=> null,
			);
			
		register_taxonomy( 'mstw_lm_league', array( 'mstw_lm_game', 'mstw_lm_team'), $args );
		register_taxonomy_for_object_type( 'mstw_lm_league', 'mstw_lm_game' );
		register_taxonomy_for_object_type( 'mstw_lm_league', 'mstw_lm_team' );
		

	} //End: mstw_lm_register_cpts( )
}
?>