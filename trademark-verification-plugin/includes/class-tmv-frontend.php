<?php
if (!defined('ABSPATH')) exit;

class TMV_Frontend {
    
    public static function init() {
        add_shortcode('tmv_application_form', array(__CLASS__, 'render_application_form'));
        add_shortcode('tmv_verification_portal', array(__CLASS__, 'render_verification_portal'));
        add_action('wp_ajax_tmv_submit_application', array(__CLASS__, 'handle_application'));
        add_action('wp_ajax_nopriv_tmv_submit_application', array(__CLASS__, 'handle_application'));
        add_action('wp_ajax_tmv_verify_trademark', array(__CLASS__, 'verify_trademark'));
        add_action('wp_ajax_nopriv_tmv_verify_trademark', array(__CLASS__, 'verify_trademark'));
    }
    
    public static function render_application_form() {
        ob_start();
        ?>
        <div class="tmv-apply-container" id="tmv-apply-form">
            <div class="tmv-3d-card">
                <div class="tmv-form-header">
                    <div class="tmv-dpdt-logo">
                        <svg viewBox="0 0 120 120" width="80" height="80">
                            <circle cx="60" cy="60" r="58" fill="#1a5c3a" stroke="#ffd700" stroke-width="2"/>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#fff" stroke-width="1"/>
                            <!-- 4 quadrants: agriculture, education, science, industry -->
                            <line x1="60" y1="15" x2="60" y2="105" stroke="#fff" stroke-width="0.5" opacity="0.5"/>
                            <line x1="15" y1="60" x2="105" y2="60" stroke="#fff" stroke-width="0.5" opacity="0.5"/>
                            <!-- Agriculture (top-left) -->
                            <path d="M35 35 L38 28 L41 35 M36 32 Q38 25 40 32" fill="none" stroke="#ffd700" stroke-width="1.5"/>
                            <path d="M33 40 Q38 38 43 40" fill="none" stroke="#ffd700" stroke-width="1"/>
                            <!-- Education (top-right) -->
                            <path d="M75 30 L80 27 L85 30 L80 33 Z" fill="#ffd700"/>
                            <line x1="80" y1="33" x2="80" y2="40" stroke="#ffd700" stroke-width="1"/>
                            <!-- Science (bottom-left) -->
                            <circle cx="38" cy="78" r="5" fill="none" stroke="#ffd700" stroke-width="1"/>
                            <circle cx="38" cy="78" r="2" fill="#ffd700"/>
                            <ellipse cx="38" cy="78" rx="8" ry="3" fill="none" stroke="#ffd700" stroke-width="0.8" transform="rotate(45 38 78)"/>
                            <!-- Industry (bottom-right) -->
                            <path d="M75 75 L78 68 L81 75 L84 68 L87 75 L87 85 L75 85 Z" fill="none" stroke="#ffd700" stroke-width="1.2"/>
                            <!-- Bengali text around the circle -->
                            <path id="dpdt-text-path" d="M60 12 A48 48 0 1 1 59.9 12" fill="none"/>
                            <text font-size="6" fill="#fff" font-family="sans-serif">
                                <textPath href="#dpdt-text-path" startOffset="5%">&#x09AA;&#x09C7;&#x099F;&#x09C7;&#x09A8;&#x09CD;&#x099F;, &#x09A1;&#x09BF;&#x099C;&#x09BE;&#x0987;&#x09A8; &#x0993; &#x099F;&#x09CD;&#x09B0;&#x09C7;&#x09A1;&#x09AE;&#x09BE;&#x09B0;&#x09CD;&#x0995;&#x09B8; &#x0985;&#x09A7;&#x09BF;&#x09A6;&#x09AA;&#x09CD;&#x09A4;&#x09B0;</textPath>
                            </text>
                            <!-- Center emblem -->
                            <circle cx="60" cy="60" r="12" fill="#fff" opacity="0.15"/>
                            <text x="60" y="63" text-anchor="middle" font-size="8" fill="#fff" font-weight="bold">DPDT</text>
                        </svg>
                    </div>
                    <h2 class="tmv-form-title">Trademark Registration Application</h2>
                    <p class="tmv-form-subtitle">Department of Patents, Designs & Trademarks</p>
                </div>
                
                <form id="tmv-application-form" method="post" enctype="multipart/form-data" class="tmv-form">
                    <input type="hidden" name="action" value="tmv_submit_application" />
                    <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_submit_app'); ?>" />
                    
                    <div class="tmv-form-grid">
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">1. Form Name</label>
                            <input type="text" name="tmv_form_name" value="TMR-1 Form" class="tmv-input" readonly />
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">3. Certificate Title</label>
                            <input type="text" name="tmv_cert_title" value="Certificate of Registration of Trademark [Rule 30(1)]" class="tmv-input" required />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">4. TM Number</label>
                            <input type="text" name="tmv_tm_number" class="tmv-input" placeholder="Enter TM Number" required />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">5. Registration Date</label>
                            <input type="date" name="tmv_reg_date" class="tmv-input" required />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">6. Sealing Date</label>
                            <input type="date" name="tmv_sealing_date" class="tmv-input" required />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">7. Class</label>
                            <input type="text" name="tmv_class" class="tmv-input" placeholder="e.g. 41" required />
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">8. Owner/Company</label>
                            <input type="text" name="tmv_owner" class="tmv-input" placeholder="Full Name or Company" required />
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">9. Address</label>
                            <textarea name="tmv_address" class="tmv-input tmv-textarea" placeholder="Full Address" required></textarea>
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">10. Service Description</label>
                            <textarea name="tmv_service_desc" class="tmv-input tmv-textarea" placeholder="Describe services covered" required></textarea>
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">11. Pen Serial</label>
                            <input type="text" name="tmv_pen_serial" class="tmv-input" placeholder="Pen Serial Number" />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">12. Signatory</label>
                            <input type="text" name="tmv_signatory" class="tmv-input" placeholder="Signatory Name" />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">13. Designation</label>
                            <input type="text" name="tmv_designation" class="tmv-input" placeholder="e.g. Director General" />
                        </div>
                        
                        <div class="tmv-field-group">
                            <label class="tmv-label">14. Sealed Text</label>
                            <input type="text" name="tmv_sealed_text" class="tmv-input" placeholder="Sealed at my direction this" />
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">15. Logo Text / Brand Name</label>
                            <input type="text" name="tmv_logo_text" class="tmv-input" placeholder="Brand/Logo Text" />
                        </div>
                        
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">16. Brand Logo Upload</label>
                            <div class="tmv-file-upload">
                                <input type="file" name="tmv_brand_logo" id="tmv-logo-upload" accept="image/*" class="tmv-file-input" />
                                <label for="tmv-logo-upload" class="tmv-file-label">
                                    <span class="tmv-upload-icon">&#8682;</span>
                                    <span>Choose Logo File (PNG, JPG)</span>
                                </label>
                                <div id="tmv-logo-preview" class="tmv-preview"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tmv-form-actions">
                        <button type="submit" class="tmv-btn tmv-btn-primary tmv-3d-btn">
                            <span class="tmv-btn-text">Submit Application</span>
                            <span class="tmv-btn-loader" style="display:none;">Processing...</span>
                        </button>
                    </div>
                </form>
                
                <div id="tmv-form-response" class="tmv-response" style="display:none;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function render_verification_portal() {
        ob_start();
        ?>
        <div class="tmv-verify-container" id="tmv-verify-portal">
            <div class="tmv-verify-header">
                <div class="tmv-govt-logo">
                    <svg viewBox="0 0 100 100" width="80" height="80">
                        <!-- Outer green border -->
                        <circle cx="50" cy="50" r="48" fill="none" stroke="#27ae60" stroke-width="4"/>
                        <!-- Red circle background -->
                        <circle cx="50" cy="50" r="44" fill="#c0392b"/>
                        <!-- Gold ring -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#ffd700" stroke-width="2"/>
                        <!-- Inner dark circle -->
                        <circle cx="50" cy="50" r="36" fill="#a93226"/>
                        <!-- Bangladesh map outline (stylized) - gold/yellow -->
                        <path d="M45 28 Q47 30 46 33 Q44 36 45 38 L44 40 Q43 42 44 44 Q45 46 44 48 L43 50 Q42 52 43 55 Q44 57 43 59 L42 62 Q43 65 45 67 Q47 69 50 70 Q53 69 55 67 Q57 65 58 62 L57 59 Q56 57 57 55 Q58 52 57 50 L56 48 Q55 46 56 44 Q57 42 56 40 L55 38 Q54 36 55 33 Q53 30 52 28 Q50 26 48 27 Q46 27 45 28 Z" fill="#ffd700" stroke="#ffed4a" stroke-width="0.5"/>
                        <!-- Bengali text - Government of Bangladesh -->
                        <path id="bd-seal-top" d="M50 10 A40 40 0 0 1 90 50" fill="none"/>
                        <path id="bd-seal-bottom" d="M90 50 A40 40 0 0 1 10 50" fill="none"/>
                        <text font-size="5.5" fill="#fff" font-family="sans-serif">
                            <textPath href="#bd-seal-top" startOffset="10%">&#x0997;&#x09A3;&#x09AA;&#x09CD;&#x09B0;&#x099C;&#x09BE;&#x09A4;&#x09A8;&#x09CD;&#x09A4;&#x09CD;&#x09B0;&#x09C0;</textPath>
                        </text>
                        <text font-size="5.5" fill="#fff" font-family="sans-serif">
                            <textPath href="#bd-seal-bottom" startOffset="15%">&#x09AC;&#x09BE;&#x0982;&#x09B2;&#x09BE;&#x09A6;&#x09C7;&#x09B6; &#x09B8;&#x09B0;&#x0995;&#x09BE;&#x09B0;</textPath>
                        </text>
                    </svg>
                </div>
                <h1 class="tmv-verify-title">Trademark Verification System</h1>
                <p class="tmv-verify-dept">DEPARTMENT OF PATENTS, DESIGNS & TRADEMARKS</p>
            </div>
            
            <div class="tmv-verify-card tmv-3d-card">
                <div class="tmv-verify-inner">
                    <div class="tmv-shield-verify">
                        <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    </div>
                    <h2 class="tmv-portal-title">Digital Verification Portal</h2>
                    <p class="tmv-portal-subtitle">Enter the registered trademark number to verify authenticity.</p>
                    
                    <form id="tmv-verify-form" class="tmv-verify-search" method="post">
                        <input type="hidden" name="action" value="tmv_verify_trademark" />
                        <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_verify_nonce'); ?>" />
                        <div class="tmv-search-wrapper">
                            <input type="text" name="tmv_search_code" id="tmv-search-input" class="tmv-search-input" placeholder="Enter TM Number or Verification Code" required maxlength="20" autocomplete="off" />
                            <button type="submit" class="tmv-search-btn tmv-3d-btn">
                                <span>&#128269; Search</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="tmv-verify-loading" class="tmv-verify-card tmv-3d-card" style="display:none;">
                <div class="tmv-loading-content">
                    <div class="tmv-spinner"></div>
                    <p class="tmv-loading-text">Retrieving secure data...</p>
                </div>
            </div>
            
            <div id="tmv-verify-result" class="tmv-verify-result" style="display:none;"></div>
            
            <div class="tmv-verify-footer">
                <p><?php echo esc_html(get_option('tmv_copyright_text', '© 2026 DPDT Registry Cloud Interface. Powered by TRICK A4IF Technology Solutions.')); ?></p>
                <p class="tmv-established">Established since 2009</p>
            </div>
            
            <script>
            (function() {
                var urlParams = new URLSearchParams(window.location.search);
                var autoCode = urlParams.get('code');
                if (autoCode) {
                    document.addEventListener('DOMContentLoaded', function() {
                        var input = document.getElementById('tmv-search-input');
                        if (input) {
                            input.value = autoCode;
                            setTimeout(function() {
                                document.getElementById('tmv-verify-form').dispatchEvent(new Event('submit', {bubbles: true}));
                            }, 800);
                        }
                    });
                }
            })();
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function handle_application() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['tmv_nonce'], 'tmv_submit_app')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient_key = 'tmv_rate_' . md5($ip);
        $attempts = get_transient($transient_key);
        if ($attempts && $attempts > 5) {
            wp_send_json_error(array('message' => 'Too many attempts. Please try again later.'));
        }
        set_transient($transient_key, ($attempts ? $attempts + 1 : 1), 3600);
        
