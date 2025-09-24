<?php

namespace App\Services\LPadmin;

use Illuminate\Support\Facades\Cache;
use App\Models\LPadmin\Admin;
use App\Models\LPadmin\Rule;

/**
 * 权限服务类
 *
 * 处理权限验证相关的业务逻辑
 */
class PermissionService
{
    /**
     * 检查管理员是否为超级管理员
     *
     * @param Admin $admin
     * @return bool
     */
    public function isSuperAdmin(Admin $admin): bool
    {
        return $admin->roles()->where('name', config('lpadmin.permission.super_admin_role', 'super_admin'))->exists();
    }

    /**
     * 检查管理员是否有指定权限
     *
     * @param Admin $admin
     * @param string $permission
     * @return bool
     */
    public function hasPermission(Admin $admin, string $permission): bool
    {
        // 超级管理员拥有所有权限
        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        // 从缓存获取权限列表
        $permissions = $this->getAdminPermissions($admin);

        return in_array($permission, $permissions);
    }

    /**
     * 检查管理员是否有路由权限
     *
     * @param Admin $admin
     * @param string $routeName
     * @return bool
     */
    public function hasRoutePermission(Admin $admin, string $routeName): bool
    {
        // 超级管理员拥有所有权限
        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        // 获取路由对应的权限规则
        $rule = Rule::where('route_name', $routeName)->first();
        if (!$rule) {
            // 如果没有配置权限规则，默认允许访问
            return true;
        }

        return $this->hasPermission($admin, $rule->name);
    }

    /**
     * 获取路由对应的权限名称
     *
     * @param string $routeName
     * @return string|null
     */
    public function getRoutePermission(string $routeName): ?string
    {
        $rule = Rule::where('route_name', $routeName)->first();
        return $rule ? $rule->name : null;
    }

    /**
     * 获取权限详细信息和建议
     *
     * @param Admin $admin
     * @param string $requiredPermission
     * @return array
     */
    public function getPermissionSuggestion(Admin $admin, string $requiredPermission): array
    {
        $userPermissions = $this->getAdminPermissions($admin);

        // 查找权限规则详情
        $rule = Rule::where('name', $requiredPermission)->first();

        $suggestion = [
            'required_permission' => $requiredPermission,
            'permission_title' => $rule ? $rule->title : $requiredPermission,
            'user_has_permission' => in_array($requiredPermission, $userPermissions),
            'user_permissions' => $userPermissions,
            'suggestion' => $this->generatePermissionSuggestion($requiredPermission, $userPermissions, $rule),
        ];

        return $suggestion;
    }

    /**
     * 生成权限分配建议
     *
     * @param string $requiredPermission
     * @param array $userPermissions
     * @param Rule|null $rule
     * @return string
     */
    private function generatePermissionSuggestion(string $requiredPermission, array $userPermissions, ?Rule $rule): string
    {
        if (!$rule) {
            return "请联系管理员为您分配 '{$requiredPermission}' 权限";
        }

        $suggestions = [];

        // 检查是否有相关的父权限
        $parts = explode('.', $requiredPermission);
        if (count($parts) > 1) {
            $parentPermission = $parts[0];
            if (!in_array($parentPermission, $userPermissions)) {
                $parentRule = Rule::where('name', $parentPermission)->first();
                $parentTitle = $parentRule ? $parentRule->title : $parentPermission;
                $suggestions[] = "需要先分配父权限: {$parentTitle} ({$parentPermission})";
            }
        }

        $suggestions[] = "需要分配权限: {$rule->title} ({$requiredPermission})";

        // 添加操作建议
        $suggestions[] = "操作路径: 系统管理 → 角色管理 → 选择用户角色 → 权限分配 → 勾选相应权限";

        return implode("\n", $suggestions);
    }

