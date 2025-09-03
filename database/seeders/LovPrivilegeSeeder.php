<?php

namespace Database\Seeders;

use App\Models\LovPrivilegeGroups;
use App\Models\LovPrivileges;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LovPrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LovPrivilegeGroupsSeeder::class);

        LovPrivileges::truncate();
        Schema::disableForeignKeyConstraints();
        $id         = 10000;

        // Dashboard
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 1,
            'group_id'  => 0, 'parent_id' => 0,
            'name'      => 'Dashboard', 'path' => '/', 'permission_key' => 'DASHBOARD',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'Dashboard', 'path' => '/', 'permission_key' => 'DASHBOARD_INDEX', 'is_active' => 1],
            ]
        ];
        // Properties
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 2,
            'group_id'  => 0, 'parent_id' => 0,
            'name'      => 'Properties', 'path' => '/', 'permission_key' => 'PROPERTIES',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'Properties', 'path' => '/', 'permission_key' => 'PROPERTIES_INDEX', 'is_active' => 1],
            ]
        ];
        // Property Inquiries
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 3,
            'group_id'  => 0, 'parent_id' => 0,
            'name'      => 'Property Inquiries', 'path' => '/', 'permission_key' => 'PROPERTY_INQUIRIES',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'Property Inquiries', 'path' => '/', 'permission_key' => 'PROPERTY_INQUIRIES_INDEX', 'is_active' => 1],
            ]
        ];

        // Disposition Manager
        $usersManagementGroupId = 0;
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 4,
            'group_id'  => $usersManagementGroupId, 'parent_id' => 0,
            'name'      => 'Disposition Manager Management', 'path' => '/disposition-managers', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/disposition-managers', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 2, 'name' => 'New', 'path' => '/disposition-managers/create', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 3, 'name' => 'Detail View', 'path' => '/disposition-managers/{id}/details', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_DETAILS', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 4, 'name' => 'Edit', 'path' => '/disposition-managers/{id}/update', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_UPDATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 5, 'name' => 'Delete', 'path' => '/disposition-managers/{id}/delete', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_DELETE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 6, 'name' => 'Change Status', 'path' => '/disposition-managers/{id}/change-status', 'permission_key' => 'DISPOSITION_MANAGER_MANAGEMENT_CHANGE_STATUS', 'is_active' => 1],
            ]
        ];
        

        // // Edit Profile
        // $parentData[] = [
        //     'id'        => $id += 1,
        //     'sequence'  => 5,
        //     'group_id'  => $usersManagementGroupId, 'parent_id' => 0,
        //     'name'      => 'Edit Profile', 'path' => '/profile', 'permission_key' => 'PROFILE',
        //     'is_active' => 1,
        //     'childData' => [
        //         ['id' => $id += 1, 'sequence' => 7, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 1, 'name' => 'View', 'path' => '/profile', 'permission_key' => 'PROFILE_INDEX', 'is_active' => 1],
        //         ['id' => $id += 1, 'sequence' => 7, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 2, 'name' => 'Update', 'path' => '/profile/update', 'permission_key' => 'PROFILE_UPDATE', 'is_active' => 1],
        //     ]
        // ];

        // // Change Password
        // $parentData[] = [
        //     'id'        => $id += 1,
        //     'sequence'  => 6,
        //     'group_id'  => $usersManagementGroupId, 'parent_id' => 0,
        //     'name'      => 'Change Password', 'path' => '/change-password', 'permission_key' => 'CHANGE_PASSWORD',
        //     'is_active' => 1,
        //     'childData' => [
        //         ['id' => $id += 1, 'sequence' => 8, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 1, 'name' => 'View', 'path' => '/change-password', 'permission_key' => 'CHANGE_PASSWORD_INDEX', 'is_active' => 1],
        //         ['id' => $id += 1, 'sequence' => 8, 'group_id' => $usersManagementGroupId, 'parent_id' => $id - 2, 'name' => 'Update', 'path' => '/change-password/update', 'permission_key' => 'CHANGE_PASSWORD_UPDATE', 'is_active' => 1],
        //     ]
        // ];

        foreach ($parentData as $value) {
            LovPrivileges::create([
                'id'             => $value['id'],
                'sequence'       => $value['sequence'],
                'group_id'       => $value['group_id'],
                'parent_id'      => $value['parent_id'],
                'name'           => $value['name'],
                'path'           => $value['path'],
                'permission_key' => $value['permission_key'],
                'is_active'      => $value['is_active'],
            ]);

            if (!empty($value['childData'])) {
                foreach ($value['childData'] as $value1) {
                    LovPrivileges::create([
                        'id'             => $value1['id'],
                        'sequence'       => $value1['sequence'],
                        'group_id'       => $value1['group_id'],
                        'parent_id'      => $value1['parent_id'],
                        'name'           => $value1['name'],
                        'path'           => $value1['path'],
                        'permission_key' => $value1['permission_key'],
                        'is_active'      => $value1['is_active'],
                    ]);
                }
            }
        }

        $this->call(RoleSeeder::class);
        Schema::enableForeignKeyConstraints();
    }
}
