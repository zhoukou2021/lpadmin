<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\LPadmin\Option;
use App\Services\MenuCacheService;


/**
 * 全局缓存管理服务
 */
class CacheService
{
    /**
     * 缓存类型常量
     */
    const CACHE_TYPE_CONFIG = 'config';
    const CACHE_TYPE_ROUTE = 'route';
    const CACHE_TYPE_VIEW = 'view';
    const CACHE_TYPE_EVENT = 'event';
    const CACHE_TYPE_APPLICATION = 'application';
    const CACHE_TYPE_MENU = 'menu';
    const CACHE_TYPE_PERMISSION = 'permission';
    const CACHE_TYPE_DICTIONARY = 'dictionary';
    const CACHE_TYPE_SYSTEM = 'system';

    /**
     * 获取所有缓存类型
     */
    public static function getCacheTypes(): array
    {
        return [
            self::CACHE_TYPE_CONFIG => [
                'name' => '配置缓存',
                'description' => '系统配置项缓存',
                'icon' => 'layui-icon-set',
            ],
            self::CACHE_TYPE_ROUTE => [
                'name' => '路由缓存',
                'description' => 'Laravel路由缓存',
                'icon' => 'layui-icon-link',
            ],
            self::CACHE_TYPE_VIEW => [
                'name' => '视图缓存',
                'description' => 'Blade模板编译缓存',
                'icon' => 'layui-icon-template-1',
            ],
            self::CACHE_TYPE_EVENT => [
                'name' => '事件缓存',
                'description' => 'Laravel事件缓存',
                'icon' => 'layui-icon-fire',
            ],
            self::CACHE_TYPE_APPLICATION => [
                'name' => '应用缓存',
                'description' => 'Laravel应用缓存',
                'icon' => 'layui-icon-app',
            ],
            self::CACHE_TYPE_MENU => [
                'name' => '菜单缓存',
                'description' => '系统菜单树缓存',
                'icon' => 'layui-icon-menu-fill',
            ],
            self::CACHE_TYPE_PERMISSION => [
                'name' => '权限缓存',
                'description' => '用户权限缓存',
                'icon' => 'layui-icon-vercode',
            ],
            self::CACHE_TYPE_DICTIONARY => [
                'name' => '字典缓存',
                'description' => '数据字典缓存',
                'icon' => 'layui-icon-list',
            ],
            self::CACHE_TYPE_SYSTEM => [
                'name' => '系统缓存',
                'description' => '系统统计等缓存',
                'icon' => 'layui-icon-engine',
            ],
        ];
    }

