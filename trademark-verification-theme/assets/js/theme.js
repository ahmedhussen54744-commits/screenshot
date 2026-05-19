/**
 * TMV Theme JavaScript
 * Mobile navigation, smooth interactions, and 3D page transitions
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

        // ===== 3D Page Entrance Animation =====
        var pageContent = document.querySelector('.tmv-page-content');
        if (pageContent) {
            // Set initial state
            pageContent.classList.add('tmv-entering');
            // Force reflow
            void pageContent.offsetWidth;
            // Animate to final state
            setTimeout(function() {
                pageContent.classList.remove('tmv-entering');
            }, 50);
        }

        // ===== Staggered Reveal for Grid Items =====
        var gridSelectors = '.tmv-form-grid > *, .tmv-details-grid > *, .tmv-features-grid > *';
        var gridItems = document.querySelectorAll(gridSelectors);

        if (gridItems.length > 0) {
            gridItems.forEach(function(item, index) {
                item.classList.add('tmv-stagger-item');
                item.style.transitionDelay = (index * 80) + 'ms';
            });

            // Use IntersectionObserver for grid item reveal
            var gridObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('tmv-revealed');
                        gridObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.05 });

            // Start observing after a brief delay for page load
            setTimeout(function() {
                gridItems.forEach(function(item) {
                    gridObserver.observe(item);
                });
            }, 200);
        }
    });

})(jQuery);
