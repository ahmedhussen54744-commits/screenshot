<?php
if (!defined('ABSPATH')) exit;

class TMV_Admin_Extended {

    public static function init() {
        add_action('wp_ajax_tmv_save_extended_settings', array(__CLASS__, 'ajax_save_extended_settings'));
        add_action('wp_ajax_tmv_save_appearance', array(__CLASS__, 'ajax_save_appearance'));
        add_action('wp_ajax_tmv_save_notifications', array(__CLASS__, 'ajax_save_notifications'));
        add_action('wp_ajax_tmv_generate_report', array(__CLASS__, 'ajax_generate_report'));
        add_action('wp_ajax_tmv_save_workflow', array(__CLASS__, 'ajax_save_workflow'));
        add_action('wp_ajax_tmv_save_seo', array(__CLASS__, 'ajax_save_seo'));
        add_action('wp_ajax_tmv_save_copyright', array(__CLASS__, 'ajax_save_copyright'));
        add_action('wp_ajax_tmv_export_backup', array(__CLASS__, 'ajax_export_backup'));
        add_action('wp_ajax_tmv_import_backup', array(__CLASS__, 'ajax_import_backup'));
    }

    // =========================================================================
    // TAB RENDER METHODS
    // =========================================================================

    public static function render_tab_settings_extended() {
        $site_mode = get_option('tmv_site_mode', 'live');
        $registration_open = get_option('tmv_registration_open', '1');
        $default_role = get_option('tmv_default_user_role', 'subscriber');
        $max_upload = get_option('tmv_max_upload_size', '10');
        $allowed_types = get_option('tmv_allowed_file_types', 'jpg,jpeg,png,pdf');
        $auto_approve = get_option('tmv_auto_approve', '0');
        $cert_validity = get_option('tmv_certificate_validity_days', '365');
        $qr_size = get_option('tmv_qr_size', '200');
        $watermark_text = get_option('tmv_watermark_text', 'DPDT Registry');
        $watermark_opacity = get_option('tmv_watermark_opacity', '30');
        $footer_text = get_option('tmv_admin_footer_text', '');
        $timezone = get_option('tmv_timezone', 'Asia/Dhaka');
        $date_format = get_option('tmv_date_format', 'Y-m-d');
        $app_prefix = get_option('tmv_application_prefix', 'TM-');
        $require_email_verify = get_option('tmv_require_email_verification', '0');
        ?>
        <h2>Extended Settings</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Site Configuration</h3>
                <div class="tmv-setting-row">
                    <label>Site Mode</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-site-mode" <?php checked($site_mode, 'maintenance'); ?> />
                        <label for="tmv-site-mode"><span class="tmv-toggle-label-on">Maintenance</span><span class="tmv-toggle-label-off">Live</span></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Registration Open</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-registration-open" <?php checked($registration_open, '1'); ?> />
                        <label for="tmv-registration-open"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Default User Role</label>
                    <select id="tmv-default-role">
                        <option value="subscriber" <?php selected($default_role, 'subscriber'); ?>>Subscriber</option>
                        <option value="contributor" <?php selected($default_role, 'contributor'); ?>>Contributor</option>
                        <option value="author" <?php selected($default_role, 'author'); ?>>Author</option>
                        <option value="editor" <?php selected($default_role, 'editor'); ?>>Editor</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Max Upload Size (MB)</label>
                    <input type="number" id="tmv-max-upload" value="<?php echo esc_attr($max_upload); ?>" min="1" max="100" />
                </div>
                <div class="tmv-setting-row">
                    <label>Allowed File Types</label>
                    <input type="text" id="tmv-allowed-types" value="<?php echo esc_attr($allowed_types); ?>" placeholder="jpg,png,pdf" />
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Application Settings</h3>
                <div class="tmv-setting-row">
                    <label>Auto-Approve Applications</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-auto-approve" <?php checked($auto_approve, '1'); ?> />
                        <label for="tmv-auto-approve"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Certificate Validity (days)</label>
                    <input type="number" id="tmv-cert-validity" value="<?php echo esc_attr($cert_validity); ?>" min="30" max="3650" />
                </div>
                <div class="tmv-setting-row">
                    <label>QR Code Size (px)</label>
                    <input type="range" id="tmv-qr-size-slider" value="<?php echo esc_attr($qr_size); ?>" min="100" max="500" step="10" />
                    <span id="tmv-qr-size-value"><?php echo esc_html($qr_size); ?>px</span>
                </div>
                <div class="tmv-setting-row">
                    <label>Watermark Text</label>
                    <input type="text" id="tmv-watermark-text" value="<?php echo esc_attr($watermark_text); ?>" />
                </div>
                <div class="tmv-setting-row">
                    <label>Watermark Opacity (%)</label>
                    <input type="range" id="tmv-watermark-opacity" value="<?php echo esc_attr($watermark_opacity); ?>" min="0" max="100" step="5" />
                    <span id="tmv-watermark-opacity-value"><?php echo esc_html($watermark_opacity); ?>%</span>
                </div>
                <div class="tmv-setting-row">
                    <label>Footer Text</label>
                    <textarea id="tmv-footer-text" rows="2"><?php echo esc_textarea($footer_text); ?></textarea>
                </div>
                <div class="tmv-setting-row">
                    <label>Timezone</label>
                    <select id="tmv-timezone">
                        <?php foreach (array('Asia/Dhaka','UTC','America/New_York','Europe/London','Asia/Tokyo','Asia/Kolkata') as $tz): ?>
                        <option value="<?php echo esc_attr($tz); ?>" <?php selected($timezone, $tz); ?>><?php echo esc_html($tz); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Date Format</label>
                    <select id="tmv-date-format">
                        <option value="Y-m-d" <?php selected($date_format, 'Y-m-d'); ?>>2024-01-15</option>
                        <option value="d/m/Y" <?php selected($date_format, 'd/m/Y'); ?>>15/01/2024</option>
                        <option value="m/d/Y" <?php selected($date_format, 'm/d/Y'); ?>>01/15/2024</option>
                        <option value="F j, Y" <?php selected($date_format, 'F j, Y'); ?>>January 15, 2024</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Application Number Prefix</label>
                    <input type="text" id="tmv-app-prefix" value="<?php echo esc_attr($app_prefix); ?>" />
                </div>
                <div class="tmv-setting-row">
                    <label>Require Email Verification</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-require-email-verify" <?php checked($require_email_verify, '1'); ?> />
                        <label for="tmv-require-email-verify"></label>
                    </div>
                </div>
            </div>
        </div>
        <button class="button button-primary" id="tmv-save-extended-settings-btn">Save Extended Settings</button>
        <?php
    }

