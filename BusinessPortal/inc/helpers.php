<?php
/**
 * Shared admin helpers: slugs, image uploads, body parsing, sitemap ping.
 */

declare(strict_types=1);

/** URL-safe slug. Falls back to a timestamp if the title is all punctuation. */
function slugify(string $text): string
{
    $text = trim($text);
    // Transliterate accents where the extension is available.
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($t !== false) {
            $text = $t;
        }
    }
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '');
    $text = trim($text, '-');

    return $text !== '' ? substr($text, 0, 170) : 'item-' . time();
}

/** Make a slug unique within a table, ignoring the row being edited. */
function unique_slug(PDO $pdo, string $table, string $slug, ?int $ignoreId = null): string
{
    $base = $slug;
    $i = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE slug = ?" . ($ignoreId ? ' AND id <> ?' : '');
        $st = $pdo->prepare($sql);
        $st->execute($ignoreId ? [$slug, $ignoreId] : [$slug]);

        if ((int) $st->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/**
 * Editor text → the [tag, text] pairs the front end renders.
 * A line beginning with "## " becomes a heading; blank lines split paragraphs.
 */
function parse_body(string $text): array
{
    $blocks = preg_split('/\n\s*\n/', trim(str_replace("\r\n", "\n", $text))) ?: [];
    $out = [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (str_starts_with($block, '## ')) {
            $out[] = ['h2', trim(substr($block, 3))];
        } else {
            $out[] = ['p', preg_replace('/\s*\n\s*/', ' ', $block)];
        }
    }
    return $out;
}

/** Rough reading time, rounded up, never below one minute. */
function reading_minutes(string $text): int
{
    return max(1, (int) ceil(str_word_count(strip_tags($text)) / 200));
}

// ── Image upload ─────────────────────────────────────────────────────────────

const UPLOAD_MAX_BYTES = 6 * 1024 * 1024;   // 6 MB
const UPLOAD_ALLOWED = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

/**
 * Handle one uploaded image and return its stored filename.
 *
 * Returns null when no file was submitted. Throws on a rejected file so the
 * caller can surface the reason. Validation is by real MIME type, not by the
 * client-supplied name — an "image.jpg" that is really a PHP script must not
 * land in a web-served directory.
 */
function save_upload(string $field, string $uploadDir): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is larger than the server allows.',
            UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted — try again.',
            default                                   => 'Upload failed (error ' . $file['error'] . ').',
        });
    }

    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Image must be under ' . (UPLOAD_MAX_BYTES / 1024 / 1024) . ' MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);

    if (!isset(UPLOAD_ALLOWED[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WebP and AVIF images are accepted.');
    }

    // Confirm it really decodes as an image.
    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('That file is not a readable image.');
    }

    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Cannot create the upload directory.');
    }

    $base = slugify(pathinfo($file['name'], PATHINFO_FILENAME)) ?: 'image';
    $name = $base . '-' . bin2hex(random_bytes(4)) . '.' . UPLOAD_ALLOWED[$mime];

    if (!move_uploaded_file($file['tmp_name'], rtrim($uploadDir, '/') . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return $name;
}

/**
 * Strip anything executable from an SVG before it is stored.
 *
 * A logo is best supplied as SVG, but SVG is a document format: it can carry
 * <script>, event handlers and external references. Rendered through <img> a
 * browser will not run those, yet the file is still reachable directly at its
 * own URL — so it gets cleaned on the way in rather than trusted.
 */
function sanitize_svg(string $svg): string
{
    // Remove script/foreignObject blocks entirely.
    $svg = preg_replace('~<\s*(script|foreignObject|iframe|embed|object)\b[^>]*>.*?<\s*/\s*\1\s*>~is', '', $svg) ?? '';
    $svg = preg_replace('~<\s*(script|foreignObject|iframe|embed|object)\b[^>]*/?>~i', '', $svg) ?? '';

    // Inline event handlers (onload=, onclick=, …).
    $svg = preg_replace('~\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)~is', '', $svg) ?? '';

    // javascript: and data: URLs inside href/xlink:href/src.
    $svg = preg_replace('~(href|xlink:href|src)\s*=\s*(["\']?)\s*(javascript|data)\s*:~i', '$1=$2#', $svg) ?? '';

    // Entity/DOCTYPE tricks used for XXE.
    $svg = preg_replace('~<!DOCTYPE.*?>~is', '', $svg) ?? '';
    $svg = preg_replace('~<!ENTITY.*?>~is', '', $svg) ?? '';

    return trim($svg);
}

/**
 * Save a brand asset (logo or favicon). Accepts the raster types plus SVG,
 * which save_upload() deliberately rejects for content images.
 */
function save_brand_upload(string $field, string $uploadDir): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Non-SVG uploads go through the standard validated path.
    if ($ext !== 'svg') {
        return save_upload($field, $uploadDir);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error ' . $file['error'] . ').');
    }
    if ($file['size'] > 1024 * 1024) {
        throw new RuntimeException('SVG must be under 1 MB.');
    }

    $raw = (string) file_get_contents($file['tmp_name']);
    if (!preg_match('~<svg[\s>]~i', $raw)) {
        throw new RuntimeException('That file is not a valid SVG.');
    }

    $clean = sanitize_svg($raw);
    if ($clean === '' || !preg_match('~<svg[\s>]~i', $clean)) {
        throw new RuntimeException('That SVG could not be processed safely.');
    }

    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Cannot create the upload directory.');
    }

    $name = (slugify(pathinfo($file['name'], PATHINFO_FILENAME)) ?: 'brand')
        . '-' . bin2hex(random_bytes(4)) . '.svg';

    if (@file_put_contents(rtrim($uploadDir, '/') . '/' . $name, $clean) === false) {
        throw new RuntimeException('Could not save the SVG.');
    }

    return $name;
}

