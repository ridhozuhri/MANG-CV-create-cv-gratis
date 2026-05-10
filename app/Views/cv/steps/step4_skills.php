<section>
  <h2>Step 4 - Skills & Bahasa</h2>

  <div class="field">
    <h3>Keahlian (Skills)</h3>
    <div id="skill-list" class="repeatable"></div>
    <button id="skill-add" class="btn add-btn" type="button">+ Tambah Skill</button>

    <template id="skill-row-template">
      <div class="row-card skill-card">
        <div class="row-card-fields">
          <input class="skill-name" placeholder="Nama skill (mis: PHP, Figma, Public Speaking)">
          <select class="skill-level">
            <option value="">Pilih Level</option>
            <option value="beginner">Beginer</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
            <option value="expert">Expert</option>
          </select>
        </div>
        <button class="btn remove-btn skill-remove" type="button">Hapus</button>
      </div>
    </template>
  </div>

  <div class="field">
    <h3>Bahasa</h3>
    <div id="language-list" class="repeatable"></div>
    <button id="language-add" class="btn add-btn" type="button">+ Tambah Bahasa</button>

    <template id="language-row-template">
      <div class="row-card skill-card">
        <div class="row-card-fields">
          <input class="lang-name" placeholder="Nama bahasa (mis: Inggris, Mandarin, Jawa)">
          <select class="lang-level">
            <option value="">Pilih Level</option>
            <option value="native">Native</option>
            <option value="fluent">Fluent</option>
            <option value="advanced">Advanced</option>
            <option value="intermediate">Intermediate</option>
            <option value="beginner">Beginner</option>
          </select>
        </div>
        <button class="btn remove-btn lang-remove" type="button">Hapus</button>
      </div>
    </template>
  </div>

  <script>
    window.__STEP_DATA__ = window.__STEP_DATA__ || {};
    window.__STEP_DATA__.skills = <?= json_encode($sections['skills'] ?? ['items' => []], JSON_UNESCAPED_UNICODE) ?>;
    window.__STEP_DATA__.languages = <?= json_encode($sections['languages'] ?? ['items' => []], JSON_UNESCAPED_UNICODE) ?>;
  </script>
</section>