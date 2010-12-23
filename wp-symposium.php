<?php
/*
Plugin Name: WP Symposium Core
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must always be activated, before any other Symposium plugins/widgets (they rely upon it).
Version: 0.1.16
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

/* ====================================================== MENU ====================================================== */

if (is_admin()) {
	include('symposium_menu.php');
}		   	

/* ====================================================== ADMIN ====================================================== */

global $symposium_db_version;
$symposium_db_version = "1";

// Any admin warnings
function symposium_mail_warning() {
	//echo "<div class='updated'>Warning</p></div>";
}
add_action('admin_notices', 'symposium_mail_warning');

// Dashboard Widget
add_action('wp_dashboard_setup', 'symposium_dashboard_widget');
function symposium_dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', 'WP Symposium', 'symposium_widget');
}
function symposium_widget() {
	
	global $wpdb;
	
	echo '<img src="'.WP_PLUGIN_URL.'/wp-symposium/logo_small.gif" alt="WP Symposium logo" style="float:right; width:100px;height:120px;" />';

	echo '<table>';
	echo '<tr><td style="padding:4px"><a href="admin.php?page=symposium_categories">Categories</a></td>';
	echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats').'</td></tr>';
	echo '<tr><td style="padding:4px">Topics</td>';
	echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
	echo '<tr><td style="padding:4px">Replies</td>';
	echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent > 0").'</td></tr>';
	echo '<tr><td style="padding:4px">Views</td>';
	echo '<td style="padding:4px">'.$wpdb->get_var("SELECT SUM(topic_views) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
	echo '<tr><td style="padding:4px">Mail</td>';
	$mailcount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail');
	$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_read != 'on'");
	echo '<td style="padding:4px">'.$mailcount;
	echo ' ('.$unread.' unread)';
	echo '</td></tr>';
	echo '</table>';

	echo '<p>';
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
	echo '<a href="'.$forum_url.'">View Forum</a>';
	echo '</p>';
}

function symposium_activate() {
	
   	global $wpdb, $current_user;
   	global $symposium_db_version;
	wp_get_current_user();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Version of WP Symposium
	$symposium_version = "0.1.16";
	$symposium_db_ver = 16;
	
	symposium_audit(array ('code'=>1, 'type'=>'info', 'plugin'=>'core', 'message'=>'Core activation started.'));
	
	// Code version *************************************************************************************
	symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'Applying code version '.$symposium_version.'.'));
	$ver = get_option("symposium_version");
	if ($ver != false) {
		symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'Existing code version = '.$ver.'.'));
		if ($ver != $symposium_version) {
			symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'Updated to '.$symposium_version.'.'));
		}
		 
	    update_option("symposium_version", $symposium_version);	    	   	
	} else {
		// Set Database Version		
	    add_option("symposium_version", $symposium_version);	    	   	
	}

	// Database version *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver != false) {
		$db_ver = (int) $db_ver;
		symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'Existing database version = '.$db_ver.'.'));
	} else {
		symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'No current database version, setting to 1.'));
		// Set Database Version		
	    add_option("symposium_db_version", 1);	    	   	
	}


	// Create initial versions of tables *************************************************************************************

	include('create_tables.php');

  	// Update tables (may already be there, not a problem as will just skip) *************************************************************************************

   	// Add option fields
	symposium_alter_table("config", "ADD", "allow_new_topics", "varchar(2) NOT NULL", "''");
	symposium_alter_table("config", "ADD", "language", "varchar(3) NOT NULL", "'ENG'");
	symposium_alter_table("config", "ADD", "underline", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "preview1", "int(11) NOT NULL", "'45'");
	symposium_alter_table("config", "ADD", "preview2", "int(11) NOT NULL", "'90'");
	symposium_alter_table("config", "ADD", "viewer", "varchar(32) NOT NULL", "'Guest'");
	symposium_alter_table("config", "ADD", "include_admin", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "oldest_first", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "wp_width", "varchar(6) NOT NULL", "'99pc'");
	symposium_alter_table("config", "ADD", "main_background", "varchar(12) NOT NULL", "'#fff'");
	symposium_alter_table("config", "ADD", "closed_opacity", "varchar(6) NOT NULL", "'1.0'");
	symposium_alter_table("config", "ADD", "closed_word", "varchar(32) NOT NULL", "'closed'");
	symposium_alter_table("config", "ADD", "fontfamily", "varchar(64) NOT NULL", "'Georgia,Times'");
	symposium_alter_table("config", "ADD", "fontsize", "varchar(16) NOT NULL", "'15'");
	symposium_alter_table("config", "ADD", "headingsfamily", "varchar(64) NOT NULL", "'Arial,Helvetica'");
	symposium_alter_table("config", "ADD", "headingssize", "varchar(16) NOT NULL", "'20'");
	symposium_alter_table("config", "ADD", "jquery", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "emoticons", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "seo", "varchar(2) NOT NULL", "''");
	symposium_alter_table("config", "ADD", "moderation", "varchar(2) NOT NULL", "''");
	symposium_alter_table("config", "ADD", "mail_url", "varchar(128) NOT NULL", "''");
	// Profile
	symposium_alter_table("config", "ADD", "profile_url", "varchar(128) NOT NULL", "''");
	// Notification bar
	symposium_alter_table("config", "ADD", "sound", "varchar(32) NOT NULL", "'chime.mp3'");
	symposium_alter_table("config", "ADD", "bar_position", "varchar(6) NOT NULL", "'bottom'");
	symposium_alter_table("config", "ADD", "bar_label", "varchar(256) NOT NULL", "'Powered by WP Symposium'");

	// Modify option fields for all versions (if fields already exist, ADD above won't work)
	symposium_alter_table("config", "MODIFY", "language", "varchar(64) NOT NULL", "'English'");
	
	// Modify audit table
	symposium_alter_table("audit", "MODIFY", "message", "text", "''");

	// Modify styles table
	symposium_alter_table("styles", "ADD", "underline", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("styles", "ADD", "main_background", "varchar(12) NOT NULL", "'#fff'");
	symposium_alter_table("styles", "ADD", "closed_opacity", "varchar(6) NOT NULL", "'1.0'");
	symposium_alter_table("styles", "ADD", "fontfamily", "varchar(128) NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "fontsize", "varchar(8) NOT NULL", "'15'");
	symposium_alter_table("styles", "ADD", "headingsfamily", "varchar(128) NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "headingssize", "varchar(8) NOT NULL", "'20'");
	
	// Add moderation field to topics
	symposium_alter_table("topics", "ADD", "allow_replies", "varchar(2) NOT NULL", "'on'");
	symposium_alter_table("topics", "ADD", "topic_approved", "varchar(2) NOT NULL", "'on'");

	// Update default language to English
 	$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET language = 'English'");

	
	// ***********************************************************************************************
	// Recreate languages for latest version *****************************************************
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_lang");

   	$table_name = $wpdb->prefix . "symposium_lang";
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
			  lid int(11) NOT NULL AUTO_INCREMENT,
			  language varchar(64) DEFAULT NULL,
			  sant text,
			  ts text,
			  fpit text,
			  sac text,
			  emw text,
			  p text,
			  c text,
			  cat text,
			  lac text,
			  top text,
			  btf text,
			  rew text,
			  sbl text,
			  f text,
			  r text,
			  v text,
			  sb text,
			  rer text,
			  tis text,
			  re text,
			  e text,
			  d text,
			  aar text,
			  rtt text,
			  wir text,
			  rep text,
			  tt text,
			  u text,
			  bt text,
			  t text,
			  mc text,
			  s text,
			  pw text,
			  sav text,
			  hsa text,
			  i text,
			  nft text,
			  nfr text,
			  prs text,
			  prm text,
			  tp text,
			  tps text,
			  rdv text,
			  lrb text,
			  reb text,
			  ar text,
			  too text,
			  st text,
			  fdd text,
			  ycs text,
			  nty text,
			  pen text,
			  fma text,
			  fmr text,
		  PRIMARY KEY lid (lid)
  		);";

     dbDelta($sql);
   	} 	

	// Include lanagues
	include('languages.php');

					      	
	// ***********************************************************************************************
 	// Update Database Version ***********************************************************************
	update_option("symposium_db_version", $symposium_db_ver);
	symposium_audit(array ('code'=>1, 'type'=>'system', 'plugin'=>'core', 'message'=>'Database version updated to '.$symposium_db_ver.'.'));

	// Audit activation
	symposium_audit(array ('code'=>1, 'type'=>'info', 'plugin'=>'core', 'message'=>'Core activation complete.'));

	
}
/* End of Activation */

