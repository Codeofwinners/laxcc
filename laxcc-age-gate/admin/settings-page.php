<?php
if (!defined('ABSPATH')) exit;
/** @var array $s — current settings, provided by laxcc_ag_render_settings_page() */

$tabs = array(
    'general'     => array('label' => 'General',     'icon' => 'admin-settings'),
    'background'  => array('label' => 'Background',  'icon' => 'format-image'),
    'branding'    => array('label' => 'Branding',    'icon' => 'art'),
    'celebration' => array('label' => '4/20 Mode',   'icon' => 'awards'),
    'advanced'    => array('label' => 'Advanced',    'icon' => 'admin-tools'),
);
?>

<div class="wrap laxcc-ag-wrap">

    <header class="laxcc-ag-hero">
        <div class="laxcc-ag-hero-icon">
            <span class="dashicons dashicons-shield-alt"></span>
        </div>
        <div class="laxcc-ag-hero-text">
            <h1>LAXCC Age Gate</h1>
            <p>Customizable age verification overlay — Elementor friendly, responsive, video or image, optional 4/20 celebration.</p>
        </div>
        <div class="laxcc-ag-hero-actions">
            <a href="<?php echo esc_url(home_url('/?laxcc_ag_preview=1')); ?>" target="_blank" class="laxcc-ag-btn laxcc-ag-btn-ghost">
                <span class="dashicons dashicons-visibility"></span> Preview
            </a>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin:0;" onsubmit="return confirm('Reset all settings to defaults?');">
                <?php wp_nonce_field('laxcc_ag_reset'); ?>
                <input type="hidden" name="action" value="laxcc_ag_reset">
                <button type="submit" class="laxcc-ag-btn laxcc-ag-btn-ghost">
                    <span class="dashicons dashicons-undo"></span> Reset
                </button>
            </form>
        </div>
    </header>

    <?php if (!empty($_GET['updated'])) : ?>
        <div class="laxcc-ag-toast laxcc-ag-toast-success">
            <span class="dashicons dashicons-yes-alt"></span> Settings saved.
        </div>
    <?php elseif (!empty($_GET['reset'])) : ?>
        <div class="laxcc-ag-toast laxcc-ag-toast-success">
            <span class="dashicons dashicons-yes-alt"></span> Reset to defaults.
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="laxcc-ag-form">
        <?php wp_nonce_field(LAXCC_AG_NONCE); ?>
        <input type="hidden" name="action" value="laxcc_ag_save">

        <div class="laxcc-ag-shell">

            <!-- Master toggle card -->
            <div class="laxcc-ag-card laxcc-ag-master">
                <div class="laxcc-ag-master-text">
                    <strong>Age Gate Status</strong>
                    <span class="laxcc-ag-help">Turn the entire age gate on or off site-wide.</span>
                </div>
                <label class="laxcc-ag-toggle">
                    <input type="checkbox" name="laxcc_ag[enabled]" value="1" <?php checked($s['enabled'], 1); ?>>
                    <span class="laxcc-ag-toggle-slider"></span>
                </label>
            </div>

            <!-- Tabs -->
            <nav class="laxcc-ag-tabs" role="tablist">
                <?php foreach ($tabs as $key => $tab) : ?>
                    <button type="button" class="laxcc-ag-tab <?php echo $key === 'general' ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($key); ?>" role="tab">
                        <span class="dashicons dashicons-<?php echo esc_attr($tab['icon']); ?>"></span>
                        <?php echo esc_html($tab['label']); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <!-- Tab: General -->
            <section class="laxcc-ag-panel is-active" data-panel="general">
                <div class="laxcc-ag-grid">
                    <div class="laxcc-ag-card">
                        <h2>Headline &amp; Copy</h2>
                        <p class="laxcc-ag-help">What visitors read on the verification screen.</p>

                        <label class="laxcc-ag-field">
                            <span>Headline</span>
                            <input type="text" name="laxcc_ag[headline]" value="<?php echo esc_attr($s['headline']); ?>" placeholder="Are you 21?">
                        </label>

                        <label class="laxcc-ag-field">
                            <span>Subtext</span>
                            <textarea name="laxcc_ag[subtext]" rows="3" placeholder="Please verify…"><?php echo esc_textarea($s['subtext']); ?></textarea>
                        </label>
                    </div>

                    <div class="laxcc-ag-card">
                        <h2>Buttons</h2>
                        <p class="laxcc-ag-help">Confirm and reject button labels + redirect.</p>

                        <label class="laxcc-ag-field">
                            <span>Yes button text</span>
                            <input type="text" name="laxcc_ag[yes_button]" value="<?php echo esc_attr($s['yes_button']); ?>">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>No button text</span>
                            <input type="text" name="laxcc_ag[no_button]" value="<?php echo esc_attr($s['no_button']); ?>">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>"No" redirect URL</span>
                            <input type="url" name="laxcc_ag[no_redirect]" value="<?php echo esc_attr($s['no_redirect']); ?>" placeholder="https://www.google.com">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>Remember verification (days)</span>
                            <input type="number" min="1" max="3650" name="laxcc_ag[cookie_days]" value="<?php echo esc_attr($s['cookie_days']); ?>">
                        </label>
                    </div>
                </div>
            </section>

            <!-- Tab: Background -->
            <section class="laxcc-ag-panel" data-panel="background">
                <div class="laxcc-ag-card">
                    <h2>Background Type</h2>
                    <p class="laxcc-ag-help">Pick image (best for performance) or video (more cinematic). Mobile + desktop sources upload separately so dimensions stay sharp.</p>

                    <div class="laxcc-ag-radio-group" data-bind="background-type">
                        <label class="laxcc-ag-radio-card">
                            <input type="radio" name="laxcc_ag[background_type]" value="image" <?php checked($s['background_type'], 'image'); ?>>
                            <span class="laxcc-ag-radio-card-inner">
                                <span class="dashicons dashicons-format-image"></span>
                                <strong>Image</strong>
                                <em>JPG, PNG, WebP</em>
                            </span>
                        </label>
                        <label class="laxcc-ag-radio-card">
                            <input type="radio" name="laxcc_ag[background_type]" value="video" <?php checked($s['background_type'], 'video'); ?>>
                            <span class="laxcc-ag-radio-card-inner">
                                <span class="dashicons dashicons-format-video"></span>
                                <strong>Video</strong>
                                <em>MP4, WebM</em>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="laxcc-ag-grid laxcc-ag-bg-image-fields" data-show-when="image">
                    <div class="laxcc-ag-card">
                        <h2><span class="dashicons dashicons-desktop"></span> Desktop Image</h2>
                        <p class="laxcc-ag-help">Recommended ~1920×1080.</p>
                        <?php laxcc_ag_media_field('desktop_image', $s['desktop_image'], 'image'); ?>
                    </div>
                    <div class="laxcc-ag-card">
                        <h2><span class="dashicons dashicons-smartphone"></span> Mobile Image</h2>
                        <p class="laxcc-ag-help">Recommended portrait, ~750×1334. Falls back to desktop if blank.</p>
                        <?php laxcc_ag_media_field('mobile_image', $s['mobile_image'], 'image'); ?>
                    </div>
                </div>

                <div class="laxcc-ag-grid laxcc-ag-bg-video-fields" data-show-when="video">
                    <div class="laxcc-ag-card">
                        <h2><span class="dashicons dashicons-desktop"></span> Desktop Video</h2>
                        <p class="laxcc-ag-help">MP4 / WebM. Keep under 5 MB for fast load. Auto-plays muted.</p>
                        <?php laxcc_ag_media_field('desktop_video', $s['desktop_video'], 'video'); ?>
                    </div>
                    <div class="laxcc-ag-card">
                        <h2><span class="dashicons dashicons-smartphone"></span> Mobile Video</h2>
                        <p class="laxcc-ag-help">Portrait crop preferred. Falls back to desktop if blank.</p>
                        <?php laxcc_ag_media_field('mobile_video', $s['mobile_video'], 'video'); ?>
                    </div>
                    <div class="laxcc-ag-card">
                        <h2>Video Poster (fallback frame)</h2>
                        <p class="laxcc-ag-help">Image shown while video loads.</p>
                        <?php laxcc_ag_media_field('video_poster', $s['video_poster'], 'image'); ?>
                    </div>
                </div>

                <div class="laxcc-ag-card">
                    <h2>Background Darkening</h2>
                    <p class="laxcc-ag-help">How much to darken the background for text legibility.</p>
                    <label class="laxcc-ag-field laxcc-ag-range-field">
                        <span><strong id="laxcc-ag-overlay-val"><?php echo intval($s['overlay_darkness']); ?>%</strong></span>
                        <input type="range" min="0" max="100" name="laxcc_ag[overlay_darkness]" value="<?php echo esc_attr($s['overlay_darkness']); ?>" id="laxcc-ag-overlay-input">
                    </label>
                </div>
            </section>

            <!-- Tab: Branding -->
            <section class="laxcc-ag-panel" data-panel="branding">
                <div class="laxcc-ag-grid">
                    <div class="laxcc-ag-card">
                        <h2>Logo</h2>
                        <p class="laxcc-ag-help">Shown above the headline. PNG with transparent background works best.</p>
                        <?php laxcc_ag_media_field('logo_url', $s['logo_url'], 'image'); ?>
                    </div>
                    <div class="laxcc-ag-card">
                        <h2>Brand Color</h2>
                        <p class="laxcc-ag-help">Color of the primary "Yes, Enter" button + glow.</p>
                        <label class="laxcc-ag-field">
                            <span>Hex color</span>
                            <input type="text" class="laxcc-ag-color" name="laxcc_ag[brand_color]" value="<?php echo esc_attr($s['brand_color']); ?>">
                        </label>
                    </div>
                </div>
            </section>

            <!-- Tab: 4/20 Celebration -->
            <section class="laxcc-ag-panel" data-panel="celebration">
                <div class="laxcc-ag-card laxcc-ag-master">
                    <div class="laxcc-ag-master-text">
                        <strong>4/20 Celebration Screen</strong>
                        <span class="laxcc-ag-help">After a visitor confirms, show a UFO + confetti celebration before letting them in.</span>
                    </div>
                    <label class="laxcc-ag-toggle">
                        <input type="checkbox" name="laxcc_ag[celebration_enabled]" value="1" <?php checked($s['celebration_enabled'], 1); ?>>
                        <span class="laxcc-ag-toggle-slider"></span>
                    </label>
                </div>

                <div class="laxcc-ag-card">
                    <h2><span class="dashicons dashicons-layout"></span> Template</h2>
                    <p class="laxcc-ag-help">Pick a celebration design. More templates coming.</p>
                    <div class="laxcc-ag-radio-group" style="grid-template-columns: 1fr;">
                        <label class="laxcc-ag-radio-card">
                            <input type="radio" name="laxcc_ag[celebration_template]" value="420-editorial" <?php checked(($s['celebration_template'] ?? '420-editorial'), '420-editorial'); ?>>
                            <span class="laxcc-ag-radio-card-inner" style="flex-direction: row; align-items: center; gap: 14px; text-align: left; padding: 16px 18px;">
                                <span class="dashicons dashicons-cover-image" style="font-size: 22px; width: 22px; height: 22px;"></span>
                                <span style="flex: 1;">
                                    <strong style="display: block;">4/20 Editorial</strong>
                                    <em style="font-size: 11px;">Photo backdrop, italic serif type, brand-color confetti + sweeping beams</em>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="laxcc-ag-grid">
                    <div class="laxcc-ag-card">
                        <h2><span class="dashicons dashicons-format-image"></span> Background Image</h2>
                        <p class="laxcc-ag-help">Photo behind the celebration. Auto-darkened for legibility.</p>
                        <?php laxcc_ag_media_field('celebration_image', $s['celebration_image'] ?? '', 'image'); ?>
                    </div>

                    <div class="laxcc-ag-card">
                        <h2>Copy</h2>

                        <label class="laxcc-ag-field">
                            <span>Hero text (big numerals)</span>
                            <input type="text" name="laxcc_ag[celebration_headline]" value="<?php echo esc_attr($s['celebration_headline']); ?>" placeholder="4/20">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>Headline (italic line beneath)</span>
                            <input type="text" name="laxcc_ag[celebration_subtext]" value="<?php echo esc_attr($s['celebration_subtext']); ?>" placeholder="Welcome in. Enjoy the 4/20 Deals!">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>Tagline (small caps, optional)</span>
                            <input type="text" name="laxcc_ag[celebration_tagline]" value="<?php echo esc_attr($s['celebration_tagline'] ?? ''); ?>" placeholder="LAXCC · Five Minutes from LAX">
                        </label>
                    </div>
                </div>

                <div class="laxcc-ag-grid">
                    <div class="laxcc-ag-card">
                        <h2>Animation</h2>

                        <label class="laxcc-ag-field">
                            <span>Confetti pieces (0–300)</span>
                            <input type="number" min="0" max="300" name="laxcc_ag[confetti_count]" value="<?php echo esc_attr($s['confetti_count']); ?>">
                        </label>

                        <label class="laxcc-ag-field">
                            <span>Hold duration (ms)</span>
                            <input type="number" min="0" max="20000" step="100" name="laxcc_ag[celebration_duration]" value="<?php echo esc_attr($s['celebration_duration']); ?>">
                        </label>
                    </div>

                    <div class="laxcc-ag-card">
                        <h2>Schedule (optional)</h2>
                        <p class="laxcc-ag-help">Limit the celebration to a date range. Leave blank to show year-round.</p>

                        <label class="laxcc-ag-field">
                            <span>Start date</span>
                            <input type="date" name="laxcc_ag[celebration_start]" value="<?php echo esc_attr($s['celebration_start']); ?>">
                        </label>
                        <label class="laxcc-ag-field">
                            <span>End date</span>
                            <input type="date" name="laxcc_ag[celebration_end]" value="<?php echo esc_attr($s['celebration_end']); ?>">
                        </label>
                    </div>
                </div>
            </section>

            <!-- Tab: Advanced -->
            <section class="laxcc-ag-panel" data-panel="advanced">
                <div class="laxcc-ag-card">
                    <h2>Custom CSS</h2>
                    <p class="laxcc-ag-help">Inject extra styles inside the gate. Scoped to <code>#laxcc-age-gate-root</code>. No <code>&lt;style&gt;</code> tags.</p>
                    <textarea name="laxcc_ag[custom_css]" rows="10" class="laxcc-ag-mono" placeholder=".laxcc-ag-headline { letter-spacing: -2px; }"><?php echo esc_textarea($s['custom_css']); ?></textarea>
                </div>

                <div class="laxcc-ag-card">
                    <h2>Reliability &amp; SEO</h2>
                    <ul class="laxcc-ag-tips">
                        <li><span class="dashicons dashicons-yes"></span> Search-engine + social crawlers (Googlebot, Bingbot, Facebook, Twitter, etc.) bypass the gate so they index your page cleanly.</li>
                        <li><span class="dashicons dashicons-yes"></span> Inline head-guard hides body content until the gate paints — zero content flash on slow connections.</li>
                        <li><span class="dashicons dashicons-yes"></span> <code>&lt;noscript&gt;</code> fallback hides the entire page and shows a "JavaScript required" message for visitors with JS disabled.</li>
                        <li><span class="dashicons dashicons-yes"></span> Triple-redundant persistence: localStorage + sessionStorage + cookie.</li>
                        <li><span class="dashicons dashicons-yes"></span> Mobile fields fall back to desktop when blank.</li>
                        <li><span class="dashicons dashicons-yes"></span> Append <code>?laxcc_ag_preview=1</code> to any page URL to force the gate even after verifying.</li>
                    </ul>
                </div>
            </section>

        </div>

        <div class="laxcc-ag-footer-bar">
            <button type="submit" class="laxcc-ag-btn laxcc-ag-btn-primary">
                <span class="dashicons dashicons-yes"></span> Save Changes
            </button>
        </div>

    </form>
