<?php
/**
 * AJAX handlers for Easy Media Folder Manager.
 *
 * @package Easy_Media_Folder_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assign media to a folder.
 */
function emfm_assign_folder_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    $media_id = absint($_POST['media_id'] ?? 0);
    $folder_id = absint($_POST['folder_id'] ?? 0);

    if (!$media_id) {
        wp_send_json_error(__('Invalid media_id', 'easy-media-folder-manager'));
        return;
    }

    $core = new Easy_Media_Folder_Manager();
    $result = wp_set_object_terms($media_id, $folder_id ? $folder_id : [], 'emfm_media_folder', false);
    if (!is_wp_error($result)) {
        wp_send_json_success(__('Media assigned successfully', 'easy-media-folder-manager'));
    } else {
        wp_send_json_error(__('Failed to assign media', 'easy-media-folder-manager'));
    }
}
add_action('wp_ajax_emfm_assign_folder', 'emfm_assign_folder_callback');

/**
 * Create a new folder.
 */
function emfm_create_folder_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied', 'easy-media-folder-manager'));
        return;
    }

    $core = new Easy_Media_Folder_Manager();
    $result = $core->create_folder($_POST['folder_name'] ?? '');
    if (!is_wp_error($result)) {
        wp_send_json_success([
            'id' => $result->term_id,
            'name' => $result->name,
            'slug' => $result->slug,
        ]);
    } else {
        wp_send_json_error($result->get_error_message());
    }
}
add_action('wp_ajax_emfm_create_folder', 'emfm_create_folder_callback');

/**
 * Rename a folder.
 */
function emfm_rename_folder_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    if (!current_user_can('manage_categories')) {
        wp_send_json_error(__('Insufficient permissions', 'easy-media-folder-manager'));
        return;
    }

    $core = new Easy_Media_Folder_Manager();
    $result = $core->rename_folder($_POST['folder_id'] ?? 0, $_POST['folder_name'] ?? '');
    if (!is_wp_error($result)) {
        wp_send_json_success(['slug' => $result->slug]);
    } else {
        wp_send_json_error($result->get_error_message());
    }
}
add_action('wp_ajax_emfm_rename_folder', 'emfm_rename_folder_callback');

/**
 * Delete a folder.
 */
function emfm_delete_folder_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    if (!current_user_can('manage_categories')) {
        wp_send_json_error(__('Insufficient permissions', 'easy-media-folder-manager'));
        return;
    }

    $core = new Easy_Media_Folder_Manager();
    $result = $core->delete_folder($_POST['folder_id'] ?? 0);
    if (!is_wp_error($result)) {
        wp_send_json_success();
    } else {
        wp_send_json_error($result->get_error_message());
    }
}
add_action('wp_ajax_emfm_delete_folder', 'emfm_delete_folder_callback');

/**
 * Update folder icon.
 */
function emfm_update_folder_icon_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    if (!current_user_can('manage_categories')) {
        wp_send_json_error(__('Insufficient permissions', 'easy-media-folder-manager'));
        return;
    }

    $core = new Easy_Media_Folder_Manager();
    $result = $core->update_folder_icon($_POST['folder_id'] ?? 0, $_POST['icon'] ?? '');
    if (!is_wp_error($result)) {
        wp_send_json_success();
    } else {
        wp_send_json_error($result->get_error_message());
    }
}
add_action('wp_ajax_emfm_update_folder_icon', 'emfm_update_folder_icon_callback');

/**
 * Save folder order.
 */
function emfm_save_folder_order_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied', 'easy-media-folder-manager'));
        return;
    }

    $order = isset($_POST['order']) ? array_map('intval', (array) $_POST['order']) : [];
    if (empty($order)) {
        wp_send_json_error(__('No order data provided', 'easy-media-folder-manager'));
        return;
    }

    foreach ($order as $menu_order => $term_id) {
        update_term_meta($term_id, 'emf_folder_order', $menu_order);
    }

    wp_send_json_success(__('Folder order saved successfully', 'easy-media-folder-manager'));
}
add_action('wp_ajax_emfm_save_folder_order', 'emfm_save_folder_order_callback');

/**
 * Sort folders.
 */
function emfm_sort_folders_callback() {
    $plugin = EMFM_Plugin::get_instance();
    if (!$plugin->nonce_handler('emfm_action', $_POST['nonce'] ?? '')) {
        wp_send_json_error(__('Invalid nonce', 'easy-media-folder-manager'));
        return;
    }

    $sort_by = sanitize_text_field($_POST['sort_by'] ?? 'name-asc');
    $core = new Easy_Media_Folder_Manager();
    $folders = $core->get_sorted_folders($sort_by);

    ob_start();
    foreach ($folders as $folder) {
        ?>
        <li class="emf-folder-item" data-folder-id="<?php echo esc_attr($folder->term_id); ?>">
            <span class="dashicons <?php echo esc_attr($folder->meta['emf_folder_icon'] ?? 'dashicons-folder'); ?>"></span>
            <span class="emf-folder-title"><?php echo esc_html($folder->name); ?></span>
            <span class="emf-folder-menu-toggle dashicons dashicons-ellipsis" style="float:right; cursor:pointer;"></span>
            <div class="emf-folder-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:5px;">
                <a href="#" class="emf-rename-folder" data-folder-id="<?php echo esc_attr($folder->term_id); ?>">Rename</a><br>
                <a href="#" class="emf-delete-folder" data-folder-id="<?php echo esc_attr($folder->term_id); ?>">Delete</a><br>
                <a href="#" class="emf-edit-icon" data-folder-id="<?php echo esc_attr($folder->term_id); ?>">Edit Icon</a>
            </div>
        </li>
        <?php
    }
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_emfm_sort_folders', 'emfm_sort_folders_callback');
?>