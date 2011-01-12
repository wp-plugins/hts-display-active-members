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

function symposium_pagination($total, $current, $url) {
	
	$r = '';

	$r .= '<div class="tablenav"><div class="tablenav-pages">';
	for ($i = 0; $i < $total; $i++) {
		if ($i == $current) {
            $r .= "<b>".($i+1)."</b> ";
        } else {
        	if ( ($i == 0) || ($i == $total-1) || ($i+1 == $current) || ($i+1 == $current+2) ) {
	            $r .= " <a href='".$url.($i+1)."'>".($i+1)."</a> ";
        	} else {
        		$r .= ".";
        	}
        }
	}
	$r .= '</div></div>';
	
	return $r;
}

function get_message($mail_mid, $del, $language_key) {

	global $wpdb, $current_user;
	wp_get_current_user();

	$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");

	if ($del == "in") {
		$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$mail_mid);
	} else {
		$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_to = u.ID WHERE mail_mid = ".$mail_mid);
	}
	
	$styles = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."symposium_config");
	
	$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));

	$msg = "<div style='padding-bottom:10px; overflow:auto;'>";
	
		$msg .= "<div style='width:44px; margin-right: 5px'>";
			$msg .= get_avatar($mail->mail_from, 44);
		$msg .= "</div>";

		// Delete
		$msg .= "<div style='float:right'>";
		$msg .= "<form action='' method='POST'>";
		$msg .= "<input type='hidden' name='del".$del."' value=".$mail_mid." />";
		$msg .= '<input type="submit" class="button" onclick="jQuery(\'.pleasewait\').inmiddle().show();" value="'.$language->d.'" />';
		$msg .= "</form>";
		$msg .= "</div>";
		
		// Reply
		if ($del == "in") {
			$msg .= "<div style='clear:both;margin-top:-16px;float:right'>";
			$msg .= "<form action='' method='POST'>";
			$msg .= "<input type='hidden' name='reply_recipient' value=".$mail->mail_from." />";
			$msg .= "<input type='hidden' name='reply_mid' value=".$mail_mid." />";
			$msg .= '<input type="submit" class="button" onclick="jQuery(\'.pleasewait\').inmiddle().show();" value="'.$language->reb.'" />';
			$msg .= "</form>";
			$msg .= "</div>";
		}
		
		$msg .= "<span style='font-family:".$styles->headingsfamily."; font-size:".$styles->headingssize."px; font-weight:bold;'>".stripslashes($mail->mail_subject)."</span><br />";
		if ($del == "in") {
			$msg .= "From ";
		} else {
			$msg .= "To ";
		}
		$msg .= stripslashes($mail->display_name)." ".symposium_time_ago($mail->mail_sent, $language_key).".<br />";
		
	$msg .= "</div>";
	
	$msg .= "<div style='padding-top:10px'>";
	$msg .= stripslashes(str_replace(chr(13), "<br />", $mail->mail_message));
	$msg .= "</div>";
	
	// Mark as read
	if ($del == "in") {
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_read = 'on' WHERE mail_mid = ".$mail_mid." AND mail_to = ".$current_user->ID) );
	}

	$msg = symposium_smilies($msg);
	
	return $msg;
	
}

function symposium_pending_friendship($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_accepted != 'on') AND (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")";
	
	if ( $wpdb->get_var($wpdb->prepare($sql)) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_friend_of($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")")) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_get_current_userlevel() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	// Work out user level
	$user_level = 0; // Guest
	if (is_user_logged_in()) { $user_level = 1; } // Subscriber
	if (current_user_can('edit_posts')) { $user_level = 2; } // Contributor
	if (current_user_can('edit_published_posts')) { $user_level = 3; } // Author
	if (current_user_can('moderate_comments')) { $user_level = 4; } // Editor
	if (current_user_can('activate_plugins')) { $user_level = 5; } // Administrator
	
	return $user_level;

}

