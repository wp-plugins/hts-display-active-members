=== Plugin Name ===
Author: Simon Goodchild
Contributors: Simon Goodchild
Donate link: http://www.wpsymposium.com
Link: http://www.wpsymposium.com
Tags: symposium, forum, social, wall, status, message, groups, french, spanish, german, italian, turkish, czech
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: 0.1.12

A new suite of social networking plugins, starting with a forum.

== Description ==

WP Symposium is a forum and more! A new suite of social networking plugins, starting with a forum - and you simply choose which you want to activate.

NOTICE: We are currently BETA testing this plugin. As such, although we have not found any problems, we do not recommend you use it on any production sites. However, we do welcome all feedback, bug reports and suggestions. Please do so at www.wpsymposium.com - thank you. 

---------------

**FORUM**

*Overview*

The goal of the forum is to be simple and as uncluttered as possible, whilst having powerful features within.

Features currently include:

*Configuration*

* Limit viewing of forum to particular user levels
* Set width of forum in % or pixels
* Supports all permalink, and default no-permalink settings
* Supports '.html on PAGES' plugin
* Supports 'SimpleModal Login' plugin
* No need to edit stylesheet
* Health Check page to help with maintenance
* Full audit trail and event log (note, minimum event/error logging at the moment, next patch will add full logging)

*Appearance/Styles*

* Search Engine friendly links within the forum (helps page ranking and so on)
* Replaces smiley codes with images, eg. :)
* Loads of fun smilies/emoticons to insert
* Even add your own smilies/emoticons
* Change the styles (look and feel) through the admin panel
* Colour Picker popup, no codes needed!
* Set the length of the preview text
* Set optional rounded corners
* Uses member avatars

*Languages*

* English
* French
* Spanish (thanks to Patricia Blanco)
* German (thanks to Pascal)
* Italian (thanks to Massimiliano Mazza)
* Turkish (thanks to Cahit Cengizhan)
* Czech (thanks to Vase Jmeno)
* If your language is not included, or to make corrections, please contact us via info@wpsymposium.com and we will work with you on translating the forum to your language.

*Categories*

* Optional use of categories
* Order categories
* Default category for new topics (if using categories)
* Set if new Topics allowed or not
* Number of Topics in a Category
* Last topic/reply shown
* How old topics shown as 'x' minutes/hours/days ago, etc
* How many total views in each Category

*Topics*

* Create new topics
* Restrict new topics in certain categories (optionally)
* Site administrators can always create new topics
* Set sticky posts that stay at top of forum (AJAX, no page reload)
* Number of replies to a Topic
* Last post/reply shown
* Edit and Delete topics, posts and replies
* Move Topics between Categories
* How old replies shown as 'x' minutes/hours/days ago, etc
* Choose if topic replies are allowed
* Opaque "closed" topics (as defined) if used as support forum

*Email Notifications*

* Subscribe to new topics in chosen categories
* Subscribe to replies to chosen topics
* Daily Digest to all members for new topics and replies (optionally)
* AJAX selection to subscribe (no page re-load)
* Members can subscribe to receive digests (AJAX)
* Customise email notification address and footer text
  
== Installation ==

Tested with PHP 5.
Important - after activating for the first time, make sure you go to the Options page and set things up.

*Automatic installation*

* go to Plugins->Add New and search for "Symposium"
* click on Install below WP-Symposium
* activate the plugins you want to use (core plugin must remain activated)
* important - go to the Options page to configure

*Manual installation*

* download from http://wordpress.org/extend/plugins/wp-symposium/
* unzip the contents of the ZIP file in your /wp-content/plugins folder
* activate the plugins you want via Plugins on the WordPress admin dashboard (including the core plugin)
* important - go to the Options page to configure

When upgrading manually, make sure you deactivate and re-activate the core plugin.

*Adding Forum*

