=== Plugin Name ===
Contributors: CodingOurWeb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=mboomaars%40gmail%2ecom&lc=NL&item_name=CodingOurWeb&no_note=0&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Tags: beautiful, content block, css, easy, featured list, heading, image, lightbox, mask, multi-column, portfolio, styling, template
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 2.2.0

A WordPress plugin that will assist in creating beautiful content blocks by using 1 simple shortcode

== Description ==

A WordPress plugin that will assist in creating beautiful content blocks by using 1 simple shortcode. Each of those content blocks may consist of a header, an image, some content and a footer. Content blocks can be added to one or more block groups for grouping purposes.

Note: If you like this plugin, you should consider <a href="http://codecanyon.net/item/wp-boxer-pro/1497329?ref=mb2o" target="_blank" title="Start Boxing Like a Heavyweight">going PRO</a> and start boxing like a heavyweight.

Some features exclusive to <a href="http://codecanyon.net/item/wp-boxer-pro/1497329?ref=mb2o" target="_blank">WP Boxer Pro</a>:
<ul>
<li><strong>Content slider</strong></li>
<li>Support for multiple links inside a content block</li>
<li>Allows for addition of multiple, customizable styles to heading, image and content sections</li>
<li>Content block masks which allow you to easily copy styles over to other boxes</li>
<li>Import and export content block styles</li>
<li>Add almost 600 Google Fonts in the mix!</li>
</ul>

== Installation ==

This section describes how to install the plugin and get it working.

1. Extract the zipfile
2. Place the entire folder named "boxer" in the plugin folder
3. Go to the admin panel and activate the plugin

4. Start by adding a new Block Group and naming it "my-first-block-group"

5. Add a new Content Block
   - Specify a title: this will be the content block header
   - Specify your content: this will be the content block content
   - Specify a featured image: this will be the content block image (optional)
   - Select the block group you created in step 4
   
6. In the Additional Content Block Settings box you can:
   - Select a Content Block Type (Default: 2)
   - Specify header parameters
   - Specify image parameters
   - Specify content parameters

7. Repeat step 5 and 6 for as long as you like
   
7. Create a new page

8. Add the following shortcode: [wpbp_blocks set="homepage"]  or use the shortcode wizard (a button in the TinyMCE editor)

9. Save the page and you're done!

TIPS
* Content blocks also work within a Text widget! So you can easily incorporate content blocks into sidebar and footers.
* You can use shortcodes within your content blocks

== Frequently Asked Questions ==

= Does Boxer work with IE? =

Yes, it does. IE 7-10 is supported

= Is there a screencast available on how to use Boxer? =

Yes! Watch them here (http://www.youtube.com/playlist?list=PL600D8845EF163DA7&feature=view_all)

= Why can I not select a featured image? =

Seems you are using a theme that has explicitly set a post-type array as a second argument

if ( function_exists( 'add_theme_support' ) ) {
    add_theme_support( 'post-thumbnails', array('post', 'page') );
}

Remove the array like so:

if ( function_exists( 'add_theme_support' ) ) {
    add_theme_support( 'post-thumbnails' );
}

== Screenshots ==

1. Example 1
2. Example 2
3. Example with shortcodes
3. Admin section

== Changelog ==

= 2.0.2 =
* Bugfixes

= 2.0.1 =
* Bugfixes

= 2.0 =
* Full upgrade to a stripped down version of WP Boxer Pro

= 1.23 = 
* Fixed issue with box-width not being updated
* Fixed issue with tooltips not being activated

= 1.21 =
* Updated: Timthumb script to version 2.8.10

= 1.2 =
* Fixed: added an alias for the box shortcode which can be used when a conflicting theme is activated
* Fixed: issue with box display in IE7 (nesting of boxes inside span)
* Implemented an after_setup_theme hook for thumbnail support

= 1.14 =
* Added a new box type

= 1.13 =
* Announcing WP Boxer Pro 1.0

= 1.12 =
* Added several columns to the edit box screen
* Added settings and screencast option to plugin page
* Fixed issue with unsetting image frame option
* Added new image frame
* Removed redundant CSS styles
* Fixed issue with escaping quotes in link text

= 1.11 =
* Added column support. Lets Boxer do the math for you.
* Change the order of your boxes by specifying a box-index

= 1.09 = 
* Fixed an issue where empty paragraphs would get inserted 

= 1.07 =
* Added support for the use of shortcodes within boxes
* Added support for justified text alignment
* Added an option to specify header color

= 1.06 =
* Fixed a typo in wp-boxer.php

= 1.05 =
* Fixed an issue where sometimes the additional box settings would get lost
* Added an options page where default settings can be determined

= 1.04 =
* Fixed an issue where "The page you are trying to view cannot be shown because it uses an invalid or unsupported form of compression" would be displayed

= 1.03 =
* Fixed a bug where shortcodes embedded in a regular post would lead to the box being added at the top

= 1.02 =
* Images would not be properly centered in the box when applied

= 1.01 =
* Fixed a problem with boxtype 1 and 2. Images would be resized to full width of box.

= 1.00 =
* First version
