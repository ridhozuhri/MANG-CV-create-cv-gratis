<section>
  <h2>Step 2 - Pendidikan</h2>

  <div id="education-list" class="repeatable"></div>
  <button id="education-add" class="btn add-btn" type="button">+ Tambah Pendidikan</button>

  <template id="education-row-template">
    <div class="row-card">
      <div class="row-card-fields">
        <input class="edu-school" placeholder="Nama sekolah/kampus">
        <input class="edu-degree" placeholder="Gelar/Jurusan">
        <input class="edu-year" placeholder="Tahun (mis: 2020-2024)">
      </div>
      <button class="btn remove-btn edu-remove" type="button">Hapus</button>
    </div>
  </template>

  <script>
    window.__STEP_DATA__ = window.__STEP_DATA__ || {};
    window.__STEP_DATA__.education = <?= json_encode($sections['education'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
  </script>
</section>