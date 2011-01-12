<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();
	
// AJAX function to get topic details for editing
if ($_POST['action'] == 'getEditDetails') {

	$tid = $_POST['tid'];	
	
	$details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid); 
	if ($details->topic_subject == '') {
		$parent = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$details->topic_parent); 
		$subject = $parent->topic_subject;
	} else {
		$subject = $details->topic_subject;
	}

	if ($details) {
		echo stripslashes($subject)."[split]".stripslashes($details->topic_post)."[split]".$details->topic_parent."[split]".$details->tid."[split]".$details->topic_category;
	} else {
		echo "Problem retrieving topic information[split]Passed Topic ID = ".$tid;
	}
	exit;
}

// AJAX function to update Digest subscription
if ($_POST['action'] == 'updateDigest') {

	$value = $_POST['value'];	

	// Update meta record exists for user
	update_symposium_meta($current_user->ID, "forum_digest", "'".$value."'");
	echo $value;
	exit;

}

// AJAX function to update topic details after editing
if ($_POST['action'] == 'updateEditDetails') {

	$tid = $_POST['tid'];	
	$topic_subject = addslashes($_POST['topic_subject']);	
	$topic_post = addslashes($_POST['topic_post']);	
	$topic_post = str_replace("\n", chr(13), $topic_post);	
	$topic_category = $_POST['topic_category'];
	
	if ($topic_category == "") {
		$topic_category = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
	}

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE topic_parent = ".$tid) );

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_subject = '".$topic_subject."', topic_post = '".$topic_post."', topic_category = ".$topic_category." WHERE tid = ".$tid) );
	
	$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_parent FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));

	echo $topic_post;
	
	exit;
}

// AJAX function to subscribe/unsubscribe to symposium topic
if ($_POST['action'] == 'updateForum') {

	$subs = $wpdb->prefix . 'symposium_subs';

	$tid = $_POST['tid'];
	$action = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = ".$tid);
	
	if ($action == 1)
	{		
		// Store subscription if wanted
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
		exit;
		
	} else {
			
		exit;
		// Removed, and not re-added
	}
	
	echo "Sorry - subscription failed";
	exit;
}

// AJAX function to change sticky status
if ($_POST['action'] == 'updateForumSticky') {

	$topics = $wpdb->prefix . 'symposium_topics';

	$tid = $_POST['tid'];
	$value = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("UPDATE ".$topics." SET topic_sticky = ".$value." WHERE tid = ".$tid);
	
	if ($value==1) {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}

// AJAX function to change allow replies status
if ($_POST['action'] == 'updateTopicReplies') {

	$topics = $wpdb->prefix . 'symposium_topics';

	$tid = $_POST['tid'];
	$value = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("UPDATE ".$topics." SET allow_replies = '".$value."' WHERE tid = ".$tid);
	
	if ($value=='on') {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}

// AJAX function to subscribe/unsubscribe to new symposium topics
if ($_POST['action'] == 'updateForumSubscribe') {

	$subs = $wpdb->prefix . 'symposium_subs';

	$action = $_POST['value'];
	$cid = $_POST['cid'];

	// Store subscription if wanted
	$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = 0 AND (cid = ".$cid." OR cid = 0)");
	
	if ($action == 1)
	{		
		// Store subscription if wanted
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$subs."
			( 	uid, 
				tid,
				cid
			)
			VALUES ( %d, %d, %d )", 
	        array(
	        	$current_user->ID, 
	        	0,
	        	$cid
	        	) 
	        ) );
			echo 'Subscription added.';			
		exit;
		
	} else {

		echo 'Subscription removed.';			
		exit;
	}
	
	echo "Sorry - subscription failed";
	exit;

}

?>