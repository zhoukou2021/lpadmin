<?php

namespace App\Http\Controllers\LPadmin;

use App\Models\LPadmin\Rule;
use App\Services\LPadmin\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 菜单API控制器
 * 临时控制器，用于修复菜单显示问题
 */
class MenuApiController extends BaseController
{
    /**
     * 获取菜单树（用于前端菜单显示）
     */
    public function getMenuTree(Request $request): JsonResponse
    {
        try {
            $admin = auth('lpadmin')->user();
            
            if (!$admin) {
                return $this->error('未登录', 401);
            }

            // 使用权限服务获取菜单树
            $permissionService = app(PermissionService::class);
            
            if ($permissionService->isSuperAdmin($admin)) {
                // 超级管理员显示所有菜单
                $menus = Rule::where('status', Rule::STATUS_ENABLED)
                    ->where('is_show', Rule::SHOW_VISIBLE)
                    ->where('type', Rule::TYPE_MENU)
                    ->where('name', '!=', 'dashboard') // 排除仪表盘
                    ->orderBy('sort', 'desc')
                    ->orderBy('id')
                    ->get();
            } else {
                // 普通用户根据权限获取菜单
                $permissions = $permissionService->getAdminPermissions($admin);
                
                $menus = Rule::where('status', Rule::STATUS_ENABLED)
                    ->where('is_show', Rule::SHOW_VISIBLE)
                    ->where('type', Rule::TYPE_MENU)
                    ->whereIn('name', $permissions)
                    ->where('name', '!=', 'dashboard')
                    ->orderBy('sort', 'desc')
                    ->orderBy('id')
                    ->get();
            }

            // 构建菜单树
            $menuTree = $this->buildMenuTree($menus->toArray());

            return $this->success($menuTree, '获取成功');

        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 构建菜单树
     */
    private function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($menus, $menu['id']);
                
                $item = [
                    'id' => $menu['id'],
                    'title' => $menu['title'],
                    'name' => $menu['name'],
                    'icon' => $menu['icon'],
                    'href' => $menu['url'],
                    'target' => $menu['target'] ?? '_self',
                    'type' => $this->convertRuleTypeToMenuType($menu['type']),
                    'spread' => false,
                ];
                
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * 转换权限类型到菜单类型
     */
    private function convertRuleTypeToMenuType($ruleType): int
    {
        return match($ruleType) {
            'menu' => 1,     // 菜单类型
            'button' => 2,   // 按钮类型
            'api' => 1,      // API权限当作菜单处理
            default => 1
        };
    }

    /**
     * 获取权限树（用于权限分配）
     */
    public function getPermissionTree(Request $request): JsonResponse
    {
        try {
            $admin = auth('lpadmin')->user();
            
            if (!$admin) {
                return $this->error('未登录', 401);
            }

            // 使用权限服务获取权限树
            $permissionService = app(PermissionService::class);
            
            if ($permissionService->isSuperAdmin($admin)) {
                // 超级管理员显示所有权限
                $rules = Rule::where('status', Rule::STATUS_ENABLED)
                    ->orderBy('sort', 'desc')
                    ->orderBy('id')
                    ->get();
            } else {
                // 普通用户根据权限获取
                $permissions = $permissionService->getAdminPermissions($admin);
                
                $rules = Rule::where('status', Rule::STATUS_ENABLED)
                    ->whereIn('name', $permissions)
                    ->orderBy('sort', 'desc')
                    ->orderBy('id')
                    ->get();
            }

            // 构建权限树
            $permissionTree = $this->buildPermissionTree($rules->toArray());

            return $this->success($permissionTree, '获取成功');

        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 构建权限树
     */
    private function buildPermissionTree(array $rules, int $parentId = 0): array
    {
        $tree = [];

        foreach ($rules as $rule) {
            if ($rule['parent_id'] == $parentId) {
                $children = $this->buildPermissionTree($rules, $rule['id']);
                
                $item = [
                    'id' => $rule['id'],
                    'name' => $rule['name'],
                    'title' => $rule['title'],
                    'type' => $rule['type'],
                    'icon' => $rule['icon'],
                    'sort' => $rule['sort'],
                    'checked' => false,
                    'spread' => true,
                ];
                
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
