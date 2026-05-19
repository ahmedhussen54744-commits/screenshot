<?php
if (!defined('ABSPATH')) exit;

class TMV_Security {
    
    public static function init() {
        // Existing hooks
        add_action('init', array(__CLASS__, 'prevent_enumeration'));
        add_action('init', array(__CLASS__, 'enforce_ip_blacklist'));
        add_action('wp_head', array(__CLASS__, 'add_copyright_protection_script'));
        add_action('wp_head', array(__CLASS__, 'add_copyright_meta_tags'));
        add_filter('rest_authentication_errors', array(__CLASS__, 'restrict_rest_api'));
        add_action('login_failed', array(__CLASS__, 'log_failed_login'));
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', array(__CLASS__, 'security_headers'));
        
        // Remove WP version from all locations
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('rss2_head', 'the_generator');
        remove_action('commentsrss2_head', 'the_generator');
        remove_action('rss_head', 'the_generator');
        remove_action('atom_head', 'the_generator');
        remove_action('rdf_header', 'the_generator');
        
        // Filter to empty generator
        add_filter('the_generator', '__return_empty_string');
        
        // Remove version from scripts and styles (frontend only)
        add_filter('style_loader_src', array(__CLASS__, 'remove_version_query'), 10, 2);
        add_filter('script_loader_src', array(__CLASS__, 'remove_version_query'), 10, 2);
        
        // CSP via send_headers
        add_action('send_headers', array(__CLASS__, 'send_csp_header'));
        
        // Rate limiting for AJAX
        add_action('wp_ajax_nopriv_tmv_submit_application', array(__CLASS__, 'check_rate_limit_ajax'), 5);
        add_action('wp_ajax_nopriv_tmv_verify_trademark', array(__CLASS__, 'check_rate_limit_ajax'), 5);
        
        // Disable file editing if option set
        if (get_option('tmv_disallow_file_edit', false)) {
            add_filter('file_edit_pre', array(__CLASS__, 'block_file_edit'));
        }
        
        // Log security init
        self::log_security_event('security_init', 'TMV Security initialized');
    }
    
