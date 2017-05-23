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

use BrownBox\Express\Interfaces as Interfaces;
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
            'created_at'        => date( 'Y-m-d H:i:s' ),
            'updated_at'        => null,
            'item_id'           => null,
            'view_type'         => 1,
            'referrer'          => '',
            'session_id'        => '',
            'client_id'         => '',
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
    public function get_views( $args = array(), $count = false ) {

        global $wpdb;

        $results = null;

        /*

        $defaults = array(
            'number'       => 20,
            'offset'       => 0,
            'order_id'     => 0,
            'status'       => '',
            'email'        => '',
            'orderby'      => 'order_id',
            'order'        => 'DESC',
        );

        $args  = wp_parse_args( $args, $defaults );
        if( $args['number'] < 1 ) {
            $args['number'] = 999999999999;
        }
        $where = '';
        // specific referrals
        if( ! empty( $args['order_id'] ) ) {
            if( is_array( $args['order_id'] ) ) {
                $order_ids = implode( ',', $args['order_id'] );
            } else {
                $order_ids = intval( $args['order_id'] );
            }
            $where .= "WHERE `order_id` IN( {$order_ids} ) ";
        }
        if( ! empty( $args['status'] ) ) {
            if( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if( is_array( $args['status'] ) ) {
                $where .= " `status` IN('" . implode( "','", $args['status'] ) . "') ";
            } else {
                $where .= " `status` = '" . $args['status'] . "' ";
            }
        }
        if( ! empty( $args['email'] ) ) {
            if( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if( is_array( $args['email'] ) ) {
                $where .= " `email` IN(" . implode( ',', $args['email'] ) . ") ";
            } else {
                if( ! empty( $args['search'] ) ) {
                    $where .= " `email` LIKE '%%" . $args['email'] . "%%' ";
                } else {
                    $where .= " `email` = '" . $args['email'] . "' ";
                }
            }
        }
        if( ! empty( $args['date'] ) ) {
            if( is_array( $args['date'] ) ) {
                if( ! empty( $args['date']['start'] ) ) {
                    if( false !== strpos( $args['date']['start'], ':' ) ) {
                        $format = 'Y-m-d H:i:s';
                    } else {
                        $format = 'Y-m-d 00:00:00';
                    }
                    $start = date( $format, strtotime( $args['date']['start'] ) );
                    if( ! empty( $where ) ) {
                        $where .= " AND `date` >= '{$start}'";
                    } else {
                        $where .= " WHERE `date` >= '{$start}'";
                    }
                }
                if( ! empty( $args['date']['end'] ) ) {
                    if( false !== strpos( $args['date']['end'], ':' ) ) {
                        $format = 'Y-m-d H:i:s';
                    } else {
                        $format = 'Y-m-d 23:59:59';
                    }
                    $end = date( $format, strtotime( $args['date']['end'] ) );
                    if( ! empty( $where ) ) {
                        $where .= " AND `date` <= '{$end}'";
                    } else {
                        $where .= " WHERE `date` <= '{$end}'";
                    }
                }
            } else {
                $year  = date( 'Y', strtotime( $args['date'] ) );
                $month = date( 'm', strtotime( $args['date'] ) );
                $day   = date( 'd', strtotime( $args['date'] ) );
                if( empty( $where ) ) {
                    $where .= " WHERE";
                } else {
                    $where .= " AND";
                }
                $where .= " $year = YEAR ( date ) AND $month = MONTH ( date ) AND $day = DAY ( date )";
            }
        }
        $args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];
        if ( 'total' === $args['orderby'] ) {
            $args['orderby'] = 'total+0';
        } else if ( 'subtotal' === $args['orderby'] ) {
            $args['orderby'] = 'subtotal+0';
        }
        $cache_key = ( true === $count ) ? md5( 'pw_orders_count' . serialize( $args ) ) : md5( 'pw_orders_' . serialize( $args ) );
        $results = wp_cache_get( $cache_key, 'orders' );
        if ( false === $results ) {
            if ( true === $count ) {
                $results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};" ) );
            } else {
                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
                        absint( $args['offset'] ),
                        absint( $args['number'] )
                    )
                );
            }
            wp_cache_set( $cache_key, $results, 'orders', 3600 );
        }
        */

        return $results;
    }

    /**
     * Return the number of results found for a given query
     *
     * @param array $args
     * @return int
     */
    public function count( $args = array() ) {

        return $this->get_views( $args, true );

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

        /*

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE " . $this->table_name . " (
        ...
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        // Run MySQL query
        dbDelta( $sql );

        // Save the new version of the table as a WordPress option
        update_option( $this->table_name . '_db_version', $this->version );

        */

    }

}
