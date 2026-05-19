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
                <svg viewBox="0 0 120 120" width="44" height="44">
                    <circle cx="60" cy="60" r="58" fill="#1a5c3a" stroke="#ffd700" stroke-width="2"/>
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#fff" stroke-width="1"/>
                    <line x1="60" y1="15" x2="60" y2="105" stroke="#fff" stroke-width="0.5" opacity="0.5"/>
                    <line x1="15" y1="60" x2="105" y2="60" stroke="#fff" stroke-width="0.5" opacity="0.5"/>
                    <path d="M35 35 L38 28 L41 35 M36 32 Q38 25 40 32" fill="none" stroke="#ffd700" stroke-width="1.5"/>
                    <path d="M33 40 Q38 38 43 40" fill="none" stroke="#ffd700" stroke-width="1"/>
                    <path d="M75 30 L80 27 L85 30 L80 33 Z" fill="#ffd700"/>
                    <line x1="80" y1="33" x2="80" y2="40" stroke="#ffd700" stroke-width="1"/>
                    <circle cx="38" cy="78" r="5" fill="none" stroke="#ffd700" stroke-width="1"/>
                    <circle cx="38" cy="78" r="2" fill="#ffd700"/>
                    <ellipse cx="38" cy="78" rx="8" ry="3" fill="none" stroke="#ffd700" stroke-width="0.8" transform="rotate(45 38 78)"/>
                    <path d="M75 75 L78 68 L81 75 L84 68 L87 75 L87 85 L75 85 Z" fill="none" stroke="#ffd700" stroke-width="1.2"/>
                    <circle cx="60" cy="60" r="12" fill="#fff" opacity="0.15"/>
                    <text x="60" y="63" text-anchor="middle" font-size="8" fill="#fff" font-weight="bold">DPDT</text>
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
