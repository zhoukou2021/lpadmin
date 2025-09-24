<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 全局时间格式化配置
    |--------------------------------------------------------------------------
    |
    | 这里配置全局时间格式化的相关设置
    |
    */

    // 是否启用全局时间格式化
    'enabled' => env('TIME_FORMAT_ENABLED', true),

    // 默认时间格式
    'format' => env('TIME_FORMAT', 'Y-m-d H:i:s'),

    // 需要格式化的时间字段
    'fields' => [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_login_at',
        'login_at',
        'logout_at',
        'expired_at',
        'verified_at',
        'email_verified_at',
        'phone_verified_at',
        'published_at',
        'started_at',
        'ended_at',
        'completed_at',
        'processed_at',
        'sent_at',
        'received_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'confirmed_at',
        'paid_at',
        'refunded_at',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'archived_at',
        'activated_at',
        'deactivated_at',
        'suspended_at',
        'restored_at',
        'locked_at',
        'unlocked_at',
        'synced_at',
        'imported_at',
        'exported_at',
        'backup_at',
        'maintenance_at',
    ],

    // 不同场景的时间格式
    'formats' => [
        'date' => 'Y-m-d',
        'time' => 'H:i:s',
        'datetime' => 'Y-m-d H:i:s',
        'timestamp' => 'Y-m-d H:i:s',
        'human' => 'Y年m月d日 H:i:s',
        'short' => 'm-d H:i',
        'iso' => 'c',
    ],

    // 排除的路由（不进行时间格式化）
    'exclude_routes' => [
        'api/export/*',
        'api/raw/*',
    ],

    // 排除的响应类型
    'exclude_content_types' => [
        'application/octet-stream',
        'text/csv',
        'application/pdf',
        'image/*',
        'video/*',
        'audio/*',
    ],
];
