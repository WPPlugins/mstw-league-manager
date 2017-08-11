=== MSTW League Manager ===
Contributors: MarkODonnell
Donate link: http://shoalsummitsolutions.com
Tags: sports,leagues,sports schedules,sports standings,league schedules,league standings,league manager,schedule ticker,schedule slider,round robin,tournament manager,round robin tournament  
Requires at least: 4.3
Tested up to: 4.7
Stable tag: 1.4
Text Domain: mstw-league-manager
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manages multiple sports leagues and seasons. Displays league schedules and standings.

== Description ==

The MSTW League Manager plugin manages multiple sports leagues, each with multiple seasons. A "league" can be a league (like the Premier League or Pac 12) or a round robin tournament. A shortcodes are available to display league standings tables, league schedules, individual team schedules, schedule sliders, and schedule sliders. The font end displays can be formatted via a rich set of display settings. Displays may be further styled via the custom stylesheets. 

= NEW IN MSTW LEAGUE MANAGER =
* Multiple-Standings Tables - display multiple standings tables in the same screen real estate
* League Schedule 'Tickers' - horizontal scrolling schedule summaries or 'scoreboards'
* League Schedule 'Sliders' - another, larger version of horizontal scrolling schedules

See the **Changelog** below for more details.

**Need team logos for website?** Logo sets for your favorite leagues at [the MSTW Store](http://shoalsummitsolutions.com/logo-sets/). Whether you don’t have the graphics skills, don’t have the interest, or just don’t have the time, you can jump start your website’s database of teams with perfectly sized logos for league standings tables and league and team schedule tables.

= Helpful Links =
* [**See what the plugin in action on the MSTW Dev Site -»**](http://dev.shoalsummitsolutions.com/league-manager/)
* [**Read the (site admin) user's manual at shoalsummitsolutions.com -»**](http://shoalsummitsolutions.com/category/users-manuals/lm-plugin/)

== Installation ==

All the normal installation methods for WordPress plugins work. See [the installation manual page](http://shoalsummitsolutions.com/lm-installation/) for details.
*Upon installation make sure the WP default timezone is set correctly in the Wordpress Settings->General screen.*

== Frequently Asked Questions ==

[The plugin's FAQs may be found here.](http://shoalsummitsolutions.com/lm-faq/)

== Screenshots ==

1. Sample League Standings Table [shortcode]
2. Sample League Schedule Gallery [shortcode]
3. Sample League Schedule Table [shortcode]
4. Sample Team Schedule Table [shortcode]
5. Sample Locations(Venues) Table [shortcode]

== Upgrade Notice ==

The current version of MSTW League Manager has been tested on WP 4.5 with the [Wordpress Twenty Twelve theme.](https://wordpress.org/themes/twentytwelve/) If you use older version of WordPress, the plugin may or may not function properly. If you are using a newer version, please let me know how the plugin works, especially if you encounter problems.

== Changelog ==

= 1.4 =
* Two different League Schedule schedule/scoreboard scrolling views have been added. "Tickers" are a very concise view, for a banner on the main page say. "Sliders" present the same information in a larger format. [See the examples on the MSTW dev site.](http://dev.shoalsummitsolutions.com/lm-sliders-tickers/)
* League standings table now supports columns for  "Last Game" and "Next Game"
* Home games are no longer automatically indicated with a '*' in mstw_team_schedule shortcode table. A 'star_home' shortcode argument was added (which must be placed in the shortcode call). If star_home is not empty, the value is added after the opponent name for home games. For example, if star_home='*', an asterisk is added to home games. The home game format may also be changed via the tr.home-game css tag.
* Added a 'star_league' shortcode argument for mstw_team_schedule shorcode (which must be placed in the shortcode call). If star_league is not empty, the value is added after the opponent name for league games. For example, if star_league='*', an asterisk is added to league games.
* Team schedule tables now honor the settings in the Settings -> Schedule Table screen. Please read the man page for more information on how Team Schedules and League Schedules share this set of settings.
* Team Schedule tables now display the results of final games as "W 24-14" or "L 14-24" instead of the "CAL 24, USC 14" displayed in the League Schedule tables.
* New arguments have been provided to display a number of days on each side of "today" in the schedule/scoreboard tickers.
* Changed the link to the team URL from the team name in league schedule tables to NOT open a new tab. I believe this is six of one, half dozen of the other, but if there is enough pushback, I'll add a setting or shortcode argument to control this behavior.
* Games now have "post content", which is displayed on the single game template (/templates/single_game.php). This can be used to show pre-game info, and/or a post game summary and statistics.

= 1.3 =
* Fixed a bug that prevented the standings tables from updating when teams were moved between leagues. [Read more here.](http://shoalsummitsolutions.com/league-manager-versions-1-2-1-3)

= 1.2 =
* Fixed bug that prevented game times from being displayed correctly (no minutes displayed)
* Minor clean-ups

= 1.1 =
* Added [mstw_location_table] shortcode
* Added venue groups to the venues(locations) admin screen and the above shortcode
* Added a Next Game field to the Standings Tables
* Added links from standings table fields: Team Name to Team URL or Team Schedule, and Next Game to Single Game Page.
* Added links from schedules table, team schedule table, and schedule gallery fields: Team Name to Team URL or Team Schedule, Location(Venue) to Venue URL or Google Map, and Game Time/Result to Single Game page.
* Added a show_home_away argument to the mstw_team_schedule shortcode.
* Added home-game and away-game class tags to the team schedule tables.
* Corrected some minor bugs.
* Removed some left over debug messages.

= 1.0 =
* Initial release.