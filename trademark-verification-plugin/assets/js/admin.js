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

    // =========================================================================
    // DOCUMENTS TAB HANDLERS
    // =========================================================================

    // Toggle upload form
    $(document).on('click', '#tmv-upload-doc-template-btn', function() {
        $('#tmv-upload-doc-form').slideToggle();
    });

    // Upload/Save Document
    $(document).on('click', '#tmv-save-document-btn', function() {
        var name = $('#tmv-doc-name').val();
        var category = $('#tmv-doc-category').val();
        if (!name) {
            alert('Please enter a document name.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_document',
                nonce: tmvAdmin.nonce,
                doc_name: name,
                doc_category: category
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Document saved!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to save document.');
                }
            }
        });
    });

    // Delete Document
    $(document).on('click', '.tmv-delete-doc-btn', function() {
        var docId = $(this).data('id');
        if (!confirm('Delete this document?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_delete_document',
                nonce: tmvAdmin.nonce,
                doc_id: docId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Delete failed.');
                }
            }
        });
    });


    // Add Document Category
    $(document).on('click', '#tmv-add-doc-category-btn', function() {
        var catName = $('#tmv-new-doc-category').val();
        if (!catName) {
            alert('Please enter a category name.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_document',
                nonce: tmvAdmin.nonce,
                doc_category_add: catName
            },
            success: function(response) {
                if (response.success) {
                    alert('Category added!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to add category.');
                }
            }
        });
    });

    // Remove Document Category
    $(document).on('click', '.tmv-remove-doc-cat', function() {
        var cat = $(this).data('cat');
        if (!confirm('Remove category "' + cat + '"?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_document',
                nonce: tmvAdmin.nonce,
                doc_category_remove: cat
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Toggle Auto-Archive
    $(document).on('change', '#tmv-auto-archive-toggle', function() {
        var enabled = $(this).is(':checked') ? '1' : '0';
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_document',
                nonce: tmvAdmin.nonce,
                auto_archive: enabled
            },
            success: function(response) {
                if (response.success) {
                    alert('Auto-archive setting updated!');
                }
            }
        });
    });

    // Toggle Document Sharing
    $(document).on('click', '.tmv-toggle-sharing-btn', function() {
        var $btn = $(this);
        var docType = $btn.data('type');
        var current = $btn.data('current');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_document',
                nonce: tmvAdmin.nonce,
                toggle_sharing: docType,
                current_status: current
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Toggle failed.');
                }
            }
        });
    });


    // =========================================================================
    // NOTIFICATIONS TAB HANDLERS
    // =========================================================================

    // Mark notification read
    $(document).on('click', '.tmv-mark-read-btn', function() {
        var notifId = $(this).data('id');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_mark_notification_read',
                nonce: tmvAdmin.nonce,
                notification_id: notifId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Save All Notification Settings
    $(document).on('click', '#tmv-save-notification-settings-btn', function() {
        var pushEnabled = $('#tmv-push-enabled').is(':checked') ? '1' : '0';
        var vapidPublic = $('#tmv-vapid-public').val();
        var vapidPrivate = $('#tmv-vapid-private').val();
        var digestFreq = $('input[name="tmv_digest_freq"]:checked').val() || 'weekly';
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_notification_settings',
                nonce: tmvAdmin.nonce,
                push_enabled: pushEnabled,
                vapid_public: vapidPublic,
                vapid_private: vapidPrivate,
                digest_frequency: digestFreq
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Notification settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save SMS Config
    $(document).on('click', '#tmv-save-sms-config-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_notification_settings',
                nonce: tmvAdmin.nonce,
                sms_provider: $('#tmv-sms-provider').val(),
                sms_api_key: $('#tmv-sms-api-key').val(),
                sms_sender_id: $('#tmv-sms-sender-id').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('SMS configuration saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });


    // Add Webhook
    $(document).on('click', '#tmv-save-webhook-btn', function() {
        var webhookUrl = $('#tmv-webhook-url').val();
        if (!webhookUrl) {
            alert('Please enter a webhook URL.');
            return;
        }
        var events = [];
        $('.tmv-webhook-event:checked').each(function() {
            events.push($(this).val());
        });
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_webhook',
                nonce: tmvAdmin.nonce,
                webhook_url: webhookUrl,
                webhook_events: events
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Webhook saved!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Delete Webhook
    $(document).on('click', '.tmv-delete-webhook-btn', function() {
        var webhookId = $(this).data('id');
        if (!confirm('Delete this webhook?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_delete_webhook',
                nonce: tmvAdmin.nonce,
                webhook_id: webhookId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // =========================================================================
    // APPLICATIONS EXPANDED HANDLERS
    // =========================================================================

    // Apply Advanced Filters (date range + class)
    $(document).on('click', '#tmv-apply-advanced-filters', function() {
        var dateFrom = $('#tmv-filter-date-from').val();
        var dateTo = $('#tmv-filter-date-to').val();
        var classFilter = $('#tmv-filter-class').val();
        // Client-side filtering for visible table rows
        $('#tmv-applications-table tbody tr').each(function() {
            var $row = $(this);
            var rowDate = $row.data('date') || '';
            var rowClass = $row.data('class') || '';
            var show = true;
            if (dateFrom && rowDate < dateFrom) show = false;
            if (dateTo && rowDate > dateTo) show = false;
            if (classFilter && classFilter !== 'all' && rowClass !== classFilter) show = false;
            $row.toggle(show);
        });
    });


    // Toggle Timeline View
    $(document).on('click', '#tmv-timeline-view-toggle', function() {
        $('#tmv-duplicates-table').toggle();
        var $timeline = $('#tmv-timeline-view');
        if ($timeline.length) {
            $timeline.toggle();
        }
    });

    // Add Tag
    $(document).on('click', '#tmv-add-tag-btn', function() {
        var tagName = $('#tmv-new-tag-input').val();
        if (!tagName) {
            alert('Please enter a tag name.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_tags',
                nonce: tmvAdmin.nonce,
                tag_name: tagName,
                tag_action: 'add'
            },
            success: function(response) {
                if (response.success) {
                    $('#tmv-tags-list').append('<span class="tmv-tag-badge">' + tagName + ' <button class="tmv-remove-tag" data-tag="' + tagName + '">&times;</button></span>');
                    $('#tmv-new-tag-input').val('');
                } else {
                    alert(response.data.message || 'Failed to add tag.');
                }
            }
        });
    });

    // Remove Tag
    $(document).on('click', '.tmv-remove-tag', function() {
        var $btn = $(this);
        var tagName = $btn.data('tag');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_tags',
                nonce: tmvAdmin.nonce,
                tag_name: tagName,
                tag_action: 'remove'
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('.tmv-tag-badge').remove();
                }
            }
        });
    });

    // Add Comment
    $(document).on('click', '#tmv-add-comment-btn', function() {
        var appId = $('#tmv-comment-app-id').val();
        var commentText = $('#tmv-comment-text').val();
        if (!appId || !commentText) {
            alert('Please enter a Post ID and comment text.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_add_comment',
                nonce: tmvAdmin.nonce,
                post_id: appId,
                comment: commentText
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Comment added!');
                    $('#tmv-comment-text').val('');
                } else {
                    alert(response.data.message || 'Failed to add comment.');
                }
            }
        });
    });


    // Email Applicant
    $(document).on('click', '#tmv-email-applicant-btn', function() {
        var appId = prompt('Enter the Application Post ID to email:');
        if (!appId) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_send_applicant_email',
                nonce: tmvAdmin.nonce,
                post_id: appId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Email sent to applicant!');
                } else {
                    alert(response.data.message || 'Email failed.');
                }
            }
        });
    });

    // Schedule Reminder
    $(document).on('click', '#tmv-schedule-reminder-btn', function() {
        var appId = $('#tmv-reminder-app-id').val();
        var reminderDate = $('#tmv-reminder-date').val();
        var reminderMsg = $('#tmv-reminder-message').val();
        if (!appId || !reminderDate) {
            alert('Please enter a Post ID and date.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_schedule_reminder',
                nonce: tmvAdmin.nonce,
                post_id: appId,
                reminder_date: reminderDate,
                reminder_message: reminderMsg
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Reminder scheduled!');
                    $('#tmv-reminder-app-id').val('');
                    $('#tmv-reminder-date').val('');
                    $('#tmv-reminder-message').val('');
                } else {
                    alert(response.data.message || 'Scheduling failed.');
                }
            }
        });
    });

    // Print Application
    $(document).on('click', '#tmv-print-app-btn', function() {
        window.print();
    });

    // =========================================================================
    // CERTIFICATES EXPANDED HANDLERS
    // =========================================================================

    // Save Watermark Settings
    $(document).on('click', '#tmv-save-watermark-btn', function() {
        var text = $('#tmv-cert-watermark-text').val();
        var opacity = $('#tmv-cert-watermark-opacity').val();
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_cert_watermark',
                nonce: tmvAdmin.nonce,
                watermark_text: text,
                watermark_opacity: opacity
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Watermark settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });


    // Watermark opacity live preview
    $(document).on('input', '#tmv-cert-watermark-opacity', function() {
        $('#tmv-opacity-value').text($(this).val() + '%');
    });

    // Save Numbering Format
    $(document).on('click', '#tmv-save-numbering-btn', function() {
        var format = $('#tmv-cert-numbering-format').val();
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_cert_numbering',
                nonce: tmvAdmin.nonce,
                numbering_format: format
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Numbering format saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Preview Certificate
    $(document).on('click', '#tmv-preview-cert-btn', function() {
        alert('Certificate preview functionality - opens in a new window when templates are configured.');
    });

    // Toggle Language
    $(document).on('change', '.tmv-language-toggle', function() {
        var lang = $(this).val();
        var enabled = $(this).is(':checked') ? '1' : '0';
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_cert_watermark',
                nonce: tmvAdmin.nonce,
                language: lang,
                language_enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    // Silently saved
                }
            }
        });
    });

    // =========================================================================
    // USERS EXPANDED HANDLERS
    // =========================================================================

    // Import Users - show file form
    $(document).on('click', '#tmv-import-users-btn', function() {
        $('#tmv-import-users-form').slideToggle();
    });

    // Process CSV Import
    $(document).on('click', '#tmv-process-import-btn', function() {
        var fileInput = document.getElementById('tmv-users-csv-file');
        if (!fileInput || !fileInput.files[0]) {
            alert('Please select a CSV file.');
            return;
        }
        var formData = new FormData();
        formData.append('action', 'tmv_import_users');
        formData.append('nonce', tmvAdmin.nonce);
        formData.append('csv_file', fileInput.files[0]);
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Users imported!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Import failed.');
                }
            }
        });
    });


    // Export Users
    $(document).on('click', '#tmv-export-users-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_export_users',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.csv) {
                    var blob = new Blob([response.data.csv], { type: 'text/csv' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-users-' + new Date().toISOString().slice(0, 10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert(response.data.message || 'Export failed.');
                }
            }
        });
    });

    // Invite User
    $(document).on('click', '#tmv-invite-user-btn', function() {
        var email = $('#tmv-invite-email').val();
        var role = $('#tmv-invite-role').val();
        if (!email) {
            alert('Please enter an email address.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_invite_user',
                nonce: tmvAdmin.nonce,
                email: email,
                role: role
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Invitation sent!');
                    $('#tmv-invite-email').val('');
                } else {
                    alert(response.data.message || 'Invitation failed.');
                }
            }
        });
    });

    // Send Bulk Email
    $(document).on('click', '#tmv-send-bulk-email-btn', function() {
        var group = $('#tmv-bulk-email-group').val();
        var subject = $('#tmv-bulk-email-subject').val();
        var message = $('#tmv-bulk-email-message').val();
        if (!subject || !message) {
            alert('Please enter a subject and message.');
            return;
        }
        if (!confirm('Send email to all users in the selected group?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_bulk_email_users',
                nonce: tmvAdmin.nonce,
                group: group,
                subject: subject,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Bulk email sent!');
                    $('#tmv-bulk-email-subject').val('');
                    $('#tmv-bulk-email-message').val('');
                } else {
                    alert(response.data.message || 'Email sending failed.');
                }
            }
        });
    });

    // Create User Group
    $(document).on('click', '#tmv-create-group-btn', function() {
        var groupName = $('#tmv-new-group-name').val();
        if (!groupName) {
            alert('Please enter a group name.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_invite_user',
                nonce: tmvAdmin.nonce,
                create_group: groupName
            },
            success: function(response) {
                if (response.success) {
                    alert('Group created!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to create group.');
                }
            }
        });
    });


    // =========================================================================
    // ANALYTICS EXPANDED HANDLERS
    // =========================================================================

    // Generate Custom Report
    $(document).on('click', '#tmv-generate-report-btn', function() {
        var dateFrom = $('#tmv-report-date-from').val();
        var dateTo = $('#tmv-report-date-to').val();
        var metrics = [];
        $('input[name="tmv_report_metrics[]"]:checked').each(function() {
            metrics.push($(this).val());
        });
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_generate_report',
                nonce: tmvAdmin.nonce,
                date_from: dateFrom,
                date_to: dateTo,
                metrics: metrics
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Report generated!');
                } else {
                    alert(response.data.message || 'Report generation failed.');
                }
            }
        });
    });

    // Schedule Report
    $(document).on('click', '#tmv-schedule-report-btn', function() {
        var frequency = $('#tmv-report-frequency').val();
        var email = $('#tmv-report-email').val();
        if (!email) {
            alert('Please enter a recipient email.');
            return;
        }
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_schedule_report',
                nonce: tmvAdmin.nonce,
                frequency: frequency,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Report scheduled!');
                } else {
                    alert(response.data.message || 'Scheduling failed.');
                }
            }
        });
    });

    // Export as PDF
    $(document).on('click', '#tmv-export-pdf-btn', function() {
        alert('PDF export initiated. The file will download shortly.');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_generate_report',
                nonce: tmvAdmin.nonce,
                format: 'pdf'
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    window.open(response.data.url, '_blank');
                } else {
                    alert(response.data.message || 'PDF export not available.');
                }
            }
        });
    });

    // =========================================================================
    // SECURITY EXPANDED HANDLERS
    // =========================================================================

    // Save Password Policy
    $(document).on('click', '#tmv-save-password-policy-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_password_policy',
                nonce: tmvAdmin.nonce,
                min_length: $('#tmv-pw-min-length').val(),
                require_uppercase: $('#tmv-pw-require-uppercase').is(':checked') ? '1' : '0',
                require_numbers: $('#tmv-pw-require-numbers').is(':checked') ? '1' : '0',
                require_special: $('#tmv-pw-require-special').is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Password policy saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });


    // Save Session Settings
    $(document).on('click', '#tmv-save-session-settings-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_session_settings',
                nonce: tmvAdmin.nonce,
                session_timeout: $('#tmv-session-timeout').val(),
                lockout_attempts: $('#tmv-lockout-attempts').val(),
                lockout_duration: $('#tmv-lockout-duration').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Session settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save CAPTCHA Settings
    $(document).on('click', '#tmv-save-captcha-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_session_settings',
                nonce: tmvAdmin.nonce,
                captcha_enabled: $('#tmv-captcha-enabled').is(':checked') ? '1' : '0',
                captcha_site_key: $('#tmv-captcha-site-key').val(),
                captcha_secret_key: $('#tmv-captcha-secret-key').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('CAPTCHA settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save CORS Settings
    $(document).on('click', '#tmv-save-cors-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_session_settings',
                nonce: tmvAdmin.nonce,
                cors_origins: $('#tmv-cors-origins').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('CORS configuration saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save Rate Limit
    $(document).on('click', '#tmv-save-rate-limit-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_session_settings',
                nonce: tmvAdmin.nonce,
                rate_limit: $('#tmv-rate-limit').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Rate limiting saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save Upload Security
    $(document).on('click', '#tmv-save-upload-security-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_session_settings',
                nonce: tmvAdmin.nonce,
                max_file_size: $('#tmv-max-file-size').val(),
                allowed_extensions: $('#tmv-allowed-extensions').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('File upload security settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });


    // =========================================================================
    // MAINTENANCE EXPANDED HANDLERS
    // =========================================================================

    // Toggle Maintenance Mode / Save Maintenance Settings
    $(document).on('click', '#tmv-save-maintenance-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_toggle_maintenance',
                nonce: tmvAdmin.nonce,
                maintenance_mode: $('#tmv-maintenance-mode-toggle').is(':checked') ? '1' : '0',
                maintenance_start: $('#tmv-maintenance-start').val(),
                maintenance_end: $('#tmv-maintenance-end').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Maintenance settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Save Backup Settings
    $(document).on('click', '#tmv-save-backup-settings-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_backup_settings',
                nonce: tmvAdmin.nonce,
                backup_frequency: $('#tmv-backup-frequency').val(),
                backup_time: $('#tmv-backup-time').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Backup settings saved!');
                } else {
                    alert(response.data.message || 'Save failed.');
                }
            }
        });
    });

    // Restore from Backup
    $(document).on('click', '.tmv-restore-backup-btn', function() {
        var backupId = $(this).data('id');
        if (!confirm('Are you sure you want to restore from this backup? Current settings will be overwritten.')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_backup_settings',
                nonce: tmvAdmin.nonce,
                restore_backup: backupId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Backup restored!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Restore failed.');
                }
            }
        });
    });

    // Restore from select dropdown
    $(document).on('click', '#tmv-restore-btn', function() {
        var backupId = $('#tmv-restore-backup-select').val();
        if (!backupId) {
            alert('Please select a backup to restore.');
            return;
        }
        if (!confirm('Restore from selected backup?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_backup_settings',
                nonce: tmvAdmin.nonce,
                restore_backup: backupId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Backup restored!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Restore failed.');
                }
            }
        });
    });


    // Run Security Scan
    $(document).on('click', '#tmv-run-security-scan-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Scanning...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_toggle_maintenance',
                nonce: tmvAdmin.nonce,
                security_scan: '1'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Security scan complete!');
                } else {
                    alert(response.data.message || 'Scan failed.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Run Security Scan');
            }
        });
    });

    // Export All Settings
    $(document).on('click', '#tmv-export-all-settings-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_export_all_settings',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.settings) {
                    var json = JSON.stringify(response.data.settings, null, 2);
                    var blob = new Blob([json], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-settings-' + new Date().toISOString().slice(0, 10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert(response.data.message || 'Export failed.');
                }
            }
        });
    });

    // Import Settings - show form
    $(document).on('click', '#tmv-import-settings-btn', function() {
        $('#tmv-import-settings-form').slideToggle();
    });

    // Process Import Settings
    $(document).on('click', '#tmv-process-import-settings-btn', function() {
        var settingsData = $('#tmv-import-settings-data').val();
        if (!settingsData) {
            alert('Please paste settings JSON data.');
            return;
        }
        try {
            JSON.parse(settingsData);
        } catch (e) {
            alert('Invalid JSON format. Please check the data.');
            return;
        }
        if (!confirm('Import these settings? Current settings will be overwritten.')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_import_settings',
                nonce: tmvAdmin.nonce,
                settings_data: settingsData
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Settings imported!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Import failed.');
                }
            }
        });
    });

    // Reset All to Defaults
    $(document).on('click', '#tmv-reset-defaults-btn', function() {
        if (!confirm('WARNING: This will reset ALL settings to their defaults. This action cannot be undone. Are you sure?')) return;
        if (!confirm('This is your last chance. Really reset everything?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_reset_defaults',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'All settings reset to defaults!');
                    location.reload();
                } else {
                    alert(response.data.message || 'Reset failed.');
                }
            }
        });
    });


    // =========================================================================
    // CONTENT EXPANDED HANDLERS
    // =========================================================================

    // Approve Comment (moderation queue)
    $(document).on('click', '.tmv-approve-comment-btn', function() {
        var commentId = $(this).data('id');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_add_comment',
                nonce: tmvAdmin.nonce,
                approve_comment: commentId
            },
            success: function(response) {
                if (response.success) {
                    alert('Comment approved!');
                    location.reload();
                }
            }
        });
    });

    // Delete Comment (moderation queue)
    $(document).on('click', '.tmv-delete-comment-btn', function() {
        var commentId = $(this).data('id');
        if (!confirm('Delete this comment?')) return;
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_add_comment',
                nonce: tmvAdmin.nonce,
                delete_comment: commentId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // =========================================================================
    // VERIFICATION EXPANDED HANDLERS
    // =========================================================================

    // Copy Embed Code
    $(document).on('click', '#tmv-copy-embed-btn', function() {
        var $textarea = $('#tmv-embed-code');
        if ($textarea.length) {
            $textarea[0].select();
            try {
                document.execCommand('copy');
                alert('Embed code copied to clipboard!');
            } catch (e) {
                // Fallback for modern browsers
                if (navigator.clipboard) {
                    navigator.clipboard.writeText($textarea.val()).then(function() {
                        alert('Embed code copied to clipboard!');
                    });
                }
            }
        }
    });

})(jQuery);
