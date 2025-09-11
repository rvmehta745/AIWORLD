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

        // User-specific limited privileges (Dashboard + Basic User functions)
        $userPrivilegeIds = LovPrivileges::whereIn('permission_key', [
            // Dashboard
            'DASHBOARD',
            'DASHBOARD_INDEX',
            
            // Profile Management
            'PROFILE',
            'PROFILE_UPDATE',
            
            // User Details (for viewing own profile)
            'USER_DETAILS',
            
            // Change Password
            'CHANGE_PASSWORD',
            
            // Terms & Conditions
            'TERMS_CONDITIONS',
            'TERMS_CONDITIONS_INDEX'
        ])->pluck('id')->toArray();

        // Admin privileges (All except Super Admin specific functions)
        $adminPrivilegeIds = LovPrivileges::whereNotIn('permission_key', [
            // Super Admin only functions (if any specific ones exist)
        ])->pluck('id')->toArray();

        $rolesInsert = [
            [
                "id"          => 1,
                'name'        => config('global.ROLES.SUPER_ADMIN'),
                'privileges'  => "#" . implode("#", $allPrivilegeIds) . "#", // Super Admin = All privileges
                'is_editable' => 0, // Not editable
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                "id"          => 2,
                'name'        => config('global.ROLES.ADMIN'),
                'privileges'  => "#" . implode("#", $adminPrivilegeIds) . "#", // Admin = All privileges but editable
                'is_editable' => 1, // Editable
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                "id"          => 3,
                'name'        => config('global.ROLES.USERS'),
                'privileges'  => "#" . implode("#", $userPrivilegeIds) . "#", // Users = Dashboard + Basic functions only
                'is_editable' => 1, // Editable
                'is_active'   => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ];

        foreach ($rolesInsert as $value) {
            Role::updateOrCreate(
                ['id' => $value['id']],
                $value
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}
