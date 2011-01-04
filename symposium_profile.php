<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile] on any WordPress page to display forum.
Version: 0.1.18
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
	
		// Work out this page
		$thispage = get_permalink();
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		$profile_url = $wpdb->get_var($wpdb->prepare("SELECT profile_url FROM ".$wpdb->prefix . 'symposium_config'));
			
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
				
					// Re-act to any post backs
					
					// settings updates
					if ($_POST['symposium_update'] == "U") {
						$notify_new_messages = $_POST['notify_new_messages'];
						$bar_position = $_POST['bar_position'];
						$timezone = $_POST['timezone'];
						$sound = $_POST['sound'];
						$soundchat = $_POST['soundchat'];
						$language = $_POST['language'];
						
						update_symposium_meta($current_user->ID, 'timezone', $timezone);
						update_symposium_meta($current_user->ID, 'notify_new_messages', "'".$notify_new_messages."'");
						update_symposium_meta($current_user->ID, 'bar_position', "'".$bar_position."'");
						update_symposium_meta($current_user->ID, 'sound', "'".$sound."'");
						update_symposium_meta($current_user->ID, 'soundchat', "'".$soundchat."'");
						update_symposium_meta($current_user->ID, 'language', "'".$language."'");
					}
	
					// personal updates
					if ($_POST['symposium_update'] == "P") {
						$dob_day = $_POST['dob_day'];
						$dob_month = $_POST['dob_month'];
						$dob_year = $_POST['dob_year'];
						$city = $_POST['city'];
						$country = $_POST['country'];
						$share = $_POST['share'];
						
						update_symposium_meta($current_user->ID, 'dob_day', $dob_day);
						update_symposium_meta($current_user->ID, 'dob_month', $dob_month);
						update_symposium_meta($current_user->ID, 'dob_year', $dob_year);
						update_symposium_meta($current_user->ID, 'city', "'".$city."'");
						update_symposium_meta($current_user->ID, 'country', "'".$country."'");
						update_symposium_meta($current_user->ID, 'share', "'".$share."'");
					}
	
					if ($_POST['symposium_update'] == "A") {
						// Accepted friendship
	
						// Check to see if already a friend
						$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends WHERE friend_accepted == 'on' AND ((friend_from = ".$_POST['friend_from']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend_from']." AND friend_from = ".$current_user->ID."))";
						$already_a_friend = $wpdb->get_var($sql);
						if ($already_a_friend >= 1) {
							// already a friend
						} else {
						
							// Delete pending request
							$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$_POST['friend_from']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend_from']." AND friend_from = ".$current_user->ID.")";
							$wpdb->query( $wpdb->prepare( $sql ) );	
							
							// Add the two friendship rows
							$wpdb->query( $wpdb->prepare( "
								INSERT INTO ".$wpdb->prefix."symposium_friends
								( 	friend_from, 
									friend_to,
									friend_accepted
								)
								VALUES ( %d, %d, %s )", 
						        array(
						        	$current_user->ID, 
						        	$_POST['friend_from'],
						        	'on'
						        	) 
						        ) );
							$wpdb->query( $wpdb->prepare( "
								INSERT INTO ".$wpdb->prefix."symposium_friends
								( 	friend_to, 
									friend_from,
									friend_accepted
								)
								VALUES ( %d, %d, %s )", 
						        array(
						        	$current_user->ID, 
						        	$_POST['friend_from'],
						        	'on'
						        	) 
						        ) );
		
							// audit
							symposium_audit(array ('code'=>25, 'type'=>'info', 'plugin'=>'profile', 'message'=>'Friendship accepted between '.$_POST['friend_from'].' and '.$current_user->ID.'.'));
							// notify friendship requestor
							$msg = '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">Your friend request has been accepted by '.$current_user->display_name.'...</a>';
							
							symposium_add_notification($msg, $_POST['friend_from']);
						}
						
					}
	
					if ($_POST['symposium_update'] == "D") {
						// Delete friendship
	
						$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$_POST['friend']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend']." AND friend_from = ".$current_user->ID.")";
						$wpdb->query( $wpdb->prepare( $sql ) );	
	
						// audit
						symposium_audit(array ('code'=>27, 'type'=>'info', 'plugin'=>'profile', 'message'=>'Friendship deleted between '.$_POST['friend'].' and '.$current_user->ID.'.'));
						
					}					
	
					if ($_POST['symposium_update'] == "R") {
						// Rejected friendship
						$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$_POST['friend_from']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend_from']." AND friend_from = ".$current_user->ID.")";
						$wpdb->query( $wpdb->prepare( $sql ) );	
	
						// audit
						symposium_audit(array ('code'=>26, 'type'=>'info', 'plugin'=>'profile', 'message'=>'Friendship rejected between '.$_POST['friend_from'].' and '.$current_user->ID.'.'));
						
					}		
									
				} else {
	
					// Is someone trying to add this person as a friend?
					if ($_POST['symposium_update'] == "F") {
						$friend_from = $current_user->ID;
						$friend_to = $uid;					
						$friend_message = $_POST['friendmessage'];
						// check that request isn't already there
						if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")")) ) {
							// already exists
						} else {
		
							$wpdb->query( $wpdb->prepare( "
								INSERT INTO ".$wpdb->prefix."symposium_friends
								( 	friend_from, 
									friend_to,
									friend_message
								)
								VALUES ( %d, %d, %s )", 
						        array(
						        	$friend_from, 
						        	$friend_to,
						        	$friend_message
						        	) 
						        ) );
						}
						
					    // audit
						symposium_audit(array ('code'=>24, 'type'=>'info', 'plugin'=>'profile', 'message'=>'Friend request from '.$friend_from.' to '.$friend_to.'.'));
						// send email
						$friend_to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID = ".$friend_to));
						$body = "You have received a friend request from ".$current_user->display_name;
						symposium_sendmail($friend_to->user_email, "fr", $body);						
					    // add notification
						$msg = '<a href="'.symposium_get_url('profile').'?view=friends">You have a friend request from '.$current_user->display_name.'...</a>';
						symposium_add_notification($msg, $friend_to);
					}
	
					// Is someone cancelling friend request
					if ($_POST['symposium_update'] == "C") {
						$friend_from = $current_user->ID;
						$friend_to = $uid;					
						$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_from." AND friend_to = ".$friend_to.") OR (friend_from = ".$friend_to." AND friend_to = ".$friend_from.")" ) );	
					}
	
					
				}

				if ($uid == $current_user->ID) {
	
					// Set tabs
					$settings_active = 'active';
					$personal_active = 'inactive';
					$friends_active = 'inactive';
					$view = "settings";
					if ($_GET['view'] == 'friends') {
						$settings_active = 'inactive';
						$personal_active = 'inactive';
						$friends_active = 'active';
						$view = "friends";
					} 
					if ($_GET['view'] == 'personal') {
						$settings_active = 'inactive';
						$personal_active = 'active';
						$friends_active = 'inactive';
						$view = "personal";
					} 
					if ( !isset($_GET['view'])  || ($_GET['view'] == "settings") ) {
						$settings_active = 'active';
						$personal_active = 'inactive';
						$friends_active = 'inactive';
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
					$html .= '</div>';
					
					$html .= '<div id="mail-main">';
	
					$html .= symposium_profile_header($uid, $current_user->ID, $mail_url, $current_user->display_name);
					
					// Settings
					if ($view == 'settings') {
						
						$allow_personal_settings = $wpdb->get_var($wpdb->prepare("SELECT allow_personal_settings FROM ".$wpdb->prefix.'symposium_config'));
						
						// get values
						if ($allow_personal_settings == "on") {						
							$sound = get_symposium_meta($current_user->ID, 'sound');
							$soundchat = get_symposium_meta($current_user->ID, 'soundchat');
							$bar_position = get_symposium_meta($current_user->ID, 'bar_position');
							$language = get_symposium_meta($current_user->ID, 'language');
						}
						
						$timezone = get_symposium_meta($current_user->ID, 'timezone');
						$notify_new_messages = get_symposium_meta($current_user->ID, 'notify_new_messages');
						
						$html .= "<div style='clear:both'>";
						
						$html .= '<form method="post" action=""> ';
						$html .= '<input type="hidden" name="symposium_update" value="U">';
					
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
											if ($language == $option->language) { $html .= ' SELECTED'; }
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
	
							// Email notifications
							$html .= '<div style="clear:both;">';
								$html .= 'Do you want to receive an email when you get new mail messages?';
								$html .= '<div style="float:right;">';
									$html .= '<input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
										if ($notify_new_messages == "on") { $html .= "CHECKED"; }
										$html .= '/>';
								$html .= '</div>';
							$html .= '<div>';
							
							if ( ($allow_personal_settings == "on") && (function_exists('add_notification_bar')) ) {
								
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
						$dob_day = get_symposium_meta($current_user->ID, 'dob_day');
						$dob_month = get_symposium_meta($current_user->ID, 'dob_month');
						$dob_year = get_symposium_meta($current_user->ID, 'dob_year');
						$city = get_symposium_meta($current_user->ID, 'city');
						$country = get_symposium_meta($current_user->ID, 'country');
						$share = get_symposium_meta($current_user->ID, 'share');
						
						$html .= "<div style='clear:both'>";
						
						$html .= '<form method="post" action=""> ';
						$html .= '<input type="hidden" name="symposium_update" value="P">';
					
						$html .= '<div id="symposium_settings_table" style="padding-top: 15px">';
						
							// Email notifications
							$html .= '<div style="clear:both;">';
								$html .= 'Who do you want to share personal information with?';
								$html .= '<div style="float:right;">';
									$html .= '<select name="share">';
										$html .= "<option value='Nobody'";
											if ($share == 'Nobody') { $html .= ' SELECTED'; }
											$html .= '>Nobody</option>';
										$html .= "<option value='Friends Only'";
											if ($share == 'Friends Only') { $html .= ' SELECTED'; }
											$html .= '>Friends Only</option>';
										$html .= "<option value='Everyone'";
											if ($share == 'Everyone') { $html .= ' SELECTED'; }
											$html .= '>Everyone</option>';
									$html .= '</select>';
								$html .= '</div>';
							$html .= '<div>';
							
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
											$html .= '<form method="post" action="">';
											$html .= '<input type="hidden" name="symposium_update" value="R">';
											$html .= '<input type="hidden" name="friend_from" value="'.$request->friend_from.'">';
											$html .= '<input type="submit" name="friendreject" class="button" value="Reject" /> ';
											$html .= '</form>';
										$html .= "</div>";
										$html .= "<div style='float:right'>";
											$html .= '<form method="post" action="">';
											$html .= '<input type="hidden" name="symposium_update" value="A">';
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
								
								$inactivity = $wpdb->get_row($wpdb->prepare("SELECT online, offline FROM ".$wpdb->prefix . 'symposium_config'));
								$inactive = $inactivity->online;
								$offline = $inactivity->offline;
								
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
											$html .= '<form method="post" action="">';
											$html .= '<input type="hidden" name="symposium_update" value="D">';
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
				}

			// Visitor
			} else {			
				$html .= symposium_profile_header($uid, 0, $mail_url, $user->display_name);
			}				
			
			// Notices
			$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->sav."</div>";
			$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->pw."</div>";
		
		$html .= "</div>";
											
		return $html;
		exit;
	}
}  

