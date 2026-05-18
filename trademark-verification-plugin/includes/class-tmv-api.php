<?php
if (!defined('ABSPATH')) exit;

class TMV_API {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        add_action('template_redirect', array(__CLASS__, 'handle_verify_redirect'));
    }
    
    public static function add_rewrite_rules() {
        add_rewrite_rule('^verify/([A-Za-z0-9]+)/?$', 'index.php?pagename=verify&tmv_code=$matches[1]', 'top');
        add_rewrite_tag('%tmv_code%', '([A-Za-z0-9]+)');
    }
    
    public static function handle_verify_redirect() {
        $code = get_query_var('tmv_code');
        if (!empty($code)) {
            // Auto-fill the verification code
            add_action('wp_footer', function() use ($code) {
                ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var input = document.getElementById('tmv-search-input');
                        if (input) {
                            input.value = '<?php echo esc_js($code); ?>';
                            // Auto-submit
                            setTimeout(function() {
                                document.getElementById('tmv-verify-form').dispatchEvent(new Event('submit'));
                            }, 500);
                        }
                    });
                </script>
                <?php
            });
        }
    }
    
    /**
     * Generate QR Code URL using Google Charts API
     */
    public static function get_qr_code_url($data, $size = 200) {
        $size = intval($size);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
    }
    
    /**
     * Get verification URL for a trademark
     */
    public static function get_verify_url($post_id) {
        $custom_url = get_post_meta($post_id, 'tmv_custom_qr_url', true);
        if (!empty($custom_url)) {
            return $custom_url;
        }
        
        $verify_code = get_post_meta($post_id, 'tmv_verify_code', true);
        $base_url = get_option('tmv_verify_base_url', home_url('/verify/'));
        
        return rtrim($base_url, '/') . '/' . $verify_code;
    }
}
