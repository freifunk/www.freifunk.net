<?php
/*
  Plugin Name: Betterplace Projects Table
  Plugin URI: https://github.com/freifunk/www.freifunk.net
  Description: creates a table of given betterplace donation projects
  Version: 1.3.3
  Author: Andreas Bräu
  Author URI: http://blog.andi95.de
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

include_once("bpt/DonationFactory.php");
include_once("bpt/class.ffapi.php");
include_once("bpt/class.bpproject.php");


function betterplaceprojecttable($atts) {
  extract(shortcode_atts( array(
    'orderBy' => 'openAmount',
    'sort' => 'desc',
    'more_campaigns' => null
  ), $atts ) ) ;

    $ffapi = new ffapi(get_option('ffapi_summarized_dir'));
    $df = new DonationFactory();
    $campaigns = $ffapi->getValues("support.donations.campaigns");
    $bpProjects = array();
    $output = "";

    if (! empty($more_campaigns)) {
        $additionalCampaigns = explode(",", $more_campaigns);
        foreach($additionalCampaigns as $number=>$ac) {
            array_push($campaigns, array( $number => array("projectid"=>$ac, "provider" => "betterplace")));
        }
    }

    $campaigns = array_unique($campaigns, SORT_REGULAR);
    foreach($campaigns as $name => $projects) {
        foreach ($projects as $project) {
            if ( false === ( $bp = get_transient( $project['provider'].$project['projectid'] ) ) ) {
                $bp = $df->getDonationClass($project['provider'], $project['projectid'], $name);
                set_transient( $project['provider'].$project['projectid'], $bp, get_option('cache_timeout') );
            }
            array_push($bpProjects, $bp->getProjectArray());
        }
    }


    usort($bpProjects, function($a, $b) use ($orderBy) {
        return $a[$orderBy] - $b[$orderBy];
    });
    if ( ! empty($sort) && $sort == "desc") {
        $bpProjects = array_reverse($bpProjects);
    }
    wp_enqueue_script( 'sortable', get_template_directory_uri() . '/js/sorttable.js', array(), null, false );

    $output .= "<div class=\"betterplace-table\">";
    $output .= "<table class=\"sortable betterplace-table\">";
    $output .= "<thead>";
    $output .= "<th class=\"" . getSortedClass($orderBy, "projectTitle") ."\">Projekt/Träger" . getSortSign($orderBy, "projectTitle") ."</th>";
    $output .= "<th class=\"sorttable_numeric " . getSortedClass($orderBy, "incompleteNeed") . "\">Bedarfe" . getSortSign($orderBy, "incompleteNeed") ." </th>";
    $output .= "<th class=\"sorttable_numeric " . getSortedClass($orderBy, "completedNeed") . "\">Erfüllt" . getSortSign($orderBy, "completedNeed") ." </th>";
    $output .= "<th class=\"sorttable_numeric " . getSortedClass($orderBy, "donors") . "\">Spender" . getSortSign($orderBy, "donors") ." </th>";
    $output .= "<th class=\"sorttable_numeric " . getSortedClass($orderBy, "progress") . "\">Fortschritt" . getSortSign($orderBy, "progress") ." </th>";
    $output .= "<th class=\"sorttable_numeric " . getSortedClass($orderBy, "openAmount") . "\">Spenden" . getSortSign($orderBy, "openAmount")  ." </th>";
    $output .= "</thead>";

  foreach($bpProjects as $bpProject) {
    $output .= "<tr>";
    $output .= "<td class=\"organization\">".$bpProject['projectTitle']."<br/>";
    $output .= "<a href=\"#". $bpProject['organization'] ."\">" . $bpProject['organization'] . "</a></td>";
    $output .= "<td class=\"numeric\">" . $bpProject['incompleteNeed'] . "</td>";
    $output .= "<td class=\"numeric\">" . $bpProject['completedNeed'] . "</td>";
    $output .= "<td class=\"numeric\">" . $bpProject['donors'] . "</td>";
    $output .= "<td class=\"progress\" sorttable_customkey='".$bpProject['progress']."'>" . do_shortcode("[wppb progress=" . $bpProject['progress']. " fullwidth=false option=flat location=inside color=#009ee0]") . "</td>";
    $output .= "<td class=\"donor\" sorttable_customkey='".$bpProject['openAmount']."'>Es fehlen noch ".round($bpProject['openAmount']/100). " €<a href=\"". $bpProject['projectLink']  ."\" target=\"_blank\"><button>Jetzt spenden!</button></a></td>";
    $output .= "</tr>";
  }

$output .= "</table>";
$output .= "</div>";
return $output;
}

function getSortSign($orderBy, $column) {
    if ($orderBy == $column) {
        return "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";
    } else {
        return "";
    }
}

function getSortedClass($orderBy, $column) {
    if ($orderBy == $column) {
        return "sorttable_sorted";
    } else {
        return "";
    }
}




add_option('ffapi_summarized_dir', "http://freifunk.net/map/ffSummarizedDir.json");
add_option('http_timeout', 2);
add_option('cache_timeout', 1 * HOUR_IN_SECONDS);

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
