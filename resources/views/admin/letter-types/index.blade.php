<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Jenis Surat</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('letter-types.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Jenis Surat
                </a>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Field</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Publik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($letterTypes as $type)
                            <tr>
                                <td class="px-6 py-4 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $type->code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $type->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ count($type->fields ?? []) }} field</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $type->letters_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $type->publik ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:text-gray-500 dark:text-gray-300 dark:text-gray-500' }}">
                                        {{ $type->publik ? 'Publik' : 'Admin' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $type->active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:text-gray-500 dark:text-gray-300 dark:text-gray-500' }}">
                                        {{ $type->active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('letter-types.edit', $type) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                    <a href="{{ route('letter-types.clone', $type) }}" class="text-amber-600 dark:text-amber-400 hover:underline">Clone</a>
                                    <form action="{{ route('letter-types.destroy', $type) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus jenis surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Belum ada jenis surat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $letterTypes->links() }}</div>
        </div>
    </div>
</x-app-layout>
