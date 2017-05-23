<?php
namespace BrownBox\Express\Addon;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;
use BrownBox\Express\Helper as Helper;

class ViewTracking extends Base\Addon implements Interfaces\Addon {

    /**
     * Queries to create custom database tables
     *
     * @var array
     * @access protected
     */
    protected $_custom_db_table_queries = array();

    /**
     * Collection of recorded post IDs
     *
     * @var array
     * @access protected
     */
    protected $_collected_data = array();

    /**
     * Contains HTTP REFERER if exists
     *
     * @var null|string
     * @access protected
     */
    protected $_referer = null;

    /**
     * Contains an HTTP request reference for grouping view entries
     *
     * @var null|string
     * @access protected
     */
    protected $_request_reference = null;

    /**
     * Contains IP address of the client
     *
     * @var null|string
     * @access protected
     */
    protected $_ip_address = null;

    /**
     * List of post types to track
     *
     * @var array
     * @access protected
     */
    protected $_collected_post_types = array(
            'post',
            'page',
            'panel',
    );

    /**
     * Name of conversion tracking cookie
     * @const string
     */
    const CONVERSION_COOKIE = 'ct';

    /**
     * "Full" post view type
     * Number of times the post was loaded directly
     * @const integer
     */
    const RECORD_TYPE_FULL = 1;

    /**
     * "Inclusion" post view type
     * Number of times the post was included in another post
     * @const integer
     */
    const RECORD_TYPE_INCLUSION = 2;

    /**
     * "Viewport" post view type
     * Number of times the post was visible in the user's browser viewport
     * @const integer
     */
    const RECORD_TYPE_VIEWPORT = 3;

    /**
     * "Mouseover" post view type
     * Number of times the mouse cursor entered the post
     * @const integer
     */
    const RECORD_TYPE_MOUSEOVER = 4;

    /**
     * "Click" post view type
     * Number of times the user clicked within the post
     * @const integer
     */
    const RECORD_TYPE_CLICK = 5;

    /**
     * "Form Submission" view type
     * Number of times the form was successfully submitted
     * @const integer
     */
    const RECORD_TYPE_FORM_SUBMISSION = 6;

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();

        $this->_name = __('View Tracking', 'bb');
        $this->_description = __('Track views of various posts.', 'bb');
        $this->_current_version = '1.1';
        $this->_referer = $_SERVER['HTTP_REFERER'];
        $this->_ip_address = $_SERVER['REMOTE_ADDR'];
        $this->_request_reference = md5(time().rand(1, 10000).$this->_ip_address);

        $dependencies = array();

        $this->set_dependencies($dependencies);

