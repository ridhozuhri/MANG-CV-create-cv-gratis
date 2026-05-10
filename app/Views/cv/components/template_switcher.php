<div class="template-switcher-panel">
  <div class="tsp-header">
    <span class="tsp-title">Pilih Template CV</span>
    <span id="tsp-current" class="tsp-current-name">Classic</span>
  </div>

  <div class="tsp-grid">
    <?php
    $templates = [
        'classic' => [
            'name' => 'Classic',
            'description' => 'Klasik profesional',
            'color' => '#16213e',
            'gradient' => 'linear-gradient(135deg, #16213e 0%, #0a84ff 100%)',
            'shape' => 'circle',
        ],
        'modern' => [
            'name' => 'Modern',
            'description' => 'Design kontemporer',
            'color' => '#6c5ce7',
            'gradient' => 'linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%)',
            'shape' => 'rounded',
        ],
        'sidebar' => [
            'name' => 'Sidebar',
            'description' => 'Layout dengan sidebar',
            'color' => '#1e272e',
            'gradient' => 'linear-gradient(160deg, #1e272e 0%, #2d3436 100%)',
            'shape' => 'square',
        ],
        'minimalist' => [
            'name' => 'Minimalist',
            'description' => 'Clean dan sederhana',
            'color' => '#000000',
            'gradient' => 'transparent',
            'shape' => 'line',
        ],
        'professional' => [
            'name' => 'Professional',
            'description' => 'Formal tradisional',
            'color' => '#1a365d',
            'gradient' => 'transparent',
            'shape' => 'serif',
        ],
    ];
    $selected = $selected ?? 'classic';
    ?>

    <?php foreach ($templates as $key => $tmpl): ?>
      <button
        type="button"
        class="tsp-card <?= $key === $selected ? 'tsp-active' : '' ?>"
        data-template="<?= esc($key) ?>"
        data-name="<?= esc($tmpl['name']) ?>"
        aria-label="Pilih template <?= esc($tmpl['name']) ?>"
      >
        <div class="tsp-thumb" style="background: <?= esc($tmpl['gradient']) !== 'transparent' ? $tmpl['gradient'] : '#f5f5f5' ?>;">
          <?php if ($tmpl['shape'] === 'circle'): ?>
            <div class="tsp-shape tsp-circle"></div>
            <div class="tsp-lines">
              <div class="tsp-line tsp-line-short"></div>
              <div class="tsp-line"></div>
              <div class="tsp-line tsp-line-short"></div>
            </div>
          <?php elseif ($tmpl['shape'] === 'rounded'): ?>
            <div class="tsp-shape tsp-rounded"></div>
            <div class="tsp-lines">
              <div class="tsp-line tsp-line-short"></div>
              <div class="tsp-line"></div>
              <div class="tsp-line tsp-line-short"></div>
            </div>
          <?php elseif ($tmpl['shape'] === 'square'): ?>
            <div class="tsp-sidebar-shape">
              <div class="tsp-sidebar-bar"></div>
              <div class="tsp-sidebar-content">
                <div class="tsp-line tsp-line-short"></div>
                <div class="tsp-line"></div>
              </div>
            </div>
          <?php elseif ($tmpl['shape'] === 'line'): ?>
            <div class="tsp-lines tsp-center">
              <div class="tsp-h1-line"></div>
              <div class="tsp-line tsp-line-short"></div>
              <div class="tsp-line"></div>
            </div>
          <?php else: ?>
            <div class="tsp-serif-preview">
              <div class="tsp-serif-h1"></div>
              <div class="tsp-serif-border"></div>
              <div class="tsp-line tsp-line-short"></div>
              <div class="tsp-line"></div>
            </div>
          <?php endif; ?>
        </div>
        <div class="tsp-info">
          <strong><?= esc($tmpl['name']) ?></strong>
          <small><?= esc($tmpl['description']) ?></small>
        </div>
      </button>
    <?php endforeach; ?>
  </div>
</div>
