<?php
/*
Plugin Name: RSS in Page
Plugin URI: http://www.titusbicknell.com/wordpress/rss-in-page
Description: short code [rssinpage rssfeed='feedURL'] generates a list of RSS feed items with date, title, link and description; now support muiltiple feed URLs separated by a comma e.g. rssfeed='feedurl1, feedurl2' - optional attributes: rssitems='number' sets the number of items to return, default is 5; rssorder='asc' returns items from oldest to newest, default is 'desc' which returns items from newest to oldest; rsstarget allows for feed link to be opened in new window by setting to '_blank', default is '_self'; rssdateformat allows item date to be formatted using php date parameters, default is RFC 2822 formatted date; rssdescription='no' suppress description display, default is to show it; rsscss allows you to set a css class to style the list, default is the content list style in the theme; rssformat allows you to format the output using format parameters e.g. the default x: Y - w&lt;br&gt;z where x is the date, Y is the title with a link, y is the title, z is the description and w is the feed title; rsstitlelength allows you to truncate the title to a certain character length; rssdescriptionlength allows you to truncate the description to a certain character length; rsstimezone allows you to set the timezone in which the feed datetime will be displayed, default is UTC.
Version: 2.9.1	
Author: Titus Bicknell
Author URI: http://titusbicknell.com
*/

function rssinpage($atts) {
extract(shortcode_atts( array(
'rssfeed' => 'no feed',
'rssitems' => '5',
'rssorder' => 'desc',
'rssdateformat' => 'r',
'rsstarget' => '_self',
'rssdescription' => 'yes',
'rssdescriptionlength' => 'all',
'rsstitlelength' => 'all',
'rssformat' => 'x: Y - w<br />z<br /> a<br />',
'rsscss' => '',
'rsstimezone' => 'UTC',
'rssdescriptionreadmore' => 'read more'
    ), $atts ) );
$rsscachelocation = WP_CONTENT_DIR.'/cache'; 
if (!file_exists($rsscachelocation)) {
mkdir($rsscachelocation, 0777);
}
if ($rssfeed != 'no feed') { 

$rssformatsplit = str_split(html_entity_decode($rssformat));
$rssformatdef = array('p','w','x','y','Y','z','a');
$rssfeedarray = explode(",",$rssfeed);
foreach ($rssfeedarray as &$feedurl) {
$feedurl = trim($feedurl);
$rss_urlcheck = stripos($feedurl, 'http');
if ($rss_urlcheck !== 0) { $feedurl = 'http://'.$feedurl; }
$feedurl = (html_entity_decode ($feedurl)); 
}

require_once (ABSPATH . WPINC . '/class-feed.php');
date_default_timezone_set($rsstimezone);
$feed = new SimplePie();
$feed->set_feed_url($rssfeedarray);
$feed->set_cache_location($rsscachelocation);
$feed->set_cache_duration('60');
if ($rssorder == 'none') { $feed->enable_order_by_date(false); }
$feed->init();
$feed->handle_content_type();
$rss = $feed;
$maxitems = $rss->get_item_quantity(50);
if ($maxitems != 0) {
$rss_items = $rss->get_items(0, $maxitems);
if ($rssitems > $maxitems) $rssitems = $maxitems;
if ($rssorder == 'asc') $rss_items = array_reverse($rss_items);
$i=0;
while ($i < $rssitems) {
$rsstitle = $rss_items[$i]->get_title();
$w = $rss_items[$i]->get_feed()->get_title();
if ($rss_items[$i]->get_date()) $x = $rss_items[$i]->get_date($rssdateformat);
$rsslinketitle = $rsstitle;
if ($rsstitlelength != 'all') { 
if(strlen($rsstitle) > $rsstitlelength) { $rsstitle = substr($rsstitle, 0, $rsstitlelength).'... '; }
}
$y = $rsstitle;

if ($rss_items[$i]->get_permalink() != '') {
$rss_itemlink = $rss_items[$i]->get_permalink();
$rss_itemlinkstart = strrpos($rss_itemlink, "http://");
$rss_itemlink = substr($rss_itemlink, $rss_itemlinkstart);
$Y = '<a class="test" href="'.$rss_itemlink.'" target="'.$rsstarget.'" title="'.$rsslinketitle.'">'.$rsstitle.'</a>'; } else { $Y = $rsstitle; }
if ($rss_items[$i]->get_description() != '') $z = $rss_items[$i]->get_description();
if ($rssdescriptionlength != 'all') { 
if(strlen($z) > $rssdescriptionlength) { $z = substr($z, 0, $rssdescriptionlength).'... '; }
if ($rssdescriptionreadmore) { $z .= '<a href="'.$rss_itemlink.'" target="'.$rsstarget.'" title="'.$rsslinketitle.'">'.$rssdescriptionreadmore.'</a>'; }

}
if ($rssdescription == 'no') $z = '';

if ($enclosure = $rss_items[$i]->get_enclosure()) {
$p = '<img src="'.$enclosure->get_thumbnail().'" />';
if (strstr($enclosure->get_type(), 'audio')) {
$a = do_shortcode( '[podloveaudio title="'. $rsstitle .'" subtitle="'. $z .'" src="'.$enclosure->get_link().'"]');
//$a =$enclosure->get_link();
}
	}
foreach ($rssformatsplit as $v) { if (in_array($v, $rssformatdef)) { $v = ${$v}; }
$rssformatoutput = $rssformatoutput.$v;
}

$rssreturn = $rssreturn.'<li>'.$rssformatoutput.'</li>';
unset($rssformatoutput, $p, $w, $x, $Y, $y, $z, $a);
$i++;
}	
if (!empty($rsscss)) { $rssipul = '<ul class="'.$rsscss.'">'; } else { $rssipul = '<ul>'; }
return $rssipul.$rssreturn.'</ul>';
}
}	
}

add_shortcode("rssinpage", "rssinpage");
?>