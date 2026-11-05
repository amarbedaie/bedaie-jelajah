@php
    use App\Enums\CertificateDesign;

    // DomPDF tidak menyokong CSS moden — gunakan gaya mudah dan kedudukan
    // mutlak dengan ukuran A4 yang tepat (pt) supaya tiada apa terkeluar.
    $design ??= CertificateDesign::Klasik;
    $isLandscape ??= ($template?->orientation ?? 'landscape') === 'landscape';

    $accent = $template?->accent_color ?: '#8040C0';
    $ink = '#17161C';
    $gold = '#B8933A';

    // A4 = 841.89 x 595.28 pt. Sedikit kurang supaya dompdf tidak menolak
    // kandungan ke muka surat kedua.
    $pageW = $isLandscape ? 841 : 594;
    $pageH = $isLandscape ? 594 : 841;

    $isModen = $design === CertificateDesign::Moden;
    $isIslamik = $design === CertificateDesign::Islamik;
    $isMinimal = $design === CertificateDesign::Minimal;

    // Ruang dalam (jarak kandungan dari tepi kertas).
    $insetX = $isModen ? ($isLandscape ? 96 : 74) : ($isLandscape ? 70 : 54);
    $insetTop = $isLandscape ? 48 : 150;
    $footerBottom = $isLandscape ? 44 : 56;

    $background = match ($design) {
        CertificateDesign::Islamik => '#FBF8F1',
        CertificateDesign::Minimal => '#FFFFFF',
        default => '#FAFAFC',
    };

    // Nama panjang dikecilkan supaya tidak pecah pada satu baris.
    $nameLength = mb_strlen($certificate->recipient_name);
    $scale = $isLandscape ? 1 : 0.82;
    $nameSize = (match (true) {
        $nameLength > 46 => 22,
        $nameLength > 34 => 27,
        $nameLength > 24 => 32,
        default => $isModen ? 42 : 38,
    }) * $scale;

    $intro = $template?->intro_text ?: 'Dengan ini disahkan bahawa';
    $closing = $template?->closing_text
        ?: 'telah menyertai program di bawah anjuran BeDaie Jelajah.';
    $align = $isModen ? 'left' : 'center';