function symposium_get_language($uid) {
	global $wpdb;

	$allow_personal_settings = $wpdb->get_var($wpdb->prepare("SELECT allow_personal_settings FROM ".$wpdb->prefix.'symposium_config'));

	if ( ($allow_personal_settings == 'on') && (function_exists('symposium_profile')) ) {
		$language_key = get_symposium_meta($uid, 'language');
	} else {
		$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . "symposium_config"));
	}
	
	$words = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");

	$arr = array("words" => $words, "key" => $language_key);
	
	return $arr;
}

function symposium_get_core_language($uid) {
	global $wpdb;

	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . "symposium_config"));
	$words = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");

	$arr = array("words" => $words, "key" => $language_key);
	
	return $arr;
}

function symposium_get_url($plugin) {
	global $wpdb;
	$urls = $wpdb->get_row($wpdb->prepare("SELECT forum_url, mail_url, profile_url FROM ".$wpdb->prefix . 'symposium_config'));
	$return = false;
	if ($plugin == 'mail') {
		$return = $urls->mail_url;
	}
	if ($plugin == 'forum') {
		$return = $urls->forum_url;
	}
	if ($plugin == 'profile') {
		$return = $urls->profile_url;
	}
	return $return;
}

function symposium_alter_table($table, $action, $field, $format, $null, $default) {
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	
	
	$success = false;

	$ok = '';
	$check = $wpdb->get_var("SELECT count(".$field.") FROM ".$wpdb->prefix."symposium_".$table);
	if ($check != '') { 
		$ok = 'exists';
		if ($check > 0) { $ok = 'same'; }
	}
	
	if ($action == "ADD") {
		if ($ok == 'exists' || $ok == 'same') {
			// Do Nothing
		} else {
		  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default);
		}			
	}

	if ($action == "MODIFY COLUMN") {
		$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default;
	  	$wpdb->query($sql);
	}
	
	return $success;

}

// Checks is user meta exists, and if not creates it
function update_symposium_meta($uid, $meta, $value) {
   	global $wpdb;
	
	if ($value == '') { $value = "''"; }
	
	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		$sys_lang = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'language' => $sys_lang,
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
	}

	// now update value
 	$r = false;
  	if ($wpdb->query("UPDATE ".$wpdb->prefix."symposium_usermeta SET ".$meta." = ".$value." WHERE uid = ".$uid)) {
  		$r = true;
  	}
  	
  	return $r;
}

// Get user meta data
function get_symposium_meta($uid, $meta) {
   	global $wpdb;

	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		// get system default language
		$sys_lang = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'language' => $sys_lang,
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
			
	}

	if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
		return $value;
	} else {
		return false; 	
	}
}

// Get user meta data row
function get_symposium_meta_row($uid) {
   	global $wpdb;

	$row = '';
	
	// check if exists, and create record if not
	if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		// get system default language
		$sys_lang = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'language' => $sys_lang,
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
			
	}
	
	if ($row == '') {
		if ($row = $wpdb->get_row($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
			return $row;
		} else {
			return false; 	
		}
	} else {
		return $row;
	}
	
}

// Display array contents (for de-bugging only)
function symposium_displayArrayContentFunction($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= symposium_displayArrayContentFunction($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// Add notification
function symposium_add_notification($msg, $recipient) {
	global $wpdb;
	if ( $wpdb->insert( $wpdb->prefix . "symposium_notifications", array( 
		'notification_to' => $recipient, 
		'notification_message' => $msg
	 	) ) ) {
	 }
}

// Link to profile if pluing activated
function symposium_profile_link($uid) {
	global $wpdb;

	$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$uid));
	if (function_exists('symposium_profile')) {
		$profile_url = $wpdb->get_var($wpdb->prepare("SELECT profile_url FROM ".$wpdb->prefix."symposium_config"));
		$html = '<a href="'.$profile_url.'?uid='.$uid.'">'.$display_name.'</a>';
	} else {
		$html = $display_name;
	}
	return $html;
}

