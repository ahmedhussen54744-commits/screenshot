</main>

<footer class="tmv-site-footer">
    <div class="tmv-footer-inner">
        <div class="tmv-footer-columns">
            <div class="tmv-footer-col tmv-footer-col--about">
                <div class="tmv-footer-logo">
                    <?php if (class_exists('TMV_Logos')) { echo TMV_Logos::get_bd_seal_html(50, 50); } ?>
                    <h3>Trademark Verification System</h3>
                </div>
                <p class="tmv-footer-desc">Official Digital Platform for verifying registered trademarks under the Department of Patents, Designs &amp; Trademarks, Bangladesh.</p>
                <div class="tmv-footer-social">
                    <?php
                    $social_links = array(
                        'facebook'  => get_option('tmv_social_facebook', ''),
                        'twitter'   => get_option('tmv_social_twitter', ''),
                        'linkedin'  => get_option('tmv_social_linkedin', ''),
                        'youtube'   => get_option('tmv_social_youtube', ''),
                    );
                    foreach ($social_links as $platform => $url) :
                        if (!empty($url)) :
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="tmv-social-link tmv-social-link--<?php echo esc_attr($platform); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                            <span class="tmv-social-icon tmv-social-icon--<?php echo esc_attr($platform); ?>"></span>
                        </a>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <div class="tmv-footer-col tmv-footer-col--links">
                <h4 class="tmv-footer-heading">Quick Links</h4>
                <ul class="tmv-footer-menu">
                    <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
                    <li><a href="<?php echo home_url('/verify/'); ?>">Verify Trademark</a></li>
                    <li><a href="<?php echo home_url('/apply/'); ?>">Apply for Trademark</a></li>
                    <li><a href="<?php echo home_url('/blog/'); ?>">News</a></li>
                    <li><a href="<?php echo home_url('/faq/'); ?>">FAQ</a></li>
                    <li><a href="<?php echo home_url('/contact/'); ?>">Contact Us</a></li>
                </ul>
            </div>

            <div class="tmv-footer-col tmv-footer-col--services">
                <h4 class="tmv-footer-heading">Services</h4>
                <ul class="tmv-footer-menu">
                    <li><a href="<?php echo home_url('/services/'); ?>">Our Services</a></li>
                    <li><a href="<?php echo home_url('/verify/'); ?>">Certificate Verification</a></li>
                    <li><a href="<?php echo home_url('/apply/'); ?>">Trademark Registration</a></li>
                    <li><a href="<?php echo home_url('/about/'); ?>">About Us</a></li>
                    <li><a href="<?php echo home_url('/search/'); ?>">Search Trademarks</a></li>
                </ul>
            </div>

            <div class="tmv-footer-col tmv-footer-col--legal">
                <h4 class="tmv-footer-heading">Legal</h4>
                <ul class="tmv-footer-menu">
                    <li><a href="<?php echo home_url('/privacy-policy/'); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo home_url('/terms/'); ?>">Terms of Service</a></li>
                    <li><a href="<?php echo home_url('/disclaimer/'); ?>">Disclaimer</a></li>
                    <li><a href="<?php echo home_url('/accessibility/'); ?>">Accessibility</a></li>
                </ul>
            </div>
        </div>

        <div class="tmv-footer-bottom">
            <p class="tmv-footer-text">
                <?php echo esc_html(get_theme_mod('tmv_footer_text', '© 2026 DPDT Registry Cloud Interface. Powered by TRICK A4IF Technology Solutions.')); ?>
            </p>
            <p class="tmv-footer-established">
                Established since <?php echo esc_html(get_theme_mod('tmv_established_year', '2009')); ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
