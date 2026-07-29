<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/../inc/posts.php';

$uploadDir = rtrim($base_path, '/') . '/assets/uploads';
$id = (int) ($_GET['id'] ?? 0);
$errors = [];

// Load existing row, or start a blank one.
if ($id) {
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch();
    if (!$post) {
        flash('error', 'That article no longer exists.');
        header('Location: ' . portal_url('posts.php'));
        exit;
    }
} else {
    $post = [
        'id' => 0, 'slug' => '', 'title' => '', 'excerpt' => '', 'body' => '',
        'category' => array_key_first(BLOG_CATEGORIES), 'image' => '', 'og_image' => '',
        'read_minutes' => 3, 'status' => 'published', 'published_at' => date('Y-m-d'),
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'noindex' => 0,
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();

    $post['title']        = trim((string) ($_POST['title'] ?? ''));
    $post['excerpt']      = trim((string) ($_POST['excerpt'] ?? ''));
    $post['body']         = (string) ($_POST['body'] ?? '');
    $post['category']     = (string) ($_POST['category'] ?? '');
    $post['status']       = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';
    $post['published_at'] = (string) ($_POST['published_at'] ?? date('Y-m-d'));
    $slugInput            = trim((string) ($_POST['slug'] ?? ''));

    // Optional per-post SEO overrides — blank means "derive from the content".
    $post['meta_title']       = trim((string) ($_POST['meta_title'] ?? ''));
    $post['meta_description'] = trim((string) ($_POST['meta_description'] ?? ''));
    $post['meta_keywords']    = trim((string) ($_POST['meta_keywords'] ?? ''));
    $post['noindex']          = isset($_POST['noindex']) ? 1 : 0;

    if (mb_strlen($post['title']) < 3) {
        $errors[] = 'Title must be at least 3 characters.';
    }
    if (mb_strlen($post['excerpt']) < 20) {
        $errors[] = 'Excerpt should be at least 20 characters — it is the description search engines show.';
    }
    if (trim($post['body']) === '') {
        $errors[] = 'The article body cannot be empty.';
    }
    if (!isset(BLOG_CATEGORIES[$post['category']])) {
        $errors[] = 'Choose a valid category.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $post['published_at'])) {
        $errors[] = 'Publish date must be a valid date.';
    }

    // Image is optional on edit (keep the current one), required on create.
    $newImage = null;
    try {
        $newImage = save_upload('image', $uploadDir);
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }
    if (!$id && !$newImage) {
        $errors[] = 'Choose a cover image.';
    }

    if (!$errors) {
        $slug = unique_slug($pdo, 'posts', slugify($slugInput !== '' ? $slugInput : $post['title']), $id ?: null);
        $minutes = reading_minutes($post['body']);

        if ($id) {
            $image = $newImage ?: $post['image'];
            $pdo->prepare(
                'UPDATE posts SET slug=?, title=?, excerpt=?, body=?, category=?, image=?,
                        read_minutes=?, status=?, published_at=?,
                        meta_title=?, meta_description=?, meta_keywords=?, noindex=? WHERE id=?'
            )->execute([
                $slug, $post['title'], $post['excerpt'], $post['body'], $post['category'],
                $image, $minutes, $post['status'], $post['published_at'],
                $post['meta_title'], $post['meta_description'], $post['meta_keywords'], $post['noindex'], $id,
            ]);

            if ($newImage && $post['image']) {
                delete_upload($post['image'], $uploadDir);   // replaced — drop the old file
            }
            flash('ok', 'Article updated.');
        } else {
            $pdo->prepare(
                'INSERT INTO posts (slug, title, excerpt, body, category, image, read_minutes, status, published_at,
                                    meta_title, meta_description, meta_keywords, noindex)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $slug, $post['title'], $post['excerpt'], $post['body'], $post['category'],
                $newImage, $minutes, $post['status'], $post['published_at'],
                $post['meta_title'], $post['meta_description'], $post['meta_keywords'], $post['noindex'],
            ]);
            $id = (int) $pdo->lastInsertId();
            flash('ok', 'Article published.');
        }

        regenerate_sitemap();
        header('Location: ' . portal_url('post-edit.php?id=' . $id));
        exit;
    }
}

$pageTitle = $id ? 'Edit Article' : 'New Article';
$navActive = 'posts';
$pageAction = '<a href="' . portal_url('posts.php') . '" class="btn-admin is-ghost">← All articles</a>';
require __DIR__ . '/inc/layout-top.php';
?>

