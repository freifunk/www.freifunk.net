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

include_once("bpt/DonationFactory.php");
include_once("bpt/class.ffapi.php");
include_once("bpt/class.bpproject.php");


function betterplaceprojecttable($atts) {
  extract(shortcode_atts( array(
    'orderBy' => 'openAmount',
    'sort' => 'desc'
  ), $atts ) ) ;

    $ffapi = new ffapi("http://freifunk.net/map/ffSummarizedDir.json");
    $df = new DonationFactory();
    $communityCampaigns = $ffapi->getValues("support.donations.campaigns");
    $donationProjects = array();

    $communityCampaigns = array_unique($communityCampaigns, SORT_REGULAR);
    foreach($communityCampaigns as $communityName => $projectsPerCommunity) {
        foreach ($projectsPerCommunity as $singleCommunityProject) {
            $donationProject = $df->getDonationClass($singleCommunityProject['provider'], $singleCommunityProject['projectid'], $communityName);
            array_push($donationProjects, $donationProject->getProjectArray());
        }
    }


    usort($donationProjects, function($a, $b) use ($orderBy) {
        return $a[$orderBy] - $b[$orderBy];
    });
    if ($sort == "desc") {
        $donationProjects = array_reverse($donationProjects);
    }


    ?>
<div class="betterplace-table">
<table>
<thead>
  <th>Projekt</th>
  <th>Offener Betrag</th>
  <th>Bedarfe</th>
  <th>Fortschritt</th>
  <th>Spendenlink</th>
</thead>

<?php
  foreach($donationProjects as $singleProject) {
    echo "<tr>";
    echo "<td>";
    echo "<a href=\"#". $singleProject['key'] ."\">";
    echo "<img src=\"" . $singleProject['projectImage'] . "\" title=\"" . $singleProject['projectTitle'] . "\" height=\"50px\" />";
    echo "</a>";
    echo "</td>";
    echo "<td>" . $singleProject['openAmount']/100 ." €</td>";
    echo "<td>" . $singleProject['incompleteNeed'] . "</td>";
    echo "<td>" . do_shortcode("[wppb progress=" . $singleProject['progress']. " fullwidth=false option=flat location=inside color=#dc0067]") . "</td>";
      echo "<td><a href=\"". $singleProject['projectLink']  ."\" target=\"_blank\">". $singleProject['projectTitle'] . "</a></td>";
    echo "</tr>";
  }
?>
</table>
</div>
<?php
}

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
