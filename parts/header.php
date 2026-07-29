<?php
/**
 * Site head + navigation.
 *
 * Expects `$route` (lowercase slug) from index.php. Pulls per-page title,
 * description and social image from SITE_PAGES in inc/site.php so every page
 * ships unique metadata instead of the homepage's.
 */
require_once __DIR__ . '/../inc/conn.php';   // provides $base_url + site.php constants

$route = $route ?? 'home';

require_once __DIR__ . '/../inc/content-pages.php';
require_once __DIR__ . '/../inc/trade.php';   // trade_check() for the account link

/**
 * Dropdown menus keyed by the nav item they hang off. Each is a title plus a
 * list of [label, href, blurb] rows, so one block of markup renders all three.
 */
$dropdowns = [
    'slabs' => [
        'title' => 'Shop by Category',
        'items' => array_map(fn($k, $m) => [$m['label'], $k, $m['blurb']],
            array_keys(SITE_MATERIALS), array_values(SITE_MATERIALS)),
        'all'   => ['View All Inventory', 'slabs'],
    ],
    'applications' => [
        'title' => 'Shop by Project',
        'items' => array_map(fn($k, $a) => [$a['title'], $k, $a['lead']],
            array_keys(APPLICATION_PAGES), array_values(APPLICATION_PAGES)),
        'all'   => ['Project Gallery', 'gallery'],
    ],
    'resources' => [
        'title' => 'Guides & Reference',
        'items' => array_map(fn($k, $r) => [$r['title'], $k, $r['meta']],
            array_keys(REFERENCE_PAGES), array_values(REFERENCE_PAGES)),
        'all'   => ['Read the Blog', 'blog'],
    ],
];

// Which top-level item should look active for the current route.
$navOpen = '';
if (isset(SITE_MATERIALS[$route]) || $route === 'slabs') $navOpen = 'slabs';
elseif (isset(APPLICATION_PAGES[$route])) $navOpen = 'applications';
elseif (isset(REFERENCE_PAGES[$route])) $navOpen = 'resources';
$meta  = SITE_PAGES[$route] ?? SITE_PAGES['home'];

$pageTitle = $PageTitle ?? $meta['title'];
$pageDesc  = $escapedDescription ?? $meta['description'];
$canonical = $canonicalOverride ?? ($base_url . ($route === 'home' ? '' : $route));
$socialImg = $ogImage ?? (asset('assets/images/' . ($meta['image'] ?? 'hero-stone.jpg')));
// A post flagged noindex in the portal overrides the page default.
$noindex   = !empty($meta['noindex']) || !empty($forceNoindex);
$keywords  = $metaKeywords ?? '';

// Opening hours in schema.org format, skipping closed days.
$schemaHours = array_values(array_filter(array_column(site_hours(), 'schema')));

/**
 * Breadcrumb trail, derived from the route so it always matches the visible
 * <ol class="breadcrumb"> on the page. Blog posts get an extra Blog level.
 */
