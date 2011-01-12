<?php
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

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');
include_once('symposium_functions.php');
	
global $wpdb, $current_user;
wp_get_current_user();

if (is_user_logged_in()) {

	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];
	$language = $get_language['words'];
	
	$uid = $_POST['uid'];
		
	if ($uid == $current_user->ID) {

		// Wall Status/Post
		if ($_POST['symposium_update'] == "S") {
			$status = $_POST['status'];
			
			if ( $status != 'What\\\'s on your mind?' ) {
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_comments
					( 	subject_uid, 
						author_uid,
						comment_parent,
						comment_timestamp,
						comment
					)
					VALUES ( %d, %d, %d, %s, %s )", 
			        array(
			        	$current_user->ID, 
			        	$current_user->ID, 
			        	0,
			        	date("Y-m-d H:i:s"),
			        	$status
			        	) 
			        ) );
			        
				// Email all friends who want to know about it
				$sql = "SELECT u.ID, f.friend_to, u.user_email FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON f.friend_to = m.uid LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID WHERE f.friend_from = ".$current_user->ID." AND m.notify_new_wall = 'on'";
				$recipients = $wpdb->get_results($sql);			
				if ($recipients) {
					$body = "<p>".$current_user->display_name." has added a new status to their wall:</p>";
					$body .= "<p>".stripslashes($status)."</p>";
					$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$current_user->ID."'>Go to their wall...</a></p>";
					foreach ($recipients as $recipient) {
						if ($recipient->ID != $current_user->ID) {
							symposium_sendmail($recipient->user_email, "nwp", $body);
						}
					}
				}
				
			}
		
			header("Location: ".symposium_get_url('profile'));
			exit;

		}

		// settings updates
		if ($_POST['symposium_update'] == "U") {
			$notify_new_messages = $_POST['notify_new_messages'];
			$notify_new_wall = $_POST['notify_new_wall'];
			$bar_position = $_POST['bar_position'];
			$timezone = $_POST['timezone'];
			$sound = $_POST['sound'];
			$soundchat = $_POST['soundchat'];
			$language = $_POST['language'];
			$password1 = $_POST['xyz1'];
			$password2 = $_POST['xyz2'];
			$display_name = $_POST['display_name'];
			$user_email = $_POST['user_email'];
			
			update_symposium_meta($current_user->ID, 'timezone', $timezone);
			update_symposium_meta($current_user->ID, 'notify_new_messages', "'".$notify_new_messages."'");
			update_symposium_meta($current_user->ID, 'notify_new_wall', "'".$notify_new_wall."'");
			update_symposium_meta($current_user->ID, 'bar_position', "'".$bar_position."'");
			update_symposium_meta($current_user->ID, 'sound', "'".$sound."'");
			update_symposium_meta($current_user->ID, 'soundchat', "'".$soundchat."'");
			update_symposium_meta($current_user->ID, 'language', "'".$language."'");
			
			$wpdb->show_errors();
			$pwmsg = '';
			if ($password1 != '') {
				if ($password1 == $password2) {
					$sql = "UPDATE ".$wpdb->prefix."users SET user_pass = '".wp_hash_password($password1)."' WHERE ID = ".$current_user->ID;
				    if ($wpdb->query( $wpdb->prepare($sql) ) ) {
				    	$pwmsg = "Password updated. ";
						wp_safe_redirect(symposium_get_url('profile')."?view=settings&msg=".$pwmsg);
						exit;
				    } else {
				    	$pwmsg = "Failed to update password, sorry. ";
				    }
				} else {
			    	$pwmsg = "Passwords different, please try again. ";
				}
			}

			$rows_affected = $wpdb->update( $wpdb->prefix.'users', array( 'display_name' => $display_name, 'user_email' => $user_email ), array( 'ID' => $current_user->ID ), array( '%s', '%s' ), array( '%d' ) );
			if ($rows_affected > 0) {
				if ($rows_affected == 1) {
					$pwmsg .= 'Details Updated.';
				} else {
					$pwmsg .= 'Problem updating details, sorry. '.$wpdb->last_query;
				}
			}
				
			header ("Location: ".symposium_get_url('profile')."?view=settings&msg=".$pwmsg);
			exit;
			
		}
			
		// personal updates
		if ($_POST['symposium_update'] == "P") {
			$dob_day = $_POST['dob_day'];
			$dob_month = $_POST['dob_month'];
			$dob_year = $_POST['dob_year'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$share = $_POST['share'];
			$wall_share = $_POST['wall_share'];
			
			update_symposium_meta($current_user->ID, 'dob_day', $dob_day);
			update_symposium_meta($current_user->ID, 'dob_month', $dob_month);
			update_symposium_meta($current_user->ID, 'dob_year', $dob_year);
			update_symposium_meta($current_user->ID, 'city', "'".$city."'");
			update_symposium_meta($current_user->ID, 'country', "'".$country."'");
			update_symposium_meta($current_user->ID, 'share', "'".$share."'");
			update_symposium_meta($current_user->ID, 'wall_share', "'".$wall_share."'");
			
			// update extended fields
			if ($_POST['extended_name'] != '' ) {
		   		$range = array_keys($_POST['eid']);
		   		$extensions = '';
				foreach ($range as $key) {
				    $extended_name = $_POST['extended_name'][$key];
				    $extended_value = $_POST['extended_value'][$key];
				    $extensions .= $extended_name."[]".$extended_value."[|]";
				}		
				update_symposium_meta($current_user->ID, 'extended', "'".$extensions."'");
			}

			header("Location: ".symposium_get_url('profile')."?view=personal");
			exit;

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
						friend_timestamp,
						friend_accepted
					)
					VALUES ( %d, %d, %s, %s )", 
			        array(
			        	$current_user->ID, 
			        	$_POST['friend_from'],
			        	date("Y-m-d H:i:s"),
			        	'on'
			        	) 
			        ) );
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_friends
					( 	friend_to, 
						friend_from,
						friend_timestamp,
						friend_accepted
					)
					VALUES ( %d, %d, %s, %s )", 
			        array(
			        	$current_user->ID, 
			        	$_POST['friend_from'],
			        	date("Y-m-d H:i:s"),
			        	'on'
			        	) 
			        ) );

				// notify friendship requestor
				$msg = '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">Your friend request has been accepted by '.$current_user->display_name.'...</a>';
				
				symposium_add_notification($msg, $_POST['friend_from']);
			}

			header("Location: ".symposium_get_url('profile')."?view=friends");
			exit;
			
		}

		if ($_POST['symposium_update'] == "D") {
			// Delete friendship

			$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$_POST['friend']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend']." AND friend_from = ".$current_user->ID.")";
			$wpdb->query( $wpdb->prepare( $sql ) );	

			header("Location: ".symposium_get_url('profile')."?view=friends");
			exit;
		}					

		if ($_POST['symposium_update'] == "R") {
			// Rejected friendship
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$_POST['friend_from']." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$_POST['friend_from']." AND friend_from = ".$current_user->ID.")";
			$wpdb->query( $wpdb->prepare( $sql ) );	
			
			header("Location: ".symposium_get_url('profile')."?view=friends");
			exit;
		}
		
	} else {
		
		if ($_POST['symposium_update'] == "W") {
			$post_comment = $_POST['post_comment'];
			
			if ( ($post_comment != 'Write a comment...') && ($post_comment != '') ){
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_comments
					( 	subject_uid, 
						author_uid,
						comment_parent,
						comment_timestamp,
						comment
					)
					VALUES ( %d, %d, %d, %s, %s )", 
			        array(
			        	$uid, 
			        	$current_user->ID, 
			        	0,
			        	date("Y-m-d H:i:s"),
			        	$post_comment
			        	) 
			        ) );
			        
			}

			header("Location: ".symposium_get_url('profile')."?uid=".$uid);
			exit;
		}
		
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
						friend_timestamp,
						friend_message
					)
					VALUES ( %d, %d, %s, %s )", 
			        array(
			        	$friend_from, 
			        	$friend_to,
			        	date("Y-m-d H:i:s"),
			        	$friend_message
			        	) 
			        ) );
			}
			
			// send email
			$friend_to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID = ".$friend_to));
			$body = "You have received a friend request from ".$current_user->display_name;
			symposium_sendmail($friend_to->user_email, "fr", $body);						
		    // add notification
			$msg = '<a href="'.symposium_get_url('profile').'?view=friends">You have a friend request from '.$current_user->display_name.'...</a>';
			symposium_add_notification($msg, $friend_to);
			
			header("Location: ".symposium_get_url('profile')."?uid=".$uid);
			exit;
			
		}

		// Is someone cancelling friend request
		if ($_POST['symposium_update'] == "C") {
			$friend_from = $current_user->ID;
			$friend_to = $uid;					
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_from." AND friend_to = ".$friend_to.") OR (friend_from = ".$friend_to." AND friend_to = ".$friend_from.")" ) );	
			
			header("Location: ".symposium_get_url('profile')."?uid=".$uid);
			exit;
			
		}
					
	}

	if ($_POST['symposium_update'] == "WC") {
		$wall_comment = $_POST['wall_comment'];
		$comment_parent = $_POST['comment_parent'];
		$subject_uid = $_POST['subject_uid'];
		
		if ( ($wall_comment != 'Write a comment...') && ($wall_comment != '') ){
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_comments
				( 	subject_uid, 
					author_uid,
					comment_parent,
					comment_timestamp,
					comment
				)
				VALUES ( %d, %d, %d, %s, %s )", 
		        array(
		        	$subject_uid, 
		        	$current_user->ID, 
		        	$comment_parent,
		        	date("Y-m-d H:i:s"),
		        	$wall_comment
		        	) 
		        ) );

			// Email all friends who want to know about it
			$sql = "SELECT u.ID, f.friend_to, u.user_email FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON f.friend_to = m.uid LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID WHERE f.friend_from = ".$current_user->ID." AND m.notify_new_wall = 'on'";
			$recipients = $wpdb->get_results($sql);			
			if ($recipients) {

				$subject = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$subject_uid));

				$body = "<p>".$current_user->display_name." has replied to ".$subject."'s post:</p>";
				$body .= "<p>".stripslashes($wall_comment)."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$subject_uid."'>Go to their wall...</a></p>";
				foreach ($recipients as $recipient) {
					if ($recipient->ID != $current_user->ID) {
						symposium_sendmail($recipient->user_email, "nwr", $body);
					}
				}
			}		        
		}

		header("Location: ".symposium_get_url('profile')."?uid=".$uid);
		exit;
	}
}

	
?>