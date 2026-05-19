/**
 * TMV Admin JavaScript
 * Handles media uploads, quick actions, dashboard tabs, and admin interactions
 */
(function($) {
    'use strict';

    // =========================================================================
    // TAB SWITCHING
    // =========================================================================

    $(document).on('click', '.tmv-tab-btn', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        
        $('.tmv-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.tmv-tab-content').removeClass('active');
        $('#tmv-tab-' + tab).addClass('active');
    });

    // =========================================================================
    // MEDIA UPLOAD HANDLER
    // =========================================================================

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

    // =========================================================================
    // QUICK APPROVE / REJECT
    // =========================================================================

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
                    $btn.closest('tr').find('.tmv-quick-approve, .tmv-quick-reject').remove();
                    alert('Application approved!');
                }
            }
        });
    });

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
                    $btn.closest('tr').find('.tmv-quick-approve, .tmv-quick-reject').remove();
                    alert('Application rejected.');
                }
            }
        });
    });

    // =========================================================================
    // REGENERATE CERTIFICATE
    // =========================================================================

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

    // =========================================================================
    // SELECT ALL CHECKBOX
    // =========================================================================

    $(document).on('change', '#tmv-select-all', function() {
        var checked = $(this).is(':checked');
        $('.tmv-app-checkbox').prop('checked', checked);
    });

    // =========================================================================
    // SEARCH & FILTER (APPLICATIONS)
    // =========================================================================

    $(document).on('input', '#tmv-app-search', function() {
        var search = $(this).val().toLowerCase();
        var statusFilter = $('#tmv-app-status-filter').val();
        filterApplicationsTable(search, statusFilter);
    });

    $(document).on('change', '#tmv-app-status-filter', function() {
        var search = $('#tmv-app-search').val().toLowerCase();
        var statusFilter = $(this).val();
        filterApplicationsTable(search, statusFilter);
    });

    function filterApplicationsTable(search, statusFilter) {
        $('#tmv-applications-table tbody tr').each(function() {
            var $row = $(this);
            var tm = $row.data('tm') || '';
            var owner = $row.data('owner') || '';
            var status = $row.data('status') || '';
            
            var matchSearch = !search || tm.indexOf(search) !== -1 || owner.indexOf(search) !== -1;
            var matchStatus = statusFilter === 'all' || status === statusFilter;
            
            $row.toggle(matchSearch && matchStatus);
        });
    }

    // User search
    $(document).on('input', '#tmv-user-search', function() {
        var search = $(this).val().toLowerCase();
        $('#tmv-users-table tbody tr').each(function() {
            var username = $(this).data('username') || '';
            $(this).toggle(!search || username.indexOf(search) !== -1);
        });
    });

    // =========================================================================
    // BULK ACTIONS
    // =========================================================================

    $(document).on('click', '#tmv-bulk-apply-btn', function() {
        var action = $('#tmv-bulk-action-select').val();
        if (!action) {
            alert('Please select a bulk action.');
            return;
        }
        
        var ids = [];
        $('.tmv-app-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        
        if (ids.length === 0) {
            alert('Please select at least one application.');
            return;
        }
        
        if (action === 'delete' && !confirm('Are you sure you want to delete ' + ids.length + ' applications? This cannot be undone.')) {
            return;
        }
        
        if (!confirm('Apply "' + action + '" to ' + ids.length + ' selected applications?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_bulk_action',
                nonce: tmvAdmin.nonce,
                bulk_action: action,
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Action failed');
                }
            }
        });
    });

    // =========================================================================
    // PRIORITY STAR TOGGLE
    // =========================================================================

    $(document).on('click', '.tmv-star', function() {
        var $star = $(this);
        var postId = $star.data('id');
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_flag_application',
                nonce: tmvAdmin.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $star.toggleClass('active');
                }
            }
        });
    });

    // =========================================================================
    // NOTES MODAL
    // =========================================================================

    var currentNotesPostId = 0;

    $(document).on('click', '.tmv-notes-btn', function() {
        currentNotesPostId = $(this).data('id');
        $('#tmv-notes-modal').show();
        $('#tmv-notes-textarea').val('Loading...');
        
        // Load existing notes via a simple approach
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_notes',
                nonce: tmvAdmin.nonce,
                post_id: currentNotesPostId,
                notes: '__GET__'
            },
            success: function() {
                // Just clear for new entry
                $('#tmv-notes-textarea').val('');
            }
        });
        $('#tmv-notes-textarea').val('');
    });

    $(document).on('click', '#tmv-save-notes-btn', function() {
        var notes = $('#tmv-notes-textarea').val();
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_notes',
                nonce: tmvAdmin.nonce,
                post_id: currentNotesPostId,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    alert('Notes saved!');
                    $('#tmv-notes-modal').hide();
                }
            }
        });
    });

    $(document).on('click', '#tmv-close-notes-btn', function() {
        $('#tmv-notes-modal').hide();
    });

    // =========================================================================
    // CSV EXPORT
    // =========================================================================

    $(document).on('click', '.tmv-export-csv-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_export_csv',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.data) {
                    var csv = '';
                    response.data.data.forEach(function(row) {
                        csv += row.map(function(cell) {
                            return '"' + (cell || '').replace(/"/g, '""') + '"';
                        }).join(',') + '\n';
                    });
                    
                    var blob = new Blob([csv], { type: 'text/csv' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-applications-' + new Date().toISOString().slice(0, 10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            }
        });
    });

    // =========================================================================
    // CERTIFICATE ACTIONS
    // =========================================================================

    $(document).on('click', '.tmv-revoke-cert-btn', function() {
        var $btn = $(this);
        var postId = $btn.data('id');
        
        if (!confirm('Are you sure you want to revoke this certificate? This action cannot be easily undone.')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_revoke_certificate',
                nonce: tmvAdmin.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').find('.tmv-status-badge').text('Revoked')
                        .removeClass('tmv-status-approved').addClass('tmv-status-rejected');
                    $btn.remove();
                    alert('Certificate revoked.');
                }
            }
        });
    });

    $(document).on('click', '#tmv-batch-generate-btn', function() {
        if (!confirm('Generate certificates for all approved applications without one?')) return;
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_batch_generate_certs',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Batch generation failed');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Batch Generate Certificates');
            }
        });
    });

    // =========================================================================
    // USER BAN/UNBAN
    // =========================================================================

    $(document).on('click', '.tmv-ban-user-btn', function() {
        var $btn = $(this);
        var userId = $btn.data('id');
        var banAction = $btn.data('action');
        
        if (banAction === 'ban' && !confirm('Are you sure you want to ban this user?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_ban_user',
                nonce: tmvAdmin.nonce,
                user_id: userId,
                ban_action: banAction
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                }
            }
        });
    });

    // =========================================================================
    // EMAIL TEMPLATES
    // =========================================================================

    $(document).on('click', '#tmv-save-templates-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_email_templates',
                nonce: tmvAdmin.nonce,
                approved_tpl: $('#tmv-tpl-approved').val(),
                rejected_tpl: $('#tmv-tpl-rejected').val(),
                submitted_tpl: $('#tmv-tpl-submitted').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Email templates saved!');
                }
            }
        });
    });

    // =========================================================================
    // ANNOUNCEMENTS
    // =========================================================================

    $(document).on('click', '#tmv-add-announcement-btn', function() {
        var title = $('#tmv-announcement-title').val();
        var message = $('#tmv-announcement-message').val();
        
        if (!title) {
            alert('Please enter an announcement title.');
            return;
        }
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_add_announcement',
                nonce: tmvAdmin.nonce,
                title: title,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    alert('Announcement added!');
                    location.reload();
                }
            }
        });
    });

    $(document).on('click', '.tmv-delete-announcement-btn', function() {
        var index = $(this).data('index');
        if (!confirm('Delete this announcement?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_delete_announcement',
                nonce: tmvAdmin.nonce,
                index: index
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // =========================================================================
    // IP MANAGEMENT
    // =========================================================================

    $(document).on('click', '#tmv-add-blacklist-btn', function() {
        var ip = $('#tmv-blacklist-ip-input').val();
        if (!ip) return;
        manageIpList('blacklist', ip, 'add');
    });

    $(document).on('click', '#tmv-add-whitelist-btn', function() {
        var ip = $('#tmv-whitelist-ip-input').val();
        if (!ip) return;
        manageIpList('whitelist', ip, 'add');
    });

    $(document).on('click', '.tmv-remove-ip-btn', function() {
        var list = $(this).data('list');
        var ip = $(this).data('ip');
        manageIpList(list, ip, 'remove');
    });

    function manageIpList(listType, ip, ipAction) {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_manage_ip_list',
                nonce: tmvAdmin.nonce,
                list_type: listType,
                ip: ip,
                ip_action: ipAction
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    }

    // Clear failed logins
    $(document).on('click', '#tmv-clear-failed-logins-btn', function() {
        if (!confirm('Clear all failed login records?')) return;
        // Uses the error log clear mechanism but for failed logins option
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_clear_error_log',
                nonce: tmvAdmin.nonce
            },
            success: function() {
                location.reload();
            }
        });
    });

    // =========================================================================
    // FAQ MANAGEMENT
    // =========================================================================

    $(document).on('click', '#tmv-add-faq-btn', function() {
        var question = $('#tmv-faq-question').val();
        var answer = $('#tmv-faq-answer').val();
        
        if (!question) {
            alert('Please enter a question.');
            return;
        }
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_faq',
                nonce: tmvAdmin.nonce,
                question: question,
                answer: answer
            },
            success: function(response) {
                if (response.success) {
                    alert('FAQ added!');
                    location.reload();
                }
            }
        });
    });

    $(document).on('click', '.tmv-delete-faq-btn', function() {
        var index = $(this).data('index');
        if (!confirm('Delete this FAQ?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_delete_faq',
                nonce: tmvAdmin.nonce,
                index: index
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // =========================================================================
    // CONTACT & SOCIAL
    // =========================================================================

    $(document).on('click', '#tmv-save-contact-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_contact_info',
                nonce: tmvAdmin.nonce,
                phone: $('#tmv-contact-phone').val(),
                email: $('#tmv-contact-email').val(),
                address: $('#tmv-contact-address').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Contact information saved!');
                }
            }
        });
    });

    $(document).on('click', '#tmv-save-social-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_social_links',
                nonce: tmvAdmin.nonce,
                facebook: $('#tmv-social-facebook').val(),
                twitter: $('#tmv-social-twitter').val(),
                linkedin: $('#tmv-social-linkedin').val(),
                youtube: $('#tmv-social-youtube').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Social links saved!');
                }
            }
        });
    });

    // =========================================================================
    // MAINTENANCE
    // =========================================================================

    $(document).on('click', '#tmv-cleanup-db-btn', function() {
        if (!confirm('Run database cleanup? This will remove orphaned meta and old revisions.')) return;
        
        var $btn = $(this);
        var $status = $('#tmv-cleanup-status');
        $btn.prop('disabled', true);
        $status.text('Running cleanup...');
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_cleanup_database',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.text(response.data.message).css('color', 'green');
                } else {
                    $status.text('Cleanup failed').css('color', 'red');
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

    $(document).on('click', '#tmv-clear-error-log-btn', function() {
        if (!confirm('Clear the error log?')) return;
        
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_clear_error_log',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Error log cleared.');
                    location.reload();
                }
            }
        });
    });

})(jQuery);
