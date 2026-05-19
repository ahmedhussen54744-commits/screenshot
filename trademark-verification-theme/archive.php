<?php
/**
 * Archive Template - Category, Tag, and Blog listings
 */
get_header();
?>

<div class="tmv-page-content">
    <div class="tmv-archive-header">
        <h1 class="tmv-archive-title">
            <?php
            if (is_category()) {
                single_cat_title();
            } elseif (is_tag()) {
                single_tag_title();
            } elseif (is_author()) {
                the_author();
            } else {
                echo 'Blog';
            }
            ?>
        </h1>
    </div>

    <div class="tmv-archive-grid">
        <?php if (have_posts()) : ?>
        <div class="tmv-news-grid">
            <?php while (have_posts()) : the_post();
                get_template_part('template-parts/content', 'post');
            endwhile; ?>
        </div>

        <div class="tmv-pagination">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
            ));
            ?>
        </div>
        <?php else : ?>
        <p class="tmv-no-posts">No posts found.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
