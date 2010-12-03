<?php

/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium_forum] on any page to display forum.
Version: 0.1.3
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: GPL2
*/
	
/*  Copyright 2010  Simon Goodchild  (simon.goodchild@mac.com)

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

function symposium_forum() {	

	global $wpdb, $current_user;

	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	$thispage = get_permalink();
	
	wp_get_current_user();
	$users = $wpdb->prefix . 'users';
	$config = $wpdb->prefix . 'symposium_config';
	$topics = $wpdb->prefix . 'symposium_topics';
	$subs = $wpdb->prefix . 'symposium_subs';
	$cats = $wpdb->prefix . 'symposium_cats';
	$lang = $wpdb->prefix . 'symposium_lang';

	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$config));
	$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
	if (!$language) {
		
		echo "<p>Language translation not available for ".$wpdb->get_var($wpdb->prepare("SELECT language FROM ".$config))."</p>";
		
	} else {
		
		// Get Topic ID for use in jQuery functions	
		if (isset($_GET['show'])) {
			$show_tid = $_GET['show']*1;
		} else {
			$show_tid = 0;
			if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
		}
		?>
				
		<script type="text/javascript">
	    jQuery(document).ready(function() { 	
	    	
			jQuery(".notice").hide();
			jQuery(".pleasewait").hide();
			
		    jQuery('.backto').click(function() {
				jQuery('.pleasewait').inmiddle().show();
		    });		
			
			// Centre in screen
			jQuery.fn.inmiddle = function () {
		    this.css("position","absolute");
		    this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
		    this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
			    return this;
			}
	
			// Edit topic (AJAX)
		   jQuery('#starting-post').hover(function() {
		        jQuery(this).find('#edit-this-topic').show();
		   }, function() {
		        jQuery(this).find('#edit-this-topic').hide();
		   });
			// Edit the topic
		   jQuery('#edit-this-topic').click(function() {
				jQuery('.pleasewait').inmiddle().show();
				jQuery("#new-category-div").show();
		    	var tid = jQuery('.edit-topic-tid').attr('id');	
				jQuery.post("/wp-admin/admin-ajax.php", {
					action:"getEditDetails", 
					'tid':<?php echo $show_tid; ?>
					},
				function(str)
				{
					var details = str.split("|");
					jQuery('#edit_topic_subject').val(details[0]);
					jQuery('#edit_topic_subject').removeAttr("disabled");
					jQuery('#edit_topic_text').html(details[1]);
					jQuery('.edit-topic-parent').attr("id", details[2]);
					jQuery("#new-category").val(details[4]);
				});
				jQuery('#edit-topic-div').inmiddle().fadeIn();
				jQuery('.pleasewait').hide();
		   });	    	
			
		   // Edit a reply
		   jQuery('.edit-child-topic').click(function() {
				jQuery('.pleasewait').inmiddle().show();
				jQuery("#new-category-div").hide();
		    	var tid = jQuery(this).attr('id');	
				jQuery.post("/wp-admin/admin-ajax.php", {
					action:"getEditDetails", 
					'tid':tid
					},
				function(str)
				{
					var details = str.split("|");
					jQuery('#edit_topic_subject').val(details[0]);
					jQuery('#edit_topic_subject').attr("disabled", "enabled");
					jQuery('#edit_topic_text').html(details[1]);
					jQuery('.edit-topic-parent').attr("id", details[2]);
					jQuery('.edit-topic-tid').attr("id", details[3]);
				});
				jQuery('#edit-topic-div').inmiddle().fadeIn();
				jQuery('.pleasewait').hide();
		   });	 
		   // Update contents of edit form
			jQuery('.edit_topic_submit').click(function(){
				jQuery('.notice').inmiddle().show();
		    	var tid = jQuery('.edit-topic-tid').attr('id');	
		    	var parent = jQuery('.edit-topic-parent').attr('id');
				var topic_subject = jQuery('#edit_topic_subject').val();	
				var topic_post = jQuery('#edit_topic_text').val();	
				var topic_category = jQuery('#new-category').val();	
	
				if (parent == 0) {
					jQuery('.topic-post-header').html(topic_subject);
					jQuery('.topic-post-post').html(topic_post.replace(/\n/g, "<br />"));
				}
	
				jQuery.post("/wp-admin/admin-ajax.php", {
					action:"updateEditDetails", 
					'tid':tid,
					'topic_subject':topic_subject,
					'topic_post':topic_post,
					'topic_category':topic_category
					},
				function(tid)
				{
					jQuery('.notice').fadeOut('fast');
					jQuery('#edit-topic-div').fadeOut('fast');
					window.location.href=window.location.href;
				});
			});
			// Cancel form
			jQuery('.edit_topic_cancel').click(function(){
				jQuery('#edit-topic-div').fadeOut('fast');
		   });
	
			// Show delete link on row hover
		    jQuery('.row').hover(function() {
		        jQuery(this).find('.delete').show()
		    }, function() {
		        jQuery(this).find('.delete').hide();
		    });
		    jQuery('.row_odd').hover(function() {
		        jQuery(this).find('.delete').show()
		    }, function() {
		        jQuery(this).find('.delete').hide();
		    });	    
		    jQuery('.child-reply').hover(function() {
		        jQuery(this).find('.delete').show();
		        jQuery(this).find('.edit').show();
		    }, function() {
		        jQuery(this).find('.delete').hide();
		        jQuery(this).find('.edit').hide();
		    });
		    
		    // Check if really want to delete	    
			jQuery('.delete').click(function(){
			  var answer = confirm('Are you sure?');
			  return answer // answer is a boolean
			});
	
			// Show new topic and reply topic forms
			jQuery('#new-topic-link').click(function() {
			  	jQuery('#new-topic').toggle('slow');
			});
			jQuery('#cancel_post').click(function() {
			  	jQuery('#new-topic').hide('slow');
			});
	
			jQuery('#reply-topic-link').click(function() {
			  	jQuery('#reply-topic').toggle('slow');
			});
			jQuery('#cancel_reply').click(function() {
			  	jQuery('#reply-topic').hide('slow');
			});
			
			// Has a checkbox been clicked? If so, check if one for symposium (AJAX)
		    jQuery('input[type="checkbox"]').bind('click',function() {
		    	
		    	var checkbox = jQuery(this).attr('id');		    		
		    	
		    	// Subscribe to New Forum Topics
		    	if (checkbox == 'symposium_subscribe') {
					jQuery('.notice').inmiddle().fadeIn();
			        if(jQuery(this).is(':checked')) {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSubscribe", 
							'value':1
							},
						function(str)
						{
						      // Subscribed
						});
			        } else {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSubscribe", 
							'value':0
							},
						function(str)
						{
						      // Un-subscribed
						});
			        }
					jQuery('.notice').delay(100).fadeOut('slow');
		    	}
	
		    	// Subscribe to Topic Posts
		    	if (checkbox == 'subscribe') {
					jQuery('.notice').inmiddle().fadeIn();
			        if(jQuery(this).is(':checked')) {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForum", 
							'tid':<?php echo $show_tid; ?> , 
							'value':1
							},
						function(str)
						{
						      // Subscribed
						});
			        } else {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForum", 
							'tid':<?php echo $show_tid; ?> , 
							'value':0
							},
						function(str)
						{
						      // Un-subscribed
						});
			        }
					jQuery('.notice').delay(100).fadeOut('slow');
		    	}
		    	
		    	// Sticky Topics
		    	if (checkbox == 'sticky') {
					jQuery('.notice').inmiddle().fadeIn();
			        if(jQuery(this).is(':checked')) {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSticky", 
							'tid':<?php echo $show_tid; ?> , 
							'value':1
							},
						function(str)
						{
						      // Stuck
						});
						
			        } else {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSticky", 
							'tid':<?php echo $show_tid; ?> , 
							'value':0
							},
						function(str)
						{
						      // Unstuck
						});
			        }
					jQuery('.notice').delay(100).fadeOut('slow');
		    	}
		    			    	
			});
			
	    });
	 
		</script>
		
		<?php
		
		// Get passed variables
		if (isset($_GET['cid'])) { $cat_id = $_GET['cid']; }
		if (isset($_POST['cid'])) { $cat_id = $_POST['cid']; }
	
		// Set dynamic styles
		$border_radius = $wpdb->get_var($wpdb->prepare("SELECT border_radius FROM ".$config));
		$bigbutton_background = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background FROM ".$config));
		$bigbutton_color = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color FROM ".$config));
		$bigbutton_background_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background_hover FROM ".$config));
		$bigbutton_color_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color_hover FROM ".$config));
		$bg_color_1 = $wpdb->get_var($wpdb->prepare("SELECT bg_color_1 FROM ".$config));
		$bg_color_2 = $wpdb->get_var($wpdb->prepare("SELECT bg_color_2 FROM ".$config));
		$bg_color_3 = $wpdb->get_var($wpdb->prepare("SELECT bg_color_3 FROM ".$config));
		$text_color = $wpdb->get_var($wpdb->prepare("SELECT text_color FROM ".$config));
		$text_color_2 = $wpdb->get_var($wpdb->prepare("SELECT text_color_2 FROM ".$config));
		$link = $wpdb->get_var($wpdb->prepare("SELECT link FROM ".$config));
		$link_hover = $wpdb->get_var($wpdb->prepare("SELECT link_hover FROM ".$config));
		$table_rollover = $wpdb->get_var($wpdb->prepare("SELECT table_rollover FROM ".$config));
		$table_border = $wpdb->get_var($wpdb->prepare("SELECT table_border FROM ".$config));
		$replies_border_size = $wpdb->get_var($wpdb->prepare("SELECT replies_border_size FROM ".$config));
		$row_border_style = $wpdb->get_var($wpdb->prepare("SELECT row_border_style FROM ".$config));
		$row_border_size = $wpdb->get_var($wpdb->prepare("SELECT row_border_size FROM ".$config));
		$label = $wpdb->get_var($wpdb->prepare("SELECT label FROM ".$config));
		$categories_background = $wpdb->get_var($wpdb->prepare("SELECT categories_background FROM ".$config));
		$categories_color = $wpdb->get_var($wpdb->prepare("SELECT categories_color FROM ".$config));
		
		echo "<style>";
		
		echo "#symposium-wrapper * {";
		echo "	border-radius: ".$border_radius."px;";
		echo "  -moz-border-radius:".$border_radius."px;";
		echo "}";
	
		echo "#symposium-wrapper .label {";
		echo "  color: ".$label.";";
		echo "}";
		
		echo "#symposium-wrapper, #symposium-wrapper a {";
		echo "	color: ".$text_color.";";
		echo "}";
	
		echo "#symposium-wrapper #new-topic, #symposium-wrapper #reply-topic, #symposium-wrapper #edit-topic-div {";
		echo "	background-color: ".$bg_color_3.";";
		echo "	border: ".$replies_border_size."px solid ".$bg_color_1.";";	
		echo "}";
		
		echo "#symposium-wrapper #new-topic-link, #symposium-wrapper #reply-topic-link, #symposium-wrapper .button {";
		echo "	background-color: ".$bigbutton_background.";";
		echo "	color: ".$bigbutton_color.";";
		echo "}";
	
		echo "#symposium-wrapper #new-topic-link:hover, #symposium-wrapper #reply-topic-link:hover, #symposium-wrapper .button:hover {";
		echo "	background-color: ".$bigbutton_background_hover.";";
		echo "	color: ".$bigbutton_color_hover.";";
		echo "}";
		
		echo "#symposium-wrapper #symposium_table {";
		echo "	border: ".$table_border."px solid ".$bg_color_1.";";	
		echo "}";
	
		echo "#symposium-wrapper .table_topic, #symposium-wrapper .table_startedby, #symposium-wrapper .table_freshness, #symposium-wrapper .table_replies, #symposium-wrapper .table_views, #symposium-wrapper .table_topics {";
		echo "	background-color: ".$bg_color_1.";";
		echo "	color: #fff;";
		echo "  font-weight: bold;";
	 	echo "  border-radius:0px;";
		echo "  -moz-border-radius:0px;";
		echo "  border: 0";
	 	echo "}";
	
		echo "#symposium-wrapper .table_topic {";
	 	echo "  border-top-left-radius:".($border_radius-5)."px;";
		echo "  -moz-border-radius-topleft:".($border_radius-5)."px;";
		echo "}";
		
		echo "#symposium-wrapper .table_topics {";
	 	echo "  border-top-right-radius:".($border_radius-5)."px;";
		echo "  -moz-border-radius-inmiddle:".($border_radius-5)."px;";
		echo "}";
		
		echo "#symposium-wrapper .round_bottom_left {";
	 	echo "  border-bottom-left-radius:".($border_radius-5)."px;";
		echo "  -moz-border-radius-bottomleft:".($border_radius-5)."px;";
		echo "}";
		
		echo "#symposium-wrapper .round_bottom_right {";
	 	echo "  border-bottom-right-radius:".($border_radius-5)."px;";
		echo "  -moz-border-radius-bottomright:".($border_radius-5)."px;";
		echo "}";
		
		echo "#symposium-wrapper .categories_color {";
		echo "	color: ".$categories_color.";";
		echo "}";
		echo "#symposium-wrapper .categories_background {";
		echo "	background-color: ".$categories_background.";";
		echo "}";
		
		echo "#symposium-wrapper .row {";
		echo "	background-color: ".$bg_color_2.";";
		echo "}";
			
		echo "#symposium-wrapper .row_odd {";
		echo "	background-color: ".$bg_color_3.";";
		echo "}";
	
		echo "#symposium-wrapper .row:hover, #symposium-wrapper .row_odd:hover {";
		echo "	background-color: ".$table_rollover.";";
		echo "}";
		
		echo "#symposium-wrapper .row_link, #symposium-wrapper .edit, #symposium-wrapper .delete {";
		echo "	color: ".$link.";";
		echo "}";
			
		echo "#symposium-wrapper .row_link:hover {";
		echo "	color: ".$link_hover.";";
		echo "}";
	
		echo "#symposium-wrapper #starting-post {";
		echo "	border: ".$replies_border_size."px solid ".$bg_color_1.";";
		echo "	background-color: ".$bg_color_2.";";
		echo "}";
		
		echo "#symposium-wrapper .started-by {";
		echo "	color: ".$text_color_2.";";
		echo "}";
				
		echo "#symposium-wrapper #child-posts {";
		echo "	border: ".$replies_border_size."px solid ".$bg_color_1.";";
		echo "	background-color: ".$bg_color_3.";";
		echo "}";
	
		echo "#symposium-wrapper .child-reply, #symposium-wrapper .row_topic, #symposium-wrapper .row_startedby, #symposium-wrapper .row_freshness, #symposium-wrapper .row_replies, #symposium-wrapper .row_views {";
		echo "	border-bottom: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";";
		echo "}";
				
		echo "</style>";
	
		// Wrapper
		echo "<div id='symposium-wrapper'>";
	
		// default message
		$msg = "";
		
		// Check for delete topic (and posts/subs, admin only)
		if ( ($_GET['action'] == 'deltopic') && (current_user_can('level_10')) ) {	
			$wpdb->query("DELETE FROM ".$topics." WHERE tid = ".$_GET['tid']);
			$wpdb->query("DELETE FROM ".$topics." WHERE topic_parent = ".$_GET['tid']);
			$wpdb->query("DELETE FROM ".$subs." WHERE tid = ".$_GET['tid']);
		}
		
		// Check for delete post (admin only)
		if ( ($_GET['action'] == 'del') && (current_user_can('level_10')) ) {	
			$wpdb->query("DELETE FROM ".$topics." WHERE tid = ".$_GET['tid']);
		}
		
		// New Topic
		if ($_POST['action'] == 'post') {
			
			$new_topic_subject = $_POST['new_topic_subject'];
			$new_topic_text = $_POST['new_topic_text'];
			$new_topic_subscribe = $_POST['new_topic_subscribe'];
			$new_topic_category = $_POST['new_topic_category'];
		
			$store = true;
			$edit_new_topic = false;
			if ($new_topic_subject == '') { $msg = "Please enter a subject"; $store = false; $edit_new_topic = true; }
			if ($new_topic_text == '') { $msg = "Please enter a message"; $store = false; $edit_new_topic = true; }
			
			if ( ($store) && is_user_logged_in() ) {
				// Check for duplicates
				
				$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));
	
				if ($topic_count > 1) {
					// Don't double post
				} else {						
					// Store new topic in post
	
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
						WHERE tid = 0");
	
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
				$new_topic_subject = '';
				$new_topic_text = '';
				$new_topic_subscribe = '';
			}
		}
		
		// Reply to Topic
		if ($_POST['action'] == 'reply') {
			
			$tid = $_POST['tid'];
			$reply_text = $_POST['reply_text'];
			$reply_topic_subscribe = $_POST['reply_topic_subscribe'];
			
			if ($reply_text	 != '') {
			
				if (is_user_logged_in()) {
					// Check for duplicates
					$reply_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_post = '".$reply_text."' and topic_owner = ".$current_user->ID));
					
					if ($reply_count > 0)
					{
						// Suspected Double Post
					} else {						
						// Store new topic in post					
	
						$reply_text = str_replace("<", "&lt;", $reply_text);
						$reply_text = str_replace(">", "&gt;", $reply_text);
	
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
					
						$body = $owner_name." has replied to a topic you are subscribed to...<br />";
						$body .= $reply_text."<br /><br />";
						$body .= $thispage;
						$body = str_replace("\\r\\n", "<br />", $body);
						$body = str_replace("\\", "", $body);
	
						foreach ($query as $user) {
	
							sendmail($user->user_email, $language->nfr, $body);
							
						}
						
					}
					
					$reply_text = '';
					$reply_topic_subscribe = '';
				}
				
			}		
		}
		
		// any page id
		if(!isset($_GET['show'])){
			$topic = '0';
		} else {
			$topic = $_GET['show'];
		}
		
		if ($msg != '') {
			echo($msg);
		}
		
		// Get Topic ID if applicable
	
		$show = '';
		if ($_GET['show'] != '') { $show = $_GET['show']; }
		if ($_POST['show'] != '') { $show = $_POST['show']; }
		if ($tid != '') { $show = $tid; }
			
		if (isset($cat_id)) {
			echo "<div style='clear:both' class='floatright'>";
			if ( ( (isset($cat_id)) && ($cat_id != '') ) && ($show != '') ) {
				$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
				echo "<a class='backto label' href='".$thispage."?cid=".$cat_id."'>".$language->bt." ".$category_title."...</a>&nbsp;&nbsp;&nbsp;&nbsp;";
			}
	
			echo "<a class='backto label' href='".$thispage."'>".$language->btf."...</a>";
			echo "</div>";
		}
	
	
		// Submenu ***************************************************************************************************
		if (is_user_logged_in()) {
			
			// Sub Menu for Logged in User
				echo "<ul id='topic-links'>";
			if ($show == '') {
				$allow_new = $wpdb->get_var($wpdb->prepare("SELECT allow_new FROM ".$cats." WHERE cid=".$cat_id));
				if ( ($cat_id == '' || $allow_new == "on") || (current_user_can('level_10')) ) {
					echo "<li id='new-topic-link'>".$language->sant."</li>";
				} else {
					echo "<div style='height:30px'></div>";
				}
			} else {
				echo "<li id='reply-topic-link'>".$language->aar."</li>";
			}
			echo "</ul>";
		
			// New Topic Form
	
			echo '<div id="new-topic"';
				if ($edit_new_topic == false) { echo ' style="display:none;"'; } 
				echo '>';
				echo '<form action="'.$thispage.'" method="post">';
				echo '<div><input type="hidden" name="action" value="post">';
				echo '<input type="hidden" name="cid" value="'.$cat_id.'">';
				echo '<div id="new-topic-subject-label" class="new-topic-subject label">'.$language->ts.'</div>';
				echo '<input class="new-topic-subject-input" type="text" name="new_topic_subject" value="';
				echo ($new_topic_subject); 
				echo '"></div>';
				echo '<div><div class="new-topic-subject label">'.$language->fpit.'</div>';
				echo '<textarea class="new-topic-subject-text" name="new_topic_text">';
				echo ($new_topic_text);
				echo '</textarea></div>';
				$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));
				if ($show_categories == "on") {
					echo '<div class="new-topic-category label">'.$language->sac.': ';
					if (current_user_can('level_10')) {
						$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
					} else {
						$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY listorder');			
					}
					if ($categories) {
						echo '<select name="new_topic_category">';
						foreach ($categories as $category) {
							echo '<option value='.$category->cid;
							if (isset($cat_id)) {
								if ($category->cid == $cat_id) { echo " SELECTED"; }
							} else {
								if ($category->cid == $defaultcat) { echo " SELECTED"; }
							}
							echo '>'.$category->title.'</option>';
						}
						echo '</select>';
					}
					echo '</div>';
				} else {
					echo '<input type="hidden" name="new_topic_category" value="0" />';
				}
				echo '<div class="emailreplies label"><input type="checkbox" name="new_topic_subscribe"';
				if ($new_topic_subscribe != '') { echo 'checked'; } 
				echo '> '.$language->emw.'</div>';
				echo '<input type="submit" class="button" style="float: left" value="'.$language->p.'" />';
				echo '</form>';
				echo '<input id="cancel_post" type="submit" class="button" onClick="javascript:void(0)" style="float: left" value="'.$language->c.'" />';
			echo '</div>';
			
			if ($show != '') {
				echo '<div id="reply-topic" style="display:none;">';
					echo '<form action="'.$thispage.'" method="post">';
					echo '<input type="hidden" name="action" value="reply">';
					echo '<input type="hidden" name="tid" value="'.$show.'">';
					echo '<input type="hidden" name="cid" value="'.$cat_id.'">';
					echo '<div class="reply-topic-subject label">'.$language->rtt.'</div>';
					echo '<textarea class="reply-topic-subject-text" name="reply_text"></textarea>';
					echo '<div class="emailreplies label"><input type="checkbox" name="reply_topic_subscribe"';
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$show." and uid = ".$current_user->ID));
					$subscribed = false;	
					if ($subscribed_count > 0) { echo 'checked'; $subscribed = true; } 
					echo '> Email me when there are more replies to this topic</div>';
					echo '<input type="submit" class="button" style="float: left" value="'.$language->rep.'" />';
					echo '</form>';
					echo '<input id="cancel_reply" type="submit" class="button" onClick="javascript:void(0)" style="float: left" value="'.$language->c.'" />';
				echo '</div>';
			}
			
				
		} else {
	
			echo "Until you <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>login</a>, you can only view the forum.";
			echo "<br />";
	
		}
	
		if ($show == '') {
					
			// Show Forum ***************************************************************************************************
			
			// Forum Subscribe
			if (is_user_logged_in()) {
				$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = 0 and uid = ".$current_user->ID));
				
				echo "<div class='symposium_subscribe_option label'>";
				echo "<input type='checkbox' id='symposium_subscribe' name='symposium_subscribe'";
				if ($subscribed_count > 0) { echo ' checked'; } 
				echo "> ".$language->rew;
				echo "</div>";
			}	
						
			echo '<table id="symposium_table" cellspacing=0 cellpadding=6 style="width:100%;border-collapse:inherit;">';
		
			// Loop through all categories
			$use_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
			
			if ( ($use_categories == "on") && (!(isset($cat_id))) ) {
	
				echo "<tr class='table_header'>";
				echo "<td class='table_topic'>".$language->cat."</td>";
				echo "<td class='table_startedby'>".$language->lac."</td>";
				echo "<td class='table_topics' style='text-align:center'>".$language->top."</td>";
				echo "</tr>";
				
				$categories = $wpdb->get_results("SELECT * FROM ".$cats." ORDER BY listorder");
				
				$num_cats = $wpdb->num_rows;
				$cnt = 0;
				foreach($categories as $category) {
					$cnt++;
					if ($cnt/2 != round($cnt/2)) {
						echo "<tr class='row'>";
					} else {
						echo "<tr class='row_odd'>";
					}
					echo "<td class='row_topic";
					if ($cnt == $num_cats) {
						echo " round_bottom_left";
					}
					echo "' valign='top'><a class='backto row_link' href='?cid=".$category->cid."'>".$category->title."</a></td>";
					$last_topic = $wpdb->get_row("
						SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_category 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$category->cid." ORDER BY topic_date DESC"); 
					echo "<td class='row_topic'>";
					if ($last_topic) {
						$reply = $wpdb->get_row("
							SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_category 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE topic_parent = ".$last_topic->tid." ORDER BY topic_date DESC"); 
												
						echo "<a class='backto row_link' href='".$thispage."?cid=".$last_topic->topic_category."&show=".$last_topic->tid."'>".$last_topic->topic_subject."</a><br />";
	
						if ($reply) {
							echo $reply->display_name." replied ".symposium_time_ago($reply->topic_date, $language_key).":<br />";
							$post = str_replace("\\", "", $reply->topic_post);
						} else {
							echo "by ".$last_topic->display_name.", ".symposium_time_ago($last_topic->topic_date, $language_key).":<br />";
							$post = str_replace("\\", "", $last_topic->topic_post);
						}
						if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
						echo "<span class='row_topic_text'>".stripslashes($post)."</span>";
					} else {
						echo "&nbsp;";
					}
					echo "</td>";
					$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_parent = 0 AND topic_category = ".$category->cid));
					echo "<td class='row_topic";
					if ($cnt == $num_cats) {
						echo " round_bottom_right";
					}
					echo "' style='text-align:center' valign='top'>".$topic_count."</td>";
					echo "</tr>";
				}
	
	
			}
			
			if ( ($use_categories != "on") || (isset($cat_id)) ) {
	
				echo "<tr class='table_header'>";
				echo "<td class='table_topic'>".$language->t."</td>";
				echo "<td class='table_startedby'>".$language->sbl."</td>";
				echo "<td class='table_freshness'>".$language->f."</td>";
				echo "<td class='table_replies'>".$language->r."</td>";
				echo "<td class='table_topics'>".$language->v."</td>";
				echo "</tr>";
			
				// Get Forums	
				if ($use_categories == "on") {
					echo "<tr>";
					$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
					echo "<td class='categories_background categories_color' style='border:0' colspan=5>".$category_title."</td>";
					echo "</tr>";
						
					$query = $wpdb->get_results("
						SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_sticky 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$cat_id." ORDER BY topic_sticky DESC, topic_date DESC"); 
						
				} else {
					
					$query = $wpdb->get_results("
						SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_sticky 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE topic_parent = 0 ORDER BY topic_sticky DESC, topic_date DESC"); 
						
				}
	
				$num_topics = $wpdb->num_rows;
					
				if ($query) {
				
					$row_cnt=0;
				
					foreach ($query as $topic) {
					
						$row_cnt++;
						
						$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$topics." WHERE topic_parent = ".$topic->tid));
						$views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$topics." WHERE tid = ".$topic->tid));
								
						if ($row_cnt/2 != round($row_cnt/2)) {
							echo "<tr class='row'>";
						} else {
							echo "<tr class='row_odd'>";
						}
						echo "<td class='row_topic";
						if ($row_cnt == $num_topics) {
							echo " round_bottom_left";
						}
						echo "'>";
						if (current_user_can('level_10')) {
							echo " <a class='delete' href='".$thispage."?show=".$show."&cid=".$cat_id."&action=deltopic&tid=".$topic->tid."'>".$language->d."</a>";
						}
						
						echo "<div class='row_link_div'><a href='".$thispage."?cid=".$cat_id."&show=".$topic->tid."' class='backto row_link'>".stripslashes($topic->topic_subject)."</a>";
						if (is_user_logged_in()) {
							$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$topic->tid." AND uid = ".$current_user->ID));
							if ($is_subscribed > 0) { echo ' <img src="'.$plugin.'orange-tick.gif" alt="Subscribed" />'; } 
						}
						if ($topic->topic_sticky) { echo ' <img src="'.$plugin.'pin.gif" alt="Sticky Topic" />'; } 
						
						echo "</div>";
						$post = str_replace("\\", "", $topic->topic_post);
						if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
						echo "<span class='row_topic_text'>".stripslashes($post)."</span></td>";
						echo "<td class='row_startedby' valign='top'>";
						$last_post = $wpdb->get_row("
							SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_sticky 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE topic_parent = ".$topic->tid." ORDER BY tid DESC"); 
						if ( $last_post ) {
							echo "Last reply by: ".$last_post->display_name."<br />";
							$post = str_replace("\\", "", $last_post->topic_post);
							if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
							echo "<div class='row_topic_text' style='margin-top: 5px'>".stripslashes($post)."</div>";
						} else {
							echo $language->sb.": ".$topic->display_name;
						}
						echo "</td>";
						echo "<td class='row_freshness' valign='top' align='right'>".symposium_time_ago($topic->topic_date, $language_key)."</td>";
						echo "<td class='row_replies' valign='top' align='center'>".$replies."</td>";
						echo "<td class='row_views";
						if ($row_cnt == $num_topics) {
							echo " round_bottom_right";
						}
						echo "' valign='top' align='center'>".$views."</td>";
						echo "</tr>";
						
					}
				
				} else {
				
					echo "<tr>";
					echo "<td colspan=5 style='padding: 6px'>No topics yet</td>";
					echo "</tr>";			
				
				}
			}
		
			echo "</table>";
			
		} else {
			
			// Show topic ***************************************************************************************************
			
			$post = $wpdb->get_row("
				SELECT tid, topic_subject, topic_post, topic_started, display_name, topic_sticky, topic_owner 
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE tid = ".$show);
	
			if ($post) {
			
				// Edit Form
				echo '<div id="edit-topic-div" class="shadow">';
					echo '<div class="new-topic-subject label">'.$language->ts.'</div>';
					echo '<div id="'.$post->tid.'" class="edit-topic-tid"></div>';
					echo '<div id="" class="edit-topic-parent"></div>';
					echo '<input class="new-topic-subject-input" id="edit_topic_subject" type="text" name="edit_topic_subject" value="">';
					echo '<div class="new-topic-subject label">'.$language->tt.'</div>';
					echo '<textarea class="new-topic-subject-text" id="edit_topic_text" name="edit_topic_text"></textarea>';
					echo '<div id="new-category-div" style="float:left">'.$language->mc.': <select name="new-category" id="new-category">';
					echo '<option value="">'.$language->s.'</option>';
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
					if ($categories) {
						foreach ($categories as $category) {
							if ($category->allow_new == "on" || current_user_can('level_10')) {
								echo '<option value='.$category->cid.'>'.$category->title.'</option>';
							}
						}
					}
					echo '</select></div>';
					echo '<div style="float:right; margin-right:15px;">';
					echo '<input type="submit" class="button edit_topic_submit" value="'.$language->u.'" />';
					echo '<input type="submit" class="button edit_topic_cancel" value="'.$language->c.'" />';
					echo '</div>';
				echo '</div>';
				
				echo "<div id='starting-post'>";
				
				if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
					echo "<div id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".$language->e."</div>";
				}
				
				echo "<div class='topic-post-header'>".$post->topic_subject."</div>";					
				echo "<div class='started-by'>".$language->sb." ".$post->display_name." ".symposium_time_ago($post->topic_started, $language_key)."</div>";
				echo "<div class='topic-post-post'>".str_replace(chr(13), "<br />", stripslashes($post->topic_post))."</div>";
				
				echo "</div>";
	
				// Update views
				$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = ".$post->tid) );
									
				// Subscribe	
				if (is_user_logged_in()) {
	
					echo "<br /><div class='floatright label'>";
					echo "<form action='symposium.php'>";
					echo "<input type='checkbox' id='subscribe' name='subscribe'";
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$show." and uid = ".$current_user->ID));
					if ($subscribed_count > 0) { echo ' checked'; } 
					echo "> ".$language->rer;
					if (current_user_can('level_10')) {
						echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' id='sticky' name='sticky'";
						if ($post->topic_sticky > 0) { echo ' checked'; }
						echo "> ".$language->tis;
					}
					echo "</form>";
					
					echo "</div>";
				} else {
					echo "<br />";
				}
			
			}
	
			// Replies
			$child_query = $wpdb->get_results("
				SELECT tid, topic_subject, topic_post, topic_date, topic_owner, display_name, ID
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE topic_parent = ".$show." ORDER BY tid");
	
			if ($child_query) {
	
				echo "<div id='child-posts'>";
				
				foreach ($child_query as $child) {
	
					echo "<div class='child-reply'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							echo "<div style='float:right;padding-top:6px;'><a class='delete' href='".$thispage."?show=".$show."&cid=".$cat_id."&action=del&tid=".$child->tid."'>".$language->d."</a></div>";
							echo "<div id='".$child->tid."' class='edit-child-topic edit_topic edit label' style='cursor:pointer;'>".$language->e."&nbsp;&nbsp;|&nbsp;&nbsp;</div>";
						}
						echo "<div class='avatar'>";
							echo get_avatar($child->ID, 64);
						echo "</div>";
						echo "<div class='started-by'>".$child->display_name." ".$language->re." ".symposium_time_ago($child->topic_date, $language_key)."...";
						echo "</div>";
						echo "<div id='".$child->tid."' class='child-reply-post'>";
							echo "<p>".str_replace(chr(13), "<br />", stripslashes($child->topic_post))."</p>";
						echo "</div>";
					echo "</div>";
	
				}
				
				echo "</div>";
				
			}				
			
			// Quick Reply
			if (is_user_logged_in()) {
				echo '<div id="reply-topic-bottom">';
					echo '<form action="'.$thispage.'" method="post">';
					echo '<input type="hidden" name="action" value="reply">';
					echo '<input type="hidden" name="tid" value="'.$show.'">';
					echo '<input type="hidden" name="cid" value="'.$cat_id.'">';
					echo '<div class="reply-topic-subject label">'.$language->rtt.'</div>';
					echo '<textarea class="reply-topic-text" name="reply_text"></textarea>';
					echo '<div class="emailreplies label"><input type="checkbox" id="reply_subscribe" name="reply_topic_subscribe"';
					if ($subscribed_count > 0) { echo 'checked'; } 
					echo '> '.$language->wir.'</div>';
					echo '<input type="submit" class="button" style="float: left" value="'.$language->rep.'" />';
					echo '</form>';
				echo '</div>';
			
			}
		}
	
		// Notices
		echo "<div class='notice'><img src='".$plugin."busy.gif' /> ".$language->sav."</div>";
		echo "<div class='pleasewait'><img src='".$plugin."busy.gif' /> ".$language->pw."</div>";
				
		// End Wrapper
		echo "</div>";
		
		// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
		echo "<div style='width:100%;font-style:italic; font-size: 10px;text-align:center;'>Forum powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress</div>";

	} // End of language check

}

/* ====================================================== PHP FUNCTIONS ====================================================== */

