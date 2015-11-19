<?php

/*
Plugin Name: Ffcommunitymap
Plugin URI: http://api.freifunk.net
Description: display the freifunk community map
Version: 2.1
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


function ffcommunitymap($atts)
{
    $a = shortcode_atts(array(
        'feed_url' => '//api.freifunk.net/feed/feed.php',
        'geojsonurl'=> '//api.freifunk.net/map/ffGeoJsonp.php?callback=?',
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
        'center' => '51.5,10.5',
        'height' => null,
        'width' => null
    ), $atts);

    wp_enqueue_style("cssleaflet", "//api.freifunk.net/map/external/leaflet/leaflet.css");
    wp_enqueue_style("cssleafletmc", "//api.freifunk.net/map/external/leaflet/MarkerCluster.css");
    wp_enqueue_style("cssleafletmcd", "//api.freifunk.net/map/external/leaflet/MarkerCluster.Default.css");
    wp_enqueue_style("cssleafletbutton", "//api.freifunk.net/map/external/leaflet/leaflet-button-control.css");
    wp_enqueue_style("cssscrollbar", "//api.freifunk.net/timeline/malihu-scrollbar/jquery.mCustomScrollbar.min.css");
    wp_enqueue_style("csstimeline", "//api.freifunk.net/timeline/timeline.css");
    wp_enqueue_style("csstlcustom", "//api.freifunk.net/timeline/custom.css");
    wp_enqueue_style("csscommunitymap", "//api.freifunk.net/map/community_map.css");
    wp_enqueue_style("mystyles", plugin_dir_url( __FILE__ ). "/css/ffcommunitymap.css");
    wp_enqueue_script("underscore");
    wp_enqueue_script("communitymap", "//api.freifunk.net/map/community_map.js");
    wp_enqueue_script("leaflet", "//api.freifunk.net/map/external/leaflet/leaflet.js");
    wp_enqueue_script("leaflet-button-control", "//api.freifunk.net/map/external/leaflet/leaflet-button-control.js");
    wp_enqueue_script("leafletmc", "//api.freifunk.net/map/external/leaflet/leaflet.markercluster.js");
    wp_enqueue_script("scrollbar", "//api.freifunk.net/timeline/malihu-scrollbar/jquery.mCustomScrollbar.concat.min.js");
    wp_enqueue_script("timeline", "//api.freifunk.net/timeline/timeline.js");
    
    
    $feedUrl = esc_url($a['feed_url']);
    $geoJsonUrl = esc_url($a['geojsonurl']);
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
    $center = preg_match("/^\d[0-9\.]{1,},\d[0-9\.]{1,}$/", $a['center']) === 1 ? $a['center'] : "51.5,10.5";
    $height = preg_match("/^\d+(px|%)$/", $a['height']) === 1 ? $a['height'] : null;
    $width = preg_match("/^\d+(px|%)$/", $a['width']) === 1 ? $a['width'] : null;

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
    $output .= '<script type="text/template" class="template" id="community-popup">';
    $output .= '<div class="community-popup" data-id="<%- props.shortname %>">'.PHP_EOL;
    $output .= '<% if ( props.name ) { %>'.PHP_EOL;
    $output .= '<h2><a href="<%- props.url %>" target="_window"><%- props.name %></a></h2>'.PHP_EOL;
    $output .= '<% } %>'.PHP_EOL;
    $output .= '<% if (props.metacommunity) { %>'.PHP_EOL;
    $output .= '<h3><%- props.metacommunity %></h3>'.PHP_EOL;
    $output .= '<% } %>'.PHP_EOL;
    $output .= '<% if (props.city) { %>'.PHP_EOL;
    $output .= '<div class="city"><%- props.city  %></div>'.PHP_EOL;
    $output .= '<% } %>'.PHP_EOL;
    $output .= '<% if (props.nodes) { %>'.PHP_EOL;
    $output .= '<div class="nodes">Zug&auml;nge: <%- props.nodes  %>'.PHP_EOL;
    $output .= '<% if (props.state && props.age) { %>'.PHP_EOL;
    $output .= '<span class="state <%- props.state  %>" title="Die letzte Aktualisierung der Daten war vor <%- props.age  %> Tagen">(<%- props.state  %>)</span>'.PHP_EOL;
    $output .= ' <% } %>'.PHP_EOL;
    $output .= '</div>'.PHP_EOL;
    $output .= '<% } %>'.PHP_EOL;
    $output .= '<% if (props.phone) { %>'.PHP_EOL;
    $output .= '<div class="phone">&#9742; <%- props.phone  %></div>'.PHP_EOL;
    $output .= '<% } %>'.PHP_EOL;
    $output .= '<ul class="contacts" style="height:<%- Math.round(props.contacts.length/6+0.4)*30+10 %>px; width: <%- 6*(30+5)%>px;">'.PHP_EOL;
    $output .= '<% _.each(props.contacts, function(contact, index, list) { %>'.PHP_EOL;
    $output .= '<li class="contact">'.PHP_EOL;
    $output .= '<a href="<%- contact.url %>" class="button <%- contact.type %>" target="_window"></a>'.PHP_EOL;
    $output .= '</li>'.PHP_EOL;
    $output .= '<% }); %>'.PHP_EOL;
    $output .= '</ul>'.PHP_EOL;
    $output .= '<div class="events">'.PHP_EOL;
    $output .= '</div>'.PHP_EOL;
    $output .= '</div>'.PHP_EOL;
    $output .= '</script>'.PHP_EOL;

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
      'columns' => 'city,name,firmware,routing,nodes,contact'
    ), $atts);

    wp_enqueue_script("underscore", $in_footer = false);
    wp_enqueue_script("footable", plugin_dir_url( __FILE__ ). "js/footable.js");
    wp_enqueue_script("footablesort", plugin_dir_url( __FILE__ ). "js/footable.sort.js");
    wp_enqueue_script("communitymap", "//api.freifunk.net/map/community_map.js");
    wp_enqueue_style("mystyles", plugin_dir_url( __FILE__ ). "css/ffcommunitymap.css");
    wp_enqueue_style("cssfootablecore", plugin_dir_url( __FILE__ ). "css/footable.core.css");
    $summaryUrl = esc_url($a['summaryurl']);
    $columns = preg_match("/^[a-z,]*$/", $a['columns']) === 1 ? explode(',', $a['columns']) : explode(',', 'name,city');
    $scriptid = uniqid("table-data");

    $ffColumns['name']['head'] = '<th title="'.__('Name der Community').'">'.__('Name').'</th>'.PHP_EOL;
    $ffColumns['name']['js'] = '<td ><% if (item.url) {%>
                        <a href="<%= item.url%>" target="_blank"><%= item.name %>
                <% } else { %>
                           <%= item.name  %>
                   <%  } %></td>';
    $ffColumns['city']['head'] = '<th title="'.__('Stadt').'" data-sort-initial="true">'.__('Stadt/Region').'</th>'.PHP_EOL; 
    $ffColumns['city']['js'] = '<% if (item.location.city) {%>
                <td><%= item.location.city %>
                </a><% } else { %>
                <td>
                <% } %>
                </td>';
    $ffColumns['firmware']['head'] = '<th data-hide="phone" title="'.__('Benutzte Firmware').'">'.__('Firmware').'</th>'.PHP_EOL; 
    $ffColumns['firmware']['js'] = '<% if (item.techDetails.firmware && item.techDetails.firmware.name) {%>
                <td><%= item.techDetails.firmware.name %>
                <% } else { %>
                <td>
                <% } %>
                </td>';
    $ffColumns['routing']['head'] = '<th data-hide="phone" title="'.__('Benutzte Routingprotokolle').'">'.__('Routing').'</th>'.PHP_EOL;
    $ffColumns['routing']['js'] = '<td><%= item.techDetails.routing %></td>';
    $ffColumns['nodes']['head'] = '<th data-hide="phone" title="'.__('Anzahl der Knoten').'" data-type="numeric">'.__('Knoten').'</th>'.PHP_EOL;
    $ffColumns['nodes']['js'] = '<td><%= item.state.nodes   %></td>';
    $ffColumns['contact']['head'] = '<th data-class="community-popup" data-hide="phone" title="'.__('Wie kann man die Community kontaktieren?').'">'.__('Kontakt').'</th>'.PHP_EOL;
    $ffColumns['contact']['js'] = '<td><span class="community-popup"><ul class="contacts" style="height:<%- Math.round(_.size(item.contact)/6+0.4)*30+10  %>px; width: <%- 6*(30+5)%>px;">
                <% _.each(item.contact, function(contact, index, list) { %>
                        <li class="contact">
                        <a href="<%- contact %>" class="button <%- index %>" target="_window"></a>
                        </li>
                <% }); %>
              </ul><span></td>';

    $output = '<div id="1communitytabelle">'.PHP_EOL;
    $output .= '  <table id="ctable" class="sortable footable toggle-arrow-tiny community-table">'.PHP_EOL;
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
    $output .= '            var tableTemplate = jQuery("script.template#'.$scriptid.'").html();'.PHP_EOL;
    $output .= '            jQuery(document).ready(function(){'.PHP_EOL;
    $output .= '                    var url = "'.$summaryUrl.'";'.PHP_EOL;
    $output .= '                    jQuery.ajax({'.PHP_EOL;
		$output .= 'url: url,'.PHP_EOL;
		$output .= 'dataType: "jsonp",'.PHP_EOL;
		$output .= 'success: ( function(Response){'.PHP_EOL;
		$output .= '        var rows = Response;'.PHP_EOL;
		$output .= '        rows = _.sortBy(rows, function(o){ return o.location.city;});'.PHP_EOL;
		$output .= '        _.each(rows, function(item, key, list) {'.PHP_EOL;
		$output .= '                if (item.url && !item.url.match(/^http([s]?):\/\/.*/)) {'.PHP_EOL;
		$output .= '                        item.url = "http://" + item.url;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.ml && !item.contact.ml.match(/^mailto:.*/) && item.contact.ml.match(/.*\@.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.ml = "mailto:" + item.contact.ml;'.PHP_EOL;
		$output .= '                } else if (item.contact.ml && !item.contact.ml.match(/^http([s]?):\/\/.*/) ) {'.PHP_EOL;
		$output .= '                        item.contact.ml = "http://" + item.contact.ml;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.email && !item.contact.email.match(/^mailto:.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.email = "mailto:" + item.contact.email;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.twitter && !item.contact.twitter.match(/^http([s]?):\/\/.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.twitter = "https://twitter.com/" + item.contact.twitter;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.irc && !item.contact.irc.match(/^irc:.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.irc = "irc:" + item.contact.irc;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.jabber && !item.contact.jabber.match(/^jabber:.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.jabber = "xmpp:" + item.contact.jabber;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
		$output .= '                if (item.contact.identica && !item.contact.identica.match(/^identica:.*/)) {'.PHP_EOL;
		$output .= '                        item.contact.identica = "identica:" + item.contact.identica;'.PHP_EOL;
		$output .= '                }'.PHP_EOL;
    $output .= '        });'.PHP_EOL;
    $output .= '        _.templateSettings.variable = "items";'.PHP_EOL;
    $output .= '        var templ = _.template(tableTemplate);'.PHP_EOL;
		$output .= '        jQuery("table.community-table tbody").html(templ(rows));'.PHP_EOL;
		$output .= '        jQuery("#ctable").footable();'.PHP_EOL;
		$output .= '        } ),'.PHP_EOL;
		$output .= 'error: function(XMLHttpRequest, textStatus, errorThrown){alert("Error");'.PHP_EOL;
		$output .= '}'.PHP_EOL;
		$output .= '});'.PHP_EOL;
		$output .= '});'.PHP_EOL;
		$output .= '        </script> '.PHP_EOL;

    return $output;
}


add_shortcode("ffcommunitymap", "ffcommunitymap");
add_shortcode("ffcommunitytable", "ffcommunitytable");

