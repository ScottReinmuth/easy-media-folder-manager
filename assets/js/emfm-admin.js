jQuery(document).ready(function($) {
    // Namespace for global variables
    window.emfm = window.emfm || {};

    // Check for emfm_data
    if (typeof emfm_data === 'undefined') {
        console.error('emfm_data is not defined');
        return;
    }

    // Full Dashicons list (compatible with WordPress 6.5)
    const iconOptions = [
        'dashicons-menu', 'dashicons-admin-site', 'dashicons-dashboard', 'dashicons-admin-media', 'dashicons-admin-links',
        'dashicons-admin-page', 'dashicons-admin-comments', 'dashicons-admin-appearance', 'dashicons-admin-plugins',
        'dashicons-admin-users', 'dashicons-admin-tools', 'dashicons-admin-settings', 'dashicons-admin-network',
        'dashicons-admin-home', 'dashicons-admin-generic', 'dashicons-admin-collapse', 'dashicons-filter',
        'dashicons-admin-customizer', 'dashicons-admin-multisite', 'dashicons-welcome-write-blog', 'dashicons-welcome-add-page',
        'dashicons-welcome-view-site', 'dashicons-welcome-widgets-menus', 'dashicons-welcome-comments', 'dashicons-welcome-learn-more',
        'dashicons-format-aside', 'dashicons-format-image', 'dashicons-format-gallery', 'dashicons-format-video',
        'dashicons-format-status', 'dashicons-format-quote', 'dashicons-format-chat', 'dashicons-format-audio',
        'dashicons-camera', 'dashicons-images-alt', 'dashicons-images-alt2', 'dashicons-video-alt',
        'dashicons-video-alt2', 'dashicons-video-alt3', 'dashicons-media-archive', 'dashicons-media-audio',
        'dashicons-media-code', 'dashicons-media-default', 'dashicons-media-document', 'dashicons-media-interactive',
        'dashicons-media-spreadsheet', 'dashicons-media-text', 'dashicons-media-video', 'dashicons-playlist-audio',
        'dashicons-playlist-video', 'dashicons-controls-play', 'dashicons-controls-pause', 'dashicons-controls-forward',
        'dashicons-controls-skipforward', 'dashicons-controls-back', 'dashicons-controls-skipback', 'dashicons-controls-repeat',
        'dashicons-controls-volumeon', 'dashicons-controls-volumeoff', 'dashicons-image-crop', 'dashicons-image-rotate',
        'dashicons-image-rotate-left', 'dashicons-image-rotate-right', 'dashicons-image-flip-vertical', 'dashicons-image-flip-horizontal',
        'dashicons-image-filter', 'dashicons-undo', 'dashicons-redo', 'dashicons-editor-bold', 'dashicons-editor-italic',
        'dashicons-editor-ul', 'dashicons-editor-ol', 'dashicons-editor-quote', 'dashicons-editor-alignleft',
        'dashicons-editor-aligncenter', 'dashicons-editor-alignright', 'dashicons-editor-insertmore', 'dashicons-editor-spellcheck',
        'dashicons-editor-distractionfree', 'dashicons-editor-kitchensink', 'dashicons-editor-underline', 'dashicons-editor-justify',
        'dashicons-editor-textcolor', 'dashicons-editor-paste-word', 'dashicons-editor-paste-text', 'dashicons-editor-removeformatting',
        'dashicons-editor-video', 'dashicons-editor-customchar', 'dashicons-editor-outdent', 'dashicons-editor-indent',
        'dashicons-editor-help', 'dashicons-editor-strikethrough', 'dashicons-editor-unlink', 'dashicons-editor-rtl',
        'dashicons-editor-break', 'dashicons-editor-code', 'dashicons-editor-paragraph', 'dashicons-editor-table',
        'dashicons-align-left', 'dashicons-align-right', 'dashicons-align-center', 'dashicons-align-none',
        'dashicons-lock', 'dashicons-unlock', 'dashicons-calendar', 'dashicons-calendar-alt', 'dashicons-visibility',
        'dashicons-hidden', 'dashicons-post-status', 'dashicons-edit', 'dashicons-trash', 'dashicons-sticky',
        'dashicons-external', 'dashicons-arrow-up', 'dashicons-arrow-down', 'dashicons-arrow-right', 'dashicons-arrow-left',
        'dashicons-arrow-up-alt', 'dashicons-arrow-down-alt', 'dashicons-arrow-right-alt', 'dashicons-arrow-left-alt',
        'dashicons-arrow-up-alt2', 'dashicons-arrow-down-alt2', 'dashicons-arrow-right-alt2', 'dashicons-arrow-left-alt2',
        'dashicons-sort', 'dashicons-leftright', 'dashicons-randomize', 'dashicons-list-view', 'dashicons-exerpt-view',
        'dashicons-grid-view', 'dashicons-move', 'dashicons-share', 'dashicons-share-alt', 'dashicons-share-alt2',
        'dashicons-twitter', 'dashicons-rss', 'dashicons-email', 'dashicons-email-alt', 'dashicons-facebook',
        'dashicons-facebook-alt', 'dashicons-googleplus', 'dashicons-networking', 'dashicons-hammer', 'dashicons-art',
        'dashicons-migrate', 'dashicons-performance', 'dashicons-universal-access', 'dashicons-universal-access-alt',
        'dashicons-tickets', 'dashicons-tickets-alt', 'dashicons-nametag', 'dashicons-clipboard', 'dashicons-heart',
        'dashicons-megaphone', 'dashicons-schedule', 'dashicons-wordpress', 'dashicons-wordpress-alt', 'dashicons-pressthis',
        'dashicons-update', 'dashicons-screenoptions', 'dashicons-info', 'dashicons-cart', 'dashicons-feedback',
        'dashicons-cloud', 'dashicons-translation', 'dashicons-tag', 'dashicons-category', 'dashicons-archive',
        'dashicons-tagcloud', 'dashicons-text', 'dashicons-yes', 'dashicons-no', 'dashicons-no-alt',
        'dashicons-plus', 'dashicons-plus-alt', 'dashicons-minus', 'dashicons-dismiss', 'dashicons-marker',
        'dashicons-star-filled', 'dashicons-star-half', 'dashicons-star-empty', 'dashicons-flag', 'dashicons-warning',
        'dashicons-location', 'dashicons-location-alt', 'dashicons-vault', 'dashicons-shield', 'dashicons-shield-alt',
        'dashicons-sos', 'dashicons-search', 'dashicons-slides', 'dashicons-analytics', 'dashicons-chart-pie',
        'dashicons-chart-bar', 'dashicons-chart-line', 'dashicons-chart-area', 'dashicons-groups', 'dashicons-businessman',
        'dashicons-id', 'dashicons-id-alt', 'dashicons-products', 'dashicons-awards', 'dashicons-forms',
        'dashicons-testimonial', 'dashicons-portfolio', 'dashicons-book', 'dashicons-book-alt', 'dashicons-download',
        'dashicons-upload', 'dashicons-backup', 'dashicons-clock', 'dashicons-lightbulb', 'dashicons-microphone',
        'dashicons-desktop', 'dashicons-laptop', 'dashicons-tablet', 'dashicons-smartphone', 'dashicons-phone',
        'dashicons-index-card', 'dashicons-carrot', 'dashicons-building', 'dashicons-store', 'dashicons-album',
        'dashicons-palmtree', 'dashicons-tickets-alt', 'dashicons-money', 'dashicons-smiley', 'dashicons-thumbs-up',
        'dashicons-thumbs-down', 'dashicons-layout', 'dashicons-paperclip', 'dashicons-color-picker', 'dashicons-align-pull-left',
        'dashicons-align-pull-right', 'dashicons-block-default', 'dashicons-button', 'dashicons-cloud-saved',
        'dashicons-cloud-upload', 'dashicons-columns', 'dashicons-cover-image', 'dashicons-embed-audio',
        'dashicons-embed-generic', 'dashicons-embed-photo', 'dashicons-embed-post', 'dashicons-embed-video',
        'dashicons-exit', 'dashicons-heading', 'dashicons-html', 'dashicons-info-outline', 'dashicons-insert',
        'dashicons-insert-after', 'dashicons-insert-before', 'dashicons-remove', 'dashicons-shortcode',
        'dashicons-table-col-after', 'dashicons-table-col-before', 'dashicons-table-col-delete', 'dashicons-table-row-after',
        'dashicons-table-row-before', 'dashicons-table-row-delete', 'dashicons-saved', 'dashicons-ellipsis',
        'dashicons-folder' // Default icon
    ];

    // Cache jQuery selectors
    const $folderList = $('#emf-folder-list');
    const $sortSelect = $('#emf-folder-sort');

    // Helper function for AJAX error handling
    function handleAjaxError(xhr, status, error) {
        console.error('AJAX error:', status, error, xhr.responseText);
        alert('Failed to process request. Please try again or check the console for details.');
    }

    // Detect media items dynamically
    function detectMediaItems(viewType) {
        let $items = $(), selector = '';
        if (viewType === 'grid') {
            $items = $('.attachments .attachment');
            selector = $items.length ? '.attachments .attachment' : ($('#media-items .media-item').length ? '#media-items .media-item' : '');
        } else if (viewType === 'list') {
            $items = $('.wp-list-table tbody tr');
            selector = $items.length ? '.wp-list-table tbody tr' : ($('#the-list tr').length ? '#the-list tr' : '');
        }
        return { $items, selector };
    }

    // Initialize drag-and-drop and sortable
    function initializeDragAndDrop() {
        if (window.emfm.dragAndDropInitialized) return;

        const $folders = $('.emf-folder-item:not(.ui-droppable)');
        $folders.droppable({
            accept: '.attachment, tr, .media-item',
            hoverClass: 'emf-folder-hover',
            drop: function(event, ui) {
                const mediaId = ui.draggable.data('id') || ui.draggable.find('input[type="checkbox"]').val();
                if (!mediaId) return;

                const folderId = $(this).data('folder-id');
                $.post(emfm_data.ajax_url, {
                    action: 'emfm_assign_folder',
                    media_id: mediaId,
                    folder_id: folderId,
                    nonce: emfm_data.nonce
                }).done(response => {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).fail(handleAjaxError);
            }
        });

        const { $items: $gridItems } = detectMediaItems('grid');
        $gridItems.not('.ui-draggable').draggable({
            revert: 'invalid',
            helper: 'clone',
            start: function() { $(this).css('opacity', '0.5'); },
            stop: function() { $(this).css('opacity', '1'); }
        });

        const { $items: $listItems } = detectMediaItems('list');
        $listItems.not('.ui-draggable').draggable({
            revert: 'invalid',
            helper: 'clone',
            start: function() { $(this).css('opacity', '0.5'); },
            stop: function() { $(this).css('opacity', '1'); }
        });

        $folderList.sortable({
            items: '.emf-folder-item:not([data-folder-id="0"])',
            placeholder: 'emf-folder-placeholder',
            update: function() {
                const order = $(this).sortable('toArray', { attribute: 'data-folder-id' });
                $.post(emfm_data.ajax_url, {
                    action: 'emfm_save_folder_order',
                    order: order,
                    nonce: emfm_data.nonce
                }).done(response => {
                    if (response.success) {
                        $sortSelect.val('manual');
                        localStorage.setItem('emfm_folder_sort', 'manual');
                        applySavedSort();
                    } else {
                        alert('Error saving order: ' + (response.data || 'Unknown error'));
                    }
                }).fail(handleAjaxError);
            }
        });

        window.emfm.dragAndDropInitialized = true;
        applySavedSort();
    }

    // Apply saved sort
    function applySavedSort() {
        const savedSort = localStorage.getItem('emfm_folder_sort') || 'manual';
        if ($sortSelect.length) {
            $sortSelect.val(savedSort);
            $.post(emfm_data.ajax_url, {
                action: 'emfm_sort_folders',
                sort_by: savedSort,
                nonce: emfm_data.nonce
            }).done(response => {
                if (response.success) {
                    $folderList.html(response.data.html);
                    if ($folderList.hasClass('ui-sortable')) {
                        if (savedSort === 'manual') {
                            $folderList.sortable('enable');
                        } else {
                            $folderList.sortable('disable');
                        }
                    }
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(handleAjaxError);
        }
    }

    // Initialize with MutationObserver
    function waitForElements() {
        const targetNode = document.getElementById('wpbody');
        if (!targetNode) {
            console.error('wpbody not found');
            return;
        }

        const observer = new MutationObserver((mutations, obs) => {
            const sidebarLoaded = $('#emf-folder-sidebar').length;
            const mediaLoaded = $('.attachments .attachment').length || $('.wp-list-table tbody tr').length;
            if (sidebarLoaded && mediaLoaded) {
                initializeDragAndDrop();
                obs.disconnect();
            }
        });

        observer.observe(targetNode, { childList: true, subtree: true });
    }

    // Handle folder creation
    function handleFolderCreation() {
        $(document).on('click', '#emf-new-folder-btn', function(e) {
            e.preventDefault();
            $('#emf-new-folder-form').slideDown();
        });

        $(document).on('click', '#emf-cancel-folder', function(e) {
            e.preventDefault();
            $('#emf-new-folder-form').slideUp();
            $('#emf-new-folder-name').val('');
        });

        $(document).on('click', '#emf-create-folder', function(e) {
            e.preventDefault();
            const folderName = $('#emf-new-folder-name').val().trim();
            const parentId = $('#emf-parent-folder').val();
            if (!folderName) {
                alert('Please enter a folder name');
                return;
            }
            $.post(emfm_data.ajax_url, {
                action: 'emfm_create_folder',
                folder_name: folderName,
                parent_folder: parentId,
                nonce: emfm_data.nonce
            }).done(response => {
                if (response.success) {
                    const folder = response.data;
                    const $newItem = $(`
                        <li class="emf-folder-item" data-folder-id="${folder.id}" data-folder-slug="${folder.slug}">
                            <span class="dashicons dashicons-folder"></span>
                            <span class="emf-folder-title">${folder.name}</span>
                            <span class="emf-folder-menu-toggle dashicons dashicons-ellipsis" style="float:right; cursor:pointer;" tabindex="0"></span>
                            <div class="emf-folder-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:5px;">
                                <a href="#" class="emf-rename-folder" data-folder-id="${folder.id}">Rename</a><br>
                                <a href="#" class="emf-delete-folder" data-folder-id="${folder.id}">Delete</a><br>
                                <a href="#" class="emf-edit-icon" data-folder-id="${folder.id}">Edit Icon</a>
                            </div>
                        </li>
                    `);
                    $folderList.append($newItem);
                    $newItem.droppable($('.emf-folder-item').droppable('option'));
                    $('#emf-new-folder-form').slideUp();
                    $('#emf-new-folder-name').val('');
                    $('#emf-parent-folder').val('0');
                    emfm_data.folders.push({ term_id: folder.id, name: folder.name, slug: folder.slug, meta: { emf_folder_order: null, emf_folder_icon: 'dashicons-folder' }, parent: parentId });
                    applySavedSort();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(handleAjaxError);
        });
    }

    // Handle folder navigation
    function handleFolderNavigation() {
        $(document).on('click', '.emf-folder-item', function(e) {
            e.preventDefault();
            if (
                $(e.target).hasClass('emf-folder-menu-toggle') ||
                $(e.target).closest('.emf-folder-menu').length ||
                $(e.target).closest('#emf-icon-picker').length
            ) {
                return;
            }

            const folderSlug = $(this).data('folder-slug');
            const newUrl = 'upload.php' + (folderSlug ? '?emfm_media_folder=' + encodeURIComponent(folderSlug) : '');

            $('#emf-folder-list .emf-folder-item').removeClass('emf-folder-active');
            $(this).addClass('emf-folder-active');

            $('#wpbody-content').fadeTo(100, 0.3).load(newUrl + ' #wpbody-content > *', function(response, status) {
                $('#wpbody-content').fadeTo(100, 1);
                if (status === 'error') {
                    alert('Failed to load folder contents.');
                    return;
                }
                history.pushState({}, '', newUrl);
                window.emfm.dragAndDropInitialized = false;
                initializeDragAndDrop();
            });
        });
    }

    // Handle folder sorting
    function handleFolderSorting() {
        $sortSelect.on('change', function() {
            const sortBy = $(this).val();
            localStorage.setItem('emfm_folder_sort', sortBy);
            applySavedSort();
        });
    }

    // Handle folder menu actions
    function handleFolderMenuActions() {
        $(document).on('click', '.emf-folder-menu-toggle', function(e) {
            e.preventDefault();
            const $menu = $(this).siblings('.emf-folder-menu');
            $('.emf-folder-menu').not($menu).hide();
            $menu.toggle();
        }).on('keydown', '.emf-folder-menu-toggle', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        $(document).on('click', '.emf-rename-folder', function(e) {
            e.preventDefault();
            const folderId = $(this).data('folder-id');
            const $folderItem = $(this).closest('.emf-folder-item');
            const currentName = $folderItem.find('.emf-folder-title').text();
            const newName = prompt('Enter new folder name:', currentName);
            if (newName && newName !== currentName) {
                $.post(emfm_data.ajax_url, {
                    action: 'emfm_rename_folder',
                    folder_id: folderId,
                    folder_name: newName,
                    nonce: emfm_data.nonce
                }).done(response => {
                    if (response.success) {
                        $folderItem.find('.emf-folder-title').text(newName);
                        $folderItem.attr('data-folder-slug', response.data.slug);
                        const folder = emfm_data.folders.find(f => f.term_id == folderId);
                        if (folder) {
                            folder.name = newName;
                            folder.slug = response.data.slug;
                        }
                        applySavedSort();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).fail(handleAjaxError);
            }
        });

        $(document).on('click', '.emf-delete-folder', function(e) {
            e.preventDefault();
            const folderId = $(this).data('folder-id');
            if (confirm('Are you sure you want to delete this folder? Media items will be unassigned.')) {
                $.post(emfm_data.ajax_url, {
                    action: 'emfm_delete_folder',
                    folder_id: folderId,
                    nonce: emfm_data.nonce
                }).done(response => {
                    if (response.success) {
                        $(this).closest('.emf-folder-item').remove();
                        emfm_data.folders = emfm_data.folders.filter(f => f.term_id != folderId);
                        applySavedSort();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).fail(handleAjaxError);
            }
        });

        $(document).on('click', '.emf-edit-icon', function(e) {
            e.preventDefault();
            const folderId = $(this).data('folder-id');
            const $folderItem = $(this).closest('.emf-folder-item');
            const currentIcon = emfm_data.folders.find(f => f.term_id == folderId).meta.emf_folder_icon;

            let pickerHtml = `
                <div id="emf-icon-picker" style="position:absolute; background:#fff; border:1px solid #ccc; padding:10px; z-index:1001; width:250px; max-height:300px; overflow-y:auto;">
                    <input type="text" id="emf-icon-search" placeholder="Search icons..." style="width:100%; margin-bottom:10px;" aria-label="Search for an icon">
                    <div id="emf-icon-list" style="display:flex; flex-wrap:wrap;"></div>
                </div>
            `;
            $('#emf-icon-picker').remove();
            $folderItem.append(pickerHtml);

            function renderIcons(filter = '') {
                const $iconList = $('#emf-icon-list');
                $iconList.empty();
                const filteredIcons = iconOptions.filter(icon => icon.toLowerCase().includes(filter.toLowerCase()));
                filteredIcons.forEach((icon, index) => {
                    $iconList.append(`
                        <span class="dashicons ${icon}" style="cursor:pointer; margin:5px; ${icon === currentIcon ? 'border:2px solid #0073aa;' : ''}" data-icon="${icon}" tabindex="0" aria-label="Select icon ${icon}"></span>
                    `);
                });
                // Focus first icon for accessibility
                $iconList.find('.dashicons').first().focus();
            }
            renderIcons();

            let debounceTimeout;
            $('#emf-icon-search').on('input', function(e) {
                e.stopPropagation();
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => renderIcons($(this).val()), 300);
            }).on('click', function(e) {
                e.stopPropagation();
            });

            $('#emf-icon-list').on('click', '.dashicons', function(e) {
                e.stopPropagation();
                selectIcon($(this));
            }).on('keydown', '.dashicons', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectIcon($(this));
                } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    $(this).next('.dashicons').focus();
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    $(this).prev('.dashicons').focus();
                }
            });

            function selectIcon($icon) {
                const newIcon = $icon.data('icon');
                $.post(emfm_data.ajax_url, {
                    action: 'emfm_update_folder_icon',
                    folder_id: folderId,
                    icon: newIcon,
                    nonce: emfm_data.nonce
                }).done(response => {
                    if (response.success) {
                        $folderItem.find('.dashicons').first().removeClass().addClass('dashicons ' + newIcon);
                        const folder = emfm_data.folders.find(f => f.term_id == folderId);
                        if (folder) {
                            folder.meta.emf_folder_icon = newIcon;
                        }
                        $('#emf-icon-picker').remove();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).fail(handleAjaxError);
            }
        });
    }

    // Handle media list table folder dropdown
    function handleMediaListDropdown() {
        $(document).on('change', '.emfm-folder-select', function() {
            const mediaId = $(this).data('media-id');
            const folderId = $(this).val();
            $.post(emfm_data.ajax_url, {
                action: 'emfm_assign_folder',
                media_id: mediaId,
                folder_id: folderId,
                nonce: emfm_data.nonce
            }).done(response => {
                if (!response.success) {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(handleAjaxError);
        });
    }

    // Close menus and pickers when clicking outside
    function handleOutsideClick() {
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.emf-folder-menu-toggle, .emf-folder-menu, #emf-icon-picker').length) {
                $('.emf-folder-menu').hide();
                $('#emf-icon-picker').remove();
            }
        });
    }

    // Initialize plugin
    function init() {
        handleFolderCreation();
        handleFolderNavigation();
        handleFolderSorting();
        handleFolderMenuActions();
        handleMediaListDropdown();
        handleOutsideClick();

        if ($.ui && $.ui.draggable && $.ui.droppable && $.ui.sortable) {
            waitForElements();
        } else {
            console.warn('jQuery UI dependencies missing; drag-and-drop disabled');
        }

        setTimeout(applySavedSort, 500); // Fallback sort
    }

    init();
});
