---
name: BeDaie Jelajah
description: Gerakan ilmu yang membawa BeDaie ke masjid, surau, sekolah dan komuniti seluruh Malaysia
colors:
  cream: "#F4F3F7"
  surface: "#FFFFFF"
  raised: "#FAFAFC"
  mist: "#EBE9F1"
  hairline: "#E4E2EC"
  control-line: "#807D8C"
  ink: "#17161C"
  ink-soft: "#54525F"
  ink-muted: "#63616D"
  brand-50: "#F3EDFA"
  brand-100: "#E7DBF5"
  brand-200: "#D0BBEB"
  brand-300: "#B394DE"
  brand-400: "#9A6FD1"
  brand-500: "#8040C0"
  brand-600: "#7A3BB8"
  brand-700: "#6F35AA"
  brand-800: "#5F2B93"
  brand-900: "#3D1C5E"
  char-900: "#17161C"
  char-800: "#262430"
  char-700: "#34313F"
  char-400: "#8B8899"
  char-200: "#D5D2DE"
  char-100: "#EBE9F1"
  alert: "#A32017"
  teal: "#1B6E6E"
  teal-soft: "#E3F1F1"
  teal-line: "#BCDEDE"
  alert-soft: "#F8E6E3"
  alert-line: "#E8C4BE"
typography:
  display:
    fontFamily: "Source Serif 4, ui-serif, Georgia, serif"
    fontSize: "clamp(2.25rem, 5vw, 4.25rem)"
    fontWeight: 400
    lineHeight: 1.04
    letterSpacing: "-0.02em"
  heading:
    fontFamily: "Source Serif 4, ui-serif, Georgia, serif"
    fontSize: "clamp(1.25rem, 2.5vw, 1.75rem)"
    fontWeight: 400
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Hanken Grotesk, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.65
    letterSpacing: "normal"
  eyebrow:
    fontFamily: "Hanken Grotesk, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.18em"
  print:
    fontFamily: "DejaVu Serif, DejaVu Sans, serif"
    fontSize: "20pt"
    fontWeight: 400
    lineHeight: 1.22
    letterSpacing: "normal"
  email:
    fontFamily: "Roboto, Helvetica, Arial, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.6
    letterSpacing: "normal"
  mono:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, monospace"
    fontSize: "0.75rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
rounded:
  focus: "0.375rem"
  card: "0.75rem"
  card-lg: "1rem"
  pill: "999px"
spacing:
  xs: "0.5rem"
  sm: "0.75rem"
  md: "1.25rem"
  lg: "1.75rem"
  xl: "2.5rem"
  section: "4rem"
  tap: "2.75rem"
components:
  button-primary:
    backgroundColor: "{colors.brand-500}"
    textColor: "{colors.surface}"
    typography: "{typography.body}"
    rounded: "0.5rem"
    padding: "0 1.125rem"
    height: "{spacing.tap}"
  button-primary-hover:
    backgroundColor: "{colors.brand-600}"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "0.5rem"
    padding: "0 1.125rem"
    height: "{spacing.tap}"
  button-alert:
    backgroundColor: "{colors.alert}"
    textColor: "{colors.surface}"
    rounded: "0.5rem"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "1.5rem"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    typography: "{typography.body}"
    rounded: "0.75rem"
    padding: "0.625rem 1rem"
    height: "{spacing.tap}"
  badge:
    backgroundColor: "{colors.brand-50}"
    textColor: "{colors.brand-600}"
    rounded: "{rounded.pill}"
    padding: "0.25rem 0.625rem"
---

# Design

## Overview

BeDaie Jelajah ialah **kertas**. Sebuah rumah penerbitan yang membawa kitab ke
masjid patut kelihatan seperti helaian bercetak, bukan seperti papan pemuka.

Seluruh sistem berdiri di atas satu tanah: kertas sejuk `cream` #F4F3F7 yang
condong sedikit ungu. Dakwat `ink` #17161C. Satu warna tindakan — **ungu
jenama, diambil terus daripada logo BeDaie** — yang hanya muncul di tempat
orang benar-benar bertindak.

