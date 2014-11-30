Plugin Name
===========
Contributors: andibraeu
Donate link: http://www.weimarnetz.de/spenden
Tags: betterplace,donations 
Requires at least: 3.6
Tested up to: 3.6
Stable tag: trunk

Description
------------
This plugin provides betterplace projects as a html table. You can use a shortcode to embed it in your page: [bpprojecttable orderBy='progress']
Requires the Progress Bar Plugin

Options are:
* orderBy: to order the output by one of the following fields: key, openAmount, incompleteNeed, progress, donors
* sort: whether to order ascending (asc) oder descending (desc)

Changes
-------
1.3.1
* make tables sortable

1.3.0
* use transient "cache"
* set timeout for network requests, cache and url
* open amount rounded
* table is sortable

1.2.0
* optimized output table
