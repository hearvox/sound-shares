<?php
/**
 * Add HTML meta tags to posts for social sites
 *
 * @link    http://hearingvoices.com/tools/sound-shares
 * @since   0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

/* ------------------------------------------------------------------------ *
 * Insert <meta> tags into document <head>
 * ------------------------------------------------------------------------ */

/**
 * Enqueues scripts/styles checked in the meta box form.
 *
 * The form's checkboxes are registered handles, stored as custom tax terms.
 *
 * All front-end handles must be registered before this runs,
 * via the same 'wp_enqueue_scripts' action as this function is hooked.
 * So this action fires late by getting a large number as its priority param.
 */
function soundshares_add_meta_tags() {
    if ( is_singular() && is_main_query() ) { // Run only on front-end post.

        // Custom tax term is the script/style handle.
        $scripts = get_the_terms( get_the_ID(), 'postscripts' );
        $styles  = get_the_terms( get_the_ID(), 'poststyles' );

        // If custom tax terms, sanitize then enqueue handle.
        if ( $scripts ) {
            foreach ( $scripts as $script ) {
                wp_enqueue_script( sanitize_key( $script->name  ) );
            }
        }

        if ( $styles ) {
            foreach ( $styles as $style ) {
                wp_enqueue_style( sanitize_key( $style->name ) );
            }
        }

    }
}
// add_action( 'wp_head', 'soundshares_add_meta_tags', 1 );

/**
 * Enqueues script and style URLs entered in the meta box text fields.
 *
 * URLs load after registered files above (larger action priority param).
 *
 * get_post_meta( $post_id, 'postscript_meta', true ) returns:
 * Array
 * (
 *     [url_style] => http://example.com/my-post-style.css
 *     [url_script] => http://example.com/my-post-script.js
 *     [url_script_2] => http://example.com/my-post-script-2.js
 *     [class_body] => my-post-body-class
 *     [class_post] => my-post-class
 * )
 *
 * @uses postscript_get_options()   Safely gets option from database.
 */

/**
 * Adds user-entered class(es) to the body tag.
 *
 * @uses postscript_get_options()   Safely gets option from database.
 * @return  array $classes  WordPress defaults and user-added classes
 */
function soundshares_player_url( $file, $title, $author ) {
    $post_id = get_the_ID();
    $options = postscript_get_options();

    if ( ! empty( $post_id ) && isset( $options['allow']['class_body'] ) ) {
        // Get the custom post class.
        $postscript_meta = get_post_meta( $post_id, 'postscript_meta', true );

        // If a post class was input, sanitize it and add it to the body class array.
        if ( ! empty( $postscript_meta['class_body'] ) ) {
            $classes[] = sanitize_html_class( $postscript_meta['class_body'] );
        }
    }

    return $classes;
}
// add_filter( 'wp_head', 'postscript_class_body' );


/**
 * Adds user-entered class(es) to the post class list.
 *
 * @uses postscript_get_options()   Safely gets option from database.
 * @return  array $classes  WordPress defaults and user-added classes
 */
function soundshares_add_meta_tags_all( $classes ) {
    $post_id = get_the_ID();
    $options = postscript_get_options();

    if ( ! empty( $post_id ) && isset( $options['allow']['class_post'] ) ) {
        // Get the custom post class.
        $postscript_meta = get_post_meta( $post_id, 'postscript_meta', true );

        // If a post class was input, sanitize it and add it to the post class array.
        if ( ! empty( $postscript_meta['class_post'] ) ) {
            $classes[] = sanitize_html_class( $postscript_meta['class_post'] );
        }
    }

    return $classes;
}
