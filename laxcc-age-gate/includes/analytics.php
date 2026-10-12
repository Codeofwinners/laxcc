<?php
/**
 * LAXCC Age Gate — Analytics
 *
 * Privacy-first: IPs are hashed with a per-site salt before storage.
 * Geo lookups go through Cloudflare headers when available, falling back
 * to ipwho.is (free HTTPS) and cached for 7 days.
 */

if (!defined('ABSPATH')) exit;

define('LAXCC_AG_TABLE_VISITS',    'laxcc_ag_visits');
define('LAXCC_AG_TABLE_LIVE',      'laxcc_ag_live');
define('LAXCC_AG_TABLE_GEOCACHE',  'laxcc_ag_geocache');
define('LAXCC_AG_LIVE_WINDOW',     300); // seconds — "live" if heartbeat in last 5 min

/* -------------------------------------------------------------------------
 * Table creation
 * -------------------------------------------------------------------------*/
register_activation_hook(LAXCC_AG_FILE, 'laxcc_ag_install_tables');
function laxcc_ag_install_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $visits   = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $live     = $wpdb->prefix . LAXCC_AG_TABLE_LIVE;
    $geocache = $wpdb->prefix . LAXCC_AG_TABLE_GEOCACHE;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE $visits (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        visitor_hash CHAR(64) NOT NULL,
        session_id CHAR(32) NOT NULL,
        country_code CHAR(2) DEFAULT NULL,
        country VARCHAR(80) DEFAULT NULL,
        region VARCHAR(80) DEFAULT NULL,
        city VARCHAR(80) DEFAULT NULL,
        device VARCHAR(16) DEFAULT NULL,
        browser VARCHAR(32) DEFAULT NULL,
        page_url VARCHAR(500) DEFAULT NULL,
        referrer VARCHAR(500) DEFAULT NULL,
        action VARCHAR(16) NOT NULL DEFAULT 'view',
        duration_ms INT UNSIGNED DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY created_at (created_at),
        KEY country_code (country_code),
        KEY action (action),
        KEY visitor_hash (visitor_hash)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $live (
        session_id CHAR(32) NOT NULL,
        last_seen DATETIME NOT NULL,
        country_code CHAR(2) DEFAULT NULL,
        country VARCHAR(80) DEFAULT NULL,
        city VARCHAR(80) DEFAULT NULL,
        device VARCHAR(16) DEFAULT NULL,
        page_url VARCHAR(500) DEFAULT NULL,
        PRIMARY KEY  (session_id),
        KEY last_seen (last_seen)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $geocache (
        ip_hash CHAR(64) NOT NULL,
        country_code CHAR(2) DEFAULT NULL,
        country VARCHAR(80) DEFAULT NULL,
        region VARCHAR(80) DEFAULT NULL,
        city VARCHAR(80) DEFAULT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY  (ip_hash)
    ) $charset_collate;");

    if (!get_option('laxcc_ag_salt')) {
        update_option('laxcc_ag_salt', wp_generate_password(32, true, true));
    }
}

/* -------------------------------------------------------------------------
 * Cron — clean up live table + apply retention
 * -------------------------------------------------------------------------*/
add_action('init', 'laxcc_ag_schedule_cron');
function laxcc_ag_schedule_cron() {
    if (!wp_next_scheduled('laxcc_ag_cleanup')) {
        wp_schedule_event(time() + 60, 'hourly', 'laxcc_ag_cleanup');
    }
}
register_deactivation_hook(LAXCC_AG_FILE, 'laxcc_ag_unschedule_cron');
function laxcc_ag_unschedule_cron() {
    $ts = wp_next_scheduled('laxcc_ag_cleanup');
    if ($ts) wp_unschedule_event($ts, 'laxcc_ag_cleanup');
}

add_action('laxcc_ag_cleanup', 'laxcc_ag_run_cleanup');
function laxcc_ag_run_cleanup() {
    global $wpdb;
    $live = $wpdb->prefix . LAXCC_AG_TABLE_LIVE;
    // Drop live session rows older than 30 minutes (stale session cleanup only).
    // Visit history is kept forever — never auto-deleted.
    $wpdb->query("DELETE FROM $live WHERE last_seen < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 MINUTE)");
}

/* -------------------------------------------------------------------------
 * Helpers
 * -------------------------------------------------------------------------*/
function laxcc_ag_client_ip() {
    foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR') as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '';
}

function laxcc_ag_hash($value) {
    $salt = get_option('laxcc_ag_salt', 'laxcc-ag-default-salt');
    return hash('sha256', $value . '|' . $salt);
}

function laxcc_ag_detect_device($ua) {
    $ua = strtolower($ua);
    if (preg_match('/(ipad|tablet|playbook|silk)|(android(?!.*mobile))/i', $ua)) return 'tablet';
    if (preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile/i', $ua)) return 'mobile';
    return 'desktop';
}

function laxcc_ag_detect_browser($ua) {
    $ua = strtolower($ua);
    if (strpos($ua, 'firefox') !== false) return 'Firefox';
    if (strpos($ua, 'edg/') !== false || strpos($ua, 'edge/') !== false) return 'Edge';
    if (strpos($ua, 'opr/') !== false || strpos($ua, 'opera') !== false) return 'Opera';
    if (strpos($ua, 'chrome') !== false) return 'Chrome';
    if (strpos($ua, 'safari') !== false) return 'Safari';
    return 'Other';
}

/**
 * Resolve geo for a given raw IP. Cached for 7 days. Returns array.
 */
function laxcc_ag_geo_lookup($ip) {
    if (empty($ip)) return array();

    // Cloudflare cheap path — no external call
    $cf_country = !empty($_SERVER['HTTP_CF_IPCOUNTRY']) ? strtoupper(sanitize_text_field($_SERVER['HTTP_CF_IPCOUNTRY'])) : '';

    global $wpdb;
    $table = $wpdb->prefix . LAXCC_AG_TABLE_GEOCACHE;
    $hash  = laxcc_ag_hash($ip);
    $row   = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE ip_hash = %s AND updated_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY) LIMIT 1",
        $hash
    ), ARRAY_A);

    if ($row) {
        return array(
            'country_code' => $row['country_code'],
            'country'      => $row['country'],
            'region'       => $row['region'],
            'city'         => $row['city'],
        );
    }

    // Try ipwho.is (free, HTTPS, no key)
    $resp = wp_remote_get('https://ipwho.is/' . urlencode($ip) . '?fields=success,country,country_code,region,city', array('timeout' => 3));
    $geo = array(
        'country_code' => $cf_country ?: null,
        'country'      => null,
        'region'       => null,
        'city'         => null,
    );

    if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        if (!empty($data['success'])) {
            $geo['country_code'] = !empty($data['country_code']) ? substr($data['country_code'], 0, 2) : $geo['country_code'];
            $geo['country']      = !empty($data['country']) ? substr($data['country'], 0, 80) : null;
            $geo['region']       = !empty($data['region']) ? substr($data['region'], 0, 80) : null;
            $geo['city']         = !empty($data['city']) ? substr($data['city'], 0, 80) : null;
        }
    }

    $wpdb->replace($table, array(
        'ip_hash'      => $hash,
        'country_code' => $geo['country_code'],
        'country'      => $geo['country'],
        'region'       => $geo['region'],
        'city'         => $geo['city'],
        'updated_at'   => current_time('mysql', true),
    ));

    return $geo;
}

