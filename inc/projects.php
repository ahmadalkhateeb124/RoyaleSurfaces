<?php
/**
 * Project gallery data.
 *
 * `type` must match a key in SITE_MATERIALS — that's what drives the filter
 * chips and the "source this material" link back to the inventory.
 *
 * `feature => true` makes a tile span two columns, which breaks up the grid
 * rhythm. Use it sparingly (roughly one in six).
 *
 * ⚠ TODO: these entries reuse the few photos currently in assets/images as
 *    placeholders. Replace `image` with real project photography — that is the
 *    single biggest visual upgrade left on this site.
 */

require_once __DIR__ . '/site.php';   // SITE_MATERIALS, pager_range()

const GALLERY_PROJECTS = [
    [
        'title'    => 'Monolithic Kitchen Island',
        'space'    => 'Residential Kitchen',
        'material' => 'Taj Mahal Quartzite',
        'type'     => 'quartzite',
        'image'    => 'gallery-kitchen.jpg',
        'location' => 'Highland Park, TX',
        'body'     => 'A 12-foot waterfall island cut from a single bookmatched pair. Lot-matched veining carries uninterrupted from the countertop down both legs.',
        'feature'  => true,
    ],
    [
        'title'    => 'Floating Master Vanity',
        'space'    => 'Bathroom',
        'material' => 'Nero Marquina Marble',
        'type'     => 'marble',
        'image'    => 'gallery-bathroom.jpg',
        'location' => 'Preston Hollow, TX',
        'body'     => 'Honed Nero Marquina with a mitred 4cm edge and a full-height backsplash, supplied as a matched three-slab bundle from one block.',
    ],
    [
        'title'    => 'Outdoor Living Feature',
        'space'    => 'Exterior',
        'material' => 'Absolute Black Granite',
        'type'     => 'granite',
        'image'    => 'gallery-outdoor.jpg',
        'location' => 'Southlake, TX',
        'body'     => 'Leathered Absolute Black chosen for low porosity and UV stability — an outdoor kitchen and bar surround built to survive a Texas summer.',
    ],
    [
        'title'    => 'Waterfall Bar Surround',
        'space'    => 'Commercial',
        'material' => 'Calacatta Gold Quartz',
        'type'     => 'quartz',
        'image'    => 'slab-quartz.jpg',
        'location' => 'Uptown Dallas, TX',
        'body'     => 'Eighteen matched quartz slabs across a hotel bar and lounge. Engineered material was specified so every station reads identically.',
    ],
    [
        'title'    => 'Full-Height Feature Wall',
        'space'    => 'Commercial',
        'material' => 'Macaubas Fantasy Quartzite',
        'type'     => 'quartzite',
        'image'    => 'slab-quartzite.jpg',
        'location' => 'Frisco, TX',
        'body'     => 'A four-way bookmatch running two storeys in a corporate lobby. Blocks were reserved at the quarry six months before install.',
    ],
    [
        'title'    => 'Carrara Butler\'s Pantry',
        'space'    => 'Residential Kitchen',
        'material' => 'Carrara Venato Marble',
        'type'     => 'marble',
        'image'    => 'slab-marble.jpg',
        'location' => 'University Park, TX',
        'body'     => 'Honed Carrara throughout a working pantry, with the client briefed on patina so the finish ages as intended rather than as a defect.',
    ],
    [
        'title'    => 'Leathered Granite Kitchen',
        'space'    => 'Residential Kitchen',
        'material' => 'Via Lactea Granite',
        'type'     => 'granite',
        'image'    => 'slab-granite.jpg',
        'location' => 'Plano, TX',
        'body'     => 'Leathered finish specified to hide daily wear in a busy family kitchen while keeping the depth of the natural stone.',
        'feature'  => true,
    ],
    [
        'title'    => 'Travertine Spa Surround',
        'space'    => 'Bathroom',
        'material' => 'Silver Travertine',
        'type'     => 'natural-stone',
        'image'    => 'hero-stone.jpg',
        'location' => 'Westlake, TX',
        'body'     => 'Honed travertine wrapping a wet room, cut from a single lot so the tonal banding stays continuous around every corner.',
    ],
    [
        'title'    => 'Porcelain Clad Elevator Lobby',
        'space'    => 'Commercial',
        'material' => 'Statuario Maximus Porcelain',
        'type'     => 'porcelain',
        'image'    => 'about-warehouse.jpg',
        'location' => 'Las Colinas, TX',
        'body'     => 'Six-millimetre porcelain over existing substrate — the weight saving was what made the cladding viable on an occupied floor.',
    ],
];

/** How many projects render per gallery page. */
const GALLERY_PER_PAGE = 12;

/**
 * Published projects. Reads the `projects` table the admin portal writes to,
 * and falls back to the seed array above if the database is unavailable.
 */
function gallery_projects(): array
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
                "SELECT title, space, material, type, location, body, image, is_feature
                 FROM projects WHERE status = 'published' ORDER BY sort_order, id"
            )->fetchAll();

            foreach ($rows as $r) {
                $cache[] = [
                    'title'    => $r['title'],
                    'space'    => $r['space'],
                    'material' => $r['material'],
                    'type'     => $r['type'],
                    'image'    => $r['image'],
                    'location' => $r['location'],
                    'body'     => $r['body'],
                    'feature'  => (bool) $r['is_feature'],
                ];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }

    if (!$cache) {
        $cache = GALLERY_PROJECTS;
    }

    return $cache;
}

/** Only the material filters that actually have projects, with counts. */
function gallery_type_counts(): array
{
    $counts = [];
    foreach (gallery_projects() as $p) {
        $counts[$p['type']] = ($counts[$p['type']] ?? 0) + 1;
    }
    // Follow the declared material order rather than first-seen order.
    return array_intersect_key($counts, SITE_MATERIALS);
}

/**
 * One page of the gallery, optionally filtered by material.
 * Page numbers are clamped so ?page=999 lands on the last page.
 */
function gallery_page(int $page = 1, string $type = '', int $perPage = GALLERY_PER_PAGE): array
{
    $all = $type !== ''
        ? array_values(array_filter(gallery_projects(), fn($p) => $p['type'] === $type))
        : gallery_projects();

    $total = count($all);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));

    return [
        'projects' => array_slice($all, ($page - 1) * $perPage, $perPage),
        'page'     => $page,
        'pages'    => $pages,
        'total'    => $total,
        'perPage'  => $perPage,
        'type'     => $type,
    ];
}

/** Build a gallery URL preserving the active material filter. */
function gallery_url(string $base, int $page = 1, string $type = ''): string
{
    $q = [];
    if ($type !== '') {
        $q['type'] = $type;
    }
    if ($page > 1) {
        $q['page'] = $page;
    }
    return $base . 'gallery' . ($q ? '?' . http_build_query($q) : '');
}
