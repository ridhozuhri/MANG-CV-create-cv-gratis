<?php
$analysis = $analysis ?? null;
$show = $analysis && isset($analysis['has_overflow']) && $analysis['has_overflow'];
?>

<div id="overflow-alert" class="overflow-alert" style="<?= $show ? 'display:flex;' : 'display:none;' ?>">
  <div class="overflow-icon">!</div>
  <div class="overflow-content">
    <?php if ($show): ?>
      <strong>Perhatian</strong>
      <p>Template <span class="template-name"><?= esc($analysis['template_info']['name'] ?? '') ?></span> hanya bisa menampilkan:</p>
      <ul class="overflow-list">
        <?php foreach ($analysis['overflow_sections'] as $item): ?>
          <?php
          $sectionNames = [
              'education' => 'Pendidikan',
              'experience' => 'Pengalaman',
              'skills' => 'Keahlian',
              'languages' => 'Bahasa',
          ];
          $name = $sectionNames[$item['section']] ?? $item['section'];
          ?>
          <li><?= esc($name) ?> (<?= (int) $item['current'] ?> dari <?= (int) $item['max'] ?> maks)</li>
        <?php endforeach; ?>
      </ul>
      <p>Silakan hapus beberapa item atau <a href="#" onclick="MangCvOverflow.switchToRecommended(); return false;">ganti ke template <?= esc($analysis['recommended_template'] ?? '') ?></a>.</p>
    <?php else: ?>
      <strong>Perhatian</strong>
      <p>Loading...</p>
    <?php endif; ?>
  </div>
</div>

<div id="page-estimate" class="page-estimate" style="display:none;">
  <span class="page-estimate-icon"></span>
  <span class="page-estimate-text">1 halaman</span>
</div>

<div id="template-recommendation" class="template-recommendation" style="display:none;">
  <span>Rekomendasi:</span>
  <a href="#" onclick="return false;">-</a>
</div>