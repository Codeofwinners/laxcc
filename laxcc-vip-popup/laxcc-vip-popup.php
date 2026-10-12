<?php
/**
 * Plugin Name: LAXCC VIP Popup
 * Description: Premium VIP loyalty popup for LAXCC. Toggle on/off, customize colors, images, text, and timing from Settings > VIP Popup.
 * Version: 2.0.0
 * Author: LAXCC
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) exit;

class LAXCC_VIP_Popup {

    private static $option_key = 'laxcc_vip_popup_settings';

    public static function defaults() {
        return array(
            'enabled'          => '1',
            'headline'         => 'VIP Loyalty',
            'subtitle'         => 'Redeem your points for premium cannabis products & exclusive discounts.',
            'image_url'        => 'https://laxcc.com/wp-content/uploads/2024/07/shop-in-house-dispensary-flower.jpg',
            'button_text'      => 'Join the Club Now',
            'button_url'       => 'https://laxcc.com/best-cannabis-club-in-la',
            'left_number'      => '$0.01',
            'left_label'       => 'Pre-Roll',
            'left_desc'        => 'when you join',
            'right_number'     => '3X',
            'right_label'      => 'Points',
            'right_desc'       => 'per $1 spent',
            'trust_text_1'     => 'Zero hidden fees to start earning rewards',
            'trust_text_2'     => 'Takes 30 seconds',
            'color_accent'     => '#00AFC9',
            'color_button'     => '#F89459',
            'color_points'     => '#7ECBB3',
            'delay_seconds'    => '7',
            'frequency_hours'  => '24',
        );
    }

    public static function get_settings() {
        return wp_parse_args(get_option(self::$option_key, array()), self::defaults());
    }

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('wp_footer', array(__CLASS__, 'render_popup'));
    }

    public static function add_menu() {
        add_options_page(
            'VIP Popup Settings',
            'VIP Popup',
            'manage_options',
            'laxcc-vip-popup',
            array(__CLASS__, 'settings_page')
        );
    }

    public static function register_settings() {
        register_setting(self::$option_key . '_group', self::$option_key, array(
            'sanitize_callback' => array(__CLASS__, 'sanitize'),
        ));
    }

    public static function sanitize($input) {
        $defaults = self::defaults();
        $clean = array();
        foreach ($defaults as $key => $default) {
            if ($key === 'enabled') {
                $clean[$key] = isset($input[$key]) ? '1' : '0';
            } elseif (in_array($key, array('image_url', 'button_url'))) {
                $clean[$key] = esc_url_raw(isset($input[$key]) ? $input[$key] : $default);
            } elseif (in_array($key, array('delay_seconds', 'frequency_hours'))) {
                $clean[$key] = absint(isset($input[$key]) ? $input[$key] : $default);
            } elseif (strpos($key, 'color_') === 0) {
                $clean[$key] = sanitize_hex_color(isset($input[$key]) ? $input[$key] : $default);
            } else {
                $clean[$key] = sanitize_text_field(isset($input[$key]) ? $input[$key] : $default);
            }
        }
        return $clean;
    }

    public static function settings_page() {
        $s = self::get_settings();
        ?>
        <div class="wrap">
            <h1>LAXCC VIP Popup Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::$option_key . '_group'); ?>

                <table class="form-table">
                    <!-- TOGGLE -->
                    <tr>
                        <th scope="row">Enable Popup</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo self::$option_key; ?>[enabled]" value="1" <?php checked($s['enabled'], '1'); ?> />
                                Show popup on the frontend
                            </label>
                        </td>
                    </tr>

                    <!-- IMAGE -->
                    <tr>
                        <th scope="row">Header Image URL</th>
                        <td>
                            <input type="url" name="<?php echo self::$option_key; ?>[image_url]" value="<?php echo esc_attr($s['image_url']); ?>" class="large-text" />
                            <p class="description">Paste a full image URL. Use Media Library to upload, then copy the URL.</p>
                        </td>
                    </tr>

                    <!-- TEXT CONTENT -->
                    <tr><th colspan="2"><h2>Text Content</h2></th></tr>
                    <tr>
                        <th>Headline</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[headline]" value="<?php echo esc_attr($s['headline']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Subtitle</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[subtitle]" value="<?php echo esc_attr($s['subtitle']); ?>" class="large-text" /></td>
                    </tr>
                    <tr>
                        <th>Button Text</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[button_text]" value="<?php echo esc_attr($s['button_text']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Button URL</th>
                        <td><input type="url" name="<?php echo self::$option_key; ?>[button_url]" value="<?php echo esc_attr($s['button_url']); ?>" class="large-text" /></td>
                    </tr>

                    <!-- LEFT CARD -->
                    <tr><th colspan="2"><h2>Left Card (Pre-Roll)</h2></th></tr>
                    <tr>
                        <th>Number</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[left_number]" value="<?php echo esc_attr($s['left_number']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Label</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[left_label]" value="<?php echo esc_attr($s['left_label']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[left_desc]" value="<?php echo esc_attr($s['left_desc']); ?>" class="regular-text" /></td>
                    </tr>

                    <!-- RIGHT CARD -->
                    <tr><th colspan="2"><h2>Right Card (Points)</h2></th></tr>
                    <tr>
                        <th>Number</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[right_number]" value="<?php echo esc_attr($s['right_number']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Label</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[right_label]" value="<?php echo esc_attr($s['right_label']); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[right_desc]" value="<?php echo esc_attr($s['right_desc']); ?>" class="regular-text" /></td>
                    </tr>

                    <!-- TRUST LINE -->
                    <tr><th colspan="2"><h2>Trust Line</h2></th></tr>
                    <tr>
                        <th>Trust Text 1</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[trust_text_1]" value="<?php echo esc_attr($s['trust_text_1']); ?>" class="large-text" /></td>
                    </tr>
                    <tr>
                        <th>Trust Text 2</th>
                        <td><input type="text" name="<?php echo self::$option_key; ?>[trust_text_2]" value="<?php echo esc_attr($s['trust_text_2']); ?>" class="large-text" /></td>
                    </tr>

                    <!-- COLORS -->
                    <tr><th colspan="2"><h2>Colors</h2></th></tr>
                    <tr>
                        <th>Accent Color (Left Card Number + Trust Dot)</th>
                        <td><input type="color" name="<?php echo self::$option_key; ?>[color_accent]" value="<?php echo esc_attr($s['color_accent']); ?>" /></td>
                    </tr>
                    <tr>
                        <th>Button Color</th>
                        <td><input type="color" name="<?php echo self::$option_key; ?>[color_button]" value="<?php echo esc_attr($s['color_button']); ?>" /></td>
                    </tr>
                    <tr>
                        <th>Points Color (Right Card Number + Trust Dot 2)</th>
                        <td><input type="color" name="<?php echo self::$option_key; ?>[color_points]" value="<?php echo esc_attr($s['color_points']); ?>" /></td>
                    </tr>

                    <!-- TIMING -->
                    <tr><th colspan="2"><h2>Timing</h2></th></tr>
                    <tr>
                        <th>Delay (seconds)</th>
                        <td><input type="number" name="<?php echo self::$option_key; ?>[delay_seconds]" value="<?php echo esc_attr($s['delay_seconds']); ?>" min="1" max="60" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th>Frequency Cap (hours)</th>
                        <td>
                            <input type="number" name="<?php echo self::$option_key; ?>[frequency_hours]" value="<?php echo esc_attr($s['frequency_hours']); ?>" min="1" max="720" class="small-text" />
                            <p class="description">Hours before showing the popup again after a visitor closes it.</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Popup Settings'); ?>
            </form>
        </div>
        <?php
    }

    public static function hex_to_rgba($hex, $alpha = 1) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    }

    public static function darken_hex($hex, $amount = 20) {
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - $amount);
        $g = max(0, hexdec(substr($hex, 2, 2)) - $amount);
        $b = max(0, hexdec(substr($hex, 4, 2)) - $amount);
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function lighten_hex($hex, $amount = 20) {
        $hex = ltrim($hex, '#');
        $r = min(255, hexdec(substr($hex, 0, 2)) + $amount);
        $g = min(255, hexdec(substr($hex, 2, 2)) + $amount);
        $b = min(255, hexdec(substr($hex, 4, 2)) + $amount);
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function render_popup() {
        if (is_admin()) return;

        $s = self::get_settings();
        if ($s['enabled'] !== '1') return;

        $accent  = esc_attr($s['color_accent']);
        $button  = esc_attr($s['color_button']);
        $points  = esc_attr($s['color_points']);
        $btn_dark   = self::darken_hex($button, 25);
        $btn_light  = self::lighten_hex($button, 20);
        $delay_ms   = intval($s['delay_seconds']) * 1000;
        $freq_hours = intval($s['frequency_hours']);
        ?>

<style>
    /* =============================================
       LAXCC VIP POPUP — All styles scoped to #laxcc-vip-popup
       Zero conflict with WP themes, Elementor, or other plugins.
       ============================================= */
    #laxcc-vip-popup,
    #laxcc-vip-popup *,
    #laxcc-vip-popup *::before,
    #laxcc-vip-popup *::after {
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        outline: none !important;
        text-decoration: none !important;
        list-style: none !important;
        float: none !important;
        clear: none !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        text-indent: 0 !important;
        text-shadow: none !important;
        box-shadow: none !important;
        background: transparent !important;
        color: inherit !important;
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-style: normal !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: normal !important;
        vertical-align: baseline !important;
        max-width: none !important;
        max-height: none !important;
        min-width: 0 !important;
        min-height: 0 !important;
        width: auto !important;
        height: auto !important;
        overflow: visible !important;
        position: static !important;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        transition: none !important;
        animation: none !important;
        border-radius: 0 !important;
        cursor: default !important;
    }

    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;900&display=swap');

    /* --- Root overlay --- */
    #laxcc-vip-popup {
        position: fixed !important;
        inset: 0 !important;
        z-index: 999999 !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
        opacity: 0 !important;
        transition: opacity 0.45s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    #laxcc-vip-popup.laxcc-vip-open {
        display: flex !important;
        opacity: 1 !important;
    }

    /* --- Backdrop --- */
    #laxcc-vip-popup .laxcc-vip-overlay {
        position: absolute !important;
        inset: 0 !important;
        background: rgba(4, 9, 14, 0.85) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        cursor: pointer !important;
    }

    /* --- Card --- */
    #laxcc-vip-popup .laxcc-vip-card {
        position: relative !important;
        z-index: 2 !important;
        width: 100% !important;
        max-width: 460px !important;
        background: #0d141e !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        transform: translateY(30px) scale(0.94) !important;
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    #laxcc-vip-popup.laxcc-vip-open .laxcc-vip-card {
        transform: translateY(0) scale(1) !important;
    }

    /* --- Hero image --- */
    #laxcc-vip-popup .laxcc-vip-img-wrap {
        position: relative !important;
        width: 100% !important;
        height: 170px !important;
        overflow: hidden !important;
    }

    #laxcc-vip-popup .laxcc-vip-img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    #laxcc-vip-popup .laxcc-vip-img-fade {
        position: absolute !important;
        inset: 0 !important;
        background: linear-gradient(to bottom, rgba(13, 20, 30, 0) 30%, #0d141e 100%) !important;
    }

    /* --- Close button --- */
    #laxcc-vip-popup .laxcc-vip-close {
        position: absolute !important;
        top: 14px !important;
        right: 14px !important;
        z-index: 20 !important;
        width: 34px !important;
        height: 34px !important;
        background: rgba(0, 0, 0, 0.45) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 50% !important;
        color: rgba(255, 255, 255, 0.85) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        font-size: 20px !important;
        line-height: 1 !important;
        transition: background 0.2s ease, transform 0.2s ease !important;
    }

    #laxcc-vip-popup .laxcc-vip-close:hover {
        background: rgba(255, 255, 255, 0.12) !important;
        transform: scale(1.1) rotate(90deg) !important;
    }

    /* --- Body --- */
    #laxcc-vip-popup .laxcc-vip-body {
        padding: 0 32px 32px !important;
        text-align: center !important;
        margin-top: -16px !important;
        position: relative !important;
        z-index: 3 !important;
    }

    #laxcc-vip-popup .laxcc-vip-headline {
        font-size: 30px !important;
        font-weight: 900 !important;
        line-height: 1.1 !important;
        color: #ffffff !important;
        margin: 0 0 8px !important;
        letter-spacing: -0.5px !important;
    }

    #laxcc-vip-popup .laxcc-vip-subtitle {
        font-size: 14px !important;
        color: #94a3b8 !important;
        margin: 0 0 24px !important;
        line-height: 1.5 !important;
        font-weight: 300 !important;
    }

    /* --- Hero stat blocks --- */
    #laxcc-vip-popup .laxcc-vip-stats {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
        margin: 0 0 24px !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat {
        padding: 20px 14px !important;
        border-radius: 16px !important;
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat-num {
        font-weight: 900 !important;
        font-size: 36px !important;
        line-height: 1 !important;
        display: block !important;
        margin: 0 0 6px !important;
        letter-spacing: -1px !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat-num.laxcc-vip-accent {
        color: <?php echo $accent; ?> !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat-num.laxcc-vip-points {
        color: <?php echo $points; ?> !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat-label {
        font-weight: 700 !important;
        font-size: 11px !important;
        letter-spacing: 1.2px !important;
        text-transform: uppercase !important;
        color: #f1f5f9 !important;
        display: block !important;
        margin: 0 0 3px !important;
    }

    #laxcc-vip-popup .laxcc-vip-stat-desc {
        font-size: 11px !important;
        color: #64748b !important;
        font-weight: 500 !important;
        display: block !important;
    }

    /* --- CTA button --- */
    #laxcc-vip-popup .laxcc-vip-cta,
    #laxcc-vip-popup a.laxcc-vip-cta:link,
    #laxcc-vip-popup a.laxcc-vip-cta:visited,
    #laxcc-vip-popup a.laxcc-vip-cta:hover,
    #laxcc-vip-popup a.laxcc-vip-cta:active {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 16px !important;
        background: linear-gradient(135deg, <?php echo $button; ?> 0%, <?php echo $btn_dark; ?> 100%) !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        color: #fff !important;
        cursor: pointer !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        letter-spacing: 0.3px !important;
    }

    #laxcc-vip-popup a.laxcc-vip-cta:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 28px -6px <?php echo self::hex_to_rgba($button, 0.45); ?> !important;
    }

    /* --- Trust line --- */
    #laxcc-vip-popup .laxcc-vip-trust {
        display: flex !important;
        justify-content: center !important;
        gap: 20px !important;
        margin: 20px 0 0 !important;
    }

    #laxcc-vip-popup .laxcc-vip-trust-item {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        color: #64748b !important;
    }

    #laxcc-vip-popup .laxcc-vip-trust-dot {
        width: 5px !important;
        height: 5px !important;
        border-radius: 50% !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
    }

    /* --- MOBILE (480px and below) --- */
    @media (max-width: 480px) {
        #laxcc-vip-popup {
            padding: 12px !important;
            align-items: center !important;
        }

        #laxcc-vip-popup .laxcc-vip-card {
            max-width: 100% !important;
            max-height: 92vh !important;
            overflow-y: auto !important;
            border-radius: 20px !important;
        }

        #laxcc-vip-popup .laxcc-vip-img-wrap {
            height: 130px !important;
        }

        #laxcc-vip-popup .laxcc-vip-body {
            padding: 0 20px 24px !important;
            margin-top: -12px !important;
        }

        #laxcc-vip-popup .laxcc-vip-headline {
            font-size: 24px !important;
            margin: 0 0 6px !important;
        }

        #laxcc-vip-popup .laxcc-vip-subtitle {
            font-size: 13px !important;
            margin: 0 0 18px !important;
        }

        #laxcc-vip-popup .laxcc-vip-stats {
            gap: 10px !important;
            margin: 0 0 18px !important;
        }

        #laxcc-vip-popup .laxcc-vip-stat {
            padding: 16px 10px !important;
            border-radius: 14px !important;
        }

        #laxcc-vip-popup .laxcc-vip-stat-num {
            font-size: 28px !important;
        }

        #laxcc-vip-popup .laxcc-vip-stat-label {
            font-size: 10px !important;
        }

        #laxcc-vip-popup a.laxcc-vip-cta,
        #laxcc-vip-popup a.laxcc-vip-cta:link,
        #laxcc-vip-popup a.laxcc-vip-cta:visited {
            padding: 14px !important;
            font-size: 15px !important;
            border-radius: 11px !important;
        }

        #laxcc-vip-popup .laxcc-vip-trust {
            flex-direction: column !important;
            gap: 8px !important;
            align-items: center !important;
            margin: 16px 0 0 !important;
        }

        #laxcc-vip-popup .laxcc-vip-trust-item {
            font-size: 11px !important;
        }
    }
