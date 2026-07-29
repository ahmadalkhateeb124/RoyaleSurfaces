<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';

$uploadDir = rtrim($base_path, '/') . '/assets/uploads';
$id = (int) ($_GET['id'] ?? 0);
$errors = [];

$spaces = ['Residential Kitchen', 'Bathroom', 'Exterior', 'Commercial', 'Feature Wall', 'Flooring'];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$id]);
    $proj = $st->fetch();
    if (!$proj) {
        flash('error', 'That project no longer exists.');
        header('Location: ' . portal_url('projects.php'));
        exit;
    }
} else {
    $proj = [
        'id' => 0, 'title' => '', 'space' => $spaces[0], 'material' => '',
        'type' => array_key_first(SITE_MATERIALS), 'location' => '', 'body' => '',
        'image' => '', 'is_feature' => 0, 'sort_order' => 0, 'status' => 'published',
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();

    foreach (['title', 'space', 'material', 'type', 'location', 'body'] as $f) {
        $proj[$f] = trim((string) ($_POST[$f] ?? ''));
    }
    $proj['is_feature'] = isset($_POST['is_feature']) ? 1 : 0;
    $proj['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    $proj['status'] = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';

    if (mb_strlen($proj['title']) < 3) {
        $errors[] = 'Project title is required.';
    }
    if (!isset(SITE_MATERIALS[$proj['type']])) {
        $errors[] = 'Choose a valid material category.';
    }

    $newImage = null;
    try {
        $newImage = save_upload('image', $uploadDir);
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }
    if (!$id && !$newImage) {
        $errors[] = 'Choose a project photo.';
    }

    if (!$errors) {
        if ($id) {
            $image = $newImage ?: $proj['image'];
            $pdo->prepare(
                'UPDATE projects SET title=?, space=?, material=?, type=?, location=?, body=?,
                        image=?, is_feature=?, sort_order=?, status=? WHERE id=?'
            )->execute([
                $proj['title'], $proj['space'], $proj['material'], $proj['type'], $proj['location'],
                $proj['body'], $image, $proj['is_feature'], $proj['sort_order'], $proj['status'], $id,
            ]);
            if ($newImage && $proj['image']) {
                delete_upload($proj['image'], $uploadDir);
            }
            flash('ok', 'Project updated.');
        } else {
            $pdo->prepare(
                'INSERT INTO projects (title, space, material, type, location, body, image, is_feature, sort_order, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $proj['title'], $proj['space'], $proj['material'], $proj['type'], $proj['location'],
                $proj['body'], $newImage, $proj['is_feature'], $proj['sort_order'], $proj['status'],
            ]);
            $id = (int) $pdo->lastInsertId();
            flash('ok', 'Project added to the gallery.');
        }

        regenerate_sitemap();
        header('Location: ' . portal_url('project-edit.php?id=' . $id));
        exit;
    }
}

$pageTitle = $id ? 'Edit Project' : 'Add Project';
$navActive = 'projects';
$pageAction = '<a href="' . portal_url('projects.php') . '" class="btn-admin is-ghost">← All projects</a>';
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
                <h3>Project Details</h3>

                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?= e($proj['title']) ?>"
                        placeholder="Monolithic Kitchen Island" required />
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="space">Space</label>
                        <input type="text" id="space" name="space" value="<?= e($proj['space']) ?>" list="spaces" />
                        <datalist id="spaces">
                            <?php foreach ($spaces as $s): ?>
                                <option><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="field">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?= e($proj['location']) ?>"
                            placeholder="Highland Park, TX" />
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="material">Material used <span class="hint">shown on the tile</span></label>
                        <input type="text" id="material" name="material" value="<?= e($proj['material']) ?>"
                            placeholder="Taj Mahal Quartzite" />
                    </div>
                    <div class="field">
                        <label for="type">Category <span class="hint">drives the gallery filter</span></label>
                        <select id="type" name="type">
                            <?php foreach (SITE_MATERIALS as $k => $m): ?>
                                <option value="<?= e($k) ?>" <?= $proj['type'] === $k ? 'selected' : '' ?>>
                                    <?= e($m['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="body">Description <span class="hint">shown in the lightbox</span></label>
                    <textarea id="body" name="body" rows="5"><?= e($proj['body']) ?></textarea>
                </div>
            </div>
        </div>

        <aside class="edit-side">
            <div class="side-box">
                <h3>Publish</h3>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published" <?= $proj['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $proj['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <label class="check">
                    <input type="checkbox" name="is_feature" value="1" <?= $proj['is_feature'] ? 'checked' : '' ?> />
                    <span>Featured — spans two columns in the grid</span>
                </label>

                <div class="field">
                    <label for="sort_order">Sort order <span class="hint">lower shows first</span></label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) $proj['sort_order'] ?>" />
                </div>

                <button type="submit" class="btn-admin is-primary is-block">
                    <?= $id ? 'Save Changes' : 'Add to Gallery' ?>
                </button>

                <?php if ($id): ?>
                    <a href="<?= $base_url ?>gallery?type=<?= e($proj['type']) ?>" target="_blank" rel="noopener"
                        class="btn-admin is-ghost is-block">View on site ↗</a>
                <?php endif; ?>
            </div>

            <div class="side-box">
                <h3>Photo</h3>
                <div class="image-drop" data-image-drop>
                    <img src="<?= e(image_url($proj['image'])) ?>" alt="" data-image-preview
                        <?= $proj['image'] ? '' : 'hidden' ?> />
                    <div class="image-drop-empty" <?= $proj['image'] ? 'hidden' : '' ?>>
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
                <p class="hint">Landscape works best — the grid crops to 4:3, featured tiles to 8:3.</p>
            </div>
        </aside>
    </div>
</form>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
