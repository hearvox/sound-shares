<?php
/**
 * Admin Settings Page (Dashboard> Settings> Sound Shares)
 *
 * @link    http://hearingvoices.com/tools/sound-shares
 * @since   0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

/*
http://rji.local/wp-content/plugins/sound-shares/player.php
*/

/* ------------------------------------------------------------------------ *
 * Wordpress Settings API
 * ------------------------------------------------------------------------ */

/**
 * Adds submenu item to Settings dashboard menu.
 *
 * @since   0.1.0
 *
 * Sets Settings page screen ID: 'settings_page_postscript'.
 */
function soundshares_settings_menu() {
    $soundshares_options_page = add_options_page(
        __('Sound Shares: Embed audio in social posts', 'soundshares' ),
        __( 'Sound Shares', 'soundshares' ),
        'manage_options',
        'soundshares',
        'soundshares_settings_display'
    );
}
add_action('admin_menu', 'soundshares_settings_menu');

/**
 * Renders settings menu page.
 *
 * @since   0.1.0
 */
function soundshares_settings_display() {
    ?>
    <div class="wrap">
        <h2>Sound Shares (v <?php echo SOUNDSHARES_VERSION; ?>) <?php _e('Settings', 'soundshares' ); ?></h2>
        <!-- Create the form that will be used to render our options. -->
        <form method="post" action="options.php">
            <?php settings_fields( 'soundshares' ); ?>
            <?php do_settings_sections( 'soundshares' ); ?>
            <?php submit_button(); ?>
        </form>
    </div><!-- .wrap -->
    <?php
}

/*

Use Sound Shares to add Facebook OG title, description, image, and url HTML meta tags to audio pages.

fb:app_id" content="1234567890987654321">
<meta property="fb:admins

FB app ID3
FB user IDs
Twitter User name


<meta property="og:title" content="Program Title">
<meta property="og:description" content="Program description for the link preview.">
<meta property="og:image" content="https://example.org/program-image.jpg">
<meta property="og:url" content="https://example.org/program-page/">
<meta property="og:site_name" content="WNYC" />
<meta property="og:type" content="video.movie">
<meta property="og:video" content="https://example.org/program-audio.mp3">
<meta property="og:video:secure_url" content="https://example.org/program-audio.mp3">
<meta property="og:video:type" content="video/mp4">
<meta property="og:video:width" content="480">
<meta property="og:video:height" content="50">
<meta property="fb:app_id" content="1234567890987654321">
<meta property="fb:admins" content="9876543210,1234567">
<meta property="twitter:card" content="player" />
<meta property="twitter:player" content="https://www.wnyc.org/widgets/ondemand_player/#file=https%3A%2F%2Faudio3.wnyc.org%2Fbl%2Fbl040213cpod.mp3&amp;containerClass=wnyc" />
<meta property="twitter:player:width" content="280" />
<meta property="twitter:player:height" content="54" />
<meta property="twitter:image:src" content = "http://www.wnyc.org/i/200/200/80/1/wnyc-logo200.png" />
<meta name="twitter:image" content="https://media2.wnyc.org/i/1200/627/c/80/1/Maya.JPG" />
<meta name="twitter:image:alt" content="Alternative test describing image for non-visual users" />
<meta name="twitter:site" content="@twitter_username">

<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">

*/


/* ------------------------------------------------------------------------ *
 * Setting Registrations
 * ------------------------------------------------------------------------ */

/**
 * Creates settings fields via WordPress Settings API.
 *
 * @since   0.1.0
 */
function soundshares_options_init() {

    // Array to pass to $callback functions as add_settings_field() $args (last param).
    $options = soundshares_get_options(); // Option: 'soundshares'.

    add_settings_section(
        'soundshares_settings_section',
        __( 'Sound Shares meta box', 'soundshares' ),
        'soundshares_section_callback',
        'soundshares'
    );

    add_settings_field(
        'soundshares_fields_facebook',
        __( 'Facebook', 'soundshares' ),
        'soundshares_fields_facebook_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_fields_twitter',
        __( 'Twitter', 'soundshares' ),
        'soundshares_fields_twitter_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_fields_meta_tags',
        __( 'HTML Meta Tags', 'soundshares' ),
        'soundshares_fields_meta_tags_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_user_roles',
        __( 'User roles', 'postscript' ),
        'soundshares_user_roles_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_post_types',
        __( 'Post types', 'postscript' ),
        'soundshares_post_types_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_categories',
        __( 'Categories', 'postscript' ),
        'soundshares_categories_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_section(
        'soundshares_footer_section',
        __( 'Sound Shares plugin information', 'soundshares' ),
        'soundshares_footer_callback',
        'soundshares'
    );

    register_setting(
        'soundshares',
        'soundshares',
        'soundshares_sanitize_data'
    );

}
add_action('admin_init', 'soundshares_options_init');

