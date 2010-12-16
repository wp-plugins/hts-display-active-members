<?php
/* ====================================================== ADMIN MENU ====================================================== */

function symposium_plugin_menu() {
	add_menu_page(__('Symposium'), __('Symposium'), 'edit_themes', 'symposium_options', 'symposium_plugin_options', '', 7); 
	add_submenu_page('symposium_options', __('Options'), __('Options'), 'edit_themes', 'symposium_options', 'symposium_plugin_options');
	add_submenu_page('symposium_options', __('Styles'), __('Styles'), 'edit_themes', 'symposium_styles', 'symposium_plugin_styles');
	add_submenu_page('symposium_options', __('Forum Categories'), __('Forum Categories'), 'edit_themes', 'symposium_categories', 'symposium_plugin_categories');
	add_submenu_page('symposium_options', __('Health Check'), __('Health Check'), 'edit_themes', 'symposium_debug', 'symposium_plugin_debug');
	add_submenu_page('symposium_options', __('Event Audit'), __('Event Audit'), 'edit_themes', 'symposium_event', 'symposium_plugin_event');
}
add_action('admin_menu', 'symposium_plugin_menu');

function symposium_plugin_event() {

	global $wpdb;

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>WP Symposium Event Log/Audit</h2>';
  	
   	echo '<form method="post" action="">';
	echo '<input type="hidden" name="symposium_clear_events" value="Y">';
   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary delete" value="Clear event audit log" /></p>';
    echo '</form>';

    if( isset($_POST[ 'symposium_clear_events' ]) && $_POST[ 'symposium_clear_events' ] == 'Y' ) {
        $success = $wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_audit") );
        if ($success) {
			echo "<div class='updated'><p>Audit log cleared.</p></div>";
			symposium_audit(array ('code'=>7, 'type'=>'info', 'plugin'=>'core', 'message'=>'Event audit log cleared.'));
        } else {
		   	echo '<div class="error"><p>Sorry, there was a problem clearing the even audit log.</p></div>';
			symposium_audit(array ('code'=>8, 'type'=>'error', 'plugin'=>'core', 'message'=>'Event audit log failed to clear.'));
        }
    }

	$audit = $wpdb->get_results("SELECT a.*, u.display_name FROM ".$wpdb->prefix."symposium_audit a LEFT JOIN ".$wpdb->prefix."users u ON a.uid = u.ID ORDER BY aid DESC");

	if ($audit) {
		echo '<table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>ID</th>';
		echo '<th>Code</th>';
		echo '<th>Type</th>';
		echo '<th>User</th>';
		echo '<th>Time</th>';
		echo '<th>Message</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<th>ID</th>';
		echo '<th>Code</th>';
		echo '<th>Type</th>';
		echo '<th>User</th>';
		echo '<th>Time</th>';
		echo '<th>Message</th>';
		echo '</tr>';
		echo '</tfoot>';
		echo '<tbody>';
		foreach ($audit as $line) {
			echo '<tr>';
			echo '<td valign="top">'.$line->aid.'</td>';
			echo '<td valign="top">'.$line->code.'</td>';
			echo '<td valign="top"><img src="'.get_site_url().'/wp-content/plugins/wp-symposium/images/'.$line->type.'.png" alt="'.$line->type.'" /></td>';
			echo '<td valign="top" style="width: 150px">'.$line->display_name.'</td>';
			echo '<td valign="top" style="width: 150px">'.$line->stamp.'</td>';
			echo '<td valign="top">'.$line->message.'</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
  	echo '</div>';
}


function symposium_plugin_debug() {

/* ============================================================================================================================ */

	global $wpdb, $current_user;
	wp_get_current_user();

 	$wpdb->show_errors();
 	
  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>WP Symposium Health Check</h2>';

   	echo '<div class="error"><p><strong>Warning!</strong> Any interaction through this screen is done so at your own risk. You could disable your database and/or WP Symposium.<br />You are <strong>strongly advised</strong> to take a backup first. It is recommended that only <strong>advanced users</strong> use this screen. Note that error debugging is switched on for this page.</p></div>';

	echo "<div style='width:45%; float:right'>";

  	echo '<h2>Table Structures</h2><p>';
  	
  	$ok = "Test Result: <span style='color:green; font-weight:bold;'>OK</span><br /><br />";
  	$fail = "<span style='color:red; font-weight:bold;'>";
  	$fail2 = "</span><br /><br />";
  	$overall = "ok";
  	
  	// Categories
   	$table_name = $wpdb->prefix . "symposium_cats";
   	$status = $ok;
   	echo '<strong>Categories: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'cid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'title')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'allow_new')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'defaultcat')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Topics
   	$table_name = $wpdb->prefix . "symposium_config";
   	$status = $ok;
   	echo '<strong>Options: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'oid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'categories_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'categories_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_background_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_color_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_1')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_2')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_3')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'text_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'table_rollover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'link')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'link_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'table_border')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'replies_border_size')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'text_color_2')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'row_border_style')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'row_border_size')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'border_radius')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'label')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'footer')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'send_summary')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'forum_url')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'from_email')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'language')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'underline')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'preview1')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'preview2')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'viewer')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'include_admin')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'oldest_first')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'wp_width')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'main_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'closed_opacity')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'closed_word')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'fontfamily')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'fontsize')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'headingsfamily')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'headingssize')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Languages
   	$table_name = $wpdb->prefix . "symposium_lang";
   	$status = $ok;
   	echo '<strong>Languages: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'lid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'language')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'sant')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'p')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'rtt')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'c')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'e')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'd')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'reb')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'u')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'ts')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'fpit')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'cat')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 't')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'top')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tp')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tps')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'rep')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'r')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'v')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'sac')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'emw')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'rew')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'sb')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 're')) { $status = "X"; }		
		if (!symposium_field_exists($table_name, 'rer')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tis')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'aar')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'nft')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'nfr')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'wir')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tt')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'rdv')) { $status = "X"; }		
		if (!symposium_field_exists($table_name, 'btf')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bt')) { $status = "X"; }		
		if (!symposium_field_exists($table_name, 'mc')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 's')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'hsa')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'i')) { $status = "X"; }		
		if (!symposium_field_exists($table_name, 'pw')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'sav')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'prs')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'prm')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'lrb')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'reb')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'ar')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'too')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'st')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'lrb')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'fdd')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'ycs')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'nty')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Styles
   	$table_name = $wpdb->prefix . "symposium_styles";
   	$status = $ok;
   	echo '<strong>Styles Library: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'sid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'title')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'categories_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'categories_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_background_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bigbutton_color_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_1')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_2')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'bg_color_3')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'text_color')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'table_rollover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'link')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'link_hover')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'table_border')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'replies_border_size')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'text_color_2')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'row_border_style')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'row_border_size')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'border_radius')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'label')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'underline')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'main_background')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'closed_opacity')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Subscriptions
   	$table_name = $wpdb->prefix . "symposium_subs";
   	$status = $ok;
   	echo '<strong>Subscriptions: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'sid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'uid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'cid')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Topics
   	$table_name = $wpdb->prefix . "symposium_topics";
   	$status = $ok;
   	echo '<strong>Topics/Posts: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'tid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_group')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_category')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_subject')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_post')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_owner')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_date')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_parent')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_views')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_started')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'topic_sticky')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'allow_replies')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;
   	
  	// Usermeta
   	$table_name = $wpdb->prefix . "symposium_usermeta";
   	$status = $ok;
   	echo '<strong>User Meta-data: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'mid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'uid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'forum_digest')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;

  	// Audit
   	$table_name = $wpdb->prefix . "symposium_audit";
   	$status = $ok;
   	echo '<strong>Audit: '.$table_name.'</strong><br />';
   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   		$status = $fail."Table doesn't exist".$fail2;
   	} else {
		if (!symposium_field_exists($table_name, 'aid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'code')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'type')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'uid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'cid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'tid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'gid')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'message')) { $status = "X"; }
		if (!symposium_field_exists($table_name, 'stamp')) { $status = "X"; }
		if ($status == "X") { $status = $fail."Incomplete table".$fail2; $overall = "X"; }
   	}   	
   	echo $status;

   	echo '<h3>Overall</h3>';
   	
   	if ($overall == "ok") {
   		echo "<p>".$ok;
   		echo "Your database structure is accurate to the current version.</p>";
   	} else {
   		echo $fail."Your database is not accurate with the current version. Try re-applying the database upgrades below.".$fail2;
   	}

	// ********** Reset database version
   	echo '<h2>Re-apply database upgrades</h2>';
   	echo "<p>This will create any missing tables, and add any missing fields, shown above.</p>";
   	echo '<form method="post" action="">';
	echo '<input type="hidden" name="symposium_db_reset" value="Y">';
    if( isset($_POST[ 'symposium_db_reset' ]) && $_POST[ 'symposium_db_reset' ] == 'Y' ) {
		delete_option('symposium_db_version');
        echo "<div style='border:1px solid #060;border-radius:5px;padding-left:8px; background-color: #9f9; font-weight: bold; color: #000;margin-bottom:10px;'>";
        echo "<p>To complete the process, please <a href='plugins.php'>de-activate and re-activate</a> the core WP Symposium plugin.</p>";
        echo "</div>";
    }
   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Reset database version" /></p>';
    echo '</form>';

	// ********** Test AJAX
   	echo '<h2>AJAX test</h2>';
   	echo '<p>An AJAX function will be called, passing a random number as a parameter. That value will be returned multipled by 100, and shown below on screen.</p>';
   	echo '<p class="submit"><input type="submit" id="testAJAX" name="Submit" class="button-primary" value="Click to test" /></p>';
   	echo '<input type="text" id="testAJAX_results" style="width: 200px" value="Result will be posted here.">';   		
   	echo '</p>';
   	
	
	// ********** Audit Test
   	echo '<h2>Audit Trail Test</h2>';
   	echo '<p>Latest message posted: ';
  	if ($latest_audit = $wpdb->get_row("SELECT a.*, u.display_name FROM ".$wpdb->prefix."symposium_audit a LEFT JOIN ".$wpdb->prefix."users u ON a.uid = u.ID ORDER BY aid DESC")) {
	  	echo $latest_audit->message;
  	} else {
  		echo $wpdb->last_query;
  	}
  	echo '</p>';

   	echo '<p>This will post a test message to the event audit trail.</p>';
    if( isset($_POST[ 'symposium_audit_test' ]) && $_POST[ 'symposium_audit_test' ] == 'Y' ) {
		symposium_audit(array ('code'=>11, 'type'=>'system', 'plugin'=>'core', 'message'=>'Test post to event audit log.'));
		echo "<p>Test post submitted - please <a href='admin.php?page=symposium_event'>check</a> to see if it was added.</p>";
    }
   	echo '<form method="post" action="">';
	echo '<input type="hidden" name="symposium_audit_test" value="Y">';
   	echo '<p class="submit"><input type="submit" id="testAJAX" name="Submit" class="button-primary" value="Click to log" /></p>';
   	echo '</p>';
    echo '</form>';
   	
	echo "</div><div style='width:45%; float:left'>";
	
	
  	// ********** Summary
	echo '<h2>Version Numbers</h2>';

  	echo "<p>";
	  	echo "WP Symposium internal version: ".get_option("symposium_version")."<br />";
	  	echo "WP Symposium database version: ";
	  	$db_ver = get_option("symposium_db_version");
	  	if (!$db_ver) { 
	  		echo "<span style='color:red; font-weight:bold;'>Error!</span> No database version set. You may need to re-apply the upgrades</span><br />"; 
	  	} else {
	  		echo $db_ver."<br />";
	  	}
		$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
		if ($forum_url == "Important: Please update!") {
			echo $fail."You must update your forum URL on the <a href='admin.php?page=symposium_options'>options page</a>.".$fail2;
		} else {
		  	echo "According to the <a href='admin.php?page=symposium_options'>options page</a>, the forum is <a href='".$forum_url."'>here</a>. Click to check.";
		}
  	echo "</p>";
  	
	// ********** Test Email   	
    if( isset($_POST[ 'symposium_testemail' ]) && $_POST[ 'symposium_testemail' ] == 'Y' ) {
    	$to = $_POST['symposium_testemail_address'];
		if (symposium_sendmail($to, "WP Symposium Test Email", "This is a test email sent from ".get_site_url())) {
			echo "<div class='updated'><p>Email to ".$to." sent successfully. If you entered an invalid email address it won't arrive though!</p></div>";
		} else {
			echo "<div class='error'><p>Email failed to send.</p></div>";
		}
    }
   	echo '<h2>Send a test email</h2>';
   	echo '<p>Enter a valid email address to test sending an email from the server.</p>';
   	echo '<form method="post" action="">';
	echo '<input type="hidden" name="symposium_testemail" value="Y">';
   	echo '<input type="text" name="symposium_testemail_address" value="" style="width:300px" class="regular-text">';
   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Send email" /></p>';
   	echo '</form>';

	// ********** Test Updating a Value   		
   	echo '<h2>Test updating the database</h2>';
   	echo '<p>Remember, some values may be case-sensitive.</p>';
   	echo '<form method="post" action="">';
    if( isset($_POST[ 'symposium_test_viewer' ]) && $_POST[ 'symposium_test_viewer' ] == 'Y' ) {
        $table = $_POST[ 'symposium_testupdate_table' ];
        $field = $_POST[ 'symposium_testupdate_field' ];
        $value = stripslashes($_POST[ 'symposium_testupdate_value' ]);
        $result = $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_".$table." SET ".$field." = '".$value."'") );
        echo "<div style='border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
 		if ($result) {
			echo "<p>Database successfully updated. Remember this is a live effect, so make sure you set it correctly for your forum!</p>";
		} else {
			if ($result === false) {
				echo "<p>Database failed to update.</p>";
			} else {
				echo "<p>Database successfully updated. The value being set is the same as it was before (".$value.").</p>";
			}
		}
		echo "<p>".$wpdb->last_query."</p></div>";
    }
	echo '<input type="hidden" name="symposium_test_viewer" value="Y">';
	echo '<div style="width:200px; float: left;">Table (eg: config):</div>';
   	echo '<input type="text" name="symposium_testupdate_table" value="'.$table.'" class="regular-text"><br />';
	echo '<div style="width:200px; float: left;">Field (eg: viewer):</div>';
   	echo '<input type="text" name="symposium_testupdate_field" value="'.$field.'" class="regular-text"><br />';
	echo '<div style="width:200px; float: left;">Field (eg: Guest):</div>';
   	echo '<input type="text" name="symposium_testupdate_value" value="'.$value.'" class="regular-text"><br />';   	
   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Update field" /></p>';
   	echo '</form>';

	// ********** Languages
	echo '<h2>Installed Languages</h2>';
	$success = "OK";
	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . 'symposium_lang'));
	$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
	if (!$language) {
		$success = "Language translation not available for [".$language_key."] - try setting the language on the <a href='admin.php?page=symposium_options'>Options</a> page.";
	}
	$language_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix . 'symposium_lang'));
	if ($language > 0) {
		$language_options = $wpdb->get_results("SELECT DISTINCT language FROM ".$wpdb->prefix.'symposium_lang');
		if ($language_options) {
			echo '<ol>';
			foreach ($language_options as $option)
			{
				$xml_dir = WP_PLUGIN_DIR . '/wp-symposium/languages.xml';
				if ($option->language == "XML file found, but failed to load.") { 
					$success = "The default language has been installed. However, the XML language file was found at ".$xml_dir.", but no additional languages have been installed - probably need fopen or curl enabled on the server.";
				} else {
					if ($option->language == "XML file not found") { 
						$success = "The XML language file was not found at ".$xml_dir.".";
					} else {
						echo "<li>".$option->language."</li>";
					}
				}
			}
			echo '</ol>';
		}		
	} else {
		$success = "There was an undetermind problem loading the XML file";
	}
	if ($success == "OK" ) { echo $ok; } else { echo $fail.$success.$fail2; }
	   	   	
  	echo '</div>';
}

