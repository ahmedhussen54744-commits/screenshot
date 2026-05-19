<?php
if (!defined('ABSPATH')) exit;

class TMV_Certificate {

    /**
     * Path to the TTF font file.
     */
    const FONT_PATH = 'assets/fonts/DejaVuSans.ttf';

    /**
     * Certificate dimensions (A4 at 300dpi).
     */
    const WIDTH = 2480;
    const HEIGHT = 3508;

    /**
     * Generate a certificate PNG for the given post.
     *
     * @param int $post_id The trademark application post ID.
     * @return int|false Attachment ID on success, false on failure.
     */
    public static function generate($post_id) {
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }

        $post_id = intval($post_id);
        if (!$post_id) {
            return false;
        }

        // Gather meta data
        $tm_number    = get_post_meta($post_id, 'tmv_tm_number', true);
        $cert_title   = get_post_meta($post_id, 'tmv_cert_title', true);
        $class_num    = get_post_meta($post_id, 'tmv_class', true);
        $owner        = get_post_meta($post_id, 'tmv_owner', true);
        $address      = get_post_meta($post_id, 'tmv_address', true);
        $service_desc = get_post_meta($post_id, 'tmv_service_desc', true);
        $reg_date     = get_post_meta($post_id, 'tmv_reg_date', true);
        $signatory    = get_post_meta($post_id, 'tmv_signatory', true);
        $designation  = get_post_meta($post_id, 'tmv_designation', true);
        $sealed_text  = get_post_meta($post_id, 'tmv_sealed_text', true);
        $logo_text    = get_post_meta($post_id, 'tmv_logo_text', true);
        $sealing_date = get_post_meta($post_id, 'tmv_sealing_date', true);
        $brand_logo_id = get_post_meta($post_id, 'tmv_brand_logo', true);

        if (empty($cert_title)) {
            $cert_title = 'Certificate of Registration of Trademark [Rule 30(1)]';
        }
        if (empty($reg_date)) {
            $reg_date = current_time('Y-m-d');
        }
        if (empty($sealing_date)) {
            $sealing_date = current_time('Y-m-d');
        }

        // Create canvas
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if (!$img) {
            return false;
        }

        // Colors
        $white     = imagecolorallocate($img, 255, 255, 255);
        $black     = imagecolorallocate($img, 30, 30, 30);
        $dark_gray = imagecolorallocate($img, 60, 60, 60);
        $green     = imagecolorallocate($img, 0, 128, 64);
        $light_gray = imagecolorallocate($img, 200, 200, 200);

        // Fill white background
        imagefilledrectangle($img, 0, 0, self::WIDTH - 1, self::HEIGHT - 1, $white);

        // Draw border
        self::draw_border($img, $green, $light_gray);

        // Check font availability
        $font_file = TMV_PLUGIN_DIR . self::FONT_PATH;
        $use_ttf = file_exists($font_file);

        // Place logos
        self::place_dpdt_logo($img, $use_ttf, $font_file, $dark_gray);
        self::place_bd_seal($img, $use_ttf, $font_file, $dark_gray);

        // Header text
        $y = 350;
        $y = self::draw_text_centered($img, 'Government of the People\'s Republic of Bangladesh', $y, 42, $black, $font_file, $use_ttf);
        $y = self::draw_text_centered($img, 'Department of Patents, Designs & Trade Marks', $y + 20, 36, $dark_gray, $font_file, $use_ttf);
        $y = self::draw_text_centered($img, 'Ministry of Industries, Shilpa Bhaban', $y + 20, 30, $dark_gray, $font_file, $use_ttf);
        $y = self::draw_text_centered($img, '91, Motijheel C/A, Dhaka-1000', $y + 15, 28, $dark_gray, $font_file, $use_ttf);

        // Green separator line
        $y += 40;
        imagesetthickness($img, 4);
        imageline($img, 150, $y, self::WIDTH - 150, $y, $green);
        $y += 30;

        // Certificate title
        $y = self::draw_text_centered($img, $cert_title, $y + 20, 38, $black, $font_file, $use_ttf);

        // TM Number and Registration Date
        $y += 50;
        $y = self::draw_text_centered($img, 'TM Number: ' . $tm_number, $y, 32, $dark_gray, $font_file, $use_ttf);
        $y += 10;
        $y = self::draw_text_centered($img, 'Registration Date: ' . $reg_date, $y, 32, $dark_gray, $font_file, $use_ttf);

