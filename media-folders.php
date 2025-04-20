<?php
/**
 * Plugin Name: Easy Media Folder Manager
 * Plugin URI: https://github.com/ScottReinmuth/easy-media-folder-manager
 * Description: Organize your WordPress media library into folders for easier management.
 * Version: 1.2.0
 * Author: Scott Reinmuth
 * Author URI: https://github.com/ScottReinmuth
 * License: GPLv2 or later
 * Text Domain: easy-media-folder-manager
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/ScottReinmuth/easy-media-folder-manager
 *
 * @package Easy_Media_Folder_Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main plugin class loader.
 */
class EMFM_Plugin {
    /**
     * Singleton instance.
     *
     * @var EMFM_Plugin
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return EMFM_Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init();
    }

    /**
     * Load required files.
     */
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/classes/class-easy-media-folder-manager.php';
        require_once plugin_dir_path(__FILE__) . 'includes/classes/class-media-list-table.php';
        require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
    }

    /**
     * Initialize hooks.
     */
    private function init() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_footer', [$this, 'inject_sidebar']);
        add_action('init', [$this, 'load_textdomain']);

        // Initialize core and media list table
        $core = new Easy_Media_Folder_Manager();
        $core->init();
        $media_list = new EMFM_Media_List_Table();
        $media_list->init();
    }

    /**
     * Load plugin text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain('easy-media-folder-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Add admin menu page.
     */
    public function admin_menu() {
        add_media_page(
            esc_html__('Media Folders', 'easy-media-folder-manager'),
            esc_html__('Media Folders', 'easy-media-folder-manager'),
            'upload_files',
            'emfm_folders',
            [$this, 'render_folders_page']
        );
    }

    /**
     * Render folders management page.
     */
    public function render_folders_page() {
        $core = new Easy_Media_Folder_Manager();
        $folders = $core->get_folders();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Media Folders', 'easy-media-folder-manager'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                <input type="text" name="folder_name" placeholder="<?php esc_attr_e('New folder name', 'easy-media-folder-manager'); ?>" />
                <input type="submit" name="action" value="<?php esc_attr_e('Create Folder', 'easy-media-folder-manager'); ?>" class="button button-primary" />
            </form>
            <h2><?php esc_html_e('Existing Folders', 'easy-media-folder-manager'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Folder Name', 'easy-media-folder-manager'); ?></th>
                        <th><?php esc_html_e('Actions', 'easy-media-folder-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($folders as $folder) : ?>
                        <tr>
                            <td><?php echo esc_html($folder->name); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                                    <input type="hidden" name="folder_id" value="<?php echo esc_attr($folder->term_id); ?>" />
                                    <input type="text" name="new_folder_name" placeholder="<?php esc_attr_e('New name', 'easy-media-folder-manager'); ?>" />
                                    <input type="submit" name="action" value="<?php esc_attr_e('Rename', 'easy-media-folder-manager'); ?>" class="button" />
                                </form>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                                    <input type="hidden" name="folder_id" value="<?php echo esc_attr($folder->term_id); ?>" />
                                    <input type="submit" name="action" value="<?php esc_attr_e('Delete', 'easy-media-folder-manager'); ?>" class="button" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'easy-media-folder-manager'); ?>');" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_assets($hook) {
        $screen = get_current_screen();
        if (!in_array($hook, ['upload.php', 'media_page_emfm_folders']) && $screen->id !== 'media_page_emfm_folders') {
            return;
        }

        // Enqueue jQuery UI dependencies
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-sortable');

        // Enqueue plugin scripts
        wp_enqueue_script(
            'emfm-admin',
            plugins_url('assets/js/emfm-admin.js', __FILE__),
            ['jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/emfm-admin.js'),
            true
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'emfm-admin',
            plugins_url('assets/css/emfm-admin.css', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/emfm-admin.css')
        );

        // Localize script data
        $core = new Easy_Media_Folder_Manager();
        $folders = $core->get_folders();
        foreach ($folders as &$folder) {
            $order = get_term_meta($folder->term_id, 'emf_folder_order', true);
            $icon = get_term_meta($folder->term_id, 'emf_folder_icon', true);
            $folder->meta = [
                'emf_folder_order' => $order !== '' ? (int)$order : null,
                'emf_folder_icon' => $icon ?: 'dashicons-folder',
            ];
        }
        unset($folder);

        wp_localize_script('emfm-admin', 'emfm_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $this->nonce_handler('emfm_action'),
            'folders' => $folders,
        ]);
    }

    /**
     * Handle nonce creation and verification.
     *
     * @param string $action  Nonce action.
     * @param string $verify  Nonce to verify (optional).
     * @return string|bool Nonce string or verification result.
     */
    public function nonce_handler($action, $verify = '') {
        $nonce_key = 'emfm_nonce';
        if ($verify) {
            return wp_verify_nonce($verify, $action);
        }
        return wp_create_nonce($action);
    }

    /**
     * Display admin notice.
     *
     * @param string $message Message to display.
     * @param string $type    Notice type (success, error).
     */
    public function display_notice($message, $type = 'success') {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }

    /**
     * Handle admin notices.
     */
    public function admin_notices() {
        $messages = [
            'created' => __('Folder created successfully.', 'easy-media-folder-manager'),
            'renamed' => __('Folder renamed successfully.', 'easy-media-folder-manager'),
            'deleted' => __('Folder deleted successfully.', 'easy-media-folder-manager'),
            'error' => __('An error occurred. Please try again.', 'easy-media-folder-manager'),
        ];

        if (isset($_GET['message']) && array_key_exists($_GET['message'], $messages)) {
            $this->display_notice($messages[$_GET['message']], $_GET['message'] === 'error' ? 'error' : 'success');
        }
    }

    /**
     * Render folder sidebar HTML.
     *
     * @return string Sidebar HTML.
     */
    public function render_sidebar() {
        $core = new Easy_Media_Folder_Manager();
        $folders = $core->get_sorted_folders('manual');
        ob_start();
        ?>
        <div id="emf-folder-sidebar">
            <h2><?php esc_html_e('Folders', 'easy-media-folder-manager'); ?></h2>
            <button id="emf-new-folder-btn" class="button"><?php esc_html_e('New Folder', 'easy-media-folder-manager'); ?></button>
            <div id="emf-new-folder-form" style="display:none; margin-top:10px;">
                <input type="text" id="emf-new-folder-name" placeholder="<?php esc_attr_e('Folder Name', 'easy-media-folder-manager'); ?>" style="width:100%; margin-bottom:5px;" />
                <button id="emf-create-folder" class="button button-primary"><?php esc_html_e('Create', 'easy-media-folder-manager'); ?></button>
                <button id="emf-cancel-folder" class="button"><?php esc_html_e('Cancel', 'easy-media-folder-manager'); ?></button>
            </div>
            <select id="emf-folder-sort" style="margin-top:10px; width:100%;">
                <option value="name-asc"><?php esc_html_e('Name (A-Z)', 'easy-media-folder-manager'); ?></option>
                <option value="name-desc"><?php esc_html_e('Name (Z-A)', 'easy-media-folder-manager'); ?></option>
                <option value="date-asc"><?php esc_html_e('Date (Oldest First)', 'easy-media-folder-manager'); ?></option>
                <option value="date-desc"><?php esc_html_e('Date (Newest First)', 'easy-media-folder-manager'); ?></option>
                <option value="count-asc"><?php esc_html_e('Count (Low to High)', 'easy-media-folder-manager'); ?></option>
                <option value="count-desc"><?php esc_html_e('Count (High to Low)', 'easy-media-folder-manager'); ?></option>
                <option value="manual"><?php esc_html_e('Manual Order', 'easy-media-folder-manager'); ?></option>
            </select>
            <ul id="emf-folder-list" style="margin-top:10px;">
                <li class="emf-folder-item" data-folder-id="0">
                    <span class="dashicons dashicons-portfolio"></span>
                    <span class="emf-folder-title"><?php esc_html_e('All Media', 'easy-media-folder-manager'); ?></span>
                </li>
                <?php if (empty($folders)) : ?>
                    <li><?php esc_html_e('No folders found', 'easy-media-folder-manager'); ?></li>
                <?php else : ?>
                    <?php foreach ($folders as $folder) : ?>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Inject sidebar into media library.
     */
    public function inject_sidebar() {
        if ('upload.php' !== $GLOBALS['pagenow']) {
            return;
        }
        wp_add_inline_script(
            'emfm-admin',
            sprintf(
                'jQuery(document).ready(function($){$("#wpbody").prepend(%s);});',
                json_encode($this->render_sidebar())
            )
        );
    }

    /**
     * Get folders with metadata.
     *
     * @return array List of folder terms with metadata.
     */
    public function get_folders() {
        $core = new Easy_Media_Folder_Manager();
        $folders = $core->get_folders();
        foreach ($folders as &$folder) {
            $order = get_term_meta($folder->term_id, 'emf_folder_order', true);
            $icon = get_term_meta($folder->term_id, 'emf_folder_icon', true);
            $folder->meta = [
                'emf_folder_order' => $order !== '' ? (int)$order : null,
                'emf_folder_icon' => $icon ?: 'dashicons-folder',
            ];
        }
        return $folders;
    }
}

// Initialize plugin
EMFM_Plugin::get_instance();
?>