If you need to, create a new page in WordPress (Pages->Add New).
Put the following in a WordPress page (it's a hyphen, not an underscore):

  `[symposium-forum]`
  

== Screenshots ==

1. Changing Styles
2. Email notification
3. Categories (optional)
4. Start new topic
5. Forum topic with replies
6. Admin options screen
7. Edit Topic/Move Topic
8. Admin categories screen
9. Admin menu
10. Using smilies
11. SEO friendly links

== Frequently Asked Questions ==

Q. Where can I find more information?
A. Go to www.wpsymposium.com

Q. The admin side works, but the forum doesn't appear?
A. Check you have `[symposium-forum]` on your page, with a hyphen, not an underscore.

Q. I've done that, but the forum still doesn't appear?
A. Have you put `[symposium-forum]` on a WordPress page, not an HTML file? 

Q. My language isn't supported, can I add my language?
A. Go to http://www.wpsymposium.com/forum/add-new-forum-language and we will add the language with your help, thank you.

Q. Are there images other than smilies?
A. Yes, if you look in the smilies folder you can use any of the images there by putting {{xxx}} around the first part of the filename, eg: {{rofl}} would show rofl.png - to see the full list, go to the Forum Smilies page at http://www.wpsymposium.com/forum/forum-smilies

== Changelog ==

= 0.1.12 =

* Fix: Fatal Error on activating in symposium_menu.php when activated before core
* Added: Now try fopen first when loading language XML, if that fails/disabled try curl instead
* Added: Detail audit when loading XML file

= 0.1.11.1 =

* Added: Widget for recent forum posts
* Added: Check that audit function exists before doing audit when forum activates

= 0.1.11 = 

* Added: Event log (admin menu option)
* Added: AJAX test to Health Check

= 0.1.10.1 =

* Change: Improved the Health Check

= 0.1.10 = 

* Added: Change font family and size for headings and body text
* Added: Link in email notifications to stop receiving them
* Fixed: Some overlooked language sentences
* Fixed: Microsoft opacity for [closed] tag
* Changed: z-index of please wait/saving messages so they appear on top of other div's

= 0.1.9 =

* Added: Style option for main background color
* Added: Style option for opacity of topics with [closed] in the subject
* Added: Option to change the "opacity trigger word" from closed
* Added: Links in email notifications
* Added: Italian language
* Added: Turkish language
* Added: Czeck language

= 0.1.8.2 =

* Added: Subscribe to individual categories for new topics
* Removed: Forum-wide new topic subscription
* Fixed: Error caused with links from rest of site (eg. a blog) redirecting to the forum
* Fixed: Problem with apostrophe when editing topic subjects
* Fixed: CSS issue with internal widths hiding far right pixels

= 0.1.8.1 =

* Fixed: Clash with EasingSlider plugin

= 0.1.8 =

* Added: Option to change length of preview text on topics page
* Added: Set level of user that can view the forum
* Added: Members can choose if to receive digests via email
* Added: Set width of forum (helps to fit properly in with some themes)
* Added: Option to count administrator in "topic views"
* Added: Option to change order of replies (old to new, and vice versa)
* Added: Option to not permit replies (for when a topic is for info only)
* Added: Extra security on top of existing security to combat attacks

= 0.1.7.1 =

* Patch: Change to styles to handle underline option

= 0.1.7 =

* Added: Avatars in first topic post
* Added: Small avatars on started by/last reply column
* Added: Underline style option to links
* Added: Support for '.html on PAGES' plugin that appends .html to Wordpress URLs
* Added: Better reporting if loading of language XML file fails
* Change: Enhanced the layout to be simpler, and removed unnecessary headings
* Fix: loading language XML file won't rely upon allow_url_fopen being enabled in php.ini

= 0.1.6 =

* Added: Loads of smilies, and add your own! Check the Forum Smilies page on www.wpsymposium.com
* Added: Search engine friendly links within the forum
* Added: Last Topic in category takes notice of sticky posts
* Added: Views when looking at list of categories
* Added: Spanish language (thanks to Patricia Blanco)
* Added: German language (thanks to Pascal)
* Added: Use of external XML file for languages
* Added: Warning to set Options for WP Symposium after initial installation
* Fixed: Handling apostrophe's when editing a post and other places
* Fixed: Don't show backslashes where shouldn't do
* Fixed: To work if not using Permalinks, ie. using ?page_id=x
* Fixed: Avoid double posting on page refresh
* Fixed: Other minor bug fixes

= 0.1.5 =

* Added: Client side check if post/reply form not filled in
* Added: Automatic smiley replace and image tag replacement, eg: {{rofl}}
* Fixed: Delete category
* Fixed: Set 'allow new topics' for new category

= 0.1.4 =

* Fixed: Bug where all topics were appearing as new in notifications
* Fixed: Two nobreak spaces prior to Back to [topic]... link for narrow forums

= 0.1.3 =

* Added: support for multiple languages

= 0.1.2 =

* First external release candidate, no changes yet

== Upgrade Notice ==

Latest news and information on www.wpsymposium.com