$breadcrumbTrail = [];
if ($route !== 'home' && $route !== '404') {
    $breadcrumbTrail[] = ['name' => 'Home', 'url' => $base_url];

    if (isset($postSlug, $post)) {
        $breadcrumbTrail[] = ['name' => 'Blog', 'url' => $base_url . 'blog'];
        $breadcrumbTrail[] = ['name' => $post['title']];
    } else {
        $labels = SITE_NAV + ['contact' => 'Contact', 'privacy-policy' => 'Privacy Policy', 'terms' => 'Terms of Service'];
        $breadcrumbTrail[] = ['name' => $labels[$route] ?? ucfirst($route)];
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#0B0B0B" />

    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>" />
    <?php if ($keywords !== ''): ?>
        <meta name="keywords" content="<?= e($keywords) ?>" />
    <?php endif; ?>
    <?php if ($noindex): ?>
        <meta name="robots" content="noindex, follow" />
    <?php else: ?>
        <meta name="robots" content="index, follow, max-image-preview:large" />
        <link rel="canonical" href="<?= e($canonical) ?>" />
    <?php endif; ?>

    <?php if (gsc_token() !== ''): ?>
        <meta name="google-site-verification" content="<?= e(gsc_token()) ?>" />
    <?php endif; ?>

    <!-- Local business / geo signals -->
    <meta name="geo.region" content="US-<?= e(site_state()) ?>" />
    <meta name="geo.placename" content="<?= e(site_city()) ?>" />
    <meta name="geo.position" content="<?= e(SITE_LAT) ?>;<?= e(SITE_LNG) ?>" />

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="<?= e(site_name()) ?>" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:title" content="<?= e($pageTitle) ?>" />
    <meta property="og:description" content="<?= e($pageDesc) ?>" />
    <meta property="og:url" content="<?= e($canonical) ?>" />
    <meta property="og:image" content="<?= e($socialImg) ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($pageTitle) ?>" />
    <meta name="twitter:description" content="<?= e($pageDesc) ?>" />
    <meta name="twitter:image" content="<?= e($socialImg) ?>" />

    <link rel="icon" href="<?= e(favicon_url()) ?>" type="<?= e(favicon_type()) ?>" />
    <link rel="apple-touch-icon" href="<?= e(favicon_url()) ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="<?= e(asset('assets/css/styles.css')) ?>" />

    <?php if (analytics_active()): ?>
        <!-- Google Analytics 4. The ID travels in a meta tag and assets/js/main.js
             does the dataLayer bootstrap, so no inline script is needed and the
             CSP can stay free of unsafe-inline. -->
        <meta name="ga-measurement-id" content="<?= e(analytics_id()) ?>" />
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(analytics_id()) ?>"></script>
    <?php endif; ?>

    <script type="application/ld+json">
    <?= json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'HomeAndConstructionBusiness',
        '@id'         => $base_url . '#business',
        'name'        => site_name(),
        'description' => SITE_PAGES['home']['description'],
        'url'         => $base_url,
        'telephone'   => site_phone(),
        'email'       => site_email(),
        'image'       => asset('assets/images/about-warehouse.jpg'),
        'logo'        => has_logo() ? logo_url() : $base_url . 'assets/favicon.svg',
        'priceRange'  => '$$',
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => site_street(),
            'addressLocality' => site_city(),
            'addressRegion'   => site_state(),
            'postalCode'      => site_zip(),
            'addressCountry'  => 'US',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => SITE_LAT,
            'longitude' => SITE_LNG,
        ],
        'openingHours' => $schemaHours,
        'areaServed'   => ['@type' => 'State', 'name' => 'Texas'],
        'sameAs'       => array_values(array_column(social_links(), 'url')),
        'makesOffer'   => array_map(fn($m) => [
            '@type'       => 'Offer',
            'itemOffered' => ['@type' => 'Product', 'name' => $m['label'] . ' Slabs'],
        ], array_values(SITE_MATERIALS)),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <?php if ($route === 'home'): ?>
        <!-- FAQPage — mirrors the FAQ section rendered on the page itself -->
        <script type="application/ld+json">
        <?php require_once __DIR__ . '/../inc/faqs.php'; ?>
        <?= json_encode(faqs_schema(faqs_home()), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endif; ?>

    <?php if (!empty($breadcrumbTrail)): ?>
        <script type="application/ld+json">
        <?= json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array_map(fn($i, $c) => array_filter([
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $c['name'],
                'item'     => $c['url'] ?? null,
            ]), array_keys($breadcrumbTrail), $breadcrumbTrail),
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endif; ?>
</head>

<body data-route="<?= e($route) ?>">

    <a href="#main" class="skip-link">Skip to content</a>
    <!-- TOP BAR — location, hours, phone. Collapses to nothing once the visitor
         scrolls, so the fixed navbar below stays a single slim strip rather
         than permanently eating two rows of viewport height. -->
    <div class="top-bar" id="topBar">
        <div class="container top-bar-row">
            <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer" class="top-bar-item">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />
                </svg>
                <?= e(site_city()) ?>, <?= e(site_state()) ?>
            </a>
            <span class="top-bar-item is-static">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                </svg>
                <?= e(site_hours()[0]['days']) ?>: <?= e(site_hours()[0]['time']) ?>
            </span>
            <a href="<?= e(tel_link(site_phone())) ?>" class="top-bar-item top-bar-phone">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.15a16 16 0 0 0 6.09 6.09l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
                <?= e(site_phone()) ?>
            </a>
        </div>
    </div>

    <!-- NAVBAR — single row: logo left, links, account + CTA right. -->
    <header class="navbar" id="navbar">
        <div class="container nav-row">
            <a href="<?= $base_url ?>" class="logo" aria-label="<?= e(site_name()) ?> — home">
                <?php include __DIR__ . '/brand.php'; ?>
            </a>

            <nav class="nav-links" aria-label="Primary">
                <?php foreach (SITE_NAV as $slug => $label): ?>
                    <?php if (isset($dropdowns[$slug])): $dd = $dropdowns[$slug]; ?>
                        <div class="nav-item has-dropdown">
                            <a href="<?= $base_url . ($slug === 'applications' ? 'countertops' : ($slug === 'resources' ? 'glossary' : $slug)) ?>"
                               class="nav-trigger <?= $navOpen === $slug ? 'active' : '' ?>">
                                <?= e($label) ?>
                                <svg class="chevron" viewBox="0 0 24 24" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </a>
                            <div class="dropdown">
                                <div class="dropdown-inner">
                                    <p class="dropdown-title"><?= e($dd['title']) ?></p>
                                    <ul class="dropdown-list">
                                        <?php foreach ($dd['items'] as [$itemLabel, $itemSlug, $blurb]): ?>
                                            <li>
                                                <a href="<?= $base_url . e($itemSlug) ?>">
                                                    <span class="dropdown-name"><?= $itemLabel ?></span>
                                                    <span class="dropdown-blurb"><?= e(mb_substr(strip_tags(html_entity_decode($blurb)), 0, 62)) ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?= $base_url . e($dd['all'][1]) ?>" class="dropdown-all">
                                        <?= e($dd['all'][0]) ?>
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <line x1="4" y1="12" x2="19" y2="12" />
                                            <polyline points="13 6 19 12 13 18" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= $base_url . $slug ?>"
                           class="<?= $route === $slug ? 'active' : '' ?>"
                           <?= $route === $slug ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="nav-actions">
                <?php if (trade_check()): ?>
                    <a href="<?= $base_url ?>trade" class="nav-account is-in">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                        </svg>
                        My Account<?= cart_lines() ? ' (' . cart_lines() . ')' : '' ?>
                    </a>
                <?php else: ?>
                    <a href="<?= $base_url ?>trade" class="nav-account">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                        </svg>
                        Trade Login
                    </a>
                <?php endif; ?>
                <a href="<?= $base_url ?>contact" class="btn-nav">Contact Us</a>
            </div>

            <button class="mobile-toggle" id="mobileToggle" aria-label="Open menu"
                    aria-expanded="false" aria-controls="mobileMenu">
                <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span>
            </button>
        </div>
    </header>

    <!-- Mobile panel lives OUTSIDE <header> on purpose: .navbar.scrolled applies
         backdrop-filter, and a filtered ancestor becomes the containing block for
         position:fixed children — which collapsed this panel to zero height as
         soon as the page was scrolled. -->
    <nav class="mobile-menu" id="mobileMenu" aria-label="Mobile">
            <?php foreach (SITE_NAV as $slug => $label): ?>
                <?php if (isset($dropdowns[$slug])): $dd = $dropdowns[$slug]; ?>
                    <details class="mobile-sub"<?= $navOpen === $slug ? ' open' : '' ?>>
                        <summary>
                            <?= e($label) ?>
                            <svg class="chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </summary>
                        <div class="mobile-cats">
                            <?php foreach ($dd['items'] as [$itemLabel, $itemSlug, $blurb]): ?>
                                <a href="<?= $base_url . e($itemSlug) ?>">
                                    <span><?= $itemLabel ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?= $base_url . e($dd['all'][1]) ?>" class="mobile-sub-all">
                            <?= e($dd['all'][0]) ?>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <line x1="4" y1="12" x2="19" y2="12" />
                                <polyline points="13 6 19 12 13 18" />
                            </svg>
                        </a>
                    </details>
                <?php else: ?>
                    <a href="<?= $base_url . $slug ?>" class="<?= $route === $slug ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <a href="<?= $base_url ?>trade"><?= trade_check() ? 'My Account' : 'Trade Login' ?></a>
            <div class="mobile-foot">
                <a href="<?= $base_url ?>contact" class="btn-nav">Contact Us</a>
                <a href="<?= e(tel_link(site_phone())) ?>" class="mobile-call">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.15a16 16 0 0 0 6.09 6.09l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <?= e(site_phone()) ?>
                </a>
                <p class="mobile-hours"><?= e(site_hours()[0]['days']) ?>: <?= e(site_hours()[0]['time']) ?></p>
            </div>
    </nav>
