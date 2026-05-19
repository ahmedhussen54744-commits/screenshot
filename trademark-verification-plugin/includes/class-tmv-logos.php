<?php
if (!defined('ABSPATH')) exit;

class TMV_Logos {

    /**
     * Get the DPDT logo HTML (top-left on certificate/header).
     * Returns <img> tag if custom logo exists, else returns inline SVG fallback.
     */
    public static function get_dpdt_logo_html($width = 44, $height = 44) {
        $attachment_id = intval(get_option('tmv_dpdt_logo', 0));

        if ($attachment_id) {
            $url = wp_get_attachment_url($attachment_id);
            if ($url) {
                return '<img src="' . esc_url($url) . '" alt="DPDT Logo" width="' . intval($width) . '" height="' . intval($height) . '" class="tmv-dpdt-logo-img" />';
            }
        }

        // Fallback: inline SVG
        return '<svg viewBox="0 0 120 120" width="' . intval($width) . '" height="' . intval($height) . '">
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
                </svg>';
    }

    /**
     * Get the Bangladesh Government seal HTML (top-right on certificate/footer).
     * Returns <img> tag if custom logo exists, else returns inline SVG fallback.
     */
    public static function get_bd_seal_html($width = 60, $height = 60) {
        $attachment_id = intval(get_option('tmv_bd_govt_seal', 0));

        if ($attachment_id) {
            $url = wp_get_attachment_url($attachment_id);
            if ($url) {
                return '<img src="' . esc_url($url) . '" alt="Bangladesh Government Seal" width="' . intval($width) . '" height="' . intval($height) . '" class="tmv-bd-seal-img" />';
            }
        }

        // Fallback: inline SVG
        return '<svg viewBox="0 0 100 100" width="' . intval($width) . '" height="' . intval($height) . '">
                <circle cx="50" cy="50" r="48" fill="none" stroke="#27ae60" stroke-width="4"/>
                <circle cx="50" cy="50" r="44" fill="#c0392b"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="#ffd700" stroke-width="2"/>
                <circle cx="50" cy="50" r="36" fill="#a93226"/>
                <path d="M45 28 Q47 30 46 33 Q44 36 45 38 L44 40 Q43 42 44 44 Q45 46 44 48 L43 50 Q42 52 43 55 Q44 57 43 59 L42 62 Q43 65 45 67 Q47 69 50 70 Q53 69 55 67 Q57 65 58 62 L57 59 Q56 57 57 55 Q58 52 57 50 L56 48 Q55 46 56 44 Q57 42 56 40 L55 38 Q54 36 55 33 Q53 30 52 28 Q50 26 48 27 Q46 27 45 28 Z" fill="#ffd700" stroke="#ffed4a" stroke-width="0.5"/>
                <path id="bd-seal-top-' . intval($width) . '" d="M50 10 A40 40 0 0 1 90 50" fill="none"/>
                <path id="bd-seal-bottom-' . intval($width) . '" d="M90 50 A40 40 0 0 1 10 50" fill="none"/>
                <text font-size="5.5" fill="#fff" font-family="sans-serif">
                    <textPath href="#bd-seal-top-' . intval($width) . '" startOffset="10%">&#x0997;&#x09A3;&#x09AA;&#x09CD;&#x09B0;&#x099C;&#x09BE;&#x09A4;&#x09A8;&#x09CD;&#x09A4;&#x09CD;&#x09B0;&#x09C0;</textPath>
                </text>
                <text font-size="5.5" fill="#fff" font-family="sans-serif">
                    <textPath href="#bd-seal-bottom-' . intval($width) . '" startOffset="15%">&#x09AC;&#x09BE;&#x0982;&#x09B2;&#x09BE;&#x09A6;&#x09C7;&#x09B6; &#x09B8;&#x09B0;&#x0995;&#x09BE;&#x09B0;</textPath>
                </text>
            </svg>';
    }
}
