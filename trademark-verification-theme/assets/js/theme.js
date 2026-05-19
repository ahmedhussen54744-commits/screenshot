/**
 * TMV Theme JavaScript
 * Mobile navigation, smooth interactions, and 3D page transitions
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // ===== Enhanced Mobile Navigation =====
        var $nav = $('.tmv-nav');
        var $navToggle = $('.tmv-nav-toggle');
        var $navOverlay = $('.tmv-nav-overlay');
        var $navClose = $('.tmv-nav-close-btn');
        var $body = $('body');

        // Open mobile nav (slide-in from right)
        $navToggle.on('click', function(e) {
            e.preventDefault();
            $nav.addClass('active');
            $navOverlay.addClass('active');
            $body.addClass('tmv-nav-open');
        });

        // Close mobile nav
        function closeNav() {
            $nav.removeClass('active');
            $navOverlay.removeClass('active');
            $body.removeClass('tmv-nav-open');
        }

        $navClose.on('click', function() {
            closeNav();
        });

        $navOverlay.on('click', function() {
            closeNav();
        });

        // Close nav on link click (mobile)
        $nav.find('> a, .tmv-nav-dropdown-menu a, .tmv-nav-btn').on('click', function() {
            if (window.innerWidth < 768) {
                closeNav();
            }
        });

        // Escape key to close
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $nav.hasClass('active')) {
                closeNav();
            }
        });

        // ===== Dropdown Toggle for "More" =====
        var $dropdownToggle = $('.tmv-nav-dropdown-toggle');
        var $dropdownMenu = $('.tmv-nav-dropdown-menu');

        // Desktop: hover behavior handled via CSS
        // Mobile: click to toggle
        $dropdownToggle.on('click', function(e) {
            e.preventDefault();
            if (window.innerWidth < 768) {
                $(this).parent('.tmv-nav-dropdown').toggleClass('open');
            }
        });

        // Desktop hover for dropdown
        if (window.innerWidth >= 768) {
            $('.tmv-nav-dropdown').on('mouseenter', function() {
                $(this).addClass('open');
            }).on('mouseleave', function() {
                $(this).removeClass('open');
            });
        }

        // Handle window resize
        $(window).on('resize', function() {
            if (window.innerWidth >= 768) {
                closeNav();
                // Rebind hover events for desktop
                $('.tmv-nav-dropdown').off('mouseenter mouseleave').on('mouseenter', function() {
                    $(this).addClass('open');
                }).on('mouseleave', function() {
                    $(this).removeClass('open');
                });
            }
        });

        // ===== Smooth Scroll for Anchor Links =====
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 600);
            }
        });

        // ===== Scroll Reveal for Post Cards =====
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.tmv-feature-card, .tmv-post-card').forEach(function(card, index) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) ' + (index * 0.1) + 's';
            observer.observe(card);
        });

        // ===== Header Scroll Effect =====
        var header = document.querySelector('.tmv-site-header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('tmv-header-scrolled');
                    header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.3)';
                } else {
                    header.classList.remove('tmv-header-scrolled');
                    header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
                }
            });
        }

        // ===== 3D Page Entrance Animation =====
        var pageContent = document.querySelector('.tmv-page-content');
        if (pageContent) {
            pageContent.classList.add('tmv-entering');
            void pageContent.offsetWidth;
            setTimeout(function() {
                pageContent.classList.remove('tmv-entering');
            }, 50);
        }

        // ===== Video Post Card Click Handler =====
        $(document).on('click', '.tmv-play-overlay', function(e) {
            e.preventDefault();
            var postLink = $(this).closest('.tmv-post-card').find('.tmv-post-title a').attr('href');
            if (postLink) {
                window.location.href = postLink;
            }
        });

        // ===== Video Player Lazy Loading =====
        var videoObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var video = entry.target;
                    var source = video.querySelector('source');
                    if (source && source.dataset.src) {
                        source.src = source.dataset.src;
                        video.load();
                    }
                    videoObserver.unobserve(video);
                }
            });
        }, { threshold: 0.25 });

        document.querySelectorAll('.tmv-video-player video').forEach(function(video) {
            videoObserver.observe(video);
        });

        // ===== Staggered Reveal for Grid Items =====
        var gridSelectors = '.tmv-form-grid > *, .tmv-details-grid > *, .tmv-features-grid > *, .tmv-news-grid > *, .tmv-related-grid > *';
        var gridItems = document.querySelectorAll(gridSelectors);

        if (gridItems.length > 0) {
            gridItems.forEach(function(item, index) {
                item.classList.add('tmv-stagger-item');
                item.style.transitionDelay = (index * 80) + 'ms';
            });

            var gridObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('tmv-revealed');
                        gridObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.05 });

            setTimeout(function() {
                gridItems.forEach(function(item) {
                    gridObserver.observe(item);
                });
            }, 200);
        }
    });

})(jQuery);