    /**
     * 获取管理员的所有权限
     *
     * @param Admin $admin
     * @return array
     */
    public function getAdminPermissions(Admin $admin): array
    {
        $cacheKey = config('lpadmin.permission.cache_key') . '_' . $admin->id;
        $cacheTtl = config('lpadmin.permission.cache_ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($admin) {
            $permissions = [];

            // 获取管理员的所有角色
            $roles = $admin->roles()->with('rules')->get();

            foreach ($roles as $role) {
                foreach ($role->rules as $rule) {
                    $permissions[] = $rule->name;
                }
            }

            return array_unique($permissions);
        });
    }

    /**
     * 清除管理员权限缓存
     *
     * @param Admin $admin
     * @return void
     */
    public function clearAdminPermissionCache(Admin $admin): void
    {
        $cacheKey = config('lpadmin.permission.cache_key') . '_' . $admin->id;
        Cache::forget($cacheKey);
    }

    /**
     * 清除所有权限缓存
     *
     * @return void
     */
    public function clearAllPermissionCache(): void
    {
        $cacheKey = config('lpadmin.permission.cache_key');

        // 获取所有管理员ID
        $adminIds = Admin::pluck('id');

        foreach ($adminIds as $adminId) {
            Cache::forget($cacheKey . '_' . $adminId);
        }
    }

    /**
     * 构建权限菜单树
     *
     * @param Admin $admin
     * @return array
     */
    public function buildMenuTree(Admin $admin): array
    {
        // 超级管理员显示所有菜单
        if ($this->isSuperAdmin($admin)) {
            $menus = Rule::where('status', Rule::STATUS_ENABLED)
                ->where('is_show', Rule::SHOW_VISIBLE)
                ->where('type', Rule::TYPE_MENU)
                ->where('name', '!=', 'dashboard') // 排除仪表盘，因为它在配置文件中作为默认选项卡
                ->orderBy('sort', 'desc')
                ->orderBy('id')
                ->get();
        } else {
            // 普通管理员根据权限精确控制菜单显示
            $permissions = $this->getAdminPermissions($admin);

            // 获取授权的菜单（使用Rule模型）
            $menus = Rule::where('status', Rule::STATUS_ENABLED)
                ->where('is_show', Rule::SHOW_VISIBLE)
                ->where('type', Rule::TYPE_MENU)
                ->whereIn('name', $permissions)
                ->where('name', '!=', 'dashboard')
                ->orderBy('sort', 'desc')
                ->orderBy('id')
                ->get();
        }

        return $this->buildTree($menus->toArray());
    }

    /**
     * 构建树形结构
     *
     * @param array $items
     * @param int $parentId
     * @return array
     */
    private function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * 获取授权的菜单（支持父子菜单精确控制）
     *
     * @param array $permissions
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAuthorizedMenus(array $permissions)
    {
        // 获取所有启用的菜单
        $allMenus = Rule::where('status', Rule::STATUS_ENABLED)
            ->where('is_show', Rule::SHOW_VISIBLE)
            ->where('type', Rule::TYPE_MENU)
            ->where('name', '!=', 'dashboard') // 排除仪表盘
            ->orderBy('sort', 'desc')
            ->orderBy('id')
            ->get();

        $authorizedMenus = collect();

        foreach ($allMenus as $menu) {
            // 检查菜单权限
            if ($this->hasMenuPermission($menu, $permissions)) {
                $authorizedMenus->push($menu);
            }
        }

        return $authorizedMenus;
    }

    /**
     * 检查菜单权限
     *
     * @param \App\Models\LPadmin\Rule $menu
     * @param array $permissions
     * @return bool
     */
    private function hasMenuPermission($menu, array $permissions): bool
    {
        // 1. 检查是否被明确拒绝（deny.菜单名）
        if (in_array('deny.' . $menu->name, $permissions)) {
            return false;
        }

        // 2. 直接权限匹配 - 必须有明确的权限
        if (in_array($menu->name, $permissions)) {
            return true;
        }

        // 3. 子菜单不继承父菜单权限，必须有明确的权限才能显示
        // 这确保了精确的权限控制：只显示明确授权的菜单

        return false;
    }

    /**
     * 过滤菜单权限（排除功能权限）
     *
     * @param array $permissions
     * @return array
     */
    private function filterMenuPermissions(array $permissions): array
    {
        $menuPermissions = [];

        foreach ($permissions as $permission) {
            // 只保留不包含点号的权限（主权限）作为菜单权限
            // 例如：'user' 是菜单权限，'user.view' 是功能权限
            if (strpos($permission, '.') === false) {
                $menuPermissions[] = $permission;
            }
        }

        return $menuPermissions;
    }
}
