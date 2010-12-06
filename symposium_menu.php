<?php
/* ====================================================== ADMIN MENU ====================================================== */

function symposium_plugin_menu() {
	add_menu_page(__('Symposium'), __('Symposium'), 'edit_themes', 'symposium_options', 'symposium_plugin_options', '', 7); 
	add_submenu_page('symposium_options', __('Options'), __('Options'), 'edit_themes', 'symposium_options', 'symposium_plugin_options');
	add_submenu_page('symposium_options', __('Styles'), __('Styles'), 'edit_themes', 'symposium_styles', 'symposium_plugin_styles');
	add_submenu_page('symposium_options', __('Forum Categories'), __('Forum Categories'), 'edit_themes', 'symposium_categories', 'symposium_plugin_categories');
}
add_action('admin_menu', 'symposium_plugin_menu');

function symposium_plugin_categories() {

	global $wpdb;

	?>
	<script type="text/javascript">
    jQuery(document).ready(function() { 	

		jQuery('.areyousure').click(function(){
		  var answer = confirm('Are you sure?\n\nAll topics in the category will become un-categorised.');
		  return answer // answer is a boolean
		});
		
    });
 
	</script>
	<?php 
	
  	if (!current_user_can('manage_options'))  {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}
  	
  	$action = $_GET['action'];

	// Update values
	if (isset($_POST['title'])) {
		
   		$range = array_keys($_POST['cid']);
		foreach ($range as $key) {
		    $cid = $_POST['cid'][$key];
		    $title = $_POST['title'][$key];
		    $listorder = $_POST['listorder'][$key];
		    $allow_new = $_POST['allow_new'][$key];
		    
		    if ($cid == $_POST['default_category']) {
		    	$defaultcat = "on";
		    } else {
		    	$defaultcat = "";
		    }
		    
			$wpdb->query( $wpdb->prepare( "
				UPDATE ".$wpdb->prefix.'symposium_cats'."
				SET title = %s, listorder = %s, allow_new = %s, defaultcat = %s
				WHERE cid = %d", 
		        $title, $listorder, $allow_new, $defaultcat, $cid  ) );
		        			
		}

	}
		
  	// Add new category?
  	if ( ($_POST['new_title'] != '') && ($_POST['new_title'] != 'Add New Category...') ) {
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix.'symposium_cats'."
			( 	title, 
				listorder,
				allow_new
			)
			VALUES ( %s, %d, %s )", 
	        array(
	        	$_POST['new_title'], 
	        	$_POST['new_listorder'],
	        	$_POST['new_allow_new']
	        	) 
	        ) );
	      
	    $action = '';
	}

  	// Delete a category?
  	if ($action == 'delcid') {
  		// Must leave at least one category, so check
		$cat_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats'));
		if ($cat_count > 1) {
			$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$_GET['cid']) );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = 0 WHERE topic_category = ".$_GET['cid']) );					} else {
			echo "<div class='error'><p>You must have at least one category, you can hide the category title on the <a href='?page=symposium_options'>options page</a>.</p></div>";
		}
  	}
  	

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Forum Categories</h2>';

	?> 
 
	<form method="post" action=""> 
	<input type="hidden" name="symposium_update" value="Y">

	<table class="form-table">
	<tr>
	<td>Category Title</td>
	<td>Order</td>
	<td>Allow new topics</td>
	<td>&nbsp;</td>
	</tr> 
	
	<?php
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');

	if ($categories) {
		foreach ($categories as $category) {

			echo '<tr valign="top">';
			echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
			echo '<td><input name="title[]" type="text" value="'.$category->title.'" class="regular-text" /></td>';
			echo '<td><input name="listorder[]" type="text" value="'.$category->listorder.'" /></td>';
			echo '<td>';
			echo '<select name="allow_new[]">';
			echo '<option value="on"';
				if ($category->allow_new == "on") { echo " SELECTED"; }
				echo '>Yes</option>';
			echo '<option value=""';
				if ($category->allow_new != "on") { echo " SELECTED"; }
				echo '>No</option>';
			echo '</select>';
			echo '</td>';
			echo '</td>';
			echo '<td><a class="areyousure" href="?page=symposium_categories&action=delcid&cid='.$category->cid.'">Delete</td></td>';
			echo '</tr>';
	
		}
	}
	?>
	
	<tr><td align='right'>Default Category for new Topics:</td>
	<td colspan=2>
	<?php
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');

	if ($categories) {
		echo "<select name='default_category'>";
		foreach ($categories as $category) {
			echo "<option value=".$category->cid;
			if ($category->defaultcat == "on") { echo " SELECTED"; }
			echo ">".$category->title."</option>";
		}
		echo "</select>";
	}	
	?>
	</td>
	</tr>

	<tr><td colspan=4><hr /></td></tr>
	<tr valign="top">
	<td><input name="new_title" type="text" onclick="javascript:this.value = ''" value="Add New Category..." class="regular-text" />
	<td><input name="new_listorder" type="text" value="0" />
	<td>
	<input type="checkbox" name="new_allow_new" CHECKED />
	</td>
	<td colspan=2>&nbsp;</td>
	</tr>
	</table> 
	 
	<p class="submit"> 
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" /> 
	</p> 
	
	<p>
	Note: if you delete a category that has topics, you will need to select a parent category for those topics if you want to make use of the categories feature.
	<p>
	</form> 
	
	<?php
  
  	echo '</div>';

} 	

