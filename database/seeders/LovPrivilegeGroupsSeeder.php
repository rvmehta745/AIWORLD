<?php

namespace Database\Seeders;

use App\Models\LovPrivilegeGroups;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LovPrivilegeGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        LovPrivilegeGroups::truncate();
        LovPrivilegeGroups::create(
            [
                'name'       => config('global.LOV_PRIVILEGE_GROUPS.USERS_MANAGEMENT'),
                'is_default' => '0',
                'is_active'  => '1',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );
        Schema::enableForeignKeyConstraints();
    }
}
