<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();

// Members list search
if ($_GET['term'] != '') {
	
	global $wpdb;	
	$return_arr = array();

	$list = $wpdb->get_results("SELECT u.ID, u.display_name, m.city, m.country FROM ".$wpdb->prefix."users u LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$_GET['term']."%') OR (m.city LIKE '".$_GET['term']."%') OR (m.country LIKE '".$_GET['term']."%') OR (u.display_name LIKE '% %".$_GET['term']."%') ORDER BY u.display_name");
	
	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->ID;
			$row_array['value'] = $item->ID;
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

// AJAX function to get members
if ($_POST['action'] == 'getMembers') {

	$html = '';
   	$language_key = $_POST['language_key'];
	$page = $_POST['page'];
	$page_length = 25;

	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline FROM ".$wpdb->prefix . 'symposium_config'));
	
	$sql = "SELECT m.uid, m.last_activity, 
	(SELECT comment FROM ".$wpdb->prefix."symposium_comments WHERE author_uid = m.uid AND subject_uid = author_uid and comment_parent = 0 ORDER BY cid DESC LIMIT 0,1) AS latest_comment 
	FROM ".$wpdb->prefix."symposium_usermeta m 
	WHERE m.uid > 0 
	ORDER BY m.last_activity DESC LIMIT ".($page*$page_length-$page_length).",".$page_length;
	
	$members = $wpdb->get_results($sql);

	if ($members) {
		
		$inactive = $config->online;
		$offline = $config->offline;
		$profile = symposium_get_url('profile');
		
		foreach ($members as $member) {
			
			$time_now = time();
			$last_active_minutes = strtotime($member->last_activity);
			$last_active_minutes = floor(($time_now-$last_active_minutes)/60);

											
			$html .= "<div style='clear:both; margin-top:8px; overflow: auto; width:100%; margin-bottom: 0px;'>";		
				$html .= "<div style='width:100%; padding-left: 75px; margin-left:-75px;'>";
					$html .= "<div style='float: left; width:75px;'>";
						$html .= get_avatar($member->uid, 64);
					$html .= "</div>";
					$html .= symposium_profile_link($member->uid).', last active '.symposium_time_ago($member->last_activity, $language_key).". ";
					if ($last_active_minutes >= $offline) {
						//$html .= '<img src="'.$plugin_dir.'images/loggedout.gif">';
					} else {
						if ($last_active_minutes >= $inactive) {
							$html .= '<img src="'.$plugin_dir.'images/inactive.gif">';
						} else {
							$html .= '<img src="'.$plugin_dir.'images/online.gif">';
						}
					}
					$html .= '<p>'.stripslashes($member->latest_comment).'</p>';
				$html .= "</div>";
			$html .= "</div>";
		}
	}
		
	echo $html;
	exit;
}


?>

	