<?php foreach ($errors as $err): ?>
    <div class="alert is-error"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="edit-form">
    <?= csrf_field() ?>

    <div class="edit-grid">
        <!-- MAIN COLUMN -->
        <div class="edit-main">
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= e($post['title']) ?>" required
                    data-slug-source />
            </div>

            <div class="field">
                <label for="slug">URL Slug <span class="hint">leave blank to build it from the title</span></label>
                <div class="slug-field">
                    <span class="slug-prefix"><?= e(rtrim($base_url, '/')) ?>/blog/</span>
                    <input type="text" id="slug" name="slug" value="<?= e($post['slug']) ?>" data-slug-target />
                </div>
            </div>

            <div class="field">
                <label for="excerpt">
                    Excerpt
                    <span class="hint">shown on cards and used as the meta description — aim for 120–160 characters</span>
                </label>
                <textarea id="excerpt" name="excerpt" rows="3" required maxlength="300"
                    data-counter="excerptCount"><?= e($post['excerpt']) ?></textarea>
                <span class="counter" id="excerptCount"></span>
            </div>

            <div class="field">
                <label for="body">
                    Body
                    <span class="hint">blank line = new paragraph · start a line with <code>## </code> for a heading</span>
                </label>
                <textarea id="body" name="body" rows="22" class="mono" required><?= e($post['body']) ?></textarea>
            </div>

            <!-- SEO ------------------------------------------------------- -->
            <div class="side-box seo-box">
                <h3>Search Engine Listing</h3>

                <span data-site-name hidden><?= e(site_name()) ?></span>
                <div class="serp-preview" aria-hidden="true">
                    <span class="serp-url"><?= e(rtrim($base_url, '/')) ?>/blog/<span data-serp-slug><?= e($post['slug'] ?: 'your-post') ?></span></span>
                    <span class="serp-title" data-serp-title></span>
                    <span class="serp-desc" data-serp-desc></span>
                </div>

                <div class="field">
                    <label for="meta_title">
                        Meta title
                        <span class="hint">leave blank to use the article title · aim for under 60 characters</span>
                    </label>
                    <input type="text" id="meta_title" name="meta_title" maxlength="255"
                        value="<?= e($post['meta_title']) ?>" data-counter="metaTitleCount"
                        data-counter-ideal="60" placeholder="<?= e($post['title'] ?: 'Article title') ?>" />
                    <span class="counter" id="metaTitleCount"></span>
                </div>

                <div class="field">
                    <label for="meta_description">
                        Meta description
                        <span class="hint">leave blank to use the excerpt · aim for 120–160 characters</span>
                    </label>
                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="320"
                        data-counter="metaDescCount"
                        placeholder="<?= e(mb_substr($post['excerpt'] ?: 'Falls back to the excerpt above.', 0, 120)) ?>"><?= e($post['meta_description']) ?></textarea>
                    <span class="counter" id="metaDescCount"></span>
                </div>

                <div class="field">
                    <label for="meta_keywords">
                        Focus keywords
                        <span class="hint">comma separated — for your own reference; Google ignores the keywords tag</span>
                    </label>
                    <input type="text" id="meta_keywords" name="meta_keywords" maxlength="255"
                        value="<?= e($post['meta_keywords']) ?>"
                        placeholder="quartzite supplier dallas, bookmatched slabs" />
                </div>

                <label class="check">
                    <input type="checkbox" name="noindex" value="1" <?= !empty($post['noindex']) ? 'checked' : '' ?> />
                    <span>Hide from search engines — adds <code>noindex</code> and drops it from the sitemap</span>
                </label>
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="edit-side">
            <div class="side-box">
                <h3>Publish</h3>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <div class="field">
                    <label for="published_at">Publish date</label>
                    <input type="date" id="published_at" name="published_at"
                        value="<?= e($post['published_at']) ?>" required />
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <?php foreach (BLOG_CATEGORIES as $k => $label): ?>
                            <option value="<?= e($k) ?>" <?= $post['category'] === $k ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-admin is-primary is-block">
                    <?= $id ? 'Save Changes' : 'Publish Article' ?>
                </button>

                <?php if ($id): ?>
                    <a href="<?= $base_url ?>blog/<?= e($post['slug']) ?>" target="_blank" rel="noopener"
                        class="btn-admin is-ghost is-block">View on site ↗</a>
                <?php endif; ?>
            </div>

            <div class="side-box">
                <h3>Cover Image</h3>
                <div class="image-drop" data-image-drop>
                    <img src="<?= e(image_url($post['image'])) ?>" alt="" data-image-preview
                        <?= $post['image'] ? '' : 'hidden' ?> />
                    <div class="image-drop-empty" <?= $post['image'] ? 'hidden' : '' ?>>
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
                <p class="hint">JPG, PNG, WebP or AVIF · max 6 MB. Export around 1600px wide for the best balance of
                    quality and speed.</p>
            </div>
        </aside>
    </div>
</form>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
