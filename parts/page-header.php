<?php
/**
 * Shared interior-page header. Every page below the homepage uses this so the
 * breadcrumb, eyebrow, title and lead paragraph line up identically.
 *
 * Set $hero before including:
 *   $hero = [
 *     'crumbs'  => ['Gallery'],                  // trailing crumbs after Home;
 *                                                // ['Blog' => 'blog', 'Trends'] to link one
 *     'eyebrow' => 'Finished Work',              // optional small label
 *     'title'   => 'Finished Projects',          // required
 *     'lead'    => 'Sentence under the title.',  // optional
 *     'aside'   => '<a …>optional right slot</a>',
 *     'media'   => '<img …>',                    // splits the row in two and
 *                                                // sits the picture beside the
 *                                                // title instead of under it
 *     'actions' => '<a …>Call to action</a>',
 *   ];
 *   include __DIR__ . '/../parts/page-header.php';
 */

$hero = $hero ?? [];
$heroCrumbs = $hero['crumbs'] ?? [];
?>
<header class="page-header">
    <ol class="breadcrumb">
        <li><a href="<?= $base_url ?>">Home</a></li>
        <?php foreach ($heroCrumbs as $label => $slug): ?>
            <?php if (is_int($label)): ?>
                <li><?= e($slug) ?></li>
            <?php else: ?>
                <li><a href="<?= $base_url . ltrim($slug, '/') ?>"><?= e($label) ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>

    <div class="page-header-row<?= !empty($hero['media']) ? ' has-media' : '' ?>">
        <?php if (!empty($hero['media'])): ?>
            <div class="page-header-media"><?= $hero['media'] ?></div>
        <?php endif; ?>

        <div class="page-header-main">
            <?php if (!empty($hero['eyebrow'])): ?>
                <span class="section-label"><?= e($hero['eyebrow']) ?></span>
            <?php endif; ?>

            <h1><?= $hero['title'] ?? '' ?></h1>

            <?php if (!empty($hero['lead'])): ?>
                <p><?= $hero['lead'] ?></p>
            <?php endif; ?>

            <?php if (!empty($hero['actions'])): ?>
                <div class="page-header-actions"><?= $hero['actions'] ?></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($hero['aside'])): ?>
            <div class="page-header-aside"><?= $hero['aside'] ?></div>
        <?php endif; ?>
    </div>
</header>