/* -------------------------------------------------------------------------
 * AJAX endpoints — visit, heartbeat
 * -------------------------------------------------------------------------*/
add_action('wp_ajax_laxcc_ag_track',         'laxcc_ag_ajax_track');
add_action('wp_ajax_nopriv_laxcc_ag_track',  'laxcc_ag_ajax_track');
function laxcc_ag_ajax_track() {
    global $wpdb;
    $table = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;

    $ip = laxcc_ag_client_ip();
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
    $session = isset($_POST['session']) ? substr(sanitize_text_field(wp_unslash($_POST['session'])), 0, 32) : '';
    $action  = isset($_POST['event']) ? sanitize_key(wp_unslash($_POST['event'])) : 'view';
    $page    = isset($_POST['page']) ? esc_url_raw(wp_unslash($_POST['page'])) : '';
    $ref     = isset($_POST['ref']) ? esc_url_raw(wp_unslash($_POST['ref'])) : '';
    $dur     = isset($_POST['duration']) ? max(0, intval($_POST['duration'])) : 0;

    if (!in_array($action, array('view', 'verified', 'rejected'), true)) $action = 'view';
    if (empty($session)) $session = substr(md5($ip . $ua . microtime(true)), 0, 32);

    $geo    = laxcc_ag_geo_lookup($ip);
    $device = laxcc_ag_detect_device($ua);

    $wpdb->insert($table, array(
        'visitor_hash' => laxcc_ag_hash($ip . '|' . $ua),
        'session_id'   => $session,
        'country_code' => $geo['country_code'],
        'country'      => $geo['country'],
        'region'       => $geo['region'],
        'city'         => $geo['city'],
        'device'       => $device,
        'browser'      => laxcc_ag_detect_browser($ua),
        'page_url'     => substr($page, 0, 500),
        'referrer'     => substr($ref, 0, 500),
        'action'       => $action,
        'duration_ms'  => $dur,
        'created_at'   => current_time('mysql', true),
    ));

    laxcc_ag_update_live($session, $page, $device, $geo);

    wp_send_json_success();
}

