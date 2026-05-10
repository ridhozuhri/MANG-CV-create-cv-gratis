<section class="step5-section" aria-labelledby="step5-title">
  <h2 id="step5-title">Step 5 — Preview & Download</h2>

  <!-- ====== Template Switcher ====== -->
  <?= view('cv/components/template_switcher', ['selected' => $sections['_template'] ?? 'classic']) ?>

  <!-- ====== Live Preview ====== -->
  <div id="preview-container" class="preview-container" role="region" aria-label="Preview CV" aria-live="polite">
    <div class="preview-loading" aria-busy="true">
      <span>Memuat preview...</span>
    </div>
  </div>

  <!-- ====== Capacity & Overflow Info ====== -->
  <div id="overflow-alert" class="overflow-alert" style="display:none;" role="alert" aria-live="assertive"></div>

  <div class="meta-info">
    <div id="page-estimate" class="page-estimate">
      <span class="page-icon"></span>
      <span class="page-text" id="page-estimate-text">Estimasi: 1 halaman</span>
    </div>
    <div id="template-recommendation" class="recommendation" style="display:none;">
      <span>Rekomendasi:</span>
      <a href="#" id="recommendation-link">-</a>
    </div>
  </div>

  <!-- ====== Download Section ====== -->
  <div class="download-section">
    <h3>Unduh CV Anda</h3>
    <p class="hint">Pilih format yang diinginkan. Data Anda sudah tersimpan otomatis.</p>

    <div class="download-buttons">
      <a id="btn-pdf" class="btn download-btn" href="/export/pdf" target="_blank" rel="noopener" aria-label="Download PDF">
        <span class="download-icon" aria-hidden="true">PDF</span>
        <span>Download PDF</span>
        <small>Format profesional</small>
      </a>

      <a id="btn-txt" class="btn download-btn secondary" href="/export/txt" target="_blank" rel="noopener" aria-label="Download TXT">
        <span class="download-icon" aria-hidden="true">TXT</span>
        <span>Download TXT</span>
        <small>Untuk screen reader</small>
      </a>

      <a id="btn-json" class="btn download-btn secondary" href="/export/json" target="_blank" rel="noopener" aria-label="Download JSON">
        <span class="download-icon" aria-hidden="true">JSON</span>
        <span>Download JSON</span>
        <small>Backup data</small>
      </a>
    </div>

    <p class="data-notice">
      <small>Data CV disimpan selama 30 hari. Unduh dalam format lain untuk backup.</small>
    </p>
  </div>

  <!-- ====== Rate Limit Alert ====== -->
  <div id="rate-limit-alert" class="rate-limit-alert" style="display:none;" role="alert">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" width="20" height="20">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <span id="rate-limit-message"></span>
  </div>

  <!-- ====== Tips ====== -->
  <div class="tips-section">
    <h3>Tips CV</h3>
    <ul>
      <li>Pastikan nama dan email benar</li>
      <li>Gunakan foto profil dengan wajah jelas</li>
      <li>Tulis pengalaman dengan kalimat singkat dan aktif</li>
      <li>Sesuaikan skill dengan posisi yang dilamar</li>
    </ul>
  </div>
</section>

