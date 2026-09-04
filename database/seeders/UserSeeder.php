<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Staf KUA',
                'email' => env('STAFF_EMAIL') ?: 'staf@kua.local',
                'password' => env('STAFF_PASSWORD') ?: 'password',
                'role' => User::ROLE_STAFF,
            ],
            [
                'name' => 'Operator KUA',
                'email' => env('OPERATOR_EMAIL') ?: 'operator@kua.local',
                'password' => env('OPERATOR_PASSWORD') ?: 'password',
                'role' => User::ROLE_OPERATOR,
            ],
            [
                'name' => 'Kepala KUA',
                'email' => env('KEPALA_EMAIL') ?: 'kepala@kua.local',
                'password' => env('KEPALA_PASSWORD') ?: 'password',
                'role' => User::ROLE_KEPALA,
            ],
            [
                'name' => 'Superadmin KUA',
                'email' => env('SUPERADMIN_EMAIL') ?: 'superadmin@kua.local',
                'password' => env('SUPERADMIN_PASSWORD') ?: 'password',
                'role' => User::ROLE_SUPERADMIN,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );
        }
    }
}
