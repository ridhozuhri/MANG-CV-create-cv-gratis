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
    /*
     * A4 page: 210mm × 297mm
     * PDF mode: body IS the page (210mm × 297mm)
     * Preview mode: .cv-page wraps with padding + shadow
     * All content stays within 170mm content area (210mm − 2×20mm margins)
     */
    <?php if (! $partial): ?>
    @page { size: A4; margin: 0; }
    html, body { margin: 0; padding: 0; background: #fff; }
    body { width: 210mm; min-height: 297mm; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #1a1a2e;
      font-size: 11px;
      background: #fff;
      line-height: 1.5;
    }
    .page-wrapper {
      width: 210mm;
      min-height: 257mm;
      position: relative;
      background: #fff;
      overflow: hidden;
    }
    <?php else: ?>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #1a1a2e;
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
      overflow: hidden;
    }
    <?php endif; ?>

    /* Header: full 210mm, padded 20mm */
    .header {
      background: #0a84ff;
      padding: 20mm 20mm 12mm 20mm;
      position: relative;
      overflow: hidden;
    }

    /* Decorative circles — solid white divs */
    .hdr-circle1 {
      position: absolute;
      top: -30px;
      right: -30px;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: #fff;
      opacity: 0.06;
    }
    .hdr-circle2 {
      position: absolute;
      bottom: -50px;
      left: 40%;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: #fff;
      opacity: 0.04;
    }

    /* Photo: 26mm × 26mm */
    .photo {
      width: 26mm;
      height: 26mm;
      object-fit: cover;
      border-radius: 3px;
      border: 3px solid rgba(255, 255, 255, 0.4);
      display: block;
    }

    h1 {
      font-size: 22px;
      margin: 0 0 4px 0;
      color: #fff;
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    h2 {
      font-size: 11px;
      margin: 12px 0 6px 0;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #0a84ff;
      border-bottom: 2px solid #0a84ff;
      padding-bottom: 4px;
    }
    .section-item-header { font-weight: 700; color: #16213e; }
    .section-item-sub { color: #666; font-size: 10px; }
    p { margin: 2px 0; }

    .contact-row { margin-top: 6px; }
    .contact-item { font-size: 10px; color: rgba(255, 255, 255, 0.9); margin-right: 16px; }

    /* Content: padded, with left accent stripe */
    .content {
      padding: 12mm 20mm;
      position: relative;
      overflow: hidden;
    }
    .content-accent {
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: #0a84ff;
    }

    .section-item {
      margin: 0 0 8px 0;
      padding: 5px 8px;
      border-left: 3px solid #0a84ff;
      background: #e8f4ff;
      border-radius: 3px;
      overflow: hidden;
    }

    .skill-tags { margin-top: 4px; }
    .skill-tag {
      display: inline-block;
      background: #e8f4ff;
      color: #0a84ff;
      padding: 3px 8px;
      font-size: 10px;
      font-weight: 600;
      border: 1px solid rgba(10, 132, 255, 0.2);
      margin: 2px 4px 2px 0;
      vertical-align: top;
    }

    .lang-tags { margin-top: 4px; }
    .lang-tag {
      display: inline-block;
      background: #f0f0f0;
      color: #555;
      padding: 3px 8px;
      font-size: 10px;
      margin: 2px 4px 2px 0;
      vertical-align: top;
    }

    /* Footer: fixed at bottom of page */
    .footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7px;
      color: #ccc;
      padding: 4px 20mm;
      background: #f5f5f7;
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
    <div class="hdr-circle1"></div>
    <div class="hdr-circle2"></div>
    <table width="100%" cellpadding="0" cellspacing="0" style="position:relative;z-index:1;">
      <tr>
        <td width="72%">
          <h1><?= esc($sections['personal']['name'] ?? 'Nama Belum Diisi') ?></h1>
          <div class="contact-row">
            <?php if (! empty($sections['personal']['email'])): ?>
              <span class="contact-item"><?= esc($sections['personal']['email']) ?></span>
            <?php endif; ?>
            <?php if (! empty($sections['personal']['phone'])): ?>
              <span class="contact-item"><?= esc($sections['personal']['phone']) ?></span>
            <?php endif; ?>
            <?php if (! empty($sections['personal']['location'])): ?>
              <span class="contact-item"><?= esc($sections['personal']['location']) ?></span>
            <?php endif; ?>
          </div>
          <?php if (! empty($sections['personal']['summary'])): ?>
            <p style="margin-top:8px; font-size:10px; color:rgba(255,255,255,0.85); line-height:1.5;">
              <?= esc($sections['personal']['summary']) ?>
            </p>
          <?php endif; ?>
        </td>
        <td width="28%" style="text-align:right; vertical-align:top;">
          <?php if ($photoDataUri !== ''): ?>
            <img class="photo" src="<?= esc($photoDataUri) ?>" alt="Foto Profil">
          <?php elseif ($photoRel !== ''): ?>
            <img class="photo" src="/media/photo" alt="Foto Profil">
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

  <div class="content">
    <div class="content-accent"></div>

    <?php $eduItems = $sections['education']['items'] ?? []; ?>
    <?php if (is_array($eduItems) && count($eduItems)): ?>
      <h2>Pendidikan</h2>
      <?php foreach ($eduItems as $it): ?>
        <div class="section-item">
          <div class="section-item-header"><?= esc($it['school'] ?? '') ?></div>
          <div class="section-item-sub"><?= esc($it['degree'] ?? '') ?> &bull; <?= esc($it['year'] ?? '') ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php $expItems = $sections['experience']['items'] ?? []; ?>
    <?php if (is_array($expItems) && count($expItems)): ?>
      <h2>Pengalaman Kerja</h2>
      <?php foreach ($expItems as $it): ?>
        <div class="section-item">
          <div class="section-item-header"><?= esc($it['company'] ?? '') ?> &mdash; <?= esc($it['role'] ?? '') ?></div>
          <div class="section-item-sub"><?= esc($it['year'] ?? '') ?></div>
          <?php if (! empty($it['desc'])): ?>
            <p style="margin-top:3px; font-size:10px; color:#444;"><?= esc($it['desc']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php $skillItems = $sections['skills']['items'] ?? []; ?>
    <?php if (is_array($skillItems) && count($skillItems)): ?>
      <h2>Keahlian</h2>
      <div class="skill-tags">
        <?php
        $skillLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir', 'expert' => 'Ahli'];
        foreach ($skillItems as $it):
            $label = $skillLabels[$it['level']] ?? '';
            $tag = esc($it['name'] ?? '');
            if ($label) $tag .= ' &ndash; ' . $label;
            echo '<span class="skill-tag">' . $tag . '</span>';
        endforeach;
        ?>
      </div>
    <?php endif; ?>

    <?php $langItems = $sections['languages']['items'] ?? []; ?>
    <?php if (is_array($langItems) && count($langItems)): ?>
      <h2>Bahasa</h2>
      <div class="lang-tags">
        <?php
        $langLabels = ['native' => 'Bahasa Ibu', 'fluent' => 'Fasih', 'advanced' => 'Mahir', 'intermediate' => 'Menengah', 'beginner' => 'Dasar'];
        foreach ($langItems as $it):
            $label = $langLabels[$it['level']] ?? '';
            $tag = esc($it['name'] ?? '');
            if ($label) $tag .= ' &ndash; ' . $label;
            echo '<span class="lang-tag">' . $tag . '</span>';
        endforeach;
        ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="footer">Dibuat dengan MANG-CV &bull; <?= date('d/m/Y') ?></div>

</div>
<?php if ($partial): ?></div><?php endif; ?>

<?php if (! $partial): ?>
</body>
</html>
<?php endif; ?>
