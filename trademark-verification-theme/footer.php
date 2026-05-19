</main>

<footer class="tmv-site-footer">
    <div class="tmv-footer-content">
        <div class="tmv-footer-logo">
            <svg viewBox="0 0 100 100" width="60" height="60" style="display:block;margin:0 auto 12px;">
                <circle cx="50" cy="50" r="48" fill="none" stroke="#27ae60" stroke-width="4"/>
                <circle cx="50" cy="50" r="44" fill="#c0392b"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="#ffd700" stroke-width="2"/>
                <circle cx="50" cy="50" r="36" fill="#a93226"/>
                <path d="M45 28 Q47 30 46 33 Q44 36 45 38 L44 40 Q43 42 44 44 Q45 46 44 48 L43 50 Q42 52 43 55 Q44 57 43 59 L42 62 Q43 65 45 67 Q47 69 50 70 Q53 69 55 67 Q57 65 58 62 L57 59 Q56 57 57 55 Q58 52 57 50 L56 48 Q55 46 56 44 Q57 42 56 40 L55 38 Q54 36 55 33 Q53 30 52 28 Q50 26 48 27 Q46 27 45 28 Z" fill="#ffd700" stroke="#ffed4a" stroke-width="0.5"/>
                <path id="bd-footer-top" d="M50 10 A40 40 0 0 1 90 50" fill="none"/>
                <path id="bd-footer-bottom" d="M90 50 A40 40 0 0 1 10 50" fill="none"/>
                <text font-size="5.5" fill="#fff" font-family="sans-serif">
                    <textPath href="#bd-footer-top" startOffset="10%">&#x0997;&#x09A3;&#x09AA;&#x09CD;&#x09B0;&#x099C;&#x09BE;&#x09A4;&#x09A8;&#x09CD;&#x09A4;&#x09CD;&#x09B0;&#x09C0;</textPath>
                </text>
                <text font-size="5.5" fill="#fff" font-family="sans-serif">
                    <textPath href="#bd-footer-bottom" startOffset="15%">&#x09AC;&#x09BE;&#x0982;&#x09B2;&#x09BE;&#x09A6;&#x09C7;&#x09B6; &#x09B8;&#x09B0;&#x0995;&#x09BE;&#x09B0;</textPath>
                </text>
            </svg>
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
