<?php
if (!defined('ABSPATH')) exit;

class TMV_Auth {

    public static function init() {
        // Register shortcodes
        add_shortcode('tmv_login_form', array(__CLASS__, 'render_login_form'));
        add_shortcode('tmv_register_form', array(__CLASS__, 'render_register_form'));
        add_shortcode('tmv_user_dashboard', array(__CLASS__, 'render_user_dashboard'));
        add_shortcode('tmv_password_reset', array(__CLASS__, 'render_password_reset'));

        // AJAX handlers - Login
        add_action('wp_ajax_tmv_user_login', array(__CLASS__, 'handle_user_login'));
        add_action('wp_ajax_nopriv_tmv_user_login', array(__CLASS__, 'handle_user_login'));

        // AJAX handlers - Register
        add_action('wp_ajax_tmv_user_register', array(__CLASS__, 'handle_user_register'));
        add_action('wp_ajax_nopriv_tmv_user_register', array(__CLASS__, 'handle_user_register'));

        // AJAX handlers - Password Reset
        add_action('wp_ajax_tmv_password_reset_request', array(__CLASS__, 'handle_password_reset_request'));
        add_action('wp_ajax_nopriv_tmv_password_reset_request', array(__CLASS__, 'handle_password_reset_request'));
    }

    /**
     * Render Login Form Shortcode
     */
    public static function render_login_form() {
        ob_start();

        if (is_user_logged_in()) {
            ?>
            <div class="tmv-auth-container">
                <div class="tmv-3d-card" style="text-align:center;">
                    <div class="tmv-form-header">
                        <div class="tmv-shield-icon">
                            <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                        </div>
                        <h2 class="tmv-form-title">You are already logged in</h2>
                        <p class="tmv-form-subtitle">Access your dashboard to manage applications</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="tmv-btn tmv-btn-primary tmv-3d-btn">Go to Dashboard</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        ?>
        <div class="tmv-auth-container">
            <div class="tmv-3d-card">
                <div class="tmv-form-header">
                    <div class="tmv-shield-icon">
                        <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    </div>
                    <h2 class="tmv-form-title">Secure Login</h2>
                    <p class="tmv-form-subtitle">Trademark Verification System</p>
                </div>

                <form id="tmv-login-form" class="tmv-form" method="post">
                    <input type="hidden" name="action" value="tmv_user_login" />
                    <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_auth_nonce'); ?>" />

                    <div class="tmv-form-grid">
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">Email or Username</label>
                            <input type="text" name="tmv_user_login" class="tmv-input" placeholder="Enter your email or username" required />
                        </div>

                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">Password</label>
                            <input type="password" name="tmv_user_pass" class="tmv-input" placeholder="Enter your password" required />
                        </div>

                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-checkbox-label">
                                <input type="checkbox" name="tmv_remember" value="1" />
                                <span>Remember Me</span>
                            </label>
                        </div>
                    </div>

                    <div class="tmv-form-actions">
                        <button type="submit" class="tmv-btn tmv-btn-primary tmv-3d-btn">
                            <span class="tmv-btn-text">Login</span>
                            <span class="tmv-btn-loader" style="display:none;">Processing...</span>
                        </button>
                    </div>
                </form>

                <div id="tmv-login-response" class="tmv-response" style="display:none;"></div>

                <div class="tmv-auth-links">
                    <a href="<?php echo esc_url(home_url('/password-reset/')); ?>" class="tmv-auth-link">Forgot Password?</a>
                    <span class="tmv-auth-separator">|</span>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="tmv-auth-link">Don't have an account? Register</a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Registration Form Shortcode
     */
    public static function render_register_form() {
        ob_start();

        if (is_user_logged_in()) {
            ?>
            <div class="tmv-auth-container">
                <div class="tmv-3d-card" style="text-align:center;">
                    <div class="tmv-form-header">
                        <div class="tmv-shield-icon">
                            <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                        </div>
                        <h2 class="tmv-form-title">You are already registered</h2>
                        <p class="tmv-form-subtitle">Access your dashboard to manage applications</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="tmv-btn tmv-btn-primary tmv-3d-btn">Go to Dashboard</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        ?>
        <div class="tmv-auth-container">
            <div class="tmv-3d-card">
                <div class="tmv-form-header">
                    <div class="tmv-shield-icon">
                        <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    </div>
                    <h2 class="tmv-form-title">Create Account</h2>
                    <p class="tmv-form-subtitle">Register for Trademark Verification System</p>
                </div>

                <form id="tmv-register-form" class="tmv-form" method="post">
                    <input type="hidden" name="action" value="tmv_user_register" />
                    <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_auth_nonce'); ?>" />

                    <div class="tmv-form-grid">
                        <div class="tmv-field-group">
                            <label class="tmv-label">First Name</label>
                            <input type="text" name="tmv_first_name" class="tmv-input" placeholder="First Name" required />
                        </div>

                        <div class="tmv-field-group">
                            <label class="tmv-label">Last Name</label>
                            <input type="text" name="tmv_last_name" class="tmv-input" placeholder="Last Name" required />
                        </div>

                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">Email Address</label>
                            <input type="email" name="tmv_email" class="tmv-input" placeholder="your@email.com" required />
                        </div>

                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">Phone (Optional)</label>
                            <input type="tel" name="tmv_phone" class="tmv-input" placeholder="Phone Number" />
                        </div>

                        <div class="tmv-field-group">
                            <label class="tmv-label">Password</label>
                            <input type="password" name="tmv_password" class="tmv-input" placeholder="Create password" required minlength="6" />
                        </div>

                        <div class="tmv-field-group">
                            <label class="tmv-label">Confirm Password</label>
                            <input type="password" name="tmv_password_confirm" class="tmv-input" placeholder="Confirm password" required minlength="6" />
                        </div>
                    </div>

                    <div class="tmv-form-actions">
                        <button type="submit" class="tmv-btn tmv-btn-primary tmv-3d-btn">
                            <span class="tmv-btn-text">Register</span>
                            <span class="tmv-btn-loader" style="display:none;">Processing...</span>
                        </button>
                    </div>
                </form>

                <div id="tmv-register-response" class="tmv-response" style="display:none;"></div>

                <div class="tmv-auth-links">
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="tmv-auth-link">Already have an account? Login</a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render User Dashboard Shortcode
     */
    public static function render_user_dashboard() {
        ob_start();

        if (!is_user_logged_in()) {
            ?>
            <div class="tmv-auth-container">
                <div class="tmv-3d-card" style="text-align:center;">
                    <div class="tmv-form-header">
                        <div class="tmv-shield-icon">
                            <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                        </div>
                        <h2 class="tmv-form-title">Login Required</h2>
                        <p class="tmv-form-subtitle">Please login to access your dashboard</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="tmv-btn tmv-btn-primary tmv-3d-btn">Login</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Query applications by this user
        $apps_query = new WP_Query(array(
            'post_type' => 'trademark_app',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'tmv_applicant_user_id',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            ),
            'author' => $user_id,
        ));

        // Also query by author separately and merge
        $author_query = new WP_Query(array(
            'post_type' => 'trademark_app',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'author' => $user_id,
        ));

        $meta_query = new WP_Query(array(
            'post_type' => 'trademark_app',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => 'tmv_applicant_user_id',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            ),
        ));

        // Merge results, avoiding duplicates
        $all_posts = array();
        $post_ids = array();

        if ($author_query->have_posts()) {
            foreach ($author_query->posts as $post) {
                if (!in_array($post->ID, $post_ids)) {
                    $all_posts[] = $post;
                    $post_ids[] = $post->ID;
                }
            }
        }

        if ($meta_query->have_posts()) {
            foreach ($meta_query->posts as $post) {
                if (!in_array($post->ID, $post_ids)) {
                    $all_posts[] = $post;
                    $post_ids[] = $post->ID;
                }
            }
        }

        // Calculate stats
        $total = count($all_posts);
        $pending = 0;
        $approved = 0;
        $rejected = 0;

        foreach ($all_posts as $post) {
            $status = get_post_meta($post->ID, 'tmv_status', true);
            if ($status === 'approved') {
                $approved++;
            } elseif ($status === 'rejected') {
                $rejected++;
            } else {
                $pending++;
            }
        }
        ?>
        <div class="tmv-dashboard-container">
            <!-- Welcome Card -->
            <div class="tmv-dashboard-welcome">
                <h2>Welcome, <?php echo esc_html($current_user->display_name); ?></h2>
                <p>Manage your trademark applications and account</p>
            </div>

            <!-- Stats Row -->
            <div class="tmv-dashboard-stats">
                <div class="tmv-stat-card">
                    <div class="tmv-stat-number"><?php echo intval($total); ?></div>
                    <div class="tmv-stat-label">Total Applications</div>
                </div>
                <div class="tmv-stat-card tmv-stat-pending">
                    <div class="tmv-stat-number"><?php echo intval($pending); ?></div>
                    <div class="tmv-stat-label">Pending</div>
                </div>
                <div class="tmv-stat-card tmv-stat-approved">
                    <div class="tmv-stat-number"><?php echo intval($approved); ?></div>
                    <div class="tmv-stat-label">Approved</div>
                </div>
                <div class="tmv-stat-card tmv-stat-rejected">
                    <div class="tmv-stat-number"><?php echo intval($rejected); ?></div>
                    <div class="tmv-stat-label">Rejected</div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="tmv-dashboard-section">
                <h3 class="tmv-section-title">Your Applications</h3>
                <?php if (empty($all_posts)) : ?>
                    <div class="tmv-empty-state">
                        <p>You have no applications yet.</p>
                        <a href="<?php echo esc_url(home_url('/apply/')); ?>" class="tmv-btn tmv-btn-primary tmv-3d-btn">Apply Now</a>
                    </div>
                <?php else : ?>
                    <div class="tmv-table-wrapper">
                        <table class="tmv-dashboard-table">
                            <thead>
                                <tr>
                                    <th>TM Number</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                    <th>Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_posts as $post) :
                                    $tm_number = get_post_meta($post->ID, 'tmv_tm_number', true);
                                    $owner = get_post_meta($post->ID, 'tmv_owner', true);
                                    $status = get_post_meta($post->ID, 'tmv_status', true);
                                    $reg_date = get_post_meta($post->ID, 'tmv_reg_date', true);
                                    $cert_jpg = get_post_meta($post->ID, 'tmv_certificate_jpg', true);
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($tm_number); ?></strong></td>
                                    <td><?php echo esc_html($owner); ?></td>
                                    <td>
                                        <span class="tmv-status-badge tmv-status-<?php echo esc_attr($status); ?>">
                                            <?php echo esc_html(ucfirst($status ?: 'pending')); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($reg_date); ?></td>
                                    <td>
                                        <?php if ($status === 'approved' && $cert_jpg) : ?>
                                            <a href="<?php echo esc_url(wp_get_attachment_url($cert_jpg)); ?>" target="_blank" class="tmv-cert-link">View Certificate</a>
                                        <?php else : ?>
                                            <span class="tmv-text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Section -->
            <div class="tmv-dashboard-section">
                <h3 class="tmv-section-title">Your Profile</h3>
                <div class="tmv-profile-card">
                    <div class="tmv-profile-info">
                        <div class="tmv-profile-row">
                            <span class="tmv-profile-label">Name</span>
                            <span class="tmv-profile-value"><?php echo esc_html($current_user->display_name); ?></span>
                        </div>
                        <div class="tmv-profile-row">
                            <span class="tmv-profile-label">Email</span>
                            <span class="tmv-profile-value"><?php echo esc_html($current_user->user_email); ?></span>
                        </div>
                        <div class="tmv-profile-row">
                            <span class="tmv-profile-label">Member Since</span>
                            <span class="tmv-profile-value"><?php echo esc_html(date('F j, Y', strtotime($current_user->user_registered))); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logout -->
            <div class="tmv-dashboard-actions">
                <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="tmv-btn tmv-btn-primary tmv-3d-btn">Logout</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Password Reset Form Shortcode
     */
    public static function render_password_reset() {
        ob_start();
        ?>
        <div class="tmv-auth-container">
            <div class="tmv-3d-card">
                <div class="tmv-form-header">
                    <div class="tmv-shield-icon">
                        <svg viewBox="0 0 24 24" width="56" height="56"><path fill="#1a5c3a" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    </div>
                    <h2 class="tmv-form-title">Reset Password</h2>
                    <p class="tmv-form-subtitle">Enter your email to receive a reset link</p>
                </div>

                <form id="tmv-reset-form" class="tmv-form" method="post">
                    <input type="hidden" name="action" value="tmv_password_reset_request" />
                    <input type="hidden" name="tmv_nonce" value="<?php echo wp_create_nonce('tmv_auth_nonce'); ?>" />

                    <div class="tmv-form-grid">
                        <div class="tmv-field-group tmv-full-width">
                            <label class="tmv-label">Email Address</label>
                            <input type="email" name="tmv_reset_email" class="tmv-input" placeholder="Enter your registered email" required />
                        </div>
                    </div>

                    <div class="tmv-form-actions">
                        <button type="submit" class="tmv-btn tmv-btn-primary tmv-3d-btn">
                            <span class="tmv-btn-text">Send Reset Link</span>
                            <span class="tmv-btn-loader" style="display:none;">Processing...</span>
                        </button>
                    </div>
                </form>

                <div id="tmv-reset-response" class="tmv-response" style="display:none;"></div>

                <div class="tmv-auth-links">
                    <a href="<?php echo esc_url(home_url('/login/')); ?>" class="tmv-auth-link">Back to Login</a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handler: User Login
     */
    public static function handle_user_login() {
        if (!wp_verify_nonce($_POST['tmv_nonce'] ?? '', 'tmv_auth_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // Rate limiting: 10 attempts per hour per IP
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient_key = 'tmv_rate_login_' . md5($ip);
        $attempts = get_transient($transient_key);
        if ($attempts && $attempts >= 10) {
            wp_send_json_error(array('message' => 'Too many login attempts. Please try again later.'));
        }
        set_transient($transient_key, ($attempts ? $attempts + 1 : 1), 3600);

        $login = sanitize_text_field($_POST['tmv_user_login'] ?? '');
        $password = $_POST['tmv_user_pass'] ?? '';
        $remember = !empty($_POST['tmv_remember']);

        if (empty($login) || empty($password)) {
            wp_send_json_error(array('message' => 'Please enter both username/email and password.'));
        }

        $credentials = array(
            'user_login'    => $login,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon($credentials, is_ssl());

        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid credentials. Please try again.'));
        }

        // Check if user is banned
        if (get_user_meta($user->ID, 'tmv_user_banned', true)) {
            wp_logout();
            wp_send_json_error(array('message' => 'Your account has been suspended.'));
        }

        wp_send_json_success(array(
            'message' => 'Login successful! Redirecting...',
            'redirect' => home_url('/dashboard/'),
        ));
    }

    /**
     * AJAX Handler: User Registration
     */
    public static function handle_user_register() {
        if (!wp_verify_nonce($_POST['tmv_nonce'] ?? '', 'tmv_auth_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // Rate limiting: 5 attempts per hour per IP
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient_key = 'tmv_rate_register_' . md5($ip);
        $attempts = get_transient($transient_key);
        if ($attempts && $attempts >= 5) {
            wp_send_json_error(array('message' => 'Too many registration attempts. Please try again later.'));
        }
        set_transient($transient_key, ($attempts ? $attempts + 1 : 1), 3600);

        $first_name = sanitize_text_field($_POST['tmv_first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['tmv_last_name'] ?? '');
        $email = sanitize_email($_POST['tmv_email'] ?? '');
        $phone = sanitize_text_field($_POST['tmv_phone'] ?? '');
        $password = $_POST['tmv_password'] ?? '';
        $password_confirm = $_POST['tmv_password_confirm'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        }

        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }

        if ($password !== $password_confirm) {
            wp_send_json_error(array('message' => 'Passwords do not match.'));
        }

        if (strlen($password) < 6) {
            wp_send_json_error(array('message' => 'Password must be at least 6 characters.'));
        }

        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'An account with this email already exists.'));
        }

        $username = sanitize_user(strtolower($first_name . '.' . $last_name));
        if (username_exists($username)) {
            $username = $username . '_' . wp_rand(100, 999);
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => 'Registration failed. Please try again.'));
        }

        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
        ));

        if (!empty($phone)) {
            update_user_meta($user_id, 'tmv_phone', $phone);
        }

        // Set role to subscriber
        $user = new WP_User($user_id);
        $user->set_role('subscriber');

        wp_send_json_success(array(
            'message' => 'Registration successful! You can now login.',
            'redirect' => home_url('/login/'),
        ));
    }

    /**
     * AJAX Handler: Password Reset Request
     */
    public static function handle_password_reset_request() {
        if (!wp_verify_nonce($_POST['tmv_nonce'] ?? '', 'tmv_auth_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $email = sanitize_email($_POST['tmv_reset_email'] ?? '');

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }

        $user = get_user_by('email', $email);

        if ($user) {
            $result = retrieve_password($user->user_login);
        }

        // Always return the same message regardless of whether the email exists
        wp_send_json_success(array(
            'message' => 'If an account exists with that email, a password reset link has been sent.',
        ));
    }
}
