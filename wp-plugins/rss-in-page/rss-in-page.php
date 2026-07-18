<?php
/*
Plugin Name: RSS in Page (for freifunk.net)
Plugin URI: https://github.com/freifunk/www.freifunk.net/edit/master/wp-plugins/rss-in-page
Description: short code [rssinpage rssfeed='feedURL'] generates a list of RSS feed items with date, title, link and description; now support muiltiple feed URLs separated by a comma e.g. rssfeed='feedurl1, feedurl2' - optional attributes: rssitems='number' sets the number of items to return, default is 5; rssorder='asc' returns items from oldest to newest, default is 'desc' which returns items from newest to oldest; rsstarget allows for feed link to be opened in new window by setting to '_blank', default is '_self'; rssdateformat allows item date to be formatted using php date parameters, default is RFC 2822 formatted date; rssdescription='no' suppress description display, default is to show it; rsscss allows you to set a css class to style the list, default is the content list style in the theme; rssformat allows you to format the output using format parameters e.g. the default x: Y - w&lt;br&gt;z where x is the date, Y is the title with a link, y is the title, z is the description and w is the feed title; rsstitlelength allows you to truncate the title to a certain character length; rssdescriptionlength allows you to truncate the description to a certain character length; rsstimezone allows you to set the timezone in which the feed datetime will be displayed, default is UTC.
Version: 2.9.3f
Author: Titus Bicknell, Andreas Braeu
Author URI: http://titusbicknell.com
 */

/**
 * Allow only safe http(s) URLs.
 *
 * @param string $url Raw URL.
 * @return string Escaped URL or empty string.
 */
function rssinpage_safe_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}
	$validated = wp_http_validate_url( $url );
	if ( ! $validated ) {
		return '';
	}
	return esc_url( $validated );
}

/**
 * Truncate a string to a max length (characters), append ellipsis when cut.
 *
 * @param string     $text   Input text.
 * @param string|int $length 'all' or integer length.
 * @return string
 */
function rssinpage_truncate( $text, $length ) {
	if ( $length === 'all' || $length === '' || ! is_numeric( $length ) ) {
		return $text;
	}
	$length = (int) $length;
	if ( $length < 1 || strlen( $text ) <= $length ) {
		return $text;
	}
	return substr( $text, 0, $length ) . '... ';
}

/**
 * Sanitize CSS class attribute value (space-separated class names).
 *
 * @param string $css Raw class attribute.
 * @return string
 */
function rssinpage_sanitize_css_class( $css ) {
	$classes = preg_split( '/\s+/', trim( (string) $css ), -1, PREG_SPLIT_NO_EMPTY );
	$safe    = array();
	foreach ( $classes as $class ) {
		$class = sanitize_html_class( $class );
		if ( $class !== '' ) {
			$safe[] = $class;
		}
	}
	return implode( ' ', $safe );
}

/**
 * Sanitize link target to a small allow-list.
 *
 * @param string $target Raw target.
 * @return string
 */
function rssinpage_sanitize_target( $target ) {
	$target = strtolower( trim( (string) $target ) );
	$allowed = array( '_self', '_blank', '_parent', '_top' );
	return in_array( $target, $allowed, true ) ? $target : '_self';
}

