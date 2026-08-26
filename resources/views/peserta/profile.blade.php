<x-layouts.app title="Profil" nav="peserta" heading="Profil Saya"
               subheading="Nama di sini digunakan sebagai nama pada sijil akan datang.">

    <div class="max-w-2xl">
        <x-ui.card class="sm:p-8">
            <form method="POST" action="{{ route('peserta.profil.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-ui.field label="Nama penuh" for="name" required
                            hint="Seperti yang mahu dicetak pada sijil." :error="$errors->first('name')">
                    <x-ui.input id="name" name="name" :value="old('name', $user->name)"
                                :error="$errors->has('name')" autocomplete="name" required />
                </x-ui.field>

                <x-ui.field label="Nombor WhatsApp" for="phone" required :error="$errors->first('phone')">
                    <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $user->phone)"
                                :error="$errors->has('phone')" icon="whatsapp" inputmode="tel" required />
                </x-ui.field>

                <x-ui.field label="E-mel" for="email" hint="Hubungi kami untuk menukar e-mel.">
                    <x-ui.input id="email" :value="$user->email" disabled />
                </x-ui.field>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">Jantina</legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        <x-ui.choice name="gender" value="lelaki" label="Lelaki"
                                     :checked="old('gender', $user->gender) === 'lelaki'" />
                        <x-ui.choice name="gender" value="perempuan" label="Perempuan"
                                     :checked="old('gender', $user->gender) === 'perempuan'" />
                    </div>
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Negeri" for="state_id" optional :error="$errors->first('state_id')">
                        <x-ui.select id="state_id" name="state_id" :error="$errors->has('state_id')">
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

                <x-ui.button type="submit" variant="primary" size="lg">Simpan Profil</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
