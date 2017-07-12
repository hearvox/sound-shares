<?php
/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link    http://hearingvoices.com/tools/
 * @since   0.1.0
 *
 * @package    Postscript
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Register taxonomies (to get and delete its terms).
 *
 * Plugin not activated so tax registration no longer exists.
 *
 * @since   0.4.7
 */
register_taxonomy( 'postscripts', null );
register_taxonomy( 'poststyles', null );

/**
 * Remove plugin taxonomy terms, then remove taxonomy.
 *
 * @since   0.1.0
 */
if ( function_exists( 'wp_delete_term' ) ) {
    global $wp_taxonomies;
    $tax_scripts = 'postscripts';
    $tax_styles  = 'poststyles';

    $args_tax = array(
        'hide_empty' => 0,
        'get' => 'all',
        'fields' => 'ids',
    );

    if ( function_exists( 'taxonomy_exists' ) && taxonomy_exists( $tax_scripts ) ) {
        $terms = get_terms( $tax_scripts, $args_tax );

        if ( $terms ) {
            foreach ( $terms as $term ) {
                wp_delete_term( $term, $tax_scripts );
            }
        }

        unset( $wp_taxonomies[ $tax_scripts ] );
    }

    if ( function_exists( 'taxonomy_exists' ) && taxonomy_exists( $tax_styles ) ) {
        $terms = get_terms( $tax_styles, $args_tax );

        if ( $terms ) {
            foreach ( $terms as $term ) {
                wp_delete_term( $term, $tax_styles );
            }
        }

        unset( $wp_taxonomies[ $tax_styles ] );
    }
}

/**
 * Removes plugin post meta.
 *
 * @since   0.1.0
 */
if ( function_exists( 'delete_post_meta_by_key' ) ) {
    delete_post_meta_by_key ( 'postscript_meta' );
}

/**
 * Removes plugin option from database.
 *
 * @since   0.1.0
 */
if ( function_exists( 'delete_option' ) ) {
    delete_option( 'postscript' );
}