function symposium_field_exists($tablename, $fieldname) {
	global $wpdb;
	$check = $wpdb->get_var("SELECT count(".$fieldname.") FROM ".$tablename);
	if ($check != '') {
		return true;
	} else {
		echo "Missing field: ".$fieldname."<br />";
		return false;
	}
}

function symposium_plugin_categories() {

	global $wpdb;

	?>
	<script type="text/javascript">
    jQuery(document).ready(function() { 	

		jQuery('.areyousure').click(function(){
		  var answer = confirm('Are you sure?\n\nAll topics in the category will become un-categorised.');
		  return answer // answer is a boolean
		});
		
    });
 
	</script>
	<?php 
	
  	if (!current_user_can('manage_options'))  {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}
  	
  	$action = $_GET['action'];

	// Update values
	if (isset($_POST['title'])) {
		
   		$range = array_keys($_POST['cid']);
		foreach ($range as $key) {
		    $cid = $_POST['cid'][$key];
		    $title = $_POST['title'][$key];
		    $listorder = $_POST['listorder'][$key];
		    $allow_new = $_POST['allow_new'][$key];
		    
		    if ($cid == $_POST['default_category']) {
		    	$defaultcat = "on";
		    } else {
		    	$defaultcat = "";
		    }
		    
			$wpdb->query( $wpdb->prepare( "
				UPDATE ".$wpdb->prefix.'symposium_cats'."
				SET title = %s, listorder = %s, allow_new = %s, defaultcat = %s
				WHERE cid = %d", 
		        $title, $listorder, $allow_new, $defaultcat, $cid  ) );
		        			
		}

	}
		
  	// Add new category?
  	if ( ($_POST['new_title'] != '') && ($_POST['new_title'] != 'Add New Category...') ) {
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix.'symposium_cats'."
			( 	title, 
				listorder,
				allow_new
			)
			VALUES ( %s, %d, %s )", 
	        array(
	        	$_POST['new_title'], 
	        	$_POST['new_listorder'],
	        	$_POST['new_allow_new']
	        	) 
	        ) );
	      
	    $action = '';
	}

  	// Delete a category?
  	if ($action == 'delcid') {
  		// Must leave at least one category, so check
		$cat_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats'));
		if ($cat_count > 1) {
			$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$_GET['cid']) );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = 0 WHERE topic_category = ".$_GET['cid']) );					} else {
			echo "<div class='error'><p>You must have at least one category, you can hide the category title on the <a href='?page=symposium_options'>options page</a>.</p></div>";
		}
  	}
  	

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Forum Categories</h2>';

	?> 
 
	<form method="post" action=""> 
	<input type="hidden" name="symposium_update" value="Y">

	<table class="form-table">
	<tr>
	<td style="width:20px">ID</td>
	<td>Category Title</td>
	<td>Order</td>
	<td>Allow new topics</td>
	<td>&nbsp;</td>
	</tr> 
	
	<?php
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');

	if ($categories) {
		foreach ($categories as $category) {

			echo '<tr valign="top">';
			echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
			echo '<td>'.$category->cid.'</td>';
			echo '<td><input name="title[]" type="text" value="'.stripslashes($category->title).'" class="regular-text" /></td>';
			echo '<td><input name="listorder[]" type="text" value="'.$category->listorder.'" /></td>';
			echo '<td>';
			echo '<select name="allow_new[]">';
			echo '<option value="on"';
				if ($category->allow_new == "on") { echo " SELECTED"; }
				echo '>Yes</option>';
			echo '<option value=""';
				if ($category->allow_new != "on") { echo " SELECTED"; }
				echo '>No</option>';
			echo '</select>';
			echo '</td>';
			echo '</td>';
			echo '<td><a class="areyousure" href="?page=symposium_categories&action=delcid&cid='.$category->cid.'">Delete</td></td>';
			echo '</tr>';
	
		}
	}
	?>
	
	<tr><td colspan=2 align='right'>Default Category for new Topics:</td>
	<td colspan=3>
	<?php
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');

	if ($categories) {
		echo "<select name='default_category'>";
		foreach ($categories as $category) {
			echo "<option value=".$category->cid;
			if ($category->defaultcat == "on") { echo " SELECTED"; }
			echo ">".$category->title."</option>";
		}
		echo "</select>";
	}	
	?>
	</td>
	</tr>

	<tr><td colspan=5><hr /></td></tr>
	<tr valign="top">
	<td>&nbsp;</td>
	<td><input name="new_title" type="text" onclick="javascript:this.value = ''" value="Add New Category..." class="regular-text" />
	<td><input name="new_listorder" type="text" value="0" />
	<td>
	<input type="checkbox" name="new_allow_new" CHECKED />
	</td>
	<td colspan=2>&nbsp;</td>
	</tr>
	</table> 
	 
	<p class="submit"> 
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" /> 
	</p> 
	
	<p>
	Note: if you delete a category that has topics, you will need to select a parent category for those topics if you want to make use of the categories feature.
	<p>
	</form> 
	
	<?php
  
  	echo '</div>';

} 	

