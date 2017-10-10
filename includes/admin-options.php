<?php
/**
 * Admin Settings Page (Dashboard> Settings> Sound Shares)
 *
 * @see   https://hearingvoices.com/tools/sound-shares
 * @since 0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

/*
http://rji.local/wp-content/plugins/sound-shares/player.php
*/

/**
 * Dispaly admin notice action if site is not HTTPS.
 *
 * @since   0.1.0
 *
 * Sets Settings screen ID: 'settings_page_soundshares'.
 */
function soundshares_admin_notice_ssl() {
    $screen = get_current_screen();
    if ( ! is_ssl() && $screen->id === 'settings_page_soundshares' ) {
    ?>
    <div class="notice notice-warning is-dismissible">
    	<p><?php _e( 'Your site is not HTTPS. Social sites require secure audio URLs so Sound Shares may not work.', 'soundshares' ); ?></p>
    </div>
    <?php
    }
}
add_action( 'admin_notices', 'soundshares_admin_notice_ssl' );

/* ------------------------------------------------------------------------ *
 * Wordpress Settings API
 * ------------------------------------------------------------------------ */

/**
 * Adds submenu item to Settings dashboard menu.
 *
 * @since   0.1.0
 *
 * Sets Settings screen ID: 'settings_page_soundshares'.
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
        <h2>Sound Shares <?php _e('Settings', 'soundshares' ); ?></h2>
        <!-- Create the form that will be used to render our options. -->
        <form method="post" action="options.php">
            <?php settings_fields( 'soundshares' ); ?>
            <?php do_settings_sections( 'soundshares' ); ?>
            <?php submit_button(); ?>
        </form>
        <?php soundshares_settings_footer(); ?>
    </div><!-- .wrap -->
    <?php
}


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
        __( 'User roles', 'postscript', 'soundshares' ),
        'soundshares_user_roles_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_post_types',
        __( 'Post types', 'postscript', 'soundshares' ),
        'soundshares_post_types_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
    );

    add_settings_field(
        'soundshares_categories',
        __( 'Categories', 'postscript', 'soundshares' ),
        'soundshares_categories_callback',
        'soundshares',
        'soundshares_settings_section',
        $args = $options
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
    <p><?php _e('Sound Shares puts an audio player in the link preview of your socially shared posts.', 'soundshares' ); ?></p>
    <p><?php _e('This plugin inserts <code>twitter:</code> and <code>og:</code> HTML meta tags to embed audio at Twitter and Facebook.', 'soundshares' ); ?></p>
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
                <p class="wp-ui-text-icon"><?php _e( 'Enter your App ID (from your <a href="https://developers.facebook.com/apps/redirect/dashboard">Facebook App Dashboard</a>) to track <a href="https://developers.facebook.com/docs/sharing/insights">Facebook Sharing Insights</a>, viewable by admins whose Facebook users IDs you list here (comma-separated; find IDs at the <a href="https://developers.facebook.com/tools/explorer/?method=GET&amp;path=me%3Ffields%3Did%2Cname">Graph Explorer</a>).', 'soundshares' ); ?></p>
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
        <legend><?php _e( 'Enter your Twitter handle:', 'soundshares' ); ?></legend>
        <ul class="inside">
            <li>
                <label><input type="text" id="soundshares-facebook-app-id" name="soundshares[twit_user]" value="<?php if ( isset ( $options['twit_user'] ) ) { echo esc_attr( $options['twit_user'] ); } ?>" /> <?php _e( 'Site name', 'soundshares' ); ?></label>
                <p class="wp-ui-text-icon"><?php _e( 'Enter the username for the Twitter account associated with your site (e.g., @account_name).', 'postscript', 'soundshares' ); ?></p>
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
        <legend><?php _e( '<strong>Check this only if your site has no social tags.</strong>', 'soundshares' ); ?></legend>
        <ul class="inside">
            <li>
                <label><input type="checkbox" id="soundshares-meta-all" name="soundshares[meta_all]" value="on"<?php checked( 'on', isset( $options['meta_all'] ) ? $options['meta_all'] : 'off' ); ?>/> <?php _e( 'Add all social tags', 'soundshares' ); ?></label>
                <p class="wp-ui-text-icon"><?php _e( 'Many sites you an SEO plugin for social meta tags. If you see <code>twitter:url</code> and <code>og:url</code> tags in your source code, do <em>not</em> check this box.', 'soundshares' ); ?></p>
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
 * @param   array   $options    Array of plugin settings
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
    <fieldset style="max-width: 30em;">
        <legend><?php _e( 'Allow Sound Shares for all categories (default) or only these:', 'soundshares' ); ?></legend>
        <div class="categorydiv">
            <div class="tabs-panel">
                <ul class="categorychecklist form">

                    <li id="category-0" style="margin-bottom: 0.5em;"><label class="selectit"><input value="0" type="checkbox" name="soundshares[categories][]"<?php checked( in_array( 0, $cats ) ); ?>  id="in-category-0"><strong>All categories</strong></label><hr></li>
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
 * Outputs HTML for the footer of the Settings screen.
 *
 * @since   0.1.0
 *
 * @uses soundshares_get_cat_names()
 */
