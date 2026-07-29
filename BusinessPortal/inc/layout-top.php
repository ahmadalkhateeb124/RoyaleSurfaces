<?php
/**
 * Admin chrome: head + top navigation bar. Set $pageTitle and $navActive
 * before including. Close with layout-bottom.php.
 */
$pageTitle = $pageTitle ?? 'Dashboard';
$navActive = $navActive ?? '';

// Badge counts. Each is wrapped separately so a missing table only silences
// its own badge instead of all of them.
$unreadInquiries = $pendingAccounts = $newOrders = 0;
try { $unreadInquiries = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_spam = 0 AND status = 'new'")->fetchColumn(); } catch (Throwable $e) {}
try { $pendingAccounts = (int) $pdo->query("SELECT COUNT(*) FROM trade_accounts WHERE status = 'pending'")->fetchColumn(); } catch (Throwable $e) {}
try { $newOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn(); } catch (Throwable $e) {}

$nav = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php',    'icon' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
    'posts'     => ['label' => 'Blog',      'href' => 'posts.php',    'icon' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'],
    'slabs'     => ['label' => 'Inventory', 'href' => 'slabs.php',    'icon' => '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>'],
    'projects'  => ['label' => 'Gallery',   'href' => 'projects.php', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'],
    'orders'    => ['label' => 'Requests', 'href' => 'orders.php',   'icon' => '<path d="M9 11H5a2 2 0 0 0-2 2v7h18v-7a2 2 0 0 0-2-2h-4"/><path d="M9 11V5a3 3 0 0 1 6 0v6"/>'],
    'accounts'  => ['label' => 'Accounts', 'href' => 'accounts.php', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/>'],
    'faqs'      => ['label' => 'FAQ',       'href' => 'faqs.php',      'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
    'inquiries' => ['label' => 'Inquiries', 'href' => 'inquiries.php', 'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'],
    'seo'       => ['label' => 'SEO',       'href' => 'seo.php',      'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
];

$initial = strtoupper(substr(auth_user()['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?= e($pageTitle) ?> — Royale Surfaces Portal</title>
    <link rel="icon" href="<?= e(favicon_url()) ?>" type="<?= e(favicon_type()) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="<?= e(asset('BusinessPortal/assets/admin.css')) ?>" />
</head>

<body class="admin">

    <a href="#adminMain" class="skip-link">Skip to content</a>

    <!-- MASTHEAD -->
    <header class="masthead">
        <div class="masthead-inner">
            <a href="<?= portal_url('index.php') ?>" class="masthead-brand">
                <?php if (has_logo()): ?>
                    <img src="<?= e(logo_url()) ?>" alt="<?= e(site_name()) ?>" class="masthead-logo" />
                <?php else: ?>
                    <span class="auth-logo">R</span>
                <?php endif; ?>
                <span class="masthead-brand-text">
                    <?= e(site_name()) ?>
                    <small>Content Portal</small>
                </span>
            </a>

            <nav class="masthead-nav" id="mastNav" aria-label="Sections">
                <?php foreach ($nav as $key => $item): ?>
                    <a href="<?= portal_url($item['href']) ?>" class="<?= $navActive === $key ? 'active' : '' ?>"
                       <?= $navActive === $key ? 'aria-current="page"' : '' ?>>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><?= $item['icon'] ?></svg>
                        <?= e($item['label']) ?>
                        <?php
                        $badge = ['inquiries' => $unreadInquiries, 'accounts' => $pendingAccounts, 'orders' => $newOrders][$key] ?? 0;
                        ?>
                        <?php if ($badge > 0): ?>
                            <span class="nav-badge"><?= $badge ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>

                <!-- Repeated inside the mobile drawer, hidden on desktop -->
                <a href="<?= portal_url('profile.php') ?>" class="mast-mobile-only <?= $navActive === 'profile' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Profile
                </a>
                <a href="<?= $base_url ?>" target="_blank" rel="noopener" class="mast-mobile-only">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    View live site
                </a>
                <a href="<?= portal_url('logout.php') ?>" class="mast-mobile-only is-danger">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Sign out
                </a>
            </nav>

            <div class="masthead-right">
                <a href="<?= $base_url ?>" target="_blank" rel="noopener" class="mast-icon"
                    aria-label="View live site" title="View live site">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                </a>

                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-trigger" id="userTrigger" aria-expanded="false"
                        aria-haspopup="true" aria-controls="userDrop">
                        <span class="user-avatar"><?= e($initial) ?></span>
                        <span class="user-name"><?= e(auth_user()['name']) ?></span>
                        <svg class="user-chev" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>

                    <div class="user-drop" id="userDrop" hidden>
                        <div class="user-drop-head">
                            <strong><?= e(auth_user()['name']) ?></strong>
                            <small><?= e(auth_user()['username']) ?></small>
                        </div>
                        <a href="<?= portal_url('profile.php') ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Profile &amp; password
                        </a>
                        <a href="<?= portal_url('logout.php') ?>" class="is-danger">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Sign out
                        </a>
                    </div>
                </div>

                <button type="button" class="nav-toggle" id="navToggle" aria-label="Open menu"
                    aria-controls="mastNav" aria-expanded="false">
                    <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span>
                </button>
            </div>
        </div>
    </header>

    <div class="nav-scrim" id="navScrim" hidden></div>

    <!-- PAGE -->
    <div class="admin-shell">
        <div class="page-bar">
            <h1><?= e($pageTitle) ?></h1>
            <?php if (!empty($pageAction)): ?>
                <div class="topbar-action"><?= $pageAction ?></div>
            <?php endif; ?>
        </div>

        <main class="admin-main" id="adminMain">

            <?php
            // Flash messages surface as toasts rather than inline alerts, so a
            // "Saved." confirmation does not shove the whole page down.
            $flashes = flash_take();
            ?>
            <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="false">
                <?php foreach ($flashes as $f): ?>
                    <?php
                    $icon = $f['type'] === 'error'
                        ? '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
                        : '<path d="M20 6L9 17l-5-5"/>';
                    ?>
                    <div class="toast is-<?= e($f['type']) ?>" role="status">
                        <span class="toast-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg>
                        </span>
                        <p><?= $f['message'] ?></p>
                        <button type="button" class="toast-close" aria-label="Dismiss">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <line x1="6" y1="6" x2="18" y2="18" />
                                <line x1="18" y1="6" x2="6" y2="18" />
                            </svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
