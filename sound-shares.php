<?php
/*
Plugin Name:       Sound Shares
Plugin URI:        http://hearingvoices.com/tools/sound-shares
Description:       NOT READY. DO NOT USE YET. Embed audio and video in your social posts (using Facebook's Open Graph protocol and Twitter's player card). To use: 1) Go to Edit Post's box for Custom Fields. 2) Click the Add New Custom Field form's link labeled "Enter New", 3) Enter the name: "soundshares" in the Name field. 4) Enter the full URL of the audiofile (or video) in the Value field. 5) Click the form's Add Custom Field button. Done.
Version:           0.1.0
Author:            Barrett Golding
Author URI:        http://hearingvoices.com/bg/
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

define( 'SOUNDSHARES_VERSION', '0.1.0' );

/*
~ Get option, check for vars.
~ Define setting vars, inc. fb_app_id, fb_admins.
~ Save setting vars to option.
    If plugin file  vars are empty, check option:
    if option vars empty, use file vars;
    if option vars not empty, use option vars;
    if file vars and option vars  don't match, save file vars to option.
~ If singular and metadata:
    Check xml namespace.
    Add ns to lang_attr hook.
    Build basic og (if option enabled).
    Change og type to video.movie, via filters, in:
        Yoast, Jetpack, Allin1, Facebook, FB Google+
    Build HTML meta tags for og video, type: video.movie,
        url: plugin post meta, w&h: pligin vars.
    IF video URL is HTTPS:
         Add od:video:secure_url.
    Surround meta tags w/ HTML comment identifying plugin?
    Add meta tags via wp_head hook.
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
 * Set these Variables for Open Graph tags.
 *
 * Saved as an WP Option, so preserved during plugin updates.
 *
 *
 * @since   0.1.0
 */

/* ------------------------------------------------------------------------ *
 * Variables for Users (you) to track usage at Facebook.
 * ------------------------------------------------------------------------ */

/* ID number of App that tracks use in Facebook Insights. */
// Get App ID at: <https://developers.facebook.com/apps/>
$soundshares_fb_app_id = '';

/* Facebook User IDs, comma-separated, allowed to view Insights: */
// Get your User ID at: <https://developers.facebook.com/tools/explorer/>
$soundshares_fb_admins = '';

/* ------------------------------------------------------------------------ *
 * That's it. Most of you don't need to set any other vars.
 * ------------------------------------------------------------------------ */

/* Size in pixels of embedded audio (or video) player. */
$soundshares_video_h   = '50';
$soundshares_video_w   = '480';

/* Set to 1 to add OG title, description, image and URL. */
// USE ONLY if your site isn't already adding these OG tags.
$soundshares_og_all = 0;

/* Set to 1 to clear all stored settings in database. */
// USE THIS ONLY to force-clear all existing settings.
// To change a setting, enter the value above (leave this at 0).
$soundshares_clear = 0;

/* ------------------------------------------------------------------------ *
 * Functions to get/set options array.
 * ------------------------------------------------------------------------ */

/**
 * Retrieves an option, and array of plugin settings, from database.
 *
 * Option functions based on Jetpack Stats:
 * @link https://github.com/Automattic/jetpack/blob/master/modules/stats.php
 *
 * @since   0.1.0
 *
 * @uses    soundshares_upgrade_options()
 * @return  array   $options    Array of plugin settings
 */
function soundshares_get_options() {
    $options = get_option( 'soundshares' );

    // Set version if not the latest.
    if ( ! isset( $options['version'] ) || $options['version'] < SOUNDSHARES_VERSION ) {
        $options = soundshares_upgrade_options( $options );
    }

    return $options;
}

/**
 * Makes array of plugin settings, merging default and new values.
 *
 * @since   0.1.0
 *
 * @uses    soundshares_set_options()
 * @param   array   $options        Array of plugin settings
 * @return  array   $new_options    Merged array of plugin settings
 */
function soundshares_upgrade_options( $options ) {
    global $soundshares_fb_app_id, $soundshares_fb_admins, $soundshares_og_all;
    global $soundshares_video_h, $soundshares_video_w, $soundshares_clear;

    $defaults = array(
        'fb_app_id' => '',
        'fb_admins' => '',
        'og_all'    => 0,
        'video_h'   => 50,
        'video_w'   => 480,
    );

    if ( is_array( $options ) && ! empty( $options ) ) {
        $new_options = array_merge( $defaults, $options );
    } else {
        $new_options = $defaults;
    }

    $new_options['version'] = SOUNDSHARES_VERSION;

    soundshares_set_options( $new_options );

    return $new_options;
}

/**
 * Sets an option in database (an array of plugin settings).
 *
 * Note: update_option() adds option if it doesn't exist.
 *
 * @since   0.1.0
 *
 * @param   array   $option     Array of plugin settings
 */
