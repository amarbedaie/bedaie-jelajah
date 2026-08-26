<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine ?? 'BeDaie Jelajah' }}</title>
</head>
<body style="margin:0;padding:0;background:#FAF9F6;font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1B1C1E;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF9F6;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="max-width:560px;background:#FFFFFF;border:1px solid #EAEAEA;border-radius:20px;overflow:hidden;">
                    {{-- Kepala berjenama --}}
                    <tr>
                        <td style="background:#0A083B;padding:28px 32px;">
                            <p style="margin:0;font-size:19px;font-weight:600;color:#FFFFFF;letter-spacing:-0.2px;">
                                BeDaie <span style="color:#B6AAFF;">Jelajah</span>
                            </p>
                            <p style="margin:6px 0 0;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#8B88B5;">
                                {{ config('jelajah.tagline') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Kandungan --}}
                    <tr>
                        <td style="padding:32px;">
                            @if ($recipientName)
                                <p style="margin:0 0 16px;font-size:16px;color:#1B1C1E;">
                                    Assalamualaikum {{ $recipientName }},
                                </p>
                            @endif

                            <div style="font-size:15px;line-height:1.7;color:#55575C;">
                                @foreach (preg_split('/\n\s*\n/', trim($bodyText)) as $paragraph)
                                    <p style="margin:0 0 14px;">{!! nl2br(e($paragraph)) !!}</p>
                                @endforeach
                            </div>

                            @if ($actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0 8px;">
                                    <tr>
                                        <td style="background:#8875FF;border-radius:999px;">
                                            <a href="{{ $actionUrl }}"
                                               style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:500;
                                                      color:#FFFFFF;text-decoration:none;">
                                                {{ $actionLabel ?: 'Lihat Butiran' }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:12px 0 0;font-size:12px;color:#83868C;word-break:break-all;">
                                    Atau salin pautan ini: {{ $actionUrl }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Kaki --}}
                    <tr>
                        <td style="border-top:1px solid #EAEAEA;padding:22px 32px;background:#F4F4F4;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#83868C;">
                                {{ config('jelajah.slogan') }}<br>
                                BeDaie &middot; {{ config('jelajah.org') }} &middot; {{ config('jelajah.motto') }}
                            </p>
                            <p style="margin:10px 0 0;font-size:11px;color:#A0A2A8;">
                                Anda menerima e-mel ini kerana anda berurusan dengan BeDaie Jelajah.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
