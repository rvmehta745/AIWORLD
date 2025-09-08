<?php

return [

    'MAIL_TEMPLATE'    => [
        'RESET_PASSWORD' => "Reset Password",
        'WELCOME_EMAIL'  => "Welcome Email",
    ],

    'ROLES'        => [
        'SUPER_ADMIN' => 'Super Admin',
        'ADMIN' => 'Admin',
        'USERS' => 'Users',
    ],

    'STATUS' => [
        'ACTIVE'   => ['id' => 1, 'name' => 'Active'],
        'INACTIVE' => ['id' => 0, 'name' => 'Inactive'],
    ],

    'LOV_PRIVILEGE_GROUPS' => [
        'USERS_MANAGEMENT' => 'Users Management',
    ],
    
    // Default role ID for newly registered users
    'default_role_id' => env('DEFAULT_ROLE_ID', 'Users'), // Assuming Users is a basic user role
];
