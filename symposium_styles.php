<?php
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

		// Set dynamic styles
		$wp_width = $wpdb->get_var($wpdb->prepare("SELECT wp_width FROM ".$wpdb->prefix . 'symposium_config'));
		$border_radius = $wpdb->get_var($wpdb->prepare("SELECT border_radius FROM ".$wpdb->prefix . 'symposium_config'));
		$bigbutton_background = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background FROM ".$wpdb->prefix . 'symposium_config'));
		$bigbutton_color = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color FROM ".$wpdb->prefix . 'symposium_config'));
		$bigbutton_background_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_background_hover FROM ".$wpdb->prefix . 'symposium_config'));
		$bigbutton_color_hover = $wpdb->get_var($wpdb->prepare("SELECT bigbutton_color_hover FROM ".$wpdb->prefix . 'symposium_config'));
		$primary_color = $wpdb->get_var($wpdb->prepare("SELECT bg_color_1 FROM ".$wpdb->prefix . 'symposium_config'));
		$row_color = $wpdb->get_var($wpdb->prepare("SELECT bg_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		$row_color_alt = $wpdb->get_var($wpdb->prepare("SELECT bg_color_3 FROM ".$wpdb->prefix . 'symposium_config'));
		$text_color = $wpdb->get_var($wpdb->prepare("SELECT text_color FROM ".$wpdb->prefix . 'symposium_config'));
		$text_color_2 = $wpdb->get_var($wpdb->prepare("SELECT text_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		$link = $wpdb->get_var($wpdb->prepare("SELECT link FROM ".$wpdb->prefix . 'symposium_config'));
		$underline = $wpdb->get_var($wpdb->prepare("SELECT underline FROM ".$wpdb->prefix . 'symposium_config'));
		$link_hover = $wpdb->get_var($wpdb->prepare("SELECT link_hover FROM ".$wpdb->prefix . 'symposium_config'));
		$table_rollover = $wpdb->get_var($wpdb->prepare("SELECT table_rollover FROM ".$wpdb->prefix . 'symposium_config'));
		$table_border = $wpdb->get_var($wpdb->prepare("SELECT table_border FROM ".$wpdb->prefix . 'symposium_config'));
		$replies_border_size = $wpdb->get_var($wpdb->prepare("SELECT replies_border_size FROM ".$wpdb->prefix . 'symposium_config'));
		$row_border_style = $wpdb->get_var($wpdb->prepare("SELECT row_border_style FROM ".$wpdb->prefix . 'symposium_config'));
		$row_border_size = $wpdb->get_var($wpdb->prepare("SELECT row_border_size FROM ".$wpdb->prefix . 'symposium_config'));
		$label = $wpdb->get_var($wpdb->prepare("SELECT label FROM ".$wpdb->prefix . 'symposium_config'));
		$categories_background = $wpdb->get_var($wpdb->prepare("SELECT categories_background FROM ".$wpdb->prefix . 'symposium_config'));
		$categories_color = $wpdb->get_var($wpdb->prepare("SELECT categories_color FROM ".$wpdb->prefix . 'symposium_config'));
		$main_background = $wpdb->get_var($wpdb->prepare("SELECT main_background FROM ".$wpdb->prefix . 'symposium_config'));
		$closed_opacity = $wpdb->get_var($wpdb->prepare("SELECT closed_opacity FROM ".$wpdb->prefix . 'symposium_config'));
		$fontfamily = $wpdb->get_var($wpdb->prepare("SELECT fontfamily FROM ".$wpdb->prefix . 'symposium_config'));
		$fontsize = $wpdb->get_var($wpdb->prepare("SELECT fontsize FROM ".$wpdb->prefix . 'symposium_config'));
		$headingsfamily = $wpdb->get_var($wpdb->prepare("SELECT headingsfamily FROM ".$wpdb->prefix . 'symposium_config'));
		$headingssize = $wpdb->get_var($wpdb->prepare("SELECT headingssize FROM ".$wpdb->prefix . 'symposium_config'));
		
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
	
		$html .= "#symposium-wrapper .new-topic-subject-input, #symposium-wrapper .input-field {";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "}";

		$html .= "#symposium-wrapper .new-topic-subject-text, #symposium-wrapper .reply-topic-subject-text, #symposium-wrapper .reply-topic-text {";
		$html .= "	font-family: ".$fontfamily.";";
		$html .= "}";
	
		$html .= "#symposium-wrapper #new-topic, #symposium-wrapper #reply-topic, #symposium-wrapper #edit-topic-div {";
		$html .= "	background-color: ".$main_background.";";
		$html .= "	border: ".$replies_border_size."px solid ".$primary_color.";";	
		$html .= "}";

		$html .= "#symposium-wrapper #profile_right_column {";
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
		
		$html .= "#symposium-wrapper #symposium_table  {";
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
		
		// Mail
		
		$html .= "#symposium-wrapper #mail-main, #symposium-wrapper #mail_tabs .nav-tab-active, #symposium-wrapper #mail_tabs .nav-tab-inactive {";
		$html .= "	border: ".$table_border."px solid ".$primary_color.";";	
		$html .= "	background-color: ".$main_background.";";
		$html .= "}";

		$html .= "#symposium-wrapper #mail_tabs {";
		$html .= "	top: ".$table_border."px;";	
		$html .= "}";

		$html .= "#symposium-wrapper .mail_tab {";
	 	$html .= "  border-top-left-radius:".$border_radius."px;";
		$html .= "  -moz-border-radius-topleft:".$border_radius."px;";
	 	$html .= "  border-top-right-radius:".$border_radius."px;";
		$html .= "  -moz-border-radius-topright:".$border_radius."px;";
		$html .= "}";

		$html .= "#symposium-wrapper #mail_tabs .nav-tab-active {";
		$html .= "	background-color: ".$main_background.";";
		$html .= "	border-bottom: ".$table_border."px solid ".$main_background.";";	
		$html .= "}";
		$html .= "#symposium-wrapper #mail_tabs .nav-tab-active a {";
		$html .= "	color: ".$text_color.";";
		$html .= "}";

		$html .= "#symposium-wrapper #mail_tabs .nav-tab-inactive {";
		$html .= "	background-color: ".$categories_background.";";
		$html .= "	border-bottom: ".$table_border."px solid ".$primary_color.";";	
		$html .= "}";
		$html .= "#symposium-wrapper #mail_tabs .nav-tab-inactive a {";
		$html .= "	color: ".$categories_color.";";
		$html .= "}";

		$html .= "#symposium-wrapper .mail_item:hover {";
		$html .= "	background-color: ".$table_rollover.";";
		$html .= "}";

		$html .= "</style>";
?>