<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin accounts
        User::create([
            'name' => 'Admin One',
            'role' => 'admin',
            'email' => 'admin1@email.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Admin Two',
            'role' => 'admin',
            'email' => 'admin2@email.com',
            'password' => Hash::make('password'),
        ]);

        // Worker accounts
        User::create([
            'name' => 'Worker One',
            'role' => 'worker',
            'email' => 'worker1@email.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Worker Two',
            'role' => 'worker',
            'email' => 'worker2@email.com',
            'password' => Hash::make('password'),
        ]);
    }
}
