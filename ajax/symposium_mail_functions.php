<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

// Get single mail message
if ($_POST['action'] == 'getMailMessage') {

	global $wpdb, $current_user;
	wp_get_current_user();
	
	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . "symposium_config"));
	
	$mail_mid = $_POST['mid'];	
	$tray = $_POST['tray'];	
	
	$message = get_message($mail_mid, $tray, $language_key);
	
	// Fetch new unread count
	$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");

	echo $mail_mid."[split]".$unread."[split]".$tray."[split]".$message;
	exit;
}

// Compose - recipients list
if ($_GET['term'] != '') {
	
	global $wpdb;	
	$return_arr = array();
	//$wpdb->show_errors();

	$list = $wpdb->get_results("SELECT u.ID, u.display_name, m.city, m.country FROM ".$wpdb->prefix."users u LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$_GET['term']."%') OR (m.city LIKE '".$_GET['term']."%') OR (m.country LIKE '".$_GET['term']."%') OR (u.display_name LIKE '% %".$_GET['term']."%') ORDER BY u.display_name");

	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->ID;
			$row_array['value'] = $item->display_name;
			$row_array['label'] = $item->display_name;
			if ($item->city != '') {
				$row_array['city'] = $item->city;
			} else {
				$row_array['city'] = '';
			}
			if ($item->country != '') {
				if ($item->city != '') {
					$row_array['country'] = ', '.$item->country;
				} else {
					$row_array['country'] = $item->country;
				}
			} else {
				$row_array['country'] = '';
			}
			
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);
	exit;

}


?>

	