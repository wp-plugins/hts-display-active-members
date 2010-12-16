<?php
		// Set dynamic styles
		$wp_width = $wpdb->get_var($wpdb->prepare("SELECT wp_width FROM ".$config));
		$border_radius = $wpdb->get_var($wpdb->prepare("SELECT border_radius FROM ".$config));
		$bigbutton_background = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background FROM ".$config));
		$bigbutton_color = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color FROM ".$config));
		$bigbutton_background_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background_hover FROM ".$config));
		$bigbutton_color_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color_hover FROM ".$config));
		$primary_color = $wpdb->get_var($wpdb->prepare("SELECT bg_color_1 FROM ".$config));
		$row_color = $wpdb->get_var($wpdb->prepare("SELECT bg_color_2 FROM ".$config));
		$row_color_alt = $wpdb->get_var($wpdb->prepare("SELECT bg_color_3 FROM ".$config));
		$text_color = $wpdb->get_var($wpdb->prepare("SELECT text_color FROM ".$config));
		$text_color_2 = $wpdb->get_var($wpdb->prepare("SELECT text_color_2 FROM ".$config));
		$link = $wpdb->get_var($wpdb->prepare("SELECT link FROM ".$config));
		$underline = $wpdb->get_var($wpdb->prepare("SELECT underline FROM ".$config));
		$link_hover = $wpdb->get_var($wpdb->prepare("SELECT link_hover FROM ".$config));
		$table_rollover = $wpdb->get_var($wpdb->prepare("SELECT table_rollover FROM ".$config));
		$table_border = $wpdb->get_var($wpdb->prepare("SELECT table_border FROM ".$config));
		$replies_border_size = $wpdb->get_var($wpdb->prepare("SELECT replies_border_size FROM ".$config));
		$row_border_style = $wpdb->get_var($wpdb->prepare("SELECT row_border_style FROM ".$config));
		$row_border_size = $wpdb->get_var($wpdb->prepare("SELECT row_border_size FROM ".$config));
		$label = $wpdb->get_var($wpdb->prepare("SELECT label FROM ".$config));
		$categories_background = $wpdb->get_var($wpdb->prepare("SELECT categories_background FROM ".$config));
		$categories_color = $wpdb->get_var($wpdb->prepare("SELECT categories_color FROM ".$config));
		$main_background = $wpdb->get_var($wpdb->prepare("SELECT main_background FROM ".$config));
		$closed_opacity = $wpdb->get_var($wpdb->prepare("SELECT closed_opacity FROM ".$config));

		$fontfamily = $wpdb->get_var($wpdb->prepare("SELECT fontfamily FROM ".$config));
		$fontsize = $wpdb->get_var($wpdb->prepare("SELECT fontsize FROM ".$config));
		$headingsfamily = $wpdb->get_var($wpdb->prepare("SELECT headingsfamily FROM ".$config));
		$headingssize = $wpdb->get_var($wpdb->prepare("SELECT headingssize FROM ".$config));
		
		// Check defaults
		if ($wp_width == '') { $wp_width = '100pc'; }

		$html .= "<style>";
		
		$html .= "#symposium-wrapper {";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "	font-size: ".$fontsize."px;";
		$html .= "	color: ".$text_color.";";
		$html .= "  width: ".str_replace('pc', '%', $wp_width).";";
		$html .= "}";

		$html .= "#symposium-wrapper * {";
		$html .= "	border-radius: ".$border_radius."px;";
		$html .= "  -moz-border-radius:".$border_radius."px;";
		$html .= "}";
	
		$html .= "#symposium-wrapper .label {";
		$html .= "  color: ".$label.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .row a, #symposium-wrapper .row_odd a {";
		if ($underline == "on") {
			$html .= "	text-decoration: underline;";
		} else {
			$html .= "	text-decoration: none;";
		}
		$html .= "}";
	
		$html .= "#symposium-wrapper .new-topic-subject-input";
		$html .= "{";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "}";

		$html .= "#symposium-wrapper .new-topic-subject-text, #symposium-wrapper .reply-topic-subject-text, #symposium-wrapper .reply-topic-text";
		$html .= "{";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #new-topic, #symposium-wrapper #reply-topic, #symposium-wrapper #edit-topic-div {";
		$html .= "	background-color: ".$main_background.";";
		$html .= "	border: ".$replies_border_size."px solid ".$primary_color.";";	
		$html .= "}";
		
		$html .= "#symposium-wrapper #reply-topic-bottom textarea {";
		$html .= "	border: 1px solid ".$primary_color.";";			
		$html .= "}";
		
		$html .= "#symposium-wrapper #new-topic-link, #symposium-wrapper #reply-topic-link, #symposium-wrapper .button {";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "	font-size: ".$fontsize."px;";
		$html .= "	background-color: ".$bigbutton_background.";";
		$html .= "	color: ".$bigbutton_color.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #new-topic-link:hover, #symposium-wrapper #reply-topic-link:hover, #symposium-wrapper .button:hover {";
		$html .= "	background-color: ".$bigbutton_background_hover.";";
		$html .= "	color: ".$bigbutton_color_hover.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper #symposium_table {";
		$html .= "	border: ".$table_border."px solid ".$primary_color.";";	
		$html .= "}";
	
		$html .= "#symposium-wrapper .table_header {";
		$html .= "	background-color: ".$categories_background.";";
		$html .= "  font-weight: bold;";
	 	$html .= "  border-radius:0px;";
		$html .= "  -moz-border-radius:0px;";
		$html .= "  border: 0";
	 	$html .= "  border-top-left-radius:".($border_radius-5)."px;";
		$html .= "  -moz-border-radius-topleft:".($border_radius-5)."px;";
	 	$html .= "  border-top-right-radius:".($border_radius-5)."px;";
		$html .= "  -moz-border-radius-inmiddle:".($border_radius-5)."px;";
		$html .= "}";

		$html .= "#symposium-wrapper .table_topic {";
		$html .= "	font-family: ".$headingsfamily.";";
		$html .= "	font-size: ".$headingssize.";";
		$html .= "	background-color: ".$categories_background.";";
		$html .= "	color: ".$categories_color.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .round_bottom_left {";
	 	$html .= "  border-bottom-left-radius:".($border_radius-5)."px;";
		$html .= "  -moz-border-radius-bottomleft:".($border_radius-5)."px;";
		$html .= "}";
		
		$html .= "#symposium-wrapper .round_bottom_right {";
	 	$html .= "  border-bottom-right-radius:".($border_radius-5)."px;";
		$html .= "  -moz-border-radius-bottomright:".($border_radius-5)."px;";
		$html .= "}";
		
		$html .= "#symposium-wrapper .categories_color {";
		$html .= "	color: ".$categories_color.";";
		$html .= "}";
		$html .= "#symposium-wrapper .categories_background {";
		$html .= "	background-color: ".$categories_background.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .row {";
		$html .= "	background-color: ".$row_color.";";
		$html .= "}";
			
		$html .= "#symposium-wrapper .row_odd {";
		$html .= "	background-color: ".$row_color_alt.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper .row:hover, #symposium-wrapper .row_odd:hover {";
		$html .= "	background-color: ".$table_rollover.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .row_link, #symposium-wrapper .edit, #symposium-wrapper .delete {";
		$html .= "	font-size: ".$headingssize.";";
		$html .= "	color: ".$link.";";
		$html .= "}";
			
		$html .= "#symposium-wrapper .row_link:hover {";
		$html .= "	color: ".$link_hover.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #starting-post {";
		$html .= "	border: ".$replies_border_size."px solid ".$primary_color.";";
		$html .= "	background-color: ".$main_background.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .started-by {";
		$html .= "	color: ".$text_color_2.";";
		$html .= "}";
				
		$html .= "#symposium-wrapper #child-posts {";
		$html .= "	border: ".$replies_border_size."px solid ".$primary_color.";";
		$html .= "	background-color: ".$row_color_alt.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper .sep {";
		$html .= "	clear:both;";
		$html .= "	width:100%;";
		$html .= "	border-bottom: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";";
		$html .= "}";

		$html .= "#symposium-wrapper .transparent {";
		$html .= '  -ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity='.($closed_opacity*100).')";';
		$html .= "  filter: alpha(opacity=".($closed_opacity*100).");";
		$html .= "  -moz-opacity: ".$closed_opacity.";";
		$html .= "  -khtml-opacity: ".$closed_opacity.";";
		$html .= "  opacity: ".$closed_opacity.";";
		$html .= "}";
		
		$html .= "</style>";
?>