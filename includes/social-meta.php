<?php
/**
 * Add HTML meta tags to posts for social sites
 *
 * @see   https://hearingvoices.com/tools/sound-shares
 * @since 0.1.0
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
    if ( ! empty( $post_meta['file'] ) && is_singular() && is_main_query()  ) {

        // Build array of social site data for meta tags.
        $og_tags      = soundshares_facebook_tags();
        $twitter_tags = soundshares_twitter_tags();
        $meta_tags    = array_merge( $meta_tags, $og_tags,  $twitter_tags );

        /**
         * Filter the array of data for HTML meta tags.
         *
         * Example:
         * unset( $meta_tags['og:video:height'] ); // Remove item with data.
         * $meta_tags['og:video:height'] = '100'; // Add new tag data.
         *
         * @since   0.1.0
         *
         * @param   array  $meta_tags  Array of data for mata tags
         */
        $meta_tags = apply_filters( 'soundshares_meta_tags', $meta_tags );

        // Meta tag property with URLs as content.
        $meta_urls = array(
            'og:url',
            'og:image',
            'og:video',
            'og:video:secure_url',
            'twitter:url',
            'twitter:image',
            'twitter:player'
        );

        // Output meta tags, sanitize URLs and atributes.
        echo '<!-- Sound Shares social tags (embeds media player) -->' . "\n";
        foreach ($meta_tags as $property => $content ) {
            if ( in_array( $property, $meta_urls ) ) { // If an URL.
        ?>
        <meta property="<?php echo esc_attr( $property ); ?>" content="<?php echo esc_url_raw( $content ); ?>">
        <?php
            } else {
        ?>
        <meta property="<?php echo esc_attr( $property ); ?>" content="<?php echo esc_attr( $content ); ?>">
        <?php
            }
        }
        echo '<!-- / Sound Shares tags -->' . "\n";
    }
}
add_action( 'wp_head', 'soundshares_add_meta_tags', 1 );

/**
 * Build array of data for Facebook Open Graph meta tags.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 * @uses soundshares_tags_filters()
 *
 */
function soundshares_facebook_tags() {
    global $post;
    $post_id   = $post->ID;
    $options   = soundshares_get_options();
    $post_meta = get_post_meta( $post_id, 'soundshares_meta', true );
    $og_meta   = array(); // Clear var.

    // Option to add all social tags (not just media tags)
    $meta_all  = ( $options['meta_all'] === 'on' ) ? 1 : 0;

    // Change meta tag set by other plugins.
    soundshares_tags_filters();

    // Data for title meta tag.
    if ( $meta_all || ! empty( $post_meta['title'] ) ) {
        $og_meta['og:title'] = ( ! empty( $post_meta['title'] ) )
            ? $post_meta['title'] : get_the_title();;
    }

    // Data for other non-media meta tags.
    if ( $meta_all ) {
        $og_meta['og:description'] = soundshares_get_excerpt( $post );
        $og_meta['og:url']         = get_permalink();
        $og_meta['og:site_name']   = get_bloginfo( 'name' );
    }

    // Data for image meta tags, if featured or meta box image is set.
    if ( ( $meta_all && has_post_thumbnail( $post_id ) ) || ! empty( $post_meta['image'] ) ) {
        // Use post meta image ID; if none use thumb ID.
        $image_id = ( ! empty( $post_meta['image'] ) )
            ? $post_meta['image'] : get_post_thumbnail_id( $post_id );

        $og_meta['og:image']     = soundshares_get_image_src( $image_id );
        $og_meta['og:image:alt'] = soundshares_get_image_alt( $image_id );
    }

    // Data for media meta tags.
    $og_meta['og:type']             = 'video.movie';
    $og_meta['og:video']            = $post_meta['file'];
    $og_meta['og:video:secure_url'] = $post_meta['file'];
    $og_meta['og:video:type']       = 'video/mp4';
    $og_meta['og:video:width']      = '480';
    $og_meta['og:video:height']     = '60';
    if ( ! empty( $options['fb_app_id'] ) ) {
        $og_meta['fb:app_id']       = $options['fb_app_id'];
    }
    if ( ! empty( $options['fb_admins'] ) ) {
        $og_meta['fb:admins']       = $options['fb_admins'];
    }

    return $og_meta;
}

