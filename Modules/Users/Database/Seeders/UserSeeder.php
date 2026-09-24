<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Users\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Create a clearly fictional admin account plus a small pool of
     * "Agricultor" users so Quartels and Tasks can have varied responsibles.
     *
     * Why multiple Agricultor users: QuarterSeeder and TaskSeeder both pick
     * a random responsible from `User::all()`. With only the admin user,
     * every quarter and every task ends up with the same responsible,
     * which makes the demo data feel fake.
     */
    public function run(): void
    {
        // Skip if the admin user is already present (idempotent re-runs).
        if (User::where('email', 'admin@example.cl')->exists()) {
            return;
        }

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

        // Six fictional Chilean Agricultor users. DNI is Chilean national ID.
        $agricultores = [
            ['Juan', 'Pérez', '15.234.678-9', '+56912345678'],
            ['María', 'González', '12.876.543-2', '+56923456789'],
            ['Pedro', 'Rojas', '17.654.321-0', '+56934567890'],
            ['Carolina', 'Muñoz', '14.321.987-6', '+56945678901'],
            ['Andrés', 'Sepúlveda', '16.789.012-3', '+56956789012'],
            ['Francisca', 'Castro', '13.456.789-4', '+56967890123'],
        ];

        foreach ($agricultores as [$name, $lastName, $dni, $phone]) {
            $user = User::create([
                'name' => $name,
                'last_name' => $lastName,
                'email' => strtolower($name.'.'.$lastName).'@terratrace.cl',
                'dni' => $dni,
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'phone' => $phone,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            $user->assignRole('Agricultor');
        }
    }
}
