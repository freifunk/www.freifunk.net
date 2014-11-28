<?php
/*
  Plugin Name: Betterplace Projects Table
  Plugin URI: https://github.com/freifunk/www.freifunk.net
  Description: creates a table of given betterplace donation projects
  Version: 1.3.1
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
    if ($sort == "desc") {
        $bpProjects = array_reverse($bpProjects);
    }
    wp_enqueue_script( 'sortable', get_template_directory_uri() . '/js/sorttable.js', array(), null, false );


    ?>
<div class="betterplace-table">
<table class="sortable betterplace-table">
<thead>
  <th <?php if ($orderBy == "projectTitle") {echo "class='sorttable_sorted'";}?>>Projekt/Träger<?php if ($orderBy == "projectTitle") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "incompleteNeed") {echo " sorttable_sorted";}?>">Offene Bedarfe<?php if ($orderBy == "incompleteNeed") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "completedNeed") {echo " sorttable_sorted";}?>">Erfüllt<?php if ($orderBy == "completedNeed") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "donors") {echo " sorttable_sorted";}?>">Spender<?php if ($orderBy == "donors") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "progress") {echo " sorttable_sorted";}?>">Fortschritt<?php if ($orderBy == "progress") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_nosort">Spenden</th>
</thead>

<?php
  foreach($bpProjects as $bpProject) {
    echo "<tr>";
    echo "<td>".$bpProject['projectTitle']."<br/>";
    echo "<a href=\"#". $bpProject['organization'] ."\">" . $bpProject['organization'] . "</a></td>";
    echo "<td>" . $bpProject['incompleteNeed'] . "</td>";
    echo "<td>" . $bpProject['completedNeed'] . "</td>";
    echo "<td>" . $bpProject['donors'] . "</td>";
    echo "<td sorttable_customkey='".$bpProject['progress']."'>" . do_shortcode("[wppb progress=" . $bpProject['progress']. " fullwidth=false option=flat location=inside color=#009ee0]") . "</td>";
      echo "<td>Es fehlen noch ".round($bpProject['openAmount']/100). " €<a href=\"". $bpProject['projectLink']  ."\" target=\"_blank\"><button>Jetzt spenden!</button></a></td>";
    echo "</tr>";
  }
?>
</table>
</div>
<?php
}

add_option('ffapi_summarized_dir', "http://freifunk.net/map/ffSummarizedDir.json");
add_option('http_timeout', 2);
add_option('cache_timeout', 1 * HOUR_IN_SECONDS);

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
