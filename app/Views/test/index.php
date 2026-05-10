<?php
/**
 * Test Page untuk MANG-CV
 * Gunakan halaman ini untuk verifikasi semua routes berfungsi
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test MANG-CV</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
    h1 { color: #333; }
    .test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .test-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
    .test-item:last-child { border-bottom: none; }
    a { color: #0066cc; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .status { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    .pending { background: #fff3cd; color: #856404; }
    .success { background: #d4edda; color: #155724; }
    .info { background: #cce5ff; color: #004085; }
    h2 { margin-top: 0; color: #555; font-size: 16px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
    .grid a { display: block; padding: 15px; background: #e9ecef; border-radius: 6px; text-align: center; transition: background 0.2s; }
    .grid a:hover { background: #dee2e6; }
  </style>
</head>
<body>
  <h1>Test Page - MANG-CV</h1>
  <p>Halaman ini untuk verifikasi semua komponen berfungsi.</p>

  <div class="test-section">
    <h2>Step Wizard</h2>
    <div class="grid">
      <a href="/buat-cv">Start Wizard</a>
      <a href="/buat-cv/step/1">Step 1 - Personal</a>
      <a href="/buat-cv/step/2">Step 2 - Education</a>
      <a href="/buat-cv/step/3">Step 3 - Experience</a>
      <a href="/buat-cv/step/4">Step 4 - Skills</a>
      <a href="/buat-cv/step/5">Step 5 - Preview</a>
    </div>
  </div>

  <div class="test-section">
    <h2>Preview & Export</h2>
    <div class="grid">
      <a href="/preview">Preview CV</a>
      <a href="/export/pdf" target="_blank">Export PDF</a>
      <a href="/export/txt" target="_blank">Export TXT</a>
      <a href="/export/json" target="_blank">Export JSON</a>
    </div>
  </div>

  <div class="test-section">
    <h2>API Endpoints (POST)</h2>
    <div class="test-item">
      <code>POST /api/autosave</code>
      <span class="status pending">Test Manual</span>
    </div>
    <div class="test-item">
      <code>POST /api/preview</code>
      <span class="status pending">Test Manual</span>
    </div>
    <div class="test-item">
      <code>POST /api/preview-draft</code>
      <span class="status pending">Test Manual</span>
    </div>
    <div class="test-item">
      <code>POST /api/upload-photo</code>
      <span class="status pending">Test Manual</span>
    </div>
  </div>

  <div class="test-section">
    <h2>Quick Actions</h2>
    <div class="test-item">
      <span>Start fresh session</span>
      <a href="/buat-cv/step/1">Buka Wizard</a>
    </div>
  </div>

  <script>
    // Auto-refresh preview every 5 seconds
    console.log('Test page loaded. Use browser console for API testing.');
  </script>
</body>
</html>