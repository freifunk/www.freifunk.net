<?php
    /*
    Plugin Name: time.ly Calendar Feed Importer
    Plugin URI: https://github.com/freifunk/www.freifunk.net/wp-plugins/time.ly-calendar-importer
    Description: Imports calendar feeds to time.ly retrieved by Freifunk API
    Author: A. Bräu
    Version: 1.0
    Author URI: http://www.andi95.de
    */

function tci_add_calendar($feed, $name) {
	global $wpdb;
	$result = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %s)", $name, $name, '0'
		)
	);
	if ( $result === false ) {
		return "error";
	}
	$result = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) select distinct a.term_id, 'events_categories', '', b.term_id, '0' from $wpdb->terms a join $wpdb->terms b on b.slug='community-events' where a.name=%s and a.slug=%s", $name, $name
		)
	);
	if ( $result === false ) {
		return "error";
	}
	$term_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT distinct term_id FROM $wpdb->terms where slug=%s", $name
		)
	);
	$result = $wpdb->query(
		$wpdb->prepare(
			"REPLACE INTO " . $wpdb->prefix . "ai1ec_event_feeds (feed_url, feed_category, feed_tags, comments_enabled, map_display_enabled, keep_tags_categories) VALUES (%s, %s, %s, '0', '0', '0')", $feed['url'], $term_id, $feed['communityname']
		)
	);
	if ( $result === false ) {
		return "error";
	} else {
		return "success";
	}

}

function tci_import_all_feeds() {
	$source_url = get_option('tci_source_url');
	$jsonfile = file_get_contents($source_url);
	$json = json_decode($jsonfile, true);
	foreach($json as $name => $calendar) {
		$result = tci_add_calendar($json[$name], $name);
	}
}

function tci_admin_manage() {
	$source_url = get_option('tci_source_url');
	$jsonfile = file_get_contents($source_url);
	$json = json_decode($jsonfile, true);
	if($_POST['tci_hidden'] == 'ALL') {
		foreach($json as $name => $calendar) {
			$result = tci_add_calendar($json[$name], $name);
			if ( $result == "success") {
				echo '<div class="updated"><p><strong>Einstellungen gespeichert für ' . $calendar['communityname'] . '</strong></p></div>';
			} else {
				echo '<div class="error"><p><strong>Probleme beim Speichern für ' . $calendar['communityname'] . '</strong></p></div>';
			}

		}
	}
	if($_POST['tci_hidden'] == 'ONE') {
		if ( $_POST['tci_community'] != '') {
			$name = $_POST['tci_community'];
			if ( ! ( $json[$name] == '' || $json[$name]['communityname'] == '' || $json[$name]['url'] == '' ) ) {
				$result = tci_add_calendar($json[$name], $name);
				if ( $result == "success" ) {
					echo '<div class="updated"><p><strong>Einstellungen gespeichert für ' . $json[$name]['communityname'] . '</strong></p></div>';
				} else {
					echo '<div class="error"><p><strong>Probleme beim Speichern für ' . $json[$name]['communityname'] . '</strong></p></div>';
				}
			} else {
				echo '<div class="error"><p><strong>Ungültiger Communityfeed</strong></p></div>';
			}
		} else {
			echo '<div class="error"><p><strong>Ungültiger Communityname</strong></p></div>';
		}
	}

?>
<div class="wrap">
    <?php
	echo "<h2>" . __( 'time.ly Calendar Feed Importer', 'tci_trdom' ) . "</h2>";
	echo "<h4>" . __( 'Community Calendars Found:', 'tci_trdom' ) . "</h4>";
	$list = "<dl>";
	foreach($json as $name => $calendar) {
		$list .= "<dt>" . $calendar['communityname'] . '</dt><dd>Link: <a href="' . $calendar['url'] . '" target="_blank">' . $calendar['url'] . '</a>';
		$list .= '<form name="tci_form'. $name .'" method="post" action="'. str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) .'">';
		$list .= '<input type="hidden" name="tci_hidden" value="ONE"/>';
		$list .= '<input type="hidden" name="tci_community" value="' . $name . '"/>';
		$list .= '<input type="submit" name="Submit" value="Update/Insert ' . $calendar['communityname'] . ' " /></form></dd>' ;
	}
	$list .= "</dl>";
	echo $list;?>
	<form name="tci_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="tci_hidden" value="ALL" />
			<input type="submit" name="Submit" value="Update/Insert alle Kalender" /></form>
		</div>
	<?php
}

