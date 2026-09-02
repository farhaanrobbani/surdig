<section x-data="{ preview: null }">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Pegawai') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
            {{ __('Data kepegawaian yang digunakan untuk laporan kinerja.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.employee.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="nip" :value="__('NIP')" />
                <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full" :value="old('nip', $user->nip)" />
                <x-input-error class="mt-2" :messages="$errors->get('nip')" />
            </div>

            <div>
                <x-input-label for="jabatan" :value="__('Jabatan')" />
                <x-text-input id="jabatan" name="jabatan" type="text" class="mt-1 block w-full" :value="old('jabatan', $user->jabatan)" />
                <x-input-error class="mt-2" :messages="$errors->get('jabatan')" />
            </div>

            <div>
                <x-input-label for="pangkat" :value="__('Pangkat')" />
                <x-text-input id="pangkat" name="pangkat" type="text" class="mt-1 block w-full" :value="old('pangkat', $user->pangkat)" />
                <x-input-error class="mt-2" :messages="$errors->get('pangkat')" />
            </div>

            <div>
                <x-input-label for="ruang_golongan" :value="__('Ruang / Golongan')" />
                <x-text-input id="ruang_golongan" name="ruang_golongan" type="text" class="mt-1 block w-full" :value="old('ruang_golongan', $user->ruang_golongan)" />
                <x-input-error class="mt-2" :messages="$errors->get('ruang_golongan')" />
            </div>
        </div>

        <div>
            <x-input-label for="instansi" :value="__('Instansi')" />
            <x-text-input id="instansi" name="instansi" type="text" class="mt-1 block w-full" :value="old('instansi', $user->instansi)" />
            <x-input-error class="mt-2" :messages="$errors->get('instansi')" />
        </div>

        <div>
            <x-input-label for="foto_profil" :value="__('Foto Profil')" />
            <div class="mt-1 flex items-center gap-4">
                <img x-show="preview"
                     :src="preview"
                     alt="Pratinjau foto"
                     class="h-20 w-20 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                <img x-show="! preview && {{ $user->fotoUrl() ? 'true' : 'false' }}"
                     src="{{ $user->fotoUrl() }}"
                     alt="Foto profil"
                     class="h-20 w-20 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                <label class="cursor-pointer">
                    <input type="file" name="foto_profil" id="foto_profil" accept="image/jpeg,image/png,image/webp" class="sr-only"
                           @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }" />
                    <span class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 dark:text-gray-500 hover:border-teal-500 hover:text-teal-700 dark:text-teal-400">
                        {{ __('Pilih Foto') }}
                    </span>
                </label>
                @if ($user->fotoUrl())
                    <label class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                        <input type="checkbox" name="foto_hapus" value="1" class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                        {{ __('Hapus foto') }}
                    </label>
                @endif
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('foto_profil')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
