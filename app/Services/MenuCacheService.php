<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\LPadmin\Rule;

class MenuCacheService
{
    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'lpadmin_menu_';
    
    /**
     * 缓存时间（分钟）
     */
    const CACHE_TTL = 60;
    
    /**
     * 获取菜单树缓存
     */
    public static function getMenuTree(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'tree';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $menus = Rule::where('status', Rule::STATUS_ENABLED)
                        ->where('is_show', Rule::SHOW_VISIBLE)
                        ->where('type', Rule::TYPE_MENU)
                        ->orderBy('sort', 'desc')
                        ->orderBy('id')
                        ->get();

            return self::buildMenuTree($menus->toArray());
        });
    }
    
    /**
     * 获取管理菜单列表缓存
     */
    public static function getAdminMenus(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'admin_list';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return Rule::where('type', Rule::TYPE_MENU)
                      ->orderBy('sort', 'desc')
                      ->orderBy('id')
                      ->get()
                      ->toArray();
        });
    }
    
    /**
     * 获取用户权限菜单缓存
     */
    public static function getUserMenus($userId): array
    {
        $cacheKey = self::CACHE_PREFIX . 'user_' . $userId;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            // 这里可以根据用户权限过滤菜单
            // 暂时返回所有启用的菜单
            $menus = Rule::where('status', Rule::STATUS_ENABLED)
                        ->where('is_show', Rule::SHOW_VISIBLE)
                        ->where('type', Rule::TYPE_MENU)
                        ->orderBy('sort', 'desc')
                        ->orderBy('id')
                        ->get();

            return self::buildMenuTree($menus->toArray());
        });
    }
    
    /**
     * 清除所有菜单缓存
     */
    public static function clearAll(): void
    {
        $keys = [
            self::CACHE_PREFIX . 'tree',
            self::CACHE_PREFIX . 'admin_list',
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // 清除用户菜单缓存（使用通配符模式）
        self::clearUserMenus();
    }
    
    /**
     * 清除用户菜单缓存
     */
    public static function clearUserMenus($userId = null): void
    {
        if ($userId) {
            Cache::forget(self::CACHE_PREFIX . 'user_' . $userId);
        } else {
            // 清除所有用户菜单缓存
            // 注意：这里需要根据实际缓存驱动实现
            $pattern = self::CACHE_PREFIX . 'user_*';
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        }
    }
    
    /**
     * 构建菜单树
     */
    private static function buildMenuTree(array $data, int $parentId = 0): array
    {
        $tree = [];
        
        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $menuItem = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'icon' => 'layui-icon ' . ($item['icon'] ?: 'layui-icon-app'),
                    'href' => $item['url'] ?: '',
                    'spread' => false,
                    'type' => 1, // 默认为菜单类型
                ];
                
                $children = self::buildMenuTree($data, $item['id']);
                if (!empty($children)) {
                    $menuItem['children'] = $children;
                    $menuItem['spread'] = true;
                    $menuItem['type'] = 0; // 有子菜单的为目录类型
                }
                
                $tree[] = $menuItem;
            }
        }
        
        return $tree;
    }
    
    /**
     * 菜单数据变更时清除缓存
     */
    public static function handleMenuChanged(): void
    {
        self::clearAll();
    }
}