function soundshares_set_options( $options ) {
    $options_clean = soundshares_sanitize_data( $options );
    update_option( 'soundshares', $options_clean );
}

/**
 * Sanitizes values in an one- and multi- dimensional arrays.
 *
 * Used by post meta-box form before writing post-meta to database
 * and by Settings API before writing option to database.
 *
 * @link https://tommcfarlin.com/input-sanitization-with-the-wordpress-settings-api/
 *
 * @since    0.4.0
 *
 * @param    array    $input        The address input.
 * @return   array    $input_clean  The sanitized input.
 */
function soundshares_sanitize_data( $data = array() ) {
    // Initialize a new array to hold the sanitized values.
    $data_clean = array();

    // Check for non-empty array.
    if ( ! is_array( $data ) || ! count( $data )) {
        return array();
    }

    // Traverse the array and sanitize each value.
    foreach ( $data as $key => $value) {
        // For one-dimensional array.
        if ( ! is_array( $value ) && ! is_object( $value ) ) {
            // Remove blank lines and whitespaces.
            $value = preg_replace( '/^\h*\v+/m', '', trim( $value ) );
            $value = str_replace( ' ', '', $value );
            $data_clean[ $key ] = sanitize_text_field( $value );
        }

        // For multidimensional array.
        if ( is_array( $value ) ) {
            $data_clean[ $key ] = soundshares_sanitize_data( $value );
        }
    }

    return $data_clean;
}

/**
 * Sanitizes values in an one-dimensional array.
 * (Used by post meta-box form before writing post-meta to database.)
 *
 * @link https://tommcfarlin.com/input-sanitization-with-the-wordpress-settings-api/
 *
 * @since    0.4.0
 *
 * @param    array    $input        The address input.
 * @return   array    $input_clean  The sanitized input.
 */
function soundshares_sanitize_array( $input ) {
    // Initialize a new array to hold the sanitized values.
    $input_clean = array();

    // Traverse the array and sanitize each value.
    foreach ( $input as $key => $val ) {
        $input_clean[ $key ] = sanitize_text_field( $val );
    }

    return $input_clean;
}

function soundshares_remove_empty_lines( $string ) {
    return preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string );
    // preg_replace( '/^\h*\v+/m', '', $string );
}

/* ------------------------------------------------------------------------ *
 * Functions to get/set a specific options array item.
 * ------------------------------------------------------------------------ */

/**
 * Retrieves a specific setting (an array item) from an option (an array).
 *
 * @since   0.1.0
 *
 * @uses    soundshares_get_options()
 * @param   array|string    $option     Array item key
 * @return  array           $option_key Array item value
 */
function soundshares_get_option( $option_key = NULL ) {
    $options = soundshares_get_options();

    // Returns valid inner array key ($options[$option_key]).
    if ( isset( $options ) && $option_key != NULL && isset( $options[ $option_key ] ) ) {
            return $options[ $option_key ];
    } else { // Inner array key not valid.
    return NULL;
    }
}

/**
 * Sets a specified setting (array item) in the option (array of plugin settings).
 *
 * @since   0.1.0
 *
 * @uses    soundshares_set_options()
 *
 * @param   string  $option     Array item key of specified setting
 * @param   string  $value      Array item value of specified setting
 * @return  array   $options    Array of plugin settings
 */
function soundshares_set_option( $option, $value ) {
    $options = soundshares_get_options();

    $options[$option] = $value;

    soundshares_set_options( $options );
}


/**
 * Check for plugin post meta.
 *
 * @since   0.1.0
 */
