<?php

namespace App\Support;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class LapkinWordExport
{
    private const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    public static function download(string $type, array $data): Response
    {
        $docx = $type === 'rekap' ? self::buildRekap($data) : self::buildLaporan($data);
        $filename = $data['fileName'].'.docx';

        return response($docx, 200, [
            'Content-Type' => self::CONTENT_TYPE,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"; filename*=UTF-8''".rawurlencode($filename),
        ]);
    }

    private static function buildLaporan(array $data): string
    {
        $user = $data['user'];
        $phpWord = self::newDocument();

        $section = $phpWord->addSection(self::pageSettings());

        $section->addText(
            'LAPORAN KINERJA',
            ['bold' => true, 'size' => 14],
            [
                'alignment' => Jc::CENTER,
                'spaceAfter' => 240,
            ]
        );

        $identitas = $section->addTable(self::tableStyle());
        foreach (self::identitasRowsLaporan($user) as $label => $value) {
            $identitas->addRow();
            $identitas->addCell(3400, ['valign' => 'center'])->addText($label, ['bold' => true]);
            $identitas->addCell(6238, ['valign' => 'center'])->addText($value);
        }

        $section->addText('Tanggal Dicetak : '.$data['printDate'], ['bold' => true], [
            'spaceBefore' => 200,
            'spaceAfter' => 200,
        ]);

        $isi = $section->addTable(self::tableStyle());
        $isi->addRow();
        foreach ([771, 3855, 3277, 1735] as $index => $width) {
            $isi->addCell($width, ['valign' => 'center'])->addText(['NO', 'KEGIATAN', 'PEKERJAAN', 'TANGGAL'][$index], ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        $groups = $data['activities']->groupBy('tanggal');

        if ($groups->isEmpty()) {
            $isi->addRow();
            $cell = $isi->addCell(null, ['gridSpan' => 4, 'valign' => 'center']);
            $cell->addText('Tidak ada catatan kegiatan untuk bulan ini.', ['italic' => true], ['alignment' => Jc::CENTER]);
        }

        $no = 1;
        foreach ($groups as $tanggal => $items) {
            $isi->addRow();
            $isi->addCell(771, ['valign' => 'center'])->addText((string) $no, ['bold' => true], ['alignment' => Jc::CENTER]);
            self::fillItems($isi->addCell(3855, ['valign' => 'center']), $items, fn ($item) => $item->kegiatan);
            self::fillItems(
                $isi->addCell(3277, ['valign' => 'center']),
                $items,
                fn ($item) => $item->isHoliday() ? '-' : $item->pekerjaan.' ('.$item->total_jumlah.')'
            );
            $isi->addCell(1735, ['valign' => 'center'])->addText(tanggal_indonesia($tanggal, 'j F Y'), [], ['alignment' => Jc::CENTER]);
            $no++;
        }

        $section->addText('', [], ['spaceAfter' => 240]);

        $ttd = $section->addTable(['width' => 9638, 'unit' => 'dxa']);
        $ttd->addRow();
        $kiri = $ttd->addCell(4819, ['valign' => 'top']);
        $kiri->addText('Pejabat Penilai,', [], ['alignment' => Jc::LEFT]);
        self::addSignatureSpace($kiri);
        $penilai = $data['pejabatPenilai'];
        if (($penilai['nama'] ?? '') !== '') {
            self::addNameAndNip($kiri, $penilai['nama'], $penilai['nip']);
        }

        $kanan = $ttd->addCell(4819, ['valign' => 'top']);
        $kanan->addText('Pegawai yang Dinilai,', [], ['alignment' => Jc::LEFT]);
        self::addSignatureSpace($kanan, $data['userTtdPath'] ?? null);
        self::addNameAndNip($kanan, $user->name, $user->nip);

        return self::save($phpWord);
    }

    private static function buildRekap(array $data): string
    {
        $user = $data['user'];
        $phpWord = self::newDocument();

        $section = $phpWord->addSection(self::pageSettings());

        $section->addText(
            'REKAP LAPORAN KINERJA BULAN '.strtoupper($data['monthName']).' TAHUN '.$data['year'],
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        $identitas = $section->addTable(self::identitasStyle());
        $fotoCell = null;
        foreach (self::identitasRowsRekap($data) as $index => [$label, $value]) {
            $identitas->addRow();
            $identitas->addCell(3200, ['valign' => 'center'])->addText($label, ['bold' => true]);
            $identitas->addCell(200, ['valign' => 'center'])->addText(':', ['bold' => true], ['alignment' => Jc::CENTER]);
            $identitas->addCell(4300, ['valign' => 'center'])->addText($value);
            $cell = $identitas->addCell(1938, [
                'valign' => 'center',
                'vMerge' => $index === 0 ? 'restart' : 'continue',
            ]);
            if ($fotoCell === null) {
                $fotoCell = $cell;
            }
        }

        $fotoPath = $user->foto_profil_url ? Storage::disk('public')->path($user->foto_profil_url) : null;
        if ($fotoCell !== null && $fotoPath && file_exists($fotoPath)) {
            $fotoCell->addImage($fotoPath, ['width' => 90]);
        }

        $section->addText('', [], ['spaceAfter' => 240]);

        $rek = $section->addTable(self::rekapStyle());
        $rek->addRow();
        foreach ([771, 3162, 2428, 3277] as $index => $width) {
            $rek->addCell($width, ['valign' => 'center'])->addText(['NO', 'URAIAN', 'ADA / TIDAK ADA', 'KETERANGAN'][$index], ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        $rows = [
            ['Rekap Tunjangan Kinerja', $user->jumlah_tukin_kotor ? self::rupiah($user->jumlah_tukin_kotor) : '-'],
            ['Rekap Kehadiran', $data['totalHariKerja'].' Hari'],
            ['Rekap Uang Makan', self::rupiah($data['totalHariKerja'] * ($user->jumlah_uang_makan_harian ?: 35150))],
            ['Laporan Kinerja', '1 Laporan'],
        ];
        $no = 1;
        foreach ($rows as [$uraian, $keterangan]) {
            $rek->addRow();
            $rek->addCell(771, ['valign' => 'center'])->addText((string) $no, ['bold' => true], ['alignment' => Jc::CENTER]);
            $rek->addCell(3162, ['valign' => 'center'])->addText($uraian);
            $rek->addCell(2428, ['valign' => 'center'])->addText('Ada', [], ['alignment' => Jc::CENTER]);
            $rek->addCell(3277, ['valign' => 'center'])->addText($keterangan);
            $no++;
        }

        $section->addText('', [], ['spaceAfter' => 240]);

        $ttd = $section->addTable(['width' => 9638, 'unit' => 'dxa']);
        $ttd->addRow();
        $kiri = $ttd->addCell(4819, ['valign' => 'top']);
        $kiri->addText('Mengetahui,', [], ['alignment' => Jc::LEFT]);
        $kiri->addText($data['kepalaJabatan'], ['bold' => true], ['alignment' => Jc::LEFT]);
        self::addSignatureSpace($kiri);
        $penilai = $data['pejabatPenilai'];
        if (($penilai['nama'] ?? '') !== '') {
            self::addNameAndNip($kiri, $penilai['nama'], $penilai['nip']);
        }

        $kanan = $ttd->addCell(4819, ['valign' => 'top']);
        $kanan->addText($data['signatureDate'], [], ['alignment' => Jc::LEFT]);
        $kanan->addText('Pegawai,', ['bold' => true], ['alignment' => Jc::LEFT]);
        self::addSignatureSpace($kanan, $data['userTtdPath'] ?? null);
        self::addNameAndNip($kanan, $user->name, $user->nip);

        $section->addText('Catatan:', ['bold' => true], ['spaceBefore' => 300]);
        $section->addText('Keterangan diisi dengan:');
        foreach (['Nominal tunjangan kinerja yang diterima', 'Jumlah kehadiran', 'Nominal uang makan yang diterima'] as $index => $catatan) {
            $section->addText(($index + 1).'. '.$catatan);
        }

        return self::save($phpWord);
    }

    private static function newDocument(): PhpWord
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        return $phpWord;
    }

    private static function pageSettings(): array
    {
        return [
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 1134,
            'marginRight' => 1134,
        ];
    }

    private static function tableStyle(): array
    {
        return [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 9638,
            'unit' => 'dxa',
        ];
    }

    private static function identitasStyle(): array
    {
        return [
            'cellMarginLeft' => 60,
            'cellMarginRight' => 60,
            'cellMarginTop' => 20,
            'cellMarginBottom' => 20,
            'width' => 9638,
            'unit' => 'dxa',
        ];
    }

    private static function rekapStyle(): array
    {
        return [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMarginLeft' => 60,
            'cellMarginRight' => 60,
            'cellMarginTop' => 20,
            'cellMarginBottom' => 20,
            'width' => 9638,
            'unit' => 'dxa',
        ];
    }

    private static function identitasRowsLaporan($user): array
    {
        return [
            'Nama' => (string) $user->name,
            'NIP' => (string) $user->nip,
            'Jabatan' => (string) $user->jabatan,
            'Pangkat' => (string) $user->pangkat,
            'Golongan / Ruang' => (string) $user->ruang_golongan,
        ];
    }

    private static function identitasRowsRekap(array $data): array
    {
        $user = $data['user'];

        return [
            ['Nama', (string) $user->name],
            ['NIP', (string) $user->nip],
            ['Jabatan', (string) $user->jabatan],
            ['Instansi', (string) ($data['instansi'] ?: '')],
            ['Grade Tukin', $user->grade_tukin ? 'Grade '.$user->grade_tukin : '-'],
            ['Nilai Tukin Kotor', $user->jumlah_tukin_kotor ? 'Rp '.self::rupiah($user->jumlah_tukin_kotor) : '-'],
        ];
    }

    private static function fillItems(Cell $cell, $items, callable $text): void
    {
        $run = $cell->addTextRun();
        foreach ($items as $index => $item) {
            if ($index > 0) {
                $run->addTextBreak();
            }
            $run->addText(($items->count() > 1 ? ($index + 1).'. ' : '').$text($item));
        }
    }

    private static function addSignatureSpace(Cell $cell, ?string $imagePath = null): void
    {
        if ($imagePath !== null && file_exists($imagePath)) {
            $cell->addImage($imagePath, ['width' => 110]);
            $cell->addTextRun()->addTextBreak();

            return;
        }

        $cell->addTextRun()->addTextBreak(4);
    }

    private static function addNameAndNip(Cell $cell, string $nama, ?string $nip): void
    {
        $run = $cell->addTextRun(['alignment' => Jc::LEFT]);
        $run->addText($nama, ['bold' => true, 'underline' => 'single']);
        $run->addTextBreak();
        $run->addText('NIP. '.($nip ?? ''), ['size' => 10]);
    }

    private static function rupiah(int $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    private static function save(PhpWord $phpWord): string
    {
        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }
}
