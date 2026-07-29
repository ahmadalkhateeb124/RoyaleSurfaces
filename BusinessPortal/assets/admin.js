/**
 * Royale Surfaces — portal behaviour.
 * Sidebar, password reveal, slug suggestion, counters, image preview,
 * delete confirmation and unsaved-changes guard.
 */
(function () {
  'use strict';

  /* ── Top nav drawer (mobile) ────────────────────────────────────────────── */
  var mastNav = document.getElementById('mastNav');
  var navToggle = document.getElementById('navToggle');
  var navScrim = document.getElementById('navScrim');

  if (mastNav && navToggle) {
    var setNav = function (open) {
      mastNav.classList.toggle('is-open', open);
      navToggle.setAttribute('aria-expanded', String(open));
      navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      document.body.classList.toggle('nav-open', open);
      if (navScrim) navScrim.hidden = !open;
    };
    navToggle.addEventListener('click', function () {
      setNav(!mastNav.classList.contains('is-open'));
    });
    if (navScrim) navScrim.addEventListener('click', function () { setNav(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && mastNav.classList.contains('is-open')) setNav(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) setNav(false);
    });
  }

  /* ── User menu ──────────────────────────────────────────────────────────── */
  var userTrigger = document.getElementById('userTrigger');
  var userDrop = document.getElementById('userDrop');

  if (userTrigger && userDrop) {
    var setUser = function (open) {
      userDrop.hidden = !open;
      userTrigger.setAttribute('aria-expanded', String(open));
    };
    userTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      setUser(userDrop.hidden);
    });
    // Click anywhere else, or press Escape, closes it.
    document.addEventListener('click', function (e) {
      if (!userDrop.hidden && !userDrop.contains(e.target)) setUser(false);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !userDrop.hidden) {
        setUser(false);
        userTrigger.focus();
      }
    });
  }

  /* ── Live search-result preview ─────────────────────────────────────────
     Shows what Google would display, falling back to the title/excerpt the
     same way index.php does when the meta fields are left blank. */
  var serpTitle = document.querySelector('[data-serp-title]');
  if (serpTitle) {
    var serpDesc = document.querySelector('[data-serp-desc]');
    var serpSlug = document.querySelector('[data-serp-slug]');
    var siteName = document.querySelector('[data-site-name]');

    var fTitle = document.getElementById('title');
    var fExcerpt = document.getElementById('excerpt');
    var fMetaTitle = document.getElementById('meta_title');
    var fMetaDesc = document.getElementById('meta_description');
    var fSlug = document.getElementById('slug');

    var suffix = siteName ? ' | ' + siteName.textContent : '';

    var paintSerp = function () {
      var t = (fMetaTitle.value.trim() || (fTitle.value.trim() + suffix)).trim();
      var d = (fMetaDesc.value.trim() || fExcerpt.value.trim()).trim();

      serpTitle.textContent = t || 'Your article title';
      serpDesc.textContent = d || 'Your excerpt or meta description appears here.';
      if (serpSlug) serpSlug.textContent = fSlug.value.trim() || 'your-post';
    };

    [fTitle, fExcerpt, fMetaTitle, fMetaDesc, fSlug].forEach(function (el) {
      if (el) el.addEventListener('input', paintSerp);
    });
    paintSerp();
  }

  /* ── Password reveal ────────────────────────────────────────────────────── */
  document.querySelectorAll('[data-reveal-for]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.revealFor);
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('is-on', show);
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      input.focus();
    });
  });

  /* ── Slug suggestion ────────────────────────────────────────────────────
     Only fills the slug while it is untouched — editing a live post must not
     silently change its URL (and break every link to it) as you fix a typo. */
  var slugSource = document.querySelector('[data-slug-source]');
  var slugTarget = document.querySelector('[data-slug-target]');

  if (slugSource && slugTarget) {
    var slugLocked = slugTarget.value.trim() !== '';
    slugTarget.addEventListener('input', function () { slugLocked = true; });

    slugSource.addEventListener('input', function () {
      if (slugLocked) return;
      slugTarget.value = slugSource.value
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 170);
    });
  }

  /* ── Character counters ─────────────────────────────────────────────────── */
  document.querySelectorAll('[data-counter]').forEach(function (field) {
    var out = document.getElementById(field.dataset.counter);
    if (!out) return;

    var ideal = parseInt(field.dataset.counterIdeal, 10) || 160;
    var update = function () {
      var n = field.value.length;
      out.textContent = n + ' characters' + (n > ideal ? ' — may be truncated in search results' : '');
      out.classList.toggle('is-over', n > ideal);
    };
    field.addEventListener('input', update);
    update();
  });

  /* ── Image picker: preview + drag and drop ──────────────────────────────── */
  document.querySelectorAll('[data-image-drop]').forEach(function (drop) {
    var input = drop.querySelector('[data-image-input]');
    var preview = drop.querySelector('[data-image-preview]');
    var empty = drop.querySelector('.image-drop-empty');
    if (!input) return;

    var show = function (file) {
      if (!file || !file.type.startsWith('image/')) return;
      var url = URL.createObjectURL(file);
      if (preview) {
        preview.src = url;
        preview.hidden = false;
        preview.onload = function () { URL.revokeObjectURL(url); };
      }
      if (empty) empty.hidden = true;
    };

    input.addEventListener('change', function () { show(input.files[0]); });

    ['dragenter', 'dragover'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) {
        e.preventDefault();
        drop.classList.add('is-drag');
      });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      drop.addEventListener(ev, function () { drop.classList.remove('is-drag'); });
    });

    drop.addEventListener('drop', function (e) {
      e.preventDefault();
      var file = e.dataTransfer.files[0];
      if (!file) return;
      input.files = e.dataTransfer.files;
      show(file);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  /* ── Toasts ─────────────────────────────────────────────────────────────
     Success and error messages surface here instead of pushing the page
     down. Server-rendered ones already exist in the DOM; this adds the
     dismiss behaviour and the auto-hide timer. */
  var toastStack = document.getElementById('toastStack');

  var dismissToast = function (el) {
    el.classList.add('is-going');
    el.addEventListener('animationend', function () { el.remove(); }, { once: true });
    // Belt and braces if the animation never fires (reduced motion, etc.)
    setTimeout(function () { if (el.isConnected) el.remove(); }, 400);
  };

  var wireToast = function (el) {
    var close = el.querySelector('.toast-close');
    if (close) close.addEventListener('click', function () { dismissToast(el); });

    // Errors stay until dismissed — they usually need reading twice.
    if (!el.classList.contains('is-error')) {
      var timer = setTimeout(function () { dismissToast(el); }, 5000);
      el.addEventListener('mouseenter', function () { clearTimeout(timer); });
    }
  };

  if (toastStack) {
    toastStack.querySelectorAll('.toast').forEach(wireToast);
  }

  /* ── Confirm dialog ─────────────────────────────────────────────────────
     Replaces window.confirm, which cannot be styled and shows the raw host
     name ("localhost says") above the message. */
  var confirmDialog = function (opts) {
    return new Promise(function (resolve) {
      var wrap = document.createElement('div');
      wrap.className = 'modal';
      wrap.innerHTML =
        '<div class="modal-backdrop"></div>' +
        '<div class="modal-panel" role="alertdialog" aria-modal="true" aria-labelledby="mTitle">' +
          '<div class="modal-icon' + (opts.danger ? ' is-danger' : '') + '">' +
            '<svg viewBox="0 0 24 24" aria-hidden="true">' +
              (opts.danger
                ? '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'
                : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>') +
            '</svg>' +
          '</div>' +
          '<h2 id="mTitle">' + (opts.title || 'Are you sure?') + '</h2>' +
          '<p>' + (opts.message || '') + '</p>' +
          '<div class="modal-actions">' +
            '<button type="button" class="btn-admin" data-cancel>Cancel</button>' +
            '<button type="button" class="btn-admin ' +
              (opts.danger ? 'is-danger-solid' : 'is-primary') + '" data-ok>' +
              (opts.confirmLabel || 'Confirm') + '</button>' +
          '</div>' +
        '</div>';

      document.body.appendChild(wrap);
      document.body.classList.add('nav-open');   // reuse the scroll lock

      var okBtn = wrap.querySelector('[data-ok]');
      var cancelBtn = wrap.querySelector('[data-cancel]');
      var lastFocus = document.activeElement;

      var close = function (result) {
        wrap.classList.add('is-going');
        document.body.classList.remove('nav-open');
        document.removeEventListener('keydown', onKey);
        setTimeout(function () {
          wrap.remove();
          if (lastFocus) lastFocus.focus();
          resolve(result);
        }, 160);
      };

      var onKey = function (e) {
        if (e.key === 'Escape') close(false);
        if (e.key === 'Tab') {
          // Two buttons only — keep focus bouncing between them.
          e.preventDefault();
          (document.activeElement === okBtn ? cancelBtn : okBtn).focus();
        }
      };

      okBtn.addEventListener('click', function () { close(true); });
      cancelBtn.addEventListener('click', function () { close(false); });
      wrap.querySelector('.modal-backdrop').addEventListener('click', function () { close(false); });
      document.addEventListener('keydown', onKey);

      // Focus Cancel, not Confirm — a stray Enter should not delete anything.
      cancelBtn.focus();
    });
  };

  /* ── Destructive actions need confirming ────────────────────────────────── */
  document.querySelectorAll('[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed === '1') return;   // second pass, let it through
      e.preventDefault();

      confirmDialog({
        title: form.dataset.confirmTitle || 'Delete this permanently?',
        message: form.dataset.confirm,
        confirmLabel: form.dataset.confirmLabel || 'Delete',
        danger: form.dataset.confirmSafe !== '1'
      }).then(function (ok) {
        if (!ok) return;
        form.dataset.confirmed = '1';
        form.requestSubmit ? form.requestSubmit() : form.submit();
      });
    });
  });

  /* ── Unsaved-changes guard ──────────────────────────────────────────────── */
  var editForm = document.querySelector('.edit-form');
  if (editForm) {
    var dirty = false;
    editForm.addEventListener('input', function () { dirty = true; });
    editForm.addEventListener('change', function () { dirty = true; });
    editForm.addEventListener('submit', function () { dirty = false; });

    window.addEventListener('beforeunload', function (e) {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = '';
    });
  }
})();