/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */

/**
 * Outputs text for the top of the Settings screen.
 *
 * @since   0.1.0
 */
function soundshares_section_callback() {
    ?>
    <p><?php _e('Sound Shares embeds an audio (or video) player in the link preview of your socially shared posts.', 'soundshares' ); ?></p>
    <p><?php _e('This plugin inserts <code>twitter:</code> and <code>og:</code> HTML meta tags for Twitter and Facebook players.', 'soundshares' ); ?></p>
    <?php
}

/**
 * Outputs HTML form text fields (default Facebook HTML meta tag values).
 *
 * @since   0.1.0
 *
 * @param   array   $options    Array of plugin settings
 */
function soundshares_fields_facebook_callback( $options ) {
    ?>
    <fieldset>
        <legend><?php _e( 'Enter your Facebook settings:', 'soundshares' ); ?></legend>
        <ul class="inside">
            <li>
                <label><input type="text" id="soundshares-fb-app-id" name="soundshares[fb_app_id]" value="<?php if ( isset ( $options['fb_app_id'] ) ) { echo esc_attr( $options['fb_app_id'] ); } ?>" /> <?php _e( 'App ID', 'soundshares' ); ?></label></li>
            <li>
                <label><input type="text" id="soundshares-fb-admins" name="soundshares[fb_admins]" value="<?php if ( isset ( $options['fb_admins'] ) ) { echo esc_attr( $options['fb_admins'] ); } ?>" /> <?php _e( 'Admin(s)', 'soundshares' ); ?></label>
                <p class="wp-ui-text-icon"><?php _e( 'To track <a href="https://developers.facebook.com/docs/sharing/insights">Facebook Sharing Insights</a>, enter an App ID (from your <a href="https://developers.facebook.com/apps/redirect/dashboard">Facebook App Dashboard</a>). To allow users access to Insights, enter their Facebook user IDs (separated by a comma; find IDs via the <a href="https://developers.facebook.com/tools/explorer/?method=GET&amp;path=me%3Ffields%3Did%2Cname">Graph Explorer</a>).', 'postscript' ); ?></p>
            </li>
        </ul>
    </fieldset>
    <hr id="twitter" />
    <?php
}


/**
 * Outputs HTML form text fields (default Twitter HTML meta tag values).
 *
 * @since   0.1.0
 *
 * @param   array   $options    Array of plugin settings
 */
function soundshares_fields_twitter_callback( $options ) {
    ?>
    <fieldset>
        <legend><?php _e( 'Enter your twitter settings:', 'soundshares' ); ?></legend>
        <ul class="inside">
            <li>
                <label><input type="text" id="soundshares-facebook-app-id" name="soundshares[twit_user]" value="<?php if ( isset ( $options['twit_user'] ) ) { echo esc_attr( $options['twit_user'] ); } ?>" /> <?php _e( 'Site name', 'soundshares' ); ?></label>
                <p class="wp-ui-text-icon"><?php _e( 'Enter the username for the Twitter account associated with your site (e.g., @account_name).', 'postscript' ); ?></p>
            </li>
        </ul>
    </fieldset>
    <hr id="meta" />
    <?php
}

/**
 * Outputs an HTML checkbox to add all meta tags.
 *
 * Default is off, assumes another plugin add social tags (title, URL, etc.)
 * so plugin only adds tags for embedded player.
 *
 * @since   0.1.0
 *
 * @param   array   $options    Array of plugin settings
 */
function soundshares_fields_meta_tags_callback( $options ) {
    ?>
    <fieldset>
        <legend><?php _e( 'Add all social meta tags to your posts (not just for the embedded player). Inspect your site\'s HTML source code. Check this box <strong>only if no</strong> <code>twitter:</code> and <code>og:</code> tags are already there (e.g., via an SEO plugin).', 'soundshares' ); ?></legend>
        <ul class="inside">
            <li>
                <label><input type="checkbox" id="soundshares-meta-all" name="soundshares[meta_all]" value="on"<?php checked( 'on', isset( $options['meta_all'] ) ? $options['meta_all'] : 'off' ); ?>/> <?php _e( 'Add all social tags', 'postscript' ); ?></label>
            </li>
        </ul>
    </fieldset>
    <hr id="roles" />
    <?php
}

/**
 * Outputs HTML checkboxes of user roles (to choose which roles display Sound Shares meta box).
 *
 * @since   0.1.0
 *
 *  @param   array   $options    Array of plugin settings
 */