add_action('wp_ajax_laxcc_ag_heartbeat',         'laxcc_ag_ajax_heartbeat');
add_action('wp_ajax_nopriv_laxcc_ag_heartbeat',  'laxcc_ag_ajax_heartbeat');
function laxcc_ag_ajax_heartbeat() {
    $session = isset($_POST['session']) ? substr(sanitize_text_field(wp_unslash($_POST['session'])), 0, 32) : '';
    if (empty($session)) wp_send_json_error('no session');

    $page = isset($_POST['page']) ? esc_url_raw(wp_unslash($_POST['page'])) : '';
    $ip   = laxcc_ag_client_ip();
    $ua   = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $geo  = laxcc_ag_geo_lookup($ip);

    laxcc_ag_update_live($session, $page, laxcc_ag_detect_device($ua), $geo);
    wp_send_json_success();
}

function laxcc_ag_update_live($session, $page, $device, $geo) {
    global $wpdb;
    $table = $wpdb->prefix . LAXCC_AG_TABLE_LIVE;
    $wpdb->replace($table, array(
        'session_id'   => $session,
        'last_seen'    => current_time('mysql', true),
        'country_code' => $geo['country_code'],
        'country'      => $geo['country'],
        'city'         => $geo['city'],
        'device'       => $device,
        'page_url'     => substr($page, 0, 500),
    ));
}

/* -------------------------------------------------------------------------
 * Stats queries
 * -------------------------------------------------------------------------*/
/**
 * Site timezone (WordPress setting, fallback to America/Los_Angeles).
 * All reporting is done in this TZ; the DB always stores UTC.
 */
function laxcc_ag_timezone() {
    if (function_exists('wp_timezone')) {
        $tz = wp_timezone();
        if ($tz instanceof DateTimeZone) return $tz;
    }
    $string = get_option('timezone_string');
    if ($string) {
        try { return new DateTimeZone($string); } catch (Exception $e) {}
    }
    return new DateTimeZone('America/Los_Angeles');
}

/**
 * Current UTC offset for the site TZ as "+HH:MM" / "-HH:MM".
 * Safe to pass to MySQL CONVERT_TZ — doesn't require the mysql.time_zone tables.
 */
function laxcc_ag_tz_offset() {
    $tz  = laxcc_ag_timezone();
    $now = new DateTime('now', $tz);
    return $now->format('P');
}

/**
 * Get a [start_utc, end_utc, label, prev_start_utc, prev_end_utc] window for a given period key.
 * Boundaries (midnight, first-of-month) are computed in the site TZ, then converted to UTC
 * so that queries against UTC-stored created_at line up with a local-day perspective.
 */
