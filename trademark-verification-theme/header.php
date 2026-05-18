<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#1a5c3a">
    <meta name="msapplication-TileColor" content="#1a5c3a">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="tmv-site-header">
    <div class="tmv-header-inner">
        <a href="<?php echo home_url('/'); ?>" class="tmv-logo">
            <div class="tmv-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                    <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="tmv-logo-text">
                <h1><?php bloginfo('name'); ?></h1>
                <p>Department of Patents, Designs & Trademarks</p>
            </div>
        </a>
        
        <button class="tmv-nav-toggle" aria-label="Menu" onclick="document.querySelector('.tmv-nav').classList.toggle('active')">&#9776;</button>
        
        <nav class="tmv-nav">
            <a href="<?php echo home_url('/'); ?>" <?php if (is_front_page()) echo 'class="active"'; ?>>Home</a>
            <a href="<?php echo home_url('/verify/'); ?>" <?php if (is_page('verify')) echo 'class="active"'; ?>>Verify</a>
            <a href="<?php echo home_url('/apply/'); ?>" <?php if (is_page('apply')) echo 'class="active"'; ?>>Apply</a>
        </nav>
    </div>
</header>

<main class="tmv-main">
