# BeDaie Jelajah

> **Membawa Ilmu, Menghidupkan Ummah.**
> *Dari Masjid ke Masjid, Dari Hati ke Hati.*

Sistem web untuk gerakan **BeDaie Jelajah** — menghubungkan BeDaie dengan masjid,
surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.

Aliran teras sistem:

**Aliran masuk** — orang awam menjemput BeDaie:

```
MOHON → BINCANG → DISAHKAN → HALAMAN PROGRAM DIJANA → PENGGERAK SEBARKAN
      → PESERTA MENDAFTAR → QR KEHADIRAN → SIJIL AUTOMATIK
```

**Aliran keluar** — staf BeDaie memburu lokasi sendiri:

```
SASARAN → CARI KONTAK → HUBUNGI → BERBINCANG → SETUJU → PERMOHONAN → JELAJAH
```

Setiap sasaran merekod **dari mana ia datang** (staf terus, rakan, penggerak,
rujukan) supaya sumbangan sebenar setiap rakan boleh diukur.

---

## 1. Stack

| Lapisan | Teknologi |
|---|---|
| Rangka kerja | Laravel 13 (PHP 8.4) |
| Pangkalan data | MySQL 8 |
| UI | Blade + Livewire 3 + Alpine.js |
| Gaya | Tailwind CSS v4 (design tokens jenama BeDaie) |
| Bundler | Vite 8 |
| QR | `bacon/bacon-qr-code` (jana) + `html5-qrcode` (imbas, dimuat malas) |
| PDF sijil | `barryvdh/laravel-dompdf` |
| Queue | Database queue (e-mel, WhatsApp, penjanaan sijil) |
| Storan | Laravel filesystem (`public` disk) untuk poster, galeri, sijil |

---

## 2. Keperluan

- PHP 8.4 (sambungan: `pdo_mysql`, `gd`, `imagick`, `zip`, `mbstring`)
- Composer 2
- Node.js 20+ dan npm
- MySQL 8 (atau MariaDB 10.6+)

---

## 3. Pemasangan

```bash
# 1. Kebergantungan
composer install
npm install

# 2. Persekitaran
cp .env.example .env         # jika .env belum wujud
php artisan key:generate

# 3. Pangkalan data
mysql -u root -e "CREATE DATABASE bedaie_jelajah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# kemas kini DB_* dalam .env

# 4. Migrasi + data demo
php artisan migrate:fresh --seed

# 5. Pautan storan awam (poster, galeri, sijil PDF)
php artisan storage:link

# 6. Bina aset
npm run build                # atau: npm run dev semasa pembangunan
```

Jalankan pelayan:

```bash
php artisan serve
php artisan queue:work        # perlu untuk e-mel, WhatsApp & sijil
```

---

## 4. Pembolehubah persekitaran

### Wajib

```dotenv
APP_NAME="BeDaie Jelajah"
APP_ENV=local
APP_URL=http://localhost:8000
APP_LOCALE=ms
APP_TIMEZONE=Asia/Kuala_Lumpur

DB_CONNECTION=mysql
DB_DATABASE=bedaie_jelajah
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
```

### Jenama & sokongan

```dotenv
JELAJAH_BRAND_NAME=BeDaie
JELAJAH_ORG_NAME="Dakwah Digital Network"
JELAJAH_SUPPORT_PHONE=60123456789
JELAJAH_SUPPORT_EMAIL=jelajah@bedaie.com.my
```

### E-mel

```dotenv
MAIL_MAILER=log               # tukar kepada smtp/api untuk produksi
MAIL_FROM_ADDRESS="jelajah@bedaie.com.my"
MAIL_FROM_NAME="${APP_NAME}"
```

### WhatsApp (pilihan — perlu kredensial pihak ketiga)

```dotenv
WHATSAPP_ENABLED=false
WHATSAPP_DRIVER=log           # 'http' apabila provider sedia
WHATSAPP_BASE_URL=
WHATSAPP_API_KEY=
WHATSAPP_SESSION=default
```

