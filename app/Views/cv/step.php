<?php
$selected = $sections['_template'] ?? 'classic';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wizard CV - Step <?= (int) $step ?></title>
  <meta name="csrf-token" content="<?= esc($csrf) ?>">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/css/accessibility.css">
  <link rel="stylesheet" href="/assets/css/tsp.css">
</head>
<body>
<main class="container enter">
<script>
window.__CURRENT_STEP__ = <?= (int) $step ?>;
window.__STEP_DATA__ = <?= json_encode($sections ?? [], JSON_UNESCAPED_UNICODE) ?>;
window.__CURRENT_TEMPLATE__ = '<?= esc($selected) ?>';
window.__AVAILABLE_TEMPLATES__ = ['classic','modern','sidebar','minimalist','professional'];
</script>

<div class="wizard-shell">
  <header class="wizard-top">
    <div class="wizard-head">
      <div>
        <h1 class="wizard-title">Buat CV Profesional</h1>
        <p class="wizard-sub">
          <?php if ($step === 1): ?>Mulai dari data inti. Tidak perlu sempurna dulu.
          <?php elseif ($step === 2): ?>Masukkan riwayat pendidikan. Tambah lebih dari satu jika perlu.
          <?php elseif ($step === 3): ?>Tuliskan pengalaman kerja/organisasi. Gunakan kalimat singkat dan jelas.
          <?php elseif ($step === 4): ?>Tambahkan skill dan bahasa yang Anda kuasai.
          <?php else: ?>Pratinjau CV Anda dan unduh dalam format pilihan.
          <?php endif; ?>
        </p>
      </div>
      <div class="progress" aria-label="Progress">
        <div style="width:<?= (int) round($step / 5 * 100) ?>%"></div>
      </div>
    </div>
    <nav class="wizard-nav" aria-label="Langkah">
      <a class="step-link <?= $step === 1 ? 'active' : '' ?>" href="/buat-cv/step/1">1. Data Diri</a>
      <a class="step-link <?= $step === 2 ? 'active' : '' ?>" href="/buat-cv/step/2">2. Pendidikan</a>
      <a class="step-link <?= $step === 3 ? 'active' : '' ?>" href="/buat-cv/step/3">3. Pengalaman</a>
      <a class="step-link <?= $step === 4 ? 'active' : '' ?>" href="/buat-cv/step/4">4. Skills</a>
      <a class="step-link <?= $step === 5 ? 'active' : '' ?>" href="/buat-cv/step/5">5. Download</a>
    </nav>
  </header>

  <div class="wizard-body">
    <div class="section">
      <?php if ($step === 1): ?>
        <?= view('cv/steps/step1_personal', ['sections' => $sections]) ?>
      <?php elseif ($step === 2): ?>
        <?= view('cv/steps/step2_education', ['sections' => $sections]) ?>
      <?php elseif ($step === 3): ?>
        <?= view('cv/steps/step3_experience', ['sections' => $sections]) ?>
      <?php elseif ($step === 4): ?>
        <?= view('cv/steps/step4_skills', ['sections' => $sections]) ?>
      <?php else: ?>
        <?= view('cv/steps/step5_preview', ['sections' => $sections, 'selected' => $selected]) ?>
      <?php endif; ?>
    </div>
  </div>

  <footer class="wizard-footer">
    <?php if ($step === 5): ?>
      <p id="save-status">CV Anda sudah lengkap! Silakan unduh.</p>
    <?php else: ?>
      <p id="save-status">Belum ada perubahan.</p>
    <?php endif; ?>
    <div class="wizard-actions">
      <?php if ($step > 1): ?>
        <a class="btn secondary" href="/buat-cv/step/<?= (int) ($step - 1) ?>">Sebelumnya</a>
      <?php endif; ?>
      <?php if ($step < 5): ?>
        <a class="btn" href="/buat-cv/step/<?= (int) ($step + 1) ?>">Berikutnya</a>
      <?php endif; ?>
      <a class="btn secondary" href="/preview" target="_blank" rel="noopener">Buka Preview</a>
    </div>
  </footer>
</div>
</main>

<script src="/assets/js/autosave.js"></script>
<script src="/assets/js/repeatable.js"></script>
<script src="/assets/js/accessibility.js"></script>
<?php if ($step === 5): ?>
<script src="/assets/js/overflow.js"></script>
<script>
if (window.MangCvOverflow) window.MangCvOverflow.init();
</script>
<?php endif; ?>

</body>
</html>