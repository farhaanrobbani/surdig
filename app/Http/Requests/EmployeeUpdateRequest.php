<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'pangkat' => ['nullable', 'string', 'max:255'],
            'ruang_golongan' => ['nullable', 'string', 'max:50'],
            'grade_tukin' => ['nullable', 'integer', 'min:0', 'max:30'],
            'jumlah_tukin_kotor' => ['nullable', 'numeric', 'min:0'],
            'jumlah_tukin_bersih' => ['nullable', 'numeric', 'min:0'],
            'gapok' => ['nullable', 'numeric', 'min:0'],
            'jumlah_uang_makan_harian' => ['nullable', 'numeric', 'min:0'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'foto_hapus' => ['sometimes', 'in:1'],
        ];
    }
}
