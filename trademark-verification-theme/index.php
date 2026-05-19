<?php get_header(); ?>

<div class="tmv-page-content">
    <?php if (is_front_page()): ?>
    
    <!-- Hero Section -->
    <section class="tmv-hero">
        <div class="tmv-hero-content">
            <h2><?php echo esc_html(get_theme_mod('tmv_hero_title', 'Trademark Verification System')); ?></h2>
            <p><?php echo esc_html(get_theme_mod('tmv_hero_desc', 'Official Digital Platform for verifying registered trademarks under the Department of Patents, Designs & Trademarks, Bangladesh.')); ?></p>
            <div class="tmv-hero-btns">
                <a href="<?php echo home_url('/verify/'); ?>" class="tmv-hero-btn tmv-hero-btn-primary">&#128269; Verify Trademark</a>
                <a href="<?php echo home_url('/apply/'); ?>" class="tmv-hero-btn tmv-hero-btn-secondary">&#128221; Apply Now</a>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="tmv-features">
        <h2 class="tmv-features-title">Why Choose Our System</h2>
        <div class="tmv-features-grid">
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#128274;</div>
                <h3>Highly Secure</h3>
                <p>Advanced encryption, anti-tampering measures, and real-time verification ensure complete data integrity and protection.</p>
            </div>
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#9889;</div>
                <h3>Instant Verification</h3>
                <p>Verify any registered trademark in seconds using QR code or trademark number with our lightning-fast system.</p>
            </div>
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#128241;</div>
                <h3>Mobile Friendly</h3>
                <p>Fully responsive design works perfectly on all devices - mobile, tablet, and desktop with 3D visual experience.</p>
            </div>
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#128196;</div>
                <h3>PDF & JPG Certificates</h3>
                <p>Download verified certificates in both PDF and JPG formats with official digital seal and QR verification code.</p>
            </div>
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#127760;</div>
                <h3>QR Code System</h3>
                <p>Each certificate comes with a unique QR code that links directly to the verification portal for instant authentication.</p>
            </div>
            <div class="tmv-feature-card">
                <div class="tmv-feature-icon">&#128640;</div>
                <h3>Since 2009</h3>
                <p>Trusted by thousands of businesses since 2009. Our platform has processed over 1,000,000 trademark applications.</p>
            </div>
        </div>
    </section>
    
    <!-- Latest News Section -->
    <section class="tmv-news-section">
        <div class="tmv-section-header">
            <h2 class="tmv-section-title">Latest News &amp; Updates</h2>
            <p class="tmv-section-subtitle">Stay informed with the latest trademark news and system updates</p>
        </div>
        <div class="tmv-news-grid tmv-news-grid--featured">
            <?php
            $news_query = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 6,
                'post_status' => 'publish',
            ));
            $post_index = 0;
            while ($news_query->have_posts()) : $news_query->the_post();
                if ($post_index === 0) : ?>
                    <!-- Featured First Post -->
                    <article class="tmv-post-card tmv-post-card--featured">
                        <div class="tmv-post-thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
                                <div class="tmv-post-thumbnail-overlay"></div>
                            <?php endif; ?>
                            <div class="tmv-date-badge">
                                <span class="tmv-date-badge__day"><?php echo get_the_date('d'); ?></span>
                                <span class="tmv-date-badge__month"><?php echo get_the_date('M'); ?></span>
                            </div>
                            <?php
                            $categories = get_the_category();
                            if (!empty($categories)) : ?>
                                <span class="tmv-category-ribbon"><?php echo esc_html($categories[0]->name); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="tmv-post-content">
                            <div class="tmv-post-meta">
                                <span class="tmv-post-author">
                                    <?php echo get_avatar(get_the_author_meta('ID'), 24); ?>
                                    <?php the_author(); ?>
                                </span>
                                <span class="tmv-post-reading-time">
                                    <?php
                                    $word_count = str_word_count(strip_tags(get_the_content()));
                                    $reading_time = max(1, ceil($word_count / 200));
                                    echo esc_html($reading_time) . ' min read';
                                    ?>
                                </span>
                            </div>
                            <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 40, '...'); ?></p>
                            <a href="<?php the_permalink(); ?>" class="tmv-read-more">Read Full Article &rarr;</a>
                        </div>
                    </article>
                <?php else :
                    get_template_part('template-parts/content', 'post');
                endif;
                $post_index++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
        <div class="tmv-news-more">
            <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="tmv-hero-btn tmv-hero-btn-secondary">View All News</a>
        </div>
    </section>
    
    <?php else: ?>
    
    <div class="tmv-container">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <article>
                <h2><?php the_title(); ?></h2>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; endif; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php get_footer(); ?>