function soundshares_check_postmeta() {
	if ( is_singular() && metadata_exists( 'post', get_the_ID(), 'soundshares' ) ) {

        $soundshares_url = get_post_meta( get_the_ID(), 'soundshares', true );

        // Print XML Namespaces as attributes of post's <html> tag.
        add_filter( 'language_attributes', 'soundshares_xml_namespaces' );

        // Print Open Graph video (HTML <meta>) tags in post's <head>.
        add_action( 'wp_head', 'soundshares_add_og_meta_tags', 9 );
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
function soundshares_add_xml_namespaces( $output ) {
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

function soundshares_change_og_type() {
    // Check for Jetpack og:type tag:
    if ( has_filter( 'jetpack_open_graph_tags' ) ) {
        add_filter( 'jetpack_open_graph_tags', 'soundshares_change_jetpack_og_type' );
    }

    // Check for Jetpack og:type tag:
    if ( has_filter( 'wpseo_opengraph_type' ) ) {
        add_filter( 'wpseo_opengraph_type', '__return_false' );
        // add_filter( 'wpseo_opengraph_type', 'soundshares_change_yoast_og_type', 10, 1 );
    }
}

/**
 * Change OG type tag value set by Jetpack plugin.
 *
 * Called by: soundshares_change_og_type.
 *
 * @since   0.1.0
 */
function soundshares_change_jetpack_og_type( $type ) {
    // Remove the default tag added by Jetpack
    unset( $tags['og:type'] );

    // Set Open Graph type tag to video for Facebook .
    $tags['og:type'] = 'video-movie';
}

/**
 * Change OG type tag value  set by Yoast SEO plugin..
 *
 * Called by: soundshares_change_og_type.
 *
 * @since   0.1.0
 */
function soundshares_change_yoast_og_type( $type ) {
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
function soundshares_add_og_meta_tags( $type ) {
    ?>
    <meta property="fb:app_id" content="<?php echo esc_attr( $fb_app_id ); ?>" />
    <meta property="fb:admins" content="<?php echo esc_attr( $fb_admins ); ?>"/>
    <meta property="og:type" content="video.movie"/>
    <meta property="og:video:height" content="50" />
    <meta property="og:video:width" content="480" />
    <meta property="og:video:type" content="video/mp4"/>
    <meta property='og:video'content='<?php echo esc_url( $sse_url ); ?>'>
    <?php
}


/*


Facebook Open Graph, Google+ and Twitter Card Tags
$fb_type = apply_filters('fb_og_type', $fb_type);
https://wordpress.org/plugins/wonderm00ns-simple-facebook-open-graph-tags/

Facebook
$og_type = apply_filters( 'facebook_og_type', $og_type, $post );

Jetpack:
if ( class_exists( 'Jetpack' ) ) {
	if ( in_array( 'publicize', Jetpack::get_active_modules() ) || in_array( 'sharedaddy', Jetpack::get_active_modules() ) ) {
		echo 'yo';
	} else {
		echo 'no';
	}
}

if ( class_exists( 'WPSEO_Options' ) ) {
	if ( WPSEO_Options::get_option( 'wpseo_social' ); ) {
		echo 'yo';
	} else {
		echo 'no';
	}
}

*/

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



//Add Open Graph Meta Info from the actual article data, or customize as necessary
function soundshares_og() {
    global $post;
    $post_id = $post->ID;
    $og_meta = array(); // Clear var.

    // Get post excerpt for og:descritpion value.
    if ( $excerpt = $post->post_excerpt ) {
        $excerpt = strip_tags( $post->post_excerpt );
        $excerpt = str_replace( "", "'", $excerpt );
    } else {
        $excerpt = get_bloginfo('description');
    }
/*
    $excerpt = strip_tags( $post->post_content );
    $excerpt_more = '';
    if ( strlen( $excerpt ) > 155 ) {
        $excerpt = substr( $excerpt, 0, 155 );
        $excerpt_more = '...';
    }
    $excerpt = str_replace( '"', '', $excerpt );
    $excerpt = str_replace( "'", '', $excerpt );
    $excerptwords = preg_split( '/[\n\r\t ]+/', $excerpt, -1, PREG_SPLIT_NO_EMPTY );
    array_pop( $excerptwords );
    $excerpt = implode( ' ', $excerptwords ) . $excerpt_more;
*/

    // Get post featured image URL for og:image value.
    if ( has_post_thumbnail( $post_id ) ) {
        $image_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
        $og_image = $image_src[0];
    } else { // Use default image.
        $og_image = soundshares_get_option( 'og_image ');
    }

    $og_meta['og:title']       = get_the_title();
    $og_meta['og:description'] = $og_excerpt;
    $og_meta['og:url']         = get_permalink();
    $og_meta['og:site_name']   = get_bloginfo('name');
    $og_meta['og:image']       = $og_image;

    // <meta property="og:title" content="esc_attr({Title})"/>

    return $og_meta;
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

/**
 * @todo Add settings page: FB App ID, FB Admins, User roles, post types.
 * @todo Add metabox for selected user roles and post types.
 * @todo Use WP Inline Link Checker in metabox.
 * @todo Use Preview media to metabox.
 * @since   0.1.0
 */

/**
 * Register uninstall function upon activation
 *
 * @since   0.1.0
 */
function soundshares_activate(){
    register_uninstall_hook( __FILE__, 'soundshares_uninstall' );
}
register_activation_hook( __FILE__, 'soundshares_activate' );

/**
 * Execute uninstall tasks (uninstall hook callback)
 *
 * Remove plugin post meta and option from database.
 *
 * @since   0.1.0
 */
function soundshares_uninstall() {
	// Remove plugin post meta.
    delete_post_meta_by_key ( 'soundshares' );
    // Remove plugin option.
    delete_option( 'soundshares' );
}
