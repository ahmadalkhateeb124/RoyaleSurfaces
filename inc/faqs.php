<?php
/**
 * FAQ content, managed from the portal.
 *
 * Falls back to the SITE_FAQ constant when the table is missing or the database
 * is unreachable, so the homepage and /faq keep rendering either way.
 */

require_once __DIR__ . '/site.php';

/** Published questions in display order. */
function faqs_all(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    global $pdo;
    $cache = [];

    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $cache = $pdo->query(
                "SELECT id, question, answer, group_name, show_on_home
                 FROM faqs WHERE status = 'published'
                 ORDER BY group_name, sort_order, id"
            )->fetchAll();
        } catch (Throwable $e) {
            $cache = [];
        }
    }

    if (!$cache) {
        // No table yet — mirror the constant into the same shape.
        foreach (SITE_FAQ as $i => $f) {
            $cache[] = [
                'id'           => $i,
                'question'     => $f['q'],
                'answer'       => $f['a'],
                'group_name'   => 'General',
                'show_on_home' => 1,
            ];
        }
    }

    return $cache;
}

/**
 * Questions keyed by group, groups in the order they first appear.
 * Used by /faq.
 */
function faqs_grouped(): array
{
    $out = [];
    foreach (faqs_all() as $f) {
        $out[$f['group_name']][] = $f;
    }
    return $out;
}

/** The subset flagged to appear on the homepage. */
function faqs_home(int $limit = 7): array
{
    $picked = array_values(array_filter(faqs_all(), fn($f) => !empty($f['show_on_home'])));
    return array_slice($picked ?: faqs_all(), 0, $limit);
}

/** FAQPage structured data for any set of questions. */
function faqs_schema(array $items): array
{
    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type'          => 'Question',
            'name'           => $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
        ], array_values($items)),
    ];
}
