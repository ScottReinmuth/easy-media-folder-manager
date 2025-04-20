=== Easy Media Folder Manager === Contributors: scottreinmuth Tags: media, folders, organization, file manager Requires at least: 5.0 Tested up to: 6.5 Stable tag: 1.2.0 License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html

Organize your WordPress media library into folders for easier management.

== Description == Easy Media Folder Manager allows you to create, rename, and delete folders in your WordPress media library. Move media files between folders using a custom column or drag-and-drop interface. Supports hierarchical folders (subfolders) for better organization. Perfect for keeping your media library tidy and efficient.

== Features ==

- Create, rename, and delete media folders.
- Move media between folders using a dropdown or drag-and-drop.
- Support for hierarchical folders (subfolders).
- Customizable folder icons using Dashicons.
- Sort folders by name, date, count, or manual order.
- Seamless integration with the WordPress media library.
- Lightweight, secure, and compatible with WordPress 6.5+.
- User-friendly interface with success/error notifications.

== Installation ==

1. Upload the `easy-media-folder-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Media &gt; Media Folders to create and manage folders.
4. Use the "Folder" column or sidebar in the media library to assign media to folders.

== Frequently Asked Questions == = Does this plugin support subfolders? = Yes, version 1.1.0 introduced support for hierarchical folders (subfolders).

= Is it compatible with other media plugins? = It uses a unique taxonomy (`emfm_media_folder`) to minimize conflicts. Test with plugins like FileBird or Real Media Library for compatibility.

= How do I move media to a folder? = In the media library, use the "Folder" dropdown column or drag media items to folders in the sidebar.

== Screenshots ==

1. Media Folders management page with create, rename, and delete options.
2. Folder column and sidebar in the media library for assigning media to folders.

== Changelog == = 1.2.0 =

- Consolidated taxonomy into `emfm_media_folder` for consistency.
- Merged sidebar logic into main plugin file, removing redundant template.
- Centralized folder management (create, rename, delete) in core class.
- Unified asset enqueuing for scripts and styles.
- Standardized nonce handling and admin notices.
- Optimized folder sorting with server-side logic and AJAX updates.
- Improved code organization with dedicated AJAX handlers file.
- Enhanced documentation and code quality.

= 1.1.0 =

- Added support for hierarchical folders.
- Improved security with nonce verification and input sanitization.
- Optimized performance with transient caching.
- Enhanced admin interface with better styling and notices.
- Updated compatibility for WordPress 6.5.
- Improved documentation and code quality.

= 1.0.0 =

- Initial release.

== Upgrade Notice == = 1.2.0 = This update streamlines the codebase, improves performance, and enhances consistency. Backup your site before upgrading.