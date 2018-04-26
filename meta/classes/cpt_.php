<?php
namespace bb_meta;
class cptClass
{
	function __construct($singular, $plural, array $args = array())
	{
		$this->plural = $plural;
		$this->singular = $singular;
		$this->args = $args;
		add_action('init', array(
			$this,
			'cpt_mycpt'
		));
		add_action('admin_head', array(
			$this,
			'cpt_mycpt_header'
		));
		add_filter('post_updated_messages', array(
			$this,
			'cpt_mycpt_messages'
		));
	}

	function cpt_mycpt()
	{
		$labels = array(
			'name' => _x(ucfirst($this->plural) , 'post type general name') ,
			'singular_name' => _x(ucfirst($this->singular) , 'post type singular name') ,
			'add_new' => _x('Add New', ucfirst($this->singular)) ,
			'add_new_item' => __('Add New ' . ucfirst($this->singular)) ,
			'edit_item' => __('Edit ' . ucfirst($this->singular)) ,
			'new_item' => __('New ' . ucfirst($this->singular)) ,
			'all_items' => __('All ' . ucfirst($this->plural)) ,
			'view_item' => __('View ' . ucfirst($this->singular)) ,
			'search_items' => __('Search ' . ucfirst($this->plural)) ,
			'not_found' => __('No ' . ucfirst($this->plural) . ' found') ,
			'not_found_in_trash' => __('No ' . ucfirst($this->plural) . ' found in the Trash') ,
			'parent_item_colon' => '',
			'menu_name' => ucfirst($this->plural)
		);

		$default_args = array(
			'labels' => $labels,
			'description' => 'Holds our ' . ucfirst($this->singular) . ' posts',
			'public' => true,
			'menu_position' => 20,
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt',
				'comments',
				'page-attributes'
			) ,
			'has_archive' => true,
			'hierarchical' => true
		);

		$args = array_replace_recursive($default_args, $this->args);

		register_post_type($this->singular, $args);
	}

	// Styling for the custom post type icon

	function cpt_mycpt_header()
	{
		$menu = site_url() . '/wp-content/themes/' . get_template() . '/images/mycpt_menu_icon.png';
		$icon = site_url() . '/wp-content/themes/' . get_template() . '/images/mycpt_cpt_icon.png';
		echo '<style type="text/css" media="screen">' . "\n";
		echo '	#menu-posts-' . $this->singular . ' post .wp-menu-image { background: url(' . $menu . ') no-repeat 4px -35px!important; }' . "\n";
		echo '	#menu-posts-' . $this->singular . ' post:hover .wp-menu-image {background: url(' . $menu . ') no-repeat 4px -2px!important; }' . "\n";
		echo '	#icon-edit.icon32-posts-' . $this->singular . ' post {background: url(' . $icon . ') no-repeat -1px 2px;}' . "\n";
		echo ' #' . $this->singular . ' post_value {width: 50%;}' . "\n";
		echo '</style>' . "\n";
	}

	// Set Messages

	function cpt_mycpt_messages($messages)
	{

		// http://codex.wordpress.org/Function_Reference/register_post_type

		global $post, $post_ID;
		$cpttype = strtolower($this->singular);
		$messages[$cpttype] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(__(ucfirst($this->singular) . ' Post updated.', 'your_text_domain') , esc_url(get_permalink($post_ID))) ,
			2 => __(ucfirst($this->singular) . ' updated.', 'your_text_domain') ,
			3 => __(ucfirst($this->singular) . ' deleted.', 'your_text_domain') ,
			4 => __(ucfirst($this->singular) . ' Post updated.', 'your_text_domain') ,
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf(__(ucfirst($this->singular) . ' Post restored to revision from %s', 'your_text_domain') , wp_post_revision_title((int)$_GET['revision'], false)) : false,
			6 => sprintf(__(ucfirst($this->singular) . ' Post published. <a href="%s">View ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain') , esc_url(get_permalink($post_ID))) ,
			7 => __(ucfirst($this->singular) . ' Post saved.', 'your_text_domain') ,
			8 => sprintf(__(ucfirst($this->singular) . ' Post submitted. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain') , esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))) ,
			9 => sprintf(__(ucfirst($this->singular) . ' Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain') ,

			// translators: Publish box date format, see http://php.net/date

			date_i18n(__('M j, Y @ G:i') , strtotime($post->post_date)) , esc_url(get_permalink($post_ID))) ,
			10 => sprintf(__(ucfirst($this->singular) . ' Post draft updated. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'your_text_domain') , esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))) ,
		);
		return $messages;
	}
}

?>