        // Sanitize inputs
        $tm_number = sanitize_text_field($_POST['tmv_tm_number'] ?? '');
        $cert_title = sanitize_text_field($_POST['tmv_cert_title'] ?? '');
        $owner = sanitize_text_field($_POST['tmv_owner'] ?? '');
        $address = sanitize_textarea_field($_POST['tmv_address'] ?? '');
        $service_desc = sanitize_textarea_field($_POST['tmv_service_desc'] ?? '');
        $class = sanitize_text_field($_POST['tmv_class'] ?? '');
        $reg_date = sanitize_text_field($_POST['tmv_reg_date'] ?? '');
        $sealing_date = sanitize_text_field($_POST['tmv_sealing_date'] ?? '');
        $pen_serial = sanitize_text_field($_POST['tmv_pen_serial'] ?? '');
        $signatory = sanitize_text_field($_POST['tmv_signatory'] ?? '');
        $designation = sanitize_text_field($_POST['tmv_designation'] ?? '');
        $sealed_text = sanitize_text_field($_POST['tmv_sealed_text'] ?? '');
        $logo_text = sanitize_text_field($_POST['tmv_logo_text'] ?? '');
        
        if (empty($tm_number) || empty($owner)) {
            wp_send_json_error(array('message' => 'TM Number and Owner are required.'));
        }
        
