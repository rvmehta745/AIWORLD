<?php

namespace Database\Seeders;

use App\Models\LovPrivilegeGroups;
use App\Models\LovPrivileges;
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
        $id = 10000;

        $parentData = [];

        // Dashboard
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 1,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Dashboard',
            'path' => '/',
            'permission_key' => 'DASHBOARD',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'Dashboard', 'path' => '/', 'permission_key' => 'DASHBOARD_INDEX', 'is_active' => 1],
            ]
        ];

        // --------------------------
        // NEW MENU ITEMS (from sidebar)
        // --------------------------

        // Products
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 5,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Products',
            'path' => '/products',
            'permission_key' => 'PRODUCTS',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/products', 'permission_key' => 'PRODUCTS_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/products/create', 'permission_key' => 'PRODUCTS_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Edit', 'path' => '/products/{id}/edit', 'permission_key' => 'PRODUCTS_EDIT', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Delete', 'path' => '/products/{id}/delete', 'permission_key' => 'PRODUCTS_DELETE', 'is_active' => 1],
            ]
        ];

        // Product Types
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 6,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Product Types',
            'path' => '/product-types',
            'permission_key' => 'PRODUCT_TYPES',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/product-types', 'permission_key' => 'PRODUCT_TYPES_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/product-types/create', 'permission_key' => 'PRODUCT_TYPES_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Edit', 'path' => '/product-types/{id}/edit', 'permission_key' => 'PRODUCT_TYPES_EDIT', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Delete', 'path' => '/product-types/{id}/delete', 'permission_key' => 'PRODUCT_TYPES_DELETE', 'is_active' => 1],
            ]
        ];

        // Categories
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 7,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Categories',
            'path' => '/categories',
            'permission_key' => 'CATEGORIES',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/categories', 'permission_key' => 'CATEGORIES_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/categories/create', 'permission_key' => 'CATEGORIES_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Edit', 'path' => '/categories/{id}/edit', 'permission_key' => 'CATEGORIES_EDIT', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Delete', 'path' => '/categories/{id}/delete', 'permission_key' => 'CATEGORIES_DELETE', 'is_active' => 1],
            ]
        ];

        // Pricing Types
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 8,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Pricing Types',
            'path' => '/pricing-types',
            'permission_key' => 'PRICING_TYPES',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/pricing-types', 'permission_key' => 'PRICING_TYPES_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/pricing-types/create', 'permission_key' => 'PRICING_TYPES_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Edit', 'path' => '/pricing-types/{id}/edit', 'permission_key' => 'PRICING_TYPES_EDIT', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Delete', 'path' => '/pricing-types/{id}/delete', 'permission_key' => 'PRICING_TYPES_DELETE', 'is_active' => 1],
            ]
        ];

        // Featured
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 9,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Featured',
            'path' => '/featured',
            'permission_key' => 'FEATURED',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/featured', 'permission_key' => 'FEATURED_INDEX', 'is_active' => 1],
            ]
        ];

        // Subscriptions
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 10,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Subscriptions',
            'path' => '/subscriptions',
            'permission_key' => 'SUBSCRIPTIONS',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/subscriptions', 'permission_key' => 'SUBSCRIPTIONS_INDEX', 'is_active' => 1],
            ]
        ];

        // Users
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 11,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Users',
            'path' => '/users',
            'permission_key' => 'USER_MANAGEMENT',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/users', 'permission_key' => 'USER_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/users/create', 'permission_key' => 'USER_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Details', 'path' => '/users/{id}/details', 'permission_key' => 'USER_DETAILS', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Update', 'path' => '/users/{id}/update', 'permission_key' => 'USER_UPDATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 5, 'group_id' => 0, 'parent_id' => $id - 5, 'name' => 'Delete', 'path' => '/users/{id}/delete', 'permission_key' => 'USER_DELETE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 6, 'group_id' => 0, 'parent_id' => $id - 6, 'name' => 'Change Status', 'path' => '/users/{id}/change-status', 'permission_key' => 'USER_CHANGE_STATUS', 'is_active' => 1],
            ]
        ];

        // Settings
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 12,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Settings',
            'path' => '/settings',
            'permission_key' => 'SETTINGS',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'View', 'path' => '/settings', 'permission_key' => 'SETTINGS_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Update', 'path' => '/settings/update', 'permission_key' => 'SETTINGS_UPDATE', 'is_active' => 1],
            ]
        ];

        // Role Management
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 13,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Role Management',
            'path' => '/roles',
            'permission_key' => 'ROLE_MANAGEMENT',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'List', 'path' => '/roles', 'permission_key' => 'ROLE_MANAGEMENT_INDEX', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 2, 'group_id' => 0, 'parent_id' => $id - 2, 'name' => 'Create', 'path' => '/roles/create', 'permission_key' => 'ROLE_MANAGEMENT_CREATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 3, 'group_id' => 0, 'parent_id' => $id - 3, 'name' => 'Details', 'path' => '/roles/{id}/details', 'permission_key' => 'ROLE_MANAGEMENT_DETAILS', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 4, 'group_id' => 0, 'parent_id' => $id - 4, 'name' => 'Update', 'path' => '/roles/{id}/update', 'permission_key' => 'ROLE_MANAGEMENT_UPDATE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 5, 'group_id' => 0, 'parent_id' => $id - 5, 'name' => 'Delete', 'path' => '/roles/{id}/delete', 'permission_key' => 'ROLE_MANAGEMENT_DELETE', 'is_active' => 1],
                ['id' => $id += 1, 'sequence' => 6, 'group_id' => 0, 'parent_id' => $id - 6, 'name' => 'Change Status', 'path' => '/roles/{id}/change-status', 'permission_key' => 'ROLE_MANAGEMENT_CHANGE_STATUS', 'is_active' => 1],
            ]
        ];
        // Profile Management
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 14,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Profile',
            'path' => '/profile',
            'permission_key' => 'PROFILE',
            'is_active' => 1,
            'childData' => [
                ['id' => $id += 1, 'sequence' => 1, 'group_id' => 0, 'parent_id' => $id - 1, 'name' => 'Update', 'path' => '/profile/update', 'permission_key' => 'PROFILE_UPDATE', 'is_active' => 1],
            ]
        ];

        // Terms & Conditions
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 15,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Terms & Conditions',
            'path' => '/terms-conditions',
            'permission_key' => 'TERMS_CONDITIONS',
            'is_active' => 1,
            'childData' => []
        ];

        // Menu Generate privilege
        $parentData[] = [
            'id'        => $id += 1,
            'sequence'  => 16,
            'group_id'  => 0,
            'parent_id' => 0,
            'name'      => 'Generate Menu',
            'path' => '', // Empty path to prevent appearing in menu
            'permission_key' => 'MENU_GENERATE',
            'is_active' => 1,
            'childData' => []
        ];

        // --------------------------
        // Save into DB
        // --------------------------
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
