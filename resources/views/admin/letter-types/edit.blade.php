<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Edit Jenis Surat: {{ $letterType->name }}</h2>
    </x-slot>

    @push('editor')
        @vite(['resources/js/editor.js'])
    @endpush

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded-md text-sm">
                        <strong>Periksa kembali isian:</strong>
                        <ul class="list-disc ml-4 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('letter-types.update', $letterType) }}"
                      x-data="fieldRepeater({{ json_encode(old('fields', $letterType->fields ?? [])) }}, {{ json_encode(old('permohonan_fields', $letterType->permohonan_fields ?? [])) }})">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="code" value="Kode (contoh: SKN, SKT, SKC)" />
                            <x-text-input id="code" name="code" class="mt-1 block w-full" required
                                          value="{{ old('code', $letterType->code) }}" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="name" value="Nama Jenis Surat" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" required
                                          value="{{ old('name', $letterType->name) }}" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="permohonan_judul" value="Judul Surat Permohonan" />
                        <x-text-input id="permohonan_judul" name="permohonan_judul" class="mt-1 block w-full" required
                                      value="{{ old('permohonan_judul', $letterType->permohonan_judul) }}" placeholder="Contoh: Surat Keterangan Domisili" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Judul yang dicetak di bagian atas PDF Surat Permohonan untuk jenis surat ini.</p>
                        <x-input-error :messages="$errors->get('permohonan_judul')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea id="description" name="description" rows="2"
                                  class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm">{{ old('description', $letterType->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="permohonan_body" value="Narasi Surat Permohonan" />
                        <textarea id="permohonan_body" name="permohonan_body" data-editor rows="4"
                                  data-upload-url="{{ route('announcements.gambar') }}"
                                  class="block w-full">{{ old('permohonan_body', $letterType->permohonan_body) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Isi kalimat permohonan untuk dicetak di Surat Permohonan (arsip). Gunakan placeholder <code>[nama_field]</code> dari field di atas; tersedia juga <code>[nama_pemohon]</code> dan <code>[kontak]</code>. Kalimat pembuka "Yang bertanda tangan di bawah ini, saya:" otomatis tampil di atas tabel identitas, tidak perlu ditulis ulang di sini.</p>
                        <x-input-error :messages="$errors->get('permohonan_body')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="permohonan_informasi" value="Informasi di Bawah Form Permohonan (Berkas yang Dibawa)" />
                        <textarea id="permohonan_informasi" name="permohonan_informasi" rows="4"
                                  class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm">{{ old('permohonan_informasi', $letterType->permohonan_informasi) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Ditampilkan di bawah form permohonan di halaman publik untuk jenis surat ini. Baris baru menjadi baris baru. Contoh: daftar berkas yang harus dibawa ke KUA. Kosongkan untuk menyembunyikan.</p>
                        <x-input-error :messages="$errors->get('permohonan_informasi')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <x-input-label value="Field / Data Surat" />
                            <button type="button" @click="addField()"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline">+ Tambah Field</button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Field ini menjadi form pengisian saat membuat surat. Centang <strong>Internal</strong> bila field hanya diisi petugas (tidak tampil di permohonan publik). Gunakan nama field sebagai placeholder <code>[nama_field]</code> di template surat. Seret ikon untuk mengurutkan field.</p>

                        <div class="mt-3 space-y-3">
                            <template x-for="(field, index) in fields" :key="index">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4 bg-gray-50 dark:bg-gray-700/40"
                                     :class="index === draggingIndex ? 'opacity-50' : ''"
                                     @dragover.prevent="dragOver($event)"
                                     @drop.prevent="dropAt(index)">
                                    <input type="hidden" :name="`fields[${index}][required]`" :value="field.required ? 1 : 0">
                                    <input type="hidden" :name="`fields[${index}][internal]`" :value="field.internal ? 1 : 0">
                                    <div class="flex items-start gap-3">
                                        <button type="button"
                                                draggable="true"
                                                class="mt-1 cursor-grab active:cursor-grabbing text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-300 dark:text-gray-500"
                                                title="Seret untuk mengurutkan"
                                                @dragstart="dragStart(index, $event)"
                                                @dragend="dragEnd()">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                            </svg>
                                        </button>
                                        <div class="flex-1">
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                                                <div class="sm:col-span-2">
                                                    <input :name="`fields[${index}][name]`" x-model="field.name" required
                                                           placeholder="nama_field" class="w-full rounded-md border-gray-300 text-sm" />
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input :name="`fields[${index}][label]`" x-model="field.label" required
                                                           placeholder="Label field" class="w-full rounded-md border-gray-300 text-sm" />
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <select :name="`fields[${index}][type]`" x-model="field.type"
                                                            class="w-full rounded-md border-gray-300 text-sm">
                                                        <option value="text">Text</option>
                                                        <option value="textarea">Textarea</option>
                                                        <option value="date">Tanggal</option>
                                                        <option value="select">Pilihan</option>
                                                    </select>
                                                </div>
                                                <div class="sm:col-span-1 flex items-center gap-3">
                                                    <label class="flex items-center text-xs text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                                        <input type="checkbox" x-model="field.required" class="rounded border-gray-300"> Wajib
                                                    </label>
                                                    <label class="flex items-center text-xs text-gray-600 dark:text-gray-300 dark:text-gray-500" title="Hanya ditampilkan saat petugas membuat surat, tidak tampil di permohonan publik">
                                                        <input type="checkbox" x-model="field.internal" class="rounded border-gray-300"> Internal
                                                    </label>
                                                    <button type="button" @click="fields.splice(index, 1)"
                                                            class="text-red-600 dark:text-red-400 text-sm hover:underline">Hapus</button>
                                                </div>
                                            </div>
                                            <div class="mt-2" x-show="field.type === 'select'">
                                                <input :name="`fields[${index}][options][]`" x-model="field.optionsText" placeholder="Opsi pilihan, pisahkan dengan koma"
                                                       class="w-full rounded-md border-gray-300 text-sm" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <p x-show="fields.length === 0" class="text-sm text-gray-400 dark:text-gray-500">Belum ada field. Klik "+ Tambah Field".</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label value="Field yang Tampil di Surat Permohonan" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Centang field yang dicetak pada tabel identitas di Surat Permohonan. Kosongkan semua berarti menampilkan semua field.</p>
                        <div class="mt-3 space-y-2">
                            <template x-for="(field, index) in fields" :key="index">
                                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">
                                    <input type="checkbox" :name="`permohonan_fields[]`" :value="field.name"
                                           x-model="permohonanFields" class="rounded border-gray-300">
                                    <span class="ms-2" x-text="field.label || field.name"></span>
                                </label>
                            </template>
                            <p x-show="fields.length === 0" class="text-sm text-gray-400 dark:text-gray-500">Tambahkan field terlebih dahulu.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="publik" value="1" {{ old('publik', $letterType->publik) ? 'checked' : '' }} class="rounded border-gray-300">
                            <span class="ms-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">Tampil di permohonan publik</span>
                        </label>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-2">Footer Surat</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">Teks footer tampil di bagian paling bawah halaman PDF surat jenis ini dan bisa berbeda antar jenis surat.</p>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="kop_footer_enabled" value="1"
                                       @checked((bool) (old('kop_footer_enabled', $letterType->kop_footer_enabled)))
                                       class="mt-1 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                                <span class="text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">Tampilkan footer di bagian bawah surat</span>
                            </label>
                            <x-input-error :messages="$errors->get('kop_footer_enabled')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="kop_footer" value="Isi Teks Footer Surat" />
                            <textarea id="kop_footer" name="kop_footer" rows="3"
                                      class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm"
                                      placeholder="Contoh: Surat ini diterbitkan secara elektronik melalui sistem surat digital KUA.">{{ old('kop_footer', $letterType->kop_footer) }}</textarea>
                            <x-input-error :messages="$errors->get('kop_footer')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="active" value="1" {{ old('active', $letterType->active) ? 'checked' : '' }} class="rounded border-gray-300">
                            <span class="ms-2 text-sm text-gray-700 dark:text-gray-300 dark:text-gray-500">Aktif (bisa dipilih saat membuat surat)</span>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('letter-types.index') }}" class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 hover:underline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function fieldRepeater(initialFields, initialPermohonanFields) {
            const normalize = (f) => ({
                name: f.name || '',
                label: f.label || '',
                type: f.type || 'text',
                required: !!f.required,
                internal: !!f.internal,
                optionsText: Array.isArray(f.options) ? f.options.join(', ') : (f.optionsText || ''),
            });

            return {
                fields: (initialFields || []).map(normalize),
                permohonanFields: initialPermohonanFields || [],
                draggingIndex: null,
                addField() {
                    this.fields.push({ name: '', label: '', type: 'text', required: true, internal: false, optionsText: '' });
                },
                dragStart(index, event) {
                    this.draggingIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', String(index));
                },
                dragOver(event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },
                dropAt(index) {
                    if (this.draggingIndex === null || this.draggingIndex === index) {
                        this.draggingIndex = null;
                        return;
                    }
                    const target = this.draggingIndex < index ? index - 1 : index;
                    const [moved] = this.fields.splice(this.draggingIndex, 1);
                    this.fields.splice(target, 0, moved);
                    this.draggingIndex = null;
                },
                dragEnd() {
                    this.draggingIndex = null;
                },
            };
        }
    </script>
</x-app-layout>
