@props(['headers' => [], 'caption' => null, 'empty' => 'Tiada rekod'])

<div class="overflow-hidden rounded-card border border-hairline bg-surface">
    <div class="overflow-x-auto">
        {{-- 15px, bukan 14px: skrin ini dibaca berjam-jam setiap hari. --}}
        <table class="w-full text-[0.9375rem]">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            @if ($headers)
                <thead class="border-b border-hairline bg-mist/60 text-left">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-medium text-ink">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-hairline">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
