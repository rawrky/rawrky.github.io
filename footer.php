</div><!-- close inner container -->
</main>

<?php
$ctx             = function_exists('cg_get_user_nav_context') ? cg_get_user_nav_context() : [];
$rk_is_logged_in = !empty($ctx['is_logged_in']) || is_user_logged_in();
$rk_current_uid  = get_current_user_id();
$rk_current_user = wp_get_current_user();

// Strictly resolve the user-page CPT owned by the current logged-in user ID
$rk_user_page_id = 0;
if ($rk_is_logged_in && $rk_current_uid > 0) {
    $ctx_page_id = !empty($ctx['user_page_id']) ? (int) $ctx['user_page_id'] : 0;

    if ($ctx_page_id && (int) get_post_field('post_author', $ctx_page_id) === $rk_current_uid) {
        $rk_user_page_id = $ctx_page_id;
    } else {
        $up_by_slug = get_posts([
            'post_type'      => 'user-page',
            'post_status'    => 'publish',
            'author'         => $rk_current_uid,
            'name'           => $rk_current_user->user_nicename,
            'fields'         => 'ids',
            'posts_per_page' => 1,
        ]);

        if (!empty($up_by_slug)) {
            $rk_user_page_id = (int) $up_by_slug[0];
        } else {
            $up_q = get_posts([
                'post_type'      => 'user-page',
                'post_status'    => 'publish',
                'author'         => $rk_current_uid,
                'fields'         => 'ids',
                'posts_per_page' => 1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ]);
            $rk_user_page_id = !empty($up_q) ? (int) $up_q[0] : 0;
        }
    }
}

$rk_bottom_profile_url = $rk_user_page_id
    ? get_permalink($rk_user_page_id)
    : home_url('/edit-profile/');

$rk_bottom_name = $rk_current_user->display_name ?: ($ctx['display_name'] ?? 'Profile');

if ($rk_user_page_id && has_post_thumbnail($rk_user_page_id)) {
    $rk_bottom_avatar = get_the_post_thumbnail_url($rk_user_page_id, 'thumbnail');
} else {
    $rk_bottom_avatar = get_avatar_url($rk_current_uid, ['size' => 96]);
}

// Garage lives on Profile now, so My Cars and Edit Car keep this tab lit.
$rk_is_own_profile = ($rk_user_page_id && is_singular('user-page') && get_queried_object_id() === $rk_user_page_id)
    || is_page('edit-profile')
    || is_page('my-cars')
    || is_page('edit-car');

// Query top taxonomies for Footer SEO Sitemap Links
$footer_models = get_terms([
    'taxonomy'   => 'car-model',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 6,
]);

$footer_brands = get_terms([
    'taxonomy'   => 'car-part-brand',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 6,
]);

$footer_cats = get_terms([
    'taxonomy'   => 'car-part-category',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 6,
]);
?>

<style>
    /* SEO Multi-Column Footer + Mobile Safe-Area Padding */
    #footer.rk-footer {
        background-color: var(--rk-bg, #09090b) !important;
        border-top: 1px solid #27272a !important;
        margin-top: 24px !important;
        padding: 28px 14px calc(84px + env(safe-area-inset-bottom, 0px)) 14px !important;
    }

    html:not(.dark) #footer.rk-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #cbd5e1 !important;
    }

    @media (min-width: 768px) {
        #footer.rk-footer {
            margin-top: 40px !important;
            padding: 36px 24px 32px 24px !important;
        }
    }

    .rk-footer-container {
        max-width: 1320px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .rk-footer-seo-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    @media (min-width: 768px) {
        .rk-footer-seo-grid {
            grid-template-columns: 1.4fr repeat(3, 1fr);
            gap: 32px;
        }
    }

    .rk-footer-col-title {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #f4f4f5;
        margin-bottom: 10px;
    }

    html:not(.dark) .rk-footer-col-title {
        color: #0f172a;
    }

    .rk-footer-col-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .rk-footer-col-list a {
        font-size: 12.5px;
        font-weight: 600;
        color: #a1a1aa !important;
        text-decoration: none !important;
        transition: color 0.15s ease;
    }

    html:not(.dark) .rk-footer-col-list a {
        color: #64748b !important;
    }

    .rk-footer-col-list a:hover {
        color: var(--rk-crimson, #e11d48) !important;
    }

    .rk-footer-bottom-bar {
        padding-top: 18px;
        border-top: 1px solid #27272a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 12px;
    }

    html:not(.dark) .rk-footer-bottom-bar {
        border-top-color: #e2e8f0;
    }

    #footer.rk-footer .rk-footer-brand {
        color: var(--rk-muted, #71717a) !important;
    }

    #footer.rk-footer .rk-footer-brand strong {
        color: var(--rk-text-heading, #f4f4f5) !important;
    }

    html:not(.dark) #footer.rk-footer .rk-footer-brand strong {
        color: #0f172a !important;
    }

    #footer.rk-footer .rk-footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    #footer.rk-footer .rk-footer-links a {
        color: var(--rk-slate-mid, #a1a1aa) !important;
        text-decoration: none !important;
        font-weight: 600;
    }

    html:not(.dark) #footer.rk-footer .rk-footer-links a {
        color: #64748b !important;
    }

    #footer.rk-footer .rk-footer-links a:hover {
        color: var(--rk-crimson, #e11d48) !important;
    }
</style>

<footer id="footer" class="rk-footer">
    <div class="rk-footer-container">
        <div class="rk-footer-seo-grid">
            <div>
                <div class="rk-footer-col-title" style="font-size:15px; text-transform:none; letter-spacing:-0.02em;">Rawrkee</div>
                <p style="font-size:12.5px; color:#71717a; line-height:1.5; margin:0 0 12px 0;">
                    Community automotive build sheets, engine swap breakdowns, and chassis-specific aftermarket part indexes.
                </p>
            </div>

            <?php if (!is_wp_error($footer_models) && !empty($footer_models)) : ?>
                <nav aria-label="Popular Vehicle Chassis">
                    <div class="rk-footer-col-title">Popular Chassis</div>
                    <ul class="rk-footer-col-list">
                        <?php foreach ($footer_models as $fm) :
                            $fm_url = get_term_link($fm);
                            if (is_wp_error($fm_url)) continue;
                        ?>
                            <li><a href="<?php echo esc_url($fm_url); ?>"><?php echo esc_html($fm->name); ?> Builds &amp; Parts</a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <?php if (!is_wp_error($footer_brands) && !empty($footer_brands)) : ?>
                <nav aria-label="Top Aftermarket Brands">
                    <div class="rk-footer-col-title">Top Aftermarket Brands</div>
                    <ul class="rk-footer-col-list">
                        <?php foreach ($footer_brands as $fb) :
                            if (in_array(strtolower($fb->name), ['unidentified brand', 'unidentified', 'unknown'], true)) continue;
                            $fb_url = get_term_link($fb);
                            if (is_wp_error($fb_url)) continue;
                        ?>
                            <li><a href="<?php echo esc_url($fb_url); ?>"><?php echo esc_html($fb->name); ?> Parts</a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <?php if (!is_wp_error($footer_cats) && !empty($footer_cats)) : ?>
                <nav aria-label="Aftermarket Part Categories">
                    <div class="rk-footer-col-title">Browse by Category</div>
                    <ul class="rk-footer-col-list">
                        <?php foreach ($footer_cats as $fc) :
                            $fc_url = get_term_link($fc);
                            if (is_wp_error($fc_url)) continue;
                        ?>
                            <li><a href="<?php echo esc_url($fc_url); ?>"><?php echo esc_html($fc->name); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <div class="rk-footer-bottom-bar">
            <div class="rk-footer-brand">
                <strong>Rawrkee</strong> &copy; <?php echo date('Y'); ?>
            </div>

            <ul class="rk-footer-links">
                <li><a href="<?php echo esc_url(home_url('/car/')); ?>">All Builds</a></li>
                <li><a href="<?php echo esc_url(home_url('/car-part/')); ?>">Parts Index</a></li>
                <li><a href="<?php echo esc_url(function_exists('rk_posts_feed_url') ? rk_posts_feed_url() : home_url('/posts/')); ?>">Posts</a></li>
                <li><a href="<?php echo esc_url(home_url('/pricing/')); ?>">Pricing</a></li>
                <li><a href="<?php echo esc_url(home_url('/feedback/')); ?>">Feedback</a></li>
                <?php if (get_privacy_policy_url()) : ?>
                    <li><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Privacy</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</footer>

