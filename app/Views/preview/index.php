<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview CV</title>
  <meta name="csrf-token" content="<?= esc($csrf) ?>">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/css/tsp.css">
</head>
<body class="preview-body">
<header class="preview-topbar">
  <div class="preview-topbar-inner">
    <strong>Preview CV</strong>
    <div class="preview-controls">
      <?= view('cv/components/template_switcher', ['selected' => $selected_template ?? 'classic']) ?>
      <label class="toggle">
        <input id="auto-refresh" type="checkbox" checked>
        <span>Auto refresh</span>
      </label>
      <button id="refresh" class="btn" type="button">Refresh</button>
      <a class="btn secondary" href="/buat-cv/step/1">Kembali ke Form</a>
      <a id="btn-export-pdf" class="btn" href="/export/pdf" target="_blank" rel="noopener">Download PDF</a>
    </div>
  </div>
</header>
<main class="preview-main">
  <div id="preview-root">
    <?= $html ?>
  </div>
</main>
<script src="/assets/js/template-switcher.js"></script>
<script>
(function () {
  var tokenEl = document.querySelector('meta[name="csrf-token"]');
  var token = tokenEl ? tokenEl.content : '';
  var root = document.getElementById('preview-root');
  var btn = document.getElementById('refresh');
  var auto = document.getElementById('auto-refresh');
  var t = null;
  var currentTemplate = window.__CURRENT_TEMPLATE__ || '<?= esc($selected_template ?? 'classic') ?>';

  function setToken(next) {
    if (!next) return;
    token = next;
    if (tokenEl) tokenEl.content = next;
  }

  function refresh() {
    var body = new URLSearchParams();
    body.set('csrf_test_name', token);
    body.set('template', currentTemplate);
    fetch('/api/preview', {
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
        if (r.ok && root) root.innerHTML = r.html;
      });
  }

  btn && btn.addEventListener('click', refresh);

  function loop() {
    clearTimeout(t);
    if (auto && auto.checked) {
      t = setTimeout(function () { refresh(); loop(); }, 3000);
    }
  }

  auto && auto.addEventListener('change', loop);

  MangCvTemplateSwitcher.init({
    onSwitch: function (template) {
      currentTemplate = template;
      var pdfBtn = document.getElementById('btn-export-pdf');
      if (pdfBtn) pdfBtn.href = '/export/pdf?template=' + template;
      refresh();
    }
  });

  loop();
})();
</script>
</body>
</html>