<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @see   https://hearingvoices.com/tools/sound-shares
 * @since 0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Removes plugin post meta.
 *
 * @since   0.1.0
 */
if ( function_exists( 'delete_post_meta_by_key' ) ) {
    delete_post_meta_by_key ( 'soundshares_meta' );
}

/**
 * Removes plugin option from database.
 *
 * @since   0.1.0
 */
if ( function_exists( 'delete_option' ) ) {
    delete_option( 'soundshares' );
}
