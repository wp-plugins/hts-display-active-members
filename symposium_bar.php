<?php
/*
Plugin Name: WP Symposium Notification Bar
Plugin URI: http://www.wpsymposium.com
Description: Bar along bottom of screen to display notifications on new messages, mail. Also controls live chat windows. Simply activate to add.
Version: 0.1.17
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
function add_notification_bar()  
{  
	if (!is_admin()) {
		
	   	global $wpdb, $current_user;
		wp_get_current_user();

		$plugin = WP_PLUGIN_URL.'/wp-symposium';
		$allow_personal_settings = $wpdb->get_var($wpdb->prepare("SELECT allow_personal_settings FROM ".$wpdb->prefix.'symposium_config'));
		if ($allow_personal_settings != "on") {
			$sound = $wpdb->get_var($wpdb->prepare("SELECT sound FROM ".$wpdb->prefix . 'symposium_config'));
			$soundchat = $sound;
			$bar_position = $wpdb->get_var($wpdb->prepare("SELECT bar_position FROM ".$wpdb->prefix . 'symposium_config'));
		} else {
			$sound = get_symposium_meta($current_user->ID, 'sound');
			$soundchat = get_symposium_meta($current_user->ID, 'soundchat');
			$bar_position = get_symposium_meta($current_user->ID, 'bar_position');			
		}
		$border_radius = $wpdb->get_var($wpdb->prepare("SELECT border_radius FROM ".$wpdb->prefix . 'symposium_config'));
		$use_chat = $wpdb->get_var($wpdb->prepare("SELECT use_chat FROM ".$wpdb->prefix.'symposium_config'));
		$bar_polling = ( $wpdb->get_var($wpdb->prepare("SELECT bar_polling FROM ".$wpdb->prefix.'symposium_config')) ) * 1000;
		$chat_polling = ( $wpdb->get_var($wpdb->prepare("SELECT chat_polling FROM ".$wpdb->prefix.'symposium_config')) ) * 1000;

		// maximum number of chat windows
		$maxChatWindows = 3;

		?>
	
		<script type="text/javascript" src="<?php echo $plugin; ?>/soundmanager/soundmanager2-jsmin.js"></script>
		<script type="text/javascript">
		
			soundManager.url = '<?php echo $plugin; ?>/soundmanager/soundmanager2.swf'; // override default SWF url
			soundManager.debugMode = false;
			soundManager.consoleOnly = false;
					
		</script>	
	
		<style>
			#symposium-notification-bar {
				position:fixed;
				left:0px;
				<?php echo $bar_position; ?>:0px;
				padding: 4px;
				width: 100%;
				height: 20px;
				font-size: 14px;
				font-family: arial,helvetica;
				background-color: #000;
				color: #fff;
				border-radius: 0px;
				-moz-border-radius: 0px;
				-ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=75)";
				filter: alpha(opacity=75);
				-moz-opacity: 0.75;
				-khtml-opacity: 0.75;
			 	opacity: 0.75;
			}
			
			#symposium-notification-bar #icons img {
				margin-right: 8px;		
				float: left;		
			}
			
			#symposium-notification-bar #alerts, #symposium-notification-bar #info {
				float: right;
				margin-right: 8px;
			}
			#symposium-notification-bar #alerts {
				display: none;
			}
			
			#symposium-notification-bar #alerts a, #symposium-notification-bar #info a {
				color: #fff;
				text-decoration:none;
			}
			#symposium-notification-bar #alerts a:hover, #symposium-notification-bar #info a:hover {
				color: #f00;
			}
			
			#symposium-chatboxes {
				position:fixed;
				right:0px;
				<?php echo $bar_position; ?>:28px;
			}

			<?php
			for ($w = 1; $w <= $maxChatWindows; $w++) {

				echo "#symposium-chatboxes #chat".$w." {";
					echo "float: right;";
					echo "margin-left: 2px;";
					echo "border-radius: 0px;";
					echo "-moz-border-radius: 0px;";
					echo '-ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=90)";';
					echo "filter: alpha(opacity=90);";
					echo "-moz-opacity: 0.90;";
					echo "-khtml-opacity: 0.90;";
				 	echo "opacity: 0.90;";
					echo "width:180px;";
					echo "height:240px;";
					echo "padding:0px;";
					echo "border:1px solid #000;";
					echo "background-color: #fff;";
				echo "}";
	
			}
			?>

			
			#symposium-chatboxes #symposium-who-online {
				float: right;
				margin-left: 2px;
				border-radius: 0px;
				-moz-border-radius: 0px;
				-ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity=90)";
				filter: alpha(opacity=90);
				-moz-opacity: 0.90;
				-khtml-opacity: 0.90;
			 	opacity: 0.90;
				width:180px;
				height:240px;
				padding:0px;
				border:1px solid #000;
				background-color: #fff;
			}
			#symposium-chatboxes *, #symposium-chatboxes * {
				font-family: tahoma,arial,helvetica;
				font-size: 12px;				
			}
			#symposium-chatboxes .display_name_link {
				text-decoration: none;
				color: #fff;
			}

			#symposium-who-online {
				display: none;
				padding: 2px;
			}

			.symposium_online_name {
				cursor:pointer;
			}
			.symposium_online_name:hover {
				text-decoration:underline;
			}
			.symposium_online_name {
			}
			.symposium_offline_name {
				text-decoration: none;
			}
			.symposium_offline_name:hover {
				text-decoration: underline;
			}
										
			.symposium-online-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				width: 18px;
				height:18px;
				background-color: #0f0;
				color: #000;
				text-align:center;
				float: right;
				margin-right:10px;
				cursor: pointer;
				font-family: tahoma,arial,helvetica;
				font-size: 10px;				
				font-weight: bold;
			}
			.symposium-online-box-none {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				text-align:center;
				background-color: #000;
				color: #fff;
				border: 1px solid #fff;
				float: right;
				margin-right:10px;
				width: 17px;
				height: 17px;
				padding:0px;
				cursor: pointer;
				font-family: tahoma,arial,helvetica;
				font-size: 10px;
				font-weight: normal;
			}
			
			.symposium-email-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				float:right;
				color: #000;
				text-align:center;
				padding:0px;
				cursor: pointer;
				font-family: tahoma,arial,helvetica;
				font-size: 10px;
				font-weight: bold;
				margin-right:10px;
				width: 18px;
				height: 18px;
			}
			.symposium-email-box-read {
				background-image:url('<?php echo $plugin; ?>/images/email.gif');
			}
			.symposium-email-box-unread {
				background-image:url('<?php echo $plugin; ?>/images/emailunread.gif');
			}

			.symposium-friends-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				float:right;
				color: #000;
				text-align:center;
				padding:0px;
				cursor: pointer;
				font-family: tahoma,arial,helvetica;
				font-size: 10px;
				font-weight: bold;
				margin-right:10px;
				width: 18px;
				height: 18px;
			}
			.symposium-friends-box-none {
				background-image:url('<?php echo $plugin; ?>/images/friends.gif');
			}
			.symposium-friends-box-new {
				background-image:url('<?php echo $plugin; ?>/images/friendsnew.gif');
			}
			

		</style>
		<?php
		$my_style='<span style="font-weight:bold;">';
		$other_style='<span style="">';
		?>
		
		<!-- NOTIFICATION BAR -->
		<div id="symposium-notification-bar">
			<div id="icons" style="float: left">
				<?php
				$bar_label = $wpdb->get_var($wpdb->prepare("SELECT bar_label FROM ".$wpdb->prefix . 'symposium_config'));
		        $bar_label = str_replace('[logo]', '<img src="/wp-content/plugins/wp-symposium/images/icon_logo.gif" alt="WP Symposium" />', $bar_label);
		        echo $bar_label;
				?>
			</div>

			<?php if (is_user_logged_in()) {
				$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends f WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on'");
				if ($pending_friends > 0) { 
					echo "<div id='symposium-friends-box' title='Go to Friends' alt='Go to Friends' class='symposium-friends-box symposium-friends-box-new'>";
					echo $pending_friends; 
				} else {
					echo "<div id='symposium-friends-box' title='Go to Friends' alt='Go to Friends' class='symposium-friends-box symposium-friends-box-none'>";
				}
				echo "</div>";
				
			   	$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'";
				$unread_in = $wpdb->get_var($sql);
				if ($unread_in > 0) { 
					echo "<div id='symposium-email-box' title='Go to Mail' alt='Go to Mail' class='symposium-email-box symposium-email-box-unread'>";
					echo $unread_in; 
				} else {
					echo "<div id='symposium-email-box' title='Go to Mail' alt='Go to Mail' class='symposium-email-box symposium-email-box-read'>";
				}
				echo "</div>";

				echo "<div id='symposium-online-box' class='symposium-online-box'></div>";
			} ?>

			<div id="alerts">
			</div>
			<div id="info">
				<?php
				if (is_user_logged_in()) {
					echo 'Logged in as <a href="/wp-admin/profile.php">'.$current_user->user_login.'</a>.&nbsp;';
					wp_loginout( '/index.php' );
					echo '.&nbsp;';
					
					if (function_exists('symposium_mail')) {
						$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
						if ($unread_in > 0) { echo "<a href='".symposium_get_url("mail")."'>";
							echo $unread_in." unread message";
							if ($unread_in != 1) { echo "s"; }
							echo "</a>.";
						 }
						 
					}
						
				} else {
					echo "<a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>Login</a>";
				}

				?>	
			</div>
			
		</div>
		
		<?php if (is_user_logged_in()) {

			echo "<div id='symposium-chatboxes'>";
	
				// Who's online		
				echo "<div id='symposium-who-online'>";
					echo "<div id='symposium-who-online_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
						echo "<div id='symposium-who-online_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='Close' /></div>";
						echo "Friends Status";
					echo "</div>";
	
					$sql = "SELECT f.*, m.last_activity, u.display_name, u.ID FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to LEFT JOIN ".$wpdb->prefix."users u ON u.ID = f.friend_to WHERE f.friend_accepted = 'on' AND f.friend_from = ".$current_user->ID." ORDER BY last_activity DESC";
					$friends = $wpdb->get_results($sql);
					
					$inactivity = $wpdb->get_row($wpdb->prepare("SELECT online, offline FROM ".$wpdb->prefix . 'symposium_config'));
					$inactive = $inactivity->online;
					$offline = $inactivity->offline;
					$friends_online = 0;
					
					foreach ($friends as $friend) {
						
						$time_now = time();
						$last_active_minutes = strtotime($friend->last_activity);
						$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
														
						echo "<div style='clear:both; margin-top:4px; overflow: auto;'>";		
							echo "<div style='float: left; width:15px; padding-left:4px;'>";
								if ($last_active_minutes >= $offline) {
									echo "<img src='".$plugin."/images/loggedout.gif"."' alt='Logged Out'>";
								} else {
									$friends_online++;
									if ($last_active_minutes >= $inactive) {
										echo "<img src='".$plugin."/images/inactive.gif"."' alt='Inactive'>";
									} else {
										echo "<img src='".$plugin."/images/online.gif"."' alt='Online'>";
									}
								}
							echo "</div>";
							echo "<div style='float: left; width:20px;'>";
								if (function_exists('symposium_profile')) {	
									echo "<a href='".symposium_get_url('profile')."?uid=".$friend->ID."'>";
									echo get_avatar($friend->friend_to, 16);
									echo "</a>";
								} else {
									echo get_avatar($friend->friend_to, 16);
								}
							echo "</div>";
							echo "<div>";
								if ( $use_chat != 'on' ) {
									if (function_exists('symposium_profile')) {	
										echo "<a class='symposium_offline_name' href='".symposium_get_url('profile')."?uid=".$friend->ID."'>";
										echo "<span title='".$friend->friend_to."'>".$friend->display_name."</span>";
										echo "</a>";
									}
								} else {
									echo "<span class='symposium_online_name' title='".$friend->friend_to."'>".$friend->display_name."</span>";
								}
							echo "</div>";
						echo "</div>";
					}
													
				echo "</div>";
				
				// set up chat windows
				if ($use_chat == 'on') {
					for ($w = 1; $w <= $maxChatWindows; $w++) {
						addChatWindow($w);
					}
				}
				
			echo "</div>";
			?>

			<script type="text/javascript">
			
			    jQuery(document).ready(function() {
			    	
					// Centre in screen
					jQuery.fn.inmiddle = function () {
				    	this.css("position","absolute");
				    	this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
				    	this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
					    return this;
					}
					
					// Notices	    	
					jQuery(".chatpleasewait").hide();
					
					// Show friends online, etc?
			    	if (<?php echo $current_user->ID; ?> == 0) {
						jQuery('#symposium-online-box').hide("fast");
						jQuery('#symposium-email-box').hide("fast");
			    	} else {
						jQuery("#symposium-online-box").html('<?php echo $friends_online; ?>');
						if (<?php echo $friends_online; ?> == 0) {
							jQuery("#symposium-online-box").removeClass("symposium-online-box");
							jQuery("#symposium-online-box").addClass("symposium-online-box-none");
						}
			    	}
	
			    	// Who's online/mail/buttons
			    	jQuery("#symposium-email-box").click(function() {
						window.location.href ='<?php echo symposium_get_url("mail"); ?>';
			    	});
			    	jQuery("#symposium-friends-box").click(function() {
						window.location.href ='<?php echo symposium_get_url("profile"); ?>?view=friends';
			    	});
			    	jQuery("#symposium-online-box").click(function() {
						jQuery('#symposium-who-online').toggle("fast");
			    	});
			    	jQuery("#symposium-who-online_close").click(function() {
						jQuery('#symposium-who-online').hide("fast");
			    	});
	
			    	// Check for notifications 	
					var refreshId = setInterval(function()
				   	{
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"checkForNotifications", 
							tray:"in",
							'mid':1
							},
						function(str)
						{
							if (str != '' && str != '-1') {
								jQuery('#info').hide().delay(<?php echo floor($bar_polling*0.75); ?>).fadeIn('slow'); // 11 seconds
					    		jQuery('#alerts').html(str);
					    		if ('<?php echo $sound; ?>' != 'None') {
									soundManager.play('Alert','<?php echo $plugin; ?>/soundmanager/<?php echo $sound; ?>')
					    		}
								jQuery('#alerts').fadeIn('fast').delay(<?php echo floor($bar_polling*0.5); ?>).fadeOut('slow');
							}
						});
						
				   	}, <?php echo $bar_polling; ?>); // Delay to check for new mail, etc
			    	
					<?php if ($use_chat == 'on') { ?>

				   		var numChatWindows = <?php echo $maxChatWindows; ?>;
	
				    	// Click on a name to chat
				    	jQuery(".symposium_online_name").click(function() {
				    		// choose a chat box
				    		var chatbox = 0;
				    		var already_chatting = 0;
				    		// first check to see if already chatting to them
							for (w=1;w<=numChatWindows;w++) {	
					    		if ( (already_chatting == 0) && (jQuery('#chat'+w+'_to').html() == jQuery(this).attr("title")) ) { already_chatting = w; }
							}
				    		if (already_chatting == 0) {
					    		chatbox = 0;
					    		// find a free chat window
								for (w=1;w<=numChatWindows;w++) {	
						    		if (jQuery('#chat'+w).css("display") == "none") { chatbox = w; }
								}
					    		if (chatbox > 0) {
					    			// found one
									jQuery(".chatpleasewait").inmiddle().show();
									jQuery('#chat'+chatbox+'_to').html(jQuery(this).attr("title"));
									jQuery("#chat"+chatbox+"_message").html('');
									jQuery('#chat'+chatbox).show("fast");
									jQuery.post("/wp-admin/admin-ajax.php", {
										action:"symposium_openchat", 
										chat_from:<?php echo $current_user->ID; ?>,
										chat_to:jQuery(this).attr("title")
										},
									function(str)
									{
										if (str.substring(0, 2) == 'OK') { 
											jQuery('#chat'+chatbox).show("fast");
											var details = str.split("[split]");
											jQuery('#chat'+chatbox+'_to').html(details[1]);
											jQuery('#chat'+chatbox+'_display_name').html(details[2]);
											jQuery('#chat'+chatbox+'_message').html('');
										} else {
											if (jQuery('#chat'+chatbox+'_to').html() == str) { jQuery('#chat'+chatbox).show("fast"); }
										}
									});
									jQuery(".chatpleasewait").hide();
					    		} else {
					    			// no free chat windows
					    			alert("Sorry - you can't open any more chat windows.");
					    		}
				    		} else {
				    			// clear closed tag by opening it
								jQuery.post("/wp-admin/admin-ajax.php", {
									action:"symposium_reopenchat", 
									chat_from:<?php echo $current_user->ID; ?>,
									chat_to:jQuery(this).attr("title")
									},
								function(str)
								{
									for (w=1;w<=numChatWindows;w++) {	
										if (jQuery('#chat'+w+'_to').html() == str) { jQuery('#chat'+w).show("fast"); }
									}
								});
					    		if (already_chatting > 0) { jQuery('#chat'+already_chatting).show("fast"); }
				    		}
				    	});
				    	
					  	// Monitor Chat Boxes +++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					  	<?php
						for ($w = 1; $w <= $maxChatWindows; $w++) {				    	

							echo "if (jQuery('#chat".$w."').css('display') != 'none') {";
								echo "jQuery('#chat".$w."_message').attr({ scrollTop: jQuery('#chat".$w."_message').attr('scrollHeight') });";
							echo "};";
					    	echo "jQuery('#chat".$w."_close').click(function() {";
								echo "jQuery('#chat".$w."').hide('fast');";
								echo "jQuery.post('/wp-admin/admin-ajax.php', {";
									echo "action:'symposium_closechat',"; 
									echo "chat_from:".$current_user->ID.",";
									echo "chat_to:jQuery('#chat".$w."_to').html()";
									echo "},";
								echo "function(str)";
								echo "{";
									echo "if (str != '') { alert(str) };";
								echo "});";
					    	echo "});";
							// Track message box typing
							echo "jQuery('#chat".$w."_textarea').keypress(function(event) {";
								echo "if (event.which == 13) {";
									echo "var msg = jQuery('#chat".$w."_textarea').val();";
									echo "jQuery.trim(msg);";
									echo "jQuery('#chat".$w."_textarea').val('');";
									echo "event.preventDefault();";
									echo "jQuery.post('/wp-admin/admin-ajax.php', {";
										echo "action:'symposium_addchat',";
										echo "chat_from:".$current_user->ID.",";
										echo "chat_to:jQuery('#chat".$w."_to').html(),";
										echo "chat_message:msg";
										echo "},";
									echo "function(str)";
									echo "{";
										echo "jQuery('#chat".$w."_message').append('".$my_style."'+str+'</span><br />');";
										echo "jQuery('#chat".$w."_message').attr({ scrollTop: jQuery('#chat".$w."_message').attr('scrollHeight') });";
									echo "});";
								echo "}";
							echo "});";
							
						} ?>
						
				    	// Check for chat/unread mail/etc ******************************************************
						var refreshChatId = setInterval(function()
					   	{

					   		// Friends ******************************************************
							jQuery.post("/wp-admin/admin-ajax.php", {
								action:"symposium_friendrequests", 
								me:<?php echo $current_user->ID; ?>
								},
							function(str)
							{
								if (str > 0) {
									jQuery("#symposium-friends-box").html(str);
									jQuery("#symposium-friends-box").removeClass("symposium-friends-box-none");
									jQuery("#symposium-friends-box").addClass("symposium-friends-box-new");
								}
							});	
												   		
					   		// Email ******************************************************
							jQuery.post("/wp-admin/admin-ajax.php", {
								action:"symposium_getunreadmail", 
								me:<?php echo $current_user->ID; ?>
								},
							function(str)
							{
								if (str > 0) {
									jQuery("#symposium-email-box").html(str);
									jQuery("#symposium-email-box").removeClass("symposium-email-box-read");
									jQuery("#symposium-email-box").addClass("symposium-email-box-unread");
								}
							});		

					   		// Chat ******************************************************
					   		var numChatWindows = <?php echo $maxChatWindows; ?>;
	
							jQuery.post("/wp-admin/admin-ajax.php", {
								action:"symposium_getchat", 
								me:<?php echo $current_user->ID; ?>
								},
							function(str)
							{

								if (str != '[from]') {
									
									var from=str.split("[from]");
									var last_post=from[0];
									var rows=from[1].split("[split]");
									var num_rows = rows.length-1;
									var play_sound = false;
									
									// clear chat windows	
									for (w=1;w<=numChatWindows;w++) {	
										clearChatWindow(w);
									}
									
									var allocated_windows = 0;
									// loop through messages, setting up all the chat windows for each person, closed or not
									for (i=0;i<num_rows;i++) {	
										var details=rows[i].split("[|]");
										var from = details[0];
										var to = details[1];
										var msg = details[2];
										var name = details[3];
										var other = 0;
										
										if (from == <?php echo $current_user->ID; ?>) {
											other = to; 
										} else {
											other = from;
										}
										
										// see if a window has been allocated
										var chat_win = 0;
										for (w=1;w<=numChatWindows;w++) {	
											if (jQuery('#chat'+w+'_to').html() == other) { chat_win = w; }
										}
										
										if (chat_win == 0) {
											var allocated = false;
											for (w=1;w<=numChatWindows;w++) {	
												if ( (jQuery('#chat'+w+'_to').html() == '') && (allocated == false) ) { 
													jQuery('#chat'+w+'_to').html(other); 
													jQuery('#chat'+w+'_display_name').html(name); 
 													allocated_windows++; 
 													allocated = true;
 												}
											}
										}
									}		
									
									// Loop through the messages, adding the message to the correct chat window
									for (i=0;i<num_rows;i++) {	
										var details=rows[i].split("[|]");
										var from = details[0];
										var to = details[1];
										var msg = details[2];

										if (from == <?php echo $current_user->ID; ?>) {
											other = to; 
										} else {
											other = from;
										}
										
										// Find the window to add the message to
										var chat_win = 0;
										for (w=1;w<=numChatWindows;w++) {	
											if (jQuery('#chat'+w+'_to').html() == other) { chat_win = w; }
										}
										if (chat_win > 0) {											
											for (w=1;w<=numChatWindows;w++) {	
												if (chat_win == w) { 
													if (msg.indexOf('[start]') < 0) { 
														if (jQuery('#chat'+w+'_to').html() == <?php echo $current_user->ID; ?>) {
															jQuery('#chat'+w+'_message').append('<?php echo $my_style; ?>'+msg+'</span><br />');
														} else {
															jQuery('#chat'+w+'_message').append('<?php echo $other_style; ?>'+msg+'</span><br />');
														}
													}
												}
											}
										} else {
											// alert ('Failed to find window, should have been set up - probably closed by user');
										}
																				
									}
									
									for (w=1;w<=numChatWindows;w++) {
										if (jQuery('#chat".$w."').css('display') != 'none') {
											var message = jQuery("#chat"+w+"_message").html();
											if (message.indexOf('[closed-<?php echo $current_user->ID; ?>') >= 0) { jQuery('#chat'+w+'_to').html(''); }
										}
									}
									
									// Show/hide all the chat windows
									for (w=1;w<=numChatWindows;w++) {	
										if (jQuery('#chat'+w+'_to').html() != '') {
											var message = jQuery("#chat"+w+"_message").html();
											var chat_to = jQuery('#chat'+w+'_to').html();
											var new_message = message.replace("[closed-"+chat_to+"]<br>", "");
											jQuery("#chat"+w+"_message").html(new_message);
											jQuery('#chat'+w).show("fast");
											jQuery("#chat"+w+"_message").attr({ scrollTop: jQuery("#chat"+w+"_message").attr("scrollHeight") });
										} else {
											jQuery('#chat'+w).hide();
										}
									}
									
									// Finished all messages, play sound?
									if (play_sound == true) {
										soundManager.play('ChatAlert','<?php echo $plugin; ?>/soundmanager/<?php echo $soundchat; ?>');
						    		}
									
								} else {								
									// No chat occuring, close all windows
									for (w=1;w<=numChatWindows;w++) {	
										jQuery('#chat'+w).hide();
									}
								}
							
							});
							
					   	}, <?php echo $chat_polling; ?>); // Delay to check for new messages
				   	
					<?php } ?>
				   					   
			    });		

				function removeHTMLTags(strInputCode){
			 	 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
			 		 	return (p1 == "lt")? "<" : ">";
			 		});
			 		var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
			 		return strTagStrippedText;	
				}
				
				function clearChatWindow(w) {
					jQuery('#chat'+w+'_to').html('');
					jQuery('#chat'+w+'_display_name').html('');
					jQuery('#chat'+w+'_message').html('');
				}
			        
		    </script> 
		<?php
		}
	}

	// Notices
	echo "<div class='chatpleasewait' style='z-index:999999;'><img src='".$plugin."/busy.gif' /></div>";

}  
add_action('wp_footer', 'add_notification_bar', 1);

/* ====================================================== PHP FUNCTIONS ====================================================== */

