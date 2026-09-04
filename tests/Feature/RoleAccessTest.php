<?php

namespace Tests\Feature;

use App\Models\Letter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $kepala;

    private User $operator;

    private User $staff;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kepala = User::factory()->create(['role' => User::ROLE_KEPALA]);
        $this->operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    }

    public static function staffAccessibleRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'surat' => ['letters.index'],
            'permohonan' => ['submissions.index'],
            'kritik dan saran' => ['kritik-saran.index'],
        ];
    }

    public static function staffForbiddenRoutes(): array
    {
        return [
            'jenis surat' => ['letter-types.index'],
            'template' => ['letter-templates.index'],
            'navbar' => ['navbar.index'],
            'pengumuman' => ['announcements.index'],
            'download center' => ['download-items.index'],
            'page' => ['pages.index'],
            'daftar staf' => ['staff.index'],
            'pengaturan web' => ['kua-settings.edit'],
            'akun' => ['users.index'],
        ];
    }

    public static function superadminOnlyRoutes(): array
    {
        return [
            'navbar' => ['navbar.index'],
            'page' => ['pages.index'],
            'pengaturan web' => ['kua-settings.edit'],
            'layanan pernikahan' => ['marriage-services.create'],
        ];
    }

    public function test_staff_can_access_dashboard_letters_submissions_and_kritik_saran(): void
    {
        foreach (self::staffAccessibleRoutes() as $label => [$route]) {
            $this->actingAs($this->staff)
                ->get(route($route))
                ->assertOk("Staf harus dapat akses $label");
        }
    }

    public function test_staff_cannot_access_restricted_menus(): void
    {
        foreach (self::staffForbiddenRoutes() as $label => [$route]) {
            $this->actingAs($this->staff)
                ->get(route($route))
                ->assertForbidden("Staf tidak boleh akses $label");
        }
    }

    public function test_operator_can_access_all_manageable_menus(): void
    {
        $menus = array_merge(self::staffAccessibleRoutes(), self::staffForbiddenRoutes());
        $menus = array_diff_key($menus, self::superadminOnlyRoutes());
        unset($menus['akun']);

        foreach ($menus as $label => [$route]) {
            $this->actingAs($this->operator)
                ->get(route($route))
                ->assertOk("Operator harus dapat akses $label");
        }
    }

    public function test_operator_cannot_access_superadmin_only_menus(): void
    {
        foreach (self::superadminOnlyRoutes() as $label => [$route]) {
            $this->actingAs($this->operator)
                ->get(route($route))
                ->assertForbidden("Operator tidak boleh akses $label");
        }
    }

    public function test_operator_can_approve_letters(): void
    {
        $letter = Letter::factory()->create(['status' => Letter::STATUS_DIAJUKAN]);

        $this->actingAs($this->operator)
            ->post(route('letters.setujui', $letter))
            ->assertRedirect();
    }

    public function test_kepala_has_same_access_as_staff(): void
    {
        foreach (self::staffAccessibleRoutes() as $label => [$route]) {
            $this->actingAs($this->kepala)
                ->get(route($route))
                ->assertOk("Kepala harus dapat akses $label");
        }
    }

    public function test_kepala_cannot_access_operator_menus(): void
    {
        foreach (self::staffForbiddenRoutes() as $label => [$route]) {
            $this->actingAs($this->kepala)
                ->get(route($route))
                ->assertForbidden("Kepala tidak boleh akses $label");
        }
    }

    public function test_kepala_can_approve_letters(): void
    {
        $letter = Letter::factory()->create(['status' => Letter::STATUS_DIAJUKAN]);

        $this->actingAs($this->kepala)
            ->post(route('letters.setujui', $letter))
            ->assertRedirect();
    }

    public function test_superadmin_can_access_all_menus(): void
    {
        $menus = array_merge(self::staffAccessibleRoutes(), self::staffForbiddenRoutes(), self::superadminOnlyRoutes());

        foreach ($menus as $label => [$route]) {
            $this->actingAs($this->superadmin)
                ->get(route($route))
                ->assertOk("Superadmin harus dapat akses $label");
        }
    }

    public function test_superadmin_can_approve_letters(): void
    {
        $letter = Letter::factory()->create(['status' => Letter::STATUS_DIAJUKAN]);

        $this->actingAs($this->superadmin)
            ->post(route('letters.setujui', $letter))
            ->assertRedirect();
    }

    public function test_sidebar_hides_restricted_menus_for_staff(): void
    {
        $this->actingAs($this->staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Posts')
            ->assertDontSee('Download Center')
            ->assertDontSee('Daftar Staf')
            ->assertDontSee('Jenis Surat')
            ->assertDontSee('Template')
            ->assertSee('Kritik & Saran');
    }

    public function test_sidebar_shows_manageable_menus_for_operator(): void
    {
        $this->actingAs($this->operator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Posts')
            ->assertSee('Download Center')
            ->assertSee('Daftar Staf')
            ->assertSee('Jenis Surat')
            ->assertDontSee('Pengaturan Web')
            ->assertDontSee('Navbar')
            ->assertDontSee(route('pages.index'));
    }

    public function test_sidebar_shows_all_menus_for_superadmin(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Posts')
            ->assertSee('Download Center')
            ->assertSee('Daftar Staf')
            ->assertSee('Jenis Surat')
            ->assertSee('Pengaturan Web')
            ->assertSee('Navbar')
            ->assertSee(route('pages.index'));
    }

    public function test_admin_layout_has_mini_sidebar_toggle(): void
    {
        $this->actingAs($this->operator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mini sidebar', false);
    }
}
