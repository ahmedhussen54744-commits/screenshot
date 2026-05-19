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
        
        // Auto-generate certificate on approval
        $cert_id = false;
        try {
            if (class_exists('TMV_Certificate')) {
                $cert_id = TMV_Certificate::generate($post_id);
            }
        } catch (Exception $e) {
            // Certificate generation failure should not block approval
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
    
    public static function render_dashboard() {
        $total = wp_count_posts('trademark_app');
        $pending = self::count_by_status('pending');
        $approved = self::count_by_status('approved');
        $rejected = self::count_by_status('rejected');
        ?>
        <div class="wrap tmv-dashboard">
            <h1>Trademark Verification Dashboard</h1>
            <div class="tmv-stats-grid">
                <div class="tmv-stat-card tmv-stat-total">
                    <h3><?php echo intval($total->publish + ($total->draft ?? 0)); ?></h3>
                    <p>Total Applications</p>
                </div>
                <div class="tmv-stat-card tmv-stat-pending">
                    <h3><?php echo intval($pending); ?></h3>
                    <p>Pending Review</p>
                </div>
                <div class="tmv-stat-card tmv-stat-approved">
                    <h3><?php echo intval($approved); ?></h3>
                    <p>Approved</p>
                </div>
                <div class="tmv-stat-card tmv-stat-rejected">
                    <h3><?php echo intval($rejected); ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
            
            <h2>Recent Applications</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>TM Number</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Actions</th>
                    </tr>
                </thead>
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
                        <td>
                            <a href="<?php echo get_edit_post_link($app->ID); ?>" class="button button-small">Edit</a>
                            <?php if ($status === 'pending'): ?>
                            <button class="button button-small button-primary tmv-quick-approve" data-id="<?php echo $app->ID; ?>">Approve</button>
                            <button class="button button-small tmv-quick-reject" data-id="<?php echo $app->ID; ?>">Reject</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
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
                <p class="description">Upload custom logos to replace the default inline SVGs. These logos appear in the header, footer, certificate, apply form, and verify page.</p>
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
                            <p class="description">Appears in: header (top-left), apply form header, certificate. Leave empty to use the default DPDT SVG.</p>
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
                            <p class="description">Appears in: footer, verify page header. Leave empty to use the default Bangladesh Govt seal SVG.</p>
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
