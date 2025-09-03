<?php

namespace Database\Seeders;

use App\Models\LovPrivileges;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        //        Role::truncate();
        $roleIds = LovPrivileges::all()->pluck('id')->toArray() ?? [];

        // Get specific privilege IDs for Users role
        $userPrivilegeIds = LovPrivileges::whereIn('permission_key', [
            // Property
            'PROPERTIES',
            'PROPERTIES_INDEX',
            // Edit Profile
            'PROFILE',
            'PROFILE_INDEX',
            'PROFILE_UPDATE',
            'USER_DETAILS',
            // Change Password
            'CHANGE_PASSWORD',
            'CHANGE_PASSWORD_INDEX',
            'CHANGE_PASSWORD_UPDATE'
        ])->pluck('id')->toArray();

        $rolesInsert = [
            [
                "id"          => 1,
                'name'        => config('global.ROLES.ADMIN'),
                'privileges'  => "#" . implode("#", $roleIds) . "#",
                'is_editable' => 0,
                'is_active'   => 1,
                'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'  => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "id"          => 2,
                'name'        => config('global.ROLES.DISPOSITION_MANAGER'),
                'privileges'  => "#" . implode("#", $userPrivilegeIds) . "#",
                'is_editable' => 1,
                'is_active'   => 1,
                'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'  => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "id"          => 3,
                'name'        => config('global.ROLES.BUYER'),
                'privileges'  => "#" . implode("#", $userPrivilegeIds) . "#",
                'is_editable' => 1,
                'is_active'   => 1,
                'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'  => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ];
        foreach ($rolesInsert as $value) {
            $update = Role::upsert($value, ['id'], ['id', 'name', 'privileges', 'is_editable', 'is_active', 'created_at', 'updated_at']);
        }

        Schema::enableForeignKeyConstraints();
    }
}
