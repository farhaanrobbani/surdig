<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KuaSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KuaSettingController extends Controller
{
    private const GROUPS = [
        'web' => [
            'label' => 'Web',
            'keys' => [
                'hero_judul',
                'hero_subjudul',
                'bg_path',
                'hero_path',
                'jam_layanan',
            ],
        ],
        'instansi' => [
            'label' => 'Instansi',
            'keys' => [
                'instansi',
                'alamat',
                'telepon',
                'email',
                'kecamatan',
                'kabupaten',
                'kode_pos',
                'sosmed_instagram',
                'sosmed_tiktok',
                'sosmed_whatsapp',
                'link_terkait',
            ],
        ],
        'surat' => [
            'label' => 'Surat',
            'keys' => [
                'logo_path',
                'logo2_path',
                'kop_logo',
                'kop_teks',
                'kop_ukuran_judul',
                'kop_ukuran_sub',
                'kop_ukuran_sub2',
                'kop_ukuran_baris',
            ],
        ],
        'kepala' => [
            'label' => 'Kepala & Tanda Tangan',
            'keys' => [
                'kepala_nama',
                'kepala_nip',
                'kepala_pangkat',
                'sk_kepala',
                'kepala_kemenag_nama',
                'kepala_kemenag_nip',
                'kop_anchor',
            ],
        ],
        'notif' => [
            'label' => 'Notifikasi',
            'keys' => [
                'telegram_bot_token',
                'telegram_chat_id',
            ],
        ],
    ];

    public function edit(): View
    {
        $settings = [];
        foreach (self::GROUPS as $group) {
            foreach ($group['keys'] as $key) {
                $settings[$key] = ['label' => $key, 'value' => KuaSetting::get($key)];
            }
        }

        return view('admin.kua-settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'instansi' => ['required', 'string', 'max:200'],
            'alamat' => ['required', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'kecamatan' => ['nullable', 'string', 'max:150'],
            'kabupaten' => ['nullable', 'string', 'max:150'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'sosmed_instagram' => ['nullable', 'url', 'max:255'],
            'sosmed_tiktok' => ['nullable', 'url', 'max:255'],
            'sosmed_whatsapp' => ['nullable', 'url', 'max:255'],
            'link_terkait' => ['nullable', 'string', 'max:5000'],
            'jam_layanan' => ['nullable', 'string', 'max:255'],
            'kepala_nama' => ['required', 'string', 'max:150'],
            'kepala_nip' => ['nullable', 'string', 'max:50'],
            'kepala_pangkat' => ['nullable', 'string', 'max:100'],
            'sk_kepala' => ['nullable', 'string', 'max:150'],
            'kepala_kemenag_nama' => ['nullable', 'string', 'max:150'],
            'kepala_kemenag_nip' => ['nullable', 'string', 'max:50'],
            'kop_anchor' => ['nullable', 'in:1,0'],
            'logo' => ['sometimes', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo_hapus' => ['sometimes', 'in:1'],
            'logo2' => ['sometimes', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo2_hapus' => ['sometimes', 'in:1'],
            'kop_logo' => ['nullable', 'in:logo1,logo2'],
            'kop_teks' => ['nullable', 'string', 'max:2000'],
            'kop_ukuran_judul' => ['nullable', 'numeric', 'min:6', 'max:72'],
            'kop_ukuran_sub' => ['nullable', 'numeric', 'min:6', 'max:72'],
            'kop_ukuran_sub2' => ['nullable', 'numeric', 'min:6', 'max:72'],
            'kop_ukuran_baris' => ['nullable', 'numeric', 'min:6', 'max:72'],
            'hero' => ['sometimes', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'hero_hapus' => ['sometimes', 'in:1'],
            'bg' => ['sometimes', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'bg_hapus' => ['sometimes', 'in:1'],
            'hero_judul' => ['nullable', 'string', 'max:255'],
            'hero_subjudul' => ['nullable', 'string', 'max:500'],
            'telegram_bot_token' => ['nullable', 'string', 'max:200'],
            'telegram_chat_id' => ['nullable', 'string', 'max:50'],
        ]);

        if ($request->hasFile('hero')) {
            $old = KuaSetting::get('hero_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('hero')->store('heroes', 'public');
            KuaSetting::set('hero_path', $path);
        } elseif ($request->boolean('hero_hapus')) {
            $old = KuaSetting::get('hero_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            KuaSetting::set('hero_path', '');
        }

        if ($request->hasFile('bg')) {
            $old = KuaSetting::get('bg_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('bg')->store('welcome', 'public');
            KuaSetting::set('bg_path', $path);
        } elseif ($request->boolean('bg_hapus')) {
            $old = KuaSetting::get('bg_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            KuaSetting::set('bg_path', '');
        }

        if ($request->hasFile('logo')) {
            $old = KuaSetting::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('logo')->store('logos', 'public');
            KuaSetting::set('logo_path', $path);
        } elseif ($request->boolean('logo_hapus')) {
            $old = KuaSetting::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            KuaSetting::set('logo_path', '');
        }

        if ($request->hasFile('logo2')) {
            $old = KuaSetting::get('logo2_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('logo2')->store('logos2', 'public');
            KuaSetting::set('logo2_path', $path);
        } elseif ($request->boolean('logo2_hapus')) {
            $old = KuaSetting::get('logo2_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            KuaSetting::set('logo2_path', '');
        }

        if (isset($validated['link_terkait'])) {
            $rawLinks = json_decode($validated['link_terkait'], true);
            if (! is_array($rawLinks)) {
                $rawLinks = [];
            }
            $links = collect($rawLinks)
                ->filter(fn (array $l) => ! empty(trim($l['label'] ?? '')) && ! empty(trim($l['url'] ?? '')))
                ->map(fn (array $l) => ['label' => trim($l['label']), 'url' => trim($l['url'])])
                ->values()
                ->all();
            $validated['link_terkait'] = json_encode($links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $ignored = ['hero', 'hero_hapus', 'bg', 'bg_hapus', 'logo', 'logo_hapus', 'logo2', 'logo2_hapus'];
        $passwordFields = ['telegram_bot_token', 'telegram_chat_id'];
        foreach ($validated as $key => $value) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            if (in_array($key, $passwordFields, true) && $value === '') {
                continue;
            }

            KuaSetting::set($key, $value);
        }

        return redirect()->route('kua-settings.edit')
            ->with('success', 'Pengaturan Web berhasil disimpan.');
    }

    public function testTelegram(Request $request): JsonResponse
    {
        $botToken = $request->input('telegram_bot_token') ?: kua_setting('telegram_bot_token');
        $chatId = $request->input('telegram_chat_id') ?: kua_setting('telegram_chat_id');

        if (empty($botToken) || empty($chatId)) {
            return response()->json([
                'ok' => false,
                'message' => 'Bot Token dan Chat ID harus diisi.',
            ]);
        }

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => "✅ <b>Test Koneksi Berhasil</b>\n\nNotifikasi Telegram dari surdig sudah terhubung.",
                    'parse_mode' => 'HTML',
                ]
            );

            if ($response->successful()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Pesan test berhasil dikirim! Cek grup Telegram.',
                ]);
            }

            $error = $response->json('description') ?? 'Unknown error';

            return response()->json([
                'ok' => false,
                'message' => "Gagal: {$error}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal terhubung: ' . $e->getMessage(),
            ]);
        }
    }
}
