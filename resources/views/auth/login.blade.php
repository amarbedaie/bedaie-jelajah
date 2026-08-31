<x-layouts.auth title="Log Masuk" heading="Selamat Kembali"
                subheading="Log masuk untuk menjejak permohonan, program dan sijil anda.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-ui.field label="E-mel atau nombor WhatsApp" for="email" required :error="$errors->first('email')">
            <x-ui.input id="email" name="email" :value="old('email')" :error="$errors->has('email')"
                        icon="user" autocomplete="username" autofocus required />
        </x-ui.field>

        <x-ui.field label="Kata laluan" for="password" required :error="$errors->first('password')">
            <x-ui.input id="password" name="password" type="password" :error="$errors->has('password')"
                        icon="lock" autocomplete="current-password" required />
        </x-ui.field>

        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2.5 text-sm text-ink-soft">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-hairline text-brand-400 focus:ring-brand-400" />
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-700 hover:underline">
                Lupa kata laluan?
            </a>
        </div>

        <x-ui.button type="submit" variant="primary" block size="lg">Log Masuk</x-ui.button>
    </form>

    <div class="mt-7 rounded-xl border border-brand-200 bg-brand-50 p-4 text-center">
        <p class="text-sm text-ink-soft text-pretty">
            Anda Penggerak yang baru menghantar permohonan? Akaun anda dicipta
            automatik tanpa kata laluan.
        </p>
        <x-ui.button :href="route('masuk.pautan')" variant="whatsapp" size="sm" class="mt-3" icon="whatsapp">
            Log Masuk Melalui WhatsApp
        </x-ui.button>
    </div>

    <p class="mt-6 text-center text-sm text-ink-soft">
        Belum ada akaun?
        <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:underline">Daftar sekarang</a>
    </p>

    @if (! app()->environment('production'))
        <div class="mt-8 rounded-xl border border-dashed border-hairline bg-mist/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Akaun demo (pembangunan)</p>
            <ul class="mt-2 space-y-1 text-xs text-ink-soft">
                <li><strong>Admin:</strong> admin@bedaie.test</li>
                <li><strong>Penggerak:</strong> penggerak@bedaie.test</li>
                <li><strong>Peserta:</strong> peserta@bedaie.test</li>
                <li class="pt-1">Kata laluan: <code class="rounded bg-white px-1.5 py-0.5">password</code></li>
            </ul>
        </div>
    @endif
</x-layouts.auth>
