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
    'orderBy' => 'openAmount',
    'sort' => 'desc'
  ), $atts ) ) ;

    $ffapi = new ffapi("http://freifunk.net/map/ffSummarizedDir.json");
    $campaigns = $ffapi->getValues("support.donations.campaigns");
    $bpProjects = array();

    $campaigns = array_unique($campaigns, SORT_REGULAR);
    foreach($campaigns as $name => $projects) {
        foreach ($projects as $project) {
            if ($project['provider'] = "betterplace") {
                $bp = new bpProject($project['projectid'], $name);
                array_push($bpProjects, $bp->getProjectArray());
            }
        }
    }


    usort($bpProjects, function($a, $b) use ($orderBy) {
        return $a[$orderBy] - $b[$orderBy];
    });
    if ($sort == "desc") {
        $bpProjects = array_reverse($bpProjects);
    }


    ?>
<table>
<thead>
  <th>Projekt</th>
  <th>Offener Betrag</th>
  <th>Anzahl Bedarfe</th>
  <th>Fortschritt</th>
  <th>Spenden</th>
</thead>

<?php
  foreach($bpProjects as $bpProject) {
    echo "<tr>";
    echo "<td>";
    echo "<a href=\"#". $bpProject['key'] ."\">";
    echo "<img src=\"" . $bpProject['projectImage'] . "\" title=\"" . $bpProject['projectTitle'] . "\" height=\"50px\" />";
    echo "</a>";
    echo "</td>";
    echo "<td>" . $bpProject['openAmount']/100 ." €</td>";
    echo "<td>" . $bpProject['incompleteNeed'] . "</td>";
    echo "<td width=50%>" . do_shortcode("[wppb progress=" . $bpProject['progress']. " fullwidth=false option=flat location=inside color=#dc0067]") . "</td>";
      echo "<td><a href=\"". $bpProject['projectLink']  ."\" target=\"_blank\">". $bpProject['projectTitle'] . "</a></td>";
    echo "</tr>";
  }
?>
</table>
<?php
}

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
