</main>

<footer class="tmv-site-footer">
    <div class="tmv-footer-content">
        <div class="tmv-footer-logo">
            <?php if (class_exists('TMV_Logos')) { echo TMV_Logos::get_bd_seal_html(60, 60); } ?>
            <h3>Trademark Verification System</h3>
        </div>
        <p class="tmv-footer-text">
            <?php echo esc_html(get_theme_mod('tmv_footer_text', '© 2026 DPDT Registry Cloud Interface. Powered by TRICK A4IF Technology Solutions.')); ?>
        </p>
        <p class="tmv-footer-established">
            Established since <?php echo esc_html(get_theme_mod('tmv_established_year', '2009')); ?>
        </p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