</div>

<?php
/* ----------------------------------------------------------------
 * Helper: render a media uploader field
 * ----------------------------------------------------------------*/
function laxcc_ag_media_field($name, $value, $type = 'image') {
    $is_video = ($type === 'video');
    $preview_class = $is_video ? 'is-video' : 'is-image';
    $btn_label = $value ? 'Replace' : 'Choose ' . ($is_video ? 'Video' : 'Image');
    ?>
    <div class="laxcc-ag-media" data-media-type="<?php echo esc_attr($type); ?>">
        <div class="laxcc-ag-media-preview <?php echo esc_attr($preview_class); ?>" data-empty-text="<?php echo $is_video ? 'No video selected' : 'No image selected'; ?>">
            <?php if ($value && !$is_video) : ?>
                <img src="<?php echo esc_url($value); ?>" alt="">
            <?php elseif ($value && $is_video) : ?>
                <video src="<?php echo esc_url($value); ?>" muted></video>
            <?php else : ?>
                <span class="laxcc-ag-media-empty">
                    <span class="dashicons dashicons-<?php echo $is_video ? 'video-alt3' : 'format-image'; ?>"></span>
                    <em><?php echo $is_video ? 'No video selected' : 'No image selected'; ?></em>
                </span>
            <?php endif; ?>
        </div>
        <div class="laxcc-ag-media-actions">
            <button type="button" class="button laxcc-ag-media-pick"><?php echo esc_html($btn_label); ?></button>
            <button type="button" class="button-link-delete laxcc-ag-media-clear" <?php echo $value ? '' : 'hidden'; ?>>Remove</button>
        </div>
        <input type="hidden" name="laxcc_ag[<?php echo esc_attr($name); ?>]" class="laxcc-ag-media-url" value="<?php echo esc_attr($value); ?>">
    </div>
    <?php
}
