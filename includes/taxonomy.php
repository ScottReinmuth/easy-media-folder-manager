<?php
// Register Custom Taxonomy
function emf_register_media_folders() {
    $labels = array(
        'name'              => _x('Media Folders', 'taxonomy general name', 'easy-media-folder-manager'),
        'singular_name'     => _x('Media Folder', 'taxonomy singular name', 'easy-media-folder-manager'),
        'search_items'      => __('Search Media Folders', 'easy-media-folder-manager'),
        'all_items'         => __('All Media Folders', 'easy-media-folder-manager'),
        'parent_item'       => __('Parent Media Folder', 'easy-media-folder-manager'),
        'parent_item_colon' => __('Parent Media Folder:', 'easy-media-folder-manager'),
        'edit_item'         => __('Edit Media Folder', 'easy-media-folder-manager'),
        'update_item'       => __('Update Media Folder', 'easy-media-folder-manager'),
        'add_new_item'      => __('Add New Media Folder', 'easy-media-folder-manager'),
        'new_item_name'     => __('New Media Folder Name', 'easy-media-folder-manager'),
        'menu_name'         => __('Media Folders', 'easy-media-folder-manager'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'media-folder'),
    );

    register_taxonomy('media_folder', array('attachment'), $args);
}
add_action('init', 'emf_register_media_folders');

// Filter Media by Folder
function emf_filter_media_by_folder($query) {
    global $pagenow;
    if (is_admin() && $pagenow === 'upload.php' && !empty($_GET['media_folder'])) {
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'media_folder',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['media_folder']),
            ),
        ));
    }
}
add_action('pre_get_posts', 'emf_filter_media_by_folder');