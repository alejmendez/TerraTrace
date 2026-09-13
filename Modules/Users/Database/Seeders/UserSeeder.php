<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Users\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Create one clearly fictional account for local demonstrations only.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'last_name' => 'Demo',
            'email' => 'admin@example.cl',
            'dni' => '19.726.905-7',
            'password' => Hash::make('12345678'),
            'avatar' => null,
            'phone' => '0000000000',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ])->assignRole('Super Admin');
    }
}
