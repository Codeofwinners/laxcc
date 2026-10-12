<?php
/**
 * LAXCC Age Gate — Admin
 */

if (!defined('ABSPATH')) exit;

/* -------------------------------------------------------------------------
 * Admin menu
 * -------------------------------------------------------------------------*/
/**
 * Suppress all third-party admin notices on our plugin pages so the UI stays clean.
 */
add_action('in_admin_header', 'laxcc_ag_suppress_other_notices', 1000);
function laxcc_ag_suppress_other_notices() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) return;
    if (strpos($screen->id, 'laxcc-age-gate') === false) return;
    remove_all_actions('user_admin_notices');
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
    remove_all_actions('network_admin_notices');
}

add_action('admin_menu', 'laxcc_ag_admin_menu');
function laxcc_ag_admin_menu() {
    add_menu_page(
        __('LAXCC Age Gate', 'laxcc-age-gate'),
        __('Age Gate', 'laxcc-age-gate'),
        'manage_options',
        'laxcc-age-gate',
        'laxcc_ag_render_settings_page',
        'dashicons-shield-alt',
        58
    );
    add_submenu_page(
        'laxcc-age-gate',
        __('Settings', 'laxcc-age-gate'),
        __('Settings', 'laxcc-age-gate'),
        'manage_options',
        'laxcc-age-gate',
        'laxcc_ag_render_settings_page'
    );
    add_submenu_page(
        'laxcc-age-gate',
        __('Analytics', 'laxcc-age-gate'),
        __('Analytics', 'laxcc-age-gate'),
        'manage_options',
        'laxcc-age-gate-analytics',
        'laxcc_ag_render_analytics_page'
    );
}

/* -------------------------------------------------------------------------
 * Save handler
 * -------------------------------------------------------------------------*/
add_action('admin_post_laxcc_ag_save', 'laxcc_ag_handle_save');
function laxcc_ag_handle_save() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You are not allowed to do this.', 'laxcc-age-gate'));
    }
    check_admin_referer(LAXCC_AG_NONCE);

    $defaults = laxcc_ag_default_settings();
    $input    = isset($_POST['laxcc_ag']) && is_array($_POST['laxcc_ag']) ? wp_unslash($_POST['laxcc_ag']) : array();
    $clean    = array();

    // Booleans (checkboxes)
    foreach (array('enabled', 'celebration_enabled') as $k) {
        $clean[$k] = !empty($input[$k]) ? 1 : 0;
    }

    // Strings — sanitize
    $text_fields = array(
        'logo_url', 'desktop_image', 'mobile_image',
        'desktop_video', 'mobile_video', 'video_poster',
        'celebration_template', 'celebration_image',
        'celebration_headline', 'celebration_subtext', 'celebration_tagline',
        'yes_button', 'no_button', 'headline',
    );
    foreach ($text_fields as $k) {
        $clean[$k] = isset($input[$k]) ? sanitize_text_field($input[$k]) : $defaults[$k];
    }

    // Textarea
    $clean['subtext']    = isset($input['subtext']) ? sanitize_textarea_field($input['subtext']) : $defaults['subtext'];
    $clean['custom_css'] = isset($input['custom_css']) ? wp_strip_all_tags($input['custom_css']) : '';

    // URL
    $clean['no_redirect'] = isset($input['no_redirect']) ? esc_url_raw($input['no_redirect']) : $defaults['no_redirect'];

    // Background type
    $bg_type = isset($input['background_type']) ? $input['background_type'] : 'image';
    $clean['background_type'] = in_array($bg_type, array('image', 'video'), true) ? $bg_type : 'image';

    // Color
    $color = isset($input['brand_color']) ? $input['brand_color'] : $defaults['brand_color'];
    $clean['brand_color'] = preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $color) ? $color : $defaults['brand_color'];

    // Numbers
    $clean['overlay_darkness']     = max(0, min(100, intval(isset($input['overlay_darkness']) ? $input['overlay_darkness'] : $defaults['overlay_darkness'])));
    $clean['cookie_days']          = max(1, min(3650, intval(isset($input['cookie_days']) ? $input['cookie_days'] : $defaults['cookie_days'])));
    $clean['celebration_duration'] = max(0, min(20000, intval(isset($input['celebration_duration']) ? $input['celebration_duration'] : $defaults['celebration_duration'])));
    $clean['confetti_count']           = max(0, min(300, intval(isset($input['confetti_count']) ? $input['confetti_count'] : $defaults['confetti_count'])));

    // Dates (YYYY-MM-DD or empty)
    foreach (array('celebration_start', 'celebration_end') as $k) {
        $val = isset($input[$k]) ? trim($input[$k]) : '';
        $clean[$k] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) ? $val : '';
    }

    update_option(LAXCC_AG_OPTION_KEY, $clean);

    wp_safe_redirect(add_query_arg(
        array('page' => 'laxcc-age-gate', 'updated' => 'true'),
        admin_url('admin.php')
    ));
    exit;
}

/* -------------------------------------------------------------------------
 * Reset to defaults
 * -------------------------------------------------------------------------*/
add_action('admin_post_laxcc_ag_reset', 'laxcc_ag_handle_reset');
function laxcc_ag_handle_reset() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You are not allowed to do this.', 'laxcc-age-gate'));
    }
    check_admin_referer('laxcc_ag_reset');

    update_option(LAXCC_AG_OPTION_KEY, laxcc_ag_default_settings());

    wp_safe_redirect(add_query_arg(
        array('page' => 'laxcc-age-gate', 'reset' => 'true'),
        admin_url('admin.php')
    ));
    exit;
}

/* -------------------------------------------------------------------------
 * Admin scripts/styles
 * -------------------------------------------------------------------------*/
add_action('admin_enqueue_scripts', 'laxcc_ag_admin_assets');
function laxcc_ag_admin_assets($hook) {
    $is_settings  = ($hook === 'toplevel_page_laxcc-age-gate');
    $is_analytics = ($hook === 'age-gate_page_laxcc-age-gate-analytics');

    if (!$is_settings && !$is_analytics) return;

    wp_enqueue_style(
        'laxcc-ag-admin',
        LAXCC_AG_URL . 'admin/admin.css',
        array(),
        LAXCC_AG_VERSION
    );

    if ($is_settings) {
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script(
            'laxcc-ag-admin',
            LAXCC_AG_URL . 'admin/admin.js',
            array('jquery', 'wp-color-picker', 'media-upload'),
            LAXCC_AG_VERSION,
            true
        );
    }

    if ($is_analytics) {
        wp_enqueue_script(
            'laxcc-ag-chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            array(),
            '4.4.1',
            true
        );
        wp_enqueue_script(
            'laxcc-ag-analytics',
            LAXCC_AG_URL . 'admin/analytics.js',
            array('jquery', 'laxcc-ag-chartjs'),
            LAXCC_AG_VERSION,
            true
        );
        wp_localize_script('laxcc-ag-analytics', 'LAXCC_AG_ANALYTICS', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('laxcc_ag_live_nonce'),
        ));
    }
}

/* -------------------------------------------------------------------------
 * Settings page
 * -------------------------------------------------------------------------*/
function laxcc_ag_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    $s = laxcc_ag_get_settings();
    include LAXCC_AG_DIR . 'admin/settings-page.php';
}

function laxcc_ag_render_analytics_page() {
    if (!current_user_can('manage_options')) return;
    $s = laxcc_ag_get_settings();
    include LAXCC_AG_DIR . 'admin/analytics-page.php';
}
