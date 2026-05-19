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

    // Regenerate Certificate
    $(document).on('click', '.tmv-regenerate-cert', function() {
        var $btn = $(this);
        var postId = $btn.data('post-id');
        var $status = $btn.siblings('.tmv-regenerate-status');

        if (!confirm('Regenerate the certificate image?')) return;

        $btn.prop('disabled', true);
        $status.text('Generating...');

        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_regenerate_certificate',
                nonce: tmvAdmin.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $status.text(response.data.message).css('color', 'green');
                    // Update the certificate JPG preview if visible
                    if (response.data.certificate_url) {
                        var $section = $('#tmv_certificate_jpg').closest('.tmv-upload-section');
                        $section.find('img').remove();
                        $('#tmv_certificate_jpg').val(response.data.certificate_id);
                        $section.find('.tmv-upload-btn').before(
                            '<img src="' + response.data.certificate_url + '" style="max-width:200px;height:auto;display:block;margin:8px 0;" />'
                        );
                    }
                } else {
                    $status.text(response.data.message || 'Generation failed').css('color', 'red');
                }
            },
            error: function() {
                $status.text('Request failed').css('color', 'red');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

})(jQuery);
