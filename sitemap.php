<?php
/**
 * Dynamic XML sitemap — static pages + every blog post.
 * Served at /sitemap.xml via the .htaccess rewrite.
 *
 * Page priorities and change frequencies live in SITE_PAGES (inc/site.php),
 * so adding a page there automatically adds it here.
 */

require_once __DIR__ . '/inc/conn.php';
require_once __DIR__ . '/inc/posts.php';
require_once __DIR__ . '/inc/slabs.php';
require_once __DIR__ . '/inc/content-pages.php';

// Guarded so the admin panel can buffer this file to write sitemap.xml to disk
// without PHP warning that headers were already sent.
if (!headers_sent()) {
    header('Content-Type: application/xml; charset=utf-8');
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$baseUrl = rtrim($base_url, '/') . '/';

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

/** Emit one <url> entry. */
function sitemap_url(string $loc, string $priority, string $changefreq, ?string $lastmod = null, ?string $image = null, ?string $title = null): void
{
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    if ($lastmod) {
        echo "    <lastmod>{$lastmod}</lastmod>\n";
    }
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    if ($image) {
        echo "    <image:image>\n";
        echo '      <image:loc>' . htmlspecialchars($image, ENT_XML1) . "</image:loc>\n";
        if ($title) {
            echo '      <image:title>' . htmlspecialchars($title, ENT_XML1) . "</image:title>\n";
        }
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}

// ── Static pages ─────────────────────────────────────────────────────────────
foreach (SITE_PAGES as $slug => $meta) {
    if (!empty($meta['noindex']) || !isset($meta['priority'])) {
        continue;   // 404 and any page we deliberately keep out of the index
    }
    sitemap_url(
        $baseUrl . ($slug === 'home' ? '' : $slug),
        $meta['priority'],
        $meta['changefreq'],
        null,
        isset($meta['image']) ? $baseUrl . 'assets/images/' . $meta['image'] : null,
        $meta['title'] ?? null
    );
}

// ── Material category pages ──────────────────────────────────────────────────
foreach (SITE_MATERIALS as $slug => $m) {
    sitemap_url($baseUrl . $slug, '0.8', 'weekly', null,
        $baseUrl . 'assets/images/' . $m['image'], $m['label'] . ' Slabs');
}

// ── One page per slab ────────────────────────────────────────────────────────
foreach (slabs_all() as $slab) {
    if (empty($slab['slug'])) {
        continue;
    }
    sitemap_url($baseUrl . 'slabs/' . $slab['slug'], '0.7', 'weekly', null,
        slab_image($slab['image']), $slab['name']);
}

// ── Application pages ────────────────────────────────────────────────────────
foreach (APPLICATION_PAGES as $slug => $app) {
    sitemap_url($baseUrl . $slug, '0.8', 'monthly', null,
        $baseUrl . 'assets/images/' . $app['image'],
        strip_tags(html_entity_decode($app['title'])));
}

// ── Reference pages ──────────────────────────────────────────────────────────
foreach (REFERENCE_PAGES as $slug => $ref) {
    sitemap_url($baseUrl . $slug, '0.6', 'yearly');
}

// ── Blog posts ───────────────────────────────────────────────────────────────
foreach (blog_posts() as $slug => $post) {
    if (!empty($post['noindex'])) {
        continue;   // marked "hide from search engines" in the portal
    }
    sitemap_url(
        $baseUrl . 'blog/' . $slug,
        '0.6',
        'yearly',
        date('c', strtotime($post['published'])),
        slab_image($post['image']),
        $post['title']
    );
}


echo '</urlset>';
