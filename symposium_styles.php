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
		$styles = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
		
		if ($styles) {
			
			$wp_width = $styles->wp_width;
			$wp_alignment = $styles->wp_alignment;
			$border_radius = $styles->border_radius;
			$bigbutton_background = $styles->bigbutton_background;
			$bigbutton_color = $styles->bigbutton_color;
			$bigbutton_background_hover = $styles->bigbutton_background_hover;
			$bigbutton_color_hover = $styles->bigbutton_color_hover;
			$primary_color = $styles->bg_color_1;
			$row_color = $styles->bg_color_2;
			$row_color_alt = $styles->bg_color_3;
			$text_color = $styles->text_color;
			$text_color_2 = $styles->text_color_2;
			$link = $styles->link;
			$underline = $styles->underline;
			$link_hover = $styles->link_hover;
			$table_rollover = $styles->table_rollover;
			$table_border = $styles->table_border;
			$replies_border_size = $styles->replies_border_size;
			$row_border_style = $styles->row_border_style;
			$row_border_size = $styles->row_border_size;
			$label = $styles->label;
			$categories_background = $styles->categories_background;
			$categories_color = $styles->categories_color;
			$main_background = $styles->main_background;
			$closed_opacity = $styles->closed_opacity;
			$fontfamily = $styles->fontfamily;
			$fontsize = $styles->fontsize;
			$headingsfamily = $styles->headingsfamily;
			$headingssize = $styles->headingssize;
					
			// Check defaults
			if ($wp_width == '') { $wp_width = '100pc'; }
	
			$html .= "<style>";
			
			$html .= "#symposium-wrapper {";
			if ($wp_alignment == 'Center') {
				$html .= "margin: 0 auto;";
			}
			if ($wp_alignment == 'Left' || $wp_alignment == 'Right') {
				$html .= "clear: both";
				$html .= "margin: 0;";
				$html .= "float: ".strtolower($wp_alignment).";";
			}
			$html .= "	font-family: ".$fontfamily.";";
			$html .= "	font-size: ".$fontsize."px;";
			$html .= "	color: ".$text_color.";";
			$html .= "  width: ".str_replace('pc', '%', $wp_width).";";
			$html .= "}";
	
			$html .= "#symposium-wrapper * {";
			$html .= "	border-radius: ".$border_radius."px;";
			$html .= "  -moz-border-radius:".$border_radius."px;";
			$html .= "}";

			$html .= "#symposium-wrapper .corners {";
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
			$html .= "	border: ".$replies_border_size."px solid ".$primary_color.";";	
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
	
			$html .= "#symposium-wrapper .alert {";
			$html .= "	clear:both;";
			$html .= "	padding:6px;";
			$html .= "	margin-bottom:15px;";
			$html .= "	border: 1px solid #666;";	
			$html .= "	background-color: #eee;";
			$html .= "	color: #000;";
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
	
			$html .= "#symposium-wrapper .notice, #symposium-wrapper .pleasewait";
			$html .= "{";
			$html .= "	position: absolute;";
			$html .= "	padding: 6px;";
			$html .= "	border: 2px solid ".$primary_color.";";	
			$html .= "	background-color: ".$main_background.";";
			$html .= "	display: none;";
			$html .= "}";
	
			$html .= "</style>";
			
		} else {
			
			$html .= "<p><strong>Failed to get styles</strong></p>";
			
		}
?>