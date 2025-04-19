=== Easy Media Folder Manager === Contributors: scottreinmuth Tags: media, folders, organization, file manager Requires at least: 5.0 Tested up to: 6.5 Stable tag: 1.1.0 License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html

Organize your WordPress media library into folders for easier management.

== Description == Easy Media Folder Manager allows you to create, rename, and delete folders in your WordPress media library. Move media files between folders using a custom column in the media library. Supports hierarchical folders (subfolders) for better organization. Perfect for keeping your media library tidy and efficient.

== Features ==

- Create, rename, and delete media folders.
- Move media between folders using a dropdown in the media library.
- Support for hierarchical folders (subfolders).
- Seamless integration with the WordPress media library.
- Lightweight, secure, and compatible with WordPress 6.5+.
- User-friendly interface with success/error notifications.

== Installation ==

1. Upload the `easy-media-folder-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Media &gt; Media Folders to create and manage folders.
4. Use the "Folder" column in the media library to assign media to folders.

== Frequently Asked Questions == = Does this plugin support subfolders? = Yes, version 1.1.0 introduced support for hierarchical folders (subfolders).

= Is it compatible with other media plugins? = It uses a unique taxonomy (`emfm_media_folder`) to minimize conflicts. However, test with plugins like FileBird or Real Media Library for compatibility.

= How do I move media to a folder? = In the media library, use the "Folder" dropdown column to select a folder for each media item.

== Screenshots ==

1. Media Folders management page with create, rename, and delete options.
2. Folder column in the media library for assigning media to folders.

== Changelog == = 1.1.0 =

- Added support for hierarchical folders.
- Improved security with nonce verification and input sanitization.
- Optimized performance with transient caching.
- Enhanced admin interface with better styling and notices.
- Updated compatibility for WordPress 6.5.
- Improved documentation and code quality.

= 1.0.0 =

- Initial release.

== Upgrade Notice == = 1.1.0 = This update includes security improvements, hierarchical folder support, and performance optimizations. Backup your site before upgrading.