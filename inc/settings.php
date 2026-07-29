<?php
/**
 * Site settings stored in the database and edited from the portal.
 *
 * Everything here is optional: each getter falls back to the constants in
 * site.php, so the site renders identically before the portal is installed or
 * if the database goes away.
 */

require_once __DIR__ . '/site.php';

/**
 * Social networks offered in the portal, in the order they render.
 * The icon paths live here so the footer and the admin form draw from one set.
 */
const SOCIAL_NETWORKS = [
    'instagram' => [
        'label' => 'Instagram',
        'placeholder' => 'https://www.instagram.com/yourhandle',
        'icon' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/>',
    ],
    'facebook' => [
        'label' => 'Facebook',
        'placeholder' => 'https://www.facebook.com/yourpage',
        'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
    ],
    'linkedin' => [
        'label' => 'LinkedIn',
        'placeholder' => 'https://www.linkedin.com/company/yourcompany',
        'icon' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
    ],
    'youtube' => [
        'label' => 'YouTube',
        'placeholder' => 'https://www.youtube.com/@yourchannel',
        'icon' => '<path d="M22.5 6.9a2.8 2.8 0 0 0-2-2C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.5.4a2.8 2.8 0 0 0-2 2A29 29 0 0 0 1.1 12a29 29 0 0 0 .4 5.1 2.8 2.8 0 0 0 2 2c1.6.4 8.5.4 8.5.4s6.9 0 8.5-.4a2.8 2.8 0 0 0 2-2 29 29 0 0 0 .4-5.1 29 29 0 0 0-.4-5.1z"/><polygon points="9.8 15.4 15.5 12 9.8 8.6"/>',
    ],
    'tiktok' => [
        'label' => 'TikTok',
        'placeholder' => 'https://www.tiktok.com/@yourhandle',
        'icon' => '<path d="M15 3a5 5 0 0 0 5 5v3a8 8 0 0 1-5-1.8V15a6 6 0 1 1-6-6c.3 0 .7 0 1 .1v3.1A3 3 0 1 0 12 15V3z"/>',
    ],
    'pinterest' => [
        'label' => 'Pinterest',
        'placeholder' => 'https://www.pinterest.com/yourprofile',
        'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.5 20.5c-.3-1.6.1-3.4.5-4.9l.7-2.9s-.3-.6-.3-1.4c0-1.3.8-2.3 1.7-2.3.8 0 1.2.6 1.2 1.3 0 .8-.5 2-.8 3.2-.2.9.5 1.7 1.4 1.7 1.7 0 2.8-2.1 2.8-4.7 0-1.9-1.3-3.4-3.7-3.4-2.7 0-4.4 2-4.4 4.3 0 .8.2 1.3.6 1.8"/>',
    ],
    'x' => [
        'label' => 'X (Twitter)',
        'placeholder' => 'https://x.com/yourhandle',
        'icon' => '<path d="M3 3l7.5 9.8L3.4 21H5l6-6.7 5.1 6.7H21l-7.9-10.3L20.6 3H19l-5.6 6.2L8.8 3z"/>',
    ],
];

/**
 * Read a setting. Values are loaded once per request.
 * Returns $default when the key is missing, empty, or the table does not exist.
 */
function setting(string $key, string $default = ''): string
{
    static $cache = null;

    if ($cache === null) {
        global $pdo;
        $cache = [];

        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $cache = $pdo->query('SELECT `key`, `value` FROM settings')
                    ->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
            } catch (Throwable $e) {
                $cache = [];   // table missing — constants take over
            }
        }
    }

    $value = trim((string) ($cache[$key] ?? ''));
    return $value !== '' ? $value : $default;
}

/**
 * Social links that are actually filled in.
 * Falls back to the SITE_SOCIAL constants when nothing is saved yet.
 */
function social_links(): array
{
    $out = [];
    foreach (SOCIAL_NETWORKS as $key => $meta) {
        $url = setting('social_' . $key, SITE_SOCIAL[$key] ?? '');
        // A bare "https://www.instagram.com/" placeholder is not a real profile.
        if ($url !== '' && $url !== '#' && !preg_match('~^https?://(www\.)?[a-z]+\.[a-z]+/?$~i', $url)) {
            $out[$key] = ['label' => $meta['label'], 'url' => $url];
        }
    }
    return $out;
}

// ── Brand assets ─────────────────────────────────────────────────────────────

/** Filename of the uploaded logo, or '' when the built-in wordmark is used. */
function logo_file(): string
{
    return setting('logo');
}

/** Is a custom logo in place? Templates fall back to the "R" mark when not. */
function has_logo(): bool
{
    global $base_path;
    $f = logo_file();
    return $f !== '' && is_file(rtrim((string) $base_path, '/') . '/assets/uploads/' . $f);
}

/** Public URL of the uploaded logo (empty string if there isn't one). */
function logo_url(): string
{
    return has_logo() ? asset('assets/uploads/' . logo_file()) : '';
}

/**
 * The uploaded logo with its flat background cut to transparent — original
 * colours untouched. Most uploads arrive as an opaque rectangle (solid white
 * or solid black behind the artwork, not a pre-cut transparent PNG), which
 * shows up as a visible box the moment the logo sits on a panel that isn't
 * that exact colour. This derives a see-through version automatically and
 * caches it next to the original, so the admin never has to pre-cut anything.
 *
 * The background colour is read from the four corners rather than assumed —
 * works for a white canvas or a black one (or anything else) the same way.
 * Falls back to the original logo (SVGs — safely cutting one needs parsing
 * its markup, not rewriting pixels) or '' when there is no logo at all.
 */
