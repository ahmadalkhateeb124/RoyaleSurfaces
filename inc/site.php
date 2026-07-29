<?php
/**
 * Single source of truth for company data + per-page SEO metadata.
 *
 * Everything the header, footer, contact page, sitemap and JSON-LD render
 * comes from here — change a phone number once, it updates everywhere.
 *
 * ⚠ TODO: replace the placeholder NAP / domain values below with the real ones.
 */

// ── Company (NAP — must match Google Business Profile exactly) ────────────────
const SITE_NAME    = 'Royale Surfaces';
const SITE_TAGLINE = 'Wholesale Natural Stone — Texas';
const SITE_DOMAIN  = 'royalesurfaces.com';           // no protocol, no trailing slash

const SITE_STREET  = '4820 Commerce Park Dr';
const SITE_CITY    = 'Dallas';
const SITE_STATE   = 'TX';
const SITE_ZIP     = '75247';
const SITE_LAT     = '32.8795713';
const SITE_LNG     = '-96.9562622';

const SITE_PHONE   = '(972) 555-0180';
const SITE_EMAIL   = 'trade@royalesurfaces.com';

/** Where contact-form inquiries are delivered. */
const INQUIRY_TO   = 'trade@royalesurfaces.com';

const SITE_HOURS = [
    ['days' => 'Monday – Friday', 'time' => '7:00 AM – 5:00 PM', 'schema' => 'Mo-Fr 07:00-17:00'],
    ['days' => 'Saturday',        'time' => '8:00 AM – 2:00 PM', 'schema' => 'Sa 08:00-14:00'],
    ['days' => 'Sunday',          'time' => 'Closed',            'schema' => null],
];

const SITE_SOCIAL = [
    'instagram' => 'https://www.instagram.com/',
    'facebook'  => 'https://www.facebook.com/',
    'linkedin'  => 'https://www.linkedin.com/',
];

// ── Navigation (drives header, mobile menu and footer) ───────────────────────
const SITE_NAV = [
    'home'         => 'Home',
    'slabs'        => 'Inventory',      // URL stays /slabs — "slabs" is what people search
    'applications' => 'Applications',   // dropdown: project types
    'gallery'      => 'Gallery',
    'resources'    => 'Resources',     // dropdown: guides
    'about'        => 'About',
    'blog'         => 'Blog',
];

/** Footer-only links that are not top-level nav items. */
const SITE_NAV_EXTRA = [
    'services' => 'Services',
];

/**
 * Material categories. Single source for the navbar dropdown, the home grid,
 * the Slabs filter, the footer and the sitemap.
 *
 * ⚠ TODO: `porcelain`, `natural-stone` and `solid-surfaces` currently reuse
 *    existing photography as placeholders. Swap `image` once you have real shots.
 */
const SITE_MATERIALS = [
    'quartz' => [
        'label' => 'Quartz',
        'kind'  => 'Engineered',
        'image' => 'slab-quartz.jpg',
        'blurb' => 'Non-porous engineered quartz — perfectly repeatable slabs.',
    ],
    'porcelain' => [
        'label' => 'Porcelain',
        'kind'  => 'Engineered',
        'image' => 'slab-marble.jpg',
        'blurb' => 'Large-format porcelain slabs, UV-stable and heat-proof.',
    ],
    'natural-stone' => [
        'label' => 'Natural Stone',
        'kind'  => 'Quarried',
        'image' => 'hero-stone.jpg',
        'blurb' => 'Travertine, limestone and onyx — pavers to exotic slabs.',
    ],
    'quartzite' => [
        'label' => 'Quartzite',
        'kind'  => 'Natural Stone',
        'image' => 'slab-quartzite.jpg',
        'blurb' => 'Marble looks, granite-beating hardness — our fastest mover.',
    ],
    'granite' => [
        'label' => 'Granite',
        'kind'  => 'Natural Stone',
        'image' => 'slab-granite.jpg',
        'blurb' => 'The workhorse — heat resistant, built for outdoor kitchens.',
    ],
    'marble' => [
        'label' => 'Marble',
        'kind'  => 'Natural Stone',
        'image' => 'slab-marble.jpg',
        'blurb' => 'Carrara, Calacatta and Nero Marquina, honed or polished.',
    ],
    'solid-surfaces' => [
        'label' => 'Solid Surfaces',
        'kind'  => 'Engineered',
        'image' => 'slab-quartz.jpg',
        'blurb' => 'Seamless, thermoformable and fully renewable surfaces.',
    ],
];

/**
 * Cities we deliver to. Local SEO: naming the metros we actually serve gives
 * Google something concrete to match "granite supplier near <city>" against.
 */
