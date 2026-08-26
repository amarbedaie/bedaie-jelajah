@php
    // DomPDF tidak menyokong CSS moden — gunakan gaya inline yang mudah.
    $accent = $template?->accent_color ?: '#8875FF';
    $navy = '#0A083B';
    $isLandscape = ($template?->orientation ?? 'landscape') === 'landscape';

    // Nama panjang dikecilkan supaya tidak pecah pada satu baris.
    $nameLength = mb_strlen($certificate->recipient_name);
    $nameSize = match (true) {
        $nameLength > 46 => '22pt',
        $nameLength > 34 => '27pt',
        $nameLength > 24 => '32pt',
        default => '38pt',
    };

    $intro = $template?->intro_text ?: 'Dengan ini disahkan bahawa';
    $closing = $template?->closing_text
        ?: 'telah menyertai program di bawah anjuran BeDaie Jelajah.';
@endphp
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->certificate_number }}</title>
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: {{ $navy }};
            background: #FFFFFF;
        }

        .sheet {
            position: relative;
            width: 100%;
            height: {{ $isLandscape ? '540pt' : '780pt' }};
            padding: {{ $isLandscape ? '40pt 52pt' : '52pt 46pt' }};
            box-sizing: border-box;
        }

        /* Bingkai berjenama */
        .frame {
            position: absolute;
            top: 18pt; right: 18pt; bottom: 18pt; left: 18pt;
            border: 1.6pt solid {{ $accent }};
        }
        .frame-inner {
            position: absolute;
            top: 24pt; right: 24pt; bottom: 24pt; left: 24pt;
            border: 0.6pt solid #D5CCFF;
        }

        .band {
            position: absolute;
            top: 18pt; left: 18pt; right: 18pt;
            height: 8pt;
            background: {{ $accent }};
        }

        .content { position: relative; text-align: center; }

        .brand { font-size: 11pt; letter-spacing: 3pt; color: {{ $accent }}; font-weight: bold; }
        .brand-sub { margin-top: 3pt; font-size: 7.5pt; letter-spacing: 2pt; color: #83868C; }

        .title {
            margin-top: {{ $isLandscape ? '18pt' : '26pt' }};
            font-size: 19pt;
            letter-spacing: 1.5pt;
            font-weight: bold;
        }
        .subtitle { margin-top: 5pt; font-size: 9.5pt; color: #55575C; }

        .intro { margin-top: {{ $isLandscape ? '20pt' : '30pt' }}; font-size: 10pt; color: #55575C; }

        .name {
            margin-top: 8pt;
            font-size: {{ $nameSize }};
            font-weight: bold;
            color: {{ $navy }};
            line-height: 1.25;
            padding: 0 20pt;
        }

        .rule { margin: 12pt auto 0; width: 140pt; height: 1pt; background: {{ $accent }}; }

        .closing { margin-top: 12pt; font-size: 10pt; color: #55575C; }

        .event {
            margin-top: 8pt;
            font-size: 13.5pt;
            font-weight: bold;
            line-height: 1.35;
            padding: 0 30pt;
        }

        .meta { margin-top: 10pt; font-size: 9pt; color: #55575C; line-height: 1.6; }

        .footer {
            position: absolute;
            left: {{ $isLandscape ? '52pt' : '46pt' }};
            right: {{ $isLandscape ? '52pt' : '46pt' }};
            bottom: {{ $isLandscape ? '44pt' : '56pt' }};
        }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { vertical-align: bottom; font-size: 8pt; color: #55575C; }

        .sign-line { border-top: 0.8pt solid #83868C; padding-top: 4pt; width: 150pt; }
        .sign-name { font-size: 9.5pt; font-weight: bold; color: {{ $navy }}; }
        .sign-title { font-size: 7.5pt; color: #83868C; }

        .serial { font-size: 7.5pt; color: #83868C; letter-spacing: 0.5pt; }
        .verify { font-size: 6.5pt; color: #A0A2A8; margin-top: 2pt; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="band"></div>
    <div class="frame"></div>
    <div class="frame-inner"></div>

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
                <td style="width: 33%;">
                    @if ($qrDataUri)
                        <img src="{{ $qrDataUri }}" alt="" style="width: 62pt; height: 62pt;">
                    @endif
                    <div class="serial">{{ $certificate->certificate_number }}</div>
                    <div class="verify">Sahkan di {{ parse_url(config('app.url'), PHP_URL_HOST) }}/sijil/semak</div>
                </td>

                <td style="width: 34%; text-align: center;">
                    <div class="verify">
                        Dikeluarkan {{ $certificate->issued_at?->translatedFormat('j F Y') }}
                    </div>
                </td>

                <td style="width: 33%; text-align: right;">
                    <table style="width: auto; margin-left: auto;">
                        <tr><td style="text-align: center; padding-bottom: 2pt;">
                            @if ($template?->signature_path && Storage::disk('public')->exists($template->signature_path))
                                <img src="{{ Storage::disk('public')->path($template->signature_path) }}"
                                     alt="" style="height: 34pt;">
                            @else
                                <div style="height: 34pt;"></div>
                            @endif
                        </td></tr>
                        <tr><td class="sign-line" style="text-align: center;">
                            <div class="sign-name">{{ $template?->signature_name ?? 'Pasukan BeDaie' }}</div>
                            <div class="sign-title">{{ $template?->signature_title ?? config('jelajah.org') }}</div>
                        </td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
