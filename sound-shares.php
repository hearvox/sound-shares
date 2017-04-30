<?php
/*
Plugin Name:       Sound Shares
Plugin URI:        http://hearingvoices.com/tools/social-multimedia
Description:       Embed audio and video in your social posts (using the Open Graph protocol for Facebook). To use: In the Edit Post's Custom Field box, Add New Custom Field with the Name: <code>sss_url</code> and value of the audio or video URL (must be .mp3 or mp4). Then click Add Custom Filed button.
Version:           0.1.0
Author:            Barrett Golding
Author URI:        http://hearingvoices.com/bg/
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       soundshares
Plugin Prefix:     sosa
*/

/**
 * @todo Add settings page: FB App ID, FB Admins, User roles, post types.
 * @todo Add metabox for selected user roles and post types.
 * @todo Use WP Inline Link Checker in metabox. 
 * @todo Use Preview media to metabox.
 * @since   0.1.0
 */
 
/* ------------------------------------------------------------------------ *
 * Plugin init and uninstall
 * ------------------------------------------------------------------------ */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( defined( 'SOSA_VERSION' ) ) {
    return;
}

define( 'SOSA_VERSION', '0.1.0' );

/**
 * Register uninstall function upon activation
 *
 * @since   0.1.0
 */
function sosa_activate(){
    register_uninstall_hook( __FILE__, 'sosa_uninstall' );
}
register_activation_hook( __FILE__, 'sosa_activate' );

$fb_app_id = '';
$sse_url   = '';

/**
 * Check for plugin post meta.
 *
 * @since   0.1.0
 */
function sosa_check_postmeta() {
	if ( is_singular() && metadata_exists( 'post', get_the_ID(), 'sse_url' ) ) {

        $sse_url = get_post_meta( get_the_ID(), 'sse_url', true );

        // Print XML Namespaces as attributes of post's <html> tag.
        add_filter( 'language_attributes', 'sosa_xml_namespaces' );

        // Print Open Graph video (HTML <meta>) tags in post's <head>.
        add_action( 'wp_head', 'sosa_add_og_meta_tags', 9 );
	}
}

/**
 * Print XML Namespaces.
 *
 * Callback for filter: language_attributes.
 *
 * <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
 * website: http://ogp.me/ns/website# video: http://ogp.me/ns/video#
 * xmlns:fb="http://ogp.me/ns/fb#"
 *
 * @since   0.1.0
 */
function sosa_add_xml_namespaces( $output ) {
    $og_url    = 'http://ogp.me/ns#';
    $fb_url    = 'http://ogp.me/ns/fb#';
    $lang_attr = get_language_attributes( 'xhtml' );
    if ( strpos( $lang_attr, $og_url ) === false ) {
        $output .= ' prefix="og: http://ogp.me/ns#"';
    }
    if ( strpos( $lang_attr, $fb_url ) === false )  {
        $output .= ' xmlns:fb="http://ogp.me/ns/fb#"';
    }

    return $output;
}

function sosa_change_og_type() {
    // Check for Jetpack og:type tag:
    if ( has_filter( 'jetpack_open_graph_tags' ) ) {
        add_filter( 'jetpack_open_graph_tags', 'sosa_change_jetpack_og_type' );
    }

    // Check for Jetpack og:type tag:
    if ( has_filter( 'wpseo_opengraph_type' ) ) {
        add_filter( 'wpseo_opengraph_type', '__return_false' );
        // add_filter( 'wpseo_opengraph_type', 'sosa_change_yoast_og_type', 10, 1 );
    }
}

/**
 * Change OG type tag value set by Jetpack plugin.
 *
 * Called by: sosa_change_og_type.
 *
 * @since   0.1.0
 */
function sosa_change_jetpack_og_type( $type ) {
    // Remove the default tag added by Jetpack
    unset( $tags['og:type'] );

    // Set Open Graph type tag to video for Facebook .
    $tags['og:type'] = 'video-movie';
}

/**
 * Change OG type tag value  set by Yoast SEO plugin..
 *
 * Called by: sosa_change_og_type.
 *
 * @since   0.1.0
 */
function sosa_change_yoast_og_type( $type ) {
    return 'video';
}

/**
 * Add Open Graph video tags.
 *
 * Callback for filter: wp_head.
 *
 * @todo Add video duration tag using WP functions to read ID3.
 * <meta property="video:duration" content="120"/>
 * @link https://codex.wordpress.org/Function_Reference/wp_read_audio_metadata
 * @link https://codex.wordpress.org/Function_Reference/wp_read_video_metadata
 *
 * @since   0.1.0
 */
function sosa_add_og_meta_tags( $type ) {
    ?>
    <meta property="fb:app_id" content="<?php echo esc_attr( $fb_app_id ); ?>" />
    <meta property="og:type" content="video.movie"/>
    <meta property="og:video:height" content="50" />
    <meta property="og:video:width" content="480" />
    <meta property="og:video:type" content="video/mp4"/>
    <meta property='og:video'content='<?php echo esc_url( $sse_url ); ?>'>
    <?php
}


/*

<a href="https://developers.facebook.com/docs/platforminsights/domains">Insights</a>

User ID from <a href="https://developers.facebook.com/tools/explorer/?method=GET&path=me%3Ffields%3Did%2Cname">Graph Explorer</a> (use Submit button)
{
  "id": "100000387685599",
  "name": "Barrett Golding"
}

User ID (which can be found by viewing the Graph Explorer, and copying the ID value):

<meta property="fb:admins" content="USER_ID">

<a href="https://developers.facebook.com/tools/debug/og/object?q=<?php echo esc_url( get_permalink( get_the_ID()) ); ?>">Facebook Debugger</a>

https://developers.facebook.com/tools/debug

global $my_var;
$my_var = 'val';

global $post;
echo $post->ID;

$tags['fb:app_id'] = '189645238915';
<html lang="en-US" prefix="og: http://ogp.me/ns#">
OG URL 301: 'http://opengraphprotocol.org/schema/';
https://developer.wordpress.org/reference/functions/get_language_attributes/
 */

/**
 * Execute uninstall tasks (uninstall hook callback)
 *
 * Remove plugin post meta and option from database.
 *
 * @since   0.1.0
 */

/**
 * Validate URL.
 *
 * For future use.
 *
 * @since   0.1.0
 */
// Validate URL.
function sosa_check_url() {
    if ( filter_var( $sse_url, FILTER_VALIDATE_URL ) === false ) {
        $sse_url  = "URL Not Valid: $sse_url";
        update_post_meta( get_the_ID(), 'sse_url', $meta_value, $prev_value );
    } else {

    }
}

function sosa_uninstall() {
	// Remove plugin post meta.
    delete_post_meta_by_key ( 'smm_audio' );
    delete_post_meta_by_key ( 'smm_video' );
    // Remove plugin option.
    delete_option( 'socialmm' );
}
