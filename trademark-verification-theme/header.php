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
                <?php echo TMV_Logos::get_dpdt_logo_html(44, 44); ?>
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
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo home_url('/dashboard/'); ?>" <?php if (is_page('dashboard')) echo 'class="active"'; ?>>Dashboard</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            <?php else : ?>
                <a href="<?php echo home_url('/login/'); ?>" <?php if (is_page('login')) echo 'class="active"'; ?>>Login</a>
                <a href="<?php echo home_url('/register/'); ?>" <?php if (is_page('register')) echo 'class="active"'; ?>>Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="tmv-main">
