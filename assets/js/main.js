/**
 * Royale Surfaces — site behaviour.
 * Navbar state, mobile menu, slab filtering, scroll reveal, contact form.
 */
(function () {
  'use strict';

  /* ── Google Analytics bootstrap ─────────────────────────────────────────
     The gtag.js tag itself is loaded from the head; this only queues the
     config command. Kept out of the HTML so the CSP needs no unsafe-inline. */
  var gaMeta = document.querySelector('meta[name="ga-measurement-id"]');
  if (gaMeta && gaMeta.content) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    gtag('js', new Date());
    gtag('config', gaMeta.content);
  }

  /* ── Bot trap: prove a browser rendered the page ─────────────────────────
     Any form marked data-nonce carries a per-session token from the server;
     copying it into the form's own js_token field is the one thing a script
     that only parses and POSTs the HTML never does. Applies to every plain
     POST form site-wide (trade login/register/forgot, not just contact —
     that one has its own fetch-based submit handler further down). */
  document.querySelectorAll('form[data-nonce]').forEach(function (f) {
    var field = f.querySelector('input[name="js_token"]');
    if (field) field.value = f.dataset.nonce || '';
  });

  /* ── Navbar: solid background once scrolled ─────────────────────────────── */
  var navbar = document.getElementById('navbar');
  if (navbar) {
    var onScroll = function () {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ── Mobile menu ────────────────────────────────────────────────────────── */
  var toggle = document.getElementById('mobileToggle');
  var menu = document.getElementById('mobileMenu');

  if (toggle && menu) {
    var isOpen = function () { return menu.classList.contains('open'); };

    var setMenu = function (open) {
      menu.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      document.body.classList.toggle('menu-open', open);
    };

    toggle.addEventListener('click', function () {
      setMenu(!isOpen());
    });

    // Tapping any link navigates — close first so the panel isn't still
    // open if the browser restores this page from the back/forward cache.
    menu.addEventListener('click', function (e) {
      if (e.target.closest('a')) setMenu(false);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen()) {
        setMenu(false);
        toggle.focus();
      }
    });

    // Keep focus inside the panel while it's open.
    menu.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab' || !isOpen()) return;
      var items = menu.querySelectorAll('a, summary, button');
      if (!items.length) return;
      var first = toggle;
      var last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 900 && isOpen()) setMenu(false);
    });

    window.addEventListener('pageshow', function () { setMenu(false); });
  }

  /* ── Filter rails (slabs, blog, gallery) ────────────────────────────────
     On mobile these become a horizontal scroller. Fade an edge only when there
     is content past it, so the hint is honest, and make sure the active chip
     is not parked off-screen when the page loads filtered. */
  var railFade = function (rail) {
    var max = rail.scrollWidth - rail.clientWidth;
    if (max <= 1) {
      rail.style.setProperty('--fade-l', '0px');
      rail.style.setProperty('--fade-r', '0px');
      return;
    }
    var x = rail.scrollLeft;
    rail.style.setProperty('--fade-l', x > 4 ? '28px' : '0px');
    rail.style.setProperty('--fade-r', x < max - 4 ? '28px' : '0px');
  };

  var centreChip = function (rail, chip, smooth) {
    var off = chip.offsetLeft - rail.clientWidth / 2 + chip.offsetWidth / 2;
    rail.scrollTo({ left: Math.max(0, off), behavior: smooth ? 'smooth' : 'auto' });
  };

  document.querySelectorAll('.filter-bar').forEach(function (rail) {
    var update = function () { railFade(rail); };
    rail.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);

    var active = rail.querySelector('.active');
    if (active) centreChip(rail, active, false);
    update();

    rail.updateFades = update;   // the slab filter re-runs this after filtering
  });

  /* ── Slab inventory filter ──────────────────────────────────────────────── */
  var grid = document.getElementById('productsGrid');
  var filterBar = document.getElementById('filterBar');

  if (grid && filterBar) {
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.product-card'));
    var empty = document.getElementById('filterEmpty');
    var countEl = document.getElementById('filterCount');

    var applyFilter = function (type, pushState) {
      var wanted = (type || 'all').toLowerCase();
      var shown = 0;

      cards.forEach(function (card) {
        var match = wanted === 'all' || card.dataset.type.toLowerCase() === wanted;
        card.hidden = !match;
        if (match) shown++;
      });

      filterBar.querySelectorAll('.filter-btn').forEach(function (btn) {
        var active = btn.dataset.filter.toLowerCase() === wanted;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-pressed', String(active));

        if (active) centreChip(filterBar, btn, true);
      });

      if (empty) empty.hidden = shown > 0;
      if (countEl) {
        countEl.textContent = shown + (shown === 1 ? ' slab' : ' slabs');
      }

      filterBar.updateFades();

      // Keep the URL shareable and the back button working.
      if (pushState) {
        var url = wanted === 'all'
          ? window.location.pathname
          : window.location.pathname + '?type=' + wanted;
        history.pushState({ type: wanted }, '', url);
      }
    };

    filterBar.addEventListener('click', function (e) {
      var btn = e.target.closest('.filter-btn');
      if (btn) applyFilter(btn.dataset.filter, true);
    });

    window.addEventListener('popstate', function () {
      applyFilter(new URLSearchParams(window.location.search).get('type') || 'all', false);
    });

    // Sync from whichever button the server already marked active, not the
    // URL's query string — a clean route like /quartz carries no ?type= at
    // all (PHP resolves it internally), so reading the query string here
    // always saw "no type" and silently reset the filter back to All the
    // moment the page finished loading.
    var initialBtn = filterBar.querySelector('.filter-btn.active');
    applyFilter(initialBtn ? initialBtn.dataset.filter : 'all', false);
  }

  /* ── Scroll reveal (skipped for reduced-motion users) ───────────────────── */
  var reveals = document.querySelectorAll('[data-reveal]');
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (reveals.length && !reducedMotion && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
    reveals.forEach(function (el) { observer.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ── Gallery lightbox ───────────────────────────────────────────────────── */
  var lightbox = document.getElementById('lightbox');
  var galleryGrid = document.getElementById('galleryGrid');

  if (lightbox && galleryGrid) {
    var tiles = Array.prototype.slice.call(galleryGrid.querySelectorAll('.gallery-open'));
    var current = 0;
    var lastFocused = null;

    var lb = {
      image: document.getElementById('lbImage'),
      space: document.getElementById('lbSpace'),
      title: document.getElementById('lbTitle'),
      body: document.getElementById('lbBody'),
      material: document.getElementById('lbMaterial'),
      location: document.getElementById('lbLocation'),
      link: document.getElementById('lbLink'),
      count: document.getElementById('lbCount')
    };

    var show = function (index) {
      // Wrap around so the arrows are never dead ends.
      current = (index + tiles.length) % tiles.length;
      var d = tiles[current].dataset;

      lb.image.src = d.image;
      lb.image.alt = d.title + ' — ' + d.material;
      lb.space.textContent = d.space;
      lb.title.textContent = d.title;
      lb.body.textContent = d.body;
      lb.material.textContent = d.material;
      lb.location.textContent = d.location;
      lb.link.href = d.href;
      lb.count.textContent = (current + 1) + ' of ' + tiles.length;

      // Warm the neighbouring images so arrowing through feels instant.
      [current - 1, current + 1].forEach(function (i) {
        var t = tiles[(i + tiles.length) % tiles.length];
        if (t) new Image().src = t.dataset.image;
      });
    };

    var open = function (index) {
      lastFocused = document.activeElement;
      show(index);
      lightbox.hidden = false;
      document.body.classList.add('menu-open');   // reuses the scroll lock
      lightbox.querySelector('.lightbox-close').focus();
    };

    var close = function () {
      lightbox.hidden = true;
      document.body.classList.remove('menu-open');
      if (lastFocused) lastFocused.focus();
    };

    galleryGrid.addEventListener('click', function (e) {
      var btn = e.target.closest('.gallery-open');
      if (btn) open(tiles.indexOf(btn));
    });

    lightbox.addEventListener('click', function (e) {
      if (e.target.closest('[data-close]')) close();
      else if (e.target.closest('[data-prev]')) show(current - 1);
      else if (e.target.closest('[data-next]')) show(current + 1);
    });

    document.addEventListener('keydown', function (e) {
      if (lightbox.hidden) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') show(current - 1);
      else if (e.key === 'ArrowRight') show(current + 1);
      else if (e.key === 'Tab') {
        // Trap focus inside the dialog.
        var f = lightbox.querySelectorAll('button, a[href]');
        var first = f[0];
        var last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    // Swipe between projects on touch devices.
    var touchX = null;
    lightbox.addEventListener('touchstart', function (e) {
      touchX = e.changedTouches[0].clientX;
    }, { passive: true });

    lightbox.addEventListener('touchend', function (e) {
      if (touchX === null) return;
      var dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 50) show(current + (dx < 0 ? 1 : -1));
      touchX = null;
    }, { passive: true });
  }

  /* ── Article: reading progress + active section in the contents ─────────── */
  var progress = document.getElementById('readProgress');
  var articleBody = document.querySelector('.post-body');

  if (progress && articleBody) {
    var bar = progress.querySelector('span');

    var updateProgress = function () {
      // Measure against the article itself, not the whole document — the
      // footer and related posts shouldn't count toward "read".
      var start = articleBody.offsetTop;
      var span = articleBody.offsetHeight - window.innerHeight;
      var pct = span <= 0 ? 1 : (window.scrollY - start) / span;
      bar.style.width = Math.max(0, Math.min(1, pct)) * 100 + '%';
    };

    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
  }

  var tocLinks = document.querySelectorAll('.post-toc a');
  if (tocLinks.length && 'IntersectionObserver' in window) {
    var headings = [];
    tocLinks.forEach(function (link) {
      var target = document.getElementById(link.getAttribute('href').slice(1));
      if (target) headings.push({ link: link, target: target });
    });

    var tocObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        headings.forEach(function (h) {
          h.link.classList.toggle('is-active', h.target === entry.target);
        });
      });
    }, { rootMargin: '-110px 0px -70% 0px' });

    headings.forEach(function (h) { tocObserver.observe(h.target); });
  }

  /* ── Copy article link ──────────────────────────────────────────────────── */
  var copyBtn = document.querySelector('.copy-link');
  if (copyBtn && navigator.clipboard) {
    copyBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(copyBtn.dataset.url).then(function () {
        copyBtn.classList.add('is-copied');
        copyBtn.setAttribute('aria-label', 'Link copied');
        setTimeout(function () {
          copyBtn.classList.remove('is-copied');
          copyBtn.setAttribute('aria-label', 'Copy link');
        }, 2000);
      });
    });
  }

  /* ── Contact form: client validation + async submit ─────────────────────── */
  var form = document.getElementById('contactForm');
  if (form) {
    var success = document.getElementById('formSuccess');
    var statusEl = document.getElementById('formStatus');
    var submitBtn = form.querySelector('.btn-submit');

    var rules = {
      name: function (v) { return v.trim().length >= 2 || 'Please enter your full name'; },
      // Optional — private clients buying a single slab have no company.
      company: function (v) {
        var t = v.trim();
        return t === '' || t.length >= 2 || 'Company name looks too short';
      },
      phone: function (v) {
        return (v.replace(/\D/g, '').length >= 10) || 'Enter a valid phone number';
      },
      email: function (v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Enter a valid email address';
      },
      message: function (v) { return v.trim().length >= 10 || 'Please tell us a bit more'; }
    };

    var showError = function (field, msg) {
      var input = form.elements[field];
      var err = document.getElementById(field + 'Error');
      var invalid = typeof msg === 'string';
      if (err) {
        err.textContent = invalid ? msg : '';
        err.hidden = !invalid;
      }
      if (input) input.setAttribute('aria-invalid', String(invalid));
      return !invalid;
    };

    // Clear an error as soon as the user fixes it.
    Object.keys(rules).forEach(function (field) {
      var input = form.elements[field];
      if (!input) return;
      input.addEventListener('blur', function () { showError(field, rules[field](input.value)); });
      input.addEventListener('input', function () {
        if (input.getAttribute('aria-invalid') === 'true') {
          showError(field, rules[field](input.value));
        }
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var firstInvalid = null;
      Object.keys(rules).forEach(function (field) {
        var input = form.elements[field];
        if (!input) return;
        var ok = showError(field, rules[field](input.value));
        if (!ok && !firstInvalid) firstInvalid = input;
      });

      if (firstInvalid) {
        firstInvalid.focus();
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending…';
      if (statusEl) { statusEl.hidden = true; statusEl.className = 'form-status'; }

      fetch(form.action, { method: 'POST', body: new FormData(form) })
        .then(function (res) { return res.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
          if (data.ok) {
            form.hidden = true;
            if (success) {
              success.hidden = false;
              success.focus();
            }
          } else {
            throw new Error(data.error || 'Something went wrong.');
          }
        })
        .catch(function (err) {
          if (statusEl) {
            statusEl.textContent = err.message +
              ' You can also reach us directly by phone.';
            statusEl.className = 'form-status is-error';
            statusEl.hidden = false;
          }
        })
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Inquiry';
        });
    });
  }
})();

