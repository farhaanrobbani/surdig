<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\ColonTableFormatter;
use App\Support\HtmlSanitizer;

#[Fillable([
    'letter_type_id',
    'nomor',
    'tanggal_surat',
    'perihal',
    'header_html',
    'tampilkan_tanggal',
    'tampilkan_tanggal_ttd',
    'data',
    'status',
    'created_by',
    'approved_by',
    'approved_at',
    'keterangan',
])]
class Letter extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_TERBIT = 'terbit';
    public const STATUS_DITOLAK = 'ditolak';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_TERBIT => 'Terbit',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activeTemplate(): ?LetterTemplate
    {
        return $this->letterType->templates()->where('active', true)->first();
    }

    public function renderBody(): string
    {
        $template = $this->activeTemplate();
        if (! $template) {
            return 'Template surat belum tersedia untuk jenis surat ini.';
        }

        $values = [];
        foreach ($this->letterType->fields ?? [] as $field) {
            $values[$field['name']] = $this->data[$field['name']] ?? '';
        }

        $values['nomor'] = $this->nomor ?? '';
        $values['tanggal_surat'] = $this->tanggal_surat ? tanggal_indonesia($this->tanggal_surat) : '';
        $values['perihal'] = $this->perihal;

        $settingKeys = ['instansi', 'alamat', 'kecamatan', 'kabupaten', 'kode_pos', 'telepon', 'email',
            'kepala_nama', 'kepala_nip', 'kepala_pangkat', 'sk_kepala'];
        foreach ($settingKeys as $key) {
            if (! array_key_exists($key, $values)) {
                $values[$key] = KuaSetting::get($key) ?? '';
            }
        }

        $body = ColonTableFormatter::format(HtmlSanitizer::toHtml($template->body));
        foreach ($values as $key => $value) {
            $body = str_replace('[' . $key . ']', e((string) $value), $body);
        }

        return HtmlSanitizer::sanitize($body);
    }

    public static function defaultHeader(): string
    {
        return '<p>Nomor : {nomor}</p><p>Lampiran : -</p><p>Perihal : {perihal}</p>';
    }

    public function headerHtml(): string
    {
        $header = trim((string) ($this->header_html ?? ''));

        return $header === '' ? self::defaultHeader() : $header;
    }

    public function renderHeader(): string
    {
        $values = [
            'nomor' => $this->nomor ?? '',
            'perihal' => $this->perihal ?? '',
            'tanggal_surat' => $this->tanggal_surat ? tanggal_indonesia($this->tanggal_surat) : '',
        ];

        $header = $this->headerHtml();
        foreach ($values as $key => $value) {
            $header = str_replace('{' . $key . '}', e((string) $value), $header);
        }

        return HtmlSanitizer::sanitize($header);
    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'tanggal_surat' => 'date',
            'tampilkan_tanggal' => 'boolean',
            'tampilkan_tanggal_ttd' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }
}