    public static function render_tab_appearance() {
        $primary_color = get_option('tmv_primary_color', '#1d4ed8');
        $accent_color = get_option('tmv_accent_color', '#10b981');
        $dark_mode = get_option('tmv_dark_mode', '0');
        $custom_css = get_option('tmv_custom_css', '');
        $header_style = get_option('tmv_header_style', 'solid');
        $font_family = get_option('tmv_font_family', 'Inter');
        $animation_enabled = get_option('tmv_animation_enabled', '1');
        $logo_max_width = get_option('tmv_logo_max_width', '200');
        $card_border_radius = get_option('tmv_card_border_radius', '12');
        $shadow_intensity = get_option('tmv_shadow_intensity', 'medium');
        ?>
        <h2>Appearance</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Colors and Theme</h3>
                <div class="tmv-setting-row">
                    <label>Primary Color</label>
                    <input type="color" id="tmv-primary-color" value="<?php echo esc_attr($primary_color); ?>" class="tmv-color-picker" />
                    <span class="tmv-color-value"><?php echo esc_html($primary_color); ?></span>
                </div>
                <div class="tmv-setting-row">
                    <label>Accent Color</label>
                    <input type="color" id="tmv-accent-color" value="<?php echo esc_attr($accent_color); ?>" class="tmv-color-picker" />
                    <span class="tmv-color-value"><?php echo esc_html($accent_color); ?></span>
                </div>
                <div class="tmv-setting-row">
                    <label>Dark Mode</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-dark-mode" <?php checked($dark_mode, '1'); ?> />
                        <label for="tmv-dark-mode"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Header Style</label>
                    <div class="tmv-radio-group">
                        <label><input type="radio" name="tmv_header_style" value="solid" <?php checked($header_style, 'solid'); ?> /> Solid</label>
                        <label><input type="radio" name="tmv_header_style" value="gradient" <?php checked($header_style, 'gradient'); ?> /> Gradient</label>
                        <label><input type="radio" name="tmv_header_style" value="transparent" <?php checked($header_style, 'transparent'); ?> /> Transparent</label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Font Family</label>
                    <select id="tmv-font-family">
                        <option value="Inter" <?php selected($font_family, 'Inter'); ?>>Inter</option>
                        <option value="Poppins" <?php selected($font_family, 'Poppins'); ?>>Poppins</option>
                        <option value="Roboto" <?php selected($font_family, 'Roboto'); ?>>Roboto</option>
                        <option value="Open Sans" <?php selected($font_family, 'Open Sans'); ?>>Open Sans</option>
                        <option value="Lato" <?php selected($font_family, 'Lato'); ?>>Lato</option>
                    </select>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Layout and Effects</h3>
                <div class="tmv-setting-row">
                    <label>Animations Enabled</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-animation-enabled" <?php checked($animation_enabled, '1'); ?> />
                        <label for="tmv-animation-enabled"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Logo Max Width (px)</label>
                    <input type="number" id="tmv-logo-max-width" value="<?php echo esc_attr($logo_max_width); ?>" min="50" max="500" />
                </div>
                <div class="tmv-setting-row">
                    <label>Card Border Radius (px)</label>
                    <input type="range" id="tmv-card-border-radius" value="<?php echo esc_attr($card_border_radius); ?>" min="0" max="30" />
                    <span id="tmv-border-radius-value"><?php echo esc_html($card_border_radius); ?>px</span>
                </div>
                <div class="tmv-setting-row">
                    <label>Shadow Intensity</label>
                    <select id="tmv-shadow-intensity">
                        <option value="light" <?php selected($shadow_intensity, 'light'); ?>>Light</option>
                        <option value="medium" <?php selected($shadow_intensity, 'medium'); ?>>Medium</option>
                        <option value="heavy" <?php selected($shadow_intensity, 'heavy'); ?>>Heavy</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="tmv-setting-group tmv-full-width">
            <h3>Custom CSS</h3>
            <textarea id="tmv-custom-css" class="tmv-code-editor" rows="10" placeholder="/* Add your custom CSS here */"><?php echo esc_textarea($custom_css); ?></textarea>
        </div>
        <button class="button button-primary" id="tmv-save-appearance-btn">Save Appearance</button>
        <?php
    }