function soundshares_user_roles_callback( $options ) {
    // Need WP_User class.
    if ( ! function_exists( 'get_editable_roles' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/user.php' );
    }

    // Note: $options[0] below is array of user-selected roles, from 'soundshares' option.
    ?>
    <fieldset>
        <legend><?php _e( 'Allow Sound Shares only for these roles:', 'soundshares' ); ?></legend>
        <ul class="inside">
        <?php
        foreach ( get_editable_roles() as $role => $details ) {
        ?>
            <li><label><input type="checkbox" id="<?php echo esc_attr( $role ); ?>" value="<?php echo esc_attr( $role ); ?>" name="soundshares[user_roles][]"<?php checked( in_array( $role, $options['user_roles'] ) ); ?><?php disabled( 'administrator', $role ); ?> /> <?php echo esc_html( translate_user_role( $details['name'] ) ); ?></label></li>
        <?php
        }
        ?>
            <input type="hidden" value="administrator" name="soundshares[user_roles][]" />
        </ul>
    </fieldset>
    <?php
}

/**
 * Outputs HTML checkboxes of post types (to choose which display Sound Shares meta box).
 *
 * @since   0.1.0
 *
 * @param   array   $options    Array of plugin settings
 */
function soundshares_post_types_callback( $options ) {
    ?>
    <fieldset>
        <legend><?php _e( 'Allow Sound Shares only for selected post types:', 'soundshares' ); ?></legend>
        <ul class="inside">
        <?php
        // Gets post types explicitly set 'public' (not those registered only with individual public options):
        // https://codex.wordpress.org/Function_Reference/get_post_types
        foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type_arr ) {
            $post_type = $post_type_arr->name;
        ?>
            <li><label><input type="checkbox" id="<?php echo esc_attr( $post_type ); ?>" value="<?php echo esc_attr( $post_type ); ?>" name="soundshares[post_types][]"<?php checked( in_array( $post_type, $options['post_types'] ) ); ?> /> <?php echo esc_html( $post_type_arr->labels->name ); ?></label></li>
        <?php
        }
        ?>
        </ul>
    </fieldset>
    <?php
}

/**
 * Outputs HTML checkboxes of categories (to choose which display Sound Shares meta box).
 *
 * @since   0.1.0
 *
 * @param   array   $options    Array of plugin settings
 */
function soundshares_categories_callback( $options ) {
    $cats         = $options['categories']; // Get checked cats.
    // $checked_cats = ( in_array( 0, $cats ) ) ? 'false' : $cats; // If "All Cats" checked, uncheck all cats.
    ?>
    <fieldset>
        <legend><?php _e( 'Allow Sound Shares for posts only in selected categories:', 'soundshares' ); ?></legend>
        <div class="categorydiv">
            <div class="tabs-panel">
                <ul class="categorychecklist form">

                    <li id="category-0" style="margin-bottom: 0.5em;"><label class="selectit"><input value="0" type="checkbox" name="soundshares[categories][]"<?php checked( in_array( 0, $cats ) ); ?>  id="in-category-0"><strong>Any (or no) category</strong></label></li>
                    <?php

                    $args = array(
                        'descendants_and_self'  => 0,
                        'selected_cats'         => $cats,
                        'popular_cats'          => false,
                        'walker'                => null,
                        'taxonomy'              => 'category',
                        'checked_ontop'         => true,
                        'echo'                  => false
                    );

                    $cats_checklist = wp_terms_checklist( 0, $args );
                    $cats_checklist = str_replace( 'post_category[]', 'soundshares[categories][]', $cats_checklist );

                    echo $cats_checklist;
                    ?>
                </ul>
            </div>
        </div>
    </fieldset>
    <?php
}

/**
 * Outputs text for the footer of the Settings screen.
 *
 * @since   0.1.0
 */
function soundshares_footer_callback() {
    ?>
    <p><?php _e( 'This plugin created as part of a <a href="https://www.rjionline.org/stories/series/storytelling-tools/">Reynold Journalism Institute</a> fellowship.', 'soundshares' ); ?></p>

    <!-- <?php echo get_num_queries(); ?><?php _e(" queries in ", 'postscript'); ?><?php timer_stop( 1 ); ?><?php _e(" seconds uses ", 'postscript'); ?><?php echo size_format( memory_get_peak_usage(), 2); ?> <?php _e(" peak memory", 'postscript'); ?>.) -->
    <pre>
        <?php print_r( soundshares_get_options() ) ?>
    </pre>
    <?php
}
/**
 * References and notes.
 *
 * soundshares_get_options() returns:
 * Array
 * (
 *     [fb_app_id] => 0
 *     [fb_admins] => 0
 *     [twit_user] => 0
 *     [meta_all] => off
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
 *     [categories] => Array
 *          (
 *               [0] => 0
 *          )
 *     [version] => 0.1.0
 * )
 */
