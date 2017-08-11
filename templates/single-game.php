<?php
/**
 * MSTW League Manager single game template.
 *
 * NOTE: Plugin users/site admins may have to modify this template to fit their 
 * individual themes. This template has been tested in the WordPress 
 * Twenty Eleven and Twenty Twelve themes. 
 *
 */
 ?>

<?php get_header(); ?>

<div id="primary">
 <div id="content" role="main">

	<?php while ( have_posts() ) : the_post(); ?>
		<!-- Navigation back to previous page -->
		<!--
		<nav id="nav-single">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'mstw-league-manager' ); ?></h3>
			<span class="nav-previous">
				<?php //$back =$_SERVER['HTTP_REFERER'];
				//if( isset( $back ) && $back != '' ) { 
					//echo '<a href="' . $back . '">';?>
					<span class="meta-nav">&larr;</span><?php //_e( 'Previous Page', 'mstw-league-manager' ) ?></a>
				<?php
				//}?>
			</span> <!-- .nav-previous 	
		</nav><!-- #nav-single
		-->

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		 <?php
		 //
		 // PULL THE GAME DATA
		 //
		 //
		 
		 // Should pull sport from game (somehow), then get options
		 $options = mstw_lm_get_sport_options( 'football-ncaa' );
		
		 // Game ID
		 $game_ID  = get_the_ID( );
		 $game_obj = get_post( );
		
		 // find the home team
		 $home_team_slug = get_post_meta( $game_ID, 'game_home_team', true );
		 $home_team_obj = get_page_by_path( $home_team_slug, OBJECT, 'mstw_lm_team' );
		
		 // find the away team
		 $away_team_slug = get_post_meta( $game_ID, 'game_away_team', true );
		 $away_team_obj = get_page_by_path( $away_team_slug, OBJECT, 'mstw_lm_team' );
		
		 $is_home_game = get_post_meta( $game_ID, 'game_home_score', true ); 
		 $home_css_tag = ( $is_home_game ) ? 'mstw-ss-home' : '';
		 
		 // Game date & time
		 $unix_timestamp = get_post_meta( $game_ID, 'game_unix_dtg', true );
		 
		 // Time and date formats //could use gallery formats
		 $date_format = $options['date_format']; 
		 $time_format = $options['time_format']; 
		 
		 $game_is_final = get_post_meta( $game_ID, 'game_is_final', true );
		 
		 ?>
			
		 <div class="single-game single-game_<?php echo( $home_team_slug ) ?> <?php echo $home_css_tag ?>">
		  <!--
		  Build the date-time header block
		  -->
		  <div class='date-time-block'><?php echo date( 'l, j F Y', $unix_timestamp) ?></div>
		
		  <div class="single-game-sb-block">
		    <!--
		    Build the home team block
		    -->
			<div class="sb-team-block sb-home">
			  <?php echo mstw_lm_build_team_logo( $home_team_slug, 'large' ) ?>
			  <p>
			  <?php echo mstw_lm_get_team_name( $game_obj, 'home', $options, 'name_mascot' )?>
			  </p>
			</div> <!-- .sb-team-block -->
			 
			<?php
			//
			// Build the status block
			//
			$home_score = get_post_meta( $game_ID, 'game_home_score', true );
			$away_score = get_post_meta( $game_ID, 'game_away_score', true );
			$status_entry = '';
			$score_entry = date( $time_format, $unix_timestamp );;
			
			if ( !empty( $home_score ) && !empty( $away_score ) ) {
				// We gonna ass-u-me game is in progress or final
				$score_entry = "$home_score - $away_score";
				
				if ( $game_is_final ) {
					$status_entry = __( 'FINAL', 'mstw-league-manager' );
				}
				else {
					$status_entry = get_post_meta( $game_ID, 'game_time_remaining', true ) .'  ' . mstw_lm_numeral_to_ordinal( (int)get_post_meta( $game_ID, 'game_period', true ) );
				}					
			}
			?>
			
			<div class="sb-data">
			 <div class="sb-score">
			  <?php echo $score_entry ?>
			 </div>
			 <div class="sb-status">
			  <?php echo $status_entry ?>
			 </div>
			</div> <!-- .sb-data -->
			 
			<!--
			Build the away team block
			-->
			<div class="sb-team-block sb-away">
			 <?php echo mstw_lm_build_team_logo( $away_team_slug, 'large' ) ?>
			 <p>
			  <?php echo mstw_lm_get_team_name( $game_obj, 'visitor', $options, 'name_mascot' )?>
			 </p>
			</div> <!-- .sb-team-block -->
			 
		  </div> <!-- .single-game-sb-block -->
			
			
		  <?php
		  //
		  // Build the location block
		  //
		  $game_location = mstw_lm_get_game_location( $game_obj, $options, $format = null );
		  //mstw_log_msg( "game location: $game_location" );
		  ?>
		  <div class='single-game-venue'>
			<?php echo $game_location ?>
		  </div>
		  <?php
		  //
		  // Build the content block
		  //
		  ?>
		  <div class='single-game-content'>
		    <?php the_content( ) ?>
		  </div>
		  
		 </div> <!-- .single-game -->
			
		</article> <!-- #post-<?php the_ID(); ?> -->

	<?php endwhile; // end of the loop. ?>

 </div><!-- #content -->
</div><!-- #primary -->

<?php get_footer();?>