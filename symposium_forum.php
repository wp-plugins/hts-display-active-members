<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any page to display forum.
Version: 0.1.6
Author: Simon Goodchild
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

function symposium_forum() {	

	global $wpdb, $current_user;

	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	$thispage = get_permalink();
	$dbpage = WP_PLUGIN_URL.'/wp-symposium/symposium_forum_db.php';
	
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$thispage = "/?page_id=".$_GET['page_id'];
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
	
	$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$config));
	$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
	if (!$language) {
		
		$html .= "<p>Language translation not available for ".$wpdb->get_var($wpdb->prepare("SELECT language FROM ".$config))."</p>";
		
	} else {
		
		// Get Topic ID for use in jQuery functions	
		if (isset($_GET['show'])) {
			$show_tid = $_GET['show']*1;
		} else {
			$show_tid = 0;
			if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
		}

		$html .= '				
		<script type="text/javascript">

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
				jQuery(".pleasewait").hide();
		   	});	    	

		   	// Edit a reply
		   	jQuery(".edit-child-topic").click(function() {
				jQuery(".pleasewait").inmiddle().show();
				jQuery("#new-category-div").hide();
		    	var tid = jQuery(this).attr("id");	
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
				jQuery(".pleasewait").hide();
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
		    	
		    	// Subscribe to New Forum Topics
		    	if (checkbox == "symposium_subscribe") {
					jQuery(".notice").inmiddle().fadeIn();
			        if(jQuery(this).is(":checked")) {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSubscribe", 
							"value":1
							},
						function(str)
						{
						      // Subscribed
						});
			        } else {
						jQuery.post("/wp-admin/admin-ajax.php", {
							action:"updateForumSubscribe", 
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
		    			    	
			});
						

	    });

	 
		</script>
		';
		
		// Get passed variables
		if (isset($_GET['cid'])) { $cat_id = $_GET['cid']; }
		if (isset($_POST['cid'])) { $cat_id = $_POST['cid']; }

		// Include styles	
		include_once('symposium_styles.php');
	
		// Wrapper
		$html .= "<div id='symposium-wrapper'>";
	
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
			
		if (isset($cat_id)) {
			$html .= "<div style='clear:both' class='floatright'>";
			if ( ( (isset($cat_id)) && ($cat_id != '') ) && ($show != '') ) {
				$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
				$html .= "<a class='backto label' href='".$thispage.$q."cid=".$cat_id."'>".$language->bt." ".stripslashes($category_title)."...</a>&nbsp;&nbsp;&nbsp;&nbsp;";
			}
	
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
			$html .= "&nbsp;&nbsp;<a class='backto label' href='".$forum_url."'>".$language->btf."...</a>";
			$html .= "</div>";
		}
	
	
		// Submenu ***************************************************************************************************
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
				$html .= "<li id='reply-topic-link'>".$language->aar."</li>";
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
						$html .= '<select name="new_topic_category">';
						foreach ($categories as $category) {
							$html .= '<option value='.$category->cid;
							if (isset($cat_id)) {
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
			
			if ($show != '') {
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
					if ($subscribed_count > 0) { $html .= 'checked'; $subscribed = true; } 
					$html .= '> Email me when there are more replies to this topic</div>';
					$html .= '<input type="submit" class="button" style="float: left" value="'.$language->rep.'" />';
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
				$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = 0 and uid = ".$current_user->ID));
				
				$html .= "<div class='symposium_subscribe_option label'>";
				$html .= "<input type='checkbox' id='symposium_subscribe' name='symposium_subscribe'";
				if ($subscribed_count > 0) { $html .= ' checked'; } 
				$html .= "> ".$language->rew;
				$html .= "</div>";
			}	
				
			$html .= '<table id="symposium_table" cellspacing=0 cellpadding=6 style="width:100%;border-collapse:inherit;">';
		
			// Loop through all categories
			$use_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
			
			if ( ($use_categories == "on") && (!(isset($cat_id))) ) {
	
				$html .= "<tr class='table_header'>";
				$html .= "<td class='table_topic'>".$language->cat."</td>";
				$html .= "<td class='table_startedby'>".$language->lac."</td>";
				$html .= "<td class='table_topics' style='text-align:center'>".$language->top."</td>";
				$html .= "<td class='table_topics'>".$language->v."</td>";
				$html .= "</tr>";
				
				$categories = $wpdb->get_results("SELECT * FROM ".$cats." ORDER BY listorder");
				
				$num_cats = $wpdb->num_rows;
				$cnt = 0;
				foreach($categories as $category) {
					$cnt++;
					if ($cnt/2 != round($cnt/2)) {
						$html .= '<tr class="row">';
					} else {
						$html .= '<tr class="row_odd">';
					}
					$html .= '<td class="row_topic';
					if ($cnt == $num_cats) {
						$html .= ' round_bottom_left';
					}
					
					$html .= '" valign="top"><a class="backto row_link" href="'.$thispage.permalink($category->cid, "category").$q.'cid='.$category->cid.'">'.stripslashes($category->title).'</a></td>';
					$last_topic = $wpdb->get_row("
						SELECT tid, topic_subject, topic_post, topic_date, topic_sticky, topic_parent, display_name, topic_category 
						FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$category->cid." ORDER BY topic_sticky DESC, topic_date DESC"); 
					$html .= "<td class='row_topic'>";
					if ($last_topic) {
						$reply = $wpdb->get_row("
							SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_category 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE topic_parent = ".$last_topic->tid." ORDER BY topic_date DESC"); 
												
						$html .= '<a class="backto row_link" href="'.$thispage.permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($last_topic->topic_subject).'</a>';
						if ($last_topic->topic_sticky) { $html .= ' <img src="'.$plugin.'pin.gif" alt="Sticky Topic" />'; } 
						$html .= '<br />';
	
						if ($reply) {
							$html .= $reply->display_name." ".$language->re." ".symposium_time_ago($reply->topic_date, $language_key).":<br />";
							$post = stripslashes($reply->topic_post);
						} else {
							$html .= "by ".$last_topic->display_name.", ".symposium_time_ago($last_topic->topic_date, $language_key).":<br />";
							$post = stripslashes($last_topic->topic_post);
						}
						if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
						$html .= "<span class='row_topic_text'>".$post."</span>";
					} else {
						$html .= "&nbsp;";
					}
					$html .= "</td>";
					$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_parent = 0 AND topic_category = ".$category->cid));
					$html .= "<td class='row_topic";
					if ($cnt == $num_cats) {
						$html .= " round_bottom_right";
					}
					$html .= "' style='text-align:center' valign='top'>".$topic_count."</td>";
					$html .= "<td class='row_views";
					if ($row_cnt == $num_topics) {
						$html .= " round_bottom_right";
					}
					$html .= "' valign='top' align='center'>".$wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$topics." WHERE topic_category = ".$category->cid))."</td>";

					$html .= "</tr>";
				}
	
	
			}
			
			if ( ($use_categories != "on") || (isset($cat_id)) ) {
	
				$html .= "<tr class='table_header'>";
				$html .= "<td class='table_topic'>".$language->t."</td>";
				$html .= "<td class='table_startedby'>".$language->sbl."</td>";
				$html .= "<td class='table_freshness'>".$language->f."</td>";
				$html .= "<td class='table_replies'>".$language->r."</td>";
				$html .= "<td class='table_topics'>".$language->v."</td>";
				$html .= "</tr>";
			
				// Get Forums	
				if ($use_categories == "on") {
					$html .= "<tr>";
					$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
					$html .= "<td class='categories_background categories_color' style='border:0' colspan=5>".stripslashes($category_title)."</td>";
					$html .= "</tr>";
						
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
							$html .= "<tr class='row'>";
						} else {
							$html .= "<tr class='row_odd'>";
						}
						$html .= "<td class='row_topic";
						if ($row_cnt == $num_topics) {
							$html .= " round_bottom_left";
						}
						$html .= "'>";
						if (current_user_can('level_10')) {
							$html .= " <a class='delete' href='".$thispage.$q."show=".$show."&cid=".$cat_id."&action=deltopic&tid=".$topic->tid."'>".$language->d."</a>";
						}
						
						$html .= '<div class="row_link_div"><a href="'.$thispage.permalink($topic->tid, "topic").$q.'cid='.$cat_id.'&show='.$topic->tid.'" class="backto row_link">'.stripslashes($topic->topic_subject).'</a>';
						if (is_user_logged_in()) {
							$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = ".$topic->tid." AND uid = ".$current_user->ID));
							if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'orange-tick.gif" alt="Subscribed" />'; } 
						}
						if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'pin.gif" alt="Sticky Topic" />'; } 
						
						$html .= "</div>";
						$post = stripslashes($topic->topic_post);
						if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
						$html .= "<span class='row_topic_text'>".$post."</span></td>";
						$html .= "<td class='row_startedby' valign='top'>";
						$last_post = $wpdb->get_row("
							SELECT tid, topic_subject, topic_post, topic_date, display_name, topic_sticky 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE topic_parent = ".$topic->tid." ORDER BY tid DESC"); 
						if ( $last_post ) {
							$html .= "Last reply by: ".$last_post->display_name."<br />";
							$post = stripslashes($last_post->topic_post);
							if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
							$html .= "<div class='row_topic_text' style='margin-top: 5px'>".$post."</div>";
						} else {
							$html .= $language->sb.": ".$topic->display_name;
						}
						$html .= "</td>";
						$html .= "<td class='row_freshness' valign='top' align='right'>".symposium_time_ago($topic->topic_date, $language_key)."</td>";
						$html .= "<td class='row_replies' valign='top' align='center'>".$replies."</td>";
						$html .= "<td class='row_views";
						if ($row_cnt == $num_topics) {
							$html .= " round_bottom_right";
						}
						$html .= "' valign='top' align='center'>".$views."</td>";
						$html .= "</tr>";
						
					}
				
				} else {
				
					$html .= "<tr>";
					$html .= "<td colspan=5 style='padding: 6px'>No topics yet</td>";
					$html .= "</tr>";			
				
				}
			}
		
			$html .= "</table>";
			
		} else {
			
			// Show topic ***************************************************************************************************
			
			$post = $wpdb->get_row("
				SELECT tid, topic_subject, topic_post, topic_started, display_name, topic_sticky, topic_owner 
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE tid = ".$show);
	
			if ($post) {
			
				// Edit Form
				$html .= '<div id="edit-topic-div" class="shadow">';
					$html .= '<div class="new-topic-subject label">'.$language->ts.'</div>';
					$html .= '<div id="'.$post->tid.'" class="edit-topic-tid"></div>';
					$html .= '<div id="" class="edit-topic-parent"></div>';
					$html .= '<input class="new-topic-subject-input" id="edit_topic_subject" type="text" name="edit_topic_subject" value="">';
					$html .= '<div class="new-topic-subject label">'.$language->tt.'</div>';
					$html .= '<textarea class="new-topic-subject-text" id="edit_topic_text" name="edit_topic_text"></textarea>';
					$html .= '<div id="new-category-div" style="float:left">'.$language->mc.': <select name="new-category" id="new-category">';
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
					$html .= get_avatar($post->post_owner, 64);
				$html .= "</div>";
				
				$html .= "<div class='topic-post-header'>".stripslashes($post->topic_subject)."</div>";					
				$html .= "<div class='started-by'>".$language->sb." ".$post->display_name." ".symposium_time_ago($post->topic_started, $language_key)."</div>";
				$html .= "</div>";

				$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", stripslashes($post->topic_post))."</div>";
				
				$html .= "</div>";
	
				// Update views
				$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = ".$post->tid) );
									
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
					}
					$html .= "</form>";
					
					$html .= "</div>";
				} else {
					$html .= "<br />";
				}
			
			}
	
			// Replies
			$child_query = $wpdb->get_results("
				SELECT tid, topic_subject, topic_post, topic_date, topic_owner, display_name, ID
				FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
				WHERE topic_parent = ".$show." ORDER BY tid");
	
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
						$html .= "<div class='started-by'>".$child->display_name." ".$language->re." ".symposium_time_ago($child->topic_date, $language_key)."...";
						$html .= "</div>";
						$html .= "<div id='".$child->tid."' class='child-reply-post'>";
							$html .= "<p>".str_replace(chr(13), "<br />", stripslashes($child->topic_post))."</p>";
						$html .= "</div>";
					$html .= "</div>";
	
				}
				
				$html .= "</div>";
				
			}				
			
			// Quick Reply
			if (is_user_logged_in()) {
				$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
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
					$html .= '<input type="submit" class="button" style="float: left" value="'.$language->rep.'" />';
					$html .= '</form>';
				$html .= '</div>';
			
			}
		}
	
		// Notices
		$html .= "<div class='notice'><img src='".$plugin."busy.gif' /> ".$language->sav."</div>";
		$html .= "<div class='pleasewait'><img src='".$plugin."busy.gif' /> ".$language->pw."</div>";
				
		// End Wrapper
		$html .= "</div>";
		
		// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
		$html .= "<div style='width:100%;font-style:italic; font-size: 10px;text-align:center;'>Forum powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress, ".get_option("symposium_version")."</div>";

		// Send HTML
		return $html;
		
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

