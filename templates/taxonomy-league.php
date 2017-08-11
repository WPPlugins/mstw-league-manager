<?php
/**
 * MSTW League Manager template for displaying league & team schedules
 *
 * NOTE: Plugin users/site admins may have to modify this template to fit their 
 * individual themes. This template has been tested in the WordPress 
 * Twenty Eleven and Twenty Twelve themes. 
 *
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
			
			<?php 
			if ( !isset( $_GET['season'] ) or !isset( $_GET['team'] ) ) {
				echo "<h2>Both season and team are required.</h2>";
				
			} else {
				$season = $_GET['season'];
				//echo "<p>Season: " . $_GET['season'] . "</p>";
				$team = $_GET['team'];
				//echo "<p>Team: " . $_GET['team'] . "</p>";
				
				$team_obj = get_page_by_path( $team, OBJECT, 'mstw_lm_team' );
				
				if ( null !== $team_obj ) {
					$team_name = get_post_meta( $team_obj -> ID, 'team_name', true );
				} else {
					$team_name = "No team";
				}
				
				//echo "<p>" . $_SERVER['REQUEST_URI'] . "</p>";
				$uri_array = explode( '/', $_SERVER['REQUEST_URI'] );
				
				$league =  $uri_array[2];
				
				$seasons = mstw_lm_get_league_seasons( $league );
				
				$season_name =  ( array_key_exists( $season, $seasons ) ) ?  $seasons[ $season ] : 'No season' ;
				?>
				
				<div class='entry-content'>
				<h2><?php echo "$team_name $season_name " . __( 'Schedule', 'mstw-league-manager' ) ?> </h2>
				<?php
				//echo "<p>League: " . $league . "</p>";
				
				echo do_shortcode( "[mstw_team_schedule league='$league' season='$season' team='$team']" );
				
				//echo do_shortcode( "[mstw_team_schedule league='palomar-league' season='2016' team='rb-broncos']" );
			}
			?>
			</div> <!-- .entry-content -->
			
			<?php //while ( have_posts() ) : the_post(); ?>
				<?php //get_template_part( 'content', 'page' ); ?>
				<?php //comments_template( '', true ); ?>
			<?php //endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>