function symposium_alter_table($table, $action, $field, $format, $default) {
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	

	$ok = '';
	$check = $wpdb->get_var("SELECT count(".$field.") FROM ".$wpdb->prefix."symposium_".$table);
	if ($check != '') { 
		$ok = 'exists';
		if ($check > 0) { $ok = 'same'; }
	}
	
	if ($action == "ADD") {
		if ($ok == 'exists' || $ok == 'same') {
			symposium_audit(array ('code'=>21, 'type'=>'info', 'plugin'=>'core', 'message'=> 'Skipped '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default.' as field already exists.')));
		} else {
		  	if ($wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$default) ) {
				symposium_audit(array ('code'=>21, 'type'=>'system', 'plugin'=>'core', 'message'=> 'Succeeded to '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default)));
		  	} else {
				symposium_audit(array ('code'=>21, 'type'=>'error', 'plugin'=>'core', 'message'=> 'Failed to '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default." ".$ok)));
		  	}
		}			
	}

	if ($action == "MODIFY COLUMN") {
		if ($ok == 'same') {
			symposium_audit(array ('code'=>21, 'type'=>'info', 'plugin'=>'core', 'message'=> 'Skipped '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default.' as field exists and is the same.')));
		} else {
		  	if ($wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$default) ) {
				symposium_audit(array ('code'=>21, 'type'=>'system', 'plugin'=>'core', 'message'=> 'Succeeded to '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default)));
		  	} else {
				symposium_audit(array ('code'=>21, 'type'=>'error', 'plugin'=>'core', 'message'=> 'Failed to '.strtolower($action.' field '.$field.' in table '.$table.' to '.$format.' '.$default)));
		  	}
		}			
	}

}

