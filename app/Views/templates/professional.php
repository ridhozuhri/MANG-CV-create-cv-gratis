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
      font-family: 'Times New Roman', Georgia, serif;
      color: #000;
      font-size: 11px;
      background: #fff;
      line-height: 1.5;
    }
    .page-wrapper {
      width: 210mm;
      min-height: 257mm;
      position: relative;
      background: #fff;
    }
    <?php else: ?>
    body {
      font-family: 'Times New Roman', Georgia, serif;
      color: #000;
      font-size: 11px;
      background: #fff;
      line-height: 1.5;
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

    /* 170mm content area centered */
    .content-wrapper {
      width: 170mm;
      margin: 0 auto;
      box-sizing: border-box;
    }

    h1 {
      font-size: 20px;
      margin: 0 0 3px 0;
      text-align: center;
      font-weight: bold;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000;
    }
    h2 {
      font-size: 11px;
      margin: 10px 0 4px 0;
      text-transform: uppercase;
      font-weight: bold;
      border-bottom: 2px solid #000;
      padding-bottom: 2px;
      letter-spacing: 1px;
    }
    h3 { font-size: 11px; margin: 0 0 2px 0; font-weight: bold; color: #000; }
    p { margin: 0; }

    /* Header: centered, photo above name */
    .header {
      border-bottom: 3px double #000;
      padding-bottom: 6px;
      margin-bottom: 8px;
      text-align: center;
    }

    /* Photo: 26mm × 26mm centered */
    .photo {
      width: 26mm;
      height: 26mm;
      object-fit: cover;
      border-radius: 2px;
      display: block;
      margin: 0 auto 5px;
    }

    .contact-line { text-align: center; font-size: 9.5px; color: #444; margin-top: 3px; }

    /* Two-column layout: left 62% (~105mm), right 38% (~65mm) */
    .two-col { width: 100%; border-collapse: collapse; }
    .left-col { vertical-align: top; padding-right: 7mm; }
    .right-col { vertical-align: top; padding-left: 7mm; border-left: 1px solid #ddd; }

    .section-item { margin-bottom: 6px; }
    .item-header { display: table; width: 100%; }
    .item-title { display: table-cell; font-weight: bold; font-size: 10.5px; color: #000; }
    .item-date { color: #666; font-size: 9.5px; white-space: nowrap; }

    .right-section { margin-bottom: 6px; }
    .right-section-title {
      font-weight: bold;
      font-size: 10.5px;
      text-transform: uppercase;
      border-bottom: 1.5px solid #000;
      padding-bottom: 2px;
      margin-bottom: 4px;
    }

    .skill-line { font-size: 9.5px; margin: 2px 0; padding-left: 7px; border-left: 2px solid #000; color: #333; }
    .lang-line { font-size: 9.5px; margin: 2px 0; padding-left: 7px; border-left: 2px solid #999; color: #555; }
    .info-line { font-size: 9.5px; margin: 2px 0; color: #333; }

    /* Footer */
    .footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7px;
      color: #999;
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

  <div class="header">
    <div class="content-wrapper">
      <?php if ($photoDataUri !== ''): ?>
        <img class="photo" src="<?= esc($photoDataUri) ?>" alt="Foto Profil">
      <?php elseif ($photoRel !== ''): ?>
        <img class="photo" src="/media/photo" alt="Foto Profil">
      <?php endif; ?>

      <h1><?= esc($sections['personal']['name'] ?? 'Nama') ?></h1>

      <div class="contact-line">
        <?php
        $contacts = [];
        if (! empty($sections['personal']['email'])) $contacts[] = $sections['personal']['email'];
        if (! empty($sections['personal']['phone'])) $contacts[] = $sections['personal']['phone'];
        if (! empty($sections['personal']['location'])) $contacts[] = $sections['personal']['location'];
        if (! empty($sections['personal']['linkedin'])) $contacts[] = $sections['personal']['linkedin'];
        if (! empty($sections['personal']['website'])) $contacts[] = $sections['personal']['website'];
        echo implode(' &nbsp;&bull;&nbsp; ', $contacts);
        ?>
      </div>
    </div>
  </div>

  <div class="content-wrapper">
    <table class="two-col" cellpadding="0" cellspacing="0">
      <tr>
        <td class="left-col">
          <?php if (! empty($sections['personal']['summary'])): ?>
            <h2>Ringkasan</h2>
            <p style="text-align:justify; font-size:10px; line-height:1.6;">
              <?= esc($sections['personal']['summary']) ?>
            </p>
          <?php endif; ?>

          <?php $eduItems = $sections['education']['items'] ?? []; ?>
          <?php if (is_array($eduItems) && count($eduItems)): ?>
            <h2>Pendidikan</h2>
            <?php foreach ($eduItems as $it): ?>
              <div class="section-item">
                <div class="item-header">
                  <span class="item-title"><?= esc($it['school'] ?? '') ?></span>
                  <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
                </div>
                <p style="font-style:italic; font-size:9.5px; color:#444; margin-top:1px;">
                  <?= esc($it['degree'] ?? '') ?>
                </p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php $expItems = $sections['experience']['items'] ?? []; ?>
          <?php if (is_array($expItems) && count($expItems)): ?>
            <h2>Pengalaman Kerja</h2>
            <?php foreach ($expItems as $it): ?>
              <div class="section-item">
                <div class="item-header">
                  <span class="item-title"><?= esc($it['company'] ?? '') ?></span>
                  <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
                </div>
                <p style="font-size:9.5px; font-style:italic; color:#444; margin-top:1px;">
                  <?= esc($it['role'] ?? '') ?>
                </p>
                <?php if (! empty($it['desc'])): ?>
                  <p style="text-align:justify; font-size:9.5px; margin-top:2px; line-height:1.5;">
                    <?= esc($it['desc']) ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </td>

        <td class="right-col">
          <?php $skillItems = $sections['skills']['items'] ?? []; ?>
          <?php if (is_array($skillItems) && count($skillItems)): ?>
            <div class="right-section">
              <div class="right-section-title">Keahlian</div>
              <?php foreach ($skillItems as $it): ?>
                <div class="skill-line"><?= esc($it['name'] ?? '') ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php $langItems = $sections['languages']['items'] ?? []; ?>
          <?php if (is_array($langItems) && count($langItems)): ?>
            <div class="right-section">
              <div class="right-section-title">Bahasa</div>
              <?php
              $langLabels = ['native' => 'Bahasa Ibu', 'fluent' => 'Fasih', 'advanced' => 'Mahir', 'intermediate' => 'Menengah', 'beginner' => 'Dasar'];
              foreach ($langItems as $it):
                  $label = $langLabels[$it['level']] ?? '';
                  $tag = esc($it['name'] ?? '');
                  if ($label) $tag .= ' (' . $label . ')';
                  echo '<div class="lang-line">' . $tag . '</div>';
              endforeach;
              ?>
            </div>
          <?php endif; ?>

          <?php if (! empty($sections['personal']['birth_date'])): ?>
            <div class="right-section">
              <div class="right-section-title">Data Diri</div>
              <div class="info-line">Tgl Lahir: <?= esc($sections['personal']['birth_date']) ?></div>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

  <div class="footer">Dibuat dengan MANG-CV &bull; <?= date('d/m/Y') ?></div>

</div>
<?php if ($partial): ?></div><?php endif; ?>

<?php if (! $partial): ?>
</body>
</html>
<?php endif; ?>