    public static function render_tab_notifications() {
        $email_new_app = get_option('tmv_notify_new_application', '1');
        $email_approval = get_option('tmv_notify_approval', '1');
        $email_rejection = get_option('tmv_notify_rejection', '1');
        $admin_recipients = get_option('tmv_admin_email_recipients', '');
        $digest_frequency = get_option('tmv_email_digest_frequency', 'none');
        $slack_webhook = get_option('tmv_slack_webhook_url', '');
        $notification_sound = get_option('tmv_notification_sound', '1');
        $sms_notifications = get_option('tmv_sms_notifications', '0');
        $push_notifications = get_option('tmv_push_notifications', '0');
        $auto_reminder_days = get_option('tmv_auto_reminder_days', '7');
        ?>
        <h2>Notifications</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Email Notifications</h3>
                <div class="tmv-setting-row">
                    <label>Email on New Application</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-notify-new-app" <?php checked($email_new_app, '1'); ?> />
                        <label for="tmv-notify-new-app"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Email on Approval</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-notify-approval" <?php checked($email_approval, '1'); ?> />
                        <label for="tmv-notify-approval"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Email on Rejection</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-notify-rejection" <?php checked($email_rejection, '1'); ?> />
                        <label for="tmv-notify-rejection"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Admin Email Recipients</label>
                    <input type="text" id="tmv-admin-recipients" value="<?php echo esc_attr($admin_recipients); ?>" placeholder="admin@example.com, manager@example.com" />
                </div>
                <div class="tmv-setting-row">
                    <label>Email Digest Frequency</label>
                    <select id="tmv-digest-frequency">
                        <option value="none" <?php selected($digest_frequency, 'none'); ?>>None</option>
                        <option value="daily" <?php selected($digest_frequency, 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected($digest_frequency, 'weekly'); ?>>Weekly</option>
                    </select>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Other Notifications</h3>
                <div class="tmv-setting-row">
                    <label>Slack Webhook URL</label>
                    <input type="url" id="tmv-slack-webhook" value="<?php echo esc_attr($slack_webhook); ?>" placeholder="https://hooks.slack.com/..." />
                </div>
                <div class="tmv-setting-row">
                    <label>Notification Sound</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-notification-sound" <?php checked($notification_sound, '1'); ?> />
                        <label for="tmv-notification-sound"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>SMS Notifications</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-sms-notifications" <?php checked($sms_notifications, '1'); ?> />
                        <label for="tmv-sms-notifications"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Push Notifications</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-push-notifications" <?php checked($push_notifications, '1'); ?> />
                        <label for="tmv-push-notifications"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Auto-Reminder for Pending (days)</label>
                    <input type="number" id="tmv-auto-reminder-days" value="<?php echo esc_attr($auto_reminder_days); ?>" min="1" max="90" />
                </div>
            </div>
        </div>
        <button class="button button-primary" id="tmv-save-notifications-btn">Save Notifications</button>
        <?php
    }

    public static function render_tab_reports() {
        $report_recipients = get_option('tmv_report_recipients', '');
        $scheduled_reports = get_option('tmv_scheduled_reports', '0');
        ?>
        <h2>Reports</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Generate Reports</h3>
                <div class="tmv-setting-row">
                    <label>Report Type</label>
                    <select id="tmv-report-type">
                        <option value="applications">Applications</option>
                        <option value="users">Users</option>
                        <option value="verifications">Verifications</option>
                        <option value="certificates">Certificates</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Date From</label>
                    <input type="date" id="tmv-report-date-from" value="<?php echo esc_attr(date('Y-m-01')); ?>" />
                </div>
                <div class="tmv-setting-row">
                    <label>Date To</label>
                    <input type="date" id="tmv-report-date-to" value="<?php echo esc_attr(date('Y-m-d')); ?>" />
                </div>
                <div class="tmv-report-actions">
                    <button class="button button-primary" id="tmv-generate-report-btn">Generate Report</button>
                    <button class="button" id="tmv-export-csv-report-btn">Export CSV</button>
                    <button class="button" id="tmv-export-json-report-btn">Export JSON</button>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Report Settings</h3>
                <div class="tmv-setting-row">
                    <label>Scheduled Reports</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-scheduled-reports" <?php checked($scheduled_reports, '1'); ?> />
                        <label for="tmv-scheduled-reports"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Report Email Recipients</label>
                    <input type="text" id="tmv-report-recipients" value="<?php echo esc_attr($report_recipients); ?>" placeholder="admin@example.com" />
                </div>
            </div>
        </div>
        <div class="tmv-setting-group tmv-full-width">
            <h3>Application Trend (Last 12 Months)</h3>
            <table class="tmv-data-table" id="tmv-report-trend-table">
                <thead><tr><th>Month</th><th>Applications</th><th>Approved</th><th>Rejected</th><th>Pending</th></tr></thead>
                <tbody>
                <?php
                global $wpdb;
                for ($i = 11; $i >= 0; $i--):
                    $month_start = date('Y-m-01', strtotime("-$i months"));
                    $month_end = date('Y-m-t', strtotime("-$i months"));
                    $month_label = date('M Y', strtotime("-$i months"));
                    $total = intval($wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'trademark_app' AND post_date BETWEEN %s AND %s",
                        $month_start, $month_end . ' 23:59:59'
                    )));
                    $app_count = intval($wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'trademark_app' AND pm.meta_key = 'tmv_status' AND pm.meta_value = 'approved' AND p.post_date BETWEEN %s AND %s",
                        $month_start, $month_end . ' 23:59:59'
                    )));
                    $rej_count = intval($wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'trademark_app' AND pm.meta_key = 'tmv_status' AND pm.meta_value = 'rejected' AND p.post_date BETWEEN %s AND %s",
                        $month_start, $month_end . ' 23:59:59'
                    )));
                    $pend_count = $total - $app_count - $rej_count;
                ?>
                    <tr><td><?php echo esc_html($month_label); ?></td><td><?php echo $total; ?></td><td><?php echo $app_count; ?></td><td><?php echo $rej_count; ?></td><td><?php echo $pend_count; ?></td></tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <div id="tmv-report-output" class="tmv-section-box" style="display:none;">
            <h3>Report Output</h3>
            <div id="tmv-report-content"></div>
        </div>
        <?php
    }

    public static function render_tab_workflow() {
        $auto_assign = get_option('tmv_auto_assign_reviewer', '0');
        $default_reviewer = get_option('tmv_default_reviewer', '');
        $escalation_days = get_option('tmv_escalation_days', '14');
        $sla_warning = get_option('tmv_sla_warning_days', '7');
        $auto_archive = get_option('tmv_auto_archive_days', '365');
        $reminder_frequency = get_option('tmv_reminder_frequency_days', '3');
        $duplicate_detection = get_option('tmv_duplicate_detection', '0');
        $auto_merge = get_option('tmv_auto_merge_duplicates', '0');
        $priority_levels = get_option('tmv_priority_levels', 'low,medium,high,urgent');
        $required_fields = get_option('tmv_required_fields', 'tm_number,owner,class');
        $admins = get_users(array('role' => 'administrator'));
        ?>
        <h2>Workflow</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Review Process</h3>
                <div class="tmv-setting-row">
                    <label>Auto-Assign Reviewer</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-auto-assign" <?php checked($auto_assign, '1'); ?> />
                        <label for="tmv-auto-assign"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Default Reviewer</label>
                    <select id="tmv-default-reviewer">
                        <option value="">-- Select --</option>
                        <?php foreach ($admins as $admin): ?>
                        <option value="<?php echo esc_attr($admin->ID); ?>" <?php selected($default_reviewer, $admin->ID); ?>><?php echo esc_html($admin->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Escalation After (days)</label>
                    <input type="number" id="tmv-escalation-days" value="<?php echo esc_attr($escalation_days); ?>" min="1" max="90" />
                </div>
                <div class="tmv-setting-row">
                    <label>SLA Warning Threshold (days)</label>
                    <input type="number" id="tmv-sla-warning" value="<?php echo esc_attr($sla_warning); ?>" min="1" max="60" />
                </div>
                <div class="tmv-setting-row">
                    <label>Auto-Archive After (days)</label>
                    <input type="number" id="tmv-auto-archive" value="<?php echo esc_attr($auto_archive); ?>" min="30" max="3650" />
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Automation</h3>
                <div class="tmv-setting-row">
                    <label>Reminder Frequency (days)</label>
                    <input type="number" id="tmv-reminder-frequency" value="<?php echo esc_attr($reminder_frequency); ?>" min="1" max="30" />
                </div>
                <div class="tmv-setting-row">
                    <label>Duplicate Detection</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-duplicate-detection" <?php checked($duplicate_detection, '1'); ?> />
                        <label for="tmv-duplicate-detection"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Auto-Merge Duplicates</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-auto-merge" <?php checked($auto_merge, '1'); ?> />
                        <label for="tmv-auto-merge"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Priority Levels</label>
                    <input type="text" id="tmv-priority-levels" value="<?php echo esc_attr($priority_levels); ?>" placeholder="low,medium,high,urgent" />
                </div>
                <div class="tmv-setting-row">
                    <label>Required Fields</label>
                    <input type="text" id="tmv-required-fields" value="<?php echo esc_attr($required_fields); ?>" placeholder="tm_number,owner,class" />
                </div>
            </div>
        </div>
        <button class="button button-primary" id="tmv-save-workflow-btn">Save Workflow</button>
        <?php
    }

    public static function render_tab_seo() {
        $meta_title = get_option('tmv_meta_title_template', '{site_name} - {page_title}');
        $meta_desc = get_option('tmv_meta_description_template', 'Official Trademark Verification System');
        $og_image = get_option('tmv_og_image', '');
        $sitemap_toggle = get_option('tmv_generate_sitemap', '0');
        $structured_data = get_option('tmv_structured_data', '0');
        $canonical_base = get_option('tmv_canonical_url_base', '');
        $robots_meta = get_option('tmv_robots_meta', 'index');
        $social_title = get_option('tmv_social_preview_title', '');
        $social_desc = get_option('tmv_social_preview_description', '');
        $analytics_code = get_option('tmv_analytics_tracking_code', '');
        ?>
        <h2>SEO Settings</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Meta Tags</h3>
                <div class="tmv-setting-row">
                    <label>Meta Title Template</label>
                    <input type="text" id="tmv-meta-title" value="<?php echo esc_attr($meta_title); ?>" placeholder="{site_name} - {page_title}" />
                </div>
                <div class="tmv-setting-row">
                    <label>Meta Description Template</label>
                    <textarea id="tmv-meta-desc" rows="2"><?php echo esc_textarea($meta_desc); ?></textarea>
                </div>
                <div class="tmv-setting-row">
                    <label>OG Image URL</label>
                    <input type="url" id="tmv-og-image" value="<?php echo esc_attr($og_image); ?>" placeholder="https://..." />
                </div>
                <div class="tmv-setting-row">
                    <label>Generate Sitemap</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-sitemap-toggle" <?php checked($sitemap_toggle, '1'); ?> />
                        <label for="tmv-sitemap-toggle"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Structured Data (JSON-LD)</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-structured-data" <?php checked($structured_data, '1'); ?> />
                        <label for="tmv-structured-data"></label>
                    </div>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>URLs and Indexing</h3>
                <div class="tmv-setting-row">
                    <label>Canonical URL Base</label>
                    <input type="url" id="tmv-canonical-base" value="<?php echo esc_attr($canonical_base); ?>" placeholder="https://example.com" />
                </div>
                <div class="tmv-setting-row">
                    <label>Robots Meta</label>
                    <select id="tmv-robots-meta">
                        <option value="index" <?php selected($robots_meta, 'index'); ?>>Index, Follow</option>
                        <option value="noindex" <?php selected($robots_meta, 'noindex'); ?>>NoIndex, NoFollow</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Social Preview Title</label>
                    <input type="text" id="tmv-social-title" value="<?php echo esc_attr($social_title); ?>" />
                </div>
                <div class="tmv-setting-row">
                    <label>Social Preview Description</label>
                    <textarea id="tmv-social-desc" rows="2"><?php echo esc_textarea($social_desc); ?></textarea>
                </div>
            </div>
        </div>
        <div class="tmv-setting-group tmv-full-width">
            <h3>Analytics Tracking Code</h3>
            <textarea id="tmv-analytics-code" class="tmv-code-editor" rows="6" placeholder="<!-- Paste Google Analytics or other tracking code here -->"><?php echo esc_textarea($analytics_code); ?></textarea>
        </div>
        <button class="button button-primary" id="tmv-save-seo-btn">Save SEO Settings</button>
        <?php
    }

    public static function render_tab_copyright() {
        $disable_rightclick = get_option('tmv_copyright_disable_rightclick', '1');
        $disable_selection = get_option('tmv_copyright_disable_selection', '0');
        $disable_print = get_option('tmv_copyright_disable_print', '0');
        $disable_devtools = get_option('tmv_copyright_disable_devtools', '1');
        $watermark_text = get_option('tmv_copyright_watermark_text', '');
        $watermark_position = get_option('tmv_copyright_watermark_position', 'center');
        $hotlink_protection = get_option('tmv_copyright_hotlink_protection', '0');
        $image_overlay = get_option('tmv_copyright_image_overlay', '0');
        $dmca_notice = get_option('tmv_copyright_dmca_notice', '');
        $copyright_footer = get_option('tmv_copyright_footer_text', '');
        $disable_drag = get_option('tmv_copyright_disable_drag', '1');
        $disable_screenshot = get_option('tmv_copyright_disable_screenshot', '0');
        ?>
        <h2>Copyright Protection</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Content Protection</h3>
                <div class="tmv-setting-row">
                    <label>Disable Right-Click</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-rightclick" <?php checked($disable_rightclick, '1'); ?> />
                        <label for="tmv-disable-rightclick"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Disable Text Selection</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-selection" <?php checked($disable_selection, '1'); ?> />
                        <label for="tmv-disable-selection"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Disable Print</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-print" <?php checked($disable_print, '1'); ?> />
                        <label for="tmv-disable-print"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Disable Ctrl+U/Ctrl+S/F12</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-devtools" <?php checked($disable_devtools, '1'); ?> />
                        <label for="tmv-disable-devtools"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Disable Image Drag</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-drag" <?php checked($disable_drag, '1'); ?> />
                        <label for="tmv-disable-drag"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Screenshot Protection (CSS)</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-disable-screenshot" <?php checked($disable_screenshot, '1'); ?> />
                        <label for="tmv-disable-screenshot"></label>
                    </div>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Image Protection</h3>
                <div class="tmv-setting-row">
                    <label>Watermark Text for Images</label>
                    <input type="text" id="tmv-copyright-watermark" value="<?php echo esc_attr($watermark_text); ?>" placeholder="DPDT Registry" />
                </div>
                <div class="tmv-setting-row">
                    <label>Watermark Position</label>
                    <select id="tmv-watermark-position">
                        <option value="center" <?php selected($watermark_position, 'center'); ?>>Center</option>
                        <option value="bottom-right" <?php selected($watermark_position, 'bottom-right'); ?>>Bottom Right</option>
                        <option value="bottom-left" <?php selected($watermark_position, 'bottom-left'); ?>>Bottom Left</option>
                        <option value="tiled" <?php selected($watermark_position, 'tiled'); ?>>Tiled</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Hotlink Protection</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-hotlink-protection" <?php checked($hotlink_protection, '1'); ?> />
                        <label for="tmv-hotlink-protection"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Image Overlay Protection</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-image-overlay" <?php checked($image_overlay, '1'); ?> />
                        <label for="tmv-image-overlay"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="tmv-setting-group tmv-full-width">
            <h3>Legal Notices</h3>
            <div class="tmv-setting-row">
                <label>DMCA Notice Text</label>
                <textarea id="tmv-dmca-notice" rows="3"><?php echo esc_textarea($dmca_notice); ?></textarea>
            </div>
            <div class="tmv-setting-row">
                <label>Copyright Footer Text</label>
                <input type="text" id="tmv-copyright-footer" value="<?php echo esc_attr($copyright_footer); ?>" />
            </div>
        </div>
        <button class="button button-primary" id="tmv-save-copyright-btn">Save Copyright Settings</button>
        <?php
    }

    public static function render_tab_backup() {
        $scheduled_backup = get_option('tmv_scheduled_backup', '0');
        $backup_frequency = get_option('tmv_backup_frequency', 'weekly');
        $last_backup = get_option('tmv_last_backup_date', 'Never');
        ?>
        <h2>Backup and Restore</h2>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Export</h3>
                <p class="description">Export all plugin settings as a JSON file for backup or migration.</p>
                <button class="button button-primary" id="tmv-export-settings-btn">Export All Settings (JSON)</button>
                <br /><br />
                <p class="description">Export all trademark applications as a CSV file.</p>
                <button class="button" id="tmv-export-apps-csv-btn">Export Applications (CSV)</button>
            </div>
            <div class="tmv-setting-group">
                <h3>Import</h3>
                <p class="description">Import settings from a previously exported JSON file.</p>
                <div class="tmv-file-drop-zone" id="tmv-import-drop-zone">
                    <p>Drop JSON file here or click to select</p>
                    <input type="file" id="tmv-import-file" accept=".json" style="display:none;" />
                </div>
                <button class="button button-primary" id="tmv-import-settings-btn" disabled>Import Settings</button>
                <span id="tmv-import-status"></span>
            </div>
        </div>
        <div class="tmv-settings-grid">
            <div class="tmv-setting-group">
                <h3>Scheduled Backups</h3>
                <div class="tmv-setting-row">
                    <label>Enable Scheduled Backup</label>
                    <div class="tmv-toggle-switch">
                        <input type="checkbox" id="tmv-scheduled-backup" <?php checked($scheduled_backup, '1'); ?> />
                        <label for="tmv-scheduled-backup"></label>
                    </div>
                </div>
                <div class="tmv-setting-row">
                    <label>Backup Frequency</label>
                    <select id="tmv-backup-frequency">
                        <option value="daily" <?php selected($backup_frequency, 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected($backup_frequency, 'weekly'); ?>>Weekly</option>
                        <option value="monthly" <?php selected($backup_frequency, 'monthly'); ?>>Monthly</option>
                    </select>
                </div>
                <div class="tmv-setting-row">
                    <label>Last Backup</label>
                    <span class="tmv-info-value"><?php echo esc_html($last_backup); ?></span>
                </div>
            </div>
            <div class="tmv-setting-group">
                <h3>Danger Zone</h3>
                <p class="description" style="color:#dc2626;">Warning: This action cannot be undone. All plugin data will be permanently deleted.</p>
                <button class="button" id="tmv-clear-all-data-btn" style="background:#dc2626;color:#fff;border-color:#dc2626;">Clear All Data</button>
            </div>
        </div>
        <?php
    }

    // =========================================================================
    // AJAX HANDLERS
    // =========================================================================

    public static function ajax_save_extended_settings() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_site_mode', sanitize_text_field($_POST['site_mode'] ?? 'live'));
        update_option('tmv_registration_open', sanitize_text_field($_POST['registration_open'] ?? '0'));
        update_option('tmv_default_user_role', sanitize_text_field($_POST['default_role'] ?? 'subscriber'));
        update_option('tmv_max_upload_size', intval($_POST['max_upload'] ?? 10));
        update_option('tmv_allowed_file_types', sanitize_text_field($_POST['allowed_types'] ?? 'jpg,jpeg,png,pdf'));
        update_option('tmv_auto_approve', sanitize_text_field($_POST['auto_approve'] ?? '0'));
        update_option('tmv_certificate_validity_days', intval($_POST['cert_validity'] ?? 365));
        update_option('tmv_qr_size', intval($_POST['qr_size'] ?? 200));
        update_option('tmv_watermark_text', sanitize_text_field($_POST['watermark_text'] ?? ''));
        update_option('tmv_watermark_opacity', intval($_POST['watermark_opacity'] ?? 30));
        update_option('tmv_admin_footer_text', sanitize_textarea_field($_POST['footer_text'] ?? ''));
        update_option('tmv_timezone', sanitize_text_field($_POST['timezone'] ?? 'Asia/Dhaka'));
        update_option('tmv_date_format', sanitize_text_field($_POST['date_format'] ?? 'Y-m-d'));
        update_option('tmv_application_prefix', sanitize_text_field($_POST['app_prefix'] ?? 'TM-'));
        update_option('tmv_require_email_verification', sanitize_text_field($_POST['require_email_verify'] ?? '0'));

        wp_send_json_success(array('message' => 'Extended settings saved'));
    }

    public static function ajax_save_appearance() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_primary_color', sanitize_hex_color($_POST['primary_color'] ?? '#1d4ed8'));
        update_option('tmv_accent_color', sanitize_hex_color($_POST['accent_color'] ?? '#10b981'));
        update_option('tmv_dark_mode', sanitize_text_field($_POST['dark_mode'] ?? '0'));
        update_option('tmv_custom_css', wp_strip_all_tags($_POST['custom_css'] ?? ''));
        update_option('tmv_header_style', sanitize_text_field($_POST['header_style'] ?? 'solid'));
        update_option('tmv_font_family', sanitize_text_field($_POST['font_family'] ?? 'Inter'));
        update_option('tmv_animation_enabled', sanitize_text_field($_POST['animation_enabled'] ?? '1'));
        update_option('tmv_logo_max_width', intval($_POST['logo_max_width'] ?? 200));
        update_option('tmv_card_border_radius', intval($_POST['card_border_radius'] ?? 12));
        update_option('tmv_shadow_intensity', sanitize_text_field($_POST['shadow_intensity'] ?? 'medium'));

        wp_send_json_success(array('message' => 'Appearance settings saved'));
    }

    public static function ajax_save_notifications() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_notify_new_application', sanitize_text_field($_POST['notify_new_app'] ?? '0'));
        update_option('tmv_notify_approval', sanitize_text_field($_POST['notify_approval'] ?? '0'));
        update_option('tmv_notify_rejection', sanitize_text_field($_POST['notify_rejection'] ?? '0'));
        update_option('tmv_admin_email_recipients', sanitize_text_field($_POST['admin_recipients'] ?? ''));
        update_option('tmv_email_digest_frequency', sanitize_text_field($_POST['digest_frequency'] ?? 'none'));
        update_option('tmv_slack_webhook_url', esc_url_raw($_POST['slack_webhook'] ?? ''));
        update_option('tmv_notification_sound', sanitize_text_field($_POST['notification_sound'] ?? '0'));
        update_option('tmv_sms_notifications', sanitize_text_field($_POST['sms_notifications'] ?? '0'));
        update_option('tmv_push_notifications', sanitize_text_field($_POST['push_notifications'] ?? '0'));
        update_option('tmv_auto_reminder_days', intval($_POST['auto_reminder_days'] ?? 7));

        wp_send_json_success(array('message' => 'Notification settings saved'));
    }

    public static function ajax_generate_report() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $type = sanitize_text_field($_POST['report_type'] ?? 'applications');
        $from = sanitize_text_field($_POST['date_from'] ?? '');
        $to = sanitize_text_field($_POST['date_to'] ?? '');

        global $wpdb;
        $data = array();

        switch ($type) {
            case 'applications':
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT p.ID, p.post_date FROM {$wpdb->posts} p WHERE p.post_type = 'trademark_app' AND p.post_date >= %s AND p.post_date <= %s ORDER BY p.post_date DESC",
                    $from . ' 00:00:00', $to . ' 23:59:59'
                ));
                foreach ($results as $r) {
                    $data[] = array(
                        'tm_number' => get_post_meta($r->ID, 'tmv_tm_number', true),
                        'owner' => get_post_meta($r->ID, 'tmv_owner', true),
                        'status' => get_post_meta($r->ID, 'tmv_status', true),
                        'date' => $r->post_date,
                    );
                }
                break;
            case 'users':
                $users = get_users(array('date_query' => array(array('after' => $from, 'before' => $to))));
                foreach ($users as $u) {
                    $data[] = array('username' => $u->user_login, 'email' => $u->user_email, 'registered' => $u->user_registered);
                }
                break;
            case 'verifications':
                $log = get_option('tmv_verification_log', array());
                foreach ($log as $entry) {
                    if (isset($entry['time']) && $entry['time'] >= $from && $entry['time'] <= $to . ' 23:59:59') {
                        $data[] = $entry;
                    }
                }
                break;
            case 'certificates':
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT p.ID, p.post_date FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'trademark_app' AND pm.meta_key = 'tmv_certificate_jpg' AND pm.meta_value != '' AND p.post_date >= %s AND p.post_date <= %s",
                    $from . ' 00:00:00', $to . ' 23:59:59'
                ));
                foreach ($results as $r) {
                    $data[] = array(
                        'tm_number' => get_post_meta($r->ID, 'tmv_tm_number', true),
                        'owner' => get_post_meta($r->ID, 'tmv_owner', true),
                        'generated' => $r->post_date,
                    );
                }
                break;
        }

        wp_send_json_success(array('report_type' => $type, 'count' => count($data), 'data' => $data));
    }

    public static function ajax_save_workflow() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_auto_assign_reviewer', sanitize_text_field($_POST['auto_assign'] ?? '0'));
        update_option('tmv_default_reviewer', intval($_POST['default_reviewer'] ?? 0));
        update_option('tmv_escalation_days', intval($_POST['escalation_days'] ?? 14));
        update_option('tmv_sla_warning_days', intval($_POST['sla_warning'] ?? 7));
        update_option('tmv_auto_archive_days', intval($_POST['auto_archive'] ?? 365));
        update_option('tmv_reminder_frequency_days', intval($_POST['reminder_frequency'] ?? 3));
        update_option('tmv_duplicate_detection', sanitize_text_field($_POST['duplicate_detection'] ?? '0'));
        update_option('tmv_auto_merge_duplicates', sanitize_text_field($_POST['auto_merge'] ?? '0'));
        update_option('tmv_priority_levels', sanitize_text_field($_POST['priority_levels'] ?? 'low,medium,high,urgent'));
        update_option('tmv_required_fields', sanitize_text_field($_POST['required_fields'] ?? 'tm_number,owner,class'));

        wp_send_json_success(array('message' => 'Workflow settings saved'));
    }

    public static function ajax_save_seo() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_meta_title_template', sanitize_text_field($_POST['meta_title'] ?? ''));
        update_option('tmv_meta_description_template', sanitize_textarea_field($_POST['meta_desc'] ?? ''));
        update_option('tmv_og_image', esc_url_raw($_POST['og_image'] ?? ''));
        update_option('tmv_generate_sitemap', sanitize_text_field($_POST['sitemap_toggle'] ?? '0'));
        update_option('tmv_structured_data', sanitize_text_field($_POST['structured_data'] ?? '0'));
        update_option('tmv_canonical_url_base', esc_url_raw($_POST['canonical_base'] ?? ''));
        update_option('tmv_robots_meta', sanitize_text_field($_POST['robots_meta'] ?? 'index'));
        update_option('tmv_social_preview_title', sanitize_text_field($_POST['social_title'] ?? ''));
        update_option('tmv_social_preview_description', sanitize_textarea_field($_POST['social_desc'] ?? ''));
        update_option('tmv_analytics_tracking_code', wp_strip_all_tags($_POST['analytics_code'] ?? ''));

        wp_send_json_success(array('message' => 'SEO settings saved'));
    }

    public static function ajax_save_copyright() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        update_option('tmv_copyright_disable_rightclick', sanitize_text_field($_POST['disable_rightclick'] ?? '0'));
        update_option('tmv_copyright_disable_selection', sanitize_text_field($_POST['disable_selection'] ?? '0'));
        update_option('tmv_copyright_disable_print', sanitize_text_field($_POST['disable_print'] ?? '0'));
        update_option('tmv_copyright_disable_devtools', sanitize_text_field($_POST['disable_devtools'] ?? '0'));
        update_option('tmv_copyright_watermark_text', sanitize_text_field($_POST['watermark_text'] ?? ''));
        update_option('tmv_copyright_watermark_position', sanitize_text_field($_POST['watermark_position'] ?? 'center'));
        update_option('tmv_copyright_hotlink_protection', sanitize_text_field($_POST['hotlink_protection'] ?? '0'));
        update_option('tmv_copyright_image_overlay', sanitize_text_field($_POST['image_overlay'] ?? '0'));
        update_option('tmv_copyright_dmca_notice', sanitize_textarea_field($_POST['dmca_notice'] ?? ''));
        update_option('tmv_copyright_footer_text', sanitize_text_field($_POST['copyright_footer'] ?? ''));
        update_option('tmv_copyright_disable_drag', sanitize_text_field($_POST['disable_drag'] ?? '0'));
        update_option('tmv_copyright_disable_screenshot', sanitize_text_field($_POST['disable_screenshot'] ?? '0'));

        wp_send_json_success(array('message' => 'Copyright settings saved'));
    }

    public static function ajax_export_backup() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        global $wpdb;
        $options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'tmv_%'");
        $export = array();
        foreach ($options as $opt) {
            $export[$opt->option_name] = maybe_unserialize($opt->option_value);
        }

        update_option('tmv_last_backup_date', current_time('Y-m-d H:i:s'));

        wp_send_json_success(array('data' => $export, 'exported_at' => current_time('mysql')));
    }

    public static function ajax_import_backup() {
        check_ajax_referer('tmv_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $json = stripslashes($_POST['import_data'] ?? '');
        $data = json_decode($json, true);

        if (!is_array($data) || empty($data)) {
            wp_send_json_error(array('message' => 'Invalid JSON data'));
            return;
        }

        $count = 0;
        foreach ($data as $key => $value) {
            if (strpos($key, 'tmv_') === 0) {
                update_option($key, $value);
                $count++;
            }
        }

        wp_send_json_success(array('message' => $count . ' settings imported successfully'));
    }
}
