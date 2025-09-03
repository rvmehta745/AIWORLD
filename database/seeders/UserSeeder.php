<?php

namespace Database\Seeders;

use App\Models\LovPrivilegeGroups;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        User::create([
            'first_name' => "Super",
            'last_name' => "Admin",
            'email'=> "admin@admin.com",
            'phone_number' => 11111111,
            'country_code' => '+1',
            'password' => bcrypt('Admin@1234'),
            'role' => 'Admin',
            'is_active' => 1,
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
