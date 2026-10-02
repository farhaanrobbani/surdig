<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja {{ $user->name }}</title>
    <style>
        @page { margin: 20mm; }
        * { font-family: 'Arial', 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; line-height: 1.5; color: #111; }
        .judul {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        table.border { width: 100%; border-collapse: collapse; }
        table.border th, table.border td { border: 1px solid #111; padding: 5px 7px; vertical-align: top; }
        table.border th { background: #eee; text-align: center; font-weight: bold; }
        table.identitas { width: 100%; border-collapse: collapse; }
        table.identitas td { border: 1px solid #111; padding: 4px 8px; }
        table.identitas td.label { width: 34%; font-weight: bold; }
        .tanggal-cetak { margin: 10px 0; }
        .tanggal-cetak strong { margin-right: 4px; }
        ol { margin: 0; padding-left: 18px; }
        .ttd { width: 100%; margin-top: 46px; border-collapse: collapse; }
        .ttd td { width: 50%; text-align: left; vertical-align: top; }
        .ttd .nama { font-weight: bold; }
        .ttd .nip { font-size: 10px; font-weight: normal; text-decoration: none; }
        .ttd .anchor { font-size: 16px; font-weight: bold; margin: 6px 0; }
    </style>
</head>
<body>
    <div class="judul">Laporan Kinerja</div>

    <table class="identitas">
        <tr>
            <td class="label">Nama</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td>{{ $user->nip }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>{{ $user->jabatan }}</td>
        </tr>
        <tr>
            <td class="label">Pangkat</td>
            <td>{{ $user->pangkat }}</td>
        </tr>
        <tr>
            <td class="label">Golongan / Ruang</td>
            <td>{{ $user->ruang_golongan }}</td>
        </tr>
    </table>

    <p class="tanggal-cetak">
        <strong>Tanggal Dicetak :</strong> {{ $printDate }}
    </p>

    <table class="border">
        <thead>
            <tr>
                <th style="width: 8%">NO</th>
                <th style="width: 40%">KEGIATAN</th>
                <th style="width: 34%">PEKERJAAN</th>
                <th style="width: 18%">TANGGAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activities->groupBy('tanggal') as $tanggal => $items)
                <tr>
                    <td style="text-align: center; font-weight: bold">{{ $loop->iteration }}</td>
                    <td>
                        @if ($items->count() === 1)
                            {{ $items->first()->kegiatan }}
                        @else
                            <ol>
                                @foreach ($items as $item)
                                    <li>{{ $item->kegiatan }}</li>
                                @endforeach
                            </ol>
                        @endif
                    </td>
                    <td>
                        @if ($items->count() === 1)
                            {{ $items->first()->isHoliday() ? '-' : $items->first()->pekerjaan . ' (' . $items->first()->total_jumlah . ')' }}
                        @else
                            <ol>
                                @foreach ($items as $item)
                                    <li>{{ $item->isHoliday() ? '-' : $item->pekerjaan . ' (' . $item->total_jumlah . ')' }}</li>
                                @endforeach
                            </ol>
                        @endif
                    </td>
                    <td style="text-align: center; white-space: nowrap">{{ tanggal_indonesia($tanggal, 'j F Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; font-style: italic; padding: 18px;">
                        Tidak ada catatan kegiatan untuk bulan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>
                <div>Pejabat Penilai</div>
                @if (($kop_anchor ?? '1') !== '0')
                    <div style="height: 30px;"></div>
                    <div class="anchor">^</div>
                    <div style="height: 19px;"></div>
                @else
                    <div style="height: 90px;"></div>
                @endif
                @if (($pejabatPenilai['nama'] ?? '') !== '')
                    <div class="nama"><u>{{ $pejabatPenilai['nama'] }}</u><br><span class="nip">NIP. {{ $pejabatPenilai['nip'] }}</span></div>
                @endif
            </td>
            <td>
                <div>Pegawai yang Dinilai</div>
                @if (! empty($userTtdPath))
                    <div style="height: 90px; text-align: center;"><img src="{{ $userTtdPath }}" style="max-height: 80px; max-width: 170px;"></div>
                @else
                    <div style="height: 90px;"></div>
                @endif
                <div class="nama"><u>{{ $user->name }}</u><br><span class="nip">NIP. {{ $user->nip }}</span></div>
            </td>
        </tr>
    </table>
</body>
</html>
