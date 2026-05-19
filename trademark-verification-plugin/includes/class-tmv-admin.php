<?php
if (!defined('ABSPATH')) exit;

class TMV_Admin {
    
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu_pages'));
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post_trademark_app', array(__CLASS__, 'save_meta_boxes'));
        add_action('wp_ajax_tmv_approve_application', array(__CLASS__, 'approve_application'));
        add_action('wp_ajax_tmv_reject_application', array(__CLASS__, 'reject_application'));
        add_action('wp_ajax_tmv_upload_certificate', array(__CLASS__, 'upload_certificate'));
        add_action('wp_ajax_tmv_regenerate_certificate', array(__CLASS__, 'regenerate_certificate'));
        add_filter('manage_trademark_app_posts_columns', array(__CLASS__, 'custom_columns'));
        add_action('manage_trademark_app_posts_custom_column', array(__CLASS__, 'column_content'), 10, 2);

        // New AJAX handlers for enhanced dashboard
        add_action('wp_ajax_tmv_export_csv', array(__CLASS__, 'ajax_export_csv'));
        add_action('wp_ajax_tmv_bulk_action', array(__CLASS__, 'ajax_bulk_action'));
        add_action('wp_ajax_tmv_flag_application', array(__CLASS__, 'ajax_flag_application'));
        add_action('wp_ajax_tmv_save_notes', array(__CLASS__, 'ajax_save_notes'));
        add_action('wp_ajax_tmv_revoke_certificate', array(__CLASS__, 'ajax_revoke_certificate'));
        add_action('wp_ajax_tmv_batch_generate_certs', array(__CLASS__, 'ajax_batch_generate_certs'));
        add_action('wp_ajax_tmv_ban_user', array(__CLASS__, 'ajax_ban_user'));
        add_action('wp_ajax_tmv_save_email_templates', array(__CLASS__, 'ajax_save_email_templates'));
        add_action('wp_ajax_tmv_add_announcement', array(__CLASS__, 'ajax_add_announcement'));
        add_action('wp_ajax_tmv_delete_announcement', array(__CLASS__, 'ajax_delete_announcement'));
        add_action('wp_ajax_tmv_manage_ip_list', array(__CLASS__, 'ajax_manage_ip_list'));
        add_action('wp_ajax_tmv_save_faq', array(__CLASS__, 'ajax_save_faq'));
        add_action('wp_ajax_tmv_delete_faq', array(__CLASS__, 'ajax_delete_faq'));
        add_action('wp_ajax_tmv_save_contact_info', array(__CLASS__, 'ajax_save_contact_info'));
        add_action('wp_ajax_tmv_save_social_links', array(__CLASS__, 'ajax_save_social_links'));
        add_action('wp_ajax_tmv_cleanup_database', array(__CLASS__, 'ajax_cleanup_database'));
        add_action('wp_ajax_tmv_clear_error_log', array(__CLASS__, 'ajax_clear_error_log'));

