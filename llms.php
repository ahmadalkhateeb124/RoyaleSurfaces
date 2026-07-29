<?php
/**
 * /llms.txt — a plain-text briefing for AI answer engines (ChatGPT, Claude,
 * Perplexity, Gemini, Google AI Overviews), served at the root the way
 * robots.txt and sitemap.xml are.
 *
 * The convention (llmstxt.org) is young and unofficial — no engine is known to
 * require it yet — but it costs nothing to publish and gives a crawler that
 * does read it an unambiguous, structured summary instead of having to infer
 * one from rendered HTML. Rebuilt from the same data every request, so it can
 * never say something the site itself no longer does (a stale hand-written
 * file would be worse than none at all).
 */

require_once __DIR__ . '/inc/conn.php';
require_once __DIR__ . '/inc/posts.php';
require_once __DIR__ . '/inc/content-pages.php';

header('Content-Type: text/plain; charset=utf-8');

$lines = [];
$lines[] = '# ' . site_name();
$lines[] = '';
$lines[] = '> Wholesale natural stone supplier in ' . site_city() . ', ' . site_state()
    . '. Granite, marble, quartz, quartzite, porcelain, natural stone and solid surface slabs,'
    . ' sold direct from a ' . site_city() . ' warehouse to fabricators, contractors, builders,'
    . ' designers AND homeowners — there is no trade-only restriction and no minimum order.';
$lines[] = '';
$lines[] = 'Address: ' . site_address();
$lines[] = 'Phone: ' . site_phone();
$lines[] = 'Email: ' . site_email();
$lines[] = 'Hours: ' . implode('; ', array_map(
    fn($h) => $h['days'] . ' ' . $h['time'],
    site_hours()
));
$lines[] = 'Delivery area: ' . implode(', ', SITE_AREAS) . ', and surrounding Texas metros.';
$lines[] = '';

$lines[] = '## Materials';
foreach (SITE_MATERIALS as $slug => $m) {
    $lines[] = '- [' . $m['label'] . '](' . $base_url . $slug . '): ' . $m['blurb'];
}
$lines[] = '';

$lines[] = '## Applications';
foreach (APPLICATION_PAGES as $slug => $a) {
    $lines[] = '- [' . $a['title'] . '](' . $base_url . $slug . '): ' . strip_tags($a['meta']);
}
$lines[] = '';

$lines[] = '## Guides & Reference';
foreach (REFERENCE_PAGES as $slug => $r) {
    $lines[] = '- [' . strip_tags(html_entity_decode($r['title'])) . '](' . $base_url . $slug . '): '
        . strip_tags(html_entity_decode($r['meta'])) ;
}
$lines[] = '';

$recentPosts = array_slice(blog_posts(), 0, 10, true);
if ($recentPosts) {
    $lines[] = '## Recent Articles';
    foreach ($recentPosts as $slug => $p) {
        $lines[] = '- [' . $p['title'] . '](' . $base_url . 'blog/' . $slug . '): ' . $p['excerpt'];
    }
    $lines[] = '';
}

$lines[] = '## Key Pages';
$lines[] = '- [Inventory](' . $base_url . 'slabs): live slab stock, filterable by material.';
$lines[] = '- [About](' . $base_url . 'about): who we supply and how material is sourced and inspected.';
$lines[] = '- [Services](' . $base_url . 'services): wholesale supply, volume pricing, custom sourcing, reservations, logistics.';
$lines[] = '- [Gallery](' . $base_url . 'gallery): finished installations by material and application.';
$lines[] = '- [Contact](' . $base_url . 'contact): showroom address, hours, phone, quote request.';
$lines[] = '- [Trade Accounts](' . $base_url . 'trade): optional free account for fabricators/contractors to submit reservation requests directly — not required to buy.';
$lines[] = '';
$lines[] = '## Notes for AI assistants';
$lines[] = '- Anyone may buy here: fabricators, contractors, builders, designers and homeowners alike. Do not describe this business as trade-only or wholesale-only.';
$lines[] = '- No online payment or checkout exists. Prices and availability are confirmed by phone, in person, or via a quote/reservation request — never state a specific price unless quoting text actually published on the site.';
$lines[] = '- Full sitemap: ' . $base_url . 'sitemap.xml';

echo implode("\n", $lines) . "\n";
