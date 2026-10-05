<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KuaSetting;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Submission;
use App\Support\PdfSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterController extends Controller
{
    public function index(Request $request): View
    {
        $query = Letter::with(['letterType', 'creator'])->latest();

        if ($request->filled('jenis')) {
            $query->where('letter_type_id', $request->input('jenis'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('tahun')) {
            $tahun = (int) $request->input('tahun');
            $query->whereBetween('created_at', [$tahun . '-01-01 00:00:00', $tahun . '-12-31 23:59:59']);
        }
        if ($request->filled('cari')) {
            $cari = $request->input('cari');
            $query->where(function ($q) use ($cari) {
                $q->where('perihal', 'like', "%{$cari}%")
                    ->orWhere('nomor', 'like', "%{$cari}%");
            });
        }

        return view('admin.letters.index', [
            'letters' => $query->paginate(15)->withQueryString(),
            'letterTypes' => LetterType::orderBy('name')->get(),
            'statuses' => Letter::statuses(),
            'years' => Letter::query()->select('created_at')->pluck('created_at')->map(fn ($d) => substr((string) $d, 0, 4))->unique()->sortDesc()->values(),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedType = null;
        $data = [];

        if ($request->has('jenis')) {
            $selectedType = LetterType::where('code', $request->input('jenis'))->with('templates')->first();
            $data = old('data', []);
        }

        $dari = null;
        if ($request->filled('dari') && $selectedType) {
            $dari = Submission::find($request->input('dari'));
            if ($dari && $dari->letter_type_id === $selectedType->id) {
                foreach ($dari->data ?? [] as $key => $value) {
                    $data[$key] ??= $value;
                }
            }
        }

        return view('admin.letters.create', [
            'letterTypes' => LetterType::where('active', true)->orderBy('name')->get(),
            'selectedType' => $selectedType,
            'data' => $data,
            'dari' => $dari,
            'headerHtml' => old('header_html', Letter::defaultHeader()),
            'perihal' => old('perihal', $dari ? 'Permohonan Penerbitan ' . $selectedType->name : null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $letterType = LetterType::where('code', $request->input('jenis'))->firstOrFail();
        $validated = $this->validateDynamic($request, $letterType);

        $letter = Letter::create([
            'letter_type_id' => $letterType->id,
            'nomor' => $validated['nomor'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'perihal' => $validated['perihal'],
            'header_html' => $validated['header_html'],
            'tampilkan_tanggal' => $validated['tampilkan_tanggal'],
            'data' => $validated['data'],
            'status' => Letter::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        if ($request->filled('dari')) {
            $submission = Submission::find($request->input('dari'));
            if ($submission && $submission->letter_type_id === $letterType->id
                && $submission->status !== Submission::STATUS_DIPROSES) {
                $submission->update(['status' => Submission::STATUS_DIPROSES]);
            }
        }

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat draft berhasil dibuat.');
    }

    public function show(Letter $letter): View
    {
        $letter->load(['letterType', 'creator', 'approver']);

        return view('admin.letters.show', compact('letter'));
    }

    public function edit(Letter $letter): View
    {
        abort_unless(in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_DITOLAK, Letter::STATUS_DISETUJUI]), 403, 'Surat sudah diproses dan tidak bisa diubah.');

        return view('admin.letters.edit', [
            'letter' => $letter,
            'data' => $letter->data,
            'headerHtml' => old('header_html', $letter->header_html ?: Letter::defaultHeader()),
        ]);
    }

    public function update(Request $request, Letter $letter): RedirectResponse
    {
        abort_unless(in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_DITOLAK, Letter::STATUS_DISETUJUI]), 403, 'Surat sudah diproses dan tidak bisa diubah.');

        $validated = $this->validateDynamic($request, $letter->letterType);

        $letter->update([
            'nomor' => $validated['nomor'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'perihal' => $validated['perihal'],
            'header_html' => $validated['header_html'],
            'tampilkan_tanggal' => $validated['tampilkan_tanggal'],
            'data' => $validated['data'],
        ]);

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Letter $letter): RedirectResponse
    {
        $letter->delete();

        return redirect()->route('letters.index')
            ->with('success', 'Surat berhasil dihapus.');
    }

    public function ajukan(Letter $letter): RedirectResponse
    {
        abort_unless($letter->status === Letter::STATUS_DRAFT, 403, 'Hanya surat draft yang bisa diajukan.');

        $letter->update(['status' => Letter::STATUS_DIAJUKAN]);

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat diajukan untuk persetujuan Kepala KUA.');
    }

    public function setujui(Letter $letter): RedirectResponse
    {
        abort_unless($letter->status === Letter::STATUS_DIAJUKAN, 403, 'Hanya surat berstatus diajukan yang bisa disetujui.');

        $letter->update([
            'status' => Letter::STATUS_DISETUJUI,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat disetujui. Silakan terbitkan surat.');
    }

    public function reject(Letter $letter): View
    {
        abort_unless($letter->status === Letter::STATUS_DIAJUKAN, 403, 'Hanya surat berstatus diajukan yang bisa ditolak.');

        return view('admin.letters.reject', compact('letter'));
    }

    public function tolak(Request $request, Letter $letter): RedirectResponse
    {
        abort_unless($letter->status === Letter::STATUS_DIAJUKAN, 403, 'Hanya surat berstatus diajukan yang bisa ditolak.');

        $request->validate(['keterangan' => ['required', 'string', 'max:1000']]);

        $letter->update([
            'status' => Letter::STATUS_DITOLAK,
            'keterangan' => $request->input('keterangan'),
        ]);

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat ditolak dengan catatan.');
    }

    public function terbitkan(Letter $letter): RedirectResponse
    {
        abort_unless($letter->status === Letter::STATUS_DISETUJUI, 403, 'Hanya surat disetujui yang bisa diterbitkan.');

        if (! $letter->nomor) {
            return back()->withErrors(['nomor' => 'Nomor surat wajib diisi sebelum surat diterbitkan.']);
        }

        if (! $letter->tanggal_surat) {
            return back()->withErrors(['tanggal_surat' => 'Tanggal surat wajib diisi sebelum surat diterbitkan.']);
        }

        $letter->status = Letter::STATUS_TERBIT;
        $letter->save();

        return redirect()->route('letters.show', $letter)
            ->with('success', 'Surat diterbitkan dengan nomor ' . $letter->nomor . '.');
    }

    public function pdf(Letter $letter)
    {
        abort_unless($letter->status === Letter::STATUS_TERBIT, 403, 'Surat dapat diunduh setelah diterbitkan.');

        [$pdf, $fileName] = $this->buildPdf($letter);

        return $pdf->download($fileName);
    }

    public function preview(Letter $letter)
    {
        [$pdf, $fileName] = $this->buildPdf($letter);

        return $pdf->stream($fileName, ['Attachment' => false]);
    }

    private function buildPdf(Letter $letter): array
    {
        PdfSupport::registerArialFonts();

        $settingKeys = ['instansi', 'alamat', 'kecamatan', 'kabupaten', 'kode_pos', 'telepon', 'email',
            'kepala_nama', 'kepala_nip', 'kepala_pangkat', 'sk_kepala', 'kop_anchor', 'logo_path',
            'logo2_path', 'kop_logo', 'kop_teks', 'kop_ukuran_judul', 'kop_ukuran_sub', 'kop_ukuran_sub2', 'kop_ukuran_baris'];
        $settings = [];
        foreach ($settingKeys as $key) {
            $settings[$key] = KuaSetting::get($key) ?? '';
        }

        $pdf = Pdf::loadView('pdf.letter', [
            'letter' => $letter,
            'settings' => $settings,
            'body' => PdfSupport::resolveLocalImages($letter->renderBody()),
            'kopEnabled' => $letter->letterType?->kop_enabled ?? true,
            'kopLines' => PdfSupport::parseKopTeks($settings['kop_teks']),
            'kopFooter' => $letter->letterType?->kop_footer ?? null,
            'kopFooterEnabled' => $letter->letterType?->kop_footer_enabled ?? false,
            'kopSizes' => [
                'judul' => (float) ($settings['kop_ukuran_judul'] ?: 17),
                'sub' => (float) ($settings['kop_ukuran_sub'] ?: 13),
                'sub2' => (float) ($settings['kop_ukuran_sub2'] ?: 11.5),
                'baris' => (float) ($settings['kop_ukuran_baris'] ?: 10.5),
            ],
        ])->setPaper('a4');

        $fileName = ($letter->nomor ? str_replace('/', '-', $letter->nomor) : 'surat') . '.pdf';

        return [$pdf, $fileName];
    }

    private function validateDynamic(Request $request, LetterType $letterType): array
    {
        $rules = [
            'nomor' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'header_html' => ['nullable', 'string', 'max:65535'],
            'tampilkan_tanggal' => ['boolean'],
        ];

        $fields = $letterType->fields ?? [];
        foreach ($fields as $index => $field) {
            $name = 'data.' . $field['name'];
            $fieldRules = ['string', 'max:1000'];
            if (! empty($field['required'])) {
                $fieldRules[] = 'required';
            }
            $rules[$name] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $data = $request->input('data', []);
        $safeData = [];
        foreach ($fields as $field) {
            $safeData[$field['name']] = $data[$field['name']] ?? null;
        }

        return [
            'nomor' => $validated['nomor'] ?? null,
            'tanggal_surat' => $validated['tanggal_surat'] ?? null,
            'perihal' => $validated['perihal'],
            'header_html' => $validated['header_html'] ?? null,
            'tampilkan_tanggal' => $request->boolean('tampilkan_tanggal'),
            'data' => $safeData,
        ];
    }
}
