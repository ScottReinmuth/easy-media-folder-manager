<?php
/*
 * Plugin Name: Easy Media Folder Manager
 * Description: Organize your WordPress media library into folders with ease, integrated into the main Media Library with drag-and-drop and folder creation.
 * Version: 1.2
 * Author: Scott Reinmuth
 * License: GPL-2.0+
 * Text Domain: easy-media-folder-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('EMF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load modular files
require_once EMF_PLUGIN_DIR . 'includes/taxonomy.php';
require_once EMF_PLUGIN_DIR . 'includes/ajax.php';
require_once EMF_PLUGIN_DIR . 'includes/ui.php';

// Load text domain for translations
function emf_load_textdomain() {
    load_plugin_textdomain('easy-media-folder-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'emf_load_textdomain');