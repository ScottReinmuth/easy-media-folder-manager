<?php
/**
 * Core plugin functionality for managing media folders.
 *
 * @package Easy_Media_Folder_Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Easy_Media_Folder_Manager
 */
class Easy_Media_Folder_Manager {
    /**
     * Initialize hooks.
     */
    public function init() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('admin_init', [$this, 'handle_folder_actions']);
        add_action('wp_ajax_emfm_move_media', [$this, 'ajax_move_media']);
    }

    /**
     * Register media folder taxonomy.
     */
    public function register_taxonomy() {
        register_taxonomy('emfm_media_folder', 'attachment', [
            'label' => __('Media Folders', 'easy-media-folder-manager'),
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => false,
        ]);
    }

    /**
     * Handle folder creation, renaming, and deletion.
     */
    public function handle_folder_actions() {
        // Only process if the form action is set
        if (!isset($_POST['action']) || !in_array($_POST['action'], ['Create Folder', 'Rename', 'Delete'], true)) {
            return;
        }

        if (!current_user_can('upload_files')) {
            wp_die('Unauthorized access.');
        }

        if (!isset($_POST['emfm_nonce']) || !wp_verify_nonce($_POST['emfm_nonce'], 'emfm_folder_action')) {
            wp_die('Security check failed.');
        }

        $redirect_url = admin_url('upload.php?page=emfm_folders');

        if ($_POST['action'] === 'Create Folder') {
            $folder_name = sanitize_text_field($_POST['folder_name'] ?? '');
            if (!empty($folder_name)) {
                $term = wp_insert_term($folder_name, 'emfm_media_folder');
                if (!is_wp_error($term)) {
                    $redirect_url = add_query_arg('message', 'created', $redirect_url);
                    delete_transient('emfm_folders');
                } else {
                    $redirect_url = add_query_arg('message', 'error', $redirect_url);
                }
            }
        } elseif ($_POST['action'] === 'Rename') {
            $folder_id = absint($_POST['folder_id'] ?? 0);
            $new_name = sanitize_text_field($_POST['new_folder_name'] ?? '');
            if ($folder_id && !empty($new_name)) {
                $result = wp_update_term($folder_id, 'emfm_media_folder', ['name' => $new_name]);
                if (!is_wp_error($result)) {
                    $redirect_url = add_query_arg('message', 'renamed', $redirect_url);
                    delete_transient('emfm_folders');
                } else {
                    $redirect_url = add_query_arg('message', 'error', $redirect_url);
                }
            }
        } elseif ($_POST['action'] === 'Delete') {
            $folder_id = absint($_POST['folder_id'] ?? 0);
            if ($folder_id) {
                $result = wp_delete_term($folder_id, 'emfm_media_folder');
                if (!is_wp_error($result)) {
                    $redirect_url = add_query_arg('message', 'deleted', $redirect_url);
                    delete_transient('emfm_folders');
                } else {
                    $redirect_url = add_query_arg('message', 'error', $redirect_url);
                }
            }
        }

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Get all media folders with caching.
     *
     * @return array List of folder terms.
     */
    public function get_folders() {
        $transient_key = 'emfm_folders';
        $folders = get_transient($transient_key);

        if (false === $folders) {
            $folders = get_terms([
                'taxonomy' => 'emfm_media_folder',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);
            set_transient($transient_key, $folders, HOUR_IN_SECONDS);
        }

        return is_wp_error($folders) ? [] : $folders;
    }

    /**
     * Handle AJAX request to move media to a folder.
     */
    public function ajax_move_media() {
        check_ajax_referer('emfm_move_media', 'nonce');

        $media_id = absint($_POST['media_id'] ?? 0);
        $folder_id = absint($_POST['folder_id'] ?? 0);

        if (!current_user_can('edit_post', $media_id)) {
            wp_send_json_error(__('Unauthorized access.', 'easy-media-folder-manager'));
        }

        if ($media_id && $folder_id) {
            $result = wp_set_object_terms($media_id, $folder_id, 'emfm_media_folder', false);
            if (!is_wp_error($result)) {
                wp_send_json_success();
            } else {
                wp_send_json_error(__('Failed to move media.', 'easy-media-folder-manager'));
            }
        } else {
            wp_send_json_error(__('Invalid media or folder ID.', 'easy-media-folder-manager'));
        }
    }
}
?>