function symposium_plugin_styles() {
	
	global $wpdb;
	
	?>
	
	<script type="text/javascript">
    jQuery(document).ready(function() { 	
    	
		jQuery('.Multiple').jPicker();

    });
 	</script>

	<?php

	if (!current_user_can('manage_options'))  {
	    wp_die( __('You do not have sufficient permissions to access this page.') );
	}

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Styles</h2>';
  	
    // See if the user has selected a template
    if( isset($_POST[ 'symposium_apply' ]) && $_POST[ 'symposium_apply' ] == 'Y' ) {
		$style = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_styles'." WHERE sid = ".$_POST['sid']);
		if ($style) {
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$style->categories_background."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$style->categories_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$style->border_radius."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$style->bigbutton_background."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$style->bigbutton_background_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$style->bigbutton_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$style->bigbutton_color_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$style->bg_color_1."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$style->bg_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$style->bg_color_3."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$style->row_border_style."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$style->row_border_size) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = ".$style->replies_border_size) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$style->table_rollover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$style->table_border) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$style->text_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$style->text_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$style->link."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$style->link_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$style->label."'") );

	        // Put an settings updated message on the screen
			echo "<div class='updated'><p>Template Applied</p></div>";
		} else {
			echo "<div class='error'><p>Template Not Found</p></div>";
		}
    }
    
    // See if the user has posted us some information
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'Y' ) {
        // Read their posted value
        $categories_background = $_POST[ 'categories_background' ];
        $categories_color = $_POST[ 'categories_color' ];
        $border_radius = $_POST[ 'border_radius' ];
        $bigbutton_background = $_POST[ 'bigbutton_background' ];
        $bigbutton_background_hover = $_POST[ 'bigbutton_background_hover' ];
        $bigbutton_color = $_POST[ 'bigbutton_color' ];
        $bigbutton_color_hover = $_POST[ 'bigbutton_color_hover' ];
        $bg_color_1 = $_POST[ 'bg_color_1' ];
        $bg_color_2 = $_POST[ 'bg_color_2' ];
        $bg_color_3 = $_POST[ 'bg_color_3' ];
        $row_border_style = $_POST[ 'row_border_style' ];
        $row_border_size = $_POST[ 'row_border_size' ];
        $table_rollover = $_POST[ 'table_rollover' ];
        $table_border = $_POST[ 'table_border' ];
        $replies_border_size = $_POST[ 'replies_border_size' ];
        $text_color = $_POST[ 'text_color' ];
        $text_color_2 = $_POST[ 'text_color_2' ];
        $link = $_POST[ 'link' ];
        $link_hover = $_POST[ 'link_hover' ];
        $label = $_POST[ 'label' ];

        // Save the posted value in the database
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$categories_background."'") );				
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$categories_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$border_radius."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$bigbutton_background."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$bigbutton_background_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$bigbutton_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$bigbutton_color_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$bg_color_1."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$bg_color_2."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$bg_color_3."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$row_border_style."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$row_border_size) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$table_rollover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$table_border) );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = '".$replies_border_size."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$text_color."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$text_color_2."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$link."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$link_hover."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$label."'") );					

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Options Saved</p></div>";

    }
    
	$styles = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_config');

	if ($styles) {
		foreach ($styles as $style) {
			
			?> 
		 
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="Y">
		
			<table class="form-table"> 
		
			<tr valign="top"> 
			<th scope="row"><label for="border_radius">Corners</label></th> 
			<td>
			<select name="border_radius" id="border_radius"> 
				<option <?if ( $style->border_radius=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->border_radius=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->border_radius=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->border_radius=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
				<option <?if ( $style->border_radius=='4') { echo "selected='selected'"; } ?> value='4'>4 pixels</option> 
				<option <?if ( $style->border_radius=='5') { echo "selected='selected'"; } ?> value='5'>5 pixels</option> 
				<option <?if ( $style->border_radius=='6') { echo "selected='selected'"; } ?> value='6'>6 pixels</option> 
				<option <?if ( $style->border_radius=='7') { echo "selected='selected'"; } ?> value='7'>7 pixels</option> 
				<option <?if ( $style->border_radius=='8') { echo "selected='selected'"; } ?> value='8'>8 pixels</option> 
				<option <?if ( $style->border_radius=='9') { echo "selected='selected'"; } ?> value='9'>9 pixels</option> 
				<option <?if ( $style->border_radius=='10') { echo "selected='selected'"; } ?> value='10'>10 pixels</option> 
				<option <?if ( $style->border_radius=='11') { echo "selected='selected'"; } ?> value='11'>11 pixels</option> 
				<option <?if ( $style->border_radius=='12') { echo "selected='selected'"; } ?> value='12'>12 pixels</option> 
				<option <?if ( $style->border_radius=='13') { echo "selected='selected'"; } ?> value='13'>13 pixels</option> 
				<option <?if ( $style->border_radius=='14') { echo "selected='selected'"; } ?> value='14'>14 pixels</option> 
				<option <?if ( $style->border_radius=='15') { echo "selected='selected'"; } ?> value='15'>15 pixels</option> 
			</select> 
			<span class="description">Rounded Corner radius (not supported in all browsers)</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_background">Buttons</label></th> 
			<td><input name="bigbutton_background" type="text" id="bigbutton_background" class="iColorPicker" value="<?php echo $style->bigbutton_background; ?>"  /> 
			<span class="description">Background Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_background_hover"></label></th> 
			<td><input name="bigbutton_background_hover" type="text" id="bigbutton_background_hover" class="iColorPicker" value="<?php echo $style->bigbutton_background_hover; ?>"  /> 
			<span class="description">Background Colour on mouse hover</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_color"></label></th> 
			<td><input name="bigbutton_color" type="text" id="bigbutton_color" class="iColorPicker" value="<?php echo $style->bigbutton_color; ?>"  /> 
			<span class="description">Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bigbutton_color_hover"></label></th> 
			<td><input name="bigbutton_color_hover" type="text" id="bigbutton_color_hover" class="iColorPicker" value="<?php echo $style->bigbutton_color_hover; ?>"  /> 
			<span class="description">Text Colour on mouse hover</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_1">Tables</label></th> 
			<td><input name="bg_color_1" type="text" id="bg_color_1" class="iColorPicker" value="<?php echo $style->bg_color_1; ?>"  /> 
			<span class="description">Primary Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_2"></label></th> 
			<td><input name="bg_color_2" type="text" id="bg_color_2" class="iColorPicker" value="<?php echo $style->bg_color_2; ?>"  /> 
			<span class="description">Row Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="bg_color_3"></label></th> 
			<td><input name="bg_color_3" type="text" id="bg_color_3" class="iColorPicker" value="<?php echo $style->bg_color_3; ?>"  /> 
			<span class="description">Alternative Row Colour</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="table_rollover"></label></th> 
			<td><input name="table_rollover" type="text" id="table_rollover" class="iColorPicker" value="<?php echo $style->table_rollover; ?>"  /> 
			<span class="description">Row colour on mouse hover</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="table_border"></label></th> 
			<td>
			<select name="table_border" id="table_border"> 
				<option <?if ( $style->table_border=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->table_border=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->table_border=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->table_border=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">Border Size</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="row_border_style">Table/Rows</label></th> 
			<td>
			<select name="row_border_style" id="row_border_styledefault_role"> 
				<option <?if ( $style->row_border_style=='dotted') { echo "selected='selected'"; } ?> value='dotted'>Dotted</option> 
				<option <?if ( $style->row_border_style=='dashed') { echo "selected='selected'"; } ?> value='dashed'>Dashed</option> 
				<option <?if ( $style->row_border_style=='solid') { echo "selected='selected'"; } ?> value='solid'>Solid</option> 
			</select> 
			<span class="description">Border style between rows</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="row_border_size"></label></th> 
			<td>
			<select name="row_border_size" id="row_border_size"> 
				<option <?if ( $style->row_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->row_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->row_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->row_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">Border size between rows</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="replies_border_size">Other borders</label></th> 
			<td>
			<select name="replies_border_size" id="replies_border_size"> 
				<option <?if ( $style->replies_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
				<option <?if ( $style->replies_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
				<option <?if ( $style->replies_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
				<option <?if ( $style->replies_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
			</select> 
			<span class="description">For new topics/replies and topic replies</span></td> 
			</tr> 
				
			<tr valign="top"> 
			<th scope="row"><label for="categories_background">In-Table Headings</label></th> 
			<td><input name="categories_background" type="text" id="categories_background" class="iColorPicker" value="<?php echo $style->categories_background; ?>"  /> 
			<span class="description">Background colour of, for example, current Category within a Forum Topic</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="categories_color"></label></th> 
			<td><input name="categories_color" type="text" id="categories_color" class="iColorPicker" value="<?php echo $style->categories_color; ?>"  /> 
			<span class="description">Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="text_color">Text Colour</label></th> 
			<td><input name="text_color" type="text" id="text_color" class="iColorPicker" value="<?php echo $style->text_color; ?>"  /> 
			<span class="description">Primary Text Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="text_color_2"></label></th> 
			<td><input name="text_color_2" type="text" id="text_color_2" class="iColorPicker" value="<?php echo $style->text_color_2; ?>"  /> 
			<span class="description">Alternative Text Colour / Border Colour between rows</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="link">Topic Links</label></th> 
			<td><input name="link" type="text" id="link" class="iColorPicker" value="<?php echo $style->link; ?>"  /> 
			<span class="description">Link Colour</span></td> 
			</tr> 
		
			<tr valign="top"> 
			<th scope="row"><label for="link_hover"</label></th> 
			<td><input name="link_hover" type="text" id="link_hover" class="iColorPicker" value="<?php echo $style->link_hover; ?>"  /> 
			<span class="description">Link Colour on mouse hover</span></td> 
			</tr> 

			<tr valign="top"> 
			<th scope="row"><label for="label">Labels</label></th> 
			<td><input name="label" type="text" id="label" class="iColorPicker" value="<?php echo $style->label; ?>"  /> 
			<span class="description">Colour of text labels outside forum areas</span></td> 
			</tr> 
		
			</table> 
			 
			<p class="submit"> 
			<input type="submit" name="Submit" class="button-primary" value="Apply Changes" /> 
			</p> 
			</form> 

			<h3>Style Templates</h3>
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_apply" value="Y">
		
			<table class="form-table"> 
		
			<tr valign="top"> 
			<th scope="row"><label for="sid">Select Template</label></th> 
			<td>
			<select name="sid" id="sid"> 
				<option value='1'>Symposium</option> 
				<option value='2'>Azure (Blue)</option> 
				<option value='3'>Gothic (Black/Grey)</option> 
				<option value='4'>Metal (Grey)</option> 
				<option value='5'>Neutral (Grey)</option> 
			</select> 
			<span class="description">Changes are applied immediately when applied</span></td> 
			</tr> 			

			</table> 
						
			<p class="submit"> 
			<input type="submit" name="Submit" class="button-primary" value="Apply Template" /> 
			</p> 
			</form> 
			
			<?php
			
		}
		  	
	}
	
 	echo '</div>';


} 	

function symposium_plugin_options() {

	global $wpdb;
	
  	if (!current_user_can('manage_options'))  {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>Options</h2>';
  	
    // See if the user has posted updated category information
    if( isset($_POST[ 'categories_update' ]) && $_POST[ 'categories_update' ] == 'Y' ) {
    	
   		$range = array_keys($_POST['tid']);
		foreach ($range as $key) {
	
		    $tid = $_POST['tid'][$key];
		    $topic_category = $_POST['topic_category'][$key];
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE tid = ".$tid) );					
			echo $wpdb->last_query."<br>";
		        			
		}

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Categories Saved</p></div>";

    }

    // See if the user has posted us some information
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'Y' ) {
        // Read their posted value
        $footer = $_POST[ 'email_footer' ];
        $show_categories = $_POST[ 'show_categories' ];
        $send_summary = $_POST[ 'send_summary' ];
        $forum_url = $_POST[ 'forum_url' ];
        $from_email = $_POST[ 'from_email' ];
        $language = $_POST[ 'language' ];

        // Save the posted value in the database
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET footer = '".$footer."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET show_categories = '".$show_categories."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET send_summary = '".$send_summary."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_url = '".$forum_url."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET from_email = '".$from_email."'") );					
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET language = '".$language."'") );					

        // Put an settings updated message on the screen
		echo "<div class='updated'><p>Options Saved</p></div>";

    }

      
	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));
	$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config'));
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix.'symposium_config'));
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
	$from_email = $wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->prefix.'symposium_config'));
	$language = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));

	?> 
	
	<div style='margin-bottom:20px; overflow:auto;'>
	
		<img style='float:left; margin: 10px 30px 10px 10px;' src='<?php echo get_site_url().'/wp-content/plugins/wp-symposium/'; ?>logo.png' />
		
		<p><em>Symposium:</em> sym&middot;po&middot;si&middot;um;
		<ul>
		<li>&middot; A meeting or conference for discussion of a topic</li>
		<li>&middot; A collection of writings on a particular topic</li>
		<li>&middot; A convivial meeting</li>
		</ul>
		</p>
		<p><em>Sym:</em> as in simple</p>
		
		<p style='margin-top:40px'><strong>Thank you for trying Symposium.<br />
		For support, suggestions and feedback please visit <a href='http://www.wpsymposium.com'>www.wpsymposium.com</a></strong></p>
		
	</div>

	<form method="post" action=""> 
	<input type="hidden" name="symposium_update" value="Y">

	<table class="form-table"> 

	<tr valign="top"> 
	<th scope="row"><label for="language">Language</label></th> 
	<td>
	<select name="language">
		<option value='ENG'<?php if ($language == 'ENG') { echo ' SELECTED'; } ?>>English</option>
		<option value='FR'<?php if ($language == 'FR') { echo ' SELECTED'; } ?>>French</option>
	</select> 
	<span class="description">Contact info@wpsymposium.com to help with other languages, or to make corrections</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="forum_url">Forum URL</label></th> 
	<td><input name="forum_url" type="text" id="forum_url"  value="<?php echo $forum_url; ?>" class="regular-text" /> 
	<span class="description">Full URL for forum, eg: http://www.example.com/forum</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="email_footer">Email Notifications</label></th> 
	<td><input name="email_footer" type="text" id="email_footer"  value="<?php echo $footer; ?>" class="regular-text" /> 
	<span class="description">Footer appended to notification emails</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="from_email">&nbsp;</label></th> 
	<td><input name="from_email" type="text" id="from_email"  value="<?php echo $from_email; ?>" class="regular-text" /> 
	<span class="description">Email address used for email notifications</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="send_summary">Daily Digest</label></th>
	<td>
	<input type="checkbox" name="send_summary" id="send_summary" <?php if ($send_summary == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Send daily summaries to all members via email</span></td> 
	</tr> 

	<tr valign="top"> 
	<th scope="row"><label for="show_categories">Categories</label></th>
	<td>
	<input type="checkbox" name="show_categories" id="show_categories" <?php if ($show_categories == "on") { echo "CHECKED"; } ?>/>
	<span class="description">Organise forum topics by categories</span></td> 
	</tr> 


	</table> 


	<p style='margin-top:20px'>
	<span class="description">
	<strong>Notes</strong>
	<ul>
	<li>&middot;&nbsp;Daily summaries (if there is anything to send) are sent when the first visitor comes to the site after midnight, local time, for the previous day.</li>
	<li>&middot;&nbsp;Be aware of any limits set by your hosting provider for sending out bulk emails, they may suspend your website.</li>
  	<li>&middot;&nbsp;WP Symposium version: 0.1.5</li>
  	<li>&middot;&nbsp;Database version: <?php echo get_option("symposium_db_version"); ?></li>

	</ul>
	</p>	
	 
	<p class="submit"> 
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" /> 
	</p> 
	</form> 
	
	<?php
	
	if ($show_categories == "on") {
		$topics = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_category=0 AND topic_parent=0");

		if ($topics) {
			echo "<p>The following topics are un-categorised, if you want them to appear in a category, please select below.</p>";
			echo '<form method="post" action="">';
			echo '<input type="hidden" name="categories_update" value="Y">';
		
			echo '<table class="form-table">';

			foreach ($topics as $topic) {
				echo '<tr valign="top">';
				echo '<th scope="row"><label for="topic_category">'.$topic->topic_subject.'</label></th>';
				echo '<td>';
				echo '<input type="hidden" name="tid[]" value='.$topic->tid.' />';
				echo '<select name="topic_category[]">';
				$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats');
				if ($categories) {
					foreach ($categories as $category) {
						echo '<option value='.$category->cid.'>'.$category->title.'</option>';
					}
				}				
				echo '</select>';
				echo '</td>';
				echo '</tr>';
			}

			echo '</table>';

			echo '<p class="submit">';
			echo '<input type="submit" name="Submit" class="button-primary" value="Update Categories" />';
			echo '</p>';
			echo '</form>';

		}
	}
  
  	echo '</div>';

} 	

?>