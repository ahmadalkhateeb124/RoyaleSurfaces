<?php
/**
 * Blog content. Shared by pages/blog.php (listing) and pages/blog-details.php
 * (single post), so a post is defined exactly once.
 *
 * When the blog_posts table exists, blog.php prefers it and falls back here.
 */

require_once __DIR__ . '/site.php';   // pager_range()

const BLOG_POSTS = [
    'natural-stone-trends' => [
        'title'     => 'Natural Stone Trends Shaping Texas Projects',
        'excerpt'   => 'Warm-toned quartzite, heavy bookmatching and leathered finishes are driving what fabricators are being asked to quote this year.',
        'published' => '2026-07-20',
        'read'      => 4,
        'category'  => 'trends',
        'image'     => 'slab-quartzite.jpg',
        'body'      => [
            ['h2', 'Warm neutrals are replacing cool grey'],
            ['p',  'For most of the last decade the default specification was a cool grey quartz. That has shifted. Designers are asking for warmer, softer backgrounds — creamy quartzites, honed marbles with beige movement, and granites with brown or gold undertones. If you are stocking for spec work, weight your inventory toward warm neutrals.'],
            ['h2', 'Bookmatching is no longer just for feature walls'],
            ['p',  'Bookmatched pairs used to be reserved for a fireplace surround or a lobby wall. We are now quoting bookmatched material for kitchen islands, waterfall legs and even shower enclosures. This changes how you buy: you need to secure consecutive slabs from the same block at the point of purchase, not hope they are still on the floor a month later.'],
            ['h2', 'Leathered and honed finishes keep gaining'],
            ['p',  'Polished surfaces show every fingerprint and water spot. Leathered granite and honed marble hide daily wear far better, and clients increasingly ask for them by name. Leathered finishes also perform well outdoors, where UV stability and low porosity matter more than shine.'],
            ['h2', 'What this means for your buying'],
            ['p',  'Trends move faster than containers do. The practical response is to hold depth in a smaller number of proven materials rather than a single slab of everything. If a client falls in love with a stone you only have one of, the job stalls. Talk to your supplier about reserving lots before you quote.'],
        ],
    ],
    'choosing-the-right-material' => [
        'title'     => 'Granite, Quartzite, Marble or Quartz: Choosing the Right Material',
        'excerpt'   => 'A practical comparison of the four categories on hardness, porosity, heat tolerance and where each one genuinely belongs.',
        'published' => '2026-07-10',
        'read'      => 5,
        'category'  => 'guides',
        'image'     => 'slab-granite.jpg',
        'body'      => [
            ['h2', 'Granite: the workhorse'],
            ['p',  'Granite is an igneous stone, dense and highly heat resistant. It handles hot pans, outdoor exposure and heavy daily use without complaint. Sealing once a year is usually enough. It is the safest recommendation for a busy family kitchen or any outdoor application.'],
            ['h2', 'Quartzite: the one clients actually want'],
            ['p',  'Quartzite gives you marble-like veining with granite-like durability — which is why it commands a premium. Be careful with naming: a good deal of material sold as quartzite is actually dolomitic marble and will etch. Ask for the lot origin and test a sample with acid before you commit a client to it.'],
            ['h2', 'Marble: beautiful, and honest about it'],
            ['p',  'Marble etches. Lemon juice, wine and vinegar will mark it, and no sealer fully prevents that. The right move is not to talk clients out of marble but to set expectations: a honed finish disguises etching far better than a polished one, and many clients genuinely want the patina. Specify it for vanities, fireplaces and low-traffic surfaces.'],
            ['h2', 'Engineered quartz: consistency above all'],
            ['p',  'Quartz is non-porous, needs no sealing and comes in perfectly repeatable patterns — which makes it ideal for multi-unit and commercial work where every unit must match. Its weakness is heat: a hot pan can scorch the resin binder permanently. Never specify it outdoors, as UV exposure will discolour it.'],
            ['h2', 'The short version'],
            ['p',  'Outdoors or heavy heat, use granite. Marble looks with real durability, use quartzite. Pure aesthetics on a low-traffic surface, use marble. Repeatability across many units, use quartz.'],
        ],
    ],
    'maintaining-stone-surfaces' => [
        'title'     => 'How to Maintain Natural Stone Surfaces',
        'excerpt'   => 'The care advice worth passing to your clients at handover — what to seal, what to avoid, and how to keep a surface looking new.',
        'published' => '2026-06-28',
        'read'      => 3,
        'category'  => 'care',
        'image'     => 'slab-marble.jpg',
        'body'      => [
            ['h2', 'Daily cleaning'],
            ['p',  'Warm water and a pH-neutral stone cleaner are all that is needed. Avoid anything acidic — vinegar, lemon, bleach and most general-purpose bathroom sprays will dull a polished surface and etch calcium-based stone permanently.'],
            ['h2', 'Sealing schedule'],
            ['p',  'Most natural stone benefits from an impregnating sealer once a year; darker, denser granite can go longer. The simple field test: leave a few drops of water on the surface for fifteen minutes. If the stone darkens underneath, it is ready to be resealed. Engineered quartz never needs sealing.'],
            ['h2', 'Preventing damage'],
            ['p',  'Use trivets under hot cookware — essential on quartz, good practice on marble. Use cutting boards; stone will win against a knife and the client loses a blade. Wipe spills of wine, coffee, oil and citrus promptly, especially on light-coloured or honed material.'],
            ['h2', 'Handling etch marks'],
            ['p',  'Light etching on honed marble can often be buffed out with a marble polishing powder. Deeper damage needs a professional restoration pass. Set this expectation at handover — a client who understands that marble develops a patina is a happy client; one who was promised it would stay flawless is not.'],
        ],
    ],
];

