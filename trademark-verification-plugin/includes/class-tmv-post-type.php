<?php
if (!defined('ABSPATH')) exit;

class TMV_Post_Type {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'register'));
        add_action('init', array(__CLASS__, 'register_taxonomy'));
    }
    
    public static function register() {
        $labels = array(
            'name' => 'Trademark Applications',
            'singular_name' => 'Trademark Application',
            'menu_name' => 'Trademarks',
            'add_new' => 'Add New Application',
            'add_new_item' => 'Add New Trademark Application',
            'edit_item' => 'Edit Application',
            'view_item' => 'View Application',
            'all_items' => 'All Applications',
            'search_items' => 'Search Applications',
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-shield-alt',
            'supports' => array('title'),
            'rewrite' => false,
        );
        
        register_post_type('trademark_app', $args);
        
        // Register custom statuses
        register_post_status('tmv_pending', array(
            'label' => 'Pending Review',
            'public' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>'),
        ));
        
        register_post_status('tmv_approved', array(
            'label' => 'Approved',
            'public' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>'),
        ));
        
        register_post_status('tmv_rejected', array(
            'label' => 'Rejected',
            'public' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>'),
        ));
    }
    
    public static function register_taxonomy() {
        register_taxonomy('trademark_class', 'trademark_app', array(
            'labels' => array(
                'name' => 'Trademark Classes',
                'singular_name' => 'Trademark Class',
            ),
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
        ));
    }
}