function symposium_plugin_styles() {
	
	global $wpdb;

	if (!current_user_can('manage_options'))  {
	    wp_die( __('You do not have sufficient permissions to access this page.') );
	}

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Styles</h2>';
  	
    // See if the user has selected a template
    if( isset($_POST[ 'symposium_apply' ]) && $_POST[ 'symposium_apply' ] == 'Y' ) {
		$style = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_styles'." WHERE sid = ".$_POST['sid']);
		if ($style) {
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$style->categories_background."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$style->categories_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$style->border_radius."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$style->bigbutton_background."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$style->bigbutton_background_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$style->bigbutton_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$style->bigbutton_color_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$style->bg_color_1."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$style->bg_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$style->bg_color_3."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$style->row_border_style."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$style->row_border_size) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = ".$style->replies_border_size) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$style->table_rollover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$style->table_border) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$style->text_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$style->text_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$style->link."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET underline = '".$style->underline."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$style->link_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$style->label."'") );

	        // Put an settings updated message on the screen
			echo "<div class='updated'><p>Template Applied</p></div>";
		} else {
			echo "<div class='error'><p>Template Not Found</p></div>";
		}
    }
    
    // See if the user has posted us some information
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'Y' ) {
        // Read their posted value
        $categories_background = $_POST[ 'categories_background' ];
        $categories_color = $_POST[ 'categories_color' ];
        $border_radius = $_POST[ 'border_radius' ];
        $bigbutton_background = $_POST[ 'bigbutton_background' ];
        $bigbutton_background_hover = $_POST[ 'bigbutton_background_hover' ];
        $bigbutton_color = $_POST[ 'bigbutton_color' ];
        $bigbutton_color_hover = $_POST[ 'bigbutton_color_hover' ];
        $bg_color_1 = $_POST[ 'bg_color_1' ];
        $bg_color_2 = $_POST[ 'bg_color_2' ];
        $bg_color_3 = $_POST[ 'bg_color_3' ];
        $row_border_style = $_POST[ 'row_border_style' ];
        $row_border_size = $_POST[ 'row_border_size' ];
        $table_rollover = $_POST[ 'table_rollover' ];
        $table_border = $_POST[ 'table_border' ];
        $replies_border_size = $_POST[ 'replies_border_size' ];
        $text_color = $_POST[ 'text_color' ];
        $text_color_2 = $_POST[ 'text_color_2' ];
        $link = $_POST[ 'link' ];
        $underline = $_POST[ 'underline' ];
        $link_hover = $_POST[ 'link_hover' ];
        $label = $_POST[ 'label' ];
        $closed_opacity = $_POST[ 'closed_opacity' ];

        // Save the posted value in the database
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$categories_background."'") );				
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$categories_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$border_radius."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$bigbutton_background."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$bigbutton_background_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$bigbutton_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$bigbutton_color_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$bg_color_1."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$bg_color_2."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$bg_color_3."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$row_border_style."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$row_border_size) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$table_rollover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$table_border) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = '".$replies_border_size."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$text_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$text_color_2."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$link."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET underline = '".$underline."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$link_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$label."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET closed_opacity = '".$closed_opacity."'") );					

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Options Saved</p></div>";

    }
    
	$styles = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_config');

	if ($styles) {
		foreach ($styles as $style) {
			
			?> 
		 
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="Y">
		
			<table class="form-table"> 
			
			<tr valign="top"> 
			<th scope="row"><label for="main_background">Main background <img src="../wp-content/plugins/wp-symposium/new.png" alt="New!" /></label></th> 
			<td><input name="main_background" type="text" id="main_background" class="iColorPicker" value="<?php echo $style->main_background; ?>"  /> 
			<span class="description">Main background colour (for example, new/edit forum topic/post)</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="border_radius">Corners</label></th> 
			<td>
			<select name="border_radius" id="border_radius"> 
				<option <?if ( $style->border_radius=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->border_radius=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->border_radius=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->border_radius=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
				<option <?if ( $style->border_radius=='4') { echo "selected='selected'"; } ?> value='4'>4 pixels</option> 
				<option <?if ( $style->border_radius=='5') { echo "selected='selected'"; } ?> value='5'>5 pixels</option> 
				<option <?if ( $style->border_radius=='6') { echo "selected='selected'"; } ?> value='6'>6 pixels</option> 
				<option <?if ( $style->border_radius=='7') { echo "selected='selected'"; } ?> value='7'>7 pixels</option> 
				<option <?if ( $style->border_radius=='8') { echo "selected='selected'"; } ?> value='8'>8 pixels</option> 
				<option <?if ( $style->border_radius=='9') { echo "selected='selected'"; } ?> value='9'>9 pixels</option> 
				<option <?if ( $style->border_radius=='10') { echo "selected='selected'"; } ?> value='10'>10 pixels</option> 
				<option <?if ( $style->border_radius=='11') { echo "selected='selected'"; } ?> value='11'>11 pixels</option> 
				<option <?if ( $style->border_radius=='12') { echo "selected='selected'"; } ?> value='12'>12 pixels</option> 
				<option <?if ( $style->border_radius=='13') { echo "selected='selected'"; } ?> value='13'>13 pixels</option> 
				<option <?if ( $style->border_radius=='14') { echo "selected='selected'"; } ?> value='14'>14 pixels</option> 
				<option <?if ( $style->border_radius=='15') { echo "selected='selected'"; } ?> value='15'>15 pixels</option> 
			</select> 
			<span class="description">Rounded Corner radius (not supported in all browsers)</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_background">Buttons</label></th> 
			<td><input name="bigbutton_background" type="text" id="bigbutton_background" class="iColorPicker" value="<?php echo $style->bigbutton_background; ?>"  /> 
			<span class="description">Background Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_background_hover"></label></th> 
			<td><input name="bigbutton_background_hover" type="text" id="bigbutton_background_hover" class="iColorPicker" value="<?php echo $style->bigbutton_background_hover; ?>"  /> 
			<span class="description">Background Colour on mouse hover</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_color"></label></th> 
			<td><input name="bigbutton_color" type="text" id="bigbutton_color" class="iColorPicker" value="<?php echo $style->bigbutton_color; ?>"  /> 
			<span class="description">Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_color_hover"></label></th> 
			<td><input name="bigbutton_color_hover" type="text" id="bigbutton_color_hover" class="iColorPicker" value="<?php echo $style->bigbutton_color_hover; ?>"  /> 
			<span class="description">Text Colour on mouse hover</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_1">Tables</label></th> 
			<td><input name="bg_color_1" type="text" id="bg_color_1" class="iColorPicker" value="<?php echo $style->bg_color_1; ?>"  /> 
			<span class="description">Primary Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_2"></label></th> 
			<td><input name="bg_color_2" type="text" id="bg_color_2" class="iColorPicker" value="<?php echo $style->bg_color_2; ?>"  /> 
			<span class="description">Row Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_3"></label></th> 
			<td><input name="bg_color_3" type="text" id="bg_color_3" class="iColorPicker" value="<?php echo $style->bg_color_3; ?>"  /> 
			<span class="description">Alternative Row Colour</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="table_rollover"></label></th> 
			<td><input name="table_rollover" type="text" id="table_rollover" class="iColorPicker" value="<?php echo $style->table_rollover; ?>"  /> 
			<span class="description">Row colour on mouse hover</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="table_border"></label></th> 
			<td>
			<select name="table_border" id="table_border"> 
				<option <?if ( $style->table_border=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->table_border=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->table_border=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->table_border=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">Border Size</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="row_border_style">Table/Rows</label></th> 
			<td>
			<select name="row_border_style" id="row_border_styledefault_role"> 
				<option <?if ( $style->row_border_style=='dotted') { echo "selected='selected'"; } ?> value='dotted'>Dotted</option> 
				<option <?if ( $style->row_border_style=='dashed') { echo "selected='selected'"; } ?> value='dashed'>Dashed</option> 
				<option <?if ( $style->row_border_style=='solid') { echo "selected='selected'"; } ?> value='solid'>Solid</option> 
			</select> 
			<span class="description">Border style between rows</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="row_border_size"></label></th> 
			<td>
			<select name="row_border_size" id="row_border_size"> 
				<option <?if ( $style->row_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->row_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->row_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->row_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">Border size between rows</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="replies_border_size">Other borders</label></th> 
			<td>
			<select name="replies_border_size" id="replies_border_size"> 
				<option <?if ( $style->replies_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->replies_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->replies_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->replies_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">For new topics/replies and topic replies</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="categories_background">In-Table Headings</label></th> 
			<td><input name="categories_background" type="text" id="categories_background" class="iColorPicker" value="<?php echo $style->categories_background; ?>"  /> 
			<span class="description">Background colour of, for example, current Category within a Forum Topic</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="categories_color"></label></th> 
			<td><input name="categories_color" type="text" id="categories_color" class="iColorPicker" value="<?php echo $style->categories_color; ?>"  /> 
			<span class="description">Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="text_color">Text Colour</label></th> 
			<td><input name="text_color" type="text" id="text_color" class="iColorPicker" value="<?php echo $style->text_color; ?>"  /> 
			<span class="description">Primary Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="text_color_2"></label></th> 
			<td><input name="text_color_2" type="text" id="text_color_2" class="iColorPicker" value="<?php echo $style->text_color_2; ?>"  /> 
			<span class="description">Alternative Text Colour / Border Colour between rows</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="link">Topic Links</label></th> 
			<td><input name="link" type="text" id="link" class="iColorPicker" value="<?php echo $style->link; ?>"  /> 
			<span class="description">Link Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="link_hover"</label></th> 
			<td><input name="link_hover" type="text" id="link_hover" class="iColorPicker" value="<?php echo $style->link_hover; ?>"  /> 
			<span class="description">Link Colour on mouse hover</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="underline">Underlined?</label></th> 
			<td>
			<select name="underline" id="underline"> 
				<option <?if ( $style->underline=='') { echo "selected='selected'"; } ?> value=''>No</option> 
				<option <?if ( $style->underline=='on') { echo "selected='selected'"; } ?> value='on'>Yes</option> 
			</select> 
			<span class="description">Whether links are underlined or not</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="label">Labels</label></th> 
			<td><input name="label" type="text" id="label" class="iColorPicker" value="<?php echo $style->label; ?>"  /> 
			<span class="description">Colour of text labels outside forum areas</span></td> 
			</tr> 
			
			<tr valign="top"> 
			<td colspan="2"><h3>Forum Styles</h3></td> 
			</tr> 
			
			<tr valign="top"> 
			<th scope="row"><label for="closed_opacity">Closed topics <img src="../wp-content/plugins/wp-symposium/new.png" alt="New" /></label></th> 
			<td><input name="closed_opacity" type="text" id="closed_opacity" class="iColorPicker" value="<?php echo $style->closed_opacity; ?>"  /> 
			<?php
			$closed_word = $wpdb->get_var($wpdb->prepare("SELECT closed_word FROM ".$wpdb->prefix.'symposium_config'));
			?>
			<span class="description">Opacity of topics with [<?php echo $closed_word; ?>] in the subject (between 0.0 and 1.0)</span></td> 
			</tr> 
		
		
			</table> 
			 
			<p class="submit">
			<p>NB. If changes don't follow the above, you may be overriding them with your own stylesheet.</p>
			<input type="submit" name="Submit" class="button-primary" value="Apply Changes" /> 
			</p> 
			</form> 

			<h2>Style Templates</h2>
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_apply" value="Y">
		
			<table class="form-table"> 
		
			<tr valign="top"> 
			<th scope="row"><label for="sid">Select Template</label></th> 
			<td>
			<select name="sid" id="sid"> 
				<?php
				$styles_lib = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_styles');
				if ($styles_lib) {
					foreach ($styles_lib as $style_lib)
					{
						echo "<option value='".$style_lib->sid."'>".$style_lib->title."</option>";
					}
				}
				?>
			</select> 
			<span class="description">Changes are applied immediately when applied</span></td> 
			</tr> 			

			</table> 
						
			<p class="submit"> 
			<input type="submit" name="Submit" class="button-primary" value="Apply Template" /> 
			</p> 
			</form> 
			
			<?php
			
		}
		  	
	}
	
 	echo '</div>';


} 	

function symposium_plugin_options() {

	global $wpdb;
	
  	if (!current_user_can('manage_options'))  {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Options</h2>';
  	
    // See if the user has posted updated category information
    if( isset($_POST[ 'categories_update' ]) && $_POST[ 'categories_update' ] == 'Y' ) {
    	
   		$range = array_keys($_POST['tid']);
		foreach ($range as $key) {
	
		    $tid = $_POST['tid'][$key];
		    $topic_category = $_POST['topic_category'][$key];
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE tid = ".$tid) );					
		}

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Categories Saved</p></div>";

    }

    // See if the user has posted us some information
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'Y' ) {
        // Read their posted value
        $footer = $_POST[ 'email_footer' ];
        $show_categories = $_POST[ 'show_categories' ];
        $send_summary = $_POST[ 'send_summary' ];
        $forum_url = $_POST[ 'forum_url' ];
        $from_email = $_POST[ 'from_email' ];
        $language = $_POST[ 'language' ];
        $include_admin = $_POST[ 'include_admin' ];
        $oldest_first = $_POST[ 'oldest_first' ];
        $preview1 = $_POST[ 'preview1' ];
        $preview2 = $_POST[ 'preview2' ];
        $viewer = $_POST[ 'viewer' ];
        $wp_width = str_replace('%', 'pc', ($_POST[ 'wp_width' ]));
        $closed_word = $_POST[ 'closed_word' ];
        $fontfamily = $_POST[ 'fontfamily' ];
        $fontsize = str_replace("px", "", strtolower($_POST[ 'fontsize' ]));
        $headingsfamily = $_POST[ 'headingsfamily' ];
        $headingssize = str_replace("px", "", strtolower($_POST[ 'headingssize' ]));

        // Save the posted value in the database
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET footer = '".$footer."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET show_categories = '".$show_categories."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET send_summary = '".$send_summary."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_url = '".$forum_url."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET from_email = '".$from_email."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET language = '".$language."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET include_admin = '".$include_admin."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET oldest_first = '".$oldest_first."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET preview1 = ".$preview1) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET preview2 = ".$preview2) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET viewer = '".$viewer."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET wp_width = '".$wp_width."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET closed_word = '".$closed_word."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontfamily = '".$fontfamily."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontsize = '".$fontsize."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingsfamily = '".$headingsfamily."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingssize = '".$headingssize."'") );					

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Options Saved</p></div>";

    }

    // Get values from database  
	$wp_width = str_replace('pc', '%', $wpdb->get_var($wpdb->prepare("SELECT wp_width FROM ".$wpdb->prefix.'symposium_config')));
	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));
	$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config'));
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix.'symposium_config'));
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
	$from_email = $wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->prefix.'symposium_config'));
	$language = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));
	$include_admin = $wpdb->get_var($wpdb->prepare("SELECT include_admin FROM ".$wpdb->prefix.'symposium_config'));
	$oldest_first = $wpdb->get_var($wpdb->prepare("SELECT oldest_first FROM ".$wpdb->prefix.'symposium_config'));
	$preview1 = $wpdb->get_var($wpdb->prepare("SELECT preview1 FROM ".$wpdb->prefix.'symposium_config'));
	$preview2 = $wpdb->get_var($wpdb->prepare("SELECT preview2 FROM ".$wpdb->prefix.'symposium_config'));
	$viewer = $wpdb->get_var($wpdb->prepare("SELECT viewer FROM ".$wpdb->prefix.'symposium_config'));
	$closed_word = $wpdb->get_var($wpdb->prepare("SELECT closed_word FROM ".$wpdb->prefix.'symposium_config'));
	$fontfamily = $wpdb->get_var($wpdb->prepare("SELECT fontfamily FROM ".$wpdb->prefix.'symposium_config'));
	$fontsize = $wpdb->get_var($wpdb->prepare("SELECT fontsize FROM ".$wpdb->prefix.'symposium_config'));
	$headingsfamily = $wpdb->get_var($wpdb->prepare("SELECT headingsfamily FROM ".$wpdb->prefix.'symposium_config'));
	$headingssize = $wpdb->get_var($wpdb->prepare("SELECT headingssize FROM ".$wpdb->prefix.'symposium_config'));

	?> 
	
	<div style='margin-bottom:20px; overflow:auto;'>
	
		<img style='float:left; margin: 10px 30px 10px 10px;' src='<?php echo get_site_url().'/wp-content/plugins/wp-symposium/'; ?>logo.png' />
		
		<p><em>Symposium:</em> sym&middot;po&middot;si&middot;um;
		<ul>
		<li>&middot; A meeting or conference for discussion of a topic</li>
		<li>&middot; A collection of writings on a particular topic</li>
		<li>&middot; A convivial meeting</li>
		</ul>
		</p>
		<p><em>Sym:</em> as in simple</p>
		
		<p style='margin-top:40px'><strong>Thank you for trying Symposium.<br />
		For support, suggestions and feedback please visit <a href='http://www.wpsymposium.com'>www.wpsymposium.com</a></strong></p>
		
	</div>

	<form method="post" action=""> 
	<input type="hidden" name="symposium_update" value="Y">

	<table class="form-table"> 

	<tr valign="top"> 
	<th scope="row"><label for="language">Language</label></th> 
	<td>
	<?php
	// Check that languages file exists
	$xml_dir = WP_PLUGIN_DIR . '/wp-symposium/languages.xml';
	if (!(file_exists($xml_dir))) {
		echo '<div class="error"><p>The language file ('.$xml_dir.') cannot be found.</p></div>';
	}

	// Check that languages have been loaded
	$language_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_lang');
	if ($language_count == 0) {
		echo '<div class="error"><p>The language file has not been loaded, have you changed it? Try downloading the latest version from <a href="http://wordpress.org/extend/plugins/wp-symposium/">the plugin page</a> and replacing your language.xml file. Then <a href="plugins.php">de-activate</a> the core WP-Symposium plugin and re-activate it. If the file has still not loaded, please report this on the <a href="http://www.wpsymposium.com">Support Forum</a>.</p></div>';
		echo 'Language file not loaded.';
	} else {
		echo '<select name="language">';
		$language_options = $wpdb->get_results("SELECT DISTINCT language FROM ".$wpdb->prefix.'symposium_lang');
		if ($language_options) {
			foreach ($language_options as $option)
			{
				echo "<option value='".$option->language."'";
				if ($language == $option->language) { echo ' SELECTED'; }
				echo ">".$option->language."</option>";
			}
		}		
		echo '</select>';
		echo '<span class="description">Go to www.wpsymposium.com to help with other languages, or to make corrections</span></td>';
	}
	?>
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="wp_width">Width</label></th> 
	<td><input name="wp_width" type="text" id="wp_width" value="<?php echo $wp_width; ?>"/> 
	<span class="description">Width of all WP Symposium plugins, eg: 600px or 85%</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="fontfamily">Body Text <img src="../wp-content/plugins/wp-symposium/new.png" alt="New!" /></label></th> 
	<td><input name="fontfamily" type="text" id="fontfamily" value="<?php echo $fontfamily; ?>"/> 
	<span class="description">Font family for body text</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="fontsize"></label></th> 
	<td><input name="fontsize" type="text" id="fontsize" value="<?php echo $fontsize; ?>"/> 
	<span class="description">Font size in pixels for body text</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="headingsfamily">Headings <img src="../wp-content/plugins/wp-symposium/new.png" alt="New!" /></label></th> 
	<td><input name="headingsfamily" type="text" id="headingsfamily" value="<?php echo $headingsfamily; ?>"/> 
	<span class="description">Font family for headings and large text</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="headingssize"></label></th> 
	<td><input name="headingssize" type="text" id="headingssize" value="<?php echo $headingssize; ?>"/> 
	<span class="description">Font size in pixels for headings and large text</span></td> 
	</tr> 

	<tr><td colspan='2'><h2>Forum</h2><td></tr>
	
	<tr valign="top"> 
	<th scope="row"><label for="forum_url">Forum URL</label></th> 
	<td><input name="forum_url" type="text" id="forum_url"  value="<?php echo $forum_url; ?>" class="regular-text" /> 
	<span class="description"><strong>Very Important!</strong> eg: http://www.example.com/forum</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="email_footer">Email Notifications</label></th> 
	<td><input name="email_footer" type="text" id="email_footer"  value="<?php echo $footer; ?>" class="regular-text" /> 
	<span class="description">Footer appended to notification emails</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="from_email">&nbsp;</label></th> 
	<td><input name="from_email" type="text" id="from_email"  value="<?php echo $from_email; ?>" class="regular-text" /> 
	<span class="description">Email address used for email notifications</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="send_summary">Daily Digest</label></th>
	<td>
	<input type="checkbox" name="send_summary" id="send_summary" <?php if ($send_summary == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Enable daily summaries to all members via email</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="show_categories">Categories</label></th>
	<td>
	<input type="checkbox" name="show_categories" id="show_categories" <?php if ($show_categories == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Organise forum topics by categories</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="include_admin">Admin views</label></th>
	<td>
	<input type="checkbox" name="include_admin" id="include_admin" <?php if ($include_admin == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Include administrator viewing a topic in the total view count</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="oldest_first">Order of replies</label></th>
	<td>
	<input type="checkbox" name="oldest_first" id="oldest_first" <?php if ($oldest_first == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Show oldest replies first (uncheck to reverse order)</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="preview1">Preview length</label></th>
	<td><input name="preview1" type="text" id="preview1"  value="<?php echo $preview1; ?>" /> 
	<span class="description">Maximum number of characters to show in topic preview</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="preview2"></label></th>
	<td><input name="preview2" type="text" id="preview2"  value="<?php echo $preview2; ?>" /> 
	<span class="description">Maximum number of characters to show in reply preview</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="viewer">View forum level</label></th> 
	<td>
	<select name="viewer">
		<option value='Guest'<?php if ($viewer == 'Guest') { echo ' SELECTED'; } ?>>Guest</option>
		<option value='Subscriber'<?php if ($viewer == 'Subscriber') { echo ' SELECTED'; } ?>>Subscriber</option>
		<option value='Contributor'<?php if ($viewer == 'Contributor') { echo ' SELECTED'; } ?>>Contributor</option>
		<option value='Editor'<?php if ($viewer == 'Editor') { echo ' SELECTED'; } ?>>Editor</option>
		<option value='Administrator'<?php if ($viewer == 'Administrator') { echo ' SELECTED'; } ?>>Administrator</option>
	</select> 
	<span class="description">The minimum level a visitor has to be to view the forum</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="closed_word">Closed word <img src="../wp-content/plugins/wp-symposium/new.png" alt="New" /></label></th>
	<td><input name="closed_word" type="text" id="closed_word"  value="<?php echo $closed_word; ?>" /> 
	<span class="description">Word used to denote a topic that is closed (see also Styles)</span></td> 
	</tr> 

	</table> 


	<p style='margin-top:20px'>
	<span class="description">
	<strong>Notes</strong>
	<ul>
	<li>&middot;&nbsp;Daily summaries (if there is anything to send) are sent when the first visitor comes to the site after midnight, local time, for the previous day.</li>
	<li>&middot;&nbsp;Be aware of any limits set by your hosting provider for sending out bulk emails, they may suspend your website.</li>
	</ul>
	</p>	
	 
	<p class="submit"> 
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" /> 
	</p> 
	</form> 
	
	<?php
	
	if ($show_categories == "on") {
		$topics = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_category=0 AND topic_parent=0");

		if ($topics) {
			echo "<p>The following topics are un-categorised, if you want them to appear in a category, please select below.</p>";
			echo '<form method="post" action="">';
			echo '<input type="hidden" name="categories_update" value="Y">';
		
			echo '<table class="form-table">';

			foreach ($topics as $topic) {
				echo '<tr valign="top">';
				echo '<th scope="row"><label for="topic_category">'.$topic->topic_subject.'</label></th>';
				echo '<td>';
				echo '<input type="hidden" name="tid[]" value='.$topic->tid.' />';
				echo '<select name="topic_category[]">';
				$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats');
				if ($categories) {
					foreach ($categories as $category) {
						echo '<option value='.$category->cid.'>'.$category->title.'</option>';
					}
				}				
				echo '</select>';
				echo '</td>';
				echo '</tr>';
			}

			echo '</table>';

			echo '<p class="submit">';
			echo '<input type="submit" name="Submit" class="button-primary" value="Update Categories" />';
			echo '</p>';
			echo '</form>';

		}
	}
  
  	echo '</div>';

} 	

/* ====================================================== AJAX FUNCTIONS ====================================================== */

function symposium_test_head() {
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
	   	// Test AJAX
	   	jQuery("#testAJAX").click(function() {
	   		random = Math.floor(Math.random()*10)+1;
	   		alert("The random number being sent is "+random);
			jQuery.post('/wp-admin/admin-ajax.php', { action:'symposium_test', postID:random }, function(str_test) { 
				jQuery("#testAJAX_results").val('Value of '+str_test+' returned.');
			} );
   		});

	    // Check if really want to delete	    
		jQuery(".delete").click(function(){
		  var answer = confirm("Are you sure?");
		  return answer // answer is a boolean
		});

	});
   	</script>
<?php
}
add_action('admin_head', 'symposium_test_head');

// AJAX test function
function symposium_test(){
	$value = $_POST['postID'];	
	echo $value*100;
	exit;
}
add_action('wp_ajax_symposium_test', 'symposium_test');


?>