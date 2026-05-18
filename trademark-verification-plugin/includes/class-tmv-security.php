<?php
if (!defined('ABSPATH')) exit;

class TMV_Security {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'prevent_enumeration'));
        add_action('wp_head', array(__CLASS__, 'add_copyright_protection'));
        add_action('wp_head', array(__CLASS__, 'add_security_meta'));
        add_filter('rest_authentication_errors', array(__CLASS__, 'restrict_rest_api'));
        add_action('login_failed', array(__CLASS__, 'log_failed_login'));
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', array(__CLASS__, 'security_headers'));
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
    }
    
    public static function prevent_enumeration() {
        if (!is_admin() && isset($_GET['author'])) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }
    
    public static function add_copyright_protection() {
        ?>
        <script>
        (function(){
            // Disable right click
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Disable keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && (e.key === 'u' || e.key === 'U' || e.key === 's' || e.key === 'S')) {
                    e.preventDefault();
                    return false;
                }
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Disable text selection on protected content
            var protectedElements = document.querySelectorAll('.tmv-verify-result, .tmv-certificate-display');
            protectedElements.forEach(function(el) {
                el.style.userSelect = 'none';
                el.style.webkitUserSelect = 'none';
            });
            
            // Anti-debugging
            (function detect(){
                var start = performance.now();
                debugger;
                var end = performance.now();
                if (end - start > 100) {
                    document.body.innerHTML = '<h1 style="text-align:center;padding:50px;">Access Denied</h1>';
                }
            })();
        })();
        </script>
        <style>
            .tmv-verify-result img,
            .tmv-certificate-display img {
                pointer-events: none;
                -webkit-user-drag: none;
                user-drag: none;
            }
        </style>
        <?php
    }
    
    public static function add_security_meta() {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
        echo '<meta http-equiv="X-Content-Type-Options" content="nosniff" />' . "\n";
    }
    
    public static function restrict_rest_api($result) {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'REST API restricted', array('status' => 403));
        }
        return $result;
    }
    
    public static function log_failed_login($username) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = current_time('mysql');
        $log = get_option('tmv_failed_logins', array());
        $log[] = array('user' => $username, 'ip' => $ip, 'time' => $time);
        
        // Keep last 100 entries
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        update_option('tmv_failed_logins', $log);
        
        // Block IP after 5 failed attempts
        $ip_attempts = array_filter($log, function($entry) use ($ip) {
            return $entry['ip'] === $ip && strtotime($entry['time']) > (time() - 3600);
        });
        
        if (count($ip_attempts) >= 5) {
            set_transient('tmv_blocked_' . md5($ip), true, 3600);
        }
    }
    
    public static function security_headers($headers) {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        return $headers;
    }
}
