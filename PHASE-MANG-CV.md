# PHASE-MANG-CV

Dokumen ini adalah breakdown fase implementasi **backend** dan **frontend** yang sinkron dengan `blueprint-create-cv-gratis.md` (Bagian 16: Fase Pengerjaan), ditambah target pengujian per fase.

## Prinsip Eksekusi
- Prioritas: Aksesibilitas -> Keamanan -> Performa -> Fitur.
- Stack wajib: CodeIgniter 4 + MySQL/MariaDB + Vanilla JS (tanpa framework JS).
- Model user: anonymous session (tanpa login), retensi data 30 hari.

## Phase 1 - MVP Foundation 

### Scope Backend
- Setup project CI4, `.env`, `.htaccess`, koneksi database.
- Implementasi seluruh tabel inti: `cv_sessions`, `cv_data`, `export_logs`, `rate_limits`, `abuse_reports`.
- Implementasi `SessionManager` + `SessionInitFilter` untuk pembuatan/validasi session anonim.
- Implementasi `CvSessionModel`, `CvDataModel`.
- Implementasi controller dasar `Cv.php` (`wizard()`, `step()`).
- Implementasi `Api::autosave()`.
- Implementasi `TemplateManager::renderToHtml()` untuk template `classic`.
- Implementasi `PdfGenerator` dan `Export::pdf()` (tanpa caching pada fase ini).

### Scope Frontend
- Skeleton landing page sederhana + CTA ke wizard.
- Wizard step dasar:
  - Step 1: Personal + foto.
  - Step 2: Education (repeatable).
  - Step 3: Experience (repeatable).
- Integrasi `autosave.js` (debounce 2 detik).
- Integrasi `preview.js` untuk live preview template Classic.

### Target Pengujian Phase 1
- **Session test:** session baru terbentuk, session kembali < 30 hari tetap valid, session expired ditolak.
- **Autosave test:** perubahan field tersimpan otomatis ke `cv_data`, refresh tidak menghilangkan data.
- **Wizard persistence test:** pindah step bolak-balik tidak kehilangan data.
- **Preview test:** perubahan input tampil realtime di preview Classic.
- **PDF smoke test:** file PDF bisa diunduh dan dibuka normal.
- **Deployment smoke test:** app berjalan di shared hosting tanpa error PHP fatal.

### Deliverable Phase 1
- 3 step form aktif, autosave aktif, preview Classic aktif, export PDF aktif.

## Phase 2 - Template & Multi-Export 

### Scope Backend
- Implementasi 4 template tambahan: `modern`, `sidebar`, `minimalist`, `professional`.
- Implementasi `Api::switchTemplate()`.
- Implementasi Step 4 & Step 5 endpoint/data handling.
- Implementasi `Export::txt()` dan `Export::json()`.
- Implementasi `RateLimiter` + `RateLimitModel`.
- Aktivasi rate limit endpoint export.
- Implementasi PDF caching di `Export::pdf()`.

### Scope Frontend
- Komponen `template_switcher.php` + JS pergantian template tanpa kehilangan data.
- Step 4: Skills & Languages form.
- Step 5: Preview & Download hub (PDF/TXT/JSON).
- UX feedback saat rate limit tercapai.

### Target Pengujian Phase 2
- **Template switch test:** 5 template bisa dipilih, preview update benar, data tetap persisten.
- **Export format test:** PDF/TXT/JSON dapat diunduh dengan konten valid.
- **Rate limit test:** melebihi batas export memunculkan response limit + pesan ramah.
- **Cache test:** export PDF kedua untuk konten sama lebih cepat dan log `was_cached = 1`.
- **Repeatable field test:** Step 2/3/4 tambah-hapus item sinkron ke DB.

### Deliverable Phase 2
- 5 template aktif, 3 format export aktif, rate limiting aktif, caching PDF aktif.

## Phase 3 - Intelligence & Robustness (Target: 1 Minggu)

### Scope Backend
- Implementasi `ContentAnalyzer` untuk hitung kapasitas/overflow.
- Implementasi estimasi halaman `ContentAnalyzer::estimatedPages()`.
- Implementasi mode ringkasan (truncation/peringatan detail).
- Implementasi `Cron::cleanup()` + proteksi secret key.
- Implementasi rate limit berbasis IP (`RateLimiter::checkIpBased()`).

### Scope Frontend
- Integrasi `overflow.js` + `overflow_warning.php`.
- Tampilkan status overflow per template secara realtime.
- Tampilkan estimasi jumlah halaman di Step 5.
- UI guidance saat konten perlu diringkas.

### Target Pengujian Phase 3
- **Overflow accuracy test:** skenario over-capacity terdeteksi konsisten pada semua template.
- **Summary mode test:** konten panjang ditangani sesuai strategi tanpa merusak struktur CV.
- **Estimated pages test:** estimasi halaman tampil dan masuk akal terhadap hasil PDF aktual.
- **Cron cleanup test:** data expired dan rate limit usang terhapus sesuai jadwal.
- **IP abuse test:** lonjakan request dari IP sama terdeteksi dan dibatasi.