function symposium_uninstall() {
   
   	global $wpdb;

   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_config");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_topics");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_subs");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_cats");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_styles");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_lang");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_usermeta");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_audit");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_mail");

	// Delete Notification options
	delete_option("symposium_notification_inseconds");
	delete_option("symposium_notification_recc");
	delete_option("symposium_notification_triggercount");
	wp_clear_scheduled_hook('symposium_notification_hook');
	
	// Delete any options thats stored also
	delete_option('symposium_db_version');
	
}
/* End of Un-install */

function symposium_deactivate() {

	wp_clear_scheduled_hook('symposium_notification_hook');
	// Audit de-activation
	symposium_audit(array ('code'=>2, 'type'=>'system', 'plugin'=>'core', 'message'=>'Core de-activated.'));

}

// Add audit
function symposium_audit($array) {

   	global $wpdb, $current_user;
	wp_get_current_user();

    $rows_affected = $wpdb->insert( $wpdb->prefix.'symposium_audit', array( 
    	'code' => $array[code], 
		'type' => $array[type],
		'plugin' => $array[plugin],
		'uid' => $current_user->ID,
		'cid' => $array[cid]+1-1,
		'tid' => $array[tid]+1-1,
		'gid' => $array[gid]+1-1,
     	'message' => $array[message]
   		) );
   		
   	if (!$rows_affected) {
   		    		
	$rows_affected = $wpdb->insert( $wpdb->prefix.'symposium_audit', array( 
    	'code' => 13, 
		'type' => 'error',
		'plugin' => 'core',
		'uid' => $current_user->ID,
		'cid' => 0,
		'tid' => 0,
		'gid' => 0,
     	'message' => 'Failed to log audit item. Code:'.$array[code].' Type:'.$array[type].' Plugin:'.$array['plugin']
     	) );
   	
   	}
   	
    if ($array[debug] == 1) {
    	echo $wpdb->last_query;
    }
    	
    return $rows_affected;
}