function addChatWindow($id) {
	
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	echo "<div id='chat".$id."' style='display:none'>";
		echo "<div id='chat".$id."_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
		echo "<div id='chat".$id."_to' style='display:none'></div>";
		echo "<div id='chat".$id."_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='Close' /></div>";
		echo "<div id='chat".$id."_display_name'></div>";
		echo "</div>";
		echo "<div id='chat".$id."_message' style='width:176px; height:170px;overflow:auto;padding:2px;padding-bottom:7px;'>";
		echo "</div>";
		echo "<div style='width:180px; height:40px;'>";
			echo "<textarea id='chat".$id."_textarea' onclick='if (this.value == \"Type here...\") { this.value=\"\"; }' style='background-color:#efefef;border:0px;width:176px;height:34px;'>Type here...</textarea>";
		echo "</div>";
	echo "</div>";
	
}

/* ====================================================== AJAX FUNCTIONS ====================================================== */

// Get friend requests
function symposium_friendrequests() {

   	global $wpdb;	
   	$me = $_POST['me'];
	$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends f WHERE f.friend_to = ".$me." AND f.friend_accepted != 'on'";
	$pending = $wpdb->get_var($sql);
	
	echo $pending;
	exit;

}
add_action('wp_ajax_symposium_friendrequests', 'symposium_friendrequests');

