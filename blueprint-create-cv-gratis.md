# Blueprint Teknis: Create CV Gratis
**Versi:** 1.0.0  
**Target Deploy:** Shared Hosting PHP 8.3 + MySQL/MariaDB  
**Framework:** CodeIgniter 4 (CI4)  
**Status:** Production-Ready Blueprint  

---

## Daftar Isi

1. [Filosofi & Prinsip Arsitektur](#1-filosofi--prinsip-arsitektur)
2. [Struktur Folder Lengkap](#2-struktur-folder-lengkap)
3. [Skema Database](#3-skema-database)
4. [Konfigurasi Environment](#4-konfigurasi-environment)
5. [Routing](#5-routing)
6. [Controllers](#6-controllers)
7. [Models](#7-models)
8. [Libraries](#8-libraries)
9. [Helpers](#9-helpers)
10. [Views & Templates](#10-views--templates)
11. [Frontend Architecture](#11-frontend-architecture)
12. [Keamanan & Anti-Abuse](#12-keamanan--anti-abuse)
13. [Aksesibilitas](#13-aksesibilitas)
14. [SEO & Landing Page](#14-seo--landing-page)
15. [Optimasi Shared Hosting](#15-optimasi-shared-hosting)
16. [Fase Pengerjaan](#16-fase-pengerjaan)
17. [Panduan Deployment](#17-panduan-deployment)
18. [Checklist QA](#18-checklist-qa)

---

## 1. Filosofi & Prinsip Arsitektur

### 1.1 Prinsip Utama

Seluruh keputusan teknis dalam proyek ini harus mengikuti urutan prioritas berikut:

**Ketersediaan untuk semua orang** → **Keamanan** → **Performa** → **Fitur**

Artinya, ketika ada tradeoff antara fitur canggih dan aksesibilitas, aksesibilitas selalu menang. Ketika ada tradeoff antara fitur baru dan keamanan, keamanan selalu menang.

### 1.2 Batasan Keras (Hard Constraints)

Semua implementasi WAJIB tunduk pada batasan berikut:

- Tidak ada framework JavaScript (no jQuery, no React, no Vue). Vanilla JS murni.
- Tidak ada Redis, Memcached, atau queue system apapun.
- Tidak ada dependency yang membutuhkan ekstensi PHP non-standar selain yang tersedia di shared hosting umum (ctype, mbstring, intl, openssl, curl, gd).
- Total RAM usage per request tidak boleh melebihi 48MB untuk operasi biasa, dan 96MB untuk generate PDF.
- Seluruh fitur harus berfungsi penuh di viewport 360px lebar (layar HP 5 inci).
- Seluruh interaksi inti harus dapat dioperasikan tanpa mouse (keyboard-only dan touch-only).

### 1.3 Model Data: Session-Based Tanpa Login

Proyek ini menggunakan model **anonymous session** sebagai identitas pengguna. Tidak ada fitur registrasi atau login. Setiap pengunjung memiliki session ID unik yang menjadi kunci untuk semua data mereka.

Keputusan ini dibuat secara sadar dengan mempertimbangkan:
- Menghilangkan friction bagi pengguna yang tidak familiar dengan form registrasi
- Mengurangi risiko data breach (tidak ada password yang perlu dilindungi)
- Memungkinkan pengguna yang tidak punya email tetap bisa menggunakan layanan

Konsekuensinya adalah data tidak permanen (30 hari), yang harus dikomunikasikan dengan jelas kepada pengguna.

### 1.4 Pola Keamanan

Semua input pengguna diperlakukan sebagai tidak terpercaya. Tidak ada pengecualian. Defense in depth diterapkan di 4 lapisan:

- **Lapisan 1 (Database):** Parameterized query via CI4 Query Builder. Tidak pernah menggunakan string concatenation untuk query.
- **Lapisan 2 (Output):** Semua output ke HTML di-escape via `esc()` helper CI4. Tidak pernah menggunakan `echo` langsung untuk data dari pengguna.
- **Lapisan 3 (CSRF):** Token CSRF wajib di semua form dan AJAX POST. CI4 CSRF filter aktif secara global kecuali untuk endpoint yang secara eksplisit dikecualikan.
- **Lapisan 4 (Rate Limiting):** Semua endpoint yang bisa disalahgunakan (export PDF, save data, preview) memiliki rate limiter berbasis session + IP yang disimpan di database.

---

## 2. Struktur Folder Lengkap

```
create-cv-gratis/
│
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php
│   │   ├── Filters.php
│   │   ├── Security.php
│   │   └── CvConfig.php                  ← Konfigurasi kapasitas template & batas rate limit
│   │
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Home.php                      ← Landing page & halaman statis
│   │   ├── Cv.php                        ← Multi-step form, session management
│   │   ├── Export.php                    ← Download PDF, TXT, JSON
│   │   └── Api.php                       ← AJAX endpoints (autosave, preview, ganti template)
│   │
│   ├── Filters/
│   │   ├── RateLimitFilter.php           ← Middleware rate limiting universal
│   │   ├── SessionInitFilter.php         ← Inisialisasi anonymous session
│   │   └── SecurityHeadersFilter.php     ← Tambahkan security headers ke semua response
│   │
│   ├── Models/
│   │   ├── CvSessionModel.php            ← CRUD tabel cv_sessions
│   │   ├── CvDataModel.php               ← CRUD tabel cv_data (JSON blob per section)
│   │   ├── ExportLogModel.php            ← Log export untuk rate limiting & caching
│   │   ├── RateLimitModel.php            ← Tracking hit count per key (session+action)
│   │   └── AbuseReportModel.php          ← Log insiden abuse untuk review manual
│   │
│   ├── Libraries/
│   │   ├── PdfGenerator.php              ← Wrapper Dompdf dengan timeout & cache logic
│   │   ├── ContentAnalyzer.php           ← Hitung panjang konten & deteksi overflow
│   │   ├── TemplateManager.php           ← Load template, validasi, ganti template
│   │   ├── RateLimiter.php               ← Business logic rate limiting (bukan filter)
│   │   ├── SessionManager.php            ← Buat & validasi anonymous session
│   │   └── ImageProcessor.php            ← Resize & kompres foto profil (maks 200KB)
│   │
│   ├── Helpers/
│   │   ├── cv_helper.php                 ← Fungsi utilitas formatting data CV
│   │   └── accessibility_helper.php      ← Generate ARIA attributes, landmark roles
│   │
│   └── Views/
│       ├── layouts/
│       │   ├── main.php                  ← Layout utama (head, nav, footer, accessibility toolbar)
│       │   └── minimal.php              ← Layout tanpa nav untuk halaman embed/print
│       │
│       ├── landing/
│       │   ├── index.php                 ← Hero, template showcase, testimoni, FAQ
│       │   └── partials/
│       │       ├── hero.php
│       │       ├── template_showcase.php
│       │       ├── testimonials.php
│       │       └── faq.php
│       │
│       ├── cv/
│       │   ├── wizard.php               ← Shell halaman wizard (container semua step)
│       │   ├── steps/
│       │   │   ├── step1_personal.php   ← Data diri + foto
│       │   │   ├── step2_education.php  ← Pendidikan (repeatable)
│       │   │   ├── step3_experience.php ← Pengalaman kerja (repeatable)
│       │   │   ├── step4_skills.php     ← Skill & bahasa
│       │   │   └── step5_preview.php   ← Preview & pilih template & download
│       │   │
│       │   └── partials/
│       │       ├── progress_bar.php
│       │       ├── overflow_warning.php
│       │       └── template_switcher.php
│       │
│       ├── templates/
│       │   ├── classic.php              ← Render template Classic
│       │   ├── modern.php               ← Render template Modern
│       │   ├── sidebar.php              ← Render template Sidebar
│       │   ├── minimalist.php           ← Render template Minimalist
│       │   └── professional.php         ← Render template Professional
│       │
│       ├── errors/
│       │   ├── 404.php
│       │   ├── 500.php
│       │   └── rate_limit.php           ← Halaman khusus ketika kena rate limit
│       │
│       └── partials/
│           ├── accessibility_toolbar.php
│           ├── cookie_notice.php
│           └── meta_tags.php
│
├── public/
│   ├── index.php                         ← Entry point CI4 (tidak diubah)
│   ├── .htaccess                         ← Rewrite rules untuk shared hosting
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── app.css                   ← Stylesheet utama (mobile-first)
│   │   │   ├── wizard.css                ← Spesifik untuk form wizard
│   │   │   ├── templates.css             ← Styling semua template CV (shared)
│   │   │   └── accessibility.css         ← High contrast mode, font scaling
│   │   │
│   │   ├── js/
│   │   │   ├── wizard.js                 ← Navigasi step, validasi client
│   │   │   ├── autosave.js              ← Debounced auto-save via AJAX
│   │   │   ├── preview.js               ← Live preview realtime
│   │   │   ├── overflow.js              ← Deteksi overflow & notifikasi
│   │   │   ├── accessibility.js          ← Toolbar aksesibilitas, voice guide
│   │   │   └── utils.js                 ← Fungsi shared (debounce, throttle, dll)
│   │   │
│   │   └── fonts/
│   │       └── (font lokal untuk PDF rendering, format .ttf)
│   │
│   └── storage/
│       └── pdf-cache/                   ← PDF ter-cache (diproteksi .htaccess)
│           └── .htaccess                ← Deny all direct access
│
├── writable/
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/
│       └── photos/                      ← Foto profil yang sudah diproses
│
├── .env                                 ← Environment variables (tidak di-commit)
├── .env.example                         ← Template .env untuk onboarding
├── .htaccess                           ← Redirect ke public/ (root level)
└── composer.json
```

---

## 3. Skema Database

### 3.1 Prinsip Desain Database

Database dirancang dengan prinsip **minimum table, maximum flexibility**. Data CV disimpan sebagai JSON blob per section untuk menghindari schema migration ketika field ditambah di masa depan. Semua tabel menggunakan InnoDB untuk mendukung foreign key constraint dan transaction.

### 3.2 Tabel: `cv_sessions`

Tabel ini adalah anchor utama sistem. Setiap baris mewakili satu pengguna anonim.

Field `session_token` adalah UUID v4 yang dibuat server-side menggunakan `random_bytes()`, bukan input dari pengguna. Field ini diindex unique untuk lookup cepat.

Field `fingerprint_hash` adalah hash SHA-256 dari kombinasi User-Agent + Accept-Language + IP range (/24 subnet). Digunakan untuk deteksi duplikasi sesi dan abuse, bukan sebagai pengganti autentikasi.

Field `is_flagged` diset true secara otomatis oleh sistem ketika terdeteksi pola abuse. Session yang diflag tidak dapat mengakses fitur export sampai di-review.

Schema:
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `session_token` CHAR(36) NOT NULL UNIQUE
- `fingerprint_hash` VARCHAR(64) NULL
- `ip_address` VARCHAR(45) NOT NULL (support IPv6)
- `user_agent_hash` VARCHAR(64) NOT NULL (hash, bukan raw string)
- `current_step` TINYINT UNSIGNED DEFAULT 1
- `selected_template` VARCHAR(20) DEFAULT 'classic'
- `is_flagged` TINYINT(1) DEFAULT 0
- `flag_reason` VARCHAR(255) NULL
- `pdf_generated_count` SMALLINT UNSIGNED DEFAULT 0
- `last_activity_at` DATETIME NOT NULL
- `expires_at` DATETIME NOT NULL (NOW + 30 hari)
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Index: INDEX pada `session_token`, INDEX pada `expires_at` (untuk cleanup job), INDEX pada `fingerprint_hash`.

### 3.3 Tabel: `cv_data`

Satu baris per section per session. Menggunakan `section_name` sebagai identifier tipe data.

Nilai `section_name` yang valid: `personal`, `education`, `experience`, `skills`, `languages`, `preferences`.

Field `data_json` menyimpan array atau object JSON. Contoh untuk section `personal`: object dengan key name, email, phone, address, photo_path. Contoh untuk `education`: array of objects dengan key institution, major, start_year, end_year, gpa.

Field `data_hash` adalah MD5 dari `data_json`, digunakan untuk mendeteksi apakah data benar-benar berubah saat auto-save (menghindari write tidak perlu ke database).

Schema:
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `session_id` BIGINT UNSIGNED NOT NULL (FK ke cv_sessions.id ON DELETE CASCADE)
- `section_name` VARCHAR(20) NOT NULL
- `data_json` MEDIUMTEXT NOT NULL
- `data_hash` VARCHAR(32) NOT NULL
- `character_count` MEDIUMINT UNSIGNED DEFAULT 0 (panjang total teks, untuk overflow detection)
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Constraint: UNIQUE KEY `unique_session_section` (`session_id`, `section_name`)

Index: INDEX pada `session_id`.

### 3.4 Tabel: `export_logs`

Mencatat setiap export yang dilakukan. Digunakan untuk rate limiting, caching, dan analisis penggunaan.

Field `cache_path` berisi path relatif ke file PDF yang di-cache. Null jika export format TXT/JSON atau jika caching gagal.

Field `content_hash` adalah MD5 dari semua data CV saat export dilakukan. Jika content_hash sama dengan export sebelumnya dalam 1 jam terakhir, sistem menggunakan file cache daripada generate ulang.

Schema:
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `session_id` BIGINT UNSIGNED NOT NULL (FK ke cv_sessions.id ON DELETE CASCADE)
- `export_format` ENUM('pdf', 'txt', 'json') NOT NULL
- `template_name` VARCHAR(20) NULL
- `content_hash` VARCHAR(32) NOT NULL
- `cache_path` VARCHAR(255) NULL
- `file_size_bytes` INT UNSIGNED NULL
- `generation_time_ms` SMALLINT UNSIGNED NULL (waktu generate dalam milidetik)
- `ip_address` VARCHAR(45) NOT NULL
- `was_cached` TINYINT(1) DEFAULT 0
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

Index: INDEX pada `session_id`, INDEX pada `created_at`, INDEX pada `content_hash`.

### 3.5 Tabel: `rate_limits`

Menyimpan hit counter per kombinasi (key_identifier, action_name). Ini adalah implementasi rate limiting berbasis database yang cukup untuk shared hosting.

Field `key_identifier` adalah kombinasi session_token dan action_name yang di-hash. Contoh: SHA-256 dari `"{session_token}:{action_name}:{window_start}"`.

Field `window_start` adalah UNIX timestamp awal window. Untuk window 1 menit, nilainya adalah `floor(time() / 60) * 60`.

Schema:
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `key_identifier` VARCHAR(64) NOT NULL
- `action_name` VARCHAR(50) NOT NULL
- `hit_count` SMALLINT UNSIGNED DEFAULT 1
- `window_start` INT UNSIGNED NOT NULL
- `window_duration_seconds` SMALLINT UNSIGNED NOT NULL
- `ip_address` VARCHAR(45) NOT NULL
- `last_hit_at` DATETIME NOT NULL

Constraint: UNIQUE KEY `unique_key_action_window` (`key_identifier`, `action_name`, `window_start`)

Index: INDEX pada `window_start` (untuk cleanup expired records).

### 3.6 Tabel: `abuse_reports`

Log otomatis ketika sistem mendeteksi pola mencurigakan. Tidak ada UI untuk ini di frontend, hanya dapat diakses via phpMyAdmin atau query langsung untuk review manual oleh admin.

Schema:
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `session_id` BIGINT UNSIGNED NULL (FK ke cv_sessions.id ON DELETE SET NULL)
- `ip_address` VARCHAR(45) NOT NULL
- `action_attempted` VARCHAR(100) NOT NULL
- `reason` VARCHAR(500) NOT NULL
- `request_data` TEXT NULL (sanitized snapshot request yang mencurigakan)
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

### 3.7 SQL Lengkap Pembuatan Tabel

Query berikut dibuat untuk MySQL 5.7 / MariaDB dan harus dijalankan secara berurutan.

Gunakan charset `utf8mb4` dengan collation `utf8mb4_unicode_ci` untuk semua tabel agar mendukung emoji dan karakter non-Latin dalam data CV.

Setelah pembuatan tabel, buat juga EVENT MySQL untuk cleanup otomatis:

Event `cleanup_expired_sessions` berjalan setiap hari pukul 02:00 server time. Event ini menghapus baris dari `cv_sessions` di mana `expires_at < NOW()`. Karena `cv_data` memiliki FK dengan ON DELETE CASCADE, data CV yang terkait juga terhapus otomatis. Hapus juga record di `rate_limits` di mana `window_start < (UNIX_TIMESTAMP() - 3600)` untuk mencegah tabel membengkak.

Jika MySQL EVENT tidak tersedia di shared hosting, sediakan endpoint `/cron/cleanup` yang dapat dipanggil via cron job hosting (cPanel Cron Jobs) setiap hari. Endpoint ini harus diproteksi dengan secret key dari `.env`.

---

## 4. Konfigurasi Environment

### 4.1 File `.env`

File `.env` di root project (bukan di dalam `public/`) berisi semua konfigurasi yang berbeda antara development dan production. File ini TIDAK boleh di-commit ke version control.

Variabel yang WAJIB ada:

- `CI_ENVIRONMENT` — nilai: `development` atau `production`
- `app.baseURL` — URL lengkap dengan trailing slash, contoh: `https://createcvgratis.com/`
- `app.sessionExpiration` — durasi session PHP dalam detik, nilai: `2592000` (30 hari)
- `app.sessionCookieName` — nama cookie session, nilai: `ccg_session`
- `app.sessionSavePath` — path untuk menyimpan file session, nilai: `writable/session`
- `database.default.hostname` — hostname database dari cPanel
- `database.default.database` — nama database
- `database.default.username` — username database
- `database.default.password` — password database (tidak boleh ada di kode)
- `database.default.DBDriver` — nilai: `MySQLi`
- `security.csrfTokenName` — nama token CSRF, nilai: `ccg_csrf`
- `security.csrfHeaderName` — nilai: `X-CSRF-TOKEN`
- `security.csrfRegenerate` — nilai: `false` (agar AJAX tidak invalidate token setiap request)
- `CCG_PDF_CACHE_DIR` — path absolut ke folder cache PDF, contoh: `/home/username/public_html/public/storage/pdf-cache/`
- `CCG_PDF_CACHE_TTL` — durasi cache PDF dalam detik, nilai: `3600`
- `CCG_MAX_PDF_PER_HOUR` — maksimum export PDF per session per jam, nilai: `10`
- `CCG_MAX_PDF_PER_DAY` — maksimum export PDF per session per hari, nilai: `30`
- `CCG_MAX_PHOTO_SIZE_KB` — ukuran maksimum foto setelah kompresi dalam KB, nilai: `200`
- `CCG_RATE_LIMIT_AUTOSAVE_PER_MINUTE` — nilai: `30`
- `CCG_RATE_LIMIT_EXPORT_PER_MINUTE` — nilai: `3`
- `CCG_CRON_SECRET` — secret key untuk endpoint cron, generate via `bin2hex(random_bytes(16))`
- `CCG_ABUSE_FLAG_THRESHOLD` — jumlah pelanggaran sebelum session diflag, nilai: `5`
- `CCG_DOMPDF_TIMEOUT` — timeout untuk generate PDF dalam detik, nilai: `30`

### 4.2 File `app/Config/CvConfig.php`

File konfigurasi khusus proyek (bukan CI4 default) berisi semua konstanta bisnis yang mungkin perlu diubah:

Kapasitas template (jumlah maksimum item yang bisa dirender per template):
- Template `classic`: pengalaman 6, pendidikan 4, skill 10
- Template `modern`: pengalaman 8, pendidikan 5, skill 15
- Template `sidebar`: pengalaman 4, pendidikan 3, skill 6
- Template `minimalist`: pengalaman tanpa batas (nilai 999), pendidikan tanpa batas, skill tanpa batas
- Template `professional`: pengalaman 12, pendidikan 6, skill 12

Definisi template (array of objects):
- Setiap template memiliki: `slug` (string ID), `label` (nama tampilan), `description` (satu kalimat deskripsi), `best_for` (array string target user), `supports_photo` (boolean), `columns` (jumlah kolom layout: 1 atau 2)

Batas karakter per section untuk estimasi halaman:
- Section `personal`: tidak dihitung
- Section `experience_item`: deskripsi per item maksimum 500 karakter sebelum mempengaruhi overflow warning
- Section `education_item`: 200 karakter

### 4.3 File `.htaccess` (Root Level)

File `.htaccess` di root project bertugas mengarahkan semua traffic ke folder `public/`. Ini adalah pattern standar CI4 untuk shared hosting.

Aturan: Jika request bukan ke file atau folder yang ada, arahkan semua request ke `public/index.php`.

Tambahkan juga: sembunyikan file sensitif dari akses publik. File `.env`, `composer.json`, `composer.lock`, semua file `.php` di luar `public/`, dan folder `writable/` harus mengembalikan 403 Forbidden jika diakses langsung via URL.

### 4.4 File `public/.htaccess`

File `.htaccess` di dalam `public/` adalah standar CI4. Tambahkan aturan berikut di atasnya:

Aktifkan kompresi Gzip untuk tipe konten: `text/html`, `text/css`, `application/javascript`, `application/json`. Ini menghemat bandwidth secara signifikan di jaringan lambat.

Tambahkan header cache control untuk aset statis: CSS dan JS dengan `Cache-Control: public, max-age=31536000` (1 tahun) menggunakan URL versioning via query string. Gambar dengan `max-age=604800` (7 hari).

Proteksi folder `storage/pdf-cache/`: tambahkan sub-`.htaccess` di folder tersebut yang berisi `Deny from all`. PDF cache tidak boleh dapat didownload langsung via URL; harus melalui controller Export.

---

## 5. Routing

### 5.1 Prinsip Routing

Semua route didefinisikan di `app/Config/Routes.php`. Tidak ada route yang di-generate otomatis (auto-routing disabled). Setiap route eksplisit karena ini adalah praktik keamanan, bukan preferensi style.

Route dikelompokkan berdasarkan fungsi dengan prefix yang jelas:
- `/` dan halaman statis: controller `Home`
- `/cv/...`: controller `Cv` (semua halaman wizard)
- `/export/...`: controller `Export` (semua download)
- `/api/...`: controller `Api` (semua AJAX endpoint)
- `/cron/...`: controller `Cron` (task terjadwal, hanya dapat diakses dengan secret key)

### 5.2 Definisi Route Lengkap

Route GET `/` menuju `Home::index` — landing page.

Route GET `/buat-cv` menuju `Cv::wizard` — halaman utama form wizard. Inisialisasi session jika belum ada.

Route GET `/cv/step/{n}` di mana `{n}` adalah integer 1-5 menuju `Cv::step($n)` — render step tertentu. Redirect ke `/buat-cv` jika session tidak valid.

Route POST `/api/autosave` menuju `Api::autosave` — terima data JSON satu section, validasi, simpan ke database. Memerlukan filter `RateLimitFilter` dengan action `autosave`.

Route POST `/api/preview` menuju `Api::preview` — terima semua data CV, return HTML rendered template. Memerlukan filter `RateLimitFilter` dengan action `preview`.

Route POST `/api/switch-template` menuju `Api::switchTemplate` — update template yang dipilih di `cv_sessions`. Return data kapasitas template baru.

Route POST `/api/check-overflow` menuju `Api::checkOverflow` — analisis data saat ini terhadap kapasitas template, return status overflow per section.

Route GET `/export/pdf` menuju `Export::pdf` — generate atau ambil dari cache, kemudian stream file PDF ke browser dengan header `Content-Disposition: attachment`.

Route GET `/export/txt` menuju `Export::txt` — generate plain text dan stream ke browser.

Route GET `/export/json` menuju `Export::json` — package data CV sebagai JSON dan stream ke browser.

Route GET `/cron/cleanup` menuju `Cron::cleanup` — terima query param `secret`, validasi, jalankan cleanup. Return JSON status.

Route GET `/sitemap.xml` menuju `Home::sitemap`.

Route GET `/robots.txt` menuju `Home::robots`.

Semua route yang tidak cocok menuju `Home::notFound` yang render view error 404.

### 5.3 Filter Assignment

Di `app/Config/Filters.php`, definisikan alias filter:

- `rate_limit` → `App\Filters\RateLimitFilter`
- `session_init` → `App\Filters\SessionInitFilter`
- `security_headers` → `App\Filters\SecurityHeadersFilter`

Terapkan filter secara global:
- `security_headers` aktif untuk SEMUA route (before)
- `session_init` aktif untuk semua route kecuali `/cron/*` dan `/api/*` (before)

Terapkan filter spesifik per route:
- `rate_limit:autosave` untuk route `/api/autosave`
- `rate_limit:preview` untuk route `/api/preview`
- `rate_limit:export_pdf` untuk route `/export/pdf`
- `rate_limit:export_txt` untuk route `/export/txt`
- `rate_limit:export_json` untuk route `/export/json`

---

## 6. Controllers

### 6.1 `BaseController.php`

Semua controller mewarisi BaseController. Di constructor BaseController, lakukan:

Load library `SessionManager` dan pastikan session valid. Jika tidak valid, buat session baru. Simpan instance SessionManager sebagai property `$this->sessionManager`.

Load helper `cv_helper` dan `accessibility_helper` secara global.

Definisikan method protected `jsonResponse(array $data, int $statusCode = 200)` yang return `$this->response->setStatusCode($statusCode)->setContentType('application/json')->setBody(json_encode($data))`. Semua controller menggunakan method ini untuk response AJAX, bukan `echo json_encode()`.

Definisikan method protected `isAjax()` yang mengecek header `X-Requested-With: XMLHttpRequest` ATAU header `Content-Type: application/json`. Digunakan untuk membedakan request AJAX dari request biasa.

Definisikan method protected `getCurrentSession()` yang return row dari `cv_sessions` berdasarkan session token aktif. Throw exception (yang ditangkap oleh error handler) jika session tidak ditemukan.

### 6.2 `Home.php`

Method `index()`: Load semua data yang dibutuhkan landing page dari config (daftar template, jumlah "pengguna"), render view `landing/index`. Tidak ada query database di halaman ini kecuali mungkin counter total CV yang dibuat (dari aggregate query sederhana).

Method `sitemap()`: Generate XML sitemap statis. Hanya halaman publik yang perlu diindex (landing page, halaman FAQ jika ada). Halaman `/buat-cv` dan `/cv/*` tidak dimasukkan ke sitemap karena kontennya dinamis per pengguna.

Method `robots()`: Return file robots.txt yang melarang crawling `/api/*`, `/export/*`, `/cron/*`.

Method `notFound()`: Set HTTP status 404, render view `errors/404`.

### 6.3 `Cv.php`

Method `wizard()`: Ini adalah entry point utama. Cek apakah session sudah ada via `SessionManager`. Jika belum ada, inisialisasi session baru (buat row di `cv_sessions`, set cookie 30 hari). Load semua data CV yang sudah tersimpan (semua section dari `cv_data`) untuk pre-populate form. Render view `cv/wizard` dengan data tersebut.

Method `step(int $step)`: Validasi bahwa `$step` adalah integer antara 1-5. Load data untuk step tersebut dari `cv_data`. Render view `cv/steps/step{$step}_*.php` yang sesuai. Jika session tidak ada (cookie expired), redirect ke `/buat-cv` dengan flash message penjelasan bahwa data belum tersimpan.

### 6.4 `Api.php`

Method `autosave()`: Endpoint POST. Validasi bahwa request adalah AJAX (cek header). Ambil body JSON dari request. Validasi struktur JSON sesuai section yang dikirim. Hitung `data_hash` dari data. Cek apakah hash berbeda dari yang tersimpan (jika sama, return success tanpa melakukan write). Jika berbeda, sanitasi semua field (esc(), strip_tags untuk field teks biasa, lebih ketat untuk field yang akan dirender), simpan ke `cv_data`. Update `last_activity_at` di `cv_sessions`. Return JSON `{success: true, timestamp: "..."}`.

Validasi field yang WAJIB per section:

Section `personal`: field `name` wajib ada dan tidak boleh kosong, panjang maksimum 100 karakter. Field `email` jika diisi harus valid format email. Field `phone` jika diisi harus mengandung hanya angka, spasi, tanda plus, dan tanda kurung.

Section `education`: array tidak boleh kosong jika dikirim. Setiap item wajib memiliki `institution` (maks 150 karakter) dan `start_year` (4 digit angka). Field `gpa` jika diisi harus angka antara 0.00 sampai 4.00.

Section `experience`: setiap item wajib memiliki `company` (maks 150 karakter) dan `position` (maks 100 karakter).

Section `skills`: setiap item wajib memiliki `name` (maks 50 karakter). Field `level` harus salah satu dari: `beginner`, `intermediate`, `advanced`, `expert`.

Method `preview()`: Terima semua data CV dari body JSON. Jalankan `ContentAnalyzer::analyze()` pada data. Load template view yang sesuai dengan template yang dipilih. Render view dengan data. Return HTML string. Tidak ada write ke database di endpoint ini.

Method `switchTemplate()`: Terima `template_slug` dari body JSON. Validasi bahwa slug adalah salah satu template yang terdaftar di `CvConfig`. Update field `selected_template` di `cv_sessions`. Jalankan `ContentAnalyzer::analyze()` dengan data saat ini terhadap template baru. Return JSON berisi `{success: true, capacity: {...}, overflow_status: {...}}`.

Method `checkOverflow()`: Ambil semua data CV saat ini dari database berdasarkan session. Ambil kapasitas template yang sedang aktif dari `CvConfig`. Return JSON berisi status overflow per section dengan detail berapa item yang ada vs berapa maksimum.

### 6.5 `Export.php`

Method `pdf()`: 

Langkah 1 — Rate limit check. Ambil hit count dari `rate_limits` untuk action `export_pdf` dalam window 1 jam dan 24 jam. Jika melebihi batas harian atau per jam, redirect ke halaman error rate limit dengan penjelasan ramah kapan bisa mencoba lagi.

Langkah 2 — Ambil semua data CV dari database.

Langkah 3 — Hitung `content_hash` dari semua data CV.

Langkah 4 — Cek cache. Query `export_logs` untuk cek apakah ada record dalam 1 jam terakhir dengan `content_hash` yang sama, `export_format = 'pdf'`, dan `template_name` yang sama. Jika ada dan `cache_path` valid (file masih exist), tandai request ini sebagai cache hit.

Langkah 5 — Jika cache miss: inisialisasi `PdfGenerator`, set timeout, render template ke HTML string, generate PDF. Simpan ke folder cache dengan nama file `{session_token}_{content_hash}.pdf`. Catat di `export_logs` dengan `was_cached = 0`.

Langkah 6 — Jika cache hit: ambil path dari record export log. Catat di `export_logs` dengan `was_cached = 1`.

Langkah 7 — Increment `pdf_generated_count` di `cv_sessions`.

Langkah 8 — Stream file ke browser dengan header yang tepat: `Content-Type: application/pdf`, `Content-Disposition: attachment; filename="cv-{nama}.pdf"`, `Content-Length: {ukuran file}`, `X-Cache: HIT atau MISS`.

Jika generate PDF timeout atau gagal: log error, return response JSON error dengan pesan ramah dalam bahasa Indonesia yang menyarankan untuk mencoba lagi atau menggunakan template Minimalist.

Method `txt()`: Ambil data CV, format sebagai plain text menggunakan helper `format_cv_as_text()` dari `cv_helper`. Return sebagai download file `.txt` dengan charset UTF-8.

Method `json()`: Ambil data CV dari database. Bungkus dalam envelope JSON dengan metadata (timestamp export, versi aplikasi). Remove field sensitif yang tidak perlu ada di export (ip_address, fingerprint_hash, flag status). Return sebagai download file `.json`.

### 6.6 `Cron.php`

Method `cleanup()`: Validasi `secret` query param terhadap `CCG_CRON_SECRET` dari `.env`. Jika tidak valid, return 403. Jalankan: delete expired sessions, delete expired rate limit records, delete orphaned PDF cache files (file di folder cache yang tidak memiliki referensi valid di `export_logs`). Return JSON dengan statistik: jumlah baris yang dihapus per operasi, waktu eksekusi.

---

## 7. Models

### 7.1 `CvSessionModel.php`

Extends `CodeIgniter\Model`. Definisikan `$table = 'cv_sessions'`, `$primaryKey = 'id'`, `$useAutoIncrement = true`, `$allowedFields` mencakup semua field yang boleh ditulis (kecuali `id` dan `created_at`).

Method `findByToken(string $token): ?array` — query SELECT dengan WHERE `session_token = ?` dan `expires_at > NOW()`. Return null jika tidak ditemukan atau sudah expired.

Method `createSession(array $data): int` — insert row baru, return ID yang baru dibuat.

Method `updateLastActivity(int $sessionId): bool` — UPDATE SET `last_activity_at = NOW()` WHERE id.

Method `extendExpiry(int $sessionId, int $days = 30): bool` — UPDATE SET `expires_at = DATE_ADD(NOW(), INTERVAL ? DAY)`.

Method `flagSession(int $sessionId, string $reason): bool` — UPDATE SET `is_flagged = 1`, `flag_reason = ?`.

Method `incrementPdfCount(int $sessionId): bool` — UPDATE dengan `pdf_generated_count = pdf_generated_count + 1`.

Method `cleanupExpired(): int` — DELETE WHERE `expires_at < NOW()`, return jumlah baris yang dihapus.

### 7.2 `CvDataModel.php`

Method `getSection(int $sessionId, string $sectionName): ?array` — SELECT WHERE `session_id = ?` AND `section_name = ?`. Return decoded JSON atau null.

Method `getAllSections(int $sessionId): array` — SELECT WHERE `session_id = ?`. Return associative array dengan `section_name` sebagai key dan decoded JSON sebagai value.

Method `saveSection(int $sessionId, string $sectionName, array $data): bool` — INSERT ... ON DUPLICATE KEY UPDATE. Hitung `data_hash` dan `character_count` sebelum query. Return true jika berhasil.

Method `hashChanged(int $sessionId, string $sectionName, string $newHash): bool` — SELECT `data_hash` WHERE `session_id` AND `section_name`. Return true jika hash berbeda atau record tidak ada (artinya perlu disimpan).

Method `calculateCharacterCount(array $data): int` — rekursif flatten array, gabungkan semua string value, return panjangnya. Digunakan untuk mengisi field `character_count`.

### 7.3 `ExportLogModel.php`

Method `findCachedExport(int $sessionId, string $format, string $contentHash, string $template): ?array` — SELECT WHERE `session_id = ?` AND `export_format = ?` AND `content_hash = ?` AND `template_name = ?` AND `created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)` (TTL dari env). Hanya return record yang `cache_path` nya tidak null dan file masih ada di disk (`file_exists()`).

Method `logExport(array $data): int` — insert record log, return ID.

Method `countExportsInWindow(int $sessionId, string $format, int $windowSeconds): int` — SELECT COUNT WHERE `session_id` AND `export_format` AND `created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)`.

### 7.4 `RateLimitModel.php`

Method `getHitCount(string $keyIdentifier, string $actionName, int $windowStart): int` — SELECT `hit_count` WHERE `key_identifier = ?` AND `action_name = ?` AND `window_start = ?`. Return 0 jika tidak ada record.

Method `incrementHit(string $keyIdentifier, string $actionName, int $windowStart, int $windowDuration, string $ipAddress): void` — INSERT dengan `hit_count = 1` ON DUPLICATE KEY UPDATE `hit_count = hit_count + 1`, `last_hit_at = NOW()`.

Method `cleanupExpired(): int` — DELETE WHERE `window_start < (UNIX_TIMESTAMP() - 3600)`.

### 7.5 `AbuseReportModel.php`

Method `report(int $sessionId = null, string $ip, string $action, string $reason, string $requestData = null): void` — INSERT ke tabel `abuse_reports`. Tidak throw exception jika gagal (logging tidak boleh mengganggu flow utama).

---

## 8. Libraries

### 8.1 `SessionManager.php`

Namespace: `App\Libraries\SessionManager`.

Library ini menjadi satu-satunya tempat di mana cookie session dibuat, dibaca, dan divalidasi. Controller TIDAK boleh mengakses cookie secara langsung.

Property: `$sessionToken`, `$sessionData` (row dari `cv_sessions`), `$model` (instance `CvSessionModel`).

Method `initialize(): void` — dipanggil di `BaseController`. Baca cookie `ccg_session`. Jika cookie ada, validasi via `$model->findByToken()`. Jika token valid, set `$sessionToken` dan `$sessionData`. Jika tidak valid (expired atau tidak ada di DB), hapus cookie dan buat session baru. Jika cookie tidak ada, buat session baru.

Method `createNewSession(): void` — generate token via `bin2hex(random_bytes(18))` (36 karakter, format mirip UUID tapi bukan UUID agar tidak mudah ditebak). Hitung `fingerprint_hash` dari User-Agent (maks 200 char) + Accept-Language header + 3 oktet pertama IP (subnet /24). Insert ke `cv_sessions`. Set cookie dengan parameter: HttpOnly, Secure (jika HTTPS), SameSite=Lax, expired dalam 30 hari.

Method `getToken(): string` — return `$sessionToken`.

Method `getSessionData(): array` — return `$sessionData`.

Method `getSessionId(): int` — return `$sessionData['id']`.

Method `isValid(): bool` — return true jika `$sessionData` tidak null dan `is_flagged` adalah 0.

Method `isFlagged(): bool` — return true jika `is_flagged` adalah 1.

Method `refreshExpiry(): void` — panggil `$model->extendExpiry()` jika `expires_at` kurang dari 7 hari lagi.

### 8.2 `RateLimiter.php`

Namespace: `App\Libraries\RateLimiter`.

Library ini menjadi brain dari sistem rate limiting. `RateLimitFilter` memanggil library ini.

Konfigurasi limit per action (ambil dari `.env` atau `CvConfig`):
- `autosave`: 30 request per menit per session
- `preview`: 20 request per menit per session
- `export_pdf`: 3 request per menit per session, 10 per jam, 30 per hari
- `export_txt`: 5 request per menit per session
- `export_json`: 5 request per menit per session

Method `check(string $sessionToken, string $action, string $ipAddress): RateLimitResult` — return object `RateLimitResult` dengan property `allowed` (boolean), `remaining` (int), `resetAt` (UNIX timestamp), `retryAfter` (detik).

Logika internal: Buat `keyIdentifier` dari SHA-256 `"{sessionToken}:{action}:{windowStart}"`. Panggil `RateLimitModel::getHitCount()`. Bandingkan dengan limit. Jika belum melebihi, panggil `incrementHit()` dan return allowed=true. Jika sudah melebihi, return allowed=false tanpa increment.

Method `checkIpBased(string $ipAddress, string $action): RateLimitResult` — rate limit tambahan berbasis IP murni (tanpa session). Ini untuk mencegah serangan dari IP yang terus membuat session baru. Limit IP: 50 request export PDF per jam per IP.

### 8.3 `PdfGenerator.php`

Namespace: `App\Libraries\PdfGenerator`.

Library ini adalah wrapper tipis di atas Dompdf yang menambahkan: timeout handling, error catching, dan memory management.

Konfigurasi Dompdf yang wajib diset:
- `isHtml5ParserEnabled`: true
- `isPhpEnabled`: false (KRITIS — jangan aktifkan ini, risiko RCE)
- `defaultFont`: 'DejaVu Sans' atau font yang sudah di-bundle
- `isRemoteEnabled`: false (jangan biarkan Dompdf fetch resource eksternal)
- `chroot`: path ke folder `public/assets/` (batasi file yang bisa diakses Dompdf)
- Memory limit sementara: set ke `96M` sebelum generate, kemudian restore setelah selesai

Method `generate(string $htmlContent, string $outputPath = null): string|false` — 

Gunakan `set_time_limit(CCG_DOMPDF_TIMEOUT)` sebelum mulai. Bungkus dalam try-catch. Instantiate Dompdf, `loadHtml($htmlContent)`, `setPaper('A4', 'portrait')`, `render()`. 

Jika `$outputPath` tidak null, simpan ke file dengan `file_put_contents($outputPath, $dompdf->output())`. Return path jika berhasil.

Jika `$outputPath` null, return string binary PDF langsung (untuk stream ke browser).

Tangkap semua exception. Jika timeout atau memory exhausted, log ke CI4 error log dan return false.

Setelah generate (sukses atau gagal), panggil `gc_collect_cycles()` untuk membantu garbage collection.

Method `estimateMemoryUsage(string $htmlContent): int` — estimasi kasar penggunaan memori berdasarkan panjang HTML. Digunakan untuk early bailout jika konten terlalu besar. Rumus sederhana: `strlen($htmlContent) * 5` (bytes). Jika lebih dari 80MB, tolak dan return error sebelum inisialisasi Dompdf.

### 8.4 `ContentAnalyzer.php`

Namespace: `App\Libraries\ContentAnalyzer`.

Library ini menganalisis data CV terhadap kapasitas template dan menghasilkan laporan overflow.

Method `analyze(array $cvData, string $templateSlug): AnalysisResult` — return object dengan:
- `hasOverflow`: boolean, true jika ada section yang melebihi kapasitas
- `overflowSections`: array nama section yang overflow
- `sectionCounts`: array jumlah item per section
- `capacity`: array kapasitas maksimum per section untuk template ini
- `estimatedPages`: integer estimasi jumlah halaman PDF

Logika estimasi halaman: template 1-kolom dengan Minimalist: setiap 1500 karakter total konten ≈ 1 halaman. Template 2-kolom: setiap 2000 karakter ≈ 1 halaman. Ini hanya estimasi kasar untuk memberikan guidance kepada pengguna.

Method `getRecommendedTemplate(array $cvData): string` — analisis data dan rekomendasikan template yang paling cocok berdasarkan jumlah item. Contoh: jika pengalaman lebih dari 6, rekomendasikan `professional` atau `minimalist`.

### 8.5 `TemplateManager.php`

Namespace: `App\Libraries\TemplateManager`.

Method `renderToHtml(string $templateSlug, array $cvData): string` — load view `templates/{slug}.php` dengan data CV, return HTML string.

Method `getTemplateConfig(string $slug): array` — return konfigurasi template dari `CvConfig` berdasarkan slug. Throw `InvalidArgumentException` jika slug tidak valid.

Method `getAllTemplates(): array` — return semua template yang tersedia beserta konfigurasinya.

Method `isValidTemplate(string $slug): bool` — cek apakah slug ada di daftar template yang valid.

### 8.6 `ImageProcessor.php`

Namespace: `App\Libraries\ImageProcessor`.

Method `processUpload(array $fileData): ProcessResult` — terima data file dari `$_FILES` atau CI4 uploaded file object. Validasi tipe MIME (hanya `image/jpeg`, `image/png`, `image/webp`). Validasi ukuran raw maksimum 5MB. Resize ke maksimum 400x400 pixel sambil mempertahankan aspect ratio menggunakan GD Library (tersedia di semua shared hosting). Kompres ke JPEG dengan kualitas 80. Simpan ke `writable/uploads/photos/{session_token}_photo.jpg`. Return object dengan `success`, `path`, `size_bytes`, `error_message`.

Method `deletePhoto(string $sessionToken): void` — hapus file foto lama jika ada saat pengguna upload foto baru.

Semua operasi file menggunakan absolute path. Tidak pernah menerima path dari input pengguna.

---

## 9. Helpers

### 9.1 `cv_helper.php`

File ini di-load secara global di `BaseController`. Berisi fungsi procedural untuk formatting data CV.

Fungsi `format_name(string $name): string` — trim, ucwords, maksimum 100 karakter. Strip semua tag HTML.

Fungsi `format_phone(string $phone): string` — simpan hanya angka, spasi, +, (, ), -. Maksimum 20 karakter.

Fungsi `format_year_range(int $start, ?int $end): string` — return string seperti "2018 – 2022" atau "2020 – Sekarang" jika end null.

Fungsi `format_gpa(float $gpa): string` — format sebagai "3.75 / 4.00" atau "3,75 / 4,00" (menggunakan locale).

Fungsi `sanitize_cv_text(string $text, int $maxLength = 500): string` — strip semua HTML tags, escape HTML entities, trim whitespace, potong ke maxLength dengan penanda elipsis.

Fungsi `format_cv_as_text(array $cvData): string` — format seluruh data CV sebagai plain text yang rapi untuk export TXT. Gunakan separator baris seperti `===` dan `---` untuk membedakan section. Sertakan label section dalam Bahasa Indonesia.

Fungsi `generate_cv_filename(string $name, string $format): string` — buat nama file download yang aman. Contoh: `cv-budi-santoso-2025.pdf`. Gunakan `preg_replace` untuk mengganti karakter non-alphanumeric dengan tanda hubung.

Fungsi `mask_email(string $email): string` — untuk keperluan log, masking email menjadi `b***@g***.com`.

### 9.2 `accessibility_helper.php`

Fungsi `aria_label(string $text): string` — return `aria-label="..."` setelah meng-escape atribut.

Fungsi `aria_describedby(string $id): string` — return `aria-describedby="..."`.

Fungsi `form_group_attrs(string $inputId, bool $required = false, bool $invalid = false): string` — return string atribut ARIA untuk form group: `role="group"`, tambahkan `aria-required="true"` jika required, `aria-invalid="true"` jika invalid.

Fungsi `skip_link(string $targetId, string $text = 'Langsung ke konten'): string` — return HTML anchor element untuk skip navigation.

Fungsi `announce(string $message, string $politeness = 'polite'): string` — return HTML `<div role="status" aria-live="..." aria-atomic="true" class="sr-only">` untuk screen reader announcement.

---

## 10. Views & Templates

### 10.1 Prinsip Views

Semua view menggunakan PHP sebagai template engine bawaan CI4. Tidak ada library templating tambahan.

Setiap view TIDAK boleh melakukan query database secara langsung. Semua data harus diterima via `$data` array dari controller.

Semua output data pengguna wajib dibungkus dengan `esc()` helper CI4. Tidak ada pengecualian untuk ini.

### 10.2 `layouts/main.php`

Struktur HTML5 lengkap dengan:

Tag `<html lang="id">` dengan atribut `lang` yang benar untuk screen reader.

Bagian `<head>` berisi: charset UTF-8, viewport meta tag dengan `width=device-width, initial-scale=1`, meta description dinamis (dari `$meta_description`), meta title (dari `$page_title`), Open Graph tags untuk sharing, canonical URL, preload CSS utama, link stylesheet dengan versioning via query string `?v={hash file}`, Schema.org JSON-LD jika ada (dari `$schema_json`).

Bagian `<body>` dimulai dengan: skip link `<a href="#main-content">Langsung ke konten utama</a>` sebagai elemen pertama di body (tersembunyi kecuali saat fokus), ARIA live region untuk pengumuman dinamis screen reader, cookie notice yang muncul sekali.

Navigation bar: landmark `<nav role="navigation" aria-label="Navigasi utama">`. Di mobile, nav berubah jadi hamburger menu. Tombol hamburger harus memiliki `aria-expanded` dan `aria-controls`.

Konten utama dibungkus dalam `<main id="main-content" role="main">`.

Footer berisi: link ke halaman tentang, kebijakan privasi, disclaimer bahwa CV disimpan 30 hari, copyright.

Accessibility toolbar diletakkan sebagai komponen mengambang (fixed position) di pojok kanan bawah, dapat disembunyikan.

Sebelum closing `</body>`: load semua JavaScript. Urutan: `utils.js`, `accessibility.js`, kemudian file JS spesifik halaman.

### 10.3 `cv/wizard.php`

Halaman ini adalah shell untuk semua step form wizard. Tidak merender konten step secara langsung; konten step di-load via AJAX atau dirender di server tergantung JavaScript tersedia.

Struktur:
- Progress bar di atas dengan `role="progressbar"`, `aria-valuenow`, `aria-valuemin="1"`, `aria-valuemax="5"`, `aria-label="Langkah {n} dari 5"`
- Container step form di sebelah kiri (60% width desktop, 100% mobile)
- Container preview di sebelah kanan (40% width desktop, tersembunyi di mobile kecuali tab preview aktif)
- Template switcher (dropdown atau carousel kecil) di atas preview
- Overflow warning area di antara form dan preview

Data yang di-pass ke view dari controller: `current_step`, `cv_data` (semua section yang sudah tersimpan), `selected_template`, `templates` (array semua template), `overflow_status`, `csrf_token`.

### 10.4 Form Steps (step1 s/d step5)

**Step 1 — Data Diri:**

Field form: Nama Lengkap (text, required, maxlength 100), Email (email, maxlength 150), Nomor Telepon (tel, maxlength 20), Alamat (textarea, maxlength 300), Foto Profil (file input, optional, accept="image/jpeg,image/png,image/webp", max 5MB — peringatan ukuran ditampilkan di bawah field).

Setiap field memiliki: `<label for="{id}">` yang terhubung via `for` dan `id`. Error message area dengan `role="alert"` di bawah field. Placeholder yang informatif (bukan sebagai pengganti label).

Tombol navigasi: "Lanjut ke Pendidikan →" di kanan bawah. Tidak ada tombol "Kembali" di step 1.

**Step 2 — Pendidikan:**

Form ini repeatable. Pengguna bisa menambah beberapa entry pendidikan.

Setiap entry: Nama Institusi (text), Jurusan/Program Studi (text), Tahun Masuk (number, min 1950, max tahun ini), Tahun Keluar (number atau checkbox "Masih Berkuliah"), IPK (number, min 0, max 4, step 0.01, optional).

Tombol "Tambah Pendidikan Lain" di bawah daftar entry. Tombol "Hapus" untuk setiap entry dengan `aria-label="Hapus entri pendidikan {n}"`.

Tombol navigasi: "← Kembali" dan "Lanjut ke Pengalaman →".

**Step 3 — Pengalaman Kerja:**

Form repeatable. Setiap entry: Nama Perusahaan (text), Posisi/Jabatan (text), Bulan & Tahun Mulai (dua input terpisah: month dan year), Bulan & Tahun Selesai (sama, plus checkbox "Masih Bekerja di Sini"), Deskripsi Pekerjaan (textarea, diformat sebagai poin-poin — setiap baris baru adalah satu poin).

Petunjuk di bawah textarea: "Tulis satu pencapaian per baris. Mulai dengan kata kerja aktif seperti 'Memimpin', 'Meningkatkan', 'Mengembangkan'."

**Step 4 — Skill & Bahasa:**

Bagian Skill: Nama Skill (text), Level (dropdown: Pemula/Menengah/Mahir/Ahli). Tombol tambah skill.

Bagian Bahasa: Nama Bahasa (text), Level (dropdown: Dasar/Percakapan/Profesional/Fasih/Penutur Asli). Tombol tambah bahasa.

**Step 5 — Preview & Download:**

Tab "Preview CV" ditampilkan di sini untuk mobile (desktop sudah menampilkan preview di samping).

Pilihan template dalam bentuk grid 2-3 kolom dengan thumbnail kecil (gambar statis atau SVG placeholder) dan nama template.

Tombol download tiga format: "Unduh PDF" (primary button, CTA utama), "Unduh TXT" (secondary, dengan penjelasan "Untuk screen reader"), "Unduh JSON" (tertiary, dengan penjelasan "Untuk backup data").

Sebelum tombol download: tampilkan estimasi halaman PDF, peringatan kapasitas jika overflow, dan catatan bahwa data akan disimpan 30 hari.

### 10.5 View Templates CV (5 File)

Kelima template view ini di-render sisi server oleh `TemplateManager::renderToHtml()`. HTML yang dihasilkan digunakan baik untuk preview (ditampilkan di browser) maupun untuk input ke Dompdf (PDF).

Karena digunakan untuk Dompdf, semua styling harus inline CSS atau CSS internal `<style>` tag. Tidak ada external stylesheet link. Dompdf memiliki dukungan CSS yang terbatas; hanya gunakan properti CSS yang kompatibel Dompdf.

CSS yang AMAN untuk Dompdf: font-family, font-size, font-weight, color, background-color, border, margin, padding, width, height (dalam satuan pixel, pt, atau cm), display (block, inline, inline-block, table, table-row, table-cell), text-align, float (dengan hati-hati).

CSS yang TIDAK AMAN untuk Dompdf: flexbox, CSS Grid, CSS custom properties (variabel), transform, animation, vh/vw units, position: sticky, multi-column layout.

Teknik layout 2-kolom untuk Dompdf: gunakan `<table>` dengan `width: 100%`, dua `<td>` dengan lebar yang ditentukan. Ini lebih reliable daripada float di Dompdf.

Setiap template view menerima array `$cv` yang berisi semua data CV. Setiap template juga menerima array `$options` yang bisa berisi: `$options['mode']` ('preview' atau 'pdf') — jika preview, tambahkan CSS interaktif; jika pdf, hapus CSS yang tidak didukung Dompdf.

**Template Classic:** Dua kolom. Kolom kiri (30% lebar) berwarna abu-abu gelap (#333 atau #2c3e50), berisi foto (opsional), nama, kontak, skill, bahasa. Kolom kanan (70% lebar) berisi pendidikan dan pengalaman kerja. Font: serif (untuk PDF: DejaVu Serif atau Times New Roman). Warna: hitam dan putih dengan aksen abu-abu.

**Template Modern:** Dua kolom dengan sidebar kiri lebar 35%. Sidebar berwarna solid dengan warna aksen yang bisa dipilih (default: biru #2980b9). Konten sidebar: foto, info kontak, skill dengan bar indikator level (menggunakan div dengan lebar percentage). Konten utama kanan: pendidikan dan pengalaman. Font: sans-serif.

**Template Sidebar:** Proporsi 40:60. Sidebar kiri lebih lebar untuk fresh graduate yang mungkin punya pengalaman sedikit tapi skill banyak. Sidebar berisi: info kontak, skill, bahasa, pendidikan (jika banyak). Konten utama kanan: nama besar, ringkasan opsional, pengalaman.

**Template Minimalist:** Satu kolom penuh. Layout linear dari atas ke bawah. Tidak ada sidebar. Menggunakan banyak whitespace. Font: sans-serif, ukuran sedang. Tanda separator section menggunakan garis tipis `<hr>` atau border-bottom. Template ini yang paling ringan, paling cepat di-render Dompdf, dan paling mudah dibaca screen reader.

**Template Professional:** Header letterhead-style: nama besar di tengah, kontak di bawahnya, kemudian garis pemisah tebal. Konten menggunakan satu kolom dengan section yang jelas. Cocok untuk CV senior. Mendukung banyak item pengalaman (sampai 12). Font: kombinasi serif untuk heading dan sans-serif untuk body.

### 10.6 `partials/overflow_warning.php`

Komponen ini dirender secara dinamis via JavaScript ketika overflow terdeteksi. Versi server-rendered tersedia sebagai fallback.

Tampilannya: kartu peringatan berwarna kuning/amber (bukan merah — tidak boleh menakut-nakuti pengguna). Icon segitiga peringatan. Pesan: "Template [nama] hanya bisa menampilkan [n] pengalaman kerja. Kamu punya [m]. CV kamu mungkin terpotong di halaman PDF." Tombol aksi: "Ganti ke Template [yang direkomendasikan]" dan "Saya mengerti, lanjutkan".

### 10.7 `landing/index.php`

Hero section: Headline utama (H1) berisi value proposition singkat. Subheadline 1-2 kalimat. Tombol CTA "Buat CV Sekarang — Gratis!" yang menuju `/buat-cv`. Ilustrasi atau mockup CV (SVG sederhana, bukan gambar berat).

Template showcase: Grid 5 kartu template. Setiap kartu: thumbnail kecil (gambar statis atau SVG), nama template, deskripsi singkat, tag "Cocok untuk: ...". Kartu dapat diklik untuk membuka preview modal atau langsung menuju wizard dengan template tersebut.

Social proof: nomor total CV yang dibuat (diambil dari query COUNT), mungkin testimoni singkat.

FAQ accordion: pertanyaan umum seperti "Apakah data saya aman?", "Berapa lama data tersimpan?", "Apakah bisa dipakai di HP?".

Bagian aksesibilitas: penjelasan singkat fitur aksesibilitas yang tersedia.

Footer dengan informasi kontak, disclaimer data, dan link ke halaman kebijakan privasi.

---

## 11. Frontend Architecture

### 11.1 Prinsip JavaScript

Semua JavaScript ditulis sebagai Vanilla ES6+. Tidak ada dependency eksternal. File JS di-load di akhir body untuk tidak memblokir rendering.

Setiap file JS adalah module yang self-contained. File `utils.js` harus di-load pertama karena berisi fungsi yang digunakan file lain.

Semua operasi DOM dilakukan setelah `DOMContentLoaded` event. Tidak ada code di top-level yang langsung memanipulasi DOM.

Error handling: semua `fetch()` call dibungkus dalam try-catch. Jika AJAX gagal, tampilkan pesan error inline yang informatif — jangan biarkan pengguna bingung kenapa tidak ada response.

### 11.2 `utils.js`

Fungsi `debounce(func, wait)` — return fungsi yang menunda eksekusi `func` sebesar `wait` milidetik setiap kali dipanggil. Digunakan untuk auto-save dan live preview agar tidak terlalu sering fire.

Fungsi `throttle(func, limit)` — return fungsi yang memastikan `func` tidak dipanggil lebih dari sekali per `limit` milidetik. Digunakan untuk overflow check.

Fungsi `showToast(message, type, duration)` — tampilkan notifikasi kecil di pojok layar. `type` bisa 'success', 'error', 'warning', 'info'. Notifikasi ini menggunakan ARIA live region agar dibaca screen reader. Auto-dismiss setelah `duration` milidetik (default: 4000).

Fungsi `getCsrfToken()` — ambil nilai CSRF token dari meta tag di head. Semua AJAX POST menyertakan token ini di header `X-CSRF-TOKEN`.

Fungsi `fetchJson(url, options)` — wrapper di atas `fetch()` yang otomatis: set `Content-Type: application/json`, tambahkan CSRF header, parse response sebagai JSON, throw Error jika status bukan 2xx. Return Promise yang resolve ke data JSON.

Fungsi `formatBytes(bytes)` — format bytes ke KB/MB untuk display.

Fungsi `announceToScreenReader(message)` — isi ARIA live region dengan pesan untuk diumumkan screen reader.

### 11.3 `wizard.js`

Mengelola navigasi antar step di form wizard.

State: `currentStep` (integer 1-5), `isNavigating` (boolean untuk mencegah double click).

Fungsi `goToStep(n)` — validasi step sebelumnya sebelum pindah. Tampilkan step n, sembunyikan step lain. Update progress bar (set `aria-valuenow`, update visual). Scroll ke atas halaman. Focus ke heading step yang baru.

Fungsi `validateCurrentStep()` — jalankan validasi HTML5 native (`reportValidity()`) ditambah validasi custom. Return boolean. Jika invalid, fokus ke field pertama yang error.

Event listener untuk tombol "Lanjut": panggil `validateCurrentStep()`. Jika valid, panggil `triggerSave()` dari `autosave.js`, kemudian `goToStep(currentStep + 1)`.

Event listener untuk tombol "Kembali": langsung panggil `goToStep(currentStep - 1)` tanpa validasi.

Keyboard navigation: pastikan tab order logis di setiap step. Tombol navigasi harus dapat diakses via keyboard.

### 11.4 `autosave.js`

Implementasi auto-save berbasis debounce.

Fungsi `triggerSave(sectionName, data)` — panggil via debounce 2000ms (2 detik). Di dalam fungsi: tampilkan indicator "Menyimpan..." di status bar, panggil `fetchJson('/api/autosave', {method: 'POST', body: ...})`. Jika berhasil, tampilkan "Tersimpan ✓" dengan timestamp. Jika gagal, tampilkan "Gagal menyimpan — coba lagi" dengan tombol retry.

Fungsi `collectSectionData(sectionName)` — ambil semua value dari form fields untuk section tertentu, return sebagai object.

Setup event listeners: untuk setiap input, textarea, dan select dalam wizard, tambahkan listener `input` (untuk text/textarea) atau `change` (untuk select, checkbox, radio) yang memanggil `triggerSave()` dengan debounce.

Untuk field file (foto): upload langsung saat file dipilih menggunakan FormData dan fetch dengan Content-Type multipart (bukan JSON). Response berisi path foto yang tersimpan, yang kemudian dimasukkan ke hidden input.

### 11.5 `preview.js`

Mengelola live preview realtime.

State: `previewMode` ('desktop' atau 'mobile' based on window width), `lastUpdateTime`.

Fungsi `updatePreview()` — kumpulkan semua data dari form (semua section, bukan hanya yang sedang aktif), panggil `fetchJson('/api/preview', ...)` dengan semua data. Saat response datang, update innerHTML container preview dengan HTML yang diterima. Jalankan `overflow.js` check terhadap data saat ini.

Debounce: `updatePreview` dibungkus dalam debounce 500ms. Setiap perubahan input men-trigger debounce ini.

Preview mode mobile: ketika `window.innerWidth < 768`, preview disembunyikan dari samping dan ditampilkan di panel tab terpisah. Toggle antara "Form" dan "Preview" dengan tombol di bagian atas.

### 11.6 `overflow.js`

Mengelola deteksi overflow dan notifikasi.

Fungsi `checkOverflow(cvData, templateSlug)` — panggil `fetchJson('/api/check-overflow', ...)` atau hitung secara lokal menggunakan data kapasitas template yang di-embed sebagai JSON di halaman (untuk performa, data kapasitas di-embed di tag `<script type="application/json" id="template-capacities">`).

Jika ada overflow:
1. Tampilkan komponen `overflow_warning` dengan detail section mana yang overflow
2. Highlight field yang overflow di form dengan border merah dan aria-invalid="true"
3. Tampilkan rekomendasi template lain yang bisa menampung semua konten

Fungsi `suggestTemplate(cvData)` — hitung template yang paling cocok berdasarkan jumlah item. Tampilkan sebagai "Rekomendasi: Template Professional" dengan tombol langsung ganti template.

### 11.7 `accessibility.js`

Mengelola semua fitur aksesibilitas.

**Toolbar aksesibilitas** (komponen fixed di pojok layar):
- Tombol toggle high contrast mode. Dua mode: hitam-kuning (untuk low vision) dan hitam-putih (untuk photosensitivity). Mode disimpan di `localStorage` dan diterapkan saat load.
- Slider ukuran teks. Range: 14px sampai 22px, default 16px. Ubah `font-size` di `<html>` element (semua ukuran font menggunakan `rem` agar ikut berubah). Simpan ke `localStorage`.
- Tombol "Baca Instruksi" yang aktifkan Web Speech API.

**Voice Guide** via Web Speech API:
- Deteksi dukungan: `if ('speechSynthesis' in window)`. Jika tidak didukung, sembunyikan tombol voice guide.
- Fungsi `readInstructions(stepNumber)` — baca instruksi untuk step saat ini menggunakan `SpeechSynthesisUtterance` dengan bahasa `id-ID`.
- Fungsi `stopReading()` — panggil `speechSynthesis.cancel()`.
- Teks instruksi per step di-hardcode sebagai array di dalam file ini (dalam Bahasa Indonesia).

**High Contrast Mode:**
- Saat aktif, tambahkan class `hc-black-yellow` atau `hc-black-white` ke `<html>` element.
- CSS di `accessibility.css` mendefinisikan aturan untuk class ini yang mengoverride warna seluruh halaman.

---

## 12. Keamanan & Anti-Abuse

### 12.1 Perlindungan Input

**XSS Prevention:** Semua output ke HTML menggunakan `esc()` CI4. Untuk output ke dalam atribut HTML: `esc($value, 'attr')`. Untuk output ke dalam JavaScript: `esc($value, 'js')`. Untuk output ke dalam CSS: `esc($value, 'css')`. Content Security Policy header ditambahkan via `SecurityHeadersFilter`: `default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';`. Perhatian: `unsafe-inline` untuk style diperlukan karena CSS inline digunakan di template CV untuk Dompdf.

**SQL Injection Prevention:** Seluruh query menggunakan CI4 Query Builder atau prepared statement. Tidak pernah string concatenation dalam query. Tidak ada raw query yang menerima input pengguna langsung.

**CSRF Protection:** CI4 CSRF filter aktif global. Token dikirim di setiap form sebagai hidden field dan di setiap AJAX request sebagai header `X-CSRF-TOKEN`. Token tidak di-regenerate setiap request (setting `csrfRegenerate = false`) agar AJAX tidak invalidate token saat navigasi antar step.

**File Upload Security:** Validasi MIME type tidak hanya dari ekstensi tapi juga dari isi file menggunakan `finfo_file()`. Rename file dengan nama yang aman (tidak menggunakan nama asli dari pengguna). Simpan di luar document root jika memungkinkan, atau di folder dengan `.htaccess deny all`. Tidak pernah serve file upload langsung; selalu melalui controller yang memvalidasi session.

### 12.2 Rate Limiting

Rate limiting diterapkan berlapis:

**Lapisan 1 — Per Session per Action:** Implementasi via `RateLimiter::check()`. Sudah dijelaskan di bagian Libraries.

**Lapisan 2 — Per IP per Action:** `RateLimiter::checkIpBased()`. Batas IP lebih longgar dari batas session tapi tetap ada. Ini untuk mencegah satu IP membuat banyak session palsu dan masih bisa melakukan spam.

**Lapisan 3 — Per IP Global:** Jika satu IP mengirim lebih dari 200 request ke endpoint apapun dalam 10 menit, IP tersebut di-block sementara (30 menit) dengan mencatat di tabel `rate_limits` dengan action khusus `ip_block`. Ini adalah circuit breaker untuk serangan DDoS ringan.

Respons ketika kena rate limit: jika request AJAX, return JSON dengan HTTP 429, body berisi `{error: "rate_limited", message: "...", retry_after: seconds}`. Jika request biasa (non-AJAX), redirect ke halaman `errors/rate_limit.php` yang menampilkan pesan ramah dan timer countdown.

Pesan rate limit harus: ramah (tidak menuduh pengguna melakukan kejahatan), informatif (jelaskan kapan bisa coba lagi), helpful (tawarkan alternatif seperti download dalam format berbeda).

### 12.3 Anti-Scraping & Anti-Bot

**Fingerprinting:** Field `fingerprint_hash` di `cv_sessions` menggabungkan User-Agent, Accept-Language, dan subnet IP. Jika fingerprint yang sama membuat lebih dari 5 session berbeda dalam 24 jam, semua session tersebut di-flag dan tindakan eksportnya dibatasi.

**Honeypot Field:** Tambahkan satu field tersembunyi di form wizard dengan name seperti `website` atau `confirm_email` (nama yang menarik bot). Sembunyikan via CSS `display:none` bukan via `type="hidden"`. Jika field ini terisi saat submit, tandai sebagai bot dan log ke `abuse_reports` tanpa memberikan error yang jelas ke pengguna (silent rejection).

**User Agent Validation:** Request tanpa User-Agent header (atau dengan User-Agent yang sangat umum dipakai scraper) ke endpoint export di-flag. Ini tidak diblock secara langsung karena bisa false positive, tapi dilog.

**Export Content Inspection:** Sebelum generate PDF, lakukan pemeriksaan cepat terhadap konten: apakah field `name` terisi (jika kosong, tolak export dengan pesan ramah), apakah ada setidaknya satu section yang terisi selain personal. Ini mengurangi pemborosan resource dari export CV kosong.

### 12.4 Security Headers

`SecurityHeadersFilter` menambahkan header berikut ke semua response:

- `X-Frame-Options: SAMEORIGIN` — mencegah clickjacking
- `X-Content-Type-Options: nosniff` — mencegah MIME sniffing
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()` — tidak meminta permission yang tidak dibutuhkan
- `Content-Security-Policy` — seperti didefinisikan di 12.1

### 12.5 Proteksi Data Pengguna

**Data Minimization:** Jangan simpan raw User-Agent, simpan hash-nya saja. Jangan simpan IP address lengkap di semua tabel; hanya di `cv_sessions` dan `export_logs` untuk keperluan abuse detection.

**Data Retention:** Buat mekanisme yang jelas dan otomatis untuk menghapus data setelah 30 hari via event MySQL atau cron job.

**Photo Storage:** Foto profil pengguna disimpan dengan nama yang tidak bisa ditebak (`{session_token}_photo.jpg`, di mana session_token adalah string acak panjang). Folder upload diproteksi dari akses langsung.

**Transparency:** Halaman kebijakan privasi harus menjelaskan dengan jelas dalam Bahasa Indonesia sederhana: data apa yang dikumpulkan, untuk tujuan apa, berapa lama disimpan, dan bahwa tidak ada login yang diperlukan.

---

## 13. Aksesibilitas

### 13.1 Standard Minimum

Semua halaman harus memenuhi WCAG 2.1 Level AA. Prioritas dari yang terpenting:

**Perceivable:**
- Semua gambar non-dekoratif memiliki `alt` text yang deskriptif
- Semua gambar dekoratif memiliki `alt=""` agar screen reader melewatinya
- Rasio kontras warna minimal 4.5:1 untuk teks normal, 3:1 untuk teks besar
- Konten tidak hanya mengandalkan warna untuk menyampaikan informasi

**Operable:**
- Semua fungsi dapat dioperasikan via keyboard
- Tidak ada keyboard trap (fokus tidak terjebak dalam komponen)
- Skip link tersedia di awal setiap halaman
- Tombol dan link memiliki ukuran tap target minimal 44x44 pixel

**Understandable:**
- Bahasa halaman dideklarasikan dengan `lang="id"`
- Label form yang jelas dan terhubung ke input via `for`/`id`
- Pesan error yang spesifik (bukan hanya "Field ini wajib diisi" tapi "Nama lengkap harus diisi sebelum melanjutkan")
- Instruksi tidak bergantung pada posisi atau warna

**Robust:**
- HTML yang valid, divalidasi via W3C Validator
- Semua komponen interaktif menggunakan elemen semantik yang tepat atau ARIA yang benar
- Tidak ada ARIA attribute yang konflik dengan semantik native

### 13.2 Screen Reader Support

Pastikan urutan heading logis di setiap halaman (tidak melompat dari H1 ke H4).

Gunakan landmark region HTML5: `<header>`, `<nav>`, `<main>`, `<footer>`, `<aside>` untuk preview area.

Form wizard step transition: ketika pindah step, fokus harus berpindah ke heading step yang baru (bukan ke tombol atau ke atas halaman). Ini penting agar screen reader tahu konten telah berubah.

AJAX update: gunakan ARIA live region untuk mengumumkan: perubahan preview, status save, perubahan overflow status.

Template preview yang di-load via AJAX: container preview harus memiliki `aria-label="Preview CV"` dan `aria-live="polite"` sehingga screen reader mengumumkan update.

### 13.3 Motor Impairment Support

Semua tombol dan link memiliki ukuran minimal 44x44 pixel. Di mobile, ini sangat penting.

Tombol-tombol kritis (Tambah entry, Hapus entry, Lanjut, Kembali) tidak boleh terlalu berdekatan satu sama lain untuk menghindari accidental tap.

Form wizard tidak menggunakan drag-and-drop untuk mengurutkan item (karena tidak accessible). Gunakan tombol "Pindah Atas" dan "Pindah Bawah" jika perlu reorder.

### 13.4 Cognitive Accessibility

Progress bar yang jelas menunjukkan posisi dalam wizard (misalnya: "Langkah 2 dari 5 — Pendidikan").

Setiap step memiliki judul yang jelas dan penjelasan singkat apa yang diisi di step ini.

Auto-save yang terlihat: pengguna harus tahu data mereka tersimpan. Status "Tersimpan" harus terlihat dan mudah dipahami.

Notifikasi tidak menggunakan istilah teknis. Tidak pernah menampilkan kode error kepada pengguna; selalu terjemahkan ke pesan yang actionable.

---

## 14. SEO & Landing Page

### 14.1 Technical SEO

**URL Structure:**
- Landing page: `/`
- Halaman wizard: `/buat-cv` (satu halaman)
- Tidak ada URL per-step yang di-index (step adalah state internal, bukan halaman terpisah)

**Meta Tags per Halaman:**
- Landing page: title "Buat CV Gratis Online | Create CV Gratis", description 150-160 karakter yang mengandung keyword utama
- Halaman wizard: `<meta name="robots" content="noindex, nofollow">` karena ini halaman personal pengguna

**Sitemap XML:** Hanya berisi landing page dan halaman statis. Disubmit ke Google Search Console.

**robots.txt:** Izinkan crawling landing page. Larang crawling: `/api/*`, `/export/*`, `/cv/*`, `/cron/*`.

### 14.2 Schema.org

Di landing page, tambahkan JSON-LD Schema.org untuk:

Type `WebApplication`: berisi nama, URL, deskripsi, `applicationCategory: "BusinessApplication"`, `isAccessibleForFree: true`, `offers: {price: 0}`.

Type `HowTo`: panduan singkat cara membuat CV dengan langkah-langkah, membantu Google menampilkan rich result.

### 14.3 Performa untuk SEO

Core Web Vitals target:
- LCP (Largest Contentful Paint): di bawah 2.5 detik. Pastikan gambar hero (jika ada) memiliki `loading="eager"` dan ukuran tidak lebih dari 100KB. Teks hero tidak bergantung pada load font eksternal.
- CLS (Cumulative Layout Shift): 0. Semua gambar dan elemen dinamis harus memiliki dimensi yang ditentukan sebelum konten load.
- FID/INP: di bawah 100ms. JavaScript tidak memblokir thread utama saat load.

---

## 15. Optimasi Shared Hosting

### 15.1 Memory Management

Dompdf membutuhkan memori paling banyak dari semua operasi. Manajemennya:

Sebelum generate PDF, cek memory usage saat ini dengan `memory_get_usage(true)`. Jika sudah di atas 64MB, tolak request dengan pesan ramah dan saran untuk menggunakan template Minimalist.

Sementara generate PDF, naikkan `memory_limit` ke `96M` via `ini_set()`. Setelah selesai, kembalikan ke nilai default dengan `ini_restore()`.

Tidak pernah menyimpan binary PDF dalam variabel PHP lebih lama dari yang diperlukan. Gunakan `file_put_contents()` ke disk, kemudian stream dari disk ke browser menggunakan `readfile()`.

Setelah proses PDF selesai, panggil `unset($dompdf)` dan `gc_collect_cycles()`.

### 15.2 Database Optimization

Query yang dijalankan paling sering adalah `findByToken()` di `CvSessionModel`. Pastikan index pada `session_token` sudah ada dan digunakan (verifikasi dengan `EXPLAIN`).

Gunakan `SELECT` dengan kolom spesifik, bukan `SELECT *`. Ini mengurangi data yang ditransfer dari database ke PHP.

Batasi koneksi database: CI4 menggunakan persistent connection secara default. Pastikan setting ini cocok dengan batas koneksi hosting.

Cleanup job WAJIB berjalan teratur. Tanpa cleanup, tabel `rate_limits` akan terus membesar dan memperlambat query.

### 15.3 File Caching PDF

Strategi caching PDF dijelaskan di `Export.php`. Beberapa hal tambahan:

Folder cache PDF harus diproteksi dari akses langsung (`.htaccess deny all`).

Nama file cache: `{content_hash}.pdf` — tidak menyertakan session_token di nama file cache untuk memungkinkan cache sharing di masa depan (jika dua user punya konten identik, mereka bisa berbagi cache). Namun untuk fase awal, simpan per-session: `{session_id}_{content_hash}.pdf`.

Cleanup file cache: file cache PDF yang berumur lebih dari 1 jam (`CCG_PDF_CACHE_TTL`) dihapus oleh cron job. Implementasi di `Cron::cleanup()`.

### 15.4 Aset Statis

Semua CSS dan JS di-minify sebelum deployment. Tidak ada tool build yang rumit diperlukan; minifikasi manual atau menggunakan online tool cukup.

CSS dan JS menggunakan filename versioning via query string: `app.css?v=20250601`. Saat ada update, ganti nilai `v`. Ini memaksa browser mengambil versi terbaru.

Gambar: semua gambar di landing page dioptimasi sebelum upload. Target: foto/ilustrasi di bawah 100KB, ikon SVG digunakan untuk semua ikon UI.

Tidak ada CDN external yang digunakan (mengikuti prinsip minimalisme dan tidak bergantung pada third-party uptime). Semua font untuk tampilan browser di-bundle secara lokal. Font untuk Dompdf (DejaVu) sudah include dalam paket Dompdf.

### 15.5 PHP Configuration

Setting PHP di `.htaccess` atau `php.ini` lokal (jika shared hosting mengizinkan):

`upload_max_filesize = 6M` — untuk upload foto profil.

`post_max_size = 8M` — sedikit di atas upload_max_filesize.

`max_execution_time = 60` — cukup untuk generate PDF yang kompleks.

`max_input_vars = 500` — untuk form yang punya banyak field repeatable.

`error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED` di production — jangan tampilkan notice dan deprecated warning.

`display_errors = 0` di production — semua error ditulis ke log, tidak ditampilkan ke pengguna.

---

## 16. Fase Pengerjaan

### 16.1 Fase 1 — MVP (Target: 2 Minggu)

**Minggu 1 (Infrastruktur & Foundation):**

Hari 1-2: Setup CI4, konfigurasi database, buat semua tabel. Setup `.env` dan `.htaccess`. Verifikasi CI4 berjalan di shared hosting.

Hari 3-4: Implementasi `SessionManager` dan `SessionInitFilter`. Pastikan session dibuat, disimpan ke database, dan cookie bekerja dengan benar. Tulis test manual untuk skenario: session baru, session kembali dalam 30 hari, session expired.

Hari 5-7: Implementasi `CvDataModel`, `CvSessionModel`. Implementasi Controller `Cv.php` untuk `wizard()` dan `step()`. Buat view skeleton untuk 3 step pertama (personal, education, experience) dengan form dasar.

**Minggu 2 (Fitur Core):**

Hari 8-9: Implementasi `Api::autosave()`. Implementasi `autosave.js`. Test auto-save di semua browser.

Hari 10-11: Implementasi template Classic. Implementasi `TemplateManager::renderToHtml()`. Implementasi `Api::preview()`. Implementasi `preview.js`.

Hari 12-13: Implementasi `PdfGenerator`. Implementasi `Export::pdf()` tanpa caching dulu. Test generate PDF.

Hari 14: Implementasi landing page sederhana. Deploy ke hosting. Smoke test end-to-end.

**Deliverable Fase 1:** Pengguna bisa mengisi form 3 step, auto-save bekerja, preview dengan template Classic tampil, bisa download PDF. Tidak ada rate limiting, tidak ada caching, tidak ada template lain.

### 16.2 Fase 2 — Template & Export (Target: 1 Minggu)

Hari 1-2: Implementasi 4 template CV tersisa (Modern, Sidebar, Minimalist, Professional). Implementasi `Api::switchTemplate()`. Implementasi `template_switcher.php` dan JavaScript pendukungnya.

Hari 3-4: Implementasi Step 4 (Skills & Languages) dan Step 5 (Preview & Download). Implementasi `Export::txt()` dan `Export::json()`.

Hari 5-7: Implementasi `RateLimiter` dan `RateLimitModel`. Aktifkan rate limiting di semua endpoint export. Implementasi PDF caching di `Export::pdf()`. Test caching bekerja.

**Deliverable Fase 2:** 5 template tersedia, pengguna bisa ganti template, export ke 3 format, rate limiting aktif, caching PDF aktif.

### 16.3 Fase 3 — Intelligence & Robustness (Target: 1 Minggu)

Hari 1-2: Implementasi `ContentAnalyzer`. Implementasi `overflow.js` dan komponen `overflow_warning.php`. Test semua skenario overflow di semua template.

Hari 3-4: Implementasi mode ringkasan (truncation otomatis atau peringatan yang lebih detail). Implementasi `ContentAnalyzer::estimatedPages()`. Tampilkan estimasi halaman di Step 5.

Hari 5-7: Implementasi `Cron::cleanup()`. Setup cron job di hosting. Implementasi `RateLimiter::checkIpBased()` untuk proteksi IP-level. Test cleanup berjalan dan membersihkan data dengan benar.

**Deliverable Fase 3:** Overflow detection bekerja, mode ringkasan tersedia, cron cleanup berjalan, rate limiting per-IP aktif.

### 16.4 Fase 4 — Polish & Production (Target: 1 Minggu)

Hari 1-2: Implementasi seluruh fitur aksesibilitas: high contrast mode, font scaler, voice guide, skip link, ARIA yang lengkap.

Hari 3-4: SEO optimization: meta tag dinamis, Schema.org, sitemap, robots.txt. Optimasi performa: minifikasi CSS/JS, kompresi Gzip, cache header.

Hari 5-6: Implementasi honeypot, abuse reporting, flag system. Review semua security header. Audit CSRF di semua form dan AJAX.

Hari 7: End-to-end testing di mobile device asli. Tes dengan screen reader (NVDA atau browser built-in). Perbaikan bug. Siap production.

**Deliverable Fase 4:** Aplikasi production-ready, aksesibel, aman, teroptimasi.

---

## 17. Panduan Deployment

### 17.1 Prasyarat di Shared Hosting

Verifikasi bahwa hosting mendukung:
- PHP 8.1 atau lebih baru (8.3 direkomendasikan)
- Ekstensi PHP: `intl`, `mbstring`, `gd`, `curl`, `openssl`, `ctype`, `json`, `pdo`, `pdo_mysql` — semuanya standar di shared hosting modern
- MySQL 5.7+ atau MariaDB 10.3+
- Akses ke `.htaccess` untuk Apache (atau konfigurasi setara untuk LiteSpeed/Nginx)
- Kemampuan membuat cron job (via cPanel Cron Jobs)
- Direktori `writable/` dapat ditulis oleh web server process

### 17.2 Langkah Deployment

**Langkah 1 — Persiapan Lokal:**
Jalankan `composer install --no-dev` untuk menginstall dependency produksi saja (tanpa testing library). Verifikasi tidak ada error.

Minifikasi semua CSS dan JS di `public/assets/`. Update query string versioning di semua link stylesheet dan script.

Set `CI_ENVIRONMENT=production` di `.env`.

Pastikan semua kredensial database production sudah benar di `.env`.

**Langkah 2 — Upload File:**
Upload semua file project ke server hosting. Jika menggunakan cPanel File Manager, upload sebagai ZIP kemudian extract. Jika menggunakan FTP, gunakan binary mode untuk semua file.

Struktur yang PENTING: folder `app/`, `system/` (Codeigniter framework files), `writable/`, dan `public/` harus berada di level yang benar. Root domain harus menunjuk ke folder `public/`.

Jika hosting tidak bisa setting document root ke `public/`, gunakan `.htaccess` di root untuk redirect ke `public/`. Ini kurang ideal dari segi keamanan tapi umum di shared hosting.

**Langkah 3 — Database Setup:**
Buat database baru di cPanel. Buat user database dan assign ke database tersebut dengan semua privilege.

Import SQL schema (semua 5 tabel + EVENT cleanup) via phpMyAdmin. Verifikasi semua tabel terbuat dengan benar dan charset `utf8mb4`.

Jika MySQL EVENT tidak tersedia, skip bagian EVENT dan setup cron job sebagai gantinya.

**Langkah 4 — Permission:**
Set permission folder:
- `writable/` dan semua subfolder: chmod 755 atau 775 (web server harus bisa menulis)
- `public/storage/pdf-cache/`: chmod 755
- File `public/index.php`: chmod 644
- File `.env`: chmod 600 (hanya owner yang bisa baca)
- Semua folder lain: chmod 755
- Semua file PHP: chmod 644

**Langkah 5 — Konfigurasi .env Production:**
Isi semua variabel di `.env` dengan nilai production. Pastikan `app.baseURL` menggunakan domain aktual. Pastikan `database.*` menggunakan kredensial database production.

Generate `CCG_CRON_SECRET` baru yang aman: `php -r "echo bin2hex(random_bytes(32));"`.

**Langkah 6 — Test Awal:**
Buka landing page di browser. Verifikasi tidak ada PHP error. Klik "Buat CV Sekarang". Verifikasi form terbuka dan session terbuat (cek tabel `cv_sessions` via phpMyAdmin). Isi beberapa field. Verifikasi auto-save bekerja (cek tabel `cv_data`). Coba download PDF. Verifikasi file ter-download.

**Langkah 7 — Setup Cron Job:**
Di cPanel → Cron Jobs, tambahkan job baru:
- Timing: setiap hari pukul 03:00 (pilih: Minute 0, Hour 3, Day *, Month *, Weekday *)
- Command: `curl -s "https://[DOMAIN]/cron/cleanup?secret=[CCG_CRON_SECRET]" > /dev/null 2>&1`

Test cron job dengan menjalankannya secara manual sekali dan verifikasi response di log.

**Langkah 8 — SSL/HTTPS:**
Pastikan SSL certificate aktif (Let's Encrypt tersedia gratis di hampir semua shared hosting modern via cPanel). Tambahkan redirect HTTP ke HTTPS di `.htaccess` root: RewriteCond `%{HTTPS} off`, RewriteRule redirect ke `https://`.

Set cookie `Secure` flag: ini otomatis jika `$this->request->isSecure()` return true di CI4.

### 17.3 Monitoring Post-Deployment

Pantau log error CI4 di `writable/logs/` secara berkala di minggu pertama setelah deployment.

Pantau ukuran folder `writable/` — jika membesar terus, cron cleanup mungkin tidak berjalan.

Pantau tabel `abuse_reports` di database — jika ada entri, review dan putuskan apakah perlu tindakan.

Cek tabel `export_logs` untuk memahami pola penggunaan dan apakah caching bekerja (`was_cached = 1`).

---

## 18. Checklist QA

### 18.1 Fungsionalitas Core

- [ ] Landing page load tanpa error, semua section tampil
- [ ] Klik CTA → halaman wizard terbuka, session terbuat di database
- [ ] Cookie CCG session dibuat dengan expiry 30 hari
- [ ] Mengisi field di Step 1 → auto-save aktif setelah 2 detik, status "Tersimpan" muncul
- [ ] Pindah ke Step 2 → data Step 1 tetap ada setelah kembali
- [ ] Refresh halaman → data tidak hilang (diambil kembali dari database)
- [ ] Tambah multiple entry di Step 2 dan 3 → semua tersimpan
- [ ] Hapus entry → terhapus dari form dan tersimpan ke database
- [ ] Preview realtime update saat isi form
- [ ] Ganti template → preview update, data tidak hilang
- [ ] Overflow terdeteksi ketika jumlah item melebihi kapasitas template
- [ ] Download PDF → file ter-download, bisa dibuka di PDF reader
- [ ] Download TXT → file ter-download, konten readable
- [ ] Download JSON → file ter-download, JSON valid

### 18.2 Keamanan

- [ ] Input dengan karakter HTML (`<script>alert(1)</script>`) di field nama → di-escape dengan benar di preview dan PDF, tidak ada XSS
- [ ] Request AJAX tanpa CSRF token → ditolak dengan 403
- [ ] Akses endpoint export tanpa session valid → redirect ke landing page
- [ ] Akses `/api/*` langsung dari browser → ditolak atau return JSON error
- [ ] Akses `/cron/cleanup` tanpa secret → return 403
- [ ] Rate limit PDF: lebih dari 3 request per menit → kena rate limit, pesan ramah tampil
- [ ] Akses `/writable/` via URL → 403 atau 404
- [ ] Upload file bukan gambar ke field foto → ditolak dengan pesan error yang tepat

### 18.3 Aksesibilitas

- [ ] Tab through seluruh halaman tanpa mouse → semua element interaktif dapat difocus
- [ ] Skip link tampil saat keyboard focus → klik membawa ke main content
- [ ] Screen reader (VoiceOver/NVDA) bisa membaca seluruh form dengan konteks yang benar
- [ ] High contrast mode toggle → warna berubah, tetap terbaca
- [ ] Font size slider → teks membesar/mengecil, layout tidak rusak
- [ ] Voice guide → instruksi dibaca dengan benar
- [ ] Progress bar diumumkan screen reader saat pindah step
- [ ] Error form → focus berpindah ke field yang error, pesan error dibaca screen reader
- [ ] Preview CV memiliki label yang jelas untuk screen reader

### 18.4 Mobile

- [ ] Tampilan di viewport 360px tidak ada horizontal scroll
- [ ] Semua tombol minimal 44x44 pixel saat diukur di mobile
- [ ] Toggle antara tab Form dan Preview di mobile bekerja
- [ ] Upload foto dari kamera HP bekerja
- [ ] Download file di mobile browser bekerja (Android Chrome, iOS Safari)
- [ ] Keyboard virtual muncul saat tap field teks, tidak menutup konten penting

### 18.5 Performa

- [ ] Landing page load time di bawah 3 detik di koneksi 3G (simulasi via DevTools)
- [ ] Generate PDF selesai di bawah 30 detik untuk konten normal
- [ ] Auto-save tidak menyebabkan lag input (debounce bekerja)
- [ ] Live preview update terasa responsif (tidak lebih dari 500ms setelah ketik)
- [ ] Cache PDF bekerja: export kedua untuk konten yang sama lebih cepat dan `was_cached = 1` di database

### 18.6 Cross-Browser

- [ ] Chrome (Android & Desktop)
- [ ] Firefox (Desktop)
- [ ] Safari (iOS)
- [ ] Samsung Internet (Android)
- [ ] Edge (Desktop)

---

## Lampiran: Dependency & Versi

Semua dependency dikelola via Composer. Versi yang digunakan:

- `codeigniter4/codeigniter4`: ^4.5
- `dompdf/dompdf`: ^2.0
- Tidak ada dependency lain.

Tidak ada dependency npm atau package.json karena tidak menggunakan build tool JavaScript.

Font untuk Dompdf: DejaVu Sans (sudah termasuk dalam paket Dompdf). Tidak perlu install font terpisah.

---

*Blueprint ini adalah dokumen living document. Setiap perubahan requirement harus direfleksikan di sini sebelum implementasi dimulai.*

*Versi blueprint ini mencakup semua keputusan arsitektur dan implementasi yang diperlukan untuk membangun project Create CV Gratis dari nol hingga siap production di shared hosting.*