// Checks is user meta exists, and if not creates it
function update_symposium_meta($meta, $value) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ($value == '') { $value = "''"; }
	
	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$current_user->ID))) {
	} else {
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix.'symposium_usermeta'."
			( 	uid, 
				forum_digest
			)
			VALUES ( %d, %s )", 
	        array(
	        	$current_user->ID, 
	        	'on'
	        	) 
	        ) );
	}
	// now update value
  	if ($wpdb->query("UPDATE ".$wpdb->prefix."symposium_usermeta SET ".$meta." = ".$value)) {
  		return true;
  	} else {
  		return false;
  	}
}

// Get user meta data
function get_symposium_meta($meta) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$current_user->ID))) {
		return $value;
	} else {
		return false;
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

/* NOTIFICATIONS */

add_action('init', 'symposium_notification_setoptions');
function symposium_notification_setoptions() {
	update_option("symposium_notification_inseconds",86400);
	// 60 = 1 minute, 3600 = 1 hour, 10800 = 3 hours, 21600 = 6 hours, 43200 = 12 hours, 86400 = Daily, 604800 = Weekly
	/* This is where the actual recurring event is scheduled */
	if (!wp_next_scheduled('symposium_notification_hook')) {
		$dt=explode(':',date('d:m:Y',time()));
		$schedule=mktime(0,1,0,$dt[1],$dt[0],$dt[2])+86400;
		// set for 00:01 from tomorrow
		wp_schedule_event($schedule, "symposium_notification_recc", "symposium_notification_hook");
	}
}

/* a reccurence has to be added to the cron_schedules array */
add_filter('cron_schedules', 'symposium_notification_more_reccurences');
function symposium_notification_more_reccurences($recc) {
	$recc['symposium_notification_recc'] = array('interval' => get_option("symposium_notification_inseconds"), 'display' => 'Symposium Notification Schedule');
	return $recc;
}
	
/* This is the scheduling hook for our plugin that is triggered by cron */
add_action('symposium_notification_hook','symposium_notification_trigger_schedule');
function symposium_notification_trigger_schedule() {
	
	global $wpdb;

	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix."symposium_lang"));
	$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
	
	// Check to see if we should be sending a digest
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix . 'symposium_config'));
	if ($send_summary == "on") {
		// Calculate yesterday			
		$startTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
		$endTime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
		
		// Get all new topics from previous period
		$topics_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0 AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime));

		if ($topics_count > 0) {

			// Get Forum URL
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));

			$body = "";
			
			$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats'." ORDER BY listorder"); 
			if ($categories) {
				foreach ($categories as $category) {
					
					$shown_category = false;
					$topics = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime." 
						ORDER BY tid"); 
					if ($topics) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".stripslashes($category->title)."</h1>";
						}
						$body .= "<h2>New Topics</h2>";
						$body .= "<ol>";
						foreach ($topics as $topic) {
							$body .= "<li><strong><a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>".stripslashes($topic->topic_subject)."</a></strong>";
							$body .= " started by ".$topic->display_name.":<br />";																
							$body .= stripslashes($topic->topic_post);
							$body .= "</li>";
						}
						$body .= "</ol>";
					}

					$replies = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
						WHERE topic_parent > 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime."
						ORDER BY topic_parent, tid"); 
					if ($replies) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".$category->title."</h1>";
						}
						$body .= "<h2>Replies in ".$category->title."</h2>";
						$current_parent = '';
						foreach ($replies as $reply) {
							$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$reply->topic_parent));
							if ($parent != $current_parent) {
								$body .= "<h3>".$parent."</h3>";
								$current_parent = $parent;
							}
							$body .= "<em>".$reply->display_name." wrote:</em> ";
							$post = $reply->topic_post;
							if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
							$body .= stripslashes($post);
							$body .= " <a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>View topic...</a>";
							$body .= "<br />";
							$body .= "<br />";
						}						
					}	
				}
			}
			
			$body .= "<p>".$language->ycs." <a href='".$forum_url."'>".$forum_url."</a>.</p>";
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email FROM ".$wpdb->prefix.'users'." u INNER JOIN ".$wpdb->prefix.'symposium_usermeta'." m ON u.ID = m.uid WHERE m.forum_digest = 'on'"); 
			if ($users) {
				foreach ($users as $user) {
					if(symposium_sendmail($user->user_email, $language->fdd, $body)) {
						update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
					}			
				}
			}

			// Report back to monitor the service - you can delete the following lines if you do not want this support
			// but in providing this anonymous information you can help us to help you
			if ($topics_count > 4) {
				$mail_to = 'info@wpsymposium.com';
				$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$config));				
				if(symposium_sendmail($mail_to, 'Forum Digest Report: '.get_site_url(), get_site_url().'<br />'.$forum_url.'<br /><br />'.$topics_count.' post(s)')) {
					update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
				}
			}

		}
	}

}