        // Body paragraph
        $y += 60;
        $body = 'Certified that the Trademark, of which a representation is annexed hereto, '
              . 'has been registered in the name of ' . $owner . ', having its office at '
              . $address . ', organized under the laws of Bangladesh, in Class ' . $class_num
              . ' as of the date ' . $reg_date . ', in respect of ' . $service_desc;

        $y = self::draw_paragraph($img, $body, 150, $y, self::WIDTH - 300, 30, $dark_gray, $font_file, $use_ttf);

        // Trademark Owner
        $y += 60;
        $y = self::draw_text_centered($img, 'Trademark Owner: ' . $owner, $y, 30, $black, $font_file, $use_ttf);

        // Logo text / brand name
        if (!empty($logo_text)) {
            $y += 40;
            $y = self::draw_text_centered($img, $logo_text, $y, 28, $dark_gray, $font_file, $use_ttf);
        }

        // Brand logo
        if (!empty($brand_logo_id)) {
            $y += 30;
            $y = self::place_brand_logo($img, intval($brand_logo_id), $y);
        }

        // Signatory section (positioned toward bottom)
        $sig_y = 2800;
        if (!empty($signatory)) {
            self::draw_text_centered($img, $signatory, $sig_y, 30, $black, $font_file, $use_ttf);
            $sig_y += 50;
        }
        if (!empty($designation)) {
            self::draw_text_centered($img, $designation, $sig_y, 26, $dark_gray, $font_file, $use_ttf);
            $sig_y += 50;
        }

        // Sealed text
        if (!empty($sealed_text)) {
            $seal_line = $sealed_text . ' - ' . $sealing_date;
            self::draw_text_centered($img, $seal_line, $sig_y + 20, 26, $dark_gray, $font_file, $use_ttf);
        }

        // QR Code
        $verify_url = TMV_API::get_verify_url($post_id);
        self::place_qr_code($img, $verify_url);

        // Verify URL at bottom
        self::draw_text_centered($img, 'Verify: ' . $verify_url, 3380, 24, $green, $font_file, $use_ttf);

        // Save the image
        $result = self::save_certificate($img, $post_id);

        imagedestroy($img);