@endphp
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->certificate_number }}</title>
    <style>
        @page { margin: 0; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: {{ $ink }};
        }

        .sheet {
            position: absolute;
            top: 0; left: 0;
            width: {{ $pageW }}pt;
            height: {{ $pageH }}pt;
            overflow: hidden;
            background: {{ $background }};
        }

        .abs { position: absolute; }

        .content {
            position: absolute;
            top: {{ $insetTop }}pt;
            left: {{ $insetX }}pt;
            right: {{ $isModen ? ($isLandscape ? 60 : 46) : $insetX }}pt;
            text-align: {{ $align }};
        }

        .brand { font-size: 11pt; letter-spacing: 3pt; color: {{ $accent }}; font-weight: bold; }
        .brand-sub { margin-top: 3pt; font-size: 7.5pt; letter-spacing: 2pt; color: #63616D; }

        /* Serif untuk tajuk dan nama — dompdf membawa DejaVu Serif. */
        .title {
            margin-top: {{ $isLandscape ? 18 : 30 }}pt;
            font-family: DejaVu Serif, serif;
            font-size: {{ $isModen ? 24 : 20 }}pt;
            letter-spacing: {{ $isMinimal ? 4 : 1 }}pt;
            color: {{ $isIslamik ? $accent : $ink }};
        }
        .subtitle { margin-top: 5pt; font-size: 9.5pt; color: #54525F; }

        .intro { margin-top: {{ $isLandscape ? 20 : 34 }}pt; font-size: 10pt; color: #54525F; }

        .name {
            margin-top: 10pt;
            font-family: DejaVu Serif, serif;
            font-size: {{ $nameSize }}pt;
            color: {{ $isModen ? $accent : $ink }};
            line-height: 1.22;
        }

        .rule {
            margin: 12pt {{ $isModen ? '0' : 'auto' }} 0;
            width: {{ $isModen ? 90 : 140 }}pt;
            height: {{ $isModen ? 3 : 1 }}pt;
            background: {{ $isIslamik ? $gold : $accent }};
        }

        .closing { margin-top: 12pt; font-size: 10pt; color: #54525F; }

        .event {
            margin-top: 8pt;
            font-size: 13.5pt;
            font-weight: bold;
            line-height: 1.35;
        }

        .meta { margin-top: 10pt; font-size: 9pt; color: #54525F; line-height: 1.6; }

        .footer {
            position: absolute;
            left: {{ $insetX }}pt;
            right: {{ $isModen ? ($isLandscape ? 60 : 46) : $insetX }}pt;
            bottom: {{ $footerBottom }}pt;
        }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { vertical-align: bottom; font-size: 8pt; color: #54525F; }

        .sign-line { border-top: 0.8pt solid #63616D; padding-top: 4pt; width: 150pt; }
        .sign-name { font-size: 9.5pt; font-weight: bold; color: {{ $ink }}; }
        .sign-title { font-size: 7.5pt; color: #63616D; }

        .serial { font-size: 7.5pt; color: #63616D; letter-spacing: 0.5pt; }
        .verify { font-size: 6.5pt; color: #63616D; margin-top: 2pt; }
    </style>
</head>
<body>
<div class="sheet">
    {{-- ── Hiasan mengikut gaya ── --}}
    @switch($design)
        @case(CertificateDesign::Moden)
            <div class="abs" style="top: 0; left: 0; width: {{ $isLandscape ? 54 : 40 }}pt; height: {{ $pageH }}pt; background: {{ $accent }};"></div>
            <div class="abs" style="top: 0; left: {{ $isLandscape ? 60 : 45 }}pt; width: 4pt; height: {{ $pageH }}pt; background: {{ $accent }}; opacity: 0.35;"></div>
            <div class="abs" style="top: 0; right: 0; width: {{ $pageW * 0.38 }}pt; height: 6pt; background: {{ $accent }}; opacity: 0.25;"></div>
            <div class="abs" style="bottom: 0; right: 0; width: {{ $pageW * 0.22 }}pt; height: 6pt; background: {{ $accent }};"></div>
            @break

        @case(CertificateDesign::Islamik)
            <div class="abs" style="top: 16pt; left: 16pt; width: {{ $pageW - 32 - 6 }}pt; height: {{ $pageH - 32 - 6 }}pt; border: 3pt solid {{ $accent }};"></div>
            <div class="abs" style="top: 25pt; left: 25pt; width: {{ $pageW - 50 - 2 }}pt; height: {{ $pageH - 50 - 2 }}pt; border: 1pt dotted {{ $gold }};"></div>
            <div class="abs" style="top: 31pt; left: 31pt; width: {{ $pageW - 62 - 1.2 }}pt; height: {{ $pageH - 62 - 1.2 }}pt; border: 0.6pt solid {{ $gold }};"></div>
            @foreach ([['top', 'left'], ['top', 'right'], ['bottom', 'left'], ['bottom', 'right']] as [$v, $h])
                {{-- Bucu berhias: petak bertingkat corak geometri --}}
                <div class="abs" style="{{ $v }}: 10pt; {{ $h }}: 10pt; width: 30pt; height: 30pt; background: {{ $accent }};"></div>
                <div class="abs" style="{{ $v }}: 15pt; {{ $h }}: 15pt; width: 18pt; height: 18pt; border: 1pt solid {{ $gold }}; background: {{ $background }};"></div>
                <div class="abs" style="{{ $v }}: 21pt; {{ $h }}: 21pt; width: 8pt; height: 8pt; background: {{ $gold }};"></div>
            @endforeach
            @foreach (['top' => 13.5, 'bottom' => 13.5] as $v => $offset)
                {{-- Permata kecil di tengah sisi atas & bawah --}}
                <div class="abs" style="{{ $v }}: {{ $offset }}pt; left: {{ $pageW / 2 - 6 }}pt; width: 8pt; height: 8pt; background: {{ $gold }}; border: 1pt solid {{ $accent }};"></div>
            @endforeach
            @break

        @case(CertificateDesign::Minimal)
            <div class="abs" style="top: {{ $insetTop - 22 }}pt; left: {{ $pageW / 2 - 24 }}pt; width: 48pt; height: 2pt; background: {{ $accent }};"></div>
            <div class="abs" style="bottom: {{ $footerBottom - 18 }}pt; left: {{ $insetX }}pt; width: {{ $pageW - 2 * $insetX }}pt; height: 0.5pt; background: #D9D7DF;"></div>
            @break

        @default
            <div class="abs" style="top: 18pt; left: 18pt; width: {{ $pageW - 36 }}pt; height: 8pt; background: {{ $accent }};"></div>
            <div class="abs" style="top: 18pt; left: 18pt; width: {{ $pageW - 36 - 3.2 }}pt; height: {{ $pageH - 36 - 3.2 }}pt; border: 1.6pt solid {{ $accent }};"></div>
            <div class="abs" style="top: 24pt; left: 24pt; width: {{ $pageW - 48 - 1.2 }}pt; height: {{ $pageH - 48 - 1.2 }}pt; border: 0.6pt solid #D0BBEB;"></div>
    @endswitch

    <div class="content">
        <div class="brand">BEDAIE</div>
        <div class="brand-sub">JELAJAH &middot; {{ mb_strtoupper(config('jelajah.motto')) }}</div>

        <div class="title">{{ mb_strtoupper($certificate->type->label()) }}</div>
        <div class="subtitle">{{ config('jelajah.tagline') }}</div>

        <div class="intro">{{ $intro }}</div>

        <div class="name">{{ $certificate->recipient_name }}</div>
        <div class="rule"></div>

        <div class="closing">{{ $closing }}</div>

        <div class="event">{{ $certificate->event_title }}</div>

        <div class="meta">
            @if ($certificate->event_date)
                {{ \Illuminate\Support\Carbon::parse($certificate->event_date)->translatedFormat('j F Y') }}
            @endif
            @if ($certificate->venue_name)
                &middot; {{ $certificate->venue_name }}
            @endif
            <br>
            @if ($certificate->speaker_name)
                Penceramah: {{ $certificate->speaker_name }}
            @endif
            @if ($certificate->learning_hours)
                &middot; {{ rtrim(rtrim(number_format((float) $certificate->learning_hours, 1), '0'), '.') }} jam pembelajaran
            @endif
            @if ($certificate->organization_name)
                <br>Dengan kerjasama {{ $certificate->organization_name }}
            @endif
        </div>
    </div>

    <div class="footer">
        <table>
            <tr>
                @if ($isModen)
                    {{-- Moden: tandatangan di kiri, QR di kanan --}}
                    <td style="width: 50%;">
                        @include('certificates.partials.signature', ['template' => $template, 'alignLeft' => true])
                        <div class="verify" style="margin-top: 8pt;">
                            Dikeluarkan {{ $certificate->issued_at?->translatedFormat('j F Y') }}
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right;">@include('certificates.partials.qr')</td>
                @else
                    <td style="width: 33%;">@include('certificates.partials.qr')</td>
                    <td style="width: 34%; text-align: center;">
                        <div class="verify">
                            Dikeluarkan {{ $certificate->issued_at?->translatedFormat('j F Y') }}
                        </div>
                    </td>
                    <td style="width: 33%; text-align: right;">
                        @include('certificates.partials.signature', ['template' => $template, 'alignLeft' => false])
                    </td>
                @endif
            </tr>
        </table>
    </div>
</div>
</body>
</html>