function laxcc_ag_period_range($period) {
    $tz     = laxcc_ag_timezone();
    $now    = new DateTimeImmutable('now', $tz);
    $to_utc = function ($d) {
        /** @var DateTimeImmutable $d */
        return $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    };

    switch ($period) {
        case 'today':
            $start = $now->setTime(0, 0, 0);
            $end   = $now;
            $label = 'Today';
            $span  = 86400;
            break;
        case 'yesterday':
            $start = $now->modify('-1 day')->setTime(0, 0, 0);
            $end   = $now->setTime(0, 0, 0);
            $label = 'Yesterday';
            $span  = 86400;
            break;
        case '7d':
            $start = $now->modify('-7 days');
            $end   = $now;
            $label = 'Last 7 days';
            $span  = 7 * 86400;
            break;
        case '30d':
            $start = $now->modify('-30 days');
            $end   = $now;
            $label = 'Last 30 days';
            $span  = 30 * 86400;
            break;
        case '90d':
            $start = $now->modify('-90 days');
            $end   = $now;
            $label = 'Last 90 days';
            $span  = 90 * 86400;
            break;
        case 'mtd':
            $start = $now->modify('first day of this month')->setTime(0, 0, 0);
            $end   = $now;
            $label = 'Month to date';
            $span  = $end->getTimestamp() - $start->getTimestamp();
            break;
        case 'all':
            $start = new DateTimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));
            $end   = $now;
            $label = 'All time';
            $span  = 0;
            break;
        default:
            return laxcc_ag_period_range('30d');
    }

    $prev_start = null;
    $prev_end   = null;
    if ($span > 0) {
        $prev_start = $start->modify('-' . $span . ' seconds');
        $prev_end   = $start;
    }

    return array(
        'start'      => $to_utc($start),
        'end'        => $to_utc($end),
        'label'      => $label,
        'prev_start' => $prev_start ? $to_utc($prev_start) : null,
        'prev_end'   => $prev_end   ? $to_utc($prev_end)   : null,
        'span'       => $span,
    );
}

function laxcc_ag_pct_change($curr, $prev) {
    if ($prev == 0) return $curr > 0 ? 100 : 0;
    return round((($curr - $prev) / $prev) * 100, 1);
}

function laxcc_ag_stats_summary($period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $live   = $wpdb->prefix . LAXCC_AG_TABLE_LIVE;
    $r = laxcc_ag_period_range($period);

    $views    = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $visits WHERE created_at >= %s AND created_at < %s AND action = 'view'", $r['start'], $r['end']));
    $verified = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $visits WHERE created_at >= %s AND created_at < %s AND action = 'verified'", $r['start'], $r['end']));
    $rejected = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $visits WHERE created_at >= %s AND created_at < %s AND action = 'rejected'", $r['start'], $r['end']));
    $unique   = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT visitor_hash) FROM $visits WHERE created_at >= %s AND created_at < %s", $r['start'], $r['end']));
    $avg_dur  = (float) $wpdb->get_var($wpdb->prepare("SELECT AVG(duration_ms) FROM $visits WHERE created_at >= %s AND created_at < %s AND duration_ms > 0 AND action IN ('verified','rejected')", $r['start'], $r['end']));

    // Previous period for comparison
    $prev_views = $prev_unique = $prev_verified = 0;
    if ($r['prev_start']) {
        $prev_views    = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $visits WHERE created_at >= %s AND created_at < %s AND action = 'view'", $r['prev_start'], $r['prev_end']));
        $prev_unique   = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT visitor_hash) FROM $visits WHERE created_at >= %s AND created_at < %s", $r['prev_start'], $r['prev_end']));
        $prev_verified = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $visits WHERE created_at >= %s AND created_at < %s AND action = 'verified'", $r['prev_start'], $r['prev_end']));
    }

    $live_now = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $live WHERE last_seen > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d SECOND)",
        LAXCC_AG_LIVE_WINDOW
    ));

    $rate = ($verified + $rejected) > 0 ? round(($verified / ($verified + $rejected)) * 100, 1) : 0;

    return array(
        'period_label'     => $r['label'],
        'live_now'         => $live_now,
        'views'            => $views,
        'verified'         => $verified,
        'rejected'         => $rejected,
        'unique'           => $unique,
        'rate'             => $rate,
        'avg_duration_ms'  => round($avg_dur),
        'views_change'     => laxcc_ag_pct_change($views, $prev_views),
        'unique_change'    => laxcc_ag_pct_change($unique, $prev_unique),
        'verified_change'  => laxcc_ag_pct_change($verified, $prev_verified),
    );
}

function laxcc_ag_stats_hourly($period = 'today') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r   = laxcc_ag_period_range($period);
    $off = laxcc_ag_tz_offset();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT HOUR(CONVERT_TZ(created_at, '+00:00', %s)) AS h, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s AND action = 'view'
         GROUP BY h",
        $off, $r['start'], $r['end']
    ), ARRAY_A);
    $bucket = array_fill(0, 24, 0);
    foreach ($rows as $row) {
        if ($row['h'] === null) continue;
        $bucket[(int)$row['h']] = (int)$row['c'];
    }
    return $bucket;
}

/**
 * Time-series for the chart, bucketed by appropriate granularity for the period.
 * Bucketing is done in the site TZ so days line up with local midnight.
 */
