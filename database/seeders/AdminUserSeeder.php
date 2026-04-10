<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@infinix-roxy.local'],
            [
                'name' => 'Admin Infinix',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
            ]
        );
    }
}