const SITE_AREAS = [
    'Dallas', 'Fort Worth', 'Plano', 'Frisco', 'Arlington', 'Irving',
    'Houston', 'Austin', 'San Antonio', 'Waco', 'Tyler', 'Oklahoma City',
];

/**
 * When the /glossary, /care, /buying-guide, /finishes, /thickness, /compare
 * and /faq guide content was last substantively reviewed or rewritten.
 * Feeds the visible "Reviewed <month year>" line and each page's dateModified
 * in structured data — both real freshness signals search engines and AI
 * answer engines weigh when a query is about pricing, thickness or care
 * advice that can go stale. Bump this by hand whenever that content changes;
 * there is no database row backing these pages to derive it from.
 */
const SITE_GUIDES_REVIEWED = '2026-07-28';

/**
 * FAQ. Rendered on the homepage AND emitted as FAQPage JSON-LD, so the
 * questions can win rich results — one source, no drift between the two.
 */
const SITE_FAQ = [
    [
        'q' => 'Can homeowners buy directly, or do I need to be a fabricator?',
        'a' => 'Anyone is welcome. Fabricators, builders and designers make up most of our volume and receive wholesale pricing tiers, but homeowners and private clients are equally welcome to visit the gallery and buy from us directly. If you still need someone to fabricate and install, we can introduce you to shops in our network.',
    ],
    [
        'q' => 'What is the minimum order?',
        'a' => 'There is no minimum. Customers regularly buy a single slab for one countertop or a full container for a commercial build. Volume pricing tiers begin to apply as your quarterly totals increase.',
    ],
    [
        'q' => 'Can you guarantee slabs will match across a large project?',
        'a' => 'Yes. Every slab in our Dallas facility is catalogued by bundle and lot number. When you need twenty matching slabs for a hotel or multi-unit build, we pull them from the same block so veining and colour stay consistent across the entire run.',
    ],
    [
        'q' => 'What is the difference between quartzite and engineered quartz?',
        'a' => 'Quartzite is a natural stone quarried from the earth — it offers marble-like veining with granite-like hardness and heat resistance. Engineered quartz is a manufactured product of ground quartz and resin: non-porous and perfectly repeatable, but it can scorch under a hot pan and should never be installed outdoors.',
    ],
    [
        'q' => 'Do you deliver, or is it pickup only?',
        'a' => 'Both. Most customers load A-frames at our Dallas facility, where our crew and industrial cranes handle the lift. We also arrange delivery across Texas and dedicated container logistics for larger sourcing programs.',
    ],
    [
        'q' => 'Can you source a specific material you do not stock?',
        'a' => 'Usually, yes. We buy direct from quarries in Brazil, Italy, India and Spain, which lets us source specific exotics or large quantities of uniform material to your spec. Lead times depend on the quarry and shipping schedule — send us the material name and quantity and we will come back with a realistic timeline.',
    ],
    [
        'q' => 'How long can you hold material for me?',
        'a' => 'We can reserve specific lots for up to 14 days while a job is being finalised. Reservations are confirmed in writing and we guarantee block-matching across the material you selected.',
    ],
];