/* ====================================================== PHP FUNCTIONS ====================================================== */

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
    $difference = time() - $date;
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
	    	$retval = "";
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
    		$retval = str_replace("mess", "meses", $retval);
    		$retval = str_replace("year", "ano", $retval);
    		$retval = str_replace("anos", "ano", $retval);
	    	$retval = "hace ".$retval;
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
	    	$retval = "hace ".$retval;
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
    		$retval = str_replace("day", "dag&#324;", $retval);
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
function symposium_sendmail($email, $subject, $msg)
{
	global $wpdb;

	$subject = '=?UTF-8?B?' . base64_encode(html_entity_decode($subject)) . '?=';	

	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));

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
	
	if (mail($email, $subject, $body, $headers))
	{
		return true;
	} else {
		return false;
	}
}

// Hook to replace Smilies
function symposium_smilies($buffer){ // $buffer contains entire page
	global $wpdb;
	$emoticons = $wpdb->get_var($wpdb->prepare("SELECT emoticons FROM ".$wpdb->prefix . 'symposium_config'));
	
	if ($emoticons == "on") {
		
		$smileys = WP_PLUGIN_URL . '/wp-symposium/smilies/';
		$smileys_dir = WP_PLUGIN_DIR . '/wp-symposium/smilies/';
		// Smilies as classic text
		$buffer = str_replace(":)", "<img src='".$smileys."smile.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":(", "<img src='".$smileys."sad.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":'(", "<img src='".$smileys."crying.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":x", "<img src='".$smileys."kiss.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":X", "<img src='".$smileys."shutup.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":D", "<img src='".$smileys."laugh.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":|", "<img src='".$smileys."neutral.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":?", "<img src='".$smileys."question.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":z", "<img src='".$smileys."sleepy.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(":P", "<img src='".$smileys."tongue.png' alt='emoticon'/>", $buffer);
		$buffer = str_replace(";)", "<img src='".$smileys."wink.png' alt='emoticon'/>", $buffer);
		// Other images
		
		$i = 0;
		do {
			$i++;
			$start = strpos($buffer, "{{");
			if ($start === false) {
			} else {
				$end = strpos($buffer, "}}");
				if ($end === false) {
				} else {
					$first_bit = substr($buffer, 0, $start);
					$last_bit = substr($buffer, $end+2, strlen($buffer)-$end-2);
					$bit = substr($buffer, $start+2, $end-$start-2);
					if (file_exists($smileys_dir.$bit.".png")) {
						$buffer = $first_bit."<img src='".$smileys.$bit.".png' alt='emoticon'/>".$last_bit;
					} else {
						$buffer = $first_bit."&#123;&#123;".$bit."&#125;&#125;".$last_bit;
					}
				}
			}
		} while ($i < 100 && strpos($buffer, "{{")>0);
		
	}
	
	return $buffer;
}

// Hook for URL redirect
function symposium_redirect($buffer){ 
	
	global $wpdb;

	$seo = $wpdb->get_var($wpdb->prepare("SELECT seo FROM ".$wpdb->prefix . 'symposium_config'));
	
	if ($seo == "on") {
	
		$thispage = get_permalink();
	
		if (function_exists('symposium_forum')) {
				
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix."symposium_config"));
			if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
			
			$parsed_url=parse_url($_SERVER['REQUEST_URI']);
			
			if ( substr(get_site_url().$parsed_url['path'], 0, strlen($forum_url)) == $forum_url ) {
				
				$path = $parsed_url['path'];
				if ($path[strlen($path)-1] != '/') { $path .= '/'; }
				$paths = explode('/',$path);
				$query = $parsed_url['query'];
				
				$max = count($paths);
				$id = $paths[$max-4];
				$category = $paths[$max-3];
				$topic = $paths[$max-2];
				if (is_numeric($category)) {
					// Categories not in use
					$id = $category;
					$category = "-";
				}
						
				// If an ID was passed	
				if ($id != '') {
					if (!(isset($_GET['show']))) {
						// Just show category
						header("Location: ".$forum_url."?cid=".$id);
						exit;					
					} else {				
						// Try getting category for id
						$cat_id = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix."symposium_topics"." WHERE tid = ".$id));
						if ($cat_id != 0) {
							header("Location: ".$forum_url."?cid=".$cat_id."&show=".$id);
							exit;
						} else {
							header("Location: ".$forum_url."?cid=&show=".$id);
							exit;
						}
					}
				}
			}
		}
		
	}
	
    return $buffer;
}

