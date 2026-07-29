<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="footer-grid">

            <div class="footer-brand">
                <a href="<?= $base_url ?>" class="logo" aria-label="<?= e(site_name()) ?> — home">
                    <?php include __DIR__ . '/brand.php'; ?>
                </a>
                <p class="footer-desc">Premium natural stone slab supplier serving fabricators, contractors, designers
                    and homeowners across Texas. Wholesale pricing, full-slab inventory.</p>
                <?php $socials = social_links();   // only networks with a URL saved in the portal ?>
                <?php if ($socials): ?>
                    <ul class="footer-social" aria-label="Social media">
                        <?php foreach ($socials as $key => $s): ?>
                            <li>
                                <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer"
                                    aria-label="<?= e($s['label']) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><?= SOCIAL_NETWORKS[$key]['icon'] ?></svg>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div>
                <h4>Contact</h4>
                <ul class="footer-contact">
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer">
                            <?= e(site_street()) ?><br /><?= e(site_city()) ?>, <?= e(site_state()) ?>
                            <?= e(site_zip()) ?>
                        </a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.15a16 16 0 0 0 6.09 6.09l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <a href="<?= e(tel_link(site_phone())) ?>"><?= e(site_phone()) ?></a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        <a href="mailto:<?= e(site_email()) ?>"><?= e(site_email()) ?></a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span><?php foreach (site_hours() as $i => $h): ?><?= $i ? '<br />' : '' ?><?= e($h['days']) ?>:
                                <?= e($h['time']) ?><?php endforeach; ?></span>
                    </li>
                </ul>
            </div>

            <div>
                <h4>Quick Links</h4>
                <ul>
                    <?php foreach (SITE_NAV + SITE_NAV_EXTRA as $slug => $label): ?>
                        <li><a
                                href="<?= $base_url . ($slug === 'applications' ? 'countertops' : ($slug === 'resources' ? 'glossary' : $slug)) ?>"><?= e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="<?= $base_url ?>contact">Contact &amp; Location</a></li>
                    <li><a href="<?= $base_url ?>trade/login">Trade Account</a></li>
                </ul>
            </div>

            <div>
                <h4>Materials</h4>
                <ul>
                    <?php foreach (SITE_MATERIALS as $slug => $m): ?>
                        <li><a href="<?= $base_url ?>slabs?type=<?= e($slug) ?>"><?= e($m['label']) ?> Slabs</a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(site_name()) ?>. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="<?= $base_url ?>privacy-policy">Privacy Policy</a>
                <a href="<?= $base_url ?>terms">Terms of Service</a>
                <a href="<?= $base_url ?>sitemap.xml">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<?php include __DIR__ . '/contact-rail.php'; ?>

<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>

</html>