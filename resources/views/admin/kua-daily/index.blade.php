<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Master Data Harian KUA</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-4">
                <form method="GET" action="{{ route('kua-daily.index') }}" class="flex flex-wrap items-end gap-3">
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
                    <x-primary-button>Tampilkan</x-primary-button>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('kua-themes.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                        + Tema Pekerjaan
                    </a>

                    <a href="{{ route('kua-daily.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                        + Input Data Harian
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Tanggal</th>
                            @foreach ($columns as $label)
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap dark:text-gray-400">{{ $label }}</th>
                            @endforeach
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($data as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-100">
                                    <a href="{{ route('kua-daily.edit', $item) }}"
                                       title="Klik untuk edit"
                                       class="hover:text-teal-700 hover:underline cursor-pointer dark:hover:text-teal-400">
                                        {{ tanggal_indonesia($item->tanggal, 'd F Y') }}
                                    </a>
                                </td>
                                @foreach ($columns as $key => $label)
                                    <td class="px-3 py-3 text-sm text-gray-700 text-right dark:text-gray-300">{{ $item->value($key) ?? 0 }}</td>
                                @endforeach
                                <td class="px-3 py-3 text-sm font-semibold text-teal-700 text-right dark:text-teal-400">{{ $item->totalVolume() }}</td>
                                <td class="px-4 py-3 text-sm space-x-2 whitespace-nowrap">
                                    <a href="{{ route('kua-daily.edit', $item) }}" class="text-blue-600 hover:underline dark:text-blue-400">Edit</a>
                                    <form action="{{ route('kua-daily.destroy', $item) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus data harian tanggal {{ tanggal_indonesia($item->tanggal, 'd F Y') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline dark:text-red-400">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 3 }}" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada data harian pada {{ tanggal_indonesia(now()->startOfMonth()->month($month)->year($year), 'F Y') }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
