=== FF Communitymap ===
Contributors: andibraeu
Donate link: http://www.weimarnetz.de/spenden
Tags: community map, freifunk, wifi
Requires at least: 3.6
Tested up to: 5.6.2
Stable tag: 4.2

Displays the freifunk community map and/or the freifunk community table

== Description ==

This plugin provides a shortcode to display the well known freifunk community map: [ffcommunitymap]

It is fully configurable to embed your own sources. These options are available:

* geojsonurl
 * Default: "//api.freifunk.net/map/ffGeoJsonp.php?callback=?"
 * Description: URL with API data, we need jsonp there
* hidelocationbutton
 * Default: 0
 * Description:
* hidelayercontrol
 * Default: 0
 * Description: hide or show layer box
* hideinfobox
 * Default: 0
 * Description: hide or show info box
* feedurl
 * Default: "//api.freifunk.net/feed/feed.php"
 * Description: a feed provided by https://github.com/freifunk/feed.api.freifunk.net
* newscontentlimit
 * Default: 3
 * Description: number of news entries
* eventscontentlimit
 * Default: 2
 * Description: number of event entries
* postcontentlength
 * Default: 30
 * Description: length event headlines
* zoomlevel
 * Default: 5
 * Description: default zoom level on page load
* scrollandzoom
 * Default: 1
 * Enable scroll and zooming by mouse wheel or one-finger-touch
* center
 * Default: [51.5,10.5]
 * Description: initial center of map
* divid
 * Default: "map"
 * Description: div id where map should be displayed
* showevents
 * Default: 0
 * Description: show events in community popup
* shownews
 * Default: 0
 * Description: show news in community popup
* mapboxid
 * Default: "mapbox.streets"
 * Description: id for your mapbox tiles
* mapjs
 * Default: "//api.freifunk.net/map/community_map.js"
 * Description: link to community js files
* popuptype
 * Default: "community"
 * Description: type for popups for different purposes

Another shortcode is for displaying a community table: [ffcommunitytable]

Available options are:

* summaryurl
 * Default: "//api.freifunk.net/map/ffApiJsonp.php?mode=summary&callback=?"
 * Description: URL with API data, we need jsonp there
* columns
 * Default: "city,name,firmware,routing,nodes,contact"
 * Description: select available columns comma separated
* number_communities
 * Default: "3"
 * Description: number of communities displayed in search
* enable_zip_search
 * Default: "1"
 * Description: enable zip/city search
* nominatim_email
 * Default: "yourmail@domain.tld"
 * Description: email provided to nominatim queries to avoid abuse

The shortcode [ffapijs] simply loads ffctable.js and dependencies for use API data in pages and posts.

Example code to embed a selction of email addresses of communities:

`<p>Adresse<br />
*    [text* your-street placeholder "Straße"]
*    [text your-location id:zipinput placeholder "Postleitzahl und Ort"]
</p>
<div class="zipsearch"><button type="button" id="zipsubmit" class="btn waves-effect waves-light">Nächste Communities finden</button></div>
<div id="zipresult"></div>

<p>Communities<br />
*    [select your-communities id:mycommunities multiple]
</p>

<p>[submit "Senden"]</p>

<script type="text/javascript">
var cTable;
jQuery(document).ready(function() {
  cTable = FFCTABLE.init("dummyid", "//api.freifunk.net/map/ffApiJsonp.php?mode=summary&callback=?", "mail@dingsund.so", "3");
  cTable.getData(function(e) {});
  jQuery("#zipsubmit").click(function(e) {
      cTable.getDistanceByZip(cTable, function(data, type) {
      console.log(data);
      jQuery("#mycommunities").empty();
      _.each(data.communityDataDisplay, function(item, key, list) {
          if (item.socialprojects && item.socialprojects.contact) {
          email = item.socialprojects.contact;

          } else if (item.contact && item.contact.email) {
          email = item.contact.email;

          }
          if (email) {
          email = email.replace('mailto:', '');
          jQuery("#mycommunities").append(new Option(item.name + " (ca. " + item.distance + " km)", email, true, true));

          }

          })

          });

      });

    });
</script>`

== Changelog ==

 = 4.2 =
 * use an icon font instead of an image for contact options 

 = 4.1 =
 * add option to disable scroll by mouse wheel or one-finger-touch

 = 4.0 =
 * javascripts now use callbacks, we're more flexible to use api data
 * added shortcode [ffapijs] to load javascripts in page

 = 3.2 =
 * refactoring to display popups on different use cases

 = 3.1 =
 * some minor corrections

 = 3.0 =
 * add zip search to community table

 = 2.1 =
 * refactoring
 * table now sortable
 * table now mobile friendly

 = 2.0 =
 * add community table

 = 1.0 =
 * initial release
 * show community map and use a bunch of options
