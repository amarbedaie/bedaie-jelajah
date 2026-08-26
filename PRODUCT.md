# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Tiga peranan, ketiga-tiganya sama penting untuk kejayaan sistem (disahkan pengguna).
Tiada satu peranan yang boleh dikorbankan untuk peranan lain.

**Staf BeDaie (Super Admin).** Pasukan Dakwah Digital Network. Mereka menilai
permohonan masuk, menentukan penceramah dan tarikh, dan — mulai kini — juga
memburu lokasi sasaran sendiri. Kerja mereka bercampur antara pejabat dan
telefon bimbit di lapangan.

**Penggerak Jelajah.** Individu yang membawa BeDaie ke kawasannya: bekas pelajar
BeDaie, ahli kariah, wakil masjid/surau, guru, wakil tahfiz atau persatuan, atau
orang awam. **Bukan penganjur acara profesional.** Ramai menguruskan program
pertama dalam hidup mereka, selalunya melalui telefon, di antara kerja lain.
Andaian reka bentuk yang mengikat: jika sesuatu skrin memerlukan penjelasan,
skrin itu gagal.

**Peserta.** Orang awam yang menghadiri program. Mendaftar melalui pautan
WhatsApp, hadir di masjid/dewan, mengimbas QR di pintu masuk, dan kemudian
mahukan sijil. Julat umur luas — termasuk warga emas.

## Product Purpose

BeDaie Jelajah membawa pengajian BeDaie keluar dari skrin, terus ke masjid,
surau, sekolah, tahfiz dan dewan komuniti di seluruh Malaysia.

Sistem ini menyatukan satu aliran penuh yang sebelum ini bertaburan merentas
WhatsApp, borang dan hamparan:

```
MOHON → BINCANG → DISAHKAN → HALAMAN PROGRAM DIJANA → PENGGERAK SEBARKAN
      → PESERTA MENDAFTAR → QR KEHADIRAN → SIJIL AUTOMATIK
```

Serta aliran kedua yang berlawanan arah — staf memburu lokasi sendiri:

```
SASARAN → CARI KONTAK → HUBUNGI → BERBINCANG → SETUJU → PERMOHONAN → JELAJAH
```

Kejayaan bermakna: seorang ahli kariah yang tidak pernah menganjurkan apa-apa
boleh membawa BeDaie ke suraunya, dan pasukan BeDaie tahu dengan tepat lokasi
mana sedang dikejar oleh siapa.

## Positioning

**Bukan marketplace penceramah.** Pemohon tidak memilih penceramah daripada
katalog. Setiap permohonan dinilai oleh pasukan BeDaie berdasarkan keperluan
sebenar komuniti, kesesuaian lokasi dan keutamaan jelajah. BeDaie yang
menentukan penceramah dan pengisian.

**Bukan event page builder.** Penggerak tidak memilih templat, tidak menyusun
layout, tidak membina halaman. Apabila program disahkan, sistem menjana halaman
rasmi, pautan pendek, QR, poster dan dashboard secara automatik daripada satu
templat BeDaie. Kekonsistenan jenama tidak boleh dirundingkan.

Mekanisme yang tidak boleh ditiru dengan jujur oleh produk jiran: **satu
permohonan daripada orang awam menjadi microsite lengkap dengan pendaftaran,
kehadiran QR, laporan impak dan sijil digital — tanpa penganjur menyentuh
satu pun tetapan.**

## Operating Context

- **WhatsApp ialah saluran utama**, bukan e-mel. Setiap skrin Penggerak
  mengutamakan perkongsian WhatsApp. Nombor telefon lebih dipercayai daripada
  alamat e-mel.
- **Mobile-first secara mutlak.** Penggerak berkongsi pautan dari telefon.
  Staf mengimbas QR di pintu masuk masjid, selalunya waktu malam selepas Maghrib,
  kadangkala dengan isyarat lemah.
- **Bahasa Melayu** ialah bahasa utama sistem, bukan terjemahan.
- **Ritual sebenar:** program lazimnya selepas Maghrib atau Isyak pada hari
  bekerja, atau pagi Sabtu/Ahad. Peserta datang sekeluarga — pendaftaran ahli
  keluarga adalah keperluan, bukan tambahan.
- **Sejarah tidak lengkap** (disahkan pengguna): beberapa program sudah berjalan
  sebelum sistem ini, tetapi rekodnya tidak tersusun. Sistem mesti berfungsi
  dengan data lampau yang berlubang, dan statistik mesti berguna walaupun
  bermula rendah.

## Capabilities

- Permohonan awam empat langkah, dengan akaun Penggerak dicipta automatik.
- Penjanaan EventSpace automatik: halaman program, slug, pautan pendek `/j/KOD`,
  QR pendaftaran, poster, dashboard Penggerak.
- Pipeline sasaran keluar untuk staf, termasuk sumber sasaran (staf terus,
  rakan, penggerak, rujukan) supaya sumbangan rakan boleh diukur.
- Pendaftaran peserta dengan kapasiti, senarai menunggu dan ahli keluarga.
- Kehadiran QR dengan sandaran carian manual dan pendaftaran walk-in.
- Sijil digital automatik dengan pengesahan awam melalui nombor siri.
- Peta jelajah Malaysia yang menunjukkan negeri dijelajahi, akan datang dan
  permintaan komuniti.
- Pasport Ilmu: rekod pembelajaran peserta merentas program.

## Constraints

- **Jenama BeDaie ialah "BeDaie"** — bukan Bidai, bukan Bedie. Penerbit ialah
  Dakwah Digital Network. Motto: *1 Rumah, 1 Daie*.
- **Logo rasmi belum dibekalkan.** Placeholder digunakan dan mesti diganti;
  logo tidak boleh dicipta semula atau di-hotlink daripada laman lama.
- **Domain belum diputuskan** (disahkan pengguna). Jangan tanam andaian domain
  dalam kod atau salinan.
- Kredensial WhatsApp, e-mel produksi dan gateway pembayaran belum tersedia;
  setiap satu mesti jatuh balik dengan selamat tanpa memecahkan aliran.
- Teks privasi dan terma masih placeholder dan memerlukan semakan undang-undang.
- Peranan dihadkan kepada tiga pada MVP: Super Admin, Penggerak, Peserta.

## Accessibility

- Sasaran ketuk sekurang-kurangnya 44px — pengguna termasuk warga emas.
- Peta SVG mesti mempunyai alternatif senarai dan setiap negeri terdedah sebagai
  kawalan berlabel kepada pembaca skrin.
- Mesej ralat dalam Bahasa Melayu yang jelas, bukan jargon teknikal.
- Butang tidak boleh bergantung kepada ikon sahaja.
- Nombor telefon peserta disamarkan pada paparan yang tidak memerlukannya.

## Voice

Mesra, tenang, tidak menghukum. Bahasa dakwah yang merendah diri — bukan bahasa
korporat, bukan bahasa jualan. Tagline: *Membawa Ilmu, Menghidupkan Ummah.*
Slogan sokongan: *Dari Masjid ke Masjid, Dari Hati ke Hati.*

## Open Decisions

- Domain dan hos akhir.
- Sama ada sistem ini akhirnya menggantikan bedaie.com.my atau hidup bersamanya.
- Provider WhatsApp dan gateway pembayaran yang akan digunakan.
