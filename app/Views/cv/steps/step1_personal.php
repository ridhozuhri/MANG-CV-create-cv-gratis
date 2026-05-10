<section>
  <h2>Step 1 - Data Diri</h2>

  <div class="field">
    <div class="label">Nama Lengkap</div>
    <input id="name" placeholder="Contoh: Andi Pratama" value="<?= esc($sections['personal']['name'] ?? '') ?>">
  </div>

  <div class="field">
    <div class="label">Email</div>
    <input id="email" type="email" placeholder="contoh@email.com" value="<?= esc($sections['personal']['email'] ?? '') ?>">
  </div>

  <div class="field">
    <div class="label">Nomor Handphone</div>
    <input id="phone" type="tel" inputmode="tel" placeholder="Contoh: 0812xxxxxxx" value="<?= esc($sections['personal']['phone'] ?? '') ?>">
  </div>

  <div class="field">
    <div class="label">Informasi Opsional</div>
    <div class="option-grid" role="group" aria-label="Opsi tambahan">
      <label class="option-pill">
        <input id="opt-location" type="checkbox" onchange="toggleOptional('location', this.checked)">
        <span>Domisili</span>
      </label>
      <label class="option-pill">
        <input id="opt-birth" type="checkbox" onchange="toggleOptional('birth', this.checked)">
        <span>Tanggal Lahir</span>
      </label>
      <label class="option-pill">
        <input id="opt-linkedin" type="checkbox" onchange="toggleOptional('linkedin', this.checked)">
        <span>LinkedIn</span>
      </label>
      <label class="option-pill">
        <input id="opt-website" type="checkbox" onchange="toggleOptional('website', this.checked)">
        <span>Website</span>
      </label>
    </div>
    <p class="hint">Centang yang ingin ditampilkan di CV.</p>
  </div>

  <div id="block-location" class="optional-block">
    <div class="field">
      <div class="label">Domisili</div>
      <input id="location" placeholder="Contoh: Jakarta, Indonesia" value="<?= esc($sections['personal']['location'] ?? '') ?>">
    </div>
  </div>

  <div id="block-birth" class="optional-block">
    <div class="field">
      <div class="label">Tanggal Lahir</div>
      <input id="birth_date" type="date" value="<?= esc($sections['personal']['birth_date'] ?? '') ?>">
      <p class="hint">Opsional. Biasanya dipakai jika diminta perusahaan.</p>
    </div>
  </div>

  <div id="block-linkedin" class="optional-block">
    <div class="field">
      <div class="label">LinkedIn</div>
      <input id="linkedin" type="url" inputmode="url" placeholder="https://linkedin.com/in/username" value="<?= esc($sections['personal']['linkedin'] ?? '') ?>">
    </div>
  </div>

  <div id="block-website" class="optional-block">
    <div class="field">
      <div class="label">Website/Portfolio</div>
      <input id="website" type="url" inputmode="url" placeholder="https://nama-portfolio.com" value="<?= esc($sections['personal']['website'] ?? '') ?>">
    </div>
  </div>

  <div class="field">
    <div class="label">Foto Profil</div>
    <input id="photo" type="file" accept="image/jpeg,image/png,image/webp">
    <p class="hint">
      Rekomendasi: foto kotak 1:1 minimal 600x600px. Tipe: JPG/PNG/WEBP. Maksimal 2MB.
      Anda bisa mengganti foto kapan saja dengan memilih file baru.
    </p>
    <div class="photo-preview">
      <img id="photo-thumb" alt="Preview foto profil" <?php if (! empty($sections['personal']['photo_path'])): ?>src="/media/photo"<?php endif; ?>>
      <div>
        <div id="photo-status"><?= ! empty($sections['personal']['photo_path']) ? 'Foto terpasang' : 'Belum ada foto' ?></div>
        <div class="hint">Tips: wajah terlihat jelas, background netral.</div>
      </div>
    </div>
    <button id="photo-delete" class="btn danger" type="button">Hapus Foto</button>
  </div>

  <div class="field">
    <div class="label">Ringkasan Singkat</div>
    <textarea id="summary" placeholder="2-3 kalimat tentang Anda (opsional)"><?= esc($sections['personal']['summary'] ?? '') ?></textarea>
  </div>

  <!-- Honeypot anti-spam -->
  <input type="text" name="website_url" id="hp-website" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">

  <script>
  (function() {
    var savedData = <?= json_encode($sections['personal'] ?? [], JSON_UNESCAPED_UNICODE) ?>;

    function toggleOptional(name, show) {
      var block = document.getElementById('block-' + name);
      if (!block) return;

      if (show) {
        block.classList.add('is-visible');
        block.style.display = 'block';
      } else {
        block.classList.remove('is-visible');
        block.style.display = 'none';
        // Clear the field
        var inputMap = {
          'location': 'location',
          'birth': 'birth_date',
          'linkedin': 'linkedin',
          'website': 'website'
        };
        var fieldId = inputMap[name];
        if (fieldId) {
          var field = document.getElementById(fieldId);
          if (field && field.type !== 'file') {
            field.value = '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }
      }
    }

    window.toggleOptional = toggleOptional;

    window.addEventListener('DOMContentLoaded', function() {
      if (savedData.location) {
        var cb = document.getElementById('opt-location');
        if (cb) { cb.checked = true; toggleOptional('location', true); }
      }
      if (savedData.birth_date) {
        var cb2 = document.getElementById('opt-birth');
        if (cb2) { cb2.checked = true; toggleOptional('birth', true); }
      }
      if (savedData.linkedin) {
        var cb3 = document.getElementById('opt-linkedin');
        if (cb3) { cb3.checked = true; toggleOptional('linkedin', true); }
      }
      if (savedData.website) {
        var cb4 = document.getElementById('opt-website');
        if (cb4) { cb4.checked = true; toggleOptional('website', true); }
      }
    });
  })();
  </script>
</section>