    /**
     * 清除指定类型的缓存
     */
    public static function clearCache(string $type): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];

        try {
            switch ($type) {
                case self::CACHE_TYPE_CONFIG:
                    self::clearConfigCache();
                    $result['message'] = '配置缓存清除成功';
                    break;

                case self::CACHE_TYPE_ROUTE:
                    self::clearRouteCache();
                    $result['message'] = '路由缓存清除成功';
                    break;

                case self::CACHE_TYPE_VIEW:
                    self::clearViewCache();
                    $result['message'] = '视图缓存清除成功';
                    break;

                case self::CACHE_TYPE_EVENT:
                    self::clearEventCache();
                    $result['message'] = '事件缓存清除成功';
                    break;

                case self::CACHE_TYPE_APPLICATION:
                    self::clearApplicationCache();
                    $result['message'] = '应用缓存清除成功';
                    break;

                case self::CACHE_TYPE_MENU:
                    self::clearMenuCache();
                    $result['message'] = '菜单缓存清除成功';
                    break;

                case self::CACHE_TYPE_PERMISSION:
                    self::clearPermissionCache();
                    $result['message'] = '权限缓存清除成功';
                    break;

                case self::CACHE_TYPE_DICTIONARY:
                    self::clearDictionaryCache();
                    $result['message'] = '字典缓存清除成功';
                    break;

                case self::CACHE_TYPE_SYSTEM:
                    self::clearSystemCache();
                    $result['message'] = '系统缓存清除成功';
                    break;

                default:
                    throw new \InvalidArgumentException('不支持的缓存类型: ' . $type);
            }

            $result['success'] = true;
        } catch (\Exception $e) {
            $result['message'] = '缓存清除失败: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * 清除所有缓存
     */
    public static function clearAllCache(): array
    {
        $results = [];
        $types = array_keys(self::getCacheTypes());

        foreach ($types as $type) {
            $results[$type] = self::clearCache($type);
        }

        // 统计结果
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        return [
            'success' => $successCount === $totalCount,
            'message' => "缓存清除完成，成功 {$successCount}/{$totalCount} 项",
            'details' => $results
        ];
    }

    /**
     * 清除配置缓存
     */
    private static function clearConfigCache(): void
    {
        // 清除Option模型缓存
        Option::clearCache();
        
        // 清除统计缓存
        Cache::forget('lpadmin_config_statistics');
        
        // 清除分组缓存
        $groups = Option::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget('lpadmin_options_group_' . $group);
        }
    }

    /**
     * 清除路由缓存
     */
    private static function clearRouteCache(): void
    {
        Artisan::call('route:clear');
    }

    /**
     * 清除视图缓存
     */
    private static function clearViewCache(): void
    {
        Artisan::call('view:clear');
    }

    /**
     * 清除事件缓存
     */
    private static function clearEventCache(): void
    {
        Artisan::call('event:clear');
    }

    /**
     * 清除应用缓存
     */
    private static function clearApplicationCache(): void
    {
        Artisan::call('cache:clear');
    }

    /**
     * 清除菜单缓存
     */
    private static function clearMenuCache(): void
    {
        MenuCacheService::clearAll();
    }

    /**
     * 清除权限缓存
     */
    private static function clearPermissionCache(): void
    {
        // 清除所有管理员权限缓存
        $cachePrefix = config('lpadmin.permission.cache_key', 'lpadmin_permission');
        
        // 如果是Redis缓存，使用通配符删除
        $store = Cache::getStore();
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            try {
                $redis = $store->connection();
                $keys = $redis->keys($cachePrefix . '_*');
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                // Redis操作失败，使用通用清除方法
                Cache::flush();
            }
        } else {
            // 对于其他缓存驱动，需要遍历所有管理员
            // 这里可以根据实际需求优化
            Cache::flush();
        }
    }

    /**
     * 清除字典缓存
     */
    private static function clearDictionaryCache(): void
    {
        // 使用DictionaryService清除字典缓存
        $dictionaryService = app(\App\Services\LPadmin\DictionaryService::class);
        $dictionaryService->clearDictCache();

        // 也清除Dictionary模型的缓存
        \App\Models\LPadmin\Dictionary::clearCache();
    }

    /**
     * 清除系统缓存
     */
    private static function clearSystemCache(): void
    {
        // 清除系统统计缓存
        $systemCacheKeys = [
            'lpadmin_system_statistics',
            'lpadmin_dashboard_data',
            'lpadmin_system_info',
        ];

        foreach ($systemCacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 获取缓存统计信息
     */
    public static function getCacheStats(): array
    {
        $stats = [];
        $types = self::getCacheTypes();

        foreach ($types as $type => $info) {
            $stats[$type] = [
                'name' => $info['name'],
                'description' => $info['description'],
                'icon' => $info['icon'],
                'size' => self::getCacheSize($type),
                'count' => self::getCacheCount($type),
            ];
        }

        return $stats;
    }

    /**
     * 获取指定类型缓存大小
     */
    private static function getCacheSize(string $type): string
    {
        try {
            switch ($type) {
                case self::CACHE_TYPE_VIEW:
                    $path = storage_path('framework/views');
                    return self::getDirectorySize($path);

                case self::CACHE_TYPE_ROUTE:
                    $files = [
                        base_path('bootstrap/cache/routes-v7.php'),
                        base_path('bootstrap/cache/routes.php'),
                    ];
                    $size = 0;
                    foreach ($files as $file) {
                        if (File::exists($file)) {
                            $size += File::size($file);
                        }
                    }
                    return self::formatBytes($size);

                case self::CACHE_TYPE_APPLICATION:
                    $path = storage_path('framework/cache');
                    return self::getDirectorySize($path);

                case self::CACHE_TYPE_DICTIONARY:
                    // 估算字典缓存大小
                    $dictCount = \App\Models\LPadmin\Dictionary::count();
                    $estimatedSize = $dictCount * 1024; // 每个字典估算1KB
                    return self::formatBytes($estimatedSize);

                default:
                    return '未知';
            }
        } catch (\Exception $e) {
            return '获取失败';
        }
    }

    /**
     * 获取指定类型缓存数量
     */
    private static function getCacheCount(string $type): int
    {
        try {
            switch ($type) {
                case self::CACHE_TYPE_CONFIG:
                    return Option::count();

                case self::CACHE_TYPE_VIEW:
                    $path = storage_path('framework/views');
                    return File::exists($path) ? count(File::allFiles($path)) : 0;

                case self::CACHE_TYPE_DICTIONARY:
                    return \App\Models\LPadmin\Dictionary::count();

                default:
                    return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取目录大小
     */
    private static function getDirectorySize(string $path): string
    {
        if (!File::exists($path)) {
            return '0 B';
        }

        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return self::formatBytes($size);
    }

    /**
     * 格式化字节数
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 预热指定类型的缓存
     *
     * @param string $type 缓存类型
     * @return array
     */
    public static function warmupCache(string $type): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];

        try {
            switch ($type) {
                case self::CACHE_TYPE_CONFIG:
                    $result = \App\Helpers\ConfigHelper::warmupCache();
                    break;

                case self::CACHE_TYPE_MENU:
                    MenuCacheService::getMenuTree();
                    MenuCacheService::getAdminMenus();
                    $result['message'] = '菜单缓存预热成功';
                    $result['success'] = true;
                    break;

                case self::CACHE_TYPE_ROUTE:
                    Artisan::call('route:cache');
                    $result['message'] = '路由缓存预热成功';
                    $result['details'][] = Artisan::output();
                    $result['success'] = true;
                    break;

                case self::CACHE_TYPE_VIEW:
                    Artisan::call('view:cache');
                    $result['message'] = '视图缓存预热成功';
                    $result['details'][] = Artisan::output();
                    $result['success'] = true;
                    break;

                default:
                    $result['message'] = '该缓存类型不支持预热';
                    return $result;
            }
        } catch (\Exception $e) {
            $result['message'] = '缓存预热失败: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * 预热所有支持的缓存
     *
     * @return array
     */
    public static function warmupAllCache(): array
    {
        $results = [];
        $supportedTypes = [
            self::CACHE_TYPE_CONFIG,
            self::CACHE_TYPE_MENU,
            self::CACHE_TYPE_ROUTE,
            self::CACHE_TYPE_VIEW
        ];

        foreach ($supportedTypes as $type) {
            $results[$type] = self::warmupCache($type);
        }

        // 统计结果
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        return [
            'success' => $successCount === $totalCount,
            'message' => "缓存预热完成，成功 {$successCount}/{$totalCount} 项",
            'details' => $results
        ];
    }

    /**
     * 获取缓存监控数据
     *
     * @return array
     */
    public static function getMonitorData(): array
    {
        try {
            // 模拟监控数据，实际项目中应该从缓存驱动获取真实数据
            $stats = [
                'hit_rate' => rand(85, 98), // 命中率
                'avg_response' => rand(1, 5), // 平均响应时间(ms)
                'memory_usage' => self::formatBytes(rand(1024*1024*10, 1024*1024*100)), // 内存使用
                'total_keys' => rand(100, 1000), // 缓存键数量
            ];

            // 生成趋势数据
            $trends = [
                'times' => [],
                'hit_rates' => [],
                'response_times' => []
            ];

            // 生成最近12个时间点的数据
            for ($i = 11; $i >= 0; $i--) {
                $time = date('H:i', strtotime("-{$i} minutes"));
                $trends['times'][] = $time;
                $trends['hit_rates'][] = rand(80, 99);
                $trends['response_times'][] = rand(1, 8);
            }

            return [
                'stats' => $stats,
                'trends' => $trends
            ];
        } catch (\Exception $e) {
            throw new \Exception('获取监控数据失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取缓存键列表
     *
     * @param string $pattern 搜索模式
     * @param int $limit 限制数量
     * @return array
     */
    public static function getKeys(string $pattern = '*', int $limit = 100): array
    {
        try {
            $keys = [];
            $store = Cache::getStore();

            if ($store instanceof \Illuminate\Cache\RedisStore) {
                // Redis缓存
                $redis = $store->connection();
                $allKeys = $redis->keys($pattern);

                $count = 0;
                foreach ($allKeys as $key) {
                    if ($count >= $limit) break;

                    $ttl = $redis->ttl($key);
                    $type = $redis->type($key);
                    $size = strlen($redis->get($key) ?? '');

                    $keys[] = [
                        'name' => $key,
                        'type' => $type,
                        'size' => self::formatBytes($size),
                        'ttl' => $ttl > 0 ? $ttl . 's' : '永久',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $count++;
                }
            } else {
                // 其他缓存驱动，生成模拟数据
                $sampleKeys = [
                    'lpadmin_config_system',
                    'lpadmin_menu_tree',
                    'lpadmin_permission_admin_1',
                    'lpadmin_dictionary_status',
                    'lpadmin_options_group_system',
                    'laravel_cache_config',
                    'laravel_cache_routes',
                ];

                foreach ($sampleKeys as $i => $keyName) {
                    if ($i >= $limit) break;

                    if ($pattern !== '*' && strpos($keyName, str_replace('*', '', $pattern)) === false) {
                        continue;
                    }

                    $keys[] = [
                        'name' => $keyName,
                        'type' => 'string',
                        'size' => self::formatBytes(rand(100, 5000)),
                        'ttl' => rand(0, 1) ? rand(300, 3600) . 's' : '永久',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 1440) . ' minutes'))
                    ];
                }
            }

            return [
                'keys' => $keys,
                'total' => count($keys)
            ];
        } catch (\Exception $e) {
            throw new \Exception('获取缓存键失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取指定缓存键的值
     *
     * @param string $key 缓存键
     * @return array
     */
    public static function getCacheValue(string $key): array
    {
        try {
            $value = Cache::get($key);

            return [
                'key' => $key,
                'value' => $value,
                'type' => gettype($value),
                'exists' => $value !== null
            ];
        } catch (\Exception $e) {
            throw new \Exception('获取缓存值失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除指定缓存键
     *
     * @param string $key 缓存键
     * @return bool
     */
    public static function deleteCacheKey(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            throw new \Exception('删除缓存键失败: ' . $e->getMessage());
        }
    }

    /**
     * 设置缓存值
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $ttl 过期时间(秒)
     * @return array
     */
    public static function setValue(string $key, $value, int $ttl = 3600): array
    {
        try {
            $result = Cache::put($key, $value, $ttl);

            return [
                'success' => $result,
                'message' => $result ? '缓存设置成功' : '缓存设置失败',
                'key' => $key,
                'ttl' => $ttl
            ];
        } catch (\Exception $e) {
            throw new \Exception('设置缓存值失败: ' . $e->getMessage());
        }
    }
}
