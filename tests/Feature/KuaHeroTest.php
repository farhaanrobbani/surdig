<?php

namespace Tests\Feature;

use App\Models\KuaSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KuaHeroTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => User::ROLE_KEPALA]);
    }

    public function test_staff_can_upload_hero(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero' => UploadedFile::fake()->image('hero.png', 1280, 540),
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $path = KuaSetting::get('hero_path');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_staff_can_replace_hero_and_old_file_is_deleted(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('lama.png', 1280, 540)->store('heroes', 'public');
        KuaSetting::set('hero_path', $oldPath);

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero' => UploadedFile::fake()->image('baru.png', 1280, 540),
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $newPath = KuaSetting::get('hero_path');

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_staff_can_delete_hero(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('hero.png', 1280, 540)->store('heroes', 'public');
        KuaSetting::set('hero_path', $path);

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero_hapus' => '1',
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $this->assertSame('', KuaSetting::get('hero_path'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_hero_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero' => UploadedFile::fake()->create('hero.txt', 100),
            ]))
            ->assertSessionHasErrors('hero');

        $this->assertNull(KuaSetting::get('hero_path'));
    }

    public function test_landing_page_shows_hero_when_available(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('hero.png', 1280, 540)->store('heroes', 'public');
        KuaSetting::set('hero_path', $path);

        $this->get('/')
            ->assertOk()
            ->assertSee('storage/heroes/', false);
    }

    public function test_landing_page_hides_hero_when_absent(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('storage/heroes/', false);
    }

    public function test_admin_can_set_hero_text_and_landing_shows_it(): void
    {
        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero_judul' => 'Judul Baru'."\n".'Baris Kedua',
                'hero_subjudul' => 'Deskripsi baru dari admin.',
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $this->assertSame("Judul Baru\nBaris Kedua", KuaSetting::get('hero_judul'));
        $this->assertSame('Deskripsi baru dari admin.', KuaSetting::get('hero_subjudul'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Judul Baru')
            ->assertSee('Baris Kedua')
            ->assertSee('Deskripsi baru dari admin.')
            ->assertDontSee('Layanan Surat Digital');
    }

    public function test_landing_hero_text_falls_back_to_defaults(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Layanan Surat Digital')
            ->assertSee('Tanpa Antre, Kapan Saja')
            ->assertSee('Ajukan permohonan surat keterangan');
    }

    public function test_landing_kicker_omits_kecamatan_word(): void
    {
        KuaSetting::set('kecamatan', 'Ampelgading');

        $this->get('/')
            ->assertOk()
            ->assertSee('Kantor Urusan Agama Ampelgading')
            ->assertDontSee('Kantor Urusan Agama Kecamatan');
    }

    public function test_staff_can_upload_background(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'bg' => UploadedFile::fake()->image('bg.png', 1600, 900),
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $path = KuaSetting::get('bg_path');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_staff_can_replace_background_and_old_file_is_deleted(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('lama.png', 1600, 900)->store('welcome', 'public');
        KuaSetting::set('bg_path', $oldPath);

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'bg' => UploadedFile::fake()->image('baru.png', 1600, 900),
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $newPath = KuaSetting::get('bg_path');

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_staff_can_delete_background(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('bg.png', 1600, 900)->store('welcome', 'public');
        KuaSetting::set('bg_path', $path);

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'bg_hapus' => '1',
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $this->assertSame('', KuaSetting::get('bg_path'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_background_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'bg' => UploadedFile::fake()->create('bg.txt', 100),
            ]))
            ->assertSessionHasErrors('bg');

        $this->assertNull(KuaSetting::get('bg_path'));
    }

    public function test_landing_page_shows_background_when_available(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('bg.png', 1600, 900)->store('welcome', 'public');
        KuaSetting::set('bg_path', $path);

        $this->get('/')
            ->assertOk()
            ->assertSee('storage/welcome/', false);
    }

    public function test_landing_page_hides_background_when_absent(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('storage/welcome/', false);
    }

    public function test_footer_shows_medsos_links_when_set(): void
    {
        KuaSetting::set('sosmed_instagram', 'https://instagram.com/kua.contoh');
        KuaSetting::set('sosmed_tiktok', 'https://tiktok.com/@kua.contoh');
        KuaSetting::set('sosmed_whatsapp', 'https://wa.me/6281234567890');

        $this->get('/')
            ->assertOk()
            ->assertSee('Media Sosial')
            ->assertSee('https://instagram.com/kua.contoh')
            ->assertSee('https://tiktok.com/@kua.contoh')
            ->assertSee('https://wa.me/6281234567890')
            ->assertSee('Instagram')
            ->assertSee('WhatsApp');
    }

    public function test_footer_hides_medsos_column_when_empty(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Media Sosial')
            ->assertDontSee('Instagram');
    }

    public function test_settings_reject_invalid_sosmed_urls(): void
    {
        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), array_merge($this->basePayload(), [
                'sosmed_instagram' => 'bukan-url',
                'sosmed_whatsapp' => 'wa.me/6281234567890',
            ]))
            ->assertSessionHasErrors(['sosmed_instagram', 'sosmed_whatsapp']);
    }

    public function test_update_does_not_persist_file_or_hapus_flag_keys(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('kua-settings.update'), $this->basePayload([
                'hero' => UploadedFile::fake()->image('hero.png', 1280, 540),
                'hero_hapus' => '1',
                'logo_hapus' => '1',
            ]))
            ->assertRedirect(route('kua-settings.edit'));

        $this->assertNull(KuaSetting::where('key', 'hero')->first());
        $this->assertNull(KuaSetting::where('key', 'hero_hapus')->first());
        $this->assertNull(KuaSetting::where('key', 'logo_hapus')->first());
    }

    private function basePayload(array $extra = []): array
    {
        return array_merge([
            'instansi' => 'KUA Contoh',
            'alamat' => 'Jl. Contoh No. 1',
            'kepala_nama' => 'H. Contoh',
        ], $extra);
    }
}
