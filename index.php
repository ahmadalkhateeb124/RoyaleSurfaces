<?php
/**
 * Front controller.
 *
 * .htaccess rewrites every non-file request to index.php?url=<slug>&puid=<param>
 * Routes are matched case-insensitively so /Slabs, /slabs and /SLABS all resolve
 * to the same page (important: production Linux filesystems are case-sensitive).
 */

ob_start();

require_once __DIR__ . '/inc/conn.php';

// Show errors locally, log them silently in production.
if ($_isLocal) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

$rawUrl = isset($_GET['url']) ? trim((string) $_GET['url'], '/') : '';
$SP     = isset($_GET['puid']) ? (string) $_GET['puid'] : '';

// Normalise: lowercase, strip anything that isn't a safe slug character.
$route = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $rawUrl));
if ($route === '' || $route === 'index') {
    $route = 'home';
}

/**
 * Route slug → page file. Keeping this explicit means an attacker can never
 * reach a file we didn't intend to expose, and it lets us keep the existing
 * mixed-case filenames on disk.
 */
$routes = [
    'home'           => 'Home.php',
    'about'          => 'about.php',
    'slabs'          => 'Slabs.php',
    'products'       => 'Slabs.php',   // legacy slug
    'services'       => 'services.php',
    'gallery'        => 'gallery.php',
    'blog'           => 'blog.php',
    'contact'        => 'contact.php',
    'privacy-policy' => 'privacy-policy.php',
    'terms'          => 'terms.php',
];

// /blog/<slug> → single post. Resolve it here so the header can emit the
// post's own title, description and social image instead of the listing's.
if ($route === 'blog' && $SP !== '') {
    require_once __DIR__ . '/inc/posts.php';
    $postSlug = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $SP));
    // blog_posts() is DB-backed — reading BLOG_POSTS here would 404 every
    // article created through the admin portal.
    $post = blog_posts()[$postSlug] ?? null;

    if ($post) {
        // Per-post SEO overrides from the portal win; otherwise derive from content.
        $PageTitle          = !empty($post['meta_title'])
            ? $post['meta_title']
            : $post['title'] . ' | ' . site_name();
        $escapedDescription = !empty($post['meta_description'])
            ? $post['meta_description']
            : $post['excerpt'];
        $metaKeywords       = $post['meta_keywords'] ?? '';
        $forceNoindex       = !empty($post['noindex']);
        $ogImage            = slab_image($post['image']);
        $canonicalOverride  = $base_url . 'blog/' . $postSlug;

        include_once __DIR__ . '/parts/header.php';
        include_once __DIR__ . '/pages/blog-details.php';
        include_once __DIR__ . '/parts/footer.php';
        exit;
    }

    // Unknown slug — fall through to the 404 below.
    $route = 'unknown';
}

require_once __DIR__ . '/inc/content-pages.php';

// ── Trade account area ───────────────────────────────────────────────────────
if ($route === 'trade') {
    $tradePage = strtolower(preg_replace('/[^a-z-]/', '', $SP));

    require_once __DIR__ . '/inc/trade.php';
    $isLanding = $tradePage === '' && !trade_check();

    if ($isLanding) {
        // The public explainer is worth ranking for "trade account stone supplier".
        $PageTitle = 'Open a Trade Account — Wholesale Stone ' . site_city() . ' | ' . site_name();
        $escapedDescription = 'Trade accounts for Texas fabricators, builders and designers. Build a request list '
            . 'straight from our ' . site_city() . ' inventory and send it in one go. Approval in one business day.';
        $canonicalOverride = $base_url . 'trade';
    } else {
        $PageTitle = 'Trade Account | ' . site_name();
        $escapedDescription = 'Sign in to your account to browse inventory and send reservation requests.';
        $forceNoindex = true;   // signed-in and form pages stay out of search
    }

    include_once __DIR__ . '/parts/header.php';
    include_once __DIR__ . '/pages/trade.php';
    include_once __DIR__ . '/parts/footer.php';
    exit;
}