// ── Per-page SEO metadata ────────────────────────────────────────────────────
// Keys are lowercase route slugs. `priority`/`changefreq` feed sitemap.php.
const SITE_PAGES = [
    'home' => [
        'title'       => 'Wholesale Granite, Marble & Quartz Slabs | Royale Surfaces',
        'description' => 'Dallas wholesale stone supplier stocking granite, marble, quartz and quartzite. We buy direct from quarries so fabricators and homeowners get real pricing.',
        'image'       => 'hero-stone.jpg',
        'priority'    => '1.0',
        'changefreq'  => 'weekly',
    ],
    'about' => [
        'title'       => 'About Us — Direct-Import Stone Supplier | Royale Surfaces',
        'description' => '26,900 sq ft Dallas facility importing granite, marble and quartzite direct from quarries — no brokers, no markup games. See who we supply and how we source.',
        'image'       => 'about-warehouse.jpg',
        'priority'    => '0.8',
        'changefreq'  => 'monthly',
    ],
    'slabs' => [
        'title'       => 'Granite, Marble & Quartz Slab Inventory | Royale Surfaces',
        'description' => 'Search live slab inventory at our Dallas yard: granite, marble, quartz, quartzite and porcelain in full bundles and lot-matched sets, ready to walk and pick.',
        'image'       => 'slab-marble.jpg',
        'priority'    => '0.9',
        'changefreq'  => 'weekly',
    ],
    'services' => [
        'title'       => 'Wholesale Supply & Sourcing Services | Royale Surfaces',
        'description' => 'Volume pricing, custom quarry sourcing and dedicated accounts for fabricators, contractors and builders across DFW — plus direct access for homeowners.',
        'image'       => 'about-warehouse.jpg',
        'priority'    => '0.8',
        'changefreq'  => 'monthly',
    ],
    'gallery' => [
        'title'       => 'Stone Project Gallery — Texas Installs | Royale Surfaces',
        'description' => 'Kitchen islands, vanities and outdoor installs cut from our granite, marble and quartzite stock — real Texas projects fabricated from our Dallas inventory.',
        'image'       => 'gallery-kitchen.jpg',
        'priority'    => '0.7',
        'changefreq'  => 'monthly',
    ],
    'blog' => [
        'title'       => 'Stone Buying Guides & Material Tips | Royale Surfaces',
        'description' => 'Granite vs quartz, care and sealing tips, finish comparisons and slab-buying advice from a Dallas wholesale supplier who talks to fabricators daily.',
        'image'       => 'hero-stone.jpg',
        'priority'    => '0.7',
        'changefreq'  => 'weekly',
    ],
    'contact' => [
        'title'       => 'Contact Us — Visit Our Dallas Slab Yard | Royale Surfaces',
        'description' => 'Visit our 26,900 sq ft slab yard at 4820 Commerce Park Dr, Dallas. Open to fabricators and homeowners alike — call or request a quote today.',
        'image'       => 'about-warehouse.jpg',
        'priority'    => '0.8',
        'changefreq'  => 'yearly',
    ],
    'privacy-policy' => [
        'title'       => 'Privacy Policy | Royale Surfaces',
        'description' => 'How Royale Surfaces collects, uses and protects information submitted through this website.',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
        'noindex'     => false,
    ],
    'terms' => [
        'title'       => 'Terms of Service | Royale Surfaces',
        'description' => 'Terms governing use of the Royale Surfaces website and our stone supply relationship with customers.',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
    ],
    '404' => [
        'title'       => 'Page Not Found | Royale Surfaces',
        'description' => 'The page you requested could not be found.',
        'noindex'     => true,
    ],
];

// ── Business details ─────────────────────────────────────────────────────────
// Every one of these is editable from the portal (Profile → Business Details).
// The constants above are only the fallback: they are what the site shows before
// anything has been saved, and if the database ever goes away.
//
// setting() lives in settings.php, which loads after this file — hence the
// function_exists guard, so site.php still works when included on its own.

/** Read a business detail from the portal, falling back to the constant. */
function biz(string $key, string $default): string
{
    return function_exists('setting') ? setting('biz_' . $key, $default) : $default;
}

function site_name(): string   { return biz('name',   SITE_NAME); }
function site_phone(): string  { return biz('phone',  SITE_PHONE); }
function site_email(): string  { return biz('email',  SITE_EMAIL); }
function site_street(): string { return biz('street', SITE_STREET); }
function site_city(): string   { return biz('city',   SITE_CITY); }
function site_state(): string  { return biz('state',  SITE_STATE); }
function site_zip(): string    { return biz('zip',    SITE_ZIP); }

/**
 * Where contact-form and trade-request emails are delivered. Falls back to the
 * public address, so leaving the field blank in the portal is safe rather than
 * silently sending mail to a stale constant.
 */
function inquiry_to(): string
{
    return biz('inquiry_to', site_email());
}

/** Full street address on one line. */
function site_address(): string
{
    return site_street() . ', ' . site_city() . ', ' . site_state() . ' ' . site_zip();
}

/**
 * Google Maps directions link.
 *
 * An admin can paste their own Google Business Profile or Plus Code link, which
 * lands on the exact storefront rather than whatever Maps guesses from a typed
 * address. Otherwise the address is used.
 */
function site_directions_url(): string
{
    $custom = biz('map_url', '');
    if ($custom !== '' && filter_var($custom, FILTER_VALIDATE_URL)) {
        return $custom;
    }
    return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode(site_address());
}

/**
 * Google Maps embed URL for the contact page.
 *
 * An admin pastes the embed from their Google Business Profile, which drops the
 * pin on the actual storefront. Without one we fall back to a search for the
 * address — that always renders, but Google is only guessing at the exact spot.
 */
function site_map_embed(): string
{
    $custom = biz('map_embed', '');
    if ($custom !== '' && str_starts_with($custom, 'https://www.google.com/maps/embed')) {
        return $custom;
    }
    return 'https://maps.google.com/maps?q=' . rawurlencode(site_address())
        . '&t=m&z=14&output=embed&iwloc=near';
}

