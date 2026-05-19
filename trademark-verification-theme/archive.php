<?php
/**
 * Archive Template - Category, Tag, and Blog listings
 * Enhanced with post count, category description, and improved grid
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
                printf('Tag: %s', single_tag_title('', false));
            } elseif (is_author()) {
                the_author();
            } elseif (is_date()) {
                if (is_year()) {
                    echo get_the_date('Y');
                } elseif (is_month()) {
                    echo get_the_date('F Y');
                } elseif (is_day()) {
                    echo get_the_date('F j, Y');
                }
            } else {
                echo 'Blog';
            }
            ?>
        </h1>
        <?php
        global $wp_query;
        $total_posts = $wp_query->found_posts;
        ?>
        <p class="tmv-archive-count"><?php echo esc_html($total_posts); ?> <?php echo ($total_posts === 1) ? 'article' : 'articles'; ?> found</p>
        <?php
        if (is_category()) {
            $cat_description = category_description();
            if ($cat_description) : ?>
                <div class="tmv-archive-description"><?php echo $cat_description; ?></div>
            <?php endif;
        }
        ?>
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
