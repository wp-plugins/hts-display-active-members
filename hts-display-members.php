<?php

/*
Plugin Name: HTS Display Members
Plugin URI: http://www.htsweb.net/projects/plug-ins
Description: Display members with the choice of what to display, and how many to show. Includes private message links for logged in members
Version: 0.1
Author: Simon Goodchild
Author URI: http://www.htsweb.net/about/
License: GPL2
*/

/*  GPL2 Licence
    
    Copyright 2010  Simon Goodchild  (email : simon.goodchild@mac.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    or visit http://www.gnu.org/licenses/gpl.html
    
*/

function display_members($array) {
	
   	extract(shortcode_atts(array('count' => 5, 'display' => 'display_name', 'emaillink' => 'true', 'emailicon' => 'true', 'avatar_size' => 50, 'debug' => 'false'), $array));

	global $wpdb, $current_user;
	wp_get_current_user();
	$table = $wpdb->prefix . 'users';
	$meta = $wpdb->prefix . 'usermeta';
	$usemeta = ($display == 'display_name' || $display == 'user_nicename' || $display == 'user_email') ? 'false' : 'true';

	$sql = 'SELECT '.$table.'.*, '.$meta.'.* FROM '.$table.', '.$meta.' WHERE '.$meta.'.meta_key = "last_activity" AND '.$meta.'.user_id = '.$table.'.ID ORDER BY '.$meta.'.meta_value DESC LIMIT 0,'.$count;
   	$result = $wpdb->get_results($sql);

	// Start recording HTML
	$html = '';
	
	// If debugging show info
	if ($debug == 'true') {
		
		$html .= '<strong>Debug Information</strong><br />';
		$html .= $sql.'<br />';
		$html .= 'count='.$count.'<br />';
		$html .= 'display='.$display.'<br />';
		$html .= 'usemeta='.$usemeta.'<br />';
		$html .= 'WP_PLUGIN_URL='.WP_PLUGIN_URL.'<br />';
		$html .= 'WP_PLUGIN_DIR='.WP_PLUGIN_DIR.'<br />';
	    $myStyleFile = STYLESHEETPATH . '/hts-display-members/hts-display-members.css';
		$html .= 'STYLESHEETPATH='.$myStyleFile;
	   	if ( file_exists($myStyleFile) ) { $html .= ' (Exists)'; } else { $html .= ' (Not Found)'; }
		$html .= '<br />';
		$html .= '<br /><strong>Results:</strong><br /><br />';
	}
	
	// Start list
	$html .= '<ul id="hts_displaymembers" style="list-style-type:none">';
	foreach ($result as $row) {
		
		// Start item, and first line of HTML
      	$html .= '<li style="overflow: hidden"><a href="'.$row->user_url.'">'.get_avatar($row->ID, $avatar_size).'</a>';
      	if ($usemeta == 'true') {
      		$sql = 'SELECT meta_value FROM '.$meta.' WHERE user_id = '.$row->ID.' AND meta_key = "'.$display.'"';
  				$meta_result = $wpdb->get_results($sql);
	      	$show = $meta_result[0]->meta_value;
      	} else {
	      	$show = $row->$display;
      	}
      	$html .= '<h2><a href="'.$row->user_url.'">'.$show.'</a></h2>';

		// Calculate various times since last activity
		$second = 1; 
		$minute = $second*60; 
		$hour = $minute*60; 
		$day = $hour*24; 
		$week = $day*7;
		$time = time();
		$offset = strtotime($row->meta_value);
		$difference = $time-$offset;
		$wcount = 0; 
		for($wcount = 0; $difference>$week; $wcount++) { $difference = $difference - $week; } 
		$dcount = 0; 
		for($dcount = 0; $difference>$day; $dcount++) { $difference = $difference - $day; } 
		$hcount = 0; 
		for($hcount = 0; $difference>$hour; $hcount++) { $difference = $difference - $hour; } 
		$mcount = 0; 
		for($mcount = 0; $difference>$minute; $mcount++) { $difference = $difference - $minute; }
		
		$count = $difference.' seconds ago';
		if ($mcount == 1) { $count = '1 minute ago'; }
		if ($mcount > 1) { $count = $mcount.' minutes ago'; }
		if ($hcount == 1) { $count = 'about an hour ago'; }
		if ($hcount > 1) { $count = $hcount.' hours ago'; }
		if ($dcount == 1) { $count = 'Yesterday'; }
		if ($dcount > 1) { $count = $dcount.' days ago'; }
		if ($wcount == 1) { $count = ' about a week ago'; }
		if ($wcount > 1) { $count = $wcount.' weeks ago'; }

		// Start second line HTML
      	$html .= '<span class="lastactive">';
      	$html .= 'Last active: '.$count;
      
      	// Add PM link if logged in
      	if ( ( is_user_logged_in() ) && ( $emaillink == 'true' ) ) {
      		if ($emailicon == 'true') {
			  	$plugin_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    		  	$html .= ' <img src="'.$plugin_url.'email_icon.gif" alt="Email Icon" />';
      		}
    	  	$html .= ' <a href="/members/'.$current_user->user_login.'/messages/compose/?r='.$row->user_login.'">Send Message</a>';
      	}
      
      // Close second line
      $html .= '</span></li>';
	}
   	$html .= '</ul>';
   	
   	
	
   	return $html;
}
add_shortcode('hts-displaymembers', 'display_members');  


/*register with hook 'wp_print_styles' to add stylesheet */
add_action('wp_print_styles', 'add_hts_stylesheet');
function add_hts_stylesheet() {
   $myStyleUrl = WP_PLUGIN_URL . '/hts-display-members/hts-display-members.css';
   $myStyleFile = WP_PLUGIN_DIR . '/hts-display-members/hts-display-members.css';
   if ( file_exists($myStyleFile) ) {
       wp_register_style('myStyleSheets', $myStyleUrl);
       wp_enqueue_style( 'myStyleSheets');
   }
}

/*register with hook 'wp_print_styles' to add child theme stylesheet if there */
add_action('wp_print_styles', 'add_child_theme_stylesheet');
function add_child_theme_stylesheet() {

	$css_url = dirname( get_bloginfo('stylesheet_url') );
   	$myStyleUrl = $css_url . '/hts-display-members/hts-display-members.css';
    $myStyleFile = STYLESHEETPATH . '/hts-display-members/hts-display-members.css';
   	if ( file_exists($myStyleFile) ) {
       wp_register_style('my2StyleSheets', $myStyleUrl);
       wp_enqueue_style( 'my2StyleSheets');
   	}
}




?>