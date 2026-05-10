<?php
$partial = (bool) ($options['partial'] ?? false);
$embedImages = (bool) ($options['embed_images'] ?? false);
$photoRel = (string) ($sections['personal']['photo_path'] ?? '');
$photoDataUri = '';
if ($embedImages && $photoRel !== '') {
    $prefix = 'writable/uploads/photos/';
    $file = str_starts_with($photoRel, $prefix) ? substr($photoRel, strlen($prefix)) : basename($photoRel);
    $abs = WRITEPATH . 'uploads/photos/' . $file;
    if (is_file($abs)) {
        $photoDataUri = 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($abs));
    }
}
?>
<?php if (! $partial): ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?= esc($sections['personal']['name'] ?? 'CV') ?> - MANG-CV</title>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
  <style>
    <?php if (! $partial): ?>
    @page { size: A4; margin: 0; }
    html, body { margin: 0; padding: 0; background: #fff; }
    body { width: 210mm; min-height: 297mm; }
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      color: #000;
      font-size: 10.5px;
      background: #fff;
      line-height: 1.6;
    }
    .page-wrapper {
      width: 210mm;
      min-height: 257mm;
      position: relative;
      background: #fff;
    }
    <?php else: ?>
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      color: #000;
      font-size: 10.5px;
      background: #fff;
      line-height: 1.6;
    }
    <?php endif; ?>

    <?php if ($partial): ?>
    .cv-page {
      width: 210mm;
      min-height: 297mm;
      box-sizing: border-box;
      background: #fff;
      margin: 0 auto;
      box-shadow: 0 4px 24px rgba(0, 0, 0, .12);
      padding: 20mm;
    }
    .page-wrapper {
      width: 210mm;
      min-height: 257mm;
      box-sizing: border-box;
      position: relative;
      background: #fff;
    }
    <?php endif; ?>

    /* All content inside a 170mm centered content area */
    .content-wrapper {
      width: 170mm;
      margin: 0 auto;
      box-sizing: border-box;
      padding: 0 20mm;
    }

    h1 {
      font-size: 26px;
      margin: 0 0 2px 0;
      font-weight: 300;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #000;
    }
    h2 {
      font-size: 8.5px;
      margin: 10px 0 4px 0;
      text-transform: uppercase;
      letter-spacing: 3.5px;
      color: #bbb;
      font-weight: 400;
      border: none;
      padding: 0;
    }
    h3 { font-size: 11px; margin: 0; font-weight: 600; color: #111; }
    p { margin: 0; }

    /* Photo: 18mm × 18mm */
    .photo {
      width: 18mm;
      height: 18mm;
      object-fit: cover;
      border-radius: 2px;
      display: block;
    }

    /* Header: table row — name left, photo right — Dompdf-safe */
    .header {
      padding-bottom: 6px;
      border-bottom: 1px solid #eee;
      margin-bottom: 4px;
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    .header-left { display: table-cell; width: 100%; vertical-align: top; padding-right: 10mm; }
    .header-right { display: table-cell; width: 18mm; text-align: right; vertical-align: top; }

    .contact-line { color: #888; font-size: 8.5px; margin-top: 4px; }
    .contact-line span { margin-right: 12px; }

    .section { margin-bottom: 5px; }

    .item-block { margin-bottom: 5px; }
    .item-row { display: table; width: 100%; }
    .item-title { display: table-cell; font-weight: 600; font-size: 10.5px; color: #000; }
    .item-date { display: table-cell; font-size: 8.5px; color: #aaa; white-space: nowrap; text-align: right; }
    .item-sub { font-size: 9px; color: #555; font-style: italic; margin-top: 1px; }
    .item-desc { font-size: 9px; color: #444; margin-top: 2px; line-height: 1.5; }

    .tag-line { font-size: 9px; color: #666; line-height: 1.6; }
    .tag-line span { margin-right: 10px; }

    /* Footer */
    .footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7px;
      color: #ccc;
      padding: 4px 20mm;
      border-top: 1px solid #eee;
    }
  </style>
<?php if (! $partial): ?>
</head>
<body>
<?php endif; ?>

<?php if ($partial): ?><div class="cv-page"><?php endif; ?>
<div class="page-wrapper">

  <div class="content-wrapper">
    <div class="header">
      <div class="header-left">
        <h1><?= esc($sections['personal']['name'] ?? strtoupper('Nama')) ?></h1>
        <div class="contact-line">
          <?php if (! empty($sections['personal']['email'])): ?>
            <span><?= esc($sections['personal']['email']) ?></span>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['phone'])): ?>
            <span><?= esc($sections['personal']['phone']) ?></span>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['location'])): ?>
            <span><?= esc($sections['personal']['location']) ?></span>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['linkedin'])): ?>
            <span><?= esc($sections['personal']['linkedin']) ?></span>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['website'])): ?>
            <span><?= esc($sections['personal']['website']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="header-right">
        <?php if ($photoDataUri !== ''): ?>
          <img class="photo" src="<?= esc($photoDataUri) ?>" alt="Foto Profil">
        <?php elseif ($photoRel !== ''): ?>
          <img class="photo" src="/media/photo" alt="Foto Profil">
        <?php endif; ?>
      </div>
    </div>

    <?php if (! empty($sections['personal']['summary'])): ?>
      <div class="section">
        <h2>About</h2>
        <p style="font-size:9.5px; color:#333; line-height:1.6;">
          <?= esc($sections['personal']['summary']) ?>
        </p>
      </div>
    <?php endif; ?>

    <?php $eduItems = $sections['education']['items'] ?? []; ?>
    <?php if (is_array($eduItems) && count($eduItems)): ?>
      <div class="section">
        <h2>Education</h2>
        <?php foreach ($eduItems as $it): ?>
          <div class="item-block">
            <div class="item-row">
              <span class="item-title"><?= esc($it['school'] ?? '') ?></span>
              <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
            </div>
            <div class="item-sub"><?= esc($it['degree'] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php $expItems = $sections['experience']['items'] ?? []; ?>
    <?php if (is_array($expItems) && count($expItems)): ?>
      <div class="section">
        <h2>Experience</h2>
        <?php foreach ($expItems as $it): ?>
          <div class="item-block">
            <div class="item-row">
              <span class="item-title"><?= esc($it['company'] ?? '') ?></span>
              <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
            </div>
            <div class="item-sub"><?= esc($it['role'] ?? '') ?></div>
            <?php if (! empty($it['desc'])): ?>
              <div class="item-desc"><?= esc($it['desc']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php $skillItems = $sections['skills']['items'] ?? []; ?>
    <?php if (is_array($skillItems) && count($skillItems)): ?>
      <div class="section">
        <h2>Skills</h2>
        <p class="tag-line">
          <?php
          $skillLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir', 'expert' => 'Ahli'];
          $tags = [];
          foreach ($skillItems as $it) {
              $label = $skillLabels[$it['level']] ?? '';
              $tag = esc($it['name'] ?? '');
              if ($label) $tag .= ' (' . $label . ')';
              $tags[] = $tag;
          }
          echo implode(' &nbsp;&middot;&nbsp; ', $tags);
          ?>
        </p>
      </div>
    <?php endif; ?>

    <?php $langItems = $sections['languages']['items'] ?? []; ?>
    <?php if (is_array($langItems) && count($langItems)): ?>
      <div class="section">
        <h2>Languages</h2>
        <p class="tag-line">
          <?php
          $langLabels = ['native' => 'Bahasa Ibu', 'fluent' => 'Fasih', 'advanced' => 'Mahir', 'intermediate' => 'Menengah', 'beginner' => 'Dasar'];
          $tags = [];
          foreach ($langItems as $it) {
              $label = $langLabels[$it['level']] ?? '';
              $tag = esc($it['name'] ?? '');
              if ($label) $tag .= ' (' . $label . ')';
              $tags[] = $tag;
          }
          echo implode(' &nbsp;&middot;&nbsp; ', $tags);
          ?>
        </p>
      </div>
    <?php endif; ?>

    <?php if (! empty($sections['personal']['birth_date'])): ?>
      <div class="section">
        <h2>Info</h2>
        <p class="tag-line"><span>Tgl Lahir: <?= esc($sections['personal']['birth_date']) ?></span></p>
      </div>
    <?php endif; ?>
  </div>

  <div class="footer">Made with MANG-CV &bull; <?= date('d/m/Y') ?></div>

</div>
<?php if ($partial): ?></div><?php endif; ?>

<?php if (! $partial): ?>
</body>
</html>
<?php endif; ?>