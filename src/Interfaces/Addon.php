<?php

namespace BrownBox\Express\Interfaces;

/**
 * Interface for Addon
 *
 * @package   BB_Express
 * @author    Anton Zaroutski <anton@brownbox.net.au>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Brown Box
 */
interface Addon {

    /**
     * Register addon
     *
     * @since 1.0
     * @param string $name
     * @param array $options
     * @return mixed
     */
    public function register( $name, array $options );

    /**
     * Set addon's dependencies
     *
     * @since 1.0
     * @param array $dependency
     * @return mixed
     */
    public function set_dependencies( array $dependency );

    /**
     * Get name of addon
     *
     * @since 1.0
     * @return string
     */
    public function get_name();

    /**
     * Get current version of addon
     *
     * @since 1.0
     * @return string
     */
    public function get_current_version();

    /**
     * Get addon's dependencies
     *
     * @since 1.0
     * @return mixed
     */
    public function get_dependencies();

    /**
     * Install the addon
     *
     * @since 1.0
     * @return mixed
     */
    public function install();

    /**
     * Uninstall the addon
     *
     * @since 1.0
     * @return mixed
     */
    public function uninstall();

    /**
     * Activate the addon
     *
     * @since 1.0
     * @return mixed
     */
    public function activate();

    /**
     * Deactivate the addon
     *
     * @since 1.0
     * @return mixed
     */
    public function deactivate();

}