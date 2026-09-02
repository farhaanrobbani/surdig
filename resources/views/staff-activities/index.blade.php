<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Kegiatan Harian</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
                    <p class="font-semibold mb-1">Kegiatan gagal disimpan:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="GET" action="{{ route('kegiatan.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="bulan" value="Bulan" />
                    <select name="bulan" id="bulan"
                            class="mt-1 border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m === $month)>{{ tanggal_indonesia(now()->startOfMonth()->month($m), 'F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="tahun" value="Tahun" />
                    <input type="number" name="tahun" id="tahun" value="{{ $year }}" min="2000" max="2100"
                           class="mt-1 block w-28 border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm" />
                </div>
                @if ($users->isNotEmpty())
                    <div>
                        <x-input-label for="user_id" value="Pegawai" />
                        <select name="user_id" id="user_id"
                                class="mt-1 border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm">
                            <option value="0">Semua pegawai</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected($selectedUserId === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <x-input-label value="{{ $currentUser->name }}" />
                    </div>
                @endif
                <x-primary-button>Tampilkan</x-primary-button>
            </form>

            <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800"
                 x-data="{
                     csrf: '{{ csrf_token() }}',
                     today: '{{ now()->format('Y-m-d') }}',
                     storeUrl: '{{ route('kegiatan.store') }}',
                     updateUrl: '{{ str_replace('9999', ':id', route('kegiatan.update', 9999)) }}',
                     daily: @js($dailyMap),
                     themes: @js($columns),
                     templates: @js($templates),
                     activityList: @js($activities->map(fn ($a) => [
                         'id' => $a->id,
                         'tanggal' => $a->tanggal,
                         'kegiatan' => $a->kegiatan,
                         'pekerjaan' => $a->pekerjaan,
                         'activity_type_key' => $a->activity_type_key,
                         'total_jumlah' => $a->total_jumlah,
                     ])->values()),
                     importItems: [],
                     picked: {},
                     importSearch: '',
                     importCategory: 'semua',
                     item: { id: null, tanggal: '', key: '', kegiatan: '', pekerjaan: '', volume: '' },
                     itemError: '',

                     initImport() {
                         const out = [];
                         Object.keys(this.daily).sort().forEach(tanggal => {
                             const data = this.daily[tanggal] || {};
                             Object.entries(this.themes).forEach(([key, label]) => {
                                 const volume = Number(data[key] || 0);
                                 if (volume > 0) {
                                     const t = this.templates[key] || {};
                                     out.push({
                                         uid: tanggal + '|' + key,
                                         tanggal,
                                         key,
                                         label: t.kegiatan || label,
                                         pekerjaan: t.pekerjaan || '',
                                         volume,
                                     });
                                 }
                             });
                         });
                         this.importItems = out;
                         this.importItems.forEach(e => this.picked[e.uid] = false);
                     },
                     visibleImport() {
                         const q = this.importSearch.trim().toLowerCase();
                         return this.importItems.filter(e =>
                             (this.importCategory === 'semua' || e.key === this.importCategory) &&
                             (! q || e.tanggal.includes(q) || e.label.toLowerCase().includes(q) || e.pekerjaan.toLowerCase().includes(q))
                         );
                     },
                     pickedEntries() {
                         return this.importItems.filter(e => this.picked[e.uid]);
                     },
                     pickedCount() {
                         return this.importItems.filter(e => this.picked[e.uid]).length;
                     },
                     missingUraianCount() {
                         return this.importItems.filter(e => this.picked[e.uid] && ! e.pekerjaan.trim()).length;
                     },
                     pickVisible(status) {
                         this.visibleImport().forEach(e => this.picked[e.uid] = status);
                     },
                     pickAll(status) {
                         this.importItems.forEach(e => this.picked[e.uid] = status);
                     },

                     openNew() {
                         const keys = Object.keys(this.themes);
                         const key = keys[0] || '';
                         const t = this.templates[key] || {};
                         this.item = {
                             id: null,
                             tanggal: this.today,
                             key,
                             kegiatan: t.kegiatan || this.themes[key] || '',
                             pekerjaan: t.pekerjaan || '',
                             volume: '',
                         };
                         this.itemError = '';
                         this.syncVolume();
                         $dispatch('open-modal', 'single-activity');
                     },
                     openEdit(activity) {
                         this.item = {
                             id: activity.id,
                             tanggal: activity.tanggal,
                             key: activity.activity_type_key || '',
                             kegiatan: activity.kegiatan,
                             pekerjaan: activity.pekerjaan,
                             volume: activity.total_jumlah,
                         };
                         this.itemError = '';
                         $dispatch('open-modal', 'single-activity');
                     },
                     syncVolume() {
                         if (! this.item.key || this.item.key === 'libur' || this.item.key === 'lainnya') return;
                         const d = this.daily[this.item.tanggal];
                         const v = d ? d[this.item.key] : undefined;
                         if (v !== undefined) this.item.volume = v;
                     },
                     onKeyChange() {
                         if (this.item.key === 'libur') {
                             this.item.kegiatan = 'Hari Libur / Libur Nasional';
                             this.item.pekerjaan = '-';
                             this.item.volume = '';
                         } else if (this.item.key === 'lainnya') {
                             if (this.item.kegiatan === 'Hari Libur / Libur Nasional') this.item.kegiatan = '';
                             if (this.item.pekerjaan === '-') this.item.pekerjaan = '';
                         } else {
                             const t = this.templates[this.item.key] || {};
                             this.item.kegiatan = t.kegiatan || this.themes[this.item.key] || this.item.kegiatan;
                             this.item.pekerjaan = t.pekerjaan || this.item.pekerjaan;
                         }
                         this.syncVolume();
                     },
                     async saveItem() {
                         if (! this.item.tanggal || ! this.item.kegiatan || ! this.item.pekerjaan) {
                             this.itemError = 'Tanggal, Kalimat Kegiatan, dan Kalimat Pekerjaan wajib diisi.';
                             return;
                         }
                        const fd = new FormData();
                        const isEdit = !! this.item.id;
                        const volume = this.item.volume === '' ? (this.item.key === 'libur' ? 0 : 1) : this.item.volume;
                        if (isEdit) {
                            fd.append('_method', 'PUT');
                            fd.append('tanggal', this.item.tanggal);
                            fd.append('kegiatan', this.item.kegiatan);
                            fd.append('pekerjaan', this.item.pekerjaan);
                            fd.append('activity_type_key', this.item.key);
                            fd.append('total_jumlah', volume);
                        } else {
                            fd.append('items[0][tanggal]', this.item.tanggal);
                            fd.append('items[0][kegiatan]', this.item.kegiatan);
                            fd.append('items[0][pekerjaan]', this.item.pekerjaan);
                            fd.append('items[0][activity_type_key]', this.item.key);
                            fd.append('items[0][total_jumlah]', volume);
                        }
                         const res = await fetch(isEdit ? this.updateUrl.replace(':id', this.item.id) : this.storeUrl, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': this.csrf,
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'Accept': 'application/json',
                             },
                             body: fd,
                         });
                         if (res.ok) {
                             location.reload();
                         } else {
                             const json = await res.json().catch(() => ({}));
                             const errors = json.errors ? Object.values(json.errors).flat() : [];
                             this.itemError = errors[0] || json.message || 'Gagal menyimpan kegiatan.';
                         }
                     }
                 }"
                 x-init="initImport()">

                <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Log Kegiatan &amp; Pekerjaan Staf</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Pilih data pekerjaan yang telah diinput operator untuk sebulan, lalu buat rincian kalimat pekerjaan Anda.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('kegiatan.templates.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                                Atur Template Kalimat
                            </a>
                            <button type="button" @click="$dispatch('open-modal', 'pull-master-data')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                                + Ambil Data dari Operator
                            </button>
                            <button type="button" @click="openNew()"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                                + Buat Pekerjaan Baru
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400 w-10">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Pegawai</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400 w-28">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Tema Kegiatan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Rincian Uraian Pekerjaan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400 w-28">Volume Berkas</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-400 w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse ($activities as $activity)
                                <tr x-data="{ confirm: false }">
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $activity->user->name }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-teal-700 dark:text-teal-400 whitespace-nowrap">
                                        {{ tanggal_indonesia($activity->tanggal, 'd/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 max-w-md">
                                        {{ $activity->kegiatan }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-md">{{ $activity->pekerjaan }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-center whitespace-nowrap">
                                        @if ($activity->total_jumlah > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                                {{ $activity->total_jumlah }} Berkas
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                        <template x-if="! confirm">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="openEdit(activityList[{{ $loop->index }}])"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                                                <button type="button" @click="confirm = true"
                                                        class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                            </div>
                                        </template>
                                        <template x-if="confirm">
                                            <div class="flex items-center justify-end gap-2 text-xs">
                                                <form action="{{ route('kegiatan.destroy', $activity) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="px-2 py-0.5 bg-red-600 text-white rounded font-semibold">Ya, Hapus</button>
                                                </form>
                                                <button type="button" @click="confirm = false"
                                                        class="text-gray-500 dark:text-gray-400 hover:underline">Batal</button>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada log kegiatan tercatat untuk bulan ini. Klik
                                        <span class="font-medium">"+ Ambil Data dari Operator"</span> atau
                                        <span class="font-medium">"+ Buat Pekerjaan Baru"</span>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('kegiatan.store') }}">
                    @csrf
                    <x-modal name="pull-master-data" maxWidth="2xl">
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Ambil Data Pekerjaan dari Operator</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Data master untuk seluruh bulan terpilih. Centang pekerjaan yang sesuai dengan tugas Anda,
                                        lalu sesuaikan kalimat rincian pekerjaan Anda.
                                    </p>
                                </div>
                                <button type="button" @click="$dispatch('close-modal', 'pull-master-data')"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs">
                                <button type="button" @click="pickVisible(true)"
                                        class="px-2.5 py-1 rounded-md bg-teal-500/10 text-teal-700 dark:text-teal-400 font-semibold hover:bg-teal-500/20">Pilih Tampilan Ini</button>
                                <button type="button" @click="pickVisible(false)"
                                        class="px-2.5 py-1 rounded-md bg-red-500/10 text-red-600 dark:text-red-400 font-semibold hover:bg-red-500/20">Batal Tampilan Ini</button>
                                <button type="button" @click="pickAll(true)"
                                        class="px-2.5 py-1 rounded-md bg-gray-500/10 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-500/20">Pilih Semua</button>
                                <button type="button" @click="pickAll(false)"
                                        class="px-2.5 py-1 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-semibold">Batal Semua</button>
                                <input type="text" x-model="importSearch" placeholder="Cari tanggal atau kata kunci..."
                                       class="ml-auto px-2.5 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs" />
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                                <button type="button" @click="importCategory = 'semua'"
                                        :class="importCategory === 'semua' ? 'bg-gray-800 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'"
                                        class="px-2 py-1 rounded-md font-semibold transition-all">Semua</button>
                                @foreach ($columns as $key => $label)
                                    <button type="button" @click="importCategory = '{{ $key }}'"
                                            :class="importCategory === '{{ $key }}' ? 'bg-gray-800 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'"
                                            class="px-2 py-1 rounded-md font-semibold transition-all">{{ $label }}</button>
                                @endforeach
                            </div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                Ditemukan: <strong class="text-gray-900 dark:text-gray-100" x-text="visibleImport().length"></strong>
                                entri master (Dipilih: <strong class="text-teal-700 dark:text-teal-400" x-text="pickedCount() + ' / ' + importItems.length"></strong>)
                            </div>

                            <template x-if="importItems.length === 0">
                                <div class="mt-4 p-5 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300 text-xs">
                                    <p class="font-bold">Belum Ada Data Master di Bulan Ini</p>
                                    <p class="mt-0.5">Operator belum menginput data kegiatan untuk bulan ini. Silakan buat pekerjaan baru secara manual.</p>
                                </div>
                            </template>

                            <template x-if="importItems.length > 0">
                                <div class="mt-3 max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="e in visibleImport()" :key="e.uid">
                                        <div class="p-3.5" :class="picked[e.uid] ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900 opacity-60'">
                                            <div class="flex items-center justify-between gap-3 cursor-pointer" @click="picked[e.uid] = ! picked[e.uid]">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox" x-model="picked[e.uid]" class="rounded border-gray-300 text-teal-600 dark:text-teal-400 focus:ring-teal-500" />
                                                    <div>
                                                        <span class="text-xs font-bold text-gray-900 dark:text-gray-100 block" x-text="e.label"></span>
                                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Tanggal: <span x-text="e.tanggal"></span></span>
                                                    </div>
                                                </div>
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 whitespace-nowrap" x-text="e.volume + ' Berkas'"></span>
                                            </div>
                                            <div class="mt-2 ml-6" x-show="picked[e.uid]">
                                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Kalimat Rincian Uraian Pekerjaan Saya:</label>
                                                <textarea rows="2" x-model="e.pekerjaan"
                                                          placeholder="Tuliskan uraian rincian pekerjaan yang Anda laksanakan..."
                                                          class="w-full px-2.5 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs"></textarea>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <div>
                                    <p x-show="missingUraianCount() > 0" class="text-xs font-semibold text-red-600 dark:text-red-400"
                                       x-text="missingUraianCount() + ' item terpilih belum diisi uraian pekerjaan. Lengkapi sebelum menyimpan.'"></p>
                                    <p x-show="pickedCount() > 0 && missingUraianCount() === 0" class="text-xs text-teal-700 dark:text-teal-400 font-semibold">
                                        Semua item terpilih sudah memiliki uraian. Siap disimpan.
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <button type="button" @click="$dispatch('close-modal', 'pull-master-data')"
                                            class="text-sm text-gray-600 dark:text-gray-300 hover:underline">Batal</button>
                                    <x-primary-button>
                                        Simpan Pekerjaan Terpilih (<span x-text="pickedCount()"></span>) ke Laporan Saya
                                    </x-primary-button>
                                </div>
                            </div>

                            <template x-for="(e, i) in pickedEntries()" :key="e.uid">
                                <div class="hidden">
                                    <input type="hidden" :name="'items[' + i + '][tanggal]'" :value="e.tanggal" />
                                    <input type="hidden" :name="'items[' + i + '][kegiatan]'" :value="e.label" />
                                    <input type="hidden" :name="'items[' + i + '][pekerjaan]'" :value="e.pekerjaan" />
                                    <input type="hidden" :name="'items[' + i + '][activity_type_key]'" :value="e.key" />
                                    <input type="hidden" :name="'items[' + i + '][total_jumlah]'" :value="e.volume" />
                                </div>
                            </template>
                        </div>
                    </x-modal>
                </form>

                <x-modal name="single-activity" maxWidth="lg">
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-100" x-text="item.id ? 'Edit Log Pekerjaan Laporan' : 'Buat Pekerjaan Baru'"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Isi rincian pekerjaan Anda dahulu, lalu hubungkan dengan tema data master operator.
                                </p>
                            </div>
                            <button type="button" @click="$dispatch('close-modal', 'single-activity')"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <x-input-label value="1. Tanggal Pelaksanaan Pekerjaan" />
                                <input type="date" x-model="item.tanggal" @change="syncVolume()" required
                                       class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm" />
                            </div>

                            <div>
                                <x-input-label value="2. Tema Pekerjaan (Dari Master Data)" />
                                <select x-model="item.key" @change="onKeyChange()"
                                        class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm">
                                    <option value="libur">Hari Libur</option>
                                    @foreach ($columns as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                    <option value="lainnya">Kegiatan / Pekerjaan Lainnya (Manual)</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label value="3. Judul Tema Kegiatan Laporan" />
                                <input type="text" x-model="item.kegiatan" required
                                       class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm" />
                            </div>

                            <div>
                                <x-input-label value="4. Tuliskan Kalimat Rincian Uraian Pekerjaan Anda" />
                                <textarea rows="3" x-model="item.pekerjaan" required
                                          placeholder="Misal: Memeriksa dan merekap berkas permohonan penerbitan duplikat buku nikah..."
                                          class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm"></textarea>
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <x-input-label value="5. Volume Berkas Pekerjaan" />
                                    <span class="text-[11px] text-teal-600 dark:text-teal-400 font-medium"
                                          x-text="item.key === 'libur' ? 'Hari Libur / Tanpa Berkas' : 'Otomatis diambil dari data operator'"></span>
                                </div>
                                <input type="number" x-model="item.volume" min="0"
                                       :required="item.key !== 'libur'"
                                       class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm text-sm font-semibold" />
                            </div>

                            <p x-show="itemError" class="text-xs text-red-600 dark:text-red-400" x-text="itemError"></p>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-3">
                            <button type="button" @click="$dispatch('close-modal', 'single-activity')"
                                    class="text-sm text-gray-600 dark:text-gray-300 hover:underline">Batal</button>
                            <button type="button" @click="saveItem()"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                                Simpan ke Laporan
                            </button>
                        </div>
                    </div>
                </x-modal>

            </div>
        </div>
    </div>
</x-app-layout>
