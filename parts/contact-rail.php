<?php
/**
 * Fixed contact rail — directions, phone and email, on every page.
 *
 * The three things a fabricator wants at the moment they decide to act, without
 * scrolling back to the header or down to the footer. Included from
 * parts/footer.php so it lands on every route automatically.
 */
?>
<!-- CONTACT RAIL -->
<aside class="contact-rail" aria-label="Contact <?= e(site_name()) ?>">
    <a href="<?= e(site_directions_url()) ?>" target="_blank" rel="noopener noreferrer" class="rail-btn"
        data-rail="Directions">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
        </svg>
        <span class="rail-label" data-short="Map">Directions</span>
        <span class="sr-only">Open <?= e(site_address()) ?> in Google Maps</span>
    </a>

    <a href="<?= e(tel_link(site_phone())) ?>" class="rail-btn" data-rail="Call">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2
                2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2
                0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z" />
        </svg>
        <span class="rail-label" data-short="Call"><?= e(site_phone()) ?></span>
        <span class="sr-only">Call <?= e(site_name()) ?> on <?= e(site_phone()) ?></span>
    </a>

    <a href="mailto:<?= e(site_email()) ?>" class="rail-btn" data-rail="Email">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <polyline points="2.5 6 12 13 21.5 6" />
        </svg>
        <span class="rail-label" data-short="Email">Email us</span>
        <span class="sr-only">Email <?= e(site_email()) ?></span>
    </a>
</aside>
