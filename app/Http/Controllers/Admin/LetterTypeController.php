<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterType;
use App\Models\Submission;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LetterTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.letter-types.index', [
            'letterTypes' => LetterType::withCount('letters')->orderByDesc('active')->orderBy('code')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.letter-types.create');
    }

    public function clone(LetterType $letterType): View
    {
        return view('admin.letter-types.create', ['source' => $letterType]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        LetterType::create($data);

        return redirect()->route('letter-types.index')
            ->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function edit(LetterType $letterType): View
    {
        return view('admin.letter-types.edit', compact('letterType'));
    }

    public function update(Request $request, LetterType $letterType): RedirectResponse
    {
        $data = $this->validateData($request, $letterType);

        $letterType->update($data);

        return redirect()->route('letter-types.index')
            ->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy(LetterType $letterType): RedirectResponse
    {
        if ($letterType->letters()->exists()) {
            return redirect()->route('letter-types.index')
                ->with('error', 'Jenis surat tidak bisa dihapus karena masih memiliki surat terkait.');
        }

        if (Submission::where('letter_type_id', $letterType->id)->exists()) {
            return redirect()->route('letter-types.index')
                ->with('error', 'Jenis surat tidak bisa dihapus karena masih memiliki permohonan terkait.');
        }

        if ($letterType->templates()->exists()) {
            return redirect()->route('letter-types.index')
                ->with('error', 'Jenis surat tidak bisa dihapus karena masih memiliki template terkait.');
        }

        $letterType->delete();

        return redirect()->route('letter-types.index')
            ->with('success', 'Jenis surat berhasil dihapus.');
    }

    private function validateData(Request $request, ?LetterType $letterType = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('letter_types', 'code')->ignore($letterType)],
            'name' => ['required', 'string', 'max:150'],
            'permohonan_judul' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permohonan_body' => ['nullable', 'string'],
            'permohonan_informasi' => ['nullable', 'string', 'max:2000'],
            'permohonan_fields' => ['nullable', 'array'],
            'permohonan_fields.*' => ['string', 'max:50'],
            'fields' => ['nullable', 'array'],
            'fields.*.name' => ['required', 'string', 'max:50', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.type' => ['required', 'in:text,textarea,date,select'],
            'fields.*.required' => ['nullable'],
            'fields.*.internal' => ['nullable'],
            'fields.*.options' => ['nullable', 'array'],
            'active' => ['sometimes', 'boolean'],
            'publik' => ['sometimes', 'boolean'],
            'kop_footer' => ['nullable', 'string', 'max:5000'],
            'kop_footer_enabled' => ['sometimes', 'boolean'],
        ]);

        if (! blank($data['permohonan_body'] ?? null)) {
            $data['permohonan_body'] = HtmlSanitizer::normalize($data['permohonan_body']);
        } else {
            $data['permohonan_body'] = null;
        }

        if (isset($data['fields'])) {
            $data['fields'] = LetterType::normalizeFieldOptions($data['fields']);
        }

        $data['active'] = $request->boolean('active');
        $data['publik'] = $request->boolean('publik');
        $data['kop_footer_enabled'] = $request->boolean('kop_footer_enabled');

        return $data;
    }
}
