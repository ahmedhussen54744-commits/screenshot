<?php
/**
 * Single Post Template - Rich reading experience
 */
get_header();
?>

<!-- Reading Progress Bar -->
<div class="tmv-reading-progress" id="tmv-reading-progress">
    <div class="tmv-reading-progress__bar" id="tmv-reading-progress-bar"></div>
</div>

<div class="tmv-page-content">
    <div class="tmv-single-post">
        <?php while (have_posts()) : the_post(); ?>

        <!-- Breadcrumb Navigation -->
        <nav class="tmv-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span class="tmv-breadcrumb__sep">&rsaquo;</span>
            <?php
            $blog_page_id = get_option('page_for_posts');
            $blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/');
            ?>
            <a href="<?php echo esc_url($blog_url); ?>">Blog</a>
            <span class="tmv-breadcrumb__sep">&rsaquo;</span>
            <span class="tmv-breadcrumb__current"><?php the_title(); ?></span>
        </nav>

        <!-- Hero Featured Image -->
        <?php if (has_post_thumbnail()) : ?>
        <div class="tmv-single-hero">
            <div class="tmv-single-hero__image">
                <?php the_post_thumbnail('full'); ?>
            </div>
            <div class="tmv-single-hero__overlay">
                <div class="tmv-single-hero__content">
                    <?php
                    $categories = get_the_category();
                    if (!empty($categories)) : ?>
                        <span class="tmv-single-hero__category"><?php echo esc_html($categories[0]->name); ?></span>
                    <?php endif; ?>
                    <h1 class="tmv-single-hero__title"><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
        <?php else : ?>
        <div class="tmv-single-header">
            <h1 class="tmv-single-title"><?php the_title(); ?></h1>
        </div>
        <?php endif; ?>

        <!-- Post Meta Bar -->
        <div class="tmv-single-meta-bar">
            <div class="tmv-date-badge tmv-date-badge--large">
                <span class="tmv-date-badge__day"><?php echo get_the_date('d'); ?></span>
                <span class="tmv-date-badge__month"><?php echo get_the_date('M'); ?></span>
                <span class="tmv-date-badge__year"><?php echo get_the_date('Y'); ?></span>
            </div>
            <div class="tmv-single-meta-info">
                <span class="tmv-single-author-inline">
                    <?php echo get_avatar(get_the_author_meta('ID'), 32); ?>
                    <strong><?php the_author(); ?></strong>
                </span>
                <span class="tmv-reading-time">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php
                    $word_count = str_word_count(strip_tags(get_the_content()));
                    $reading_time = max(1, ceil($word_count / 200));
                    echo esc_html($reading_time) . ' min read';
                    ?>
                </span>
                <?php if (!empty($categories)) : ?>
                <span class="tmv-single-meta-category">
                    <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $video_id = get_post_meta(get_the_ID(), 'tmv_post_video', true);
        if ($video_id) :
            get_template_part('template-parts/content', 'video');
        endif;
        ?>

        <!-- Post Content -->
        <div class="tmv-single-content" id="tmv-single-content">
            <?php the_content(); ?>
        </div>

        <!-- Tags -->
        <?php
        $tags = get_the_tags();
        if ($tags) : ?>
        <div class="tmv-single-tags">
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"><?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Social Sharing Buttons -->
        <div class="tmv-share-buttons">
            <span class="tmv-share-buttons__label">Share this article:</span>
            <div class="tmv-share-buttons__list">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-btn--facebook" title="Share on Facebook">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-btn--twitter" title="Share on X (Twitter)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-btn--linkedin" title="Share on LinkedIn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-btn--whatsapp" title="Share on WhatsApp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </a>
                <button class="tmv-share-btn tmv-share-btn--copy" title="Copy link" data-url="<?php echo esc_url(get_permalink()); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
            </div>
        </div>

        <!-- Author Bio Box -->
        <div class="tmv-author-box">
            <div class="tmv-author-box__avatar">
                <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
            </div>
            <div class="tmv-author-box__info">
                <span class="tmv-author-box__label">Written by</span>
                <h4 class="tmv-author-box__name"><?php the_author(); ?></h4>
                <?php $author_desc = get_the_author_meta('description'); ?>
                <?php if ($author_desc) : ?>
                    <p class="tmv-author-box__bio"><?php echo esc_html($author_desc); ?></p>
                <?php else : ?>
                    <p class="tmv-author-box__bio">Author at <?php bloginfo('name'); ?>.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Previous/Next Navigation -->
        <div class="tmv-post-nav">
            <div class="tmv-post-nav-prev">
                <?php
                $prev_post = get_previous_post();
                if ($prev_post) : ?>
                    <span class="tmv-post-nav__label">&larr; Previous</span>
                    <a href="<?php echo esc_url(get_permalink($prev_post)); ?>"><?php echo esc_html($prev_post->post_title); ?></a>
                <?php endif; ?>
            </div>
            <div class="tmv-post-nav-next">
                <?php
                $next_post = get_next_post();
                if ($next_post) : ?>
                    <span class="tmv-post-nav__label">Next &rarr;</span>
                    <a href="<?php echo esc_url(get_permalink($next_post)); ?>"><?php echo esc_html($next_post->post_title); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Posts -->
        <?php
        $current_post_id = get_the_ID();
        $related_cat_ids = wp_list_pluck($categories, 'term_id');
        if (!empty($related_cat_ids)) :
            $related_query = new WP_Query(array(
                'post_type'      => 'post',
                'posts_per_page' => 3,
                'post_status'    => 'publish',
                'post__not_in'   => array($current_post_id),
                'category__in'   => $related_cat_ids,
                'orderby'        => 'rand',
            ));
            if ($related_query->have_posts()) : ?>
        <div class="tmv-related-posts">
            <h3 class="tmv-related-posts__title">Related Articles</h3>
            <div class="tmv-related-posts__grid">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="tmv-related-posts__item">
                    <div class="tmv-related-posts__thumb">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="tmv-related-posts__info">
                        <span class="tmv-related-posts__date"><?php echo get_the_date('M d, Y'); ?></span>
                        <h4 class="tmv-related-posts__item-title"><?php the_title(); ?></h4>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
            <?php endif;
            wp_reset_postdata();
        endif;
        ?>

        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
