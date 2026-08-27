<x-layouts.admin title="Template Notifikasi" heading="Template Notifikasi">
    <p class="mb-5 text-ink-soft text-pretty">
        Setiap pencetus boleh mempunyai template berasingan bagi setiap saluran.
        Gunakan placeholder yang disenaraikan pada setiap template.
    </p>

    @unless ($whatsappReady)
        <x-ui.alert variant="warning" icon="alert" class="mb-6" title="WhatsApp belum dikonfigurasi">
            Mesej WhatsApp direkod ke log sehingga kredensial provider ditetapkan dalam fail
            <code class="font-mono">.env</code> (WHATSAPP_ENABLED, WHATSAPP_BASE_URL, WHATSAPP_API_KEY).
        </x-ui.alert>
    @endunless

    @if ($templates->isEmpty())
        <x-ui.empty-state icon="mail" title="Belum ada template notifikasi" />
    @else
        <div class="space-y-5">
            @foreach ($templates as $key => $rows)
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-ink">{{ $rows->first()->name }}</h2>
                            <p class="mt-0.5 font-mono text-xs text-ink-muted">{{ $key }}</p>
                        </div>
                    </div>

                    <ul class="mt-2 divide-y divide-hairline">
                        @foreach ($rows as $row)
                            <livewire:admin.template-editor :template="$row" :key="'tpl-'.$row->id" />
                        @endforeach
                    </ul>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