/**
 * Shortcode handler: [rssinpage ...]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function rssinpage( $atts ) {
	$atts = shortcode_atts(
		array(
			'rssfeed'                => 'no feed',
			'rssitems'               => '5',
			'rssorder'               => 'desc',
			'rssdateformat'          => 'r',
			'rsstarget'              => '_self',
			'rssdescription'         => 'yes',
			'rssdescriptionlength'   => 'all',
			'rsstitlelength'         => 'all',
			'rssformat'              => 'x: Y - w<br />z<br /> a<br />',
			'rsscss'                 => '',
			'rsstimezone'            => 'UTC',
			'rssdescriptionreadmore' => 'read more',
		),
		$atts,
		'rssinpage'
	);

	$rssfeed                = (string) $atts['rssfeed'];
	$rssitems               = max( 0, (int) $atts['rssitems'] );
	$rssorder               = strtolower( (string) $atts['rssorder'] );
	$rssdateformat          = (string) $atts['rssdateformat'];
	$rsstarget              = rssinpage_sanitize_target( $atts['rsstarget'] );
	$rssdescription         = strtolower( (string) $atts['rssdescription'] );
	$rssdescriptionlength   = $atts['rssdescriptionlength'];
	$rsstitlelength         = $atts['rsstitlelength'];
	$rssformat              = (string) $atts['rssformat'];
	$rsscss                 = rssinpage_sanitize_css_class( $atts['rsscss'] );
	$rsstimezone            = (string) $atts['rsstimezone'];
	$rssdescriptionreadmore = sanitize_text_field( $atts['rssdescriptionreadmore'] );

	if ( $rssfeed === 'no feed' || $rssfeed === '' ) {
		return '';
	}

	// Validate timezone; fall back to UTC.
	try {
		new DateTimeZone( $rsstimezone );
	} catch ( Exception $e ) {
		$rsstimezone = 'UTC';
	}

	$rsscachelocation = WP_CONTENT_DIR . '/cache';
	if ( ! file_exists( $rsscachelocation ) ) {
		wp_mkdir_p( $rsscachelocation );
	}

	// Format string: decode entities, then only allow a small HTML subset around placeholders.
	$rssformat = html_entity_decode( $rssformat, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	$rssfeedarray = array_filter( array_map( 'trim', explode( ',', $rssfeed ) ) );
	foreach ( $rssfeedarray as &$feedurl ) {
		if ( stripos( $feedurl, 'http://' ) !== 0 && stripos( $feedurl, 'https://' ) !== 0 ) {
			$feedurl = 'https://' . $feedurl;
		}
		$feedurl = html_entity_decode( $feedurl, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$feedurl = esc_url_raw( $feedurl );
	}
	unset( $feedurl );

	$rssfeedarray = array_values( array_filter( $rssfeedarray ) );
	if ( empty( $rssfeedarray ) ) {
		return '';
	}

	require_once ABSPATH . WPINC . '/class-feed.php';

	$previous_timezone = date_default_timezone_get();
	date_default_timezone_set( $rsstimezone );

	$feed = new SimplePie();
	$feed->set_feed_url( $rssfeedarray );
	$feed->set_cache_location( $rsscachelocation );
	$feed->set_cache_duration( 60 );
	if ( $rssorder === 'none' ) {
		$feed->enable_order_by_date( false );
	}
	$feed->init();
	$feed->handle_content_type();

	$maxitems = $feed->get_item_quantity( 50 );
	if ( $maxitems === 0 ) {
		date_default_timezone_set( $previous_timezone );
		return '';
	}

	$rss_items = $feed->get_items( 0, $maxitems );
	if ( $rssitems > $maxitems ) {
		$rssitems = $maxitems;
	}
	if ( $rssorder === 'asc' ) {
		$rss_items = array_reverse( $rss_items );
	}

	$rssreturn = '';
	$i         = 0;
	while ( $i < $rssitems ) {
		$item = $rss_items[ $i ];

		$rsstitle_raw = (string) $item->get_title();
		$rsstitle_raw = html_entity_decode( $rsstitle_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		$source    = $item->get_item_tags( SIMPLEPIE_NAMESPACE_RSS_20, 'source' );
		$w_raw     = isset( $source[0]['data'] ) ? (string) $source[0]['data'] : '';
		$sourceUrl = isset( $source[0]['attribs']['']['url'] ) ? (string) $source[0]['attribs']['']['url'] : '';

		if ( $w_raw === '' ) {
			$w_raw = (string) $item->get_feed()->get_title();
		}
		$w_raw = html_entity_decode( $w_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$w     = esc_html( $w_raw );

		$feed_link = rssinpage_safe_url( $sourceUrl !== '' ? $sourceUrl : (string) $item->get_feed()->get_link() );
		if ( $feed_link !== '' ) {
			$W = '<a href="' . $feed_link . '" target="_blank" rel="noopener noreferrer">' . $w . '</a>';
		} else {
			$W = $w;
		}

		$x = '';
		if ( $item->get_date() ) {
			$x = esc_html( $item->get_date( $rssdateformat ) );
		}

		$rsslinketitle = esc_attr( $rsstitle_raw );
		$rsstitle      = rssinpage_truncate( $rsstitle_raw, $rsstitlelength );
		$y             = esc_html( $rsstitle );

		$rss_itemlink = rssinpage_safe_url( (string) $item->get_permalink() );
		if ( $rss_itemlink !== '' ) {
			$Y = '<a class="test" href="' . $rss_itemlink . '" target="' . esc_attr( $rsstarget ) . '" title="' . $rsslinketitle . '">' . esc_html( $rsstitle ) . '</a>';
		} else {
			$Y = esc_html( $rsstitle );
		}

		$z = '';
		if ( $item->get_description() !== '' ) {
			// Allow a safe HTML subset from feed descriptions (no scripts/events).
			$z = wp_kses_post( $item->get_description() );
		}
		if ( $rssdescriptionlength !== 'all' && is_numeric( $rssdescriptionlength ) ) {
			// Truncate on plain text length, then re-escape as text to avoid broken tags.
			$plain = wp_strip_all_tags( $z );
			$plain = rssinpage_truncate( $plain, $rssdescriptionlength );
			$z     = esc_html( $plain );
			if ( $rssdescriptionreadmore !== '' && $rss_itemlink !== '' ) {
				$z .= ' <a href="' . $rss_itemlink . '" target="' . esc_attr( $rsstarget ) . '" title="' . $rsslinketitle . '">' . esc_html( $rssdescriptionreadmore ) . '</a>';
			}
		}
		if ( $rssdescription === 'no' ) {
			$z = '';
		}

		$p = '';
		$a = '';
		if ( $enclosure = $item->get_enclosure() ) {
			$thumb = rssinpage_safe_url( (string) $enclosure->get_thumbnail() );
			if ( $thumb !== '' ) {
				$p = '<img src="' . $thumb . '" alt="" />';
			}
			$enctype = (string) $enclosure->get_type();
			$enclink = rssinpage_safe_url( (string) $enclosure->get_link() );
			if ( $enclink !== '' && strstr( $enctype, 'audio' ) ) {
				// Shortcode attribute must be quoted/escaped; src is a validated URL.
				$a = do_shortcode( '[podloveaudio src="' . esc_attr( $enclink ) . '" width="80%"]<br />' );
			}
		}

		// Protect placeholders, sanitize literal markup in rssformat, then restore values.
		$tokens = array(
			"\x01P\x01" => $p,
			"\x01W\x01" => $W,
			"\x01w\x01" => $w,
			"\x01X\x01" => $x,
			"\x01Y\x01" => $Y,
			"\x01y\x01" => $y,
			"\x01Z\x01" => $z,
			"\x01A\x01" => $a,
		);

		$format_masked = strtr(
			$rssformat,
			array(
				'p' => "\x01P\x01",
				'W' => "\x01W\x01",
				'w' => "\x01w\x01",
				'x' => "\x01X\x01",
				'Y' => "\x01Y\x01",
				'y' => "\x01y\x01",
				'z' => "\x01Z\x01",
				'a' => "\x01A\x01",
			)
		);

		$format_safe = wp_kses(
			$format_masked,
			array(
				'br' => array(),
				'p'  => array(),
				'span' => array(
					'class' => true,
				),
				'strong' => array(),
				'em'     => array(),
				'b'      => array(),
				'i'      => array(),
			)
		);

		$rssformatoutput = strtr( $format_safe, $tokens );
		$rssreturn      .= '<li>' . $rssformatoutput . '</li>';
		$i++;
	}

	date_default_timezone_set( $previous_timezone );

	if ( $rsscss !== '' ) {
		$rssipul = '<ul class="' . esc_attr( $rsscss ) . '">';
	} else {
		$rssipul = '<ul>';
	}

	return $rssipul . $rssreturn . '</ul>';
}

add_shortcode( 'rssinpage', 'rssinpage' );
