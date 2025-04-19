<div id="emf-folder-sidebar">
    <h2><?php esc_html_e('Folders', 'easy-media-folder-manager'); ?></h2>
    <button id="emf-new-folder-btn" class="button"><?php esc_html_e('New Folder', 'easy-media-folder-manager'); ?></button>
    <div id="emf-new-folder-form" style="display: none; margin-top: 10px;">
        <input type="text" id="emf-new-folder-name" placeholder="<?php esc_attr_e('Folder Name', 'easy-media-folder-manager'); ?>" style="width: 100%; margin-bottom: 5px;" />
        <button id="emf-create-folder" class="button button-primary"><?php esc_html_e('Create', 'easy-media-folder-manager'); ?></button>
        <button id="emf-cancel-folder" class="button"><?php esc_html_e('Cancel', 'easy-media-folder-manager'); ?></button>
    </div>
    <ul id="emf-folder-list" style="margin-top: 10px;">
        <li class="emf-folder-item" data-folder-id="0">
            <span class="dashicons dashicons-portfolio"></span>
            <span class="emf-folder-title"><?php esc_html_e('All Media', 'easy-media-folder-manager'); ?></span>
        </li>
        <?php foreach ($folders as $folder) : ?>
            <li class="emf-folder-item" data-folder-id="<?php echo esc_attr($folder->term_id); ?>">
                <span class="dashicons dashicons-folder"></span>
                <span class="emf-folder-title"><?php echo esc_html($folder->name); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>