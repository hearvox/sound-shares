<?php
/**
 * Functions for getting/setting plugin option.
 *
 * @link    http://hearingvoices.com/tools/sound-shares
 * @since   0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

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
        'fb_app_id'  => '',
        'fb_admins'  => '',
        'twit_user'  => '',
        'meta_all'   => 'off',
        'video_h'    => 50,
        'video_w'    => 480,
        'user_roles' => array( 'administrator' ),
        'post_types' => array( 'post' ),
        'categories' => array( '0' ),
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