<!-- Mobile bottom nav. Five tabs: Garage was removed because Profile opens it. -->
<style>
    @media (max-width: 767.98px) {
        :root body nav.nav-bottom.glass-nav .nav-link {
            justify-content: flex-end;
            gap: 4px !important;
            padding: 0 2px 8px !important;
        }

        :root body nav.nav-bottom.glass-nav button.nav-link {
            font: inherit;
            cursor: pointer;
            border: 0;
            appearance: none;
            -webkit-appearance: none;
        }

        :root body nav.nav-bottom.glass-nav .rk-nav-icon {
            width: 22px;
            height: 22px;
            display: block;
            flex: 0 0 22px;
        }

        :root body nav.nav-bottom.glass-nav .nav-link.active .rk-nav-icon {
            color: var(--sh-accent, #dc2626) !important;
        }

        :root body nav.nav-bottom.glass-nav .nav-link img.nav-avatar {
            width: 22px !important;
            height: 22px !important;
        }

        :root body nav.nav-bottom.glass-nav .rk-nav-add .nav-link {
            color: var(--sh-accent, #dc2626) !important;
            font-weight: 700 !important;
            gap: 3px !important;
            padding-bottom: 7px !important;
        }

        :root body nav.nav-bottom.glass-nav .rk-nav-add-mark {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--sh-accent, #dc2626);
            color: #ffffff;
            display: grid;
            place-items: center;
            box-shadow: 0 5px 12px rgba(220, 38, 38, 0.28);
        }

        :root body nav.nav-bottom.glass-nav .rk-nav-add .nav-link.active .rk-nav-add-mark {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.18), 0 5px 12px rgba(220, 38, 38, 0.28);
        }

        :root body nav.nav-bottom.glass-nav .rk-nav-add-mark svg {
            width: 16px;
            height: 16px;
            display: block;
        }

        :root body nav.nav-bottom.glass-nav .nav-link:focus-visible {
            outline: 2px solid var(--sh-accent, #dc2626);
            outline-offset: -4px;
            border-radius: 10px;
        }
    }

    .rk-create-icon .rk-create-svg {
        width: 20px;
        height: 20px;
        display: block;
    }
</style>
<nav class="nav-bottom glass-nav d-md-none" aria-label="Mobile Bottom Navigation">
    <div class="nav-item">
        <a href="<?php echo esc_url(home_url('/')); ?>"
            class="nav-link<?php echo function_exists('cg_active') ? cg_active(is_front_page()) : ''; ?>"
            <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
            <svg class="rk-nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" fill-rule="evenodd" d="M12 3.15 2.7 10.7h2.35V20.6h13.9V10.7H21.3L12 3.15zM8.7 20.6v-7.1h6.6v7.1H8.7zm0-3.7h6.6v1.25H8.7v-1.25z"/>
            </svg>
            <span>Home</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="<?php echo esc_url(home_url('/car/')); ?>"
            class="nav-link<?php echo function_exists('cg_active') ? cg_active(is_post_type_archive('car') || is_singular('car')) : ''; ?>"
            <?php echo (is_post_type_archive('car') || is_singular('car')) ? 'aria-current="page"' : ''; ?>>
            <svg class="rk-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true">
                <path d="M2.6 15.4c.3-1 .9-1.5 2.2-1.7l2-.3c.55-1.7 1.9-2.9 3.7-3.2l3.3-.4c1.7-.15 3 .8 3.7 2.1l1.7.5c.9.3 1.5 1.1 1.5 2v1"/>
                <path d="M2.6 15.4h1.7M8.7 15.4h6.5M19.3 15.4h1.9"/>
                <circle cx="6.5" cy="15.4" r="2.05"/>
                <circle cx="17.2" cy="15.4" r="2.05"/>
            </svg>
            <span>Cars</span>
        </a>
    </div>

    <div class="nav-item rk-nav-add">
        <button type="button"
            id="rkOpenCreateModalBtn"
            class="nav-link<?php echo function_exists('cg_active') ? cg_active(is_page('add-your-car') || is_page('add-new-guide')) : ''; ?>"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-controls="rkCreateModal">
            <span class="rk-nav-add-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path fill="currentColor" d="M10.7 4.8h2.6v6.1H19.4v2.6h-6.1v6.1h-2.6v-6.1H4.6v-2.6h6.1z"/>
                </svg>
            </span>
            <span>Add</span>
        </button>
    </div>

    <div class="nav-item">
        <a href="<?php echo esc_url(home_url('/car-part/')); ?>"
            class="nav-link<?php echo function_exists('cg_active') ? cg_active(is_post_type_archive('car-part') || is_singular('car-part')) : ''; ?>"
            <?php echo (is_post_type_archive('car-part') || is_singular('car-part')) ? 'aria-current="page"' : ''; ?>>
            <svg class="rk-nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" fill-rule="evenodd" d="M2.40 12.00A9.60 9.60 0 1 0 21.60 12.00A9.60 9.60 0 1 0 2.40 12.00Z M5.45 12.00A6.55 6.55 0 1 0 18.55 12.00A6.55 6.55 0 1 0 5.45 12.00Z M11.13 15.03L10.19 18.30L13.81 18.30L12.87 15.03Z M14.61 13.76L17.43 15.66L18.55 12.23L15.15 12.11Z M14.48 10.06L17.16 7.97L14.24 5.85L13.08 9.04Z M10.92 9.04L9.76 5.85L6.84 7.97L9.52 10.06Z M8.85 12.11L5.45 12.23L6.57 15.66L9.39 13.76Z M9.45 12.00A2.55 2.55 0 1 0 14.55 12.00A2.55 2.55 0 1 0 9.45 12.00Z M10.95 12.00A1.05 1.05 0 1 0 13.05 12.00A1.05 1.05 0 1 0 10.95 12.00Z"/>
            </svg>
            <span>Parts</span>
        </a>
    </div>

    <div class="nav-item">
        <?php if ($rk_is_logged_in) : ?>
            <a href="<?php echo esc_url($rk_bottom_profile_url); ?>"
                class="nav-link<?php echo function_exists('cg_active') ? cg_active($rk_is_own_profile) : ($rk_is_own_profile ? ' active' : ''); ?>"
                <?php echo $rk_is_own_profile ? 'aria-current="page"' : ''; ?>>
                <img src="<?php echo esc_url($rk_bottom_avatar); ?>"
                    alt="<?php echo esc_attr($rk_bottom_name); ?>'s avatar"
                    class="nav-avatar" width="22" height="22" loading="lazy">
                <span>Profile</span>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/login')); ?>" class="nav-link" data-rk-mode="login">
                <svg class="rk-nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" fill-rule="evenodd" d="M2.50 12.00A9.50 9.50 0 1 0 21.50 12.00A9.50 9.50 0 1 0 2.50 12.00Z M4.85 12.00A7.15 7.15 0 1 0 19.15 12.00A7.15 7.15 0 1 0 4.85 12.00Z M12.44 9.74L13.32 5.23L10.68 5.23L11.56 9.74Z M9.83 12.75L5.48 14.25L6.79 16.53L10.26 13.51Z M13.74 13.51L17.21 16.53L18.52 14.25L14.17 12.75Z M9.85 12.00A2.15 2.15 0 1 0 14.15 12.00A2.15 2.15 0 1 0 9.85 12.00Z"/>
                </svg>
                <span>Log In</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Create / Add Chooser Modal (Uses non-heading tags so SEO heading hierarchy stays clean) -->
<style>
    .rk-create-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(9, 9, 11, 0.72);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 100000;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 12px 10px calc(74px + env(safe-area-inset-bottom, 0px)) 10px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    @media (min-width: 768px) {
        .rk-create-backdrop {
            align-items: center;
            padding: 16px;
        }
    }

    .rk-create-backdrop.rk-create-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .rk-create-modal {
        width: 100%;
        max-width: 440px;
        background-color: var(--rk-surface-solid, #18181b);
        border: 1px solid var(--rk-border, #27272a);
        border-radius: 20px;
        padding: 10px 14px 14px 14px;
        color: var(--rk-text-heading, #ffffff);
        box-shadow: 0 -10px 35px rgba(0, 0, 0, 0.35);
        font-family: var(--rk-font);
        transform: translateY(110%) scale(0.96);
        transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        will-change: transform;
    }

    .rk-create-backdrop.rk-create-open .rk-create-modal {
        transform: translateY(0) scale(1);
    }

    .rk-sheet-handle {
        width: 34px;
        height: 4px;
        background-color: var(--rk-border-strong, #3f3f46);
        border-radius: 999px;
        margin: 0 auto 10px auto;
    }

    .rk-create-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .rk-create-title {
        font-size: 15px !important;
        font-weight: 800 !important;
        color: var(--rk-text-heading, #ffffff) !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .rk-create-close {
        background: var(--rk-surface-elevated, #27272a);
        border: 1px solid var(--rk-border, transparent);
        color: var(--rk-slate-mid, #a1a1aa);
        width: 26px;
        height: 26px;
        border-radius: 50%;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .rk-create-close:hover {
        color: var(--rk-text-heading, #ffffff);
        background: var(--rk-surface-hover, #3f3f46);
    }

    .rk-create-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rk-create-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        background-color: var(--rk-surface-subtle, #09090b);
        border: 1px solid var(--rk-border, #27272a);
        border-radius: 12px;
        text-decoration: none !important;
        color: var(--rk-text-heading, #ffffff) !important;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.22s ease, transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.15s ease, background-color 0.15s ease;
    }

    .rk-create-backdrop.rk-create-open .rk-create-card {
        opacity: 1;
        transform: translateY(0);
    }

    .rk-create-backdrop.rk-create-open .rk-create-card:nth-child(1) {
        transition-delay: 0.04s;
    }

    .rk-create-backdrop.rk-create-open .rk-create-card:nth-child(2) {
        transition-delay: 0.08s;
    }

    .rk-create-card:hover,
    .rk-create-card:focus {
        background-color: var(--rk-surface-elevated, #27272a);
        border-color: rgb(var(--rk-accent-500, 239 68 68));
        color: var(--rk-text-heading, #ffffff) !important;
    }

    .rk-create-card:active {
        transform: scale(0.98) !important;
    }

    .rk-create-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background-color: rgba(239, 68, 68, 0.12);
        color: var(--rk-crimson, #e11d48);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    html.dark .rk-create-icon {
        color: rgb(var(--rk-accent-400, 248 113 113));
    }

    .rk-create-text {
        display: flex;
        flex-direction: column;
        gap: 1px;
        text-align: left;
        flex: 1 1 auto;
    }

    .rk-create-text strong {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--rk-text-heading, #ffffff);
        line-height: 1.2;
    }

    .rk-create-text span {
        font-size: 11.5px;
        color: var(--rk-slate-mid, #a1a1aa);
        line-height: 1.3;
    }

    .rk-create-arrow {
        color: var(--rk-muted, #71717a);
        font-size: 17px;
        font-weight: 700;
    }
</style>

<div class="rk-create-backdrop" id="rkCreateModal" aria-hidden="true" data-nosnippet>
    <div class="rk-create-modal" role="dialog" aria-modal="true" aria-labelledby="rkCreateTitle">
        <div class="rk-sheet-handle"></div>
        <div class="rk-create-header">
            <div class="rk-create-title" id="rkCreateTitle">What would you like to add?</div>
            <button type="button" class="rk-create-close" id="rkCreateClose" aria-label="Close">&times;</button>
        </div>

        <div class="rk-create-options">
            <a href="<?php echo esc_url(home_url('/add-your-car/')); ?>"
                class="rk-create-card"
                <?php if (!$rk_is_logged_in) : ?>
                data-rk-login data-redirect="<?php echo esc_url(home_url('/add-your-car/')); ?>"
                <?php endif; ?>>
                <span class="rk-create-icon">
                    <svg class="rk-create-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true">
                        <path d="M2.6 15.4c.3-1 .9-1.5 2.2-1.7l2-.3c.55-1.7 1.9-2.9 3.7-3.2l3.3-.4c1.7-.15 3 .8 3.7 2.1l1.7.5c.9.3 1.5 1.1 1.5 2v1"/>
                        <path d="M2.6 15.4h1.7M8.7 15.4h6.5M19.3 15.4h1.9"/>
                        <circle cx="6.5" cy="15.4" r="2.05"/>
                        <circle cx="17.2" cy="15.4" r="2.05"/>
                    </svg>
                </span>
                <span class="rk-create-text">
                    <strong>Add a Car</strong>
                    <span>Five photos, then we stage the walkaround</span>
                </span>
                <span class="rk-create-arrow">&rsaquo;</span>
            </a>

            <a href="<?php echo esc_url(home_url('/add-new-guide/')); ?>"
                class="rk-create-card"
                <?php if (!$rk_is_logged_in) : ?>
                data-rk-login data-redirect="<?php echo esc_url(home_url('/add-new-guide/')); ?>"
                <?php endif; ?>>
                <span class="rk-create-icon">
                    <svg class="rk-create-svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                </span>
                <span class="rk-create-text">
                    <strong>Create a Post</strong>
                    <span>Share a DIY guide, build log, or story</span>
                </span>
                <span class="rk-create-arrow">&rsaquo;</span>
            </a>
        </div>
    </div>
</div>

<?php if (!$rk_is_logged_in && !is_page('login')) :
    $rk_auth_error = $GLOBALS['rk_auth_error'] ?? '';
    $rk_auth_mode  = $GLOBALS['rk_auth_mode'] ?? 'login';
    $rk_auth_old   = $GLOBALS['rk_auth_old'] ?? [];
    $rk_here_url   = home_url($_SERVER['REQUEST_URI']);
    $rk_modal_back = (!empty($_POST['redirect_to']) && function_exists('rk_auth_clean_redirect'))
        ? rk_auth_clean_redirect(wp_unslash($_POST['redirect_to']))
        : '';
    if ($rk_modal_back === '') {
        $rk_modal_back = $rk_here_url;
    }
    $rk_google_on  = function_exists('rk_google_configured') && rk_google_configured();
    $rk_google_url = $rk_google_on ? rk_google_start_url($rk_modal_back) : '';
?>
    <style>
        .rk-auth-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(9, 9, 11, 0.76);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            overflow-y: auto;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .rk-auth-backdrop.rk-modal-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .rk-login-modal {
            width: 100%;
            max-width: 400px;
            background-color: var(--rk-surface-solid, #18181b);
            border: 1px solid var(--rk-border, #27272a);
            border-radius: 20px;
            padding: 20px 18px;
            color: var(--rk-slate, #f4f4f5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            position: relative;
            font-family: var(--rk-font);
            transform: translateY(12px) scale(0.98);
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @media (min-width: 640px) {
            .rk-login-modal {
                padding: 24px 22px;
            }
        }

        .rk-auth-backdrop.rk-modal-open .rk-login-modal {
            transform: translateY(0) scale(1);
        }

        .rk-modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: var(--rk-surface-elevated, #27272a);
            border: 1px solid var(--rk-border, transparent);
            color: var(--rk-slate-mid, #a1a1aa);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .rk-modal-close:hover {
            color: var(--rk-text-heading, #ffffff);
            background-color: var(--rk-surface-hover, #3f3f46);
        }

        .rk-modal-brand-wrap {
            text-align: center;
            margin-bottom: 16px;
        }

        .rk-modal-logo-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgb(var(--rk-accent-500, 239 68 68)), rgb(var(--rk-accent-600, 220 38 38)));
            color: #ffffff;
            font-weight: 900;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px auto;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .rk-modal-title {
            font-size: 19px !important;
            font-weight: 800 !important;
            color: var(--rk-text-heading, #ffffff) !important;
            letter-spacing: -0.02em !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
        }

        .rk-modal-desc {
            font-size: 12px !important;
            color: var(--rk-slate-mid, #a1a1aa) !important;
            margin: 4px 0 0 0 !important;
            padding: 0 !important;
        }

        .rk-modal-error {
            background-color: rgba(225, 29, 72, 0.12);
            border: 1px solid rgba(225, 29, 72, 0.35);
            color: var(--rk-crimson, #e11d48);
            font-size: 12px;
            font-weight: 600;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        html.dark .rk-modal-error {
            color: #fca5a5;
        }

        .rk-btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background-color: var(--rk-surface-elevated, #27272a);
            color: var(--rk-text-heading, #f4f4f5) !important;
            border: 1px solid var(--rk-border-strong, #3f3f46);
            font-size: 12px;
            font-weight: 600;
            padding: 9px 14px;
            border-radius: 10px;
            text-decoration: none !important;
            box-sizing: border-box;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .rk-btn-google:hover {
            background-color: var(--rk-surface-hover, #3f3f46);
        }

        .rk-divider {
            position: relative;
            text-align: center;
            margin: 14px 0;
        }

        .rk-divider::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: var(--rk-border, #27272a);
        }

        .rk-divider span {
            position: relative;
            background: var(--rk-surface-solid, #18181b);
            padding: 0 10px;
            font-size: 9.5px;
            font-weight: 700;
            color: var(--rk-muted, #71717a);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .rk-form-group {
            margin-bottom: 10px;
            text-align: left;
        }

        .rk-form-group label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--rk-slate-mid, #d4d4d8);
            margin-bottom: 4px;
        }

        .rk-form-input {
            width: 100%;
            padding: 8px 11px;
            font-size: 16px !important;
            background-color: var(--rk-surface-subtle, #09090b);
            border: 1px solid var(--rk-border-strong, #27272a);
            border-radius: 10px;
            color: var(--rk-text-heading, #ffffff);
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s ease;
        }

        @media (min-width: 768px) {
            .rk-form-input {
                font-size: 13px !important;
            }
        }

        .rk-form-input:focus {
            border-color: var(--rk-crimson, #e11d48);
        }

        .rk-handle-input-wrap {
            display: flex;
            align-items: center;
            background-color: var(--rk-surface-subtle, #09090b);
            border: 1px solid var(--rk-border-strong, #27272a);
            border-radius: 10px;
            padding: 0 11px;
            transition: border-color 0.15s ease;
        }

        .rk-handle-input-wrap:focus-within {
            border-color: var(--rk-crimson, #e11d48);
        }

        .rk-handle-prefix {
            color: var(--rk-crimson, #e11d48);
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            margin-right: 2px;
            white-space: nowrap;
            user-select: none;
        }

        html.dark .rk-handle-prefix {
            color: rgb(var(--rk-accent-400, 248 113 113));
        }

        .rk-handle-input {
            border: none !important;
            background: transparent !important;
            padding: 8px 0 !important;
            width: 100%;
            color: var(--rk-text-heading, #ffffff);
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px !important;
            outline: none !important;
        }

        @media (min-width: 768px) {
            .rk-handle-input {
                font-size: 13px !important;
            }
        }

        .rk-form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
            color: var(--rk-slate-mid, #a1a1aa);
            margin: 6px 0 12px 0;
        }

        .rk-form-row a {
            color: var(--rk-slate-mid, #a1a1aa);
            font-weight: 600;
            text-decoration: none;
        }

        .rk-form-row a:hover {
            color: var(--rk-crimson, #e11d48);
        }

        .rk-btn-submit {
            width: 100%;
            background-color: var(--rk-crimson, #dc2626);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 2px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .rk-btn-submit:hover {
            background-color: var(--rk-crimson-hover, #ef4444);
        }

        .rk-modal-footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--rk-border, #27272a);
            text-align: center;
            font-size: 12px;
            color: var(--rk-slate-mid, #a1a1aa);
        }

        .rk-modal-footer button {
            background: none;
            border: none;
            color: var(--rk-crimson, #e11d48);
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            padding: 0;
            margin-left: 4px;
        }

        html.dark .rk-modal-footer button {
            color: rgb(var(--rk-accent-400, 248 113 113));
        }

        .rk-modal-footer button:hover {
            text-decoration: underline;
        }

        .rk-hidden {
            display: none !important;
        }

        .rk-handle-hint {
            min-height: 15px;
            margin-top: 5px;
            font-size: 11px;
            font-weight: 600;
            color: var(--rk-muted, #71717a);
        }

        .rk-handle-hint[data-state="ok"] {
            color: #10b981;
        }

        .rk-handle-hint[data-state="bad"] {
            color: #f87171;
        }

        @keyframes rkModalShake {

            0%,
            100% {
                transform: translateX(0);
            }

            25%,
            75% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }
        }

        .rk-modal-error.rk-shake {
            animation: rkModalShake 0.36s ease;
        }

        .rk-pw-field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .rk-pw-field-wrap .rk-form-input {
            padding-right: 54px !important;
        }

        .rk-pw-toggle-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--rk-muted, #71717a);
            font-size: 11px;
            font-weight: 700;
            padding: 4px 6px;
            border-radius: 6px;
            cursor: pointer;
            line-height: 1;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .rk-pw-toggle-btn:hover {
            color: var(--rk-text-heading, #ffffff);
            background-color: var(--rk-surface-elevated, #27272a);
        }

        .rk-auth-legal {
            margin: 10px 0 0 0 !important;
            font-size: 10.5px;
            line-height: 1.4;
            color: var(--rk-muted, #71717a);
            text-align: center;
        }

        .rk-auth-legal a {
            color: var(--rk-slate-mid, #a1a1aa);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .rk-auth-legal a:hover {
            color: var(--rk-crimson, #e11d48);
        }
    </style>

    <div class="rk-auth-backdrop<?php echo !empty($rk_auth_error) ? ' rk-modal-open' : ''; ?>" id="rkLoginModal" aria-hidden="<?php echo !empty($rk_auth_error) ? 'false' : 'true'; ?>" data-nosnippet>
        <div class="rk-login-modal" role="dialog" aria-modal="true" aria-labelledby="rkModalTitle">
            <button type="button" class="rk-modal-close" id="rkModalClose" aria-label="Close">&times;</button>

            <div class="rk-modal-brand-wrap">
                <div class="rk-modal-logo-badge">r</div>
                <div class="rk-modal-title" id="rkModalTitle">
                    <?php echo ($rk_auth_mode === 'register') ? 'Claim Your Build Sheet' : 'Sign in to rawrkee'; ?>
                </div>
                <p class="rk-modal-desc" id="rkModalDesc">
                    <?php echo ($rk_auth_mode === 'register')
                        ? 'Reserve your @handle and start organizing your mod list.'
                        : 'Manage your cars and published modification lists.'; ?>
                </p>
            </div>

            <div class="rk-modal-error<?php echo empty($rk_auth_error) ? ' rk-hidden' : ''; ?>" id="rkModalError" role="alert">
                <?php echo esc_html($rk_auth_error); ?>
            </div>

            <?php if ($rk_google_on) : ?>
                <a href="<?php echo esc_url($rk_google_url); ?>" class="rk-btn-google" id="rkModalGoogle" rel="nofollow" data-rk-no-modal="1"
                    data-base="<?php echo esc_url(add_query_arg('rk_google', 'start', home_url('/login'))); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <div class="rk-divider"><span>Or with Email</span></div>
            <?php endif; ?>

            <!-- SIGN IN FORM -->
            <form method="post" id="rkModalLoginForm" class="<?php echo ($rk_auth_mode === 'register') ? 'rk-hidden' : ''; ?>">
                <input type="hidden" name="redirect_to" id="rkModalRedirect" value="<?php echo esc_attr($rk_modal_back); ?>">
                <?php wp_nonce_field('user_login', 'login_nonce'); ?>

                <div class="rk-form-group">
                    <label for="rkModalUsername">Username or Email</label>
                    <input type="text" name="username" id="rkModalUsername" class="rk-form-input" placeholder="driver_name or email" autocomplete="username" value="<?php echo esc_attr($rk_auth_old['username'] ?? ''); ?>" required>
                </div>

                <div class="rk-form-group">
                    <label for="rkModalPassword">Password</label>
                    <div class="rk-pw-field-wrap">
                        <input type="password" name="password" id="rkModalPassword" class="rk-form-input" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="rk-pw-toggle-btn js-rk-pw-toggle" data-target="rkModalPassword" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                </div>

                <div class="rk-form-row">
                    <label style="display:inline-flex; align-items:center; gap:6px; margin:0; cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="remember" value="1" checked> Remember me
                    </label>
                    <a href="<?php echo esc_url(home_url('/forgot-password/')); ?>" rel="nofollow">Forgot password?</a>
                </div>

                <button type="submit" class="rk-btn-submit" id="rkModalLoginBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="rkModalLoginSpinner" role="status" aria-hidden="true"></span>
                    <span>Sign In</span>
                </button>
            </form>

            <!-- REGISTER / CLAIM BUILD SHEET FORM -->
            <form method="post" id="rkModalRegisterForm" class="<?php echo ($rk_auth_mode === 'register') ? '' : 'rk-hidden'; ?>">
                <?php wp_nonce_field('user_register', 'register_nonce'); ?>
                <?php if (function_exists('rk_auth_bot_fields')) {
                    echo rk_auth_bot_fields();
                } ?>
                <input type="hidden" name="redirect_to" id="rkModalRegRedirect" value="<?php echo esc_attr($rk_modal_back); ?>">

                <div class="rk-form-group">
                    <label for="rkRegUsername">Your Desired Handle</label>
                    <div class="rk-handle-input-wrap">
                        <span class="rk-handle-prefix">rawrkee.com/@</span>
                        <input type="text" name="reg_username" id="rkRegUsername" class="rk-handle-input" placeholder="driver_name" autocomplete="username"
                            maxlength="20" autocapitalize="none" spellcheck="false" value="<?php echo esc_attr($rk_auth_old['reg_username'] ?? ''); ?>" required
                            data-rk-handle-check="rkRegHandleHint">
                    </div>
                    <div class="rk-handle-hint" id="rkRegHandleHint" aria-live="polite" data-default="4–20 characters: letters, numbers, - or _">4–20 characters: letters, numbers, - or _</div>
                </div>

                <div class="rk-form-group">
                    <label for="rkRegEmail">Email Address</label>
                    <input type="email" name="reg_email" id="rkRegEmail" class="rk-form-input" placeholder="you@domain.com" autocomplete="email" value="<?php echo esc_attr($rk_auth_old['reg_email'] ?? ''); ?>" required>
                </div>

                <div class="rk-form-group">
                    <label for="rkRegPassword">Password</label>
                    <div class="rk-pw-field-wrap">
                        <input type="password" name="reg_password" id="rkRegPassword" class="rk-form-input" placeholder="8+ characters" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="rk-pw-toggle-btn js-rk-pw-toggle" data-target="rkRegPassword" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                </div>

                <button type="submit" class="rk-btn-submit" id="rkModalRegBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="rkModalRegSpinner" role="status" aria-hidden="true"></span>
                    <span>Create Build Sheet</span>
                </button>

                <?php if (function_exists('rk_auth_legal_links_html')) {
                    echo rk_auth_legal_links_html('creating an account');
                } ?>
            </form>

            <div class="rk-modal-footer">
                <span id="rkTogglePrompt">
                    <?php echo ($rk_auth_mode === 'register') ? 'Already have a garage?' : "Don't have a build sheet yet?"; ?>
                </span>
                <button type="button" id="rkToggleModeBtn">
                    <?php echo ($rk_auth_mode === 'register') ? 'Sign In' : 'Claim Handle →'; ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Check if any page template or form handler flagged a successful save
$rk_changes_saved   = get_query_var('changes_saved', false);
$rk_toast_msg       = $rk_changes_saved ? get_query_var('changes_saved_message', 'Your profile was updated successfully!') : '';
$rk_is_edit_profile = is_page_template('page-user-edit-profile.php');
$rk_is_edit_car     = is_page_template('page-user-edit-car.php');
$rk_profile_url     = '';
$rk_car_url         = '';

if ($rk_is_edit_profile && is_user_logged_in()) {
    $rk_edit_post_id = (isset($_GET['edit_user_page']) && (function_exists('rk_is_effective_admin') ? rk_is_effective_admin() : current_user_can('administrator')))
        ? intval($_GET['edit_user_page'])
        : 0;
    if ($rk_edit_post_id) {
        $rk_profile_url = get_permalink($rk_edit_post_id);
    } elseif ($rk_user_page_id) {
        $rk_profile_url = get_permalink($rk_user_page_id);
    } else {
        $rk_profile_url = home_url('/@' . $rk_current_user->user_nicename);
    }
}

if ($rk_is_edit_car && !empty($_GET['car_id'])) {
    $rk_car_id = absint($_GET['car_id']);
    if ($rk_car_id) {
        $rk_car_url = get_permalink($rk_car_id);
    }
}
?>

<!-- Global Toast Container (data-nosnippet prevents search engines from indexing UI notifications) -->
<div class="toast-container" id="mobile-toast" data-nosnippet>
    <div id="globalSaveToast"
        class="toast rk-toast border-0"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        data-auto-show="<?php echo $rk_changes_saved ? 'true' : 'false'; ?>"
        data-profile-url="<?php echo esc_url($rk_profile_url); ?>"
        data-car-url="<?php echo esc_url($rk_car_url); ?>">
        <div class="rk-toast-inner">
            <div class="rk-toast-icon" id="rkToastIcon">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="toast-body"><?php
                                    if ($rk_changes_saved && $rk_toast_msg !== '') {
                                        echo esc_html($rk_toast_msg);
                                        if (!empty($rk_profile_url)) {
                                            echo ' <a href="' . esc_url($rk_profile_url) . '" class="rk-toast-link">View Profile &rarr;</a>';
                                        } elseif (!empty($rk_car_url)) {
                                            echo ' <a href="' . esc_url($rk_car_url) . '" class="rk-toast-link">View Car &rarr;</a>';
                                        }
                                    }
                                    ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<?php wp_footer(); ?>

<!-- Consolidated UI Scripts: Nav Dropdowns, Theme Toggle, Modals & Global Toast -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1) Header Dropdown Menus
        const dropdownPairs = [{
                wrap: document.getElementById('rkMenuWrap'),
                btn: document.getElementById('rkMenuBtn')
            },
            {
                wrap: document.getElementById('rkAddWrap'),
                btn: document.getElementById('rkAddBtn')
            }
        ].filter(pair => pair.wrap && pair.btn);

        dropdownPairs.forEach(current => {
            current.btn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownPairs.forEach(other => {
                    if (other !== current) {
                        other.wrap.classList.remove('rk-menu-open');
                        other.btn.setAttribute('aria-expanded', 'false');
                    }
                });
                const isOpen = current.wrap.classList.toggle('rk-menu-open');
                current.btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });

        document.addEventListener('click', function(e) {
            dropdownPairs.forEach(pair => {
                if (!pair.wrap.contains(e.target)) {
                    pair.wrap.classList.remove('rk-menu-open');
                    pair.btn.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // 2) Light / Dark Theme Toggle
        const themeBtns = document.querySelectorAll('.js-rk-theme-toggle, #rkThemeToggleBtn');

        function syncThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.rk-theme-icon-sun, #rkThemeIconSun').forEach(el => {
                el.classList.toggle('hidden', !isDark);
            });
            document.querySelectorAll('.rk-theme-icon-moon, #rkThemeIconMoon').forEach(el => {
                el.classList.toggle('hidden', isDark);
            });
        }
        syncThemeIcons();
        themeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('rk_theme_mode', isDark ? 'dark' : 'light');
                syncThemeIcons();
            });
        });

        // 3) Mobile Bottom Nav Scroll Shrink
        const navBottom = document.querySelector('.nav-bottom');
        if (navBottom) {
            let lastScrollY = window.scrollY;
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(() => {
                        const currentScrollY = Math.max(0, window.scrollY);
                        const scrollDelta = currentScrollY - lastScrollY;
                        if (currentScrollY < 20) {
                            navBottom.classList.remove('nav-shrunk');
                        } else if (Math.abs(scrollDelta) >= 6) {
                            navBottom.classList.toggle('nav-shrunk', scrollDelta > 0);
                        }
                        lastScrollY = currentScrollY;
                        ticking = false;
                    });
                    ticking = true;
                }
            }, {
                passive: true
            });
        }

        // 4) Create / Add Modal
        const createModal = document.getElementById('rkCreateModal');
        const createClose = document.getElementById('rkCreateClose');
        const createTriggers = document.querySelectorAll('#rkOpenCreateModalBtn, [data-rk-create-trigger]');
        if (createModal && createTriggers.length) {
            const openCreateModal = (e) => {
                if (e) e.preventDefault();
                createModal.classList.add('rk-create-open');
                createModal.setAttribute('aria-hidden', 'false');
                createTriggers.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
            };
            const closeCreateModal = () => {
                createModal.classList.remove('rk-create-open');
                createModal.setAttribute('aria-hidden', 'true');
                createTriggers.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
            };
            createTriggers.forEach(btn => btn.addEventListener('click', openCreateModal));
            if (createClose) createClose.addEventListener('click', closeCreateModal);
            createModal.addEventListener('click', e => {
                if (e.target === createModal) closeCreateModal();
            });
            createModal.querySelectorAll('[data-rk-login]').forEach(card => {
                card.addEventListener('click', closeCreateModal);
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && createModal.classList.contains('rk-create-open')) closeCreateModal();
            });
        }

        // 5) Guest Auth Modal (AJAX Login & Sign-Up + Inline Password Toggles)
        const authModal = document.getElementById('rkLoginModal');
        if (authModal) {
            const closeBtn = document.getElementById('rkModalClose');
            const titleEl = document.getElementById('rkModalTitle');
            const descEl = document.getElementById('rkModalDesc');
            const errorEl = document.getElementById('rkModalError');
            const loginForm = document.getElementById('rkModalLoginForm');
            const regForm = document.getElementById('rkModalRegisterForm');
            const toggleBtn = document.getElementById('rkToggleModeBtn');
            const togglePrompt = document.getElementById('rkTogglePrompt');
            const redirectInput = document.getElementById('rkModalRedirect');
            const regRedirectInput = document.getElementById('rkModalRegRedirect');
            const googleBtn = document.getElementById('rkModalGoogle');
            const userInput = document.getElementById('rkModalUsername');
            const regInput = document.getElementById('rkRegUsername');
            const authAjaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

            function clearModalError() {
                if (!errorEl) return;
                errorEl.textContent = '';
                errorEl.classList.add('rk-hidden');
                errorEl.classList.remove('rk-shake');
            }

            function showModalError(msg) {
                if (!errorEl) return;
                errorEl.textContent = msg;
                errorEl.classList.remove('rk-hidden', 'rk-shake');
                void errorEl.offsetWidth;
                errorEl.classList.add('rk-shake');
            }

            function setAuthMode(mode, preserveError = false) {
                if (!preserveError) clearModalError();
                const isReg = (mode === 'register');
                loginForm.classList.toggle('rk-hidden', isReg);
                regForm.classList.toggle('rk-hidden', !isReg);
                titleEl.textContent = isReg ? 'Claim Your Build Sheet' : 'Sign in to rawrkee';
                descEl.textContent = isReg ?
                    'Reserve your @handle and start organizing your mod list.' :
                    'Manage your cars and published modification lists.';
                togglePrompt.textContent = isReg ? 'Already have a garage?' : "Don't have a build sheet yet?";
                toggleBtn.textContent = isReg ? 'Sign In' : 'Claim Handle →';
            }

            authModal.querySelectorAll('.js-rk-pw-toggle').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = document.getElementById(this.getAttribute('data-target'));
                    if (!target) return;
                    const showing = target.type === 'password';
                    target.type = showing ? 'text' : 'password';
                    this.textContent = showing ? 'Hide' : 'Show';
                    this.setAttribute('aria-pressed', showing ? 'true' : 'false');
                    this.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
                });
            });

            window.openRkAuthModal = function(mode = 'register', customRedirect = '', prefilledHandle = '') {
                const normalizedMode = (mode === 'login') ? 'login' : 'register';
                const backTo = customRedirect || window.location.href;
                if (redirectInput) redirectInput.value = backTo;
                if (regRedirectInput) regRedirectInput.value = backTo;
                if (googleBtn && googleBtn.dataset.base) {
                    googleBtn.href = googleBtn.dataset.base + '&redirect_to=' + encodeURIComponent(backTo);
                }
                if (prefilledHandle && regInput) {
                    regInput.value = prefilledHandle;
                    regInput.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
                setAuthMode(normalizedMode, false);
                authModal.classList.add('rk-modal-open');
                authModal.setAttribute('aria-hidden', 'false');
                setTimeout(() => {
                    if (normalizedMode === 'register' && regInput) regInput.focus();
                    else if (userInput) userInput.focus();
                }, 50);
            };

            window.openAuthModal = function(mode = 'register', prefilledHandle = '') {
                window.openRkAuthModal(mode, window.location.href, prefilledHandle);
            };

            toggleBtn.addEventListener('click', () => {
                const isCurrentlyReg = !regForm.classList.contains('rk-hidden');
                setAuthMode(isCurrentlyReg ? 'login' : 'register', false);
            });

            document.querySelectorAll('a[href$="/login"], a[href$="/login/"], [data-rk-login]').forEach(link => {
                if (link.hasAttribute('data-rk-no-modal')) return;
                link.addEventListener('click', function(e) {
                    if (this.hasAttribute('data-rk-no-modal')) return;
                    e.preventDefault();
                    const text = this.textContent.trim().toLowerCase();
                    const requestedMode = this.getAttribute('data-rk-mode');
                    const mode = (
                        requestedMode === 'login' ||
                        text.includes('log in') ||
                        text.includes('login') ||
                        text.includes('sign in')
                    ) ? 'login' : 'register';
                    const targetUrl = this.getAttribute('data-redirect') || window.location.href;
                    window.openRkAuthModal(mode, targetUrl);
                });
            });

            async function submitModalAuth(formEl, mode, btnId, spinnerId) {
                const btn = document.getElementById(btnId);
                const spn = document.getElementById(spinnerId);
                clearModalError();
                if (btn) btn.disabled = true;
                if (spn) spn.classList.remove('d-none');

                try {
                    const fd = new FormData(formEl);
                    fd.append('action', 'rk_modal_auth');
                    fd.append('auth_mode', mode);

                    const res = await fetch(authAjaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    });
                    const json = await res.json();

                    if (json && json.success && json.data && json.data.redirect) {
                        window.location.href = json.data.redirect;
                        return;
                    }

                    const errMsg = (json && json.data && json.data.message) ?
                        json.data.message :
                        'Something went wrong. Please try again.';
                    showModalError(errMsg);
                } catch (_) {
                    showModalError('Network error. Please check your connection and try again.');
                }

                if (btn) btn.disabled = false;
                if (spn) spn.classList.add('d-none');
            }

            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                submitModalAuth(loginForm, 'login', 'rkModalLoginBtn', 'rkModalLoginSpinner');
            });

            regForm.addEventListener('submit', (e) => {
                e.preventDefault();
                submitModalAuth(regForm, 'register', 'rkModalRegBtn', 'rkModalRegSpinner');
            });

            const closeModal = () => {
                authModal.classList.remove('rk-modal-open');
                authModal.setAttribute('aria-hidden', 'true');
            };
            closeBtn.addEventListener('click', closeModal);
            authModal.addEventListener('click', e => {
                if (e.target === authModal) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && authModal.classList.contains('rk-modal-open')) closeModal();
            });
        }

        // 6) Global Toast + Like Handler
        const toastEl = document.getElementById('globalSaveToast');
        const iconWrap = document.getElementById('rkToastIcon');
        const bodyEl = toastEl ? toastEl.querySelector('.toast-body') : null;
        const profileUrl = toastEl ? toastEl.dataset.profileUrl : '';
        const carUrl = toastEl ? toastEl.dataset.carUrl : '';
        const STORAGE_KEY = 'rk_pending_toast';

        window.showToast = function(
            message = 'Your changes were saved successfully!',
            isHtml = false,
            arg3 = false,
            arg4 = false,
            skipStorage = false
        ) {
            if (!toastEl) return;

            let isError = false;
            let includeLink = false;

            if (typeof arg4 === 'string') {
                isError = ['error', 'danger'].includes(arg4.toLowerCase());
                includeLink = Boolean(arg3);
            } else {
                isError = arg3 === true || (typeof arg3 === 'string' && ['error', 'danger'].includes(arg3.toLowerCase()));
                includeLink = Boolean(arg4);
            }

            const cleanMsg = String(message).replace(/^[\s✅❌]+/, '');
            const targetCarUrl = carUrl || window.viewCarUrl || '';

            if (!skipStorage) {
                try {
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                        message: cleanMsg,
                        isHtml: Boolean(isHtml),
                        isError: isError,
                        includeLink: includeLink,
                        viewCarUrl: targetCarUrl,
                        timestamp: Date.now()
                    }));
                } catch (_) {}
            }

            toastEl.classList.remove('bg-success', 'bg-danger', 'is-error', 'is-success');
            toastEl.classList.add(isError ? 'bg-danger' : 'bg-success');
            toastEl.classList.add(isError ? 'is-error' : 'is-success');

            if (iconWrap) {
                iconWrap.innerHTML = isError ?
                    '<i class="fa-solid fa-triangle-exclamation"></i>' :
                    '<i class="fa-solid fa-check"></i>';
            }

            if (bodyEl) {
                if (isHtml) {
                    bodyEl.innerHTML = cleanMsg;
                } else {
                    bodyEl.textContent = cleanMsg;
                }

                if (!isError && !bodyEl.querySelector('.rk-toast-link')) {
                    if (profileUrl) {
                        bodyEl.innerHTML += ` <a href="${profileUrl}" class="rk-toast-link">View Profile &rarr;</a>`;
                    } else if (targetCarUrl) {
                        bodyEl.innerHTML += ` <a href="${targetCarUrl}" class="rk-toast-link">View Car &rarr;</a>`;
                    }
                }
            }

            if (window.bootstrap && bootstrap.Toast) {
                const instance = bootstrap.Toast.getOrCreateInstance(toastEl, {
                    autohide: true,
                    delay: 5000,
                });
                if (!toastEl.classList.contains('show') && !toastEl.classList.contains('showing')) {
                    instance.show();
                }
            }
        };

        if (toastEl) {
            toastEl.addEventListener('hidden.bs.toast', () => {
                try {
                    sessionStorage.removeItem(STORAGE_KEY);
                } catch (_) {}
            });
        }

        try {
            const rawPending = sessionStorage.getItem(STORAGE_KEY);
            if (rawPending) {
                sessionStorage.removeItem(STORAGE_KEY);
                const pending = JSON.parse(rawPending);
                if (pending && (Date.now() - pending.timestamp) < 4000) {
                    if (pending.viewCarUrl && !window.viewCarUrl) {
                        window.viewCarUrl = pending.viewCarUrl;
                    }
                    window.showToast(pending.message, pending.isHtml, pending.isError, pending.includeLink, true);
                }
            }
        } catch (_) {}

        if (toastEl && toastEl.dataset.autoShow === 'true') {
            window.showToast('Your profile was updated successfully!', false, false, false, true);
        }

        try {
            const curUrl = new URL(window.location.href);
            if (curUrl.searchParams.get('password_reset') === '1') {
                window.showToast('Your password has been updated! Welcome back.', false, false, false, true);
                curUrl.searchParams.delete('password_reset');
                window.history.replaceState({}, document.title, curUrl.pathname + curUrl.search + curUrl.hash);
            }
        } catch (_) {}

        const currentUserId = <?php echo (int) get_current_user_id(); ?>;
        const postNonce = '<?php echo wp_create_nonce('wp_rest'); ?>';

        document.querySelectorAll('.like-post-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!currentUserId) {
                    if (typeof window.openRkAuthModal === 'function') {
                        window.openRkAuthModal('register', window.location.href);
                    } else {
                        window.location.href = `/login/?redirect_to=${encodeURIComponent(location.href)}`;
                    }
                    return;
                }
                if (btn.dataset.liking === '1') return;
                const wrapper = btn.closest('[data-post-id]');
                if (!wrapper) return;
                const postId = wrapper.dataset.postId;
                btn.dataset.liking = '1';

                try {
                    const res = await fetch(`${window.location.origin}/wp-json/custom/v1/posts/${postId}/like`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-WP-Nonce': postNonce
                        },
                    });
                    if (!res.ok) throw res;
                    const data = await res.json();
                    btn.innerHTML = `<i class="bi bi-heart${data.liked ? '-fill' : ''}"></i><span>${data.like_count > 0 ? data.like_count : ''}</span>`;
                } catch (err) {
                    window.showToast('Couldn’t update like.', false, true);
                } finally {
                    delete btn.dataset.liking;
                }
            });
        });
    });
</script>

<script>
    // Live "@handle available?" hints for any input with data-rk-handle-check="<hint id>"
    (function() {
        const inputs = document.querySelectorAll('input[data-rk-handle-check]');
        if (!inputs.length) return;
        const api = <?php echo wp_json_encode(rest_url('rawrkee/v1/username-check')); ?>;

        inputs.forEach(input => {
            const hint = document.getElementById(input.getAttribute('data-rk-handle-check'));
            const current = (input.getAttribute('data-current') || '').toLowerCase();
            const nonce = input.getAttribute('data-rest-nonce') || '';
            let timer = 0;
            let seq = 0;

            const show = (state, msg) => {
                if (!hint) return;
                hint.textContent = msg;
                if (state) hint.setAttribute('data-state', state);
                else hint.removeAttribute('data-state');
            };
            const reset = () => show('', hint ? (hint.getAttribute('data-default') || '') : '');

            const run = () => {
                const value = input.value.trim().replace(/^@+/, '');
                if (!value || (current && value.toLowerCase() === current)) {
                    reset();
                    return;
                }
                const mine = ++seq;
                show('', 'Checking…');
                fetch(api + (api.indexOf('?') === -1 ? '?' : '&') + 'u=' + encodeURIComponent(value), {
                        credentials: 'same-origin',
                        headers: nonce ? {
                            'X-WP-Nonce': nonce
                        } : {}
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (mine !== seq) return;
                        show(d && d.ok ? 'ok' : 'bad', (d && d.message) || '');
                    })
                    .catch(() => {
                        if (mine === seq) reset();
                    });
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(run, 350);
            });
            if (input.getAttribute('data-check-now') && input.value.trim()) run();
        });
    })();
</script>

<!-- Histats.com  START  (aync)-->
<script type="text/javascript">
    var _Hasync = _Hasync || [];
    _Hasync.push(['Histats.start', '1,5063732,4,0,0,0,00010000']);
    _Hasync.push(['Histats.fasi', '1']);
    _Hasync.push(['Histats.track_hits', '']);
    (function() {
        var hs = document.createElement('script');
        hs.type = 'text/javascript';
        hs.async = true;
        hs.src = ('//s10.histats.com/js15_as.js');
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(hs);
    })();
</script>
<noscript><a href="/" target="_blank"><img src="//sstatic1.histats.com/0.gif?5063732&101" alt="web log free" border="0"></a></noscript>
<!-- Histats.com  END  -->

</body>

</html>