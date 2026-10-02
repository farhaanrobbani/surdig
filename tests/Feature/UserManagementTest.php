<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_can_view_user_list(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();

        $this->actingAs($kepala)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($kepala->email);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('users.create'))
            ->assertForbidden();
    }

    public function test_kepala_can_create_user(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();

        $this->actingAs($kepala)
            ->post(route('users.store'), [
                'name' => 'Staf Baru',
                'email' => 'stafbaru@kua.local',
                'role' => User::ROLE_STAFF,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'stafbaru@kua.local',
            'name' => 'Staf Baru',
            'role' => User::ROLE_STAFF,
            'is_active' => 1,
        ]);

        $created = User::where('email', 'stafbaru@kua.local')->first();
        $this->assertNotNull($created->email_verified_at);
    }

    public function test_kepala_can_create_operator_user(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();

        $this->actingAs($kepala)
            ->post(route('users.store'), [
                'name' => 'Operator Baru',
                'email' => 'operatorbaru@kua.local',
                'role' => User::ROLE_OPERATOR,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'operatorbaru@kua.local',
            'name' => 'Operator Baru',
            'role' => User::ROLE_OPERATOR,
            'is_active' => 1,
        ]);
    }

    public function test_create_user_requires_valid_role_and_unique_email(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();
        $staff = User::factory()->create(['email' => 'sama@kua.local']);

        $this->actingAs($kepala)
            ->post(route('users.store'), [
                'name' => 'X',
                'email' => $staff->email,
                'role' => 'admin',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors(['email', 'role']);
    }

    public function test_kepala_can_update_user_without_changing_password(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();
        $staff = User::factory()->create(['password' => 'passwordlama']);

        $this->actingAs($kepala)
            ->put(route('users.update', $staff), [
                'name' => 'Nama Baru',
                'email' => $staff->email,
                'role' => User::ROLE_KEPALA,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $staff->refresh();
        $this->assertSame('Nama Baru', $staff->name);
        $this->assertSame(User::ROLE_KEPALA, $staff->role);
        $this->assertTrue(password_verify('passwordlama', $staff->password));
    }

    public function test_superadmin_can_upload_user_signature(): void
    {
        Storage::fake('public');

        $admin = User::factory()->role(User::ROLE_SUPERADMIN)->create();
        $staff = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'is_active' => '1',
                'tanda_tangan' => UploadedFile::fake()->image('ttd.png', 200, 80),
            ])
            ->assertRedirect(route('users.index'));

        $staff->refresh();
        $this->assertNotNull($staff->ttd_url);
        Storage::disk('public')->assertExists($staff->ttd_url);
    }

    public function test_kepala_can_reset_user_password(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();
        $staff = User::factory()->create();

        $this->actingAs($kepala)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'password' => 'passwordbaru',
                'password_confirmation' => 'passwordbaru',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue(password_verify('passwordbaru', $staff->fresh()->password));
    }

    public function test_kepala_can_deactivate_and_delete_user(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();
        $staff = User::factory()->create();

        $this->actingAs($kepala)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertFalse((bool) $staff->fresh()->is_active);

        $this->actingAs($kepala)
            ->delete(route('users.destroy', $staff))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_kepala_cannot_delete_or_edit_own_account(): void
    {
        $kepala = User::factory()->role(User::ROLE_KEPALA)->create();

        $this->actingAs($kepala)
            ->delete(route('users.destroy', $kepala))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $kepala->id]);

        $this->actingAs($kepala)
            ->put(route('users.update', $kepala), [
                'name' => 'Berubah',
                'email' => $kepala->email,
                'role' => User::ROLE_KEPALA,
            ])
            ->assertSessionHasErrors('user');

        $this->assertSame($kepala->name, $kepala->fresh()->name);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $staff = User::factory()->inactive()->create([
            'email' => 'nonaktif@kua.local',
            'password' => 'password',
        ]);

        $this->post('/yukmasuk', [
            'email' => 'nonaktif@kua.local',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_is_blocked_from_panel(): void
    {
        $staff = User::factory()->inactive()->create();

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_register_route_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'X',
            'email' => 'x@kua.local',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }
}
