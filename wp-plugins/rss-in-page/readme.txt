=== Plugin Name ===
Contributors: titusbicknell
Donate link: http://www.titusbicknell.com/wordpress/rss-in-page
Tags: pages, posts, rss
Requires at least: 2.5
Tested up to: 3.6
Stable tag: trunk

== Description ==

Short code [rssinpage rssfeed='feedURL'] generates a list of RSS feed items with date, title, link, description and now image. 

Supports multiple feed URLs separated by commas e.g. rssfeed='feedurl1, feedurl2' and displaying the feed name.

Supports truncation of the feed title and description now with a read more link.

Optional attributes: 

* rssformat allows you to format the output using format parameters e.g. the default x: Y - w&lt;br&gt;z where x is the date, Y is the title with a link, y is the title, z is the description and w is the feed title, p adds a thumbnail image
* rssitems='number' sets the number of items to return, default is 5
* rssorder='asc' returns items from oldest to newest, default is 'desc' which returns items from newest to oldest
* rsstarget='_blank' opens item link in new window, default is '_self'
* rssdateformat allows item date to be formatted using php date parameters, default is RFC 2822 formatted date
* rss description='no' suppresses default display of the description
* rsscss='yourclassname' allows you to give the list a specific style rather than inheriting the default content list style in your theme  
* rsstitlelength allows you to truncate the title to a certain character length
* rssdescriptionlength allows you to truncate the description to a certain character length
* rssdescriptionreadmore allows you to set the text for a read more link
* rsstimezone allows you to set the timezone in which the feed datetime will be displayed, default is UTC; use values from the php list of supported timezones e.g. PST is America/Los_Angeles, MST is America/Denver, CST is America/Chicago, EST is America/New_York.

== Installation ==

1. Download <code>RSSinpage.zip</code>
1. Upload and activate **RSS in Page** through the <code>Plugins</code> menu in **WordPress**
1. insert short code [rssinpage rssfeed='feedURL'] into your page or post and replace feedURL with a valid RSS feed for the content you wish to include

or

1. Download <code>RSSinpage.zip</code>
1. Unzip <code>RSSinpage.zip</code> and upload to the <code>/wp-content/plugins/</code> directory
1. Activate **RSS in Page** through the <code>Plugins</code> menu in **WordPress**
1. insert short code [rssinpage rssfeed='feedURL'] into your page or post and replace feedURL with a valid RSS feed for the content you wish to include

== Change Log ==

2.9.1:

* defined default value for rssdescriptionreadmore

2.9:

* support for html tages in rssformat e.g. &lt;br /&gt;, &lt;b&gt;
* added format paramater p to add a thumbnail for a feed item
* added rssdescriptionreadmore to set the text for a read more link when the description is truncated using rssdescriptionlength

2.8: 

* added rssdescriptionlength to truncate long descriptions and add an elipsis
* added unset() to ensure feed item data is not passed to subsequent item

2.7:

* added format parameter to allow custom output to include the feed name as w

2.6:

* added timezone support

2.5:

* updated separator for multiple feed URLs to avoid yahoo pipes feed issue

2.4:

* added support for multiple feed URLs

2.3:

* set simplepie cache location, check folder exists and create it if necessary

2.2:

* rebuilt fetch_feed to shorten chache time to 60 seconds
* added link title roll over

2.1:

* added function so shortcode can handle feed URLs with or without http:// element
* added rsstitlelength to truncate long titles and add an elipsis

2.0: 

* added format parameters to allow custom output using x for date, y for title, Y for title with link and z for description - default rssformat='x: Y<br>z'

1.5:

* fixed encoding issue preventing feed URLs with iso-8859-1 entities from parsing

1.2:

* added optional attributes:
* rssdescription
* rsscss

1.1:

* added optional attributes:
* rsstarget='_blank' opens item link in new window, default is '_self'
* rssdateformat allows item date to be formatted using php date parameters, default is RFC 2822 formatted date

1.0:

* added optional attributes:
* rssitems='number' sets the number of items to return, default is 5;
* rssorder='asc' returns items from oldest to newest, default is 'desc' which returns items from newest to oldest.
* added condition to verify requested items does not exceed available items