<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 0.1.22
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
	
	// Check for delete post (admin and owner only)
	if ($_GET['action'] == 'del') {	
		// get owner
		$post_owner = $wpdb->get_var($wpdb->prepare("SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid=".$_GET['tid']));
		// delete if you can
		if ( (current_user_can('level_10')) || ($current_user->ID == $post_owner) ) {
			$wpdb->query("DELETE FROM ".$topics." WHERE tid = ".$_GET['tid']);
		}
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
					$html .= '<p class="label"><img src="'.$plugin.'images/padlock.gif" alt="Replies locked" /> Replies are not allowed for this topic.</p>';

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

			if ($show != '') {
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
						if ($subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
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
								if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
							
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
		$html .= "<div class='notice' style='z-index:999999;'><img src='".$plugin."images/busy.gif' /> ".$language->sav."</div>";
		$html .= "<div class='pleasewait' style='z-index:999999;'><img src='".$plugin."images/busy.gif' /> ".$language->pw."</div>";

	} else {
		
		if ($viewer == "Subscriber") {
			$html .= "<p>Sorry, this forum can only be used by registered members. :(</p>";
		} else {
			$html .= "<p>Sorry, the minimum user level for this forum is ".$viewer.". :(</p>";
		}
	}			
	
	// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
	$html .= "<div style='width:100%;font-style:italic; font-size: 10px;text-align:center;'>Powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress, ".get_option("symposium_version")."</div>";
		
	// End Wrapper
	$html .= "</div>";
	
	$html .= "<div style='clear: both'></div>";

	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-forum', 'symposium_forum');  



?>
