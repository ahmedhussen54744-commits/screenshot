<?php
/**
 * Template part for displaying posts in grid/archive views
 */
?>
<article class="tmv-post-card">
    <div class="tmv-post-thumbnail">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium_large'); ?>
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
    </div>
    <div class="tmv-post-content">
        <div class="tmv-post-meta">
            <span class="tmv-post-date"><?php echo get_the_date(); ?></span>
            <span class="tmv-post-category"><?php
                $categories = get_the_category();
                if (!empty($categories)) {
                    echo esc_html($categories[0]->name);
                }
            ?></span>
        </div>
        <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
        <a href="<?php the_permalink(); ?>" class="tmv-read-more">Read More</a>
    </div>
</article>
