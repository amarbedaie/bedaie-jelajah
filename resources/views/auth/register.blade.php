<x-layouts.auth title="Daftar Akaun" heading="Sertai BeDaie Jelajah"
                subheading="Satu akaun untuk mendaftar program, menyimpan sijil dan menjejak Pasport Ilmu anda.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-ui.field label="Nama penuh" for="name" required
                    hint="Nama ini akan dicetak pada sijil anda." :error="$errors->first('name')">
            <x-ui.input id="name" name="name" :value="old('name')" :error="$errors->has('name')"
                        autocomplete="name" autofocus required />
        </x-ui.field>

        <x-ui.field label="Nombor WhatsApp" for="phone" required :error="$errors->first('phone')">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone')" :error="$errors->has('phone')"
                        icon="whatsapp" inputmode="tel" autocomplete="tel" placeholder="012-345 6789" required />
        </x-ui.field>

        <x-ui.field label="E-mel" for="email" required :error="$errors->first('email')">
            <x-ui.input id="email" name="email" type="email" :value="old('email')" :error="$errors->has('email')"
                        icon="mail" autocomplete="email" required />
        </x-ui.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Kata laluan" for="password" required
                        hint="Sekurang-kurangnya 8 aksara." :error="$errors->first('password')">
                <x-ui.input id="password" name="password" type="password" :error="$errors->has('password')"
                            icon="lock" autocomplete="new-password" required />
            </x-ui.field>

            <x-ui.field label="Sahkan kata laluan" for="password_confirmation" required>
                <x-ui.input id="password_confirmation" name="password_confirmation" type="password"
                            icon="lock" autocomplete="new-password" required />
            </x-ui.field>
        </div>

        <div>
            <label class="flex items-start gap-2.5 text-sm text-ink-soft">
                <input type="checkbox" name="privacy" value="1" @checked(old('privacy'))
                       class="mt-0.5 h-4 w-4 shrink-0 rounded border-control-line text-brand-600
                              focus:ring-brand-400" required />
                <span>
                    Saya bersetuju dengan
                    <a href="{{ route('privasi') }}" target="_blank" class="font-medium text-brand-600 hover:underline">polisi privasi</a>
                    dan
                    <a href="{{ route('terma') }}" target="_blank" class="font-medium text-brand-600 hover:underline">terma penggunaan</a>.
                </span>
            </label>
            @error('privacy')
                <p class="mt-2 text-sm text-alert">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" variant="primary" block size="lg">Daftar Akaun</x-ui.button>
    </form>

    <p class="mt-7 text-center text-sm text-ink-soft">
        Sudah ada akaun?
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Log masuk</a>
    </p>
</x-layouts.auth>
