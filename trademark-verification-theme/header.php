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
                <?php if (class_exists('TMV_Logos')) { echo TMV_Logos::get_dpdt_logo_html(44, 44); } else { echo 'DPDT'; } ?>
            </div>
            <div class="tmv-logo-text">
                <h1><?php bloginfo('name'); ?></h1>
                <p>Department of Patents, Designs & Trademarks</p>
            </div>
        </a>
        
        <button class="tmv-nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
            <span class="tmv-hamburger"></span>
        </button>
        
        <nav class="tmv-nav" role="navigation" aria-label="Primary Navigation">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'depth'          => 2,
                'fallback_cb'    => 'tmv_primary_nav_fallback',
            ));
            ?>
            <div class="tmv-nav-user">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo home_url('/dashboard/'); ?>" class="tmv-nav-btn tmv-nav-btn--dashboard"><?php echo esc_html(wp_get_current_user()->display_name); ?></a>
                    <a href="<?php echo wp_logout_url(home_url('/')); ?>" class="tmv-nav-btn tmv-nav-btn--logout">Logout</a>
                <?php else : ?>
                    <a href="<?php echo home_url('/login/'); ?>" class="tmv-nav-btn tmv-nav-btn--login">Login</a>
                    <a href="<?php echo home_url('/register/'); ?>" class="tmv-nav-btn tmv-nav-btn--register">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main class="tmv-main">
