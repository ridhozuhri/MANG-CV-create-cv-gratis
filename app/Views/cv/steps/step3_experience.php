<section>
  <h2>Step 3 - Pengalaman</h2>

  <div id="experience-list" class="repeatable"></div>
  <button id="experience-add" class="btn add-btn" type="button">+ Tambah Pengalaman</button>

  <template id="experience-row-template">
    <div class="row-card">
      <div class="row-card-fields">
        <input class="exp-company" placeholder="Perusahaan">
        <input class="exp-role" placeholder="Posisi">
        <input class="exp-year" placeholder="Periode (mis: 2025-2026)">
        <textarea class="exp-desc" placeholder="Deskripsi singkat"></textarea>
      </div>
      <button class="btn remove-btn exp-remove" type="button">Hapus</button>
    </div>
  </template>

  <script>
    window.__STEP_DATA__ = window.__STEP_DATA__ || {};
    window.__STEP_DATA__.experience = <?= json_encode($sections['experience'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
  </script>
</section>