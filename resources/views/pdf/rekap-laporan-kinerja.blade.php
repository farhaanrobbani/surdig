<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Laporan Kinerja {{ $user->name }}</title>
    <style>
        @page { margin: 20mm; }
        * { font-family: 'Arial', 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; line-height: 1.5; color: #111; }
        .judul {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        table.identitas { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        table.identitas td { padding: 3px 6px; vertical-align: top; }
        table.identitas td.label { width: 30%; font-weight: bold; }
        table.identitas td.colon { width: 3%; text-align: right; }
        table.identitas td.nilai { width: 52%; }
        .foto { text-align: center; vertical-align: middle; }
        .foto img { width: 90px; height: auto; border: 1px solid #111; }
        table.border { width: 100%; border-collapse: collapse; }
        table.border th, table.border td { border: 1px solid #111; padding: 5px 7px; vertical-align: top; }
        table.border th { background: #eee; text-align: center; font-weight: bold; }
        .ttd { width: 100%; margin-top: 42px; border-collapse: collapse; }
        .ttd td { width: 50%; vertical-align: top; }
        .ttd td.kiri { text-align: left; }
        .ttd td.kanan { text-align: left; }
        .ttd .nama { font-weight: bold; }
        .ttd .nip { font-size: 10px; font-weight: normal; text-decoration: none; }
        .ttd .anchor { font-size: 16px; font-weight: bold; margin: 6px 0; }
        .catatan { margin-top: 24px; border-top: 1px solid #ccc; padding-top: 8px; font-size: 10px; }
        .catatan ol { margin: 4px 0 0 0; padding-left: 16px; }
    </style>
</head>
<body>
    <div class="judul">Rekap Laporan Kinerja Bulan {{ strtoupper($monthName) }} {{ $year }}</div>

    <table class="identitas">
        <tr>
            <td class="label">Nama</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $user->name }}</td>
            <td class="foto" rowspan="6">
                @php($fotoPath = $user->foto_profil_url ? \Illuminate\Support\Facades\Storage::disk('public')->path($user->foto_profil_url) : null)
                @if ($fotoPath && file_exists($fotoPath))
                    <img src="{{ $fotoPath }}" alt="{{ $user->name }}">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $user->nip }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $user->jabatan }}</td>
        </tr>
        <tr>
            <td class="label">Instansi</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $instansi }}</td>
        </tr>
        <tr>
            <td class="label">Grade Tukin</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $user->grade_tukin ? 'Grade ' . $user->grade_tukin : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nilai Tukin Kotor</td>
            <td class="colon">:</td>
            <td class="nilai">{{ $user->jumlah_tukin_kotor ? 'Rp ' . number_format($user->jumlah_tukin_kotor, 0, ',', '.') : '-' }}</td>
        </tr>
    </table>

    <table class="border">
        <thead>
            <tr>
                <th style="width: 8%">NO</th>
                <th style="width: 38%">URAIAN</th>
                <th style="width: 20%">ADA / TIDAK ADA</th>
                <th style="width: 34%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold">1</td>
                <td>Rekap Tunjangan Kinerja</td>
                <td style="text-align: center">Ada</td>
                <td>{{ $user->jumlah_tukin_kotor ? 'Rp ' . number_format($user->jumlah_tukin_kotor, 0, ',', '.') : '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold">2</td>
                <td>Rekap Kehadiran</td>
                <td style="text-align: center">Ada</td>
                <td>{{ $totalHariKerja }} Hari</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold">3</td>
                <td>Rekap Uang Makan</td>
                <td style="text-align: center">Ada</td>
                <td>{{ 'Rp ' . number_format($totalHariKerja * ($user->jumlah_uang_makan_harian ?: 35150), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold">4</td>
                <td>Laporan Kinerja</td>
                <td style="text-align: center">Ada</td>
                <td>1 Laporan</td>
            </tr>
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td class="kiri">
                <div>Mengetahui,</div>
                <div style="font-weight: bold;">{{ $kepalaJabatan }}</div>
                @if (($kop_anchor ?? '1') !== '0')
                    <div style="height: 30px;"></div>
                    <div class="anchor">^</div>
                    <div style="height: 7px;"></div>
                @else
                    <div style="height: 78px;"></div>
                @endif
                @if (($pejabatPenilai['nama'] ?? '') !== '')
                    <div class="nama"><u>{{ $pejabatPenilai['nama'] }}</u><br><span class="nip">NIP. {{ $pejabatPenilai['nip'] }}</span></div>
                @endif
            </td>
            <td class="kanan">
                <div>{{ $signatureDate }}</div>
                <div style="font-weight: bold;">Pegawai</div>
                <div style="height: 78px;"></div>
                <div class="nama"><u>{{ $user->name }}</u><br><span class="nip">NIP. {{ $user->nip }}</span></div>
            </td>
        </tr>
    </table>

    <div class="catatan">
        <p style="font-weight: bold; margin: 0;">Catatan:</p>
        <p style="margin: 4px 0 0 0;">Keterangan diisi dengan:</p>
        <ol>
            <li>Nominal tunjangan kinerja yang diterima</li>
            <li>Jumlah kehadiran</li>
            <li>Nominal uang makan yang diterima</li>
        </ol>
    </div>
</body>
</html>