// Send email
function sendmail($email, $subject, $msg)
{
	global $wpdb;
	
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

// How long ago in English
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
    case "ENG":
	    	$retval .= " ago";
        	break;    
    case "FR":
    		$retval = str_replace("second", "seconde", $retval);
    		$retval = str_replace("hour", "heure", $retval);
    		$retval = str_replace("day", "jour", $retval);
    		$retval = str_replace("week", "semaine", $retval);
    		$retval = str_replace("month", "mois", $retval);
    		$retval = str_replace("moiss", "mois", $retval);
    		$retval = str_replace("year", "an", $retval);
	    	$retval = "il ya ".$retval;
        	break;    
    }
    return $retval;      
}

/* ====================================================== AJAX FUNCTIONS ====================================================== */

// AJAX function to get topic details for editing
function getEditDetails(){

	global $wpdb;
	
	$tid = $_POST['tid'];	
	
	$details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid); 
	if ($details->topic_subject == '') {
		$parent = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$details->topic_parent); 
		$subject = $parent->topic_subject;
	} else {
		$subject = $details->topic_subject;
	}

	if ($details) {
		echo $subject."|".$details->topic_post."|".$details->topic_parent."|".$details->tid."|".$details->topic_category;
	} else {
		echo "Problem retrieving topic information|Passed Topic ID = ".$tid;
	}
	exit;
}
add_action('wp_ajax_getEditDetails', 'getEditDetails');