// Create Permalink for Forum
function symposium_permalink($id, $type) {

	global $wpdb;
	$seo = $wpdb->get_var($wpdb->prepare("SELECT seo FROM ".$wpdb->prefix.'symposium_config'));
	
	if ($seo != "on") {
		// Not set on options page
		return "";
	} else {
	
		if ($_GET['page_id'] != '') {
			
			// Not using Permalinks
			return "";
			
		} else {
		
			if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on")
			
			if ($type == "category") {
				$info = $wpdb->get_row("
					SELECT title FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$id); 
				$string = stripslashes($info->title);
				$string = str_replace('\\', '-', $string);
				$string = str_replace('/', '-', $string);
			} else {
				$info = $wpdb->get_row("
					SELECT topic_subject, title FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'symposium_cats'." ON ".$wpdb->prefix.'symposium_topics'.".topic_category = ".$wpdb->prefix.'symposium_cats'.".cid WHERE tid = ".$id); 
				$string = stripslashes($info->topic_subject);
				$string = str_replace('\\', '-', $string);
				$string = str_replace('/', '-', $string);
				if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on") {
					$title = stripslashes($info->title);
					$title = str_replace('\\', '-', $title);
					$title = str_replace('/', '-', $title);
					$string = $title."/".$string;
				}
			}
	
							
			$patterns = array();
			$patterns[0] = '/ /';
			$patterns[1] = '/\?/';
			$patterns[2] = '/\&/';
			$replacements = array();
			$replacements[0] = '-';
			$replacements[1] = '';
			$replacements[2] = '';
			$string = preg_replace($patterns, $replacements, $string);
	
			$string = $id."/".$string;
	
			
			return $string;
		}
	}
}