/**
 * Delete an uploaded image. Only touches files inside the uploads directory —
 * seeded images that ship with the theme live elsewhere and are left alone.
 */
function delete_upload(?string $name, string $uploadDir): void
{
    if (!$name) {
        return;
    }
    $path = rtrim($uploadDir, '/') . '/' . basename($name);
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Resolve a stored image name to a public URL. Uploaded files live in
 * assets/uploads/; the originals that shipped with the site are in assets/images/.
 */
function image_url(string $name): string
{
    global $base_path;

    if ($name === '') {
        return asset('assets/images/hero-stone.jpg');
    }

    // Versioned like the public side — otherwise a replaced photo keeps showing
    // the old thumbnail in the portal for a year.
    $dir = is_file(rtrim($base_path, '/') . '/assets/uploads/' . $name) ? 'uploads' : 'images';

    return asset('assets/' . $dir . '/' . $name);
}

// ── Sitemap ──────────────────────────────────────────────────────────────────

/**
 * Write sitemap.xml to disk and tell the search engines it moved.
 *
 * /sitemap.xml is already generated live by sitemap.php, so this is belt and
 * braces: it gives you a static copy you can inspect, and pings the crawlers
 * whenever content actually changes rather than on every page view.
 */
function regenerate_sitemap(): array
{
    global $base_path, $base_url;

    $result = ['written' => false, 'pinged' => [], 'error' => null];

    try {
        ob_start();
        include rtrim($base_path, '/') . '/sitemap.php';
        $xml = (string) ob_get_clean();

        if (!str_contains($xml, '<urlset')) {
            throw new RuntimeException('Generated sitemap looked malformed; not written.');
        }

        $target = rtrim($base_path, '/') . '/sitemap.xml';
        if (@file_put_contents($target, $xml) === false) {
            throw new RuntimeException('Could not write sitemap.xml — check folder permissions.');
        }
        $result['written'] = true;

        // Only ping live search engines from a real domain.
        if (!str_contains($base_url, 'localhost') && !str_contains($base_url, '127.0.0.1')) {
            $loc = rawurlencode(rtrim($base_url, '/') . '/sitemap.xml');
            foreach (['https://www.google.com/ping?sitemap=', 'https://www.bing.com/ping?sitemap='] as $endpoint) {
                $ctx = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true]]);
                if (@file_get_contents($endpoint . $loc, false, $ctx) !== false) {
                    $result['pinged'][] = parse_url($endpoint, PHP_URL_HOST);
                }
            }
        }
    } catch (Throwable $ex) {
        $result['error'] = $ex->getMessage();
    }

    return $result;
}