    /**
     * A) Copyright protection - comprehensive JavaScript output in wp_head
     */
    public static function add_copyright_protection_script() {
        ?>
        <script>
        (function(){
            'use strict';
            
            // Check if user is admin (body has logged-in-admin class)
            var isAdmin = document.body && document.body.classList.contains('logged-in') && document.body.classList.contains('admin-bar');
            
            if (!isAdmin) {
                // Disable right-click globally
                document.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Disable keyboard shortcuts
                document.addEventListener('keydown', function(e) {
                    // Ctrl+U (view source)
                    if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
                        e.preventDefault();
                        return false;
                    }
                    // Ctrl+S (save)
                    if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                        e.preventDefault();
                        return false;
                    }
                    // Ctrl+C (copy) on protected elements
                    if (e.ctrlKey && (e.key === 'c' || e.key === 'C')) {
                        var sel = window.getSelection();
                        if (sel && sel.anchorNode) {
                            var el = sel.anchorNode.parentElement;
                            while (el) {
                                if (el.classList && el.classList.contains('tmv-protected')) {
                                    e.preventDefault();
                                    return false;
                                }
                                el = el.parentElement;
                            }
                        }
                    }
                    // Ctrl+Shift+I (DevTools)
                    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i')) {
                        e.preventDefault();
                        return false;
                    }
                    // Ctrl+Shift+J (Console)
                    if (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j')) {
                        e.preventDefault();
                        return false;
                    }
                    // F12
                    if (e.key === 'F12') {
                        e.preventDefault();
                        return false;
                    }
                    // Print Screen attempt
                    if (e.key === 'PrintScreen') {
                        navigator.clipboard.writeText('');
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Disable text selection on protected elements via JS
                document.addEventListener('DOMContentLoaded', function() {
                    var protectedEls = document.querySelectorAll('.tmv-protected, .tmv-verify-result, .tmv-certificate-display');
                    protectedEls.forEach(function(el) {
                        el.style.userSelect = 'none';
                        el.style.webkitUserSelect = 'none';
                        el.style.msUserSelect = 'none';
                        el.style.MozUserSelect = 'none';
                        el.addEventListener('selectstart', function(e) { e.preventDefault(); });
                    });
                });
                
                // Disable image dragging globally
                document.addEventListener('dragstart', function(e) {
                    if (e.target.tagName === 'IMG') {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Override copy event to add copyright notice
                document.addEventListener('copy', function(e) {
                    var selection = window.getSelection().toString();
                    if (selection.length > 0) {
                        var copyright = '\n\n--- \nSource: DPDT Registry Cloud Interface\nCopyright (c) 2026. All Rights Reserved.\nUnauthorized reproduction is prohibited.\n---';
                        e.clipboardData.setData('text/plain', selection + copyright);
                        e.preventDefault();
                    }
                });
            }
            
            // Console warning message (always shown)
            if (window.console) {
                console.log('%c STOP!', 'color: red; font-size: 60px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);');
                console.log('%c This is a protected government system.', 'color: #1a5c3a; font-size: 18px; font-weight: bold;');
                console.log('%c Unauthorized access, inspection, or reproduction of content is strictly prohibited.', 'color: #333; font-size: 14px;');
                console.log('%c Violators will be prosecuted under applicable cyber laws.', 'color: #c00; font-size: 14px; font-weight: bold;');
                console.log('%c DPDT Registry Cloud Interface - All Rights Reserved', 'color: #666; font-size: 12px;');
            }
            
            // Debugger detection (timing-based)
            var devtoolsOpen = false;
            var threshold = 160;
            setInterval(function() {
                var widthThreshold = window.outerWidth - window.innerWidth > threshold;
                var heightThreshold = window.outerHeight - window.innerHeight > threshold;
                if (widthThreshold || heightThreshold) {
                    if (!devtoolsOpen) {
                        devtoolsOpen = true;
                        console.log('%c DevTools detected - Activity is being monitored.', 'color: red; font-size: 16px; font-weight: bold;');
                    }
                } else {
                    devtoolsOpen = false;
                }
            }, 1000);
        })();
        </script>
        <?php
    }
    
    /**
     * B) Copyright meta tags in wp_head
     */
    public static function add_copyright_meta_tags() {
        $copyright_text = get_option('tmv_copyright_text', '(c) 2026 DPDT Registry Cloud Interface. All Rights Reserved.');
        echo '<meta name="copyright" content="' . esc_attr($copyright_text) . '" />' . "\n";
        echo '<meta name="author" content="DPDT Registry Cloud Interface" />' . "\n";
        echo '<meta name="rights" content="All Rights Reserved" />' . "\n";
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
        echo '<meta http-equiv="X-Content-Type-Options" content="nosniff" />' . "\n";
        echo '<link rel="license" href="https://dpdt-registry.gov.bd/license" />' . "\n";
        echo '<!-- Security: Directory browsing should be disabled via server config -->' . "\n";
        echo '<!-- Security: .well-known/security.txt should be configured on server -->' . "\n";
    }
    
    /**
     * C) Security headers via wp_headers filter
     */
    public static function security_headers($headers) {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'DENY';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        $headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=(), payment=()';
        $headers['Cross-Origin-Opener-Policy'] = 'same-origin';
        $headers['Cross-Origin-Resource-Policy'] = 'same-origin';
        return $headers;
    }
    
    /**
     * D) CSP header via send_headers action
     */
    public static function send_csp_header() {
        if (is_admin()) {
            return;
        }
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' cdnjs.cloudflare.com cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'none';");
        // Remove X-Powered-By
        header_remove('X-Powered-By');
    }
    
    /**
     * E) Remove version query string from scripts/styles on frontend
     */
    public static function remove_version_query($src, $handle = '') {
        if (is_admin()) {
            return $src;
        }
        if (strpos($src, '?ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
    
    /**
     * F) Rate limiting for AJAX endpoints
     */
    public static function rate_limit($action, $max = 10, $window = 60) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $key = 'tmv_rl_' . md5($ip . '_' . $action);
        $data = get_transient($key);
        
        if ($data === false) {
            set_transient($key, 1, $window);
            return true;
        }
        
        if ($data >= $max) {
            return false;
        }
        
        set_transient($key, $data + 1, $window);
        return true;
    }
    
    /**
     * Check rate limit on AJAX requests (hook callback)
     */
    public static function check_rate_limit_ajax() {
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : 'unknown';
        if (!self::rate_limit($action, 10, 60)) {
            self::log_security_event('rate_limit_exceeded', 'Rate limit exceeded for action: ' . $action);
            wp_send_json_error(array('message' => 'Too many requests. Please try again later.'));
        }
    }
    
    /**
     * G) Honeypot field helper - render hidden field
     */
    public static function render_honeypot() {
        return '<div style="position:absolute;left:-9999px;"><input type="text" name="tmv_website_url" value="" tabindex="-1" autocomplete="off"></div>';
    }
    
    /**
     * G) Honeypot field helper - check if filled (bot detected)
     */
    public static function check_honeypot() {
        if (isset($_POST['tmv_website_url']) && !empty($_POST['tmv_website_url'])) {
            self::log_security_event('honeypot_triggered', 'Bot detected via honeypot field');
            return true;
        }
        return false;
    }
    
    /**
     * H) Prevent author enumeration
     */
    public static function prevent_enumeration() {
        if (!is_admin() && isset($_GET['author'])) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }
    
    /**
     * H) Enforce IP blacklist with whitelist bypass
     */
    public static function enforce_ip_blacklist() {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (empty($ip)) {
            return;
        }

        // Check if IP is temporarily blocked from failed logins
        if (get_transient('tmv_blocked_' . md5($ip))) {
            wp_die('Access denied. Your IP address has been temporarily blocked due to suspicious activity.', 'Forbidden', array('response' => 403));
        }

        // Check whitelist first
        $whitelist = get_option('tmv_ip_whitelist', array());
        if (!empty($whitelist) && is_array($whitelist) && in_array($ip, $whitelist, true)) {
            return;
        }

        // Check blacklist
        $blacklist = get_option('tmv_ip_blacklist', array());
        if (empty($blacklist) || !is_array($blacklist)) {
            return;
        }
        if (in_array($ip, $blacklist, true)) {
            wp_die('Access denied. Your IP address has been blocked.', 'Forbidden', array('response' => 403));
        }
    }
    
    /**
     * H) Restrict REST API for non-logged users
     */
    public static function restrict_rest_api($result) {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'REST API restricted', array('status' => 403));
        }
        return $result;
    }
    
    /**
     * H) Log failed logins and block IP after 5 attempts
     */
    public static function log_failed_login($username) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $time = current_time('mysql');
        $log = get_option('tmv_failed_logins', array());
        $log[] = array('user' => sanitize_text_field($username), 'ip' => $ip, 'time' => $time);
        
        // Keep last 100 entries
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        update_option('tmv_failed_logins', $log, false);
        
        // Block IP after 5 failed attempts in last hour
        $ip_attempts = array_filter($log, function($entry) use ($ip) {
            return $entry['ip'] === $ip && strtotime($entry['time']) > (time() - 3600);
        });
        
        if (count($ip_attempts) >= 5) {
            set_transient('tmv_blocked_' . md5($ip), true, 3600);
            self::log_security_event('ip_blocked', 'IP blocked after 5 failed logins: ' . $ip);
        }
    }
    
    /**
     * I) Block file editing
     */
    public static function block_file_edit($content) {
        wp_die('File editing is disabled for security reasons.', 'Forbidden', array('response' => 403));
        return $content;
    }
    
    /**
     * I) Log security events
     */
    public static function log_security_event($type, $message) {
        $log = get_option('tmv_security_log', array());
        $log[] = array(
            'type' => sanitize_text_field($type),
            'message' => sanitize_text_field($message),
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
            'time' => current_time('mysql'),
        );
        
        // Keep last 200 entries
        if (count($log) > 200) {
            $log = array_slice($log, -200);
        }
        update_option('tmv_security_log', $log, false);
    }
}