// Get count of unread mail
function symposium_getunreadmail() {

   	global $wpdb;	
   	$me = $_POST['me'];
   	$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$me." AND mail_in_deleted != 'on' AND mail_read != 'on'";
	$unread_in = $wpdb->get_var($sql);
	
	echo $unread_in;
	exit;
}
add_action('wp_ajax_symposium_getunreadmail', 'symposium_getunreadmail');

// Get chat for updates
function symposium_getchat() {

   	global $wpdb;
	
   	$me = $_POST['me'];
   	$last_post = '';
   	
   	$results = '';
   	$sql = "SELECT c.*, u1.display_name AS fromname, u2.display_name AS toname FROM ".$wpdb->prefix."symposium_chat c LEFT JOIN ".$wpdb->prefix."users u1 ON c.chat_from = u1.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.chat_to = u2.ID WHERE (chat_from = ".$me." OR chat_to = ".$me.") ORDER BY chid";
	$chats = $wpdb->get_results($sql);
	if ($chats) {
		foreach ($chats as $chat) {
			$results .= $chat->chat_from.'[|]'.$chat->chat_to.'[|]'.stripslashes($chat->chat_message).'[|]';
			if ($chat->chat_from == $me) {
				$results .= $chat->toname."[split]";
				$last_post = "me";
			} else {
				$results .= $chat->fromname."[split]";
				$last_post = "notme";
			}
		}
	}
	
   	echo $last_post."[from]".$results;
   	exit;
	
}
add_action('wp_ajax_symposium_getchat', 'symposium_getchat');


