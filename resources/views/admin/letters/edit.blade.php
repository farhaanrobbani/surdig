<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Edit Surat</h2>
    </x-slot>

    @push('editor')
        @vite(['resources/js/editor.js'])
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $letter->letterType->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Perihal saat ini: {{ $letter->perihal }}</p>
                    </div>

                    <form method="POST" action="{{ route('letters.update', $letter) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nomor" value="Nomor Surat" />
                                <x-text-input id="nomor" name="nomor" class="mt-1 block w-full"
                                              value="{{ old('nomor', $letter->nomor) }}"
                                              placeholder="Contoh: B.001/KUA.01.01.01/PW.01/01/2026" />
                                <x-input-error :messages="$errors->get('nomor')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tanggal_surat" value="Tanggal Surat" />
                                <x-text-input id="tanggal_surat" name="tanggal_surat" type="date"
                                              class="mt-1 block w-full"
                                              value="{{ old('tanggal_surat', $letter->tanggal_surat?->format('Y-m-d')) }}" />
                                <x-input-error :messages="$errors->get('tanggal_surat')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-6">
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                <input type="checkbox" name="tampilkan_tanggal" value="1"
                                       {{ old('tampilkan_tanggal', $letter->tampilkan_tanggal ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                                Tampilkan tanggal di baris atas
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500">
                                <input type="hidden" name="tampilkan_tanggal_ttd" value="0">
                                <input type="checkbox" name="tampilkan_tanggal_ttd" value="1"
                                       {{ old('tampilkan_tanggal_ttd', $letter->tampilkan_tanggal_ttd ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                                Tampilkan tanggal di blok tanda tangan
                            </label>
                        </div>

                        <div class="mt-6">
                            <x-input-label for="perihal" value="Perihal Surat" />
                            <x-text-input id="perihal" name="perihal" class="mt-1 block w-full" required
                                          value="{{ old('perihal', $letter->perihal) }}" />
                            <x-input-error :messages="$errors->get('perihal')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="header_html" value="Baris Atas Surat (Bebas)" />
                            <textarea id="header_html" name="header_html" data-editor rows="4"
                                      class="block w-full">{{ old('header_html', $headerHtml) }}</textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Tulis baris atas surat (Nomor, Lampiran, Perihal, dll.) secara bebas. Gunakan <code>{nomor}</code>, <code>{perihal}</code>, <code>{tanggal_surat}</code> agar otomatis mengikuti kolom di atas saat PDF dirender. Kosongkan untuk memakai baris otomatis.</p>
                            <x-input-error :messages="$errors->get('header_html')" class="mt-2" />
                        </div>

                        <div class="mt-6 space-y-4">
                            @php($internalShown = false)
                            @foreach ($letter->letterType->fields ?? [] as $field)
                                @if (! empty($field['internal']))
                                    @if (! $internalShown)
                                        @php($internalShown = true)
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 dark:text-gray-500">Data tambahan (diisi petugas)</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Field berikut hanya diisi oleh petugas saat membuat surat.</p>
                                        </div>
                                    @endif
                                @endif
                                @php($name = 'data[' . $field['name'] . ']')
                                @php($value = old('data.' . $field['name'], $data[$field['name']] ?? null))
                                <div>
                                    <x-input-label :for="'field-' . $field['name']" :value="$field['label'] . ($field['required'] ? ' *' : '')" />
                                    @if ($field['type'] === 'textarea')
                                        <textarea id="field-{{ $field['name'] }}" name="{{ $name }}" rows="3"
                                                  class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm">{{ $value }}</textarea>
                                    @elseif ($field['type'] === 'date')
                                        <x-text-input id="field-{{ $field['name'] }}" name="{{ $name }}" type="date"
                                                      class="mt-1 block w-full" :value="$value" />
                                    @elseif ($field['type'] === 'select')
                                        <select id="field-{{ $field['name'] }}" name="{{ $name }}"
                                                class="mt-1 block w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($field['options'] ?? [] as $option)
                                                <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <x-text-input id="field-{{ $field['name'] }}" name="{{ $name }}"
                                                      class="mt-1 block w-full" :value="$value" />
                                    @endif
                                    <x-input-error :messages="$errors->get('data.' . $field['name'])" class="mt-2" />
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Simpan Perubahan</x-primary-button>
                            <a href="{{ route('letters.show', $letter) }}" class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 hover:underline">Batal</a>
                        </div>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6 h-fit">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Template Surat</h3>
                    @if ($letter->letterType->templates->first())
                        <div class="text-sm text-gray-800 dark:text-gray-100 bg-gray-50 dark:bg-gray-700/40 rounded-md p-3">{!! \App\Support\ColonTableFormatter::format(\App\Support\HtmlSanitizer::normalize($letter->letterType->templates->first()->body)) !!}</div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada template untuk jenis surat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
