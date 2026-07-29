<?php
/**
 * Reference pages. Each has a different shape — a glossary is a term list, a
 * comparison is a table — so this file branches on $refSlug rather than trying
 * to force one template over all of them.
 */
require_once __DIR__ . '/../inc/conn.php';
require_once __DIR__ . '/../inc/content-pages.php';

$refSlug = $refSlug ?? '';
$ref = REFERENCE_PAGES[$refSlug] ?? null;
if (!$ref) {
    return;
}

$leads = [
    'glossary'     => 'The vocabulary of the stone trade, in plain English. If a fabricator or supplier uses a word you have not met, it is probably here.',
    'care'         => 'What each material needs, what damages it, and what to tell a client at handover. Most surface failures come from cleaning products, not from the stone.',
    'buying-guide' => 'Buying stone is not like buying an appliance. Here is the whole process, in order, with the decisions that actually change the outcome.',
    'finishes'     => 'The same stone looks and behaves very differently depending on how the surface is finished. This is what each one does.',
    'thickness'    => 'Thickness is a structural and budget decision, not an aesthetic one — and it narrows which slabs are available to you.',
    'compare'      => 'Six materials side by side on the properties that decide whether a surface survives the room it is installed in.',
    'faq'          => 'Practical answers about visiting, ordering, holds, delivery and choosing material.',
];
?>

<main id="main">
  <div class="container">

    <?php
    $hero = [
        'crumbs'  => ['Resources' => 'glossary', strip_tags(html_entity_decode($ref['title']))],
        'eyebrow' => 'Resources',
        'title'   => $ref['title'],
        'lead'    => $leads[$refSlug] ?? '',
        // A visible freshness signal — both for a reader deciding whether to
        // trust a thickness/pricing figure, and for AI answer engines, which
        // weigh dated content more heavily than an undated page saying the
        // same thing.
        'aside'   => '<span class="page-reviewed">Reviewed ' . date('F Y', strtotime(SITE_GUIDES_REVIEWED)) . '</span>',
    ];
    include __DIR__ . '/../parts/page-header.php';

    include __DIR__ . '/reference/' . $refSlug . '.php';
    ?>

  </div>
</main>

<section class="cta-banner">
  <div class="container">
    <h2>Still Deciding?</h2>
    <p>Walk our <?= e(site_city()) ?> gallery and see the material in person — it settles most questions in ten minutes.</p>
    <a href="<?= $base_url ?>contact" class="btn-dark">Book a Visit</a>
  </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'WebPage',
    '@id'           => $base_url . $refSlug,
    'name'          => strip_tags(html_entity_decode($ref['title'])),
    'description'   => strip_tags(html_entity_decode($ref['meta'])),
    'url'           => $base_url . $refSlug,
    'dateModified'  => SITE_GUIDES_REVIEWED,
    'isPartOf'      => ['@type' => 'WebSite', 'name' => site_name(), 'url' => $base_url],
    'about'         => ['@type' => 'Thing', 'name' => 'Natural stone slabs'],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
