jQuery(document).ready(function($) {
    $('.emfm-folder-select').on('change', function() {
        var $select = $(this);
        var mediaId = parseInt($select.data('media-id'), 10);
        var folderId = parseInt($select.val(), 10);

        if (!mediaId || isNaN(folderId)) {
            alert('Invalid media or folder selection.');
            return;
        }

        $.ajax({
            url: emfmAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'emfm_move_media',
                media_id: mediaId,
                folder_id: folderId,
                nonce: emfmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Media moved successfully.');
                } else {
                    alert(response.data || 'Failed to move media.');
                    $select.val($select.data('original-value'));
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $select.val($select.data('original-value'));
            }
        });

        // Store original value for rollback
        $select.data('original-value', $select.val());
    });
});