</style>

<div id="laxcc-vip-popup" role="dialog" aria-modal="true" aria-label="VIP Loyalty Popup">
    <div class="laxcc-vip-overlay" data-laxcc-vip-close></div>

    <div class="laxcc-vip-card">
        <button class="laxcc-vip-close" data-laxcc-vip-close aria-label="Close popup">&times;</button>

        <div class="laxcc-vip-img-wrap">
            <img src="<?php echo esc_url($s['image_url']); ?>" alt="<?php echo esc_attr($s['headline']); ?>" class="laxcc-vip-img" loading="lazy">
            <div class="laxcc-vip-img-fade"></div>
        </div>

        <div class="laxcc-vip-body">
            <h2 class="laxcc-vip-headline"><?php echo esc_html($s['headline']); ?></h2>
            <p class="laxcc-vip-subtitle"><?php echo esc_html($s['subtitle']); ?></p>

            <div class="laxcc-vip-stats">
                <div class="laxcc-vip-stat">
                    <span class="laxcc-vip-stat-num laxcc-vip-accent"><?php echo esc_html($s['left_number']); ?></span>
                    <span class="laxcc-vip-stat-label"><?php echo esc_html($s['left_label']); ?></span>
                    <span class="laxcc-vip-stat-desc"><?php echo esc_html($s['left_desc']); ?></span>
                </div>
                <div class="laxcc-vip-stat">
                    <span class="laxcc-vip-stat-num laxcc-vip-points"><?php echo esc_html($s['right_number']); ?></span>
                    <span class="laxcc-vip-stat-label"><?php echo esc_html($s['right_label']); ?></span>
                    <span class="laxcc-vip-stat-desc"><?php echo esc_html($s['right_desc']); ?></span>
                </div>
            </div>

            <a href="<?php echo esc_url($s['button_url']); ?>" class="laxcc-vip-cta" data-laxcc-vip-cta>
                <?php echo esc_html($s['button_text']); ?>
            </a>

            <div class="laxcc-vip-trust">
                <span class="laxcc-vip-trust-item">
                    <span class="laxcc-vip-trust-dot" style="background: <?php echo $accent; ?> !important;"></span>
                    <span><?php echo esc_html($s['trust_text_1']); ?></span>
                </span>
                <span class="laxcc-vip-trust-item">
                    <span class="laxcc-vip-trust-dot" style="background: <?php echo $points; ?> !important;"></span>
                    <span><?php echo esc_html($s['trust_text_2']); ?></span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var cfg = {
        id: 'laxcc_vip_popup_v5',
        delay: <?php echo $delay_ms; ?>,
        freq: <?php echo $freq_hours; ?>
    };

    var root = document.getElementById('laxcc-vip-popup');
    if (!root) return;

    function shouldShow() {
        var t = localStorage.getItem(cfg.id + '_closed');
        if (!t) return true;
        return (Date.now() - parseInt(t)) / 36e5 >= cfg.freq;
    }

    function open() {
        if (!shouldShow()) return;
        root.style.display = 'flex';
        setTimeout(function () {
            root.classList.add('laxcc-vip-open');
            document.body.style.overflow = 'hidden';
        }, 20);
    }

    function close() {
        root.classList.remove('laxcc-vip-open');
        setTimeout(function () {
            root.style.display = 'none';
            document.body.style.overflow = '';
        }, 450);
        localStorage.setItem(cfg.id + '_closed', Date.now().toString());
    }

    root.querySelectorAll('[data-laxcc-vip-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });

    var cta = root.querySelector('[data-laxcc-vip-cta]');
    if (cta) cta.addEventListener('click', close);

    setTimeout(open, cfg.delay);
})();
</script>
        <?php
    }
}

LAXCC_VIP_Popup::init();