Selagi `WHATSAPP_ENABLED=false`, semua mesej WhatsApp direkod ke
`notification_logs` dan log aplikasi — sistem kekal berfungsi penuh.

### Pembayaran (pilihan — perlu kredensial pihak ketiga)

```dotenv
PAYMENT_GATEWAY=manual        # manual | bayarcash

PAYMENT_BANK_NAME=Maybank
PAYMENT_BANK_ACCOUNT=000000000000
PAYMENT_BANK_HOLDER="Dakwah Digital Network"

BAYARCASH_API_TOKEN=
BAYARCASH_PORTAL_KEY=
BAYARCASH_SECRET_KEY=
BAYARCASH_SANDBOX=true
```

Gateway yang belum dikonfigurasi akan **jatuh balik ke mod manual**
(arahan pindahan bank + pengesahan admin) secara automatik.

> **Jangan** letakkan sebarang kunci API dalam kod sumber.

---

## 5. Tugas berjadual

Satu entri cron sahaja diperlukan:

```cron
* * * * * cd /laluan/projek && php artisan schedule:run >> /dev/null 2>&1
```

| Tugas | Kekerapan | Fungsi |
|---|---|---|
| `jelajah:hantar-peringatan` | Setiap jam | Peringatan 7 hari / 1 hari / 2 jam sebelum program |
| `jelajah:kemas-kini-program` | Setiap jam | Tandakan berlangsung; tutup program tamat → lepaskan sijil + minta maklum balas |
| Naikkan senarai menunggu | 30 minit | Isi tempat kosong daripada senarai menunggu |
| Bersih rekod peringatan | Mingguan | Buang jejak peringatan melebihi 6 bulan |

Kedua-dua arahan menyokong `--dry-run` untuk semakan tanpa mengubah data.

---

## 6. Akaun demo (pembangunan sahaja)

Dicipta oleh `DemoSeeder`, yang **tidak** dijalankan dalam `APP_ENV=production`.

| Peranan | E-mel | Kata laluan |
|---|---|---|
| Super Admin | `admin@bedaie.test` | `password` |
| Penggerak Jelajah | `penggerak@bedaie.test` | `password` |
| Peserta | `peserta@bedaie.test` | `password` |

Log masuk menerima **e-mel atau nombor WhatsApp**.

### Akaun Penggerak yang dicipta automatik

Apabila permohonan awam diterima, sistem mencipta akaun Penggerak dengan
kata laluan rawak yang **tidak diketahui sesiapa** dan — jika pemohon tidak
memberi e-mel — alamat pemegang tempat `@jelajah.bedaie.local`. Mereka masuk
melalui **pautan log masuk WhatsApp** (`/masuk-pautan`):

- Pautan dihantar automatik sebaik permohonan berstatus *Diterima*.
- Pemohon boleh memintanya sendiri bila-bila masa di `/masuk-pautan`.
- Admin boleh menghantarnya semula dari halaman butiran Penggerak.
- Sekali-guna, sah 30 minit, membatalkan pautan sebelumnya, dan berfungsi
  walaupun peranti itu masih log masuk sebagai orang lain.

Selepas masuk, Penggerak boleh **menetapkan kata laluan** dan mengisi e-mel
sebenar di halaman Profil. Halaman itu tidak meminta "kata laluan semasa"
selagi `password_set_at` masih kosong.

---

## 7. Struktur modul

```
app/
├── Console/Commands/      Tugas berjadual (peringatan, kitaran hayat program)
├── Enums/                 Status & kategori berjenis (13 enum)
├── Http/
│   ├── Controllers/       Public · Auth · Penggerak · Peserta · Admin
│   └── Middleware/        EnsureUserHasRole
├── Livewire/
│   ├── Public/            Borang permohonan, pendaftaran, minat, maklum balas
│   ├── CheckIn/           Pengimbas QR
│   └── Admin/             Carian global, aliran kerja permohonan, editor program,
│                          galeri, testimoni, penceramah, kategori, rakan,
│                          bayaran, sijil, tetapan, template notifikasi
├── Models/                24 model Eloquent
├── Policies/              Application · Event · Registration
├── Services/
│   ├── ApplicationService     Hantar & tukar status permohonan
│   ├── EventSpaceGenerator    Automasi jana halaman program
│   ├── RegistrationService    Pendaftaran, kapasiti, senarai menunggu
│   ├── AttendanceService      Check-in QR / manual / walk-in
│   ├── CertificateService     Jana, batal, jana semula, PDF
│   ├── EventLifecycleService  Terbit → berlangsung → selesai
│   ├── ImpactStatsService     Statistik peta & laporan
│   ├── Notifications/         In-app · E-mel · WhatsApp (modular)
│   └── Payments/              Abstraksi gateway (manual · BayarCash)
└── Support/Phone.php      Normalisasi nombor Malaysia
```

