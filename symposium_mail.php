<?php
/*
Plugin Name: WP Symposium Mail
Plugin URI: http://www.wpsymposium.com
Description: RELEASED FOR TESTING ONLY! Mail component for the Symposium suite of plug-ins. Put [symposium-mail] on any WordPress page.
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

function symposium_mail() {	
	

	global $wpdb, $current_user;
	wp_get_current_user();

	$thispage = get_permalink();
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));

	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$thispage = $mail_url;
		$q = "&";
	} else {
		$q = "?";
	}
	
	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	
	$html = "";

	if (is_user_logged_in()) {

		// Count unread mail in inbox to help decide which is default tab
		$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
	
		// View (and set tabs)
		$inbox_active = 'inactive';
		$sent_active = 'inactive';
		$compose_active = 'active';
		$view = "compose";
		if ($_GET['view'] == 'sent') {
			$inbox_active = 'inactive';
			$sent_active = 'active';
			$compose_active = 'inactive';
			$view = "sent";
		} 
		if ( !isset($_GET['view'])  || ($_GET['view'] == "in") ) {
			$inbox_active = 'active';
			$sent_active = 'inactive';
			$compose_active = 'inactive';
			$view = "in";
		} 
		if (isset($_POST['reply_recipient'])) {
			$inbox_active = 'inactive';
			$sent_active = 'inactive';
			$compose_active = 'active';
			$view = "compose";
		} 
		
		// Javascript and jQuery
		$js_script .= '<script type="text/javascript">
	
		    jQuery(document).ready(function() { 	

				// Centre in screen
				jQuery.fn.inmiddle = function () {
			    	this.css("position","absolute");
			    	this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
			    	this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
				    return this;
				}
		    		
		    	// Notices	    	
				jQuery(".notice").hide();
				jQuery(".pleasewait").hide();
		
				// Change between boxes
		    	jQuery(".mail_tab").click(function() {
					jQuery(".pleasewait").inmiddle().show();
		    	});		
		
				// React to click on message list
		    	jQuery(".mail_item").click(function() {
					jQuery("#in_message").html(\'<img src="'.$plugin_dir."busy.gif".'" />\');
					jQuery("#sent_message").html(\'<img src="'.$plugin_dir."busy.gif".'" />\');
					var mail_mid = jQuery(this).attr("id");
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"getMailMessage", 
						tray:"'.$view.'",
						\'mid\':mail_mid
						},
					function(str)
					{
						var details = str.split("[split]");
						if (details[1] > 0) {
							jQuery("#'.$view.'count").html(\' (\'+details[1]+\')\');
						}
						if (details[2] == "in") {
							jQuery("#"+details[0]).removeClass("row_odd");
						}
						jQuery("#'.$view.'_message").html(details[3]);
					});
		    	});		
					
				// Autocomplete values for recipient list
				jQuery("input#compose_recipient").autocomplete({
				    source: [';
				    
					$list = $wpdb->get_results("SELECT display_name FROM ".$wpdb->prefix."users ORDER BY display_name");
					if ($list) {
						foreach ($list as $item) {
							$js_script .= '"'.$item->display_name.'",';
						}
					}				    
					$js_script .= 
				    ']
				});
		
		    });
	 
		</script>
		';
		
		$html .= $js_script;
		
		// Include styles	
		include_once('symposium_styles.php');

		// Send a dummy email
		if ($_GET['dummy'] != '') {
			$rows_affected = $wpdb->insert( $wpdb->prefix . "symposium_mail", array( 
			'mail_from' => $current_user->ID, 
			'mail_to' => $current_user->ID, 
			'mail_subject' => 'Welcome to WP Symposium Mail.',
			'mail_message' => 'This is an example message, from me to myself. It is a dummy message used to test the mail system.'
			 ) );
			 $html .= "Dummy email sent to yourself.";
		}
			
		// Work out user level
		$user_level = 0; // Guest
		if (is_user_logged_in()) { $user_level = 1; } // Subscriber
		if (current_user_can('edit_posts')) { $user_level = 2; } // Contributor
		if (current_user_can('edit_published_posts')) { $user_level = 3; } // Author
		if (current_user_can('moderate_comments')) { $user_level = 4; } // Editor
		if (current_user_can('activate_plugins')) { $user_level = 5; } // Administrator
		
		// Language
		$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix . "symposium_config"));
		$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
		if ($language->pw == '') {
			$html .= "<p>Language has not been set, please check the admin options page, and the admin health check page</p>";
		} else {
						
			// Act upon any actions
			if (isset($_POST['delin'])) {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_in_deleted = 'on' WHERE mail_mid = ".$_POST['delin']." AND mail_to = ".$current_user->ID) );
			}
			if (isset($_POST['delsent'])) {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_sent_deleted = 'on' WHERE mail_mid = ".$_POST['delsent']." AND mail_from = ".$current_user->ID) );
			}
			
			// Has a new mail been sent
			if (isset($_POST['compose_recipient'])) {
				$recipient_name = $_POST['compose_recipient'];
				$recipient = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE lower(display_name) = '".strtolower($recipient_name)."'");
				if (!$recipient) {
					$mail_sent_result = $recipient_name.' could not be found.';
				} else {
					$subject = $_POST['compose_subject'];
					$message = $_POST['compose_text'];
					$previous = $_POST['compose_previous'];
					$message = $message.$previous;
					
					// Send mail
					$rows_affected = $wpdb->prepare( $wpdb->insert( $wpdb->prefix . "symposium_mail", array( 
					'mail_from' => $current_user->ID, 
					'mail_to' => $recipient, 
					'mail_subject' => $subject,
					'mail_message' => $message
					 ) ) );		

					$mail_sent_result = 'Message sent to '.$recipient_name.'.';
				}
				$view = "result";
			}

			// Get mail id worked out with default message before tabs to include correct unread count
			$show = $_GET['show'];
			if (!isset($_GET['show'])) {
				if ($view == "in") {
					$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->prefix."symposium_mail WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
				} else {
					$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->prefix."symposium_mail WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
				}
			}
			if ($show > 0) { $message_html .= get_message($show, $view, $language_key); } else { $message_html .= "&nbsp;"; }

			// Re-count unread mail (in case previously deleted)
			$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
			$unread_sent = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_from = ".$current_user->ID." AND mail_sent_deleted != 'on' AND mail_read != 'on'");
			if ($unread_in > 0) { $unread_in_show = " (".$unread_in.")"; } else { $unread_in_show = ""; }
			if ($unread_sent > 0) { $unread_sent_show = " (".$unread_sent.")"; } else { $unread_sent_show = ""; }

			$html .= '<div id="symposium-wrapper">';
							
				$html .= '<div id="mail_tabs">';
				$html .= '<div class="mail_tab nav-tab-'.$compose_active.'"><a href="'.$thispage.$q.'view=compose" class="nav-tab-'.$compose_active.'-link">Compose</a></div>';
				$html .= '<div class="mail_tab nav-tab-'.$inbox_active.'"><a href="'.$thispage.$q.'view=in" class="nav-tab-'.$inbox_active.'-link">In Box <span id="incount">'.$unread_in_show.'</span></a></div>';
				$html .= '<div class="mail_tab nav-tab-'.$sent_active.'"><a href="'.$thispage.$q.'view=sent" class="nav-tab-'.$sent_active.'-link">Sent Items<span id="sentcount">'.$unread_sent_show.'</span></a></div>';
				$html .= '</div>';
				
				$html .= '<div id="mail-main">';
				
				// Show result
				if ($view == "result") {
					
					$html .= '<p><br />'.$mail_sent_result.'</p>';
					$view = "compose";
					
				}
				
				// COMPOSE
				if ($view == "compose") {
					
					$recipient_id = '';
					if (isset($_POST['reply_recipient'])) { $recipient_id = $_POST['reply_recipient']; }
					if (isset($_GET['to'])) { $recipient_id = $_GET['to']; }
					
					if ($recipient_id != '') {
						$recipient = $wpdb->get_var("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$recipient_id);
						
						if (isset($_POST['reply_mid'])) {
							$mail_message = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$_POST['reply_mid']);
							
							$subject = $mail_message->mail_subject;
							if (substr($subject, 0, 4) != "Re: ") {
								$subject = "Re: ".$subject;
							}
							$message = stripslashes($mail_message->mail_message);
							
							$header = chr(13).chr(13).chr(13)."--------------------------".chr(13);
							$header .= "From: ".stripslashes($mail_message->display_name).chr(13);
							$header .= "Sent: ".$mail_message->mail_sent.chr(13);
							$header .= "Subject: ".stripslashes($mail_message->mail_subject).chr(13).chr(13);
							
							$message = $header.$message;
						} else {
							$subject = '';
							$message = '';
						}
						
					} else {
						$recipient = '';
						$subject = '';
						$message = '';
					}
					
   					$html .= '<form method="post" action="">';

					$html .= '<div style="float:right; padding:22px;">';
					$html .= '<input type="submit" class="button" style="float: left; height:46px;" value="Send" />';
					$html .= '</div>';

					$html .= '<div class="new-topic-subject label">Start typing the "Display Name" of the member you are sending to...</div>';
   					$html .= "<input type='text' id='compose_recipient' name='compose_recipient' class='new-topic-subject-input' style='width:75%' value='".htmlentities(stripslashes($recipient), ENT_QUOTES)."'/>";
   					
					$html .= '<div class="new-topic-subject label">Subject</div>';
   					$html .= "<input type='text' id='compose_subject' name='compose_subject' class='new-topic-subject-input' style='width:95%' value='".htmlentities(stripslashes($subject), ENT_QUOTES)."' />";
   					
					$html .= '<div class="new-topic-subject label">Message</div>';
					$html .= '<textarea class="reply-topic-subject-text" style="height:300px" id="compose_text" name="compose_text"></textarea>';

   					if ($message != '') {
						$html .= '<div class="new-topic-subject label">Previous message...</div>';
   						$html .= str_replace(chr(13), "<br />", $message);
   					}
   					
					$html .= '<input type="hidden" name="compose_previous" value="'.$message.'" />';

   					$html .= '</form>';
					
				} // End of Compose


				// IN BOX
				if ($view == "in") {
					
					$html .= "<div class='style='width:100%; padding:0px; border: 0px;'>";
	
					// Message
					$html .= "<div style='float: left; width: 100%;'>";
					$html .= "<div id='in_message' style='margin-left: 325px;'>";
					$html .= $message_html;
					$html .= "</div></div>";
					
					// Get list of inbox messages
					$html .= "<div id='inbox' style='float:left;width:300px;margin-left:-100%;'>";
					$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." ORDER BY mail_mid DESC");
					if ($mail) {
						foreach ($mail as $item)
						{
							if ($item->mail_read != "on") {
								$row_bg = "row_odd";
							} else {
								$row_bg = "";
							}
									
							$html .= "<div id='".$item->mail_mid."' class='mail_item ".$row_bg."' style='cursor:pointer;border-bottom: 1px solid #aaa;padding-top:4px;padding-bottom:4px;'>";
							$html .= "<div style='float:right; font-size: 12px; color: #aaa;'>";
							$html .= symposium_time_ago($item->mail_sent, $language_key);
							$html .= "</div>";
							$html .= "<strong>".stripslashes(symposium_profile_link($item->mail_from))."</strong><br />";
							$html .= stripslashes($item->mail_subject)."<br />";
							$message = stripslashes($item->mail_message);
							if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
							$html .= "<span style='font-style:italic;'>".$message."</span>";
							$html .= "</div>";
						}
					} else {
						$html .= "<p>No mail.</p>";
					}
					$html .= "</div>";
					
					$html .= "</div>";
					
				} // End of Inbox

				// SENT BOX
				if ($view == "sent") {
					
					$show = $_GET['show'];
					if (!isset($_GET['show'])) {
						$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->prefix."symposium_mail WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
					}
					
					$html .= "<div class='style='width:100%; padding:0px; border: 0px;'>";
	
					// Message
					$html .= "<div style='float: left; width: 100%;'>";
					$html .= "<div id='sent_message' style='margin-left: 325px;'>";
					$html .= $message_html;
					$html .= "</div></div>";
					
					// Get list of sent messages
					$html .= "<div id='sentbox' style='float:left;width:300px;margin-left:-100%;'>";
					$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_to = u.ID WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC");

					if ($mail) {
						foreach ($mail as $item)
						{
							if ($item->mail_read != "on") {
								$row_bg = "row_odd";
							} else {
								$row_bg = "";
							}
									
							$html .= "<div id='".$item->mail_mid."' class='mail_item ".$row_bg."' style='cursor:pointer;border-bottom: 1px solid #aaa;padding-top:4px;padding-bottom:4px;'>";
							$html .= "<div style='float:right; font-size: 12px; color: #aaa;'>";
							$html .= symposium_time_ago($item->mail_sent, $language_key);
							$html .= "</div>";
							$html .= "<strong>".stripslashes(symposium_profile_link($item->mail_to))."</strong><br />";
							$html .= stripslashes($item->mail_subject)."<br />";
							$message = stripslashes($item->mail_message);
							if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
							$html .= "<span style='font-style:italic;'>".$message."</span>";
							$html .= "</div>";
						}
					} else {
						$html .= "<p>No mail.</p>";
					}
					$html .= "</div>";
					
					$html .= "</div>";

				} // End of Sent
							
				$html .= '</div>'; 
				 
				// Notices
				$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin_dir."busy.gif' /> ".$language->sav."</div>";
				$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin_dir."busy.gif' /> ".$language->pw."</div>";
		
			$html .= '</div>'; // End of Wrapper
		
		}
		
	} else {
		// Not logged in
		$html .= "You have to <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>login</a>, to access mail.<br />";
	}
	
	// Send HTML
	return $html;

}

/* ====================================================== PHP FUNCTIONS ====================================================== */

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
	
	$msg .= "<span style='font-family:".$styles->headingsfamily."; font-size:".$styles->headingssize."px; font-weight:bold;'>".stripslashes($mail->mail_subject)."<br /></span><br />";
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
/* ====================================================== AJAX FUNCTIONS ====================================================== */

// AJAX function to get topic details for editing
function getMailMessage(){

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
add_action('wp_ajax_getMailMessage', 'getMailMessage');

/* ====================================================== ADMIN/ACTIVATE/DEACTIVATE ====================================================== */

function symposium_mail_activate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>5, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Mail activated.'));
	} else {
	    wp_die( __('Core plugin must be actived first.') );
	}

}

function symposium_mail_deactivate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>6, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Mail de-activated.'));
	}

}

register_activation_hook(__FILE__,'symposium_mail_activate');
register_deactivation_hook(__FILE__, 'symposium_mail_deactivate');

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-mail', 'symposium_mail');  



?>