/** Categories shown as filters. Keys must match each post's `category`. */
const BLOG_CATEGORIES = [
    'trends'   => 'Trends',
    'guides'   => 'Material Guides',
    'care'     => 'Care & Maintenance',
    'projects' => 'Project Spotlights',
    'industry' => 'Industry News',
];

/** How many posts appear on one listing page. */
const BLOG_PER_PAGE = 9;

/**
 * Published posts, newest first, keyed by slug.
 *
 * Reads the `posts` table when it exists (the admin portal writes there) and
 * falls back to the BLOG_POSTS array above, so the site keeps rendering if the
 * database is unreachable or the portal has not been installed yet.
 */
function blog_posts(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    global $pdo;
    $cache = [];

    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $rows = $pdo->query(
                "SELECT slug, title, excerpt, body, category, image, read_minutes, published_at,
                        created_at, updated_at, meta_title, meta_description, meta_keywords, noindex
                 FROM posts WHERE status = 'published' AND published_at <= CURDATE()
                 ORDER BY published_at DESC, id DESC"
            )->fetchAll();

            foreach ($rows as $r) {
                $cache[$r['slug']] = [
                    'title'     => $r['title'],
                    'excerpt'   => $r['excerpt'],
                    'published' => $r['published_at'],
                    // created_at/updated_at are both full timestamps set identically at
                    // insert time, so comparing them (not against published_at, a bare
                    // DATE with no time component) is what actually detects a real edit
                    // rather than reporting every fresh post as "just updated".
                    'updated'   => $r['updated_at'] > $r['created_at'] ? $r['updated_at'] : $r['published_at'],
                    'read'      => (int) $r['read_minutes'],
                    'category'  => $r['category'],
                    'image'     => $r['image'],
                    'body'      => blog_parse_body((string) $r['body']),
                    // Per-post SEO overrides. Blank means "derive from the content",
                    // which is what index.php falls back to.
                    'meta_title'       => $r['meta_title'],
                    'meta_description' => $r['meta_description'],
                    'meta_keywords'    => $r['meta_keywords'],
                    'noindex'          => (bool) $r['noindex'],
                ];
            }
        } catch (Throwable $e) {
            // Table missing or DB down — fall through to the seed array.
            $cache = [];
        }
    }

    if (!$cache) {
        $cache = BLOG_POSTS;
        uasort($cache, fn($a, $b) => strcmp($b['published'], $a['published']));
    }

    return $cache;
}

