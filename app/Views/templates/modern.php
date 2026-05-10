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
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #222;
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
      color: #222;
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

    h1 { font-size: 22px; margin: 0 0 4px 0; font-weight: 800; color: #1a1a2e; letter-spacing: -0.5px; }
    h2 {
      font-size: 11px;
      margin: 10px 0 6px 0;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #6c5ce7;
      font-weight: 700;
      padding-bottom: 4px;
      border-bottom: 2px solid #6c5ce7;
    }
    h3 { font-size: 11px; margin: 0 0 2px 0; font-weight: 700; color: #1a1a2e; }
    p { margin: 1px 0; }

    /* Header: padded 20mm */
    .header {
      padding: 16mm 20mm 10mm 20mm;
      background: #fff;
      position: relative;
      overflow: hidden;
    }

    /* Decorative shapes as HTML divs — solid colors for Dompdf */
    .hdr-accent1 {
      position: absolute;
      top: 0;
      right: 0;
      width: 80px;
      height: 80px;
      background: #6c5ce7;
    }
    .hdr-accent2 {
      position: absolute;
      bottom: -20px;
      left: 0;
      width: 200px;
      height: 40px;
      background: #a29bfe;
    }

    /* Photo */
    .photo {
      width: 24mm;
      height: 24mm;
      object-fit: cover;
      border-radius: 2px;
      border: 3px solid #6c5ce7;
      display: block;
    }

    .contact-grid { margin-top: 6px; }
    .contact-chip {
      background: #f0effe;
      color: #6c5ce7;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 500;
      display: inline-block;
      margin: 2px 4px 2px 0;
    }

    /* Content + two-column table layout */
    .content { padding: 6mm 20mm 12mm 20mm; }
    .two-col { width: 100%; border-collapse: collapse; }
    .col-main { vertical-align: top; padding-right: 8mm; }
    .col-side { vertical-align: top; padding-left: 8mm; border-left: 2px solid #eee; }

    /* Cards */
    .card {
      background: #fafafa;
      border-radius: 4px;
      padding: 6px 8px;
      margin-bottom: 6px;
      border-left: 3px solid #6c5ce7;
      overflow: hidden;
    }
    .card-side {
      background: #f0effe;
      border-radius: 4px;
      padding: 5px 6px;
      margin-bottom: 5px;
      border-left: 3px solid #a29bfe;
      overflow: hidden;
    }

    .item-header { width: 100%; }
    .item-title { font-weight: 700; color: #1a1a2e; }
    .item-sub { color: #6c5ce7; font-size: 10px; font-weight: 500; }
    .item-date { color: #999; font-size: 10px; }

    .skill-wrap { margin-top: 4px; }
    .skill-dot {
      display: inline-block;
      background: #6c5ce7;
      color: #fff;
      padding: 3px 8px;
      font-size: 9px;
      font-weight: 500;
      margin: 2px 4px 2px 0;
      vertical-align: top;
    }
    .lang-dot {
      display: inline-block;
      background: #a29bfe;
      color: #fff;
      padding: 3px 8px;
      font-size: 9px;
      margin: 2px 4px 2px 0;
      vertical-align: top;
    }

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
    <div class="hdr-accent1" style="opacity:<?= $partial ? '0.15' : '1' ?>;"></div>
    <div class="hdr-accent2" style="opacity:<?= $partial ? '0.08' : '1' ?>;"></div>
    <table width="100%" cellpadding="0" cellspacing="0" style="position:relative;z-index:1;">
      <tr>
        <td width="72%">
          <h1><?= esc($sections['personal']['name'] ?? 'Nama') ?></h1>
          <div class="contact-grid">
            <?php if (! empty($sections['personal']['email'])): ?>
              <span class="contact-chip"><?= esc($sections['personal']['email']) ?></span>
            <?php endif; ?>
            <?php if (! empty($sections['personal']['phone'])): ?>
              <span class="contact-chip"><?= esc($sections['personal']['phone']) ?></span>
            <?php endif; ?>
            <?php if (! empty($sections['personal']['location'])): ?>
              <span class="contact-chip"><?= esc($sections['personal']['location']) ?></span>
            <?php endif; ?>
          </div>
          <?php if (! empty($sections['personal']['summary'])): ?>
            <p style="margin-top:8px; font-size:10px; color:#555; line-height:1.5;">
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
    <table class="two-col" cellpadding="0" cellspacing="0">
      <tr>
        <td class="col-main">
          <?php $eduItems = $sections['education']['items'] ?? []; ?>
          <?php if (is_array($eduItems) && count($eduItems)): ?>
            <h2>Pendidikan</h2>
            <?php foreach ($eduItems as $it): ?>
              <div class="card">
                <div class="item-header">
                  <span class="item-title"><?= esc($it['school'] ?? '') ?></span>
                  <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
                </div>
                <div class="item-sub"><?= esc($it['degree'] ?? '') ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php $expItems = $sections['experience']['items'] ?? []; ?>
          <?php if (is_array($expItems) && count($expItems)): ?>
            <h2>Pengalaman Kerja</h2>
            <?php foreach ($expItems as $it): ?>
              <div class="card">
                <div class="item-header">
                  <span class="item-title"><?= esc($it['company'] ?? '') ?></span>
                  <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
                </div>
                <div class="item-sub"><?= esc($it['role'] ?? '') ?></div>
                <?php if (! empty($it['desc'])): ?>
                  <p style="margin-top:3px; font-size:10px; color:#555;"><?= esc($it['desc']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </td>

        <td class="col-side">
          <?php $skillItems = $sections['skills']['items'] ?? []; ?>
          <?php if (is_array($skillItems) && count($skillItems)): ?>
            <h2 style="color:#6c5ce7; border-color:#6c5ce7;">Keahlian</h2>
            <div class="skill-wrap">
              <?php
              $skillLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir', 'expert' => 'Ahli'];
              foreach ($skillItems as $it):
                  $label = $skillLabels[$it['level']] ?? '';
                  $tag = esc($it['name'] ?? '');
                  if ($label) $tag .= ' (' . $label . ')';
                  echo '<span class="skill-dot">' . $tag . '</span>';
              endforeach;
              ?>
            </div>
          <?php endif; ?>

          <?php $langItems = $sections['languages']['items'] ?? []; ?>
          <?php if (is_array($langItems) && count($langItems)): ?>
            <h2 style="color:#a29bfe; border-color:#a29bfe; margin-top:10px;">Bahasa</h2>
            <div class="skill-wrap">
              <?php
              $langLabels = ['native' => 'Ibu', 'fluent' => 'Fasih', 'advanced' => 'Mahir', 'intermediate' => 'Menengah', 'beginner' => 'Dasar'];
              foreach ($langItems as $it):
                  $label = $langLabels[$it['level']] ?? '';
                  $tag = esc($it['name'] ?? '');
                  if ($label) $tag .= ' &ndash; ' . $label;
                  echo '<span class="lang-dot">' . $tag . '</span>';
              endforeach;
              ?>
            </div>
          <?php endif; ?>

          <?php if (! empty($sections['personal']['birth_date'])): ?>
            <h2 style="color:#555; border-color:#ddd; font-size:10px; margin-top:10px;">Info</h2>
            <div class="card-side">
              <p style="font-size:10px; color:#555;">Tgl Lahir: <?= esc($sections['personal']['birth_date']) ?></p>
            </div>
          <?php endif; ?>

          <?php if (! empty($sections['personal']['linkedin'])): ?>
            <div class="card-side">
              <p style="font-size:9px; color:#6c5ce7; word-break:break-all;"><?= esc($sections['personal']['linkedin']) ?></p>
            </div>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['website'])): ?>
            <div class="card-side">
              <p style="font-size:9px; color:#6c5ce7; word-break:break-all;"><?= esc($sections['personal']['website']) ?></p>
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
