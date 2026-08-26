<x-layouts.app title="Profil" nav="penggerak" heading="Profil Saya"
               subheading="Maklumat ini digunakan untuk menghubungi anda dan dipaparkan sebagai Penggerak Jelajah.">

    <div class="max-w-2xl">
        <x-ui.card class="sm:p-8">
            <form method="POST" action="{{ route('penggerak.profil.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-ui.field label="Nama penuh" for="name" required :error="$errors->first('name')">
                    <x-ui.input id="name" name="name" :value="old('name', $user->name)"
                                :error="$errors->has('name')" autocomplete="name" required />
                </x-ui.field>

                <x-ui.field label="Nombor WhatsApp" for="phone" required :error="$errors->first('phone')">
                    <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $user->phone)"
                                :error="$errors->has('phone')" icon="whatsapp" inputmode="tel" required />
                </x-ui.field>

                @if ($user->hasPlaceholderEmail())
                    {{-- Akaun ini dicipta tanpa e-mel sebenar — benarkan pemiliknya mengisinya. --}}
                    <x-ui.field label="E-mel" for="email" optional
                                hint="Kami hanya menggunakannya untuk sijil dan resit. WhatsApp kekal saluran utama."
                                :error="$errors->first('email')">
                        <x-ui.input id="email" name="email" type="email" :value="old('email')"
                                    :error="$errors->has('email')" icon="mail"
                                    autocomplete="email" placeholder="nama@contoh.com" />
                    </x-ui.field>
                @else
                    <x-ui.field label="E-mel" for="email" hint="Hubungi pasukan BeDaie untuk menukar e-mel.">
                        <x-ui.input id="email" :value="$user->email" disabled />
                    </x-ui.field>
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Negeri" for="state_id" required :error="$errors->first('state_id')">
                        <x-ui.select id="state_id" name="state_id" :error="$errors->has('state_id')" required>
                            <option value="">Pilih negeri</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected(old('state_id', $user->state_id) == $state->id)>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Daerah" for="district_id" optional
                                hint="Simpan negeri dahulu untuk memuatkan senarai daerah."
                                :error="$errors->first('district_id')">
                        <x-ui.select id="district_id" name="district_id" :error="$errors->has('district_id')">
                            <option value="">Pilih daerah</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}" @selected(old('district_id', $user->district_id) == $district->id)>
                                    {{ $district->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <x-ui.field label="Nama masjid / surau / organisasi" for="organization_name" optional
                            :error="$errors->first('organization_name')">
                    <x-ui.input id="organization_name" name="organization_name"
                                :value="old('organization_name', $user->mobilizerProfile?->organization_name)"
                                :error="$errors->has('organization_name')" />
                </x-ui.field>

                <x-ui.field label="Sedikit tentang anda" for="about" optional
                            hint="Membantu pasukan BeDaie memahami konteks komuniti anda."
                            :error="$errors->first('about')">
                    <x-ui.textarea id="about" name="about" rows="3" :error="$errors->has('about')">{{ old('about', $user->mobilizerProfile?->about) }}</x-ui.textarea>
                </x-ui.field>

                <x-ui.button type="submit" variant="primary" size="lg">Simpan Profil</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card class="mt-6 sm:p-8">
            <h2 class="font-semibold text-navy-900">
                {{ $user->password_set_at ? 'Tukar Kata Laluan' : 'Tetapkan Kata Laluan' }}
            </h2>
            <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                @if ($user->password_set_at)
                    Kemas kini kata laluan anda.
                @else
                    Akaun anda dicipta automatik daripada permohonan, jadi ia belum
                    mempunyai kata laluan. Tetapkan satu jika anda mahu log masuk
                    tanpa pautan WhatsApp setiap kali.
                @endif
            </p>

            <form method="POST" action="{{ route('kata-laluan.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                {{-- Pengurus kata laluan perlukan medan nama pengguna untuk
                     mengaitkan kata laluan dengan akaun yang betul. --}}
                <input type="text" name="username" autocomplete="username" class="sr-only"
                       tabindex="-1" aria-hidden="true" readonly
                       value="{{ $user->realEmail() ?? $user->phone }}">

                @if ($user->password_set_at)
                    <x-ui.field label="Kata laluan semasa" for="current_password" required
                                :error="$errors->first('current_password')">
                        <x-ui.input id="current_password" name="current_password" type="password"
                                    :error="$errors->has('current_password')" icon="lock"
                                    autocomplete="current-password" required />
                    </x-ui.field>
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Kata laluan baharu" for="new_password" required
                                hint="Sekurang-kurangnya 8 aksara." :error="$errors->first('password')">
                        <x-ui.input id="new_password" name="password" type="password"
                                    :error="$errors->has('password')" icon="lock"
                                    autocomplete="new-password" required />
                    </x-ui.field>

                    <x-ui.field label="Sahkan kata laluan" for="password_confirmation" required>
                        <x-ui.input id="password_confirmation" name="password_confirmation" type="password"
                                    icon="lock" autocomplete="new-password" required />
                    </x-ui.field>
                </div>

                <x-ui.button type="submit" variant="outline">
                    {{ $user->password_set_at ? 'Kemas Kini Kata Laluan' : 'Tetapkan Kata Laluan' }}
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