/* ── Decorative video ──────────────────────────────────────────────────────
   A muted autoplay loop is allowed by every browser, but it still gets paused
   when the tab is hidden, when the OS drops into a power-saving mode, or when
   the element scrolls far out of view on mobile Safari. None of those resume on
   their own, so the video would silently stay frozen for the rest of the visit.
   ------------------------------------------------------------------------- */
(function () {
  const videos = document.querySelectorAll('.about-video');
  if (!videos.length) return;

  // Honour the OS "reduce motion" setting: leave the poster frame showing.
  const still = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (still.matches) {
    videos.forEach((v) => {
      v.autoplay = false;
      v.removeAttribute('autoplay');
      v.pause();
    });
    return;
  }

  const resume = (v) => {
    // muted is re-asserted because autoplay is only permitted while muted, and
    // an extension or devtools poke can flip it.
    v.muted = true;
    if (v.paused && !document.hidden) {
      const p = v.play();
      if (p && p.catch) p.catch(() => {});
    }
  };

  videos.forEach((v) => {
    v.addEventListener('pause', () => resume(v));
    v.addEventListener('loadeddata', () => resume(v));

    // data-clip="15" → play the first 15s on repeat. The loop attribute only
    // restarts at the true end of the file, so the rewind is done here.
    const clip = parseFloat(v.dataset.clip);
    if (clip > 0) {
      v.addEventListener('timeupdate', () => {
        if (v.currentTime >= clip) v.currentTime = 0;
      });
    }

    resume(v);
  });

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) videos.forEach(resume);
  });
})();