/**
 * Build array of data for Twitter meta tags.
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

    // Option to add all social tags (not just media tags)
    $meta_all  = ( $options['meta_all'] === 'on' ) ? 1 : 0;

    // Data for title meta tag.
    if ( $meta_all || ! empty( $post_meta['title'] ) ) {
        $title = ( ! empty( $post_meta['title'] ) ) ? $post_meta['title'] : get_the_title();
        $twitter_meta['twitter:title'] = $title;
    }

    // Data for other non-media meta tags.
    if ( $meta_all ) {
        $twitter_meta['twitter:description'] = soundshares_get_excerpt( $post );
        $twitter_meta['twitter:url']         = get_permalink();
    }

    // Data for image meta tags (if featured or meta box image set).
    if ( ( $meta_all && has_post_thumbnail( $post_id ) ) || ! empty( $post_meta['image'] ) ) {
        // Use post meta image ID; if none use thumb ID.
        $image_id = ( ! empty( $post_meta['image'] ) )
            ? $post_meta['image'] : get_post_thumbnail_id( $post_id );

        $twitter_meta['twitter:image']     = soundshares_get_image_src( $image_id );
        $twitter_meta['twitter:image:alt'] = soundshares_get_image_alt( $image_id );
    }


    // Append player URL with query string of audio meta -- URL, title, and author; e.g.:
    // .../player.html?file=https%3A%2F%2Fexample.com%2Faudio.mp3&title=Title&author=Author
    // urlencode( wp_trim_words( get_the_title( $post_id ), 5 ) );
    $media_file   = $post_meta['file'];
    $media_title  = ( ! empty( $post_meta['title'] ) )
        ? $post_meta['title'] : wp_trim_words( get_the_title(), 5 );
    $media_author = ( ! empty( $post_meta['author'] ) )
        ? $post_meta['author'] : get_bloginfo( 'name' );
    $player_url  = plugin_dir_url( __FILE__ ) . 'player.html';
    $player_url .= '?file=' . urlencode( $media_file );
    $player_url .= '&title=' . urlencode( $media_title );
    $player_url .= '&author=' . urlencode( $media_author );

    // Data for media meta tags.
    $twitter_meta['twitter:card']          = 'player';
    $twitter_meta['twitter:player']        = $player_url;
    $twitter_meta['twitter:player:width']  = '440';
    $twitter_meta['twitter:player:height'] = '140';
     if ( ! empty( $options['twit_user'] ) ) {
        $twitter_meta['twitter:site'] = $options['twit_user'];
    }

    return $twitter_meta;
}

/**
 * Get post excerpt for meta tag attribute ('description').
 *
 * Based on Jetpack code for generating 'og:description':
 * @see https://github.com/Automattic/jetpack/blob/master/functions.opengraph.php'
 *
 * @param  integer $post         Post object
 * @return string  $description  Post excerpt without HTML, URLs, shortcodes.
 */
 function soundshares_get_excerpt( $post ) {
    if ( ! post_password_required() ) {
        if ( ! empty( $post->post_excerpt ) ) {
            $description = $post->post_excerpt;
        } else {
            $content_excerpt = explode( '<!--more-->', $post->post_content);
            $description     = $content_excerpt[0];
        }
    }

    // Remove URLs, HTML, and shortcodes.
    $description = strip_shortcodes( wp_strip_all_tags( $description ) );
    $description = preg_replace( '@https?://[\S]+@', '', $description );

    // Twiiter validator requires a description.
    if ( empty( $description ) ) {
        $description = __('Media by ', 'soundshshares') . get_bloginfo( 'name' ) . '.';
    }

     return $description;
 }

/**
 * Get image source URL.
 *
 * @since   0.1.0
 *
 * @param  int           $image_id   Attachment ID
 * @return false|string  $image_src  Attachment URL, or false if no image.
 *
 */
function soundshares_get_image_src( $image_id ) {
    $image_arr = wp_get_attachment_image_src( $image_id, 'large' );
    $image_src = $image_arr[0];

    return $image_src;
}

/**
 * Get alt text of image from attachment meta data.
 *
 * @since   0.1.0
 *
 * @param  int           $image_id   Attachment ID
 * @return false|string  $image_alt  Attachment alt text, or false is no meta
 *
 */
function soundshares_get_image_alt( $image_id ) {
    $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true);

    return $image_alt;
}


/**
 * Set or remove social meta tags inserted by other plugins.
 *
 * Called by soundshares_add_meta_tags().
 *
 * @since   0.1.0
 *
 */
function soundshares_tags_filters() {
    // Check for Jetpack social meta tag:
    add_filter( 'jetpack_open_graph_tags', 'soundshares_jetpack_tags' );

    // Remove Yoast SEO og:type meta tag:
    add_filter( 'wpseo_opengraph_type', '__return_false' );

    // Remove Yoast SEO twitter:card mata tag:
    add_filter( 'wpseo_output_twitter_card', '__return_false' );

    // Check for All in One SEO social meta tags.
    add_filter('aiosp_opengraph_meta','soundshare_aiosp_tags', 10, 3);
}


/**
 * Remove OG type and Twitter card tags set by Jetpack plugin.
 *
 * Called by: soundshares_tags_filters.
 *
 * @since  0.1.0
 *
 * @param  array  $tags  Array of meta tag data
 * @return void
 */
function soundshares_jetpack_tags( $tags ) {
    // Remove the default tag added by Jetpack
    unset( $tags['og:type'] );
    unset( $tags['twitter:card'] );

    // Set Open Graph type tag to video for Facebook .
    // $tags['og:type']      = 'video-movie';
    // $tags['twitter:card'] = 'player';

    return $tags;
}

/**
 * Change OG type tag value set by Yoast SEO plugin.
 *
 * Called by: soundshares_tags_filters.
 *
 * @since   0.1.0
 *
 * @param  string $type OG meta tag property 'og:type'
 * @return string       Content value for 'og:type'
 */
function soundshares_change_yoast_og_type( $type ) {
    return 'video';
}

/**
 * Change OG type and Twitter card values set by All in One SEO.
 *
 * Called by: soundshares_tags_filters.
 *
 * @param  string $value Meta tag content
 * @param  string $type
 * @param  string $field Meta tag property
 * @return string $value Meta tag content
 */
function soundshare_aiosp_tags ( $value, $type, $field ){
	if ( $field == 'type' ) {
	 	$value = 'video.movie';
		return $value;
    } elseif ( $field == 'card' ) {
	 	$value = 'player';
		return $value;
    } else {
        return $value;
    }
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
    <meta property="twitter:site" content="<?php echo esc_attr( $options['twit_user'] ); ?>">
    <?php
}
// add_filter( 'wp_head', 'soundshares_add_og_meta_tags' );

/**
 * References and notes.
 *
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
 * get_post_meta( $post_id, 'soundshares_meta', true ) returns:
 * Array
 * (
 *    [file] =>
 *    [title] =>
 *    [author] =>
 *    [image] =>
 * )
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
