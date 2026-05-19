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

// Remove X-Powered-By header (all other security headers handled by TMV_Security class)
add_action('send_headers', function() {
    if (!is_admin()) {
        header_remove('X-Powered-By');
    }
});

// Remove WP version from all generator locations
add_filter('the_generator', '__return_empty_string');
add_filter('style_loader_src', 'tmv_remove_version_query', 10, 2);
add_filter('script_loader_src', 'tmv_remove_version_query', 10, 2);

/**
 * Remove version query string from frontend scripts/styles
 */
function tmv_remove_version_query($src, $handle = '') {
    if (is_admin()) {
        return $src;
    }
    if (strpos($src, '?ver=') !== false) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

// Remove generator from RSS feeds
add_action('init', function() {
    remove_action('rss2_head', 'the_generator');
    remove_action('commentsrss2_head', 'the_generator');
    remove_action('rss_head', 'the_generator');
    remove_action('atom_head', 'the_generator');
    remove_action('rdf_header', 'the_generator');
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
