<?php get_header(); ?>

<div class="tmv-page-content">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="tmv-container">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
