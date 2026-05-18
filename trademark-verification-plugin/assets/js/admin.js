/**
 * TMV Admin JavaScript
 * Handles media uploads, quick actions, and admin interactions
 */
(function($) {
    'use strict';

    // Media Upload Handler
    $(document).on('click', '.tmv-upload-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var targetId = $btn.data('target');
        var fileType = $btn.data('type') || '';
        
        var mediaUploader = wp.media({
            title: 'Select File',
            button: { text: 'Use This File' },
            library: { type: fileType },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + targetId).val(attachment.id);
            
            // Show preview
            var $section = $btn.closest('.tmv-upload-section');
            $section.find('img').remove();
            $section.find('.tmv-file-info').remove();
            
            if (attachment.type === 'image') {
                $btn.before('<img src="' + attachment.url + '" style="max-width:200px;height:auto;display:block;margin:8px 0;" />');
            } else {
                $btn.before('<p class="tmv-file-info">File: ' + attachment.filename + '</p>');
            }
        });
        
        mediaUploader.open();
    });

    // Remove File
    $(document).on('click', '.tmv-remove-btn', function(e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        $('#' + targetId).val('');
        $(this).closest('.tmv-upload-section').find('img, .tmv-file-info').remove();
    });

    // Quick Approve
    $(document).on('click', '.tmv-quick-approve', function() {
        var $btn = $(this);
        var postId = $btn.data('id');
        
        if (!confirm('Approve this application?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_approve_application',
                nonce: tmvAdmin.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').find('.tmv-status-badge')
                        .removeClass('tmv-status-pending')
                        .addClass('tmv-status-approved')
                        .text('Approved');
                    $btn.remove();
                    alert('Application approved!');
                }
            }
        });
    });

    // Quick Reject
    $(document).on('click', '.tmv-quick-reject', function() {
        var $btn = $(this);
        var postId = $btn.data('id');
        
        if (!confirm('Reject this application?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_reject_application',
                nonce: tmvAdmin.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').find('.tmv-status-badge')
                        .removeClass('tmv-status-pending')
                        .addClass('tmv-status-rejected')
                        .text('Rejected');
                    $btn.remove();
                    alert('Application rejected.');
                }
            }
        });
    });

})(jQuery);