function logo_url_cutout(): string
{
    global $base_path;

    if (!has_logo() || !extension_loaded('gd')) {
        return logo_url();
    }

    $file = logo_file();
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['png', 'webp'], true)) {
        return logo_url();   // SVG / JPEG: no safe automatic cutout
    }

    $dir    = rtrim((string) $base_path, '/') . '/assets/uploads/';
    $cached = 'cutout-' . md5($file) . '.png';

    if (!is_file($dir . $cached)) {
        $src = $ext === 'webp' ? @imagecreatefromwebp($dir . $file) : @imagecreatefrompng($dir . $file);
        if (!$src) {
            return logo_url();
        }

        $w = imagesx($src);
        $h = imagesy($src);

        $corner = static function (int $x, int $y) use ($src): array {
            $c = imagecolorat($src, $x, $y);
            return [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
        };
        $corners = [$corner(0, 0), $corner($w - 1, 0), $corner(0, $h - 1), $corner($w - 1, $h - 1)];

        // Corners disagree (a busy or already-transparent background) — safer
        // to leave every pixel's existing alpha alone than guess wrong.
        $spread = 0;
        foreach ($corners as $c) {
            foreach ($corners as $c2) {
                $spread = max($spread, abs($c[0] - $c2[0]) + abs($c[1] - $c2[1]) + abs($c[2] - $c2[2]));
            }
        }
        [$bgR, $bgG, $bgB] = $corners[0];
        $autoDetect = $spread < 30;

        $out = imagecreatetruecolor($w, $h);
        imagesavealpha($out, true);
        imagealphablending($out, false);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($src, $x, $y);
                $srcAlpha = ($c >> 24) & 0x7F;
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;

                $alpha = $srcAlpha;
                if ($autoDetect) {
                    $dist = abs($r - $bgR) + abs($g - $bgG) + abs($b - $bgB);
                    $bgAlpha = $dist < 12 ? 127 : ($dist > 60 ? 0 : (int) round(127 * (1 - ($dist - 12) / 48)));
                    $alpha = max($srcAlpha, $bgAlpha);
                }

                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));
            }
        }

        // A logo is usually handed over on a square canvas with the actual
        // artwork sitting in a horizontal band across the middle of it — the
        // canvas, not the artwork, was what a fixed display height was
        // measuring, so the logo rendered far smaller than it looked like it
        // should. Cropping to where the pixels actually are (plus a little
        // breathing room) means a given CSS height fills with logo, not
        // padding that came along for the ride.
        $minX = $w; $minY = $h; $maxX = -1; $maxY = -1;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ((imagecolorat($out, $x, $y) >> 24 & 0x7F) < 110) {
                    if ($x < $minX) $minX = $x;
                    if ($x > $maxX) $maxX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($y > $maxY) $maxY = $y;
                }
            }
        }

        if ($maxX > $minX && $maxY > $minY) {
            $pad = (int) round(max($maxX - $minX, $maxY - $minY) * 0.03);
            $cx0 = max(0, $minX - $pad);
            $cy0 = max(0, $minY - $pad);
            $cw  = min($w, $maxX + $pad) - $cx0;
            $ch  = min($h, $maxY + $pad) - $cy0;

            $trimmed = imagecreatetruecolor($cw, $ch);
            imagesavealpha($trimmed, true);
            imagealphablending($trimmed, false);
            imagefill($trimmed, 0, 0, imagecolorallocatealpha($trimmed, 0, 0, 0, 127));
            imagecopy($trimmed, $out, 0, 0, $cx0, $cy0, $cw, $ch);
            imagedestroy($out);
            $out = $trimmed;
        }

        imagepng($out, $dir . $cached, 9);
        @chmod($dir . $cached, 0644);
        imagedestroy($src);
        imagedestroy($out);
    }

    return asset('assets/uploads/' . $cached);
}

/**
 * Favicon URL. Prefers a dedicated square upload, then the logo, then the
 * bundled SVG mark — a wide logo makes a poor favicon, hence the separate slot.
 */
function favicon_url(): string
{
    global $base_url, $base_path;

    $icon = setting('favicon');
    if ($icon !== '' && is_file(rtrim((string) $base_path, '/') . '/assets/uploads/' . $icon)) {
        return asset('assets/uploads/' . $icon);
    }
    return has_logo() ? logo_url() : asset('assets/favicon.svg');
}

/** MIME type for a favicon <link>, derived from its extension. */
function favicon_type(): string
{
    $path = parse_url(favicon_url(), PHP_URL_PATH) ?: '';

    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'ico'  => 'image/x-icon',
        default => 'image/jpeg',
    };
}

/** GA4 measurement ID, e.g. G-XXXXXXXXXX. Empty means analytics is off. */
function analytics_id(): string
{
    return setting('ga4_id');
}

/** Search Console HTML-tag verification token (the content="" value). */
function gsc_token(): string
{
    return setting('gsc_verification');
}

/**
 * Should tracking actually run? Never on localhost — otherwise your own
 * development traffic pollutes the reports.
 */
function analytics_active(): bool
{
    global $base_url;
    return analytics_id() !== ''
        && !str_contains((string) $base_url, 'localhost')
        && !str_contains((string) $base_url, '127.0.0.1');
}