// Create Permalink
function permalink($id, $type) {

	global $wpdb;
	
	if ($_GET['page_id'] != '') {
		
		// Not using Permalinks
		return "";
		
	} else {
	
		if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on")
		
		if ($type == "category") {
			$info = $wpdb->get_row("
				SELECT title FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$id); 
			$string = stripslashes($info->title);
		} else {
			$info = $wpdb->get_row("
				SELECT topic_subject, title FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'symposium_cats'." ON ".$wpdb->prefix.'symposium_topics'.".topic_category = ".$wpdb->prefix.'symposium_cats'.".cid WHERE tid = ".$id); 
			$string = $info->topic_subject;
			if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on") {
				$string = stripslashes($info->title)."/".stripslashes($string);
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
		
		return $id."/".$string;
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
    case "English":
	    	$retval .= " ago";
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
		echo stripslashes($subject)."[split]".stripslashes($details->topic_post)."[split]".$details->topic_parent."[split]".$details->tid."[split]".$details->topic_category;
	} else {
		echo "Problem retrieving topic information[split]Passed Topic ID = ".$tid;
	}
	exit;
}
add_action('wp_ajax_getEditDetails', 'getEditDetails');

// AJAX function to update topic details after editing
function updateEditDetails(){

	global $wpdb;
	
	$tid = $_POST['tid'];	
	$topic_subject = $_POST['topic_subject'];	
	$topic_subject = str_replace("\\", "\\\\", $topic_subject);	
	$topic_post = $_POST['topic_post'];	
	$topic_post = str_replace("\n", chr(13), $topic_post);	
	$topic_post = str_replace("\\", "\\\\", $topic_post);	
	$topic_post = str_replace("'", "\'", $topic_post);	
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
        wp_enqueue_style('symposium_StyleSheet');
    } else {
	    wp_die( __('Stylesheet ('.$myStyleFile.' not found.') );
    }
    
}
add_action('wp_print_styles', 'add_symposium_stylesheet');

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-forum', 'symposium_forum');  


?>
