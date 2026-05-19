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

    // Auto-blacklist permanent toggle
    $(document).on('change', '#tmv-auto-blacklist-permanent', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_security_option',
                nonce: tmvAdmin.nonce,
                option_key: 'tmv_auto_blacklist_permanent',
                option_value: $(this).is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    // Saved silently
                }
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
    // EXTENDED SETTINGS TAB
    // =========================================================================

    // Range slider updates
    $(document).on('input', '#tmv-qr-size-slider', function() {
        $('#tmv-qr-size-value').text($(this).val() + 'px');
    });

    $(document).on('input', '#tmv-watermark-opacity', function() {
        $('#tmv-watermark-opacity-value').text($(this).val() + '%');
    });

    $(document).on('input', '#tmv-card-border-radius', function() {
        $('#tmv-border-radius-value').text($(this).val() + 'px');
    });

    // Color picker change events
    $(document).on('input', '.tmv-color-picker', function() {
        $(this).siblings('.tmv-color-value').text($(this).val());
    });

    // Save Extended Settings
    $(document).on('click', '#tmv-save-extended-settings-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_extended_settings',
                nonce: tmvAdmin.nonce,
                site_mode: $('#tmv-site-mode').is(':checked') ? 'maintenance' : 'live',
                registration_open: $('#tmv-registration-open').is(':checked') ? '1' : '0',
                default_role: $('#tmv-default-role').val(),
                max_upload: $('#tmv-max-upload').val(),
                allowed_types: $('#tmv-allowed-types').val(),
                auto_approve: $('#tmv-auto-approve').is(':checked') ? '1' : '0',
                cert_validity: $('#tmv-cert-validity').val(),
                qr_size: $('#tmv-qr-size-slider').val(),
                watermark_text: $('#tmv-watermark-text').val(),
                watermark_opacity: $('#tmv-watermark-opacity').val(),
                footer_text: $('#tmv-footer-text').val(),
                timezone: $('#tmv-timezone').val(),
                date_format: $('#tmv-date-format').val(),
                app_prefix: $('#tmv-app-prefix').val(),
                require_email_verify: $('#tmv-require-email-verify').is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) alert('Extended settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Extended Settings');
            }
        });
    });

    // Save Appearance
    $(document).on('click', '#tmv-save-appearance-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_appearance',
                nonce: tmvAdmin.nonce,
                primary_color: $('#tmv-primary-color').val(),
                accent_color: $('#tmv-accent-color').val(),
                dark_mode: $('#tmv-dark-mode').is(':checked') ? '1' : '0',
                custom_css: $('#tmv-custom-css').val(),
                header_style: $('input[name="tmv_header_style"]:checked').val(),
                font_family: $('#tmv-font-family').val(),
                animation_enabled: $('#tmv-animation-enabled').is(':checked') ? '1' : '0',
                logo_max_width: $('#tmv-logo-max-width').val(),
                card_border_radius: $('#tmv-card-border-radius').val(),
                shadow_intensity: $('#tmv-shadow-intensity').val()
            },
            success: function(response) {
                if (response.success) alert('Appearance settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Appearance');
            }
        });
    });

    // Save Notifications
    $(document).on('click', '#tmv-save-notifications-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_notifications',
                nonce: tmvAdmin.nonce,
                notify_new_app: $('#tmv-notify-new-app').is(':checked') ? '1' : '0',
                notify_approval: $('#tmv-notify-approval').is(':checked') ? '1' : '0',
                notify_rejection: $('#tmv-notify-rejection').is(':checked') ? '1' : '0',
                admin_recipients: $('#tmv-admin-recipients').val(),
                digest_frequency: $('#tmv-digest-frequency').val(),
                slack_webhook: $('#tmv-slack-webhook').val(),
                notification_sound: $('#tmv-notification-sound').is(':checked') ? '1' : '0',
                sms_notifications: $('#tmv-sms-notifications').is(':checked') ? '1' : '0',
                push_notifications: $('#tmv-push-notifications').is(':checked') ? '1' : '0',
                auto_reminder_days: $('#tmv-auto-reminder-days').val()
            },
            success: function(response) {
                if (response.success) alert('Notification settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Notifications');
            }
        });
    });

    // Generate Report
    $(document).on('click', '#tmv-generate-report-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_generate_report',
                nonce: tmvAdmin.nonce,
                report_type: $('#tmv-report-type').val(),
                date_from: $('#tmv-report-date-from').val(),
                date_to: $('#tmv-report-date-to').val()
            },
            success: function(response) {
                if (response.success) {
                    var $container = $('#tmv-report-content').empty();
                    $container.append($('<p>').html('<strong>Type:</strong> ').append($('<span>').text(response.data.report_type)));
                    $container.append($('<p>').html('<strong>Records:</strong> ').append($('<span>').text(response.data.count)));
                    if (response.data.data && response.data.data.length > 0) {
                        var $table = $('<table class="tmv-data-table">');
                        var $thead = $('<thead>').appendTo($table);
                        var $headRow = $('<tr>').appendTo($thead);
                        var keys = Object.keys(response.data.data[0]);
                        keys.forEach(function(k) { $('<th>').text(k).appendTo($headRow); });
                        var $tbody = $('<tbody>').appendTo($table);
                        response.data.data.slice(0, 20).forEach(function(row) {
                            var $tr = $('<tr>').appendTo($tbody);
                            keys.forEach(function(k) { $('<td>').text(row[k] || '').appendTo($tr); });
                        });
                        $container.append($table);
                    }
                    $('#tmv-report-output').show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Generate Report');
            }
        });
    });

    // Export CSV Report
    $(document).on('click', '#tmv-export-csv-report-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_generate_report',
                nonce: tmvAdmin.nonce,
                report_type: $('#tmv-report-type').val(),
                date_from: $('#tmv-report-date-from').val(),
                date_to: $('#tmv-report-date-to').val()
            },
            success: function(response) {
                if (response.success && response.data.data) {
                    var csv = '';
                    if (response.data.data.length > 0) {
                        csv += Object.keys(response.data.data[0]).join(',') + '
';
                        response.data.data.forEach(function(row) {
                            csv += Object.values(row).map(function(v) {
                                return '"' + (v || '').toString().replace(/"/g, '""') + '"';
                            }).join(',') + '
';
                        });
                    }
                    var blob = new Blob([csv], { type: 'text/csv' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-report-' + new Date().toISOString().slice(0, 10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            }
        });
    });

    // Export JSON Report
    $(document).on('click', '#tmv-export-json-report-btn', function() {
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_generate_report',
                nonce: tmvAdmin.nonce,
                report_type: $('#tmv-report-type').val(),
                date_from: $('#tmv-report-date-from').val(),
                date_to: $('#tmv-report-date-to').val()
            },
            success: function(response) {
                if (response.success) {
                    var blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-report-' + new Date().toISOString().slice(0, 10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            }
        });
    });

    // Save Workflow
    $(document).on('click', '#tmv-save-workflow-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_workflow',
                nonce: tmvAdmin.nonce,
                auto_assign: $('#tmv-auto-assign').is(':checked') ? '1' : '0',
                default_reviewer: $('#tmv-default-reviewer').val(),
                escalation_days: $('#tmv-escalation-days').val(),
                sla_warning: $('#tmv-sla-warning').val(),
                auto_archive: $('#tmv-auto-archive').val(),
                reminder_frequency: $('#tmv-reminder-frequency').val(),
                duplicate_detection: $('#tmv-duplicate-detection').is(':checked') ? '1' : '0',
                auto_merge: $('#tmv-auto-merge').is(':checked') ? '1' : '0',
                priority_levels: $('#tmv-priority-levels').val(),
                required_fields: $('#tmv-required-fields').val()
            },
            success: function(response) {
                if (response.success) alert('Workflow settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Workflow');
            }
        });
    });

    // Save SEO
    $(document).on('click', '#tmv-save-seo-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_seo',
                nonce: tmvAdmin.nonce,
                meta_title: $('#tmv-meta-title').val(),
                meta_desc: $('#tmv-meta-desc').val(),
                og_image: $('#tmv-og-image').val(),
                sitemap_toggle: $('#tmv-sitemap-toggle').is(':checked') ? '1' : '0',
                structured_data: $('#tmv-structured-data').is(':checked') ? '1' : '0',
                canonical_base: $('#tmv-canonical-base').val(),
                robots_meta: $('#tmv-robots-meta').val(),
                social_title: $('#tmv-social-title').val(),
                social_desc: $('#tmv-social-desc').val(),
                analytics_code: $('#tmv-analytics-code').val()
            },
            success: function(response) {
                if (response.success) alert('SEO settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save SEO Settings');
            }
        });
    });

    // Save Copyright
    $(document).on('click', '#tmv-save-copyright-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_save_copyright',
                nonce: tmvAdmin.nonce,
                disable_rightclick: $('#tmv-disable-rightclick').is(':checked') ? '1' : '0',
                disable_selection: $('#tmv-disable-selection').is(':checked') ? '1' : '0',
                disable_print: $('#tmv-disable-print').is(':checked') ? '1' : '0',
                disable_devtools: $('#tmv-disable-devtools').is(':checked') ? '1' : '0',
                watermark_text: $('#tmv-copyright-watermark').val(),
                watermark_position: $('#tmv-watermark-position').val(),
                hotlink_protection: $('#tmv-hotlink-protection').is(':checked') ? '1' : '0',
                image_overlay: $('#tmv-image-overlay').is(':checked') ? '1' : '0',
                dmca_notice: $('#tmv-dmca-notice').val(),
                copyright_footer: $('#tmv-copyright-footer').val(),
                disable_drag: $('#tmv-disable-drag').is(':checked') ? '1' : '0',
                disable_screenshot: $('#tmv-disable-screenshot').is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) alert('Copyright settings saved!');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Copyright Settings');
            }
        });
    });

    // =========================================================================
    // BACKUP TAB
    // =========================================================================

    // Export Settings as JSON
    $(document).on('click', '#tmv-export-settings-btn', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Exporting...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_export_backup',
                nonce: tmvAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var blob = new Blob([JSON.stringify(response.data.data, null, 2)], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'tmv-settings-backup-' + new Date().toISOString().slice(0, 10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    alert('Settings exported successfully!');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Export All Settings (JSON)');
            }
        });
    });

    // Export Applications CSV
    $(document).on('click', '#tmv-export-apps-csv-btn', function() {
        $('.tmv-export-csv-btn').trigger('click');
    });

    // File drop zone
    var importData = null;

    $(document).on('click', '#tmv-import-drop-zone', function() {
        $('#tmv-import-file').trigger('click');
    });

    $(document).on('change', '#tmv-import-file', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(evt) {
            try {
                importData = evt.target.result;
                JSON.parse(importData);
                $('#tmv-import-settings-btn').prop('disabled', false);
                $('#tmv-import-status').text('File loaded: ' + file.name).css('color', 'green');
            } catch(err) {
                $('#tmv-import-status').text('Invalid JSON file').css('color', 'red');
                importData = null;
            }
        };
        reader.readAsText(file);
    });

    $(document).on('click', '#tmv-import-settings-btn', function() {
        if (!importData) return;
        if (!confirm('Import settings? This will overwrite existing settings.')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Importing...');
        $.ajax({
            url: tmvAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'tmv_import_backup',
                nonce: tmvAdmin.nonce,
                import_data: importData
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Import failed');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Import Settings');
            }
        });
    });

    // Clear all data
    $(document).on('click', '#tmv-clear-all-data-btn', function() {
        var confirm1 = confirm('WARNING: This will permanently delete ALL plugin data. Are you sure?');
        if (!confirm1) return;
        var confirm2 = prompt('Type DELETE to confirm:');
        if (confirm2 !== 'DELETE') {
            alert('Cancelled.');
            return;
        }
        alert('Data clearing is disabled for safety. Use database tools directly.');
    });

})(jQuery);
