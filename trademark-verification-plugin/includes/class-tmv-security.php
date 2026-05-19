<?php
if (!defined('ABSPATH')) exit;

class TMV_Security {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'prevent_enumeration'));
        add_action('init', array(__CLASS__, 'enforce_ip_blacklist'));
        add_action('wp_head', array(__CLASS__, 'add_copyright_protection'));
        add_action('wp_head', array(__CLASS__, 'add_security_meta'));
        add_filter('rest_authentication_errors', array(__CLASS__, 'restrict_rest_api'));
        add_action('login_failed', array(__CLASS__, 'log_failed_login'));
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', array(__CLASS__, 'security_headers'));
        add_action('init', array(__CLASS__, 'enforce_single_session'));
        add_action('send_headers', array(__CLASS__, 'add_csp_header'));
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
    
    public static function enforce_ip_blacklist() {
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check whitelist first - whitelisted IPs bypass the blacklist
        $whitelist = get_option('tmv_ip_whitelist', array());
        if (!empty($whitelist) && is_array($whitelist) && in_array($ip, $whitelist, true)) {
            return;
        }

        $blacklist = get_option('tmv_ip_blacklist', array());
        if (empty($blacklist) || !is_array($blacklist)) {
            return;
        }
        if (in_array($ip, $blacklist, true)) {
            wp_die('Access denied. Your IP address has been blocked.', 'Forbidden', array('response' => 403));
        }
    }
    
    public static function add_copyright_protection() {
        $disable_rightclick = get_option('tmv_copyright_disable_rightclick', '1');
        $disable_selection = get_option('tmv_copyright_disable_selection', '0');
        $disable_print = get_option('tmv_copyright_disable_print', '0');
        $disable_devtools = get_option('tmv_copyright_disable_devtools', '1');
        $disable_drag = get_option('tmv_copyright_disable_drag', '1');
        $disable_screenshot = get_option('tmv_copyright_disable_screenshot', '0');

        $has_js = ($disable_rightclick === '1' || $disable_devtools === '1' || $disable_selection === '1' || $disable_drag === '1');
        $has_css = ($disable_print === '1' || $disable_drag === '1' || $disable_selection === '1' || $disable_screenshot === '1');

        if ($has_js): ?>
        <script>
        (function(){
            <?php if ($disable_rightclick === '1'): ?>
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            <?php endif; ?>

            <?php if ($disable_devtools === '1'): ?>
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
            <?php endif; ?>

            <?php if ($disable_selection === '1'): ?>
            document.body.style.userSelect = 'none';
            document.body.style.webkitUserSelect = 'none';
            <?php endif; ?>

            <?php if ($disable_drag === '1'): ?>
            document.addEventListener('dragstart', function(e) {
                if (e.target.tagName === 'IMG') {
                    e.preventDefault();
                    return false;
                }
            });
            <?php endif; ?>
        })();
        </script>
        <?php endif;

        if ($has_css): ?>
        <style>
            <?php if ($disable_print === '1'): ?>
            @media print {
                body { display: none !important; }
            }
            <?php endif; ?>
            <?php if ($disable_drag === '1'): ?>
            img {
                pointer-events: none;
                -webkit-user-drag: none;
                user-drag: none;
            }
            <?php endif; ?>
            <?php if ($disable_selection === '1'): ?>
            body {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            <?php endif; ?>
            <?php if ($disable_screenshot === '1'): ?>
            body {
                -webkit-filter: none;
                filter: none;
            }
            .tmv-verify-result, .tmv-certificate-display {
                position: relative;
            }
            .tmv-verify-result::after, .tmv-certificate-display::after {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                pointer-events: none;
                background: repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(255,255,255,0.01) 10px,
                    rgba(255,255,255,0.01) 20px
                );
            }
            <?php endif; ?>
        </style>
        <?php endif;
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
        
        // Progressive lockout: count attempts in last 24 hours
        $ip_attempts_24h = array_filter($log, function($entry) use ($ip) {
            return $entry['ip'] === $ip && strtotime($entry['time']) > (time() - 86400);
        });
        $attempt_count = count($ip_attempts_24h);

        // 5 attempts = 1 hour block
        if ($attempt_count >= 15) {
            // Permanent block until admin unlock
            $blacklist = get_option('tmv_ip_blacklist', array());
            if (!in_array($ip, $blacklist, true)) {
                $blacklist[] = $ip;
                update_option('tmv_ip_blacklist', $blacklist);
            }
        } elseif ($attempt_count >= 10) {
            // 24 hour block
            set_transient('tmv_blocked_' . md5($ip), true, 86400);
        } elseif ($attempt_count >= 5) {
            // 1 hour block
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

    public static function enforce_single_session() {
        if (get_option('tmv_single_session', '0') !== '1') {
            return;
        }
        if (!is_user_logged_in()) {
            return;
        }
        $user_id = get_current_user_id();
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $all_sessions = $sessions->get_all();
        if (count($all_sessions) > 1) {
            $current_token = wp_get_session_token();
            $sessions->destroy_others($current_token);
        }
    }

    public static function add_csp_header() {
        if (is_admin()) {
            return;
        }
        $enabled = get_option('tmv_csp_enabled', '0');
        if ($enabled !== '1') {
            return;
        }
        $policy = get_option('tmv_csp_policy', '');
        if (!empty($policy)) {
            header('Content-Security-Policy: ' . $policy);
        }
    }

    public static function render_honeypot_field() {
        echo '<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">';
        echo '<input type="text" name="tmv_hp_field" value="" tabindex="-1" autocomplete="off" />';
        echo '</div>';
    }

    public static function check_honeypot() {
        if (!empty($_POST['tmv_hp_field'])) {
            wp_die('Spam detected.', 'Forbidden', array('response' => 403));
        }
    }
}
