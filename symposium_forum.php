<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 0.1.19
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

function symposium_forum() {	

	global $wpdb, $current_user;

	$plugin_dir = 'wp-symposium';
	$plugin = get_site_url().'/wp-content/plugins/'.$plugin_dir.'/';
	$thispage = get_permalink();
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));

	$seo = $wpdb->get_var($wpdb->prepare("SELECT seo FROM ".$wpdb->prefix . 'symposium_config'));
	
	$dbpage = WP_PLUGIN_URL.'/'.$plugin_dir.'/symposium_forum_db.php';
	
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$thispage = $forum_url;
		$q = "&";
	} else {
		$q = "?";
	}

	$html = "";
	
	wp_get_current_user();
	$users = $wpdb->prefix . 'users';
	$config = $wpdb->prefix . 'symposium_config';
	$topics = $wpdb->prefix . 'symposium_topics';
	$subs = $wpdb->prefix . 'symposium_subs';
	$cats = $wpdb->prefix . 'symposium_cats';
	$lang = $wpdb->prefix . 'symposium_lang';	

	// Includes
	include_once('symposium_styles.php');
	include_once('symposium_functions.php');

	// Get user level
	$user_level = symposium_get_current_userlevel();
	
	// Post preview
	$snippet_length = $wpdb->get_var($wpdb->prepare("SELECT preview1 FROM ".$config));
	if ($snippet_length == '') { $snippet_length = '45'; }
	$snippet_length_long = $wpdb->get_var($wpdb->prepare("SELECT preview2 FROM ".$config));
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }
		
	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];
	$language = $get_language['words'];
		
	// Get Topic ID and Category ID for use in jQuery functions	
	if (isset($_GET['show'])) {
		$show_tid = $_GET['show']*1;
	} else {
		$show_tid = 0;
		if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
	}

	$cat_id = 0;
	if (isset($_GET['cid'])) { $cat_id = $_GET['cid']; }
	if (isset($_POST['cid'])) { $cat_id = $_POST['cid']; }

	// Javascript and jQuery
	$html .= '<script type="text/javascript">

	function validate_form(thisform)
	{
		form_id = thisform.id;
		if ( (form_id) == "start-new-topic") {
			with (thisform)
			{
				if (new_topic_subject.value == \'\' || new_topic_subject.value == null) {
					jQuery(".new-topic-subject-warning").show("slow");
					new_topic_subject.focus(); 
					return false;
				}
				if (new_topic_text.value == \'\' || new_topic_text.value == null) {
					jQuery(".new_topic_text-warning").show("slow");
					new_topic_text.focus(); 
					return false;
				}
			}
		}
		if ( (form_id) == "start-reply-topic") {
			with (thisform)
			{
				if (reply_text.value == \'\' || reply_text.value == null) {
					jQuery(".reply_text-warning").show("slow");
					reply_text.focus(); 
					return false;
				}
			}
		}
		if ( (form_id) == "quick-reply") {
			with (thisform)
			{
				if (reply_text.value == \'\' || reply_text.value == null) {
					jQuery(".quick-reply-warning").show("slow");
					reply_text.focus(); 
					return false;
				}
			}
		}			
	}

    jQuery(document).ready(function() { 	
    		
    	// Notices	    	
		jQuery(".notice").hide();
		jQuery(".pleasewait").hide();
	    	jQuery(".backto").click(function() {
			jQuery(".pleasewait").inmiddle().show();
	    	});		
		jQuery(".new-topic-subject-warning").hide();
		jQuery(".new_topic_text-warning").hide();
		jQuery(".reply_text-warning").hide();
		jQuery(".quick-reply-warning").hide();
		
		// Centre in screen
		jQuery.fn.inmiddle = function () {
	    	this.css("position","absolute");
	    	this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
	    	this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
		    return this;
		}
		
		// Edit topic (AJAX)
	   	jQuery("#starting-post").hover(function() {
	        jQuery(this).find("#edit-this-topic").show();
	   	}, function() {
	        jQuery(this).find("#edit-this-topic").hide();
	   	});
		// Edit the topic
	   	jQuery("#edit-this-topic").click(function() {
			jQuery(".pleasewait").inmiddle().show();
			jQuery("#new-category-div").show();
	    	var tid = jQuery(".edit-topic-tid").attr("id");	
			jQuery("#edit_topic_subject").val("Please wait...");
			jQuery("#edit_topic_text").html("Retrieving content...");
			jQuery.post("/wp-admin/admin-ajax.php", {
				action:"getEditDetails", 
				\'tid\':'.$show_tid.'
				},
			function(str)
			{
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").removeAttr("disabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery("#new-category").val(details[4]);
			});
			jQuery("#edit-topic-div").inmiddle().fadeIn();
			jQuery(".pleasewait").fadeOut("slow");
	   	});	    	

	   	// Edit a reply
	   	jQuery(".edit-child-topic").click(function() {
			jQuery(".pleasewait").inmiddle().show();
			jQuery("#new-category-div").hide();
	    	var tid = jQuery(this).attr("id");	
			jQuery("#edit_topic_subject").val("Please wait...");
			jQuery("#edit_topic_text").html("Retrieving content...");
			jQuery.post("/wp-admin/admin-ajax.php", {
				action:"getEditDetails", 
				\'tid\':tid
				},
			function(str)
			{
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").attr("disabled", "enabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery(".edit-topic-tid").attr("id", details[3]);
			});
			jQuery("#edit-topic-div").inmiddle().fadeIn();
			jQuery(".pleasewait").fadeOut("slow");
	   	});	 
	   	
	   	// Update contents of edit form
		jQuery(".edit_topic_submit").click(function(){
			jQuery(".notice").inmiddle().show();
    		var tid = jQuery(".edit-topic-tid").attr("id");	
    		var parent = jQuery(".edit-topic-parent").attr("id");
			var topic_subject = jQuery("#edit_topic_subject").val();	
			var topic_post = jQuery("#edit_topic_text").val();	
			var topic_category = jQuery("#new-category").val();	
				
			if (parent == 0) {
				jQuery(".topic-post-header").html(topic_subject);
				jQuery(".topic-post-post").html(topic_post.replace(/\n/g, "<br />"));
			}

			jQuery.post("/wp-admin/admin-ajax.php", {
				action:"updateEditDetails", 
				\'tid\':tid,
				\'topic_subject\':topic_subject,
				\'topic_post\':topic_post,
				\'topic_category\':topic_category
				},
			function(tid)
			{
				jQuery(".notice").fadeOut("fast");
				jQuery("#edit-topic-div").fadeOut("fast");
				window.location.href=window.location.href;
			});
		});
		// Cancel form
		jQuery(".edit_topic_cancel").click(function(){
			jQuery("#edit-topic-div").fadeOut("fast");
	   	});

		// Show delete link on row hover
	    jQuery(".row").hover(function() {
	        jQuery(this).find(".delete").show()
	    }, function() {
	        jQuery(this).find(".delete").hide();
	    });
	    jQuery(".row_odd").hover(function() {
	        jQuery(this).find(".delete").show()
	    }, function() {
	        jQuery(this).find(".delete").hide();
	    });	    
	    jQuery(".child-reply").hover(function() {
	        jQuery(this).find(".delete").show();
	        jQuery(this).find(".edit").show();
	    }, function() {
	        jQuery(this).find(".delete").hide();
	        jQuery(this).find(".edit").hide();
	    });
	    
	    // Check if really want to delete	    
		jQuery(".delete").click(function(){
		  var answer = confirm("Are you sure?");
		  return answer // answer is a boolean
		});

		// Show new topic and reply topic forms
		jQuery("#new-topic-link").click(function() {
		  	jQuery("#new-topic").toggle("slow");
		});
		jQuery("#cancel_post").click(function() {
		  	jQuery("#new-topic").hide("slow");
		});

		jQuery("#reply-topic-link").click(function() {
		  	jQuery("#reply-topic").toggle("slow");
		});
		jQuery("#cancel_reply").click(function() {
		  	jQuery("#reply-topic").hide("slow");
		});
		
		// Has a checkbox been clicked? If so, check if one for symposium (AJAX)
	    jQuery("input[type=\'checkbox\']").bind("click",function() {
	    	
	    	var checkbox = jQuery(this).attr("id");		    		
	    	
	    	// Subscribe to New Forum Topics in a category
	    	if (checkbox == "symposium_subscribe") {
				jQuery(".notice").inmiddle().fadeIn();
		        if(jQuery(this).is(":checked")) {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForumSubscribe", 
						\'cid\':'.$cat_id.',
						"value":1
						},
					function(str)
					{
					      // Subscribed
					});
		        } else {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForumSubscribe", 
						\'cid\':'.$cat_id.',
						"value":0
						},
					function(str)
					{
				      // Un-subscribed
					});
		        }
				jQuery(".notice").delay(100).fadeOut("slow");
	    	}

	    	// Subscribe to Topic Posts
	    	if (checkbox == "subscribe") {
				jQuery(".notice").inmiddle().fadeIn();
		        if(jQuery(this).is(":checked")) {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForum", 
						\'tid\':'.$show_tid.', 
						\'value\':1
						},
					function(str)
					{
						// Subscribed
					});
		        } else {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForum", 
						\'tid\':'.$show_tid.', 
						\'value\':0
						},
					function(str)
					{
					      // Un-subscribed
					});
		        }
				jQuery(".notice").delay(100).fadeOut("slow");
	    	}
	    	
	    	// Sticky Topics
	    	if (checkbox == "sticky") {
				jQuery(".notice").inmiddle().fadeIn();
		        if(jQuery(this).is(":checked")) {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForumSticky", 
						\'tid\':'.$show_tid.', 
						\'value\':1
						},
					function(str)
					{
					      // Stuck
					});
					
		        } else {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateForumSticky", 
						\'tid\':'.$show_tid.', 
						\'value\':0
						},
					function(str)
					{
					      // Unstuck
					});
		        }
				jQuery(".notice").delay(100).fadeOut("slow");
	    	}
	    			    	
	    	// Digest
	    	if (checkbox == "symposium_digest") {
				jQuery(".notice").inmiddle().fadeIn();
		        if(jQuery(this).is(":checked")) {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateDigest", 
						\'value\':\'on\'
						},
					function(str)
					{
					      // Subscribed
					});
					
		        } else {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateDigest", 
						\'value\':\'\'
						},
					function(str)
					{
					      // Unsubscribed
					});
		        }
				jQuery(".notice").delay(100).fadeOut("slow");
	    	}
	    		
	    	// Replied
	    	if (checkbox == "replies") {
				jQuery(".notice").inmiddle().fadeIn();
		        if(jQuery(this).is(":checked")) {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateTopicReplies", 
						\'tid\':'.$show_tid.', 
						\'value\':\'on\'
						},
					function(str)
					{
					      // Replies
					});
					
		        } else {
					jQuery.post("/wp-admin/admin-ajax.php", {
						action:"updateTopicReplies", 
						\'tid\':'.$show_tid.', 
						\'value\':\'\'
						},
					function(str)
					{
					      // No replies
					});
		        }
				jQuery(".notice").delay(100).fadeOut("slow");
	    	}

		});
					

    });

 
	</script>
	';
	
	// Wrapper
	$html .= "<div id='symposium-wrapper' style='z-index:900000;'>";

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
			
	// any page id
	if(!isset($_GET['show'])){
		$topic = '0';
	} else {
		$topic = $_GET['show'];
	}
	
	// error message?
	if ($msg != '') {
		$html .= '<div class="warning">'.$msg.'</div>';
	}
	
	// Get Topic ID if applicable

	$show = '';
	if ($_GET['show'] != '') { $show = $_GET['show']; }
	if ($_POST['show'] != '') { $show = $_POST['show']; }
	if ($tid != '') { $show = $tid; }
		
	$html .= "<div style='clear:both' class='floatright'>";
	if ($cat_id > 0) {
		if ( ($cat_id > 0) && ($show != '') ) {
			$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
			$html .= "<a class='backto label' href='".$thispage.$q."cid=".$cat_id."'>".$language->bt." ".stripslashes($category_title)."...</a>&nbsp;&nbsp;&nbsp;&nbsp;";
		}
	}
	$html .= "&nbsp;&nbsp;<a class='backto label' href='".get_permalink()."'>".$language->btf."...</a>";
	$html .= "</div>";


	// SHOW FORUM ***************************************************************************************************
	$show_forum = false;
	$viewer = $wpdb->get_var($wpdb->prepare("SELECT viewer FROM ".$config));
	if ($viewer == "Guest") { $show_forum = true; }
	if ($viewer == "Subscriber" && $user_level >= 1) { $show_forum = true; }
	if ($viewer == "Contributor" && $user_level >= 2) { $show_forum = true; }
	if ($viewer == "Author" && $user_level >= 3) { $show_forum = true; }
	if ($viewer == "Editor" && $user_level <= 4) { $show_forum = true; }
	if ($viewer == "Administrator" && $user_level >= 5) { $show_forum = true; }
	
	if ($show_forum) {
		if (is_user_logged_in()) {
			
			// Sub Menu for Logged in User
				$html .= "<ul id='topic-links'>";
			if ($show == '') {
				$allow_new = $wpdb->get_var($wpdb->prepare("SELECT allow_new FROM ".$cats." WHERE cid=".$cat_id));
				if ( ($cat_id == '' || $allow_new == "on") || (current_user_can('level_10')) ) {
					$html .= "<li id='new-topic-link'>".$language->sant."</li>";
				} else {
					$html .= "<div style='height:30px'></div>";
				}
			} else {
				if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$show_tid)) == "on") {
					$html .= "<li id='reply-topic-link'>".$language->aar."</li>";
				} else {
					$html .= '<p class="label"><img src="'.$plugin.'padlock.gif" alt="Replies locked" /> Replies are not allowed for this topic.</p>';

				}
			}
			$html .= "</ul>";
			
			// New Topic Form	
			$html .= '<div name="new-topic" id="new-topic"';
				if ($edit_new_topic == false) { $html .= ' style="display:none;"'; } 
				$html .= '>';
				$html .= '<form id="start-new-topic" onsubmit="return validate_form(this)" action="'.$dbpage.'" method="post">';
				$html .= '<div><input type="hidden" name="action" value="post">';
				$html .= '<input type="hidden" name="url" value="'.$thispage.$q.'">';
				$html .= '<input type="hidden" name="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.$language->ts.'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" name="new_topic_subject" value="';
				$html .= ($new_topic_subject); 
				$html .= '"></div>';
				$html .= '<div class="new-topic-subject-warning warning">'.$language->prs.'.</div>';
				$html .= '<div><div class="new-topic-subject label">'.$language->fpit.'</div>';
				$html .= '<textarea class="new-topic-subject-text" name="new_topic_text">';
				$html .= ($new_topic_text);
				$html .= '</textarea></div>';
				$html .= '<div class="new_topic_text-warning warning">'.$language->prm.'</div>';
				$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));
				if ($show_categories == "on") {
					$html .= '<div class="new-topic-category label">'.$language->sac.': ';
					if (current_user_can('level_10')) {
						$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
					} else {
						$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY listorder');			
					}
					if ($categories) {
						$html .= '<select name="new_topic_category" style="width: 200px">';
						foreach ($categories as $category) {
							$html .= '<option value='.$category->cid;
							if ($cat_id > 0) {
								if ($category->cid == $cat_id) { $html .= " SELECTED"; }
							} else {
								if ($category->cid == $defaultcat) { $html .= " SELECTED"; }
							}
							$html .= '>'.stripslashes($category->title).'</option>';
						}
						$html .= '</select>';
					}
					$html .= '</div>';
				} else {
					$html .= '<input type="hidden" name="new_topic_category" value="0" />';
				}
				$html .= '<div class="emailreplies label"><input type="checkbox" name="new_topic_subscribe"';
				if ($new_topic_subscribe != '') { $html .= 'checked'; } 
				$html .= '> '.$language->emw.'</div>';
				$html .= '<input type="submit" class="button" style="float: left" value="'.$language->p.'" />';
				$html .= '</form>';
				$html .= '<input id="cancel_post" type="submit" class="button" onClick="javascript:void(0)" style="float: left" value="'.$language->c.'" />';
			$html .= '</div>';
			$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$show));
			// Reply Form
			if ($show != '' && $allow_replies=="on") {
				$html .= '<div id="reply-topic" name="reply-topic" style="display:none;">';
					$html .= '<form id="start-reply-topic" action="'.$dbpage.'" onsubmit="return validate_form(this)" method="post">';
					$html .= '<input type="hidden" name="action" value="reply">';
					$html .= '<input type="hidden" name="url" value="'.$thispage.$q.'">';
					$html .= '<input type="hidden" name="tid" value="'.$show.'">';
					$html .= '<input type="hidden" name="cid" value="'.$cat_id.'">';
					$html .= '<div class="reply-topic-subject label">'.$language->rtt.'</div>';
					$html .= '<textarea class="reply-topic-subject-text" name="reply_text"></textarea>';
					$html .= '<div class="reply_text-warning warning">'.$language->prm.'</div>';
					$html .= '<div class="emailreplies label"><input type="checkbox" name="reply_topic_subscribe"';
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$show." and uid = ".$current_user->ID));
					$subscribed = false;	
					if ($subscribed_count > 0) { $html .= ' checked'; $subscribed = true; } 
					$html .= '> '.$language->wir.'</div>';
					$html .= '<input type="submit" class="button" style="float: left" value="'.$language->reb.'" />';
					$html .= '</form>';
					$html .= '<input id="cancel_reply" type="submit" class="button" onClick="javascript:void(0)" style="float: left" value="'.$language->c.'" />';
				$html .= '</div>';
			}
			
				
		} else {
	
			$html .= "Until you <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='Login'>login</a>, you can only view the forum.";
			$html .= "<br />";
	
		}
	
		if ($show == '') {
					
			// Show Forum ***************************************************************************************************
			
			// Forum Subscribe
			if (is_user_logged_in()) {
				
				$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix . 'symposium_config'));
				if ($send_summary == "on") {
					$forum_digest = get_symposium_meta($current_user->ID, 'forum_digest');
					$html .= "<div class='symposium_subscribe_option label'>";
					$html .= "<input type='checkbox' id='symposium_digest' name='symposium_digest'";
					if ($forum_digest == 'on') { $html .= ' checked'; } 
					$html .= "> ".$language->rdv;
					$html .= "</div><br />";
				}
				if ($cat_id > 0) {
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = 0 AND cid = ".$cat_id." AND uid = ".$current_user->ID));
					$html .= "<div class='symposium_subscribe_option label'>";
					$html .= "<input type='checkbox' id='symposium_subscribe' name='symposium_subscribe'";
					if ($subscribed_count > 0) { $html .= ' checked'; } 
					$html .= "> ".$language->rew;
					$html .= "</div>";
				}
			}	
		  
			// Start of table
			$html .= '<div id="symposium_table">';
		
			// Top level (categories)
			$use_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
			
			if ( ($use_categories == "on") && ($cat_id == 0) ) {
	
				$html .= "<div class='table_header'>";
				$html .= "<div class='table_topic'>".$language->cat."</div>";
				$html .= "</div>";
				
				$categories = $wpdb->get_results("SELECT * FROM ".$cats." ORDER BY listorder");
				
				$num_cats = $wpdb->num_rows;
				$cnt = 0;
				foreach($categories as $category) {
					$cnt++;
					if ($cnt&1) {
						$html .= '<div class="row ';
						if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
						$html .= '">';
					} else {
						$html .= '<div class="row_odd ';
						if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
						$html .= '">';
					}
						// Last Topic
						$last_topic = $wpdb->get_row("
							SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, topic_sticky, topic_parent, display_name, topic_category 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = 0 AND topic_category = ".$category->cid." ORDER BY topic_date DESC"); 
						$html .= "<div class='row_topic row_startedby'>";
						if ($last_topic) {
							$reply = $wpdb->get_row("
								SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_category 
								FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
								WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = ".$last_topic->tid." ORDER BY topic_date DESC"); 
											
								if ($reply) {
									$html .= "<div class='avatar' style='margin-right:0px;margin-bottom:0px; padding-bottom: 0px;'>";
										$html .= get_avatar($reply->topic_owner, 32);
									$html .= "</div>";
									$html .= symposium_profile_link($reply->topic_owner)." ".$language->re." ".$language->too." ";
									$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($last_topic->topic_subject).'</a> ';
									$html .= symposium_time_ago($reply->topic_date, $language_key).".";
									if ($reply->topic_approved != 'on') { $html .= " <em>[".$language->pen."]</em>"; }
								} else {
									$html .= "<div class='avatar' style='margin-right:0px;margin-bottom:0px; padding-bottom: 0px;'>";
										$html .= get_avatar($last_topic->topic_owner, 32);
									$html .= "</div>";
									$html .= symposium_profile_link($last_topic->topic_owner)." ".$language->st." ";
									$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($last_topic->topic_subject).'</a> ';
									$html .= symposium_time_ago($last_topic->topic_date, $language_key).".";
								}
	
						}
						$html .= "</div>";
						
						// Posts
						$html .= "<div class='row_views row_views'>";
						$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$topics." t INNER JOIN ".$topics." u ON u.topic_parent = t.tid WHERE t.topic_parent = 0 AND (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND t.topic_category = ".$category->cid));

						if ($post_count) { 
							$html .= "<div class='row_link' style='color:".$text_color."; margin-top:4px;font-weight: bold;'>".$post_count."</div>";
							$html .= "<div style='color:".$text_color."; margin-top:-4px;font-size:8px;'>";
							if ($post_count > 1) {
								$html .= $language->tps;
							} else {
								$html .= $language->tp;
							}
							$html .= "</div>";
						}
						$html .= "</div>";

						// Topic Count
						$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = 0 AND topic_category = ".$category->cid));
						$html .= "<div class='row_topic row_replies'>";
						$html .= "<div class='row_link' style='color:".$text_color."; margin-top:4px;font-weight: bold;'>".$topic_count."</div>";
						$html .= "<div style='color:".$text_color."; margin-top:-4px;font-size:8px;'>";
						if ($topic_count != 1) {
							$html .= $language->top;
						} else {
							$html .= $language->t;
						}
						$html .= "</div>";
						$html .= "</div>";

						// Category title
						$html .= '<div style="padding-left:8px;padding-top:13px">';
						$html .= '<a class="backto row_link" href="'.$thispage.symposium_permalink($category->cid, "category").$q.'cid='.$category->cid.'">'.stripslashes($category->title).'</a>';
						$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE cid = ".$category->cid." AND uid = ".$current_user->ID));
						if ($subscribed > 0) { $html .= ' <img src="'.$plugin.'orange-tick.gif" alt="Subscribed" />'; } 
						$html .= '</div>';

												
						// Separator
						$html .= "<div class='sep'></div>";											

					$html .= "</div>"; // Row in the table

				}
	
	
			}
			
			// Topic level
			if ( ($use_categories != "on") || ($cat_id > 0) ) {
	
				$html .= "<div class='table_header'>";
				if ($use_categories == "on") {
					$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
					$html .= "<div class='table_topic'><a style='color:".$categories_color."; text-decoration:none;' href='".get_permalink()."'>".stripslashes($category_title)."</a></div>";
				} else {
					$html .= "<div class='table_topic'>".stripslashes($language->t)."</div>";
				}
				$html .= "</div>";

				// Get Forums	
				if ($use_categories == "on") {
						
					$query = $wpdb->get_results("
						SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, allow_replies 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = 0 AND topic_category = ".$cat_id." ORDER BY topic_sticky DESC, topic_date DESC"); 
						
				} else {
					
					$query = $wpdb->get_results("
						SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, allow_replies 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = 0 ORDER BY topic_sticky DESC, topic_date DESC"); 
						
				}
	
				$num_topics = $wpdb->num_rows;
					
				if ($query) {
				
					$row_cnt=0;
				
					foreach ($query as $topic) {
					
						$row_cnt++;
						
						$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = ".$topic->tid));
						$reply_views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND tid = ".$topic->tid));
								
						if ($row_cnt&1) {
							$html .= '<div class="row ';
							if ($row_cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
						} else {
							$html .= '<div class="row_odd ';
							if ($row_cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
						}
						$closed_word = strtolower($wpdb->get_var($wpdb->prepare("SELECT closed_word FROM ".$config)));
						if ( strpos(strtolower($topic->topic_subject), "[".$closed_word."]") > 0) {
							$color_check = ' transparent';
						} else {
							$color_check = '';
						}
						$html .= $color_check.'">';

							// Started by/Last Reply
							$html .= "<div class='row_startedby' style='float:right;'>";
							$last_post = $wpdb->get_row("
								SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky 
								FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
								WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = ".$topic->tid." ORDER BY tid DESC"); 
							if ( $last_post ) {
								$html .= "<div class='avatar' style='margin-bottom:0px; margin-right: 0px;'>";
									$html .= get_avatar($last_post->topic_owner, 32);
								$html .= "</div>";
								$html .= $language->lrb." ".symposium_profile_link($last_post->topic_owner);
								$html .= " ".symposium_time_ago($topic->topic_date, $language_key).".";
								$post = stripslashes($last_post->topic_post);
								if ( strlen($post) > $snippet_length_long ) { $post = substr($post, 0, $snippet_length_long)."..."; }
								$html .= "<br /><span class='row_topic_text'>".$post."</span>";
								if ($last_post->topic_approved != 'on') { $html .= " <em>[".$language->pen."]</em>"; }
							} else {
								$html .= "<div class='avatar' style='margin-bottom:0px; margin-right: 0px;'>";
									$html .= get_avatar($topic->topic_owner, 32);
								$html .= "</div>";
								$html .= $language->sb." ".symposium_profile_link($topic->topic_owner);
								$html .= " ".symposium_time_ago($topic->topic_date, $language_key).".";
							}
							$html .= "</div>";
							
							// Views
							$html .= "<div class='row_views'>";
							if ($reply_views) { 
								$html .= "<div class='row_link' style='color:".$text_color."; margin-top:4px;font-weight: bold;'>".$reply_views."</div>";
								$html .= "<div style='color:".$text_color."; margin-top:-4px;font-size:8px;'>".$language->v."</div>";
							}
							$html .= "</div>";
							
							// Replies
							$html .= "<div class='row_replies'>";
							$html .= "<div class='row_link' style='color:".$text_color."; margin-top:4px;font-weight: bold;'>".$replies."</div>";
							$html .= "<div style='color:".$text_color."; margin-top:-4px;font-size:8px;'>";
							if ($replies != 1) {
								$html .= $language->r;
							} else {
								$html .= $language->rep;
							}
							$html .= "</div>";
							$html .= "</div>";

							// Topic Title		
							$html .= "<div class='row_topic' style='padding:10px'>";
							$html .= '<div class="row_link_div"><a href="'.$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$cat_id.'&show='.$topic->tid.'" class="backto row_link">'.stripslashes($topic->topic_subject).'</a>';
							if ($topic->topic_approved != 'on') { $html .= " <em>[".$language->pen."]</em>"; }
							if (is_user_logged_in()) {
								$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$topic->tid." AND uid = ".$current_user->ID));
								if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'pin.gif" alt="Sticky Topic" />'; } 
							
							// Delete link if applicable
							if (current_user_can('level_10')) {
								$html .= " <a class='delete' href='".$thispage.$q."show=".$show."&cid=".$cat_id."&action=deltopic&tid=".$topic->tid."'>".$language->d."</a>";
							}

							$html .= "</div>";
							$post = stripslashes($topic->topic_post);
							if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
							$html .= "<span class='row_topic_text'>".$post."</span>";
							$html .= "</div>";
														
							// Separator
							$html .= "<div class='sep'></div>";											
	
						$html .= "</div>"; // End of Table Row
						
					}
				
				} else {
				
					$html .= "<div style='padding: 6px'>".$language->nty."</div>";
				
				}

			}
		
			$html .= "</div>"; // End of table
			
		} else {
			
			// Show topic ***************************************************************************************************
			
			$post = $wpdb->get_row("
				SELECT tid, topic_subject, topic_approved, topic_post, topic_started, display_name, topic_sticky, topic_owner 
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND tid = ".$show);
				
			if ($post) {
			
				// Edit Form
				$html .= '<div id="edit-topic-div" class="shadow">';
					$html .= '<div class="new-topic-subject label">'.$language->ts.'</div>';
					$html .= '<div id="'.$post->tid.'" class="edit-topic-tid"></div>';
					$html .= '<div id="" class="edit-topic-parent"></div>';
					$html .= '<input class="new-topic-subject-input" id="edit_topic_subject" type="text" name="edit_topic_subject" value="">';
					$html .= '<div class="new-topic-subject label">'.$language->tt.'</div>';
					$html .= '<textarea class="new-topic-subject-text" id="edit_topic_text" name="edit_topic_text"></textarea>';
					$html .= '<div id="new-category-div" style="float:left">'.$language->mc.': <select name="new-category" id="new-category" style="width: 200px">';
					$html .= '<option value="">'.$language->s.'</option>';
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
					if ($categories) {
						foreach ($categories as $category) {
							if ($category->allow_new == "on" || current_user_can('level_10')) {
								$html .= '<option value='.$category->cid.'>'.stripslashes($category->title).'</option>';
							}
						}
					}
					$html .= '</select></div>';
					$html .= '<div style="float:right; margin-right:15px;">';
					$html .= '<input type="submit" class="button edit_topic_submit" value="'.$language->u.'" />';
					$html .= '<input type="submit" class="button edit_topic_cancel" value="'.$language->c.'" />';
					$html .= '</div>';
				$html .= '</div>';
				
				$html .= "<div id='starting-post'>";
				
				if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
					$html .= "<div id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".$language->e."</div>";
				}

				$html .= "<div id='top_of_first_post' style='height:80px'>";
				$html .= "<div class='avatar' style='margin-bottom:0px'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
				
				$html .= "<div class='topic-post-header'>".stripslashes($post->topic_subject);
				if ($post->topic_approved != 'on') { $html .= " <em>[".$language->pen."]</em>"; }
				$html .= "</div>";					
				$html .= "<div class='started-by'>".$language->sb." ".symposium_profile_link($post->topic_owner)." ".symposium_time_ago($post->topic_started, $language_key)."</div>";
				$html .= "</div>";

				$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", stripslashes($post->topic_post))."</div>";
				
				$html .= "</div>";
	
				// Update views
				if ($user_level == 5) {
					if ($wpdb->get_var($wpdb->prepare("SELECT include_admin FROM ".$wpdb->prefix.'symposium_config')) == "on") { 
						$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = ".$post->tid) );
					}
				} else {
					$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = ".$post->tid) );
				}
									
				// Subscribe	
				if (is_user_logged_in()) {
	
					$html .= "<br /><div class='floatright label'>";
					$html .= "<form action='symposium.php'>";
					$html .= "<input type='checkbox' id='subscribe' name='subscribe'";
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$show." and uid = ".$current_user->ID));
					if ($subscribed_count > 0) { $html .= ' checked'; } 
					$html .= "> ".$language->rer;
					if (current_user_can('level_10')) {
						$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' id='sticky' name='sticky'";
						if ($post->topic_sticky > 0) { $html .= ' checked'; }
						$html .= "> ".$language->tis;
						$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' id='replies' name='replies'";
						$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$post->tid));
						if ($allow_replies == "on") { $html .= ' checked'; }
						$html .= "> ".$language->ar;
					}
					$html .= "</form>";
					
					$html .= "</div>";
				} else {
					$html .= "<br />";
				}
			
			}
	
			// Replies
			$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND topic_parent = ".$show." ORDER BY tid";
			if ($wpdb->get_var($wpdb->prepare("SELECT oldest_first FROM ".$wpdb->prefix.'symposium_config')) != "on") { $sql .= " DESC"; }
			
			$child_query = $wpdb->get_results($sql);
	
			if ($child_query) {
	
				$html .= "<div id='child-posts'>";
				
				foreach ($child_query as $child) {
	
					$html .= "<div class='child-reply'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<div style='float:right;padding-top:6px;'><a class='delete' href='".$thispage.$q."show=".$show."&cid=".$cat_id."&action=del&tid=".$child->tid."'>".$language->d."</a></div>";
							$html .= "<div id='".$child->tid."' class='edit-child-topic edit_topic edit label' style='cursor:pointer;'>".$language->e."&nbsp;&nbsp;|&nbsp;&nbsp;</div>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>".symposium_profile_link($child->topic_owner)." ".$language->re." ".symposium_time_ago($child->topic_date, $language_key)."...";
						$html .= "</div>";
						$html .= "<div id='".$child->tid."' class='child-reply-post'>";
							$html .= "<p>".str_replace(chr(13), "<br />", stripslashes($child->topic_post));
							if ($child->topic_approved != 'on') { $html .= " <em>[".$language->pen."]</em>"; }
							$html .= "</p>";
						$html .= "</div>";
					$html .= "</div>";

					// Separator
					$html .= "<div class='sep'></div>";						
	
				}
				
				$html .= "</div>";
				
			}				
			
			// Quick Reply
			if (is_user_logged_in()) {
				$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
				if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$post->tid)) == "on")
				{
					$html .= '<form id="quick-reply" action="'.$dbpage.'" onsubmit="return validate_form(this)" method="post">';
					$html .= '<input type="hidden" name="action" value="reply">';
					$html .= '<input type="hidden" name="url" value="'.$thispage.$q.'">';
					$html .= '<input type="hidden" name="tid" value="'.$show.'">';
					$html .= '<input type="hidden" name="cid" value="'.$cat_id.'">';
					$html .= '<div class="reply-topic-subject label">'.$language->rtt.'</div>';
					$html .= '<textarea class="reply-topic-text" name="reply_text"></textarea>';
					$html .= '<div class="quick-reply-warning warning">'.$language->prm.'</div>';
					$html .= '<div class="emailreplies label"><input type="checkbox" id="reply_subscribe" name="reply_topic_subscribe"';
					if ($subscribed_count > 0) { $html .= 'checked'; } 
					$html .= '> '.$language->wir.'</div>';
					$html .= '<input type="submit" class="button" style="float: left" value="'.$language->reb.'" />';
					$html .= '</form>';
				}				
				$html .= '</div>';
			}
		}
	
		// Notices
		$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->sav."</div>";
		$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin."busy.gif' /> ".$language->pw."</div>";

	} else {
		
		if ($viewer == "Subscriber") {
			$html .= "<p>Sorry, this forum can only be used by registered members. :(</p>";
		} else {
			$html .= "<p>Sorry, the minimum user level for this forum is ".$viewer.". :(</p>";
		}
	}				
	// End Wrapper
	$html .= "</div>";
	
	// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
	$html .= "<div style='width:100%;font-style:italic; font-size: 10px;text-align:center;'>Forum powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress, ".get_option("symposium_version")."</div>";

	// Send HTML
	return $html;

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
		echo stripslashes($subject)."[split]".stripslashes($details->topic_post)."[split]".$details->topic_parent."[split]".$details->tid."[split]".$details->topic_category;
	} else {
		echo "Problem retrieving topic information[split]Passed Topic ID = ".$tid;
	}
	exit;
}
add_action('wp_ajax_getEditDetails', 'getEditDetails');

