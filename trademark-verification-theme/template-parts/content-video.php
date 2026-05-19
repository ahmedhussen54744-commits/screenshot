<?php
/**
 * Template part for displaying video player in single post view
 */
$video_id = get_post_meta(get_the_ID(), 'tmv_post_video', true);
if ($video_id) :
    $video_url = wp_get_attachment_url($video_id);
    $poster_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
    if ($video_url) :
?>
<div class="tmv-video-player">
    <video controls preload="metadata"<?php if ($poster_url) echo ' poster="' . esc_url($poster_url) . '"'; ?>>
        <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>
<?php
    endif;
endif;
?>
