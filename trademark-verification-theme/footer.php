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
        <div class="tmv-footer-legal">
            <p class="tmv-footer-legal-text">All content, trademarks, and data on this platform are protected by international copyright and intellectual property laws. Unauthorized reproduction, distribution, or use of any materials without prior written consent is strictly prohibited.</p>
            <p class="tmv-footer-legal-notice">Protected by DPDT Registry Digital Rights Management System</p>
        </div>
    </div>

    <!-- Copyright Watermark Overlay -->
    <div class="tmv-copyright-watermark" aria-hidden="true">
        <span class="tmv-copyright-watermark-text" style="top:10%;left:5%;">DPDT Registry - Protected Content</span>
        <span class="tmv-copyright-watermark-text" style="top:30%;left:35%;">DPDT Registry - Protected Content</span>
        <span class="tmv-copyright-watermark-text" style="top:50%;left:15%;">DPDT Registry - Protected Content</span>
        <span class="tmv-copyright-watermark-text" style="top:70%;left:50%;">DPDT Registry - Protected Content</span>
        <span class="tmv-copyright-watermark-text" style="top:90%;left:25%;">DPDT Registry - Protected Content</span>
    </div>

    <!-- Fallback copyright protection script -->
    <script>
    (function(){
        if (!document.body.classList.contains('logged-in') || !document.body.classList.contains('admin-bar')) {
            document.addEventListener('contextmenu', function(e){ e.preventDefault(); });
            document.addEventListener('selectstart', function(e){
                if (e.target && e.target.closest && e.target.closest('.tmv-protected, .tmv-verify-result, .tmv-certificate-display')) {
                    e.preventDefault();
                }
            });
        }
    })();
    </script>

    <noscript>
        <style>
            .tmv-noscript-warning {
                display: block !important;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background: #c00;
                color: #fff;
                text-align: center;
                padding: 12px;
                z-index: 99999;
                font-weight: bold;
            }
        </style>
        <div class="tmv-noscript-warning">
            JavaScript is required for full functionality and security features of this site.
        </div>
    </noscript>
</footer>

<?php wp_footer(); ?>
</body>
</html>
