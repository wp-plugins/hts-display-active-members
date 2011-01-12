=== Plugin Name ===
Author: WP Symposium
Contributors: Simon Goodchild
Donate link: http://www.wpsymposium.com
Link: http://www.wpsymposium.com
Tags: symposium, forum, social, chat, friends, wall, status, message, groups, french, spanish, german, italian, turkish, czech, hungarian, portuguese, norwegian, dutch, russian, polish, swedish
Requires at least: 3.0
Tested up to: 3.0.4
Stable tag: 0.1.22

A new suite of social networking plugins - forum, wall, mail, member directory, private messaging, notification bar, chat windows, profile page, widgets, and more!

== Description ==

WP Symposium is a new suite of social networking plugins - forum, wall, mail, private messaging, notification bar, chat windows, profile page, widgets, and more. You simply choose which you want to activate!

NOTICE: We are currently BETA testing this plugin. As such, although we have not found any problems, we do not recommend you use it on any production sites. However, we do welcome all feedback, bug reports and suggestions. Please do so at www.wpsymposium.com - thank you. 

---------------

**WP SYMPOSIUM FEATURES**

*Plugins*

* Member Profile
* Notification Bar
* Forum
* Mail/Private Messaging
* Friends
* Chat
* Wall
* Member Directory
* ... more to come!!!

*Widgets*

* Latest New Members
* Latest Forum Posts
* ... more to come!!!

*Configuration*

* Works with any WordPress theme, and standard WordPress users
* Optional moderation of new topics/replies
* Set width of forum in % or pixels
* Supports all permalink, and default no-permalink settings
* Supports '.html on PAGES' plugin
* Supports 'SimpleModal Login' plugin
* No need to edit stylesheet
* Health Check page to help with maintenance
* Option to not load jQuery
* Full audit trail and event log (note, minimum event/error logging at the moment, next patch will add full logging)

*Appearance/Styles*

* Optional Search Engine friendly links within the forum
* Optional Replacement of smiley codes with images, eg. :)
* Loads of fun smilies/emoticons to insert
* Even add your own smilies/emoticons
* Change the styles (look and feel) through the admin panel
* Colour Picker popup, no codes needed!
* Set the length of the preview text
* Set optional rounded corners
* Uses member avatars

*Languages*

* English
* French (thanks to Frederic Lohbrunner)
* Spanish (thanks to Patricia Blanco)
* German (thanks to Pascal)
* Italian (thanks to Massimiliano Mazza)
* Turkish (thanks to Cahit Cengizhan)
* Czech (thanks to Vase Jmeno)
* Hungarian (thanks to Gergo Nagy)
* Portuguese (thanks to Nuno Coelho)
* Brazilian Portuguese (thanks to Eduardo Machado)
* Norwegian (thanks to Celina)
* Dutch (thanks to Monique Huizen and Monique Huizen)
* Russian (thanks to Ruslan)
* Polish (thanks to Dariusz Labuda)
* Swedish (thanks to Mattias Dahl)

* If your language is not included, or to make corrections, please contact us via info@wpsymposium.com and we will work with you on translating the forum to your language.

**MEMBER PROFILE PAGE**

*Overview*

The Member profile does not yet include all the functionality to form a useful page, but sets the foundation of sending mail to another another member, and linking the forum plugin and mail plugins. 

Put `[symposium-profile]` on any WP page, and put the page URL in the Symposium options.

