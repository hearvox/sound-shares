<?php
/**
 * Add HTML meta tags to posts for social sites
 *
 * @link    https://hearingvoices.com/tools/sound-shares
 * @since   0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

/* ------------------------------------------------------------------------ *
 * Insert <meta> tags into document <head>
 * ------------------------------------------------------------------------ */

// Get text printed in <html> tag (e.g., lang="en-US").
$lang_attr =  get_language_attributes( 'xhtml' );

/**
 * Print XML Namespaces into <html> tag.
 *
 * <html lang="en-US" prefix="og: http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#">
 *
 * Callback for filter: language_attributes.
 *
 * @since   0.1.0
 */
function soundshares_add_xml_namespaces( $output ) {
    global $lang_attr;
    $og_url = 'http://ogp.me/ns#';
    $fb_url = 'http://ogp.me/ns/fb#';

    // Is Open Graph ns already in <html> tag?
    if ( strpos( $lang_attr, $og_url ) === false ) {
       $output .= ' prefix="og: http://ogp.me/ns#"';
    }

    // Is Facebook ns already in <html> tag?
    if ( strpos( $lang_attr, $fb_url ) === false )  {
        $output .= ' xmlns:fb="http://ogp.me/ns/fb#"';
    }

    return $output;
}
add_filter( 'language_attributes', 'soundshares_add_xml_namespaces' );

/**
 * Add social-site meta tags to embed media player.
 *
 * Callback for filter: wp_head.
 *
 * @since   0.1.0
 *
 * @todo Add video duration tag using WP functions to read ID3.
 * <meta property="video:duration" content="120"/>
 * @link https://codex.wordpress.org/Function_Reference/wp_read_audio_metadata
 * @link https://codex.wordpress.org/Function_Reference/wp_read_video_metadata
 *
 * @uses soundshares_facebook_tags()
 * @uses soundshares_twitter_tags()
 */
function soundshares_add_meta_tags() {
    global $post;
    $post_id   = $post->ID;
    $options   = soundshares_get_options();
    $post_meta = get_post_meta( $post_id, 'soundshares_meta', true );
    $meta_tags = array(); // Clear array.

    // Run only on front-end post with a media file in Sound Shares meta box.
    if ( isset( $post_meta['file'] ) && is_singular() && is_main_query()  ) {

        // Build array of social site data for meta tags.
        $og_tags      = soundshares_facebook_tags();
        $twitter_tags = soundshares_twitter_tags();
        $meta_tags    = array_merge( $meta_tags, $og_tags,  $twitter_tags );

        // Replace default meta with Sound Shares meta box values.
        if ( isset( $post_meta['title'] ) ) {
            unset( $meta_tags['og:title'] );
            unset( $meta_tags['twitter:title'] );

            $meta_tags['og:title'] = $post_meta['title'];
            $meta_tags['twitter:title'] = $post_meta['title'];
        }

        if ( isset( $post_meta['image'] ) ) {
            unset( $meta_tags['og:image'] );
            unset( $meta_tags['twitter:image'] );

            $meta_tags['og:title'] = $post_meta['image'];
            $meta_tags['twitter:title'] = $post_meta['image'];
        }

        // Filter for users to edit array of meta tag data, e.g.:
        // unset( $meta_tags['og:video:height'] ); // Remove array item with tag data, then:
        // $meta_tags['og:video:height'] = '100'; // Add new tag data.
        $meta_tags = apply_filters( 'soundshares_meta_tags', $meta_tags );

        // Output meta tags.
        echo '<!-- Sound Shares social tags (embeds media player) -->' . "\n";
        foreach ($meta_tags as $property => $content ) {
        ?>
        <meta property="<?php echo esc_attr( $property ); ?>" content="<?php echo esc_attr( $content ); ?>">
        <?php
        }
        echo '<!-- End: Sound Shares tags -->' . "\n";
    }
}
add_action( 'wp_head', 'soundshares_add_meta_tags', 1 );