function symposium_admin_check() {
	global $wpdb;
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
	if ($forum_url == "Important: Please update!") {
		echo "<div class='updated'><p><strong>Important!</strong> Please set <a href='admin.php?page=symposium_options'>WP Symposium Options</a> immediately.</p></div>";
	}
}
add_action('admin_notices', 'symposium_admin_check');

function symposium_replace(){
	ob_start();
	ob_start('symposium_smilies');
	ob_start('symposium_redirect');
}
add_action('template_redirect', 'symposium_replace');

/* ====================================================== AJAX FUNCTIONS ====================================================== */

// Add Stylesheet
function add_symposium_stylesheet() {
	if (!is_admin()) {
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/symposium.css';
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/symposium.css';
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    } else {
		    wp_die( __('Stylesheet ('.$myStyleFile.' not found.') );
	    }
	    
        wp_register_style('symposium_jquery-ui-css', WP_PLUGIN_URL.'/wp-symposium/jquery-ui.css');
        wp_enqueue_style('symposium_jquery-ui-css');

	    
	}    
}
add_action('wp_print_styles', 'add_symposium_stylesheet');


// Add jQuery and jQuery scripts
function forum_init() {
	global $wpdb;
	$jquery = $wpdb->get_var($wpdb->prepare("SELECT jquery FROM ".$wpdb->prefix . 'symposium_config'));
	if ($jquery=="on" && !is_admin()) {
		wp_enqueue_script('jquery');

        wp_register_script('symposium_jquery-ui-js', WP_PLUGIN_URL.'/wp-symposium/jquery-ui.min.js');
        wp_enqueue_script('symposium_jquery-ui-js');
		
	}
}
add_action('init', 'forum_init');

// Add jQuery and jQuery scripts
function admin_init() {
	if (is_admin()) {
		// Color Picker
		wp_register_script('symposium_iColorPicker', WP_PLUGIN_URL . '/wp-symposium/iColorPicker.js');
	    	wp_enqueue_script('symposium_iColorPicker');

	}

}
add_action('init', 'admin_init');




register_activation_hook(__FILE__,'symposium_activate');
register_deactivation_hook(__FILE__, 'symposium_deactivate');
register_uninstall_hook(__FILE__, 'symposium_uninstall');


?>