// Add to chat
function symposium_addchat() {

   	global $wpdb;
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];
   	$chat_message = $_POST['chat_message'];
   	$r = '';
   	
   	$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_message = '[closed-".$chat_to."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
	if ($rows_affected === false) {
		$r .= $wpdb->last_query;
	}

	if ( $rows_affected = $wpdb->insert( $wpdb->prefix . "symposium_chat", array( 
		'chat_to' => $chat_to, 
		'chat_from' => $chat_from, 
		'chat_message' => $chat_message
	) ) ) {
		$r.= stripslashes($chat_message);
	} else {
		$r .= $wpdb->last_query;
	}
   	
   	echo $r;
   	exit;

}
add_action('wp_ajax_symposium_addchat', 'symposium_addchat');

// Re-open chat
function symposium_reopenchat() {

   	global $wpdb;
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];

	// clear the closed flag
   	$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_message = '[closed-".$chat_from."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
	$wpdb->query( $wpdb->prepare($sql) );

	return $chat_to;
}

// Open chat
function symposium_openchat() {
	
   	global $wpdb;
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];
   	$r = '';
   	
	// check to see if they are already chatting
	if ($wpdb->query( $wpdb->prepare("SELECT chid FROM ".$wpdb->prefix."symposium_chat WHERE (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")"))) {

		// clear the closed flag
	   	$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_message = '[closed-".$chat_from."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
		$wpdb->query( $wpdb->prepare($sql) );
		$r .= $chat_to;

	} else {

		if ( $rows_affected = $wpdb->insert( $wpdb->prefix . "symposium_chat", array( 
			'chat_to' => $chat_to, 
			'chat_from' => $chat_from, 
			'chat_message' => '[start]'
		) ) ) {
			
			$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$chat_to));
			$r .= "OK[split]".$chat_to."[split]".$display_name;
			
		}
	}
	
	echo $r;
	exit;

}
add_action('wp_ajax_symposium_openchat', 'symposium_openchat');

