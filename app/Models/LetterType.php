<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'permohonan_judul', 'description', 'permohonan_body', 'permohonan_informasi', 'permohonan_fields', 'fields', 'active', 'publik', 'kop_footer', 'kop_footer_enabled'])]
class LetterType extends Model
{
    use HasFactory;

    public function scopePublik($query)
    {
        return $query->where('publik', true);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(LetterTemplate::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    public static function normalizeFieldOptions(array $fields): array
    {
        return array_values(array_map(function (array $field) {
            $field['required'] = (bool) filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $field['internal'] = (bool) filter_var($field['internal'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (($field['type'] ?? null) !== 'select') {
                unset($field['options']);

                return $field;
            }

            $options = [];
            foreach ($field['options'] ?? [] as $item) {
                foreach (explode(',', (string) $item) as $option) {
                    $option = trim($option);
                    if ($option !== '') {
                        $options[] = $option;
                    }
                }
            }

            $field['options'] = array_values(array_unique($options));

            return $field;
        }, $fields));
    }

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'permohonan_fields' => 'array',
            'active' => 'boolean',
            'publik' => 'boolean',
            'kop_footer_enabled' => 'boolean',
        ];
    }
}
