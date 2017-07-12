<?php
/**
 * Add HTML meta tags to posts for social sites
 *
 * @link    http://hearingvoices.com/tools/
 * @since   0.1.0
 *
 * @package    Postscript
 * @subpackage Postscript/includes
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
function postscript_enqueue_handles() {
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
add_action( 'wp_enqueue_scripts', 'postscript_enqueue_handles', 100000 );

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
function postscript_enqueue_script_urls() {
    if ( is_singular() && is_main_query() ) {
        $post_id         = get_the_id();
        $postscript_meta = get_post_meta( $post_id, 'postscript_meta', true );
        $options         = postscript_get_options();

        $url_style    = ( isset( $postscript_meta['url_style'] ) ) ? $postscript_meta['url_style'] : null;
        $url_script   = ( isset( $postscript_meta['url_script'] ) ) ? $postscript_meta['url_script'] : null;
        $url_script_2 = ( isset( $postscript_meta['url_script_2'] ) ) ? $postscript_meta['url_script_2'] : null;

        $css = array( 'css' );
        $js = array( 'js' );

        // If the post has a Style/Script URL value,
        // and the URL hostname/extension is in whitelist,
        // and the user-settings allow enqueue by URL.
        if ( $url_style && postscript_check_url( $url_style, $css ) && $options['allow']['urls_style'] ) {
            // Style/script handles made from string: "postscript-style-{$post_id}".
            wp_enqueue_style( "postscript-style-$post_id", esc_url_raw( $postscript_meta['url_style'] ), array() );
        }

        if ( $url_script && postscript_check_url( $url_script, $js )  && $options['allow']['urls_script'] ) {
            wp_enqueue_script( "postscript-script-$post_id", esc_url_raw( $postscript_meta['url_script'] ), array(), false, true );
        }

        if ( $url_script_2 && postscript_check_url( $url_script_2, $js ) && $options['allow']['urls_script'] == '2' ) {
            // Load second JS last (via dependency param).
            $dep = ( isset( $postscript_meta['url_script_2'] ) ) ? "postscript-script-$post_id" : '';
            wp_enqueue_script( "postscript-script-2-$post_id", esc_url_raw( $postscript_meta['url_script_2'] ), array( $dep ), false, true );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'postscript_enqueue_script_urls', 100010 );

/**
 * Adds user-entered class(es) to the body tag.
 *
 * @uses postscript_get_options()   Safely gets option from database.
 * @return  array $classes  WordPress defaults and user-added classes
 */
function postscript_class_body( $classes ) {
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
add_filter( 'body_class', 'postscript_class_body' );


/**
 * Adds user-entered class(es) to the post class list.
 *
 * @uses postscript_get_options()   Safely gets option from database.
 * @return  array $classes  WordPress defaults and user-added classes
 */
function postscript_class_post( $classes ) {
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
add_filter( 'post_class', 'postscript_class_post' );
