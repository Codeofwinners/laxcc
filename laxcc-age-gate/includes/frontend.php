<?php
/**
 * LAXCC Age Gate — Frontend
 */

if (!defined('ABSPATH')) exit;

/**
 * Detect search-engine + social crawlers so they see the page cleanly.
 * NOT cloaking — the underlying HTML is identical, we just skip the JS gate.
 * Standard practice for age gates (Google explicitly allows this).
 */
function laxcc_ag_is_bot() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) return false;
    $ua = strtolower((string) $_SERVER['HTTP_USER_AGENT']);
    $patterns = array(
        'googlebot', 'google-inspectiontool', 'google-site-verification', 'storebot-google',
        'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandex', 'sogou',
        'applebot', 'petalbot', 'qwantify',
        'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot',
        'pinterest', 'redditbot', 'embedly', 'whatsapp', 'telegrambot',
        'showyoubot', 'outbrain', 'quora link preview', 'discordbot', 'skypeuripreview',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'rogerbot',
        'screaming frog', 'sitechecker', 'lighthouse', 'pagespeed',
        'crawler', 'spider', 'bot/'
    );
    foreach ($patterns as $p) {
        if (strpos($ua, $p) !== false) return true;
    }
    return false;
}

/**
 * Helper: are we serving the gate at all on this request?
 */
function laxcc_ag_should_serve() {
    if (is_admin()) return false;
    $s = laxcc_ag_get_settings();
    if (empty($s['enabled'])) return false;
    if (laxcc_ag_is_bot()) return false; // bots bypass for clean SEO
    return true;
}

/* -------------------------------------------------------------------------
 * Head guard — prevents content flash + handles JS-disabled visitors
 * Printed at very top of <head> so it parses before any content renders.
 * -------------------------------------------------------------------------*/
add_action('wp_head', 'laxcc_ag_print_head_guard', 1);
function laxcc_ag_print_head_guard() {
    if (!laxcc_ag_should_serve()) return;
    ?>
    <style id="laxcc-ag-flash-guard">
        html.laxcc-ag-pending body > *:not(#laxcc-age-gate-root) { visibility: hidden !important; }
        html.laxcc-ag-pending body { background: #000 !important; }
    </style>
    <script id="laxcc-ag-pre-check">
    (function(){
        try {
            var k = 'laxcc-age-verified-v2';
            var verified =
                localStorage.getItem(k) === 'true' ||
                sessionStorage.getItem(k) === 'true' ||
                document.cookie.indexOf(k + '=true') !== -1;
            var preview = /[?&]laxcc_ag_preview=1/.test(location.search);
            if (!verified || preview) {
                document.documentElement.classList.add('laxcc-ag-pending');
            }
        } catch(e) {}
    })();
    </script>
    <noscript>
        <style id="laxcc-ag-noscript-guard">
            body { visibility: hidden !important; }
            html::before {
                content: 'JavaScript is required to access this site for age verification. Please enable it and reload.';
                visibility: visible !important;
                position: fixed;
                inset: 0;
                z-index: 2147483647;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #000;
                color: #fff;
                padding: 40px;
                text-align: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 16px;
                line-height: 1.5;
            }
        </style>
    </noscript>
    <?php
}

/* -------------------------------------------------------------------------
 * Graffiti font for celebration — only loaded when needed
 * -------------------------------------------------------------------------*/
add_action('wp_head', 'laxcc_ag_head_assets', 2);
function laxcc_ag_head_assets() {
    if (!laxcc_ag_should_serve()) return;
    $s = laxcc_ag_get_settings();
    if (empty($s['celebration_enabled'])) return;
    echo "<link rel='preconnect' href='https://fonts.googleapis.com' crossorigin>\n";
    echo "<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>\n";
    echo "<link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,700;9..144,900&display=swap'>\n";
}

/* -------------------------------------------------------------------------
 * Asset enqueue
 * -------------------------------------------------------------------------*/
add_action('wp_enqueue_scripts', 'laxcc_ag_frontend_assets', 999);
function laxcc_ag_frontend_assets() {
    if (!laxcc_ag_should_serve()) return;

    $s = laxcc_ag_get_settings();

    wp_enqueue_style(
        'laxcc-age-gate',
        LAXCC_AG_URL . 'assets/age-gate.css',
        array(),
        LAXCC_AG_VERSION
    );
    wp_enqueue_script(
        'laxcc-age-gate',
        LAXCC_AG_URL . 'assets/age-gate.js',
        array(),
        LAXCC_AG_VERSION,
        false // load in head so the gate paints before content
    );

    $config = array(
        'ajaxUrl'      => admin_url('admin-ajax.php'),
        'analytics'    => !empty($s['analytics_enabled']),
        'logo'         => $s['logo_url'],
        'bgType'       => $s['background_type'],
        'desktopImage' => $s['desktop_image'],
        'mobileImage'  => $s['mobile_image'] ? $s['mobile_image'] : $s['desktop_image'],
        'desktopVideo' => $s['desktop_video'],
        'mobileVideo'  => $s['mobile_video'] ? $s['mobile_video'] : $s['desktop_video'],
        'videoPoster'  => $s['video_poster'],
        'overlay'      => intval($s['overlay_darkness']) / 100,
        'brandColor'   => $s['brand_color'],
        'headline'     => $s['headline'],
        'subtext'      => $s['subtext'],
        'yesText'      => $s['yes_button'],
        'noText'       => $s['no_button'],
        'noRedirect'   => $s['no_redirect'],
        'cookieDays'   => intval($s['cookie_days']),
        'celebration'  => array(
            'enabled'   => !empty($s['celebration_enabled']),
            'template'  => isset($s['celebration_template']) ? $s['celebration_template'] : '420-editorial',
            'image'     => isset($s['celebration_image']) ? $s['celebration_image'] : '',
            'headline'  => $s['celebration_headline'],
            'subtext'   => $s['celebration_subtext'],
            'tagline'   => isset($s['celebration_tagline']) ? $s['celebration_tagline'] : '',
            'duration'  => intval($s['celebration_duration']),
            'startDate' => $s['celebration_start'],
            'endDate'   => $s['celebration_end'],
            'confetti'  => intval($s['confetti_count']),
        ),
        'customCss'    => $s['custom_css'],
    );

    wp_localize_script('laxcc-age-gate', 'LAXCC_AG_CONFIG', $config);
}
