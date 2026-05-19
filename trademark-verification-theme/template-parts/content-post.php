<?php
/**
 * Template part for displaying posts in grid/archive views
 * Enhanced with date badge, author info, reading time, and category badges
 */
?>
<article class="tmv-post-card">
    <div class="tmv-post-thumbnail">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium_large'); ?>
        <?php endif; ?>
        <span class="tmv-post-date-badge"><?php echo get_the_date('M d, Y'); ?></span>
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
    </div>
    <div class="tmv-post-content">
        <div class="tmv-post-meta">
            <?php
            $categories = get_the_category();
            if (!empty($categories)) : ?>
                <span class="tmv-post-category-badge"><?php echo esc_html($categories[0]->name); ?></span>
            <?php endif; ?>
            <span class="tmv-post-reading-time"><?php echo tmv_reading_time(get_the_ID()); ?> min read</span>
        </div>
        <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
        <div class="tmv-post-footer">
            <div class="tmv-post-author-row">
                <span class="tmv-post-author-name">By <?php the_author(); ?></span>
            </div>
            <a href="<?php the_permalink(); ?>" class="tmv-read-more">Read More &rarr;</a>
        </div>
    </div>
</article>
