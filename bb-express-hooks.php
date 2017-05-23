<?php

/**
 * @file bb-express-hooks.php
 *
 * This file contains the hooks related to general functionality of the plugin
 */

/**
 * Remove the Piklist admin page for the menu
 */
add_filter( 'piklist_admin_pages', function( $admin_pages ) {

    $updated_admin_pages = [];

    foreach ( $admin_pages as $admin_page ) {

        if ( false === strpos( $admin_page['menu_slug'], 'piklist' ) && false === strpos( $admin_page['menu_slug'], 'shortcode_editor' ) ) {
            $updated_admin_pages[] = $admin_page;
        }

    }

    return $updated_admin_pages;

});
