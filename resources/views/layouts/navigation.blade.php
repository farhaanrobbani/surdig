<aside :class="(sidebarOpen ? 'translate-x-0' : '-translate-x-full') + (sidebarCollapsed ? ' sidebar-collapsed lg:w-16' : ' lg:w-64')"
       class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-teal-700 transition-[transform,width] duration-200 ease-in-out lg:translate-x-0">
    <!-- Brand -->
    <div class="flex h-16 shrink-0 items-center border-b border-teal-600"
         :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-4'">
        <button type="button" @click="sidebarCollapsed = ! sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed ? '1' : '0')"
                class="hidden shrink-0 rounded-md p-1.5 text-teal-200 transition hover:bg-teal-800/40 hover:text-white lg:block"
                aria-label="Mini sidebar" title="Mini sidebar">
            <svg x-show="!sidebarCollapsed" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="sidebarCollapsed" x-cloak class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>
        <div x-show="!sidebarCollapsed" class="flex min-w-0 items-center gap-2">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2">
                @if (\App\Models\KuaSetting::logoUrl())
                    <img src="{{ \App\Models\KuaSetting::logoUrl() }}" alt="Logo {{ kua_setting('instansi', 'KUA') }}"
                         class="h-9 w-9 shrink-0 rounded-md bg-white p-0.5 object-contain" />
                @else
                    <x-application-logo class="block h-9 w-auto shrink-0 fill-current text-white" />
                @endif
                <span class="truncate text-sm font-semibold text-white">{{ kua_setting('instansi', config('app.name')) }}</span>
            </a>
            <button @click="sidebarOpen = false" class="p-1 text-teal-200 hover:text-white lg:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation Links -->
    @php($suratGroupActive = request()->routeIs('letters.*') || request()->routeIs('submissions.*') || request()->routeIs('letter-types.*') || request()->routeIs('letter-templates.*'))

    <nav class="flex-1 space-y-1 px-3 py-4" :class="sidebarCollapsed ? 'overflow-visible' : 'overflow-y-auto'">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="nav-text">{{ __('Dashboard') }}</span>
        </x-nav-link>

        <!-- Group: Surat -->
        <div x-data="{ open: {{ $suratGroupActive ? 'true' : 'false' }} }" class="nav-group">
            <button type="button" @click="open = ! open"
                    :class="open || {{ $suratGroupActive ? 'true' : 'false' }} ? 'bg-teal-800/70 text-white' : 'text-teal-100 hover:bg-teal-800/40 hover:text-white'"
                    class="nav-group-button flex w-full items-center justify-between rounded-md px-4 py-2.5 text-sm font-medium transition duration-150 ease-in-out">
                <span class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="nav-text">{{ __('Surat') }}</span>
                </span>
                <svg :class="open ? 'rotate-180' : ''" class="nav-chevron h-4 w-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-cloak class="nav-group-content mt-1 space-y-1">
                <div class="nav-group-inner ms-4">
                    <x-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')">
                        <span class="nav-text">{{ __('Surat') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('submissions.index')" :active="request()->routeIs('submissions.*')">
                        <span class="nav-text">{{ __('Permohonan') }}</span>
                    </x-nav-link>
                    @if (Auth::user()->canManageContent())
                        <x-nav-link :href="route('letter-types.index')" :active="request()->routeIs('letter-types.*')">
                            <span class="nav-text">{{ __('Jenis Surat') }}</span>
                        </x-nav-link>
                        <x-nav-link :href="route('letter-templates.index')" :active="request()->routeIs('letter-templates.*')">
                            <span class="nav-text">{{ __('Template') }}</span>
                        </x-nav-link>
                    @endif
                </div>
            </div>
        </div>

        <!-- Group: Laporan Kinerja -->
        @php($lapkinGroupActive = request()->routeIs('kegiatan.*') || request()->routeIs('kua-daily.*'))
        <div x-data="{ open: {{ $lapkinGroupActive ? 'true' : 'false' }} }" class="nav-group">
            <button type="button" @click="open = ! open"
                    :class="open || {{ $lapkinGroupActive ? 'true' : 'false' }} ? 'bg-teal-800/70 text-white' : 'text-teal-100 hover:bg-teal-800/40 hover:text-white'"
                    class="nav-group-button flex w-full items-center justify-between rounded-md px-4 py-2.5 text-sm font-medium transition duration-150 ease-in-out">
                <span class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span class="nav-text">{{ __('Laporan Kinerja') }}</span>
                </span>
                <svg :class="open ? 'rotate-180' : ''" class="nav-chevron h-4 w-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-cloak class="nav-group-content mt-1 space-y-1">
                <div class="nav-group-inner ms-4">
                    @if (Auth::user()->canManageContent())
                        <x-nav-link :href="route('kua-daily.index')" :active="request()->routeIs('kua-daily.*')">
                            <span class="nav-text">{{ __('Master Data Harian') }}</span>
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('kegiatan.index')" :active="request()->routeIs('kegiatan.index', 'kegiatan.edit')">
                        <span class="nav-text">{{ __('Kegiatan Harian') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('kegiatan.export.index')" :active="request()->routeIs('kegiatan.export.*')">
                        <span class="nav-text">{{ __('Export') }}</span>
                    </x-nav-link>
                </div>
            </div>
        </div>

        @if (Auth::user()->canManageContent())
            <x-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span class="nav-text">{{ __('Posts') }}</span>
            </x-nav-link>

            <x-nav-link :href="route('download-items.index')" :active="request()->routeIs('download-items.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span class="nav-text">{{ __('Download Center') }}</span>
            </x-nav-link>

            <x-nav-link :href="route('marriage-announcements.index')" :active="request()->routeIs('marriage-announcements.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span class="nav-text">{{ __('Pengumuman Nikah') }}</span>
            </x-nav-link>

            @if (Auth::user()->isSuperadmin())
                <x-nav-link :href="route('pages.index')" :active="request()->routeIs('pages.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="nav-text">{{ __('Page') }}</span>
                </x-nav-link>
            @endif

            <x-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="nav-text">{{ __('Daftar Staf') }}</span>
            </x-nav-link>

            @if (Auth::user()->isSuperadmin())
                <x-nav-link :href="route('navbar.index')" :active="request()->routeIs('navbar.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <span class="nav-text">{{ __('Navbar') }}</span>
                </x-nav-link>
            @endif
        @endif

        <x-nav-link :href="route('kritik-saran.index')" :active="request()->routeIs('kritik-saran.*')">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="nav-text">{{ __('Kritik & Saran') }}</span>
        </x-nav-link>

        @if (Auth::user()->isSuperadmin())
            <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="nav-text">{{ __('Akun') }}</span>
            </x-nav-link>
        @endif

        @if (Auth::user()->isSuperadmin())
            <!-- Pengaturan Web (menu utama) -->
            <x-nav-link :href="route('kua-settings.edit')" :active="request()->routeIs('kua-settings.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </svg>
                <span class="nav-text">{{ __('Pengaturan Web') }}</span>
            </x-nav-link>
        @endif
    </nav>

    <!-- User & Session -->
    <div class="shrink-0 border-t border-teal-600 p-3">
        <div class="nav-text px-3 py-2">
            <div class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</div>
            <div class="truncate text-xs text-teal-200">{{ Auth::user()->email }}</div>
        </div>
        <div class="space-y-1">
            <button type="button"
                    x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    @click="dark = ! dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                    class="nav-btn flex w-full items-center gap-3 rounded-md px-4 py-2.5 text-sm font-medium text-teal-100 transition hover:bg-teal-800/40 hover:text-white">
                <template x-if="dark">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m6.364 1.636l-1.06 1.06M21 12h-1.5m-1.636 6.364l-1.06-1.06M12 19.5V21m-4.773-4.773l-1.06 1.06M4.5 12H3m4.773-4.773l-1.06-1.06M12 7.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9z" />
                    </svg>
                </template>
                <template x-if="! dark">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </template>
                <span class="nav-text" x-text="dark ? 'Mode Terang' : 'Mode Gelap'"></span>
            </button>
            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="nav-text">{{ __('Profile') }}</span>
            </x-nav-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-btn flex w-full items-center gap-3 rounded-md px-4 py-2.5 text-sm font-medium text-teal-100 transition hover:bg-teal-800/40 hover:text-white">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="nav-text">{{ __('Log Out') }}</span>
                </button>
            </form>
        </div>
    </div>
</aside>