/**
 * Editor text → [tag, text] pairs.
 * Blank line separates blocks; a leading "## " marks a heading.
 */
function blog_parse_body(string $text): array
{
    $blocks = preg_split('/\n\s*\n/', trim(str_replace("\r\n", "\n", $text))) ?: [];
    $out = [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $out[] = str_starts_with($block, '## ')
            ? ['h2', trim(substr($block, 3))]
            : ['p', preg_replace('/\s*\n\s*/', ' ', $block)];
    }
    return $out;
}

/** Posts in one category, newest first. */
function blog_by_category(string $category): array
{
    return array_filter(blog_posts(), fn($p) => ($p['category'] ?? '') === $category);
}

/**
 * Only categories that actually have posts, with their counts — so an empty
 * category never renders a filter that leads to a dead end.
 */
function blog_category_counts(): array
{
    $counts = [];
    foreach (blog_posts() as $post) {
        $key = $post['category'] ?? '';
        if (isset(BLOG_CATEGORIES[$key])) {
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
    }
    // Preserve the declared order rather than first-seen order.
    return array_intersect_key($counts, BLOG_CATEGORIES);
}

/**
 * Slice the archive for one listing page.
 *
 * Returns the page's posts plus everything the pager needs. Page numbers are
 * clamped, so /blog?page=999 shows the last page instead of an empty grid.
 */
function blog_page(int $page = 1, string $category = '', int $perPage = BLOG_PER_PAGE): array
{
    $all = $category !== '' ? blog_by_category($category) : blog_posts();
    $total = count($all);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));

    return [
        'posts'    => array_slice($all, ($page - 1) * $perPage, $perPage, true),
        'page'     => $page,
        'pages'    => $pages,
        'total'    => $total,
        'perPage'  => $perPage,
        'category' => $category,
    ];
}

/**
 * Related posts: same category first, then recent posts to fill the gap.
 * Beats "the next two in the array", which pairs unrelated topics.
 */
function blog_related(string $slug, int $limit = 3): array
{
    $posts = blog_posts();
    $current = $posts[$slug] ?? null;
    if (!$current) {
        return array_slice($posts, 0, $limit, true);
    }

    $sameCat = array_filter(
        $posts,
        fn($p, $s) => $s !== $slug && ($p['category'] ?? '') === ($current['category'] ?? ''),
        ARRAY_FILTER_USE_BOTH
    );

    $related = array_slice($sameCat, 0, $limit, true);

    if (count($related) < $limit) {
        foreach ($posts as $s => $p) {
            if ($s === $slug || isset($related[$s])) {
                continue;
            }
            $related[$s] = $p;
            if (count($related) >= $limit) {
                break;
            }
        }
    }

    return $related;
}

/** Previous / next post in publication order, for article-footer navigation. */
function blog_neighbours(string $slug): array
{
    $slugs = array_keys(blog_posts());
    $i = array_search($slug, $slugs, true);
    if ($i === false) {
        return ['prev' => null, 'next' => null];
    }
    $posts = blog_posts();

    // The array is newest-first, so the *previous* article is the next index.
    return [
        'prev' => isset($slugs[$i + 1]) ? [$slugs[$i + 1], $posts[$slugs[$i + 1]]] : null,
        'next' => $i > 0 ? [$slugs[$i - 1], $posts[$slugs[$i - 1]]] : null,
    ];
}

/** Build a listing URL preserving the active category. */
function blog_url(string $base, int $page = 1, string $category = ''): string
{
    $q = [];
    if ($category !== '') {
        $q['cat'] = $category;
    }
    if ($page > 1) {
        $q['page'] = $page;
    }
    return $base . 'blog' . ($q ? '?' . http_build_query($q) : '');
}

