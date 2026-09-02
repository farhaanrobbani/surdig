<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $hour = (int) now()->format('H');
                $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
            @endphp
            <div class="mb-6 flex items-center gap-3">
                @if ($user->fotoUrl())
                    <img src="{{ $user->fotoUrl() }}" alt="Foto profil"
                         class="h-9 w-9 sm:h-10 sm:w-10 md:h-14 md:w-14 shrink-0 rounded-full object-cover border-2 border-teal-100 dark:border-teal-800" />
                @else
                    <div class="h-9 w-9 sm:h-10 sm:w-10 md:h-14 md:w-14 shrink-0 rounded-full bg-teal-100 dark:bg-teal-900 flex items-center justify-center text-sm sm:text-base md:text-lg font-bold text-teal-700 dark:text-teal-300">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 truncate">
                        {{ $greeting }}, {{ $user->name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Selamat datang di dashboard Surat Digital KUA. Berikut ringkasan aktivitas Anda hari ini.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Surat</div>
                    <div class="text-3xl font-bold text-gray-800 mt-1 dark:text-gray-100">{{ $stats['total_surat'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Surat Terbit Bulan Ini</div>
                    <div class="text-3xl font-bold text-gray-800 mt-1 dark:text-gray-100">{{ $stats['surat_bulan_ini'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu Persetujuan</div>
                    <div class="text-3xl font-bold {{ $stats['menunggu_persetujuan'] ? 'text-yellow-600' : 'text-gray-800 dark:text-gray-100' }} mt-1">{{ $stats['menunggu_persetujuan'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Permohonan Baru</div>
                    <div class="text-3xl font-bold {{ $stats['permohonan_baru'] ? 'text-blue-600' : 'text-gray-800 dark:text-gray-100' }} mt-1">{{ $stats['permohonan_baru'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Pengumuman Nikah</div>
                    <div class="text-3xl font-bold text-gray-800 mt-1 dark:text-gray-100">{{ $statsNikah['total'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Akad Bulan Ini</div>
                    <div class="text-3xl font-bold text-teal-600 mt-1">{{ $statsNikah['bulan_ini'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Akad Hari Ini</div>
                    <div class="text-3xl font-bold text-teal-600 mt-1">{{ $statsNikah['hari_ini'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Akad Besok</div>
                    <div class="text-3xl font-bold text-gray-800 mt-1 dark:text-gray-100">{{ $statsNikah['besok'] }}</div>
                </div>
            </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-800 mb-4 dark:text-gray-100">Surat per Status</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($perStatus as $item)
                            <li class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">{{ $item['label'] }}</span>
                                <span class="font-semibold dark:text-gray-100">{{ $item['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <h3 class="font-semibold text-gray-800 mt-6 mb-3 dark:text-gray-100">Surat per Jenis (Top 5)</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($perJenis as $type)
                            <li class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">{{ $type->name }}</span>
                                <span class="font-semibold dark:text-gray-100">{{ $type->letters_count }}</span>
                            </li>
                        @empty
                            <li class="text-gray-400 dark:text-gray-500">Belum ada data.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Surat Terbaru</h3>
                        <a href="{{ route('letters.index') }}" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Lihat semua</a>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($suratTerbaru as $letter)
                            <li class="py-3">
                                <a href="{{ route('letters.show', $letter) }}" class="text-sm font-medium text-gray-800 hover:text-blue-600 dark:text-gray-100">{{ $letter->perihal }}</a>
                                <div class="text-xs text-gray-500 mt-0.5 dark:text-gray-400">
                                    {{ $letter->letterType->name }} &bull; {{ $letter->created_at->format('d M Y') }}
                                    @if ($letter->nomor)<span class="font-mono"> &bull; {{ $letter->nomor }}</span>@endif
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-400 dark:text-gray-500">Belum ada surat.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Permohonan Terbaru</h3>
                        <a href="{{ route('submissions.index') }}" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Lihat semua</a>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($permohonanTerbaru as $submission)
                            <li class="py-3">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-medium text-gray-800 hover:text-blue-600 dark:text-gray-100">{{ $submission->nama_pemohon }}</a>
                                <div class="text-xs text-gray-500 mt-0.5 dark:text-gray-400">
                                    {{ $submission->letterType->name }} &bull; {{ $submission->created_at->format('d M Y H:i') }}
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-400 dark:text-gray-500">Belum ada permohonan.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
