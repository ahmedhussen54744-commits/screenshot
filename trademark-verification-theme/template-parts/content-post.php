<?php
/**
 * Template part for displaying posts in grid/archive views
 * Enhanced with date badge, category ribbon, author avatar, and reading time
 */
$word_count = str_word_count(strip_tags(get_the_content()));
$reading_time = max(1, ceil($word_count / 200));
?>
<article class="tmv-post-card">
    <div class="tmv-post-thumbnail">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium_large'); ?>
            <div class="tmv-post-thumbnail-overlay"></div>
        <?php endif; ?>
        <?php
        $video_id = get_post_meta(get_the_ID(), 'tmv_post_video', true);
        if ($video_id) : ?>
            <div class="tmv-play-overlay">
                <div class="tmv-play-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M8 5v14l11-7z" fill="#1a5c3a"/>
                    </svg>
                </div>
            </div>
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
            <span class="tmv-post-reading-time"><?php echo esc_html($reading_time); ?> min read</span>
        </div>
        <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
        <a href="<?php the_permalink(); ?>" class="tmv-read-more">Read More &rarr;</a>
    </div>
</article>
