<?php

namespace Database\Seeders;

use App\Models\LovPrivileges;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        $allPrivilegeIds = LovPrivileges::all()->pluck('id')->toArray() ?? [];

        // User-specific limited privileges
        $userPrivilegeIds = LovPrivileges::whereIn('permission_key', [
            // Properties
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
                'name'        => config('global.ROLES.SUPER_ADMIN'),
                'privileges'  => "#" . implode("#", $allPrivilegeIds) . "#", // Super Admin = All privileges
                'is_editable' => 0,
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                "id"          => 2,
                'name'        => config('global.ROLES.ADMIN'),
                'privileges'  => "#" . implode("#", $allPrivilegeIds) . "#", // Admin also has all privileges
                'is_editable' => 0,
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                "id"          => 3,
                'name'        => config('global.ROLES.USERS'),
                'privileges'  => "#" . implode("#", $userPrivilegeIds) . "#", // Limited privileges
                'is_editable' => 1,
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ];

        foreach ($rolesInsert as $value) {
            Role::upsert($value, ['id'], ['name', 'privileges', 'is_editable', 'is_active', 'updated_at']);
        }

        Schema::enableForeignKeyConstraints();
    }
}
