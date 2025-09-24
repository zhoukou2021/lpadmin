<?php

namespace App\Services\LPadmin;

use App\Models\LPadmin\Admin;
use App\Models\LPadmin\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 简化权限服务
 * 统一处理菜单和权限逻辑
 */
class SimplifiedPermissionService
{
    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'lpadmin_permission_';
    
    /**
     * 缓存时间（分钟）
     */
    const CACHE_TTL = 60;

    /**
     * 获取管理员权限列表
     */
    public function getAdminPermissions($adminId): array
    {
        $cacheKey = self::CACHE_PREFIX . 'admin_' . $adminId;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($adminId) {
            $admin = Admin::with('roles.rules')->find($adminId);
            
            if (!$admin) {
                return [];
            }

            $permissions = [];
            
            foreach ($admin->roles as $role) {
                foreach ($role->rules as $rule) {
                    if ($rule->status == Rule::STATUS_ENABLED) {
                        $permissions[] = $rule->name;
                    }
                }
            }

            return array_unique($permissions);
        });
    }

    /**
     * 获取菜单树
     */
    public function getMenuTree($adminId): array
    {
        $cacheKey = self::CACHE_PREFIX . 'menu_tree_' . $adminId;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($adminId) {
            $permissions = $this->getAdminPermissions($adminId);
            
            if (empty($permissions)) {
                return [];
            }

            // 获取菜单类型且显示的权限记录
            $menuRules = Rule::where('type', Rule::TYPE_MENU)
                ->where('is_show', Rule::SHOW_VISIBLE)
                ->where('status', Rule::STATUS_ENABLED)
                ->whereIn('name', $permissions)
                ->orderBy('sort', 'desc')
                ->get();

            return $this->buildTree($menuRules);
        });
    }

    /**
     * 检查权限
     */
    public function hasPermission($adminId, $permission): bool
    {
        $permissions = $this->getAdminPermissions($adminId);
        return in_array($permission, $permissions);
    }

    /**
     * 检查多个权限（任一满足即可）
     */
    public function hasAnyPermission($adminId, array $permissions): bool
    {
        $adminPermissions = $this->getAdminPermissions($adminId);
        return !empty(array_intersect($permissions, $adminPermissions));
    }

    /**
     * 检查多个权限（全部满足）
     */
    public function hasAllPermissions($adminId, array $permissions): bool
    {
        $adminPermissions = $this->getAdminPermissions($adminId);
        return empty(array_diff($permissions, $adminPermissions));
    }

    /**
     * 构建树形结构
     */
    private function buildTree($items, $parentId = 0): array
    {
        $tree = [];
        
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildTree($items, $item->id);
                
                $node = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'name' => $item->name,
                    'icon' => $item->icon,
                    'url' => $item->url,
                    'target' => $item->target ?? '_self',
                    'type' => $item->type,
                    'sort' => $item->sort,
                ];
                
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    /**
     * 清理权限缓存
     */
    public function clearCache($adminId = null): void
    {
        if ($adminId) {
            // 清理指定管理员的缓存
            Cache::forget(self::CACHE_PREFIX . 'admin_' . $adminId);
            Cache::forget(self::CACHE_PREFIX . 'menu_tree_' . $adminId);
        } else {
            // 清理所有权限相关缓存
            $pattern = self::CACHE_PREFIX . '*';
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
        
        Log::info('权限缓存已清理', ['admin_id' => $adminId]);
    }

    /**
     * 获取权限树（用于权限分配界面）
     */
    public function getPermissionTree(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'permission_tree';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $rules = Rule::where('status', Rule::STATUS_ENABLED)
                ->orderBy('sort', 'desc')
                ->get();

            return $this->buildPermissionTree($rules);
        });
    }

    /**
     * 构建权限树（用于权限分配）
     */
    private function buildPermissionTree($items, $parentId = 0): array
    {
        $tree = [];
        
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildPermissionTree($items, $item->id);
                
                $node = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'title' => $item->title,
                    'type' => $item->type,
                    'icon' => $item->icon,
                    'sort' => $item->sort,
                    'checked' => false,
                    'spread' => true,
                ];
                
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    /**
     * 根据路由推断权限
     */
    public function inferPermissionFromRoute($routeName): ?string
    {
        // 移除路由前缀
        $permission = str_replace('lpadmin.', '', $routeName);
        
        // 处理资源路由
        $resourceMappings = [
            '.index' => '',
            '.create' => '.create',
            '.store' => '.create',
            '.show' => '.view',
            '.edit' => '.update',
            '.update' => '.update',
            '.destroy' => '.destroy',
        ];
        
        foreach ($resourceMappings as $suffix => $permissionSuffix) {
            if (str_ends_with($permission, $suffix)) {
                $basePermission = str_replace($suffix, '', $permission);
                return $basePermission . $permissionSuffix;
            }
        }
        
        // 处理特殊路由
        $specialMappings = [
            'toggle_status' => '.toggle_status',
            'batch_delete' => '.batch_delete',
            'batch_destroy' => '.batch_destroy',
            'statistics' => '.statistics',
            'select' => '.select',
        ];
        
        foreach ($specialMappings as $action => $permissionSuffix) {
            if (str_ends_with($permission, $action)) {
                $basePermission = str_replace('.' . $action, '', $permission);
                return $basePermission . $permissionSuffix;
            }
        }
        
        return $permission;
    }

    /**
     * 刷新权限缓存
     */
    public function refreshCache(): void
    {
        $this->clearCache();
        
        // 预热缓存 - 获取所有管理员的权限
        $admins = Admin::where('status', 1)->get();
        foreach ($admins as $admin) {
            $this->getAdminPermissions($admin->id);
            $this->getMenuTree($admin->id);
        }
        
        // 预热权限树缓存
        $this->getPermissionTree();
        
        Log::info('权限缓存已刷新');
    }
}
