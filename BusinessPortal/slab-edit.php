<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

$uploadDir = rtrim($base_path, '/') . '/assets/uploads';
$id = (int) ($_GET['id'] ?? 0);
$errors = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM slabs WHERE id = ?');
    $st->execute([$id]);
    $slab = $st->fetch();
    if (!$slab) {
        flash('error', 'That slab no longer exists.');
        header('Location: ' . portal_url('slabs.php'));
        exit;
    }
} else {
    $slab = [
        'id' => 0, 'name' => '', 'type' => array_key_first(SITE_MATERIALS), 'origin' => '',
        'finish' => '', 'thickness' => '3cm', 'size' => '', 'stock' => 0, 'image' => '',
        'sort_order' => 0, 'status' => 'published',
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();

    foreach (['name', 'type', 'origin', 'finish', 'thickness', 'size'] as $f) {
        $slab[$f] = trim((string) ($_POST[$f] ?? ''));
    }
    $slab['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    // Never negative — a negative count would read as "in stock" everywhere.
    $slab['stock'] = max(0, min(9999, (int) ($_POST['stock'] ?? 0)));
    $slab['status'] = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';

    if (mb_strlen($slab['name']) < 2) {
        $errors[] = 'Slab name is required.';
    }
    if (!isset(SITE_MATERIALS[$slab['type']])) {
        $errors[] = 'Choose a valid material category.';
    }

    $newImage = null;
    try {
        $newImage = save_upload('image', $uploadDir);
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }
    if (!$id && !$newImage) {
        $errors[] = 'Choose a slab photo.';
    }

    if (!$errors) {
        if ($id) {
            $image = $newImage ?: $slab['image'];
            $pdo->prepare(
                'UPDATE slabs SET name=?, type=?, origin=?, finish=?, thickness=?, size=?,
                        stock=?, image=?, sort_order=?, status=? WHERE id=?'
            )->execute([
                $slab['name'], $slab['type'], $slab['origin'], $slab['finish'], $slab['thickness'],
                $slab['size'], $slab['stock'], $image, $slab['sort_order'], $slab['status'], $id,
            ]);
            if ($newImage && $slab['image']) {
                delete_upload($slab['image'], $uploadDir);
            }
            flash('ok', 'Slab updated.');
        } else {
            $pdo->prepare(
                'INSERT INTO slabs (name, type, origin, finish, thickness, size, stock, image, sort_order, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $slab['name'], $slab['type'], $slab['origin'], $slab['finish'], $slab['thickness'],
                $slab['size'], $slab['stock'], $newImage, $slab['sort_order'], $slab['status'],
            ]);
            $id = (int) $pdo->lastInsertId();
            flash('ok', 'Slab added to inventory.');
        }

        header('Location: ' . portal_url('slab-edit.php?id=' . $id));
        exit;
    }
}

$pageTitle = $id ? 'Edit Slab' : 'Add Slab';
$navActive = 'slabs';
$pageAction = '<a href="' . portal_url('slabs.php') . '" class="btn-admin is-ghost">← All slabs</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<?php foreach ($errors as $err): ?>
    <div class="alert is-error"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="edit-form">
    <?= csrf_field() ?>

    <div class="edit-grid">
        <div class="edit-main">
            <div class="side-box">
                <h3>Slab Details</h3>

                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?= e($slab['name']) ?>"
                        placeholder="Taj Mahal" required />
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="type">Material</label>
                        <select id="type" name="type">
                            <?php foreach (SITE_MATERIALS as $k => $m): ?>
                                <option value="<?= e($k) ?>" <?= $slab['type'] === $k ? 'selected' : '' ?>>
                                    <?= e($m['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="origin">Origin</label>
                        <input type="text" id="origin" name="origin" value="<?= e($slab['origin']) ?>"
                            placeholder="Brazil" />
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="finish">Finish</label>
                        <input type="text" id="finish" name="finish" value="<?= e($slab['finish']) ?>"
                            placeholder="Polished" list="finishes" />
                        <datalist id="finishes">
                            <option>Polished</option>
                            <option>Honed</option>
                            <option>Leathered</option>
                            <option>Matte</option>
                            <option>Satin</option>
                            <option>Brushed</option>
                        </datalist>
                    </div>
                    <div class="field">
                        <label for="thickness">Thickness</label>
                        <input type="text" id="thickness" name="thickness" value="<?= e($slab['thickness']) ?>"
                            placeholder="3cm" list="thicknesses" />
                        <datalist id="thicknesses">
                            <option>2cm</option>
                            <option>3cm</option>
                            <option>6mm</option>
                            <option>12mm</option>
                            <option>20mm</option>
                        </datalist>
                    </div>
                </div>

                <div class="field">
                    <label for="size">Average Size <span class="hint">as shown on the inventory card</span></label>
                    <input type="text" id="size" name="size" value="<?= e($slab['size']) ?>"
                        placeholder='130&quot; × 80&quot;' />
                </div>
            </div>
        </div>

        <aside class="edit-side">
            <div class="side-box">
                <h3>Availability</h3>

                <div class="field">
                    <label for="stock">Slabs on the floor</label>
                    <input type="number" id="stock" name="stock" value="<?= (int) $slab['stock'] ?>"
                        min="0" max="9999" step="1" />
                    <p class="hint" style="margin-top:8px;">
                        Traders cannot request more than this number. Set it to <strong>0</strong> and the slab shows
                        as <em>Sold Out</em> on the website. This count drops on its own when you confirm a request —
                        only change it here for deliveries, walk-in sales or a stock count.
                    </p>
                </div>
            </div>

            <div class="side-box">
                <h3>Publish</h3>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published" <?= $slab['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $slab['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <div class="field">
                    <label for="sort_order">Sort order <span class="hint">lower shows first</span></label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) $slab['sort_order'] ?>" />
                </div>

                <button type="submit" class="btn-admin is-primary is-block">
                    <?= $id ? 'Save Changes' : 'Add to Inventory' ?>
                </button>

                <?php if ($id): ?>
                    <a href="<?= $base_url ?>slabs?type=<?= e($slab['type']) ?>" target="_blank" rel="noopener"
                        class="btn-admin is-ghost is-block">View on site ↗</a>
                <?php endif; ?>
            </div>

            <div class="side-box">
                <h3>Photo</h3>
                <div class="image-drop" data-image-drop>
                    <img src="<?= e(image_url($slab['image'])) ?>" alt="" data-image-preview
                        <?= $slab['image'] ? '' : 'hidden' ?> />
                    <div class="image-drop-empty" <?= $slab['image'] ? 'hidden' : '' ?>>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <span>Click to choose an image</span>
                    </div>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"
                        data-image-input <?= $id ? '' : 'required' ?> />
                </div>
                <p class="hint">Shoot the full slab face-on under even light. Around 1600px wide is plenty.</p>
            </div>
        </aside>
    </div>
</form>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
