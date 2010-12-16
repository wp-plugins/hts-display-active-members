<?php
/*
Plugin Name: WP Symposium Widgets
Plugin URI: http://www.wpsymposium.com
Description: Widgets for use with WP Symposium
Version: 0.1.12.1
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: GPL2
*/

/** Add our function to the widgets_init hook. **/
add_action( 'widgets_init', 'symposium_load_widgets' );

function symposium_load_widgets() {
	register_widget( 'Forumrecentposts_Widget' );
}

/** Define the Widget as an extension of WP_Widget **/
class Forumrecentposts_Widget extends WP_Widget {

	function Forumrecentposts_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_forumrecentposts', 'description' => 'Shows a number of recent posts.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'forumrecentposts-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'forumrecentposts-widget', 'Symposium Forum Recent Posts', $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb;
		extract( $args );
		$border_radius = $wpdb->get_var($wpdb->prepare("SELECT border_radius FROM ".$wpdb->prefix.'symposium_config'));
		$language_key = $wpdb->get_var($wpdb->prepare("SELECT language FROM ".$wpdb->prefix.'symposium_config'));
		$language = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'symposium_lang'." WHERE language = '".$language_key."'");
		$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }

		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$preview = apply_filters('widget_preview', $instance['preview'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		// Content of widget
		$posts = $wpdb->get_results("
			SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent 
			FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->prefix.'users'." u ON t.topic_owner = u.ID 
			ORDER BY tid DESC LIMIT 0,".$postcount); 
		
		if ($posts) {
			
			echo '<style>';
			echo '.Forumrecentposts_row * {';
			echo "	border-radius: ".$border_radius."px;";
			echo "  -moz-border-radius:".$border_radius."px;";
			echo "}";		
			echo '</style>';
			
			foreach ($posts as $post)
			{
				echo "<div class='Forumrecentposts_row' style='clear:both; margin-top:8px;'>";		
					echo "<div style='float: left; width:32px; margin-right: 5px'>";
						echo get_avatar($post->topic_owner, 32);
					echo "</div>";
					echo "<div>";
						if ($post->topic_parent > 0) {
							$text = stripslashes($post->topic_post);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							echo $post->display_name." ".$language->re." <a href='".$forum_url.symposium_permalink($post->topic_parent, "topic")."?cid=".$post->topic_category."&show=".$post->topic_parent."'>".$text."</a> ".symposium_time_ago($post->topic_date, $language_key).".<br>";
						} else {
							$text = stripslashes($post->topic_subject);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							echo $post->display_name." ".$language->re." <a href='".$forum_url.symposium_permalink($post->tid, "topic")."?cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_date, $language_key).".<br>";
						}
					echo "</div>";
				echo "</div>";
			}
		}
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
		$instance['preview'] = strip_tags( $new_instance['preview'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Recent Forum Posts', 'postcount' => '3', 'preview' => '30' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>">Widget Title:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>">Max number of posts:</label>
			<input id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'preview' ); ?>">Max length of preview:</label>
			<input id="<?php echo $this->get_field_id( 'preview' ); ?>" name="<?php echo $this->get_field_name( 'preview' ); ?>" value="<?php echo $instance['preview']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

?>