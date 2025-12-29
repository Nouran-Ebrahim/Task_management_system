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
        //manager user
        User::create([
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'manager'
        ]);
        // users
        for ($i = 0; $i < 2; $i++) {
            User::create([
                'name' => "User{$i}",
                'email' => "user{$i}@gmail.com",
                'password' => Hash::make("password{$i}"),
                'role' => 'user'
            ]);

        }
    }
}
