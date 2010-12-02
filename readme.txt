=== Plugin Name ===
Author: Simon Goodchild
Contributors: Simon Goodchild
Donate link: http://www.htsweb.net
Link: http://www.htsweb.net/projects/plug-ins
Tags: recent, latest, active, members, buddypress
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 0.2

Displays most recently logged in members and optionally link to Private Message if logged in. Requires BuddyPress.

== Description ==

Requires BuddyPress.

Allows a page or post to display the most recently logged in members with how long since they last did any activity on the site. Options include showing a link to Private Message the member if logged in, the field to display as the members name and so on. 

A live demonstration is available at [BlueFlipper Diving](http://www.blueflipperdiving.co.uk/ "Where Scuba Divers Go Online!")

All options:

1. To change the number of members shown (defaults to 5):
 
  `[hts-displaymembers count=10]`
  
2. To change the displayed field (defaults to Display Name):

  `[hts-displaymembers count=10 display=user_nicename]`
  
3. You can use any field in the wp_users table or wp_usermeta table, for example:

  - display_name
  - user_nicename
  - user_email
  - user_url
  - first_name
  - last_name
  - nickname 

4. To change the size of the avatar (defaults to 50px):

  `[hts-displaymembers avatar_size=128]`

5. To not display the email icon (default to true):

  `[hts-displaymembers emailicon=false]`

6. To not display the email link (default to true):

  `[hts-displaymembers emaillink=false]`

7. To display debug information (default to false):

  `[hts-displaymembers debug=true]`
  


== Installation ==

1. Upload the plug-in folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


*Including in a page or post*

To call it with a shortcode, put the following in a page or post:

  `[hts-displaymembers]`
  

To add options as in the description, pass as parameters.
See plug-in description for all options. eg:

  `[hts-displaymembers count=10 display=user_nicename]`



*Including in a theme*

To call it from within a theme, you have to wrap it in this PHP function: 

`<?php echo do_shortcode('[hts-displaymembers count="10" field="display_name"]'); ?>`


== Screenshots ==

1. Screenshot of a recent active member list
2. Bigger screenshot showing within a larger page

== Frequently Asked Questions ==

Q. How do I change the style of the list?
A. In the plugin folder is a .css file that you can edit, remember to keep your changes and re-apply them after updating the plugin in future.
   
   You can confirm the path for the file by switching on the debug information (see Plug-in
   description) and looking for the value for STYLESHEETPATH. This is the file you need to create.


== Changelog ==

= 0.1 =

First version, no changes yet

= 0.2 =

1. Added check for BuddyPress installation
2. Fixed debug check for stylesheet availability
3. Updated readme to state BuddyPress is required

== Upgrade Notice ==

See change log.