function symposium_profile_header($uid1, $uid2, $url, $display_name) {

	if ($uid1 > 0) {
		
		$get_language = symposium_get_language($uid2);
		$language_key = $get_language['key'];
		
		$html = "<div style='padding:0px;'>";
	
			$html .= "<div style='float: left; width: 100%;'>";
	
				$privacy = get_symposium_meta($uid1, 'share');
	
					$html .= "<div id='profile_details' style='margin-left: 150px;'>";
					
					if ( ($uid1 == $uid2) || ($privacy == 'Everyone') || ($privacy = 'Friends Only' && symposium_friend_of($uid1)) ) {

						$city = get_symposium_meta($uid1, 'city');
						$country = get_symposium_meta($uid1, 'country');
		
						if ($city != '' && $country != '') { 	
												
							$html .= "<div style='float:right;width: 200px; margin-left:15px;'>";
							$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to englarge">';
							$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size=200x200&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
							$html .= "</a></div>";
							
						}

						$html .= "<div style='float:right;'>";
							if ($city != '') { $html .= $city; }
							if ($city != '' && $country != '') { $html .= ", "; }
							if ($country != '') { $html .= $country; }
							if ($city != '' || $country != '') { 
								//$html .= '.<br /><a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5">View Larger Map</a>';
							}
							$day = get_symposium_meta($uid1, 'dob_day');
							$month = get_symposium_meta($uid1, 'dob_ymonth');
							$year = get_symposium_meta($uid1, 'dob_year');
							if ($year != '' && $month != '' && $day != '') {
								$ts = convert_datetime($year."-".$month."-".$day." 00:00:01");
								$html .= "Born ".symposium_time_ago($ts, $language_key).".";
							}
						$html .= "</div>";
												
						
					}
	
					$html .= "<div style='float:left'>";
						
						$html .= "<h1>".$display_name."</h1>";
						
						// Buttons
						if ( ($uid1 != $uid2) && (is_user_logged_in()) ) {
							
							$html .= "<div width: 100%;'>";
		
								if (symposium_friend_of($uid1)) {
			
									// A friend
			
									// Send mail
									$html .='<input type="button" value="Send Mail" class="button" onclick="document.location = \''.$url.'?view=compose&to='.$uid1.'\';">';
									
								} else {
									
									if (symposium_pending_friendship($uid1)) {
										// Pending
										$html .= 'Friend request sent...<br />';
										$html .= '<form method="post" action="">';
										$html .= '<input type="hidden" name="symposium_update" value="C">';
										$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
										$html .= '<input type="submit" name="cancelfriend" class="button" value="Cancel" /> ';
										$html .= '</form>';
									} else {							
										// Not a friend
										$html .= '<strong>Add as a Friend...</strong><br />';
										$html .= '<form method="post" action="">';
										$html .= '<input type="hidden" name="symposium_update" value="F">';
										$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
										$html .= '<input type="text" name="friendmessage" style="width:200px" onclick="this.value=\'\'" value="Add a personal message...">';
										$html .= '&nbsp;&nbsp;<input type="submit" name="addasfriend" class="button" value="Add as Friend" /> ';
										$html .= '</form>';
									}
									
								}
								
							$html .= "</div>"; // End of buttons
						}
	
					$html .= "</div>";
		
				$html .= "</div>";
					
			$html .= "</div>";
		
			// Photo
			$html .= "<div id='profile_photo' style='float:left;width:150px;margin-left:-100%;'>";
			$html .= get_avatar($uid1, 128);
			$html .= "</div>";
	
		$html .= "</div>";
		
		return $html;
		
	} else {
		
		return '';
		
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
