<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Kelola Akun</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('user'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded-md text-sm">
                    {{ $errors->first('user') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('users.index') }}" class="flex gap-2 flex-wrap">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email..."
                           class="rounded-md border-gray-300 text-sm shadow-sm">
                    <select name="role" class="rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Semua role</option>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold uppercase tracking-widest dark:bg-gray-700 dark:border-gray-600">
                        Filter
                    </button>
                </form>
                <a href="{{ route('users.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Akun
                </a>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="text-xs text-gray-400 dark:text-gray-500">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $user->isSuperadmin() ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : ($user->isKepala() ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : ($user->isOperator() ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-300')) }}">
                                        {{ $roles[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $user->isActive() ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $user->isActive() ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    @if ($user->id !== auth()->id())
                                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                              data-confirm-name="{{ $user->name }}"
                                              onsubmit="return confirm('Hapus akun ' + this.dataset.confirmName + '?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 text-xs">Kelola di Profil</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Belum ada akun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
