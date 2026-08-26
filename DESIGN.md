---
name: BeDaie Jelajah
description: Gerakan ilmu yang membawa BeDaie ke masjid, surau, sekolah dan komuniti seluruh Malaysia
colors:
  brand-50: "#F2EFFF"
  brand-100: "#E7E2FF"
  brand-200: "#D5CCFF"
  brand-300: "#B6AAFF"
  brand-400: "#9C8CFF"
  brand-500: "#8875FF"
  brand-600: "#7561F2"
  brand-700: "#6350D1"
  brand-800: "#4C3EA3"
  brand-900: "#322A6B"
  navy-50: "#EEEEF6"
  navy-100: "#D4D3E6"
  navy-300: "#8B88B5"
  navy-500: "#3A3670"
  navy-700: "#1B1858"
  navy-900: "#0A083B"
  ink: "#1B1C1E"
  ink-soft: "#55575C"
  ink-muted: "#83868C"
  cream: "#FAF9F6"
  surface: "#FFFFFF"
  mist: "#F4F4F4"
  hairline: "#EAEAEA"
  success: "#00B96B"
  success-soft: "#E6F8F0"
  whatsapp: "#00D357"
  warning: "#F5A623"
  warning-soft: "#FEF4E4"
  danger: "#E5484D"
  danger-soft: "#FDECED"
typography:
  display:
    fontFamily: "Playfair Display, Poppins, ui-serif, Georgia, serif"
    fontSize: "clamp(1.875rem, 4.5vw, 3rem)"
    fontWeight: 400
    lineHeight: 1.1
    letterSpacing: "-0.015em"
  heading:
    fontFamily: "Poppins, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(1.125rem, 2vw, 1.5rem)"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-0.015em"
  body:
    fontFamily: "Poppins, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.65
    letterSpacing: "normal"
  eyebrow:
    fontFamily: "Poppins, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.16em"
  mono:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, monospace"
    fontSize: "0.75rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
rounded:
  lg: "0.75rem"
  xl: "1rem"
  card: "1.25rem"
  card-lg: "1.5rem"
  pill: "999px"
spacing:
  xs: "0.5rem"
  sm: "0.75rem"
  md: "1.25rem"
  lg: "1.75rem"
  xl: "2.5rem"
  section: "3.5rem"
  tap: "2.75rem"
components:
  button-primary:
    backgroundColor: "{colors.brand-500}"
    textColor: "{colors.surface}"
    typography: "{typography.body}"
    rounded: "{rounded.pill}"
    padding: "0 1.5rem"
    height: "{spacing.tap}"
  button-primary-hover:
    backgroundColor: "{colors.brand-600}"
  button-navy:
    backgroundColor: "{colors.navy-900}"
    textColor: "{colors.surface}"
    rounded: "{rounded.pill}"
    padding: "0 1.5rem"
    height: "{spacing.tap}"
  button-outline:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    padding: "0 1.5rem"
    height: "{spacing.tap}"
  button-whatsapp:
    backgroundColor: "{colors.whatsapp}"
    textColor: "{colors.surface}"
    rounded: "{rounded.pill}"
    padding: "0 1.5rem"
    height: "{spacing.tap}"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "1.5rem"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    typography: "{typography.body}"
    rounded: "{rounded.xl}"
    padding: "0.625rem 1rem"
    height: "{spacing.tap}"
  badge:
    backgroundColor: "{colors.brand-50}"
    textColor: "{colors.brand-700}"
    rounded: "{rounded.pill}"
    padding: "0.25rem 0.625rem"
---

# Design

## Overview

BeDaie Jelajah memakai **gaya korporat pendidikan Islam** — tenang, lapang dan
premium, tanpa ornamen keagamaan yang berat. Tiada kubah, tiada bulan sabit.
Kesan Islamnya datang daripada satu motif geometri girih yang sangat halus,
digunakan sebagai tekstur latar sahaja.

Dua dunia visual hidup berdampingan:

- **Navy pekat** (`navy-900`) untuk hero, poster, sijil dan skrin operasi malam
  seperti pengimbas QR. Ini wajah "acara" — berwibawa, tenang, malam masjid.
- **Krim suam** (`cream`) untuk badan halaman. Ini wajah "bacaan" — lapang,
  rendah kontras, mudah pada mata.

Ungu jenama (`brand-500`) ialah satu-satunya warna tindakan. Ia tidak pernah
digunakan sebagai hiasan besar; ia menandakan "di sini tempat anda bertindak".

## Colors

**Ungu jenama.** `brand-500` #8875FF untuk semua tindakan utama. `brand-600`
untuk hover. `brand-50` untuk latar lembut kad dan lencana. `brand-200`/`300`
untuk aksen di atas navy. Skala 800/900 jarang digunakan — simpan untuk teks
pada latar sangat cerah.

**Navy korporat.** `navy-900` #0A083B ialah warna semua tajuk dan latar hero.
`navy-300` untuk teks sekunder di atas navy.

**Neutral suam.** Latar halaman ialah `cream` #FAF9F6, bukan putih. Kad ialah
`surface` putih tulen supaya ia timbul sedikit daripada krim. `hairline`
#EAEAEA untuk semua sempadan — tidak pernah lebih gelap.

**Semantik.** `success` untuk kehadiran dan pengesahan. `whatsapp` #00D357
khusus untuk butang WhatsApp sahaja — jangan guna sebagai hijau am.
`warning` untuk menunggu tindakan. `danger` untuk pembatalan.

