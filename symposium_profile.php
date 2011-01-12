<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile] on any WordPress page to display forum.
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
		$dbpage = $plugin.'/symposium_profile_db.php';
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."users WHERE ID=".$uid));
		$meta = get_symposium_meta_row($current_user->ID);					
		
		$mail_url = $config->mail_url;
	
		// Work out this page
		$thispage = get_permalink();
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		$profile_url = $config->profile_url;
			
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$thispage = $profile_url;
			$q = "&";
		} else {
			$q = "?";
		}
	
		$html = "";

		// Includes
		include_once('symposium_styles.php');
		include_once('symposium_functions.php');

		// Language
		$get_language = symposium_get_language($current_user->ID);
		$language_key = $get_language['key'];
		$language = $get_language['words'];

		// Wrapper
		$html .= "<div id='symposium-wrapper'>";

			// Tabs
			if (is_user_logged_in()) {
			
				if ($uid == $current_user->ID) {
	
					// Set tabs
					$settings_active = 'active';
					$personal_active = 'inactive';
					$friends_active = 'inactive';
					$wall_active = 'inactive';
					$view = "settings";
					if ($_GET['view'] == 'friends') {
						$settings_active = 'inactive';
						$personal_active = 'inactive';
						$friends_active = 'active';
						$wall_active = 'inactive';
						$view = "friends";
					} 
					if ( (!isset($_GET['view'])) || ($_GET['view'] == 'wall') ) {
						$settings_active = 'inactive';
						$personal_active = 'inactive';
						$friends_active = 'inactive';
						$wall_active = 'active';
						$view = "wall";
					} 
					if ($_GET['view'] == 'personal') {
						$settings_active = 'inactive';
						$personal_active = 'active';
						$friends_active = 'inactive';
						$wall_active = 'inactive';
						$view = "personal";
					} 
					if ($_GET['view'] == "settings") {
						$settings_active = 'active';
						$personal_active = 'inactive';
						$friends_active = 'inactive';
						$wall_active = 'inactive';
						$view = "settings";
					} 
					
					// Check for pending friends
					$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends f WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on'");
				
					if ($pending_friends > 0) {
						$pending_friends = " (".$pending_friends.")";
					} else {
						$pending_friends = "";
					}
					
					$html .= '<div id="mail_tabs">';
					$html .= '<div class="mail_tab nav-tab-'.$settings_active.'"><a href="'.$thispage.$q.'view=settings" class="nav-tab-'.$settings_active.'-link">Settings</a></div>';
					$html .= '<div class="mail_tab nav-tab-'.$personal_active.'"><a href="'.$thispage.$q.'view=personal" class="nav-tab-'.$personal_active.'-link">Personal</a></div>';
					$html .= '<div class="mail_tab nav-tab-'.$friends_active.'"><a href="'.$thispage.$q.'view=friends" class="nav-tab-'.$friends_active.'-link">Friends'.$pending_friends.'</a></div>';
					$html .= '<div class="mail_tab nav-tab-'.$wall_active.'"><a href="'.$thispage.$q.'view=wall" class="nav-tab-'.$wall_active.'-link">Wall</a></div>';
					$html .= '</div>';
					
					$html .= '<div id="mail-main">';

						if ($_GET['msg']) {
							$html .= "<div class='alert'>".addslashes($_GET['msg'])."</div>";
						}
					
						$html .= symposium_profile_header($uid, $current_user->ID, $mail_url, $current_user->display_name);
						
						// Wall
						if ($view == 'wall') {
							$html .= symposium_profile_body($uid, $current_user->ID, $row_color);
						}
											
						// Settings
						if ($view == 'settings') {
							
							$allow_personal_settings = $config->allow_personal_settings;
							
							// get values
							if ($allow_personal_settings == "on") {	
								$sound = $meta->sound;
								$soundchat = $meta->soundchat;
								$bar_position = $meta->bar_position;
								$lang = $meta->language;
							}
							
							$timezone = $meta->timezone;
							$notify_new_messages = $meta->notify_new_messages;
							$notify_new_wall = $meta->notify_new_wall;
							
							$html .= "<div style='clear:both'>";
							
								$html .= '<form method="post" action="'.$dbpage.'"> ';
									$html .= '<input type="hidden" name="symposium_update" value="U">';
									$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
								
									$html .= '<div id="symposium_settings_table" style="padding-top: 15px">';
									
										// Language
										$html .= '<div style="clear:both">';
											$html .= 'Select a language for screen messages';
											$html .= '<div style="float:right;">';
												$html .= '<select name="language">';
												$language_options = $wpdb->get_results("SELECT DISTINCT language FROM ".$wpdb->prefix.'symposium_lang');
												if ($language_options) {
													foreach ($language_options as $option)
													{
														$html .= "<option value='".$option->language."'";
														if ($lang == $option->language) { $html .= ' SELECTED'; }
														$html .= ">".$option->language."</option>";
													}
												}		
												$html .= '</select>';
												$html .= '</div>';
										$html .= '</div>';
				
										// Time zone adjustment
										$html .= '<div style="clear:both">';
											$html .= 'Your local time zone adjustment in hours (difference from GMT which is '.date('jS \of M h:i:s A').')';
											$html .= '<div style="float:right;">';
												$html .= '<select name="timezone">';
												for ($i = -12; $i <= 14; $i++) {
													$html .= "<option value='".$i."'";
														if ($timezone == $i) { $html .= ' SELECTED'; }
														$html .= '>'.$i.'</option>';
												}
												$html .= '</select>';									
											$html .= '</div>';
										$html .= '</div>';
				
										if ( function_exists('add_notification_bar') ) {
											
											// Sound alert
											$html .= '<div style="clear:both">';
												$html .= 'Notification bar alert that sounds when you get new mail, relevant forum posts, etc';
												$html .= '<div style="float:right;">';
													$html .= '<select name="sound">';
														$html .= "<option value='None'";
															if ($sound == 'None') { $html .= ' SELECTED'; }
															$html .= '>None</option>';
														$html .= "<option value='baby.mp3'";
															if ($sound == 'baby.mp3') { $html .= ' SELECTED'; }
															$html .= '>Baby</option>';
														$html .= "<option value='beep.mp3'";
															if ($sound == 'beep.mp3') { $html .= ' SELECTED'; }
															$html .= '>Beep</option>';
														$html .= "<option value='bell.mp3'";
															if ($sound == 'bell.mp3') { $html .= ' SELECTED'; }
															$html .= '>Bell</option>';
														$html .= "<option value='buzzer.mp3'";
															if ($sound == 'buzzer.mp3') { $html .= ' SELECTED'; }
															$html .= '>Buzzer</option>';
														$html .= "<option value='chime.mp3'";
															if ($sound == 'chime.mp3') { $html .= ' SELECTED'; }
															$html .= '>Chime</option>';
														$html .= "<option value='doublechime.mp3'";
															if ($sound == 'doublechime.mp3') { $html .= ' SELECTED'; }
															$html .= '>Double Chime</option>';
														$html .= "<option value='dudeyougotmail.mp3'";
															if ($sound == 'dudeyougotmail.mp3') { $html .= ' SELECTED'; }
															$html .= '>Dude! You got mail!</option>';
														$html .= "<option value='hacksaw.mp3'";
															if ($sound == 'hacksaw.mp3') { $html .= ' SELECTED'; }
															$html .= '>Hacksaw</option>';
														$html .= "<option value='incoming.mp3'";
															if ($sound == 'incoming.mp3') { $html .= ' SELECTED'; }
															$html .= '>Incoming!</option>';
														$html .= "<option value='tap.mp3'";
															if ($sound == 'tap.mp3') { $html .= ' SELECTED'; }
															$html .= '>Tap</option>';
														$html .= "<option value='youvegotmail.mp3'";
															if ($sound == 'youvegotmail.mp3') { $html .= ' SELECTED'; }
															$html .= ">You've got mail</option>";
													$html .= '</select>';									
												$html .= '</div>';								
											$html .= '</div>';
											
											// Sound alert (for chat)
											$html .= '<div style="clear:both">';;
												$html .= 'Notification bar alert that sounds when a new chat message arrives';
												$html .= '<div style="float:right;">';
													$html .= '<select name="soundchat">';
														$html .= "<option value='None'";
															if ($soundchat == 'None') { $html .= ' SELECTED'; }
															$html .= '>None</option>';
														$html .= "<option value='baby.mp3'";
															if ($soundchat == 'baby.mp3') { $html .= ' SELECTED'; }
															$html .= '>Baby</option>';
														$html .= "<option value='beep.mp3'";
															if ($soundchat == 'beep.mp3') { $html .= ' SELECTED'; }
															$html .= '>Beep</option>';
														$html .= "<option value='bell.mp3'";
															if ($soundchat == 'bell.mp3') { $html .= ' SELECTED'; }
															$html .= '>Bell</option>';
														$html .= "<option value='buzzer.mp3'";
															if ($soundchat == 'buzzer.mp3') { $html .= ' SELECTED'; }
															$html .= '>Buzzer</option>';
														$html .= "<option value='chime.mp3'";
															if ($soundchat == 'chime.mp3') { $html .= ' SELECTED'; }
															$html .= '>Chime</option>';
														$html .= "<option value='doublechime.mp3'";
															if ($soundchat == 'doublechime.mp3') { $html .= ' SELECTED'; }
															$html .= '>Double Chime</option>';
														$html .= "<option value='dudeyougotmail.mp3'";
															if ($soundchat == 'dudeyougotmail.mp3') { $html .= ' SELECTED'; }
															$html .= '>Dude! You got mail!</option>';
														$html .= "<option value='hacksaw.mp3'";
															if ($soundchat == 'hacksaw.mp3') { $html .= ' SELECTED'; }
															$html .= '>Hacksaw</option>';
														$html .= "<option value='incoming.mp3'";
															if ($soundchat == 'incoming.mp3') { $html .= ' SELECTED'; }
															$html .= '>Incoming!</option>';
														$html .= "<option value='tap.mp3'";
															if ($soundchat == 'tap.mp3') { $html .= ' SELECTED'; }
															$html .= '>Tap</option>';
														$html .= "<option value='youvegotmail.mp3'";
															if ($soundchat == 'youvegotmail.mp3') { $html .= ' SELECTED'; }
															$html .= ">You've got mail</option>";
													$html .= '</select>';									
												$html .= '</div>';								
											$html .= '</div>';
											
											// Bar position
											$html .= '<div style="clear:both">';
												$html .= 'Where do you want the notification bar?';
												$html .= '<div style="float: right;">';
													$html .= '<select name="bar_position">';
														$html .= "<option value='bottom'";
															if ($bar_position == 'bottom') { $html .= ' SELECTED'; }
															$html .= '>Bottom</option>';
														$html .= "<option value='top'";
															if ($bar_position == 'top') { $html .= ' SELECTED'; }
															$html .= '>Top</option>';
													$html .= '</select>';
												$html .= '</div>';
											$html .= '</div>';	
											
										}

										// Display name
										$html .= '<div style="clear:both; margin-top:15px;">';
											$html .= 'Your name as shown';
											$html .= '<div style="float:right;">';
												$html .= '<input type="text" class="input-field" name="display_name" value="'.$current_user->display_name.'">';
											$html .= '</div>';
										$html .= '</div>';
										
										// Email address
										$html .= '<div style="clear:both; margin-top:15px;">';
											$html .= 'Your email address';
											$html .= '<div style="float:right;">';
												$html .= '<input type="text" class="input-field" name="user_email" style="width:300px" value="'.$current_user->user_email.'">';
											$html .= '</div>';
										$html .= '</div>';
										
										// Email notifications
										$html .= '<div style="clear:both;">';
											$html .= 'Do you want to receive an email when you get new mail messages?';
											$html .= '<div style="float:right;">';
												$html .= '<input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
													if ($notify_new_messages == "on") { $html .= "CHECKED"; }
													$html .= '/>';
											$html .= '</div>';
										$html .= '</div>';

										// Email wall
										$html .= '<div style="clear:both;">';
											$html .= 'Do you want to receive an email when a friend adds a new wall post or reply?';
											$html .= '<div style="float:right;">';
												$html .= '<input type="checkbox" name="notify_new_wall" id="notify_new_wall"';
													if ($notify_new_wall == "on") { $html .= "CHECKED"; }
													$html .= '/>';
											$html .= '</div>';
										$html .= '</div>';
																					
										// Password
										if ($config->enable_password == "on") {
											$html .= '<div class="sep"></div>';
											$html .= '<div style="clear:both; margin-top:15px;">';
												$html .= 'Change your password';
												$html .= '<div style="float:right;">';
													$html .= '<input type="text" class="input-field" name="xyz1" value="">';
												$html .= '</div>';
											$html .= '</div>';
											$html .= '<div style="clear:both">';
												$html .= 'Re-enter to confirm';
												$html .= '<div style="float:right;">';
													$html .= '<input type="text" class="input-field" name="xyz2" value="">';
												$html .= '</div>';
											$html .= '</div>';
																						
										}
									
									$html .= '</div> ';
									 
									$html .= '<p style="clear: both; padding-top:15px;" class="submit"> ';
									$html .= '<input type="submit" name="Submit" class="button" value="Save" /> ';
									$html .= '</p> ';
									$html .= '</form> ';
							
							$html .= "</div>";
		
						}
		
						// Personal
						if ($view == 'personal') {
							
							// get values
							$dob_day = $meta->dob_day;
							$dob_month = $meta->dob_month;
							$dob_year = $meta->dob_year;
							$city = $meta->city;
							$country = $meta->country;
							$share = $meta->share;
							$wall_share = $meta->wall_share;
							$extended = $meta->extended;
							
							$html .= "<div style='clear:both'>";
							
								$html .= '<form method="post" action="'.$dbpage.'"> ';
									$html .= '<input type="hidden" name="symposium_update" value="P">';
									$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
								
									$html .= '<div id="symposium_settings_table" style="padding-top: 15px">';
									
										// Sharing personal information
										$html .= '<div style="clear:both;">';
											$html .= 'Who do you want to share personal information with?';
											$html .= '<div style="float:right;">';
												$html .= '<select name="share">';
													$html .= "<option value='Nobody'";
														if ($share == 'Nobody') { $html .= ' SELECTED'; }
														$html .= '>Nobody</option>';
													$html .= "<option value='Friends only'";
														if ($share == 'Friends only') { $html .= ' SELECTED'; }
														$html .= '>Friends only</option>';
													$html .= "<option value='Everyone'";
														if ($share == 'Everyone') { $html .= ' SELECTED'; }
														$html .= '>Everyone</option>';
												$html .= '</select>';
											$html .= '</div>';
										$html .= '</div>';
										
										// Sharing wall
										$html .= '<div style="clear:both;">';
											$html .= 'Who do you want to share your wall with?';
											$html .= '<div style="float:right;">';
												$html .= '<select name="wall_share">';
													$html .= "<option value='Nobody'";
														if ($wall_share == 'Nobody') { $html .= ' SELECTED'; }
														$html .= '>Nobody</option>';
													$html .= "<option value='Friends only'";
														if ($wall_share == 'Friends only') { $html .= ' SELECTED'; }
														$html .= '>Friends only</option>';
													$html .= "<option value='Everyone'";
														if ($wall_share == 'Everyone') { $html .= ' SELECTED'; }
														$html .= '>Everyone</option>';
												$html .= '</select>';
											$html .= '</div>';
										$html .= '</div>';
										
										// Birthday
										$html .= '<div style="clear:both">';
											$html .= 'Your date of birth (day/month/year)';
											$html .= '<div style="float:right;">';
												$html .= "<select name='dob_day'>";
													for ($i = 1; $i <= 31; $i++) {
														$html .= "<option value='".$i."'";
															if ($dob_day == $i) { $html .= ' SELECTED'; }
															$html .= '>'.$i.'</option>';
													}
												$html .= '</select> / ';									
												$html .= "<select name='dob_month'>";
													for ($i = 1; $i <= 12; $i++) {
														$html .= "<option value='".$i."'";
															if ($dob_month == $i) { $html .= ' SELECTED'; }
															$html .= '>'.$i.'</option>';
													}
												$html .= '</select> / ';									
												$html .= "<select name='dob_year'>";
													for ($i = date("Y"); $i >= 1900; $i--) {
														$html .= "<option value='".$i."'";
															if ($dob_year == $i) { $html .= ' SELECTED'; }
															$html .= '>'.$i.'</option>';
													}
												$html .= '</select>';									
											$html .= '</div>';
										$html .= '</div>';
											
										// City
										$html .= '<div style="clear:both">';
											$html .= 'Which town/city are you in?';
											$html .= '<div style="float:right;">';
												$html .= '<input type="text" name="city" value="'.$city.'">';
											$html .= '</div>';
										$html .= '</div>';
											
										// Country
										$html .= '<div style="clear:both">';
											$html .= 'Which country are you in?';
											$html .= '<div style="float:right;">';
												$html .= '<input type="text" name="country" value="'.$country.'">';
											$html .= '</div>';
										$html .= '</div>';
										
										// Extensions
										$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
										$fields = explode('[|]', $extended);
										if ($extensions) {
											foreach ($extensions as $extension) {
												$value = $extension->extended_default;
												if ($extension->extended_type == "List") {
													$tmp = explode(',', $extension->extended_default);
													$value = $tmp[0];
			
												}
												foreach ($fields as $field) {
													$split = explode('[]', $field);
													if ($split[0] == $extension->extended_name) { 
														$value = $split[1];
													 }
												}
												
												$html .= '<div style="clear:both">';
													$html .= $extension->extended_name;
													$html .= '<input type="hidden" name="eid[]" value="'.$extension->eid.'">';
													$html .= '<input type="hidden" name="extended_name[]" value="'.$extension->extended_name.'">';
													$html .= '<div style="float:right;">';
														if ($extension->extended_type == 'Text') {
															$html .= '<input type="text" name="extended_value[]" value="'.$value.'">';
														}
														if ($extension->extended_type == 'List') {
															$html .= '<select name="extended_value[]">';
															$items = explode(',', $extension->extended_default);
															foreach ($items as $item) {
																$html .= '<option value="'.$item.'"';
																	if ($value == $item) { $html .= " SELECTED"; }
																	$html .= '>'.$item.'</option>';
															}												
															$html .= '</select>';
														}
													$html .= '</div>';
												$html .= '</div>';
											}
										}
					
											
									$html .= '</div> ';
									 
									$html .= '<p style="clear: both" class="submit"> ';
										$html .= '<input type="submit" name="Submit" class="button" value="Save" /> ';
									$html .= '</p> ';
								$html .= '</form> ';
							
							$html .= "</div>";
		
						}
										
						// Friends
						if ($view == 'friends') {
							
							$html .= '<div style="clear:both; padding-top:15px; ">';
								
								$sql = "SELECT u1.display_name, u1.ID, f.friend_timestamp, f.friend_message, f.friend_from FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."users u1 ON f.friend_from = u1.ID WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on' ORDER BY f.friend_timestamp DESC";
			
								$requests = $wpdb->get_results($sql);
								if ($requests) {
									
									$html .= '<h2>Requests...</h2>';
									foreach ($requests as $request) {
										$html .= "<div style='clear:both; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
											$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
												$html .= get_avatar($request->ID, 64);
											$html .= "</div>";
											$html .= "<div style='float: left; width:50%'>";
												$html .= symposium_profile_link($request->ID)."<br />";
												$html .= symposium_time_ago($request->friend_timestamp, $language_key)."<br />";
												$html .= "<em>".stripslashes($request->friend_message)."</em>";
											$html .= "</div>";
											$html .= "<div style='float:right'>";
												$html .= '<form method="post" action="'.$dbpage.'">';
												$html .= '<input type="hidden" name="symposium_update" value="R">';
												$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
												$html .= '<input type="hidden" name="friend_from" value="'.$request->friend_from.'">';
												$html .= '<input type="submit" name="friendreject" class="button" value="Reject" /> ';
												$html .= '</form>';
											$html .= "</div>";
											$html .= "<div style='float:right'>";
												$html .= '<form method="post" action="'.$dbpage.'">';
												$html .= '<input type="hidden" name="symposium_update" value="A">';
												$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
												$html .= '<input type="hidden" name="friend_from" value="'.$request->friend_from.'">';
												$html .= '<input type="submit" name="friendaccept" class="button" value="Accept" /> ';
												$html .= '</form>';
											$html .= "</div>";
										$html .= "</div>";
									}
								}
		
								$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_from = ".$current_user->ID." ORDER BY last_activity DESC";
								$friends = $wpdb->get_results($sql);
		
								if ($friends) {
									
									$inactive = $config->online;
									$offline = $config->offline;
									
									$html .= '<h2>Friends...</h2>';
									foreach ($friends as $friend) {
										
										$time_now = time();
										$last_active_minutes = strtotime($friend->last_activity);
										$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
																		
										$html .= "<div style='clear:both; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
											$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
												$html .= get_avatar($friend->friend_to, 64);
											$html .= "</div>";
											$html .= "<div style='float: left; width:50%'>";
												$html .= symposium_profile_link($friend->friend_to)."<br />";
												if ($last_active_minutes >= $offline) {
													$html .= 'Logged out. Last active '.symposium_time_ago($friend->last_activity, $language_key).".";
												} else {
													if ($last_active_minutes >= $inactive) {
														$html .= 'Offline. Last active '.symposium_time_ago($friend->last_activity, $language_key).".";
													} else {
														$html .= 'Last active '.symposium_time_ago($friend->last_activity, $language_key).".";
													}
												}
											$html .= "</div>";
		
											$html .= "<div style='float:right'>";
												$html .= '<form method="post" action="'.$dbpage.'">';
												$html .= '<input type="hidden" name="symposium_update" value="D">';
												$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
												$html .= '<input type="hidden" name="friend" value="'.$friend->friend_to.'">';
												$html .= '<input type="submit" name="frienddelete" class="button" value="Remove" /> ';
												$html .= '</form>';
											$html .= "</div>";
		
											$html .= "<div style='float:right'>";
												$html .='<input type="button" value="Send Mail" class="button" onclick="document.location = \''.symposium_get_url('mail').'?view=compose&to='.$friend->friend_to.'\';">';
											$html .= "</div>";
		
										$html .= "</div>";
									}
								}						
		
							$html .= '</div>';
		
						}
					
					$html .= "</div>";
					
				} else {
					$html .= symposium_profile_header($uid, $current_user->ID, $mail_url, $user->display_name);
					$html .= symposium_profile_body($uid, $current_user->ID, $row_color);
				}

			// Visitor
			} else {			
				$html .= symposium_profile_header($uid, 0, $mail_url, $user->display_name);
			}				
			
			// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
			$html .= "<div style='width:100%;font-style:italic; font-size: 10px;text-align:center;'>Powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress, ".get_option("symposium_version")."</div>";
		
		$html .= "</div>";
		
		$html .= "<div style='clear: both'></div>";

		// Notices
		$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin."/images/busy.gif' /> ".$language->sav."</div>";
		$html .= "<div class='pleasewait' style='display:none;z-index:999999;'><img src='".$plugin."/images/busy.gif' /> ".$language->pw."</div>";

											
		return $html;
		exit;
	}
}  

