<?php

/*
Plugin Name: Ffcommunityml
Plugin URI: http://api.freifunk.net
Description: display the freifunk community mailinglists
Version: 1.0
Author: Philhil
Author URI: https://github.com/philhil
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


function ffcommunitymltable($atts)
{
    $a = shortcode_atts(array(
      'summaryurl'=> '//api.freifunk.net/map/ffApiJsonp.php?mode=summary&callback=?',
      'columns' => 'city,name,mailinglist'
    ), $atts);

    wp_enqueue_script("underscore", $in_footer = false);
    wp_enqueue_script("footable", plugin_dir_url( __FILE__ ). "js/footable.js");
    wp_enqueue_script("footablesort", plugin_dir_url( __FILE__ ). "js/footable.sort.js");
    wp_enqueue_style("cssfootablecore", plugin_dir_url( __FILE__ ). "css/footable.core.css");
    $summaryUrl = esc_url($a['summaryurl']);
    $columns = preg_match("/^[a-z,]*$/", $a['columns']) === 1 ? explode(',', $a['columns']) : explode(',', 'name,city');
    $scriptid = uniqid("table-ml-data");

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
    $ffColumns['mailinglist']['head'] = '<th data-class="community-popup" data-hide="phone" title="'.__('Mailingliste der Community').'">'.__('Mailingliste').'</th>'.PHP_EOL;
    $ffColumns['mailinglist']['js'] = '<% if (item.contact.ml) {%>
                <td> <%= item.contact.ml %>
                </a><% } else { %>
                <td>
                <% } %>
                </td>';

    $output = '<div id="mltabelle">'.PHP_EOL;
    $output .= '  <table id="mltable" class="sortable footable toggle-arrow-tiny ml-table">'.PHP_EOL;
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
    $output .= '        var mailinglists = Array();'.PHP_EOL;
	$output .= '        rows = _.sortBy(rows, function(o){ return o.location.city;});'.PHP_EOL;
	$output .= '        _.each(rows, function(item, key, list) {'.PHP_EOL;
	$output .= '           if (item.contact.ml) {'.PHP_EOL;
    $output .= '                if (item.url && !item.url.match(/^http([s]?):\/\/.*/)) {'.PHP_EOL;
	$output .= '                        item.url = "http://" + item.url;'.PHP_EOL;
	$output .= '                }'.PHP_EOL;
	$output .= '                if (!item.contact.ml.match(/^mailto:.*/) && item.contact.ml.match(/.*\@.*/)) {'.PHP_EOL;
    $output .= '                        item.contact.ml = "<a href=\"mailto:" + item.contact.ml + "\">"  + item.contact.ml + "</a>";'.PHP_EOL;
	$output .= '                } else if (!item.contact.ml.match(/^http([s]?):\/\/.*/) ) {'.PHP_EOL;
	$output .= '                        item.contact.ml = "<a href=\"http://" + item.contact.ml + "\" target=\"_window\">"  + item.contact.ml + "</a>";'.PHP_EOL;
	$output .= '                } else {'.PHP_EOL;
    $output .= '                        item.contact.ml = "<a href=\"" + item.contact.ml + "\" target=\"_window\">"  + item.contact.ml + "</a>";'.PHP_EOL;
    $output .= '                }'.PHP_EOL;
    $output .= '                mailinglists.push(item)'.PHP_EOL;
    $output .= '            }'.PHP_EOL;
    $output .= '        });'.PHP_EOL;
    $output .= '        _.templateSettings.variable = "items";'.PHP_EOL;
    $output .= '        var templ = _.template(tableTemplate);'.PHP_EOL;
	$output .= '        jQuery("table.ml-table tbody").html(templ(mailinglists));'.PHP_EOL;
	$output .= '        jQuery("#mltable").footable();'.PHP_EOL;
	$output .= '        } ),'.PHP_EOL;
	$output .= 'error: function(XMLHttpRequest, textStatus, errorThrown){alert("Error");'.PHP_EOL;
	$output .= '}'.PHP_EOL;
	$output .= '});'.PHP_EOL;
	$output .= '});'.PHP_EOL;
	$output .= '        </script> '.PHP_EOL;

    return $output;
}

add_shortcode("ffcommunitymltable", "ffcommunitymltable");
