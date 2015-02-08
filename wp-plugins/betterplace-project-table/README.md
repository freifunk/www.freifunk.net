Plugin Name
===========
Contributors: andibraeu
Donate link: http://www.weimarnetz.de/spenden
Tags: betterplace,donations 
Requires at least: 3.6
Tested up to: 4.1
Stable tag: trunk

Description
------------
This plugin provides betterplace projects as a html table. You can use a shortcode to embed it in your page: [bpprojecttable orderBy='progress']
Requires the Progress Bar Plugin

Options are:
* orderBy: to order the output by one of the following fields: key, openAmount, incompleteNeed, progress, donors
* sort: whether to order ascending (asc) oder descending (desc)
* use_ffapi: enable or disable use of ffapi
* more_campaign: add betterplace campaign-ids

Changes
-------
1.4.0
* add boost campaigns that can be provided via freifunk API
* Bug fixes
 * don't crash when there's an unknown campaign provider
 * avoid duplicate campaign entries

1.3.5
* Bug fixes
* allow to hide project provider
* clean way to use shortcode attributes

1.3.4
* add switch to enable or disable use of freifunk api

1.3.3
* Output will be returned as string

1.3.1
* make tables sortable

1.3.0
* use transient "cache"
* set timeout for network requests, cache and url
* open amount rounded
* table is sortable

1.2.0
* optimized output table
