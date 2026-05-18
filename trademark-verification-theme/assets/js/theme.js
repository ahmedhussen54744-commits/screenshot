/**
 * TMV Theme JavaScript
 * Mobile navigation and smooth interactions
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Mobile nav toggle
        $('.tmv-nav-toggle').on('click', function() {
            $('.tmv-nav').toggleClass('active');
        });

        // Close nav on link click (mobile)
        $('.tmv-nav a').on('click', function() {
            if (window.innerWidth < 768) {
                $('.tmv-nav').removeClass('active');
            }
        });

        // Scroll reveal for feature cards
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.tmv-feature-card').forEach(function(card, index) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) ' + (index * 0.1) + 's';
            observer.observe(card);
        });

        // Header scroll effect
        var header = document.querySelector('.tmv-site-header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.3)';
                } else {
                    header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
                }
            });
        }
    });

})(jQuery);
