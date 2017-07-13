<?php
/**
 * Meta box for the Edit Post screen
 *
 * @link    http://hearingvoices.com/tools/sound-shares
 * @since   0.1.0
 *
 * @package    Sound Shares
 * @subpackage sound-shares/includes
 */

/* ------------------------------------------------------------------------ *
 * Meta Box for the Post Edit screen.
 * ------------------------------------------------------------------------ */

/**
 * Displays meta box on post editor screen (both new and edit pages).
 */
function soundshares_meta_box_setup() {
    $options = soundshares_get_options();
    $user    = wp_get_current_user();
    $roles   = $options['user_roles'];

    // Add meta boxes only for allowed user roles.
    if ( array_intersect( $roles, $user->roles ) ) {
        // Add meta box.
        add_action( 'add_meta_boxes', 'soundshares_add_meta_box' );

        // Save post meta.
        add_action( 'save_post', 'soundshares_save_post_meta', 10, 2 );
    }
}
add_action( 'load-post.php', 'soundshares_meta_box_setup' );
add_action( 'load-post-new.php', 'soundshares_meta_box_setup' );


function soundshares_metabox_admin_notice() {
    $soundshares_meta = get_post_meta( get_the_id(), 'soundshares_meta', true );
    ?>
    <div class="error">
    <?php var_dump( $_POST ) ?>
        <p><?php _e( 'Error!', 'soundshares' ); ?></p>
    </div>
    <?php
    // }
}

/**
 * Creates meta box for the post editor screen.
 *
 * Passes array of user-setting options to callback.
 *
 * @uses soundshares_get_options()   Safely gets option from database.
 */
function soundshares_add_meta_box() {
    $options = soundshares_get_options();

    add_meta_box(
        'soundshares-meta',
        esc_html__( 'Sound Shares', 'soundshares' ),
        'soundshares_meta_box_callback',
        $options['post_types'],
        'side',
        'default',
        $options
    );
}

/**
 * Builds HTML form for the post meta box.
 *
 * Form elements are checkboxes to select script/style handles (stored as tax terms),
 * and text fields for entering body/post classes (stored in same post-meta array).
 *
 * Form elements are printed only if allowed on Setting page.
 * Callback function passes array of settings-options in args ($box):
 *
/**
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
 * @param  Object $post Object containing the current post.
 * @param  array  $box  Array of meta box id, title, callback, and args elements.
 */
function soundshares_meta_box_callback( $post, $box ) {
    $post_id = $post->ID;
    // Print checklist of file and file info (to display in HTML meta tags).
    ?>
    <?php wp_nonce_field( basename( __FILE__ ), 'soundshares_meta_nonce' ); ?>
    <?php
    // Display text fields for: URLs (style/script) and classes (body/post).
    $opt_meta_all     = $box['args']['meta_all'];
    $soundshares_meta = get_post_meta( $post_id, 'soundshares_meta', true );

    // @todo Turn all this logic and HTML into an array.
    $file   = ( isset( $soundshares_meta['file'] ) ) ? $soundshares_meta['file'] : '';
    $title  = ( isset( $soundshares_meta['title'] ) ) ? $soundshares_meta['title'] : '';
    $author = ( isset( $soundshares_meta['author'] ) ) ? $soundshares_meta['author'] : '';
    $image  = ( isset( $soundshares_meta['image'] ) ) ? $soundshares_meta['image'] : '';
    ?>
    <p>
        <label for="soundshares-file"><?php _e( 'Audio URL:', 'soundshares' ); ?></label><br />
        <input class="widefat" type="url" name="soundshares_meta[file]" id="soundshares-file"  size="30" value="<?php if ( ! empty( $file ) ) { echo esc_url_raw( $file ); } ?>" placeholder="<?php _e( '(Must be https://)', 'soundshares' ); ?>" />
    </p>
    <p>
        <label for="soundshares-title"><?php _e( 'Audio title:', 'soundshares' ); ?></label><br />
        <input class="widefat" type="text" name="soundshares_meta[title]" id="soundshares-title" value="<?php if ( isset ( $soundshares_meta['title'] ) ) { echo sanitize_text_field( $soundshares_meta['title'] ); } ?>" size="30" />
    </p>
    <p>
        <label for="soundshares-author"><?php _e( 'Audio author:', 'soundshares' ); ?></label><br />
        <input class="widefat" type="text" name="soundshares_meta[author]" id="soundshares-author" value="<?php if ( isset ( $soundshares_meta['author'] ) ) { echo sanitize_text_field( $soundshares_meta['author'] ); } ?>" size="30" />
    </p>
    <p>
        <label for="soundshares-image"><?php _e( 'Image URL:', 'soundshares' ); ?></label><br />
        <input class="widefat" type="url" name="soundshares_meta[image]" id="soundshares-image"  size="30" value="<?php if ( ! empty( $image ) ) { echo esc_url_raw( $image ); } ?>" placeholder="<?php _e( '(.jpg, .gif, or .png)', 'soundshares' ); ?>" />
    </p>
    <?php
}


/**
 * Returns class name for HTML form input.
 *
 * @since   0.4.0
 *
 * @param  string   $url_error        Error message from soundshares_url_error().
 * @return string   $url_error_class  Error class if true, else empty string if not.
 */
function soundshares_url_error_class( $url_error ) {
    $url_error_class = ( empty( $url_error ) )? '' : ' class="form-invalid"';

    return $url_error_class;
}

/**
 * Saves the meta box form data upon submission.
 *
 * @uses  soundshares_sanitize_data()    Sanitizes $_POST array.
 *
 * @param int     $post_id    Post ID.
 * @param WP_Post $post       Post object.
 */
function soundshares_save_post_meta( $post_id, $post ) {

    // Checks save status
    $is_autosave    = wp_is_post_autosave( $post_id );
    $is_revision    = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'soundshares_meta_nonce' ] ) && wp_verify_nonce( $_POST[ 'soundshares_meta_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
        return;
    }

    // Get the post type object (to match with current user capability).
    $post_type = get_post_type_object( $post->post_type );

    // Check if the current user has permission to edit the post.
    if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
        return $post_id;
    }

    $meta_key   = 'soundshares_meta';
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    // $form_data = $_POST['soundshares_meta'];
    // update_post_meta( $post_id, $meta_key, $form_data );

    // If any user-submitted form fields have a value.
    // (implode() reduces array values to a string to do the check).
    if ( isset( $_POST['soundshares_meta'] ) && implode( $_POST['soundshares_meta'] ) ) {
        $form_data  = soundshares_sanitize_data( $_POST['soundshares_meta'] );
    } else {
        $form_data  = null;
    }

    // $form_data  = ( isset( $_POST['soundshares_meta'] ) && implode( $_POST['soundshares_meta'] ) ) ? $_POST['soundshares_meta'] : null;

    // Add post-meta, if none exists, and if user entered new form data.
    if ( $form_data && '' == $meta_value ) {
        add_post_meta( $post_id, $meta_key, $form_data, true );

    // Update post-meta if user changed existing post-meta values in form.
    } elseif ( $form_data && $form_data != $meta_value ) {
        update_post_meta( $post_id, $meta_key, $form_data );

    // Delete existing post-meta if user cleared all post-meta values from form.
    } elseif ( null == $form_data && $meta_value ) {
        delete_post_meta( $post_id, $meta_key );

    // Any other possibilities?
    } else {
        return;
    }

}
