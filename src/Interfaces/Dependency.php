<?php

namespace BrownBox\Express\Interfaces;

/**
 * Interface for a Plugin Dependency
 *
 * @package   BB_Express
 * @author    Anton Zaroutski <anton@brownbox.net.au>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Brown Box
 */
interface Dependency {

    /**
     * Register a dependency
     *
     * @param array $name
     * @param array $options
     * @return mixed
     */
    public function register( $name, array $options );

    /**
     * Get name
     *
     * @return string
     */
    public function get_name();

    /**
     * Get plugin name
     *
     * @return string
     */
    public function get_plugin_name();

}