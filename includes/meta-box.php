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
 * Creates meta box for the post editor screen (for user-selected post types).
 *
 * Passes array of user-setting options to callback.
 *
 * @uses soundshares_get_options()   Safely gets option from database.
 */
function soundshares_add_meta_box() {
    $options = soundshares_get_options();

    global $post;

    // Get array of post category IDs.
    $cat_ids = wp_list_pluck( get_the_category( $post->ID ), 'cat_ID' );

    // Add meta box for user-selected categories and post types (from Settings).
    if ( in_array( 0, $options['categories'] ) || ( (bool) array_intersect( $cat_ids, $options['categories'] ) ) ) {
        add_meta_box(
            'soundshares-meta',
            esc_html__( 'Sound Shares', 'soundshares' ),
            'soundshares_meta_box_callback',
            $options['post_types'],
            'advanced',
            'default',
            $options
        );
    }
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
        <input class="widefat" type="url" name="soundshares_meta[file]" id="soundshares-file"  size="30" value="<?php if ( ! empty( $file ) ) { echo esc_url_raw( $file ); } ?>" placeholder="<?php _e( '(Required: Must be https://)', 'soundshares' ); ?>" />
        <?php if ( ! empty( $file ) ) { ?>
        <audio controls src="<?php echo esc_url_raw( $file ); ?>" controlsList="nodownload" preload="metadata">
        </audio>
        <?php } ?>
    </p>
    <p>
        <label for="soundshares-title"><?php _e( 'Audio title:', 'soundshares' ); ?></label><br />
        <input class="widefat" type="text" name="soundshares_meta[title]" id="soundshares-title" size="30" value="<?php if ( isset ( $soundshares_meta['title'] ) ) { echo sanitize_textarea_field( $soundshares_meta['title'] ); } ?>" placeholder="(<?php _e( 'default: post title', 'soundshares' ); ?>)" />
    </p>
    <p>
        <label for="soundshares-author"><?php _e( 'Audio author:', 'soundshares' ); ?> <?php the_author( $post_id ); ?></label><br />
        <input class="widefat" type="text" name="soundshares_meta[author]" id="soundshares-author" size="30" value="<?php if ( isset ( $soundshares_meta['author'] ) ) { echo sanitize_textarea_field( $soundshares_meta['author'] ); } ?>" placeholder="(<?php _e( 'default: post author', 'soundshares' ); ?>)" />
    </p>

    <?php soundshares_image_metabox( $post, $soundshares_meta ) ?>

     <pre style="font-size: 0.7em;">
        <?php print_r( $soundshares_meta ); ?>
    </pre>

    <?php
}



/**
 * Prints button to choose image for social site link previews
 *
 * Based on Featured Image button.
 * If no image chosen, plugin uses featured image.
 *
 * @link https://hugh.blog/2015/12/18/create-a-custom-featured-image-box/
 *
 * @since 0.1.0
 *
 */
