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
    <?php endif; ?>

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #ecf0f1;
      font-size: 10px;
      background: #fff;
      line-height: 1.4;
    }

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
    }
    <?php endif; ?>

    .page-wrapper {
      width: 210mm;
      min-height: 257mm;
      background: #fff;
    }

    /* CSS table: full A4 page as table */
    .cv-table {
      display: table;
      width: 210mm;
      border-collapse: collapse;
      table-layout: fixed;
    }
    .cv-row { display: table-row; }
    .cv-cell { display: table-cell; vertical-align: top; }

    /* Sidebar: 84mm wide, dark bg */
    .sidebar-cell {
      width: 84mm;
      background: #1e272e;
      padding: 12mm 7mm 8mm 7mm;
    }
    <?php if ($partial): ?>
    .sidebar-cell { background: linear-gradient(160deg, #1e272e 0%, #2d3436 100%); }
    <?php endif; ?>

    /* Corner accent shape */
    .sidebar-corner {
      width: 22px;
      height: 22px;
      background: rgba(0,206,201,0.2);
      margin-bottom: 5px;
    }
    <?php if ($partial): ?>
    .sidebar-corner { border-radius: 0 0 0 22px; }
    <?php endif; ?>

    /* Photo */
    .photo {
      width: 24mm;
      height: 24mm;
      object-fit: cover;
      border-radius: 2px;
      border: 2px solid rgba(0, 206, 201, 0.5);
      margin-bottom: 5px;
      display: block;
    }

    h1 { font-size: 13px; margin: 0 0 2px 0; color: #fff; font-weight: 700; line-height: 1.2; }
    .sidebar-contact p {
      font-size: 7.5px;
      color: rgba(236, 240, 241, 0.7);
      margin: 2px 0;
      word-break: break-all;
      line-height: 1.3;
    }

    h2 {
      font-size: 7.5px;
      margin: 7px 0 3px 0;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #00cec9;
      font-weight: 700;
      padding-bottom: 2px;
      border-bottom: 1px solid rgba(0, 206, 201, 0.3);
    }

    .skill-badge {
      display: inline-block;
      background: rgba(0, 206, 201, 0.15);
      color: #00cec9;
      padding: 2px 4px;
      font-size: 7px;
      margin: 1px 1px 1px 0;
      border: 1px solid rgba(0, 206, 201, 0.25);
      line-height: 1.3;
      max-width: 100%;
      word-break: break-word;
    }
    .lang-badge {
      display: inline-block;
      background: rgba(255, 255, 255, 0.08);
      color: rgba(236, 240, 241, 0.8);
      padding: 2px 4px;
      font-size: 7px;
      margin: 1px 1px 1px 0;
      line-height: 1.3;
    }

    .divider { height: 1px; background: rgba(255, 255, 255, 0.1); margin: 6px 0; }

    /* Main content: fills rest of 210mm */
    .main-cell {
      width: 126mm;
      padding: 10mm 8mm 8mm 8mm;
    }

    .summary-box {
      background: #f8f9fa;
      border-left: 3px solid #00cec9;
      padding: 4px 5px;
      margin-bottom: 6px;
    }
    .summary-box p { color: #555; font-size: 8.5px; line-height: 1.4; }
    .summary-box h2 {
      color: #00cec9;
      border: none;
      margin: 0 0 2px 0;
      padding: 0;
      font-size: 7.5px;
    }

    .section-item { margin-bottom: 5px; }
    .item-header { display: table; width: 100%; }
    .item-title { font-weight: 700; color: #2d3436; font-size: 10px; }
    .item-date { color: #888; font-size: 8px; font-style: italic; }
    .item-role { color: #00cec9; font-size: 8.5px; font-weight: 600; margin-top: 1px; }
    .item-desc { font-size: 8px; color: #555; margin-top: 2px; line-height: 1.35; }

    /* Footer: inside page-wrapper at bottom */
    .footer {
      display: table-cell;
      width: 210mm;
      text-align: center;
      font-size: 7px;
      color: #ccc;
      padding: 3px 0;
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

  <div class="cv-table">
    <div class="cv-row">

      <!-- ── Sidebar ── -->
      <div class="cv-cell sidebar-cell">
        <div class="sidebar-corner"></div>
        <?php if ($photoDataUri !== ''): ?>
          <img class="photo" src="<?= esc($photoDataUri) ?>" alt="Foto Profil">
        <?php elseif ($photoRel !== ''): ?>
          <img class="photo" src="/media/photo" alt="Foto Profil">
        <?php endif; ?>

        <h1><?= esc($sections['personal']['name'] ?? 'Nama') ?></h1>

        <div class="sidebar-contact">
          <?php if (! empty($sections['personal']['email'])): ?>
            <p><?= esc($sections['personal']['email']) ?></p>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['phone'])): ?>
            <p><?= esc($sections['personal']['phone']) ?></p>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['location'])): ?>
            <p><?= esc($sections['personal']['location']) ?></p>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['linkedin'])): ?>
            <p><?= esc($sections['personal']['linkedin']) ?></p>
          <?php endif; ?>
          <?php if (! empty($sections['personal']['website'])): ?>
            <p><?= esc($sections['personal']['website']) ?></p>
          <?php endif; ?>
        </div>

        <?php if (! empty($sections['personal']['birth_date'])): ?>
          <div class="divider"></div>
          <h2>Info</h2>
          <p style="font-size:7.5px; color:rgba(236,240,241,0.7);">Tgl Lahir: <?= esc($sections['personal']['birth_date']) ?></p>
        <?php endif; ?>

        <?php $skillItems = $sections['skills']['items'] ?? []; ?>
        <?php if (is_array($skillItems) && count($skillItems)): ?>
          <div class="divider"></div>
          <h2>Keahlian</h2>
          <div>
            <?php
            $skillLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir', 'expert' => 'Ahli'];
            foreach ($skillItems as $it):
                $label = $skillLabels[$it['level']] ?? '';
                $tag = esc($it['name'] ?? '');
                if ($label) $tag .= ' (' . $label . ')';
                echo '<span class="skill-badge">' . $tag . '</span>';
            endforeach;
            ?>
          </div>
        <?php endif; ?>

        <?php $langItems = $sections['languages']['items'] ?? []; ?>
        <?php if (is_array($langItems) && count($langItems)): ?>
          <div class="divider"></div>
          <h2>Bahasa</h2>
          <div>
            <?php
            $langLabels = ['native' => 'Ibu', 'fluent' => 'Fasih', 'advanced' => 'Mahir', 'intermediate' => 'Menengah', 'beginner' => 'Dasar'];
            foreach ($langItems as $it):
                $label = $langLabels[$it['level']] ?? '';
                $tag = esc($it['name'] ?? '');
                if ($label) $tag .= ' (' . $label . ')';
                echo '<span class="lang-badge">' . $tag . '</span>';
            endforeach;
            ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── Main content ── -->
      <div class="cv-cell main-cell">
        <?php if (! empty($sections['personal']['summary'])): ?>
          <div class="summary-box">
            <h2>Tentang Saya</h2>
            <p><?= esc($sections['personal']['summary']) ?></p>
          </div>
        <?php endif; ?>

        <?php $eduItems = $sections['education']['items'] ?? []; ?>
        <?php if (is_array($eduItems) && count($eduItems)): ?>
          <h2 style="color:#00cec9; font-size:8px; margin:8px 0 4px 0; text-transform:uppercase; letter-spacing:1.5px; border-bottom:1px solid rgba(0,206,201,0.3); padding-bottom:2px;">Pendidikan</h2>
          <?php foreach ($eduItems as $it): ?>
            <div class="section-item">
              <div class="item-header">
                <span class="item-title"><?= esc($it['school'] ?? '') ?></span>
                <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
              </div>
              <p style="font-size:8px; color:#666; font-style:italic; margin-top:1px;">
                <?= esc($it['degree'] ?? '') ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php $expItems = $sections['experience']['items'] ?? []; ?>
        <?php if (is_array($expItems) && count($expItems)): ?>
          <h2 style="color:#00cec9; font-size:8px; margin:8px 0 4px 0; text-transform:uppercase; letter-spacing:1.5px; border-bottom:1px solid rgba(0,206,201,0.3); padding-bottom:2px;">Pengalaman Kerja</h2>
          <?php foreach ($expItems as $it): ?>
            <div class="section-item">
              <div class="item-header">
                <span class="item-title"><?= esc($it['company'] ?? '') ?></span>
                <span class="item-date"><?= esc($it['year'] ?? '') ?></span>
              </div>
              <p class="item-role"><?= esc($it['role'] ?? '') ?></p>
              <?php if (! empty($it['desc'])): ?>
                <p class="item-desc"><?= esc($it['desc']) ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div><!-- /.cv-row -->

    <!-- ── Footer ── -->
    <div class="cv-row">
      <div class="footer">Dibuat dengan MANG-CV &bull; <?= date('d/m/Y') ?></div>
    </div>

  </div><!-- /.cv-table -->

</div><!-- /.page-wrapper -->
<?php if ($partial): ?></div><?php endif; ?>

<?php if (! $partial): ?>
</body>
</html>
<?php endif; ?>
