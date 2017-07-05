<?php
/*
Plugin Name: BB Express
Plugin URI: http://brownbox.net.au
Description: Brown Box Express
Version: 0.3.5
Author: Brown Box
Author URI: http://brownbox.net.au
License: GPL2
Plugin Type: Piklist
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Set up constants
define( 'BB_EXPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BB_EXPRESS_SRC_PATH', BB_EXPRESS_PATH . 'src' );
define( 'BB_EXPRESS_ASSETS_PATH', BB_EXPRESS_PATH . 'assets' );
define( 'BB_EXPRESS_CSS_PATH', BB_EXPRESS_ASSETS_PATH . '/css' );
define( 'BB_EXPRESS_VENDOR_PATH', BB_EXPRESS_PATH . 'vendor' );
define( 'BB_EXPRESS_VENDOR_PIKLIST_PATH', BB_EXPRESS_VENDOR_PATH . '/piklist' );
define( 'BB_EXPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'BB_EXPRESS_SRC_URL', BB_EXPRESS_URL . 'src' );
define( 'BB_EXPRESS_ADDONS_URL', BB_EXPRESS_SRC_URL . '/Addon' );
define( 'BB_EXPRESS_VER', '0.3.5' );
define( 'BB_EXPRESS_BASENAME', plugin_basename( __FILE__ ) );

// "src" directories
define( 'BB_EXPRESS_ADDONS_PATH', BB_EXPRESS_SRC_PATH . '/Addon' );
define( 'BB_EXPRESS_DEPENDENCY_PATH', BB_EXPRESS_SRC_PATH . '/Dependency' );

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once BB_EXPRESS_PATH . '/bb-express-scripts.php';
// require_once BB_EXPRESS_VENDOR_PIKLIST_PATH . '/piklist.php';

require_once BB_EXPRESS_PATH . '/bb-express-hooks.php';


// Register an autoloader
spl_autoload_register( 'bb_express_autoload' );

/**
 * BB Express autoloader implementation
 *
 * @param $class
 */
function bb_express_autoload( $class ) {

    $prefix = 'BrownBox\\Express\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }

}

/**
 * Hack to get the code going until the concrete way of launching has been developed
 *
 * @todo fix this up
 */
function bb_express_run() {
    require_once(trailingslashit(BB_EXPRESS_SRC_PATH).'Updates.php');
    if (is_admin()) {
        new \BrownBox\Express\Updates(__FILE__, 'BrownBox', 'bb-express');
    }

    $bb_express = new \BrownBox\Express\Express();
}

add_action('init', 'bb_express_run', 1);

// @todo write functionality for global settings
// $settings = new BrownBox\Express\Settings();

// @todo Introduce the dedicated directories for addons

// $bb_express->get_all_addons();
// $bb_express->launch();

add_action('phpmailer_init', 'bb_set_mail_envelope_id');
function bb_set_mail_envelope_id($phpmailer) {
    $phpmailer->Sender = $phpmailer->From;
}
