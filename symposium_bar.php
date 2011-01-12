<?php
/*
Plugin Name: WP Symposium Notification Bar
Plugin URI: http://www.wpsymposium.com
Description: Bar along bottom of screen to display notifications on new messages, mail. Also controls live chat windows. Simply activate to add.
Version: 0.1.22
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

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
		$meta = get_symposium_meta_row($current_user->ID);
		
		if ( ($config->visitors == 'on') || (is_user_logged_in()) ) {
	
			$allow_personal_settings = $config->allow_personal_settings;
			if ($allow_personal_settings != "on") {
				$sound = $config->sound;
				$soundchat = $sound;
				$bar_position = $config->bar_position;
			} else {
				$sound = $meta->sound;
				$soundchat = $meta->soundchat;
				$bar_position = $meta->bar_position;			
			}
			$border_radius = $config->border_radius;
			$use_chat = $config->use_chat;
			$bar_polling = ($config->bar_polling*1000);
			$chat_polling = ($config->chat_polling*1000);
			$use_wp_profile = $config->use_wp_profile;
			$use_wp_login = $config->use_wp_login;
			$custom_login_url = $config->custom_login_url;
			$custom_logout_url = $config->custom_logout_url;
			$inactive = $config->online;
			$offline = $config->offline;
	
			include_once('symposium_functions.php');
	
			$get_language = symposium_get_language($current_user->ID);
			$language = $get_language['words'];
	
			// maximum number of chat windows
			$maxChatWindows = 3;
	
			?>
				
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
					border-radius: 0px;
					-moz-border-radius: 0px;
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
				
				#symposium-chatboxes {
					position:fixed;
					right:0px;
					<?php echo $bar_position; ?>:28px;
				}
	
				#symposium-chatboxes .chat_window {
					float: right;
					margin-left: 2px;
					border-radius: 0px;
					moz-border-radius: 0px;
					ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=90)";
					filter: alpha(opacity=90);
					-moz-opacity: 0.90;
					-khtml-opacity: 0.90;
					opacity: 0.90;
					width:180px;
					height:240px;
					padding:0px;
					border:1px solid #000;
					background-color: #fff;
				}
				
				#symposium-chatboxes #symposium-who-online {
					float: right;
					margin-left: 2px;
					border-radius: 0px;
					-moz-border-radius: 0px;
					-ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=90)";
					filter: alpha(opacity=90);
					-moz-opacity: 0.90;
					-khtml-opacity: 0.90;
				 	opacity: 0.90;
					width:180px;
					height:240px;
					padding:0px;
					border:1px solid #000;
					background-color: #fff;
				}
				#symposium-chatboxes *, #symposium-chatboxes * {
					font-family: tahoma,arial,helvetica;
					font-size: 12px;				
				}
				#symposium-chatboxes .display_name_link {
					text-decoration: none;
					color: #fff;
				}
	
				#symposium-who-online {
					display: none;
					padding: 2px;
				}
				#symposium-friends-online-list {
					height: 218px;
					max-height: 218px;
					overflow: auto;
				}
	
				.symposium_online_name {
					cursor:pointer;
				}
				.symposium_online_name:hover {
					text-decoration:underline;
				}
				.symposium_offline_name {
					text-decoration: none;
				}
				.symposium_offline_name:hover {
					text-decoration: underline;
				}
											
				.symposium-online-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					width: 18px;
					height:18px;
					background-color: #0f0;
					color: #000;
					text-align:center;
					float: right;
					margin-right:10px;
					cursor: pointer;
					font-family: tahoma,arial,helvetica;
					font-size: 10px;				
					font-weight: bold;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				.symposium-online-box-none {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					text-align:center;
					background-color: #000;
					color: #fff;
					border: 1px solid #fff;
					float: right;
					margin-right:10px;
					width: 17px;
					height: 17px;
					padding:0px;
					cursor: pointer;
					font-family: tahoma,arial,helvetica;
					font-size: 10px;
					font-weight: normal;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				
				.symposium-email-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					float:right;
					color: #000;
					text-align:center;
					padding:0px;
					cursor: pointer;
					font-family: tahoma,arial,helvetica;
					font-size: 10px;
					font-weight: bold;
					margin-right:10px;
					width: 18px;
					height: 18px;
					<?php if (!function_exists('symposium_mail')) {
						echo 'display: none';
					}?>
				}
				.symposium-email-box-read {
					background-image:url('<?php echo $plugin; ?>/images/email.gif');
				}
				.symposium-email-box-unread {
					background-image:url('<?php echo $plugin; ?>/images/emailunread.gif');
				}
	
				.symposium-friends-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					float:right;
					color: #000;
					text-align:center;
					padding:0px;
					cursor: pointer;
					font-family: tahoma,arial,helvetica;
					font-size: 10px;
					font-weight: bold;
					margin-right:10px;
					width: 18px;
					height: 18px;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				.symposium-friends-box-none {
					background-image:url('<?php echo $plugin; ?>/images/friends.gif');
				}
				.symposium-friends-box-new {
					background-image:url('<?php echo $plugin; ?>/images/friendsnew.gif');
				}
				.corners {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
				}
			</style>
			
			<!-- NOTIFICATION BAR -->
			<div id="symposium-notification-bar">
				<div id="icons" style="float: left">
					<?php
			        echo '<a href="http://www.wpsymposium.com" target="_blank"><img src="http://www.wpsymposium.com/wp-content/plugins/wp-symposium/images/icon_logo.gif" alt="Powered by WP Symposium" title="Powered by WP Symposium" /></a> Powered by WP Symposium';
			        if ($config->bar_label != '') {
				        echo ". ".$config->bar_label;
			        }
					?>
				</div>
	
				<?php if (is_user_logged_in()) {
					// Pending Friends
					if (function_exists('symposium_profile')) {
						echo "<div id='symposium-friends-box' title='Go to Friends' alt='Go to Friends' class='symposium-friends-box symposium-friends-box-none'>";
					} else {
						echo "<div id='symposium-friends-box' style='display:none'>";
					}
					echo "</div>";
					
					// Unread Mail
					if (function_exists('symposium_mail')) {
						echo "<div id='symposium-email-box' title='Go to Mail' alt='Go to Mail' class='symposium-email-box symposium-email-box-read'>";
					} else {
						echo "<div id='symposium-email-box' style='display:none'>";
					}
					echo "</div>";
	
					// Friends Status/Online
					echo "<div id='symposium-online-box' class='symposium-online-box-none'></div>";
				} ?>
	
				<!-- DIV for notification alerts -->
				<div id="alerts"></div>
				
				<!-- DIV for login/logout/etc links -->
				<div id="info">
					<?php
					if (is_user_logged_in()) {

						
						echo 'Logged in as ';
						if ($use_wp_profile == 'on') {
							echo '<a href="/wp-admin/profile.php">'.$current_user->user_login.'</a>';
						} else {
							echo '<a href="'.symposium_get_url("profile").'">'.$current_user->user_login.'</a>';
						}
						
						if (current_user_can('activate_plugins')) {
							echo wp_register('.&nbsp;', '');
						}
						
						echo '.&nbsp;';
						if ($use_wp_login == "on") {
							wp_loginout( '/index.php' );
						} else {
							echo '<a href="'.$custom_logout_url.'">Log out</a>';
						}
						echo '.&nbsp;';
													
					} else {
						
						if ($use_wp_login == "on") {
							echo "<a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>Login</a>";
						} else {
							echo '<a href="'.$custom_login_url.'">Login</a>';
						}

						echo wp_register('&nbsp;', '');

					}
	
					?>	
				</div>
				
			</div>
			
			<?php if (is_user_logged_in()) {
	
				echo "<div id='symposium-chatboxes'>";
		
					// DIV for who's online
					echo "<div id='symposium-who-online'>";
					
						echo "<div id='symposium-who-online_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
							echo "<div id='symposium-who-online_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='Close' /></div>";
						echo "Friends Status";
						echo "</div>";
						echo "<div id='symposium-friends-online-list'>";
						echo "</div>";
														
					echo "</div>";
					
					// set up chat windows (should match numChatWindows in function do_chat_check() in symposium_bar.js)
					if ($use_chat == 'on') {
						for ($w = 1; $w <= 3; $w++) {
							addChatWindow($w);
						}
					}
					
				echo "</div>";
			
			}
	
		}
	}

}  
add_action('wp_footer', 'add_notification_bar', 1);

/* ====================================================== PHP FUNCTIONS ====================================================== */

function addChatWindow($id) {
	
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	echo "<div id='chat".$id."' title='chat".$id."' class='chat_window' style='display:none'>";
		echo "<div id='chat".$id."_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
		echo "<div id='chat".$id."_to' style='display:none'></div>";
		echo "<div id='chat".$id."_close' class='chat_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='Close' /></div>";
		echo "<div id='chat".$id."_display_name'></div>";
		echo "</div>";
		echo "<div id='chat".$id."_message' style='width:176px; height:170px;overflow:auto;padding:2px;padding-bottom:7px;'>";
		echo "</div>";
		echo "<div style='width:180px; height:40px;'>";
			echo "<textarea id='chat".$id."_textarea' class='chat_message' onclick='if (this.value == \"Type here...\") { this.value=\"\"; }' style='background-color:#efefef;border:0px;width:176px;height:34px;'>Type here...</textarea>";
		echo "</div>";
	echo "</div>";
	
}


?>