/**
 * Opening hours. Each row keeps the constant's day label and schema window, and
 * takes its displayed time from the portal when one is saved.
 *
 * The schema.org string is re-derived from the saved time so structured data
 * never contradicts what the page shows. A row that cannot be parsed simply
 * drops out of the schema rather than publishing a wrong window.
 */
function site_hours(): array
{
    $out = [];
    foreach (SITE_HOURS as $i => $row) {
        $time = biz('hours_' . $i, $row['time']);
        $out[] = [
            'days'   => biz('hours_days_' . $i, $row['days']),
            'time'   => $time,
            'schema' => $time === $row['time'] ? $row['schema'] : hours_to_schema($row['days'], $time),
        ];
    }
    return $out;
}

/**
 * Turn "Monday – Friday" + "7:00 AM – 5:00 PM" into "Mo-Fr 07:00-17:00".
 * Returns null when the text does not parse, or when the day is closed.
 */
function hours_to_schema(string $days, string $time): ?string
{
    if (stripos($time, 'closed') !== false) {
        return null;
    }

    $map = ['monday' => 'Mo', 'tuesday' => 'Tu', 'wednesday' => 'We', 'thursday' => 'Th',
            'friday' => 'Fr', 'saturday' => 'Sa', 'sunday' => 'Su'];

    preg_match_all('/[a-z]+/i', strtolower($days), $dm);
    $codes = [];
    foreach ($dm[0] as $word) {
        if (isset($map[$word])) {
            $codes[] = $map[$word];
        }
    }
    if (!$codes) {
        return null;
    }

    // "7:00 AM – 5:00 PM" — en dash, hyphen or "to" all appear in real copy.
    if (!preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*(?:–|-|—|to)\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i', $time, $m)) {
        return null;
    }

    $to24 = static function (string $h, string $min, string $mer): string {
        $h = (int) $h;
        $mer = strtolower($mer);
        if ($mer === 'pm' && $h < 12) {
            $h += 12;
        } elseif ($mer === 'am' && $h === 12) {
            $h = 0;
        }
        return sprintf('%02d:%02d', $h, $min === '' ? 0 : (int) $min);
    };

    $open  = $to24($m[1], $m[2] ?? '', $m[3] ?? '');
    $close = $to24($m[4], $m[5] ?? '', $m[6] ?? '');

    return (count($codes) > 1 ? $codes[0] . '-' . end($codes) : $codes[0]) . ' ' . $open . '-' . $close;
}

/** Escape for HTML output. Short alias used throughout the templates. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Page numbers to render in a pager: always first and last, plus a window
 * around the current page, with nulls marking where an ellipsis goes. Keeps the
 * control a fixed width whether there are 3 pages or 200.
 *
 * Shared by the blog archive and the project gallery.
 */
function pager_range(int $page, int $pages, int $window = 1): array
{
    if ($pages <= 7) {
        return range(1, $pages);
    }

    $out = [1];
    $from = max(2, $page - $window);
    $to = min($pages - 1, $page + $window);

    if ($from > 2) {
        $out[] = null;
    }
    for ($i = $from; $i <= $to; $i++) {
        $out[] = $i;
    }
    if ($to < $pages - 1) {
        $out[] = null;
    }
    $out[] = $pages;

    return $out;
}

/**
 * Versioned URL for a static asset.
 *
 * .htaccess serves CSS/JS with `Cache-Control: immutable`, which tells browsers
 * never to revalidate — so a hand-written `?v=2` means every edit stays invisible
 * until someone remembers to bump it. Stamping the file's mtime busts the cache
 * automatically on save while keeping the long cache lifetime in production.
 */
function asset(string $path): string
{
    global $base_url, $base_path;

    $path = ltrim($path, '/');
    $file = rtrim($base_path, '/') . '/' . $path;

    // filemtime is the whole point: the URL changes the moment the file does,
    // so a year-long immutable cache can never serve a stale image again.
    $version = is_file($file) ? filemtime($file) : 1;

    // Encode each segment but keep the slashes — upload names contain spaces.
    $safe = implode('/', array_map('rawurlencode', explode('/', $path)));

    return $base_url . $safe . '?v=' . $version;
}

/**
 * Public URL for a content image. Files uploaded through the portal live in
 * assets/uploads/; the originals that shipped with the theme are in assets/images/.
 */
function slab_image(string $name): string
{
    global $base_path;

    if ($name === '') {
        return asset('assets/images/hero-stone.jpg');
    }

    // Uploads win over the bundled seed images of the same name.
    $dir = is_file(rtrim($base_path, '/') . '/assets/uploads/' . $name) ? 'uploads' : 'images';

    return asset('assets/' . $dir . '/' . $name);
}
