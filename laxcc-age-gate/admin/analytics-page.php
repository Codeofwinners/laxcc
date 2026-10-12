<?php
if (!defined('ABSPATH')) exit;
/** @var array $s */

// Period from URL — default 30d
$period = isset($_GET['period']) ? sanitize_key($_GET['period']) : '30d';
$valid_periods = array(
    'today'     => 'Today',
    'yesterday' => 'Yesterday',
    '7d'        => '7 days',
    '30d'       => '30 days',
    'mtd'       => 'Month to date',
    '90d'       => '90 days',
    'all'       => 'All time',
);
if (!isset($valid_periods[$period])) $period = '30d';

$summary    = laxcc_ag_stats_summary($period);
$series     = laxcc_ag_stats_series($period);
$hourly     = laxcc_ag_stats_hourly($period);
$heatmap    = laxcc_ag_stats_heatmap($period);
$countries  = laxcc_ag_stats_top_countries(8, $period);
$states     = laxcc_ag_stats_top_states(8, $period);
$cities     = laxcc_ag_stats_top_cities(8, $period);
$devices    = laxcc_ag_stats_devices($period);
$browsers   = laxcc_ag_stats_top_browsers(8, $period);
$referrers  = laxcc_ag_stats_top_referrers(8, $period);
$pages      = laxcc_ag_stats_top_pages(8, $period);
$live       = laxcc_ag_stats_live_list(12);

// Timezone display helpers
$laxcc_ag_tz       = laxcc_ag_timezone();
$laxcc_ag_tz_now   = new DateTime('now', $laxcc_ag_tz);
$laxcc_ag_tz_abbr  = $laxcc_ag_tz_now->format('T');   // "PST" or "PDT"
$laxcc_ag_tz_label = $laxcc_ag_tz_abbr ?: $laxcc_ag_tz->getName();

