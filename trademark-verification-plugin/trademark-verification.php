<?php
/**
 * Plugin Name: Trademark Verification System
 * Plugin URI: https://dpdt-registry.gov.bd
 * Description: Advanced Trademark Certificate Verification System with 3D UI, QR Code, Admin Dashboard, Application Management
 * Version: 2.0.0
 * Author: DPDT Registry Cloud Interface
 * Author URI: https://dpdt-registry.gov.bd
 * License: GPL v2 or later
 * Text Domain: trademark-verify
 * Requires PHP: 7.4
 * Since: 2009
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TMV_VERSION', '2.0.0');
define('TMV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TMV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TMV_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Security Headers
add_action('send_headers', function() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
});

// Include files
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-post-type.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-admin.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-frontend.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-security.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-api.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-logos.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-certificate.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-auth.php';
require_once TMV_PLUGIN_DIR . 'includes/class-tmv-admin-extended.php';

// Activation Hook
register_activation_hook(__FILE__, 'tmv_activate_plugin');
function tmv_activate_plugin() {
    TMV_Post_Type::register();
    flush_rewrite_rules();
    
    // Default options
    $defaults = array(
        'tmv_verify_base_url' => home_url('/verify/'),
        'tmv_site_established' => '2009',
        'tmv_qr_size' => 200,
        'tmv_max_applications' => 1000000,
        'tmv_security_key' => wp_generate_password(32, true, true),
        'tmv_copyright_text' => '© 2026 DPDT Registry Cloud Interface. Powered by TRICK A4IF Technology Solutions.',
    );
    
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }

    // Extended settings defaults
    $extended_defaults = array(
        'tmv_site_mode' => 'live',
        'tmv_registration_open' => '1',
        'tmv_default_user_role' => 'subscriber',
        'tmv_max_upload_size' => '10',
        'tmv_allowed_file_types' => 'jpg,jpeg,png,pdf',
        'tmv_auto_approve' => '0',
        'tmv_certificate_validity_days' => '365',
        'tmv_watermark_text' => 'DPDT Registry',
        'tmv_watermark_opacity' => '30',
        'tmv_admin_footer_text' => '',
        'tmv_timezone' => 'Asia/Dhaka',
        'tmv_date_format' => 'Y-m-d',
        'tmv_application_prefix' => 'TM-',
        'tmv_require_email_verification' => '0',
        'tmv_primary_color' => '#1d4ed8',
        'tmv_accent_color' => '#10b981',
        'tmv_dark_mode' => '0',
        'tmv_custom_css' => '',
        'tmv_header_style' => 'solid',
        'tmv_font_family' => 'Inter',
        'tmv_animation_enabled' => '1',
        'tmv_logo_max_width' => '200',
        'tmv_card_border_radius' => '12',
        'tmv_shadow_intensity' => 'medium',
        'tmv_notify_new_application' => '1',
        'tmv_notify_approval' => '1',
        'tmv_notify_rejection' => '1',
        'tmv_admin_email_recipients' => '',
        'tmv_email_digest_frequency' => 'none',
        'tmv_slack_webhook_url' => '',
        'tmv_notification_sound' => '1',
        'tmv_sms_notifications' => '0',
        'tmv_push_notifications' => '0',
        'tmv_auto_reminder_days' => '7',
        'tmv_report_recipients' => '',
        'tmv_scheduled_reports' => '0',
        'tmv_auto_assign_reviewer' => '0',
        'tmv_default_reviewer' => '',
        'tmv_escalation_days' => '14',
        'tmv_sla_warning_days' => '7',
        'tmv_auto_archive_days' => '365',
        'tmv_reminder_frequency_days' => '3',
        'tmv_duplicate_detection' => '0',
        'tmv_auto_merge_duplicates' => '0',
        'tmv_priority_levels' => 'low,medium,high,urgent',
        'tmv_required_fields' => 'tm_number,owner,class',
        'tmv_meta_title_template' => '{site_name} - {page_title}',
        'tmv_meta_description_template' => 'Official Trademark Verification System',
        'tmv_og_image' => '',
        'tmv_generate_sitemap' => '0',
        'tmv_structured_data' => '0',
        'tmv_canonical_url_base' => '',
        'tmv_robots_meta' => 'index',
        'tmv_social_preview_title' => '',
        'tmv_social_preview_description' => '',
        'tmv_analytics_tracking_code' => '',
        'tmv_copyright_disable_rightclick' => '1',
        'tmv_copyright_disable_selection' => '0',
        'tmv_copyright_disable_print' => '0',
        'tmv_copyright_disable_devtools' => '1',
        'tmv_copyright_watermark_text' => '',
        'tmv_copyright_watermark_position' => 'center',
        'tmv_copyright_hotlink_protection' => '0',
        'tmv_copyright_image_overlay' => '0',
        'tmv_copyright_dmca_notice' => '',
        'tmv_copyright_footer_text' => '',
        'tmv_copyright_disable_drag' => '1',
        'tmv_copyright_disable_screenshot' => '0',
        'tmv_scheduled_backup' => '0',
        'tmv_backup_frequency' => 'weekly',
        'tmv_last_backup_date' => 'Never',
        'tmv_csp_enabled' => '0',
        'tmv_csp_policy' => '',
        'tmv_single_session' => '0',
        'tmv_auto_blacklist_permanent' => '0',
    );

    foreach ($extended_defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
    
    // Create verification page
    $verify_page = get_page_by_path('verify');
    if (!$verify_page) {
        wp_insert_post(array(
            'post_title' => 'Trademark Verification',
            'post_name' => 'verify',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_verification_portal]',
        ));
    }
    
    // Create apply page
    $apply_page = get_page_by_path('apply');
    if (!$apply_page) {
        wp_insert_post(array(
            'post_title' => 'Apply for Trademark',
            'post_name' => 'apply',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_application_form]',
        ));
    }

    // Create login page
    $login_page = get_page_by_path('login');
    if (!$login_page) {
        wp_insert_post(array(
            'post_title' => 'Login',
            'post_name' => 'login',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_login_form]',
        ));
    }

    // Create register page
    $register_page = get_page_by_path('register');
    if (!$register_page) {
        wp_insert_post(array(
            'post_title' => 'Register',
            'post_name' => 'register',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_register_form]',
        ));
    }

    // Create dashboard page
    $dashboard_page = get_page_by_path('dashboard');
    if (!$dashboard_page) {
        wp_insert_post(array(
            'post_title' => 'Dashboard',
            'post_name' => 'dashboard',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_user_dashboard]',
        ));
    }

    // Create password reset page
    $reset_page = get_page_by_path('password-reset');
    if (!$reset_page) {
        wp_insert_post(array(
            'post_title' => 'Password Reset',
            'post_name' => 'password-reset',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_password_reset]',
        ));
    }

    // Create FAQ page
    $faq_page = get_page_by_path('faq');
    if (!$faq_page) {
        wp_insert_post(array(
            'post_title' => 'Frequently Asked Questions',
            'post_name' => 'faq',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_faq_page]',
        ));
    }

    // Create Contact page
    $contact_page = get_page_by_path('contact');
    if (!$contact_page) {
        wp_insert_post(array(
            'post_title' => 'Contact Us',
            'post_name' => 'contact',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_contact_page]',
        ));
    }

    // Create About page
    $about_page = get_page_by_path('about');
    if (!$about_page) {
        wp_insert_post(array(
            'post_title' => 'About Us',
            'post_name' => 'about',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_about_page]',
        ));
    }

    // Create Services page
    $services_page = get_page_by_path('services');
    if (!$services_page) {
        wp_insert_post(array(
            'post_title' => 'Our Services',
            'post_name' => 'services',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_services_page]',
        ));
    }

    // Create Search page
    $search_page = get_page_by_path('search');
    if (!$search_page) {
        wp_insert_post(array(
            'post_title' => 'Search Trademarks',
            'post_name' => 'search',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[tmv_verification_portal]',
        ));
    }

    // Default FAQ entries
    if (get_option('tmv_faqs') === false) {
        $default_faqs = array(
            array(
                'question' => 'What is trademark verification?',
                'answer'   => 'Trademark verification is the process of confirming the authenticity and registration status of a trademark through our official digital platform.',
            ),
            array(
                'question' => 'How do I verify a trademark certificate?',
                'answer'   => 'Enter your TM number or verification code in the verification portal. The system will search our database and display the registration details if the trademark is valid.',
            ),
            array(
                'question' => 'How long does trademark registration take?',
                'answer'   => 'The trademark registration process typically takes 18-24 months from the date of application, subject to examination and any opposition proceedings.',
            ),
            array(
                'question' => 'What documents are required for trademark registration?',
                'answer'   => 'You need a completed TMR-1 form, brand logo, proof of use (if applicable), applicant identity documents, and the prescribed fee payment receipt.',
            ),
            array(
                'question' => 'How do I contact the DPDT office?',
                'answer'   => 'You can reach us through the contact page, by phone, email, or by visiting our physical office during working hours.',
            ),
        );
        add_option('tmv_faqs', $default_faqs);
    }

    // Default contact info
    if (get_option('tmv_contact_email') === false) {
        add_option('tmv_contact_email', 'info@dpdt-registry.gov.bd');
    }
    if (get_option('tmv_contact_phone') === false) {
        add_option('tmv_contact_phone', '+880-2-1234567');
    }
    if (get_option('tmv_contact_address') === false) {
        add_option('tmv_contact_address', 'Department of Patents, Designs & Trademarks, Dhaka, Bangladesh');
    }
    if (get_option('tmv_contact_hours') === false) {
        add_option('tmv_contact_hours', 'Sunday - Thursday: 9:00 AM - 5:00 PM');
    }
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'tmv_deactivate_plugin');
function tmv_deactivate_plugin() {
    flush_rewrite_rules();
}

// Initialize Plugin
add_action('plugins_loaded', function() {
    TMV_Post_Type::init();
    TMV_Admin::init();
    TMV_Frontend::init();
    TMV_Security::init();
    TMV_API::init();
    TMV_Auth::init();
    TMV_Admin_Extended::init();
});

// Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', 'tmv_enqueue_assets');
function tmv_enqueue_assets() {
    wp_enqueue_style('tmv-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800;900&display=swap', array(), null);
    wp_enqueue_style('tmv-main', TMV_PLUGIN_URL . 'assets/css/main.css', array(), TMV_VERSION);
    wp_enqueue_style('tmv-3d', TMV_PLUGIN_URL . 'assets/css/3d-effects.css', array(), TMV_VERSION);
    wp_enqueue_script('tmv-three', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), null, true);
    wp_enqueue_script('tmv-qrcode', 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js', array(), null, true);
    wp_enqueue_script('tmv-main', TMV_PLUGIN_URL . 'assets/js/main.js', array('jquery'), TMV_VERSION, true);
    wp_enqueue_script('tmv-3d', TMV_PLUGIN_URL . 'assets/js/3d-effects.js', array('tmv-three'), TMV_VERSION, true);
    
    wp_localize_script('tmv-main', 'tmvAjax', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tmv_nonce'),
        'verify_url' => get_option('tmv_verify_base_url', home_url('/verify/')),
    ));
}

// Admin Enqueue
add_action('admin_enqueue_scripts', 'tmv_admin_enqueue');
function tmv_admin_enqueue($hook) {
    if (strpos($hook, 'trademark') !== false || get_post_type() === 'trademark_app' || strpos($hook, 'tmv-settings') !== false) {
        wp_enqueue_style('tmv-admin', TMV_PLUGIN_URL . 'assets/css/admin.css', array(), TMV_VERSION);
        wp_enqueue_script('tmv-qrcode', 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js', array(), null, true);
        wp_enqueue_script('tmv-admin', TMV_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), TMV_VERSION, true);
        wp_enqueue_media();
        wp_localize_script('tmv-admin', 'tmvAdmin', array(
            'nonce' => wp_create_nonce('tmv_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
}
