<x-layouts.auth title="Kata Laluan Baharu" heading="Tetapkan Kata Laluan Baharu">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.field label="E-mel" for="email" required :error="$errors->first('email')">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $email)"
                        :error="$errors->has('email')" icon="mail" autocomplete="email" required />
        </x-ui.field>

        <x-ui.field label="Kata laluan baharu" for="password" required
                    hint="Sekurang-kurangnya 8 aksara." :error="$errors->first('password')">
            <x-ui.input id="password" name="password" type="password" :error="$errors->has('password')"
                        icon="lock" autocomplete="new-password" autofocus required />
        </x-ui.field>

        <x-ui.field label="Sahkan kata laluan" for="password_confirmation" required>
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password"
                        icon="lock" autocomplete="new-password" required />
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" block size="lg">Simpan Kata Laluan</x-ui.button>
    </form>
</x-layouts.auth>
