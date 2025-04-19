<?php
if (!defined('ABSPATH')) {
    exit;
}

// AJAX Handler for Folder Assignment
function emf_assign_folder_callback() {
    check_ajax_referer('emf_nonce', 'nonce');

    if (!isset($_POST['media_id']) || !isset($_POST['folder_id'])) {
        wp_send_json_error(__('Missing media_id or folder_id', 'easy-media-folder-manager'));
        return;
    }

    $media_id = intval($_POST['media_id']);
    $folder_id = intval($_POST['folder_id']);

    if ($media_id && $folder_id >= 0) {
        if ($folder_id === 0) {
            wp_set_object_terms($media_id, array(), 'media_folder', false);
        } else {
            wp_set_object_terms($media_id, $folder_id, 'media_folder', false);
        }
        wp_send_json_success(__('Media assigned successfully', 'easy-media-folder-manager'));
    } else {
        wp_send_json_error(__('Invalid media_id or folder_id', 'easy-media-folder-manager'));
    }
}
add_action('wp_ajax_emf_assign_folder', 'emf_assign_folder_callback');

// AJAX Handler for Folder Creation
function emf_create_folder_callback() {
    check_ajax_referer('emf_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied', 'easy-media-folder-manager'));
        return;
    }

    $folder_name = sanitize_text_field($_POST['folder_name'] ?? '');
    if ($folder_name) {
        $term = wp_insert_term($folder_name, 'media_folder');
        if (!is_wp_error($term)) {
            $new_folder = get_term($term['term_id'], 'media_folder');
            wp_send_json_success(array(
                'id'   => $new_folder->term_id,
                'name' => $new_folder->name,
                'slug' => $new_folder->slug,
            ));
        } else {
            wp_send_json_error(__('Failed to create folder: ', 'easy-media-folder-manager') . $term->get_error_message());
        }
    } else {
        wp_send_json_error(__('Folder name cannot be empty', 'easy-media-folder-manager'));
    }
}
add_action('wp_ajax_emf_create_folder', 'emf_create_folder_callback');

// AJAX Handler for Saving Folder Order
function emf_save_folder_order_callback() {
    check_ajax_referer('emf_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied', 'easy-media-folder-manager'));
        return;
    }

    $order = isset($_POST['order']) ? (array) $_POST['order'] : [];
    $order = array_map('intval', $order);

    if (empty($order)) {
        wp_send_json_error(__('No order data provided', 'easy-media-folder-manager'));
        return;
    }

    foreach ($order as $menu_order => $term_id) {
        update_term_meta($term_id, 'emf_folder_order', $menu_order);
    }

    wp_send_json_success(__('Folder order saved successfully', 'easy-media-folder-manager'));
}
add_action('wp_ajax_emf_save_folder_order', 'emf_save_folder_order_callback');

// Assign Folders During Upload
function emf_add_folder_field($form_fields, $post) {
    $terms = get_terms('media_folder', array('hide_empty' => false));
    $selected_terms = wp_get_object_terms($post->ID, 'media_folder', array('fields' => 'ids'));

    $dropdown = '<select name="attachments[' . $post->ID . '][media_folder][]">';
    $dropdown .= '<option value="">' . __('No Folder', 'easy-media-folder-manager') . '</option>';
    foreach ($terms as $term) {
        $selected = in_array($term->term_id, $selected_terms) ? ' selected' : '';
        $dropdown .= '<option value="' . esc_attr($term->term_id) . '"' . $selected . '>' . esc_html($term->name) . '</option>';
    }
    $dropdown .= '</select>';

    $form_fields['media_folder'] = array(
        'label' => __('Media Folder', 'easy-media-folder-manager'),
        'input' => 'html',
        'html'  => $dropdown,
    );

    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'emf_add_folder_field', 10, 2);

function emf_save_folder_field($post, $attachment) {
    if (isset($attachment['media_folder']) && $attachment['media_folder'][0] !== '') {
        $term_id = intval($attachment['media_folder'][0]);
        wp_set_object_terms($post['ID'], $term_id, 'media_folder', false);
    } else {
        wp_set_object_terms($post['ID'], array(), 'media_folder', false);
    }
    return $post;
}
add_filter('attachment_fields_to_save', 'emf_save_folder_field', 10, 2);

// Rename Folder
function emf_rename_folder_callback() {
    check_ajax_referer('emf_nonce', 'nonce');
    if (!current_user_can('manage_categories')) {
        wp_send_json_error('Insufficient permissions');
    }

    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
    $folder_name = isset($_POST['folder_name']) ? sanitize_text_field($_POST['folder_name']) : '';

    if (!$folder_id || !$folder_name) {
        wp_send_json_error('Invalid folder ID or name');
    }

    $result = wp_update_term($folder_id, 'media_folder', array(
        'name' => $folder_name,
        'slug' => sanitize_title($folder_name),
    ));

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success(array('slug' => sanitize_title($folder_name)));
}
add_action('wp_ajax_emf_rename_folder', 'emf_rename_folder_callback');

// Delete Folder
function emf_delete_folder_callback() {
    check_ajax_referer('emf_nonce', 'nonce');
    if (!current_user_can('manage_categories')) {
        wp_send_json_error('Insufficient permissions');
    }

    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;

    if (!$folder_id) {
        wp_send_json_error('Invalid folder ID');
    }

    $media = get_objects_in_term($folder_id, 'media_folder');
    if (!empty($media)) {
        foreach ($media as $media_id) {
            wp_remove_object_terms($media_id, $folder_id, 'media_folder');
        }
    }

    $result = wp_delete_term($folder_id, 'media_folder');

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success();
}
add_action('wp_ajax_emf_delete_folder', 'emf_delete_folder_callback');

// Update Folder Icon
function emf_update_folder_icon_callback() {
    check_ajax_referer('emf_nonce', 'nonce');
    if (!current_user_can('manage_categories')) {
        wp_send_json_error('Insufficient permissions');
    }

    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
    $icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : '';

    if (!$folder_id || !$icon) {
        wp_send_json_error('Invalid folder ID or icon');
    }

    // Basic validation (ensure it's a Dashicons class)
    if (strpos($icon, 'dashicons-') !== 0) {
        wp_send_json_error('Invalid icon class');
    }

    update_term_meta($folder_id, 'emf_folder_icon', $icon);
    wp_send_json_success();
}
add_action('wp_ajax_emf_update_folder_icon', 'emf_update_folder_icon_callback');