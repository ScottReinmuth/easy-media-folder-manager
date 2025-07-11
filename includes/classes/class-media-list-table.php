<?php
/**
 * Custom media list table for folder management.
 *
 * @package Easy_Media_Folder_Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class EMFM_Media_List_Table
 */
class EMFM_Media_List_Table {
    /**
     * Cached folders.
     *
     * @var WP_Term[]
     */
    private $folders = null;

    /**
     * Initialize hooks.
     */
    public function init() {
        add_filter('manage_media_columns', [$this, 'add_folder_column']);
        add_action('manage_media_custom_column', [$this, 'render_folder_column'], 10, 2);
    }

    /**
     * Add folder column to media library.
     *
     * @param array $columns Media columns.
     * @return array Modified columns.
     */
    public function add_folder_column($columns) {
        $columns['emfm_media_folder'] = __('Folder', 'easy-media-folder-manager');
        return $columns;
    }

    /**
     * Render folder column content.
     *
     * @param string $column_name Column name.
     * @param int    $media_id    Media ID.
     */
    public function render_folder_column($column_name, $media_id) {
        if ('emfm_media_folder' !== $column_name) {
            return;
        }

        $core = new Easy_Media_Folder_Manager();
        if (is_null($this->folders)) {
            $this->folders = $core->get_folders();
        }

        $current_folders = wp_get_object_terms($media_id, 'emfm_media_folder', ['fields' => 'ids']);
        $current_folder_id = !empty($current_folders) ? absint($current_folders[0]) : 0;

        ?>
        <select class="emfm-folder-select" data-media-id="<?php echo esc_attr($media_id); ?>" aria-label="<?php esc_attr_e('Select folder for media item', 'easy-media-folder-manager'); ?>">
            <option value="0" <?php selected($current_folder_id, 0); ?>><?php esc_html_e('None', 'easy-media-folder-manager'); ?></option>
            <?php foreach ($this->folders as $folder) : ?>
                <?php $indent = $folder->parent ? str_repeat('&nbsp;&nbsp;', $core->get_term_depth($folder)) : ''; ?>
                <option value="<?php echo esc_attr($folder->term_id); ?>" <?php selected($current_folder_id, $folder->term_id); ?>>
                    <?php echo $indent . esc_html($folder->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
