<x-layouts.app title="Pemberitahuan" :nav="$nav" heading="Pemberitahuan"
               subheading="Kemas kini terkini tentang permohonan, program dan sijil anda.">

    @if ($notifications->isEmpty())
        <x-ui.empty-state icon="bell" title="Tiada pemberitahuan"
            description="Kemas kini akan muncul di sini apabila ada perkembangan." />
    @else
        <ul class="space-y-3">
            @foreach ($notifications as $notification)
                @php $data = $notification->data; @endphp
                <li class="rounded-card border border-hairline bg-surface p-5
                           {{ $notification->read_at ? '' : 'border-brand-200 bg-brand-50/40' }}">
                    <div class="flex items-start gap-3.5">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl
                                     {{ $notification->read_at ? 'bg-mist' : 'bg-brand-100' }}">
                            <x-ui.icon :name="$data['icon'] ?? 'bell'"
                                       class="h-4 w-4 {{ $notification->read_at ? 'text-ink-muted' : 'text-brand-600' }}" />
                        </span>

                        <div class="min-w-0 flex-1">
                            @if (! empty($data['title']))
                                <p class="font-medium text-navy-900 text-pretty">{{ $data['title'] }}</p>
                            @endif
                            <p class="mt-1 text-sm text-ink-soft text-pretty">
                                {{ $data['body'] ?? $data['message'] ?? '' }}
                            </p>
                            <p class="mt-1.5 text-xs text-ink-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>

                            @if (! empty($data['url']))
                                <a href="{{ $data['url'] }}"
                                   class="mt-2.5 inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:underline">
                                    Lihat <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $notifications->links() }}</div>
    @endif
</x-layouts.app>