function laxcc_ag_stats_series($period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r   = laxcc_ag_period_range($period);
    $off = laxcc_ag_tz_offset();
    $tz  = laxcc_ag_timezone();

    $bucket = 'day';
    if (in_array($period, array('today', 'yesterday'), true)) {
        $bucket = 'hour';
    } elseif ($period === '90d' || $period === 'all') {
        $bucket = 'week';
    }

    // Local window for zero-fill iteration
    $start_local = (new DateTimeImmutable($r['start'], new DateTimeZone('UTC')))->setTimezone($tz);
    $end_local   = (new DateTimeImmutable($r['end'],   new DateTimeZone('UTC')))->setTimezone($tz);

    if ($bucket === 'hour') {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', %s), '%%Y-%%m-%%d %%H:00') AS k,
                    SUM(action = 'view') AS views,
                    SUM(action = 'verified') AS verified,
                    SUM(action = 'rejected') AS rejected
             FROM $visits
             WHERE created_at >= %s AND created_at < %s
             GROUP BY k ORDER BY k ASC",
            $off, $r['start'], $r['end']
        ), ARRAY_A);
        $out = array();
        $cursor = $start_local->setTime((int)$start_local->format('H'), 0, 0);
        while ($cursor < $end_local) {
            $out[$cursor->format('Y-m-d H:00')] = array('views' => 0, 'verified' => 0, 'rejected' => 0);
            $cursor = $cursor->modify('+1 hour');
        }
        foreach ($rows as $row) {
            if (isset($out[$row['k']])) {
                $out[$row['k']] = array(
                    'views'    => (int)$row['views'],
                    'verified' => (int)$row['verified'],
                    'rejected' => (int)$row['rejected'],
                );
            }
        }
        return array('bucket' => 'hour', 'data' => $out);
    }

    if ($bucket === 'week') {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(
                        DATE_SUB(
                            DATE(CONVERT_TZ(created_at, '+00:00', %s)),
                            INTERVAL WEEKDAY(CONVERT_TZ(created_at, '+00:00', %s)) DAY
                        ),
                        '%%Y-%%m-%%d'
                    ) AS k,
                    SUM(action = 'view') AS views,
                    SUM(action = 'verified') AS verified,
                    SUM(action = 'rejected') AS rejected
             FROM $visits
             WHERE created_at >= %s AND created_at < %s
             GROUP BY k ORDER BY k ASC",
            $off, $off, $r['start'], $r['end']
        ), ARRAY_A);
        $out = array();
        foreach ($rows as $row) {
            $out[$row['k']] = array(
                'views'    => (int)$row['views'],
                'verified' => (int)$row['verified'],
                'rejected' => (int)$row['rejected'],
            );
        }
        return array('bucket' => 'week', 'data' => $out);
    }

    // Daily
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(CONVERT_TZ(created_at, '+00:00', %s)) AS k,
                SUM(action = 'view') AS views,
                SUM(action = 'verified') AS verified,
                SUM(action = 'rejected') AS rejected
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         GROUP BY k ORDER BY k ASC",
        $off, $r['start'], $r['end']
    ), ARRAY_A);
    $out = array();
    $cursor = $start_local->setTime(0, 0, 0);
    while ($cursor < $end_local) {
        $out[$cursor->format('Y-m-d')] = array('views' => 0, 'verified' => 0, 'rejected' => 0);
        $cursor = $cursor->modify('+1 day');
    }
    foreach ($rows as $row) {
        if (isset($out[$row['k']])) {
            $out[$row['k']] = array(
                'views'    => (int)$row['views'],
                'verified' => (int)$row['verified'],
                'rejected' => (int)$row['rejected'],
            );
        }
    }
    return array('bucket' => 'day', 'data' => $out);
}

/**
 * Day-of-week × hour heatmap in the site TZ, over the given period.
 */
function laxcc_ag_stats_heatmap($period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r   = laxcc_ag_period_range($period);
    $off = laxcc_ag_tz_offset();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT DAYOFWEEK(CONVERT_TZ(created_at, '+00:00', %s)) AS d,
                HOUR(CONVERT_TZ(created_at, '+00:00', %s)) AS h,
                COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s AND action = 'view'
         GROUP BY d, h",
        $off, $off, $r['start'], $r['end']
    ), ARRAY_A);
    // 7 days x 24 hours, init zero
    $grid = array();
    for ($d = 0; $d < 7; $d++) {
        $grid[$d] = array_fill(0, 24, 0);
    }
    foreach ($rows as $row) {
        if ($row['d'] === null || $row['h'] === null) continue;
        // MySQL DAYOFWEEK: 1=Sunday..7=Saturday → shift to 0..6
        $d = ((int)$row['d']) - 1;
        $h = (int)$row['h'];
        $grid[$d][$h] = (int)$row['c'];
    }
    return $grid;
}

