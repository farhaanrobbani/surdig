<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarriageAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarriageAnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.marriage-announcements.index', [
            'announcements' => MarriageAnnouncement::query()
                ->orderByDesc('tanggal_akad')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.marriage-announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        MarriageAnnouncement::create($this->validated($request));

        return redirect()->route('marriage-announcements.index')
            ->with('success', 'Pengumuman kehendak nikah berhasil ditambahkan.');
    }

    public function edit(MarriageAnnouncement $marriageAnnouncement): View
    {
        return view('admin.marriage-announcements.edit', [
            'announcement' => $marriageAnnouncement,
        ]);
    }

    public function update(Request $request, MarriageAnnouncement $marriageAnnouncement): RedirectResponse
    {
        $marriageAnnouncement->update($this->validated($request, $marriageAnnouncement->id));

        return redirect()->route('marriage-announcements.index')
            ->with('success', 'Pengumuman kehendak nikah berhasil diperbarui.');
    }

    public function destroy(MarriageAnnouncement $marriageAnnouncement): RedirectResponse
    {
        $marriageAnnouncement->delete();

        return redirect()->route('marriage-announcements.index')
            ->with('success', 'Pengumuman kehendak nikah berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId
            ? 'unique:marriage_announcements,no_pendaftaran,'.$ignoreId
            : 'unique:marriage_announcements,no_pendaftaran';

        $data = $request->validate([
            'no_pendaftaran' => ['nullable', 'string', 'max:80', $uniqueRule],
            'nama_pria' => ['required', 'string', 'max:150'],
            'bin_pria' => ['nullable', 'string', 'max:120'],
            'alamat_pria' => ['nullable', 'string', 'max:255'],
            'nama_wanita' => ['required', 'string', 'max:150'],
            'binti_wanita' => ['nullable', 'string', 'max:120'],
            'alamat_wanita' => ['nullable', 'string', 'max:255'],
            'tanggal_akad' => ['required', 'date'],
            'tempat_nikah' => ['nullable', 'string', 'max:255'],
            'status_wali' => ['nullable', 'string', 'max:150'],
            'active' => ['nullable', 'boolean'],
        ]);

        foreach (['no_pendaftaran', 'nama_pria', 'bin_pria', 'alamat_pria', 'nama_wanita', 'binti_wanita', 'alamat_wanita', 'tempat_nikah', 'status_wali'] as $field) {
            $data[$field] = isset($data[$field]) ? trim($data[$field]) : null;
        }

        $data['active'] = $request->boolean('active');

        return $data;
    }
}
