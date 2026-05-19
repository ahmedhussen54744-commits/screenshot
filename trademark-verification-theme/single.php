<?php
/**
 * Single Post Template
 */
get_header();
?>

<div class="tmv-page-content">
    <div class="tmv-single-post">
        <?php while (have_posts()) : the_post(); ?>
        
        <div class="tmv-single-header">
            <h1 class="tmv-single-title"><?php the_title(); ?></h1>
            <div class="tmv-single-meta">
                <span class="tmv-single-date"><?php echo get_the_date(); ?></span>
                <span class="tmv-single-author">by <?php the_author(); ?></span>
                <span class="tmv-single-category"><?php
                    $categories = get_the_category();
                    if (!empty($categories)) {
                        echo esc_html($categories[0]->name);
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

        <?php if (has_post_thumbnail() && !$video_id) : ?>
        <div class="tmv-single-featured">
            <?php the_post_thumbnail('large'); ?>
        </div>
        <?php endif; ?>

        <div class="tmv-single-content">
            <?php the_content(); ?>
        </div>

        <?php
        $tags = get_the_tags();
        if ($tags) : ?>
        <div class="tmv-single-tags">
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo get_tag_link($tag->term_id); ?>"><?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="tmv-post-nav">
            <div class="tmv-post-nav-prev">
                <?php previous_post_link('%link', '&larr; %title'); ?>
            </div>
            <div class="tmv-post-nav-next">
                <?php next_post_link('%link', '%title &rarr;'); ?>
            </div>
        </div>

        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
