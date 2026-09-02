<?php

namespace App\Http\Controllers;

use App\Models\KuaActivityTheme;
use App\Models\KuaDailyData;
use App\Models\StaffActivity;
use App\Models\User;
use App\Models\UserActivityTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = min(max($request->integer('bulan', now()->month), 1), 12);
        $year = min(max($request->integer('tahun', now()->year), 2000), 2100);

        $query = StaffActivity::query()->with('user');

        if (! $user->canManageContent()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id') && $request->integer('user_id') !== 0) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $activities = $query->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $dailyData = KuaDailyData::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get()
            ->keyBy(fn ($item) => $item->tanggal);

        $templates = $user->activityTemplates->mapWithKeys(
            fn (UserActivityTemplate $template) => [
                $template->activity_type_key => [
                    'kegiatan' => $template->kegiatan,
                    'pekerjaan' => $template->pekerjaan,
                ],
            ]
        );

        return view('staff-activities.index', [
            'activities' => $activities,
            'dailyData' => $dailyData,
            'dailyMap' => $dailyData->map(fn ($item) => $item->data ?? [])->toArray(),
            'templates' => $templates,
            'columns' => KuaActivityTheme::activeList(),
            'users' => $user->canManageContent() ? User::orderBy('name')->get() : collect(),
            'selectedUserId' => $user->canManageContent() ? $request->integer('user_id') : null,
            'currentUser' => $user,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.tanggal' => ['required', 'date'],
            'items.*.kegiatan' => ['required', 'string', 'max:1000'],
            'items.*.pekerjaan' => ['required', 'string', 'max:1000'],
            'items.*.activity_type_key' => ['nullable', 'string', 'max:100', Rule::in($this->allowedActivityKeys())],
            'items.*.total_jumlah' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'items.*.save_template' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $count = 0;

        foreach ($data['items'] as $item) {
            StaffActivity::create([
                'user_id' => $user->id,
                'tanggal' => $item['tanggal'],
                'kegiatan' => $item['kegiatan'],
                'pekerjaan' => $item['pekerjaan'],
                'activity_type_key' => $item['activity_type_key'] ?: null,
                'total_jumlah' => $this->resolveTotal($item),
            ]);

            if (! empty($item['save_template']) && ! empty($item['activity_type_key'])) {
                $user->activityTemplates()->updateOrCreate(
                    ['activity_type_key' => $item['activity_type_key']],
                    ['kegiatan' => $item['kegiatan'], 'pekerjaan' => $item['pekerjaan']]
                );
            }

            $count++;
        }

        $message = "{$count} kegiatan berhasil ditambahkan ke laporan.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()->with('success', $message);
    }

    public function edit(Request $request, StaffActivity $kegiatan): View
    {
        $this->authorizeActivity($request, $kegiatan);

        return view('staff-activities.edit', [
            'activity' => $kegiatan,
            'columns' => KuaActivityTheme::activeList(),
            'daily' => KuaDailyData::where('tanggal', $kegiatan->tanggal)->first(),
        ]);
    }

    public function update(Request $request, StaffActivity $kegiatan): RedirectResponse
    {
        $this->authorizeActivity($request, $kegiatan);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'kegiatan' => ['required', 'string', 'max:1000'],
            'pekerjaan' => ['required', 'string', 'max:1000'],
            'activity_type_key' => ['nullable', 'string', 'max:100', Rule::in($this->allowedActivityKeys())],
            'total_jumlah' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $data['total_jumlah'] = $this->resolveTotal($data);

        $kegiatan->update($data);

        return redirect()->route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, StaffActivity $kegiatan): RedirectResponse
    {
        $this->authorizeActivity($request, $kegiatan);

        $kegiatan->delete();

        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function resolveTotal(array $item): int
    {
        $key = $item['activity_type_key'] ?? null;

        if ($key === 'libur') {
            return 0;
        }

        if ($key) {
            $daily = KuaDailyData::where('tanggal', $item['tanggal'])->first();

            if ($daily && $daily->value($key) !== null) {
                return $daily->value($key);
            }
        }

        return (int) ($item['total_jumlah'] ?? 1);
    }

    private function allowedActivityKeys(): array
    {
        return [
            'libur',
            'lainnya',
            ...array_keys(KuaActivityTheme::activeList()),
        ];
    }

    private function authorizeActivity(Request $request, StaffActivity $activity): void
    {
        abort_unless(
            $request->user()->canManageContent() || $activity->user_id === $request->user()->id,
            403
        );
    }
}
