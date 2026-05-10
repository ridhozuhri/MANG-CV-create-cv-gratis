# PHASE-1-QA-MANG-CV

Dokumen ini adalah checklist verifikasi Phase 1 (MVP) yang harus lulus sebelum masuk Phase 2.

## Implementasi Phase 1 (Match Blueprint)
- Multi-step wizard: `/buat-cv/step/1..3`.
- Session anonim tersimpan di DB (`cv_sessions`) dan cookie session persist 30 hari.
- Step 1: personal + upload foto.
- Step 2: education repeatable.
- Step 3: experience repeatable.
- Autosave (debounce 2 detik) menyimpan ke `cv_data`.
- Preview template Classic update realtime (auto refresh setelah autosave).
- Export PDF Classic (tanpa caching dan tanpa rate limit).

## Pengujian Teknis Yang Sudah Dijalankan
- `php spark migrate` -> sukses.
- `php spark migrate:status` -> core migration terpasang.
- `php spark routes` -> semua route phase 1 terdaftar.
- `php -l` backend utama -> no syntax errors.

## Target Uji Phase 1 (Wajib Lulus)
- [ ] Session baru: buka `/buat-cv/step/1` -> ada row baru di `cv_sessions`.
- [ ] Session kembali < 30 hari: tutup browser, buka lagi -> data tetap ada.
- [ ] Session expired: set manual `expires_at` ke masa lalu -> session baru dibuat.
- [ ] Autosave: isi field -> status "Tersimpan" muncul, row `cv_data` ter-update.
- [ ] Persist data: refresh halaman -> data tetap muncul.
- [ ] Repeatable: tambah/hapus pendidikan & pengalaman -> tersimpan ke DB.
- [ ] Preview realtime: setelah autosave, preview ikut berubah tanpa klik tombol.
- [ ] PDF: klik Download PDF -> file terunduh dan dapat dibuka.

## Catatan
- Smoke test shared hosting tetap dilakukan di akhir Phase 1 sesuai blueprint.