Sistem ini **tidak mempunyai slab gelap**. Hero, panel jenama, kaki halaman,
poster: semuanya kertas. Hierarki datang daripada saiz taip dan ruang, bukan
daripada blok warna. Satu-satunya pengecualian ialah bar sisi admin dan skrin
pengimbas QR — dua permukaan operasi di mana arang membantu tumpuan.

Kedalaman datang daripada **sempadan rambut dan ruang**, bukan bayang. Bayang
wujud tetapi hampir tidak kelihatan; ia memisahkan, ia tidak mengangkat.

## Colors

**Tiga keluarga. Tidak lebih.** Itu peraturan yang paling ketat dalam sistem
ini, dan ia yang menjadikannya kelihatan premium.

**Kertas.** `cream` #F0EEE6 ialah tanah halaman. `surface` putih tulen untuk
kad — ia timbul daripada tulang tanpa perlu bayang. `mist` untuk telaga:
kepala jadual, blok nota, lencana senyap. `hairline` untuk setiap pembahagi.
`control-line` untuk sempadan medan borang.

**Dakwat.** `ink` #141413 untuk tajuk dan teks utama. `ink-soft` untuk
perenggan sekunder. `ink-muted` untuk metadata — ia mencapai AA di atas
ketiga-tiga latar cerah, termasuk `mist`.

**Ungu jenama.** Diambil terus daripada logo. Tanda BeDaie ialah gradien
lima warna — teal, ungu, indigo, merah jambu, amber — tetapi antara muka
premium tidak boleh memakai lima rona tepu. Jadi hanya **hue 270**, badan
huruf "B" dan bahagian terbesar tanda itu, menjadi warna sistem.

Tidak seperti kebanyakan ungu jenama, nada logo itu sendiri sudah mencapai
**6.15:1** dengan teks putih — jadi tiada nada "pengganti" diperlukan:

- `brand-500` #8040C0 — ungu logo. Butang utama, gelang fokus, aksen.
- `brand-600` #7A3BB8 — hover isian.
- `brand-700` #6F35AA — teks jenama di atas kertas. 6.87:1.

**Gradien penuh muncul HANYA dalam logo itu sendiri.** Itu satu-satunya
tempat lima warna dibenarkan bertemu; di mana-mana lain, satu ungu.

**Teal sokongan.** `teal` #1B6E6E ialah warna kedua terbesar dalam logo
(hue 180, kira-kira 30% kawasan tanda). Digunakan berhemat sebagai aksen —
ia bukan warna tindakan kedua.

**Satu isyarat, dan hanya satu.** `alert` #A32017 dikhaskan untuk
pembatalan, kegagalan dan tindakan memusnah — tidak pernah untuk hiasan.
Hue 4° dipilih dengan sengaja: 266° daripada ungu jenama, dan 28° daripada
merah jambu logo, supaya ia tidak pernah dibaca sebagai warna jenama.

**Tiada hijau kejayaan, tiada oren amaran.** Kejayaan tidak berwarna hijau
di sini; ia berwarna dakwat — pejal, selesai, dimeterai. Butang WhatsApp
memakai ungu jenama dengan ikon WhatsApp; ikon itu yang mengenalkannya.

### Status dibawa oleh bentuk, bukan rona

Ini yang membolehkan sistem ini kekal tiga warna tanpa kehilangan maklumat.
Lencana mempunyai **tujuh bentuk**, dibina daripada isian, cincin dan warna
teks:

