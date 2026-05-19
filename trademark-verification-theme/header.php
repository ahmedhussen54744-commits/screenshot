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
        
        <button class="tmv-nav-toggle" aria-label="Toggle Navigation">
            <span class="tmv-nav-toggle-bar"></span>
            <span class="tmv-nav-toggle-bar"></span>
            <span class="tmv-nav-toggle-bar"></span>
        </button>
        
        <nav class="tmv-nav">
            <div class="tmv-nav-close-btn">&times;</div>
            <a href="<?php echo home_url('/'); ?>" <?php if (is_front_page()) echo 'class="active"'; ?>>Home</a>
            <a href="<?php echo home_url('/verify/'); ?>" <?php if (is_page('verify')) echo 'class="active"'; ?>>Verify Certificate</a>
            <a href="<?php echo home_url('/apply/'); ?>" <?php if (is_page('apply')) echo 'class="active"'; ?>>Apply for Trademark</a>
            <a href="<?php echo home_url('/blog/'); ?>" <?php if (is_home() || is_single() || is_archive()) echo 'class="active"'; ?>>News/Blog</a>
            
            <!-- More dropdown for secondary items -->
            <div class="tmv-nav-dropdown">
                <a href="#" class="tmv-nav-dropdown-toggle">More <span class="tmv-dropdown-arrow">&#9662;</span></a>
                <div class="tmv-nav-dropdown-menu">
                    <a href="<?php echo home_url('/services/'); ?>" <?php if (is_page('services')) echo 'class="active"'; ?>>Services</a>
                    <a href="<?php echo home_url('/about/'); ?>" <?php if (is_page('about')) echo 'class="active"'; ?>>About Us</a>
                    <a href="<?php echo home_url('/contact/'); ?>" <?php if (is_page('contact')) echo 'class="active"'; ?>>Contact</a>
                    <a href="<?php echo home_url('/faq/'); ?>" <?php if (is_page('faq')) echo 'class="active"'; ?>>FAQ</a>
                </div>
            </div>
            
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo home_url('/dashboard/'); ?>" class="tmv-nav-btn tmv-nav-btn-dashboard <?php if (is_page('dashboard')) echo 'active'; ?>">Dashboard</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="tmv-nav-btn tmv-nav-btn-logout">Logout</a>
            <?php else : ?>
                <a href="<?php echo home_url('/login/'); ?>" class="tmv-nav-btn tmv-nav-btn-login <?php if (is_page('login')) echo 'active'; ?>">Login</a>
                <a href="<?php echo home_url('/register/'); ?>" class="tmv-nav-btn tmv-nav-btn-register <?php if (is_page('register')) echo 'active'; ?>">Register</a>
            <?php endif; ?>
        </nav>
        <div class="tmv-nav-overlay"></div>
    </div>
</header>

<main class="tmv-main">
