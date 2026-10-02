<?php

namespace App\Http\Controllers;

use App\Models\KuaSetting;
use App\Models\StaffActivity;
use App\Models\User;
use App\Support\LapkinWordExport;
use App\Support\PdfSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StaffExportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = $this->month($request);
        $year = $this->year($request);

        return view('staff-activities.export', [
            'users' => $user->canManageContent() ? User::orderBy('name')->get() : collect(),
            'selectedUserId' => $user->canManageContent() ? $request->integer('user_id') : null,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function laporanKinerja(Request $request)
    {
        $user = $this->resolveExportUser($request);
        $month = $this->month($request);
        $year = $this->year($request);

        $customTanggal = trim((string) $request->string('tanggal_ttd', '')->limit(100));

        $data = [
            'user' => $user,
            'activities' => StaffActivity::query()
                ->where('user_id', $user->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get(),
            'monthName' => $this->monthName($month),
            'printDate' => $this->printDate($month, $year, $customTanggal),
            'pejabatPenilai' => $this->pejabatPenilai($user),
            'kop_anchor' => KuaSetting::get('kop_anchor', '1'),
            'fileName' => sprintf(
                'Laporan_Kinerja_%s_%s_%s',
                str_replace(' ', '_', $user->name),
                $this->monthName($month),
                $year
            ),
        ];

        if ($this->isWord($request)) {
            return LapkinWordExport::download('laporan', $data);
        }

        PdfSupport::registerArialFonts();

        return Pdf::loadView('pdf.laporan-kinerja', $data)->download($data['fileName'].'.pdf');
    }

    public function rekap(Request $request)
    {
        $user = $this->resolveExportUser($request);
        $month = $this->month($request);
        $year = $this->year($request);
        $totalHariKerja = min(max($request->integer('total_hari_kerja', 22), 0), 31);

        $customTanggal = trim((string) $request->string('tanggal_ttd', '')->limit(100));

        $data = [
            'user' => $user,
            'month' => $month,
            'year' => $year,
            'monthName' => $this->monthName($month),
            'instansi' => KuaSetting::get('instansi', $user->instansi) ?: '',
            'kota' => KuaSetting::get('kabupaten', '') ?: '',
            'totalHariKerja' => $totalHariKerja,
            'signatureDate' => $this->signatureDate($month, $year, $customTanggal),
            'pejabatPenilai' => $this->pejabatPenilai($user),
            'kepalaJabatan' => $user->isKepala()
                ? trim('Kepala Kemenag '.(KuaSetting::get('kabupaten', '') ?: ''))
                : trim('Kepala KUA '.KuaSetting::get('kecamatan', '')),
            'kop_anchor' => KuaSetting::get('kop_anchor', '1'),
            'fileName' => sprintf(
                'Rekap_Laporan_Kinerja_%s_%s_%s',
                str_replace(' ', '_', $user->name),
                $this->monthName($month),
                $year
            ),
        ];

        if ($this->isWord($request)) {
            return LapkinWordExport::download('rekap', $data);
        }

        PdfSupport::registerArialFonts();

        return Pdf::loadView('pdf.rekap-laporan-kinerja', $data)->download($data['fileName'].'.pdf');
    }

    private function isWord(Request $request): bool
    {
        return $request->string('format')->toString() === 'word';
    }

    private function resolveExportUser(Request $request): User
    {
        if (! $request->user()->canManageContent()) {
            return $request->user();
        }

        if (! $request->filled('user_id') || $request->integer('user_id') === 0) {
            throw new HttpResponseException(
                redirect()->back()->with('error', 'Pilih pegawai terlebih dahulu sebelum melakukan export.')
            );
        }

        $user = User::find($request->integer('user_id'));

        if (! $user) {
            abort(404);
        }

        return $user;
    }

    private function month(Request $request): int
    {
        return min(max($request->integer('bulan', now()->month), 1), 12);
    }

    private function year(Request $request): int
    {
        return min(max($request->integer('tahun', now()->year), 2000), 2100);
    }

    private function monthName(int $month): string
    {
        return tanggal_indonesia(now()->month($month), 'F');
    }

    private function printDate(int $month, int $year, string $custom = ''): string
    {
        return $custom !== '' ? $custom : tanggal_indonesia(Carbon::createFromDate($year, $month, 1)->endOfMonth(), 'j F Y');
    }

    private function signatureDate(int $month, int $year, string $custom = ''): string
    {
        $kota = KuaSetting::get('kabupaten', '') ?: '';
        $tanggal = $custom !== '' ? $custom : $this->printDate($month, $year);

        return trim(($kota !== '' ? $kota.', ' : '').$tanggal);
    }

    private function kepala(): array
    {
        return [
            'nama' => KuaSetting::get('kepala_nama', '') ?: '',
            'nip' => KuaSetting::get('kepala_nip', '') ?: '',
            'pangkat' => KuaSetting::get('kepala_pangkat', '') ?: '',
        ];
    }

    private function penandatanganKemenag(): array
    {
        return [
            'nama' => KuaSetting::get('kepala_kemenag_nama', '') ?: '',
            'nip' => KuaSetting::get('kepala_kemenag_nip', '') ?: '',
        ];
    }

    private function pejabatPenilai(User $user): array
    {
        return $user->isKepala() ? $this->penandatanganKemenag() : $this->kepala();
    }
}