### Deliverable Phase 3
- Overflow detection aktif, mode ringkasan aktif, cleanup otomatis aktif, proteksi IP-level aktif.

## Phase 4 - Polish & Production Ready (Target: 1 Minggu)

### Scope Backend
- Hardening keamanan: honeypot, abuse report logging, flagging session abuse.
- Audit CSRF seluruh form dan AJAX.
- Audit dan finalisasi security headers.
- Optimasi production config (error handling, cache header, gzip jika tersedia).

### Scope Frontend
- Aksesibilitas penuh: high contrast, font scaler, voice guide, skip link, ARIA live updates.
- SEO implementasi: meta dinamis, JSON-LD Schema.org, sitemap, robots.txt.
- Optimasi performa aset: minify CSS/JS, optimasi media, versioned assets.
- Validasi final mobile-first 360px dan keyboard-only interaction.

### Target Pengujian Phase 4
- **Accessibility QA:** WCAG 2.1 AA checklist lulus (keyboard nav, screen reader, contrast, focus order).
- **Security QA:** XSS escape, CSRF enforcement, endpoint protection, upload validation, cron secret validation.
- **SEO QA:** robots/sitemap/schema/meta terpasang benar dan terverifikasi.
- **Mobile QA:** 360px tanpa horizontal scroll, tap target >= 44x44, flow download berjalan.
- **Cross-browser QA:** Chrome, Firefox, Safari iOS, Samsung Internet, Edge.
- **Performance QA:** landing < 3 detik (simulasi 3G), preview responsif, PDF generation stabil.

### Deliverable Phase 4
- Aplikasi production-ready: aman, aksesibel, cepat, dan siap deploy shared hosting.

## Gate Kualitas Antar Phase
- Tidak boleh lanjut phase berikutnya sebelum target pengujian phase aktif dinyatakan lulus.
- Semua bug blocker/security/high severity harus ditutup di phase berjalan.
- Hasil uji dicatat per phase (tanggal, environment, skenario, hasil, tindak lanjut).

## Referensi Sinkronisasi Blueprint
- Bagian 16: Fase Pengerjaan (MVP -> Production).
- Bagian 12: Keamanan & Anti-Abuse.
- Bagian 13: Aksesibilitas.
- Bagian 14: SEO & Landing Page.
- Bagian 15: Optimasi Shared Hosting.
- Bagian 18: Checklist QA.

---

## Setup Cronjob

### Linux Shared Hosting (Cron Tab)

Cronjob di Linux shared hosting dijalankan via `crontab`. Akses via SSH atau panel hosting (cPanel/DirectAdmin).

#### Cara 1: Command Line (Recommended)

```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut (jalan setiap 1 jam)
0 * * * * cd /path/to/mang-cv && php spark cv:cleanup --force >> /dev/null 2>&1
```

#### Cara 2: Via cPanel

1. Login ke cPanel
2. Buka **Cron Jobs** (Advanced section)
3. Tambahkan cron job baru:
   - **Minute:** `0`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:**
     ```
     cd /home/username/public_html/mang-cv && php spark cv:cleanup --force >> /dev/null 2>&1
     ```
4. Klik **Add New Cron Job**

#### Cara 3: HTTP Cron (Tanpa CLI)

Jika hosting tidak support SSH/cron, gunakan service HTTP cron (cron-job.org, easycron.com):

```
URL: https://domain.com/cron/cleanup?secret=CV_GRATIS_MANGIDO_CRON_SECRET_2026
Schedule: Every hour
```

#### Command Available

```bash
# Cleanup normal (hanya jalan jika interval 1 jam sudah tercapai)
php spark cv:cleanup

# Cleanup paksa (langsung jalan, abaikan interval lock)
php spark cv:cleanup --force
```

#### Cek Status Cleanup

```bash
php spark cv:cleanup
# Output:
# Starting MANG-CV cleanup...
# Last run: 2026-05-09 12:00:00
# Next run in: 3600 seconds
#
#   Expired sessions: 5
#   Orphan photos: 2
#   Expired rate limits: 10
#   Orphan pdf cache: 3
#   Old abuse reports: 0
#
# Cleanup completed successfully.
```

#### Yang Dibersihkan

| Jenis | Kondisi |
|-------|---------|
| `cv_sessions` | Expired > 30 hari |
| `cv_data` | Terikat session expired |
| `export_logs` | Terikat session expired |
| `rate_limits` | > 7 hari atau session expired |
| `abuse_reports` | > 30 hari |
| Foto orphan | File foto tanpa session aktif |
| PDF cache | File cache > 1 jam |

#### Setup Secret Key

Secret key sudah diset di `.env`:

```
CCG_CRON_SECRET = cv-gratis-mangido-cron-secret-2026
```

Untuk shared hosting production, ganti dengan secret yang lebih kuat:

```bash
# Generate random secret
openssl rand -hex 32
```

Lalu update di `.env` hosting production.

#### Troubleshooting

```bash
# Cek cron jalan atau tidak
crontab -l

# Test manual
php spark cv:cleanup --force

# Cek log error PHP
tail -f writable/logs/log-$(date +%Y-%m-%d).php
```
