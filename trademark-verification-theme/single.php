<?php
/**
 * Single Post Template - Enhanced
 */
get_header();
?>

<div class="tmv-page-content">
    <div class="tmv-single-post">
        <?php while (have_posts()) : the_post(); ?>
        
        <?php if (has_post_thumbnail()) : ?>
        <div class="tmv-single-featured-hero">
            <?php the_post_thumbnail('large'); ?>
            <div class="tmv-single-featured-overlay"></div>
        </div>
        <?php endif; ?>

        <div class="tmv-single-header">
            <div class="tmv-single-top-meta">
                <span class="tmv-single-date-badge"><?php echo get_the_date('M d, Y'); ?></span>
                <span class="tmv-single-reading-time"><?php echo tmv_reading_time(get_the_ID()); ?> min read</span>
            </div>
            <h1 class="tmv-single-title"><?php the_title(); ?></h1>
            <div class="tmv-single-meta">
                <span class="tmv-single-author">by <?php the_author(); ?></span>
                <span class="tmv-single-category"><?php
                    $categories = get_the_category();
                    if (!empty($categories)) {
                        foreach ($categories as $cat) {
                            echo '<span class="tmv-category-badge">' . esc_html($cat->name) . '</span>';
                        }
                    }
                ?></span>
            </div>
        </div>

        <?php
        $video_id = get_post_meta(get_the_ID(), 'tmv_post_video', true);
        if ($video_id) :
            get_template_part('template-parts/content', 'video');
        endif;
        ?>

        <div class="tmv-single-content">
            <?php the_content(); ?>
        </div>

        <?php
        $tags = get_the_tags();
        if ($tags) : ?>
        <div class="tmv-single-tags">
            <span class="tmv-tags-label">Tags:</span>
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo get_tag_link($tag->term_id); ?>"><?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Social Share Section -->
        <div class="tmv-social-share">
            <span class="tmv-share-label">Share this post:</span>
            <div class="tmv-share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-facebook">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-twitter">Twitter</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-linkedin">LinkedIn</a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" target="_blank" rel="noopener noreferrer" class="tmv-share-btn tmv-share-whatsapp">WhatsApp</a>
            </div>
        </div>

        <!-- Author Bio Section -->
        <div class="tmv-author-bio">
            <div class="tmv-author-avatar">
                <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
            </div>
            <div class="tmv-author-info">
                <h4 class="tmv-author-name"><?php the_author(); ?></h4>
                <p class="tmv-author-role">Author</p>
                <?php if (get_the_author_meta('description')) : ?>
                    <p class="tmv-author-desc"><?php echo esc_html(get_the_author_meta('description')); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="tmv-post-nav">
            <div class="tmv-post-nav-prev">
                <?php previous_post_link('%link', '&larr; %title'); ?>
            </div>
            <div class="tmv-post-nav-next">
                <?php next_post_link('%link', '%title &rarr;'); ?>
            </div>
        </div>

        <?php endwhile; ?>

        <!-- Related Posts Section -->
        <?php
        $categories = get_the_category();
        if (!empty($categories)) :
            $related_args = array(
                'category__in' => array($categories[0]->term_id),
                'post__not_in' => array(get_the_ID()),
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
            );
            $related_query = new WP_Query($related_args);
            if ($related_query->have_posts()) :
        ?>
        <div class="tmv-related-posts">
            <h3 class="tmv-related-title">Related Posts</h3>
            <div class="tmv-related-grid">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <article class="tmv-post-card tmv-related-card">
                    <div class="tmv-post-thumbnail">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium_large'); ?>
                        <?php endif; ?>
                        <span class="tmv-post-date-badge"><?php echo get_the_date('M d'); ?></span>
                    </div>
                    <div class="tmv-post-content">
                        <h3 class="tmv-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="tmv-post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?></p>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
            endif;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>