function symposium_profile_header($uid1, $uid2, $url, $display_name) {
	
	global $wpdb;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid1);					

	if ($uid1 > 0) {
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

		$get_language = symposium_get_language($uid2);
		$language_key = $get_language['key'];
		
		$html = "<div style='padding:0px;overflow:auto;'>";
	
			$html .= "<div style='float: left; width: 100%; overflow:auto; padding:0px;'>";
	
				$privacy = $meta->share;
	
				$html .= "<div id='profile_details' style='margin-left: 215px;overflow:auto;'>";

					if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {


					}
	
						$city = $meta->city;
						$country = $meta->country;
		
						if ($city != '' || $country != '') { 	
												
							$html .= "<div style='float:right; width: 150px; margin-left:15px;'>";
							$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to englarge">';
							$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size=150x150&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
							$html .= "</a></div>";
							
						}
						
						$html .= "<h1 style='clear:none'>".$display_name."</h1>";

						$html .= "<p>";
						if ($city != '') { $html .= $city; }
						if ($city != '' && $country != '') { $html .= ", "; }
						if ($country != '') { $html .= $country; }
						
						$day = $meta->dob_day;
						$month = $meta->dob_month;
						$year = $meta->dob_year;
						if (($day == 1) && ($month == 1) && ($year >= 2010)) {
						} else {
							if ($year != '' && $month != '' && $day != '') {
								if ($city != '' || $country != '') { $html .= ".<br />"; }
								switch($month) {									
									case 1:$monthname = "January";break;
									case 2:$monthname = "February";break;
									case 3:$monthname = "March";break;
									case 4:$monthname = "April";break;
									case 5:$monthname = "May";break;
									case 6:$monthname = "June";break;
									case 7:$monthname = "July";break;
									case 8:$monthname = "August";break;
									case 9:$monthname = "September";break;
									case 10:$monthname = "October";break;
									case 11:$monthname = "November";break;
									case 12:$monthname = "December";break;
								}
								$html .= "Born ".$day." ".$monthname." ".$year.".";
							}
						}
						$html .= "</p>";
						
						if ( is_user_logged_in() ) {

							if ($uid1 == $uid2) {

								// Status Input
								$html .= '<form method="post" action="'.$dbpage.'">';
								$html .= '<input type="hidden" name="symposium_update" value="S">';
								$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
								$html .= '<input type="text" name="status" class="input-field" value="What\'s on your mind?" onfocus="this.value = \'\';" style="width:300px" />';
								$html .= '&nbsp;<input type="submit" style="width:75px" class="button" value="Update" /> ';
								$html .= '</form>';
								
							} else {
														
								// Buttons									
								if (symposium_friend_of($uid1)) {
			
									// A friend
			
									// Send mail
									$html .='<input type="button" value="Send Mail" class="button" onclick="document.location = \''.$url.'?view=compose&to='.$uid1.'\';">';
									
								} else {
									
									if (symposium_pending_friendship($uid1)) {
										// Pending
										$html .= 'Friend request sent...<br />';
										$html .= '<form method="post" action="'.$dbpage.'">';
										$html .= '<input type="hidden" name="symposium_update" value="C">';
										$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
										$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
										$html .= '<input type="submit" name="cancelfriend" class="button" value="Cancel" /> ';
										$html .= '</form>';
									} else {							
										// Not a friend
										$html .= '<strong>Add as a Friend...</strong><br />';
										$html .= '<form method="post" action="'.$dbpage.'">';
										$html .= '<input type="hidden" name="symposium_update" value="F">';
										$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
										$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
										$html .= '<input type="text" name="friendmessage" class="input-field" style="width:200px" onclick="this.value=\'\'" value="Add a personal message...">';
										$html .= '&nbsp;&nbsp;<input type="submit" name="addasfriend" class="button" value="Add as Friend" /> ';
										$html .= '</form>';
									}
								}
							}
						}
	
				$html .= "</div>";
					
			$html .= "</div>";
		
			// Photo
			$html .= "<div id='profile_photo' style='float:left;width:215px;margin-left:-100%; margin-bottom:20px;'>";
			$html .= get_avatar($uid1, 200);
			$html .= "</div>";

		$html .= "</div>";
		
		return $html;
		
	} else {
		
		return '';
		
	}

}

