<?php
/*
  Plugin Name: Betterplace Projects Table
  Plugin URI: https://github.com/freifunk/www.freifunk.net
  Description: creates a table of given betterplace donation projects
  Version: 1.0.0
  Author: Andreas Bräu
  Author URI: http://andi95.de
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function betterplaceprojecttable($atts) {
  extract(shortcode_atts( array(
    'projects' => 'no_project',
    'bpApiUrl' => 'https://api.betterplace.org/de/api_v4/projects/'
  ), $atts ) ) ;

  $bpProjects = explode(",", $projects);
?>
<table>
<thead>
  <th></th>
  <th>Projekt</th>
  <th>Noch offen</th>
  <th># Bedarfe</th>
  <th>% Erreicht</th>
  <th># Spenden</th>
  <th></th>
</thead>

<?php
  foreach($bpProjects as $projectStr) {
    $projectArray = explode("#", $projectStr);
    $project = $projectArray[0];
    $anchor = null;
    if ($projectArray[1]) {
      $anchor = $projectArray[1];
    }
    $prjDetails = file_get_contents($bpApiUrl . $project . ".json");
    $prjDetailsJson = json_decode($prjDetails, trule);
    foreach($prjDetailsJson['links'] as $links) {
      if ($links['rel'] == 'platform') {
        $prjLink = $links['href'];
      } elseif ($links['rel'] == 'new_donation' ) {
        $prjDonationLink = $links['href'];
      }
    }
    foreach($prjDetailsJson['profile_picture']['links'] as $pic) {
      if ($pic['rel'] == 'fill_270x141') {
        $prjPic = $pic['href'];
      }
    }
    echo "<tr>";
    echo "<td>";
    if ($anchor) {
      echo "<a href=\"#". $anchor ."\">";
    }
    echo "<img src=\"" . $prjPic . "\" alt=\"" . $prjDetailsJson['title'] . "\" height=\"50px\" />";
    if ($anchor) {
      echo "</a>";
    }
    echo "</td>";
    echo "<td><a href=\"". $prjLink  ."\" target=\"_blank\">". $prjDetailsJson['title'] . "</a></td>";
    echo "<td>" . $prjDetailsJson['open_amount_in_cents']/100 ." €</td>";
    echo "<td>" . $prjDetailsJson['incomplete_need_count'] . "</td>";
    echo "<td>" . $prjDetailsJson['progress_percentage'] . " %</td>";
    echo "<td>" . $prjDetailsJson['donor_count'] . "</td>";
    echo "<td><a href=\"" . $prjDonationLink . "\" target=\"_blank\">Direkt spenden...</a></td>";
    echo "</tr>";
  }
?>
</table>
<?php
}

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
