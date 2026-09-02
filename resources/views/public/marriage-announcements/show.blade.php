@extends('layouts.public')

@php
    $shareUrl = route('pengumuman-nikah.show', $announcement);
    $shareText = 'Pengumuman Kehendak Nikah: '.$announcement->namaLengkapPria().' & '.$announcement->namaLengkapWanita();
    $shareDescription = 'Pendaftaran '.($announcement->no_pendaftaran ?: '—').' - Wali Nikah: '.($announcement->status_wali ?: '—').' - Akad: '.tanggal_indonesia($announcement->tanggal_akad).' - '.($announcement->tempat_nikah ?: '—');
@endphp

@section('title', 'Pengumuman Kehendak Nikah — '.kua_setting('instansi', 'Surat Digital KUA'))

@section('metaDescription', $shareDescription)

@push('head')
    <meta property="og:title" content="{{ $shareText }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:site_name" content="{{ kua_setting('instansi', 'Surat Digital KUA') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $shareText }}">
    <meta name="twitter:description" content="{{ $shareDescription }}">
@endpush

@section('content')
    <section class="mx-auto max-w-3xl px-6 pb-16 pt-12">
        <nav class="text-sm text-teal-700">
            <a href="{{ route('pengumuman-nikah.index') }}" class="hover:underline">← Daftar Pengumuman Kehendak Nikah</a>
        </nav>

        <div class="mt-6">
            <h1 class="text-center text-2xl font-bold">Pengumuman Kehendak Nikah</h1>
            <p class="mx-auto mt-2 max-w-2xl text-center text-sm text-[#1b1b1870]">
                {{ $page->description ?? 'Berdasarkan Pasal 9 PMA No. 30 Tahun 2024, kami mengumumkan kehendak nikah calon pasangan berikut.' }}
            </p>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-teal-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-teal-100 bg-teal-50/60 px-5 py-3">
                <p class="text-center text-xs font-semibold uppercase tracking-wide text-teal-800">
                    KUA {{ kua_setting('kecamatan', '') }}{{ kua_setting('kabupaten') ? ', '.kua_setting('kabupaten') : '' }}
                </p>
                <span class="rounded-full bg-teal-700 px-2.5 py-0.5 text-xs font-semibold text-white">{{ tanggal_indonesia($announcement->tanggal_akad) }}</span>
            </div>

            <dl class="divide-y divide-teal-50 text-sm">
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">No. Pendaftaran</dt>
                    <dd class="font-mono text-teal-900 sm:col-span-2">{{ $announcement->no_pendaftaran ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">Calon Mempelai Pria</dt>
                    <dd class="text-teal-900 sm:col-span-2">
                        <span class="block font-semibold">{{ $announcement->namaLengkapPria() }}</span>
                        @if ($announcement->alamat_pria)
                            <span class="mt-0.5 block text-xs text-[#1b1b1870]">{{ $announcement->alamat_pria }}</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">Calon Mempelai Wanita</dt>
                    <dd class="text-teal-900 sm:col-span-2">
                        <span class="block font-semibold">{{ $announcement->namaLengkapWanita() }}</span>
                        @if ($announcement->alamat_wanita)
                            <span class="mt-0.5 block text-xs text-[#1b1b1870]">{{ $announcement->alamat_wanita }}</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">Wali Nikah</dt>
                    <dd class="text-teal-900 sm:col-span-2">{{ $announcement->status_wali ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">Tanggal Akad</dt>
                    <dd class="text-teal-900 sm:col-span-2">{{ tanggal_indonesia($announcement->tanggal_akad) }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-[#1b1b1870]">Tempat</dt>
                    <dd class="text-teal-900 sm:col-span-2">{{ $announcement->tempat_nikah ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-[#1b1b1870]">Bagikan:</span>
            <a href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 rounded-md border border-teal-100 bg-white px-3 py-1.5 text-sm font-medium text-teal-800 transition hover:bg-teal-50"
               aria-label="Bagikan ke WhatsApp">
                @include('partials.sosmed-icon', ['platform' => 'whatsapp', 'class' => 'h-4 w-4'])
                WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 rounded-md border border-teal-100 bg-white px-3 py-1.5 text-sm font-medium text-teal-800 transition hover:bg-teal-50"
               aria-label="Bagikan ke Facebook">
                @include('partials.sosmed-icon', ['platform' => 'facebook', 'class' => 'h-4 w-4'])
                Facebook
            </a>
            <button type="button" x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('{{ $shareUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
                    class="inline-flex items-center gap-1.5 rounded-md border border-teal-100 bg-white px-3 py-1.5 text-sm font-medium text-teal-800 transition hover:bg-teal-50"
                    aria-label="Salin tautan">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
                <span x-text="copied ? 'Tersalin!' : 'Salin Tautan'"></span>
            </button>
            <button type="button" x-data="{ copied: false }"
                    @click="if (navigator.share) { navigator.share({ title: {{ json_encode($shareText) }}, url: '{{ $shareUrl }}' }).catch(() => {}); } else { navigator.clipboard.writeText('{{ $shareUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {}); }"
                    class="inline-flex items-center gap-1.5 rounded-md border border-teal-100 bg-white px-3 py-1.5 text-sm font-medium text-teal-800 transition hover:bg-teal-50"
                    aria-label="Bagikan">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
                <span x-text="copied ? 'Tersalin!' : 'Bagikan'"></span>
            </button>
        </div>

        <a href="{{ route('pengumuman-nikah.index') }}"
           class="mt-10 inline-block rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-600">
            &larr; Kembali ke Daftar Pengumuman
        </a>
    </section>
@endsection