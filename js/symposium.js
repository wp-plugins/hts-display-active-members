jQuery(document).ready(function() { 	

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          SHARED                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	// Centre in screen
	jQuery.fn.inmiddle = function () {
		this.css("position","absolute");
		this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
		this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
		return this;
	}
   		
   	// Hide Notices	    	
	jQuery(".notice").hide();
	jQuery(".pleasewait").hide();

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                         DIRECTORY                                        |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("input#member").length) {
		
		jQuery("input#member").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_members_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#member" ).val( ui.item.label );
					jQuery( "input#member_id" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#member" ).val( ui.item.label );
					jQuery( "input#member_id" ).val( ui.item.value );
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "<div style=\'float:right\'>" + item.city + item.country + "</div></a>" )
					.appendTo( ul );
			};

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_members_functions.php", 
			type: "POST",
			data: ({
				action:"getMembers",
				page:1,
				language_key:symposium.language_key
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#symposium_members").html(str);
			},
			error: function(err){
				alert("D1:"+err);
			}		
   		});	
	}	
   				
		
	
	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           MAIL                                           |
	   +------------------------------------------------------------------------------------------+
	*/
		
		
	// Change between boxes
   	jQuery(".mail_tab").click(function() {
		jQuery(".pleasewait").inmiddle().show();
   	});		

	// React to click on message list
   	jQuery(".mail_item").click(function() {
   		
		jQuery("#in_message").html('<img src='+symposium.plugin_url+'images/busy.gif />');
		jQuery("#sent_message").html('<img src='+symposium.plugin_url+'images/busy.gif />');
		var mail_mid = jQuery(this).attr("id");

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getMailMessage",
				tray:symposium.view,
				'mid':mail_mid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				var details = str.split("[split]");
				if (symposium.view == "in") {
					if (details[1] > 0) {
						jQuery("#incount").html(' ('+details[1]+')');
					}
					if (details[2] == "in") {
						jQuery("#"+details[0]).removeClass("row");
						jQuery("#"+details[0]).addClass("row_odd");
					}
				}
				if (symposium.view == "in") {
					jQuery("#in_message").html(details[3]);
				} else {
					jQuery("#sent_message").html(details[3]);
				}
			},
			error: function(err){
				//alert("1:"+err);
			}		
   		});	   		
   		
   	});	

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                       MAIL COMPOSE                                       |
	   +------------------------------------------------------------------------------------------+
	*/
	
	if (jQuery("input#compose_recipient").length) {
		jQuery("input#compose_recipient").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_mail_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#compose_recipient" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#compose_recipient" ).val( ui.item.value );
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "<div style=\'float:right\'>" + item.city + item.country + "</div></a>" )
					.appendTo( ul );
			};
	}
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                         PROFILE                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          FORUM                                           |
	   +------------------------------------------------------------------------------------------+
	*/

   	jQuery(".backto").click(function() {
		jQuery(".pleasewait").inmiddle().show();
   	});		
	jQuery(".new-topic-subject-warning").hide();
	jQuery(".new_topic_text-warning").hide();
	jQuery(".reply_text-warning").hide();
	jQuery(".quick-reply-warning").hide();
	
	
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
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getEditDetails",
				tid:symposium.show_tid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").removeAttr("disabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery("#new-category").val(details[4]);
			},
			error: function(err){
				//alert("2:"+err);
			}		
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

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getEditDetails",
				tid:tid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").attr("disabled", "enabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery(".edit-topic-tid").attr("id", details[3]);
			},
			error: function(err){
				//alert("3:"+err);
			}		
   		});
   		
		jQuery(".pleasewait").hide();
		jQuery("#edit-topic-div").inmiddle().fadeIn();
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

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"updateEditDetails",
				'tid':tid,
				'topic_subject':topic_subject,
				'topic_post':topic_post,
				'topic_category':topic_category
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				jQuery("#edit-topic-div").fadeOut("fast");
				window.location.href=window.location.href;
			},
			error: function(err){
				//alert("4:"+err);
			}		
   		});
   		
	});
	// Cancel form
	jQuery(".edit_topic_cancel").click(function(){
		jQuery("#edit-topic-div").fadeOut("fast");
		jQuery(".notice").fadeOut("fast");
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
    jQuery("input[type='checkbox']").bind("click",function() {
    	
    	var checkbox = jQuery(this).attr("id");		    		

    	// Subscribe to New Forum Topics in a category
    	if (checkbox == "symposium_subscribe") {
			jQuery(".notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSubscribe",
						'cid':symposium.cat_id,
						"value":1
					}),
				    error: function(err){
						//alert("5:"+err);
					}		
		   		});

	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSubscribe",
						'cid':symposium.cat_id,
						"value":0
					}),
				    error: function(err){
						//alert("6:"+err);
					}		
		   		});
		   		
	        }
			jQuery(".notice").delay(100).fadeOut("slow");
    	}

    	// Subscribe to Topic Posts
    	if (checkbox == "subscribe") {
			jQuery(".notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForum",
						'tid':symposium.show_tid, 
						'value':1
					}),
				    error: function(err){
						//alert("7:"+err);
					}		
		   		});
		   		
	        } else {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForum",
						'tid':symposium.show_tid, 
						'value':0
					}),
				    error: function(err){
						//alert("8:"+err);
					}		
		   		});

	        }
			jQuery(".notice").delay(100).fadeOut("slow");
    	}
    	
    	// Sticky Topics
    	if (checkbox == "sticky") {
			jQuery(".notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSticky",
						'tid':symposium.show_tid, 
						'value':1
					}),
				    error: function(err){
						//alert("9:"+err);
					}		
		   		});
		   							
	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSticky",
						'tid':symposium.show_tid, 
						'value':0
					}),
				    error: function(err){
						//alert("10:"+err);
					}		
		   		});

	        }
			jQuery(".notice").delay(100).fadeOut("slow");
    	}
    			    	
    	// Digest
    	if (checkbox == "symposium_digest") {
			jQuery(".notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateDigest",
						'value':'on'
					}),
				    error: function(err){
						//alert("11:"+err);
					}		
		   		});
				
	        } else {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateDigest",
						'value':''
					}),
				    error: function(err){
						//alert("12:"+err);
					}		
		   		});
		   				        }
			jQuery(".notice").delay(100).fadeOut("slow");
    	}
    		
    	// Replied
    	if (checkbox == "replies") {
			jQuery(".notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicReplies", 
						'tid':symposium.show_tid, 
						'value':'on'
					}),
				    error: function(err){
						//alert("13:"+err);
					}		
		   		});
	        	
	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicReplies", 
						'tid':symposium.show_tid, 
						'value':''
					}),
				    error: function(err){
						//alert("14:"+err);
					}		
		   		});

	        }
			jQuery(".notice").delay(100).fadeOut("slow");
    	}

	});

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           MENU                                           |
	   +------------------------------------------------------------------------------------------+
	*/

 	// Test AJAX
 	jQuery("#testAJAX").click(function() {
 		random = Math.floor(Math.random()*10)+1;
 		alert("The random number being sent is "+random);

	  	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_menu_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_test",
				postID:random
			}),
		    dataType: "html",
			async: false,
			success: function(str_test){
				jQuery("#testAJAX_results").val('Value of '+str_test+' returned!');
			},
			error: function(err){
				alert("Test:"+err);
			}		
 		});
 		
	});

  	// Check if really want to delete	    
	jQuery(".delete").click(function(){
		var answer = confirm("Are you sure?");
		return answer // answer is a boolean
	});
	
	jQuery('.areyousure').click(function(){
		var answer = confirm('Are you sure?\n\nAll topics in the category will become un-categorised.');
		return answer // answer is a boolean
	});
	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                     NOTIFICATION BAR                                     |
	   +------------------------------------------------------------------------------------------+
	*/


	// Quick check on polling frequency
	if ( (symposium.bar_polling > 1) && (symposium.chat_polling > 1) ) {
	
		// Sound Manager
		// soundManager.url = symposium.plugin_url+'/js/soundmanager/soundmanager2.swf'; // override default SWF url
		// soundManager.debugMode = false;
		// soundManager.consoleOnly = false;
				
	  	// Set up icon actions ******************************************************
		if (jQuery("#symposium-email-box").css("display") != "none") {
			
	    	jQuery("#symposium-email-box").click(function() {
				window.location.href=symposium.mail_url;
	    	});
	
		}
		
		if (jQuery("#symposium-friends-box").css("display") != "none") {
			
	    	jQuery("#symposium-friends-box").click(function() {
				window.location.href=symposium.profile_url+'?view=friends';
	    	});
	    	jQuery("#symposium-online-box").click(function() {
				jQuery('#symposium-who-online').toggle("fast");
	    	});
	    	jQuery("#symposium-who-online_close").click(function() {
				jQuery('#symposium-who-online').hide("fast");
	    	});
			
		}
	
		// Scheduled checks for chat/unread mail/etc ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		if (symposium.current_user_id > 0 ) {
		
		   	// Check for notifications, unread mail, friend requests, etc
			do_bar_check();
		   	var refreshId = setInterval(function()
		   	{
				do_bar_check();
		   	}, symposium.bar_polling*1000); // Delay to check for new mail, etc
		   	
			do_chat_check();
			var refreshChatId = setInterval(function()
		   	{
		   		do_chat_check();
		   	}, symposium.chat_polling*1000); // Delay to check for new messages
	
		}
	
		// Chat Window Close ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		jQuery(".chat_close").live('click', function() {
	
	   		var chat_win = jQuery(this).parent().parent().attr('id');
	   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';
	   		var display_name = jQuery(this).parent().parent().attr('id')+'_display_name';
	   		jQuery('#'+display_name).html('Closing...');
			jQuery('#'+chat_win).hide();
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
				type: "POST",
				data: ({
					action:'symposium_closechat', 
					chat_from:symposium.current_user_id,
					chat_to:jQuery('#'+chat_to).html()
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					jQuery('#'+chat_to).html('');
				},
				error: function(err){
					//alert("15:"+err);
				}		
		  	});
		  	
	   	});
		
		// Type in Chat Window ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		jQuery('.chat_message').keypress(function(event) {
			if (event.which == 13) {
				var msg = jQuery(this).val();
				jQuery.trim(msg);
				jQuery(this).val('');
				event.preventDefault();
	
		   		var chat_message = jQuery(this).parent().parent().attr('id')+'_message';
		   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';
				
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
					type: "POST",
					data: ({
						action:'symposium_addchat',
						chat_from:symposium.current_user_id,
						chat_to:jQuery('#'+chat_to).html(),
						chat_message:msg
					}),
				    dataType: "html",
					async: false,
					success: function(str) {
						jQuery('#'+chat_message).append('<span style="font-weight:bold">'+str+'</span><br />');
						jQuery('#'+chat_message).attr({ scrollTop: jQuery('#'+chat_message).attr('scrollHeight') });
					},
					error: function(err){
						//alert("16:"+err);
					}		
			  	});
				
			}
		});
		
		
		// CHAT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
		if (symposium.use_chat == 'on') {
		
	   		var numChatWindows = 3;
	
	    	// ************** When clicking on a name to chat...
	    	
			jQuery(".symposium_online_name").live('click', function() {
	    		// choose a chat box
	    		var chatbox = 0;
	    		var already_chatting = 0;
	    		// first check to see if already chatting to them
				for (w=1;w<=numChatWindows;w++) {	
		    		if ( (already_chatting == 0) && (jQuery('#chat'+w+'_to').html() == jQuery(this).attr("title")) ) { already_chatting = w; }
				}
	    		if (already_chatting == 0) {
		    		// not already chatting, so find a free chat window
		    		chatbox = 0;
					for (w=1;w<=numChatWindows;w++) {	
			    		if (jQuery('#chat'+w).css("display") == "none") { chatbox = w; }
					}
		    		if (chatbox > 0) {
		    			// found a free chat window
						jQuery('#chat'+chatbox+'_to').html(jQuery(this).attr("title"));
						jQuery('#chat'+chatbox+'_display_name').html('Please wait...');
						jQuery("#chat"+chatbox+"_message").html('');
						jQuery("#chat"+chatbox).show('fast');
						
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
							type: "POST",
							data: ({
								action:"symposium_openchat", 
								chat_from:symposium.current_user_id,
								chat_to:jQuery(this).attr("title")
							}),
						    dataType: "html",
							async: false,
							success: function(str){
								if (str.substring(0, 2) == 'OK') { 
									var details = str.split("[split]");
									jQuery('#chat'+chatbox+'_to').html(details[1]);
									jQuery('#chat'+chatbox+'_display_name').html(details[2]);
									jQuery('#chat'+chatbox+'_message').html('');
								} else {
									if (jQuery('#chat'+chatbox+'_to').html() == str) { 
										jQuery('#chat'+chatbox).show("fast"); 
									}
								}
							},
							error: function(err){
								//alert("17:"+err);
							}		
					  	});
											
		    		} else {
		    			// no free chat windows
		    			alert("Sorry - you can't open any more chat windows.");
		    		}
		    		
	    		} else {
	    			
	    			// already chatting, so clear closed tag and re-open it
					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
						type: "POST",
						data: ({
							action:"symposium_reopenchat", 
							chat_from:symposium.current_user_id,
							chat_to:jQuery(this).attr("title")
						}),
					    dataType: "html",
						async: false,
						success: function(str){
							if (str != '') {
								for (w=1;w<=numChatWindows;w++) {	
									if (jQuery('#chat'+w+'_to').html() == str) { 
										jQuery('#chat'+w).show(); 
									}
								}
							}
						},
						error: function(err){
							//alert("18:"+err);
						}		
				  	});
				  	
	    		}
	    	});
	    	
		}
		
	} else {
		
		alert('Polling frequencies needs to be changed');
		
	}

		
});

