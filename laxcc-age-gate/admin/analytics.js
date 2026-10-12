(function ($) {
    'use strict';

    var BRAND      = '#00AFC9';
    var BRAND_DARK = '#1D508A';
    var MINT       = '#7ECBB3';
    var ACCENT     = '#F89459';
    var GREEN      = '#34c759';
    var RED        = '#ef4444';

    function readJSON(id) {
        var el = document.getElementById(id);
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (e) { return null; }
    }

    // 12-hour time label for hour `h` (0..23) — e.g. "12a", "1a", "12p", "1p"
    function hour12(h) {
        h = parseInt(h, 10) || 0;
        if (h === 0)  return '12a';
        if (h === 12) return '12p';
        return h < 12 ? (h + 'a') : ((h - 12) + 'p');
    }

    function shortLabel(key, bucket) {
        if (bucket === 'hour') return hour12(parseInt(key.slice(11, 13), 10));
        if (bucket === 'week') return 'Wk ' + key.slice(5);
        return key.slice(5);
    }

    $(function () {

        // --- Time series (views / verified / rejected) ---
        var series = readJSON('laxcc-ag-data-series');
        if (series && series.data) {
            var keys = Object.keys(series.data);
            var labels = keys.map(function (k) { return shortLabel(k, series.bucket); });
            var views    = keys.map(function (k) { return series.data[k].views; });
            var verified = keys.map(function (k) { return series.data[k].verified; });
            var rejected = keys.map(function (k) { return series.data[k].rejected; });

            new Chart(document.getElementById('laxcc-ag-chart-series'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Views', data: views,
                            borderColor: BRAND, backgroundColor: 'rgba(0,175,201,0.10)',
                            borderWidth: 2.5, fill: true, tension: 0.35,
                            pointRadius: 2.5, pointBackgroundColor: BRAND, pointBorderColor: '#fff', pointBorderWidth: 1.5
                        },
                        {
                            label: 'Verified', data: verified,
                            borderColor: GREEN, backgroundColor: 'rgba(52,199,89,0.0)',
                            borderWidth: 2, fill: false, tension: 0.35,
                            pointRadius: 2, pointBackgroundColor: GREEN, pointBorderColor: '#fff', pointBorderWidth: 1.5
                        },
                        {
                            label: 'Rejected', data: rejected,
                            borderColor: RED, backgroundColor: 'rgba(239,68,68,0)',
                            borderWidth: 2, fill: false, tension: 0.35,
                            pointRadius: 2, pointBackgroundColor: RED, pointBorderColor: '#fff', pointBorderWidth: 1.5
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, color: '#0f172a', font: { size: 12, weight: 600 } }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                        y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' }, ticks: { color: '#64748b', font: { size: 11 }, precision: 0 } }
                    }
                }
            });
        }

        // --- Hourly bar ---
        var hourly = readJSON('laxcc-ag-data-hourly');
        if (hourly) {
            new Chart(document.getElementById('laxcc-ag-chart-hourly'), {
                type: 'bar',
                data: {
                    labels: hourly.map(function (_, i) { return hour12(i); }),
                    datasets: [{
                        data: hourly,
                        backgroundColor: 'rgba(29, 80, 138, 0.85)',
                        borderRadius: 4,
                        maxBarThickness: 14
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' }, ticks: { color: '#64748b', font: { size: 11 }, precision: 0 } }
                    }
                }
            });
        }

        // --- Devices doughnut ---
        var devices = readJSON('laxcc-ag-data-devices');
        if (devices && devices.length) {
            var labels = devices.map(function (d) { return d.device || 'unknown'; });
            var data   = devices.map(function (d) { return parseInt(d.c, 10); });
            var colors = [BRAND, ACCENT, MINT, '#8B5CF6', '#FFD700'];
            new Chart(document.getElementById('laxcc-ag-chart-devices'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, data.length),
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { color: '#0f172a', font: { size: 12, weight: 600 }, padding: 14, usePointStyle: true } } }
                }
            });
        }

        // --- Heatmap ---
        var heatmap = readJSON('laxcc-ag-data-heatmap');
        if (heatmap) {
            var $hm = $('#laxcc-ag-heatmap').empty();
            var dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            // Find max for scaling
            var max = 0;
            for (var d = 0; d < 7; d++) {
                for (var h = 0; h < 24; h++) {
                    if (heatmap[d][h] > max) max = heatmap[d][h];
                }
            }

            // Header row — label only a few anchor hours, 12-hour format
            var headLabels = { 0: '12a', 6: '6a', 12: '12p', 18: '6p' };
            var $head = $('<div class="laxcc-ag-hm-row laxcc-ag-hm-head"/>');
            $head.append('<span class="laxcc-ag-hm-label"></span>');
            for (var h2 = 0; h2 < 24; h2++) {
                $head.append('<span class="laxcc-ag-hm-tick">' + (headLabels[h2] || '') + '</span>');
            }
            $hm.append($head);

            // Day rows
            for (var d2 = 0; d2 < 7; d2++) {
                var $row = $('<div class="laxcc-ag-hm-row"/>');
                $row.append('<span class="laxcc-ag-hm-label">' + dayLabels[d2] + '</span>');
                for (var h3 = 0; h3 < 24; h3++) {
                    var v = heatmap[d2][h3];
                    var alpha = max > 0 ? (v / max) : 0;
                    var color = 'rgba(0, 175, 201, ' + (0.05 + alpha * 0.85) + ')';
                    var $cell = $('<span class="laxcc-ag-hm-cell"/>')
                        .css('background', color)
                        .attr('title', dayLabels[d2] + ' ' + hour12(h3) + ' — ' + v + ' views');
                    $row.append($cell);
                }
                $hm.append($row);
            }
        }

        // --- Live refresh: stats cards + activity list ---
        var currentPeriod = (function () {
            var m = location.search.match(/[?&]period=([^&]+)/);
            return m ? decodeURIComponent(m[1]) : '30d';
        })();

        function fmt(n) {
            n = parseInt(n, 10) || 0;
            return n.toLocaleString();
        }

        function refreshLive() {
            $.post(LAXCC_AG_ANALYTICS.ajaxUrl, {
                action: 'laxcc_ag_live_data',
                _ajax_nonce: LAXCC_AG_ANALYTICS.nonce,
                period: currentPeriod
            }, function (resp) {
                if (!resp || !resp.success) return;
                var s = resp.data.summary;

                // Update all summary cards in real time
                $('[data-live="live_now"]').text(fmt(s.live_now));
                $('[data-live="today_total"]').text(fmt(s.today_total || s.views));
                $('[data-live="unique_today"]').text(fmt(s.unique_today || s.unique));
                $('[data-live="rate"]').text((s.rate || 0) + '%');

                // Live activity list
                var $list = $('#laxcc-ag-live-list').empty();
                var rows = resp.data.live;
                if (!rows.length) {
                    $list.append('<li class="laxcc-ag-empty-row">Nobody on site right now.</li>');
                } else {
                    rows.forEach(function (r) {
                        var $li = $('<li/>');
                        $('<span class="laxcc-ag-flag"/>').text(r.country_code || '··').appendTo($li);
                        var $loc = $('<span class="laxcc-ag-loc"/>');
                        $('<strong/>').text((r.city ? r.city + ', ' : '') + (r.country || 'Unknown')).appendTo($loc);
                        $('<em/>').text(r.device || 'desktop').appendTo($loc);
                        $loc.appendTo($li);
                        $('<span class="laxcc-ag-time"/>').text('just now').appendTo($li);
                        $list.append($li);
                    });
                }
            });
        }

        // Refresh every 10 seconds while page is visible
        var refreshTimer = setInterval(function () {
            if (document.visibilityState !== 'hidden') refreshLive();
        }, 10000);

        // Refresh immediately when tab becomes visible
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshLive();
        });

    });
})(jQuery);
