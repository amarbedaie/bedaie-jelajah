<x-layouts.auth title="Lupa Kata Laluan" heading="Set Semula Kata Laluan"
                subheading="Masukkan e-mel anda. Kami akan hantar pautan untuk menetapkan kata laluan baharu.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-ui.field label="E-mel" for="email" required :error="$errors->first('email')">
            <x-ui.input id="email" name="email" type="email" :value="old('email')" :error="$errors->has('email')"
                        icon="mail" autocomplete="email" autofocus required />
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" block size="lg">Hantar Pautan</x-ui.button>
    </form>

    <p class="mt-7 text-center text-sm text-ink-soft">
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Kembali ke log masuk</a>
    </p>
</x-layouts.auth>
