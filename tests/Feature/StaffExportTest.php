<?php

namespace Tests\Feature;

use App\Models\KuaSetting;
use App\Models\StaffActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffExportTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'name' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'jabatan' => 'Juru Muda',
            'pangkat' => 'Penata Muda, III/a',
            'ruang_golongan' => 'III/a',
            'grade_tukin' => 9,
            'jumlah_tukin_kotor' => 2500000,
            'jumlah_uang_makan_harian' => 35150,
            'instansi' => 'KUA Ampelgading',
        ]);

        $this->operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'name' => 'Op KUA',
        ]);

        KuaSetting::set('kecamatan', 'Ampelgading');
        KuaSetting::set('kepala_nama', 'H. Kepala KUA');
        KuaSetting::set('kepala_nip', '197001011990011001');
        KuaSetting::set('kepala_pangkat', 'Pembina, IV/a');

        StaffActivity::create([
            'user_id' => $this->staff->id,
            'tanggal' => '2026-08-04',
            'kegiatan' => 'Pelayanan Pendaftaran Nikah',
            'pekerjaan' => 'Memeriksa berkas permohonan',
            'activity_type_key' => 'pendaftaran_nikah_kantor',
            'total_jumlah' => 3,
        ]);

        StaffActivity::create([
            'user_id' => $this->staff->id,
            'tanggal' => '2026-08-17',
            'kegiatan' => 'Hari Libur / Libur Nasional',
            'pekerjaan' => '-',
            'activity_type_key' => 'libur',
            'total_jumlah' => 0,
        ]);
    }

    public function test_staff_can_download_laporan_kinerja_pdf(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.laporan', ['bulan' => 8, 'tahun' => 2026]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('%PDF', $content);
    }

    public function test_staff_export_ignores_other_users_id(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.laporan', [
                'bulan' => 8,
                'tahun' => 2026,
                'user_id' => $this->operator->id,
            ]));

        $response->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=Laporan_Kinerja_Budi_Santoso_Agustus_2026.pdf');
    }

    public function test_operator_must_select_staff_before_export(): void
    {
        $this->actingAs($this->operator)
            ->get(route('kegiatan.export.laporan', ['bulan' => 8, 'tahun' => 2026]))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_operator_can_export_staff_laporan_kinerja(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('kegiatan.export.laporan', [
                'bulan' => 8,
                'tahun' => 2026,
                'user_id' => $this->staff->id,
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_staff_can_download_rekap_pdf_with_attendance(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.rekap', [
                'bulan' => 8,
                'tahun' => 2026,
                'total_hari_kerja' => 20,
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=Rekap_Laporan_Kinerja_Budi_Santoso_Agustus_2026.pdf');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('%PDF', $content);
    }

    public function test_staff_can_download_rekap_pdf_with_custom_signature_date(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.rekap', [
                'bulan' => 8,
                'tahun' => 2026,
                'total_hari_kerja' => 22,
                'tanggal_ttd' => '20 Agustus 2026',
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('%PDF', $content);
    }

    public function test_staff_can_view_export_page(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kegiatan.export.index', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Export Laporan Kinerja')
            ->assertSee('Export Rekap')
            ->assertSee('export-laporan')
            ->assertSee('export-rekap');
    }

    public function test_operator_export_page_requires_staff_selection(): void
    {
        $this->actingAs($this->operator)
            ->get(route('kegiatan.export.index', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Pilih pegawai (wajib)');
    }

    public function test_staff_can_download_laporan_kinerja_word(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.laporan', [
                'bulan' => 8,
                'tahun' => 2026,
                'format' => 'word',
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->assertHeader('content-disposition', 'attachment; filename="Laporan_Kinerja_Budi_Santoso_Agustus_2026.docx"; filename*=UTF-8\'\'Laporan_Kinerja_Budi_Santoso_Agustus_2026.docx');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith('PK', $content);
    }

    public function test_staff_can_download_rekap_word(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.rekap', [
                'bulan' => 8,
                'tahun' => 2026,
                'total_hari_kerja' => 22,
                'format' => 'word',
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->assertHeader('content-disposition', 'attachment; filename="Rekap_Laporan_Kinerja_Budi_Santoso_Agustus_2026.docx"; filename*=UTF-8\'\'Rekap_Laporan_Kinerja_Budi_Santoso_Agustus_2026.docx');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith('PK', $content);
    }

    public function test_operator_can_export_rekap_for_staff(): void
    {
        $this->actingAs($this->operator)
            ->get(route('kegiatan.export.rekap', [
                'bulan' => 8,
                'tahun' => 2026,
                'user_id' => $this->staff->id,
                'total_hari_kerja' => 22,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_laporan_kinerja_word_uses_custom_print_date(): void
    {
        $xml = $this->laporanWordDocument([
            'format' => 'word',
            'tanggal_ttd' => '20 Agustus 2026',
        ]);

        $this->assertStringContainsString('20 Agustus 2026', $xml);
        $this->assertStringNotContainsString('31 Agustus 2026', $xml);
    }

    public function test_laporan_kinerja_word_defaults_print_date_to_end_of_month(): void
    {
        $xml = $this->laporanWordDocument(['format' => 'word']);

        $this->assertStringContainsString('31 Agustus 2026', $xml);
    }

    public function test_export_page_shows_custom_print_date_field(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kegiatan.export.index', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Tanggal Dicetak (opsional)');
    }

    public function test_laporan_word_uses_kemenag_signer_for_kepala_subject(): void
    {
        KuaSetting::set('kepala_kemenag_nama', 'H. Kepala Kemenag');
        KuaSetting::set('kepala_kemenag_nip', '196801011990031001');

        $kepala = User::factory()->create([
            'role' => User::ROLE_KEPALA,
            'name' => 'Kepala KUA Lokal',
        ]);

        $xml = $this->laporanWordDocument(['format' => 'word'], $kepala);

        $this->assertStringContainsString('H. Kepala Kemenag', $xml);
        $this->assertStringContainsString('196801011990031001', $xml);
        $this->assertStringNotContainsString('H. Kepala KUA', $xml);
    }

    public function test_laporan_word_keeps_kua_head_signer_for_staff_subject(): void
    {
        KuaSetting::set('kepala_kemenag_nama', 'H. Kepala Kemenag');
        KuaSetting::set('kepala_kemenag_nip', '196801011990031001');

        $xml = $this->laporanWordDocument(['format' => 'word']);

        $this->assertStringContainsString('H. Kepala KUA', $xml);
        $this->assertStringNotContainsString('H. Kepala Kemenag', $xml);
    }

    public function test_laporan_word_omits_signer_name_when_kemenag_empty_for_kepala(): void
    {
        KuaSetting::set('kepala_kemenag_nama', '');
        KuaSetting::set('kepala_kemenag_nip', '');

        $kepala = User::factory()->create([
            'role' => User::ROLE_KEPALA,
            'name' => 'Kepala KUA Lokal',
        ]);

        $xml = $this->laporanWordDocument(['format' => 'word'], $kepala);

        $this->assertStringContainsString('Pejabat Penilai', $xml);
        $this->assertStringNotContainsString('H. Kepala KUA', $xml);
        $this->assertStringNotContainsString('197001011990011001', $xml);
    }

    public function test_rekap_word_uses_kemenag_signer_for_kepala_subject(): void
    {
        KuaSetting::set('kabupaten', 'Malang');
        KuaSetting::set('kepala_kemenag_nama', 'H. Kepala Kemenag');
        KuaSetting::set('kepala_kemenag_nip', '196801011990031001');

        $kepala = User::factory()->create([
            'role' => User::ROLE_KEPALA,
            'name' => 'Kepala KUA Lokal',
        ]);

        $xml = $this->rekapWordDocument(['format' => 'word'], $kepala);

        $this->assertStringContainsString('Kepala Kemenag Malang', $xml);
        $this->assertStringContainsString('H. Kepala Kemenag', $xml);
        $this->assertStringNotContainsString('Kepala KUA Ampelgading', $xml);
        $this->assertStringNotContainsString('H. Kepala KUA', $xml);
    }

    public function test_rekap_word_keeps_kua_head_for_staff_subject(): void
    {
        KuaSetting::set('kabupaten', 'Malang');
        KuaSetting::set('kepala_kemenag_nama', 'H. Kepala Kemenag');

        $xml = $this->rekapWordDocument(['format' => 'word']);

        $this->assertStringContainsString('Kepala KUA Ampelgading', $xml);
        $this->assertStringContainsString('H. Kepala KUA', $xml);
        $this->assertStringNotContainsString('Kepala Kemenag Malang', $xml);
    }

    public function test_pdf_templates_render_pejabat_penilai(): void
    {
        $kepala = User::factory()->create([
            'role' => User::ROLE_KEPALA,
            'name' => 'Kepala KUA Lokal',
        ]);

        $penilai = ['nama' => 'H. Kepala Kemenag', 'nip' => '196801011990031001'];

        $laporan = view('pdf.laporan-kinerja', [
            'user' => $kepala,
            'activities' => collect(),
            'pejabatPenilai' => $penilai,
            'kop_anchor' => '1',
            'printDate' => '31 Agustus 2026',
        ])->render();

        $this->assertStringContainsString('H. Kepala Kemenag', $laporan);
        $this->assertStringNotContainsString('H. Kepala KUA', $laporan);

        $rekap = view('pdf.rekap-laporan-kinerja', [
            'user' => $kepala,
            'monthName' => 'Agustus',
            'year' => 2026,
            'instansi' => 'KUA Ampelgading',
            'totalHariKerja' => 22,
            'signatureDate' => '31 Agustus 2026',
            'pejabatPenilai' => $penilai,
            'kepalaJabatan' => 'Kepala Kemenag Malang',
            'kop_anchor' => '1',
        ])->render();

        $this->assertStringContainsString('Kepala Kemenag Malang', $rekap);
        $this->assertStringContainsString('H. Kepala Kemenag', $rekap);
        $this->assertStringNotContainsString('H. Kepala KUA', $rekap);
    }

    public function test_laporan_word_embeds_user_signature_image(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('ttd.png', 200, 80)->store('users/signatures', 'public');
        $this->staff->update(['ttd_url' => $path]);

        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.laporan', [
                'bulan' => 8,
                'tahun' => 2026,
                'format' => 'word',
            ]));

        $this->assertTrue($this->wordHasMedia($response));
    }

    public function test_laporan_word_has_no_media_without_signature(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.laporan', [
                'bulan' => 8,
                'tahun' => 2026,
                'format' => 'word',
            ]));

        $this->assertFalse($this->wordHasMedia($response));
    }

    public function test_rekap_word_embeds_user_signature_image(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('ttd.png', 200, 80)->store('users/signatures', 'public');
        $this->staff->update(['ttd_url' => $path]);

        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.export.rekap', [
                'bulan' => 8,
                'tahun' => 2026,
                'total_hari_kerja' => 22,
                'format' => 'word',
            ]));

        $this->assertTrue($this->wordHasMedia($response));
    }

    public function test_pdf_templates_render_user_signature(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('ttd.png', 200, 80)->store('users/signatures', 'public');
        $ttdPath = Storage::disk('public')->path($path);

        $laporan = view('pdf.laporan-kinerja', [
            'user' => $this->staff,
            'activities' => collect(),
            'pejabatPenilai' => ['nama' => 'H. Kepala KUA', 'nip' => '197001011990011001'],
            'kop_anchor' => '1',
            'printDate' => '31 Agustus 2026',
            'userTtdPath' => $ttdPath,
        ])->render();

        $this->assertStringContainsString('max-height: 80px', $laporan);
        $this->assertStringContainsString('<img src="'.$ttdPath.'"', $laporan);

        $rekap = view('pdf.rekap-laporan-kinerja', [
            'user' => $this->staff,
            'monthName' => 'Agustus',
            'year' => 2026,
            'instansi' => 'KUA Ampelgading',
            'totalHariKerja' => 22,
            'signatureDate' => '31 Agustus 2026',
            'pejabatPenilai' => ['nama' => 'H. Kepala KUA', 'nip' => '197001011990011001'],
            'kepalaJabatan' => 'Kepala KUA Ampelgading',
            'kop_anchor' => '1',
            'userTtdPath' => $ttdPath,
        ])->render();

        $this->assertStringContainsString('max-height: 68px', $rekap);

        $laporanNoTtd = view('pdf.laporan-kinerja', [
            'user' => $this->staff,
            'activities' => collect(),
            'pejabatPenilai' => ['nama' => 'H. Kepala KUA', 'nip' => '197001011990011001'],
            'kop_anchor' => '1',
            'printDate' => '31 Agustus 2026',
        ])->render();

        $this->assertStringNotContainsString('<img', $laporanNoTtd);
    }

    private function laporanWordDocument(array $extra, ?User $as = null): string
    {
        $response = $this->actingAs($as ?? $this->staff)->get(route('kegiatan.export.laporan', array_merge([
            'bulan' => 8,
            'tahun' => 2026,
        ], $extra)));

        return $this->wordDocumentXml($response);
    }

    private function rekapWordDocument(array $extra, ?User $as = null): string
    {
        $response = $this->actingAs($as ?? $this->staff)->get(route('kegiatan.export.rekap', array_merge([
            'bulan' => 8,
            'tahun' => 2026,
            'total_hari_kerja' => 22,
        ], $extra)));

        return $this->wordDocumentXml($response);
    }

    private function wordDocumentXml($response): string
    {
        $response->assertOk();

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $tmp = tempnam(sys_get_temp_dir(), 'lapkin');
        file_put_contents($tmp, $content);

        $zip = new \ZipArchive;
        $zip->open($tmp);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($tmp);

        $this->assertIsString($xml);

        return $xml;
    }

    private function wordHasMedia($response): bool
    {
        $response->assertOk();

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $tmp = tempnam(sys_get_temp_dir(), 'lapkin');
        file_put_contents($tmp, $content);

        $zip = new \ZipArchive;
        $zip->open($tmp);
        $found = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->statIndex($i)['name'], 'word/media/')) {
                $found = true;
                break;
            }
        }
        $zip->close();
        unlink($tmp);

        return $found;
    }
}