// AJAX function to update topic details after editing
function updateDigest(){
	global $wpdb, $current_user;
	wp_get_current_user();

	$value = $_POST['value'];	

	// Update meta record exists for user
	update_symposium_meta($current_user->ID, "forum_digest", "'".$value."'");
	echo $value;
	exit;

}
add_action('wp_ajax_updateDigest', 'updateDigest');

// AJAX function to update topic details after editing
function updateEditDetails(){

	global $wpdb;
	
	$tid = $_POST['tid'];	
	$topic_subject = addslashes($_POST['topic_subject']);	
	$topic_post = addslashes($_POST['topic_post']);	
	$topic_post = str_replace("\n", chr(13), $topic_post);	
	$topic_category = $_POST['topic_category'];

	// Log
	symposium_audit(array ('code'=>52, 'type'=>'info', 'plugin'=>'forum', 'tid'=>$tid, 'cid'=>$topic_category, 'message'=>'AJAX post update request received.'));
	
	if ($topic_category == "") {
		$topic_category = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
	}

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE topic_parent = ".$tid) );

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_subject = '".$topic_subject."', topic_post = '".$topic_post."', topic_category = ".$topic_category." WHERE tid = ".$tid) );
	
	$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_parent FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));

	// Log
	symposium_audit(array ('code'=>52, 'type'=>'info', 'plugin'=>'forum', 'tid'=>$tid, 'cid'=>$topic_category, 'message'=>'Post updated.'));

	echo $topic_post;
	
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
	$wpdb->query("UPDATE ".$topics." SET topic_sticky = ".$value." WHERE tid = ".$tid);
	
	if ($value==1) {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}
