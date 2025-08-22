<?php
/**
 * Plugin Name: Easy Media Folder Manager
 * Plugin URI: https://github.com/ScottReinmuth/easy-media-folder-manager
 * Description: Organize your WordPress media library into folders for easier management.
 * Version: 1.2.3
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
            'manage_categories',
            'emfm_folders',
            [$this, 'render_folders_page']
        );
    }

    /**
     * Render folders management page.
     */
    public function render_folders_page() {
        if (!current_user_can('manage_categories')) {
            wp_die('Unauthorized access.');
        }

        $core = new Easy_Media_Folder_Manager();
        $folders = $core->get_folders();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Media Folders', 'easy-media-folder-manager'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                <input type="text" name="folder_name" placeholder="<?php esc_attr_e('New folder name', 'easy-media-folder-manager'); ?>" aria-label="<?php esc_attr_e('New folder name', 'easy-media-folder-manager'); ?>" />
                <select name="parent_folder" aria-label="<?php esc_attr_e('Parent folder', 'easy-media-folder-manager'); ?>">
                    <option value="0"><?php esc_html_e('No Parent', 'easy-media-folder-manager'); ?></option>
                    <?php foreach ($folders as $folder) : ?>
                        <option value="<?php echo esc_attr($folder->term_id); ?>"><?php echo esc_html($folder->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="action" value="create_folder" class="button button-primary"><?php esc_html_e('Create Folder', 'easy-media-folder-manager'); ?></button>
            </form>
            <h2><?php esc_html_e('Existing Folders', 'easy-media-folder-manager'); ?></h2>
            <input type="text" id="emfm-folder-search" placeholder="<?php esc_attr_e('Search folders...', 'easy-media-folder-manager'); ?>" style="width:100%; margin-bottom:10px;" aria-label="<?php esc_attr_e('Search folders', 'easy-media-folder-manager'); ?>" />
            <table class="wp-list-table widefat fixed striped" id="emfm-folder-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Folder Name', 'easy-media-folder-manager'); ?></th>
                        <th><?php esc_html_e('Actions', 'easy-media-folder-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($folders as $folder) : ?>
                        <tr data-folder-name="<?php echo esc_attr(strtolower($folder->name)); ?>">
                            <td><?php echo esc_html($folder->name); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                                    <input type="hidden" name="folder_id" value="<?php echo esc_attr($folder->term_id); ?>" />
                                    <input type="text" name="new_folder_name" placeholder="<?php esc_attr_e('New name', 'easy-media-folder-manager'); ?>" aria-label="<?php esc_attr_e('New folder name', 'easy-media-folder-manager'); ?>" />
                                    <button type="submit" name="action" value="rename_folder" class="button"><?php esc_html_e('Rename', 'easy-media-folder-manager'); ?></button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('emfm_folder_action', 'emfm_nonce'); ?>
                                    <input type="hidden" name="folder_id" value="<?php echo esc_attr($folder->term_id); ?>" />
                                    <button type="submit" name="action" value="delete_folder" class="button" onclick="return confirm('<?php echo esc_js(__('Are you sure?', 'easy-media-folder-manager')); ?>');"><?php esc_html_e('Delete', 'easy-media-folder-manager'); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#emfm-folder-search').on('input', function() {
                    const search = $(this).val().toLowerCase();
                    $('#emfm-folder-table tbody tr').each(function() {
                        const folderName = $(this).data('folder-name');
                        $(this).toggle(folderName.includes(search));
                    });
                });
            });
        </script>
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
        $folders = $core->get_sorted_folders('manual');
        wp_localize_script('emfm-admin', 'emfm_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $this->nonce_handler('emfm_folder_action'),
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
     * Inject sidebar into media library.
     */
    public function inject_sidebar() {
        if ('upload.php' !== $GLOBALS['pagenow']) {
            return;
        }
        wp_add_inline_script(
            'emfm-admin',
            'jQuery(document).ready(function($){$("#wpbody").prepend(' . json_encode($this->render_sidebar()) . ');});'
        );
    }

    /**
     * Render folder sidebar HTML.
     *
     * @return string Sidebar HTML.
     */
    public function render_sidebar() {
        ob_start();
        include plugin_dir_path(__FILE__) . 'includes/templates/sidebar.php';
        return ob_get_clean();
    }

    /**
     * Get folders with metadata.
     *
     * @return WP_Term[] List of folder terms with metadata.
     */
    public function get_folders() {
        $core = new Easy_Media_Folder_Manager();
        return $core->get_folders();
    }
}

// Initialize plugin
EMFM_Plugin::get_instance();
