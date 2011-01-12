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

	$users = $wpdb->prefix . 'users';
	$config = $wpdb->prefix . 'symposium_config';
	$topics = $wpdb->prefix . 'symposium_topics';
	$subs = $wpdb->prefix . 'symposium_subs';
	$cats = $wpdb->prefix . 'symposium_cats';
	$lang = $wpdb->prefix . 'symposium_lang';
	
	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];
	$language = $get_language['words'];

	// Get forum_url
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }

	// Check for moderation
	$moderation = $wpdb->get_var($wpdb->prepare("SELECT moderation FROM ".$wpdb->prefix.'symposium_config'));
	if ($moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	// New Topic ****************************************************************
	if ($_POST['action'] == 'post') {
		
		$new_topic_subject = $_POST['new_topic_subject'];
		$new_topic_text = $_POST['new_topic_text'];
		$new_topic_subscribe = $_POST['new_topic_subscribe'];
		$new_topic_category = $_POST['new_topic_category'];

		$store = true;
		$edit_new_topic = false;
		if ($new_topic_subject == '') { $msg = $language->prs; $store = false; $edit_new_topic = true; }
		if ($new_topic_text == '') { $msg = $language->prm; $store = false; $edit_new_topic = true; }
		
		if ( ($store) && is_user_logged_in() ) {
			// Check for duplicates
			
			$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));
	
			if ($topic_count > 1) {
				// Don't double post
			} else {						
				// Store new topic in post
				
				// Don't allow HTML
				$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
				$new_topic_text = str_replace(">", "&gt;", $new_topic_text);
	
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$topics."
					( 	topic_subject,
						topic_category, 
						topic_post, 
						topic_date, 
						topic_started, 
						topic_owner, 
						topic_parent, 
						topic_views,
						topic_approved
					)
					VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s )", 
			        array(
			        	$new_topic_subject, 
			        	$new_topic_category,
			        	$new_topic_text, 
			        	date("Y-m-d H:i:s"), 
						date("Y-m-d H:i:s"), 
						$current_user->ID, 
						0,
						0,
						$topic_approved
			        	) 
			        ) );
			        
				// Store subscription if wanted
				$new_tid = $wpdb->insert_id;
				if ($new_topic_subscribe == 'on') {
					$wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$subs."
						( 	uid, 
							tid
						)
						VALUES ( %d, %d )", 
				        array(
				        	$current_user->ID, 
				        	$new_tid
				        	) 
				        ) );
				}
				
				// Set category to the category posted into
				$cat_id = $new_topic_category;
								
				// Get post owner name and prepare email body
				$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$users." WHERE ID = ".$current_user->ID));
				$body = "<p>".$owner_name." ".$language->hsa;
				$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
				if ($show_categories == "on") {
					$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
					$body .= " ".$language->i." ".$category;
				}
				$body .= "...</p>";
									
				$body .= "<span style='font-size:24px'>".$new_topic_subject."</span><br /><br />";
				$body .= "<p>".$new_topic_text."</p>";
				$url = $forum_url."?cid=".$cat_id."&show=".$new_tid;
				$body .= "<p><a href='".$url."'>".$url."</a></p>";
				$body = str_replace(chr(13), "<br />", $body);
				$body = str_replace("\\r\\n", "<br />", $body);
				$body = str_replace("\\", "", $body);
				
				if ($topic_approved == "on") {
					// Email people who want to know	
					$query = $wpdb->get_results("
						SELECT user_email
						FROM ".$users." RIGHT JOIN ".$subs." ON ".$subs.".uid = ".$users.".ID 
						WHERE tid = 0 AND cid = ".$cat_id);
						
					if ($query) {					
						foreach ($query as $user) {
							symposium_sendmail($user->user_email, "nft", $body);						
						}						
					}
				} else {
					// Email admin if post needs approval
					$body = "<span style='font-size:24px font-style:italic;'>$language->mr</span><br /><br />".$body;
					symposium_sendmail(get_bloginfo('admin_email'), 'mr', $body);
				}	
			}
		}
	
		header ("Location: ".$_POST['url']."cid=".$cat_id);
		exit;
	
	}
	
	// Reply to Topic ****************************************************************
	if ($_POST['action'] == 'reply') {
		
		$tid = $_POST['tid'];
		$cat_id = $_POST['cid'];
		$reply_text = $_POST['reply_text'];
		$reply_topic_subscribe = $_POST['reply_topic_subscribe'];
		
		if ($reply_text != '') {
		
			if (is_user_logged_in()) {
	
				// Invalidate HTML
				$reply_text = str_replace("<", "&lt;", $reply_text);
				$reply_text = str_replace(">", "&gt;", $reply_text);
				
				// Check for duplicates
				$reply_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE replace(topic_post, '\r\n', '') = '".$reply_text."' and topic_owner = ".$current_user->ID));
				
				if ($reply_count > 0)
				{
					// Suspected Double Post
				} else {						
					// Store new topic in post					
	
					$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$topics."
					( 	topic_subject, 
						topic_category,
						topic_post, 
						topic_date, 
						topic_started, 
						topic_owner, 
						topic_parent, 
						topic_views,
						topic_approved
					)
					VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s )", 
			        array(
			        	'', 
			        	$cat_id,
			        	$reply_text, 
			        	date("Y-m-d H:i:s"), 
						date("Y-m-d H:i:s"), 
						$current_user->ID, 
						$tid,
						0,
						$topic_approved
			        	) 
			        ) );

					// Update main topic date for freshness
					$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_date = NOW() WHERE tid = ".$tid) );					
					
					// Store subscription if wanted					
					$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = ".$tid);
					if ($reply_topic_subscribe == 'on') {
						$wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$subs."
						( 	uid, 
							tid
						)
						VALUES ( %d, %d )", 
				        array(
				        	$current_user->ID, 
				        	$tid
				        	) 
				        ) );
					}

					// Email people who want to know and prepare body
					$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$users." WHERE ID = ".$current_user->ID));
					$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$topics." WHERE tid = ".$tid));
					
					$body = "<span style='font-size:24px'>".$parent."</span><br /><br />";
					$body .= "<p>".$owner_name." ".$language->re."...</p>";
					$body .= "<p>".$reply_text."</p>";
					$body .= "<p>".$forum_url."?cid=".$cat_id."&show=".$tid."</p>";
					$body = str_replace(chr(13), "<br />", $body);
					$body = str_replace("\\r\\n", "<br />", $body);
					$body = str_replace("\\", "", $body);
					
					if ($topic_approved == "on") {
						$query = $wpdb->get_results("
							SELECT user_email
							FROM ".$users." RIGHT JOIN ".$subs." ON ".$subs.".uid = ".$users.".ID 
							WHERE tid = ".$tid);
							
						if ($query) {						
							foreach ($query as $user) {		
								symposium_sendmail($user->user_email, 'nfr', $body);							
							}
						}						
					} else {
						// Email admin if post needs approval
						$body = "<span style='font-size:24px; font-style:italic;'>Reply Moderation Required</span><br /><br />".$body;
						symposium_sendmail(get_bloginfo('admin_email'), 'mr', $body);
					}					
				}
				
				header ("Location: ".$_POST['url']."cid=".$cat_id."&show=".$tid);
				exit;
				
			}
		}	
	}
}
	
?>