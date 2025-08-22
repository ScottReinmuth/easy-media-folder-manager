=== Easy Media Folder Manager ===
Contributors: scottreinmuth
Tags: media, folders, organization, file manager
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.2.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Organize your WordPress media library into folders for easier management.

== Description ==
Easy Media Folder Manager allows you to create, rename, and delete folders in your WordPress media library. Move media files between folders using a custom column or drag-and-drop interface. Supports hierarchical folders (subfolders) for better organization. Perfect for keeping your media library tidy and efficient.

== Features ==

- Create, rename, and delete media folders with support for subfolders.
- Move media between folders using a dropdown or drag-and-drop.
- Customizable folder icons using Dashicons.
- Sort folders by name, date, count, or manual order.
- Seamless integration with the WordPress media library.
- Lightweight, secure, and compatible with WordPress 6.5+.
- User-friendly interface with success/error notifications.
- Accessible UI with keyboard navigation and ARIA support.

== Installation ==

1. Upload the `easy-media-folder-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Media > Media Folders to create and manage folders.
4. Use the "Folder" column or sidebar in the media library to assign media to folders.

== Frequently Asked Questions ==

= Does this plugin support subfolders? =
Yes, version 1.1.0 introduced support for hierarchical folders (subfolders). You can assign a parent folder when creating a new folder.

= Is it compatible with other media plugins? =
It uses a unique taxonomy (`emfm_media_folder`) to minimize conflicts. Test with plugins like FileBird or Real Media Library for compatibility. If conflicts occur, check for taxonomy overlaps and disable conflicting plugins.

= How do I move media to a folder? =
In the media library, use the "Folder" dropdown column or drag media items to folders in the sidebar. Changes are saved via AJAX, and a page reload may be required to reflect updates.

= What should I do if AJAX requests fail? =
Check the browser console for error details. Ensure jQuery UI dependencies are loaded and there are no JavaScript conflicts with other plugins. Verify your WordPress AJAX URL and nonce settings.

= How can I troubleshoot folder display issues? =
Clear the `emfm_folders` transient in the WordPress database (using a plugin like WP-Optimize) or deactivate/reactivate the plugin to refresh folder data.

== Screenshots ==

1. Media Folders management page with create, rename, delete, and parent folder options.
2. Folder column in the media library for assigning media to folders via dropdown.
3. Sidebar with drag-and-drop interface and folder icon customization.
4. Hierarchical folder display with indentation for subfolders.

== Changelog ==

= 1.2.5 =
- Fixed folder filtering for AJAX and admin queries.
- Fixed manual folder sort when the first item has order 0.

= 1.2.4 =
- Fixed folder content not displaying.
- Fixed folder sorting via AJAX.

= 1.2.3 =
- Load folders via AJAX for sidebar navigation.
- Fixed create folder button event binding.

= 1.2.2 =
- Exposed folder depth helper to prevent fatal error when used outside the class.

= 1.2.1 =
- Fixed folder action buttons using translation-independent identifiers.
- Added nonce existence checks and safe redirects for folder actions.

= 1.2.0 =
- Added support for hierarchical folder UI with parent folder selection.
- Standardized nonce handling with consistent `emfm_folder_action` action.
- Unified permission checks to `manage_categories` for taxonomy-related actions.
- Optimized transient caching by updating incrementally instead of deleting.
- Improved accessibility with ARIA attributes and keyboard navigation.
- Added client-side folder search on the management page.
- Moved sidebar rendering to a separate template file for better organization.
- Enhanced error handling with detailed logging and standardized responses.
- Optimized JavaScript with `MutationObserver`, debouncing, and cached selectors.
- Improved CSS with custom properties and responsive design for large screens.

= 1.1.0 =
- Added support for hierarchical folders.
- Improved security with nonce verification and input sanitization.
- Optimized performance with transient caching.
- Enhanced admin interface with better styling and notices.
- Updated compatibility for WordPress 6.5.
- Improved documentation and code quality.

= 1.0.0 =
- Initial release.

== Upgrade Notice ==

= 1.2.5 =
Fixes folder filtering in the media library and improves manual sorting.

= 1.2.4 =
Resolves issues with folder content display and sorting via AJAX.

= 1.2.3 =
Loads folders via AJAX for faster sidebar navigation and fixes create folder button event binding.

= 1.2.2 =
Fixes fatal error when retrieving folder hierarchy depth outside core.

= 1.2.1 =
Ensures folder actions work across translated WordPress installations and improves security checks.

= 1.2.0 =
This update improves performance, security, and accessibility. Backup your site before upgrading. Check for taxonomy conflicts with other media plugins (e.g., FileBird) and clear the `emfm_folders` transient if folders do not display correctly. Deactivate/reactivate the plugin to refresh cached data.

== Developer Notes ==

- **Extending Sorting Methods**: Add custom sorting options in `Easy_Media_Folder_Manager::get_sorted_folders` by extending the `switch` statement and updating the `emf-folder-sort` dropdown.
- **Custom Icons**: Extend the `iconOptions` array in `emfm-admin.js` or fetch icons dynamically via an AJAX endpoint.
- **Hooks**: Use the `emfm_folders` transient or `pre_get_posts` filter to modify folder queries.
- **Debugging**: Enable WordPress debug mode (`WP_DEBUG`) and check the browser console for AJAX errors. Log errors are written to the PHP error log for AJAX failures.
