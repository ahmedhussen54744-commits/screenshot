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
                <button class="tmv-tab-btn" data-tab="settings-extended">Settings Extended</button>
                <button class="tmv-tab-btn" data-tab="appearance">Appearance</button>
                <button class="tmv-tab-btn" data-tab="notifications">Notifications</button>
                <button class="tmv-tab-btn" data-tab="reports">Reports</button>
                <button class="tmv-tab-btn" data-tab="workflow">Workflow</button>
                <button class="tmv-tab-btn" data-tab="seo">SEO</button>
                <button class="tmv-tab-btn" data-tab="copyright">Copyright</button>
                <button class="tmv-tab-btn" data-tab="backup">Backup</button>
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
            <div class="tmv-tab-content" id="tmv-tab-settings-extended">
                <?php TMV_Admin_Extended::render_tab_settings_extended(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-appearance">
                <?php TMV_Admin_Extended::render_tab_appearance(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-notifications">
                <?php TMV_Admin_Extended::render_tab_notifications(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-reports">
                <?php TMV_Admin_Extended::render_tab_reports(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-workflow">
                <?php TMV_Admin_Extended::render_tab_workflow(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-seo">
                <?php TMV_Admin_Extended::render_tab_seo(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-copyright">
                <?php TMV_Admin_Extended::render_tab_copyright(); ?>
            </div>
            <div class="tmv-tab-content" id="tmv-tab-backup">
                <?php TMV_Admin_Extended::render_tab_backup(); ?>
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