---

## 8. Skema pangkalan data (ringkas)

**Rujukan** `states` · `districts` · `event_categories` · `speakers` · `venues` · `settings`

**Permohonan (aliran masuk)** `applications` · `application_status_histories` · `application_notes`

**Sasaran (aliran keluar)** `outreach_targets` · `outreach_activities`

**Program** `events` · `event_mobilizers` · `qr_tokens` · `tickets`

**Peserta** `users` · `mobilizer_profiles` · `registrations` · `registration_guests` · `payments`

**Rakaman** `event_recordings` · `recording_views`

**Kehadiran & sijil** `attendance_records` · `event_reminder_dispatches` ·
`certificate_templates` · `certificates` · `certificate_status_histories`

**Penglibatan** `feedback` · `event_galleries` · `testimonials` ·
`area_interest_requests` · `partners`

**Sistem** `notifications` · `notification_templates` · `notification_logs` · `activity_logs`

Nota reka bentuk:

- Setiap rekod awam sensitif menggunakan **UUID/token** (`public_id`, `public_token`,
  `qr_tokens.token`) — ID berjujukan tidak pernah didedahkan pada URL awam.
- Soft delete pada rekod yang perlu diaudit.
- Index pada `slug`, `status`, `starts_at`, `phone`, `email`, `certificate_number`.

---

## 9. Senarai halaman

### Awam
`/` Utama · `/peta-jelajah` Peta · `/peta-jelajah/{negeri}` Butiran negeri ·
`/program` Program akan datang · `/jejak-jelajah` Arkib · `/pilihan-program` Kategori ·
`/jemput-bedaie` Borang jemputan · `/bawa-bedaie-ke-kawasan-saya` Permintaan kawasan ·
`/rakan-penaja` · `/galeri-impak` · `/tentang` · `/sijil/semak` Semakan sijil ·
`/polisi-privasi` · `/terma-penggunaan`

### Program (dijana automatik)
`/jelajah/{negeri}/{slug}` Halaman program · `/j/{kod}` Pautan pendek ·
`/jelajah/{negeri}/{slug}/daftar` Pendaftaran

### Peserta (pautan selamat, tanpa log masuk)
`/tiket/{token}` · `/tiket/{token}/batal` · `/tiket/{token}/kalendar` ·
`/maklum-balas/{token}` · `/bayaran/{id}`

### Penggerak Jelajah
`/penggerak` Ringkasan · `/penggerak/permohonan` · `/penggerak/program` ·
`/penggerak/peserta` · `/penggerak/sijil-laporan` · `/penggerak/profil`

### Peserta (log masuk)
`/saya` Pasport Ilmu · `/saya/program` · `/saya/sijil` · `/saya/profil`

### Admin
`/admin` dan 19 submenu (termasuk **Sasaran Jelajah** — aliran keluar) (permohonan, program, kalendar, penggerak, peserta,
kehadiran, sijil, pembayaran, negeri, permintaan kawasan, penceramah, kategori,
galeri, rakan, laporan, kandungan, template notifikasi, tetapan)

**Yang boleh diurus admin:** status permohonan & nota dalaman · penjanaan program ·
sunting program (tarikh, lokasi, kapasiti, harga, poster) · tutup/tangguh/batal
program · muat naik & kelulusan galeri · testimoni · penceramah · kategori ·
rakan & penaja · pengesahan bayaran manual · pembetulan & penarikan sijil ·
tetapan sistem · kandungan website · template notifikasi

