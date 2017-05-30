<?php

namespace BrownBox\Express\Addon;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;
use BrownBox\Express\Helper as Helper;

class BestBefore extends Base\Addon implements Interfaces\Addon {
	/**
	 * List of post types covered by Best Before addon
	 *
	 * @var array
	 * @access private
	 */
	private $_post_types_to_cover = array();

	/**
     * Class constructor
     */
    public function __construct() {
        $this->_name = __( 'Best Before', 'bb' );
        $this->_description = __( 'Introduce expiry dates for posts', 'bb' );
        $this->_current_version = '1.1';

		$dependencies = array();

        $this->set_dependencies($dependencies);

        $post_types = apply_filters('bbx_best_before_post_types_covered', array('post', 'page'));
        $this->set_post_types_covered($post_types);
		add_filter('piklist_part_process', array($this, 'set_metabox_post_types'), 10, 2);

		add_action('init', array($this, 'frontend_hooks'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

	/**
	 * Execute cron
	 */
	public function execute_cron() {
		$this->send_expiry_notification();
		$this->unpublish_expired_posts();
	}

	/**
	 * Declare frontend hooks
	 *
	 * @access public
	 */
	public function frontend_hooks() {
		if ( ! is_admin() ) {
			// add_action( 'pre_get_posts', [ $this, 'pre_get_posts_filter' ] );
			// add_filter( 'posts_results', [ $this, 'posts_results_filter' ] );
			// add_action( 'shutdown', [ $this, 'process_collected_data' ] );
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @access public
	 * @since 1.0
	 */
	public static function enqueue_admin_scripts() {
		wp_enqueue_style( 'best-before-css', BB_EXPRESS_ADDONS_URL . '/BestBefore/css/best-before.css', array(), filemtime( BB_EXPRESS_ADDONS_URL . '/BestBefore/css/best-before.css' ) );
		wp_enqueue_script( 'best-before-js', BB_EXPRESS_ADDONS_URL . '/BestBefore/js/best-before.js', array('jquery'), '0.0.1', true );
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @access public
	 * @since 1.0
	 */
	public static function enqueue_frontend_scripts() {}

	/**
	 * Send email notification with the posts that have expired and are not enabled to automatically un-publish
	 *
	 * @access public
	 */
	public function send_expiry_notification() {
		$expired_posts = $this->get_expired_posts();

		$subject = 'Posts that need your attention';
		$recipient = \get_bloginfo('admin_email');
		$content = $this->_generate_expiry_notification_content( $expired_posts );

		// Set post to draft
		// bb_log( $content, 'Sending an email to ' . $recipient );
		// Helper\Email::send_email( $recipient, $subject, $content );
	}

	/**
	 * Generate content for the posts epxpiry notification
	 *
	 * @param array $expired_posts
	 * @return string
	 * @access private
	 */
	private function _generate_expiry_notification_content( $expired_posts ) {
		$html = '';
		$expired_posts_html = '<ul>';
		$current_date = time();

		foreach ( $expired_posts as $expired_post ) {
			// Only automatically send an email if the auto un-publish option is disabled for this post
			if ( ! $expired_post->bbx_best_before_unpublish_when_expired || empty( $expired_post->bbx_best_before_unpublish_when_expired ) ) {

				// Work out different between today's date and expiry date in seconds
				$dates_difference = $current_date - strtotime($expired_post->bbx_best_before_expiry_date);

				// Convert difference in seconds into chunks of years, days, minutes and seconds
				$converted_time = Helper\DateTime::seconds_to_time($dates_difference);

				// Generate URL for editing a post
				$edit_post_url = \get_bloginfo('url') . '/wp-admin/post.php?post=' . $expired_post->ID . '&action=edit';
				$expired_posts_html .= '<li>"' . $expired_post->post_title . '" post expired ' . $converted_time['days'] . ' day(s) ago. <a href="' . $edit_post_url . '">Edit this post.</a></li>';
			}
		}

		$expired_posts_html .= '</ul>';

		$html .= <<<MULTI
<h2>Hi there</h2>
<p>Please see below a list of posts that have expired and require your attention.</p>
{$expired_posts_html}
<p>Please note: all the posts in the list are not enabled to auto-unpublish once expired.</p>
<p>Kind regards,<br>Your site</p>
MULTI;

		return $html;
	}

	/**
	 * Automatically un-publish expired posts (that have auto un-publishing enabled)
	 *
	 * @access public
	 */
	public function unpublish_expired_posts() {
		$expired_posts = $this->get_expired_posts();
		foreach ( $expired_posts as $expired_post ) {
			// Only automatically unpublish if the corresponding option is enabled for this post
			if ( $expired_post->bbx_best_before_unpublish_when_expired ) {
				// Set post to draft
				wp_update_post(
					array(
						'ID'    =>  $expired_post->ID,
						'post_status'   =>  'draft'
					)
				);
			}
		}
	}

	/**
	 * Get a list of expired posts
	 *
	 * @return array
	 */
	public function get_expired_posts() {
		$today = date("Y-m-d");

		$args = array(
			'post_type' => $this->_post_types_to_cover,
			'meta_key' => 'bbx_best_before_expiry_date',
			'orderby'   => 'meta_value',
			'order' => 'ASC',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'bbx_best_before_expiry_date',
					'value' => $today,
					'compare' => '<=',
					'type' => 'DATE'
				)
			)
		);

		$expired_posts = get_posts( $args );

		return $expired_posts;
	}

	/**
	 * Set which post types are covered by Best Before functionality
	 *
	 * @param $value
	 * @access public
	 */
	public function set_post_types_covered(array $value) {
		$this->_post_types_to_cover = $value;
	}

	/**
	 * Get list of post types covered by Best Before functionality
	 *
	 * @param $value
	 * @access public
	 * @return array
	 */
	public function get_post_types_covered() {
		return $this->_post_types_to_cover;
	}

	/**
	 * Make meta box visible on all covered post types
	 * @param array $part
	 * @param string $folder
	 */
	public function set_metabox_post_types($part, $folder) {
	    if ($folder == 'meta-boxes' && $part['part'] == 'best-before.php') {
    	    $part['data']['post_type'] = $this->get_post_types_covered();
	    }

	    return $part;
	}
}
