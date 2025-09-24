<?php

namespace App\Http\Middleware\LPadmin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Services\LPadmin\PermissionService;

/**
 * 共享菜单数据中间件
 */
class ShareMenuData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取当前登录的管理员
        $admin = auth('lpadmin')->user();

        if ($admin) {
            // 获取菜单数据
            $menuData = $this->getMenuData($admin);



            // 共享菜单数据到所有视图
            View::share('lpadmin_menu', $menuData);
            View::share('lpadmin_admin', $admin);
        }

        return $next($request);
    }

    /**
     * 获取菜单数据
     */
    protected function getMenuData($admin): array
    {
        try {
            // 使用权限服务获取菜单
            $permissionService = app(PermissionService::class);
            return $permissionService->buildMenuTree($admin);

        } catch (\Exception $e) {
            // 如果获取菜单失败，返回空数组
            Log::error('获取菜单数据失败：' . $e->getMessage());
            return [];
        }
    }


}
