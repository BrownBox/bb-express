<?php

namespace BrownBox\Express\Base;

class Dependency {

    /**
     * Name of the dependency
     *
     * @var string
     * @access protected
     */
    protected $_name;

    /**
     * Plugin name of the dependency
     *
     * @var string
     * @access protected
     */
    protected $_plugin_name;

    /**
     * Get name of dependency
     *
     * @return string
     * @access public
     */
    public function get_name() {

        return $this->_name;

    }

    /**
     * Set plugin's name
     *
     * @param string $plugin_name
     */
    public function get_plugin_name() {

        return $this->_plugin_name;

    }

    /**
     * Register a dependency
     *
     * @param string $name
     * @param array $options
     */
    public function register( $name, array $options ) {

    }

}