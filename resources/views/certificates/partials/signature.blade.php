<table style="width: auto; {{ $alignLeft ? '' : 'margin-left: auto;' }}">
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
