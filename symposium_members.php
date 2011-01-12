<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 0.1.22
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL2
*/
	
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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
*/

function symposium_members() {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_members_db.php';
	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];

	include_once('symposium_styles.php');

	/* ================================================== POST BACKS ==================================================== */

	if ($_POST['member_id'] != '') {
		$profile = symposium_get_url('profile');
			
	}

	/* ================================================================================================================== */

	if (is_user_logged_in()) {
	}

	$html .= '<div id="symposium-wrapper">';

		$html .= '<form method="post" action="'.$dbpage.'"> ';

		$html .= '<div style="float:right; padding:0px;">';
		$html .= '<input type="submit" class="button" style="float: left; height:46px;" value="Go" />';
		$html .= '</div>';

		$html .= '<input type="text" id="member" name="member" class="new-topic-subject-input" style="width:75%" onfocus="this.value = \'\';" value="Search..." />';
		$html .= '<input type="hidden" id="member_id" name="member_id" />';
		
		$html .= '</form>';

		$html .= '<div id="symposium_members"><img src="'.$plugin.'/images/busy.gif" /></div>';
		
		// Notices
		$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin_dir."images/busy.gif' /> ".$language->sav."</div>";
		$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin_dir."images/busy.gif' /> ".$language->pw."</div>";

	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-members', 'symposium_members');  



?>
