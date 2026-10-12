(function () {
    'use strict';

    if (window.LAXCC_AGEGATE_LOADED) return;
    window.LAXCC_AGEGATE_LOADED = true;

    var cfg = window.LAXCC_AG_CONFIG || {};
    var STORAGE_KEY = 'laxcc-age-verified-v2';
    var SESSION_KEY = 'laxcc-ag-session';
    var COOKIE_DAYS = parseInt(cfg.cookieDays, 10) || 365;
    var FORCE_PREVIEW = /[?&]laxcc_ag_preview=1/.test(location.search);
    var AJAX_URL = (cfg.ajaxUrl) ? cfg.ajaxUrl : ((window.location.origin || '') + '/wp-admin/admin-ajax.php');
    var GATE_OPENED_AT = 0;
    var HEARTBEAT_TIMER = null;

    function getSessionId() {
        try {
            var sid = sessionStorage.getItem(SESSION_KEY);
            if (sid) return sid;
            sid = (Date.now().toString(36) + Math.random().toString(36).slice(2, 10)).slice(0, 32);
            sessionStorage.setItem(SESSION_KEY, sid);
            return sid;
        } catch (e) {
            return (Date.now().toString(36) + Math.random().toString(36).slice(2, 10)).slice(0, 32);
        }
    }

    function track(event, extra) {
        try {
            var body = new URLSearchParams();
            body.append('action', 'laxcc_ag_track');
            body.append('event', event);
            body.append('session', getSessionId());
            body.append('page', location.href);
            body.append('ref', document.referrer || '');
            if (extra && extra.duration) body.append('duration', String(extra.duration));

            if (navigator.sendBeacon) {
                var blob = new Blob([body.toString()], { type: 'application/x-www-form-urlencoded' });
                navigator.sendBeacon(AJAX_URL, blob);
            } else {
                fetch(AJAX_URL, { method: 'POST', body: body, keepalive: true, credentials: 'same-origin' });
            }
        } catch (e) {}
    }

    function heartbeat() {
        try {
            var body = new URLSearchParams();
            body.append('action', 'laxcc_ag_heartbeat');
            body.append('session', getSessionId());
            body.append('page', location.href);
            fetch(AJAX_URL, { method: 'POST', body: body, keepalive: true, credentials: 'same-origin' });
        } catch (e) {}
    }

    function startHeartbeat() {
        if (HEARTBEAT_TIMER) return;
        heartbeat();
        HEARTBEAT_TIMER = setInterval(function () {
            if (document.visibilityState !== 'hidden') heartbeat();
        }, 30000);
    }

    function isMobile() {
        return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
    }

    function isVerified() {
        if (FORCE_PREVIEW) return false;
        try {
            return (
                localStorage.getItem(STORAGE_KEY) === 'true' ||
                sessionStorage.getItem(STORAGE_KEY) === 'true' ||
                document.cookie.indexOf(STORAGE_KEY + '=true') !== -1
            );
        } catch (e) { return false; }
    }

    function setVerified() {
        try {
            localStorage.setItem(STORAGE_KEY, 'true');
            sessionStorage.setItem(STORAGE_KEY, 'true');
            document.cookie = STORAGE_KEY + '=true; max-age=' + (COOKIE_DAYS * 86400) + '; path=/';
        } catch (e) {}
    }

    function shouldCelebrate() {
        // In preview mode, ALWAYS show celebration so it can be tested
        if (FORCE_PREVIEW) return true;
        var c = cfg.celebration || {};
        if (c.enabled === false) return false; // explicitly disabled — only blocker
        var today = new Date();
        var pad = function (n) { n = String(n); return n.length < 2 ? '0' + n : n; };
        var ymd = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());
        if (c.startDate && ymd < c.startDate) return false;
        if (c.endDate && ymd > c.endDate) return false;
        return true;
    }

    function injectCustomCss() {
        if (!cfg.customCss) return;
        var s = document.createElement('style');
        s.id = 'laxcc-ag-custom';
        s.textContent = cfg.customCss;
        document.head.appendChild(s);
    }

    function mountAgeGate() {
        var existing = document.getElementById('laxcc-age-gate-root');
        if (existing) existing.remove();

        injectCustomCss();

        // Set CSS variables
        document.documentElement.style.setProperty('--laxcc-ag-brand', cfg.brandColor || '#00AFC9');
        document.documentElement.style.setProperty('--laxcc-ag-overlay', cfg.overlay != null ? cfg.overlay : 0.65);

        var root = document.createElement('div');
        root.id = 'laxcc-age-gate-root';
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-modal', 'true');

        // Background
        var bg = document.createElement('div');
        bg.className = 'laxcc-ag-background';
        var mobile = isMobile();

        if (cfg.bgType === 'video' && (cfg.desktopVideo || cfg.mobileVideo)) {
            var src = mobile && cfg.mobileVideo ? cfg.mobileVideo : (cfg.desktopVideo || cfg.mobileVideo);
            var v = document.createElement('video');
            v.src = src;
            v.autoplay = true;
            v.muted = true;
            v.loop = true;
            v.playsInline = true;
            v.setAttribute('playsinline', '');
            v.setAttribute('webkit-playsinline', '');
            if (cfg.videoPoster) v.poster = cfg.videoPoster;
            bg.appendChild(v);
        } else {
            var imgSrc = mobile && cfg.mobileImage ? cfg.mobileImage : (cfg.desktopImage || cfg.mobileImage);
            if (imgSrc) {
                var img = document.createElement('img');
                img.src = imgSrc;
                img.alt = '';
                bg.appendChild(img);
            }
        }

        var overlay = document.createElement('div');
        overlay.className = 'laxcc-ag-background-overlay';
        bg.appendChild(overlay);
        root.appendChild(bg);

        // Content
        var content = document.createElement('div');
        content.className = 'laxcc-ag-content';

        if (cfg.logo) {
            var logo = document.createElement('img');
            logo.className = 'laxcc-ag-logo';
            logo.src = cfg.logo;
            logo.alt = 'Logo';
            content.appendChild(logo);
        }

        var h = document.createElement('h1');
        h.className = 'laxcc-ag-headline';
        h.textContent = cfg.headline || 'Are you 21?';
        content.appendChild(h);

        var p = document.createElement('p');
        p.className = 'laxcc-ag-subtext';
        p.textContent = cfg.subtext || '';
        content.appendChild(p);

        var btnYes = document.createElement('button');
        btnYes.type = 'button';
        btnYes.className = 'laxcc-ag-btn-primary';
        btnYes.id = 'laxcc-ag-yes';
        btnYes.textContent = cfg.yesText || 'Yes, Enter Site';
        content.appendChild(btnYes);

        var btnNo = document.createElement('button');
        btnNo.type = 'button';
        btnNo.className = 'laxcc-ag-btn-secondary';
        btnNo.id = 'laxcc-ag-no';
        btnNo.textContent = cfg.noText || 'No, I am under 21';
        content.appendChild(btnNo);

        root.appendChild(content);
        document.body.appendChild(root);

        document.documentElement.classList.add('laxcc-ag-locked');
        document.body.classList.add('laxcc-ag-locked');

        requestAnimationFrame(function () { root.classList.add('laxcc-ag-visible'); });

        // Track impression
        GATE_OPENED_AT = Date.now();
        track('view');

        ['click', 'touchend'].forEach(function (evt) {
            btnYes.addEventListener(evt, function (e) {
                if (evt === 'touchend') e.preventDefault();
                e.stopPropagation();
                track('verified', { duration: Date.now() - GATE_OPENED_AT });
                closeGate(root);
            }, { once: true });
        });

        btnNo.addEventListener('click', function () {
            track('rejected', { duration: Date.now() - GATE_OPENED_AT });
            // Small delay so beacon fires before nav
            setTimeout(function () {
                window.location.href = cfg.noRedirect || 'https://www.google.com';
            }, 50);
        });
    }

    function closeGate(root) {
        setVerified();

        if (!shouldCelebrate()) {
            dismiss(root);
            return;
        }

        var c = cfg.celebration || {};
        var template = c.template || '420-editorial';

        var celebrate = document.createElement('div');
        celebrate.className = 'laxcc-ag-celebrate';

        // Render selected template (currently only one — library-ready)
        if (template === '420-editorial') {
            render420Editorial(celebrate, c);
        } else {
            render420Editorial(celebrate, c);
        }

        root.appendChild(celebrate);
        requestAnimationFrame(function () {
            celebrate.classList.add('laxcc-ag-show');
        });

        var hold = parseInt(c.duration, 10);
        if (isNaN(hold) || hold < 0) hold = 4500;
        setTimeout(function () { dismiss(root); }, hold);
    }

    /* ----------------------------------------------------------------
     * Template: 4/20 Editorial
     * ---------------------------------------------------------------- */
    function render420Editorial(celebrate, c) {
        // Apply background image via CSS variable (no inline HTML strings)
        if (c.image) {
            celebrate.style.setProperty('--laxcc-ag-celebration-bg', 'url(' + c.image + ')');
        }

        // Background photo
        var photo = document.createElement('div');
        photo.className = 'laxcc-ag-cb-photo';
        celebrate.appendChild(photo);

        // Aurora glow
        var aurora = document.createElement('div');
        aurora.className = 'laxcc-ag-cb-aurora';
        celebrate.appendChild(aurora);

        // Two diagonal sweeping beams
        var beam1 = document.createElement('div');
        beam1.className = 'laxcc-ag-cb-beam b1';
        var beam2 = document.createElement('div');
        beam2.className = 'laxcc-ag-cb-beam b2';
        celebrate.appendChild(beam1);
        celebrate.appendChild(beam2);

        // Vignette
        var vignette = document.createElement('div');
        vignette.className = 'laxcc-ag-cb-vignette';
        celebrate.appendChild(vignette);

        // Confetti
        var count = parseInt(c.confetti, 10);
        if (isNaN(count)) count = 70;
        if (count > 0) {
            var layer = document.createElement('div');
            layer.className = 'laxcc-ag-cb-confetti-layer';
            var palette = ['#00AFC9', '#7ECBB3', '#1D508A', '#ffffff'];
            for (var i = 0; i < count; i++) {
                var piece = document.createElement('span');
                piece.className = 'laxcc-ag-cb-confetti';
                var st = piece.style;
                st.left = (Math.random() * 100).toFixed(1) + '%';
                st.background = palette[i % palette.length];
                st.width  = (5 + Math.random() * 8).toFixed(0) + 'px';
                st.height = (8 + Math.random() * 10).toFixed(0) + 'px';
                st.animationDelay    = (Math.random() * 4).toFixed(2) + 's';
                st.animationDuration = (3 + Math.random() * 3.5).toFixed(2) + 's';
                st.transform = 'rotate(' + Math.floor(Math.random() * 360) + 'deg)';
                layer.appendChild(piece);
            }
            celebrate.appendChild(layer);
        }

        // Brand mark + hairline
        var mark = document.createElement('span');
        mark.className = 'laxcc-ag-cb-mark';
        mark.textContent = 'LAXCC';
        celebrate.appendChild(mark);

        var hairline = document.createElement('span');
        hairline.className = 'laxcc-ag-cb-hairline';
        celebrate.appendChild(hairline);

        // Content
        var content = document.createElement('div');
        content.className = 'laxcc-ag-cb-content';

        // Hero: "Happy" stacked over the big numerals
        var hero = document.createElement('h1');
        hero.className = 'laxcc-ag-cb-hero';

        var happy = document.createElement('span');
        happy.className = 'laxcc-ag-cb-happy';
        happy.textContent = 'Happy';
        hero.appendChild(happy);

        var headlineText = c.headline || '4/20';
        // Split on "/" to render slash with separate styling, if present
        var slashIdx = headlineText.indexOf('/');
        if (slashIdx > 0 && slashIdx < headlineText.length - 1) {
            hero.appendChild(document.createTextNode(headlineText.substring(0, slashIdx)));
            var slash = document.createElement('span');
            slash.className = 'laxcc-ag-cb-slash';
            slash.textContent = '/';
            hero.appendChild(slash);
            hero.appendChild(document.createTextNode(headlineText.substring(slashIdx + 1)));
        } else {
            hero.appendChild(document.createTextNode(headlineText));
        }
        content.appendChild(hero);

        // Headline (italic serif, mint accent on second sentence)
        var subtext = c.subtext || 'Welcome in. Enjoy the 4/20 Deals!';
        var headline = document.createElement('p');
        headline.className = 'laxcc-ag-cb-headline';
        // Split on first ". " to italicize the second sentence
        var dotIdx = subtext.indexOf('. ');
        if (dotIdx !== -1) {
            headline.appendChild(document.createTextNode(subtext.substring(0, dotIdx + 2)));
            var em = document.createElement('em');
            em.textContent = subtext.substring(dotIdx + 2);
            headline.appendChild(em);
        } else {
            headline.textContent = subtext;
        }
        content.appendChild(headline);

        // Tagline (optional)
        if (c.tagline) {
            var tagline = document.createElement('p');
            tagline.className = 'laxcc-ag-cb-tagline';
            // Split on " · " or " | " for dot styling
            var parts = c.tagline.split(' · ');
            if (parts.length > 1) {
                parts.forEach(function (part, idx) {
                    if (idx > 0) {
                        var dot = document.createElement('span');
                        dot.className = 'dot';
                        dot.textContent = '\u00b7';
                        tagline.appendChild(dot);
                    }
                    tagline.appendChild(document.createTextNode(part));
                });
            } else {
                tagline.textContent = c.tagline;
            }
            content.appendChild(tagline);
        }

        celebrate.appendChild(content);
    }

    function dismiss(root) {
        root.classList.remove('laxcc-ag-visible');
        setTimeout(function () {
            if (root && root.parentNode) root.remove();
            document.documentElement.classList.remove('laxcc-ag-locked');
            document.body.classList.remove('laxcc-ag-locked');
        }, 500);
    }

    function clearPending() {
        document.documentElement.classList.remove('laxcc-ag-pending');
    }

    function init() {
        if (isVerified()) {
            // Already verified — reveal page immediately + start tracking visit
            clearPending();
            startHeartbeat();
            return;
        }
        var run = function () {
            mountAgeGate();
            // Reveal underlying content beneath the gate (gate has higher z-index)
            // so the page is rendered for layout and is ready the moment the gate dismisses.
            requestAnimationFrame(clearPending);
            startHeartbeat();
        };
        if (document.body) run();
        else document.addEventListener('DOMContentLoaded', run);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