        return $result;
    }

    /**
     * Draw decorative border around the certificate.
     */
    private static function draw_border($img, $green, $light_gray) {
        // Outer green border
        imagesetthickness($img, 6);
        imagerectangle($img, 40, 40, self::WIDTH - 40, self::HEIGHT - 40, $green);

        // Inner lighter border
        imagesetthickness($img, 2);
        imagerectangle($img, 60, 60, self::WIDTH - 60, self::HEIGHT - 60, $light_gray);

        // Corner decorations (small green squares)
        $corner_size = 30;
        imagefilledrectangle($img, 40, 40, 40 + $corner_size, 40 + $corner_size, $green);
        imagefilledrectangle($img, self::WIDTH - 40 - $corner_size, 40, self::WIDTH - 40, 40 + $corner_size, $green);
        imagefilledrectangle($img, 40, self::HEIGHT - 40 - $corner_size, 40 + $corner_size, self::HEIGHT - 40, $green);
        imagefilledrectangle($img, self::WIDTH - 40 - $corner_size, self::HEIGHT - 40 - $corner_size, self::WIDTH - 40, self::HEIGHT - 40, $green);

        imagesetthickness($img, 1);
    }

    /**
     * Place the DPDT logo at top-left.
     */
    private static function place_dpdt_logo($img, $use_ttf, $font_file, $color) {
        $logo_id = intval(get_option('tmv_dpdt_logo', 0));
        $placed = false;

        if ($logo_id) {
            $file_path = get_attached_file($logo_id);
            if ($file_path && file_exists($file_path)) {
                $placed = self::place_image_on_canvas($img, $file_path, 120, 100, 250, 250);
            }
        }

        if (!$placed) {
            // Draw placeholder
            $placeholder_color = imagecolorallocate($img, 220, 220, 220);
            imagefilledrectangle($img, 120, 100, 370, 350, $placeholder_color);
            imagerectangle($img, 120, 100, 370, 350, $color);
            if ($use_ttf) {
                imagettftext($img, 16, 0, 165, 235, $color, $font_file, 'DPDT Logo');
            } else {
                imagestring($img, 4, 180, 220, 'DPDT Logo', $color);
            }
        }
    }

    /**
     * Place the Bangladesh Govt seal at top-right.
     */
    private static function place_bd_seal($img, $use_ttf, $font_file, $color) {
        $seal_id = intval(get_option('tmv_bd_govt_seal', 0));
        $placed = false;

        if ($seal_id) {
            $file_path = get_attached_file($seal_id);
            if ($file_path && file_exists($file_path)) {
                $placed = self::place_image_on_canvas($img, $file_path, self::WIDTH - 370, 100, 250, 250);
            }
        }

        if (!$placed) {
            // Draw placeholder
            $placeholder_color = imagecolorallocate($img, 220, 220, 220);
            imagefilledrectangle($img, self::WIDTH - 370, 100, self::WIDTH - 120, 350, $placeholder_color);
            imagerectangle($img, self::WIDTH - 370, 100, self::WIDTH - 120, 350, $color);
            if ($use_ttf) {
                imagettftext($img, 16, 0, self::WIDTH - 340, 235, $color, $font_file, 'BD Govt Seal');
            } else {
                imagestring($img, 4, self::WIDTH - 330, 220, 'BD Govt Seal', $color);
            }
        }
    }

    /**
     * Place the brand logo on the certificate.
     *
     * @return int The new Y position after the logo.
     */
    private static function place_brand_logo($img, $logo_id, $y) {
        $file_path = get_attached_file($logo_id);
        if (!$file_path || !file_exists($file_path)) {
            return $y;
        }

        $max_w = 400;
        $max_h = 300;
        $x = (self::WIDTH - $max_w) / 2;

        if (self::place_image_on_canvas($img, $file_path, (int)$x, $y, $max_w, $max_h)) {
            return $y + $max_h + 20;
        }

        return $y;
    }

    /**
     * Load and place an image file onto the canvas.
     *
     * @return bool True if placed successfully.
     */
    private static function place_image_on_canvas($img, $file_path, $x, $y, $max_w, $max_h) {
        $mime = '';
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($file_path);
        } elseif (function_exists('wp_check_filetype')) {
            $info = wp_check_filetype($file_path);
            $mime = $info['type'];
        }

        $src = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $src = @imagecreatefromgif($file_path);
                break;
            default:
                // Try PNG then JPEG as fallback
                $src = @imagecreatefrompng($file_path);
                if (!$src) {
                    $src = @imagecreatefromjpeg($file_path);
                }
                break;
        }

        if (!$src) {
            return false;
        }

        $src_w = imagesx($src);
        $src_h = imagesy($src);

        // Calculate scaled dimensions maintaining aspect ratio
        $ratio = min($max_w / $src_w, $max_h / $src_h);
        $dst_w = (int)($src_w * $ratio);
        $dst_h = (int)($src_h * $ratio);

        // Center within the allocated space
        $dst_x = $x + (int)(($max_w - $dst_w) / 2);
        $dst_y = $y + (int)(($max_h - $dst_h) / 2);

        imagecopyresampled($img, $src, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        imagedestroy($src);

        return true;
    }

    /**
     * Fetch and place QR code on the certificate.
     */
    private static function place_qr_code($img, $verify_url) {
        $qr_size = 300;
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $qr_size . 'x' . $qr_size . '&data=' . urlencode($verify_url);

        $qr_data = false;
        $response = wp_remote_get($qr_url, array('timeout' => 5));
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $qr_data = wp_remote_retrieve_body($response);
        }

        $x = self::WIDTH - 500;
        $y = 3050;

        if (!$qr_data) {
            // If fetch fails, draw a text placeholder with verify URL
            $gray = imagecolorallocate($img, 200, 200, 200);
            $dark = imagecolorallocate($img, 80, 80, 80);
            imagefilledrectangle($img, $x, $y, $x + $qr_size, $y + $qr_size, $gray);
            imagerectangle($img, $x, $y, $x + $qr_size, $y + $qr_size, $dark);
            imagestring($img, 4, $x + 100, $y + 130, 'QR Code', $dark);
            imagestring($img, 2, $x + 20, $y + 160, substr($verify_url, 0, 40), $dark);
            return;
        }

        $qr_img = @imagecreatefromstring($qr_data);
        if (!$qr_img) {
            return;
        }

        imagecopyresampled($img, $qr_img, $x, $y, 0, 0, $qr_size, $qr_size, imagesx($qr_img), imagesy($qr_img));
        imagedestroy($qr_img);
    }

    /**
     * Draw centered text on the image.
     *
     * @return int The Y position after the text.
     */
    private static function draw_text_centered($img, $text, $y, $size, $color, $font_file, $use_ttf) {
        if ($use_ttf) {
            $bbox = imagettfbbox($size, 0, $font_file, $text);
            $text_width = abs($bbox[2] - $bbox[0]);
            $x = (self::WIDTH - $text_width) / 2;
            imagettftext($img, $size, 0, (int)$x, $y, $color, $font_file, $text);
            return $y + abs($bbox[1] - $bbox[7]) + 5;
        } else {
            // Fallback: use built-in font (font 5 is the largest built-in)
            $font_num = 5;
            $char_w = imagefontwidth($font_num);
            $text_width = strlen($text) * $char_w;
            $x = (self::WIDTH - $text_width) / 2;
            if ($x < 0) $x = 10;
            imagestring($img, $font_num, (int)$x, $y, $text, $color);
            return $y + imagefontheight($font_num) + 5;
        }
    }

    /**
     * Draw a wrapped paragraph of text.
     *
     * @return int The Y position after the paragraph.
     */
    private static function draw_paragraph($img, $text, $x, $y, $max_width, $size, $color, $font_file, $use_ttf) {
        if ($use_ttf) {
            $words = explode(' ', $text);
            $line = '';
            $line_height = $size + 12;

            foreach ($words as $word) {
                $test_line = $line ? $line . ' ' . $word : $word;
                $bbox = imagettfbbox($size, 0, $font_file, $test_line);
                $line_width = abs($bbox[2] - $bbox[0]);

                if ($line_width > $max_width && $line !== '') {
                    imagettftext($img, $size, 0, $x, $y, $color, $font_file, $line);
                    $y += $line_height;
                    $line = $word;
                } else {
                    $line = $test_line;
                }
            }

            if ($line !== '') {
                imagettftext($img, $size, 0, $x, $y, $color, $font_file, $line);
                $y += $line_height;
            }
        } else {
            // Fallback: simple word wrap with built-in font
            $font_num = 5;
            $char_w = imagefontwidth($font_num);
            $chars_per_line = (int)($max_width / $char_w);
            $lines = wordwrap($text, $chars_per_line, "\n", true);
            $line_height = imagefontheight($font_num) + 4;

            foreach (explode("\n", $lines) as $line) {
                imagestring($img, $font_num, $x, $y, $line, $color);
                $y += $line_height;
            }
        }

        return $y;
    }

    /**
     * Save the generated certificate image as a WordPress attachment.
     *
     * @return int|false Attachment ID or false on failure.
     */
    private static function save_certificate($img, $post_id) {
        $upload_dir = wp_upload_dir();
        $filename = 'certificate-tm-' . $post_id . '-' . time() . '.png';
        $filepath = $upload_dir['path'] . '/' . $filename;

        // Save PNG to file
        $saved = imagepng($img, $filepath, 6);
        if (!$saved) {
            return false;
        }

        // Create WordPress attachment
        $filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'guid'           => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => $filetype['type'],
            'post_title'     => 'Certificate - TM ' . get_post_meta($post_id, 'tmv_tm_number', true),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment($attachment, $filepath, $post_id);
        if (is_wp_error($attach_id)) {
            return false;
        }

        // Generate attachment metadata
        if (function_exists('wp_generate_attachment_metadata')) {
            $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }

        // Delete old certificate attachment if exists
        $old_id = get_post_meta($post_id, 'tmv_certificate_jpg', true);
        if ($old_id && intval($old_id) !== $attach_id) {
            wp_delete_attachment(intval($old_id), true);
        }

        // Store new certificate attachment ID
        update_post_meta($post_id, 'tmv_certificate_jpg', $attach_id);

        return $attach_id;
    }
}
