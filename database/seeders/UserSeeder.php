<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();

        $users = [
            [
                'first_name'   => "Super",
                'last_name'    => "Admin",
                'email'        => "aiworldsuperadmin@yopmail.com",
                'phone_number' => 1111111111,
                'country_code' => '+1',
                'password'     => Hash::make('Admin@1234'),
                'role'         => "Super Admin", // Super Admin
                'is_active'    => 1,
            ],
            [
                'first_name'   => "Admin",
                'last_name'    => "User",
                'email'        => "aiworldadmin@yopmail.com",
                'phone_number' => 2222222222,
                'country_code' => '+1',
                'password'     => Hash::make('Admin@1234'),
                'role'         => "Admin", // Admin
                'is_active'    => 1,
            ],
            [
                'first_name'   => "Normal",
                'last_name'    => "User",
                'email'        => "aiworld@yopmail.com",
                'phone_number' => 3333333333,
                'country_code' => '+1',
                'password'     => Hash::make('User@1234'),
                'role'         => "Users", // User
                'is_active'    => 1,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        Schema::enableForeignKeyConstraints();
    }
}