// How long ago as text
function symposium_time_ago($date,$language,$granularity=1) {
	
    $date = strtotime($date);
    $difference = (time() - $date) + 1;
    $periods = array('decade' => 315360000,
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1);
                                 
    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference/$value);
            $difference %= $value;
            $retval .= ($retval ? ' ' : '').$time.' ';
            $retval .= (($time > 1) ? $key.'s' : $key);
            $granularity--;
        }
        if ($granularity == '0') { break; }
    }
    switch ($language) {
    case "Default":
	    	$retval .= " ago";
        	break;    
    case "English":
	    	$retval .= " ago";
        	break;    
    case "Russian":
			$retval = str_replace("second", "се&#1082;у&#1085;&#1076;а", $retval);
			$retval = str_replace("се&#1082;у&#1085;&#1076;аs", "се&#1082;у&#1085;&#1076;&#1099;", $retval);
			$retval = str_replace("minute", "&#1084;&#1080;&#1085;у&#1090;а", $retval);
			$retval = str_replace("&#1084;&#1080;&#1085;у&#1090;аs", "&#1084;&#1080;&#1085;у&#1090;&#1099;", $retval);
			$retval = str_replace("hour", "&#1095;ас", $retval);
			$retval = str_replace("&#1095;асs", "&#1095;аса", $retval);
			$retval = str_replace("day", "&#1076;е&#1085;&#1100;", $retval);
			$retval = str_replace("&#1076;е&#1085;&#1100;s", "&#1076;&#1085;&#1103;", $retval);
			$retval = str_replace("week", "&#1085;е&#1076;е&#1083;&#1103;", $retval);
			$retval = str_replace("&#1085;е&#1076;е&#1083;&#1103;s", "&#1085;е&#1076;е&#1083;&#1080;", $retval);
			$retval = str_replace("month", "&#1084;ес&#1103;&#1094;", $retval);
			$retval = str_replace("&#1084;ес&#1103;&#1094;s", "&#1084;ес&#1103;&#1094;а", $retval);
			$retval = str_replace("year", "&#1075;о&#1076;", $retval);
			$retval = str_replace("&#1075;о&#1076;s", "&#1083;е&#1090;", $retval);
			$retval = $retval." &#1085;а&#1079;а&#1076;";
			break;   
    case "French":
    		$retval = str_replace("second", "seconde", $retval);
    		$retval = str_replace("hour", "heure", $retval);
    		$retval = str_replace("day", "jour", $retval);
    		$retval = str_replace("week", "semaine", $retval);
    		$retval = str_replace("month", "mois", $retval);
    		$retval = str_replace("moiss", "mois", $retval);
    		$retval = str_replace("year", "an", $retval);
	    	$retval = "il ya ".$retval;
        	break;    
    case "Spanish":
    		$retval = str_replace("second", "segundo", $retval);
    		$retval = str_replace("minute", "minuto", $retval);
    		$retval = str_replace("hour", "hora", $retval);
    		$retval = str_replace("day", "dia", $retval);
    		$retval = str_replace("week", "semana", $retval);
    		$retval = str_replace("month", "mes", $retval);
    		$retval = str_replace("mess", "meses", $retval);
    		$retval = str_replace("year", "ano", $retval);
	    	$retval = "hace ".$retval;
        	break;    
    case "German":
    		$retval = str_replace("second", "sekunde", $retval);
    		$retval = str_replace("sekundes", "sekunden", $retval);
    		$retval = str_replace("minutes", "minuten", $retval);
    		$retval = str_replace("hour", "stunde", $retval);
    		$retval = str_replace("stundes", "stunden", $retval);
    		$retval = str_replace("day", "tag", $retval);
    		$retval = str_replace("tags", "tage", $retval);
    		$retval = str_replace("week", "woche", $retval);
    		$retval = str_replace("woches", "wochen", $retval);
    		$retval = str_replace("month", "monat", $retval);
    		$retval = str_replace("monats", "monate", $retval);
    		$retval = str_replace("year", "jahr", $retval);
    		$retval = str_replace("jahrs", "jahre", $retval);
	    	$retval = "vor ".$retval;
        	break;    
    case "Czech":
    		$retval = str_replace("second", "sekundou", $retval);
    		$retval = str_replace("sekundous", "sekundy", $retval);
    		$retval = str_replace("minute", "minutou", $retval);
    		$retval = str_replace("minutous", "minuty", $retval);
    		$retval = str_replace("hour", "hodina", $retval);
    		$retval = str_replace("hodinas", "hodinami", $retval);
    		$retval = str_replace("day", "dnem", $retval);
    		$retval = str_replace("dnems", "dny", $retval);
    		$retval = str_replace("week", "t&yacute;dnem", $retval);
    		$retval = str_replace("t&yacute;dnems", "t&yacute;dny", $retval);
    		$retval = str_replace("month", "m&#283;s&iacute;c", $retval);
    		$retval = str_replace("m&#283;s&iacute;c", "m&#283;s&iacute;i", $retval);
    		$retval = str_replace("year", "rokem", $retval);
    		$retval = str_replace("rokems", "lety", $retval);
	    	$retval = "p&#345;ed ".$retval;
        	break;    
    case "Turkish":
    		$retval = str_replace("second", "saniye", $retval);
    		$retval = str_replace("saniyes", "saniye", $retval);
    		$retval = str_replace("minute", "dakika", $retval);
    		$retval = str_replace("dakikas", "dakika", $retval);
    		$retval = str_replace("hour", "saat", $retval);
    		$retval = str_replace("saats", "saat", $retval);
    		$retval = str_replace("day", "g&uuml;n", $retval);
    		$retval = str_replace("g&uuml;ns", "g&uuml;n", $retval);
    		$retval = str_replace("week", "hafta", $retval);
    		$retval = str_replace("haftas", "hafta", $retval);
    		$retval = str_replace("month", "ay", $retval);
    		$retval = str_replace("ays", "ay", $retval);
    		$retval = str_replace("year", "y&#305;l", $retval);
    		$retval = str_replace("y&#305;ls", "y&#305;l", $retval);
	    	$retval = $retval." &ouml;nce";
        	break;  
   case "Hungarian":
           $retval = str_replace("second", "m&aacute;sodpercel", $retval);
           $retval = str_replace("m&aacute;sodpercels", "m&aacute;sodpercel", $retval);
           $retval = str_replace("minute", "percel", $retval);
           $retval = str_replace("percels", "percel", $retval);
           $retval = str_replace("hour", "&oacute;r&aacute;val", $retval);
           $retval = str_replace("&oacute;r&aacute;vals", "&oacute;r&aacute;val", $retval);
           $retval = str_replace("day", "nappal", $retval);
           $retval = str_replace("nappals", "nappal", $retval);
           $retval = str_replace("week", "h&eacute;ttel", $retval);
           $retval = str_replace("h&eacute;ttels", "h&eacute;ttel", $retval);
           $retval = str_replace("month", "h&oacute;nappal", $retval);
           $retval = str_replace("h&oacute;nappals", "h&oacute;nappal", $retval);
           $retval = str_replace("year", "&eacute;vvel", $retval);
           $retval = str_replace("&eacute;vvels", "&eacute;vvel", $retval);
           $retval = $retval." ezel&ouml;tt";
           break; 
	case "Portuguese":
			$retval = str_replace("second", "segundo", $retval);
			$retval = str_replace("segundos", "segundos", $retval);
			$retval = str_replace("minute", "minuto", $retval);
			$retval = str_replace("minutos", "minuto", $retval);
			$retval = str_replace("hour", "hora", $retval);
			$retval = str_replace("horas", "horas", $retval);
			$retval = str_replace("day", "dia", $retval);
			$retval = str_replace("dias", "dias", $retval);
			$retval = str_replace("week", "semana", $retval);
			$retval = str_replace("semanas", "semanas", $retval);
			$retval = str_replace("month", "mes", $retval);
			$retval = str_replace("mess", "meses", $retval);
			$retval = str_replace("year", "ano", $retval);
			$retval = str_replace("anos", "ano", $retval);
			$retval = "h&aacute; ".$retval;
			break; 
	case "Brazilian Portuguese":
			$retval = str_replace("second", "segundo", $retval);
			$retval = str_replace("segundos", "segundo", $retval);
			$retval = str_replace("minute", "minuto", $retval);
			$retval = str_replace("minutos", "minuto", $retval);
			$retval = str_replace("hour", "hora", $retval);
			$retval = str_replace("horas", "hora", $retval);
			$retval = str_replace("day", "dia", $retval);
			$retval = str_replace("dias", "dia", $retval);
			$retval = str_replace("week", "semana", $retval);
			$retval = str_replace("semanas", "semana", $retval);
			$retval = str_replace("month", "mes", $retval);
			$retval = str_replace("mess", "mes", $retval);
			$retval = str_replace("mess", "meses", $retval);
			$retval = str_replace("year", "ano", $retval);
			$retval = str_replace("anos", "ano", $retval);
			$retval = "h&aacute; ".$retval;
			break;   
    case "Norwegian":
    		$retval = str_replace("second", "sekund", $retval);
    		$retval = str_replace("sekunds", "sekunder", $retval);
    		$retval = str_replace("minutes", "minutt", $retval);
    		$retval = str_replace("minutts", "minutter", $retval);
    		$retval = str_replace("day", "dag", $retval);
    		$retval = str_replace("dags", "dager", $retval);
    		$retval = str_replace("week", "uke", $retval);
    		$retval = str_replace("uke", "uker", $retval);
    		$retval = str_replace("month", "m&aring;ned", $retval);
    		$retval = str_replace("m&aring;neds", "m&aring;neder", $retval);
    		$retval = str_replace("year", "&aring;r", $retval);
    		$retval = str_replace("&aring;rs", "&aring;r", $retval);
	    	$retval = $retval." siden";
        	break;    
    case "Dutch":
    		$retval = str_replace("second", "seconde", $retval);
    		$retval = str_replace("seconde", "seconden", $retval);
    		$retval = str_replace("minute", "minuut", $retval);
    		$retval = str_replace("minuuts", "minuten", $retval);
    		$retval = str_replace("hour", "uur", $retval);
    		$retval = str_replace("uurs", "uur", $retval);
    		$retval = str_replace("day", "dag", $retval);
    		$retval = str_replace("dags", "dagen", $retval);
    		$retval = str_replace("week", "hafta", $retval);
    		$retval = str_replace("weeks", "weken", $retval);
    		$retval = str_replace("month", "maand", $retval);
    		$retval = str_replace("maands", "maanden", $retval);
    		$retval = str_replace("year", "jaar", $retval);
    		$retval = str_replace("jaars", "jaar", $retval);
	    	$retval = $retval." geleden";        	
			break;
    case "Polish":
    		$retval = str_replace("second", "sekunda", $retval);
    		$retval = str_replace("sekundas", "sekundy", $retval);
    		$retval = str_replace("minute", "minuta", $retval);
    		$retval = str_replace("minutas", "minuty", $retval);
    		$retval = str_replace("hour", "godzina", $retval);
    		$retval = str_replace("godzinas", "godziny", $retval);
    		$retval = str_replace("day", "dzie&#324;", $retval);
    		$retval = str_replace("dzie&#324;s", "dni", $retval);
    		$retval = str_replace("week", "tydzie&#324;", $retval);
    		$retval = str_replace("tydzie&#324;s", "tygodnie", $retval);
    		$retval = str_replace("month", "miesi&#261;c", $retval);
    		$retval = str_replace("miesi&#261;cs", "miesi&#261;ce", $retval);
    		$retval = str_replace("year", "rok", $retval);
    		$retval = str_replace("roks", "lata", $retval);
	    	$retval = $retval." temu";        	
			break;
    case "Swedish":
    		$retval = str_replace("second", "sekund", $retval);
    		$retval = str_replace("sekunds", "sekunder", $retval);
    		$retval = str_replace("minute", "minut", $retval);
    		$retval = str_replace("minuts", "minuter", $retval);
    		$retval = str_replace("hour", "timme", $retval);
    		$retval = str_replace("timmes", "timmar", $retval);
    		$retval = str_replace("day", "dag", $retval);
    		$retval = str_replace("dags", "dagar", $retval);
    		$retval = str_replace("week", "vecka", $retval);
    		$retval = str_replace("veckas", "veckor", $retval);
    		$retval = str_replace("month", "m&acirc;nad", $retval);
    		$retval = str_replace("m&acirc;nads", "m&acirc;nader", $retval);
    		$retval = str_replace("year", "&acric;r", $retval);
    		$retval = str_replace("&acric;rs", "&acric;r", $retval);
	    	$retval = $retval." sedan";        	
			break;
	    }
    return $retval;      
}

