<?php

return [

    'MAIL_TEMPLATE'    => [
        'RESET_PASSWORD' => "Reset Password",
        'WELCOME_EMAIL'  => "Welcome Email",
    ],

    'ROLES'        => [
        'ADMIN' => 'Admin',
        'DISPOSITION_MANAGER' => 'Disposition Manager',
        'BUYER' => 'Buyer',
    ],

    'STATUS' => [
        'ACTIVE'   => ['id' => 1, 'name' => 'Active'],
        'INACTIVE' => ['id' => 0, 'name' => 'Inactive'],
    ],

    'LOV_PRIVILEGE_GROUPS' => [
        'USERS_MANAGEMENT' => 'Users Management',
    ],
    
    // Default role ID for newly registered users
    'default_role_id' => env('DEFAULT_ROLE_ID', 'Buyer'), // Assuming Buyer is a basic user role
];
