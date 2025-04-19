jQuery(document).ready(function($) {
    if (!$.ui || !$.ui.draggable || !$.ui.droppable || !$.ui.sortable) {
        console.log('jQuery UI dependencies missing');
        return;
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

    // Sort folders
    function sortFolders(sortBy) {
        const $folderList = $('#emf-folder-list');
        const $folders = $folderList.find('.emf-folder-item').not('[data-folder-id="0"]');
        const foldersArray = $folders.toArray();

        if (sortBy === 'manual') {
            foldersArray.sort((a, b) => {
                const aId = $(a).data('folder-id');
                const bId = $(b).data('folder-id');
                const aFolder = emf_data.folders.find(f => f.term_id == aId);
                const bFolder = emf_data.folders.find(f => f.term_id == bId);
                const aOrder = aFolder && aFolder.meta && aFolder.meta.emf_folder_order !== null ? aFolder.meta.emf_folder_order : Infinity;
                const bOrder = bFolder && bFolder.meta && bFolder.meta.emf_folder_order !== null ? bFolder.meta.emf_folder_order : Infinity;
                return aOrder - bOrder;
            });
        } else {
            foldersArray.sort((a, b) => {
                const aId = $(a).data('folder-id');
                const bId = $(b).data('folder-id');
                const aFolder = emf_data.folders.find(f => f.term_id == aId);
                const bFolder = emf_data.folders.find(f => f.term_id == bId);
                switch (sortBy) {
                    case 'name-asc':
                        return $(a).find('.emf-folder-title').text().localeCompare($(b).find('.emf-folder-title').text());
                    case 'name-desc':
                        return $(b).find('.emf-folder-title').text().localeCompare($(a).find('.emf-folder-title').text());
                    case 'date-asc':
                        return (aFolder ? aFolder.term_id : 0) - (bFolder ? bFolder.term_id : 0);
                    case 'date-desc':
                        return (bFolder ? bFolder.term_id : 0) - (aFolder ? aFolder.term_id : 0);
                    case 'count-asc':
                        return (aFolder ? aFolder.count || 0 : 0) - (bFolder ? bFolder.count || 0 : 0);
                    case 'count-desc':
                        return (bFolder ? bFolder.count || 0 : 0) - (aFolder ? aFolder.count || 0 : 0);
                }
            });
        }

        $folderList.empty();
        $folderList.append('<li class="emf-folder-item" data-folder-id="0"><span class="dashicons dashicons-portfolio"></span><span class="emf-folder-title">All Media</span></li>');
        $.each(foldersArray, function(index, folder) {
            $folderList.append(folder);
        });
    }

    // Initialize drag-and-drop and sortable
    function initializeDragAndDrop() {
        if (window.emfDragAndDropInitialized) return;

        const $folders = $('.emf-folder-item:not(.ui-droppable)');
        $folders.droppable({
            accept: '.attachment, tr, .media-item',
            hoverClass: 'emf-folder-hover',
            drop: function(event, ui) {
                const mediaId = ui.draggable.data('id') || ui.draggable.find('input[type="checkbox"]').val();
                if (!mediaId) return;

                const folderId = $(this).data('folder-id');
                $.post(emf_data.ajax_url, {
                    action: 'emf_assign_folder',
                    media_id: mediaId,
                    folder_id: folderId,
                    nonce: emf_data.nonce
                }).done(response => {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                }).fail(() => alert('AJAX failed'));
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

        const $folderList = $('#emf-folder-list');
        $folderList.sortable({
            items: '.emf-folder-item:not([data-folder-id="0"])',
            placeholder: 'emf-folder-placeholder',
            update: function() {
                const order = $(this).sortable('toArray', { attribute: 'data-folder-id' });
                $.post(emf_data.ajax_url, {
                    action: 'emf_save_folder_order',
                    order: order,
                    nonce: emf_data.nonce
                }).done(response => {
                    if (response.success) {
                        $.each(order, function(index, termId) {
                            const folder = emf_data.folders.find(f => f.term_id == termId);
                            if (folder) {
                                folder.meta = folder.meta || {};
                                folder.meta.emf_folder_order = index;
                            }
                        });
                        $('#emf-folder-sort').val('manual');
                        localStorage.setItem('emf_folder_sort', 'manual');
                        console.log('Manual order saved:', localStorage.getItem('emf_folder_sort'));
                        sortFolders('manual');
                    } else {
                        alert('Error saving order: ' + (response.data || 'Unknown error'));
                    }
                }).fail(() => alert('AJAX failed'));
            }
        });

        window.emfDragAndDropInitialized = true;
        applySavedSort();
    }

    // Apply saved sort
    function applySavedSort() {
        const savedSort = localStorage.getItem('emf_folder_sort') || 'manual';
        const $sortSelect = $('#emf-folder-sort');
        const $folderList = $('#emf-folder-list');
        console.log('Applying sort:', savedSort, 'Dropdown exists:', !!$sortSelect.length, 'Sortable initialized:', $folderList.hasClass('ui-sortable'));
        
        if ($sortSelect.length) {
            $sortSelect.val(savedSort);
            sortFolders(savedSort);
            if ($folderList.hasClass('ui-sortable')) {
                if (savedSort === 'manual') {
                    $folderList.sortable('enable');
                } else {
                    $folderList.sortable('disable');
                }
            }
        } else {
            console.log('Retrying applySavedSort...');
            setTimeout(applySavedSort, 100);
        }
    }

    // Full Dashicons list (as of WordPress 6.7)
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
        'dashicons-folder', // Ensure default is included
    ];

    // Wait for sidebar and media library
    let retryCount = 0;
    const maxRetries = 50;
    function waitForElements() {
        const sidebarLoaded = $('#emf-folder-sidebar').length;
        const mediaLoaded = $('.attachments .attachment').length || $('.wp-list-table tbody tr').length;
        if (sidebarLoaded && mediaLoaded) {
            initializeDragAndDrop();
        } else if (retryCount < maxRetries) {
            retryCount++;
            setTimeout(waitForElements, 100);
        } else {
            console.log('Max retries reached, forcing initialization');
            initializeDragAndDrop();
        }
    }
    waitForElements();

    // Event delegation for sidebar actions
    $(document).on('click', '#emf-folder-sidebar #emf-new-folder-btn', function(e) {
        e.preventDefault();
        $('#emf-new-folder-form').slideDown();
    }).on('click', '#emf-folder-sidebar #emf-cancel-folder', function(e) {
        e.preventDefault();
        $('#emf-new-folder-form').slideUp();
        $('#emf-new-folder-name').val('');
    }).on('click', '#emf-folder-sidebar #emf-create-folder', function(e) {
        e.preventDefault();
        const folderName = $('#emf-new-folder-name').val().trim();
        if (!folderName) {
            alert('Please enter a folder name');
            return;
        }
        $.post(emf_data.ajax_url, {
            action: 'emf_create_folder',
            folder_name: folderName,
            nonce: emf_data.nonce
        }).done(response => {
            if (response.success) {
                const folder = response.data;
                const $newItem = $(`<li class="emf-folder-item" data-folder-id="${folder.id}">
                    <span class="dashicons dashicons-folder"></span>
                    <span class="emf-folder-title">${folder.name}</span>
                    <span class="emf-folder-menu-toggle dashicons dashicons-ellipsis" style="float:right; cursor:pointer;"></span>
                    <div class="emf-folder-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:5px;">
                        <a href="#" class="emf-rename-folder" data-folder-id="${folder.id}">Rename</a><br>
                        <a href="#" class="emf-delete-folder" data-folder-id="${folder.id}">Delete</a><br>
                        <a href="#" class="emf-edit-icon" data-folder-id="${folder.id}">Edit Icon</a>
                    </div>
                </li>`);
                $('#emf-folder-list').append($newItem);
                $newItem.droppable($('.emf-folder-item').droppable('option'));
                $('#emf-new-folder-form').slideUp();
                $('#emf-new-folder-name').val('');
                emf_data.folders.push({ term_id: folder.id, name: folder.name, slug: folder.slug, meta: { emf_folder_order: null, emf_folder_icon: 'dashicons-folder' } });
                const currentSort = $('#emf-folder-sort').val();
                sortFolders(currentSort);
            } else {
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        }).fail(() => alert('AJAX failed'));
    }).on('click', '#emf-folder-sidebar .emf-folder-item', function(e) {
        e.preventDefault();
        if ($(e.target).hasClass('emf-folder-menu-toggle') || $(e.target).closest('.emf-folder-menu').length || $(e.target).closest('#emf-icon-picker').length) {
            return; // Prevent navigation if clicking menu, toggle, or icon picker
        }
        const folderId = $(this).data('folder-id');
        const folder = folderId === 0 ? null : emf_data.folders.find(f => f.term_id == folderId);
        const newUrl = 'upload.php' + (folder ? '?media_folder=' + folder.slug : '');
        window.location.href = newUrl;
    }).on('change', '#emf-folder-sort', function() {
        const sortBy = $(this).val();
        localStorage.setItem('emf_folder_sort', sortBy);
        console.log('Sort changed to:', sortBy, 'Stored:', localStorage.getItem('emf_folder_sort'));
        sortFolders(sortBy);
        const $folderList = $('#emf-folder-list');
        if ($folderList.hasClass('ui-sortable')) {
            if (sortBy === 'manual') {
                $folderList.sortable('enable');
            } else {
                $folderList.sortable('disable');
            }
        }
    }).on('click', '.emf-folder-menu-toggle', function(e) {
        e.preventDefault();
        const $menu = $(this).siblings('.emf-folder-menu');
        $('.emf-folder-menu').not($menu).hide();
        $menu.toggle();
    }).on('click', '.emf-rename-folder', function(e) {
        e.preventDefault();
        const folderId = $(this).data('folder-id');
        const $folderItem = $(this).closest('.emf-folder-item');
        const currentName = $folderItem.find('.emf-folder-title').text();
        const newName = prompt('Enter new folder name:', currentName);
        if (newName && newName !== currentName) {
            $.post(emf_data.ajax_url, {
                action: 'emf_rename_folder',
                folder_id: folderId,
                folder_name: newName,
                nonce: emf_data.nonce
            }).done(response => {
                if (response.success) {
                    $folderItem.find('.emf-folder-title').text(newName);
                    const folder = emf_data.folders.find(f => f.term_id == folderId);
                    if (folder) {
                        folder.name = newName;
                        folder.slug = response.data.slug;
                    }
                    const currentSort = $('#emf-folder-sort').val();
                    sortFolders(currentSort);
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(() => alert('AJAX failed'));
        }
    }).on('click', '.emf-delete-folder', function(e) {
        e.preventDefault();
        const folderId = $(this).data('folder-id');
        if (confirm('Are you sure you want to delete this folder? Media items will be unassigned.')) {
            $.post(emf_data.ajax_url, {
                action: 'emf_delete_folder',
                folder_id: folderId,
                nonce: emf_data.nonce
            }).done(response => {
                if (response.success) {
                    $(this).closest('.emf-folder-item').remove();
                    emf_data.folders = emf_data.folders.filter(f => f.term_id != folderId);
                    const currentSort = $('#emf-folder-sort').val();
                    sortFolders(currentSort);
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(() => alert('AJAX failed'));
        }
    }).on('click', '.emf-edit-icon', function(e) {
        e.preventDefault();
        const folderId = $(this).data('folder-id');
        const $folderItem = $(this).closest('.emf-folder-item');
        const currentIcon = emf_data.folders.find(f => f.term_id == folderId).meta.emf_folder_icon;

        // Build scrollable, searchable icon picker
        let pickerHtml = `
            <div id="emf-icon-picker" style="position:absolute; background:#fff; border:1px solid #ccc; padding:10px; z-index:1001; width:250px; max-height:300px; overflow-y:auto;">
                <input type="text" id="emf-icon-search" placeholder="Search icons..." style="width:100%; margin-bottom:10px;">
                <div id="emf-icon-list" style="display:flex; flex-wrap:wrap;"></div>
            </div>
        `;
        $('#emf-icon-picker').remove();
        $folderItem.append(pickerHtml);

        // Render initial icon list
        function renderIcons(filter = '') {
            const $iconList = $('#emf-icon-list');
            $iconList.empty();
            const filteredIcons = iconOptions.filter(icon => icon.toLowerCase().includes(filter.toLowerCase()));
            filteredIcons.forEach(icon => {
                $iconList.append(`
                    <span class="dashicons ${icon}" style="cursor:pointer; margin:5px; ${icon === currentIcon ? 'border:2px solid #0073aa;' : ''}" data-icon="${icon}"></span>
                `);
            });
        }
        renderIcons();

        // Search functionality
        $('#emf-icon-search').on('input', function(e) {
            e.stopPropagation(); // Prevent bubbling to folder item
            renderIcons($(this).val());
        }).on('click', function(e) {
            e.stopPropagation(); // Prevent click from triggering navigation
        });

        // Handle icon selection
        $('#emf-icon-list').on('click', '.dashicons', function(e) {
            e.stopPropagation(); // Prevent bubbling
            const newIcon = $(this).data('icon');
            $.post(emf_data.ajax_url, {
                action: 'emf_update_folder_icon',
                folder_id: folderId,
                icon: newIcon,
                nonce: emf_data.nonce
            }).done(response => {
                if (response.success) {
                    $folderItem.find('.dashicons').first().removeClass().addClass('dashicons ' + newIcon);
                    const folder = emf_data.folders.find(f => f.term_id == folderId);
                    if (folder) {
                        folder.meta.emf_folder_icon = newIcon;
                    }
                    $('#emf-icon-picker').remove();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            }).fail(() => alert('AJAX failed'));
        });
    });

    // Close menus and pickers when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.emf-folder-menu-toggle, .emf-folder-menu, #emf-icon-picker').length) {
            $('.emf-folder-menu').hide();
            $('#emf-icon-picker').remove();
        }
    });

    // Fallback: Apply sort on DOM ready
    $(document).ready(function() {
        setTimeout(applySavedSort, 500);
    });
});