// AJAX function to update topic details after editing
function updateEditDetails(){

	global $wpdb;
	
	$tid = $_POST['tid'];	
	$topic_subject = $_POST['topic_subject'];	
	$topic_post = $_POST['topic_post'];	
	$topic_post = str_replace("\n", chr(13), $topic_post);	
	$topic_category = $_POST['topic_category'];
	
	if ($topic_category == "") {
		$topic_category = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
	}

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE topic_parent = ".$tid) );

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_subject = '".$topic_subject."', topic_post = '".$topic_post."', topic_category = ".$topic_category." WHERE tid = ".$tid) );
	
	$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_parent FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
	echo $parent;
	
	exit;
}
add_action('wp_ajax_updateEditDetails', 'updateEditDetails');

// AJAX function to subscribe/unsubscribe to symposium topic
function updateForum(){

	global $wpdb, $current_user;
	wp_get_current_user();
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
add_action('wp_ajax_updateForum', 'updateForum');

// AJAX function to change sticky status
function updateForumSticky(){

	global $wpdb;
	$topics = $wpdb->prefix . 'symposium_topics';

	$tid = $_POST['tid'];
	$value = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("UPDATE ".$topics." SET topic_sticky = ".$value." AND tid = ".$tid);
	
	if ($value==1) {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}
add_action('wp_ajax_updateForumSticky', 'updateForumSticky');

// AJAX function to subscribe/unsubscribe to new symposium topics
function updateForumSubscribe(){

	global $wpdb, $current_user;
	wp_get_current_user();
	$subs = $wpdb->prefix . 'symposium_subs';

	$action = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = 0");
	
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
	        	0
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
add_action('wp_ajax_updateForumSubscribe', 'updateForumSubscribe');

// Add jQuery and jQuery scripts
function forum_init() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
	}
}
add_action('init', 'forum_init');

// Add Stylesheet
function add_symposium_stylesheet() {
    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/symposium.css';
    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/symposium.css';
    if ( file_exists($myStyleFile) ) {
        wp_register_style('symposium_StyleSheet', $myStyleUrl);
        wp_enqueue_style( 'symposium_StyleSheet');
    } else {
	    wp_die( __('Stylesheet ('.$myStyleFile.' not found.') );
    }
    
}
add_action('wp_print_styles', 'add_symposium_stylesheet');

/* ====================================================== SET SHORTCODE ====================================================== */

add_shortcode('symposium-forum', 'symposium_forum');  


?>
