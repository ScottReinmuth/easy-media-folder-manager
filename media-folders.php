<?php
/**
 * Plugin Name: Easy Media Folder Manager
 * Plugin URI: https://github.com/ScottReinmuth/easy-media-folder-manager
 * Description: Organize your WordPress media library into folders for easier management.
 * Version: 1.1.0
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
        require_once plugin_dir_path(__FILE__) . 'includes/class-easy-media-folder-manager.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-media-list-table.php';
    }

    /**
     * Initialize hooks.
     */
    private function init() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('init', [$this, 'load_textdomain']);

        // Initialize core class
        $core = new Easy_Media_Folder_Manager();
        $core->init();
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
     * Enqueue admin scripts and styles conditionally.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_scripts($hook) {
        $screen = get_current_screen();
        if ('upload.php' === $hook || 'media_page_emfm_folders' === $screen->id) {
            wp_enqueue_style(
                'emfm-admin',
                plugins_url('assets/css/admin.css', __FILE__),
                [],
                '1.1.0'
            );
            wp_enqueue_script(
                'emfm-admin',
                plugins_url('assets/js/admin.js', __FILE__),
                ['jquery'],
                '1.1.0',
                true
            );
            wp_localize_script('emfm-admin', 'emfmAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('emfm_move_media'),
            ]);
        }
    }

    /**
     * Display admin notices for user feedback.
     */
    public function admin_notices() {
        $messages = [
            'created' => __('Folder created successfully.', 'easy-media-folder-manager'),
            'renamed' => __('Folder renamed successfully.', 'easy-media-folder-manager'),
            'deleted' => __('Folder deleted successfully.', 'easy-media-folder-manager'),
            'error' => __('An error occurred. Please try again.', 'easy-media-folder-manager'),
        ];

        if (isset($_GET['message']) && array_key_exists($_GET['message'], $messages)) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                $_GET['message'] === 'error' ? 'error' : 'success',
                esc_html($messages[$_GET['message']])
            );
        }
    }
}

// Initialize plugin
EMFM_Plugin::get_instance();
?>