Setiap warna semantik ada pasangan `-soft` untuk latar amaran dan lencana.

**Kontras.** Teks badan `ink-soft` di atas `cream` mencapai AA. `ink-muted`
hanya untuk metadata kecil, tidak pernah untuk teks yang perlu dibaca penuh.

## Typography

Dua muka taip sahaja, dan itu sengaja.

**Poppins** — badan, navigasi, butang, label, borang, dashboard. Semua kerja
berat. Saiz badan tidak pernah turun bawah 16px kerana pengguna termasuk warga
emas.

**Playfair Display** — hanya untuk nombor besar dan tajuk kempen: tajuk hero,
kaunter impak, tajuk halaman. Ia memberi rasa "penerbitan" yang sesuai dengan
sebuah rumah penerbitan buku. Jangan gunakannya untuk teks berjalan.

**Eyebrow** (0.75rem, tracking 0.16em, huruf besar) menandakan setiap seksyen.
Ini ciri jenama yang konsisten merentas semua halaman.

**Mono** hanya untuk data mesin yang perlu disalin: nombor rujukan, nombor
sijil, pautan pendek.

`text-pretty` digunakan pada hampir semua perenggan; `text-balance` pada tajuk.

## Layout

Bekas `jelajah-container`: lebar penuh, max 80rem, padding 1.25rem (2rem dari
768px ke atas).

Irama seksyen: `py-12 sm:py-16` untuk seksyen biasa, `py-14 sm:py-20` untuk
seksyen naratif.

Grid dua lajur yang berulang di seluruh sistem:
`lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]` — kandungan utama di kiri,
sisi lengket (`lg:sticky lg:top-24`) di kanan. Digunakan pada halaman program,
permohonan, dan detail admin.

**Mobile-first mutlak.** Setiap skrin direka untuk 390px dahulu. Kawalan utama
mesti `tap-target` (44px minimum). Jadual lebar dibalut `overflow-x-auto`;
badan halaman tidak pernah menatal mendatar.

## Elevation & Depth

Tiga tahap sahaja, semuanya berasaskan navy bukan hitam:

- `shadow-soft` — kad rehat. Hampir tidak kelihatan, hanya memisahkan dari krim.
- `shadow-lift` — kad tindakan penting (kad pendaftaran, tiket, poster).
- `shadow-brand` — hanya butang utama ungu, memberi cahaya ungu lembut.

Di atas navy, kedalaman datang daripada `bg-white/8` dengan `ring-1 ring-white/12`,
bukan bayang. Tambah `blur-3xl` bulatan ungu untuk kedalaman atmosfera pada hero.

## Shapes

Radius kad 20–24px (`card`, `card-lg`) mengikut garis panduan jenama.
Butang sentiasa pil penuh (`pill`). Input dan kawalan kecil 16px (`xl`).
Ikon dalam petak bulat 12px (`lg`).

Motif `motif-girih` (ungu, di atas cerah) dan `motif-girih-dark` (putih, di atas
navy) — jubin **khatam 8-mata** 96px pada opacity 7–16%. Bintang terbentuk
daripada dua segi empat bertindih: geometri Islam tulen yang berjubin sempurna.
Ini satu-satunya ornamen Islam yang dibenarkan. Ia tekstur, bukan gambar.

Poster dan imej kad program dijana dengan **rosette girih 10-mata** sebagai
subjek visual, bersama siluet arked masjid tanpa kubah pada kaki imej.

## Components

Semua komponen hidup dalam `resources/views/components/`:

- `ui/` — primitif: button, input, select, textarea, field, card, badge, alert,
  progress, icon, choice, copy-button, empty-state, section-heading.
- `jelajah/` — khusus domain: map, event-card, event-filters, page-hero,
  admin-table.
- `layouts/` — public, app, admin, auth, bare.
- `brand/` — logo.

`ui/icon` membawa satu set ikon garis 24px, stroke 1.7. Prop `fill` membenarkan
ikon terisi apabila makna memerlukannya (bintang penilaian).

`ui/field` ialah pembungkus label + hint + ralat yang standard. Setiap input
dalam sistem mesti melaluinya supaya label, `for`, hint dan ralat sentiasa
konsisten dan terpaut dengan betul.

## Do's and Don'ts

**Do**

- Guna ungu hanya untuk tindakan. Satu tindakan utama setiap skrin.
- Mulakan setiap seksyen dengan eyebrow + tajuk.
- Beri setiap keadaan kosong satu jalan keluar (butang, bukan sekadar teks).
- Utamakan WhatsApp pada setiap skrin Penggerak.
- Samarkan nombor telefon di mana ia tidak diperlukan.
- Tulis semua salinan UI dalam Bahasa Melayu yang mesra dan tidak menghukum.

**Don't**

- Jangan tambah muka taip ketiga.
- Jangan guna kubah, bulan sabit, kaligrafi hiasan atau lampu tanglung.
- Jangan letak arahan Blade (`@js`, `@disabled`, `@class`) di dalam tag
  komponen `<x-...>` — ia tidak dikompil dan menghasilkan atribut rosak.
- Jangan guna hijau WhatsApp untuk apa-apa selain butang WhatsApp.
- Jangan letak teks penting pada `ink-muted`.
- Jangan bina graf dalam dashboard Penggerak — mereka bukan penganalisis.
- Jangan biarkan Penggerak menyunting reka bentuk program; templat rasmi
  ialah keseluruhan maksud sistem ini.
- Jangan turunkan saiz teks badan bawah 16px, di mana-mana.
