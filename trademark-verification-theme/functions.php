<?php
/**
 * TMV Theme Functions
 * Trademark Verification System Theme
 * Established: 2009
 */

if (!defined('ABSPATH')) exit;

define('TMV_THEME_VERSION', '2.0.0');

// Theme Setup
add_action('after_setup_theme', 'tmv_theme_setup');
function tmv_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'width' => 200,
        'height' => 80,
        'flex-width' => true,
        'flex-height' => true,
    ));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('custom-background');
    add_theme_support('editor-styles');
    
    register_nav_menus(array(
        'primary' => 'Primary Navigation',
        'footer' => 'Footer Navigation',
    ));
}

// Enqueue Styles & Scripts
add_action('wp_enqueue_scripts', 'tmv_theme_enqueue');
function tmv_theme_enqueue() {
    // Google Fonts
    wp_enqueue_style('tmv-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800;900&display=swap', array(), null);
    
    // Theme Style
    wp_enqueue_style('tmv-theme-style', get_stylesheet_uri(), array(), TMV_THEME_VERSION);
    
    // Theme Script
    wp_enqueue_script('tmv-theme-script', get_template_directory_uri() . '/assets/js/theme.js', array('jquery'), TMV_THEME_VERSION, true);
}

// Custom Page Templates
add_filter('theme_page_templates', 'tmv_add_page_templates');
function tmv_add_page_templates($templates) {
    $templates['template-fullwidth.php'] = 'Full Width (No Header/Footer)';
    $templates['template-verify.php'] = 'Verification Portal';
    $templates['template-apply.php'] = 'Application Form';
    return $templates;
}

// Widgets
add_action('widgets_init', 'tmv_widgets_init');
function tmv_widgets_init() {
    register_sidebar(array(
        'name' => 'Footer Widget Area',
        'id' => 'footer-widget',
        'description' => 'Footer widget area',
        'before_widget' => '<div class="tmv-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="tmv-widget-title">',
        'after_title' => '</h4>',
    ));
}

// Security: Remove WP version
remove_action('wp_head', 'wp_generator');

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Custom excerpt length
add_filter('excerpt_length', function() { return 25; });

// Add body classes
add_filter('body_class', 'tmv_body_classes');
function tmv_body_classes($classes) {
    $classes[] = 'tmv-theme';
    if (is_page_template('template-verify.php')) {
        $classes[] = 'tmv-verify-page';
    }
    if (is_page_template('template-apply.php')) {
        $classes[] = 'tmv-apply-page';
    }
    return $classes;
}

// Customizer Settings
add_action('customize_register', 'tmv_customize_register');
function tmv_customize_register($wp_customize) {
    // Site Established Year
    $wp_customize->add_section('tmv_general', array(
        'title' => 'TMV Settings',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('tmv_established_year', array(
        'default' => '2009',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tmv_established_year', array(
        'label' => 'Established Year',
        'section' => 'tmv_general',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('tmv_footer_text', array(
        'default' => '© 2026 DPDT Registry Cloud Interface. Powered by TRICK A4IF Technology Solutions.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tmv_footer_text', array(
        'label' => 'Footer Copyright Text',
        'section' => 'tmv_general',
        'type' => 'textarea',
    ));
    
    $wp_customize->add_setting('tmv_hero_title', array(
        'default' => 'Trademark Verification System',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tmv_hero_title', array(
        'label' => 'Hero Title',
        'section' => 'tmv_general',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('tmv_hero_desc', array(
        'default' => 'Official Digital Platform for verifying registered trademarks under the Department of Patents, Designs & Trademarks, Bangladesh.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('tmv_hero_desc', array(
        'label' => 'Hero Description',
        'section' => 'tmv_general',
        'type' => 'textarea',
    ));
}
