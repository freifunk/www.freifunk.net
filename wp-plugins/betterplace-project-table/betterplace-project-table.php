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
    print(get_template_directory_uri());
    wp_enqueue_script( 'sortable', get_template_directory_uri() . '/js/sorttable.js', array(), null, false );


    ?>
<div class="betterplace-table">
<table class="sortable betterplace-table">
<thead>
  <th <?php if ($orderBy == "projectTitle") {echo "class='sorttable_sorted'";}?>>Projektname<?php if ($orderBy == "projectTitle") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th <?php if ($orderBy == "organization") {echo "class='sorttable_sorted'";}?>>Organisation<?php if ($orderBy == "organization") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "openAmount") {echo " sorttable_sorted";}?>">Offener Betrag <?php if ($orderBy == "openAmount") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "incompleteNeed") {echo " sorttable_sorted";}?>">Bedarfe<?php if ($orderBy == "incompleteNeed") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "donors") {echo " sorttable_sorted";}?>">Spender<?php if ($orderBy == "donors") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_numeric<?php if ($orderBy == "progress") {echo " sorttable_sorted";}?>">Fortschritt<?php if ($orderBy == "progress") {echo "<span id='sorttable_sortfwdind'>&nbsp;▾</span>";}?></th>
  <th class="sorttable_nosort">Spendenlink</th>
</thead>

<?php
  foreach($bpProjects as $bpProject) {
    echo "<tr>";
    echo "<td><a href=\"#". $bpProject['key'] ."\">".$bpProject['projectTitle']."</a></td>";
    echo "<td>" . $bpProject['organization'] . "</td>";
    echo "<td>" . round($bpProject['openAmount']/100) ." €</td>";
    echo "<td>" . $bpProject['incompleteNeed'] . "</td>";
    echo "<td>" . $bpProject['donors'] . "</td>";
    echo "<td sorttable_customkey='".$bpProject['progress']."'>" . do_shortcode("[wppb progress=" . $bpProject['progress']. " fullwidth=false option=flat location=inside color=#dc0067]") . "</td>";
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
add_option('cache_timeout', 1 * HOUR_IN_SECONDS);

add_shortcode("bpprojecttable", "betterplaceprojecttable");
?>