// For Notification Bar
function do_chat_check() {

  	var numChatWindows = 3; // Should equal number of chat windows set up in symposium_bar.php

	jQuery.ajax({
		url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
		type: "POST",
		data: ({
			action:"symposium_getchat", 
			me:symposium.current_user_id,
			inactive:symposium.inactive,
			offline:symposium.offline,
			language_key:symposium.language_key
		}),
	    dataType: "html",
		async: true,
		success: function(str){
			if (str != '[topsplit]') {
				var topsplit=str.split("[topsplit]");
				if (topsplit.length == 2) {
					var last_post=topsplit[0];
					var rows=topsplit[1].split("[split]");
					var num_rows = rows.length-1;
					var play_sound = false;
					
					// clear chat windows	
					for (w=1;w<=numChatWindows;w++) {	
						jQuery('#chat'+w+'_to').html('');
						jQuery('#chat'+w+'_display_name').html('');
						jQuery('#chat'+w+'_message').html('');
					}
					
					var allocated_windows = 0;
					// loop through messages, setting up all the chat windows for each person
					for (i=0;i<num_rows;i++) {	
						var details=rows[i].split("[|]");
						var from = details[0];
						var to = details[1];
						var msg = details[2];
						var name = details[3];
						var status = details[4];
	
						var other = 0;
						
						if (from == symposium.current_user_id) {
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
									jQuery('#chat'+w+'_display_name').html('<img src="'+symposium.plugin_url+'/images/'+status+'_header.gif"> '+name); 
										allocated_windows++; 
										allocated = true;
									}
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
	
					if (from == symposium.current_user_id) {
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
									if (!(msg.indexOf('[closed-'+other+']') >= 0)) {
										if (from != other) {
											jQuery('#chat'+w+'_message').append('<span style="color:#003">'+msg+'</span><br />');
										} else {
											jQuery('#chat'+w+'_message').append('<span style="color:#633">'+msg+'</span><br />');
										}
									}
								} else {
									// New chat session
									//jQuery('#chat'+w+'_message').append('Powered by <a href="http://www.wpsymposium.com" target="_blank">WP Symposium</a><hr />');
									jQuery('#chat'+w+'_message').append('Chat is still being developed...<hr />');
								}
							}
						}
					}
															
				}
				
				// Show/hide all the chat windows
				for (w=1;w<=numChatWindows;w++) {	
					if (jQuery('#chat'+w+'_to').html() != '') {
						var message = jQuery("#chat"+w+"_message").html()+' ';
						if (message.indexOf('[closed-'+symposium.current_user_id+']') >= 0) { 						
							jQuery('#chat'+w+'_to').html(''); 
							jQuery('#chat'+w).hide(); 
						} else {
							var chat_to = jQuery('#chat'+w+'_to').html();
							jQuery("#chat"+w+"_message").html(message);
							jQuery('#chat'+w).show();
							jQuery("#chat"+w+"_message").attr({ scrollTop: jQuery("#chat"+w+"_message").attr("scrollHeight") });
						}
					} else {
						jQuery('#chat'+w).hide();
					}
				}
				
				// Finished all messages, play sound? There is no check for new mail yet, so won't work yet
				if (play_sound == true) {
					// soundManager.play('ChatAlert',symposium.plugin+'/js/soundmanager/'+symposium.soundchat);
	    		}
				
			} else {								
				// No chat occuring, close all windows
				for (w=1;w<=numChatWindows;w++) {	
					jQuery('#chat'+w).hide();
				}
			}
		},
		error: function(err){
			//alert("19:"+err);
		}		
  	});
		   	
}	
function do_bar_check() {

  	// Notifications ************************************************
	jQuery.ajax({
		url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
		type: "POST",
		data: ({
			action:"checkForNotifications"
		}),
	    dataType: "html",
		async: true,
		success: function(str){
			if (str != '' && str != '-1') {
				jQuery('#info').hide().delay((symposium.bar_polling*1000) * 0.75).fadeIn('slow'); // 11 seconds
	    		jQuery('#alerts').html(str);
	    		if (symposium.sound != 'None') {
					// soundManager.play('Alert',symposium.plugin_url+'/js/soundmanager/'+symposium.sound);
	    		}
				jQuery('#alerts').fadeIn().delay((symposium.bar_polling*1000)*0.5).fadeOut('slow');
			}
		},
		error: function(err){
			//alert("20:"+err);
		}		
  	});
	
  	// Email ******************************************************
	if (jQuery("#symposium-email-box").css("display") != "none") {
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getunreadmail", 
				me:symposium.current_user_id
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str > 0) {
					jQuery("#symposium-email-box").html(str);
					jQuery("#symposium-email-box").removeClass("symposium-email-box-read");
					jQuery("#symposium-email-box").addClass("symposium-email-box-unread");
				}
			},
			error: function(err){
				//alert("21:"+err);
			}		
   		});

	}
	
  	// Friends ******************************************************
	if (jQuery("#symposium-friends-box").css("display") != "none") {
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_friendrequests", 
				me:symposium.current_user_id
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str > 0) {
					jQuery("#symposium-friends-box").html(str);
					jQuery("#symposium-friends-box").removeClass("symposium-friends-box-none");
					jQuery("#symposium-friends-box").addClass("symposium-friends-box-new");
				}
			},
			error: function(err){
				//alert("22:"+err);
			}		
   		});
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getfriendsonline", 
				me:symposium.current_user_id,
				inactive:symposium.inactive,
				offline:symposium.offline,
				use_chat:symposium.use_chat
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != '') {
					var split=str.split("[split]");
					jQuery("#symposium-online-box").html(split[0]);
					jQuery("#symposium-friends-online-list").html(split[1]);
					if (split[0] > 0) {
						jQuery("#symposium-online-box").removeClass("symposium-online-box-none");
						jQuery("#symposium-online-box").addClass("symposium-online-box");
					} else {
						jQuery("#symposium-online-box").removeClass("symposium-online-box");
						jQuery("#symposium-online-box").addClass("symposium-online-box-none");
					}
				}
			},
			error: function(err){
				//alert("23:"+err);
			}		
   		});
		
	}
	
}		

function removeHTMLTags(strInputCode){
 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
	 	return (p1 == "lt")? "<" : ">";
	});
	var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
	return strTagStrippedText;	
}

// For Forum
function validate_form(thisform)
{
	form_id = thisform.id;
	if ( (form_id) == "start-new-topic") {
		with (thisform)
		{
			if (new_topic_subject.value == '' || new_topic_subject.value == null) {
				jQuery(".new-topic-subject-warning").show("slow");
				new_topic_subject.focus(); 
				return false;
			}
			if (new_topic_text.value == '' || new_topic_text.value == null) {
				jQuery(".new_topic_text-warning").show("slow");
				new_topic_text.focus(); 
				return false;
			}
		}
	}
	if ( (form_id) == "start-reply-topic") {
		with (thisform)
		{
			if (reply_text.value == '' || reply_text.value == null) {
				jQuery(".reply_text-warning").show("slow");
				reply_text.focus(); 
				return false;
			}
		}
	}
	if ( (form_id) == "quick-reply") {
		with (thisform)
		{
			if (reply_text.value == '' || reply_text.value == null) {
				jQuery(".quick-reply-warning").show("slow");
				reply_text.focus(); 
				return false;
			}
		}
	}			
}