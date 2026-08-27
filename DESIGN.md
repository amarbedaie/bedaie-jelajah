---
name: BeDaie Jelajah
description: Gerakan ilmu yang membawa BeDaie ke masjid, surau, sekolah dan komuniti seluruh Malaysia
colors:
  cream: "#F0EEE6"
  surface: "#FFFFFF"
  raised: "#FAF9F5"
  mist: "#E8E5DB"
  hairline: "#E3E1D9"
  control-line: "#858175"
  ink: "#141413"
  ink-soft: "#57564F"
  ink-muted: "#67665F"
  clay-50: "#FBF0EB"
  clay-100: "#F6E0D6"
  clay-200: "#EDC4B2"
  clay-300: "#E3A488"
  clay-400: "#D97757"
  clay-500: "#C96A48"
  clay-600: "#B4552F"
  clay-700: "#9E4726"
  clay-800: "#7E3720"
  clay-900: "#4A2115"
  char-900: "#141413"
  char-800: "#262622"
  char-700: "#33322D"
  char-400: "#8A887F"
  char-200: "#D6D3C9"
  char-100: "#E8E5DB"
  alert: "#8E2438"
  alert-soft: "#F4E4E7"
  alert-line: "#DFC0C7"
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
    backgroundColor: "{colors.clay-600}"
    textColor: "{colors.surface}"
    typography: "{typography.body}"
    rounded: "0.5rem"
    padding: "0 1.125rem"
    height: "{spacing.tap}"
  button-primary-hover:
    backgroundColor: "{colors.clay-700}"
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
    backgroundColor: "{colors.clay-50}"
    textColor: "{colors.clay-700}"
    rounded: "{rounded.pill}"
    padding: "0.25rem 0.625rem"
---

# Design

## Overview

BeDaie Jelajah ialah **kertas**. Sebuah rumah penerbitan yang membawa kitab ke
masjid patut kelihatan seperti helaian bercetak, bukan seperti papan pemuka.

Seluruh sistem berdiri di atas satu tanah: kertas tulang `cream` #F0EEE6.
Dakwat hitam suam `ink` #141413. Satu warna tindakan — tanah liat — yang hanya
muncul di tempat orang benar-benar bertindak.

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

**Tanah liat.** Satu keluarga, satu tangga penuh 50→900, tiga peranan:

- `clay-400` #D97757 — aksen, tekstur girih, garis. Ia **tidak** boleh
  membawa teks putih; ia hanya mencapai 3.12:1.
- `clay-600` #B4552F — setiap permukaan berisi yang membawa teks putih.
  4.91:1. Ini warna butang utama.
- `clay-700` #9E4726 — hover, dan teks tanah liat di atas kertas. 5.34:1.

**Satu isyarat, dan hanya satu.** `alert` #8E2438 ialah rona keempat dan
satu-satunya. Ia dikhaskan untuk pembatalan, kegagalan dan tindakan
memusnah — tidak pernah untuk hiasan, tidak pernah untuk amaran lembut.
Hue-nya 349° berbanding 17° tanah liat, dipilih dengan sengaja supaya ia
tidak pernah dibaca sebagai aksen jenama.

**Tiada hijau, tiada oren amaran, tiada biru.** Kejayaan tidak berwarna
hijau di sini; ia berwarna dakwat — pejal, selesai, dimeterai. Butang
WhatsApp memakai tanah liat dengan ikon WhatsApp; ikon itu yang
mengenalkannya, dan hijau #00D357 ialah satu-satunya benda paling kuat yang
boleh diletakkan pada halaman kertas.

### Status dibawa oleh bentuk, bukan rona

Ini yang membolehkan sistem ini kekal tiga warna tanpa kehilangan maklumat.
Lencana mempunyai **tujuh bentuk**, dibina daripada isian, cincin dan warna
teks:

| Bentuk | Isian | Cincin | Teks | Maksud |
|---|---|---|---|---|
| `quiet` | mist | tiada | ink-muted | tidak aktif, tidur |
| `line` | tiada | control-line | ink | sedang diproses |
| `edge` | tiada | clay-400 | clay-700 | bergerak, aktif |
| `soft` | clay-50 | clay-200 | clay-700 | baharu, diterima |
| `strong` | clay-600 | — | putih | tindakan diperlukan |
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
garis rambut tanah liat sepanjang 2rem. Garis itu ciri jenama — bukan setiap
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
`clay-300` dan tidak mengangkatnya.

Dua bayang sahaja, kedua-duanya membawa offset dan kabur, kedua-duanya
berasaskan `ink` bukan hitam tulen:

- `shadow-soft` — lencana di atas imej, kad hero. Hampir tidak kelihatan.
- `shadow-lift` — hanya untuk lapisan sebenar: modal, popover.

## Shapes

Radius kecil: kad 12px, kad besar 16px, kawalan 12px, butang 8px. Kertas
mempunyai tepi, bukan sudut gula-gula. Pil (`rounded-full`) hanya untuk
lencana, tidak pernah untuk butang.

Motif `motif-girih` — jubin **khatam 8-mata** 96px dalam tanah liat pada
opacity 30–50%. Ini satu-satunya ornamen Islam yang dibenarkan. Ia tekstur
latar, bukan gambar. `motif-girih-dark` (putih) dikekalkan untuk dua
permukaan arang sahaja.

Peta jelajah mewarnakan negeri dengan **kedalaman tanah liat**, bukan rona
berbeza: sedang berlangsung paling pekat, telah dijelajahi sederhana, akan
datang pucat, dan **belum dijelajahi dibiarkan kosong sebagai kertas** — itu
maknanya secara harfiah, dan ia melepaskan hujung pucat supaya tiga keadaan
lain boleh terpisah dengan jelas.

Poster dan imej kad program dijana pada kertas tulang dengan **rosette girih
10-mata** tanah liat sebagai subjek visual, dan siluet arked masjid tanpa
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

- Guna tanah liat hanya untuk tindakan. Satu tindakan utama setiap skrin.
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
- Jangan letak teks putih di atas `clay-400` — guna `clay-600`.
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
