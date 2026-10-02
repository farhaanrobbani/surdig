<div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 dark:text-gray-500">Data Pegawai</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Digunakan untuk laporan kinerja pegawai.</p>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="nip" value="NIP" />
            <x-text-input id="nip" name="nip" class="mt-1 block w-full" value="{{ old('nip', $user->nip ?? null) }}" />
            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="jabatan" value="Jabatan" />
            <x-text-input id="jabatan" name="jabatan" class="mt-1 block w-full" value="{{ old('jabatan', $user->jabatan ?? null) }}" />
            <x-input-error :messages="$errors->get('jabatan')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="pangkat" value="Pangkat" />
            <x-text-input id="pangkat" name="pangkat" class="mt-1 block w-full" value="{{ old('pangkat', $user->pangkat ?? null) }}" />
            <x-input-error :messages="$errors->get('pangkat')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="ruang_golongan" value="Ruang / Golongan" />
            <x-text-input id="ruang_golongan" name="ruang_golongan" class="mt-1 block w-full" value="{{ old('ruang_golongan', $user->ruang_golongan ?? null) }}" />
            <x-input-error :messages="$errors->get('ruang_golongan')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="grade_tukin" value="Grade Tukin" />
            <x-text-input id="grade_tukin" name="grade_tukin" type="number" min="0" max="30" class="mt-1 block w-full"
                          value="{{ old('grade_tukin', $user->grade_tukin ?? 8) }}" />
            <x-input-error :messages="$errors->get('grade_tukin')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="jumlah_uang_makan_harian" value="Uang Makan Harian (Rp)" />
            <x-text-input id="jumlah_uang_makan_harian" name="jumlah_uang_makan_harian" type="number" min="0" step="0.01" class="mt-1 block w-full"
                          value="{{ old('jumlah_uang_makan_harian', $user->jumlah_uang_makan_harian ?? 35150) }}" />
            <x-input-error :messages="$errors->get('jumlah_uang_makan_harian')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="jumlah_tukin_kotor" value="Tukin Kotor (Rp)" />
            <x-text-input id="jumlah_tukin_kotor" name="jumlah_tukin_kotor" type="number" min="0" step="0.01" class="mt-1 block w-full"
                          value="{{ old('jumlah_tukin_kotor', $user->jumlah_tukin_kotor ?? 0) }}" />
            <x-input-error :messages="$errors->get('jumlah_tukin_kotor')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="jumlah_tukin_bersih" value="Tukin Bersih (Rp)" />
            <x-text-input id="jumlah_tukin_bersih" name="jumlah_tukin_bersih" type="number" min="0" step="0.01" class="mt-1 block w-full"
                          value="{{ old('jumlah_tukin_bersih', $user->jumlah_tukin_bersih ?? 0) }}" />
            <x-input-error :messages="$errors->get('jumlah_tukin_bersih')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="gapok" value="Gaji Pokok (Rp)" />
            <x-text-input id="gapok" name="gapok" type="number" min="0" step="0.01" class="mt-1 block w-full"
                          value="{{ old('gapok', $user->gapok ?? 0) }}" />
            <x-input-error :messages="$errors->get('gapok')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="instansi" value="Instansi" />
            <x-text-input id="instansi" name="instansi" class="mt-1 block w-full" value="{{ old('instansi', $user->instansi ?? 'KUA Ampelgading') }}" />
            <x-input-error :messages="$errors->get('instansi')" class="mt-2" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="foto_profil" value="Foto Profil" />
        <div class="mt-1 flex items-center gap-4">
            @if (isset($user) && $user->fotoUrl())
                <img src="{{ $user->fotoUrl() }}" alt="Foto profil"
                     class="h-20 w-20 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
            @endif
            <input type="file" name="foto_profil" id="foto_profil" accept="image/jpeg,image/png,image/webp"
                   class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-600" />
            @if (isset($user) && $user->fotoUrl())
                <label class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                    <input type="checkbox" name="foto_hapus" value="1" class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                    Hapus foto
                </label>
            @endif
        </div>
        <x-input-error :messages="$errors->get('foto_profil')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="tanda_tangan" value="Tanda Tangan" />
        <div class="mt-1 flex items-center gap-4">
            @if (isset($user) && $user->ttdUrl())
                <img src="{{ $user->ttdUrl() }}" alt="Tanda tangan"
                     class="h-20 w-40 object-contain border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900" />
            @endif
            <input type="file" name="tanda_tangan" id="tanda_tangan" accept="image/jpeg,image/png,image/webp"
                   class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-600" />
            @if (isset($user) && $user->ttdUrl())
                <label class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                    <input type="checkbox" name="tanda_tangan_hapus" value="1" class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                    Hapus tanda tangan
                </label>
            @endif
        </div>
        <x-input-error :messages="$errors->get('tanda_tangan')" class="mt-2" />
    </div>
</div>
