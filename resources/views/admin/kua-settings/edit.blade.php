<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Pengaturan Web</h2>
    </x-slot>

    @php($activeTab = in_array(request('tab'), ['web', 'instansi', 'surat', 'notif'], true) ? request('tab') : 'web')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6" x-data="{ tab: '{{ $activeTab }}', testLoading: false, testResult: null }">
                <div class="mb-6 flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-700">
                    <button type="button" @click="tab = 'web'"
                            :class="tab === 'web' ? 'border-teal-700 text-teal-700 dark:text-teal-400' : 'border-transparent text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:text-gray-500'"
                            class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold">
                        Web
                    </button>
                    <button type="button" @click="tab = 'instansi'"
                            :class="tab === 'instansi' ? 'border-teal-700 text-teal-700 dark:text-teal-400' : 'border-transparent text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:text-gray-500'"
                            class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold">
                        Instansi
                    </button>
                    <button type="button" @click="tab = 'surat'"
                            :class="tab === 'surat' ? 'border-teal-700 text-teal-700 dark:text-teal-400' : 'border-transparent text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:text-gray-500'"
                            class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold">
                        Surat
                    </button>
                    <button type="button" @click="tab = 'notif'"
                            :class="tab === 'notif' ? 'border-teal-700 text-teal-700 dark:text-teal-400' : 'border-transparent text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:text-gray-500'"
                            class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold">
                        Notifikasi
                    </button>
                </div>

                <form method="POST" action="{{ route('kua-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div x-show="tab === 'web'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Teks Beranda</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="hero_judul" value="Judul Utama Beranda" />
                                <textarea id="hero_judul" name="hero_judul" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                          maxlength="255" placeholder="Layanan Surat Digital&#10;Tanpa Antre, Kapan Saja">{{ old('hero_judul', $settings['hero_judul']['value']) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Baris baru (enter) menjadi baris baru pada judul. Kosongkan untuk memakai teks bawaan.</p>
                                <x-input-error :messages="$errors->get('hero_judul')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hero_subjudul" value="Paragraf Deskripsi Beranda" />
                                <textarea id="hero_subjudul" name="hero_subjudul" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                          maxlength="500" placeholder="Ajukan permohonan surat keterangan dan surat pengantar secara online.">{{ old('hero_subjudul', $settings['hero_subjudul']['value']) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Kosongkan untuk memakai teks bawaan.</p>
                                <x-input-error :messages="$errors->get('hero_subjudul')" class="mt-2" />
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Foto Background Welcome</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="bg" value="Foto Background Welcome (PNG/JPG/WEBP, maks 3MB)" />
                                <input id="bg" name="bg" type="file" accept="image/png,image/jpeg,image/webp"
                                       class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-800" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Foto yang menjadi latar belakang teks welcome di beranda. Kosongkan untuk memakai latar gradien bawaan.</p>
                                <x-input-error :messages="$errors->get('bg')" class="mt-2" />

                                @if (! empty($settings['bg_path']['value']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['bg_path']['value']))
                                    <div class="mt-4 flex items-start gap-4">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['bg_path']['value']) }}"
                                             alt="Background Welcome" class="w-full max-w-md rounded-md border border-gray-200 dark:border-gray-700 object-cover" />
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                            <input type="checkbox" name="bg_hapus" value="1"
                                                   class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                                            Hapus gambar
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Banner Beranda</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="hero" value="Banner Beranda (PNG/JPG/WEBP, maks 3MB)" />
                                <input id="hero" name="hero" type="file" accept="image/png,image/jpeg,image/webp"
                                       class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-800" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Gambar lebar (mis. rasio 21:9) yang tampil sebagai banner di bawah teks welcome. Bisa diganti kapan saja. Kosongkan untuk menyembunyikan banner.</p>
                                <x-input-error :messages="$errors->get('hero')" class="mt-2" />

                                @if (! empty($settings['hero_path']['value']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['hero_path']['value']))
                                    <div class="mt-4 flex items-start gap-4">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['hero_path']['value']) }}"
                                             alt="Hero Beranda" class="w-full max-w-md rounded-md border border-gray-200 dark:border-gray-700 object-cover" />
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                            <input type="checkbox" name="hero_hapus" value="1"
                                                   class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                                            Hapus gambar
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Jam Layanan</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="jam_layanan" value="Jam Layanan" />
                                <textarea id="jam_layanan" name="jam_layanan" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                          maxlength="255" placeholder="Senin – Jumat&#10;08.00 – 15.00 WIB">{{ old('jam_layanan', $settings['jam_layanan']['value']) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Ditampilkan di footer beranda, halaman permohonan, dan pengumuman. Boleh lebih dari satu baris.</p>
                                <x-input-error :messages="$errors->get('jam_layanan')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'instansi'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Data Instansi</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="instansi" value="Nama Instansi" />
                                <x-text-input id="instansi" name="instansi" class="mt-1 block w-full" required
                                              value="{{ old('instansi', $settings['instansi']['value']) }}" />
                                <x-input-error :messages="$errors->get('instansi')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="alamat" value="Alamat" />
                                <x-text-input id="alamat" name="alamat" class="mt-1 block w-full" required
                                              value="{{ old('alamat', $settings['alamat']['value']) }}" />
                                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="telepon" value="Telepon" />
                                <x-text-input id="telepon" name="telepon" class="mt-1 block w-full"
                                              value="{{ old('telepon', $settings['telepon']['value']) }}" />
                                <x-input-error :messages="$errors->get('telepon')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                              value="{{ old('email', $settings['email']['value']) }}" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="kecamatan" value="Kecamatan" />
                                <x-text-input id="kecamatan" name="kecamatan" class="mt-1 block w-full"
                                              value="{{ old('kecamatan', $settings['kecamatan']['value']) }}" />
                            </div>
                            <div>
                                <x-input-label for="kabupaten" value="Kabupaten/Kota" />
                                <x-text-input id="kabupaten" name="kabupaten" class="mt-1 block w-full"
                                              value="{{ old('kabupaten', $settings['kabupaten']['value']) }}" />
                            </div>
                            <div>
                                <x-input-label for="kode_pos" value="Kode Pos" />
                                <x-text-input id="kode_pos" name="kode_pos" class="mt-1 block w-full"
                                              value="{{ old('kode_pos', $settings['kode_pos']['value']) }}" />
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Media Sosial</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="sosmed_instagram" value="Instagram" />
                                <x-text-input id="sosmed_instagram" name="sosmed_instagram" type="url" class="mt-1 block w-full"
                                              placeholder="https://instagram.com/..." value="{{ old('sosmed_instagram', $settings['sosmed_instagram']['value']) }}" />
                                <x-input-error :messages="$errors->get('sosmed_instagram')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sosmed_tiktok" value="TikTok" />
                                <x-text-input id="sosmed_tiktok" name="sosmed_tiktok" type="url" class="mt-1 block w-full"
                                              placeholder="https://tiktok.com/@..." value="{{ old('sosmed_tiktok', $settings['sosmed_tiktok']['value']) }}" />
                                <x-input-error :messages="$errors->get('sosmed_tiktok')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sosmed_whatsapp" value="WhatsApp" />
                                <x-text-input id="sosmed_whatsapp" name="sosmed_whatsapp" type="url" class="mt-1 block w-full"
                                              placeholder="https://wa.me/6281..." value="{{ old('sosmed_whatsapp', $settings['sosmed_whatsapp']['value']) }}" />
                                <x-input-error :messages="$errors->get('sosmed_whatsapp')" class="mt-2" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-2">Isi URL lengkap dengan <code>https://</code>. Link tampil di footer beranda dengan ikon platform. Kosongkan untuk menyembunyikan platform tersebut.</p>

                        <div x-data="{
                            links: {{ \Illuminate\Support\Js::from(json_decode(kua_setting('link_terkait', '[]'), true) ?? []) }}
                        }" class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Link Terkait</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1 mb-2">Tambahkan link eksternal yang ingin ditampilkan di footer (misal: SIMKAH, AIW, SIMAS, dsb). Label bersifat custom.</p>
                            <template x-for="(link, i) in links" :key="i">
                                <div class="grid grid-cols-12 gap-3 items-end mb-3">
                                    <div class="col-span-5">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Label</label>
                                        <input type="text"
                                               x-model="link.label"
                                               class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                               placeholder="SIMKAH">
                                    </div>
                                    <div class="col-span-6">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">URL</label>
                                        <input type="url"
                                               x-model="link.url"
                                               class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                               placeholder="https://...">
                                    </div>
                                    <div class="col-span-1 flex items-end pb-0.5">
                                        <button type="button" @click="links.splice(i, 1)"
                                                class="text-red-400 hover:text-red-300" title="Hapus link">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="links.push({label: '', url: ''})"
                                    class="mt-2 text-sm text-teal-600 hover:text-teal-700 font-medium inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Link
                            </button>
                            <input type="hidden" name="link_terkait" :value="JSON.stringify(links)">
                        </div>
                    </div>

                    <div x-show="tab === 'surat'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Logo KUA</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="logo" value="Logo 1 (KUA) (PNG/JPG/WEBP, maks 2MB)" />
                                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                                       class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-800" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Gunakan PNG dengan latar transparan. Logo 1 tampil di beranda, halaman login, dan favicon.</p>
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />

                                @if (! empty($settings['logo_path']['value']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo_path']['value']))
                                    <div class="mt-4 flex items-center gap-4">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['logo_path']['value']) }}"
                                             alt="Logo 1 (KUA)" class="h-20 w-20 rounded-md border border-gray-200 dark:border-gray-700 object-contain p-1 bg-gray-50 dark:bg-gray-700/40" />
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                            <input type="checkbox" name="logo_hapus" value="1"
                                                   class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                                            Hapus logo 1
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <x-input-label for="logo2" value="Logo 2 (PNG/JPG/WEBP, maks 2MB)" />
                                <input id="logo2" name="logo2" type="file" accept="image/png,image/jpeg,image/webp"
                                       class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-teal-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-800" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Gunakan PNG dengan latar transparan. Logo 2 hanya dipakai untuk kop surat PDF jika dipilih.</p>
                                <x-input-error :messages="$errors->get('logo2')" class="mt-2" />

                                @if (! empty($settings['logo2_path']['value']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo2_path']['value']))
                                    <div class="mt-4 flex items-center gap-4">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['logo2_path']['value']) }}"
                                             alt="Logo 2" class="h-20 w-20 rounded-md border border-gray-200 dark:border-gray-700 object-contain p-1 bg-gray-50 dark:bg-gray-700/40" />
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                            <input type="checkbox" name="logo2_hapus" value="1"
                                                   class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500">
                                            Hapus logo 2
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <x-input-label value="Logo yang Dipakai di Kop Surat" />
                                <div class="mt-1 space-y-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">
                                        <input type="radio" name="kop_logo" value="logo1"
                                               class="rounded-full border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500"
                                               {{ (old('kop_logo', $settings['kop_logo']['value'] ?: 'logo1') === 'logo1') ? 'checked' : '' }}>
                                        Logo 1 (KUA)
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">
                                        <input type="radio" name="kop_logo" value="logo2"
                                               class="rounded-full border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500"
                                               {{ (old('kop_logo', $settings['kop_logo']['value'] ?: 'logo1') === 'logo2') ? 'checked' : '' }}>
                                        Logo 2
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Satu logo terpilih tampil di sisi kiri kop surat PDF.</p>
                                <x-input-error :messages="$errors->get('kop_logo')" class="mt-2" />
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Kop Surat (Teks)</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="kop_teks" value="Isi Teks Kop Surat" />
                                <textarea id="kop_teks" name="kop_teks" rows="5"
                                          class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm font-mono text-sm"
                                          placeholder="#KUA KECAMATAN CONTOH&#10;##KECAMATAN CONTOH KABUPATEN CONTOH&#10;Jl. Contoh No. 1, Telp. (021) 123456">{{ old('kop_teks', $settings['kop_teks']['value']) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">
                                    Tiap baris menjadi satu baris di kop surat. Penanda:
                                    <code>#</code> = Level 1 (besar dan tebal, nama instansi), <code>##</code> = Level 2 (tebal),
                                    <code>###</code> = Level 3 (tebal, ukuran sedang), tanpa penanda = baris biasa (alamat, dll).
                                    Kosongkan untuk memakai field Instansi/Kecamatan/Alamat yang sudah diisi di atas.
                                </p>
                                <x-input-error :messages="$errors->get('kop_teks')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            <div>
                                <x-input-label for="kop_ukuran_judul" value="Ukuran Font Level 1 (px)" />
                                <x-text-input id="kop_ukuran_judul" name="kop_ukuran_judul" type="number" min="6" max="72" step="0.5"
                                              class="mt-1 block w-full" placeholder="17"
                                              value="{{ old('kop_ukuran_judul', $settings['kop_ukuran_judul']['value']) }}" />
                                <x-input-error :messages="$errors->get('kop_ukuran_judul')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="kop_ukuran_sub" value="Ukuran Font Level 2 (px)" />
                                <x-text-input id="kop_ukuran_sub" name="kop_ukuran_sub" type="number" min="6" max="72" step="0.5"
                                              class="mt-1 block w-full" placeholder="13"
                                              value="{{ old('kop_ukuran_sub', $settings['kop_ukuran_sub']['value']) }}" />
                                <x-input-error :messages="$errors->get('kop_ukuran_sub')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="kop_ukuran_sub2" value="Ukuran Font Level 3 (px)" />
                                <x-text-input id="kop_ukuran_sub2" name="kop_ukuran_sub2" type="number" min="6" max="72" step="0.5"
                                              class="mt-1 block w-full" placeholder="11.5"
                                              value="{{ old('kop_ukuran_sub2', $settings['kop_ukuran_sub2']['value']) }}" />
                                <x-input-error :messages="$errors->get('kop_ukuran_sub2')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="kop_ukuran_baris" value="Ukuran Font Baris (px)" />
                                <x-text-input id="kop_ukuran_baris" name="kop_ukuran_baris" type="number" min="6" max="72" step="0.5"
                                              class="mt-1 block w-full" placeholder="10.5"
                                              value="{{ old('kop_ukuran_baris', $settings['kop_ukuran_baris']['value']) }}" />
                                <x-input-error :messages="$errors->get('kop_ukuran_baris')" class="mt-2" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-2">Kosongkan untuk memakai ukuran bawaan (Level 1 = 17, Level 2 = 13, Level 3 = 11.5, Baris = 10.5 px).</p>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Kepala KUA (Penandatangan)</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="kepala_nama" value="Nama Kepala KUA" />
                                <x-text-input id="kepala_nama" name="kepala_nama" class="mt-1 block w-full" required
                                              value="{{ old('kepala_nama', $settings['kepala_nama']['value']) }}" />
                                <x-input-error :messages="$errors->get('kepala_nama')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="kepala_nip" value="NIP" />
                                <x-text-input id="kepala_nip" name="kepala_nip" class="mt-1 block w-full"
                                              value="{{ old('kepala_nip', $settings['kepala_nip']['value']) }}" />
                            </div>
                            <div>
                                <x-input-label for="kepala_pangkat" value="Pangkat/Golongan" />
                                <x-text-input id="kepala_pangkat" name="kepala_pangkat" class="mt-1 block w-full"
                                              value="{{ old('kepala_pangkat', $settings['kepala_pangkat']['value']) }}" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="sk_kepala" value="No. SK Pengangkatan" />
                                <x-text-input id="sk_kepala" name="sk_kepala" class="mt-1 block w-full"
                                              value="{{ old('sk_kepala', $settings['sk_kepala']['value']) }}" />
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4">Tanda Tangan</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-input-label value="Tampilkan Anchor (^) di Blok Tanda Tangan" />
                                <div class="mt-1 space-y-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">
                                        <input type="radio" name="kop_anchor" value="1"
                                               class="rounded-full border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500"
                                               {{ (old('kop_anchor', $settings['kop_anchor']['value'] ?: '1') === '1') ? 'checked' : '' }}>
                                        Ya, tampilkan
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">
                                        <input type="radio" name="kop_anchor" value="0"
                                               class="rounded-full border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500"
                                               {{ (old('kop_anchor', $settings['kop_anchor']['value'] ?: '1') === '0') ? 'checked' : '' }}>
                                        Tidak
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Simbol <code>^</code> ditampilkan di antara "Kepala," dan nama penandatangan pada kop surat PDF sebagai penanda posisi tanda tangan.</p>
                                <x-input-error :messages="$errors->get('kop_anchor')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'notif'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Telegram Bot</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Konfigurasi bot Telegram untuk mengirim notifikasi otomatis ke grup saat ada permohonan baru atau kritik/saran masuk.</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="telegram_bot_token" value="Bot Token" />
                                <input id="telegram_bot_token" name="telegram_bot_token" type="password"
                                       class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm"
                                       placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz" autocomplete="off"
                                       value="{{ old('telegram_bot_token', $settings['telegram_bot_token']['value']) }}" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dapatkan dari @BotFather di Telegram. Kosongkan jika tidak ingin mengubah.</p>
                                <x-input-error :messages="$errors->get('telegram_bot_token')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="telegram_chat_id" value="Chat ID Grup" />
                                <x-text-input id="telegram_chat_id" name="telegram_chat_id" class="mt-1 block w-full"
                                              placeholder="-1001234567890"
                                              value="{{ old('telegram_chat_id', $settings['telegram_chat_id']['value']) }}" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Chat ID grup Telegram (angka negatif). Kosongkan jika tidak ingin mengubah.</p>
                                <x-input-error :messages="$errors->get('telegram_chat_id')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <button type="button"
                                        @click="
                                            testLoading = true;
                                            testResult = null;
                                            const fd = new FormData();
                                            fd.append('telegram_bot_token', document.getElementById('telegram_bot_token').value);
                                            fd.append('telegram_chat_id', document.getElementById('telegram_chat_id').value);
                                            fd.append('_token', '{{ csrf_token() }}');
                                            try {
                                                const res = await fetch('{{ route('kua-settings.test-telegram') }}', {
                                                    method: 'POST',
                                                    body: fd,
                                                });
                                                testResult = await res.json();
                                            } catch (e) {
                                                testResult = { ok: false, message: 'Gagal menghubungi server.' };
                                            }
                                            testLoading = false;
                                        "
                                        :disabled="testLoading"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 dark:bg-teal-600 text-white text-sm font-semibold rounded-md hover:bg-teal-700 dark:hover:bg-teal-500 disabled:opacity-50 transition">
                                    <span x-show="!testLoading">🧪 Tes Koneksi</span>
                                    <span x-show="testLoading" class="flex items-center gap-1">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        Mengirim...
                                    </span>
                                </button>
                                <div x-show="testResult"
                                     x-transition
                                     :class="testResult?.ok ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800'"
                                     class="mt-3 px-4 py-3 rounded-md border text-sm">
                                    <span x-text="testResult?.message"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 hover:underline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