/**
 * Plugin options (settings page):
 * soundshares_get_options() returns:
 * Array
 * (
 *     [fb_app_id] => 0
 *     [fb_admins] => 0
 *     [twit_user] => 0
 *     [meta_all] => 0
 *     [video_h] => 50
 *     [video_w] => 480
 *     [version] => 0.1.0
 *     [user_roles] => Array
 *          (
 *               [0] => administrator
 *          )
 *     [post_types] => Array
 *          (
 *               [0] => post
 *          )
 *     [version] => 0.1.0
 * )
 *
 * Post settings (meta box):
 * get_post_meta( $post_id, 'soundshares_meta', true ) returns:
 * Array
 * (
 *    [file] => {media URL}
 *    [title] => {text}
 *    [author] => {name}
 *    [image] => {media URL}
 * )
 */

/**
 * Add Facebook Open Graph meta tags data.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 * @uses soundshares_change_og_type()
 *
 */
function soundshares_facebook_tags() {
    global $post;
    $post_id   = $post->ID;
    $options   = soundshares_get_options();
    $post_meta = get_post_meta( $post_id, 'soundshares_meta', true );
    $og_meta   = array(); // Clear var.

    // Change meta tag set by other plugins.
    soundshares_change_og_type();

    // Get post excerpt for og:description value.
    if ( $excerpt = $post->post_excerpt ) {
        $excerpt = wp_strip_all_tags( $post->post_excerpt );
        $excerpt = str_replace( "", "'", $excerpt );
    } else {
        $excerpt = get_bloginfo( 'description' );
    }

    // Get post featured image URL for og:image value.
    if ( has_post_thumbnail( $post_id ) ) {
        $image_id  = get_post_thumbnail_id( $post_id );
        $image_arr = wp_get_attachment_image_src( $image_id, 'large' );
        $image_src = $image_arr[0];
        $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true);
    }

    if ( isset( $options['meta_all'] ) ) {
        $og_meta['og:title']       = get_the_title();
        $og_meta['og:description'] = $excerpt;
        $og_meta['og:url']         = get_permalink();
        $og_meta['og:site_name']   = get_bloginfo('name');
        $og_meta['og:image']       = $image_src;
    }

    $og_meta['og:type']             = 'video.movie';
    $og_meta['og:video']            = $post_meta['file'];
    $og_meta['og:video:secure_url'] = $post_meta['file'];
    $og_meta['og:video:type']       = 'video/mp4';
    $og_meta['og:video:width']      = ( isset( $options['video_w'] ) ) ? $options['video_w'] : '480';
    $og_meta['og:video:height']     = ( isset( $options['video_h'] ) ) ? $options['video_w'] : '50';
    if ( isset( $options['fb_app_id'] ) ) {
        $og_meta['fb:app_id']       = $options['fb_app_id'];
    }
    if ( isset( $options['fb_admins'] ) ) {
        $og_meta['fb:admins']       = $options['fb_admins'];
    }

    return $og_meta;
}

/**
 * Add Facebook Open Graph meta tags data.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 */
