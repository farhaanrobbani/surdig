<?php

namespace Tests\Feature;

use App\Models\KuaDailyData;
use App\Models\StaffActivity;
use App\Models\User;
use App\Models\UserActivityTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LapkinTest extends TestCase
{
    use RefreshDatabase;

    private User $kepala;

    private User $operator;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kepala = User::factory()->create(['role' => User::ROLE_KEPALA]);
        $this->operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->staff = User::factory()->create(['role' => User::ROLE_STAFF]);
    }

    public function test_staff_can_access_kegiatan_harian(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kegiatan.index'))
            ->assertOk();
    }

    public function test_staff_cannot_access_master_data_harian(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kua-daily.index'))
            ->assertForbidden();
    }

    public function test_operator_and_kepala_can_access_master_data_harian(): void
    {
        $this->actingAs($this->operator)
            ->get(route('kua-daily.index'))
            ->assertOk();

        $this->actingAs($this->kepala)
            ->get(route('kua-daily.index'))
            ->assertOk();
    }

    public function test_staff_cannot_store_kua_daily_data(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kua-daily.store'), ['tanggal' => '2026-08-03'])
            ->assertForbidden();
    }

    public function test_operator_can_store_kua_daily_data(): void
    {
        $this->actingAs($this->operator)
            ->post(route('kua-daily.store'), [
                'tanggal' => '2026-08-03',
                'pendaftaran_nikah_kantor' => 5,
                'pelaksanaan_wakaf' => 2,
            ])
            ->assertRedirect(route('kua-daily.index', ['bulan' => 8, 'tahun' => 2026]));

        $record = KuaDailyData::where('tanggal', '2026-08-03')->first();

        $this->assertNotNull($record);
        $this->assertSame(5, $record->data['pendaftaran_nikah_kantor']);
        $this->assertSame(2, $record->data['pelaksanaan_wakaf']);
        $this->assertSame($this->operator->id, $record->created_by);
    }

    public function test_kua_daily_data_is_upserted_by_tanggal(): void
    {
        KuaDailyData::create([
            'tanggal' => '2026-08-03',
            'data' => ['pendaftaran_nikah_kantor' => 5],
            'created_by' => $this->operator->id,
        ]);

        $this->actingAs($this->operator)
            ->post(route('kua-daily.store'), [
                'tanggal' => '2026-08-03',
                'pendaftaran_nikah_kantor' => 9,
            ]);

        $this->assertDatabaseCount('kua_daily_data', 1);

        $record = KuaDailyData::where('tanggal', '2026-08-03')->first();
        $this->assertSame(9, $record->data['pendaftaran_nikah_kantor']);
    }

    public function test_staff_can_store_batch_activities(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    ['tanggal' => '2026-08-03', 'kegiatan' => 'Melayani pendaftaran', 'pekerjaan' => '5 pendaftar', 'activity_type_key' => 'pendaftaran_nikah_kantor', 'total_jumlah' => 3],
                    ['tanggal' => '2026-08-03', 'kegiatan' => 'Rapat koordinasi', 'pekerjaan' => 'Notulen rapat', 'activity_type_key' => '', 'total_jumlah' => 1],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('staff_activities', [
            'user_id' => $this->staff->id,
            'kegiatan' => 'Melayani pendaftaran',
            'total_jumlah' => 3,
        ]);
        $this->assertDatabaseHas('staff_activities', ['kegiatan' => 'Rapat koordinasi']);
        $this->assertDatabaseCount('staff_activities', 2);
    }

    public function test_activity_total_auto_synced_from_kua_daily_data(): void
    {
        KuaDailyData::create([
            'tanggal' => '2026-08-03',
            'data' => ['pendaftaran_nikah_kantor' => 7],
            'created_by' => $this->operator->id,
        ]);

        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    ['tanggal' => '2026-08-03', 'kegiatan' => 'Melayani pendaftaran', 'pekerjaan' => 'Input berkas', 'activity_type_key' => 'pendaftaran_nikah_kantor', 'total_jumlah' => 1],
                ],
            ]);

        $this->assertDatabaseHas('staff_activities', [
            'kegiatan' => 'Melayani pendaftaran',
            'total_jumlah' => 7,
        ]);
    }

    public function test_staff_can_save_activity_template_from_batch(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    ['tanggal' => '2026-08-03', 'kegiatan' => 'Melayani legalisir', 'pekerjaan' => 'Legalisir buku nikah', 'activity_type_key' => 'legalisir_buku_nikah', 'total_jumlah' => 2, 'save_template' => '1'],
                ],
            ]);

        $this->assertDatabaseHas('user_activity_templates', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'legalisir_buku_nikah',
            'kegiatan' => 'Melayani legalisir',
        ]);
    }

    public function test_staff_cannot_edit_other_staff_activity(): void
    {
        $activity = $this->createActivity($this->staff);

        $other = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($other)
            ->get(route('kegiatan.edit', $activity))
            ->assertForbidden();
    }

    public function test_staff_can_edit_own_activity(): void
    {
        $activity = $this->createActivity($this->staff);

        $this->actingAs($this->staff)
            ->put(route('kegiatan.update', $activity), [
                'tanggal' => '2026-08-03',
                'kegiatan' => 'Kegiatan baru',
                'pekerjaan' => 'Hasil baru',
                'total_jumlah' => 4,
            ])
            ->assertRedirect(route('kegiatan.index'));

        $this->assertDatabaseHas('staff_activities', [
            'id' => $activity->id,
            'kegiatan' => 'Kegiatan baru',
            'total_jumlah' => 4,
        ]);
    }

    public function test_operator_can_edit_any_staff_activity(): void
    {
        $activity = $this->createActivity($this->staff);

        $this->actingAs($this->operator)
            ->put(route('kegiatan.update', $activity), [
                'tanggal' => '2026-08-03',
                'kegiatan' => 'Dikoreksi operator',
                'pekerjaan' => 'Hasil',
                'total_jumlah' => 2,
            ])
            ->assertRedirect(route('kegiatan.index'));
    }

    public function test_staff_activity_index_shows_own_activities_only(): void
    {
        $this->createActivity($this->staff);
        $this->createActivity($this->operator);

        $response = $this->actingAs($this->staff)
            ->get(route('kegiatan.index'));

        $response->assertOk()->assertSee('Kegiatan staf');
        $response->assertDontSee('Kegiatan operator');
    }

    public function test_staff_can_update_employee_data_in_profile(): void
    {
        $this->actingAs($this->staff)
            ->patch(route('profile.employee.update'), [
                'nip' => '198001012000031001',
                'jabatan' => 'Penghulu',
                'pangkat' => 'Penata Muda',
                'ruang_golongan' => 'III/a',
                'instansi' => 'KUA Ampelgading',
            ])
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'nip' => '198001012000031001',
            'jabatan' => 'Penghulu',
            'pangkat' => 'Penata Muda',
        ]);
    }

    public function test_staff_can_manage_personal_template_sentences(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kegiatan.templates.index'))
            ->assertOk()
            ->assertSee('Atur Template Kalimat');

        $this->actingAs($this->staff)
            ->post(route('kegiatan.templates.store'), [
                'templates' => [
                    'pendaftaran_nikah_kantor' => ['kegiatan' => 'Melayani pendaftaran nikah', 'pekerjaan' => 'Input berkas pemohon'],
                    'pelaksanaan_bimwin' => ['kegiatan' => '', 'pekerjaan' => ''],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_activity_templates', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'pendaftaran_nikah_kantor',
            'kegiatan' => 'Melayani pendaftaran nikah',
        ]);
        $this->assertDatabaseMissing('user_activity_templates', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'pelaksanaan_bimwin',
        ]);
    }

    public function test_staff_templates_do_not_leak_between_users(): void
    {
        UserActivityTemplate::create([
            'user_id' => $this->staff->id,
            'activity_type_key' => 'pelaksanaan_wakaf',
            'kegiatan' => 'Milik staf',
            'pekerjaan' => '',
        ]);

        $this->actingAs($this->operator)
            ->post(route('kegiatan.templates.store'), [
                'templates' => [
                    'pendaftaran_nikah_kantor' => ['kegiatan' => 'Milik operator', 'pekerjaan' => ''],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_activity_templates', [
            'user_id' => $this->operator->id,
            'activity_type_key' => 'pendaftaran_nikah_kantor',
            'kegiatan' => 'Milik operator',
        ]);
        $this->assertDatabaseHas('user_activity_templates', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'pelaksanaan_wakaf',
            'kegiatan' => 'Milik staf',
        ]);
    }

    public function test_staff_can_log_holiday_activity_with_zero_volume(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    ['tanggal' => '2026-08-17', 'kegiatan' => 'Hari Libur / Libur Nasional', 'pekerjaan' => '-', 'activity_type_key' => 'libur', 'total_jumlah' => 0],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('staff_activities', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'libur',
            'total_jumlah' => 0,
        ]);
    }

    public function test_staff_can_log_manual_activity_with_lainnya_key(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    ['tanggal' => '2026-08-04', 'kegiatan' => 'Rapat Koordinasi KUA', 'pekerjaan' => 'Menyusun notulen rapat', 'activity_type_key' => 'lainnya', 'total_jumlah' => 1],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('staff_activities', [
            'user_id' => $this->staff->id,
            'activity_type_key' => 'lainnya',
            'total_jumlah' => 1,
        ]);
    }

    public function test_staff_can_create_single_activity_from_modal_payload(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [
                    '0' => [
                        'tanggal' => '2026-08-05',
                        'kegiatan' => 'Pelayanan Pendaftaran Nikah',
                        'pekerjaan' => 'Memeriksa dan merekap berkas permohonan',
                        'activity_type_key' => 'pendaftaran_nikah_kantor',
                        'total_jumlah' => 3,
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('staff_activities', [
            'user_id' => $this->staff->id,
            'tanggal' => '2026-08-05',
            'activity_type_key' => 'pendaftaran_nikah_kantor',
            'total_jumlah' => 3,
        ]);
    }

    public function test_staff_sees_pull_master_data_button(): void
    {
        $this->actingAs($this->staff)
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Ambil Data dari Operator')
            ->assertSee('Buat Pekerjaan Baru')
            ->assertSee('Volume Berkas');
    }

    public function test_modal_payload_returns_json_success_when_request_expects_json(): void
    {
        $this->actingAs($this->staff)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post(route('kegiatan.store'), [
                'items' => [
                    '0' => [
                        'tanggal' => '2026-08-05',
                        'kegiatan' => 'Pelayanan Pendaftaran Nikah',
                        'pekerjaan' => 'Memeriksa dan merekap berkas permohonan',
                        'activity_type_key' => 'pendaftaran_nikah_kantor',
                        'total_jumlah' => 3,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJson(['message' => '1 kegiatan berhasil ditambahkan ke laporan.']);

        $this->assertDatabaseHas('staff_activities', [
            'user_id' => $this->staff->id,
            'tanggal' => '2026-08-05',
            'activity_type_key' => 'pendaftaran_nikah_kantor',
        ]);
    }

    public function test_modal_payload_returns_json_validation_errors_when_request_expects_json(): void
    {
        $this->actingAs($this->staff)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post(route('kegiatan.store'), [
                'items' => [
                    '0' => [
                        'tanggal' => '2026-08-05',
                        'kegiatan' => 'Pelayanan Pendaftaran Nikah',
                        'pekerjaan' => '',
                        'activity_type_key' => 'pendaftaran_nikah_kantor',
                        'total_jumlah' => 1,
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['items.0.pekerjaan']]);

        $this->assertDatabaseCount('staff_activities', 0);
    }

    public function test_staff_cannot_store_kegiatan_without_uraian_pekerjaan(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [[
                    'tanggal' => '2026-08-03',
                    'kegiatan' => 'Pendaftaran Nikah di Kantor',
                    'pekerjaan' => '',
                    'activity_type_key' => 'pendaftaran_nikah_kantor',
                    'total_jumlah' => 3,
                ]],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('items.0.pekerjaan');

        $this->assertDatabaseMissing('staff_activities', ['kegiatan' => 'Pendaftaran Nikah di Kantor']);
    }

    public function test_staff_index_shows_validation_error_alert(): void
    {
        $this->actingAs($this->staff)
            ->post(route('kegiatan.store'), [
                'items' => [[
                    'tanggal' => '2026-08-03',
                    'kegiatan' => 'Pendaftaran Nikah di Kantor',
                    'pekerjaan' => '',
                    'activity_type_key' => 'pendaftaran_nikah_kantor',
                    'total_jumlah' => 3,
                ]],
            ]);

        $this->actingAs($this->staff)
            ->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Kegiatan gagal disimpan')
            ->assertSee('pekerjaan');
    }

    public function test_staff_index_renders_operator_daily_data_for_pull_modal(): void
    {
        KuaDailyData::create([
            'tanggal' => '2026-08-03',
            'data' => ['pendaftaran_nikah_kantor' => 3, 'pelaksanaan_bimwin' => 2],
        ]);

        $this->actingAs($this->staff)
            ->get(route('kegiatan.index', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('2026-08-03')
            ->assertSee('pendaftaran_nikah_kantor')
            ->assertSee('pelaksanaan_bimwin')
            ->assertSee('belum diisi uraian pekerjaan');
    }

    public function test_sidebar_shows_lapkin_menu_for_staff(): void
    {
        $this->actingAs($this->staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laporan Kinerja')
            ->assertSee('Kegiatan Harian')
            ->assertDontSee('Master Data Harian');
    }

    public function test_sidebar_shows_master_data_for_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laporan Kinerja')
            ->assertSee('Master Data Harian');
    }

    private function createActivity(User $user): StaffActivity
    {
        return StaffActivity::create([
            'user_id' => $user->id,
            'tanggal' => '2026-08-03',
            'kegiatan' => 'Kegiatan '.($user->isStaff() ? 'staf' : 'operator'),
            'pekerjaan' => 'Pekerjaan',
            'total_jumlah' => 1,
        ]);
    }
}
