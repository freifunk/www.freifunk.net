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

include_once("bpt/class.ffapi.php");
include_once("bpt/class.bpproject.php");

function betterplaceprojecttable($atts) {
  extract(shortcode_atts( array(
    'projects' => 'no_project',
    'bpApiUrl' => 'https://api.betterplace.org/de/api_v4/projects/',
    'orderBy' => 'openAmount',
    'sort' => 'asc'
  ), $atts ) ) ;

    $ffapi = new ffapi("http://freifunk.net/map/ffSummarizedDir.json");
    $campaigns = $ffapi->getValues("support.donations.campaigns");
    $bpProjects = array();

    foreach($campaigns as $projects) {
        foreach ($projects as $project) {
            if ($project['provider'] = "betterplace") {
                $bp = new bpProject($project['projectid']);
                array_push($bpProjects, $bp->getProjectArray());
            }
        }
    }

    print_r($campaigns);

    usort($bpProjects, function($a, $b) {
        return $a[$orderBy] - $b[$orderBy];
    });
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
  foreach($bpProjects as $bpProject) {
    echo "<tr>";
    echo "<td>";
    echo "<a href=\"#". "test" ."\">";
    echo "<img src=\"" . $bpProject['projectImage'] . "\" alt=\"" . $bpProject['projectTitle'] . "\" height=\"50px\" />";
    echo "</a>";
    echo "</td>";
    echo "<td><a href=\"". $bpProject['projectLink']  ."\" target=\"_blank\">". $bpProject['projectTitle'] . "</a></td>";
    echo "<td>" . $bpProject['openAmount']/100 ." €</td>";
    echo "<td>" . $bpProject['incompletedNeed'] . "</td>";
    echo "<td>" . $bpProject['progress'] . " %</td>";
    echo "<td>" . $bpProject['donors'] . "</td>";
    echo "<td><a href=\"" . $bpProject['donationLink']. "\" target=\"_blank\">Direkt spenden...</a></td>";
    echo "</tr>";
  }
?>
</table>
<?php
}

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
