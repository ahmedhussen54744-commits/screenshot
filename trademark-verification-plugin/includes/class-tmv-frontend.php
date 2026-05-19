<?php
if (!defined('ABSPATH')) exit;

class TMV_Frontend {
    
    public static function init() {
        add_shortcode('tmv_application_form', array(__CLASS__, 'render_application_form'));
        add_shortcode('tmv_verification_portal', array(__CLASS__, 'render_verification_portal'));
        add_shortcode('tmv_news_feed', array(__CLASS__, 'render_news_feed'));
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
                        <?php echo TMV_Logos::get_dpdt_logo_html(80, 80); ?>
                    </div>
                    <h2 class="tmv-form-title">Trademark Registration Application</h2>
                    <p class="tmv-form-subtitle">Department of Patents, Designs & Trademarks</p>
                </div>
                
                <form id="tmv-application-form" method="post" enctype="multipart/form-data" class="tmv-form">
                    <input type="hidden" name="action" value="tmv_submit_application" />
                    <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_submit_app'); ?>" />
                    <?php echo TMV_Security::render_honeypot(); ?>
                    
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
                    <?php echo TMV_Logos::get_bd_seal_html(80, 80); ?>
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
                        <?php echo TMV_Security::render_honeypot(); ?>
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
    
    public static function render_news_feed($atts = array()) {
        $atts = shortcode_atts(array(
            'count' => 6,
            'category' => '',
        ), $atts, 'tmv_news_feed');

        $query_args = array(
            'post_type' => 'post',
            'posts_per_page' => intval($atts['count']),
            'post_status' => 'publish',
        );

        if (!empty($atts['category'])) {
            $query_args['category_name'] = sanitize_text_field($atts['category']);
        }

        $news_query = new WP_Query($query_args);

        ob_start();
        ?>
        <div class="tmv-news-section tmv-news-shortcode">
            <div class="tmv-news-grid">
                <?php
                if ($news_query->have_posts()) :
                    while ($news_query->have_posts()) : $news_query->the_post();
                        ?>
                        <article class="tmv-post-card">
                            <div class="tmv-post-thumbnail">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium_large'); ?>
                                <?php endif; ?>
                                <?php
                                $video_id = get_post_meta(get_the_ID(), 'tmv_post_video', true);
                                if ($video_id) : ?>
                                    <div class="tmv-play-overlay">
                                        <div class="tmv-play-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M8 5v14l11-7z" fill="#1a5c3a"/>
                                            </svg>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tmv-post-content">
                                <div class="tmv-post-meta">
                                    <span class="tmv-post-date"><?php echo get_the_date(); ?></span>
                                    <span class="tmv-post-category"><?php
                                        $categories = get_the_category();
                                        if (!empty($categories)) {
                                            echo esc_html($categories[0]->name);
                                        }
                                    ?></span>
                                </div>
                                <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                                <a href="<?php the_permalink(); ?>" class="tmv-read-more">Read More</a>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No news posts found.</p>';
                endif;
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_application() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['tmv_nonce'], 'tmv_submit_app')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Honeypot check
        if (TMV_Security::check_honeypot()) {
            wp_send_json_error(array('message' => 'Request blocked'));
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
        $post_args = array(
            'post_type' => 'trademark_app',
            'post_title' => $tm_number . ' - ' . $owner,
            'post_status' => 'publish',
        );

        if (is_user_logged_in()) {
            $post_args['post_author'] = get_current_user_id();
        }

        $post_id = wp_insert_post($post_args);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => 'Failed to submit application.'));
        }

        // Associate user with application
        if (is_user_logged_in()) {
            update_post_meta($post_id, 'tmv_applicant_user_id', get_current_user_id());
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
        
        // Honeypot check
        if (TMV_Security::check_honeypot()) {
            wp_send_json_error(array('message' => 'Request blocked'));
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
        
        // Log verification attempt
        $log = get_option('tmv_verification_log', array());
        $log[] = array(
            'code' => $search,
            'result' => $query->have_posts() ? 'found' : 'not_found',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'time' => current_time('mysql')
        );
        if (count($log) > 500) $log = array_slice($log, -500);
        update_option('tmv_verification_log', $log, false);
        
        if (!$query->have_posts()) {
            wp_send_json_error(array('message' => 'No verified trademark found with this number.'));
        }
        
        $post = $query->posts[0];
        
        // Increment verification count on the found post
        $verify_count = intval(get_post_meta($post->ID, 'tmv_verify_count', true));
        update_post_meta($post->ID, 'tmv_verify_count', $verify_count + 1);
        
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