        // Documents & Notifications AJAX handlers
        add_action('wp_ajax_tmv_save_document', array(__CLASS__, 'ajax_save_document'));
        add_action('wp_ajax_tmv_delete_document', array(__CLASS__, 'ajax_delete_document'));
        add_action('wp_ajax_tmv_save_notification_settings', array(__CLASS__, 'ajax_save_notification_settings'));
        add_action('wp_ajax_tmv_mark_notification_read', array(__CLASS__, 'ajax_mark_notification_read'));
        add_action('wp_ajax_tmv_save_webhook', array(__CLASS__, 'ajax_save_webhook'));
        add_action('wp_ajax_tmv_delete_webhook', array(__CLASS__, 'ajax_delete_webhook'));
        add_action('wp_ajax_tmv_save_password_policy', array(__CLASS__, 'ajax_save_password_policy'));
        add_action('wp_ajax_tmv_save_session_settings', array(__CLASS__, 'ajax_save_session_settings'));
        add_action('wp_ajax_tmv_toggle_maintenance', array(__CLASS__, 'ajax_toggle_maintenance'));
        add_action('wp_ajax_tmv_save_backup_settings', array(__CLASS__, 'ajax_save_backup_settings'));
        add_action('wp_ajax_tmv_export_all_settings', array(__CLASS__, 'ajax_export_all_settings'));
        add_action('wp_ajax_tmv_import_settings', array(__CLASS__, 'ajax_import_settings'));
        add_action('wp_ajax_tmv_reset_defaults', array(__CLASS__, 'ajax_reset_defaults'));
        add_action('wp_ajax_tmv_save_tags', array(__CLASS__, 'ajax_save_tags'));
        add_action('wp_ajax_tmv_add_comment', array(__CLASS__, 'ajax_add_comment'));
        add_action('wp_ajax_tmv_send_applicant_email', array(__CLASS__, 'ajax_send_applicant_email'));
        add_action('wp_ajax_tmv_schedule_reminder', array(__CLASS__, 'ajax_schedule_reminder'));
        add_action('wp_ajax_tmv_import_users', array(__CLASS__, 'ajax_import_users'));
        add_action('wp_ajax_tmv_export_users', array(__CLASS__, 'ajax_export_users'));
        add_action('wp_ajax_tmv_invite_user', array(__CLASS__, 'ajax_invite_user'));
        add_action('wp_ajax_tmv_bulk_email_users', array(__CLASS__, 'ajax_bulk_email_users'));
        add_action('wp_ajax_tmv_save_cert_watermark', array(__CLASS__, 'ajax_save_cert_watermark'));
        add_action('wp_ajax_tmv_save_cert_numbering', array(__CLASS__, 'ajax_save_cert_numbering'));
        add_action('wp_ajax_tmv_generate_report', array(__CLASS__, 'ajax_generate_report'));
        add_action('wp_ajax_tmv_schedule_report', array(__CLASS__, 'ajax_schedule_report'));
    }
    
    public static function add_menu_pages() {
        add_submenu_page(
            'edit.php?post_type=trademark_app',
            'TMV Dashboard',
            'Dashboard',
            'manage_options',
            'tmv-dashboard',
            array(__CLASS__, 'render_dashboard')
        );
        
        add_submenu_page(
            'edit.php?post_type=trademark_app',
            'TMV Settings',
            'Settings',
            'manage_options',
            'tmv-settings',
            array(__CLASS__, 'render_settings')
        );
    }
    
    public static function add_meta_boxes() {
        add_meta_box('tmv_application_details', 'Application Details', array(__CLASS__, 'render_details_metabox'), 'trademark_app', 'normal', 'high');
        add_meta_box('tmv_dates', 'Important Dates', array(__CLASS__, 'render_dates_metabox'), 'trademark_app', 'normal', 'high');
        add_meta_box('tmv_certificate', 'Certificate Files', array(__CLASS__, 'render_certificate_metabox'), 'trademark_app', 'normal', 'high');
        add_meta_box('tmv_status_control', 'Status & Actions', array(__CLASS__, 'render_status_metabox'), 'trademark_app', 'side', 'high');
        add_meta_box('tmv_qr_settings', 'QR & Verification Link', array(__CLASS__, 'render_qr_metabox'), 'trademark_app', 'side', 'default');
    }
    
    public static function render_details_metabox($post) {
        wp_nonce_field('tmv_save_meta', 'tmv_meta_nonce');
        $meta = get_post_meta($post->ID);
        ?>
        <div class="tmv-admin-grid">
            <div class="tmv-field">
                <label>TM Number</label>
                <input type="text" name="tmv_tm_number" value="<?php echo esc_attr($meta['tmv_tm_number'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Certificate Title</label>
                <input type="text" name="tmv_cert_title" value="<?php echo esc_attr($meta['tmv_cert_title'][0] ?? 'Certificate of Registration of Trademark [Rule 30(1)]'); ?>" />
            </div>
            <div class="tmv-field">
                <label>Class</label>
                <input type="text" name="tmv_class" value="<?php echo esc_attr($meta['tmv_class'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Owner/Company</label>
                <input type="text" name="tmv_owner" value="<?php echo esc_attr($meta['tmv_owner'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Address</label>
                <textarea name="tmv_address"><?php echo esc_textarea($meta['tmv_address'][0] ?? ''); ?></textarea>
            </div>
            <div class="tmv-field">
                <label>Service Description</label>
                <textarea name="tmv_service_desc"><?php echo esc_textarea($meta['tmv_service_desc'][0] ?? ''); ?></textarea>
            </div>
            <div class="tmv-field">
                <label>Pen Serial</label>
                <input type="text" name="tmv_pen_serial" value="<?php echo esc_attr($meta['tmv_pen_serial'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Signatory</label>
                <input type="text" name="tmv_signatory" value="<?php echo esc_attr($meta['tmv_signatory'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Designation</label>
                <input type="text" name="tmv_designation" value="<?php echo esc_attr($meta['tmv_designation'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Sealed Text</label>
                <input type="text" name="tmv_sealed_text" value="<?php echo esc_attr($meta['tmv_sealed_text'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Logo Text</label>
                <input type="text" name="tmv_logo_text" value="<?php echo esc_attr($meta['tmv_logo_text'][0] ?? ''); ?>" />
            </div>
        </div>
        <?php
    }
    
    public static function render_dates_metabox($post) {
        $meta = get_post_meta($post->ID);
        ?>
        <div class="tmv-admin-grid tmv-dates-grid">
            <div class="tmv-field">
                <label>Application Date</label>
                <input type="date" name="tmv_application_date" value="<?php echo esc_attr($meta['tmv_application_date'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Registration Date</label>
                <input type="date" name="tmv_reg_date" value="<?php echo esc_attr($meta['tmv_reg_date'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Approved Date</label>
                <input type="date" name="tmv_approved_date" value="<?php echo esc_attr($meta['tmv_approved_date'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Sealing Date</label>
                <input type="date" name="tmv_sealing_date" value="<?php echo esc_attr($meta['tmv_sealing_date'][0] ?? ''); ?>" />
            </div>
            <div class="tmv-field">
                <label>Expiry Date</label>
                <input type="date" name="tmv_expiry_date" value="<?php echo esc_attr($meta['tmv_expiry_date'][0] ?? ''); ?>" />
            </div>
        </div>
        <?php
    }
    
    public static function render_certificate_metabox($post) {
        $pdf_id = get_post_meta($post->ID, 'tmv_certificate_pdf', true);
        $jpg_id = get_post_meta($post->ID, 'tmv_certificate_jpg', true);
        $logo_id = get_post_meta($post->ID, 'tmv_brand_logo', true);
        ?>
        <div class="tmv-certificate-upload">
            <div class="tmv-upload-section">
                <h4>Certificate PDF</h4>
                <input type="hidden" name="tmv_certificate_pdf" id="tmv_certificate_pdf" value="<?php echo esc_attr($pdf_id); ?>" />
                <?php if ($pdf_id): ?>
                    <p class="tmv-file-info">PDF Uploaded: <?php echo esc_html(basename(get_attached_file($pdf_id))); ?></p>
                <?php endif; ?>
                <button type="button" class="button tmv-upload-btn" data-target="tmv_certificate_pdf" data-type="application/pdf">Upload PDF</button>
                <button type="button" class="button tmv-remove-btn" data-target="tmv_certificate_pdf">Remove</button>
            </div>
            
            <div class="tmv-upload-section">
                <h4>Certificate JPG</h4>
                <input type="hidden" name="tmv_certificate_jpg" id="tmv_certificate_jpg" value="<?php echo esc_attr($jpg_id); ?>" />
                <?php if ($jpg_id): ?>
                    <img src="<?php echo esc_url(wp_get_attachment_url($jpg_id)); ?>" style="max-width:200px;height:auto;" />
                <?php endif; ?>
                <button type="button" class="button tmv-upload-btn" data-target="tmv_certificate_jpg" data-type="image">Upload JPG</button>
                <button type="button" class="button tmv-remove-btn" data-target="tmv_certificate_jpg">Remove</button>
            </div>
            
            <div class="tmv-upload-section">
                <h4>Brand Logo</h4>
                <input type="hidden" name="tmv_brand_logo" id="tmv_brand_logo" value="<?php echo esc_attr($logo_id); ?>" />
                <?php if ($logo_id): ?>
                    <img src="<?php echo esc_url(wp_get_attachment_url($logo_id)); ?>" style="max-width:150px;height:auto;" />
                <?php endif; ?>
                <button type="button" class="button tmv-upload-btn" data-target="tmv_brand_logo" data-type="image">Upload Logo</button>
                <button type="button" class="button tmv-remove-btn" data-target="tmv_brand_logo">Remove</button>
            </div>
            
            <div class="tmv-upload-section" style="margin-top:15px;padding-top:15px;border-top:1px solid #ddd;">
                <h4>Auto-Generate Certificate</h4>
                <p class="description">Generate or regenerate the certificate image from current application data.</p>
                <button type="button" class="button button-primary tmv-regenerate-cert" data-post-id="<?php echo esc_attr($post->ID); ?>">Regenerate Certificate</button>
                <span class="tmv-regenerate-status" style="margin-left:10px;"></span>
            </div>
        </div>
        <?php
    }
    
    public static function render_status_metabox($post) {
        $status = get_post_meta($post->ID, 'tmv_status', true) ?: 'pending';
        ?>
        <div class="tmv-status-box">
            <p><strong>Current Status:</strong> 
                <span class="tmv-status-badge tmv-status-<?php echo esc_attr($status); ?>">
                    <?php echo esc_html(ucfirst($status)); ?>
                </span>
            </p>
            <select name="tmv_status" id="tmv_status">
                <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                <option value="approved" <?php selected($status, 'approved'); ?>>Approved</option>
                <option value="rejected" <?php selected($status, 'rejected'); ?>>Rejected</option>
            </select>
        </div>
        <?php
    }
    
    public static function render_qr_metabox($post) {
        $verify_code = get_post_meta($post->ID, 'tmv_verify_code', true);
        $custom_qr_url = get_post_meta($post->ID, 'tmv_custom_qr_url', true);
        $verify_base = get_option('tmv_verify_base_url', home_url('/verify/'));
        $qr_size = intval(get_option('tmv_qr_size', 200));
        
        if (empty($verify_code)) {
            $verify_code = self::generate_verify_code();
            update_post_meta($post->ID, 'tmv_verify_code', $verify_code);
        }
        
        $verify_url = TMV_API::get_verify_url($post->ID);
        ?>
        <div class="tmv-qr-box">
            <p><strong>Verify Code:</strong></p>
            <input type="text" name="tmv_verify_code" value="<?php echo esc_attr($verify_code); ?>" maxlength="20" style="width:100%;" />
            <p class="description">20 character verification code (POST type)</p>
            
            <p><strong>Custom QR URL:</strong></p>
            <input type="url" name="tmv_custom_qr_url" value="<?php echo esc_url($custom_qr_url); ?>" placeholder="<?php echo esc_attr($verify_base); ?>" style="width:100%;" />
            <p class="description">Leave blank to use default verify URL</p>
            
            <p><strong>Full Verify Link:</strong></p>
            <code style="word-break:break-all;font-size:11px;"><?php echo esc_html($verify_url); ?></code>
            
            <div id="tmv-qr-display" style="margin-top:15px;text-align:center;"></div>
        </div>
        <script>
        (function() {
            var verifyUrl = <?php echo wp_json_encode($verify_url); ?>;
            var qrSize = <?php echo intval($qr_size); ?>;
            window.addEventListener('load', function() {
                var container = document.getElementById('tmv-qr-display');
                if (container && typeof QRCode !== 'undefined') {
                    new QRCode(container, {
                        text: verifyUrl,
                        width: qrSize,
                        height: qrSize
                    });
                } else if (container) {
                    container.innerHTML = '<a href="' + verifyUrl + '" target="_blank" style="word-break:break-all;font-size:12px;">' + verifyUrl + '</a>';
                }
            });
        })();
        </script>
        <?php
    }
    
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['tmv_meta_nonce']) || !wp_verify_nonce($_POST['tmv_meta_nonce'], 'tmv_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        $fields = array(
            'tmv_tm_number', 'tmv_cert_title', 'tmv_class', 'tmv_owner',
            'tmv_address', 'tmv_service_desc', 'tmv_pen_serial', 'tmv_signatory',
            'tmv_designation', 'tmv_sealed_text', 'tmv_logo_text',
            'tmv_application_date', 'tmv_reg_date', 'tmv_approved_date',
            'tmv_sealing_date', 'tmv_expiry_date', 'tmv_status',
            'tmv_verify_code', 'tmv_custom_qr_url',
            'tmv_certificate_pdf', 'tmv_certificate_jpg', 'tmv_brand_logo',
        );
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    public static function custom_columns($columns) {
        $new = array();
        $new['cb'] = $columns['cb'];
        $new['title'] = 'Application';
        $new['tmv_tm_number'] = 'TM Number';
        $new['tmv_owner'] = 'Owner';
        $new['tmv_status'] = 'Status';
        $new['tmv_reg_date'] = 'Reg Date';
        $new['tmv_expiry_date'] = 'Expiry';
        $new['date'] = 'Submitted';
        return $new;
    }
    
    public static function column_content($column, $post_id) {
        switch ($column) {
            case 'tmv_tm_number':
                echo esc_html(get_post_meta($post_id, 'tmv_tm_number', true));
                break;
            case 'tmv_owner':
                echo esc_html(get_post_meta($post_id, 'tmv_owner', true));
                break;
            case 'tmv_status':
                $status = get_post_meta($post_id, 'tmv_status', true) ?: 'pending';
                echo '<span class="tmv-status-badge tmv-status-' . esc_attr($status) . '">' . esc_html(ucfirst($status)) . '</span>';
                break;
            case 'tmv_reg_date':
                echo esc_html(get_post_meta($post_id, 'tmv_reg_date', true));
                break;
            case 'tmv_expiry_date':
                echo esc_html(get_post_meta($post_id, 'tmv_expiry_date', true));
                break;
        }
    }
    
    public static function approve_application() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id']);
        update_post_meta($post_id, 'tmv_status', 'approved');
        update_post_meta($post_id, 'tmv_approved_date', current_time('Y-m-d'));
        
        $cert_id = false;
        try {
            if (class_exists('TMV_Certificate')) {
                $cert_id = TMV_Certificate::generate($post_id);
            }
        } catch (Exception $e) {
            $cert_id = false;
        }
        
        $message = 'Application approved successfully';
        if ($cert_id) {
            $message .= ' and certificate generated';
        }
        
        wp_send_json_success(array('message' => $message, 'certificate_id' => $cert_id));
    }
    
    public static function reject_application() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id']);
        update_post_meta($post_id, 'tmv_status', 'rejected');
        
        wp_send_json_success(array('message' => 'Application rejected'));
    }
    
    public static function upload_certificate() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        wp_send_json_success();
    }
    
    public static function regenerate_certificate() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        try {
            if (!class_exists('TMV_Certificate')) {
                wp_send_json_error(array('message' => 'Certificate class not available'));
                return;
            }
            $cert_id = TMV_Certificate::generate($post_id);
            if ($cert_id) {
                wp_send_json_success(array(
                    'message' => 'Certificate regenerated successfully',
                    'certificate_id' => $cert_id,
                    'certificate_url' => wp_get_attachment_url($cert_id),
                ));
            } else {
                wp_send_json_error(array('message' => 'Certificate generation failed'));
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
        }
    }
    
    // =========================================================================
    // ENHANCED DASHBOARD
    // =========================================================================
    
    public static function render_dashboard() {
        ?>
        <div class="wrap tmv-dashboard">
            <h1>Trademark Verification Dashboard</h1>
            
            <div class="tmv-dashboard-tabs">
                <button class="tmv-tab-btn active" data-tab="overview">Overview</button>
                <button class="tmv-tab-btn" data-tab="applications">Applications</button>
                <button class="tmv-tab-btn" data-tab="certificates">Certificates</button>
                <button class="tmv-tab-btn" data-tab="users">Users</button>
                <button class="tmv-tab-btn" data-tab="analytics">Analytics</button>
                <button class="tmv-tab-btn" data-tab="communication">Communication</button>
                <button class="tmv-tab-btn" data-tab="security">Security</button>
                <button class="tmv-tab-btn" data-tab="content">Content</button>
                <button class="tmv-tab-btn" data-tab="verification">Verification</button>
                <button class="tmv-tab-btn" data-tab="maintenance">Maintenance</button>
                <button class="tmv-tab-btn" data-tab="documents">Documents</button>
                <button class="tmv-tab-btn" data-tab="notifications">Notifications</button>
            </div>
            
            <div class="tmv-tab-content active" id="tmv-tab-overview">
                <?php self::render_tab_overview(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-applications">
                <?php self::render_tab_applications(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-certificates">
                <?php self::render_tab_certificates(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-users">
                <?php self::render_tab_users(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-analytics">
                <?php self::render_tab_analytics(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-communication">
                <?php self::render_tab_communication(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-security">
                <?php self::render_tab_security(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-content">
                <?php self::render_tab_content(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-verification">
                <?php self::render_tab_verification(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-maintenance">
                <?php self::render_tab_maintenance(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-documents">
                <?php self::render_tab_documents(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-notifications">
                <?php self::render_tab_notifications(); ?>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_overview() {
        $total = wp_count_posts('trademark_app');
        $total_count = intval(($total->publish ?? 0) + ($total->draft ?? 0) + ($total->private ?? 0));
        $pending = intval(self::count_by_status('pending'));
        $approved = intval(self::count_by_status('approved'));
        $rejected = intval(self::count_by_status('rejected'));
        
        // Today's applications
        $today_args = array(
            'post_type' => 'trademark_app',
            'post_status' => 'any',
            'date_query' => array(array('after' => 'today')),
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        $today_query = new WP_Query($today_args);
        $today_count = $today_query->found_posts;
        
        // This week
        $week_args = array(
            'post_type' => 'trademark_app',
            'post_status' => 'any',
            'date_query' => array(array('after' => '1 week ago')),
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        $week_query = new WP_Query($week_args);
        $week_count = $week_query->found_posts;
        
        // This month
        $month_args = array(
            'post_type' => 'trademark_app',
            'post_status' => 'any',
            'date_query' => array(array('after' => '1 month ago')),
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        $month_query = new WP_Query($month_args);
        $month_count = $month_query->found_posts;
        
        // Users
        $user_count = count_users();
        $total_users = $user_count['total_users'];
        
        // Active users (last login within 30 days)
        $active_users = get_users(array(
            'meta_key' => 'last_login',
            'meta_value' => date('Y-m-d', strtotime('-30 days')),
            'meta_compare' => '>=',
            'count_total' => true,
            'fields' => 'ids',
        ));
        $active_count = is_array($active_users) ? count($active_users) : 0;
        
        // Certificates generated
        global $wpdb;
        $certs_generated = intval($wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_certificate_jpg' AND meta_value != ''"
        ));
        
        // Certificates expiring within 90 days
        $expiry_date = date('Y-m-d', strtotime('+90 days'));
        $certs_expiring = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_expiry_date' AND meta_value != '' AND meta_value <= %s AND meta_value >= %s",
            $expiry_date,
            date('Y-m-d')
        )));
        
        // Approval rate
        $approval_rate = $total_count > 0 ? round(($approved / $total_count) * 100, 1) : 0;
        ?>
        <div class="tmv-stats-grid tmv-stats-grid-large">
            <div class="tmv-stat-card tmv-stat-total">
                <div class="tmv-stat-icon">&#128203;</div>
                <h3><?php echo $total_count; ?></h3>
                <p>Total Applications</p>
            </div>
            <div class="tmv-stat-card tmv-stat-pending">
                <div class="tmv-stat-icon">&#9203;</div>
                <h3><?php echo $pending; ?></h3>
                <p>Pending Applications</p>
            </div>
            <div class="tmv-stat-card tmv-stat-approved">
                <div class="tmv-stat-icon">&#9989;</div>
                <h3><?php echo $approved; ?></h3>
                <p>Approved Applications</p>
            </div>
            <div class="tmv-stat-card tmv-stat-rejected">
                <div class="tmv-stat-icon">&#10060;</div>
                <h3><?php echo $rejected; ?></h3>
                <p>Rejected Applications</p>
            </div>
            <div class="tmv-stat-card tmv-stat-today">
                <div class="tmv-stat-icon">&#128197;</div>
                <h3><?php echo $today_count; ?></h3>
                <p>Today's Applications</p>
            </div>
            <div class="tmv-stat-card tmv-stat-week">
                <div class="tmv-stat-icon">&#128198;</div>
                <h3><?php echo $week_count; ?></h3>
                <p>This Week</p>
            </div>
            <div class="tmv-stat-card tmv-stat-month">
                <div class="tmv-stat-icon">&#128199;</div>
                <h3><?php echo $month_count; ?></h3>
                <p>This Month</p>
            </div>
            <div class="tmv-stat-card tmv-stat-users">
                <div class="tmv-stat-icon">&#128101;</div>
                <h3><?php echo $total_users; ?></h3>
                <p>Total Registered Users</p>
            </div>
            <div class="tmv-stat-card tmv-stat-active">
                <div class="tmv-stat-icon">&#128994;</div>
                <h3><?php echo $active_count; ?></h3>
                <p>Active Users (30 days)</p>
            </div>
            <div class="tmv-stat-card tmv-stat-certs">
                <div class="tmv-stat-icon">&#128220;</div>
                <h3><?php echo $certs_generated; ?></h3>
                <p>Certificates Generated</p>
            </div>
            <div class="tmv-stat-card tmv-stat-expiring">
                <div class="tmv-stat-icon">&#9888;</div>
                <h3><?php echo $certs_expiring; ?></h3>
                <p>Certificates Expiring (90d)</p>
            </div>
            <div class="tmv-stat-card tmv-stat-rate">
                <div class="tmv-stat-icon">&#128200;</div>
                <h3><?php echo $approval_rate; ?>%</h3>
                <p>Approval Rate</p>
            </div>
        </div>
        
        <div class="tmv-overview-sections">
            <div class="tmv-section-box">
                <h3>Recent Activity</h3>
                <table class="tmv-data-table">
                    <thead><tr><th>TM Number</th><th>Owner</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php
                    $recent = get_posts(array('post_type' => 'trademark_app', 'posts_per_page' => 10, 'post_status' => 'any'));
                    foreach ($recent as $app):
                        $status = get_post_meta($app->ID, 'tmv_status', true) ?: 'pending';
                    ?>
                        <tr>
                            <td><?php echo esc_html(get_post_meta($app->ID, 'tmv_tm_number', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta($app->ID, 'tmv_owner', true)); ?></td>
                            <td><span class="tmv-status-badge tmv-status-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                            <td><?php echo esc_html(get_the_date('Y-m-d', $app->ID)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tmv-quick-actions">
                <a href="<?php echo admin_url('edit.php?post_type=trademark_app&tmv_status=pending'); ?>" class="button button-primary">View All Pending</a>
                <button class="button tmv-export-csv-btn">Generate CSV Report</button>
            </div>
        </div>
        
        <!-- Pie Chart Data Widget -->
        <div class="tmv-section-box">
            <h3>Application Status Distribution</h3>
            <?php
            $dist_total = $approved + $pending + $rejected;
            $pct_approved = $dist_total > 0 ? round(($approved / $dist_total) * 100, 1) : 0;
            $pct_pending = $dist_total > 0 ? round(($pending / $dist_total) * 100, 1) : 0;
            $pct_rejected = $dist_total > 0 ? round(($rejected / $dist_total) * 100, 1) : 0;
            ?>
            <div class="tmv-distribution-bars">
                <div class="tmv-dist-item">
                    <span class="tmv-dist-label">Approved (<?php echo intval($approved); ?>)</span>
                    <div class="tmv-progress-bar"><div class="tmv-progress-fill tmv-fill-green" style="width:<?php echo esc_attr($pct_approved); ?>%"></div></div>
                    <span class="tmv-dist-pct"><?php echo esc_html($pct_approved); ?>%</span>
                </div>
                <div class="tmv-dist-item">
                    <span class="tmv-dist-label">Pending (<?php echo intval($pending); ?>)</span>
                    <div class="tmv-progress-bar"><div class="tmv-progress-fill tmv-fill-yellow" style="width:<?php echo esc_attr($pct_pending); ?>%"></div></div>
                    <span class="tmv-dist-pct"><?php echo esc_html($pct_pending); ?>%</span>
                </div>
                <div class="tmv-dist-item">
                    <span class="tmv-dist-label">Rejected (<?php echo intval($rejected); ?>)</span>
                    <div class="tmv-progress-bar"><div class="tmv-progress-fill tmv-fill-red" style="width:<?php echo esc_attr($pct_rejected); ?>%"></div></div>
                    <span class="tmv-dist-pct"><?php echo esc_html($pct_rejected); ?>%</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Login Activity -->
        <div class="tmv-section-box">
            <h3>Recent Login Activity</h3>
            <table class="tmv-data-table">
                <thead><tr><th>User</th><th>Email</th><th>Last Login</th><th>IP Address</th></tr></thead>
                <tbody>
                <?php
                $login_users = get_users(array(
                    'meta_key' => 'tmv_last_login',
                    'orderby' => 'meta_value',
                    'order' => 'DESC',
                    'number' => 5,
                ));
                foreach ($login_users as $lu):
                    $last_login = get_user_meta($lu->ID, 'tmv_last_login', true);
                    $login_ip = get_user_meta($lu->ID, 'tmv_last_login_ip', true);
                ?>
                    <tr>
                        <td><?php echo esc_html($lu->user_login); ?></td>
                        <td><?php echo esc_html($lu->user_email); ?></td>
                        <td><?php echo $last_login ? esc_html($last_login) : 'N/A'; ?></td>
                        <td><?php echo $login_ip ? esc_html($login_ip) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- System Uptime -->
        <div class="tmv-section-box">
            <h3>System Uptime</h3>
            <?php
            $activation_time = get_option('tmv_plugin_activated', current_time('timestamp'));
            $uptime_seconds = current_time('timestamp') - intval($activation_time);
            $uptime_days = floor($uptime_seconds / 86400);
            $uptime_hours = floor(($uptime_seconds % 86400) / 3600);
            $uptime_minutes = floor(($uptime_seconds % 3600) / 60);
            ?>
            <div class="tmv-uptime-display">
                <div class="tmv-stat-card"><h3><?php echo intval($uptime_days); ?></h3><p>Days</p></div>
                <div class="tmv-stat-card"><h3><?php echo intval($uptime_hours); ?></h3><p>Hours</p></div>
                <div class="tmv-stat-card"><h3><?php echo intval($uptime_minutes); ?></h3><p>Minutes</p></div>
            </div>
        </div>
        
        <!-- Pending Actions Counter -->
        <div class="tmv-section-box">
            <h3>Pending Actions</h3>
            <?php
            $pending_apps = intval(self::count_by_status('pending'));
            $unread_notifications = intval(get_option('tmv_unread_notifications_count', 0));
            ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card tmv-stat-pending"><h3><?php echo $pending_apps; ?></h3><p>Pending Applications</p></div>
                <div class="tmv-stat-card tmv-stat-expiring"><h3><?php echo $certs_expiring; ?></h3><p>Expiring Certificates</p></div>
                <div class="tmv-stat-card"><h3><?php echo $unread_notifications; ?></h3><p>Unread Notifications</p></div>
            </div>
        </div>
        
        <!-- Revenue/Fee Tracker -->
        <div class="tmv-section-box">
            <h3>Revenue / Fee Tracker</h3>
            <?php
            $total_revenue = floatval(get_option('tmv_total_revenue', 0));
            $monthly_revenue = floatval(get_option('tmv_monthly_revenue', 0));
            $pending_fees = floatval(get_option('tmv_pending_fees', 0));
            ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3>&#2547;<?php echo number_format($total_revenue, 2); ?></h3><p>Total Revenue Collected</p></div>
                <div class="tmv-stat-card"><h3>&#2547;<?php echo number_format($monthly_revenue, 2); ?></h3><p>This Month Revenue</p></div>
                <div class="tmv-stat-card tmv-stat-pending"><h3>&#2547;<?php echo number_format($pending_fees, 2); ?></h3><p>Pending Fees</p></div>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_applications() {
        ?>
        <div class="tmv-tab-header">
            <h2>Application Management</h2>
            <div class="tmv-tab-actions">
                <input type="text" id="tmv-app-search" class="tmv-search-input" placeholder="Search by TM Number or Owner..." />
                <select id="tmv-app-status-filter" class="tmv-filter-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button class="button tmv-export-csv-btn">Export CSV</button>
            </div>
        </div>
        
        <div class="tmv-bulk-actions">
            <select id="tmv-bulk-action-select">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve Selected</option>
                <option value="reject">Reject Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="button" id="tmv-bulk-apply-btn">Apply</button>
        </div>
        
        <table class="tmv-data-table" id="tmv-applications-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="tmv-select-all" /></th>
                    <th>TM Number</th>
                    <th>Owner</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $apps = get_posts(array('post_type' => 'trademark_app', 'posts_per_page' => 50, 'post_status' => 'any'));
            foreach ($apps as $app):
                $status = get_post_meta($app->ID, 'tmv_status', true) ?: 'pending';
                $priority = get_post_meta($app->ID, 'tmv_priority', true);
                $tm_number = get_post_meta($app->ID, 'tmv_tm_number', true);
                $owner = get_post_meta($app->ID, 'tmv_owner', true);
                $class = get_post_meta($app->ID, 'tmv_class', true);
            ?>
                <tr data-id="<?php echo esc_attr($app->ID); ?>" data-status="<?php echo esc_attr($status); ?>" data-tm="<?php echo esc_attr(strtolower($tm_number)); ?>" data-owner="<?php echo esc_attr(strtolower($owner)); ?>">
                    <td><input type="checkbox" class="tmv-app-checkbox" value="<?php echo esc_attr($app->ID); ?>" /></td>
                    <td><?php echo esc_html($tm_number); ?></td>
                    <td><?php echo esc_html($owner); ?></td>
                    <td><?php echo esc_html($class); ?></td>
                    <td><span class="tmv-status-badge tmv-status-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                    <td><?php echo esc_html(get_the_date('Y-m-d', $app->ID)); ?></td>
                    <td><span class="tmv-star <?php echo $priority ? 'active' : ''; ?>" data-id="<?php echo esc_attr($app->ID); ?>">&#9733;</span></td>
                    <td class="tmv-action-btns">
                        <a href="<?php echo get_edit_post_link($app->ID); ?>" class="button button-small">Edit</a>
                        <?php if ($status === 'pending'): ?>
                        <button class="button button-small button-primary tmv-quick-approve" data-id="<?php echo $app->ID; ?>">Approve</button>
                        <button class="button button-small tmv-quick-reject" data-id="<?php echo $app->ID; ?>">Reject</button>
                        <?php endif; ?>
                        <button class="button button-small tmv-notes-btn" data-id="<?php echo $app->ID; ?>">Notes</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <div id="tmv-notes-modal" class="tmv-modal" style="display:none;">
            <div class="tmv-modal-content">
                <h3>Admin Notes</h3>
                <textarea id="tmv-notes-textarea" rows="5" style="width:100%;"></textarea>
                <div class="tmv-modal-actions">
                    <button class="button button-primary" id="tmv-save-notes-btn">Save Notes</button>
                    <button class="button" id="tmv-close-notes-btn">Close</button>
                </div>
            </div>
        </div>
        
        <!-- Advanced Date Range Filter -->
        <div class="tmv-section-box">
            <h3>Advanced Filters</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>From Date:</label>
                    <input type="date" id="tmv-filter-date-from" class="tmv-date-input" />
                </div>
                <div class="tmv-field-inline">
                    <label>To Date:</label>
                    <input type="date" id="tmv-filter-date-to" class="tmv-date-input" />
                </div>
                <div class="tmv-field-inline">
                    <label>Class Filter:</label>
                    <select id="tmv-filter-class" class="tmv-filter-select">
                        <option value="">All Classes</option>
                        <?php
                        global $wpdb;
                        $classes = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_class' AND meta_value != '' ORDER BY meta_value");
                        foreach ($classes as $cls):
                        ?>
                            <option value="<?php echo esc_attr($cls); ?>"><?php echo esc_html($cls); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="button" id="tmv-apply-advanced-filters">Apply Filters</button>
                <button class="button" id="tmv-timeline-view-toggle">Toggle Timeline View</button>
            </div>
        </div>
        
        <!-- Duplicate Detection Alert -->
        <div class="tmv-section-box">
            <h3>Duplicate Detection Alerts</h3>
            <p class="description">Applications with similar TM numbers or owner names that may be duplicates.</p>
            <table class="tmv-data-table" id="tmv-duplicates-table">
                <thead><tr><th>TM Number</th><th>Owner</th><th>Similar To</th><th>Similarity</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $dup_alerts = get_option('tmv_duplicate_alerts', array());
                if (!empty($dup_alerts)):
                    foreach (array_slice($dup_alerts, 0, 10) as $dup):
                ?>
                    <tr>
                        <td><?php echo esc_html($dup['tm_number'] ?? ''); ?></td>
                        <td><?php echo esc_html($dup['owner'] ?? ''); ?></td>
                        <td><?php echo esc_html($dup['similar_to'] ?? ''); ?></td>
                        <td><?php echo esc_html($dup['similarity'] ?? ''); ?>%</td>
                        <td><button class="button button-small">Dismiss</button></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Auto-Assign Reviewer -->
        <div class="tmv-section-box">
            <h3>Auto-Assign Reviewer</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Default Reviewer:</label>
                    <select id="tmv-auto-reviewer">
                        <option value="">Select Reviewer</option>
                        <?php
                        $admins = get_users(array('role' => 'administrator'));
                        foreach ($admins as $admin):
                        ?>
                            <option value="<?php echo esc_attr($admin->ID); ?>"><?php echo esc_html($admin->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Application Tagging System -->
        <div class="tmv-section-box">
            <h3>Application Tags</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Add Tag:</label>
                    <input type="text" id="tmv-new-tag-input" placeholder="Enter tag name" />
                    <button class="button" id="tmv-add-tag-btn">Add Tag</button>
                </div>
                <div class="tmv-tags-display" id="tmv-tags-list">
                    <?php
                    $tags = get_option('tmv_application_tags', array());
                    foreach ($tags as $tag):
                    ?>
                        <span class="tmv-tag-badge"><?php echo esc_html($tag); ?> <button class="tmv-remove-tag" data-tag="<?php echo esc_attr($tag); ?>">&times;</button></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Application Comments Thread -->
        <div class="tmv-section-box">
            <h3>Application Comments</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Application ID:</label>
                    <input type="number" id="tmv-comment-app-id" placeholder="Post ID" />
                </div>
                <textarea id="tmv-comment-text" rows="3" placeholder="Write a comment..." style="width:100%;margin-top:8px;"></textarea>
                <button class="button button-primary" id="tmv-add-comment-btn" style="margin-top:8px;">Add Comment</button>
            </div>
            <div id="tmv-comments-list" style="margin-top:10px;"></div>
        </div>
        
        <!-- Quick Action Buttons -->
        <div class="tmv-section-box">
            <h3>Quick Actions</h3>
            <div class="tmv-quick-links">
                <button class="button" id="tmv-print-app-btn">Print Application</button>
                <button class="button" id="tmv-email-applicant-btn">Email Applicant</button>
            </div>
        </div>
        
        <!-- Application Reminder Scheduling -->
        <div class="tmv-section-box">
            <h3>Schedule Reminder</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Application ID:</label>
                    <input type="number" id="tmv-reminder-app-id" placeholder="Post ID" />
                </div>
                <div class="tmv-field-inline">
                    <label>Reminder Date:</label>
                    <input type="date" id="tmv-reminder-date" />
                </div>
                <div class="tmv-field-inline">
                    <label>Message:</label>
                    <input type="text" id="tmv-reminder-message" placeholder="Reminder message" style="width:300px;" />
                </div>
                <button class="button button-primary" id="tmv-schedule-reminder-btn" style="margin-top:8px;">Schedule Reminder</button>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_certificates() {
        global $wpdb;
        ?>
        <div class="tmv-tab-header">
            <h2>Certificate Management</h2>
            <div class="tmv-tab-actions">
                <button class="button button-primary" id="tmv-batch-generate-btn">Batch Generate Certificates</button>
            </div>
        </div>
        
        <h3>Certificate Template Settings</h3>
        <div class="tmv-form-section">
            <div class="tmv-field-inline">
                <label>Default Signatory:</label>
                <input type="text" id="tmv-cert-signatory" value="<?php echo esc_attr(get_option('tmv_default_signatory', '')); ?>" />
            </div>
            <div class="tmv-field-inline">
                <label>Default Designation:</label>
                <input type="text" id="tmv-cert-designation" value="<?php echo esc_attr(get_option('tmv_default_designation', '')); ?>" />
            </div>
        </div>
        
        <h3>Certificates List</h3>
        <table class="tmv-data-table">
            <thead>
                <tr>
                    <th>TM Number</th>
                    <th>Owner</th>
                    <th>Generated Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $certs = $wpdb->get_results(
                "SELECT p.ID, p.post_date FROM {$wpdb->posts} p 
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                 WHERE p.post_type = 'trademark_app' AND pm.meta_key = 'tmv_certificate_jpg' AND pm.meta_value != '' 
                 ORDER BY p.post_date DESC LIMIT 50"
            );
            foreach ($certs as $cert):
                $revoked = get_post_meta($cert->ID, 'tmv_revoked', true);
                $expiry = get_post_meta($cert->ID, 'tmv_expiry_date', true);
                $expired = ($expiry && strtotime($expiry) < time());
                $cert_status = $revoked ? 'Revoked' : ($expired ? 'Expired' : 'Active');
                $cert_status_class = $revoked ? 'rejected' : ($expired ? 'pending' : 'approved');
                $cert_url = wp_get_attachment_url(get_post_meta($cert->ID, 'tmv_certificate_jpg', true));
            ?>
                <tr>
                    <td><?php echo esc_html(get_post_meta($cert->ID, 'tmv_tm_number', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($cert->ID, 'tmv_owner', true)); ?></td>
                    <td><?php echo esc_html(date('Y-m-d', strtotime($cert->post_date))); ?></td>
                    <td><span class="tmv-status-badge tmv-status-<?php echo esc_attr($cert_status_class); ?>"><?php echo esc_html($cert_status); ?></span></td>
                    <td>
                        <?php if ($cert_url): ?>
                        <a href="<?php echo esc_url($cert_url); ?>" class="button button-small" target="_blank">Download</a>
                        <?php endif; ?>
                        <?php if (!$revoked): ?>
                        <button class="button button-small tmv-revoke-cert-btn" data-id="<?php echo esc_attr($cert->ID); ?>">Revoke</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <h3>Expiry Tracker</h3>
        <div class="tmv-expiry-groups">
            <?php
            $ranges = array(
                '30 days' => date('Y-m-d', strtotime('+30 days')),
                '60 days' => date('Y-m-d', strtotime('+60 days')),
                '90 days' => date('Y-m-d', strtotime('+90 days')),
            );
            foreach ($ranges as $label => $end_date):
                $count = intval($wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_expiry_date' AND meta_value != '' AND meta_value <= %s AND meta_value >= %s",
                    $end_date, date('Y-m-d')
                )));
            ?>
                <div class="tmv-expiry-card">
                    <h4>Expiring within <?php echo esc_html($label); ?></h4>
                    <span class="tmv-expiry-count"><?php echo $count; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Certificate Watermark Settings -->
        <div class="tmv-section-box">
            <h3>Certificate Watermark Settings</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Watermark Text:</label>
                    <input type="text" id="tmv-cert-watermark-text" value="<?php echo esc_attr(get_option('tmv_cert_watermark_text', 'CERTIFIED')); ?>" />
                </div>
                <div class="tmv-field-inline">
                    <label>Opacity (0-100):</label>
                    <input type="range" id="tmv-cert-watermark-opacity" min="0" max="100" value="<?php echo esc_attr(get_option('tmv_cert_watermark_opacity', '20')); ?>" />
                    <span id="tmv-opacity-value"><?php echo esc_html(get_option('tmv_cert_watermark_opacity', '20')); ?>%</span>
                </div>
                <button class="button button-primary" id="tmv-save-watermark-btn">Save Watermark Settings</button>
            </div>
        </div>
        
        <!-- Custom Certificate Numbering -->
        <div class="tmv-section-box">
            <h3>Custom Certificate Numbering Format</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Format:</label>
                    <input type="text" id="tmv-cert-numbering-format" value="<?php echo esc_attr(get_option('tmv_cert_numbering_format', 'CERT-{YEAR}-{NUMBER}')); ?>" style="width:300px;" />
                </div>
                <p class="description">Variables: {YEAR}, {MONTH}, {NUMBER}, {CLASS}, {OWNER_INITIALS}</p>
                <button class="button button-primary" id="tmv-save-numbering-btn">Save Numbering Format</button>
            </div>
        </div>
        
        <!-- Certificate Authenticity Seal -->
        <div class="tmv-section-box">
            <h3>Certificate Authenticity Seal</h3>
            <div class="tmv-form-section">
                <label>
                    <input type="checkbox" id="tmv-cert-seal-enabled" <?php checked(get_option('tmv_cert_seal_enabled', '0'), '1'); ?> />
                    Enable Authenticity Seal on Certificates
                </label>
                <div class="tmv-field-inline" style="margin-top:10px;">
                    <label>Custom Seal Image:</label>
                    <input type="hidden" id="tmv-cert-seal-image" value="<?php echo esc_attr(get_option('tmv_cert_seal_image', '')); ?>" />
                    <button type="button" class="button tmv-upload-btn" data-target="tmv-cert-seal-image" data-type="image">Upload Seal Image</button>
                </div>
            </div>
        </div>
        
        <!-- Certificate Design Preview -->
        <div class="tmv-section-box">
            <h3>Certificate Design Preview</h3>
            <div class="tmv-cert-preview-box" style="border:2px dashed #ccc;padding:40px;text-align:center;min-height:200px;background:#f9f9f9;">
                <p style="color:#666;">Certificate preview will appear here based on current settings.</p>
                <div style="border:1px solid #ddd;padding:20px;margin:10px;background:white;">
                    <h4 style="margin:0;">Certificate of Registration of Trademark</h4>
                    <p style="color:#888;">Watermark: <?php echo esc_html(get_option('tmv_cert_watermark_text', 'CERTIFIED')); ?></p>
                    <p style="color:#888;">Format: <?php echo esc_html(get_option('tmv_cert_numbering_format', 'CERT-{YEAR}-{NUMBER}')); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Certificate Download Count Tracker -->
        <div class="tmv-section-box">
            <h3>Certificate Download Statistics</h3>
            <table class="tmv-data-table">
                <thead><tr><th>TM Number</th><th>Owner</th><th>Downloads</th><th>Last Downloaded</th></tr></thead>
                <tbody>
                <?php
                $download_stats = get_option('tmv_cert_download_stats', array());
                if (!empty($download_stats)):
                    foreach (array_slice($download_stats, 0, 10) as $stat):
                ?>
                    <tr>
                        <td><?php echo esc_html($stat['tm_number'] ?? ''); ?></td>
                        <td><?php echo esc_html($stat['owner'] ?? ''); ?></td>
                        <td><?php echo intval($stat['count'] ?? 0); ?></td>
                        <td><?php echo esc_html($stat['last_download'] ?? 'Never'); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Multi-Language Certificate Support -->
        <div class="tmv-section-box">
            <h3>Multi-Language Certificate Support</h3>
            <div class="tmv-form-section">
                <p class="description">Enable certificate generation in multiple languages:</p>
                <?php
                $supported_langs = get_option('tmv_cert_languages', array('en'));
                $available_langs = array('en' => 'English', 'bn' => 'Bangla', 'ar' => 'Arabic', 'hi' => 'Hindi', 'zh' => 'Chinese', 'es' => 'Spanish', 'fr' => 'French');
                foreach ($available_langs as $code => $name):
                ?>
                    <label style="display:inline-block;margin-right:15px;">
                        <input type="checkbox" class="tmv-lang-checkbox" value="<?php echo esc_attr($code); ?>" <?php checked(in_array($code, $supported_langs, true)); ?> />
                        <?php echo esc_html($name); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_users() {
        $users = get_users(array('number' => 50, 'orderby' => 'registered', 'order' => 'DESC'));
        $user_counts = count_users();
        ?>
        <div class="tmv-tab-header">
            <h2>User Management</h2>
            <div class="tmv-tab-actions">
                <input type="text" id="tmv-user-search" class="tmv-search-input" placeholder="Search users..." />
            </div>
        </div>
        
        <div class="tmv-stats-grid tmv-stats-grid-small">
            <div class="tmv-stat-card"><h3><?php echo intval($user_counts['total_users']); ?></h3><p>Total Users</p></div>
            <div class="tmv-stat-card"><h3><?php echo intval($user_counts['avail_roles']['administrator'] ?? 0); ?></h3><p>Admins</p></div>
            <div class="tmv-stat-card"><h3><?php echo intval($user_counts['avail_roles']['subscriber'] ?? 0); ?></h3><p>Subscribers</p></div>
            <div class="tmv-stat-card">
                <h3><?php
                    global $wpdb;
                    $banned_count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'tmv_user_banned' AND meta_value = '1'"));
                    echo $banned_count;
                ?></h3>
                <p>Banned</p>
            </div>
        </div>
        
        <table class="tmv-data-table" id="tmv-users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Applications</th>
                    <th>Last Activity</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user):
                $app_count = count_user_posts($user->ID, 'trademark_app');
                $last_login = get_user_meta($user->ID, 'last_login', true);
                $is_banned = get_user_meta($user->ID, 'tmv_user_banned', true);
                $role = !empty($user->roles) ? $user->roles[0] : 'none';
            ?>
                <tr data-user-id="<?php echo esc_attr($user->ID); ?>" data-username="<?php echo esc_attr(strtolower($user->user_login)); ?>">
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html(date('Y-m-d', strtotime($user->user_registered))); ?></td>
                    <td><?php echo intval($app_count); ?></td>
                    <td><?php echo $last_login ? esc_html($last_login) : 'Never'; ?></td>
                    <td><?php echo esc_html(ucfirst($role)); ?></td>
                    <td>
                        <?php if ($is_banned): ?>
                            <span class="tmv-status-badge tmv-status-rejected">Banned</span>
                        <?php else: ?>
                            <span class="tmv-status-badge tmv-status-approved">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($is_banned): ?>
                            <button class="button button-small tmv-ban-user-btn" data-id="<?php echo esc_attr($user->ID); ?>" data-action="unban">Unban</button>
                        <?php else: ?>
                            <button class="button button-small tmv-ban-user-btn" data-id="<?php echo esc_attr($user->ID); ?>" data-action="ban">Ban</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- User Groups/Departments -->
        <div class="tmv-section-box">
            <h3>User Groups / Departments</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Create Group:</label>
                    <input type="text" id="tmv-new-group-name" placeholder="Group name" />
                    <button class="button" id="tmv-create-group-btn">Create Group</button>
                </div>
            </div>
            <table class="tmv-data-table">
                <thead><tr><th>Group Name</th><th>Members</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $groups = get_option('tmv_user_groups', array());
                foreach ($groups as $gidx => $group):
                ?>
                    <tr>
                        <td><?php echo esc_html($group['name'] ?? ''); ?></td>
                        <td><?php echo intval($group['member_count'] ?? 0); ?></td>
                        <td><?php echo esc_html($group['created'] ?? ''); ?></td>
                        <td><button class="button button-small">Manage</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Import/Export Users -->
        <div class="tmv-section-box">
            <h3>Import / Export Users</h3>
            <div class="tmv-quick-links">
                <button class="button" id="tmv-import-users-btn">Import Users from CSV</button>
                <button class="button" id="tmv-export-users-btn">Export Users to CSV</button>
            </div>
            <div id="tmv-import-users-form" style="display:none;margin-top:10px;">
                <input type="file" id="tmv-users-csv-file" accept=".csv" />
                <button class="button button-primary" id="tmv-process-import-btn">Process Import</button>
            </div>
        </div>
        
        <!-- Send Bulk Email -->
        <div class="tmv-section-box">
            <h3>Send Bulk Email</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>User Group:</label>
                    <select id="tmv-bulk-email-group">
                        <option value="all">All Users</option>
                        <option value="subscribers">Subscribers</option>
                        <option value="administrators">Administrators</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo esc_attr($group['name'] ?? ''); ?>"><?php echo esc_html($group['name'] ?? ''); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tmv-field-inline">
                    <label>Subject:</label>
                    <input type="text" id="tmv-bulk-email-subject" style="width:400px;" placeholder="Email subject" />
                </div>
                <textarea id="tmv-bulk-email-message" rows="4" placeholder="Email message..." style="width:100%;margin-top:8px;"></textarea>
                <button class="button button-primary" id="tmv-send-bulk-email-btn" style="margin-top:8px;">Send Bulk Email</button>
            </div>
        </div>
        
        <!-- User Activity Timeline -->
        <div class="tmv-section-box">
            <h3>User Activity Timeline</h3>
            <table class="tmv-data-table">
                <thead><tr><th>User</th><th>Action</th><th>Details</th><th>Date/Time</th></tr></thead>
                <tbody>
                <?php
                $user_activity = get_option('tmv_user_activity_log', array());
                foreach (array_slice(array_reverse($user_activity), 0, 20) as $activity):
                ?>
                    <tr>
                        <td><?php echo esc_html($activity['user'] ?? ''); ?></td>
                        <td><?php echo esc_html($activity['action'] ?? ''); ?></td>
                        <td><?php echo esc_html($activity['details'] ?? ''); ?></td>
                        <td><?php echo esc_html($activity['time'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- User Permission Matrix -->
        <div class="tmv-section-box">
            <h3>User Permission Matrix</h3>
            <table class="tmv-data-table">
                <thead>
                    <tr>
                        <th>Capability</th>
                        <th>Administrator</th>
                        <th>Editor</th>
                        <th>Subscriber</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>View Applications</td><td>&#9989;</td><td>&#9989;</td><td>&#10060;</td></tr>
                    <tr><td>Approve Applications</td><td>&#9989;</td><td>&#10060;</td><td>&#10060;</td></tr>
                    <tr><td>Generate Certificates</td><td>&#9989;</td><td>&#10060;</td><td>&#10060;</td></tr>
                    <tr><td>Manage Users</td><td>&#9989;</td><td>&#10060;</td><td>&#10060;</td></tr>
                    <tr><td>View Reports</td><td>&#9989;</td><td>&#9989;</td><td>&#10060;</td></tr>
                    <tr><td>Submit Applications</td><td>&#9989;</td><td>&#9989;</td><td>&#9989;</td></tr>
                    <tr><td>Download Certificates</td><td>&#9989;</td><td>&#9989;</td><td>&#9989;</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Invite New User -->
        <div class="tmv-section-box">
            <h3>Invite New User</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Email:</label>
                    <input type="email" id="tmv-invite-email" placeholder="user@example.com" />
                </div>
                <div class="tmv-field-inline">
                    <label>Role:</label>
                    <select id="tmv-invite-role">
                        <option value="subscriber">Subscriber</option>
                        <option value="editor">Editor</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                <button class="button button-primary" id="tmv-invite-user-btn">Send Invitation</button>
            </div>
        </div>
        
        <!-- User Login History -->
        <div class="tmv-section-box">
            <h3>User Login History</h3>
            <table class="tmv-data-table">
                <thead><tr><th>User</th><th>Login Time</th><th>IP Address</th><th>Browser</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                $login_history = get_option('tmv_login_history', array());
                foreach (array_slice(array_reverse($login_history), 0, 20) as $log):
                ?>
                    <tr>
                        <td><?php echo esc_html($log['user'] ?? ''); ?></td>
                        <td><?php echo esc_html($log['time'] ?? ''); ?></td>
                        <td><?php echo esc_html($log['ip'] ?? ''); ?></td>
                        <td><?php echo esc_html($log['browser'] ?? ''); ?></td>
                        <td><span class="tmv-status-badge tmv-status-<?php echo ($log['status'] ?? '') === 'success' ? 'approved' : 'rejected'; ?>"><?php echo esc_html(ucfirst($log['status'] ?? '')); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    private static function render_tab_analytics() {
        global $wpdb;
        $total_count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_status'"));
        $approved = intval(self::count_by_status('approved'));
        $approval_rate = $total_count > 0 ? round(($approved / $total_count) * 100, 1) : 0;
        
        // Average processing time
        $avg_time = $wpdb->get_var(
            "SELECT AVG(DATEDIFF(pm2.meta_value, pm1.meta_value)) FROM {$wpdb->postmeta} pm1 
             INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id 
             WHERE pm1.meta_key = 'tmv_application_date' AND pm2.meta_key = 'tmv_approved_date' 
             AND pm1.meta_value != '' AND pm2.meta_value != ''"
        );
        $avg_days = $avg_time ? round(floatval($avg_time), 1) : 0;
        
        // Class distribution
        $class_dist = $wpdb->get_results(
            "SELECT meta_value as class_name, COUNT(*) as count FROM {$wpdb->postmeta} 
             WHERE meta_key = 'tmv_class' AND meta_value != '' GROUP BY meta_value ORDER BY count DESC"
        );
        
        // Top 10 applicants
        $top_applicants = $wpdb->get_results(
            "SELECT meta_value as owner, COUNT(*) as count FROM {$wpdb->postmeta} 
             WHERE meta_key = 'tmv_owner' AND meta_value != '' GROUP BY meta_value ORDER BY count DESC LIMIT 10"
        );
        
        // Monthly trend (last 12 months)
        $monthly_trend = $wpdb->get_results(
            "SELECT DATE_FORMAT(post_date, '%Y-%m') as month, COUNT(*) as count 
             FROM {$wpdb->posts} WHERE post_type = 'trademark_app' AND post_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
             GROUP BY month ORDER BY month DESC"
        );
        ?>
        <h2>Analytics</h2>
        
        <div class="tmv-stats-grid tmv-stats-grid-small">
            <div class="tmv-stat-card">
                <h3><?php echo $approval_rate; ?>%</h3>
                <p>Approval Rate</p>
                <div class="tmv-progress-bar"><div class="tmv-progress-fill" style="width:<?php echo $approval_rate; ?>%"></div></div>
            </div>
            <div class="tmv-stat-card">
                <h3><?php echo $avg_days; ?> days</h3>
                <p>Avg Processing Time</p>
            </div>
        </div>
        
        <div class="tmv-analytics-sections">
            <div class="tmv-section-box">
                <h3>Class Distribution</h3>
                <table class="tmv-data-table">
                    <thead><tr><th>Class</th><th>Count</th></tr></thead>
                    <tbody>
                    <?php foreach ($class_dist as $row): ?>
                        <tr><td><?php echo esc_html($row->class_name); ?></td><td><?php echo intval($row->count); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tmv-section-box">
                <h3>Top 10 Applicants</h3>
                <table class="tmv-data-table">
                    <thead><tr><th>Owner</th><th>Applications</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_applicants as $row): ?>
                        <tr><td><?php echo esc_html($row->owner); ?></td><td><?php echo intval($row->count); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tmv-section-box">
                <h3>Monthly Application Trend</h3>
                <table class="tmv-data-table">
                    <thead><tr><th>Month</th><th>Applications</th></tr></thead>
                    <tbody>
                    <?php foreach ($monthly_trend as $row): ?>
                        <tr><td><?php echo esc_html($row->month); ?></td><td><?php echo intval($row->count); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Bar Chart for Monthly Trends -->
        <div class="tmv-section-box">
            <h3>Monthly Trends (Visual)</h3>
            <div class="tmv-bar-chart">
                <?php
                $max_count = 1;
                foreach ($monthly_trend as $row) {
                    if (intval($row->count) > $max_count) $max_count = intval($row->count);
                }
                foreach (array_reverse($monthly_trend) as $row):
                    $bar_height = ($max_count > 0) ? round((intval($row->count) / $max_count) * 100) : 0;
                ?>
                    <div class="tmv-bar-item">
                        <div class="tmv-bar" style="height:<?php echo intval($bar_height); ?>%;"></div>
                        <span class="tmv-bar-label"><?php echo esc_html(substr($row->month, 5)); ?></span>
                        <span class="tmv-bar-value"><?php echo intval($row->count); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Year-over-Year Comparison -->
        <div class="tmv-section-box">
            <h3>Year-over-Year Comparison</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Year</th><th>Total Applications</th><th>Approved</th><th>Rejected</th><th>Growth</th></tr></thead>
                <tbody>
                <?php
                $yearly_data = $wpdb->get_results(
                    "SELECT YEAR(post_date) as year, COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'trademark_app' GROUP BY year ORDER BY year DESC LIMIT 5"
                );
                $prev_count = 0;
                foreach ($yearly_data as $yidx => $yrow):
                    $growth = ($prev_count > 0) ? round((intval($yrow->count) - $prev_count) / $prev_count * 100, 1) : 0;
                    $prev_count = intval($yrow->count);
                ?>
                    <tr>
                        <td><?php echo esc_html($yrow->year); ?></td>
                        <td><?php echo intval($yrow->count); ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td><?php echo ($yidx > 0) ? esc_html($growth . '%') : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Peak Hours Analysis -->
        <div class="tmv-section-box">
            <h3>Peak Hours Analysis</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Hour</th><th>Applications Submitted</th><th>Activity Level</th></tr></thead>
                <tbody>
                <?php
                $peak_hours = $wpdb->get_results(
                    "SELECT HOUR(post_date) as hour, COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'trademark_app' GROUP BY hour ORDER BY count DESC LIMIT 10"
                );
                foreach ($peak_hours as $ph):
                ?>
                    <tr>
                        <td><?php echo esc_html(sprintf('%02d:00 - %02d:59', $ph->hour, $ph->hour)); ?></td>
                        <td><?php echo intval($ph->count); ?></td>
                        <td><div class="tmv-progress-bar" style="width:200px;display:inline-block;"><div class="tmv-progress-fill" style="width:<?php echo min(100, intval($ph->count) * 10); ?>%"></div></div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Geographic Data -->
        <div class="tmv-section-box">
            <h3>Geographic Distribution</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Region/Country</th><th>Applications</th><th>Percentage</th></tr></thead>
                <tbody>
                <?php
                $geo_data = get_option('tmv_geographic_stats', array());
                if (!empty($geo_data)):
                    foreach ($geo_data as $geo):
                ?>
                    <tr>
                        <td><?php echo esc_html($geo['region'] ?? ''); ?></td>
                        <td><?php echo intval($geo['count'] ?? 0); ?></td>
                        <td><?php echo esc_html($geo['percentage'] ?? '0'); ?>%</td>
                    </tr>
                <?php endforeach;
                else: ?>
                    <tr><td colspan="3">No geographic data available yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Custom Report Builder -->
        <div class="tmv-section-box">
            <h3>Custom Report Builder</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Date From:</label>
                    <input type="date" id="tmv-report-date-from" />
                </div>
                <div class="tmv-field-inline">
                    <label>Date To:</label>
                    <input type="date" id="tmv-report-date-to" />
                </div>
                <div style="margin-top:10px;">
                    <p><strong>Metrics to include:</strong></p>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="applications" checked /> Applications</label>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="approvals" checked /> Approvals</label>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="rejections" /> Rejections</label>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="certificates" /> Certificates</label>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="verifications" /> Verifications</label>
                    <label style="margin-right:15px;"><input type="checkbox" class="tmv-report-metric" value="revenue" /> Revenue</label>
                </div>
                <button class="button button-primary" id="tmv-generate-report-btn" style="margin-top:10px;">Generate Report</button>
                <button class="button" id="tmv-export-pdf-btn" style="margin-top:10px;">Export as PDF</button>
            </div>
        </div>
        
        <!-- Report Scheduling -->
        <div class="tmv-section-box">
            <h3>Report Scheduling</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Frequency:</label>
                    <select id="tmv-report-frequency">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="tmv-field-inline">
                    <label>Email Recipient:</label>
                    <input type="email" id="tmv-report-email" placeholder="admin@example.com" />
                </div>
                <button class="button button-primary" id="tmv-schedule-report-btn" style="margin-top:8px;">Schedule Report</button>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_communication() {
        $approved_tpl = get_option('tmv_email_approved_tpl', 'Dear {owner}, your trademark {tm_number} has been approved.');
        $rejected_tpl = get_option('tmv_email_rejected_tpl', 'Dear {owner}, your trademark {tm_number} has been rejected.');
        $submitted_tpl = get_option('tmv_email_submitted_tpl', 'Dear {owner}, your trademark application {tm_number} has been submitted.');
        $announcements = get_option('tmv_announcements', array());
        ?>
        <h2>Communication</h2>
        
        <div class="tmv-section-box">
            <h3>Email Templates</h3>
            <p class="description">Available variables: {tm_number}, {owner}, {status}, {class}, {date}</p>
            
            <div class="tmv-form-section">
                <label><strong>Approved Email Template:</strong></label>
                <textarea id="tmv-tpl-approved" class="tmv-template-editor" rows="4"><?php echo esc_textarea($approved_tpl); ?></textarea>
            </div>
            <div class="tmv-form-section">
                <label><strong>Rejected Email Template:</strong></label>
                <textarea id="tmv-tpl-rejected" class="tmv-template-editor" rows="4"><?php echo esc_textarea($rejected_tpl); ?></textarea>
            </div>
            <div class="tmv-form-section">
                <label><strong>Submitted Email Template:</strong></label>
                <textarea id="tmv-tpl-submitted" class="tmv-template-editor" rows="4"><?php echo esc_textarea($submitted_tpl); ?></textarea>
            </div>
            <button class="button button-primary" id="tmv-save-templates-btn">Save Templates</button>
        </div>
        
        <div class="tmv-section-box">
            <h3>Announcements</h3>
            <div class="tmv-form-section">
                <input type="text" id="tmv-announcement-title" placeholder="Announcement Title" style="width:100%;margin-bottom:8px;" />
                <textarea id="tmv-announcement-message" rows="3" placeholder="Announcement message..." style="width:100%;"></textarea>
                <button class="button button-primary" id="tmv-add-announcement-btn" style="margin-top:8px;">Add Announcement</button>
            </div>
            
            <div id="tmv-announcements-list">
            <?php if (!empty($announcements)): foreach ($announcements as $idx => $ann): ?>
                <div class="tmv-announcement-card" data-index="<?php echo intval($idx); ?>">
                    <h4><?php echo esc_html($ann['title'] ?? ''); ?></h4>
                    <p><?php echo esc_html($ann['message'] ?? ''); ?></p>
                    <small><?php echo esc_html($ann['date'] ?? ''); ?></small>
                    <button class="button button-small tmv-delete-announcement-btn" data-index="<?php echo intval($idx); ?>">Delete</button>
                </div>
            <?php endforeach; endif; ?>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_security() {
        $failed_logins = get_option('tmv_failed_logins', array());
        $blacklist = get_option('tmv_ip_blacklist', array());
        $whitelist = get_option('tmv_ip_whitelist', array());
        $tfa_enabled = get_option('tmv_2fa_enabled', '0');
        $audit_trail = get_option('tmv_audit_trail', array());
        ?>
        <h2>Security</h2>
        
        <div class="tmv-section-box">
            <h3>Failed Login Attempts</h3>
            <button class="button" id="tmv-clear-failed-logins-btn">Clear Failed Logins</button>
            <table class="tmv-data-table">
                <thead><tr><th>Username</th><th>IP Address</th><th>Time</th></tr></thead>
                <tbody>
                <?php if (!empty($failed_logins)): foreach (array_slice($failed_logins, -20) as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['username'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['ip'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['time'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="tmv-security-columns">
            <div class="tmv-section-box">
                <h3>IP Blacklist</h3>
                <div class="tmv-ip-input-row">
                    <input type="text" id="tmv-blacklist-ip-input" placeholder="Enter IP address" />
                    <button class="button" id="tmv-add-blacklist-btn">Add to Blacklist</button>
                </div>
                <ul class="tmv-ip-list" id="tmv-blacklist-list">
                <?php foreach ($blacklist as $ip): ?>
                    <li><?php echo esc_html($ip); ?> <button class="button button-small tmv-remove-ip-btn" data-list="blacklist" data-ip="<?php echo esc_attr($ip); ?>">Remove</button></li>
                <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="tmv-section-box">
                <h3>IP Whitelist</h3>
                <div class="tmv-ip-input-row">
                    <input type="text" id="tmv-whitelist-ip-input" placeholder="Enter IP address" />
                    <button class="button" id="tmv-add-whitelist-btn">Add to Whitelist</button>
                </div>
                <ul class="tmv-ip-list" id="tmv-whitelist-list">
                <?php foreach ($whitelist as $ip): ?>
                    <li><?php echo esc_html($ip); ?> <button class="button button-small tmv-remove-ip-btn" data-list="whitelist" data-ip="<?php echo esc_attr($ip); ?>">Remove</button></li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="tmv-section-box">
            <h3>Two-Factor Authentication</h3>
            <label>
                <input type="checkbox" id="tmv-2fa-toggle" <?php checked($tfa_enabled, '1'); ?> />
                Enable Two-Factor Authentication for admin accounts
            </label>
        </div>
        
        <div class="tmv-section-box">
            <h3>Activity Audit Trail</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Action</th><th>User</th><th>Time</th><th>Details</th></tr></thead>
                <tbody>
                <?php if (!empty($audit_trail)): foreach (array_slice(array_reverse($audit_trail), 0, 20) as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['action'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['user'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['time'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['details'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Password Policy Settings -->
        <div class="tmv-section-box">
            <h3>Password Policy Settings</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Minimum Password Length:</label>
                    <input type="number" id="tmv-pw-min-length" min="6" max="32" value="<?php echo intval(get_option('tmv_pw_min_length', 8)); ?>" />
                </div>
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-pw-require-uppercase" <?php checked(get_option('tmv_pw_require_uppercase', '1'), '1'); ?> />
                    Require Uppercase Letters
                </label>
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-pw-require-numbers" <?php checked(get_option('tmv_pw_require_numbers', '1'), '1'); ?> />
                    Require Numbers
                </label>
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-pw-require-special" <?php checked(get_option('tmv_pw_require_special', '0'), '1'); ?> />
                    Require Special Characters
                </label>
                <button class="button button-primary" id="tmv-save-password-policy-btn" style="margin-top:8px;">Save Password Policy</button>
            </div>
        </div>
        
        <!-- Session Timeout -->
        <div class="tmv-section-box">
            <h3>Session Timeout Configuration</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Session Timeout (minutes):</label>
                    <input type="number" id="tmv-session-timeout" min="5" max="1440" value="<?php echo intval(get_option('tmv_session_timeout', 60)); ?>" />
                </div>
                <button class="button button-primary" id="tmv-save-session-settings-btn" style="margin-top:8px;">Save Session Settings</button>
            </div>
        </div>
        
        <!-- Login Attempt Lockout -->
        <div class="tmv-section-box">
            <h3>Login Attempt Lockout</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Max Failed Attempts:</label>
                    <input type="number" id="tmv-lockout-attempts" min="3" max="20" value="<?php echo intval(get_option('tmv_lockout_attempts', 5)); ?>" />
                </div>
                <div class="tmv-field-inline">
                    <label>Lockout Duration (minutes):</label>
                    <input type="number" id="tmv-lockout-duration" min="5" max="1440" value="<?php echo intval(get_option('tmv_lockout_duration', 30)); ?>" />
                </div>
            </div>
        </div>
        
        <!-- CAPTCHA Settings -->
        <div class="tmv-section-box">
            <h3>CAPTCHA Settings</h3>
            <div class="tmv-form-section">
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-captcha-enabled" <?php checked(get_option('tmv_captcha_enabled', '0'), '1'); ?> />
                    Enable CAPTCHA on Login/Registration
                </label>
                <div class="tmv-field-inline">
                    <label>Site Key:</label>
                    <input type="text" id="tmv-captcha-site-key" value="<?php echo esc_attr(get_option('tmv_captcha_site_key', '')); ?>" style="width:400px;" />
                </div>
                <div class="tmv-field-inline">
                    <label>Secret Key:</label>
                    <input type="password" id="tmv-captcha-secret-key" value="<?php echo esc_attr(get_option('tmv_captcha_secret_key', '')); ?>" style="width:400px;" />
                </div>
            </div>
        </div>
        
        <!-- CORS Configuration -->
        <div class="tmv-section-box">
            <h3>CORS Configuration</h3>
            <div class="tmv-form-section">
                <label>Allowed Origins (one per line):</label>
                <textarea id="tmv-cors-origins" rows="4" style="width:100%;"><?php echo esc_textarea(get_option('tmv_cors_origins', '')); ?></textarea>
            </div>
        </div>
        
        <!-- SSL Certificate Status -->
        <div class="tmv-section-box">
            <h3>SSL Certificate Status</h3>
            <div class="tmv-health-grid">
                <div class="tmv-health-item <?php echo is_ssl() ? 'tmv-health-ok' : 'tmv-health-warn'; ?>">
                    <span class="tmv-health-indicator"></span>
                    <strong>SSL Status:</strong> <?php echo is_ssl() ? 'Active (HTTPS)' : 'Not Active (HTTP)'; ?>
                </div>
            </div>
        </div>
        
        <!-- File Upload Security -->
        <div class="tmv-section-box">
            <h3>File Upload Security Settings</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Max File Size (MB):</label>
                    <input type="number" id="tmv-max-file-size" min="1" max="100" value="<?php echo intval(get_option('tmv_max_file_size', 5)); ?>" />
                </div>
                <div class="tmv-field-inline">
                    <label>Allowed Extensions (comma separated):</label>
                    <input type="text" id="tmv-allowed-extensions" value="<?php echo esc_attr(get_option('tmv_allowed_extensions', 'jpg,jpeg,png,pdf,doc,docx')); ?>" style="width:400px;" />
                </div>
            </div>
        </div>
        
        <!-- API Rate Limiting -->
        <div class="tmv-section-box">
            <h3>API Rate Limiting</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Requests Per Minute:</label>
                    <input type="number" id="tmv-rate-limit" min="10" max="1000" value="<?php echo intval(get_option('tmv_rate_limit', 60)); ?>" />
                </div>
            </div>
        </div>
        <?php
    }
    
    private static function render_tab_content() {
        $faqs = get_option('tmv_faqs', array());
        $contact_phone = get_option('tmv_contact_phone', '');
        $contact_email = get_option('tmv_contact_email', '');
        $contact_address = get_option('tmv_contact_address', '');
        $social_facebook = get_option('tmv_social_facebook', '');
        $social_twitter = get_option('tmv_social_twitter', '');
        $social_linkedin = get_option('tmv_social_linkedin', '');
        $social_youtube = get_option('tmv_social_youtube', '');
        ?>
        <h2>Content Management</h2>
        
        <div class="tmv-section-box">
            <h3>Quick Links</h3>
            <div class="tmv-quick-links">
                <a href="<?php echo admin_url('edit.php'); ?>" class="button">Manage Posts</a>
                <a href="<?php echo admin_url('post-new.php'); ?>" class="button">Add New Post</a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" class="button">Categories</a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=post_tag'); ?>" class="button">Tags</a>
            </div>
        </div>
        
        <div class="tmv-section-box">
            <h3>FAQ Management</h3>
            <div class="tmv-form-section">
                <input type="text" id="tmv-faq-question" placeholder="Question" style="width:100%;margin-bottom:8px;" />
                <textarea id="tmv-faq-answer" rows="3" placeholder="Answer..." style="width:100%;"></textarea>
                <button class="button button-primary" id="tmv-add-faq-btn" style="margin-top:8px;">Add FAQ</button>
            </div>
            <div id="tmv-faq-list">
            <?php if (!empty($faqs)): foreach ($faqs as $idx => $faq): ?>
                <div class="tmv-faq-item" data-index="<?php echo intval($idx); ?>">
                    <strong>Q: <?php echo esc_html($faq['question'] ?? ''); ?></strong>
                    <p>A: <?php echo esc_html($faq['answer'] ?? ''); ?></p>
                    <button class="button button-small tmv-delete-faq-btn" data-index="<?php echo intval($idx); ?>">Delete</button>
                </div>
            <?php endforeach; endif; ?>
            </div>
        </div>
        
        <div class="tmv-section-box">
            <h3>Contact Information</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline"><label>Phone:</label><input type="text" id="tmv-contact-phone" value="<?php echo esc_attr($contact_phone); ?>" /></div>
                <div class="tmv-field-inline"><label>Email:</label><input type="email" id="tmv-contact-email" value="<?php echo esc_attr($contact_email); ?>" /></div>
                <div class="tmv-field-inline"><label>Address:</label><input type="text" id="tmv-contact-address" value="<?php echo esc_attr($contact_address); ?>" /></div>
                <button class="button button-primary" id="tmv-save-contact-btn" style="margin-top:8px;">Save Contact Info</button>
            </div>
        </div>
        
        <div class="tmv-section-box">
            <h3>Social Media Links</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline"><label>Facebook:</label><input type="url" id="tmv-social-facebook" value="<?php echo esc_url($social_facebook); ?>" /></div>
                <div class="tmv-field-inline"><label>Twitter/X:</label><input type="url" id="tmv-social-twitter" value="<?php echo esc_url($social_twitter); ?>" /></div>
                <div class="tmv-field-inline"><label>LinkedIn:</label><input type="url" id="tmv-social-linkedin" value="<?php echo esc_url($social_linkedin); ?>" /></div>
                <div class="tmv-field-inline"><label>YouTube:</label><input type="url" id="tmv-social-youtube" value="<?php echo esc_url($social_youtube); ?>" /></div>
                <button class="button button-primary" id="tmv-save-social-btn" style="margin-top:8px;">Save Social Links</button>
            </div>
        </div>
        
        <!-- Page Builder Shortcuts -->
        <div class="tmv-section-box">
            <h3>Page Builder Shortcuts</h3>
            <div class="tmv-quick-links">
                <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="button">Create New Page</a>
                <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" class="button">Edit Pages</a>
                <a href="<?php echo admin_url('customize.php'); ?>" class="button">Customize Theme</a>
            </div>
        </div>
        
        <!-- Widget Manager -->
        <div class="tmv-section-box">
            <h3>Widget Manager</h3>
            <div class="tmv-quick-links">
                <a href="<?php echo admin_url('widgets.php'); ?>" class="button">Manage Widgets</a>
                <a href="<?php echo admin_url('customize.php?autofocus[panel]=widgets'); ?>" class="button">Widget Customizer</a>
            </div>
        </div>
        
        <!-- Menu Management -->
        <div class="tmv-section-box">
            <h3>Menu Management</h3>
            <div class="tmv-quick-links">
                <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button">Manage Menus</a>
                <a href="<?php echo admin_url('customize.php?autofocus[panel]=nav_menus'); ?>" class="button">Menu Customizer</a>
            </div>
        </div>
        
        <!-- Media Library Stats -->
        <div class="tmv-section-box">
            <h3>Media Library Statistics</h3>
            <?php
            $media_count = wp_count_posts('attachment');
            $total_media = intval($media_count->inherit ?? 0);
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['basedir'];
            $disk_usage = '0 MB';
            if (is_dir($upload_path)) {
                $size_bytes = intval(get_option('tmv_media_disk_usage', 0));
                $disk_usage = $size_bytes > 0 ? round($size_bytes / 1048576, 2) . ' MB' : 'Calculating...';
            }
            ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3><?php echo $total_media; ?></h3><p>Total Media Files</p></div>
                <div class="tmv-stat-card"><h3><?php echo esc_html($disk_usage); ?></h3><p>Disk Usage</p></div>
            </div>
        </div>
        
        <!-- Scheduled Posts -->
        <div class="tmv-section-box">
            <h3>Scheduled Posts</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Title</th><th>Author</th><th>Scheduled Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $scheduled = get_posts(array('post_status' => 'future', 'posts_per_page' => 10, 'orderby' => 'date', 'order' => 'ASC'));
                foreach ($scheduled as $spost):
                ?>
                    <tr>
                        <td><?php echo esc_html($spost->post_title); ?></td>
                        <td><?php echo esc_html(get_the_author_meta('display_name', $spost->post_author)); ?></td>
                        <td><?php echo esc_html(get_the_date('Y-m-d H:i', $spost->ID)); ?></td>
                        <td><a href="<?php echo get_edit_post_link($spost->ID); ?>" class="button button-small">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Draft Posts -->
        <div class="tmv-section-box">
            <h3>Draft Posts</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Title</th><th>Author</th><th>Last Modified</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $drafts = get_posts(array('post_status' => 'draft', 'posts_per_page' => 10, 'orderby' => 'modified', 'order' => 'DESC'));
                foreach ($drafts as $dpost):
                ?>
                    <tr>
                        <td><?php echo esc_html($dpost->post_title); ?></td>
                        <td><?php echo esc_html(get_the_author_meta('display_name', $dpost->post_author)); ?></td>
                        <td><?php echo esc_html(get_the_modified_date('Y-m-d H:i', $dpost->ID)); ?></td>
                        <td><a href="<?php echo get_edit_post_link($dpost->ID); ?>" class="button button-small">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Content Moderation Queue -->
        <div class="tmv-section-box">
            <h3>Content Moderation Queue</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Title</th><th>Author</th><th>Submitted</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $pending_posts = get_posts(array('post_status' => 'pending', 'posts_per_page' => 10, 'orderby' => 'date', 'order' => 'DESC'));
                foreach ($pending_posts as $ppost):
                ?>
                    <tr>
                        <td><?php echo esc_html($ppost->post_title); ?></td>
                        <td><?php echo esc_html(get_the_author_meta('display_name', $ppost->post_author)); ?></td>
                        <td><?php echo esc_html(get_the_date('Y-m-d', $ppost->ID)); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($ppost->ID); ?>" class="button button-small">Review</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Comment Management -->
        <div class="tmv-section-box">
            <h3>Recent Comments</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Author</th><th>Comment</th><th>Post</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                $comments = get_comments(array('number' => 10, 'orderby' => 'comment_date', 'order' => 'DESC'));
                foreach ($comments as $comment):
                ?>
                    <tr>
                        <td><?php echo esc_html($comment->comment_author); ?></td>
                        <td><?php echo esc_html(wp_trim_words($comment->comment_content, 10)); ?></td>
                        <td><?php echo esc_html(get_the_title($comment->comment_post_ID)); ?></td>
                        <td><?php echo esc_html($comment->comment_date); ?></td>
                        <td><span class="tmv-status-badge tmv-status-<?php echo $comment->comment_approved === '1' ? 'approved' : 'pending'; ?>"><?php echo $comment->comment_approved === '1' ? 'Approved' : 'Pending'; ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    private static function render_tab_verification() {
        $log = get_option('tmv_verification_log', array());
        $total_verifications = count($log);
        $successful = 0;
        $failed = 0;
        $today_count = 0;
        $today_date = date('Y-m-d');
        
        foreach ($log as $entry) {
            if (($entry['result'] ?? '') === 'found') {
                $successful++;
            } else {
                $failed++;
            }
            if (isset($entry['time']) && strpos($entry['time'], $today_date) === 0) {
                $today_count++;
            }
        }
        
        // Most verified trademarks
        global $wpdb;
        $most_verified = $wpdb->get_results(
            "SELECT meta_value as verify_count, post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = 'tmv_verify_count' AND meta_value != '' 
             ORDER BY CAST(meta_value AS UNSIGNED) DESC LIMIT 10"
        );
        ?>
        <h2>Verification</h2>
        
        <div class="tmv-stats-grid tmv-stats-grid-small">
            <div class="tmv-stat-card"><h3><?php echo $total_verifications; ?></h3><p>Total Verifications</p></div>
            <div class="tmv-stat-card tmv-stat-approved"><h3><?php echo $successful; ?></h3><p>Successful</p></div>
            <div class="tmv-stat-card tmv-stat-rejected"><h3><?php echo $failed; ?></h3><p>Failed</p></div>
            <div class="tmv-stat-card tmv-stat-today"><h3><?php echo $today_count; ?></h3><p>Today</p></div>
        </div>
        
        <div class="tmv-section-box">
            <h3>Recent Verification Log</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Code Searched</th><th>Result</th><th>IP</th><th>Timestamp</th></tr></thead>
                <tbody>
                <?php foreach (array_slice(array_reverse($log), 0, 30) as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['code'] ?? ''); ?></td>
                        <td><span class="tmv-status-badge tmv-status-<?php echo ($entry['result'] ?? '') === 'found' ? 'approved' : 'rejected'; ?>"><?php echo esc_html(ucfirst($entry['result'] ?? '')); ?></span></td>
                        <td><?php echo esc_html($entry['ip'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['time'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="tmv-section-box">
            <h3>Most Verified Trademarks</h3>
            <table class="tmv-data-table">
                <thead><tr><th>TM Number</th><th>Owner</th><th>Verification Count</th></tr></thead>
                <tbody>
                <?php foreach ($most_verified as $row): ?>
                    <tr>
                        <td><?php echo esc_html(get_post_meta($row->post_id, 'tmv_tm_number', true)); ?></td>
                        <td><?php echo esc_html(get_post_meta($row->post_id, 'tmv_owner', true)); ?></td>
                        <td><?php echo intval($row->verify_count); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Verification Success Rate Graph -->
        <div class="tmv-section-box">
            <h3>Verification Success Rate</h3>
            <?php
            $success_rate = $total_verifications > 0 ? round(($successful / $total_verifications) * 100, 1) : 0;
            $fail_rate = $total_verifications > 0 ? round(($failed / $total_verifications) * 100, 1) : 0;
            ?>
            <div class="tmv-distribution-bars">
                <div class="tmv-dist-item">
                    <span class="tmv-dist-label">Successful</span>
                    <div class="tmv-progress-bar"><div class="tmv-progress-fill tmv-fill-green" style="width:<?php echo esc_attr($success_rate); ?>%"></div></div>
                    <span class="tmv-dist-pct"><?php echo esc_html($success_rate); ?>%</span>
                </div>
                <div class="tmv-dist-item">
                    <span class="tmv-dist-label">Failed</span>
                    <div class="tmv-progress-bar"><div class="tmv-progress-fill tmv-fill-red" style="width:<?php echo esc_attr($fail_rate); ?>%"></div></div>
                    <span class="tmv-dist-pct"><?php echo esc_html($fail_rate); ?>%</span>
                </div>
            </div>
        </div>
        
        <!-- Average Daily Verifications -->
        <div class="tmv-section-box">
            <h3>Verification Statistics</h3>
            <?php
            $first_log_date = !empty($log) ? ($log[0]['time'] ?? '') : '';
            $days_active = 1;
            if ($first_log_date) {
                $days_active = max(1, intval((time() - strtotime($first_log_date)) / 86400));
            }
            $avg_daily = round($total_verifications / $days_active, 1);
            ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3><?php echo esc_html($avg_daily); ?></h3><p>Average Daily Verifications</p></div>
                <div class="tmv-stat-card"><h3><?php echo intval($days_active); ?></h3><p>Days Active</p></div>
            </div>
        </div>
        
        <!-- Top Verification IPs -->
        <div class="tmv-section-box">
            <h3>Top Verification IPs</h3>
            <table class="tmv-data-table">
                <thead><tr><th>IP Address</th><th>Verification Count</th><th>Last Verification</th></tr></thead>
                <tbody>
                <?php
                $ip_counts = array();
                $ip_last = array();
                foreach ($log as $entry) {
                    $ip = $entry['ip'] ?? 'unknown';
                    if (!isset($ip_counts[$ip])) $ip_counts[$ip] = 0;
                    $ip_counts[$ip]++;
                    $ip_last[$ip] = $entry['time'] ?? '';
                }
                arsort($ip_counts);
                $top_ips = array_slice($ip_counts, 0, 10, true);
                foreach ($top_ips as $ip => $cnt):
                ?>
                    <tr>
                        <td><?php echo esc_html($ip); ?></td>
                        <td><?php echo intval($cnt); ?></td>
                        <td><?php echo esc_html($ip_last[$ip] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Verification API Response Time -->
        <div class="tmv-section-box">
            <h3>API Response Time</h3>
            <?php $avg_response_time = floatval(get_option('tmv_avg_verify_response_time', 0.15)); ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3><?php echo esc_html(number_format($avg_response_time * 1000, 0)); ?>ms</h3><p>Average Response Time</p></div>
            </div>
        </div>
        
        <!-- QR Code Scan Analytics -->
        <div class="tmv-section-box">
            <h3>QR Code Scan Analytics</h3>
            <?php
            $qr_scans = intval(get_option('tmv_total_qr_scans', 0));
            $qr_today = intval(get_option('tmv_qr_scans_today', 0));
            ?>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3><?php echo $qr_scans; ?></h3><p>Total QR Scans</p></div>
                <div class="tmv-stat-card"><h3><?php echo $qr_today; ?></h3><p>QR Scans Today</p></div>
            </div>
        </div>
        
        <!-- Verification Embed Code Generator -->
        <div class="tmv-section-box">
            <h3>Verification Embed Code Generator</h3>
            <p class="description">Copy this code to embed a verification widget on external sites:</p>
            <textarea id="tmv-embed-code" rows="4" style="width:100%;font-family:monospace;font-size:12px;" readonly><?php
                $site_url = esc_url(home_url('/'));
                echo esc_textarea('<iframe src="' . $site_url . '?tmv_embed=verify" width="400" height="300" frameborder="0"></iframe>');
            ?></textarea>
            <button class="button" id="tmv-copy-embed-btn" style="margin-top:8px;">Copy Embed Code</button>
        </div>
        <?php
    }
    
    private static function render_tab_maintenance() {
        $error_log = get_option('tmv_error_log', array());
        ?>
        <h2>Maintenance</h2>
        
        <div class="tmv-section-box">
            <h3>System Health Check</h3>
            <div class="tmv-health-grid">
                <div class="tmv-health-item <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'tmv-health-ok' : 'tmv-health-warn'; ?>">
                    <span class="tmv-health-indicator"></span>
                    <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                </div>
                <div class="tmv-health-item <?php echo extension_loaded('gd') ? 'tmv-health-ok' : 'tmv-health-warn'; ?>">
                    <span class="tmv-health-indicator"></span>
                    <strong>GD Extension:</strong> <?php echo extension_loaded('gd') ? 'Enabled' : 'Not Available'; ?>
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?>
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Upload Max Filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?>
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>WordPress Version:</strong> <?php echo function_exists('get_bloginfo') ? get_bloginfo('version') : 'N/A'; ?>
                </div>
            </div>
        </div>
        
        <div class="tmv-section-box">
            <h3>Database Cleanup</h3>
            <p class="description">Remove orphaned meta entries and old revisions to optimize performance.</p>
            <button class="button button-primary" id="tmv-cleanup-db-btn">Run Database Cleanup</button>
            <span id="tmv-cleanup-status" style="margin-left:10px;"></span>
        </div>
        
        <div class="tmv-section-box">
            <h3>Cache Management</h3>
            <p class="description">Cache clearing functionality (requires cache plugin integration).</p>
            <button class="button" disabled>Clear Cache (Not Available)</button>
        </div>
        
        <div class="tmv-section-box">
            <h3>Error Log</h3>
            <button class="button" id="tmv-clear-error-log-btn">Clear Error Log</button>
            <table class="tmv-data-table" style="margin-top:10px;">
                <thead><tr><th>Time</th><th>Message</th><th>Context</th></tr></thead>
                <tbody>
                <?php if (!empty($error_log)): foreach (array_slice(array_reverse($error_log), 0, 20) as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['time'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['message'] ?? ''); ?></td>
                        <td><?php echo esc_html($entry['context'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="tmv-section-box">
            <h3>Plugin Info</h3>
            <p><strong>Plugin:</strong> Trademark Verification System</p>
            <p><strong>Version:</strong> 2.0.0</p>
            <p><strong>Established:</strong> 2009</p>
        </div>
        
        <!-- Scheduled Maintenance Mode -->
        <div class="tmv-section-box">
            <h3>Scheduled Maintenance Mode</h3>
            <div class="tmv-form-section">
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-maintenance-mode-toggle" <?php checked(get_option('tmv_maintenance_mode', '0'), '1'); ?> />
                    Enable Maintenance Mode
                </label>
                <div class="tmv-field-inline">
                    <label>Scheduled Start:</label>
                    <input type="datetime-local" id="tmv-maintenance-start" value="<?php echo esc_attr(get_option('tmv_maintenance_start', '')); ?>" />
                </div>
                <div class="tmv-field-inline">
                    <label>Scheduled End:</label>
                    <input type="datetime-local" id="tmv-maintenance-end" value="<?php echo esc_attr(get_option('tmv_maintenance_end', '')); ?>" />
                </div>
                <button class="button button-primary" id="tmv-save-maintenance-btn" style="margin-top:8px;">Save Maintenance Settings</button>
            </div>
        </div>
        
        <!-- Auto-Backup Schedule -->
        <div class="tmv-section-box">
            <h3>Auto-Backup Schedule</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Backup Frequency:</label>
                    <select id="tmv-backup-frequency">
                        <option value="daily" <?php selected(get_option('tmv_backup_frequency', 'daily'), 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected(get_option('tmv_backup_frequency', 'daily'), 'weekly'); ?>>Weekly</option>
                    </select>
                </div>
                <div class="tmv-field-inline">
                    <label>Backup Time:</label>
                    <input type="time" id="tmv-backup-time" value="<?php echo esc_attr(get_option('tmv_backup_time', '02:00')); ?>" />
                </div>
                <button class="button button-primary" id="tmv-save-backup-settings-btn" style="margin-top:8px;">Save Backup Settings</button>
            </div>
        </div>
        
        <!-- Backup History -->
        <div class="tmv-section-box">
            <h3>Backup History</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Date</th><th>Size</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $backups = get_option('tmv_backup_history', array());
                foreach (array_slice(array_reverse($backups), 0, 10) as $backup):
                ?>
                    <tr>
                        <td><?php echo esc_html($backup['date'] ?? ''); ?></td>
                        <td><?php echo esc_html($backup['size'] ?? ''); ?></td>
                        <td><?php echo esc_html($backup['type'] ?? 'full'); ?></td>
                        <td><span class="tmv-status-badge tmv-status-approved"><?php echo esc_html($backup['status'] ?? 'completed'); ?></span></td>
                        <td><button class="button button-small tmv-restore-backup-btn" data-id="<?php echo esc_attr($backup['id'] ?? ''); ?>">Restore</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Restore from Backup -->
        <div class="tmv-section-box">
            <h3>Restore from Backup</h3>
            <div class="tmv-form-section">
                <select id="tmv-restore-backup-select">
                    <option value="">Select a backup to restore</option>
                    <?php foreach ($backups as $backup): ?>
                        <option value="<?php echo esc_attr($backup['id'] ?? ''); ?>"><?php echo esc_html(($backup['date'] ?? '') . ' - ' . ($backup['type'] ?? 'full')); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button" id="tmv-restore-btn" style="margin-top:8px;">Restore Selected Backup</button>
            </div>
        </div>
        
        <!-- Plugin Compatibility Check -->
        <div class="tmv-section-box">
            <h3>Plugin Compatibility Check</h3>
            <div class="tmv-health-grid">
                <?php
                $active_plugins = get_option('active_plugins', array());
                $plugin_count = count($active_plugins);
                ?>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Active Plugins:</strong> <?php echo intval($plugin_count); ?>
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Theme Compatibility:</strong> Compatible
                </div>
            </div>
        </div>
        
        <!-- Security Scan Results -->
        <div class="tmv-section-box">
            <h3>Security Scan Results</h3>
            <?php $last_scan = get_option('tmv_last_security_scan', array()); ?>
            <div class="tmv-health-grid">
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>File Integrity:</strong> <?php echo esc_html($last_scan['file_integrity'] ?? 'Not scanned'); ?>
                </div>
                <div class="tmv-health-item tmv-health-ok">
                    <span class="tmv-health-indicator"></span>
                    <strong>Last Scan:</strong> <?php echo esc_html($last_scan['date'] ?? 'Never'); ?>
                </div>
            </div>
        </div>
        
        <!-- Performance Metrics -->
        <div class="tmv-section-box">
            <h3>Performance Metrics</h3>
            <div class="tmv-stats-grid tmv-stats-grid-small">
                <div class="tmv-stat-card"><h3><?php echo esc_html(number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3)); ?>s</h3><p>Page Load Time</p></div>
                <div class="tmv-stat-card"><h3><?php global $wpdb; echo intval($wpdb->num_queries); ?></h3><p>DB Queries</p></div>
                <div class="tmv-stat-card"><h3><?php echo esc_html(size_format(memory_get_peak_usage(true))); ?></h3><p>Peak Memory Usage</p></div>
            </div>
        </div>
        
        <!-- Cron Job Management -->
        <div class="tmv-section-box">
            <h3>Cron Job Management</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Hook</th><th>Schedule</th><th>Next Run</th></tr></thead>
                <tbody>
                <?php
                $cron_jobs = _get_cron_array();
                $displayed = 0;
                if (is_array($cron_jobs)):
                    foreach ($cron_jobs as $timestamp => $crons):
                        foreach ($crons as $hook => $data):
                            if ($displayed >= 15) break 2;
                            $displayed++;
                ?>
                    <tr>
                        <td><?php echo esc_html($hook); ?></td>
                        <td><?php echo esc_html(key($data)); ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i:s', $timestamp)); ?></td>
                    </tr>
                <?php endforeach; endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Import/Export Settings -->
        <div class="tmv-section-box">
            <h3>Import / Export All Settings</h3>
            <div class="tmv-quick-links">
                <button class="button button-primary" id="tmv-export-all-settings-btn">Export All Settings</button>
                <button class="button" id="tmv-import-settings-btn">Import Settings</button>
            </div>
            <div id="tmv-import-settings-form" style="display:none;margin-top:10px;">
                <textarea id="tmv-import-settings-data" rows="5" placeholder="Paste exported settings JSON here..." style="width:100%;"></textarea>
                <button class="button button-primary" id="tmv-process-import-settings-btn" style="margin-top:8px;">Process Import</button>
            </div>
        </div>
        
        <!-- Reset to Defaults -->
        <div class="tmv-section-box">
            <h3>Reset to Defaults</h3>
            <p class="description" style="color:red;">Warning: This will reset all TMV plugin settings to their default values.</p>
            <button class="button" id="tmv-reset-defaults-btn" style="color:red;border-color:red;">Reset All Settings to Defaults</button>
        </div>
        <?php
    }
    
    // =========================================================================
    // DOCUMENTS TAB
    // =========================================================================
    
    private static function render_tab_documents() {
        $doc_templates = get_option('tmv_document_templates', array());
        $doc_categories = get_option('tmv_document_categories', array('Legal', 'Certificate', 'Application', 'Report'));
        ?>
        <h2>Documents Management</h2>
        
        <!-- Document Templates Library -->
        <div class="tmv-section-box">
            <h3>Document Templates Library</h3>
            <div class="tmv-tab-actions" style="margin-bottom:10px;">
                <button class="button button-primary" id="tmv-upload-doc-template-btn">Upload Document Template</button>
            </div>
            <div id="tmv-upload-doc-form" style="display:none;margin-bottom:15px;padding:15px;background:#f9f9f9;border:1px solid #ddd;">
                <div class="tmv-field-inline">
                    <label>Document Name:</label>
                    <input type="text" id="tmv-doc-name" placeholder="Document name" style="width:300px;" />
                </div>
                <div class="tmv-field-inline">
                    <label>Category:</label>
                    <select id="tmv-doc-category">
                        <?php foreach ($doc_categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tmv-field-inline">
                    <label>File:</label>
                    <input type="file" id="tmv-doc-file" accept=".pdf,.doc,.docx,.txt" />
                </div>
                <button class="button button-primary" id="tmv-save-document-btn">Save Document</button>
            </div>
            <table class="tmv-data-table">
                <thead><tr><th>Name</th><th>Category</th><th>Date Added</th><th>Version</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($doc_templates as $doc): ?>
                    <tr>
                        <td><?php echo esc_html($doc['name'] ?? ''); ?></td>
                        <td><?php echo esc_html($doc['category'] ?? ''); ?></td>
                        <td><?php echo esc_html($doc['date'] ?? ''); ?></td>
                        <td><?php echo esc_html($doc['version'] ?? '1.0'); ?></td>
                        <td>
                            <button class="button button-small">Download</button>
                            <button class="button button-small tmv-delete-doc-btn" data-id="<?php echo esc_attr($doc['id'] ?? ''); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Document Version History -->
        <div class="tmv-section-box">
            <h3>Document Version History</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Document</th><th>Version</th><th>Modified By</th><th>Date</th><th>Changes</th></tr></thead>
                <tbody>
                <?php
                $doc_versions = get_option('tmv_document_versions', array());
                foreach (array_slice(array_reverse($doc_versions), 0, 15) as $ver):
                ?>
                    <tr>
                        <td><?php echo esc_html($ver['name'] ?? ''); ?></td>
                        <td><?php echo esc_html($ver['version'] ?? ''); ?></td>
                        <td><?php echo esc_html($ver['modified_by'] ?? ''); ?></td>
                        <td><?php echo esc_html($ver['date'] ?? ''); ?></td>
                        <td><?php echo esc_html($ver['changes'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Document Sharing Settings -->
        <div class="tmv-section-box">
            <h3>Document Sharing Settings</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Document Type</th><th>Visibility</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $doc_types = array('Certificate PDF', 'Application Form', 'Legal Notice', 'Renewal Form', 'Transfer Document');
                $sharing_settings = get_option('tmv_doc_sharing', array());
                foreach ($doc_types as $dtype):
                    $is_public = isset($sharing_settings[$dtype]) ? $sharing_settings[$dtype] : 'private';
                ?>
                    <tr>
                        <td><?php echo esc_html($dtype); ?></td>
                        <td>
                            <span class="tmv-status-badge tmv-status-<?php echo $is_public === 'public' ? 'approved' : 'pending'; ?>">
                                <?php echo esc_html(ucfirst($is_public)); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button button-small tmv-toggle-sharing-btn" data-type="<?php echo esc_attr($dtype); ?>" data-current="<?php echo esc_attr($is_public); ?>">
                                Toggle <?php echo $is_public === 'public' ? 'Private' : 'Public'; ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Document Categories Management -->
        <div class="tmv-section-box">
            <h3>Document Categories</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>New Category:</label>
                    <input type="text" id="tmv-new-doc-category" placeholder="Category name" />
                    <button class="button" id="tmv-add-doc-category-btn">Add Category</button>
                </div>
            </div>
            <div class="tmv-tags-display">
                <?php foreach ($doc_categories as $cat): ?>
                    <span class="tmv-tag-badge"><?php echo esc_html($cat); ?> <button class="tmv-remove-doc-cat" data-cat="<?php echo esc_attr($cat); ?>">&times;</button></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Document Expiry Dates Tracker -->
        <div class="tmv-section-box">
            <h3>Document Expiry Tracker</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Document</th><th>Category</th><th>Expiry Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                $expiry_docs = get_option('tmv_document_expiry', array());
                foreach ($expiry_docs as $edoc):
                    $exp_status = (isset($edoc['expiry']) && strtotime($edoc['expiry']) < time()) ? 'Expired' : 'Active';
                    $exp_class = $exp_status === 'Expired' ? 'rejected' : 'approved';
                ?>
                    <tr>
                        <td><?php echo esc_html($edoc['name'] ?? ''); ?></td>
                        <td><?php echo esc_html($edoc['category'] ?? ''); ?></td>
                        <td><?php echo esc_html($edoc['expiry'] ?? ''); ?></td>
                        <td><span class="tmv-status-badge tmv-status-<?php echo esc_attr($exp_class); ?>"><?php echo esc_html($exp_status); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Auto-Archive Old Documents -->
        <div class="tmv-section-box">
            <h3>Auto-Archive Settings</h3>
            <div class="tmv-form-section">
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-auto-archive-toggle" <?php checked(get_option('tmv_auto_archive_docs', '0'), '1'); ?> />
                    Automatically archive documents older than 1 year
                </label>
                <p class="description">Archived documents will be moved to the archive section and not shown in the main library.</p>
            </div>
        </div>
        <?php
    }
    
    // =========================================================================
    // NOTIFICATIONS TAB
    // =========================================================================
    
    private static function render_tab_notifications() {
        $notifications = get_option('tmv_notifications', array());
        $notification_settings = get_option('tmv_notification_settings', array());
        ?>
        <h2>Notifications Center</h2>
        
        <!-- In-App Notifications Feed -->
        <div class="tmv-section-box">
            <h3>Recent Notifications</h3>
            <div class="tmv-notifications-feed">
                <?php
                if (!empty($notifications)):
                    foreach (array_slice(array_reverse($notifications), 0, 20) as $nidx => $notif):
                        $is_read = isset($notif['read']) && $notif['read'];
                ?>
                    <div class="tmv-notification-item <?php echo $is_read ? 'tmv-notif-read' : 'tmv-notif-unread'; ?>" data-id="<?php echo intval($nidx); ?>">
                        <div class="tmv-notif-icon"><?php echo $is_read ? '&#128172;' : '&#128276;'; ?></div>
                        <div class="tmv-notif-content">
                            <strong><?php echo esc_html($notif['title'] ?? ''); ?></strong>
                            <p><?php echo esc_html($notif['message'] ?? ''); ?></p>
                            <small><?php echo esc_html($notif['time'] ?? ''); ?></small>
                        </div>
                        <div class="tmv-notif-actions">
                            <button class="button button-small tmv-mark-read-btn" data-id="<?php echo intval($nidx); ?>">
                                <?php echo $is_read ? 'Mark Unread' : 'Mark Read'; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach;
                else: ?>
                    <p>No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Push Notification Settings -->
        <div class="tmv-section-box">
            <h3>Push Notification Settings</h3>
            <div class="tmv-form-section">
                <label style="display:block;margin:5px 0;">
                    <input type="checkbox" id="tmv-push-enabled" <?php checked($notification_settings['push_enabled'] ?? '0', '1'); ?> />
                    Enable Push Notifications
                </label>
                <div class="tmv-field-inline">
                    <label>VAPID Public Key:</label>
                    <input type="text" id="tmv-vapid-public" value="<?php echo esc_attr($notification_settings['vapid_public'] ?? ''); ?>" style="width:400px;" />
                </div>
                <div class="tmv-field-inline">
                    <label>VAPID Private Key:</label>
                    <input type="password" id="tmv-vapid-private" value="<?php echo esc_attr($notification_settings['vapid_private'] ?? ''); ?>" style="width:400px;" />
                </div>
            </div>
        </div>
        
        <!-- Email Digest Frequency -->
        <div class="tmv-section-box">
            <h3>Email Digest Frequency</h3>
            <div class="tmv-form-section">
                <?php $digest_freq = $notification_settings['digest_frequency'] ?? 'weekly'; ?>
                <label style="display:inline-block;margin-right:15px;">
                    <input type="radio" name="tmv_digest_freq" value="daily" <?php checked($digest_freq, 'daily'); ?> /> Daily
                </label>
                <label style="display:inline-block;margin-right:15px;">
                    <input type="radio" name="tmv_digest_freq" value="weekly" <?php checked($digest_freq, 'weekly'); ?> /> Weekly
                </label>
                <label style="display:inline-block;margin-right:15px;">
                    <input type="radio" name="tmv_digest_freq" value="monthly" <?php checked($digest_freq, 'monthly'); ?> /> Monthly
                </label>
            </div>
        </div>
        
        <!-- SMS Gateway Configuration -->
        <div class="tmv-section-box">
            <h3>SMS Gateway Configuration</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>SMS Provider:</label>
                    <select id="tmv-sms-provider">
                        <option value="twilio" <?php selected($notification_settings['sms_provider'] ?? '', 'twilio'); ?>>Twilio</option>
                        <option value="nexmo" <?php selected($notification_settings['sms_provider'] ?? '', 'nexmo'); ?>>Nexmo (Vonage)</option>
                        <option value="messagebird" <?php selected($notification_settings['sms_provider'] ?? '', 'messagebird'); ?>>MessageBird</option>
                        <option value="custom" <?php selected($notification_settings['sms_provider'] ?? '', 'custom'); ?>>Custom API</option>
                    </select>
                </div>
                <div class="tmv-field-inline">
                    <label>API Key:</label>
                    <input type="password" id="tmv-sms-api-key" value="<?php echo esc_attr($notification_settings['sms_api_key'] ?? ''); ?>" style="width:400px;" />
                </div>
                <div class="tmv-field-inline">
                    <label>Sender ID:</label>
                    <input type="text" id="tmv-sms-sender-id" value="<?php echo esc_attr($notification_settings['sms_sender_id'] ?? ''); ?>" />
                </div>
            </div>
        </div>
        
        <!-- Webhook Notifications Setup -->
        <div class="tmv-section-box">
            <h3>Webhook Notifications</h3>
            <div class="tmv-form-section">
                <div class="tmv-field-inline">
                    <label>Webhook URL:</label>
                    <input type="url" id="tmv-webhook-url" placeholder="https://example.com/webhook" style="width:400px;" />
                </div>
                <div style="margin-top:10px;">
                    <p><strong>Trigger Events:</strong></p>
                    <label style="display:block;margin:3px 0;"><input type="checkbox" class="tmv-webhook-event" value="application_submitted" /> Application Submitted</label>
                    <label style="display:block;margin:3px 0;"><input type="checkbox" class="tmv-webhook-event" value="application_approved" /> Application Approved</label>
                    <label style="display:block;margin:3px 0;"><input type="checkbox" class="tmv-webhook-event" value="application_rejected" /> Application Rejected</label>
                    <label style="display:block;margin:3px 0;"><input type="checkbox" class="tmv-webhook-event" value="certificate_generated" /> Certificate Generated</label>
                    <label style="display:block;margin:3px 0;"><input type="checkbox" class="tmv-webhook-event" value="user_registered" /> User Registered</label>
                </div>
                <button class="button button-primary" id="tmv-save-webhook-btn" style="margin-top:8px;">Save Webhook</button>
            </div>
            
            <!-- Existing Webhooks -->
            <table class="tmv-data-table" style="margin-top:15px;">
                <thead><tr><th>URL</th><th>Events</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $webhooks = get_option('tmv_webhooks', array());
                foreach ($webhooks as $widx => $webhook):
                ?>
                    <tr>
                        <td><?php echo esc_html($webhook['url'] ?? ''); ?></td>
                        <td><?php echo esc_html(implode(', ', $webhook['events'] ?? array())); ?></td>
                        <td><span class="tmv-status-badge tmv-status-approved">Active</span></td>
                        <td><button class="button button-small tmv-delete-webhook-btn" data-id="<?php echo intval($widx); ?>">Delete</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Notification History Log -->
        <div class="tmv-section-box">
            <h3>Notification History Log</h3>
            <table class="tmv-data-table">
                <thead><tr><th>Type</th><th>Recipient</th><th>Subject</th><th>Status</th><th>Sent At</th></tr></thead>
                <tbody>
                <?php
                $notif_history = get_option('tmv_notification_history', array());
                foreach (array_slice(array_reverse($notif_history), 0, 20) as $nh):
                ?>
                    <tr>
                        <td><?php echo esc_html($nh['type'] ?? ''); ?></td>
                        <td><?php echo esc_html($nh['recipient'] ?? ''); ?></td>
                        <td><?php echo esc_html($nh['subject'] ?? ''); ?></td>
                        <td><span class="tmv-status-badge tmv-status-<?php echo ($nh['status'] ?? '') === 'sent' ? 'approved' : 'rejected'; ?>"><?php echo esc_html(ucfirst($nh['status'] ?? '')); ?></span></td>
                        <td><?php echo esc_html($nh['time'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Save All Notification Settings -->
        <div class="tmv-section-box">
            <button class="button button-primary" id="tmv-save-notification-settings-btn">Save All Notification Settings</button>
        </div>
        <?php
    }
    
    // =========================================================================
    // AJAX HANDLERS FOR ENHANCED DASHBOARD
    // =========================================================================
    
    public static function ajax_export_csv() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $apps = get_posts(array('post_type' => 'trademark_app', 'posts_per_page' => -1, 'post_status' => 'any'));
        $rows = array();
        $rows[] = array('TM Number', 'Owner', 'Class', 'Status', 'Application Date', 'Registration Date', 'Expiry Date');
        
        foreach ($apps as $app) {
            $rows[] = array(
                get_post_meta($app->ID, 'tmv_tm_number', true),
                get_post_meta($app->ID, 'tmv_owner', true),
                get_post_meta($app->ID, 'tmv_class', true),
                get_post_meta($app->ID, 'tmv_status', true),
                get_post_meta($app->ID, 'tmv_application_date', true),
                get_post_meta($app->ID, 'tmv_reg_date', true),
                get_post_meta($app->ID, 'tmv_expiry_date', true),
            );
        }
        
        wp_send_json_success(array('data' => $rows));
    }
    
    public static function ajax_bulk_action() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $action_type = sanitize_text_field($_POST['bulk_action'] ?? '');
        $ids = array_map('intval', (array)($_POST['ids'] ?? array()));
        
        if (empty($ids) || empty($action_type)) {
            wp_send_json_error(array('message' => 'No items or action selected'));
            return;
        }
        
        $count = 0;
        foreach ($ids as $id) {
            switch ($action_type) {
                case 'approve':
                    update_post_meta($id, 'tmv_status', 'approved');
                    update_post_meta($id, 'tmv_approved_date', current_time('Y-m-d'));
                    $count++;
                    break;
                case 'reject':
                    update_post_meta($id, 'tmv_status', 'rejected');
                    $count++;
                    break;
                case 'delete':
                    wp_trash_post($id);
                    $count++;
                    break;
            }
        }
        
        wp_send_json_success(array('message' => $count . ' items processed'));
    }
    
    public static function ajax_flag_application() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        $current = get_post_meta($post_id, 'tmv_priority', true);
        $new_value = $current ? '' : '1';
        update_post_meta($post_id, 'tmv_priority', $new_value);
        
        wp_send_json_success(array('flagged' => (bool)$new_value));
    }
    
    public static function ajax_save_notes() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        update_post_meta($post_id, 'tmv_admin_notes', $notes);
        wp_send_json_success(array('message' => 'Notes saved'));
    }
    
    public static function ajax_revoke_certificate() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        update_post_meta($post_id, 'tmv_revoked', '1');
        wp_send_json_success(array('message' => 'Certificate revoked'));
    }
    
    public static function ajax_batch_generate_certs() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        global $wpdb;
        $approved_without_cert = $wpdb->get_col(
            "SELECT pm.post_id FROM {$wpdb->postmeta} pm 
             LEFT JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id AND pm2.meta_key = 'tmv_certificate_jpg'
             WHERE pm.meta_key = 'tmv_status' AND pm.meta_value = 'approved' 
             AND (pm2.meta_value IS NULL OR pm2.meta_value = '')"
        );
        
        $generated = 0;
        foreach ($approved_without_cert as $post_id) {
            try {
                if (class_exists('TMV_Certificate')) {
                    $cert_id = TMV_Certificate::generate(intval($post_id));
                    if ($cert_id) $generated++;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        
        wp_send_json_success(array('message' => $generated . ' certificates generated'));
    }
    
    public static function ajax_ban_user() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $user_id = intval($_POST['user_id'] ?? 0);
        $ban_action = sanitize_text_field($_POST['ban_action'] ?? 'ban');
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        if ($ban_action === 'ban') {
            update_user_meta($user_id, 'tmv_user_banned', '1');
        } else {
            delete_user_meta($user_id, 'tmv_user_banned');
        }
        
        wp_send_json_success(array('message' => 'User ' . $ban_action . ' successful'));
    }
    
    public static function ajax_save_email_templates() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $approved = sanitize_textarea_field($_POST['approved_tpl'] ?? '');
        $rejected = sanitize_textarea_field($_POST['rejected_tpl'] ?? '');
        $submitted = sanitize_textarea_field($_POST['submitted_tpl'] ?? '');
        
        update_option('tmv_email_approved_tpl', $approved);
        update_option('tmv_email_rejected_tpl', $rejected);
        update_option('tmv_email_submitted_tpl', $submitted);
        
        wp_send_json_success(array('message' => 'Email templates saved'));
    }
    
    public static function ajax_add_announcement() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $title = sanitize_text_field($_POST['title'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (empty($title)) {
            wp_send_json_error(array('message' => 'Title is required'));
            return;
        }
        
        $announcements = get_option('tmv_announcements', array());
        $announcements[] = array(
            'title' => $title,
            'message' => $message,
            'date' => current_time('mysql'),
        );
        update_option('tmv_announcements', $announcements);
        
        wp_send_json_success(array('message' => 'Announcement added'));
    }
    
    public static function ajax_delete_announcement() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $index = intval($_POST['index'] ?? -1);
        $announcements = get_option('tmv_announcements', array());
        
        if (isset($announcements[$index])) {
            array_splice($announcements, $index, 1);
            update_option('tmv_announcements', $announcements);
            wp_send_json_success(array('message' => 'Announcement deleted'));
        } else {
            wp_send_json_error(array('message' => 'Announcement not found'));
        }
    }
    
    public static function ajax_manage_ip_list() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $list_type = sanitize_text_field($_POST['list_type'] ?? '');
        $ip = sanitize_text_field($_POST['ip'] ?? '');
        $action_type = sanitize_text_field($_POST['ip_action'] ?? 'add');
        
        if (!in_array($list_type, array('blacklist', 'whitelist'), true)) {
            wp_send_json_error(array('message' => 'Invalid list type'));
            return;
        }
        
        $option_key = 'tmv_ip_' . $list_type;
        $list = get_option($option_key, array());
        
        if ($action_type === 'add' && !empty($ip)) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                wp_send_json_error(array('message' => 'Invalid IP address format.'));
                return;
            }
            if (!in_array($ip, $list, true)) {
                $list[] = $ip;
            }
        } elseif ($action_type === 'remove' && !empty($ip)) {
            $list = array_values(array_diff($list, array($ip)));
        }
        
        update_option($option_key, $list);
        wp_send_json_success(array('message' => 'IP list updated'));
    }
    
    public static function ajax_save_faq() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $question = sanitize_text_field($_POST['question'] ?? '');
        $answer = sanitize_textarea_field($_POST['answer'] ?? '');
        
        if (empty($question)) {
            wp_send_json_error(array('message' => 'Question is required'));
            return;
        }
        
        $faqs = get_option('tmv_faqs', array());
        $faqs[] = array('question' => $question, 'answer' => $answer);
        update_option('tmv_faqs', $faqs);
        
        wp_send_json_success(array('message' => 'FAQ added'));
    }
    
    public static function ajax_delete_faq() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $index = intval($_POST['index'] ?? -1);
        $faqs = get_option('tmv_faqs', array());
        
        if (isset($faqs[$index])) {
            array_splice($faqs, $index, 1);
            update_option('tmv_faqs', $faqs);
            wp_send_json_success(array('message' => 'FAQ deleted'));
        } else {
            wp_send_json_error(array('message' => 'FAQ not found'));
        }
    }
    
    public static function ajax_save_contact_info() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        update_option('tmv_contact_phone', sanitize_text_field($_POST['phone'] ?? ''));
        update_option('tmv_contact_email', sanitize_email($_POST['email'] ?? ''));
        update_option('tmv_contact_address', sanitize_text_field($_POST['address'] ?? ''));
        
        wp_send_json_success(array('message' => 'Contact info saved'));
    }
    
    public static function ajax_save_social_links() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        update_option('tmv_social_facebook', esc_url_raw($_POST['facebook'] ?? ''));
        update_option('tmv_social_twitter', esc_url_raw($_POST['twitter'] ?? ''));
        update_option('tmv_social_linkedin', esc_url_raw($_POST['linkedin'] ?? ''));
        update_option('tmv_social_youtube', esc_url_raw($_POST['youtube'] ?? ''));
        
        wp_send_json_success(array('message' => 'Social links saved'));
    }
    
    public static function ajax_cleanup_database() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        global $wpdb;
        
        // Remove orphaned postmeta (scoped to tmv_ prefix only)
        $orphaned = $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL AND pm.meta_key LIKE 'tmv_%'"
        );
        
        // Remove old revisions (older than 30 days)
        $revisions = $wpdb->query(
            "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        wp_send_json_success(array('message' => 'Cleanup complete. Orphaned meta: ' . intval($orphaned) . ', Old revisions: ' . intval($revisions)));
    }
    
    public static function ajax_clear_error_log() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        update_option('tmv_error_log', array());
        wp_send_json_success(array('message' => 'Error log cleared'));
    }
    
    // =========================================================================
    // NEW AJAX HANDLERS (Documents, Notifications, Extended Features)
    // =========================================================================
    
    public static function ajax_save_document() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        if (empty($name)) {
            wp_send_json_error(array('message' => 'Document name is required'));
            return;
        }
        
        $templates = get_option('tmv_document_templates', array());
        $templates[] = array(
            'id' => wp_generate_uuid4(),
            'name' => $name,
            'category' => $category,
            'date' => current_time('Y-m-d'),
            'version' => '1.0',
        );
        update_option('tmv_document_templates', $templates);
        
        wp_send_json_success(array('message' => 'Document saved successfully'));
    }
    
    public static function ajax_delete_document() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $doc_id = sanitize_text_field($_POST['doc_id'] ?? '');
        $templates = get_option('tmv_document_templates', array());
        
        $templates = array_filter($templates, function($doc) use ($doc_id) {
            return ($doc['id'] ?? '') !== $doc_id;
        });
        
        update_option('tmv_document_templates', array_values($templates));
        wp_send_json_success(array('message' => 'Document deleted'));
    }
    
    public static function ajax_save_notification_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $settings = array(
            'push_enabled' => sanitize_text_field($_POST['push_enabled'] ?? '0'),
            'vapid_public' => sanitize_text_field($_POST['vapid_public'] ?? ''),
            'vapid_private' => sanitize_text_field($_POST['vapid_private'] ?? ''),
            'digest_frequency' => sanitize_text_field($_POST['digest_frequency'] ?? 'weekly'),
            'sms_provider' => sanitize_text_field($_POST['sms_provider'] ?? ''),
            'sms_api_key' => sanitize_text_field($_POST['sms_api_key'] ?? ''),
            'sms_sender_id' => sanitize_text_field($_POST['sms_sender_id'] ?? ''),
        );
        
        update_option('tmv_notification_settings', $settings);
        wp_send_json_success(array('message' => 'Notification settings saved'));
    }
    
    public static function ajax_mark_notification_read() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $notif_id = intval($_POST['notif_id'] ?? -1);
        $notifications = get_option('tmv_notifications', array());
        
        if (isset($notifications[$notif_id])) {
            $notifications[$notif_id]['read'] = !($notifications[$notif_id]['read'] ?? false);
            update_option('tmv_notifications', $notifications);
            wp_send_json_success(array('message' => 'Notification status updated'));
        } else {
            wp_send_json_error(array('message' => 'Notification not found'));
        }
    }
    
    public static function ajax_save_webhook() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $url = esc_url_raw($_POST['webhook_url'] ?? '');
        $events = array_map('sanitize_text_field', (array)($_POST['events'] ?? array()));
        
        if (empty($url)) {
            wp_send_json_error(array('message' => 'Webhook URL is required'));
            return;
        }
        
        $webhooks = get_option('tmv_webhooks', array());
        $webhooks[] = array(
            'url' => $url,
            'events' => $events,
            'created' => current_time('mysql'),
        );
        update_option('tmv_webhooks', $webhooks);
        
        wp_send_json_success(array('message' => 'Webhook saved'));
    }
    
    public static function ajax_delete_webhook() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $webhook_id = intval($_POST['webhook_id'] ?? -1);
        $webhooks = get_option('tmv_webhooks', array());
        
        if (isset($webhooks[$webhook_id])) {
            array_splice($webhooks, $webhook_id, 1);
            update_option('tmv_webhooks', $webhooks);
            wp_send_json_success(array('message' => 'Webhook deleted'));
        } else {
            wp_send_json_error(array('message' => 'Webhook not found'));
        }
    }
    
    public static function ajax_save_password_policy() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        update_option('tmv_pw_min_length', intval($_POST['min_length'] ?? 8));
        update_option('tmv_pw_require_uppercase', sanitize_text_field($_POST['require_uppercase'] ?? '0'));
        update_option('tmv_pw_require_numbers', sanitize_text_field($_POST['require_numbers'] ?? '0'));
        update_option('tmv_pw_require_special', sanitize_text_field($_POST['require_special'] ?? '0'));
        
        wp_send_json_success(array('message' => 'Password policy saved'));
    }
    
    public static function ajax_save_session_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $timeout = intval($_POST['session_timeout'] ?? 60);
        $timeout = max(5, min(1440, $timeout));
        update_option('tmv_session_timeout', $timeout);
        
        $lockout_attempts = intval($_POST['lockout_attempts'] ?? 5);
        $lockout_duration = intval($_POST['lockout_duration'] ?? 30);
        update_option('tmv_lockout_attempts', max(3, min(20, $lockout_attempts)));
        update_option('tmv_lockout_duration', max(5, min(1440, $lockout_duration)));
        
        wp_send_json_success(array('message' => 'Session settings saved'));
    }
    
    public static function ajax_toggle_maintenance() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $enabled = sanitize_text_field($_POST['enabled'] ?? '0');
        $start = sanitize_text_field($_POST['start'] ?? '');
        $end = sanitize_text_field($_POST['end'] ?? '');
        
        update_option('tmv_maintenance_mode', $enabled);
        update_option('tmv_maintenance_start', $start);
        update_option('tmv_maintenance_end', $end);
        
        wp_send_json_success(array('message' => 'Maintenance settings updated'));
    }
    
    public static function ajax_save_backup_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $frequency = sanitize_text_field($_POST['frequency'] ?? 'daily');
        $time = sanitize_text_field($_POST['time'] ?? '02:00');
        
        update_option('tmv_backup_frequency', $frequency);
        update_option('tmv_backup_time', $time);
        
        wp_send_json_success(array('message' => 'Backup settings saved'));
    }
    
    public static function ajax_export_all_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        global $wpdb;
        $tmv_options = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'tmv_%'"
        );
        
        $sensitive_patterns = array('secret', 'private', 'api_key', 'password');
        $export_data = array();
        foreach ($tmv_options as $opt) {
            $is_sensitive = false;
            foreach ($sensitive_patterns as $pattern) {
                if (stripos($opt->option_name, $pattern) !== false) {
                    $is_sensitive = true;
                    break;
                }
            }
            if (!$is_sensitive) {
                $export_data[$opt->option_name] = maybe_unserialize($opt->option_value);
            }
        }
        
        wp_send_json_success(array('data' => $export_data, 'message' => 'Settings exported'));
    }
    
    public static function ajax_import_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $json_data = sanitize_textarea_field($_POST['settings_data'] ?? '');
        $settings = json_decode($json_data, true);
        
        if (!is_array($settings)) {
            wp_send_json_error(array('message' => 'Invalid settings data format'));
            return;
        }
        
        $imported = 0;
        foreach ($settings as $key => $value) {
            if (strpos($key, 'tmv_') === 0) {
                $sanitized_key = sanitize_text_field($key);
                if (is_array($value)) {
                    $value = array_map('sanitize_text_field', $value);
                } elseif (is_int($value)) {
                    $value = intval($value);
                } else {
                    $value = sanitize_text_field((string) $value);
                }
                update_option($sanitized_key, $value);
                $imported++;
            }
        }
        
        wp_send_json_success(array('message' => $imported . ' settings imported'));
    }
    
    public static function ajax_reset_defaults() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'tmv_%' AND option_name NOT IN ('tmv_verify_base_url', 'tmv_plugin_activated')");
        
        wp_send_json_success(array('message' => 'All settings reset to defaults'));
    }
    
    public static function ajax_save_tags() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $tag = sanitize_text_field($_POST['tag'] ?? '');
        $action_type = sanitize_text_field($_POST['tag_action'] ?? 'add');
        
        $tags = get_option('tmv_application_tags', array());
        
        if ($action_type === 'add' && !empty($tag)) {
            if (!in_array($tag, $tags, true)) {
                $tags[] = $tag;
            }
        } elseif ($action_type === 'remove' && !empty($tag)) {
            $tags = array_values(array_diff($tags, array($tag)));
        }
        
        update_option('tmv_application_tags', $tags);
        wp_send_json_success(array('message' => 'Tags updated', 'tags' => $tags));
    }
    
    public static function ajax_add_comment() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');
        
        if (!$post_id || empty($comment)) {
            wp_send_json_error(array('message' => 'Post ID and comment are required'));
            return;
        }
        
        $comments = get_post_meta($post_id, 'tmv_admin_comments', true);
        if (!is_array($comments)) $comments = array();
        
        $current_user = wp_get_current_user();
        $comments[] = array(
            'user' => $current_user->user_login,
            'comment' => $comment,
            'time' => current_time('mysql'),
        );
        
        update_post_meta($post_id, 'tmv_admin_comments', $comments);
        wp_send_json_success(array('message' => 'Comment added', 'comments' => $comments));
    }
    
    public static function ajax_send_applicant_email() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (!$post_id || empty($subject) || empty($message)) {
            wp_send_json_error(array('message' => 'Post ID, subject, and message are required'));
            return;
        }
        
        $owner_email = get_post_meta($post_id, 'tmv_owner_email', true);
        if (empty($owner_email)) {
            wp_send_json_error(array('message' => 'No email found for this applicant'));
            return;
        }
        
        $sent = wp_mail($owner_email, $subject, $message);
        
        if ($sent) {
            wp_send_json_success(array('message' => 'Email sent to applicant'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send email'));
        }
    }
    
    public static function ajax_schedule_reminder() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $date = sanitize_text_field($_POST['reminder_date'] ?? '');
        $message = sanitize_text_field($_POST['reminder_message'] ?? '');
        
        if (!$post_id || empty($date)) {
            wp_send_json_error(array('message' => 'Post ID and date are required'));
            return;
        }
        
        $reminders = get_option('tmv_scheduled_reminders', array());
        $reminders[] = array(
            'post_id' => $post_id,
            'date' => $date,
            'message' => $message,
            'created' => current_time('mysql'),
        );
        update_option('tmv_scheduled_reminders', $reminders);
        
        wp_send_json_success(array('message' => 'Reminder scheduled for ' . $date));
    }
    
    public static function ajax_import_users() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'No CSV file uploaded or upload error'));
            return;
        }
        
        $file = $_FILES['csv_file'];
        $allowed_mimes = array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel');
        if (!in_array($file['type'], $allowed_mimes, true)) {
            wp_send_json_error(array('message' => 'Invalid file type. Please upload a CSV file'));
            return;
        }
        
        $csv_data = file_get_contents($file['tmp_name']);
        if (empty($csv_data)) {
            wp_send_json_error(array('message' => 'CSV file is empty'));
            return;
        }
        
        $lines = explode("\n", $csv_data);
        $imported = 0;
        $allowed_roles = array('subscriber', 'editor', 'author', 'contributor');
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $fields = str_getcsv($line);
            if (count($fields) >= 2) {
                $username = sanitize_user($fields[0]);
                $email = sanitize_email($fields[1]);
                $role = isset($fields[2]) ? sanitize_text_field($fields[2]) : 'subscriber';
                
                if (!in_array($role, $allowed_roles, true)) {
                    $role = 'subscriber';
                }
                
                if (!empty($username) && !empty($email) && !username_exists($username) && !email_exists($email)) {
                    $password = wp_generate_password();
                    $user_id = wp_create_user($username, $password, $email);
                    if (!is_wp_error($user_id)) {
                        $user = new WP_User($user_id);
                        $user->set_role($role);
                        $imported++;
                    }
                }
            }
        }
        
        wp_send_json_success(array('message' => $imported . ' users imported'));
    }
    
    public static function ajax_export_users() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $users = get_users(array('number' => -1));
        $rows = array();
        $rows[] = array('Username', 'Email', 'Role', 'Registered', 'Applications');
        
        foreach ($users as $user) {
            $app_count = count_user_posts($user->ID, 'trademark_app');
            $role = !empty($user->roles) ? $user->roles[0] : 'none';
            $rows[] = array(
                $user->user_login,
                $user->user_email,
                $role,
                $user->user_registered,
                $app_count,
            );
        }
        
        wp_send_json_success(array('data' => $rows, 'message' => 'Users exported'));
    }
    
    public static function ajax_invite_user() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $role = sanitize_text_field($_POST['role'] ?? 'subscriber');
        
        $allowed_roles = array('subscriber', 'editor', 'author', 'contributor');
        if (!in_array($role, $allowed_roles, true)) {
            $role = 'subscriber';
        }
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Valid email is required'));
            return;
        }
        
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'User with this email already exists'));
            return;
        }
        
        $username = sanitize_user(strstr($email, '@', true));
        $password = wp_generate_password();
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
            return;
        }
        
        $user = new WP_User($user_id);
        $user->set_role($role);
        
        wp_new_user_notification($user_id, null, 'user');
        
        wp_send_json_success(array('message' => 'Invitation sent to ' . $email));
    }
    
    public static function ajax_bulk_email_users() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $group = sanitize_text_field($_POST['group'] ?? 'all');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (empty($subject) || empty($message)) {
            wp_send_json_error(array('message' => 'Subject and message are required'));
            return;
        }
        
        $args = array('number' => -1, 'fields' => array('user_email'));
        if ($group !== 'all' && in_array($group, array('subscribers', 'administrators', 'editors'), true)) {
            $args['role'] = rtrim($group, 's');
        }
        
        $users = get_users($args);
        $total_users = count($users);
        $batch_limit = 50;
        $users_batch = array_slice($users, 0, $batch_limit);
        $sent = 0;
        
        foreach ($users_batch as $user) {
            if (wp_mail($user->user_email, $subject, $message)) {
                $sent++;
            }
        }
        
        $remaining = $total_users - $batch_limit;
        $msg = 'Email sent to ' . $sent . ' users';
        if ($remaining > 0) {
            $msg .= '. ' . $remaining . ' remaining users were not emailed. Please send again for the next batch.';
        }
        
        wp_send_json_success(array('message' => $msg));
    }
    
    public static function ajax_save_cert_watermark() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $text = sanitize_text_field($_POST['watermark_text'] ?? 'CERTIFIED');
        $opacity = intval($_POST['opacity'] ?? 20);
        $opacity = max(0, min(100, $opacity));
        
        update_option('tmv_cert_watermark_text', $text);
        update_option('tmv_cert_watermark_opacity', $opacity);
        
        wp_send_json_success(array('message' => 'Watermark settings saved'));
    }
    
    public static function ajax_save_cert_numbering() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $format = sanitize_text_field($_POST['format'] ?? 'CERT-{YEAR}-{NUMBER}');
        update_option('tmv_cert_numbering_format', $format);
        
        wp_send_json_success(array('message' => 'Certificate numbering format saved'));
    }
    
    public static function ajax_generate_report() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $date_from = sanitize_text_field($_POST['date_from'] ?? '');
        $date_to = sanitize_text_field($_POST['date_to'] ?? '');
        $metrics = array_map('sanitize_text_field', (array)($_POST['metrics'] ?? array()));
        
        global $wpdb;
        $report = array();
        
        $where_date = '';
        if (!empty($date_from) && !empty($date_to)) {
            $where_date = $wpdb->prepare(" AND post_date BETWEEN %s AND %s", $date_from, $date_to . ' 23:59:59');
        }
        
        if (in_array('applications', $metrics, true)) {
            $report['applications'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'trademark_app'" . $where_date));
        }
        if (in_array('approvals', $metrics, true)) {
            $report['approvals'] = intval(self::count_by_status('approved'));
        }
        if (in_array('rejections', $metrics, true)) {
            $report['rejections'] = intval(self::count_by_status('rejected'));
        }
        if (in_array('certificates', $metrics, true)) {
            $report['certificates'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_certificate_jpg' AND meta_value != ''"));
        }
        if (in_array('verifications', $metrics, true)) {
            $log = get_option('tmv_verification_log', array());
            $report['verifications'] = count($log);
        }
        if (in_array('revenue', $metrics, true)) {
            $report['revenue'] = floatval(get_option('tmv_total_revenue', 0));
        }
        
        wp_send_json_success(array('report' => $report, 'message' => 'Report generated'));
    }
    
    public static function ajax_schedule_report() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        
        $frequency = sanitize_text_field($_POST['frequency'] ?? 'weekly');
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Valid email address is required'));
            return;
        }
        
        update_option('tmv_report_schedule_frequency', $frequency);
        update_option('tmv_report_schedule_email', $email);
        
        wp_send_json_success(array('message' => 'Report scheduled: ' . $frequency . ' to ' . $email));
    }
    
    // =========================================================================
    // SETTINGS PAGE
    // =========================================================================
    
    public static function render_settings() {
        if (isset($_POST['tmv_save_settings']) && wp_verify_nonce($_POST['tmv_settings_nonce'], 'tmv_settings')) {
            update_option('tmv_verify_base_url', esc_url_raw($_POST['tmv_verify_base_url']));
            update_option('tmv_copyright_text', sanitize_text_field($_POST['tmv_copyright_text']));
            update_option('tmv_qr_size', intval($_POST['tmv_qr_size']));
            update_option('tmv_dpdt_logo', intval($_POST['tmv_dpdt_logo']));
            update_option('tmv_bd_govt_seal', intval($_POST['tmv_bd_govt_seal']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

        $dpdt_logo_id = intval(get_option('tmv_dpdt_logo', 0));
        $bd_seal_id = intval(get_option('tmv_bd_govt_seal', 0));
        $dpdt_logo_url = $dpdt_logo_id ? wp_get_attachment_url($dpdt_logo_id) : '';
        $bd_seal_url = $bd_seal_id ? wp_get_attachment_url($bd_seal_id) : '';
        ?>
        <div class="wrap">
            <h1>TMV Settings</h1>
            <form method="post">
                <?php wp_nonce_field('tmv_settings', 'tmv_settings_nonce'); ?>

                <h2>Logo Management</h2>
                <p class="description">Upload custom logos to replace the default inline SVGs.</p>
                <table class="form-table">
                    <tr>
                        <th>DPDT Logo (Top-Left)</th>
                        <td>
                            <input type="hidden" name="tmv_dpdt_logo" id="tmv_dpdt_logo" value="<?php echo esc_attr($dpdt_logo_id); ?>" />
                            <div id="tmv-dpdt-logo-preview" style="margin-bottom:10px;">
                                <?php if ($dpdt_logo_url): ?>
                                    <img src="<?php echo esc_url($dpdt_logo_url); ?>" style="max-width:80px;height:auto;" />
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button tmv-upload-btn" data-target="tmv_dpdt_logo" data-preview="tmv-dpdt-logo-preview" data-type="image">Upload DPDT Logo</button>
                            <button type="button" class="button tmv-remove-btn" data-target="tmv_dpdt_logo" data-preview="tmv-dpdt-logo-preview">Reset to Default SVG</button>
                            <p class="description">Leave empty to use the default DPDT SVG.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Bangladesh Govt Seal (Top-Right)</th>
                        <td>
                            <input type="hidden" name="tmv_bd_govt_seal" id="tmv_bd_govt_seal" value="<?php echo esc_attr($bd_seal_id); ?>" />
                            <div id="tmv-bd-seal-preview" style="margin-bottom:10px;">
                                <?php if ($bd_seal_url): ?>
                                    <img src="<?php echo esc_url($bd_seal_url); ?>" style="max-width:80px;height:auto;" />
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button tmv-upload-btn" data-target="tmv_bd_govt_seal" data-preview="tmv-bd-seal-preview" data-type="image">Upload BD Govt Seal</button>
                            <button type="button" class="button tmv-remove-btn" data-target="tmv_bd_govt_seal" data-preview="tmv-bd-seal-preview">Reset to Default SVG</button>
                            <p class="description">Leave empty to use the default Bangladesh Govt seal SVG.</p>
                        </td>
                    </tr>
                </table>

                <hr />
                <h2>General Settings</h2>
                <table class="form-table">
                    <tr>
                        <th>Verify Base URL</th>
                        <td><input type="url" name="tmv_verify_base_url" value="<?php echo esc_url(get_option('tmv_verify_base_url')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Copyright Text</th>
                        <td><input type="text" name="tmv_copyright_text" value="<?php echo esc_attr(get_option('tmv_copyright_text')); ?>" class="large-text" /></td>
                    </tr>
                    <tr>
                        <th>QR Code Size (px)</th>
                        <td><input type="number" name="tmv_qr_size" value="<?php echo intval(get_option('tmv_qr_size', 200)); ?>" min="100" max="500" /></td>
                    </tr>
                </table>
                <input type="submit" name="tmv_save_settings" class="button button-primary" value="Save Settings" />
            </form>
        </div>
        <?php
    }
    
    // =========================================================================
    // UTILITY METHODS
    // =========================================================================
    
    private static function count_by_status($status) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'tmv_status' AND meta_value = %s",
            $status
        ));
    }
    
    public static function generate_verify_code() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 20; $i++) {
            $code .= $chars[wp_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }
}
