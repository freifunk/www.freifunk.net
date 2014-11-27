<?php
/*
  Plugin Name: Betterplace Projects Table
  Plugin URI: https://github.com/freifunk/www.freifunk.net
  Description: creates a table of given betterplace donation projects
  Version: 1.2.0
  Author: Andreas Bräu
  Author URI: http://andi95.de
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

include_once("bpt/DonationFactory.php");
include_once("bpt/class.ffapi.php");
include_once("bpt/class.bpproject.php");


function betterplaceprojecttable($atts) {
  extract(shortcode_atts( array(
    'orderBy' => 'openAmount',
    'sort' => 'desc'
  ), $atts ) ) ;

    $ffapi = new ffapi(get_option('ffapi_summarized_dir'));
    $df = new DonationFactory();
    $campaigns = $ffapi->getValues("support.donations.campaigns");
    $bpProjects = array();

    $campaigns = array_unique($campaigns, SORT_REGULAR);
    foreach($campaigns as $name => $projects) {
        foreach ($projects as $project) {
            if ( false === ( $bp = get_transient( $project['provider'].$project['projectid'] ) ) ) {
                $bp = $df->getDonationClass($project['provider'], $project['projectid'], $name);
                set_transient( $project['provider'].$project['projectid'], $bp, HOUR_IN_SECONDS );
            }
            array_push($bpProjects, $bp->getProjectArray());
        }
    }


    usort($bpProjects, function($a, $b) use ($orderBy) {
        return $a[$orderBy] - $b[$orderBy];
    });
    if ($sort == "desc") {
        $bpProjects = array_reverse($bpProjects);
    }


    ?>
<div class="betterplace-table">
<table>
<thead>
  <th>Projektname</th>
  <th>Organisation</th>
  <th>Offener Betrag</th>
  <th>Bedarfe</th>
  <th>Spender</th>
  <th>Fortschritt</th>
  <th>Spendenlink</th>
</thead>

<?php
  foreach($bpProjects as $bpProject) {
    echo "<tr>";
    echo "<td><a href=\"#". $bpProject['key'] ."\">".$bpProject['projectTitle']."</a></td>";
    echo "<td>" . $bpProject['organization'] . "</td>";
    echo "<td>" . $bpProject['openAmount']/100 ." €</td>";
    echo "<td>" . $bpProject['incompleteNeed'] . "</td>";
    echo "<td>" . $bpProject['donors'] . "</td>";
    echo "<td>" . do_shortcode("[wppb progress=" . $bpProject['progress']. " fullwidth=false option=flat location=inside color=#dc0067]") . "</td>";
      echo "<td><a href=\"". $bpProject['projectLink']  ."\" target=\"_blank\">direkt spenden...</a></td>";
    echo "</tr>";
  }
?>
</table>
</div>
<?php
}

add_option('ffapi_summarized_dir', "http://freifunk.net/map/ffSummarizedDir.json");
add_option('http_timeout', 2);

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