function soundshares_image_metabox() {
    global $content_width, $_wp_additional_image_sizes, $post;
    $post_meta = get_post_meta( $post->ID, 'soundshares_meta', true );
    // $image_id = get_post_meta( $post->ID, '_soundshares_image_id', true );

    $image_id = ( isset( $post_meta['image'] ) ) ? $post_meta['image'] : NULL;
    $old_content_width = $content_width;
    $content_width = 254;
    if ( $image_id && get_post( $image_id ) ) {
        if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
            $thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
        } else {
            $thumbnail_html = wp_get_attachment_image( $image_id, 'post-thumbnail' );
        }
        if ( ! empty( $thumbnail_html ) ) {
            $content = $thumbnail_html;
            $content .= '<p id="soundsharesimagediv" class="hide-if-no-js"><a href="javascript:;" id="remove_soundshares_image_button" >' . esc_html__( 'Remove social-site image', 'soundshares' ) . '</a></p>';
            $content .= '<input type="hidden" id="upload_soundshares_image" name="soundshares_meta[image]" value="' . esc_attr( $image_id ) . '" />';
        }
        $content_width = $old_content_width;
    } else {
        $content = '<img src="" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;display:none;" />';
        $content .= '<p class="hide-if-no-js">' . __( 'Image:', 'soundshares' ) . '<br>';
        $content .= '<a title="' . esc_attr__( 'Set social-site image', 'soundshares' ) . '" href="javascript:;" id="upload_soundshares_image_button" id="set-soundshares-image" data-uploader_title="' . esc_attr__( 'Select image for social sites', 'soundshares' ) . '" data-uploader_button_text="' . esc_attr__( 'Use image', 'soundshares' ) . '">' . esc_html__( 'Set social-site image', 'soundshares' ) . '</a></p>';
        $content .= '<input type="hidden" id="upload_soundshares_image" name="soundshares_meta[image]" value="" />';
        $content .= '<p class="wp-ui-text-icon">' . __( '(default: featured image)', 'soundshares' ) . '</p>';
    }
    echo '<div id="soundsharesimagediv">' . $content . '</div>';
    ?>
    <script type="text/javascript">
    /* Social site image */
        jQuery(document).ready(function($) {

            // Uploading files
            var file_frame;

            jQuery.fn.upload_soundshares_image = function( button ) {
                var button_id = button.attr( 'id' );
                var field_id = button_id.replace( '_button', '' );

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: jQuery( '#upload_soundshares_image_button' ).data( 'uploader_title' ),
                    library: {type: 'image'},
                    button: {
                    text: jQuery( '#upload_soundshares_image_button' ).data( 'uploader_button_text' ),
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    var attachment = file_frame.state().get( 'selection' ).first().toJSON();
                    jQuery( "#"+field_id ).val( attachment.id );
                    jQuery( '#soundsharesimagediv img' ).attr( 'src',attachment.url );
                    jQuery( '#soundsharesimagediv img' ).show();
                    jQuery( '#soundsharesimagediv .wp-ui-text-icon' ).hide();
                    jQuery( '#' + button_id ).attr( 'id', 'remove_soundshares_image_button' );
                    jQuery( '#remove_soundshares_image_button' ).text( 'Remove social-site image' );
                });

                // Finally, open the modal
                file_frame.open();
            };

            jQuery( '#soundsharesimagediv' ).on( 'click', '#upload_soundshares_image_button', function( event ) {
                event.preventDefault();
                jQuery.fn.upload_soundshares_image( jQuery(this) );
            });

            jQuery( '#soundsharesimagediv' ).on( 'click', '#remove_soundshares_image_button', function( event ) {
                event.preventDefault();
                jQuery( '#upload_soundshares_image' ).val( '' );
                jQuery( '#soundsharesimagediv img' ).attr( 'src', '' );
                jQuery( '#soundsharesimagediv img' ).hide();
                jQuery( this ).attr( 'id', 'upload_soundshares_image_button' );
                jQuery( '#upload_soundshares_image_button' ).text( 'Set social-site image' );
                jQuery( '#soundsharesimagediv .wp-ui-text-icon' ).show();
            });

        });
    </script>
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

/*
http://rji.local/wp-content/plugins/sound-shares/js/media-button.js?ver=1.0
http://rji.local/wp-content/plugins/sound-shares/js/media_button.js?ver=1.0

*/

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
 * get_post_meta( $post->ID, 'soundshares_meta', true ) returns:
 * Array
 * (
 *    [file] =>
 *    [title] =>
 *    [author] =>
 *    [image] =>
 * )
 */

// rm:
function soundshares_image_add_metabox () {
    add_meta_box( 'soundsharesimagediv', __( 'Social-site Image', 'text-domain', 'soundshares' ), 'soundshares_image_metabox', 'post', 'side', 'low');
}
// add_action( 'add_meta_boxes', 'soundshares_image_add_metabox' );
function soundshares_image_save ( $post_id ) {
    if( isset( $_POST['_soundshares_cover_image'] ) ) {
        $image_id = (int) $_POST['_soundshares_cover_image'];
        update_post_meta( $post_id, '_soundshares_image_id', $image_id );
    }
}
// add_action( 'save_post', 'soundshares_image_save', 10, 1 );
