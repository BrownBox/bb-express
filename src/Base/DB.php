<?php

namespace BrownBox\Express\Base;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BB Express DB base class
 *
 * @package     BB Express
 * @since       1.0
 */
abstract class DB {

    /**
     * The name of database table
     *
     * @access  public
     * @since   1.0
     */
    public $table_name;

    /**
     * The version of database table
     *
     * @access  public
     * @since   1.0
     */
    public $version;

    /**
     * The name of the primary column
     *
     * @access  public
     * @since   1.0
     */
    public $primary_key;

    /**
     * Global prefix used for all table names
     *
     * @var string
     * @since   1.0
     */
    protected $_prefix = '';

    /**
     * Get things started
     *
     * @access  public
     * @since   1.0
     */
    public function __construct() {
        global $wpdb;
        $this->_prefix = $wpdb->prefix.'bbx_';
    }

    /**
     * Whitelist of columns
     *
     * @access  public
     * @since   1.0
     * @return  array
     */
    public function get_columns() {

        return array();

    }

    /**
     * Get default column values
     *
     * @access  public
     * @since   1.0
     * @return  array
     */
    public function get_column_defaults() {

        return array();

    }

    /**
     * Retrieve a row by the primary key
     *
     * @access  public
     * @since   1.0
     * @return  object
     */
    public function get( $row_id ) {

        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );

    }

    /**
     * Retrieve rows by a specific column / value
     *
     * @access  public
     * @since   1.1
     * @return  array
     */
    public function get_by( $column, $filter_value ) {
        global $wpdb;

        $column = esc_sql( $column );

        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s;", $filter_value ) );
    }

    /**
     * Retrieve a row by a specific column / value
     *
     * @access  public
     * @since   1.0
     * @return  object
     */
    public function get_one_by( $column, $row_id ) {
        global $wpdb;

        $column = esc_sql( $column );

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
    }

    /**
     * Retrieve a specific column's value by the primary key
     *
     * @access  public
     * @since   1.0
     * @return  string
     */
    public function get_column( $column, $row_id ) {

        global $wpdb;

        $column = esc_sql( $column );

        return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );

    }

    /**
     * Retrieve a specific column's value by the the specified column / value
     *
     * @access  public
     * @since   1.0
     * @return  string
     */
    public function get_column_by( $column, $column_where, $column_value ) {

        global $wpdb;

        $column_where = esc_sql( $column_where );
        $column       = esc_sql( $column );

        return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );

    }

    /**
     * Retrieve all rows from the table
     *
     * @access  public
     * @since   1.0
     * @return  array
     */
    public function get_all() {

        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name", null ) );

    }

    /**
     * Get some rows from the table
     * @param array $args Optional
     * @return array
     */
    public function get_rows($args) {
        $defaults = array(
                'number'        => 20,
                'offset'        => 0,
                'orderby'       => $this->primary_key,
                'order'         => 'ASC',
                'filter'        => array(),
        );

        $args = wp_parse_args($args, $defaults);

        $where_sql = ' WHERE 1 = 1';
        $order_sql = $limit_sql = '';
        $where_vars = array();

        foreach ($args['filter'] as $field => $value) {
            $op = '=';
            if (is_array($value)) {
                $op = $value['op'];
                if (!empty($value['field'])) {
                    $field = $value['field']; // This way we can specify multiple filters for a single field
                }
                $value = $value['value'];
            }
            if (is_numeric($value)) {
                if (is_int($value)) {
                    $format = '%f';
                } else {
                    $format = '%d';
                }
            } else {
                $format = '%s';
            }
            $where_sql .= ' AND '.$field.' '.$op.' '.$format;
            $where_vars[] = $value;
        }

        if (!empty($args['orderby'])) {
            $order_sql = ' ORDER BY '.$args['orderby'].' '.$args['order'];
        }

        if (isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0) {
            $limit_sql = ' LIMIT '.$args['limit'];
            if (isset($args['offset']) && is_numeric($args['offset']) && $args['offset'] > 0) {
                $limit_sql = ' OFFSET '.$args['offset'];
            }
        }

        global $wpdb;
        return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->table_name.$where_sql.$order_sql.$limit_sql, $where_vars));
    }

    /**
     * Insert a new row
     *
     * @access  public
     * @since   1.0
     * @return  int
     */
    public function insert( $data, $type = '' ) {

        global $wpdb;

        $type = empty( $type )  ? '' : '_' . $type;

        // Set default values
        $data = wp_parse_args( $data, $this->get_column_defaults() );

        do_action( 'bbx_pre_insert' . $type, $data );

        // Initialise column format array
        $column_formats = $this->get_columns();

        // Force fields to lower case
        $data = array_change_key_case( $data );

        // White list columns
        $data = array_intersect_key( $data, $column_formats );

        // Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys( $data );

        $column_formats = array_merge( array_flip( $data_keys ), $column_formats );
        $wpdb->insert( $this->table_name, $data, $column_formats );

        do_action( 'bbx_post_insert' . $type, $wpdb->insert_id, $data );

        return $wpdb->insert_id;
    }

    /**
     * Update a row
     *
     * @access  public
     * @since   1.0
     * @return  bool
     */
    public function update( $row_id, $data = array(), $where = '' ) {

        global $wpdb;

        // Row ID must be positive integer
        $row_id = absint( $row_id );

        if( empty( $row_id ) ) {
            return false;
        }

        if( empty( $where ) ) {
            $where = $this->primary_key;
        }

        // Initialise column format array
        $column_formats = $this->get_columns();

        // Force fields to lower case
        $data = array_change_key_case( $data );

        // White list columns
        $data = array_intersect_key( $data, $column_formats );

        // Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys( $data );
        $column_formats = array_merge( array_flip( $data_keys ), $column_formats );

        if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
            return false;
        }

        return true;
    }

    /**
     * Delete a row identified by the primary key
     *
     * @access  public
     * @since   1.0
     * @return  bool
     */
    public function delete( $row_id = 0 ) {

        global $wpdb;

        // Row ID must be positive integer
        $row_id = absint( $row_id );

        if( empty( $row_id ) ) {
            return false;
        }

        if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
            return false;
        }

        return true;

    }

    /**
     * Check if the given table exists
     *
     * @since  1.0
     * @param  string $table The table name
     * @return bool If the table name exists
     */
    public function table_exists( $table ) {

        global $wpdb;

        $table = sanitize_text_field( $table );

        return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;

    }

    /**
     * Check if the table was ever installed
     *
     * @since  1.0
     * @return bool Returns if the table was installed and upgrade routine run
     */
    public function installed() {

        return $this->table_exists( $this->table_name );

    }

}