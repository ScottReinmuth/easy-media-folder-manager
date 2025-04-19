<?php
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue Scripts and Styles
function emf_enqueue_media_scripts($hook) {
    if ('upload.php' !== $hook) {
        return;
    }

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script(
        'emf-media-scripts',
        EMF_PLUGIN_URL . 'emf-scripts.js',
        array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'),
        filemtime(EMF_PLUGIN_DIR . 'emf-scripts.js'),
        true
    );

    $style_path = EMF_PLUGIN_DIR . 'emf-styles.css';
    wp_enqueue_style(
        'emf-media-styles',
        EMF_PLUGIN_URL . 'emf-styles.css',
        array(),
        file_exists($style_path) ? filemtime($style_path) : '1.2'
    );

    $folders = get_terms('media_folder', array(
        'hide_empty' => false,
        'meta_key'   => 'emf_folder_order',
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
    ));
    if (!is_array($folders)) {
        $folders = [];
    }
    foreach ($folders as &$folder) {
        $order = get_term_meta($folder->term_id, 'emf_folder_order', true);
        $icon = get_term_meta($folder->term_id, 'emf_folder_icon', true);
        $folder->meta = array(
            'emf_folder_order' => $order !== '' ? (int)$order : null,
            'emf_folder_icon'  => $icon ?: 'dashicons-folder' // Default icon
        );
    }
    unset($folder);

    wp_localize_script('emf-media-scripts', 'emf_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('emf_nonce'),
        'folders'  => $folders,
    ));
}
add_action('admin_enqueue_scripts', 'emf_enqueue_media_scripts');

// Inject Sidebar as First Child of #wpbody
function emf_inject_sidebar() {
    if ('upload.php' !== $GLOBALS['pagenow']) {
        return;
    }

    $folders = get_terms('media_folder', array(
        'hide_empty' => false,
        'meta_key'   => 'emf_folder_order',
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
    ));
    if (!is_array($folders)) {
        $folders = [];
    }
    foreach ($folders as &$folder) {
        $order = get_term_meta($folder->term_id, 'emf_folder_order', true);
        $icon = get_term_meta($folder->term_id, 'emf_folder_icon', true);
        $folder->meta = array(
            'emf_folder_order' => $order !== '' ? (int)$order : null,
            'emf_folder_icon'  => $icon ?: 'dashicons-folder'
        );
    }
    unset($folder);

    $sidebar_html = '<div id="emf-folder-sidebar">';
    $sidebar_html .= '<h2>' . esc_html__('Folders', 'easy-media-folder-manager') . '</h2>';
    $sidebar_html .= '<button id="emf-new-folder-btn" class="button">' . esc_html__('New Folder', 'easy-media-folder-manager') . '</button>';
    $sidebar_html .= '<div id="emf-new-folder-form" style="display:none; margin-top:10px;">';
    $sidebar_html .= '<input type="text" id="emf-new-folder-name" placeholder="' . esc_attr__('Folder Name', 'easy-media-folder-manager') . '" style="width:100%; margin-bottom:5px;" />';
    $sidebar_html .= '<button id="emf-create-folder" class="button button-primary">' . esc_html__('Create', 'easy-media-folder-manager') . '</button>';
    $sidebar_html .= '<button id="emf-cancel-folder" class="button">' . esc_html__('Cancel', 'easy-media-folder-manager') . '</button>';
    $sidebar_html .= '</div>';
    $sidebar_html .= '<select id="emf-folder-sort" style="margin-top:10px; width:100%;">';
    $sidebar_html .= '<option value="name-asc">' . esc_html__('Name (A-Z)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="name-desc">' . esc_html__('Name (Z-A)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="date-asc">' . esc_html__('Date (Oldest First)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="date-desc">' . esc_html__('Date (Newest First)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="count-asc">' . esc_html__('Count (Low to High)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="count-desc">' . esc_html__('Count (High to Low)', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '<option value="manual">' . esc_html__('Manual Order', 'easy-media-folder-manager') . '</option>';
    $sidebar_html .= '</select>';
    $sidebar_html .= '<ul id="emf-folder-list" style="margin-top:10px;">';
    $sidebar_html .= '<li class="emf-folder-item" data-folder-id="0"><span class="dashicons dashicons-portfolio"></span><span class="emf-folder-title">' . esc_html__('All Media', 'easy-media-folder-manager') . '</span></li>';
    if (empty($folders)) {
        $sidebar_html .= '<li>No folders found</li>';
    } else {
        foreach ($folders as $folder) {
            $icon_class = esc_attr($folder->meta['emf_folder_icon']);
            $sidebar_html .= '<li class="emf-folder-item" data-folder-id="' . esc_attr($folder->term_id) . '">';
            $sidebar_html .= '<span class="dashicons ' . $icon_class . '"></span>';
            $sidebar_html .= '<span class="emf-folder-title">' . esc_html($folder->name) . '</span>';
            $sidebar_html .= '<span class="emf-folder-menu-toggle dashicons dashicons-ellipsis" style="float:right; cursor:pointer;"></span>';
            $sidebar_html .= '<div class="emf-folder-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:5px;">';
            $sidebar_html .= '<a href="#" class="emf-rename-folder" data-folder-id="' . esc_attr($folder->term_id) . '">Rename</a><br>';
            $sidebar_html .= '<a href="#" class="emf-delete-folder" data-folder-id="' . esc_attr($folder->term_id) . '">Delete</a><br>';
            $sidebar_html .= '<a href="#" class="emf-edit-icon" data-folder-id="' . esc_attr($folder->term_id) . '">Edit Icon</a>';
            $sidebar_html .= '</div>';
            $sidebar_html .= '</li>';
        }
    }
    $sidebar_html .= '</ul>';
    $sidebar_html .= '</div>';

    wp_add_inline_script(
        'emf-media-scripts',
        sprintf(
            'jQuery(document).ready(function($){$("#wpbody").prepend(%s);});',
            json_encode($sidebar_html)
        )
    );
}
add_action('admin_footer', 'emf_inject_sidebar');