### Operasi
`/check-in/{program}` Pengimbas QR (admin & Penggerak program berkenaan)

---

## 10. Peranan & kebenaran

| Peranan | Capaian |
|---|---|
| **Super Admin** | Semua modul, laporan penuh, eksport data, nota dalaman |
| **Penggerak Jelajah** | Permohonan & program **sendiri sahaja**, link/QR/poster, senarai peserta (telefon disamarkan), laporan ringkas, pengimbas check-in program sendiri |
| **Peserta** | Pendaftaran, tiket & QR, sijil, Pasport Ilmu, maklum balas |

Dikuatkuasakan melalui middleware `role:` dan policy
(`ApplicationPolicy`, `EventPolicy`, `RegistrationPolicy`) serta gate
`export-participants` dan `view-internal-notes`.

---

## 11. Ujian

```bash
php artisan test                       # 133 ujian
php artisan test --filter=Registration # satu suite
```

Ujian menggunakan pangkalan data MySQL berasingan:

```bash
mysql -u root -e "CREATE DATABASE bedaie_jelajah_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Liputan aliran kritikal:

| Suite | Liputan |
|---|---|
| `ApplicationFlowTest` | Borang 4 langkah, pengesahan, tukar status, **auto-jana EventSpace**, tiada penjanaan berganda |
| `RegistrationTest` | Pendaftaran, QR unik, kapasiti, ahli keluarga, senarai menunggu, duplikat ditolak, program berbayar, pembatalan, kod jemputan |
| `AttendanceAndCertificateTest` | Check-in QR, tolak check-in berganda, QR program lain, walk-in, carian manual, sijil hanya untuk yang hadir, format nombor sijil, pengesahan awam, jana semula & batal |
| `AccessControlTest` | Isolasi peranan, Penggerak lihat program sendiri sahaja, nota dalaman tersembunyi, telefon disamarkan, eksport admin sahaja, token tiket |
| `ImpactAndPassportTest` | Statistik peta dikemas kini, permintaan kawasan digabung & tidak didedahkan, Pasport Ilmu, maklum balas, kadar kehadiran |
| `PageRenderTest` | Semua halaman awam/penggerak/peserta/admin dibuka, manifest PWA, .ics, tiada halaman kosong |
| `ScheduledTaskTest` | Peringatan dalam tetingkap, tiada peringatan berganda, penutupan program automatik |
| `OutreachTest` | Papan sasaran keluar: tambah sasaran, sumber (staf/rakan/penggerak/rujukan), prestasi rakan, peringkat, rekod kontak, penukaran kepada permohonan, susulan tertunggak, kebenaran |
| `RecordingTest` | Akses rakaman: hadir vs berdaftar vs awam, belum diterbitkan, berjadual, program lain ditolak, URL benam YouTube, jejak tontonan |
| `PosterTest` | Poster 4:5 & hero 16:9 dijana, nisbah betul, tajuk panjang tidak pecah, penjanaan automatik semasa program disahkan, **lima gaya poster**, penukaran gaya melalui admin |
| `AdminManagementTest` | Sunting program, had kapasiti, notifikasi perubahan tarikh, muat naik & kelulusan galeri, CRUD penceramah/kategori/rakan/testimoni, pengesahan & pengecualian bayaran, pembetulan & penarikan sijil, tetapan, template notifikasi, kebenaran |

---

## 11b. Pengesahan pelayar

Disahkan dalam pelayar sebenar (Chromium) pada 1440×900 dan 390×844:

| Semakan | Keputusan |
|---|---|
| Console error / warning | **0** merentas laman utama, peta, borang, admin dan pengimbas |
| Peta interaktif | Klik negeri → panel butiran terisi, negeri lain malap |
| Aksesibiliti peta | Setiap negeri ialah `button` berlabel status + jumlah program |
| Countdown & kaunter impak | Alpine berjalan, data dari pangkalan data |
| Borang 4 langkah | Livewire responsif, penunjuk kemajuan berfungsi |
| Pautan dalam `?negeri=kelantan` | Negeri terpilih automatik, ID **tidak** terdedah pada URL |
| Pengimbas check-in (mobile) | Kiraan langsung, carian manual, telefon disamarkan |
| Skip-link & landmark | `banner` · `main` · `region` berlabel · `contentinfo` |

---

## 12. Keselamatan & privasi

- Kata laluan di-hash; reset kata laluan melalui token standard Laravel.
- Pautan log masuk: token rawak 48 aksara, sekali-guna, luput 30 minit,
  dihadkan 5 permintaan / 15 minit setiap IP. Borang memberi jawapan yang
  **sama** sama ada akaun wujud atau tidak, supaya nombor berdaftar tidak
  boleh diuji dari luar.
- E-mel pemegang tempat (`@jelajah.bedaie.local`) tidak pernah dihantar mel;
  notifikasi jatuh ke WhatsApp. E-mel sebenar hanya boleh diisi sekali oleh
  pemiliknya, dan tidak boleh ditukar melalui borang profil selepas itu.
- CSRF pada semua borang (kecuali webhook pembayaran yang disahkan tandatangan).
- Rate limiting: log masuk, pendaftaran akaun, borang permohonan, pendaftaran peserta.
- Token QR dijana rawak dan tidak mendedahkan ID pangkalan data.
- Nombor telefon peserta **disamarkan** pada paparan Penggerak dan direktori admin.
- Eksport CSV dilindungi gate `export-participants` (admin sahaja).
- Permintaan kawasan dipaparkan sebagai **agregat sahaja** kepada umum.
- Log aktiviti merekod tindakan penting (status permohonan, jana program,
  check-in, sijil, callback pembayaran).
- Persetujuan privasi direkod dengan cap masa pada permohonan dan pendaftaran.

---

## 13. Aset jenama

| Fail | Status |
|---|---|
| `public/brand/bedaie-logo-placeholder.svg` | **Placeholder** — gantikan dengan logo rasmi |
| `public/brand/bedaie-logo.svg` | Letakkan fail rasmi di sini; komponen `<x-brand.logo>` akan menggunakannya secara automatik |
| `public/img/icon-*.png` | Ikon PWA dijana daripada placeholder — jana semula selepas logo rasmi dipasang |

Logo tidak di-hotlink daripada laman lama. Design token (ungu `#8875FF`,
navy `#0A083B`, putih suam `#FAF9F6`, Poppins) ditakrifkan dalam
`resources/css/app.css`.

