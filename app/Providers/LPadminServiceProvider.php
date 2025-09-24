<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Services\LPadmin\PermissionService;
use App\Models\LPadmin\Rule;

/**
 * LPadmin服务提供者
 *
 * 注册LPadmin相关的服务和配置
 */
class LPadminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册配置文件
        $this->mergeConfigFrom(
            __DIR__.'/../../config/lpadmin.php', 'lpadmin'
        );

        // 注册服务
        $this->app->singleton(PermissionService::class, function ($app) {
            return new PermissionService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/../../config/lpadmin.php' => config_path('lpadmin.php'),
        ], 'lpadmin-config');

        // 发布视图文件
        $this->publishes([
            __DIR__.'/../../resources/views/lpadmin' => resource_path('views/lpadmin'),
        ], 'lpadmin-views');

        // 发布静态资源
        $this->publishes([
            __DIR__.'/../../public/static' => public_path('static'),
        ], 'lpadmin-assets');

        // 注册视图命名空间
        $this->loadViewsFrom(__DIR__.'/../../resources/views/lpadmin', 'lpadmin');

        // 注册Blade指令
        $this->registerBladeDirectives();

        // 共享视图数据
        $this->shareViewData();
    }

    /**
     * 注册Blade指令
     */
    protected function registerBladeDirectives(): void
    {
        // 权限检查指令
        Blade::if('lpadmin_can', function ($permission) {
            $admin = auth('lpadmin')->user();
            if (!$admin) {
                return false;
            }

            $permissionService = app(PermissionService::class);
            return $permissionService->hasPermission($admin, $permission);
        });

        // 超级管理员检查指令
        Blade::if('lpadmin_super', function () {
            $admin = auth('lpadmin')->user();
            if (!$admin) {
                return false;
            }

            $permissionService = app(PermissionService::class);
            return $permissionService->isSuperAdmin($admin);
        });

        // 角色检查指令
        Blade::if('lpadmin_role', function ($role) {
            $admin = auth('lpadmin')->user();
            if (!$admin) {
                return false;
            }

            return $admin->roles()->where('name', $role)->exists();
        });
    }

    /**
     * 共享视图数据
     */
    protected function shareViewData(): void
    {
        View::composer('lpadmin.*', function ($view) {
            $admin = auth('lpadmin')->user();

            $menuData = [];
            if ($admin) {
                $permissionService = app(\App\Services\LPadmin\PermissionService::class);
                $menuData = $permissionService->buildMenuTree($admin);
            }

            $view->with([
                'lpadmin_config' => config('lpadmin'),
                'lpadmin_admin' => $admin,
                'lpadmin_menu' => $menuData,
            ]);
        });
    }

    /**
     * 构建菜单树（使用Rule模型）
     */
    protected function buildMenuTree(): array
    {
        try {
            // 获取启用且显示的菜单
            $menus = Rule::where('status', Rule::STATUS_ENABLED)
                        ->where('is_show', Rule::SHOW_VISIBLE)
                        ->where('type', Rule::TYPE_MENU)
                        ->orderBy('sort', 'desc')
                        ->orderBy('id')
                        ->get();

            // 构建菜单树
            return $this->buildTree($menus->toArray());

        } catch (\Exception $e) {
            // 如果获取菜单失败，返回空数组
            return [];
        }
    }

    /**
     * 构建树形结构
     */
    protected function buildTree(array $data, int $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $node = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'url' => $item['url'],
                    'type' => $item['type'],
                ];

                $children = $this->buildTree($data, $item['id']);
                if (!empty($children)) {
                    $node['children'] = $children;
                }

                $tree[] = $node;
            }
        }

        return $tree;
    }
}
