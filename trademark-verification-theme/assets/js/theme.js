/**
 * TMV Theme JavaScript
 * Mobile navigation, dropdown menus, smooth interactions, and 3D page transitions
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // ===== Mobile Nav Toggle with Animation =====
        var $navToggle = $('.tmv-nav-toggle');
        var $nav = $('.tmv-nav');
        var isNavOpen = false;

        $navToggle.on('click', function(e) {
            e.stopPropagation();
            isNavOpen = !isNavOpen;
            $navToggle.attr('aria-expanded', isNavOpen);
            $navToggle.toggleClass('active', isNavOpen);

            if (isNavOpen) {
                $nav.addClass('tmv-nav--opening');
                $nav.slideDown(300, function() {
                    $nav.removeClass('tmv-nav--opening').addClass('active');
                });
            } else {
                $nav.slideUp(250, function() {
                    $nav.removeClass('active');
                });
            }
        });

        // Close mobile nav on link click
        $nav.on('click', 'a:not(.tmv-dropdown-toggle)', function() {
            if (window.innerWidth < 768 && isNavOpen) {
                isNavOpen = false;
                $navToggle.attr('aria-expanded', false).removeClass('active');
                $nav.slideUp(250, function() {
                    $nav.removeClass('active');
                });
            }
        });

        // Close mobile nav on outside click
        $(document).on('click', function(e) {
            if (isNavOpen && !$(e.target).closest('.tmv-nav, .tmv-nav-toggle').length) {
                isNavOpen = false;
                $navToggle.attr('aria-expanded', false).removeClass('active');
                $nav.slideUp(250, function() {
                    $nav.removeClass('active');
                });
            }
        });

        // ===== Dropdown Sub-menus (Desktop) =====
        var $menuItemsWithChildren = $nav.find('.menu-item-has-children');

        $menuItemsWithChildren.each(function() {
            var $item = $(this);
            var $submenu = $item.find('> .sub-menu');
            var hoverTimeout;

            // Desktop hover behavior
            $item.on('mouseenter', function() {
                if (window.innerWidth >= 768) {
                    clearTimeout(hoverTimeout);
                    $submenu.stop(true, true).slideDown(200);
                    $item.addClass('tmv-dropdown-open');
                }
            });

            $item.on('mouseleave', function() {
                if (window.innerWidth >= 768) {
                    hoverTimeout = setTimeout(function() {
                        $submenu.stop(true, true).slideUp(150);
                        $item.removeClass('tmv-dropdown-open');
                    }, 150);
                }
            });

            // Mobile: toggle on click
            var $toggle = $('<button class="tmv-dropdown-toggle" aria-label="Toggle submenu">&#9662;</button>');
            $item.find('> a').after($toggle);

            $toggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.innerWidth < 768) {
                    $submenu.slideToggle(200);
                    $item.toggleClass('tmv-dropdown-open');
                }
            });
        });

        // ===== Keyboard Navigation (Accessibility) =====
        $nav.on('keydown', 'a, button', function(e) {
            if (e.key === 'Escape') {
                var $parentDropdown = $(this).closest('.menu-item-has-children');
                if ($parentDropdown.length) {
                    $parentDropdown.find('> .sub-menu').slideUp(150);
                    $parentDropdown.removeClass('tmv-dropdown-open');
                    $parentDropdown.find('> a').focus();
                } else if (isNavOpen && window.innerWidth < 768) {
                    isNavOpen = false;
                    $navToggle.attr('aria-expanded', false).removeClass('active');
                    $nav.slideUp(250, function() {
                        $nav.removeClass('active');
                    });
                    $navToggle.focus();
                }
            }
        });

        // ===== Reset nav state on window resize =====
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 768) {
                    $nav.removeAttr('style').removeClass('active tmv-nav--opening');
                    isNavOpen = false;
                    $navToggle.attr('aria-expanded', false).removeClass('active');
                    $nav.find('.sub-menu').removeAttr('style');
                    $nav.find('.tmv-dropdown-open').removeClass('tmv-dropdown-open');
                }
            }, 150);
        });

        // ===== Scroll Reveal for Feature Cards =====
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

        // ===== Header Scroll Effect =====
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

        // ===== Reading Progress Bar (Single Post) =====
        var progressBar = document.getElementById('tmv-reading-progress-bar');
        var singleContent = document.getElementById('tmv-single-content');

        if (progressBar && singleContent) {
            window.addEventListener('scroll', function() {
                var contentRect = singleContent.getBoundingClientRect();
                var contentTop = singleContent.offsetTop;
                var contentHeight = singleContent.offsetHeight;
                var windowHeight = window.innerHeight;
                var scrollY = window.scrollY || window.pageYOffset;

                var start = contentTop - windowHeight;
                var end = contentTop + contentHeight;
                var progress = 0;

                if (scrollY <= start) {
                    progress = 0;
                } else if (scrollY >= end) {
                    progress = 100;
                } else {
                    progress = ((scrollY - start) / (end - start)) * 100;
                }

                progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
            });
        }

        // ===== Copy Link to Clipboard (Share Button) =====
        $(document).on('click', '.tmv-share-btn--copy', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var url = $btn.data('url') || window.location.href;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    $btn.addClass('copied');
                    setTimeout(function() {
                        $btn.removeClass('copied');
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                var tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                $btn.addClass('copied');
                setTimeout(function() {
                    $btn.removeClass('copied');
                }, 2000);
            }
        });

        // ===== Lazy Loading for Post Images =====
        var imgObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    img.classList.add('tmv-img-loaded');
                    imgObserver.unobserve(img);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.tmv-post-card img[data-src]').forEach(function(img) {
            imgObserver.observe(img);
        });

        // ===== Staggered Reveal for Grid Items =====
        var gridSelectors = '.tmv-form-grid > *, .tmv-details-grid > *, .tmv-features-grid > *, .tmv-news-grid > *';
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

        // ===== FAQ Accordion =====
        $(document).on('click', '.tmv-faq-question', function() {
            var $item = $(this).closest('.tmv-faq-item');
            var $answer = $item.find('.tmv-faq-answer');
            var isOpen = $item.hasClass('tmv-faq-item--open');

            // Close other items
            $('.tmv-faq-item--open').not($item).removeClass('tmv-faq-item--open')
                .find('.tmv-faq-answer').slideUp(250);

            if (isOpen) {
                $item.removeClass('tmv-faq-item--open');
                $answer.slideUp(250);
            } else {
                $item.addClass('tmv-faq-item--open');
                $answer.slideDown(300);
            }
        });
    });

})(jQuery);
