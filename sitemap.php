<?php
/**
 * Dynamic XML sitemap.
 * Lists all static pages + every published blog post.
 *
 *   Accessible at /sitemap.xml (via .htaccess rewrite)
 *   or directly at /sitemap.php
 */

require_once __DIR__ . '/inc/conn.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$baseUrl = isset($base_url) ? rtrim($base_url, '/') . '/' : 'https://jpilawfirm.com/';

// Static pages (priority + change frequency tuned for a law-firm site)
$staticPages = [
    '' => ['priority' => '1.0', 'changefreq' => 'weekly'],
    'about' => ['priority' => '0.9', 'changefreq' => 'monthly'],
    'services' => ['priority' => '0.9', 'changefreq' => 'monthly'],
    'practice' => ['priority' => '0.9', 'changefreq' => 'monthly'],
    'attorney' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    'certificates' => ['priority' => '0.7', 'changefreq' => 'yearly'],
    'faq' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    'blog' => ['priority' => '0.9', 'changefreq' => 'weekly'],
    'contact' => ['priority' => '0.8', 'changefreq' => 'yearly'],
    'appointment' => ['priority' => '0.8', 'changefreq' => 'yearly'],
    'privacy-policy' => ['priority' => '0.3', 'changefreq' => 'yearly'],
    'terms-conditions' => ['priority' => '0.3', 'changefreq' => 'yearly'],
];

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

foreach ($staticPages as $path => $meta) {
    $loc = htmlspecialchars($baseUrl . $path, ENT_XML1);
    echo "  <url>\n";
    echo "    <loc>$loc</loc>\n";
    echo "    <changefreq>{$meta['changefreq']}</changefreq>\n";
    echo "    <priority>{$meta['priority']}</priority>\n";
    echo "  </url>\n";
}

// Dynamic blog posts
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->query(
            "SELECT slug, featured_image, title_ar,
                    DATE_FORMAT(COALESCE(updated_at, published_at, created_at), '%Y-%m-%dT%H:%i:%s+03:00') AS lastmod
             FROM blog_posts
             WHERE status = 'published'
             ORDER BY COALESCE(published_at, created_at) DESC"
        );
        while ($row = $stmt->fetch()) {
            $loc = htmlspecialchars($baseUrl . 'blog-details/' . $row['slug'], ENT_XML1);
            echo "  <url>\n";
            echo "    <loc>$loc</loc>\n";
            echo "    <lastmod>{$row['lastmod']}</lastmod>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            if (!empty($row['featured_image'])) {
                $imgUrl = htmlspecialchars($row['featured_image'], ENT_XML1);
                $imgTitle = htmlspecialchars((string) $row['title_ar'], ENT_XML1);
                echo "    <image:image>\n";
                echo "      <image:loc>$imgUrl</image:loc>\n";
                echo "      <image:title>$imgTitle</image:title>\n";
                echo "    </image:image>\n";
            }
            echo "  </url>\n";
        }
    } catch (Throwable $e) {
        // continue silently — sitemap should still serve static pages
    }
}

echo '</urlset>';