function symposium_profile_body($uid1, $uid2, $reply_color) {
	
	global $wpdb;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid1);					

	$get_language = symposium_get_language($uid2);
	$language_key = $get_language['key'];

	if ($uid1 > 0) {
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

		$privacy = $meta->wall_share;		
		if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {
		
			$html .= "<div id='profile_left_column'>";

				$html .= "<div id='profile_right_column'>";
	
					// Extended Information
					$html .= "<div style='width:100%;padding:0px;overflow:auto;'>";
		
						$extended = $meta->extended;
						$fields = explode('[|]', $extended);
						if ($fields) {
							foreach ($fields as $field) {
								$split = explode('[]', $field);
								if ( ($split[0] != '') && ($split[1] != '') ) {
									$html .= "<p><strong>".$split[0]."</strong><br />";
									$html .= $split[1]."</p>";
								}
							}
						}
						
					$html .= "</div>";
	
					// Friends
					$html .= "<div style='width:100%;padding:0px;overflow:auto;'>";
		
						$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_from = ".$uid1." ORDER BY last_activity DESC LIMIT 0,6";
						$friends = $wpdb->get_results($sql);
	
						if ($friends) {
							
							$inactive = $config->online;
							$offline = $config->offline;
							
							$html .= '<strong>Recently Active Friends</strong><br />';
							foreach ($friends as $friend) {
								
								$time_now = time();
								$last_active_minutes = strtotime($friend->last_activity);
								$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
																
								$html .= "<div style='clear:both; width: 99%; margin-bottom: 10px; overflow: auto;'>";		
									$html .= "<div style='float: left; width:42px; margin-right: 5px'>";
										$html .= get_avatar($friend->friend_to, 42);
									$html .= "</div>";
									$html .= "<div>";
										$html .= symposium_profile_link($friend->friend_to)."<br />";
										$html .= 'Last active '.symposium_time_ago($friend->last_activity, $language_key).".";
									$html .= "</div>";
	
								$html .= "</div>";
							}
						}
												
					$html .= "</div>";
						
				$html .= "</div>";
				
				// Wall
				
				if ( ($uid1 != $uid2) && (is_user_logged_in()) ) {
					// Post Comment Input
					$html .= '<form method="post" action="'.$dbpage.'">';
					$html .= '<input type="hidden" name="symposium_update" value="W">';
					$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
					$html .= '<input type="text" name="post_comment" class="input-field" value="Write a comment..." onfocus="this.value = \'\';" style="width:300px" />';
					$html .= '&nbsp;<input type="submit" style="width:75px" class="button" value="Post" /> ';
					$html .= '</form>';
				}

				$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = ".$uid1.") OR (c.author_uid = ".$uid1.") OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) ) AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC";
				
				$comments = $wpdb->get_results($sql);	
				if ($comments) {
					foreach ($comments as $comment) {

						$html .= "<div style='overflow: auto; padding-top: 10px;margin-right: 15px;margin-bottom:15px;border-top: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";'>";
							$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
								$html .= "<div style='margin-left: 74px;overflow:auto;'>";
									$html .= '<a href="'.symposium_get_url('profile').'?uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
									if ($comment->author_uid != $comment->subject_uid) {
										$html .= ' &rarr; <a href="'.symposium_get_url('profile').'?uid='.$comment->subject_uid.'">'.stripslashes($comment->subject_name).'</a> ';
									}
									$html .= symposium_time_ago($comment->comment_timestamp, $language_key).".<br />";
									$html .= stripslashes($comment->comment);

									// Replies
									$sql = "SELECT c.*, u.display_name FROM ".$wpdb->prefix."symposium_comments c 
										LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID 
										LEFT JOIN ".$wpdb->prefix."symposium_comments p ON c.comment_parent = p.cid 
										WHERE ( 
												(c.subject_uid = ".$uid1.") 
										   	OR 	(c.author_uid = ".$uid1.") 
										   	OR 	(p.subject_uid = ".$uid1.") 
										   	OR 	(p.author_uid = ".$uid1.") 
										   	OR 	(p.author_uid = ".$uid2.") 
										   	OR 	(p.subject_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid2.")) 
										   	OR 	(p.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid2.")) 
										   ) 
										   AND c.comment_parent = ".$comment->cid." ORDER BY c.cid";
									
									$replies = $wpdb->get_results($sql);	
									if ($replies) {
										foreach ($replies as $reply) {
											$html .= "<div style='background-color: ".$reply_color."; padding:4px; padding-bottom:0px; clear: both; overflow: auto; margin-top:10px;'>";
												$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
													$html .= "<div style='margin-left: 45px;overflow:auto;'>";
														$html .= '<a href="'.symposium_get_url('profile').'?uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
														$html .= symposium_time_ago($reply->comment_timestamp, $language_key).".<br />";
														$html .= stripslashes($reply->comment);
													$html .= "</div>";
												$html .= "</div>";
												
												$html .= "<div style='float:left;width:45px;margin-left:-100%;'>";
													$html .= get_avatar($reply->author_uid, 40);
												$html .= "</div>";
																					
											$html .= "</div>";
										}
									}

									// Reply field
									if ( is_user_logged_in() ) {
										$html .= '<form method="post" action="'.$dbpage.'">';
										$html .= '<input type="hidden" name="symposium_update" value="WC">';
										$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
										$html .= '<input type="hidden" name="subject_uid" value="'.$comment->subject_uid.'">';
										$html .= '<input type="hidden" name="comment_parent" value="'.$comment->cid.'">';
										$html .= '<input type="text" name="wall_comment" class="input-field" style="margin-top:10px; width:300px;" value="Write a comment..." onfocus="this.value = \'\';" />';
										$html .= '&nbsp;<input type="submit" style="width:75px" class="button" value="Add" /> ';
										$html .= '</form>';
									}
									
								$html .= "</div>";
							$html .= "</div>";
							$html .= "<div style='float:left;width:74px;margin-left:-100%;'>";
								$html .= get_avatar($comment->author_uid, 64);
							$html .= "</div>";
						$html .= "</div>";
						

						
					}
				}
			$html .= "</div>";
		}
		
		return $html;
		
	} else {
		
		return '';
		
	}

}


/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-profile', 'symposium_profile');  


?>