// ── /slabs/<slug> — one page per slab ────────────────────────────────────────
if ($route === 'slabs' && $SP !== '') {
    require_once __DIR__ . '/inc/slabs.php';
    $slabSlug = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $SP));

    $slab = null;
    foreach (slabs_all() as $s) {
        if (($s['slug'] ?? '') === $slabSlug) {
            $slab = $s;
            break;
        }
    }

    if ($slab) {
        $label = SITE_MATERIALS[$slab['type']]['label'] ?? $slab['type'];
        $PageTitle = $slab['name'] . ' — ' . $label . ' Slabs ' . site_city() . ' | ' . site_name();
        $escapedDescription = ($slab['description'] ?? '') !== ''
            ? $slab['description']
            : $slab['name'] . ' ' . strtolower($label) . ' slabs'
                . (!empty($slab['origin']) ? ' from ' . $slab['origin'] : '')
                . '. ' . ($slab['finish'] ?? '') . ' finish, ' . ($slab['thickness'] ?? '')
                . '. In stock at our ' . site_city() . ', ' . site_state() . ' gallery.';
        $ogImage = slab_image($slab['image']);
        $canonicalOverride = $base_url . 'slabs/' . $slabSlug;

        include_once __DIR__ . '/parts/header.php';
        include_once __DIR__ . '/pages/slab-detail.php';
        include_once __DIR__ . '/parts/footer.php';
        exit;
    }
    $route = 'unknown';
}

// ── Material category pages: /granite, /quartzite … ──────────────────────────
if (isset(SITE_MATERIALS[$route])) {
    $m = SITE_MATERIALS[$route];
    $_GET['type'] = $route;                       // Slabs.php filters on this

    $PageTitle = $m['label'] . ' Slabs ' . site_city() . ', TX — Wholesale | ' . site_name();
    // Trimmed boilerplate ("Browse " / "material and") — the full phrasing pushed every
    // material past Google's ~160-char cutoff once the blurb was written to actually
    // carry a keyword phrase instead of a throwaway tagline.
    $escapedDescription = $m['label'] . ' slabs in stock at our ' . site_city() . ' gallery. '
        . $m['blurb'] . ' Full slabs, lot-matched, wholesale pricing.';
    $ogImage = asset('assets/images/' . $m['image']);
    $canonicalOverride = $base_url . $route;

    include_once __DIR__ . '/parts/header.php';
    include_once __DIR__ . '/pages/Slabs.php';
    include_once __DIR__ . '/parts/footer.php';
    exit;
}

// ── Application pages: /countertops, /outdoor-kitchens … ─────────────────────
if (isset(APPLICATION_PAGES[$route])) {
    $appSlug = $route;
    $app = APPLICATION_PAGES[$route];

    $PageTitle = $app['title'] . ' — ' . site_city() . ', TX | ' . site_name();
    $escapedDescription = $app['meta'];
    $ogImage = asset('assets/images/' . $app['image']);
    $canonicalOverride = $base_url . $route;

    include_once __DIR__ . '/parts/header.php';
    include_once __DIR__ . '/pages/application.php';
    include_once __DIR__ . '/parts/footer.php';
    exit;
}

// ── Reference pages: /glossary, /care, /compare … ────────────────────────────
if (isset(REFERENCE_PAGES[$route])) {
    $refSlug = $route;
    $ref = REFERENCE_PAGES[$route];

    $PageTitle = strip_tags(html_entity_decode($ref['title'])) . ' | ' . site_name();
    $escapedDescription = $ref['meta'];
    $canonicalOverride = $base_url . $route;

    include_once __DIR__ . '/parts/header.php';
    include_once __DIR__ . '/pages/reference.php';
    include_once __DIR__ . '/parts/footer.php';
    exit;
}

// Blog archive: give every page/category its own title and canonical, or all
// 23 paginated pages compete for the same listing keyword.
if ($route === 'blog') {
    require_once __DIR__ . '/inc/posts.php';

    $_cat = (string) ($_GET['cat'] ?? '');
    $_cat = isset(BLOG_CATEGORIES[$_cat]) ? $_cat : '';
    $_pg  = max(1, (int) ($_GET['page'] ?? 1));
    $_res = blog_page($_pg, $_cat);
    $_pg  = $_res['page'];   // clamped

    if ($_cat !== '' || $_pg > 1) {
        $_name = $_cat !== '' ? BLOG_CATEGORIES[$_cat] : 'Insights & Updates';
        $PageTitle = $_name
            . ($_pg > 1 ? ' — Page ' . $_pg . ' of ' . $_res['pages'] : '')
            . ' | ' . site_name();
        $escapedDescription = $_cat !== ''
            ? $_name . ' articles for stone fabricators and contractors from Royale Surfaces, Dallas TX.'
            : SITE_PAGES['blog']['description'];
        $canonicalOverride = blog_url($base_url, $_pg, $_cat);
    }
}

if (isset($routes[$route])) {
    $pageFile = __DIR__ . '/pages/' . $routes[$route];
} else {
    $route = '404';
    $pageFile = __DIR__ . '/pages/404.php';
    http_response_code(404);
}

// `$route` is read by parts/header.php to pick SEO meta and the active nav item.
include_once __DIR__ . '/parts/header.php';
include_once $pageFile;
include_once __DIR__ . '/parts/footer.php';
