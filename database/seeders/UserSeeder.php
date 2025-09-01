<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => 'Ka Nunik',
            'student_id' => 24374653112,
            'email' => 'nunik@gmail.com',
            'password' => Hash::make('nunik1234'),
            'role' => User::ROLE_ADMIN,
            'balance' => 0,
            'qr_code' => null,
            'profile_photo' => null,
        ]);

        User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => 'Pak Harnoko',
            'student_id' => 24098976321,
            'email' => 'harnoko@gmail.com',
            'password' => Hash::make('harnoko1234'),
            'role' => User::ROLE_GURU,
            'balance' => 0,
            'qr_code' => null,
            'profile_photo' => null,
        ]);

        User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => 'Sultan',
            'student_id' => '878675465670',
            'email' => 'sultannafis24@gmail.com',
            'password' => Hash::make('sultan1324'),
            'role' => User::ROLE_CUSTOMER,
            'balance' => 100000,
            'qr_code' => null,
            'profile_photo' => null,
        ]);
    }
}
