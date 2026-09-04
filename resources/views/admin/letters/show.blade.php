<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Detail Surat</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded-md text-sm">
                    <strong>Periksa kembali:</strong>
                    <ul class="list-disc ml-4 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4 flex items-center gap-3">
                @php
                    $color = match ($letter->status) {
                        'draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:text-gray-500 dark:text-gray-300 dark:text-gray-500',
                        'diajukan' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'disetujui' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                        'terbit' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                        'ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                    };
                @endphp
                <span class="px-3 py-1 text-sm rounded-full {{ $color }}">{{ \App\Models\Letter::statuses()[$letter->status] }}</span>
                @if ($letter->nomor)
                    <span class="text-sm font-mono text-gray-600 dark:text-gray-300 dark:text-gray-500">{{ $letter->nomor }}</span>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Jenis Surat:</span> <span class="font-medium">{{ $letter->letterType->name }}</span></div>
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Perihal:</span> <span class="font-medium">{{ $letter->perihal }}</span></div>
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Nomor Surat:</span> <span class="font-medium">{{ $letter->nomor ?? '—' }}</span></div>
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Tanggal Surat:</span> <span class="font-medium">{{ $letter->tanggal_surat ? $letter->tanggal_surat->format('d M Y') : '—' }}</span></div>
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Dibuat oleh:</span> {{ $letter->creator?->name ?? '—' }} ({{ $letter->created_at->format('d M Y H:i') }})</div>
                    <div><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Disetujui oleh:</span> {{ $letter->approver?->name ?? '—' }} {{ $letter->approved_at ? '(' . $letter->approved_at->format('d M Y H:i') . ')' : '' }}</div>
                    @if ($letter->keterangan)
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Catatan:</span> <span class="text-red-600 dark:text-red-400">{{ $letter->keterangan }}</span></div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Preview Surat</h3>
                <iframe src="{{ route('letters.preview', $letter) }}" title="Preview surat"
                        class="w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                        style="border:0; height:80vh; min-height:600px;"></iframe>
            </div>

            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Data Surat</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @foreach ($letter->letterType->fields ?? [] as $field)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $field['label'] }}</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-100">{{ $letter->data[$field['name']] ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Baris Atas Surat</h3>
                <div class="text-sm text-gray-800 dark:text-gray-100 bg-gray-50 dark:bg-gray-700/40 rounded-md p-3">{!! $letter->renderHeader() !!}</div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($letter->status === \App\Models\Letter::STATUS_DRAFT)
                    <form method="POST" action="{{ route('letters.ajukan', $letter) }}">
                        @csrf
                        <x-primary-button>Ajukan ke Kepala KUA</x-primary-button>
                    </form>
                    <a href="{{ route('letters.edit', $letter) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                @endif

                @if ($letter->status === \App\Models\Letter::STATUS_DIAJUKAN && (auth()->user()->isKepala() || auth()->user()->isOperator() || auth()->user()->isSuperadmin()))
                    <form method="POST" action="{{ route('letters.setujui', $letter) }}">
                        @csrf
                        <x-primary-button>Setujui</x-primary-button>
                    </form>
                    <a href="{{ route('letters.reject', $letter) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">Tolak</a>
                @endif

                @if ($letter->status === \App\Models\Letter::STATUS_DISETUJUI)
                    <form method="POST" action="{{ route('letters.terbitkan', $letter) }}">
                        @csrf
                        <x-primary-button>Terbitkan</x-primary-button>
                    </form>
                    <a href="{{ route('letters.edit', $letter) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                @endif

                @if ($letter->status === \App\Models\Letter::STATUS_TERBIT)
                    <a href="{{ route('letters.pdf', $letter) }}"
                       class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-500">
                        Unduh PDF
                    </a>
                @endif

                <a href="{{ route('letters.index') }}" class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-500 hover:underline">Kembali ke arsip</a>
            </div>
        </div>
    </div>
</x-app-layout>