---

## 14. Cadangan deployment

1. **Pelayan**: PHP 8.4-FPM + Nginx (Laravel Forge sesuai).
2. **Env**: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS penuh.
3. **Optimize**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize
   ```
4. **Queue**: daemon Supervisor untuk `php artisan queue:work --tries=3`.
5. **Cron**: satu entri `schedule:run` (lihat bahagian 5).
6. **Storan**: pertimbangkan S3 untuk poster/galeri/sijil apabila trafik meningkat —
   tukar `FILESYSTEM_DISK` sahaja, kod tidak perlu diubah.
7. **Backup**: pangkalan data harian + folder `storage/app/public`.
8. **Seeder**: `DemoSeeder` tidak akan berjalan dalam produksi. Jalankan
   `StateSeeder`, `CatalogSeeder`, `SettingSeeder`, `NotificationTemplateSeeder`
   sahaja untuk data rujukan.

---

## 15. Yang masih memerlukan kredensial pihak ketiga

| Ciri | Status | Diperlukan |
|---|---|---|
| Penghantaran WhatsApp | Kod siap, direkod ke log | `WHATSAPP_BASE_URL` + `WHATSAPP_API_KEY` |
| E-mel produksi | Kod siap, `MAIL_MAILER=log` | Kredensial SMTP/API |
| BayarCash (FPX/DuitNow) | Kod siap, jatuh balik ke manual | Token API, portal key, secret key |
| Logo rasmi BeDaie | Placeholder digunakan | Fail SVG rasmi |
| Teks privasi & terma | Draf placeholder | Semakan penasihat undang-undang |
