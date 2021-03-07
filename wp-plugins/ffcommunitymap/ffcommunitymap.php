<?php

/*
Plugin Name: Ffcommunitymap
Plugin URI: http://api.freifunk.net
Description: display the freifunk community map
Version: 4.3
Author: Andi Bräu
Author URI: https://blog.andi95.de
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
 */

include_once("lib/PopupFactory.php");
include_once("lib/class.popups.php");

function ffcommunitymap($atts)
{
    $pf = new PopupFactory();
    $a = shortcode_atts(array(
        'feed_url' => '//api.freifunk.net/feed/feed.php',
        'geojsonurl'=> '//api.freifunk.net/map/ffGeoJsonp.php?callback=?',
    	'mapjs' => '//api.freifunk.net/map/community_map.js',
	    'popuptype' => 'community',
        'mapboxid' => 'mapbox.streets',
        'showevents' => '1',
        'shownews' => '0',
        'hidelocationbutton' => '0',
        'hidelayercontrol' => '0',
        'hideinfobox' => '0',
        'newscontentlimit' => '3',
        'eventscontentlimit' => '2',
        'postcontentlength' => '30',
        'zoomlevel' => '5',
        'scrollandzoom' => '1',
        'center' => '51.5,10.5',
        'height' => null,
        'width' => null
    ), $atts);

    $feedUrl = esc_url($a['feed_url']);
    $geoJsonUrl = esc_url($a['geojsonurl']);
    $mapJs = esc_url($a['mapjs']);
    $popupType = esc_html($a['popuptype']);
    $mapboxId = esc_html($a['mapboxid']);
    $showEvents = (esc_js($a['showevents']) === "1") ? "true" : "false";
    $showNews = (esc_js($a['shownews']) === "1") ? "true" : "false";
    $hideLocationButton = (esc_js($a['hidelocationbutton']) === "1") ? "true" : "false";
    $hideLayerControl = (esc_js($a['hidelayercontrol']) === "1") ? "true" : "false";
    $hideInfoBox = (esc_js($a['hideinfobox']) === "1") ? "true" : "false";
    $newsContentLimit = is_numeric($a['newscontentlimit']) ? $a['newscontentlimit'] : 3;
    $eventsContentLimit = is_numeric($a['eventscontentlimit']) ? $a['eventscontentlimit'] : 2;
    $postContentLength = is_numeric($a['postcontentlength']) ? $a['postcontentlength'] : 30;
    $zoomLevel = is_numeric($a['zoomlevel']) ? $a['zoomlevel'] : 4;
    $scrollAndZoom = (esc_js($a['scrollandzoom']) === "1") ? "true" : "false";
    $center = preg_match("/^\d[0-9\.]{1,},\d[0-9\.]{1,}$/", $a['center']) === 1 ? $a['center'] : "51.5,10.5";
    $height = preg_match("/^\d+(px|%)$/", $a['height']) === 1 ? $a['height'] : null;
    $width = preg_match("/^\d+(px|%)$/", $a['width']) === 1 ? $a['width'] : null;

    wp_enqueue_style("cssleaflet", "//api.freifunk.net/map/external/leaflet/leaflet.css");
    wp_enqueue_style("cssleafletmc", "//api.freifunk.net/map/external/leaflet/MarkerCluster.css");
    wp_enqueue_style("cssleafletmcd", "//api.freifunk.net/map/external/leaflet/MarkerCluster.Default.css");
    wp_enqueue_style("cssleafletbutton", "//api.freifunk.net/map/external/leaflet/leaflet-button-control.css");
    wp_enqueue_style("cssscrollbar", "//api.freifunk.net/timeline/malihu-scrollbar/jquery.mCustomScrollbar.min.css");
    wp_enqueue_style("csstimeline", "//api.freifunk.net/timeline/timeline.css");
    wp_enqueue_style("csstlcustom", "//api.freifunk.net/timeline/custom.css");
    wp_enqueue_style("csscommunitymap", "//api.freifunk.net/map/community_map.css");
    wp_enqueue_style("cssforkawesome", plugin_dir_url( __FILE__ ). "fonts/forkawesome/css/fork-awesome.min.css");
    wp_enqueue_style("mystyles", plugin_dir_url( __FILE__ ). "/css/ffcommunitymap.css");
    wp_enqueue_script("underscore");
    wp_enqueue_script("communitymap", $mapJs);
    wp_enqueue_script("leaflet", "//api.freifunk.net/map/external/leaflet/leaflet.js");
    wp_enqueue_script("leaflet-button-control", "//api.freifunk.net/map/external/leaflet/leaflet-button-control.js");
    wp_enqueue_script("leafletmc", "//api.freifunk.net/map/external/leaflet/leaflet.markercluster.js");
    wp_enqueue_script("scrollbar", "//api.freifunk.net/timeline/malihu-scrollbar/jquery.mCustomScrollbar.concat.min.js");
    wp_enqueue_script("timeline", "//api.freifunk.net/timeline/timeline.js");

    $popup = $pf->getPopupClass($popupType);
    if ( $height ) {
        $height = 'height: '.$height.';';
    }
    if ( $width ) {
        $width = 'width:' . $width.';';
    }
    $style = 'style="' . $height . ' ' . $width . '"';

    $divid = uniqid("map");
    $output = '<div id="'.$divid.'" class="mapfull" '.$style.'>'.PHP_EOL;
    $output .= '</div>'.PHP_EOL;
    $output .= $popup->assemblePopup();

    $output .= '<script>'.PHP_EOL;
    $output .= 'jQuery(document).ready(function() {'.PHP_EOL;
    $output .= 'var widget = FFCommunityMapWidget({'.PHP_EOL;
    $output .= 'ffGeoJsonUrl: "'.$geoJsonUrl.'",'.PHP_EOL;
    $output .= 'showEvents: '.$showEvents.','.PHP_EOL;
    $output .= 'showNews: '.$showNews.','.PHP_EOL;
    $output .= 'hideLocationButton: '. $hideLocationButton .','.PHP_EOL;
    $output .= 'hideLayerControl: '. $hideLayerControl .','.PHP_EOL;
    $output .= 'hideInfoBox: '. $hideInfoBox .','.PHP_EOL;
    $output .= 'feedUrl: "'.$feedUrl.'",'.PHP_EOL;
    $output .= 'newsContentLimit: '. $newsContentLimit .','.PHP_EOL;
    $output .= 'eventsContentLimit: '. $eventsContentLimit .','.PHP_EOL;
    $output .= 'postContentLength: '. $postContentLength .','.PHP_EOL;
    $output .= 'zoomLevel: '.$zoomLevel.','.PHP_EOL;
    $output .= 'scrollWheelZoom: '.$scrollAndZoom.','.PHP_EOL;
    $output .= 'touchZoom: '.$scrollAndZoom.','.PHP_EOL;
    $output .= 'center: ['.$center.'],'.PHP_EOL;
    $output .= 'divid: "'. $divid .'",'.PHP_EOL;
    $output .= 'mapboxId: "'.$mapboxId . '"'.PHP_EOL;
    $output .= '});'.PHP_EOL;
    $output .= '});'.PHP_EOL;
    $output .= ' </script>'.PHP_EOL;

    return $output;

}

