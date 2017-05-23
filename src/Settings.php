<?php

namespace BrownBox\Express;

class Settings {

    public function __construct(){

        $this->register_plugin_settings();

        /*
        $json_settings = <<<MULTI
{
	"paths": {
	    "addons": "Addon",
	    "interfaces": "Interfaces",
	    "dependency": "Dependency"
	}
}
MULTI;

        $this->_process_settings( $json_settings );
        */

    }

    /**
     * Process settings
     *
     * @param string $json_settings
     * @throws Exception
     */
    private function _process_settings( $json_settings ) {

        $settings = json_decode( $json_settings );

        if ( ! $settings ) {
            throw new Exception( __( 'Failed to parse the settings file', 'bb_express' ) );
        }

        foreach ( $settings as $key => $options ) {

            switch ( $key ) {

                case 'paths':

                    foreach ( $options as $option => $value ) {
                    }

                    break;

            }

        }

    }

    private function _get_setting( $section, $option ) {

    }

    public function get_addons_path() {

        return $this->_get_setting( $section = 'paths', $option = 'addons' );

    }

    /**
     * Register plugin's setting with the WordPress installation
     */
    private function register_plugin_settings() {

        add_filter( 'piklist_admin_pages', [ $this, 'register_plugin_general_settings' ] );

    }

    /**
     * Register plugin's general settings with the WordPress installation
     *
     * @param array $pages
     * @return array
     */
    public function register_plugin_general_settings( $pages ) {

        // Register top level settings page
        $pages[] = array(

            'page_title' => __('Dashboard'),
            'menu_title' => __('BB Express', 'bbx'),
            'capability' => 'manage_options',
            'menu_slug' => 'bbx',
            'setting' => 'bbx',
            'menu_icon' => 'dashicons-admin-network',
            'page_icon' => 'dashicons-admin-network',
            'save_text' => 'Save Settings',

        );

        $pages[] = array(

            'page_title' => __('General Settings'),
            'menu_title' => __('General Settings', 'bbx'),
            'sub_menu' => 'bbx',
            'capability' => 'manage_options',
            'menu_slug' => 'bbx-general-settings',
            'setting' => 'bbx-general-settings',
            'menu_icon' => plugins_url('piklist/parts/img/piklist-icon.png'),
            'page_icon' => plugins_url('piklist/parts/img/piklist-page-icon-32.png'),
            'save_text' => 'Save Settings',

        );

        $pages[] = array(

            'page_title' => __('Addons'),
            'menu_title' => __('Addons', 'bbx'),
            'sub_menu' => 'bbx',
            'capability' => 'manage_options',
            'menu_slug' => 'bbx-addons',
            'setting' => 'bbx-addons',
            'menu_icon' => plugins_url('piklist/parts/img/piklist-icon.png'),
            'page_icon' => plugins_url('piklist/parts/img/piklist-page-icon-32.png'),
            'save_text' => 'Save Settings',

        );

        return $pages;

    }

}