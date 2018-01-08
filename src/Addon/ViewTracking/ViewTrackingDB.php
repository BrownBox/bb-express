<?php

/**
 * @class ViewTrackingDB
 *
 * This class defined the structure and functionality of the bbx_view_tracking database table used
 * for in View Tracking addon
 *
 * @since 1.0
 */

namespace BrownBox\Express\Addon\ViewTracking;

use BrownBox\Express\Base as Base;

class ViewTrackingDB extends Base\DB {

    /**
     * Get things started
     *
     * @access public
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();

        $this->table_name  = $this->_prefix . 'view_tracking';
        $this->primary_key = 'view_tracking_id';
        $this->version     = '1.0';

    }

    /**
     * Get columns and formats
     *
     * @access public
     * @since 1.0
     */
    public function get_columns() {
        return array(
                'view_tracking_id'  => '%d',
                'request_reference' => '%s',
                'created_at'        => '%s',
                'updated_at'        => '%s',
                'item_id'           => '%d',
                'view_type'         => '%s',
                'referrer'          => '%s',
                'session_id'        => '%s',
                'client_id'         => '%s',
                'user_agent'        => '%s',
        );
    }

    /**
     * Get default column values
     *
     * @access public
     * @since 1.0
     */
    public function get_column_defaults() {
        return array(
                'view_tracking_id'  => null,
                'request_reference' => null,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => null,
                'item_id'           => null,
                'view_type'         => 1,
                'referrer'          => '',
                'session_id'        => '',
                'client_id'         => '',
                'user_agent'        => '',
        );
    }

    /**
     * Retrieve views from the database
     *
     * @access public
     * @since 1.0
     * @param array $args
     * @param bool  $count  Return only the total number of results found (optional)
     */
    public function get_views($args = array(), $count = false) {
        global $wpdb;

        $results = null;

        return $results;
    }

    /**
     * Return the number of results found for a given query
     *
     * @param array $args
     * @return int
     */
    public function count($args = array()) {
        return $this->get_views($args, true);
    }

    /**
     * Create the table
     *
     * @access public
     * @since 1.0
     */
    public function create_table() {
        global $wpdb;

        // @todo Move creation of table into dedicated classes
    }
}
