<?php

namespace BrownBox\Express;

class Express {

    /**
     * Contains list of addons
     *
     * @var array
     * @access private
     */
    private $_addons = [];

    public function __construct() {

        // Parse all existing plugins
        $this->_parse_addons();

    }

    /**
     * Get list of all available addons and get them ready use
     *
     * @access private
     */
    private function _parse_addons() {

        $addons_dir = @ opendir( BB_EXPRESS_ADDONS_PATH );

        $addon_files = array();

        // Scan addons directory
        if ( $addons_dir ) {

            while (($file = readdir( $addons_dir ) ) !== false ) {

                if ( substr($file, 0, 1) == '.' ) {
                    continue;
                }

                if ( substr($file, -4) == '.php' ) {
                    $addon_files[] = $file;
                }

            }

            closedir( $addons_dir );

        }

        $classes = [];
        $namespace_prefix = '\\BrownBox\\Express\\Addon\\';

        // Generate list of addons
        foreach ( $addon_files as $addon_file ) {

            $just_class_name = str_replace( '.php', '', $addon_file );
            $class_name = $namespace_prefix . $just_class_name;
            $addon_class = new $class_name();
            $classes[ $just_class_name ] = $addon_class;

        }

        // Sort addons by name alphabetically
        ksort( $classes );

        $this->_addons = $classes;

        return $classes;

    }

    /**
     * Get addon by name
     *
     * @param string $name
     * @return mixed
     */
    public function get_addon( $name ) {

        if ( isset( $this->_addons[ $name ] ) ) {
            return $this->_addons[ $name ];
        }

        return false;

    }

    /**
     * Get all addons
     *
     * @return array
     * @access public
     */
    public function get_all_addons() {

        return $this->_addons;

    }

}