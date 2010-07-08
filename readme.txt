=== Plugin Name ===
Author: Simon Goodchild
Contributors: Simon Goodchild
Donate link: http://www.htsweb.net
Link: http://www.htsweb.net/projects/plug-ins
Tags: recent, latest, active, members, buddypress
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 0.1

Displays most recently logged in members and link to PM if logged in

== Description ==

Allows a page or post to display the most recently logged in members with how log since they last did any activity on the site. Options include showing a link to Private Message the member if logged in, the field to display as the members name and so on. 

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



== Frequently Asked Questions ==

Q. How do I change the style of the list?
A. Create a folder in your theme called hts-display-members. In that folder, create a file called
   hts-display-members.css and add the following (which is the default), and then change to 
   what you want.

`   /* First line (name) */
   #hts_displaymembers li { margin-bottom: 10px; }
   #hts_displaymembers li h2 { margin: 1px 0px 0px 0px; text-shadow: none; }
   #hts_displaymembers li h2 a { text-decoration: none; }
   
   /* Avatar image in list item */
   #hts_displaymembers li img { margin-right: 10px; }
   /* Email icon image in second line of list item */
   #hts_displaymembers li .lastactive img { margin: 0px 0px 0px 10px; }`
   
   You can confirm the path for the file by switching on the debug information (see Plug-in
   description) and looking for the value for STYLESHEETPATH. This is the file you need to create.


== Changelog ==

= 0.1 =

First version, no changes yet

== Upgrade Notice ==

Not applicable until there is a new version!