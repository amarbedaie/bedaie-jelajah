@props(['states', 'categories', 'action' => null, 'showPrice' => false])

<form method="GET" action="{{ $action ?? url()->current() }}"
      class="rounded-card border border-hairline bg-surface p-4 shadow-soft sm:p-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.field label="Negeri" for="f-negeri">
            <x-ui.select id="f-negeri" name="negeri">
                <option value="">Semua negeri</option>
                @foreach ($states as $state)
                    <option value="{{ $state->slug }}" @selected(request('negeri') === $state->slug)>
                        {{ $state->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Jenis program" for="f-kategori">
            <x-ui.select id="f-kategori" name="kategori">
                <option value="">Semua jenis</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('kategori') === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        @if ($showPrice)
            <x-ui.field label="Harga" for="f-harga">
                <x-ui.select id="f-harga" name="harga">
                    <option value="">Semua</option>
                    <option value="percuma" @selected(request('harga') === 'percuma')>Percuma sahaja</option>
                </x-ui.select>
            </x-ui.field>
        @endif

        <div class="flex items-end gap-2">
            <x-ui.button type="submit" variant="navy" icon="filter" block>Tapis</x-ui.button>
            @if (request()->hasAny(['negeri', 'kategori', 'harga']))
                <x-ui.button :href="$action ?? url()->current()" variant="ghost" size="md">Reset</x-ui.button>
            @endif
        </div>
    </div>
</form>