function tci_admin_options() {
    if($_POST['tci_hidden'] == 'Y') {
				$old_schedule = get_option('tci_schedule');
        $source_url = $_POST['tci_source_url'];
				$schedule = $_POST['tci_schedule'];
        update_option('tci_source_url', $source_url);
				update_option('tci_schedule', $schedule);
				if ( $old_schedule != $schedule) {
					tci_disable_schedule();
					if ( $schedule != 'none' ) {
						tci_enable_schedule();
					}
				}
        ?>
        <div class="updated"><p><strong><?php _e('Einstellungen gespeichert.' ); ?></strong></p></div>
        <?php
    } else {
        //Normal page display
        $source_url = get_option('tci_source_url');
				$schedule = get_option('tci_schedule');
    }
?>
<div class="wrap">
    <?php    echo "<h2>" . __( 'time.ly Calendar Feed Importer Options', 'tci_trdom' ) . "</h2>"; ?>

    <form name="tci_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="tci_hidden" value="Y">
        <?php    echo "<h4>" . __( 'time.ly Calendar Feed Importer Settings', 'tci_trdom' ) . "</h4>"; ?>
	<p>Erwartet wird ein JSON-File mit den Namen als Key, darunter dann url, lastchange, Städtename und Communityname, z.B. so:
	<pre>
{
    "hamburg": {
        "url": "https://www.google.com/calendar/ical/hamburg.freifunk%40gmail.com/public/basic.ics",
        "lastchange": "2014-04-10",
        "city": "Hamburg",
        "communityname": "Freifunk Hamburg"
    },
    "weimarnetz": {
        "url": "http://www.weimarnetz.de/api/weimarnetz_kalender.ics",
        "lastchange": "2014-06-26 13:01:04.026240",
        "city": "Weimar",
        "communityname": "weimarnetz"
    },
    "greifswald": {
        "url": "http://greifswald.freifunk.net/?plugin=all-in-one-event-calendar&controller=ai1ec_exporter_controller&action=export_events&no_html=true",
        "lastchange": "2014-06-26 13:01:04.026240",
        "city": "Greifswald",
        "communityname": "Freifunk Greifswald"
    },
    "halle": {
        "url": "http://www.freifunk-halle.net/shared_calendar.php",
        "lastchange": "2014-06-26 13:01:04.026240",
        "city": "Halle",
        "communityname": "Freifunk Halle"
    }
}
	</pre>
	</p>
        <p><?php _e("Quelle: " ); ?><input type="text" name="tci_source_url" value="<?php echo $source_url; ?>" size="50"><?php _e(" z.B.: http://www.url.to.calenderfeeds.com/" ); ?></p>
				<p><?php _e("Zeitplan: " ); ?>
				<select name="tci_schedule" size="1">
      		<option value="none">Keiner</option>
					<option value="hourly" <?php if ($schedule == 'hourly') echo 'selected="selected"'?>>Stündlich</option>
      		<option value="twicedaily" <?php if ($schedule == 'twicedaily') echo 'selected="selected"'?>>Halbtäglich</option>
      		<option value="daily" <?php if ($schedule == 'daily') echo 'selected="selected"'?>>Täglich</option>
    		</select><?php _e("Wie oft sollen die Kalenderfeeds importiert werden?" ); ?>
				<br/><small>Nächste Ausführung:
				<?php if ( wp_next_scheduled('import_all_feeds') ) { echo date("c", wp_next_scheduled('import_all_feeds'));} else {echo "nicht geplant";}?>
				</small>
				</p>
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Aktualisiere Einstellungen', 'tci_trdom' ) ?>" />
        </p>
    </form>
</div>
<?php
}

function tci_admin_actions() {
    add_management_page("time.ly Calendar Feed Importer", "time.ly Calendar Feed Importer", "manage_options", "time.lyCalendarFeedImporterManage", "tci_admin_manage");
    add_options_page("time.ly Calendar Feed Importer", "time.ly Calendar Feed Importer", "manage_options", "time.lyCalendarFeedImporterOptions", "tci_admin_options");
}

function tci_enable_schedule() {
	$recurrence = get_option('tci_schedule');
	if ( ! ( $recurrence == '' || $recurrence == 'none' ) ) {
		wp_schedule_event(time(), $recurrence , 'import_all_feeds');
	}
}

function tci_disable_schedule() {
	wp_clear_scheduled_hook( 'import_all_feeds' );
}

register_deactivation_hook( __FILE__, 'tci_disable_schedule' );

add_action('admin_menu', 'tci_admin_actions');
add_action('import_all_feeds', 'tci_import_all_feeds');

?>
