<?php

/**
 * Enqueue custom admin styles
 */
function bb_express_enqueue() {

    // Plugin styles
    wp_enqueue_style( 'bb-express-css', plugin_dir_url( __FILE__ ) . 'assets/css/bb-express.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/bb-express.css' ) );

}
add_action( 'admin_enqueue_scripts', 'bb_express_enqueue' );
