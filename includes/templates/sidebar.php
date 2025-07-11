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
        <option value="