// Send email
function symposium_sendmail($email, $code, $msg)
{
	global $wpdb;
	
	// first get ID of recipient
	$uid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".$wpdb->prefix."users WHERE lower(user_email) = '".strtolower($email)."'"));
	// now get language of recipient
	$get_language = symposium_get_language($uid);
	$language = $get_language['words'];

	// get subject
	switch ($code) {
	    case "nft":
			$subject = $language->nft;	
	        break;
	    case "nmm":
			$subject = $language->nmm;	
	        break;
	    case "fr":
			$subject = $language->fr;	
	        break;
	    case "fdd":
			$subject = $language->fdd;	
	        break;
	    case "mr":
			$subject = $language->mr;	
	        break;
	    case "nfr":
			$subject = $language->nfr;	
	        break;
	    case "fmr":
			$subject = $language->fmr;	
	        break;
	    case "fma":
			$subject = $language->fma;	
	        break;
	    case "nwp":
			$subject = "New Wall Post";	
	        break;
	    case "nwr":
			$subject = "New Wall Post Reply";	
	        break;
	    default:
	    	$subject = $code;
   	}

	// get footer
	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));

	// build body text
	$body = "<style>";
	$body .= "body { background-color: #eee; }";
	$body .= "</style>";
	$body .= "<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>";
	$body .= $msg."<br /><hr />";
	$body .= "<div style='width:430px;font-size:10px;border:0px solid #eee;text-align:left;float:left;'>".$footer."</div>";
	// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
	$body .= "<div style='width:370px;font-size:10px;border:0px solid #eee;text-align:right;float:right;'>Forum powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress</div>";
	$body .= "</div>";

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->prefix.'symposium_config'))."\r\n";
	
	// finally send mail
	if (mail($email, $subject, $body, $headers))
	{
		return true;
	} else {
		return false;
	}
}

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp 

function convert_datetime($str) { 

    list($date, $time) = explode(' ', $str); 
    list($year, $month, $day) = explode('-', $date); 
    list($hour, $minute, $second) = explode(':', $time); 
     
    $timestamp = mktime($hour, $minute, $second, $month, $day, $year); 
     
    return $timestamp; 
} 
?>