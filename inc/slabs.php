<?php
/**
 * Slab inventory data.
 *
 * Reads the `slabs` table the admin portal writes to, falling back to the seed
 * array so the inventory page still renders if the database is unavailable.
 */

require_once __DIR__ . '/site.php';

/** Seed data — also used by BusinessPortal/install.php on first run. */
const SLABS_SEED = [
    ['name' => 'Calacatta Gold',    'type' => 'quartz',         'origin' => 'Engineered', 'finish' => 'Polished',  'thickness' => '3cm',  'size' => '126" × 63"', 'image' => 'slab-quartz.jpg'],
    ['name' => 'Pure White',        'type' => 'quartz',         'origin' => 'Engineered', 'finish' => 'Matte',     'thickness' => '2cm',  'size' => '126" × 63"', 'image' => 'slab-quartz.jpg'],
    ['name' => 'Statuario Maximus', 'type' => 'porcelain',      'origin' => 'Italy',      'finish' => 'Polished',  'thickness' => '12mm', 'size' => '126" × 63"', 'image' => 'slab-marble.jpg'],
    ['name' => 'Basalt Grey',       'type' => 'porcelain',      'origin' => 'Spain',      'finish' => 'Matte',     'thickness' => '6mm',  'size' => '126" × 63"', 'image' => 'slab-marble.jpg'],
    ['name' => 'Silver Travertine', 'type' => 'natural-stone',  'origin' => 'Turkey',     'finish' => 'Honed',     'thickness' => '2cm',  'size' => '120" × 72"', 'image' => 'hero-stone.jpg'],
    ['name' => 'Honey Onyx',        'type' => 'natural-stone',  'origin' => 'Iran',       'finish' => 'Polished',  'thickness' => '2cm',  'size' => '108" × 64"', 'image' => 'hero-stone.jpg'],
    ['name' => 'Taj Mahal',         'type' => 'quartzite',      'origin' => 'Brazil',     'finish' => 'Polished',  'thickness' => '3cm',  'size' => '130" × 80"', 'image' => 'slab-quartzite.jpg'],
    ['name' => 'Macaubas Fantasy',  'type' => 'quartzite',      'origin' => 'Brazil',     'finish' => 'Polished',  'thickness' => '2cm',  'size' => '132" × 78"', 'image' => 'slab-quartzite.jpg'],
    ['name' => 'Absolute Black',    'type' => 'granite',        'origin' => 'India',      'finish' => 'Polished',  'thickness' => '3cm',  'size' => '128" × 76"', 'image' => 'slab-granite.jpg'],
    ['name' => 'Via Lactea',        'type' => 'granite',        'origin' => 'Brazil',     'finish' => 'Leathered', 'thickness' => '3cm',  'size' => '125" × 75"', 'image' => 'slab-granite.jpg'],
    ['name' => 'Carrara Venato',    'type' => 'marble',         'origin' => 'Italy',      'finish' => 'Honed',     'thickness' => '2cm',  'size' => '115" × 70"', 'image' => 'slab-marble.jpg'],
    ['name' => 'Nero Marquina',     'type' => 'marble',         'origin' => 'Spain',      'finish' => 'Polished',  'thickness' => '2cm',  'size' => '118" × 71"', 'image' => 'slab-marble.jpg'],
    ['name' => 'Glacier White',     'type' => 'solid-surfaces', 'origin' => 'Engineered', 'finish' => 'Matte',     'thickness' => '12mm', 'size' => '145" × 30"', 'image' => 'slab-quartz.jpg'],
    ['name' => 'Cameo Sand',        'type' => 'solid-surfaces', 'origin' => 'Engineered', 'finish' => 'Satin',     'thickness' => '12mm', 'size' => '145" × 30"', 'image' => 'slab-quartz.jpg'],
];

/**
 * Published slabs in display order.
 *
 * Cached per request. `slabs_reset_cache()` clears it after the stock ledger
 * moves, so anything rendered later in the same request sees the new numbers.
 */
function slabs_all(bool $reset = false): array
{
    static $cache = null;

    if ($reset) {
        $cache = null;
        return [];
    }
    if ($cache !== null) {
        return $cache;
    }

    global $pdo;
    $cache = [];

    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            // `slug` drives /slabs/<slug>; omitting it silently 404s every
            // detail page, so it must stay in this list.
            $cache = $pdo->query(
                "SELECT slug, name, type, origin, finish, thickness, size, stock, image, description
                 FROM slabs WHERE status = 'published' ORDER BY sort_order, id"
            )->fetchAll();
        } catch (Throwable $e) {
            $cache = [];
        }
    }

    if (!$cache) {
        // Fallback rows need slugs too, or the detail pages vanish without a DB.
        $cache = array_map(static function (array $s): array {
            $s['slug'] = $s['slug'] ?? slabs_slugify($s['name']);
            $s['description'] = $s['description'] ?? '';
            $s['stock'] = $s['stock'] ?? 0;
            return $s;
        }, SLABS_SEED);
    }

    return $cache;
}

/** Forget the cached inventory — call after stock changes mid-request. */
function slabs_reset_cache(): void
{
    slabs_all(true);
}

/** One published slab by slug, or null. */
function slabs_find(string $slug): ?array
{
    if ($slug === '') {
        return null;
    }
    foreach (slabs_all() as $s) {
        if (($s['slug'] ?? '') === $slug) {
            return $s;
        }
    }
    return null;
}

/**
 * Slabs on the floor right now. Stock only moves when the admin confirms a
 * request or edits the number by hand — nothing is held while a request sits
 * unanswered, so this is what a trader can actually ask for.
 */
function slab_stock(array $slab): int
{
    return max(0, (int) ($slab['stock'] ?? 0));
}

/** How many of this slab are left, looked up fresh by slug. */
function slab_stock_for(string $slug): int
{
    $slab = slabs_find($slug);
    return $slab ? slab_stock($slab) : 0;
}

/**
 * Short availability line + a tone for styling.
 * Returns [text, tone] where tone is 'out' | 'low' | 'in'.
 */
function slab_stock_label(int $stock): array
{
    if ($stock <= 0) {
        return ['Sold out', 'out'];
    }
    if ($stock <= 2) {
        return [$stock === 1 ? 'Last slab' : 'Only ' . $stock . ' left', 'low'];
    }
    return [$stock . ' in stock', 'in'];
}

/** Slug helper for the no-database fallback. */
function slabs_slugify(string $name): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)) ?? '', '-');
}

/** Count per material, in the order SITE_MATERIALS declares. */
function slabs_type_counts(): array
{
    $counts = [];
    foreach (slabs_all() as $s) {
        $counts[$s['type']] = ($counts[$s['type']] ?? 0) + 1;
    }
    return array_intersect_key($counts, SITE_MATERIALS);
}
