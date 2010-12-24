<?php
/*
Plugin Name: WP Symposium Notification Bar
Plugin URI: http://www.wpsymposium.com
Description: Bar along bottom of screen to display notifications on new messages, forum posts, etc. Simply activate to add.
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
function add_notification_bar()  
{  
	if (!is_admin()) {

	   	global $wpdb, $current_user;
		wp_get_current_user();

		$plugin = WP_PLUGIN_URL.'/wp-symposium';
		$sound = $wpdb->get_var($wpdb->prepare("SELECT sound FROM ".$wpdb->prefix . 'symposium_config'));
		$bar_position = $wpdb->get_var($wpdb->prepare("SELECT bar_position FROM ".$wpdb->prefix . 'symposium_config'));
		?>
	
		<script type="text/javascript" src="<?php echo $plugin; ?>/soundmanager/soundmanager2-jsmin.js"></script>
		<script type="text/javascript">
		
			soundManager.url = '<?php echo $plugin; ?>/soundmanager/soundmanager2.swf'; // override default SWF url
			soundManager.debugMode = false;
			soundManager.consoleOnly = false;
					
		</script>	
	
		<style>
			#symposium-notification-bar {
				position:fixed;
				left:0px;
				<?php echo $bar_position; ?>:0px;
				padding: 4px;
				width: 100%;
				height: 20px;
				font-size: 14px;
				font-family: arial,helvetica;
				background-color: #000;
				color: #fff;
				-ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=75)";
				filter: alpha(opacity=75);
				-moz-opacity: 0.75;
				-khtml-opacity: 0.75;
			 	opacity: 0.75;
			}
			
			#symposium-notification-bar #icons img {
				margin-right: 8px;		
				float: left;		
			}
			
			#symposium-notification-bar #alerts, #symposium-notification-bar #info {
				float: right;
				margin-right: 8px;
			}
			#symposium-notification-bar #alerts {
				display: none;
			}
			
			#symposium-notification-bar #alerts a, #symposium-notification-bar #info a {
				color: #fff;
				text-decoration:none;
			}
			#symposium-notification-bar #alerts a:hover, #symposium-notification-bar #info a:hover {
				color: #f00;
			}
		</style>
		<div id="symposium-notification-bar">
			<div id="icons" style="float: left">
				<?php
				$bar_label = $wpdb->get_var($wpdb->prepare("SELECT bar_label FROM ".$wpdb->prefix . 'symposium_config'));
		        $bar_label = str_replace('[logo]', '<img src="/wp-content/plugins/wp-symposium/images/icon_logo.gif" alt="WP Symposium" />', $bar_label);
		        echo $bar_label;
				?>
			</div>
			<div id="alerts">
			</div>
			<div id="info">
				<?php
				if (is_user_logged_in()) {
					echo 'Logged in as <a href="/wp-admin/profile.php">'.$current_user->user_login.'</a>.&nbsp;';
					wp_loginout( '/index.php' );
					echo '.';
				} else {
					echo "<a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>Login</a>";
				}
				?>	
			</div>
		</div>

		<script type="text/javascript">
		    jQuery(document).ready(function() { 	
				var refreshId = setInterval(function()
			   	{
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"checkForNotifications", 
						tray:"in",
						'mid':1
						},
					function(str)
					{
						if (str != '' && str != '-1') {
							jQuery('#info').hide().delay(11000).fadeIn('slow');
				    		jQuery('#alerts').html(str);
				    		if ('<?php echo $sound; ?>' != 'None') {
								soundManager.play('Alert','<?php echo $plugin; ?>/soundmanager/<?php echo $sound; ?>')
				    		}
							jQuery('#alerts').fadeIn('fast').delay(10000).fadeOut('slow');
						}
					});
					
			   	}, 15000);
				   
		    });		    
	    </script> 
		
		<?php
	}
}  
add_action('wp_footer', 'add_notification_bar', 1);

/* ====================================================== AJAX FUNCTIONS ====================================================== */

// Check for new mail, forum messages, etc
function checkForNotifications() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));
	$return = '';
	
	$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE m.mail_to = ".$current_user->ID." AND m.mail_in_deleted != 'on' AND m.mail_read != 'on' AND m.mail_notified != 'on'");
	
	if ($count == 1) {
		$newmail = $wpdb->get_row("SELECT u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE m.mail_to = ".$current_user->ID." AND m.mail_in_deleted != 'on' AND m.mail_read != 'on' AND m.mail_notified != 'on'");
		$return = '<a href="'.$mail_url.'">You have a new mail message from '.stripslashes($newmail->display_name).'...</a>';
	} 

	if ($count > 1) {
		$return = '<a href="'.$mail_url.'">You have more than one new mail message...</a>';
	} 
	
	if ($count >= 1) {
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_notified = 'on' WHERE mail_to = ".$current_user->ID) );
	} else {
		$return = '';
	}
	
	echo $return;
	exit;
}
add_action('wp_ajax_checkForNotifications', 'checkForNotifications');


/* ====================================================== ADMIN/ACTIVATE/DEACTIVATE ====================================================== */

function symposium_bar_activate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>5, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Notification bar activated.'));
	} else {
	    wp_die( __('Core plugin must be actived first.') );
	}

}

function symposium_bar_deactivate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>6, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Notification bar de-activated.'));
	}

}

register_activation_hook(__FILE__,'symposium_bar_activate');
register_deactivation_hook(__FILE__, 'symposium_bar_deactivate');



?>
