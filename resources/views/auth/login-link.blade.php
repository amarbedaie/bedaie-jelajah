<x-layouts.auth title="Log Masuk Tanpa Kata Laluan" heading="Log Masuk Melalui WhatsApp"
                subheading="Masukkan nombor WhatsApp anda. Kami hantar pautan log masuk — tiada kata laluan diperlukan.">

    @if (session('success'))
        <x-ui.alert variant="success" icon="check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('masuk.pautan.hantar') }}" class="space-y-5">
        @csrf

        <x-ui.field label="Nombor WhatsApp atau e-mel" for="contact" required
                    hint="Gunakan nombor yang sama seperti semasa anda menghantar permohonan."
                    :error="$errors->first('contact')">
            <x-ui.input id="contact" name="contact" :value="old('contact')"
                        :error="$errors->has('contact')" icon="whatsapp"
                        inputmode="tel" autocomplete="tel" autofocus required
                        placeholder="012-345 6789" />
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" block size="lg" icon="whatsapp">
            Hantar Pautan Log Masuk
        </x-ui.button>
    </form>

    <div class="mt-7 rounded-xl bg-mist p-4">
        <p class="text-sm text-ink-soft text-pretty">
            Akaun Penggerak dicipta secara automatik apabila anda menghantar permohonan,
            jadi anda mungkin tidak pernah menetapkan kata laluan. Cara ini paling mudah.
        </p>
    </div>

    <p class="mt-6 text-center text-sm text-ink-soft">
        Ada kata laluan?
        <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:underline">Log masuk biasa</a>
    </p>
</x-layouts.auth>