| Bentuk | Isian | Cincin | Teks | Maksud |
|---|---|---|---|---|
| `quiet` | mist | tiada | ink-muted | tidak aktif, tidur |
| `line` | tiada | control-line | ink | sedang diproses |
| `edge` | tiada | brand-400 | brand-700 | bergerak, aktif |
| `soft` | brand-50 | brand-200 | brand-700 | baharu, diterima |
| `strong` | brand-600 | — | putih | tindakan diperlukan |
| `solid` | ink | — | cream | selesai, dimeterai |
| `alert` | alert-soft | alert-line | alert | dibatalkan, gagal |
| `paper` | surface | hairline | ink | lencana di atas imej |

Dua status boleh berkongsi satu bentuk apabila ia benar-benar satu kelas
tindakan yang sama — "Perlu Maklumat" dan "Cadangan Tarikh" kedua-duanya
bermaksud bola di pihak pemohon. Itu pengelompokan yang disengajakan, bukan
kekurangan warna.

**Kontras diukur, bukan diagak.** 20 pasangan diperiksa; semuanya lulus
4.5:1 untuk teks dan 3:1 untuk sempadan kawalan dan penunjuk fokus.

## Typography

Dua muka taip.

**Source Serif 4** — setiap tajuk, nombor besar, dan tajuk kad program.
Serif transitional berkontras sederhana: ia membawa rasa penerbitan tanpa
menyusahkan warga emas membaca pada telefon. Berat 400 untuk tajuk besar;
biarkan saiz membawa penekanan, bukan berat.

**Hanken Grotesk** — badan, navigasi, butang, label, borang, dashboard.
Tinggi-x besar dan bentuk huruf yang jelas. Saiz badan tidak pernah turun
bawah 16px.

**Di luar pelayar, dua substitusi yang disengajakan.** Sijil PDF memakai
**DejaVu Serif/Sans** — dompdf membawanya sendiri, dan ia padanan terdekat
kepada Source Serif 4 tanpa membenamkan fail fon. E-mel memakai **Roboto**
dengan fallback Helvetica/Arial, kerana klien e-mel tidak memuatkan Google
Fonts. Kedua-duanya sengaja; jangan cuba memaksa muka taip web ke sana.

Tajuk serif memakai tracking -0.02em; sans memakai -0.011em. Ukuran perenggan
dihadkan 68ch. `text-balance` pada tajuk, `text-pretty` pada perenggan.

**Eyebrow** ialah label huruf besar 0.75rem dengan tracking 0.18em, didahului
garis rambut ungu jenama sepanjang 2rem. Garis itu ciri jenama — bukan setiap
seksyen memerlukannya, tetapi apabila ada, bentuknya sentiasa sama.

## Layout

Bekas `jelajah-container`: max 80rem, padding 1.25rem (2rem dari 768px).

Irama seksyen: `py-16 sm:py-20` biasa, `py-16 sm:py-24` untuk seksyen naratif.
Ruang di atas tajuk sentiasa lebih besar daripada ruang di bawahnya.

Grid dua lajur yang berulang:
`lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)]` — kandungan di kiri, sisi di
kanan. Setiap item grid mesti `min-w-0`; tanpa itu ia tidak boleh mengecut
bawah lebar min-content dan halaman menatal mendatar pada telefon.

**Mobile-first mutlak.** Setiap skrin direka untuk 390px dahulu. Kawalan utama
`tap-target` 44px. Jadual lebar dibalut `overflow-x-auto`; badan halaman tidak
pernah menatal mendatar.

## Elevation & Depth

Kedalaman utama ialah **sempadan**. `border-hairline` memisahkan hampir
segala-galanya. Kad tidak melayang; hover menggelapkan sempadannya kepada
`brand-300` dan tidak mengangkatnya.

Dua bayang sahaja, kedua-duanya membawa offset dan kabur, kedua-duanya
berasaskan `ink` bukan hitam tulen:

- `shadow-soft` — lencana di atas imej, kad hero. Hampir tidak kelihatan.
- `shadow-lift` — hanya untuk lapisan sebenar: modal, popover.

## Shapes

Radius kecil: kad 12px, kad besar 16px, kawalan 12px, butang 8px. Kertas
mempunyai tepi, bukan sudut gula-gula. Pil (`rounded-full`) hanya untuk
lencana, tidak pernah untuk butang.