<script>
(function () {
  'use strict';

  var tokenEl = document.querySelector('meta[name="csrf-token"]');
  var token = tokenEl ? tokenEl.content : '';
  var container = document.getElementById('preview-container');
  var currentTemplate = window.__CURRENT_TEMPLATE__ || 'classic';
  var initialized = false;

  // ── Token helpers ──────────────────────────────────────────────────────────
  function setToken(next) {
    if (!next) return;
    token = next;
    if (tokenEl) tokenEl.content = next;
  }

  // ── Load preview from draft session data ─────────────────────────────────
  function loadPreview(tmpl) {
    tmpl = tmpl || currentTemplate;
    if (!container) return;
    container.innerHTML = '<div class="preview-loading" aria-busy="true"><span>Memuat preview...</span></div>';

    var sections = window.__STEP_DATA__ || {};
    var body = new URLSearchParams();
    body.set('csrf_test_name', token);
    body.set('template', tmpl);
    Object.keys(sections).forEach(function (sec) {
      var s = sections[sec];
      body.set('sections[' + sec + ']', (typeof s === 'string') ? s : JSON.stringify(s || {}));
    });

    fetch('/api/preview-draft', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: body.toString()
    }).then(function (r) { return r.json(); })
      .then(function (r) {
        setToken(r.csrf || token);
        if (r.ok && container) {
          container.innerHTML = r.html;
        }
      })
      .catch(function () {
        if (container) {
          container.innerHTML = '<div class="preview-loading"><span>Gagal memuat preview.</span></div>';
        }
      });
  }

  // ── Update TSP card active state ─────────────────────────────────────────────
  function updateCards(template) {
    var cards = document.querySelectorAll('.tsp-card');
    cards.forEach(function (card) {
      if (card.getAttribute('data-template') === template) {
        card.classList.add('tsp-active');
      } else {
        card.classList.remove('tsp-active');
      }
    });
    var nameEl = document.getElementById('tsp-current');
    var names = {
      'classic': 'Classic', 'modern': 'Modern', 'sidebar': 'Sidebar',
      'minimalist': 'Minimalist', 'professional': 'Professional'
    };
    if (nameEl) nameEl.textContent = names[template] || template;
    window.__CURRENT_TEMPLATE__ = template;
  }

  // ── Switch template: API call + preview reload + UI update ───────────────
  function switchTemplate(tmpl) {
    if (tmpl === currentTemplate) return;

    var form = new URLSearchParams();
    form.set('csrf_test_name', token);
    form.set('template', tmpl);

    fetch('/api/switch-template', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: form.toString()
    }).then(function (r) { return r.json(); })
      .then(function (r) {
        setToken(r.csrf || token);
        if (r.ok) {
          currentTemplate = tmpl;
          updateCards(tmpl);
          loadPreview(tmpl);
          var pdfBtn = document.getElementById('btn-pdf');
          if (pdfBtn) pdfBtn.href = '/export/pdf?template=' + tmpl;
          document.dispatchEvent(new CustomEvent('cv:template-changed', {
            detail: { template: tmpl }
          }));
        }
      })
      .catch(function (err) {
        console.error('[step5] switchTemplate error:', err);
      });
  }

  // ── Attach TSP card click handler ─────────────────────────────────────────
  function initClickHandlers() {
    var grid = document.querySelector('.tsp-grid');
    if (!grid) return;
    grid.addEventListener('click', function (e) {
      var btn = e.target.closest('.tsp-card');
      if (!btn) return;
      var tmpl = btn.getAttribute('data-template');
      if (tmpl && tmpl !== currentTemplate) {
        switchTemplate(tmpl);
      }
    });
  }

  // ── Download buttons with rate-limit awareness ─────────────────────────────
  ['btn-pdf', 'btn-txt', 'btn-json'].forEach(function (id) {
    var btn = document.getElementById(id);
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var url = btn.getAttribute('href');
      fetch(url, { method: 'HEAD' })
        .then(function (r) {
          if (r.status === 429) {
            r.json().then(function (data) {
              var alert = document.getElementById('rate-limit-alert');
              var msg = document.getElementById('rate-limit-message');
              if (alert && msg) {
                msg.textContent = data.message || 'Batas download tercapai.';
                alert.style.display = 'flex';
              }
            });
          } else if (r.ok) {
            var alert = document.getElementById('rate-limit-alert');
            if (alert) alert.style.display = 'none';
            window.open(url, '_blank');
          }
        });
    });
  });

  // ── Bootstrap ─────────────────────────────────────────────────────────────
  function bootstrap() {
    if (initialized) return;
    initialized = true;
    initClickHandlers();
    loadPreview(currentTemplate);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
  } else {
    bootstrap();
  }
})();
</script>
