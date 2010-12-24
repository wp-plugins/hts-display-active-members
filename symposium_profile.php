<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Put [symposium-profile] on any WordPress page to display forum.
Version: 0.1.16.1
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL2
*/
	
/*  Copyright 2010  Simon Goodchild  (info@wpsymposium.com)

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

/* ====================================================== PHP FUNCTIONS ====================================================== */

// Adds notification bar
function symposium_profile()  
{  
	if (!is_admin()) {

	   	global $wpdb, $current_user;
		wp_get_current_user();
		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
		} else {
		$uid = $current_user->ID;
		}

		$plugin = WP_PLUGIN_URL.'/wp-symposium';
		$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."users WHERE ID=".$uid));
		$meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_usermeta WHERE ID=".$uid));

		$html = "";

		// Include styles	
		include_once('symposium_styles.php');

		// Language
		$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . "symposium_config"));
		$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");

		// Wrapper
		$html .= "<div id='symposium-wrapper' style='padding:0px;'>";

			$html .= "<div class='style='padding:0px;'>";

				// Detais
				$html .= "<div style='float: left; width: 100%;'>";
				$html .= "<div id='profile_details' style='margin-left: 150px;'>";
				$html .= "<h1>".$user->display_name."</h1>";

				// Buttons
				$html .= "<div width: 100%;'>";

					// Send mail
					if ( ($uid != $current_user->ID) && (is_user_logged_in()) ) {
						$html .='<input type="button" value="Send Mail" class="button" onclick="document.location = \''.$mail_url.'?view=compose&to='.$uid.'\';">';
					}

				$html .= "</div>"; // End of buttons
				
				$html .= "</div></div>";
				
				// Photo
				$html .= "<div id='profile_photo' style='float:left;width:150px;margin-left:-100%;'>";
				$html .= get_avatar($uid, 128);
				$html .= "</div>";
			
			$html .= "</div>";


			// Notices
			$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->sav."</div>";
			$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->pw."</div>";
		
		$html .= "</div>";
											
		return $html;
		exit;
	}
}  

/* ====================================================== AJAX FUNCTIONS ====================================================== */

// Check for new mail, forum messages, etc
function xxx() {

	exit;
}
add_action('wp_ajax_xxx', 'xxx');


/* ====================================================== ADMIN/ACTIVATE/DEACTIVATE ====================================================== */

function symposium_profile_activate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>5, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Profile activated.'));
	} else {
	    wp_die( __('Core plugin must be actived first.') );
	}

}

function symposium_profile_deactivate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>6, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Profile de-activated.'));
	}

}

register_activation_hook(__FILE__,'symposium_profile_activate');
register_deactivation_hook(__FILE__, 'symposium_profile_deactivate');

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-profile', 'symposium_profile');  


?>
