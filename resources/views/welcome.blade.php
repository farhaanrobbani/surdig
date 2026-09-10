<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $kua['instansi'] ?? config('app.name', 'Surat Digital KUA') }} — {{ $kua['kecamatan'] ? 'Kecamatan '.$kua['kecamatan'] : 'Layanan Surat Online' }}</title>

        <link rel="icon" href="{{ \App\Models\KuaSetting::logoUrl() ?: asset('favicon.ico') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-teal-50 via-emerald-50 to-white text-[#1b1b18] font-sans antialiased">
        @include('partials.public-header')

        <main class="animate-fade-up">
            @php($hasBg = ! empty($kua['bg_url']))
            <section @if ($hasBg) style="background-image: url('{{ $kua['bg_url'] }}')" @endif
                     class="relative @if ($hasBg) bg-cover bg-center @else bg-gradient-to-br from-teal-50 via-emerald-50 to-white @endif">
                @if ($hasBg)
                    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/70"></div>
                @endif
                <div class="relative mx-auto max-w-5xl px-6 pb-16 pt-16 text-center sm:pt-20">
                    <p class="text-sm font-medium uppercase tracking-widest {{ $hasBg ? 'text-teal-100' : 'text-teal-700' }}">
                        {{ $kua['kecamatan'] ? 'Kantor Urusan Agama Kecamatan '.$kua['kecamatan'] : 'Kantor Urusan Agama' }}
                    </p>
                    <h1 class="mx-auto mt-3 max-w-3xl text-4xl font-bold leading-tight sm:text-5xl {{ $hasBg ? 'text-white' : '' }}">
                        {!! ! empty($kua['hero_judul']) ? nl2br(e($kua['hero_judul'])) : 'Layanan Surat Digital<br>Tanpa Antre, Kapan Saja' !!}
                    </h1>
                    <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed {{ $hasBg ? 'text-white/85' : 'text-[#1b1b1870]' }}">
                        {{ ! empty($kua['hero_subjudul']) ? $kua['hero_subjudul'] : 'Ajukan permohonan surat keterangan dan surat pengantar secara online.
Pihak KUA akan memverifikasi, menerbitkan, dan menandatangani surat Anda secara digital.' }}
                    </p>
                </div>
            </section>

            @if (! empty($kua['hero_url']))
                <img src="{{ $kua['hero_url'] }}" alt="Banner {{ $kua['instansi'] ?? 'KUA' }}"
                     class="mx-auto mt-8 max-h-72 w-full max-w-4xl rounded-xl border border-teal-100 object-cover shadow-sm" />
            @endif

            @if ($marriageAnnouncements->isNotEmpty())
                <section class="mx-auto max-w-5xl px-6 pt-12 pb-10">
                    @include('partials.ringkasan-jadwal', ['announcements' => $marriageAnnouncements, 'title' => 'Jadwal Pelaksanaan Nikah'])
                    <div class="mt-6 text-center">
                        <a href="{{ kua_navbar_page_url('pengumuman-nikah', '/pengumuman-nikah') }}"
                           class="text-sm font-medium text-teal-700 hover:underline">Lihat Daftar Lengkap Pengumuman Nikah →</a>
                    </div>
                </section>
            @endif

            @if ($services->isNotEmpty())
                <section class="mx-auto max-w-5xl px-6 pt-8 pb-8">
                    <h2 class="text-center text-xl font-bold">Layanan Kami</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($services as $service)
                            <a href="{{ $service->url ? url($service->url) : '#' }}"
                               class="rounded-lg border border-teal-100 bg-white p-5 shadow-sm transition hover:border-teal-300">
                                <div class="flex items-center gap-3">
                                    <span class="text-teal-700">@include('partials.service-icon', ['icon' => $service->icon, 'class' => 'h-7 w-7'])</span>
                                    <h3 class="font-semibold text-teal-900">{{ $service->label }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($announcements->isNotEmpty())
                <section class="mx-auto max-w-5xl px-6 pt-8 pb-16">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">{{ kua_navbar_page_label('pengumuman', 'Berita') }} Terbaru</h2>
                        <a href="{{ kua_navbar_page_url('pengumuman') }}" class="text-sm font-medium text-teal-700 hover:underline">
                            Lihat Semua {{ kua_navbar_page_label('pengumuman', 'Berita') }}
                        </a>
                    </div>
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($announcements as $announcement)
                            <a href="{{ kua_navbar_page_url('pengumuman').'/'.$announcement->slug }}"
                               class="group overflow-hidden rounded-2xl border border-teal-100 bg-white shadow-sm transition hover:shadow-md">
                                <div class="flex h-40 items-center justify-center bg-teal-50 text-4xl">
                                    @if ($announcement->imageUrl())
                                        <img src="{{ $announcement->imageUrl() }}" alt="{{ $announcement->title }}"
                                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                    @else
                                        <svg class="h-10 w-10 text-teal-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 9l-3.75 3.75m0 0L11.25 16.5m-3.75-3.75h3m-6.75 2.25a3.75 3.75 0 013.75-3.75H5.25a3.75 3.75 0 013.75-3.75h4.5a3.75 3.75 0 013.75 3.75v2.25a3.75 3.75 0 01-3.75 3.75H9.75a3.75 3.75 0 01-3.75-3.75z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $announcement->category?->color() ?? 'bg-teal-100 text-teal-800' }}">{{ $announcement->category?->label() ?? 'Pengumuman' }}</span>
                                        <span class="shrink-0 text-xs text-slate-500">{{ tanggal_indonesia($announcement->published_at ?? $announcement->created_at, 'd M Y') }}</span>
                                    </div>
                                    <h3 class="mt-3 font-semibold leading-snug text-slate-900 group-hover:text-teal-700">{{ $announcement->title }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $announcement->excerpt() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        @include('partials.jadwal-sholat')

        @include('partials.public-footer')
    </body>
</html>