function soundshares_settings_footer() {
    $options = soundshares_get_options();

    // URL and query string for example player.
    $player_url  = plugin_dir_url( __FILE__ ) . 'player.html';
    $player_url .= '?file=' . urlencode( plugin_dir_url( __FILE__ ) . 'wolves-west-soundshares.mp3' );
    $player_url .= '&title=' . urlencode( 'Wolves in West Yellowstone' );
    $player_url .= '&author=' . urlencode( 'Hearing Voices' );
    ?>
    <hr />
    <h2 id="metabox"><?php _e('Sound Shares information', 'postscript', 'soundshares' ); ?> (v <?php echo SOUNDSHARES_VERSION; ?>)</h2>
    <p>
        <?php _e('Your settings above display the meta box on the Edit screen <em>only</em> for:', 'soundshares' ); ?>
        <ul style="list-style: disc; list-style-position: inside; margin-left: 1em;">
            <li><?php _e('User-roles:', 'soundshares' ); ?> <?php echo implode( $options['user_roles'], ', ' ); ?></li>
            <li><?php _e('Post-types:', 'soundshares' ); ?> <?php echo implode( $options['post_types'], ', ' ); ?></li>
            <?php if ( in_array( 0, $options['categories'] ) ) { ?>
            <li><?php _e('Categories: (all)', 'soundshares' ); ?></li>
            <?php } else { ?>
            <li><?php _e('Categories:', 'soundshares' ); ?> <?php echo implode( soundshares_get_cat_names( $options['categories'] ), ', ' ); ?></li>
            <?php } ?>
        </ul>
    <p><?php _e('Debug tools (link preview):', 'soundshares' ); ?> <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank">Facebook</a> | <a href="https://cards-dev.twitter.com/validator" target="_blank">Twitter</a>.
    <p>Facebook embeds the default HTML audio player. Twitter embeds this <a href="<?php echo $player_url; ?>" target="_blank"><?php _e('Sound Shares player:', 'soundshares' ); ?></a><br>
        <iframe src="<?php echo $player_url; ?>" width="480" height="140"></iframe></p>
    </p>
    <p><?php _e('Player design by', 'soundshares' ); ?> <a href="https://codepen.io/davepvm/pen/DgwlJ">Dave Pagurek</a>. <?php _e( 'This plugin is part of a <a href="https://www.rjionline.org/stories/series/storytelling-tools/">Reynold Journalism Institute</a> fellowship and an article in <a href="https://current.org/author/bgolding/">Current</a>.', 'soundshares' ); ?></p>

    <!-- <?php echo get_num_queries(); ?><?php _e(" queries in ", 'postscript', 'soundshares'); ?><?php timer_stop( 1 ); ?><?php _e(" seconds uses ", 'postscript', 'soundshares'); ?><?php echo size_format( memory_get_peak_usage(), 2); ?> <?php _e(" peak memory", 'postscript', 'soundshares'); ?>.) -->
    <pre>
        <?php // print_r( soundshares_get_options() ); ?>
    </pre>
    <?php
}

/**
 * Get category names from term IDs.
 *
 * @since   0.1.0
 *
 * @param   array  $cat_ids    Array of category term IDs
 * @return  array  $cat_names  Array of category names
 */
function soundshares_get_cat_names( $cat_ids ) {
    $cat_names = array();
    foreach ( $cat_ids as $cat_id ) {
        $cat_names[] = get_cat_name( $cat_id );
    }

    return $cat_names;
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
 * /
