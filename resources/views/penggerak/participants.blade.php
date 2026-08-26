<x-layouts.app title="Peserta" nav="penggerak" heading="Peserta"
               subheading="Senarai peserta bagi program yang anda gerakkan.">

    @if ($events->isEmpty())
        <x-ui.empty-state icon="users" title="Belum ada program"
            description="Senarai peserta akan muncul selepas program anda disahkan." />
    @else
        <form method="GET" class="mb-6">
            <x-ui.field label="Pilih program" for="program">
                <x-ui.select id="program" name="program" onchange="this.form.submit()">
                    @foreach ($events as $event)
                        <option value="{{ $event->short_code }}" @selected($selected?->id === $event->id)>
                            {{ $event->title }} — {{ $event->dateLabel() }}
                        </option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </form>

        @if ($registrations && $registrations->isNotEmpty())
            <div class="mb-5 flex flex-wrap gap-2">
                <x-ui.badge color="purple" icon="users">
                    {{ number_format($selected->seatsTaken()) }} tempat diambil
                </x-ui.badge>
                <x-ui.badge color="success" icon="check-circle">
                    {{ number_format($selected->attended_count) }} hadir
                </x-ui.badge>
                @if ($selected->seatsLeft() !== null)
                    <x-ui.badge color="grey">{{ number_format($selected->seatsLeft()) }} tempat lagi</x-ui.badge>
                @endif
            </div>

            <div class="overflow-hidden rounded-card border border-hairline bg-surface">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <caption class="sr-only">Senarai peserta {{ $selected->title }}</caption>
                        <thead class="border-b border-hairline bg-mist/60 text-left">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium text-navy-900">Nama</th>
                                <th scope="col" class="px-4 py-3 font-medium text-navy-900">Telefon</th>
                                <th scope="col" class="px-4 py-3 font-medium text-navy-900">Kawasan</th>
                                <th scope="col" class="px-4 py-3 font-medium text-navy-900">Tempat</th>
                                <th scope="col" class="px-4 py-3 font-medium text-navy-900">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @foreach ($registrations as $registration)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-navy-900">{{ $registration->name }}</p>
                                        <p class="text-xs text-ink-muted">{{ $registration->reference_no }}</p>
                                    </td>
                                    {{-- Nombor disamarkan: Penggerak tidak perlu nombor penuh --}}
                                    <td class="px-4 py-3 font-mono text-ink-soft">{{ $registration->maskedPhone() }}</td>
                                    <td class="px-4 py-3 text-ink-soft">
                                        {{ $registration->district?->name ?? $registration->state?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-ink-soft">{{ $registration->seats() }}</td>
                                    <td class="px-4 py-3">
                                        @if ($registration->hasAttended())
                                            <x-ui.badge color="success" icon="check">
                                                {{ $registration->attendance->checked_in_at->format('g:ia') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge color="grey">Belum hadir</x-ui.badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $registrations->links() }}</div>

            <p class="mt-5 text-sm text-ink-muted text-pretty">
                Nombor telefon dipaparkan secara separa untuk melindungi privasi peserta.
                Hubungi pasukan BeDaie jika anda memerlukan senarai penuh atas sebab operasi.
            </p>
        @else
            <x-ui.empty-state icon="users" title="Belum ada pendaftaran"
                description="Kongsi link pendaftaran anda di WhatsApp untuk mula menerima pendaftaran.">
                @if ($selected)
                    <x-ui.button :href="$selected->whatsappShareUrl()" target="_blank" rel="noopener"
                                 variant="whatsapp" class="mt-5" icon="whatsapp">
                        Kongsi Sekarang
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @endif
    @endif
</x-layouts.app>