        global $wpdb;
        $queries = array(
                '1.0' => array(
                        $wpdb->prefix.'bbx_view_tracking' =>
                            "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bbx_view_tracking (
                                view_tracking_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                request_reference CHAR(32) NOT NULL,
                                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                                updated_at datetime DEFAULT NULL,
                                post_id bigint(20),
                                view_type int(11),
                                referrer text,
                                session_id VARCHAR(32),
                                client_id VARCHAR(32),
                                PRIMARY KEY (view_tracking_id),
                                KEY (session_id),
                                KEY (client_id)
                            ) CHARACTER SET utf8 COLLATE utf8_general_ci;",

                        $wpdb->prefix.'bbx_view_tracking_archive' =>
                            "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bbx_view_tracking_archive (
                                view_tracking_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                request_reference CHAR(32) NOT NULL,
                                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                                updated_at datetime DEFAULT NULL,
                                post_id bigint(20),
                                view_type int(11),
                                referrer text,
                                session_id VARCHAR(32),
                                client_id VARCHAR(32),
                                PRIMARY KEY (view_tracking_id),
                                KEY (session_id),
                                KEY (client_id)
                            ) CHARACTER SET utf8 COLLATE utf8_general_ci;",
                ),
                '1.1' => array(
                        $wpdb->prefix.'bbx_view_tracking_users' =>
                            "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bbx_view_tracking_users (
                                view_tracking_user_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                client_id VARCHAR(32),
                                user_id BIGINT(20) UNSIGNED DEFAULT NULL,
                                email VARCHAR(256),
                                created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                                PRIMARY KEY (view_tracking_user_id),
                                KEY (client_id),
                                KEY (user_id),
                                KEY (email)
                            ) CHARACTER SET utf8 COLLATE utf8_general_ci;",
                        $wpdb->prefix.'bbx_view_tracking' => "ALTER TABLE ".$wpdb->prefix."bbx_view_tracking CHANGE post_id item_id BIGINT(20);",
                        $wpdb->prefix.'bbx_view_tracking_archive' => "ALTER TABLE ".$wpdb->prefix."bbx_view_tracking_archive CHANGE post_id item_id BIGINT(20);",
                ),
        );
        $this->set_database_tables_queries($queries);
        $this->install();

        add_action('wp', array($this, 'frontend_hooks'));
        add_action('wp', array($this, 'conversion_cookie'));
        add_action('plugins_loaded', array($this, 'ajax_hooks'));
        add_action('gform_after_submission', array($this, 'track_user'), 10, 2);

        // AJAX hooks
        add_action('wp_ajax_bbx_track_view', array($this, 'ajax_track_view'));
        add_action('wp_ajax_nopriv_bbx_track_view', array($this, 'ajax_track_view'));
        add_action('wp_ajax_bbx_track_mouseover', array($this, 'ajax_track_mouseover'));
        add_action('wp_ajax_nopriv_bbx_track_mouseover', array($this, 'ajax_track_mouseover'));
        add_action('wp_ajax_bbx_track_click', array($this, 'ajax_track_click'));
        add_action('wp_ajax_nopriv_bbx_track_click', array($this, 'ajax_track_click'));
    }

    /**
     * Declare frontend hooks
     *
     * @access public
     */
    public function frontend_hooks() {
        if (!is_admin()) {
            add_action('pre_get_posts', array($this, 'pre_get_posts_filter'));
            add_filter('posts_results', array($this, 'posts_results_filter'));
            add_action('shutdown', array($this, 'process_collected_data'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_footer', array($this, 'footer_scripts'));
        }
    }

    /**
     * Set up conversion tracking
     */
    public function conversion_cookie() {
        if (!session_id()) {
            session_start();
        }
        if (!Helper\Cookie::hasCookie(self::CONVERSION_COOKIE)) {
            $client_id = md5(time().rand(1, 10000).$this->_ip_address);
        } else {
            $client_id = $this->get_client_id();
        }
        Helper\Cookie::setCookie(self::CONVERSION_COOKIE, $client_id, time()+(6*MONTH_IN_SECONDS));

        $_SESSION['bbx_visit_number'] = $this->get_visit_number();
    }

    private function get_visit_number() {
        global $wpdb;
        $table = $wpdb->prefix.'bbx_view_tracking'; // @todo ViewTracking\ViewTrackingDB()
        $previous_visits = count($wpdb->get_results("SELECT count(*) FROM $table WHERE client_id = '".$this->get_client_id()."' AND NOT session_id = '".session_id()."' GROUP BY session_id;"));
        return $previous_visits+1;
    }

    /**
     * Add the queries post types to collection for saving into view tracking database table
     *
     * @param WP_Query $query
     * @return mixed
     * @access public
     */
    public function pre_get_posts_filter($query) {
        if (in_array($query->query['post_type'], $this->_collected_post_types)) {
            // $query->query_vars['suppress_filters'] = false; // This enables us to use the posts_results filter below, but affects too many queries (e.g. menus)
            $this->_add_to_collection($query->queried_object->ID, self::RECORD_TYPE_INCLUSION);
            $this->_add_to_collection($query->query['post_parent'], self::RECORD_TYPE_INCLUSION);
        }
        return $query;
    }

    /**
     * Attempt to get list of queried posts during post_results hook
     *
     * @param $posts
     * @return mixed
     * @access public
     */
    public function posts_results_filter( $posts ) {
        if (is_array($posts) && !empty($posts)) {
            $record_type = is_main_query() && is_singular() ? self::RECORD_TYPE_FULL : self::RECORD_TYPE_INCLUSION;
            foreach ($posts as $post) {
                $this->_add_to_collection($post->ID, $record_type);
            }
        }
        return $posts;
    }

    public function enqueue_scripts() {
        wp_enqueue_script('inview', trailingslashit(BB_EXPRESS_URL).'vendor/protonet/inview.min.js', array('jquery'), '1.1.2', true);
    }

    public function footer_scripts() {
        wp_localize_script('inview', 'bbx', array('ajaxurl' => admin_url('admin-ajax.php')));
        $selectors = array();
        foreach ($this->_collected_post_types as $post_type) {
            $selectors[] = '.type-'.$post_type;
        }
        $selector = implode(', ', $selectors);
?>
<script>
jQuery(document).ready(function() {
    var visibility = new Array;
    jQuery('<?php echo $selector; ?>').each(function() {
        var post_id = bbx_get_post_id(this);
        console.log(post_id);
        if (post_id !== false) {
            visibility[post_id] = false;
            jQuery(this).on('mouseenter', function() {
                bbx_track_mouseover(post_id);
            });
            jQuery(this).on('inview', function(event, visible) {
                if (visible && !visibility[post_id]) {
                    window.setTimeout(function() {
                        if (visibility[post_id]) {
                            bbx_track_view(post_id);
                        }
                    }, 1000); // Only track if it's visible for at least a second
                }
                visibility[post_id] = visible;
            });
            jQuery(this).on('click', function() {
                bbx_track_click(post_id);
            });
        }
    });
});

function bbx_get_post_id(element) {
    var classes = jQuery(element).attr('class').split(' ');
    for (var classIdx in classes) {
        var thisClass = classes[classIdx];
        if (thisClass.indexOf('post-') === 0) {
            return thisClass.replace('post-', '');
        }
    }
    return false;
}

function bbx_track_view(post_id) {
    jQuery.post(
        bbx.ajaxurl,
        {
            action: 'bbx_track_view',
            post_id: post_id
        }
    );
}

function bbx_track_mouseover(post_id) {
    jQuery.post(
        bbx.ajaxurl,
        {
            action: 'bbx_track_mouseover',
            post_id: post_id
        }
    );
}

function bbx_track_click(post_id) {
    jQuery.post(
        bbx.ajaxurl,
        {
            action: 'bbx_track_click',
            post_id: post_id
        }
    );
}
</script>
<?php
    }

    public function ajax_track_view() {
        if (is_numeric($_POST['post_id'])) {
            $post_id = (int)$_POST['post_id'];
            $this->add_entry($post_id, self::RECORD_TYPE_VIEWPORT);
        }
        die('Done');
    }

    public function ajax_track_mouseover() {
        if (is_numeric($_POST['post_id'])) {
            $post_id = (int)$_POST['post_id'];
            $this->add_entry($post_id, self::RECORD_TYPE_MOUSEOVER);
        }
        die('Done');
    }

    public function ajax_track_click() {
        if (is_numeric($_POST['post_id'])) {
            $post_id = (int)$_POST['post_id'];
            $this->add_entry($post_id, self::RECORD_TYPE_CLICK);
        }
        die('Done');
    }

    /**
     * Add post IDs to collection
     *
     * @param $query
     * @access protected
     */
    protected function _add_to_collection($post_id, $type = self::RECORD_TYPE_FULL) {

        if ($post_id && $this->post_id_not_in_collection($post_id, $type)) {

            if (!isset($this->_collected_data[$type])) {
                $this->_collected_data[$type] = array();
            }

            $this->_collected_data[$type][] = $post_id;

        }

    }

    /**
     * Get list of list of entries
     *
     * @access public
     */
    public function get_entries() {
        $gateway = new \BrownBox\Express\Addon\ViewTracking\ViewTrackingDB();

        $result = $gateway->get_all();

        return $result;
    }

    public function get_post_views($post_id) {
        $gateway = new \BrownBox\Express\Addon\ViewTracking\ViewTrackingDB();
        $results = $gateway->get_by('item_id', $post_id);
        $views = array(
                'week' => array(),
                'month' => array(),
                'forever' => array(),
        );
        $month = strtotime('-30 days');
        $week = strtotime('-7 days');
        foreach ($results as $result) {
            if (!isset($views['forever'][$result->view_type])) {
                $views['week'][$result->view_type] = $views['month'][$result->view_type] = $views['forever'][$result->view_type] = 0;
            }
            $views['forever'][$result->view_type]++;
            $view_time = strtotime($result->created_at);
            if ($view_time >= $month) {
                $views['month'][$result->view_type]++;
                if ($view_time >= $week) {
                    $views['week'][$result->view_type]++;
                }
            }
        }
        return $views;
    }

    /**
     * Check if post ID has not yet been recorded during current request
     *
     * @param int $post_id
     * @param int $type
     * @return bool
     * @access protected
     */
    protected function post_id_not_in_collection($post_id, $type) {
        return !isset($this->_collected_data[$type]) || !in_array($post_id, $this->_collected_data[$type]);
    }

    /**
     * Process collected data
     *
     * @access public
     */
    public function process_collected_data() {
        // Hack to include the current page as the main query doesn't seem to always get caught by the filters above
        if (is_singular()) {
            global $post;
            $this->_add_to_collection($post->ID, self::RECORD_TYPE_FULL);
        } elseif (is_archive()) {
            global $post;
            $page = get_page_by_path($post->post_type);
            $this->_add_to_collection($page->ID, self::RECORD_TYPE_FULL);
        }

        // Remove inclusion records if full record for the same page exists during the request
        $this->_clean_up_data();

        // bb_log( $this->_collected_data[ self::RECORD_TYPE_FULL ], 'Full post views');
        // bb_log( $this->_collected_data[ self::RECORD_TYPE_INCLUSION ], 'Inclusion post views');

        // Add entries
        foreach ($this->_collected_data as $record_type => $entries) {
            foreach ($entries as $post_id) {
                $this->add_entry($post_id, $record_type);
            }
        }
    }

    /**
     * Add view tracking entry to database
     *
     * @access public
     */
    public function add_entry($post_id, $type, $referrer = null) {
        if (empty($referrer)) {
            $referrer = $this->_referer;
        }
        $gateway = new ViewTracking\ViewTrackingDB();

        $row_data = array(
                'request_reference' => $this->_request_reference,
                'item_id'           => $post_id,
                'view_type'         => $type,
                'referrer'          => $referrer,
                'session_id'        => session_id(),
                'client_id'         => $this->get_client_id(),
        );

        $result = $gateway->insert($row_data);

        return $result;
    }

    /**
     * Get client ID from cookie
     * @return string
     */
    private function get_client_id() {
        return Helper\Cookie::getCookie(self::CONVERSION_COOKIE);
    }

    /**
     * Clean up collected data
     *
     * 1) Remove post IDs from inclusion views if they are also in the full post views
     */
    private function _clean_up_data() {
        foreach ($this->_collected_data[self::RECORD_TYPE_FULL] as $post_id) {
            if (isset($this->_collected_data[self::RECORD_TYPE_INCLUSION]) && ($key = array_search($post_id, $this->_collected_data[self::RECORD_TYPE_INCLUSION])) !== false) {
                unset($this->_collected_data[self::RECORD_TYPE_INCLUSION][$key]);
            }
        }
    }

    /**
     * Track form submissions
     * @param array $entry
     * @param array $form
     */
    public function track_user($entry, $form) {
        $user = $email = null;
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $email = $user->user_email;
        } else {
            // Look for an email address so we can locate the user
            foreach ($form['fields'] as $field) {
                if ($field->type == 'email') {
                    $email = $entry[$field->id];
                    $user = get_user_by('email', $email);
                    break;
                }
            }
        }

        $existing_user = $this->get_user_by($this->get_client_id());
        $insert = $update = false;
        if ($existing_user) {
            if (!empty($email)) {
                if (empty($existing_user->email)) {
                    $update = true;
                } elseif ($existing_user->email != $email) {
                    $insert = true;
                }
            }
            if ($user instanceof WP_User) {
                if (empty($existing_user->user_id)) {
                    $update = true;
                } elseif ($existing_user->user_id != $user->ID) {
                    $insert = true;
                }
            }
        } else {
            $insert = true;
        }

        if ($insert) {
            $this->insert_user($user, $email);
        } elseif ($update) {
            $this->update_user($user, $email);
        }

        $this->add_entry($form['id'], self::RECORD_TYPE_FORM_SUBMISSION);
    }

    /**
     * Get View Tracking user record by the specified field
     * @param string $value
     * @param string $field Optional. Valid values 'client_id', 'email', 'user_id'. Default 'client_id'.
     * @param boolean $singular Optional. Whether to return a single row (if false will return all matching rows). Default true.
     * @return object|array Row object or array of objects, depending on value of $singular
     */
    public function get_user_by($value, $field = 'client_id', $singular = true) {
        global $wpdb;
        $query = 'SELECT * FROM '.$wpdb->prefix.'bbx_view_tracking_users WHERE '.$field.' = %s ORDER BY date_created DESC';
        $query = $wpdb->prepare($query, $value);
        return $singular ? $wpdb->get_row($query) : $wpdb->get_results($query);
    }

    /**
     * Add user to tracking table
     * @param WP_User $user Optional if $email is specified
     * @param string $email Optional if $user is specified
     * @return int|false Result of insert query, or false if both $user and $email are empty.
     */
    private function insert_user($user = null, $email = '') {
        if (empty($user) && empty($email)) {
            return false;
        }
        global $wpdb;
        $now = Helper\DateTime::get_current_datetime();
        $data = array(
                'client_id' => $this->get_client_id(),
                'user_id' => $user->ID,
                'email' => $email,
                'created_at' => $now->format('Y-m-d H:i:s'),
        );
        return $wpdb->insert($wpdb->prefix.'bbx_view_tracking_users', $data, array('%s', '%d', '%s', '%s'));
    }

    /**
     * Update user in tracking table
     * @param WP_User $user Optional if $email is specified
     * @param string $email Optional if $user is specified
     * @return int|false Result of update query, or false if both $user and $email are empty.
     */
    private function update_user($user = null, $email = '') {
        if (empty($user) && empty($email)) {
            return false;
        }

        if (empty($email)) {
            $email = $user->user_email;
        } elseif (empty($user) && email_exists($email)) {
            $user = get_user_by('email', $email);
        }

        global $wpdb;
        $data = array();
        if ($user instanceof WP_User) {
            $data['user_id'] = $user->ID;
        }
        if (!empty($email)) {
            $data['email'] = $email;
        }

        $where = array(
                'client_id' => $this->get_client_id(),
        );
        return $wpdb->update($wpdb->prefix.'bbx_view_tracking_users', $data, $where, array('%d', '%s'), array('%s'));
    }
}
