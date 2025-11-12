<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LPadmin Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the LPadmin backend
    | management system. You can modify these settings to customize the
    | behavior of the admin panel.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the routing behavior of the LPadmin system.
    | You can change the route prefix, name prefix, and domain as needed.
    |
    */
    'route' => [
        'prefix' => env('LPADMIN_ROUTE_PREFIX', 'lpadmin'),
        'name' => env('LPADMIN_ROUTE_NAME', 'lpadmin.'),
        'domain' => env('LPADMIN_DOMAIN', null),
        'middleware' => ['web', 'lpadmin.auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the authentication behavior for LPadmin.
    |
    */
    'auth' => [
        'guard' => 'lpadmin',
        'provider' => 'lpadmin_admins',
        'password_timeout' => 10800, // 3 hours
        'session_key' => 'lpadmin_auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the database tables used by LPadmin.
    |
    */
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'tables' => [
            'admins' => 'lp_admins',
            'admin_roles' => 'lp_admin_roles',
            'roles' => 'lp_roles',
            'rules' => 'lp_rules',
            'users' => 'lp_users',
            
            'options' => 'lp_options',
            'logs' => 'lp_admin_logs',
        ],
    ],

    

    /*
    |--------------------------------------------------------------------------
    | System Configuration
    |--------------------------------------------------------------------------
    |
    | General system configuration options.
    |
    */
    'system' => [
        'name' => env('LPADMIN_SYSTEM_NAME', 'LPadmin管理系统'),
        'version' => '1.0.1',
        'logo' => env('LPADMIN_LOGO', '/static/admin/images/logo.png'),
        'favicon' => env('LPADMIN_FAVICON', '/static/admin/images/favicon.ico'),
        'copyright' => env('LPADMIN_COPYRIGHT', 'Copyright © 2024 LPadmin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the permission system behavior.
    |
    */
    'permission' => [
        'cache_key' => 'lpadmin_permissions',
        'cache_ttl' => 3600, // 1 hour
        'super_admin_role' => 'super_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | System Information Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the system information displayed on the homepage.
    |
    */
    'system' => [
        'name' => env('LPADMIN_SYSTEM_NAME', 'LPadmin管理系统'),
        'version' => env('LPADMIN_SYSTEM_VERSION', '1.0.0'),
        'description' => env('LPADMIN_SYSTEM_DESC', '基于Laravel 10+和PearAdminLayui构建的现代化后台管理系统'),
        'logo' => env('LPADMIN_LOGO', '/static/admin/images/logo.png'),
        'copyright' => env('LPADMIN_COPYRIGHT', 'LPadmin'),
        'contact' => [
            'email' => env('LPADMIN_CONTACT_EMAIL', 'jiu-men##qq.com'),
            'phone' => env('LPADMIN_CONTACT_PHONE', '+86 157 / 3718 / 5084'),
            'address' => env('LPADMIN_CONTACT_ADDRESS', '中国·北京'),
        ],
        'social' => [
            'github' => env('LPADMIN_GITHUB', 'https://gitee.com/xw54/lpadmin'),
            'qq' => env('LPADMIN_QQ', '446820025'),
            'wechat' => env('LPADMIN_WECHAT', 'Baron369'),
        ],
    ],
];