function soundshares_twitter_tags() {
    global $post;
    $post_id   = $post->ID;
    $options   = soundshares_get_options();
    $post_meta = get_post_meta( $post_id, 'soundshares_meta', true );
    $twitter_meta = array(); // Clear array.

    // Get post excerpt for og:descritpion value.
    if ( $excerpt = $post->post_excerpt ) {
        $excerpt = wp_strip_all_tags( $post->post_excerpt );
        $excerpt = str_replace( "", "'", $excerpt );
    } else {
        $excerpt = get_bloginfo( 'description' );
    }

    // Get post excerpt for og:description value.
    if ( $excerpt = $post->post_excerpt ) {
        $excerpt = wp_strip_all_tags( $post->post_excerpt );
        $excerpt = str_replace( "", "'", $excerpt );
    } else {
        $excerpt = get_bloginfo( 'description' );
    }

    // Get post featured image URL for og:image value.
    if ( has_post_thumbnail( $post_id ) ) {
        $image_id  = get_post_thumbnail_id( $post_id );
        $image_arr = wp_get_attachment_image_src( $image_id, 'large' );
        $image_src = $image_arr[0];
        $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true);
    }

    if ( isset( $options['meta_all'] ) ) {
        $twitter_meta['twitter:title']       = get_the_title();
        $twitter_meta['twitter:description'] = $excerpt;
        $twitter_meta['twitter:url']         = get_permalink();
        $twitter_meta['twitter:image']       = $image_src;
        $twitter_meta['twitter:image:alt']   = $image_alt;
    }

    // Append player URL with query string of audio meta -- URL, title, and author; e.g.:
    // /plugins/sound-shares/player.php?file=https%3A%2F%2Fexample.com%2Faudio.mp3&title=Title&author=Author
    $file     = '?file=' . urlencode( $post_meta['file'] );
    $title    = ( isset( $post_meta['title'] ) ) ? '$title=' . urlencode( $post_meta['title'] ) : '';
    $author   = ( isset( $post_meta['author'] ) ) ? '&author=' . urlencode( $post_meta['author'] ) : '';
    $meta_str = $file . $title . $author;
    $play_url = plugin_dir_url( __FILE__ ) . 'player.php' . $meta_str;

    $twitter_meta['twitter:card']          = 'player';
    $twitter_meta['twitter:player']        = $play_url;
    $twitter_meta['twitter:player:width']  = ( isset( $options['video_w'] ) ) ? $options['video_w'] : '480';
    $twitter_meta['twitter:player:height'] = ( isset( $options['video_h'] ) ) ? $options['video_w'] : '75';
    $twitter_meta['twitter:site']          = $options['twit_user'];

    return $twitter_meta;
}

/**
 * Add Facebook Open Graph meta tags data.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 */


/**
 * Adjust social meta tags added by other plugins.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 */
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
    $options = soundshares_get_options();
    ?>
    <meta property="fb:app_id" content="<?php echo esc_attr( $options['fb_app_id'] ); ?>" />
    <meta property="fb:admins" content="<?php echo esc_attr( $options['fb_admins'] ); ?>"/>
    <meta property="og:type" content="video.movie"/>
    <meta property="og:video:height" content="<?php echo esc_attr( $options['video_h'] ); ?>" />
    <meta property="og:video:width" content="<?php echo esc_attr( $options['video_w'] ); ?>" />
    <meta property="og:video:type" content="video/mp4"/>
    <meta property='og:video'content='<?php // echo esc_url( $sse_url ); ?>'>
    <meta property="twitter:card" content="player" />
    <meta property="twitter:player" content="https://www.wnyc.org/widgets/ondemand_player/#file=https%3A%2F%2Faudio3.wnyc.org%2Fbl%2Fbl040213cpod.mp3&amp;containerClass=wnyc" />
    <meta property="twitter:player:width" content="280" />
    <meta property="twitter:player:height" content="54" />
    <meta property="twitter:image:src" content = "http://www.wnyc.org/i/200/200/80/1/wnyc-logo200.png" />
    <meta property="twitter:image" content="https://media2.wnyc.org/i/1200/627/c/80/1/Maya.JPG" />
    <meta name="twitter:site" content="<?php echo esc_attr( $options['twit_user'] ); ?>">
    <?php
}
// add_filter( 'wp_head', 'soundshares_add_og_meta_tags' );

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

<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
website: http://ogp.me/ns/website# video: http://ogp.me/ns/video#
xmlns:fb="http://ogp.me/ns/fb#"

Theme logo WP v4.5
$custom_logo_id = get_theme_mod( 'custom_logo' );
$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
$image[0];
*/
