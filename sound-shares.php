<?php
/*
Plugin Name:       Sound Shares
Plugin URI:        https://hearingvoices.com/tools/sound-shares
Description:       Embed audio in your social posts by entering an audio URL in a post's Sound Shares box. (Uses Facebook's Open Graph protocol and Twitter's player card.)
Version:           0.1.0
Author:            Barrett Golding
Author URI:        https://hearingvoices.com/bg/
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       soundshares
Prefix:            soundshares
*/

/* ------------------------------------------------------------------------ *
 * Plugin init and uninstall
 * ------------------------------------------------------------------------ */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( defined( 'SOUNDSHARES_VERSION' ) ) {
    return;
}

/* ------------------------------------------------------------------------ *
 * Required Plugin Files
 * ------------------------------------------------------------------------ */
include_once( dirname( __FILE__ ) . '/includes/admin-options.php' );
include_once( dirname( __FILE__ ) . '/includes/functions.php' );
include_once( dirname( __FILE__ ) . '/includes/meta-box.php' );
include_once( dirname( __FILE__ ) . '/includes/social-meta.php' );

/**
 * Adds "Settings" link on Plugins screen (next to "Activate").

 * @since  0.1.0
 *
 * @param   string  $links HTML links for Plugins screen.
 * @return  string  $links HTML links for Plugins screen.
 */
function soundshares_plugin_settings_link( $links ) {
  $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=soundshares' ) ) . '">' . __( 'Settings', 'soundshares' ) . '</a>';
  array_unshift( $links, $settings_link );
  return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'soundshares_plugin_settings_link' );

/* ------------------------------------------------------------------------ *
 * Constants: plugin path, URI, dir, filename, and version.
 *
 * SOUNDSHARES_BASENAME 	sound-shares/sound-shares.php
 * SOUNDSHARES_DIR 			/path/to/wp-content/plugins/sound-shares/
 * SOUNDSHARES_DIR_BASENAME sound-shares/
 * SOUNDSHARES_URI https://example.com/wp-content/plugins/sound-shares/
 * ------------------------------------------------------------------------ */
define( 'SOUNDSHARES_VERSION', '0.1.0' );
define( 'SOUNDSHARES_BASENAME', plugin_basename( __FILE__ ) );
define( 'SOUNDSHARES_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SOUNDSHARES_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define(
	'SOUNDSHARES_DIR_BASENAME',
	trailingslashit( dirname( plugin_basename( __FILE__ ) ) )
);

/**
 * Load the plugin text domain for translation.
 *
 * @since   0.1.0
 *
 * @return void
 */
function soundshares_load_textdomain() {
    load_plugin_textdomain(
			'soundshares',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
}
add_action( 'plugins_loaded', 'soundshares_load_textdomain' );

/**
 * Redirect upon plugin activation to Settings screen.
 *
 * @param  string $plugin Plugin basename (e.g., "my-plugin/my-plugin.php")
 * @return void
 */
function soundshares_activation_redirect( $plugin ) {
	if ( $plugin === SOUNDSHARES_BASENAME ) {
		$redirect_uri = add_query_arg(
			array(
				'page' => 'soundshares' ),
				admin_url( 'options-general.php' )
			);
		wp_safe_redirect( $redirect_uri );
		exit;
	}
}
add_action( 'activated_plugin', 'soundshares_activation_redirect' );

/**
 * Get IDs of all posts using plugin's custom field.
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  array  $query->posts  Array of post IDs
 */
function soundshares_post_ids() {
	$query_args = array(
		'meta_key' => 'soundshares_meta',
		'fields'   => 'ids',
	);
	$query = new WP_Query( $query_args );

	return $query->posts;
}


/* ------------------------------------------------------------------------ *
 * Check site front-end posts for HTML meta tags.
 * ------------------------------------------------------------------------ */
/**
 * Get HTML output by wp_head().
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  string  wp_head() HTML output
 */
function soundshares_wp_head() {
    ob_start();
    wp_head();
    return ob_get_clean();
}

/**
 * Get meta tags with attributes from HTML.
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  string  wp_head() HTML output
 */
function soundshares_get_html_meta( $html ) {
    $metatags = '';

    if ( class_exists( 'DOMDocument' ) ) {
        $dom = new DOMDocument;
        @$dom->loadHTML( $body );
        $tags = $dom->getElementsByTagName( 'meta' );

        foreach ( $tags as $tag ) {
            $property = $link->getAttribute('property');
            $content = $link->getAttribute('content');

            if ( $property ) {
                $metatags .= "$property = $content<br>";
            }
        }
        return $metatags;
    } else {
        return false;
    }
}

/**
 * @todo Link to an FB/Tw post using Sound Shares audio embed.
 * @todo Check FB/Tw w/ SEO plugins on.
 * @todo rm timers, print_r, commmented out code.
 *
 * TODO for next version:
 * @todo Print wp_head() front-end output.
 * @todo Test onerror in audio tag for 404 URLs.
 * @todo Rm loop (from js, css, html)
 * @todo User sets own player.
 * @todo Document FB and Twitter image specs.
 * @todo Default image is site logo (if fn exists).
 * @todo Use WP Inline Link Checker in meta-box.
 * @todo Check drafts in FB/Twitter debug tools.
 * @todo List posts with plugin meta data.
 * @todo FB warnings: article:published_time, article:modified_time
 * @todo Add video duration tag using WP functions to read ID3.
 * Duration info:
 * <meta property="video:duration" content="120"/>
 * https://codex.wordpress.org/Function_Reference/wp_read_audio_metadata
 * https://codex.wordpress.org/Function_Reference/wp_read_video_metadata
 *
 * @todo Link to Jetpack's list of SEO plugins
 * https://plugins.trac.wordpress.org/browser/jetpack/trunk/class.jetpack.php#L218
 */
