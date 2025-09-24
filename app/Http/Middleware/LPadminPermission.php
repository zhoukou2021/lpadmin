<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LPadmin\PermissionService;

/**
 * LPadmin权限验证中间件
 * 
 * 验证管理员是否有访问权限
 */
class LPadminPermission
{
    /**
     * 权限服务
     */
    protected $permissionService;

    /**
     * 构造函数
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $admin = Auth::guard('lpadmin')->user();

        if (!$admin) {
            return $this->unauthorized($request);
        }

        // 超级管理员跳过权限检查
        if ($this->permissionService->isSuperAdmin($admin)) {
            return $next($request);
        }

        // 智能分层权限检查
        $requiredPermission = $this->determineRequiredPermission($request, $permission);

        if ($requiredPermission && !$this->permissionService->hasPermission($admin, $requiredPermission)) {
            return $this->forbiddenWithSuggestion($request, $admin, $requiredPermission);
        }

        return $next($request);
    }

    /**
     * 智能确定所需权限
     */
    private function determineRequiredPermission(Request $request, ?string $explicitPermission): ?string
    {
        // 1. 如果明确指定了权限，直接使用
        if ($explicitPermission) {
            return $explicitPermission;
        }

        // 2. 根据路由智能推断权限
        $routeName = $request->route()->getName();
        $method = $request->getMethod();
        $uri = $request->getPathInfo();

        return $this->inferPermissionFromRoute($routeName, $method, $uri);
    }

    /**
     * 从路由推断所需权限
     */
    private function inferPermissionFromRoute(?string $routeName, string $method, string $uri): ?string
    {
        if (!$routeName) {
            return null;
        }

        // 解析路由名称，提取模块名
        $parts = explode('.', $routeName);
        if (count($parts) < 2) {
            return null;
        }

        $module = $parts[1]; // 如：lpadmin.user.index -> user
        $action = $parts[2] ?? 'index'; // 如：lpadmin.user.create -> create

        // 智能权限映射
        return $this->mapActionToPermission($module, $action, $method, $uri);
    }

    /**
     * 将操作映射到权限
     */
    private function mapActionToPermission(string $module, string $action, string $method, string $uri): string
    {
        // 特殊操作权限映射
        $specialActions = [
            'toggle_status' => $module . '.toggle_status',
            'reset_password' => $module . '.reset_password',
            'batch_delete' => $module . '.batch_delete',
            'permissions' => $module . '.permissions',
            'update_permissions' => $module . '.permissions',
            'tree' => $module . '.view',
            'select' => $module . '.view',
            'stats' => $module . '.view',
            'monitor' => $module . '.monitor',
            'settings' => $module . '.settings',
            'clear' => $module . '.clear',
            'clearByType' => $module . '.clear',
            'clearAll' => $module . '.clear',
        ];

        // 检查特殊操作
        if (isset($specialActions[$action])) {
            return $specialActions[$action];
        }

        // 根据HTTP方法和操作推断权限
        switch ($method) {
            case 'GET':
                if ($action === 'index') {
                    // 列表页面只需要菜单权限
                    return $module;
                } elseif (in_array($action, ['show', 'edit'])) {
                    // 查看和编辑表单需要对应权限
                    return $action === 'show' ? $module . '.view' : $module . '.update';
                } elseif ($action === 'create') {
                    // 创建表单需要创建权限
                    return $module . '.create';
                } else {
                    // 其他GET请求默认需要查看权限
                    return $module . '.view';
                }

            case 'POST':
                if ($action === 'store') {
                    return $module . '.create';
                } else {
                    // 其他POST请求根据URI判断
                    if (strpos($uri, '/batch') !== false) {
                        return $module . '.delete';
                    }
                    return $module . '.create';
                }

            case 'PUT':
            case 'PATCH':
                return $module . '.update';

            case 'DELETE':
                return $module . '.delete';

            default:
                // 默认需要菜单权限
                return $module;
        }
    }

    /**
     * 返回未认证响应
     */
    private function unauthorized(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'code' => 401,
                'message' => '请先登录',
                'data' => null,
                'timestamp' => time(),
            ], 401);
        }

        return redirect()->route('lpadmin.login');
    }

    /**
     * 返回无权限响应
     */
    private function forbidden(Request $request, string $message = '没有权限访问'): Response
    {
        $admin = Auth::guard('lpadmin')->user();

        if ($request->expectsJson()) {
            $responseData = [
                'code' => 403,
                'message' => $message,
                'data' => null,
                'timestamp' => time(),
            ];

            // 在开发环境下提供更多调试信息
            if (config('app.debug') && $admin) {
                $responseData['debug'] = [
                    'user' => $admin->username,
                    'user_id' => $admin->id,
                    'user_permissions' => $this->permissionService->getAdminPermissions($admin),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'route' => $request->route() ? $request->route()->getName() : null,
                ];
            }

            return response()->json($responseData, 403);
        }

        return response()->view('lpadmin.errors.403', [
            'message' => $message,
            'admin' => $admin,
            'debug' => config('app.debug')
        ], 403);
    }

    /**
     * 返回带权限建议的无权限响应
     */
    private function forbiddenWithSuggestion(Request $request, $admin, string $requiredPermission): Response
    {
        $suggestion = $this->permissionService->getPermissionSuggestion($admin, $requiredPermission);

        $message = "没有权限访问: {$suggestion['permission_title']} ({$requiredPermission})";

        if ($request->expectsJson()) {
            $responseData = [
                'code' => 403,
                'message' => $message,
                'data' => null,
                'timestamp' => time(),
                'permission_info' => [
                    'required_permission' => $requiredPermission,
                    'permission_title' => $suggestion['permission_title'],
                    'suggestion' => $suggestion['suggestion'],
                ]
            ];

            // 在开发环境下提供更多调试信息
            if (config('app.debug')) {
                $responseData['debug'] = [
                    'user' => $admin->username,
                    'user_id' => $admin->id,
                    'user_permissions' => $suggestion['user_permissions'],
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'route' => $request->route() ? $request->route()->getName() : null,
                ];
            }

            return response()->json($responseData, 403);
        }

        return response()->view('lpadmin.errors.403', [
            'message' => $message,
            'admin' => $admin,
            'suggestion' => $suggestion,
            'debug' => config('app.debug')
        ], 403);
    }
}
