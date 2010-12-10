=== Plugin Name ===
Author: Simon Goodchild
Contributors: Simon Goodchild
Donate link: http://www.wpsymposium.com
Link: http://www.wpsymposium.com
Tags: symposium, forum, social, wall, status, message, groups, french, spanish, german
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: 0.1.7.1

A new suite of social networking plugins, starting with a forum.

== Description ==

A forum and more! A new suite of social networking plugins, starting with a forum.

NOTICE: We are currently BETA testing this plugin. As such, although we have not found any problems, we do not recommend you use it on any production sites. However, we do welcome all feedback, bug reports and suggestions. Please do so at www.wpsymposium.com - thank you. 

---------------

**FORUM**

*Overview*

The goal of the forum is to be simple and as uncluttered as possible, whilst having powerful features within.

Features currently include:

*Appearance/Styles*

* Search Engine friendly links within the forum (helps page ranking and so on)
* Replaces smiley codes with images, eg. :)
* Add your own smilies/emoticons
* Change the styles (look and feel) through the admin panel
* Colour Picker popup, no codes needed!
* No need to edit stylesheet
* Set optional rounded corners
* Uses member avatars
* Supports all permalink, and default no-permalink settings

*Languages*

* Supports multiple languages, currently English, French, Spanish and German
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

*Email Notifications*

* Email notifications to new topics and/or replies to existing topics
* AJAX selection to receive email notifications (no page re-load)
* Daily Digest to all members for new topics and replies (optionally)
* Customise email notification address
  
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

Put the following in a page on your site (it's a hyphen, not an underscore):

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
A. Make sure you go to the Option page in admin and save your settings. Important!

Q. My language isn't supported, can I add my language?
A. Contact us via www.wpsymposium.com and we will add the language with your help, thank you.

Q. Are there images other than smilies?
A. Yes, if you look in the smilies folder you can use any of the images there by putting {{xxx}} around the first part of the filename, eg: {{rofl}} would show rofl.png - to see the full list, go to the Forum Smilies page on www.wpsymposium.com

== Changelog ==

= 0.1.7.1 =

* Patch: Change to styles to handle underline option

= 0.1.7 =

* Added: Avatars in first topic post
* Added: Small avatars on started by/last reply column
* Added: Underline style option to links
* Added: Support for '.html on PAGES' plugin that appends .html to Wordpres URLs
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