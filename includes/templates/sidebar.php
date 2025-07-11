<?php
/**
 * Sidebar template for Easy Media Folder Manager.
 *
 * @package Easy_Media_Folder_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

$core = new Easy_Media_Folder_Manager();
$folders = $core->get_sorted_folders('manual');
?>
<div id="emf-folder-sidebar">
    <h2><?php esc_html_e('Folders', 'easy-media-folder-manager'); ?></h2>
    <button id="emf-new-folder-btn" class="button"><?php esc_html_e('New Folder', 'easy-media-folder-manager'); ?></button>
    <div id="emf-new-folder-form" style="display:none; margin-top:10px;">
        <input type="text" id="emf-new-folder-name" placeholder="<?php esc_attr_e('Folder Name', 'easy-media-folder-manager'); ?>" style="width:100%; margin-bottom:5px;" aria-label="<?php esc_attr_e('Folder Name', 'easy-media-folder-manager'); ?>" />
        <select id="emf-parent-folder" aria-label="<?php esc_attr_e('Parent folder', 'easy-media-folder-manager'); ?>">
            <option value="0"><?php esc_html_e('No Parent', 'easy-media-folder-manager'); ?></option>
            <?php foreach ($folders as $folder) : ?>
                <option value="<?php echo esc_attr($folder->term_id); ?>"><?php echo esc_html($folder->name); ?></option>
            <?php endforeach; ?>
        </select>
        <button id="emf-create-folder" class="button button-primary"><?php esc_html_e('Create', 'easy-media-folder-manager'); ?></button>
        <button id="emf-cancel-folder" class="button"><?php esc_html_e('Cancel', 'easy-media-folder-manager'); ?></button>
    </div>
    <select id="emf-folder-sort" style="margin-top:10px; width:100%;" aria-label="<?php esc_attr_e('Sort folders', 'easy-media-folder-manager'); ?>">
        <option value="name-asc"><?php esc_html_e('Name (A-Z)', 'easy-media-folder-manager'); ?></option>
        <option value="name-desc"><?php esc_html_e('Name (Z-A)', 'easy-media-folder-manager'); ?></option>
        <option value="date-asc"><?php esc_html_e('Date (Oldest First)', 'easy-media-folder-manager'); ?></option>
        <option value="date-desc"><?php esc_html_e('Date (Newest First)', 'easy-media-folder-manager'); ?></option>
        <option value="count-asc"><?php esc_html_e('Count (Low to High)', 'easy-media-folder-manager'); ?></option>
        <option value="count-desc"><?php esc_html_e('Count (High to Low)', 'easy-media-folder-manager'); ?></option>
        <option value="manual"><?php esc_html_e('Manual Order', 'easy-media-folder-manager'); ?></option>
    </select>
    <ul id="emf-folder-list" style="margin-top:10px;">
        <li class="emf-folder-item" data-folder-id="0" role="button" aria-label="<?php esc_attr_e('View all media', 'easy-media-folder-manager'); ?>">
            <span class="dashicons dashicons-portfolio"></span>
            <span class="emf-folder-title"><?php esc_html_e('All Media', 'easy-media-folder-manager'); ?></span>
        </li>
        <?php if (empty($folders)) : ?>
            <li><?php esc_html_e('No folders found', 'easy-media-folder-manager'); ?></li>
        <?php else : ?>
            <?php foreach ($folders as $folder) : ?>
                <li class="emf-folder-item" data-folder-id="<?php echo esc_attr($folder->term_id); ?>" role="button" aria-label="<?php esc_attr_e('View folder: ' . esc_html($folder->name), 'easy-media-folder-manager'); ?>">
                    <span class="dashicons <?php echo esc_attr($folder->meta['emf_folder_icon'] ?? 'dashicons-folder'); ?>"></span>
                    <span class="emf-folder-title"><?php echo str_repeat('  ', $core->get_term_depth($folder)) . esc_html($folder->name); ?></span>
                    <span class="emf-folder-menu-toggle dashicons dashicons-ellipsis" style="float:right; cursor:pointer;" tabindex="0" aria-label="<?php esc_attr_e('Folder actions', 'easy-media-folder-manager'); ?>"></span>
                    <div class="emf-folder-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:5px;">
                        <a href="#" class="emf-rename-folder" data-folder-id="<?php echo esc_attr($folder->term_id); ?>"><?php esc_html_e('Rename', 'easy-media-folder-manager'); ?></a><br>
                        <a href="#" class="emf-delete-folder" data-folder-id="<?php echo esc_attr($folder->term_id); ?>"><?php esc_html_e('Delete', 'easy-media-folder-manager'); ?></a><br>
                        <a href="#" class="emf-edit-icon" data-folder-id="<?php echo esc_attr($folder->term_id); ?>"><?php esc_html_e('Edit Icon', 'easy-media-folder-manager'); ?></a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