function laxcc_ag_period_url($p) {
    return esc_url(add_query_arg(array('page' => 'laxcc-age-gate-analytics', 'period' => $p), admin_url('admin.php')));
}
function laxcc_ag_change_class($v) {
    if ($v > 0) return 'up';
    if ($v < 0) return 'down';
    return 'flat';
}
function laxcc_ag_change_arrow($v) {
    if ($v > 0) return '▲';
    if ($v < 0) return '▼';
    return '·';
}
function laxcc_ag_format_duration($ms) {
    if ($ms < 1000) return $ms . 'ms';
    $s = $ms / 1000;
    if ($s < 60) return number_format($s, 1) . 's';
    return floor($s / 60) . 'm ' . floor($s % 60) . 's';
}
?>
<div class="wrap laxcc-ag-wrap">

    <header class="laxcc-ag-hero">
        <div class="laxcc-ag-hero-icon"><span class="dashicons dashicons-chart-area"></span></div>
        <div class="laxcc-ag-hero-text">
            <h1>Analytics</h1>
            <p>Visitor activity, locations, and verification metrics — privacy-first, no third-party trackers. All times <?php echo esc_html($laxcc_ag_tz_label); ?>.</p>
        </div>
        <div class="laxcc-ag-hero-actions">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin:0;">
                <?php wp_nonce_field('laxcc_ag_export'); ?>
                <input type="hidden" name="action" value="laxcc_ag_export">
                <button type="submit" class="laxcc-ag-btn laxcc-ag-btn-ghost">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </button>
            </form>
        </div>
    </header>

    <!-- Period selector -->
    <nav class="laxcc-ag-period" role="tablist">
        <?php foreach ($valid_periods as $key => $label) : ?>
            <a href="<?php echo laxcc_ag_period_url($key); ?>"
               class="laxcc-ag-period-btn <?php echo $period === $key ? 'is-active' : ''; ?>"
               role="tab"
               aria-selected="<?php echo $period === $key ? 'true' : 'false'; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Stats cards row -->
    <div class="laxcc-ag-stat-row">
        <div class="laxcc-ag-stat-card laxcc-ag-stat-live">
            <div class="laxcc-ag-stat-label"><span class="laxcc-ag-pulse"></span> Live now</div>
            <div class="laxcc-ag-stat-value" data-live="live_now"><?php echo esc_html($summary['live_now']); ?></div>
            <div class="laxcc-ag-stat-meta">people on site</div>
        </div>
        <div class="laxcc-ag-stat-card">
            <div class="laxcc-ag-stat-label"><span class="dashicons dashicons-visibility"></span> Impressions</div>
            <div class="laxcc-ag-stat-value"><?php echo esc_html(number_format_i18n($summary['views'])); ?></div>
            <div class="laxcc-ag-stat-meta">
                <span class="laxcc-ag-change <?php echo laxcc_ag_change_class($summary['views_change']); ?>">
                    <?php echo laxcc_ag_change_arrow($summary['views_change']); ?> <?php echo esc_html(abs($summary['views_change'])); ?>%
                </span>
                vs prev period
            </div>
        </div>
        <div class="laxcc-ag-stat-card">
            <div class="laxcc-ag-stat-label"><span class="dashicons dashicons-groups"></span> Unique visitors</div>
            <div class="laxcc-ag-stat-value"><?php echo esc_html(number_format_i18n($summary['unique'])); ?></div>
            <div class="laxcc-ag-stat-meta">
                <span class="laxcc-ag-change <?php echo laxcc_ag_change_class($summary['unique_change']); ?>">
                    <?php echo laxcc_ag_change_arrow($summary['unique_change']); ?> <?php echo esc_html(abs($summary['unique_change'])); ?>%
                </span>
                vs prev period
            </div>
        </div>
        <div class="laxcc-ag-stat-card">
            <div class="laxcc-ag-stat-label"><span class="dashicons dashicons-yes-alt"></span> Verified</div>
            <div class="laxcc-ag-stat-value"><?php echo esc_html(number_format_i18n($summary['verified'])); ?></div>
            <div class="laxcc-ag-stat-meta">
                <span class="laxcc-ag-change <?php echo laxcc_ag_change_class($summary['verified_change']); ?>">
                    <?php echo laxcc_ag_change_arrow($summary['verified_change']); ?> <?php echo esc_html(abs($summary['verified_change'])); ?>%
                </span>
                vs prev period
            </div>
        </div>
        <div class="laxcc-ag-stat-card">
            <div class="laxcc-ag-stat-label"><span class="dashicons dashicons-chart-pie"></span> Conversion</div>
            <div class="laxcc-ag-stat-value"><?php echo esc_html($summary['rate']); ?>%</div>
            <div class="laxcc-ag-stat-meta">verified / decided</div>
        </div>
        <div class="laxcc-ag-stat-card">
            <div class="laxcc-ag-stat-label"><span class="dashicons dashicons-clock"></span> Avg decision time</div>
            <div class="laxcc-ag-stat-value"><?php echo esc_html(laxcc_ag_format_duration($summary['avg_duration_ms'])); ?></div>
            <div class="laxcc-ag-stat-meta">time on gate</div>
        </div>
    </div>

    <!-- Time series + hourly -->
    <div class="laxcc-ag-grid">
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-chart-line"></span> <?php echo esc_html($summary['period_label']); ?> · views, verified, rejected</h2>
            <div class="laxcc-ag-chart-wrap">
                <canvas id="laxcc-ag-chart-series"></canvas>
            </div>
            <script type="application/json" id="laxcc-ag-data-series"><?php echo wp_json_encode($series); ?></script>
        </div>
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-clock"></span> Hour of day</h2>
            <p class="laxcc-ag-help">When visitors arrive · <?php echo esc_html(strtolower($summary['period_label'])); ?> · <?php echo esc_html($laxcc_ag_tz_label); ?>.</p>
            <div class="laxcc-ag-chart-wrap">
                <canvas id="laxcc-ag-chart-hourly"></canvas>
            </div>
            <script type="application/json" id="laxcc-ag-data-hourly"><?php echo wp_json_encode($hourly); ?></script>
        </div>
    </div>

    <!-- Heatmap + devices -->
    <div class="laxcc-ag-grid">
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-calendar-alt"></span> Day-of-week × hour heatmap</h2>
            <p class="laxcc-ag-help"><?php echo esc_html($summary['period_label']); ?> · <?php echo esc_html($laxcc_ag_tz_label); ?> · darker cells = more traffic.</p>
            <div id="laxcc-ag-heatmap" class="laxcc-ag-heatmap"></div>
            <script type="application/json" id="laxcc-ag-data-heatmap"><?php echo wp_json_encode($heatmap); ?></script>
        </div>
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-smartphone"></span> Devices · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <div class="laxcc-ag-chart-wrap laxcc-ag-chart-doughnut">
                <canvas id="laxcc-ag-chart-devices"></canvas>
            </div>
            <script type="application/json" id="laxcc-ag-data-devices"><?php echo wp_json_encode($devices); ?></script>
        </div>
    </div>

    <!-- Geography -->
    <div class="laxcc-ag-grid">
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-admin-site"></span> Top countries · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($countries)) : ?>
                <p class="laxcc-ag-empty">No country data yet.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $countries));
                foreach ($countries as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label">
                        <?php echo esc_html($r['country'] ? $r['country'] : $r['country_code']); ?>
                        <em><?php echo esc_html($r['country_code']); ?></em>
                    </span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-location"></span> Top cities · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($cities)) : ?>
                <p class="laxcc-ag-empty">No city data yet.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $cities));
                foreach ($cities as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label">
                        <?php echo esc_html($r['city']); ?>
                        <em><?php echo esc_html($r['country_code']); ?></em>
                    </span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- States + Browsers -->
    <div class="laxcc-ag-grid">
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-flag"></span> Top states / regions · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($states)) : ?>
                <p class="laxcc-ag-empty">No state/region data yet.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $states));
                foreach ($states as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label">
                        <?php echo esc_html($r['region']); ?>
                        <em><?php echo esc_html($r['country_code']); ?></em>
                    </span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-admin-appearance"></span> Top browsers · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($browsers)) : ?>
                <p class="laxcc-ag-empty">No browser data yet.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $browsers));
                foreach ($browsers as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label"><?php echo esc_html($r['browser']); ?></span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Referrers + Pages -->
    <div class="laxcc-ag-grid">
        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-external"></span> Top referrers · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($referrers)) : ?>
                <p class="laxcc-ag-empty">Mostly direct traffic.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $referrers));
                foreach ($referrers as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label" title="<?php echo esc_attr($r['referrer']); ?>">
                        <?php echo esc_html($r['host']); ?>
                    </span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="laxcc-ag-card">
            <h2><span class="dashicons dashicons-admin-page"></span> Top landing pages · <?php echo esc_html(strtolower($summary['period_label'])); ?></h2>
            <?php if (empty($pages)) : ?>
                <p class="laxcc-ag-empty">No page data yet.</p>
            <?php else :
                $max = max(array_map(function($r){ return (int)$r['c']; }, $pages));
                foreach ($pages as $r) :
                    $pct = $max > 0 ? round(((int)$r['c'] / $max) * 100, 1) : 0;
            ?>
                <div class="laxcc-ag-bar-row">
                    <span class="laxcc-ag-bar-label" title="<?php echo esc_attr($r['page_url']); ?>">
                        <?php echo esc_html($r['path']); ?>
                    </span>
                    <span class="laxcc-ag-bar"><span style="width: <?php echo esc_attr($pct); ?>%"></span></span>
                    <span class="laxcc-ag-bar-value"><?php echo esc_html(number_format_i18n((int)$r['c'])); ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Live activity -->
    <div class="laxcc-ag-card laxcc-ag-live-feed">
        <h2><span class="dashicons dashicons-marker"></span> Live activity</h2>
        <p class="laxcc-ag-help">Updates every 15 seconds.</p>
        <ul id="laxcc-ag-live-list" class="laxcc-ag-live-list">
            <?php if (empty($live)) : ?>
                <li class="laxcc-ag-empty-row">Nobody on site right now.</li>
            <?php else : foreach ($live as $row) : ?>
                <li>
                    <span class="laxcc-ag-flag"><?php echo esc_html($row['country_code'] ? $row['country_code'] : '··'); ?></span>
                    <span class="laxcc-ag-loc">
                        <strong><?php echo esc_html(($row['city'] ? $row['city'] . ', ' : '') . ($row['country'] ? $row['country'] : 'Unknown')); ?></strong>
                        <em><?php echo esc_html($row['device']); ?></em>
                    </span>
                    <span class="laxcc-ag-time">just now</span>
                </li>
            <?php endforeach; endif; ?>
        </ul>
    </div>

</div>