// Close chat
function symposium_closechat() {
	
   	global $wpdb;

	$chat_from = $_POST['chat_from'];
	$chat_to = $_POST['chat_to'];
	$r = '';

	// one window already closed?
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_chat WHERE ((chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")) AND INSTR(chat_message, '[closed')"));
	
	if ($count > 1) {

		$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")";
		if ($wpdb->query( $wpdb->prepare($sql) ) ) {
			$r .= '';
		} else {
			$r .= $wpdb->last_query;
		}
		
	} else {
	
		if ( $rows_affected = $wpdb->insert( $wpdb->prefix . "symposium_chat", array( 
			'chat_to' => $chat_to, 
			'chat_from' => $chat_from, 
			'chat_message' => '[closed-'.$chat_from.']'
		) ) ) {
			$r .= '';
		} else {
			$r.= $wpdb->last_query;
		}
		
	}

	echo $r;
	exit;

}
add_action('wp_ajax_symposium_closechat', 'symposium_closechat');

// Check for new mail, forum messages, etc
function checkForNotifications() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	$return = '';
	
	$sql = "SELECT nid, notification_message FROM ".$wpdb->prefix."symposium_notifications WHERE notification_to = ".$current_user->ID." AND notification_shown != 'on'";
	$msgs = $wpdb->get_row($wpdb->prepare($sql));
	
	if ($msgs) {
		$return = $msgs->notification_message;
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_notifications SET notification_shown = 'on' WHERE nid = ".$msgs->nid) );
	} else {
		$return = '';
	}
	
	echo $return;
	exit;
}
add_action('wp_ajax_checkForNotifications', 'checkForNotifications');


/* ====================================================== ADMIN/ACTIVATE/DEACTIVATE ====================================================== */

function symposium_bar_activate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>5, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Notification bar activated.'));
	} else {
	    wp_die( __('Core plugin must be actived first.') );
	}

}

function symposium_bar_deactivate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>6, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Notification bar de-activated.'));
	}

}

register_activation_hook(__FILE__,'symposium_bar_activate');
register_deactivation_hook(__FILE__, 'symposium_bar_deactivate');



?>
