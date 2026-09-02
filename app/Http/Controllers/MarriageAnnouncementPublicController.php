<?php

namespace App\Http\Controllers;

use App\Models\MarriageAnnouncement;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarriageAnnouncementPublicController extends Controller
{
    public function index(): View
    {
        return view('public.marriage-announcements.index', [
            'announcements' => MarriageAnnouncement::query()->aktif()->get(),
            'page' => $this->page(),
        ]);
    }

    public function show(MarriageAnnouncement $announcement): View
    {
        abort_unless($announcement->active, 404);

        return view('public.marriage-announcements.show', [
            'announcement' => $announcement,
            'page' => $this->page(),
        ]);
    }

    public function arsip(Request $request): View
    {
        $query = MarriageAnnouncement::query()->berlalu();

        if ($request->filled('dari')) {
            $query->whereDate('tanggal_akad', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_akad', '<=', $request->input('sampai'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn (Builder $qq) =>
                $qq->where('no_pendaftaran', 'like', "%{$q}%")
                   ->orWhere('nama_pria', 'like', "%{$q}%")
                   ->orWhere('nama_wanita', 'like', "%{$q}%")
                   ->orWhere('bin_pria', 'like', "%{$q}%")
                   ->orWhere('binti_wanita', 'like', "%{$q}%")
            );
        }

        return view('public.marriage-announcements.archive', [
            'announcements' => $query->paginate(20)->withQueryString(),
            'page' => $this->page(),
        ]);
    }

    private function page(): ?Page
    {
        try {
            return Page::active()->where('key', 'pengumuman-nikah')->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
