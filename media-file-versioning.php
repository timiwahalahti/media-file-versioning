<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://robertdevore.com
 * @since             1.0.0
 * @package           Media_File_Versioning
 *
 * @wordpress-plugin
 *
 * Plugin Name: Media File Versioning
 * Plugin URI:  https://github.com/robertdevore/media-file-versioning/
 * Description: Track and manage versions of media files in the WordPress media library.
 * Version:     1.0.0
 * Author:      Robert DeVore
 * Author URI:  https://robertdevore.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: media-file-versioning
 * Domain Path: /languages
 * Update URI:  https://github.com/robertdevore/media-file-versioning/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define the plugin version.
define( 'MFV_VERSION', '1.0.0' );

/**
 * Add a custom meta box for versioning in the media library.
 * 
 * @since  1.0.0
 * @return void
 */
function media_versioning_add_meta_box() {
    add_meta_box(
        'media_versioning_meta_box',
        esc_html__( 'Media Versioning', 'media-file-versioning' ),
        'media_versioning_meta_box_callback',
        'attachment',
        'side'
    );
}
add_action( 'add_meta_boxes', 'media_versioning_add_meta_box' );

/**
 * Meta box callback to display the versioning UI.
 *
 * @param WP_Post $post The current post object.
 * 
 * @since  1.0.0
 * @return void
 */
function media_versioning_meta_box_callback( $post ) {
    $versions    = get_post_meta( $post->ID, '_media_versions', true ) ?: [];
    $current_url = wp_get_attachment_url( $post->ID );

    echo '<p>' . esc_html__( 'Upload a new version or view previous versions below:', 'media-file-versioning' ) . '</p>';

    // Add file input and upload button.
    echo '<div id="media_version_upload_wrapper">';
    echo '<input type="file" id="media_version_upload" />';
    echo '<button type="button" class="button" id="media_version_upload_btn">' . esc_html__( 'Upload New Version', 'media-file-versioning' ) . '</button>';
    echo '</div>';

    echo '<div id="media_version_success" style="margin-top: 10px; color: green;"></div>';
    echo '<ul id="media_versions_list" style="margin-top: 10px;">';

    // Add the current file as "Current Version."
    echo '<li>';
    echo '<strong>' . esc_html__( 'Current Version', 'media-file-versioning' ) . ':</strong> ';
    printf(
        '<a href="%s" target="_blank">%s</a> (%s)',
        esc_url( $current_url ),
        esc_html( basename( $current_url ) ),
        esc_html( date( 'Y-m-d H:i:s', filemtime( get_attached_file( $post->ID ) ) ) )
    );
    echo '</li>';

    // Add all previous versions.
    if ( ! empty( $versions ) ) {
        foreach ( $versions as $version ) {
            echo '<li>';
            echo '<strong>' . esc_html__( 'Previous Version', 'media-file-versioning' ) . ':</strong> ';
            printf(
                '<a href="%s" target="_blank">%s</a> (%s)',
                esc_url( $version['url'] ),
                esc_html( basename( $version['url'] ) ),
                esc_html( date( 'Y-m-d H:i:s', $version['time'] ) )
            );
            echo '</li>';
        }
    }

    echo '</ul>';
    wp_nonce_field( 'media_versioning_nonce', 'media_versioning_nonce_field' );
}

/**
 * Handle file uploads and save versions.
 * 
 * @since  1.0.0
 * @return void
 */
function media_versioning_upload_handler() {
    check_ajax_referer( 'media_versioning_nonce', 'nonce' );

    if ( ! current_user_can( 'upload_files' ) ) {
        wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'media-file-versioning' ) ] );
    }

    $file          = $_FILES['file'];
    $attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;

    if ( $file['error'] !== UPLOAD_ERR_OK ) {
        wp_send_json_error( [ 'message' => esc_html__( 'File upload error.', 'media-file-versioning' ) ] );
    }

    $upload = wp_handle_upload( $file, [ 'test_form' => false ] );

    if ( isset( $upload['error'] ) ) {
        wp_send_json_error( [ 'message' => $upload['error'] ] );
    }

    $current_file_path = get_attached_file( $attachment_id );
    $current_url       = wp_get_attachment_url( $attachment_id );

    if ( file_exists( $current_file_path ) ) {
        $path_info          = pathinfo( $current_file_path );
        $versioned_filename = sprintf(
            '%s/%s-%s.%s',
            $path_info['dirname'],
            $path_info['filename'],
            time(),
            $path_info['extension']
        );

        rename( $current_file_path, $versioned_filename );

        $versioned_url = str_replace( basename( $current_url ), basename( $versioned_filename ), $current_url );

        $versions   = get_post_meta( $attachment_id, '_media_versions', true ) ?: [];
        $versions[] = [
            'url'  => $versioned_url,
            'time' => time(),
            'note' => esc_html__( 'Previous Version', 'media-file-versioning' ),
        ];
        update_post_meta( $attachment_id, '_media_versions', $versions );
    }

    $new_file_path = $upload['file'];
    copy( $new_file_path, $current_file_path );
    update_attached_file( $attachment_id, $current_file_path );

    $filetype        = wp_check_filetype( $new_file_path );
    $attachment_data = [
        'ID'             => $attachment_id,
        'post_mime_type' => $filetype['type'],
        'guid'           => $upload['url'],
    ];
    wp_update_post( $attachment_data );

    wp_send_json_success( [
        'current_file' => [
            'url'  => wp_get_attachment_url( $attachment_id ),
            'name' => basename( wp_get_attachment_url( $attachment_id ) ),
            'time' => filemtime( get_attached_file( $attachment_id ) ),
        ],
        'versions' => get_post_meta( $attachment_id, '_media_versions', true ),
    ] );
}
add_action( 'wp_ajax_media_versioning_upload', 'media_versioning_upload_handler' );

/**
 * Enqueue JavaScript for the admin UI.
 *
 * @param string $hook The current admin page hook.
 * 
 * @since  1.0.0
 * @return void
 */
function media_versioning_enqueue_scripts( $hook ) {
    if ( 'post.php' === $hook && isset( $_GET['post'] ) && 'attachment' === get_post_type( intval( $_GET['post'] ) ) ) {
        wp_enqueue_script(
            'media-file-versioning-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/media-file-versioning.js',
            [ 'jquery' ],
            MFV_VERSION,
            true
        );

        wp_localize_script( 'media-file-versioning-js', 'MediaVersioning', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'media_versioning_nonce' ),
        ] );
    }

    wp_enqueue_style(
        'media-file-versioning-styles',
        plugin_dir_url( __FILE__ ) . 'assets/css/media-file-versioning.css',
        [],
        MFV_VERSION,
        'all'
    );
}
add_action( 'admin_enqueue_scripts', 'media_versioning_enqueue_scripts' );
