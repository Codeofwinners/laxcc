<?php
/**
 * Plugin Name:       LAXCC Age Gate
 * Plugin URI:        https://laxcc.com
 * Description:       Modern, fully customizable age gate. Elementor-friendly. Separate desktop/mobile backgrounds (image OR video), brand colors, custom messaging, and an optional 4/20 celebration screen with UFO + confetti.
 * Version:           1.1.4
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            LAXCC
 * Author URI:        https://laxcc.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       laxcc-age-gate
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LAXCC_AG_VERSION', '1.1.4');
define('LAXCC_AG_FILE', __FILE__);
define('LAXCC_AG_DIR', plugin_dir_path(__FILE__));
define('LAXCC_AG_URL', plugin_dir_url(__FILE__));
define('LAXCC_AG_OPTION_KEY', 'laxcc_age_gate_settings');
define('LAXCC_AG_NONCE', 'laxcc_age_gate_save');

/* -------------------------------------------------------------------------
 * Defaults
 * -------------------------------------------------------------------------*/
function laxcc_ag_default_settings() {
    return array(
        'enabled'              => 1,
        'logo_url'             => 'https://laxcc.com/wp-content/uploads/2023/05/Logov1_1.png',
        'background_type'      => 'image', // image | video
        'desktop_image'        => 'https://laxcc.com/wp-content/uploads/2024/07/lax-cannabis-club-dispensary-directions.jpg',
        'mobile_image'         => '',
        'desktop_video'        => '',
        'mobile_video'         => '',
        'video_poster'         => '',
        'overlay_darkness'     => 65, // 0-100, % darkening of background
        'brand_color'          => '#00AFC9',
        'headline'             => 'Are you 21?',
        'subtext'              => 'Please verify that you are 21+ or hold a valid medical card to view our menu.',
        'yes_button'           => 'Yes, Enter Site',
        'no_button'            => 'No, I am under 21',
        'no_redirect'          => 'https://www.google.com',
        'cookie_days'          => 30,
        'celebration_enabled'  => 1,
        'celebration_template' => '420-editorial',
        'celebration_image'    => 'https://laxcc.com/wp-content/uploads/2025/02/rove-cannabis-brand03.jpg',
        'celebration_headline' => '4/20',
        'celebration_subtext'  => 'Welcome in. Enjoy the 4/20 Deals!',
        'celebration_tagline'  => 'LAXCC · Five Minutes from LAX',
        'celebration_duration' => 4500,
        'celebration_start'    => '',
        'celebration_end'      => '',
        'confetti_count'       => 70,
        'custom_css'           => '',
    );
}

function laxcc_ag_get_settings() {
    $defaults = laxcc_ag_default_settings();
    $saved    = get_option(LAXCC_AG_OPTION_KEY, array());
    if (!is_array($saved)) {
        $saved = array();
    }
    // Migrate stale defaults from the v1.0 UFO/graffiti template to v1.1 editorial
    $stale = array(
        'celebration_headline' => array('Happy 4/20!!!'),
        'celebration_subtext'  => array('Enjoy the Savings!'),
    );
    foreach ($stale as $key => $stale_values) {
        if (isset($saved[$key]) && in_array($saved[$key], $stale_values, true)) {
            unset($saved[$key]);
        }
    }
    return wp_parse_args($saved, $defaults);
}

/* -------------------------------------------------------------------------
 * Activation
 * -------------------------------------------------------------------------*/
register_activation_hook(__FILE__, 'laxcc_ag_activate');
function laxcc_ag_activate() {
    if (false === get_option(LAXCC_AG_OPTION_KEY)) {
        add_option(LAXCC_AG_OPTION_KEY, laxcc_ag_default_settings());
    }
}

/* -------------------------------------------------------------------------
 * Includes
 * -------------------------------------------------------------------------*/
require_once LAXCC_AG_DIR . 'includes/analytics.php';
require_once LAXCC_AG_DIR . 'includes/admin.php';
require_once LAXCC_AG_DIR . 'includes/frontend.php';