function ffcommunitytable($atts)
{
    $a = shortcode_atts(array(
      'summaryurl'=> '//api.freifunk.net/map/ffApiJsonp.php?mode=summary&callback=?',
      'columns' => 'city,name,firmware,routing,nodes,contact,distance',
      'enable_zip_search' => '1',
      'number_communities' => '3',
      'nominatim_email' => 'yourmail@domain.tld'
    ), $atts);

    wp_enqueue_script("underscore", $in_footer = false);
    wp_enqueue_script("ffctable", plugin_dir_url( __FILE__ ). "js/ffctable.js");
    wp_enqueue_script("footable", plugin_dir_url( __FILE__ ). "js/footable.min.js");
    wp_enqueue_script("communitymap", "//api.freifunk.net/map/community_map.js");
    wp_enqueue_style("mystyles", plugin_dir_url( __FILE__ ). "css/ffcommunitymap.css");
    wp_enqueue_style("cssfootablecore", plugin_dir_url( __FILE__ ). "css/footable.standalone.min.css");
    wp_enqueue_style("cssforkawesome", plugin_dir_url( __FILE__ ). "fonts/forkawesome/css/fork-awesome.min.css");
    $summaryUrl = esc_url_raw($a['summaryurl']);
    $columns = preg_match("/^[a-z,]*$/", $a['columns']) === 1 ? explode(',', $a['columns']) : explode(',', 'name,city');
    $nominatim_email = is_email($a['nominatim_email']);
    $number_communities = is_numeric($a['number_communities']) ? $a['number_communities'] : 3;
    $enable_zip_search = (esc_js($a['enable_zip_search']) === "1") ? true : false;
    $scriptid = uniqid("table-data");

    $ffColumns['name']['head'] = '<th data-type="html" title="'.__('Name der Community').'">'.__('Name').'</th>'.PHP_EOL;
    $ffColumns['name']['js'] = '<td ><% if (item.url) {%>
                        <a href="<%= item.url%>" target="_blank"><%= item.name %></a>
                <% } else { %>
                           <%= item.name  %>
                   <%  } %></td>';
    $ffColumns['city']['head'] = '<th id="hcity" title="'.__('Stadt').'" data-sorted="true" data-direction="ASC">'.__('Stadt/Region').'</th>'.PHP_EOL;
    $ffColumns['city']['js'] = '<% if (item.location.city) {%>
                <td><%= item.location.city %>
                <% } else { %>
                <td>
                <% } %>
                </td>';
    $ffColumns['firmware']['head'] = '<th data-breakpoints="xs" title="'.__('Benutzte Firmware').'">'.__('Firmware').'</th>'.PHP_EOL;
    $ffColumns['firmware']['js'] = '<% if (item.techDetails.firmware && item.techDetails.firmware.name) {%>
                <td><%= item.techDetails.firmware.name %>
                <% } else { %>
                <td>
                <% } %>
                </td>';
    $ffColumns['routing']['head'] = '<th data-breakpoints="xs" title="'.__('Benutzte Routingprotokolle').'">'.__('Routing').'</th>'.PHP_EOL;
    $ffColumns['routing']['js'] = '<td><%= item.techDetails.routing %></td>';
    $ffColumns['distance']['head'] = '<th data-breakpoints="xs" id="hdistance" data-visible="false" data-type="number" title="'.__('Entfernung zum angegebenen Ort').'">'.__('Entfernung').'</th>'.PHP_EOL;
    $ffColumns['distance']['js'] = '<td class="cdistance" data-sort-value="<%= item.distance %>"><%= item.distance %> km</td>';
    $ffColumns['nodes']['head'] = '<th data-breakpoints="xs" title="'.__('Anzahl der Knoten').'" data-type="number">'.__('Knoten').'</th>'.PHP_EOL;
    $ffColumns['nodes']['js'] = '<td><%= item.state.nodes   %></td>';
    $ffColumns['contact']['head'] = '<th data-class="community-popup" data-type="html" data-breakpoints="xs" title="'.__('Wie kann man die Community kontaktieren?').'">'.__('Kontakt').'</th>'.PHP_EOL;
    $ffColumns['contact']['js'] = '<td><span class="community-popup"><ul class="contacts" style="width: <%- 7*(30+5)%>px;">
                <% _.each(item.contacts, function(contact, index, list) { %>
                        <li class="contact">
                          <a href="<%- contact.url %>" target="_window" class="contact-icon">
                            <span class="fa-stack fa-lg">
                              <i class="fa fa-square fa-stack-2x"></i>
                              <i class="fa fa-<%- contact.type %> fa-stack-1x fa-inverse" aria-hidden="true"></i>
                            </span>
                          </a>
                        </li>
                <% }); %>
              </ul></span></td>';

    $output = '<div id="' . $scriptid . 'communitytabelle">'.PHP_EOL;
    if ( $enable_zip_search) {
      $output .= '<h3>Communities in deiner Nähe</h3>'.PHP_EOL;
      $output .= '<div class="zipsearch"><input type="text" id="zipinput" placeholder="'.__('Ort, ggf. Postleitzahl').'"><button type="button" id="zipsubmit" class="btn waves-effect waves-light">Nächste Communities finden</button><button type="button" id="zipreset" class="btn waves-effect waves-light">Reset</button></div>'.PHP_EOL;
      $output .= '<div id="zipresult"></div>'.PHP_EOL;
    }
    $output .= '  <h3>Übersicht</h3>'.PHP_EOL;
    $output .= '  <table id="ctable" data-sorting="true" class="footable community-table">'.PHP_EOL;
    $output .= '  <thead>'.PHP_EOL;
    $output .= '  <tr>'.PHP_EOL;
    foreach($columns as $column) {
      $output .= $ffColumns[$column]['head'];
    }
    $output .= '  </tr>'.PHP_EOL;
    $output .= '  </thead>'.PHP_EOL;
    $output .= '  <tbody>'.PHP_EOL;
    $output .= '  </tbody>'.PHP_EOL;
    $output .= '  </table>'.PHP_EOL;
    $output .= '</div>'.PHP_EOL;
    $output .= '<script type="text/template" class="template" id="'.$scriptid.'">'.PHP_EOL;

    $output .= '<% _.each(items,function(item,key,list){ %> })'.PHP_EOL;
    $output .= '<tr>';
    foreach($columns as $column) {
      $output .= $ffColumns[$column]['js'];
    }

    $output .= '</tr>'.PHP_EOL;
    $output .= '<% }) %>'.PHP_EOL;
    $output .= '</script>'.PHP_EOL;

    $output .= '<script  type="text/javascript">'.PHP_EOL;
    $output .= '            var cTable;'.PHP_EOL;
    $output .= '            jQuery(document).ready(function(){'.PHP_EOL;
    $output .= '              cTable = FFCTABLE.init("'. $scriptid .'","'. $summaryUrl .'", "' . $nominatim_email . '", "'. $number_communities .'");'.PHP_EOL;
    $output .= '              cTable.getData(cTable.printTable);'.PHP_EOL;
    if ( $enable_zip_search) {
      $output .= '              jQuery("#zipinput").bind("enterKey",function(e){'.PHP_EOL;
      $output .= '                cTable.getDistanceByZip(cTable, cTable.printTable);'.PHP_EOL;
      $output .= '              });'.PHP_EOL;
      $output .= '              jQuery("#zipinput").keyup(function(e){'.PHP_EOL;
      $output .= '                    if(e.keyCode == 13)'.PHP_EOL;
      $output .= '                    {'.PHP_EOL;
      $output .= '                              jQuery(this).trigger("enterKey");'.PHP_EOL;
      $output .= '                    }'.PHP_EOL;
      $output .= '              });'.PHP_EOL;
      $output .= '              jQuery("#zipsubmit").click(function(e) {cTable.getDistanceByZip(cTable, cTable.printTable);});'.PHP_EOL;
      $output .= '              jQuery("#zipreset").click(function(e) {cTable.reset(cTable, cTable.printTable);});'.PHP_EOL;
    }
		$output .= '});'.PHP_EOL;
		$output .= '        </script> '.PHP_EOL;

    return $output;
}

function ffapijs($atts) {
    wp_enqueue_script("underscore", $in_footer = false);
    wp_enqueue_script("ffctable", plugin_dir_url( __FILE__ ). "js/ffctable.js");
}

add_shortcode("ffcommunitymap", "ffcommunitymap");
add_shortcode("ffapijs", "ffapijs");
add_shortcode("ffcommunitytable", "ffcommunitytable");
