<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total_surat' => Letter::count(),
            'surat_bulan_ini' => Letter::where('status', Letter::STATUS_TERBIT)
                ->whereBetween('tanggal_surat', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'menunggu_persetujuan' => Letter::where('status', Letter::STATUS_DIAJUKAN)->count(),
            'permohonan_baru' => Submission::where('status', Submission::STATUS_BARU)->count(),
        ];

        $counts = Letter::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $perStatus = collect(Letter::statuses())
            ->map(function ($label, $key) use ($counts) {
                return [
                    'label' => $label,
                    'count' => $counts[$key] ?? 0,
                ];
            })
            ->values();

        $perJenis = LetterType::withCount('letters')->orderBy('letters_count', 'desc')->take(5)->get();

        return view('dashboard', [
            'user' => $request->user(),
            'stats' => $stats,
            'perStatus' => $perStatus,
            'perJenis' => $perJenis,
            'suratTerbaru' => Letter::with('letterType')->latest()->take(5)->get(),
            'permohonanTerbaru' => Submission::with('letterType')->latest()->take(5)->get(),
        ]);
    }
}