Motif `motif-girih` — jubin **khatam 8-mata** 96px dalam ungu jenama pada
opacity 30–50%, dalam ungu jenama. Ini satu-satunya ornamen Islam yang dibenarkan. Ia tekstur
latar, bukan gambar. `motif-girih-dark` (putih) dikekalkan untuk dua
permukaan arang sahaja.

Peta jelajah mewarnakan negeri dengan **kedalaman ungu**, bukan rona
berbeza: sedang berlangsung paling pekat, telah dijelajahi sederhana, akan
datang pucat, dan **belum dijelajahi dibiarkan kosong sebagai kertas** — itu
maknanya secara harfiah, dan ia melepaskan hujung pucat supaya tiga keadaan
lain boleh terpisah dengan jelas.

Poster dan imej kad program dijana pada kertas sejuk dengan **rosette girih
10-mata** ungu sebagai subjek visual, dan siluet arked masjid tanpa
kubah pada kakinya.

## Components

Semua komponen dalam `resources/views/components/`:

- `ui/` — primitif: button, input, select, textarea, field, card, badge, alert,
  progress, icon, choice, copy-button, empty-state, section-heading, stat, modal.
- `jelajah/` — khusus domain: map, event-card, event-filters, page-hero,
  admin-table.
- `layouts/` — public, app, admin, auth, bare.
- `brand/` — logo.

`ui/field` ialah pembungkus label + hint + ralat. Setiap input mesti melaluinya:
ia yang memberi `id` kepada mesej ralat, dan input memancarkan `aria-invalid`
serta `aria-describedby` yang sepadan.

## Do's and Don'ts

**Do**

- Guna ungu jenama hanya untuk tindakan. Satu tindakan utama setiap skrin.
- Biarkan saiz taip membawa hierarki, bukan blok warna.
- Beri setiap keadaan kosong satu jalan keluar.
- Utamakan WhatsApp pada setiap skrin Penggerak.
- Samarkan nombor telefon di mana ia tidak diperlukan.
- Tulis semua salinan UI dalam Bahasa Melayu yang mesra dan tidak menghukum.

**Don't**

- Jangan tambah muka taip ketiga.
- Jangan bina slab gelap. Sistem ini kertas; arang hanya untuk bar sisi admin
  dan skrin pengimbas.
- **Jangan tambah rona kelima.** Tiga keluarga dan satu isyarat; jika satu
  keadaan baharu perlu dibezakan, tambah satu *bentuk* lencana, bukan satu
  warna.
- Jangan guna `alert` untuk apa-apa selain pembatalan, kegagalan dan
  tindakan memusnah.
- Jangan guna gradien lima warna logo di mana-mana selain logo itu sendiri.
- Jangan jadikan teal warna tindakan kedua; ia aksen.
- Jangan guna kubah, bulan sabit, kaligrafi hiasan atau lampu tanglung.
- Jangan letak arahan Blade (`@js`, `@disabled`, `@class`) di dalam tag
  komponen `<x-...>`, dan jangan letak `@class` pada elemen yang sudah
  mempunyai atribut `class` — ia menghasilkan dua atribut yang bercanggah.
- Jangan tulis nilai arbitrari `rounded-[--radius-*]`; token itu sudah
  menjana utilitinya sendiri, dan sintaks v3 dibuang pelayar secara senyap.
- Jangan buang `focus:outline-none` tanpa penunjuk fokus gantian 3:1.
- Jangan letak teks penting pada `ink-muted`.
- Jangan angkat kad pada hover; gelapkan sempadannya.
- Jangan bina graf dalam dashboard Penggerak — mereka bukan penganalisis.
- Jangan biarkan Penggerak menyunting reka bentuk program; templat rasmi
  ialah keseluruhan maksud sistem ini.
- Jangan turunkan saiz teks badan bawah 16px, di mana-mana.
