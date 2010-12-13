<?php

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');

global $wpdb;
$users = $wpdb->prefix . 'users';
$config = $wpdb->prefix . 'symposium_config';
$topics = $wpdb->prefix . 'symposium_topics';
$subs = $wpdb->prefix . 'symposium_subs';
$cats = $wpdb->prefix . 'symposium_cats';
$lang = $wpdb->prefix . 'symposium_lang';

$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$config));
$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");

if (is_user_logged_in()) {

	// New Topic
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
						topic_views
					)
					VALUES ( %s, %d, %s, %s, %s, %d, %d, %d )", 
			        array(
			        	$new_topic_subject, 
			        	$new_topic_category,
			        	$new_topic_text, 
			        	date("Y-m-d H:i:s"), 
						date("Y-m-d H:i:s"), 
						$current_user->ID, 
						0,
						0
			        	) 
			        ) );
			        
				// Store subscription if wanted
				if ($new_topic_subscribe == 'on') {
					$wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$subs."
						( 	uid, 
							tid
						)
						VALUES ( %d, %d )", 
				        array(
				        	$current_user->ID, 
				        	$wpdb->insert_id
				        	) 
				        ) );
				}
				
				// Set category to the category posted into
				$cat_id = $new_topic_category;
				
				// Email people who want to know
				$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$users." WHERE ID = ".$current_user->ID));
	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$users." RIGHT JOIN ".$subs." ON ".$subs.".uid = ".$users.".ID 
					WHERE tid = 0 AND cid = ".$cat_id);
					
				if ($query) {
				
					$body = $owner_name." ".$language->hsa;
					$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
					if ($show_categories == "on") {
						$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
						$body .= " ".$language->i." ".$category;
					}
					$body .= "...<br /><br />";
					$body .= $new_topic_subject."<br /><br />";
					$body .= $new_topic_text."<br /><br />";
					$body .= $thispage;
					$body = str_replace("\\r\\n", "<br />", $body);
					$body = str_replace("\\", "", $body);
	
					foreach ($query as $user) {
						sendmail($user->user_email, $language->nft, $body);
						
					}
					
				}
	
			}
		}
	
		header ("Location: ".$_POST['url']."cid=".$cat_id);
		exit;
	
	}
	
	// Reply to Topic
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
						topic_views
					)
					VALUES ( %s, %d, %s, %s, %s, %d, %d, %d )", 
			        array(
			        	'', 
			        	$cat_id,
			        	$reply_text, 
			        	date("Y-m-d H:i:s"), 
						date("Y-m-d H:i:s"), 
						$current_user->ID, 
						$tid,
						0
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
					
				}
			
				// Email people who want to know
				$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$users." WHERE ID = ".$current_user->ID));
	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$users." RIGHT JOIN ".$subs." ON ".$subs.".uid = ".$users.".ID 
					WHERE tid = ".$tid);
					
				if ($query) {
				
					$body = $owner_name." has replied to a topic you are subscribed to...<br /><br />";
					$body .= $reply_text."<br /><br />";
					$body .= $thispage;
					$body = str_replace("\\r\\n", "<br />", $body);
					$body = str_replace("\\", "", $body);
	
					foreach ($query as $user) {
	
						sendmail($user->user_email, $language->nfr, $body);
						
					}
					
				}
				
				header ("Location: ".$_POST['url']."cid=".$cat_id."&show=".$tid);
				exit;
				
			}
		}	
	}
}
	
?>