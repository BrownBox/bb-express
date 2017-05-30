<?php

namespace BrownBox\Express\Base;

class Addon {

    /**
     * Addon's dependencies
     *
     * @var array
     */
    protected $_dependencies = array();

    /**
     * Name of the addon
     *
     * @var string
     * @access protected
     */
    protected $_name;

    /**
     * Description of the addon
     *
     * @var string
     * @access protected
     */
    protected $_description;

    /**
     * Current version of addon
     *
     * @var string
     * @access protected
     */
    protected $_current_version;

    /**
     * List of database queries that create custom database tables
     *
     * @var array
     * @access protected
     */
    protected $_custom_db_table_queries = array();

	/**
     * Class constructor
     */
    public function __construct() {}

    /**
     * Register an addon
     *
     * @param string $name
     * @param array $options
     */
    public function register( $name, array $options ) {

        $this->_name = $name;

    }

    /**
     * Get name of addon
     *
     * @return string
     * @access public
     */
    public function get_name() {

        return $this->_name;

    }

    /**
     * Get current version of addon
     *
     * @return string
     * @access public
     */
    public function get_current_version() {

        return $this->_current_version;

    }

    /**
     * Get description of addon
     *
     * @return string
     * @access public
     */
    public function get_description() {

        return $this->_description;

    }

    /**
     * Set addon's dependencies
     *
     * @param array $dependencies
     * @access public
     * @return null
     */
    public function set_dependencies( array $dependencies ) {

        foreach ( $dependencies as $dependency ) {

            $namespace = '\\BrownBox\\Express\\Dependency\\';
            $class_name = $namespace . $dependency;
            $dep_class = new $class_name();
            $this->_dependencies[] = $dep_class;

        }

    }

    /**
     * Get addon's dependencies
     *
     * @return array
     * @access public
     */
    public function get_dependencies() {

        return $this->_dependencies;

    }

    /**
     *
     *
     * @param $value
     */
    public function set_database_tables_queries(array $value) {
		$this->_custom_db_table_queries = $value;
    }

	/**
	 * Run custom database queries for the addon
	 *
	 * @access protected
	 */
	protected function maybe_update_db() {
	    global $wpdb;
	    $classname = strtolower(get_class($this));
	    $classname = str_replace('\\', '_', $classname);
	    $option_name = $classname.'_db_version';
        $current_version = get_option($option_name, '0');
        foreach ($this->_custom_db_table_queries as $version => $queries) {
            if (version_compare($current_version, $version, '<')) {
    			foreach ($queries as $query) {
    				$wpdb->query($query);
    			}
    			update_option($option_name, $version);
    			$current_version = $version;
            }
		}
	}

	/**
	 * Perform actions during the install process of a plugin
	 */
	public function install() {
		$this->maybe_update_db();
	}

	public function uninstall() {}

	public function activate() {}

	public function deactivate() {}

}
