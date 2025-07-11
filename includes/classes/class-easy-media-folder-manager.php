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
        add_filter('attachment_fields_to_edit', [$this, 'add_folder_field'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'save_folder_field'], 10, 2);
    }

    /**
     * Register media folder taxonomy and set up filtering.
     */
    public function register_taxonomy() {
        // Check for existing taxonomy to avoid conflicts
        if (taxonomy_exists('emfm_media_folder')) {
            return;
        }

        $labels = [
            'name' => _x('Media Folders', 'taxonomy general name', 'easy-media-folder-manager'),
            'singular_name' => _x('Media Folder', 'taxonomy singular name', 'easy-media-folder-manager'),
            'search_items' => __('Search Media Folders', 'easy-media-folder-manager'),
            'all_items' => __('All Media Folders', 'easy-media-folder-manager'),
            'parent_item' => __('Parent Media Folder', 'easy-media-folder-manager'),
            'parent_item_colon' => __('Parent Media Folder:', 'easy-media-folder-manager'),
            'edit_item' => __('Edit Media Folder', 'easy-media-folder-manager'),
            'update_item' => __('Update Media Folder', 'easy-media-folder-manager'),
            'add_new_item' => __('Add New Media Folder', 'easy-media-folder-manager'),
            'new_item_name' => __('New Media Folder Name', 'easy-media-folder-manager'),
            'menu_name' => __('Media Folders', 'easy-media-folder-manager'),
        ];

        $args = [
            'hierarchical' => true,
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'media-folder'],
        ];

        register_taxonomy('emfm_media_folder', ['attachment'], $args);

        // Filter media by folder
        add_action('pre_get_posts', [$this, 'filter_media_by_folder']);
    }

    /**
     * Filter media by folder in media library.
     *
     * @param WP_Query $query The query object.
     */
    public function filter_media_by_folder($query) {
        global $pagenow;
        if (is_admin() && $pagenow === 'upload.php' && !empty($_GET['media_folder'])) {
            $query->set('tax_query', [
                [
                    'taxonomy' => 'emfm_media_folder',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['media_folder']),
                ],
            ]);
        }
    }

    /**
     * Create a new folder.
     *
     * @param string $folder_name Folder name.
     * @param int    $parent_id   Parent folder ID (optional).
     * @return WP_Term|WP_Error Term data or error.
     */
    public function create_folder($folder_name, $parent_id = 0) {
        if (!current_user_can('manage_categories')) {
            return new WP_Error('unauthorized', __('Insufficient permissions', 'easy-media-folder-manager'));
        }

        $folder_name = sanitize_text_field($folder_name);
        $parent_id = absint($parent_id);
        if (empty($folder_name)) {
            return new WP_Error('empty_name', __('Folder name cannot be empty', 'easy-media-folder-manager'));
        }

        $term = wp_insert_term($folder_name, 'emfm_media_folder', ['parent' => $parent_id]);
        if (!is_wp_error($term)) {
            $folders = get_transient('emfm_folders') ?: [];
            $folders[] = get_term($term['term_id'], 'emfm_media_folder');
            set_transient('emfm_folders', $folders, HOUR_IN_SECONDS);
            return get_term($term['term_id'], 'emfm_media_folder');
        }
        return $term;
    }

    /**
     * Rename a folder.
     *
     * @param int    $folder_id   Folder ID.
     * @param string $new_name    New folder name.
     * @return WP_Term|WP_Error Term data or error.
     */
    public function rename_folder($folder_id, $new_name) {
        if (!current_user_can('manage_categories')) {
            return new WP_Error('unauthorized', __('Insufficient permissions', 'easy-media-folder-manager'));
        }

        $folder_id = absint($folder_id);
        $new_name = sanitize_text_field($new_name);
        if (!$folder_id || empty($new_name)) {
            return new WP_Error('invalid_input', __('Invalid folder ID or name: ' . $folder_id . ', ' . $new_name, 'easy-media-folder-manager'));
        }

        $result = wp_update_term($folder_id, 'emfm_media_folder', ['name' => $new_name, 'slug' => sanitize_title($new_name)]);
        if (!is_wp_error($result)) {
            $folders = get_transient('emfm_folders') ?: [];
            foreach ($folders as &$folder) {
                if ($folder->term_id == $folder_id) {
                    $folder = get_term($folder_id, 'emfm_media_folder');
                    break;
                }
            }
            set_transient('emfm_folders', $folders, HOUR_IN_SECONDS);
            return get_term($folder_id, 'emfm_media_folder');
        }
        return $result;
    }

    /**
     * Delete a folder and unassign media.
     *
     * @param int $folder_id Folder ID.
     * @return bool|WP_Error True on success, error on failure.
     */
    public function delete_folder($folder_id) {
        if (!current_user_can('manage_categories')) {
            return new WP_Error('unauthorized', __('Insufficient permissions', 'easy-media-folder-manager'));
        }

        $folder_id = absint($folder_id);
        if (!$folder_id) {
            return new WP_Error('invalid_id', __('Invalid folder ID: ' . $folder_id, 'easy-media-folder-manager'));
        }

        $media = get_objects_in_term($folder_id, 'emfm_media_folder');
        if (!empty($media)) {
            foreach ($media as $media_id) {
                wp_remove_object_terms($media_id, $folder_id, 'emfm_media_folder');
            }
        }

        $result = wp_delete_term($folder_id, 'emfm_media_folder');
        if (!is_wp_error($result)) {
            $folders = get_transient('emfm_folders') ?: [];
            $folders = array_filter($folders, function($folder) use ($folder_id) {
                return $folder->term_id != $folder_id;
            });
            set_transient('emfm_folders', $folders, HOUR_IN_SECONDS);
            return true;
        }
        return $result;
    }

    /**
     * Update folder icon.
     *
     * @param int    $folder_id Folder ID.
     * @param string $icon      Icon class.
     * @return bool|WP_Error True on success, error on failure.
     */
    public function update_folder_icon($folder_id, $icon) {
        if (!current_user_can('manage_categories')) {
            return new WP_Error('unauthorized', __('Insufficient permissions', 'easy-media-folder-manager'));
        }

        $folder_id = absint($folder_id);
        $icon = sanitize_text_field($icon);
        if (!$folder_id || !$icon) {
            return new WP_Error('invalid_input', __('Invalid folder ID or icon: ' . $folder_id . ', ' . $icon, 'easy-media-folder-manager'));
        }
        if (strpos($icon, 'dashicons-') !== 0) {
            return new WP_Error('invalid_icon', __('Invalid icon class: ' . $icon, 'easy-media-folder-manager'));
        }
        update_term_meta($folder_id, 'emf_folder_icon', $icon);
        return true;
    }

    /**
     * Handle folder actions (form submissions).
     */
    public function handle_folder_actions() {
        if (!isset($_POST['action']) || !in_array($_POST['action'], ['Create Folder', 'Rename', 'Delete'], true)) {
            return;
        }
        if (!current_user_can('manage_categories') || !wp_verify_nonce($_POST['emfm_nonce'], 'emfm_folder_action')) {
            wp_die('Unauthorized access.');
        }

        $redirect_url = admin_url('upload.php?page=emfm_folders');
        $message = 'error';

        if ($_POST['action'] === 'Create Folder') {
            $parent_id = absint($_POST['parent_folder'] ?? 0);
            $result = $this->create_folder($_POST['folder_name'] ?? '', $parent_id);
            if (!is_wp_error($result)) {
                $message = 'created';
            }
        } elseif ($_POST['action'] === 'Rename') {
            $result = $this->rename_folder($_POST['folder_id'] ?? 0, $_POST['new_folder_name'] ?? '');
            if (!is_wp_error($result)) {
                $message = 'renamed';
            }
        } elseif ($_POST['action'] === 'Delete') {
            $result = $this->delete_folder($_POST['folder_id'] ?? 0);
            if (!is_wp_error($result)) {
                $message = 'deleted';
            }
        }

        wp_redirect(add_query_arg('message', $message, $redirect_url));
        exit;
    }

    /**
     * Get all media folders with caching.
     *
     * @return WP_Term[] List of folder terms.
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
     * Get folder metadata.
     *
     * @param WP_Term $folder Folder term.
     * @return array Metadata array.
     */
    private function get_folder_meta($folder) {
        $order = get_term_meta($folder->term_id, 'emf_folder_order', true);
        $icon = get_term_meta($folder->term_id, 'emf_folder_icon', true);
        return [
            'emf_folder_order' => $order !== '' ? (int)$order : null,
            'emf_folder_icon' => $icon ?: 'dashicons-folder',
        ];
    }

    /**
     * Get sorted folders.
     *
     * @param string $sort_by Sorting method (name-asc, manual, etc.).
     * @return WP_Term[] Sorted folder terms.
     */
    public function get_sorted_folders($sort_by = 'name-asc') {
        $transient_key = 'emfm_sorted_folders_' . md5($sort_by);
        $folders = get_transient($transient_key);

        if (false === $folders) {
            $folders = $this->get_folders();
            if (empty($folders)) {
                return $folders;
            }

            foreach ($folders as &$folder) {
                $folder->meta = $this->get_folder_meta($folder);
            }
            unset($folder);

            usort($folders, function ($a, $b) use ($sort_by) {
                switch ($sort_by) {
                    case 'name-asc':
                        return strcasecmp($a->name, $b->name);
                    case 'name-desc':
                        return strcasecmp($b->name, $a->name);
                    case 'date-asc':
                        return $a->term_id - $b->term_id;
                    case 'date-desc':
                        return $b->term_id - $a->term_id;
                    case 'count-asc':
                        return ($a->count ?? 0) - ($b->count ?? 0);
                    case 'count-desc':
                        return ($b->count ?? 0) - ($a->count ?? 0);
                    case 'manual':
                        $a_order = get_term_meta($a->term_id, 'emf_folder_order', true) ?: PHP_INT_MAX;
                        $b_order = get_term_meta($b->term_id, 'emf_folder_order', true) ?: PHP_INT_MAX;
                        return $a_order - $b_order;
                    default:
                        return 0;
                }
            });

            set_transient($transient_key, $folders, HOUR_IN_SECONDS);
        }

        return $folders;
    }

    /**
     * Handle AJAX request to move media to a folder.
     */
    public function ajax_move_media() {
        $plugin = EMFM_Plugin::get_instance();
        if (!$plugin->nonce_handler('emfm_folder_action', $_POST['nonce'] ?? '')) {
            emfm_send_error(__('Invalid nonce', 'easy-media-folder-manager'));
        }

        $media_id = absint($_POST['media_id'] ?? 0);
        $folder_id = absint($_POST['folder_id'] ?? 0);

        if (!current_user_can('edit_post', $media_id)) {
            emfm_send_error(__('Unauthorized access.', 'easy-media-folder-manager'));
        }

        if ($media_id) {
            $result = wp_set_object_terms($media_id, $folder_id ? $folder_id : [], 'emfm_media_folder', false);
            if (!is_wp_error($result)) {
                wp_send_json_success(['message' => __('Media moved successfully', 'easy-media-folder-manager')]);
            } else {
                emfm_send_error(__('Failed to move media.', 'easy-media-folder-manager'));
            }
        } else {
            emfm_send_error(__('Invalid media or folder ID.', 'easy-media-folder-manager'));
        }
    }

    /**
     * Add folder field to media upload form.
     *
     * @param array   $form_fields Form fields.
     * @param WP_Post $post        Attachment post.
     * @return array Modified form fields.
     */
    public function add_folder_field($form_fields, $post) {
        $terms = $this->get_folders();
        $selected_terms = wp_get_object_terms($post->ID, 'emfm_media_folder', ['fields' => 'ids']);

        $dropdown = '<select name="attachments[' . $post->ID . '][media_folder][]" aria-label="' . esc_attr__('Select media folder', 'easy-media-folder-manager') . '">';
        $dropdown .= '<option value="">' . __('No Folder', 'easy-media-folder-manager') . '</option>';
        foreach ($terms as $term) {
            $indent = $term->parent ? str_repeat('&nbsp;&nbsp;', $this->get_term_depth($term)) : '';
            $selected = in_array($term->term_id, $selected_terms) ? ' selected' : '';
            $dropdown .= '<option value="' . esc_attr($term->term_id) . '"' . $selected . '>' . $indent . esc_html($term->name) . '</option>';
        }
        $dropdown .= '</select>';

        $form_fields['media_folder'] = [
            'label' => __('Media Folder', 'easy-media-folder-manager'),
            'input' => 'html',
            'html' => $dropdown,
        ];

        return $form_fields;
    }

    /**
     * Get term depth for indentation.
     *
     * @param WP_Term $term Term object.
     * @return int Depth level.
     */
    private function get_term_depth($term) {
        $depth = 0;
        while ($term->parent) {
            $depth++;
            $term = get_term($term->parent, 'emfm_media_folder');
        }
        return $depth;
    }

    /**
     * Save folder field during media upload.
     *
     * @param array $post       Attachment post data.
     * @param array $attachment Attachment data.
     * @return array Modified post data.
     */
    public function save_folder_field($post, $attachment) {
        if (isset($attachment['media_folder']) && $attachment['media_folder'][0] !== '') {
            $term_id = absint($attachment['media_folder'][0]);
            wp_set_object_terms($post['ID'], $term_id, 'emfm_media_folder', false);
        } else {
            wp_set_object_terms($post['ID'], [], 'emfm_media_folder', false);
        }
        return $post;
    }
}
