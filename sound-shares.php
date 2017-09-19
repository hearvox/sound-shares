<?php
/*
Plugin Name:       Sound Shares
Plugin URI:        https://hearingvoices.com/tools/sound-shares
Description:       NOT READY. DO NOT USE YET. Embed audio and video in your social posts (using Facebook's Open Graph protocol and Twitter's player card). To use: 1) Go to Edit Post's box for Custom Fields. 2) Click the Add New Custom Field form's link labeled "Enter New", 3) Enter the name: "soundshares" in the Name field. 4) Enter the full URL of the audiofile (or video) in the Value field. 5) Click the form's Add Custom Field button. Done.
Version:           0.1.0
Author:            Barrett Golding
Author URI:        https://hearingvoices.com/bg/
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       soundshares
Prefix:            soundshares
*/

/**
 *
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
  * Constants: plugin path, URI, dir, filename, and version.
  *
  * SOUNDSHARES_BASENAME 			sound-shares/sound-shares.php
	* SOUNDSHARES_DIR 					/path/to/wp-content/plugins/sound-shares/
	* SOUNDSHARES_DIR_BASENAME 	sound-shares/
	* SOUNDSHARES_URI		https://example.com/wp-content/plugins/sound-shares/
  * ------------------------------------------------------------------------ */
define( 'SOUNDSHARES_VERSION', '0.1.0' );
define( 'SOUNDSHARES_BASENAME', plugin_basename( __FILE__ ) );
define( 'SOUNDSHARES_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SOUNDSHARES_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define(
	'SOUNDSHARES_DIR_BASENAME',
	trailingslashit( dirname( plugin_basename( __FILE__ ) ) )
);

/* ------------------------------------------------------------------------ *
 * Required Plugin Files
 * ------------------------------------------------------------------------ */
include_once( dirname( __FILE__ ) . '/includes/admin-options.php' );
include_once( dirname( __FILE__ ) . '/includes/functions.php' );
include_once( dirname( __FILE__ ) . '/includes/meta-box.php' );
include_once( dirname( __FILE__ ) . '/includes/social-meta.php' );

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

/*
if ( $plugin === SOUNDSHARES_BASENAME ) {
	exit( wp_redirect(
		add_query_arg(
			array(
				'page' => 'soundshares' ),
				admin_url( 'options-general.php' )
			)
		)
	);
}
*/


}
add_action( 'activated_plugin', 'soundshares_activation_redirect' );

/**
 * Validate URL.
 *
 * For future use.
 *
 * @since   0.1.0
 */
// Validate URL.
function soundshares_check_url() {
    if ( filter_var( $sse_url, FILTER_VALIDATE_URL ) === false ) {
        $sse_url  = "URL Not Valid: $sse_url";
        update_post_meta( get_the_ID(), 'sse_url', $meta_value, $prev_value );
    } else {

    }
}

/**
 * Get IDs of all posts using plugin's custom field.
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  array   $query->posts    Array of post IDs
 */
function soundshares_post_ids() {
	$query_args = array(
		'meta_key' => 'soundshares',
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
 * @return  string       wp_head() HTML output
 */
function soundshares_wp_head() {
    ob_start();
    wp_head();
    return ob_get_clean();
}
// $wp_head = soundshares_wp_head();
// echo htmlentities( $wp_head ); // Print meta tags.

$repsonse = wp_remote_get( 'https://headwaterseconomics.org/public-lands/protected-lands/national-monuments/' );
$body = wp_remote_retrieve_body( $repsonse );

/**
 * Get HTML of an URL.
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  string       wp_head() HTML output
 */
function soundshares_get_url_html( $url ) {
    $repsonse = wp_remote_get( $url );
    $html = wp_remote_retrieve_body( $repsonse );

    return $html;
}

/**
 * Get meta tags with attributes from HTML.
 *
 * For future use.
 *
 * @since   0.1.0
 *
 * @return  string       wp_head() HTML output
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

/*
~ Docs (ReadMe.md and inline docs):
    Lists debug URLs: FB and Twit.
    List OG, FB-OG, and Twitter Card doc URLs.
    Print example OG output, basic and video.
    List TODOs: Twitter, different image, filters.
    List filters in Sound Shares, with examples.
    List other plugins that Sound Shares filters og:type.
    Link to Jetpack list of plugins that manage OG.
    Detail FB and Twit image specs.
    Link to current article.
    Link to an FB post using Sound Shares audio embed.
*/

/**
 * @todo Document item above.
 * @todo Twitter player URL must be HTTPS.
 * @todo Fix Jetpack replaced meta tags.
 * @todo Default author is site name.
 * @todo Default title is post title (shortened for player).
 * @todo Default image is site logo (if fn exists).
 * @todo Use WP Inline Link Checker in meta-box.
 * @todo Use Preview media to meta-box.
 * @todo Add video duration tag using WP functions to read ID3.
 *
 * Duration info:
 * <meta property="video:duration" content="120"/>
 * @link https://codex.wordpress.org/Function_Reference/wp_read_audio_metadata
 * @link https://codex.wordpress.org/Function_Reference/wp_read_video_metadata
 */
