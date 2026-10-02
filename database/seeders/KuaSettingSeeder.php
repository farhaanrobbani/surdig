<?php

namespace Database\Seeders;

use App\Models\KuaSetting;
use Illuminate\Database\Seeder;

class KuaSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'instansi' => 'KANTOR URUSAN AGAMA',
            'alamat' => 'Jl. Masjid Agung No. 1',
            'telepon' => '(021) 1234567',
            'email' => 'kua@example.com',
            'kecamatan' => 'Contoh',
            'kabupaten' => 'Contoh',
            'kode_pos' => '00000',
            'kepala_nama' => 'H. Nama Kepala KUA, S.Ag., M.H.',
            'kepala_nip' => '197001011990011001',
            'kepala_pangkat' => 'Pembina, IV/a',
            'sk_kepala' => '',
            'kepala_kemenag_nama' => '',
            'kepala_kemenag_nip' => '',
            'kop_anchor' => '1',
        ];

        foreach ($defaults as $key => $value) {
            KuaSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
