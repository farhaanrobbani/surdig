<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name', 'email', 'email_verified_at', 'password', 'role', 'is_active',
    'nip', 'jabatan', 'level_jabatan', 'pangkat', 'ruang_golongan', 'grade_tukin',
    'jumlah_tukin_kotor', 'jumlah_tukin_bersih', 'gapok', 'jumlah_uang_makan_harian',
    'foto_profil_url', 'instansi',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_STAFF = 'staff';

    public const ROLE_OPERATOR = 'operator';

    public const ROLE_KEPALA = 'kepala';

    public const ROLE_SUPERADMIN = 'superadmin';

    public function isKepala(): bool
    {
        return $this->role === self::ROLE_KEPALA;
    }

    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function canManageContent(): bool
    {
        return $this->isOperator() || $this->isSuperadmin();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function staffActivities(): HasMany
    {
        return $this->hasMany(StaffActivity::class);
    }

    public function activityTemplates(): HasMany
    {
        return $this->hasMany(UserActivityTemplate::class);
    }

    public function fotoUrl(): ?string
    {
        if (blank($this->foto_profil_url)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_profil_url);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