        // Create post
        $post_id = wp_insert_post(array(
            'post_type' => 'trademark_app',
            'post_title' => $tm_number . ' - ' . $owner,
            'post_status' => 'publish',
        ));
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => 'Failed to submit application.'));
        }
        
        // Save meta
        update_post_meta($post_id, 'tmv_tm_number', $tm_number);
        update_post_meta($post_id, 'tmv_cert_title', $cert_title);
        update_post_meta($post_id, 'tmv_owner', $owner);
        update_post_meta($post_id, 'tmv_address', $address);
        update_post_meta($post_id, 'tmv_service_desc', $service_desc);
        update_post_meta($post_id, 'tmv_class', $class);
        update_post_meta($post_id, 'tmv_reg_date', $reg_date);
        update_post_meta($post_id, 'tmv_sealing_date', $sealing_date);
        update_post_meta($post_id, 'tmv_pen_serial', $pen_serial);
        update_post_meta($post_id, 'tmv_signatory', $signatory);
        update_post_meta($post_id, 'tmv_designation', $designation);
        update_post_meta($post_id, 'tmv_sealed_text', $sealed_text);
        update_post_meta($post_id, 'tmv_logo_text', $logo_text);
        update_post_meta($post_id, 'tmv_status', 'pending');
        update_post_meta($post_id, 'tmv_application_date', current_time('Y-m-d'));
        
        // Generate verify code (20 chars)
        $verify_code = TMV_Admin::generate_verify_code();
        update_post_meta($post_id, 'tmv_verify_code', $verify_code);
        
        // Handle logo upload
        if (!empty($_FILES['tmv_brand_logo']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            
            $attachment_id = media_handle_upload('tmv_brand_logo', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'tmv_brand_logo', $attachment_id);
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Application submitted successfully! Your verification code: ' . $verify_code,
            'code' => $verify_code,
        ));
    }
    
    public static function verify_trademark() {
        if (!wp_verify_nonce($_POST['tmv_nonce'], 'tmv_verify_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        $search = sanitize_text_field($_POST['tmv_search_code'] ?? '');
        
        if (empty($search)) {
            wp_send_json_error(array('message' => 'Please enter a TM number or verification code.'));
        }
        
        // Search by verify code or TM number
        $args = array(
            'post_type' => 'trademark_app',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array('key' => 'tmv_verify_code', 'value' => $search, 'compare' => '='),
                    array('key' => 'tmv_tm_number', 'value' => $search, 'compare' => '='),
                ),
                array('key' => 'tmv_status', 'value' => 'approved', 'compare' => '='),
            ),
        );
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            wp_send_json_error(array('message' => 'No verified trademark found with this number.'));
        }
        
        $post = $query->posts[0];
        $meta = get_post_meta($post->ID);
        
        $logo_url = '';
        $logo_id = $meta['tmv_brand_logo'][0] ?? '';
        if ($logo_id) {
            $logo_url = wp_get_attachment_url($logo_id);
        }
        
        $cert_jpg_url = '';
        $cert_jpg_id = $meta['tmv_certificate_jpg'][0] ?? '';
        if ($cert_jpg_id) {
            $cert_jpg_url = wp_get_attachment_url($cert_jpg_id);
        }
        
        $result = array(
            'found' => true,
            'tm_number' => $meta['tmv_tm_number'][0] ?? '',
            'cert_title' => $meta['tmv_cert_title'][0] ?? '',
            'owner' => $meta['tmv_owner'][0] ?? '',
            'address' => $meta['tmv_address'][0] ?? '',
            'service_desc' => $meta['tmv_service_desc'][0] ?? '',
            'class' => $meta['tmv_class'][0] ?? '',
            'reg_date' => $meta['tmv_reg_date'][0] ?? '',
            'sealing_date' => $meta['tmv_sealing_date'][0] ?? '',
            'expiry_date' => $meta['tmv_expiry_date'][0] ?? '',
            'approved_date' => $meta['tmv_approved_date'][0] ?? '',
            'signatory' => $meta['tmv_signatory'][0] ?? '',
            'designation' => $meta['tmv_designation'][0] ?? '',
            'logo_text' => $meta['tmv_logo_text'][0] ?? '',
            'logo_url' => $logo_url,
            'certificate_jpg' => $cert_jpg_url,
            'status' => 'verified',
            'verify_code' => $meta['tmv_verify_code'][0] ?? '',
        );
        
        wp_send_json_success($result);
    }
}