function laxcc_ag_stats_top_referrers($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT referrer, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND action = 'view'
         AND referrer != '' AND referrer IS NOT NULL
         GROUP BY referrer
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
    foreach ($rows as &$row) {
        $host = parse_url($row['referrer'], PHP_URL_HOST);
        $row['host'] = $host ? $host : $row['referrer'];
    }
    return $rows;
}

function laxcc_ag_stats_top_pages($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT page_url, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND action = 'view'
         AND page_url != '' AND page_url IS NOT NULL
         GROUP BY page_url
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
    foreach ($rows as &$row) {
        $path = parse_url($row['page_url'], PHP_URL_PATH);
        $row['path'] = $path ? $path : $row['page_url'];
    }
    return $rows;
}

function laxcc_ag_stats_top_countries($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT COALESCE(country, country_code) AS country, country_code, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND country_code IS NOT NULL AND country_code != ''
         GROUP BY country_code
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
}

function laxcc_ag_stats_top_states($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT region, country_code, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND region IS NOT NULL AND region != ''
         GROUP BY region, country_code
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
}

function laxcc_ag_stats_top_cities($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT city, country_code, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND city IS NOT NULL AND city != ''
         GROUP BY city, country_code
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
}

function laxcc_ag_stats_top_browsers($limit = 8, $period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT browser, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND browser IS NOT NULL AND browser != ''
         GROUP BY browser
         ORDER BY c DESC
         LIMIT %d",
        $r['start'], $r['end'], $limit
    ), ARRAY_A);
}

function laxcc_ag_stats_devices($period = '30d') {
    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $r = laxcc_ag_period_range($period);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT device, COUNT(*) AS c
         FROM $visits
         WHERE created_at >= %s AND created_at < %s
         AND device IS NOT NULL
         GROUP BY device",
        $r['start'], $r['end']
    ), ARRAY_A);
}

function laxcc_ag_stats_live_list($limit = 12) {
    global $wpdb;
    $live = $wpdb->prefix . LAXCC_AG_TABLE_LIVE;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT city, country, country_code, device, page_url, last_seen
         FROM $live
         WHERE last_seen > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d SECOND)
         ORDER BY last_seen DESC
         LIMIT %d",
        LAXCC_AG_LIVE_WINDOW,
        $limit
    ), ARRAY_A);
}

/* -------------------------------------------------------------------------
 * AJAX — admin live refresh
 * -------------------------------------------------------------------------*/
add_action('wp_ajax_laxcc_ag_live_data', 'laxcc_ag_ajax_live_data');
function laxcc_ag_ajax_live_data() {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden');
    check_ajax_referer('laxcc_ag_live_nonce');

    $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '30d';

    wp_send_json_success(array(
        'summary' => laxcc_ag_stats_summary($period),
        'live'    => laxcc_ag_stats_live_list(12),
    ));
}

/* -------------------------------------------------------------------------
 * CSV export
 * -------------------------------------------------------------------------*/
add_action('admin_post_laxcc_ag_export', 'laxcc_ag_export_csv');
function laxcc_ag_export_csv() {
    if (!current_user_can('manage_options')) wp_die('Not allowed.');
    check_admin_referer('laxcc_ag_export');

    global $wpdb;
    $visits = $wpdb->prefix . LAXCC_AG_TABLE_VISITS;
    $rows = $wpdb->get_results(
        "SELECT created_at, action, country, country_code, region, city, device, browser, page_url, referrer
         FROM $visits
         WHERE created_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 90 DAY)
         ORDER BY created_at DESC",
        ARRAY_A
    );

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laxcc-age-gate-' . gmdate('Ymd-His') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, array('Timestamp (UTC)', 'Action', 'Country', 'Code', 'Region', 'City', 'Device', 'Browser', 'Page', 'Referrer'));
    foreach ($rows as $r) {
        fputcsv($out, $r);
    }
    fclose($out);
    exit;
}
