@php
    $channels = ['inapp' => 'In-App', 'email' => 'E-mel', 'whatsapp' => 'WhatsApp', 'sistem' => 'Sistem'];
@endphp

<x-layouts.admin title="Log Notifikasi" heading="Log Notifikasi"
                 subheading="Setiap mesej yang cuba dihantar — dan sama ada ia berjaya.">

    {{-- Keadaan saluran --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['WhatsApp', $whatsappReady, 'Mesej dihantar kepada peserta.', 'Mesej direkod ke log sahaja sehingga kredensial provider ditetapkan.'],
            ['E-mel', $mailReady, 'E-mel dihantar melalui SMTP/API.', 'MAIL_MAILER=log — e-mel tidak dihantar keluar.'],
            ['Queue worker', $queuePending !== null, 'Notifikasi diproses di latar.', 'Tiada maklumat.'],
        ] as [$name, $ready, $onText, $offText])
            <div class="rounded-card border border-hairline bg-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-medium text-ink">{{ $name }}</p>
                    <x-ui.badge :color="$ready ? 'success' : 'warning'" dot>
                        {{ $ready ? 'Aktif' : 'Mod log' }}
                    </x-ui.badge>
                </div>
                <p class="mt-2 text-sm text-ink-soft text-pretty">{{ $ready ? $onText : $offText }}</p>
            </div>
        @endforeach
    </div>

    @if ($queuePending > 0)
        <x-ui.alert variant="warning" icon="clock" class="mb-6" title="{{ $queuePending }} notifikasi menunggu giliran">
            Jika angka ini tidak turun, queue worker mungkin tidak berjalan pada pelayan.
        </x-ui.alert>
    @endif

    {{-- Penapis --}}
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari nama atau nombor penerima…" aria-label="Carian log" />
        <x-ui.select name="saluran" aria-label="Tapis saluran">
            <option value="">Semua saluran</option>
            @foreach ($channels as $value => $label)
                <option value="{{ $value }}" @selected(request('saluran') === $value)>{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="status" aria-label="Tapis status">
            <option value="">Semua status</option>
            <option value="sent" @selected(request('status') === 'sent')>Berjaya</option>
            <option value="logged" @selected(request('status') === 'logged')>Direkod sahaja</option>
            <option value="failed" @selected(request('status') === 'failed')>Gagal</option>
        </x-ui.select>
        <x-ui.button type="submit" variant="outline" icon="filter">Tapis</x-ui.button>
    </form>

    @if ($logs->isEmpty())
        <x-ui.empty-state icon="mail" title="Tiada rekod notifikasi"
            description="Rekod muncul di sini sebaik sahaja peserta mendaftar atau status permohonan berubah." />
    @else
        <x-jelajah.admin-table caption="Log notifikasi"
            :headers="['Masa', 'Pencetus', 'Saluran', 'Penerima', 'Status', 'Kandungan']">
            @foreach ($logs as $log)
                <tr class="align-top hover:bg-mist/40">
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-ink-muted">
                        {{ $log->created_at->translatedFormat('j M, g:ia') }}
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $log->template_key }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$log->channel === 'whatsapp' ? 'success' : 'grey'">
                            {{ $channels[$log->channel] ?? $log->channel }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-ink">{{ $log->recipient_name ?? '—' }}</p>
                        {{-- Alamat penuh disimpan; dipaparkan separa demi privasi. --}}
                        <p class="font-mono text-xs text-ink-muted">{{ $log->maskedAddress() }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $tone = match ($log->status) {
                                'sent' => 'success', 'failed' => 'danger', default => 'warning',
                            };
                            $label = match ($log->status) {
                                'sent' => 'Berjaya', 'failed' => 'Gagal', default => 'Direkod',
                            };
                        @endphp
                        <x-ui.badge :color="$tone">{{ $label }}</x-ui.badge>
                        @if ($log->error)
                            <p class="mt-1 max-w-xs text-xs text-danger text-pretty">{{ $log->error }}</p>
                        @endif
                    </td>
                    <td class="max-w-md px-4 py-3">
                        <p class="whitespace-pre-line text-xs leading-relaxed text-ink-soft text-pretty">{{ $log->preview() }}</p>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $logs->links() }}</div>
    @endif
</x-layouts.admin>
