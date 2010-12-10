<?php
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
		
		$html .= "<style>";
		
		$html .= "#symposium-wrapper * {";
		$html .= "	border-radius: ".$border_radius."px;";
		$html .= "  -moz-border-radius:".$border_radius."px;";
		$html .= "}";
	
		$html .= "#symposium-wrapper .label {";
		$html .= "  color: ".$label.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper {";
		$html .= "	color: ".$text_color.";";
		$html .= "}";

		$html .= "#symposium-wrapper row a, #symposium-wrapper row_odd a,  {";
		if ($underline == "on") {
			$html .= "	text-decoration: underline;";
		} else {
			$html .= "	text-decoration: none;";
		}
		$html .= "}";
	
		$html .= "#symposium-wrapper #new-topic, #symposium-wrapper #reply-topic, #symposium-wrapper #edit-topic-div {";
		$html .= "	background-color: ".$bg_color_3.";";
		$html .= "	border: ".$replies_border_size."px solid ".$bg_color_1.";";	
		$html .= "}";
		
		$html .= "#symposium-wrapper #new-topic-link, #symposium-wrapper #reply-topic-link, #symposium-wrapper .button {";
		$html .= "	background-color: ".$bigbutton_background.";";
		$html .= "	color: ".$bigbutton_color.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #new-topic-link:hover, #symposium-wrapper #reply-topic-link:hover, #symposium-wrapper .button:hover {";
		$html .= "	background-color: ".$bigbutton_background_hover.";";
		$html .= "	color: ".$bigbutton_color_hover.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper #symposium_table {";
		$html .= "	border: ".$table_border."px solid ".$bg_color_1.";";	
		$html .= "}";
	
		$html .= "#symposium-wrapper .table_header {";
		$html .= "	background-color: ".$bg_color_1.";";
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
		$html .= "	background-color: ".$bg_color_2.";";
		$html .= "}";
			
		$html .= "#symposium-wrapper .row_odd {";
		$html .= "	background-color: ".$bg_color_3.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper .row:hover, #symposium-wrapper .row_odd:hover {";
		$html .= "	background-color: ".$table_rollover.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .row_link, #symposium-wrapper .edit, #symposium-wrapper .delete {";
		$html .= "	color: ".$link.";";
		$html .= "}";
			
		$html .= "#symposium-wrapper .row_link:hover {";
		$html .= "	color: ".$link_hover.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #starting-post {";
		$html .= "	border: ".$replies_border_size."px solid ".$bg_color_1.";";
		$html .= "	background-color: ".$bg_color_2.";";
		$html .= "}";
		
		$html .= "#symposium-wrapper .started-by {";
		$html .= "	color: ".$text_color_2.";";
		$html .= "}";
				
		$html .= "#symposium-wrapper #child-posts {";
		$html .= "	border: ".$replies_border_size."px solid ".$bg_color_1.";";
		$html .= "	background-color: ".$bg_color_3.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper .sep {";
		$html .= "	clear:both;";
		$html .= "	width:100%;";
		$html .= "	border-bottom: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";";
		$html .= "}";
				
		$html .= "</style>";
?>