add_action('wp_ajax_updateForumSticky', 'updateForumSticky');

// AJAX function to change sticky status
function updateTopicReplies(){

	global $wpdb;
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
add_action('wp_ajax_updateTopicReplies', 'updateTopicReplies');

// AJAX function to subscribe/unsubscribe to new symposium topics
function updateForumSubscribe(){

	global $wpdb, $current_user;
	wp_get_current_user();
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
		exit;
		
	} else {

		echo 'Sorry - the subscription was not added.';			
		exit;
		// Removed, and not re-added
	}
	
	echo "Sorry - subscription failed";
	exit;

}
add_action('wp_ajax_updateForumSubscribe', 'updateForumSubscribe');

/* ====================================================== ACTIVATE/DEACTIVATE ====================================================== */

function symposium_forum_activate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>5, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Forum activated.'));
	} else {
	    wp_die( __('Core plugin must be actived first.') );
	}

}

function symposium_forum_deactivate() {

	if (function_exists('symposium_audit')) {
		symposium_audit(array ('code'=>6, 'type'=>'info', 'plugin'=>'forum', 'message'=>'Forum de-activated.'));
	}

}

register_activation_hook(__FILE__,'symposium_forum_activate');
register_deactivation_hook(__FILE__, 'symposium_forum_deactivate');

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-forum', 'symposium_forum');  



?>