* Displays member photo (as set via WordPress)
* Send Mail to member (if on another member's page)
* Set personal preferences for site language, layout, sounds, etc
* Set privacy level
* Depending on privacy level, displays location map, etc
* ... many more features to be added to this plugin!

*Settings*

* Allow members to over-ride default settings
* Set inactivity period for offline and logged out

**NOTIFICATION BAR**

*Overview*

The notification bar, if activated, can be placed at the bottom or top of every webpage. It shows a custom message to the left, a login/logout link to the right together with icons for specific purposes, ie: friends online, unread mail messages and friend requests.

When a member is logged in and a new mail arrives or a friend request is received, an alert is shown to the right instead of the logout link for a short period of time. Optionally, an alert sound (from a list available, defaulting to a subtle chime) is played.

The icons will also be highlighted with a number of unread messages or friend requests.

Clicking the friends online icon, will display which friends are online, not active and logged out. Clicking on a name will open a chat window (and on the recipients screen) for real time chatting.

Simply activate the plugin to use the notification bar.

*Settings*

* Place at top or bottom of page
* Change message to left of the bar
* Shows login/logout link to the right of the bar
* Use WP login/logout or custom URLs
* When logged in alerts show to the right of new mail
* Plays a sound when alerts show (optional)
* Shows friends online
* Set polling intervals for notifications and chat messages

**MAIL**

Private messaging for all your members! At present they can easily send a mail to another member of the site just by typing their display name. 

This will shortly be improved so that multiple recipients can be added. Furthermore mail can be limited to friends if the friends plugin is activated (yes - another plugin, but it's not available yet).

Put `[symposium-mail]` on any WP page, and put the page URL in the Symposium options.

* In Box
* Sent 'box'
* Compose new message
* Delete message
* Reply to message
* Simple layout
* AJAX enabled when switching between messages
* In Box messages highlighted when not read
* Sent messages highlighted that not read by recipient

**DIRECTORY**

A list of members, showing who is online together with their latest status post and location. Basic at the moment, but will improve.

Put `[symposium-members]` on any WP page.

* Lists members by who was most recent
* Search for members (by name or location) with intelligent search
* Includes location, latest status post and link to their profile page

**FORUM**

*Overview*

The goal of the forum is to be simple and as uncluttered as possible, whilst having powerful features within.

Put `[symposium-forum]` on any WP page, and put the page URL in the Symposium options.

*Settings*

* Option to moderate topics and replies
* Set preview text length
* Enable a daily digest (optional for members)
* Show or hide forum categories
* Change order from newest of oldest replies first
* Set a word to make posts slightly transparent (eg. [closed])
* Limit viewing of forum to particular user levels
* View all topics on the forum, with delete/approve option

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

Important - after activating for the first time, make sure you go to the Options page and set things up.
As with all upgrades to any system, it is recommended that you take a backup of the database and website first.
Tested with PHP 5.

*Automatic installation*

* go to Plugins->Add New and search for "Symposium"
* click on Install below WP-Symposium
* activate the plugins you want to use (core plugin MUST remain activated, and be activated first)
* important - go to the Options page to configure settings

*Manual installation*

* download from http://wordpress.org/extend/plugins/wp-symposium/
* unzip the contents of the ZIP file in your /wp-content/plugins folder
* activate the plugins you want via Plugins on the WordPress admin dashboard (including the core plugin)
* important - go to the Options page to configure

When upgrading manually, make sure you deactivate and re-activate the core plugin.

*Adding WP Symposium plugins to your site*

If you need to, create a new page in WordPress (Pages->Add New). Then put the following in a WordPress page (it's a hyphen, not an underscore):

For the Forum:

  `[symposium-forum]`
  
For the Member Profile:
  
  `[symposium-profile]`
  
For the Mail/Private Messaging:
  
  `[symposium-mail]`
  
Installation of the notification bar is just a matter of activating it.

IMPORTANT: Update settings on the Options page.

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

Q. The admin side works, but the forum (or another plugin) doesn't appear?
A. Check you have `[symposium-forum]` on your page, with a hyphen, not an underscore. Replace [symposium-forum] with the shortcode of the plugin.

Q. I've done that, but the forum (or another plugin) still doesn't appear?
A. Have you put `[symposium-forum]` on a WordPress page, not an HTML file? 

Q. My language isn't supported, can I add my language?
A. Go to http://www.wpsymposium.com/forum/add-new-forum-language and we will add the language with your help, thank you.

Q. Are there images other than smilies?
A. Yes, if you look in the smilies folder you can use any of the images there by putting {{xxx}} around the first part of the filename, eg: {{rofl}} would show rofl.png - to see the full list, go to the Forum Smilies page at http://www.wpsymposium.com/forum/forum-smilies

Q. Will WP Symposium work on WPMU?
A. Sorry no, not at the moment - but it's planned to get this working at some point.

Q. I don't see friends when there online, can't edit forum posts or read all mail in my inbox
A. Sounds like AJAX isn't working - check on the admin Health Check page.


== Changelog ==

= 0.1.22 =

* New Plugin: Members Directory/Search (basic for now, but will improve)
* Core: Re-wrote AJAX functions
* Core: Organised files with folder structure
* Core: Reduced number of SQL calls throughout
* Core: If profile plugin deactivated, language is taken from core options
* Admin: Added option to clear event audit table
* Admin: Added options to disable certain features (in case of plugin clashes/server load issues)
* Admin: Added option to disable password change
* Notification Bar: Added Register link if enabled in WordPress
* Notification Bar: Added Site Admin link if site administrator
* Profile: Added option to recieve email on new wall post/reply
* Profile: If birthday still system default (1/1/2010+) then not shown
* Forum: Fixed post author can delete as well as administrators

= 0.1.21 =

* Core: Added Custom redirect URL after logging in
* Core: Added Custom redirect URL after logging out
* Profile: Fixed birthday text

= 0.1.20.1 = 

* Profile: Added friends posts/comments to wall

= 0.1.20 =

* Profile: Added reply to wall posts
* Profile: Can update Email address
* Profile: Can update Display name
* Profile: Password change (currently logs you out to re-authenticate)
* Notification bar: Hides email/friends icons if plugins deactivated
* Notification bar: Added option to show only to logged in members
* Core: option to redirect user to a particular page
* Core: Fixed return value from "ago" for less than 1 second
* Core: Member's default language set to the default language
* Core: Added "ago" for Russian
* Core: Added alignment setting for all WPS plugins

= 0.1.19 =

* Profile: Added Wall with personal status and post comment (this is to be improved with replies, etc)
* Profile: Added custom profile extension fields (to be improved with delete, etc)
* Profile: Added friends and profile extension fields
* Profile: Added privacy for wall

= 0.1.18.1 =

* Forum: Fixed inclusion of functions file prior to function call

= 0.1.18 =

* Chat: Chatbox now shows friends status beside name
* Chat: Friends online status now updates, at notification polling refresh frequency
* Notification bar: Improved polling to reduce server load
* Notification bar: Busy image now hidden if not logged in
* Notification bar: Choose between login/logout via WP or your own URLs
* Notification bar: Choose between WP profile page or WPS profile page
* Core: Email subjects sent in recipients language
* Health Check: Added test for symposium_get_current_userlevel() function
* Languages: Updated Swedish (thanks to Mattias Dahl)
* Languages: Updated 'ago' in Portuguese (thanks to marclatino)
* Lanaguges: Added 'Requires Moderation', 'Friend Request', 'New Message'

= 0.1.17 =

* Friends: Added plugin
* Friends: Added %f as page replacement for pending friends (use in page titles)
* Notification Bar: Added friends online count
* Notification Bar: Added chat windows (click on friends name)
* Notification Bar: Added unread mail icon
* Notification Bar: Added new friends requests icon
* Mail: Added %m as page replacement for unread mail (use in page titles)
* Mail: Added avatar to received mail
* Mail: Added hyperlinks in forum email notifications instead of raw text
* Mail: Enchanced autocomplete to include city and country (and use jQuery UI)
* Member Profile (Personal tab): Added level of privacy (everyone/friends/no-one)
* Member Profile (Personal tab): Added Google Map displaying location
* Member Profile (Personal tab): Added birth date
* Member Profile (Personal tab): Added city and country member is currently in
* Member Profile (Settings tab): Added select language
* Member Profile (Settings tab): Added local time zone
* Member Profile (Settings tab): Added option to receive email notifications of new mail
* Member Profile (Settings tab): Added option to place notification bar top or bottom of screen
* Member Profile (Settings tab): Added option to change alert sound
* Admin Options: Added allow/disallow personal settings in Member Profile
* Code: Started on more Symposium functions to replace repeated code
* Code: Moved Symposium functions to external file
* and changed format of change log...

= 0.1.16.3 =

* Changed: Enhanced field check during activation
* Added: Paging on Audit Log

= 0.1.16.2 =

* Added: New Members widget
* Changed: Enhanced language field health check

= 0.1.16.1 =

* Added: Health check for languages option field
* Changed: Field modification on activation

= 0.1.16 =

* Added: Mail plugin
* Added: Member Profile plugin
* Added: Notification bar plugin
* Changed: creation/updates of tables simplified in core activation
* Added: Hungarian and Swedish languages

= 0.1.15 =

* Changed: Language fields set to TEXT to handle longer language versions and maximum row size in mySQL)

= 0.1.14.2 = 

* Changed: Enhanced moderation page to include all posts for quick administration

= 0.1.14.1 =

* Changed: Corrected 'replies' label on topic level of forum
* Changed: French language translation
* Added: Languages: Czech, Hungarian, Portuguese, Braxilian Portuguese, Norwegian, Dutch, Russian, Polish

= 0.1.14 =

* Added: Additional language fields

= 0.1.13 =

* Added: Moderation
* Changed: Languages no longer loaded from external XML file, built into plugin instead
* Added: Option to load jQuery
* Added: Option to enable smilies/emoticons
* Added: Option to use SEO friendly links
* Changed: Font settings now with each style template, not global options

= 0.1.12.1 =

* Added: Default internal language (English) in case external language XML file can't be opened (usually fopen and curl are both disabled on the server)

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
* Added: Italian language (thanks to Luca Trovato)
* Added: Turkish language (thanks to Cahit Cengizhan)
* Added: Czeck language (thanks to Va&#353;e jm&eacute;no)

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