<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LPadmin\AdminLog;

/**
 * LPadmin操作日志中间件
 * 
 * 记录管理员的操作日志
 */
class LPadminLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 只记录POST、PUT、PATCH、DELETE请求
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        // 只记录已登录管理员的操作
        $admin = Auth::guard('lpadmin')->user();
        if (!$admin) {
            return $response;
        }

        try {
            $this->logOperation($request, $response, $admin);
        } catch (\Exception $e) {
            Log::error('记录操作日志失败: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * 记录操作日志
     */
    private function logOperation(Request $request, Response $response, $admin): void
    {
        $routeName = $request->route()->getName();
        $action = $this->getActionFromRoute($routeName);
        $module = $this->getModuleFromRoute($routeName);
        
        // 过滤敏感数据
        $requestData = $this->filterSensitiveData($request->all());
        
        AdminLog::create([
            'admin_id' => $admin->id,
            'admin_username' => $admin->username,
            'action' => $action,
            'module' => $module,
            'route_name' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => json_encode($requestData, JSON_UNESCAPED_UNICODE),
            'response_code' => $response->getStatusCode(),
            'created_at' => now(),
        ]);
    }

    /**
     * 从路由名称获取操作类型
     */
    private function getActionFromRoute(?string $routeName): string
    {
        if (!$routeName) {
            return 'unknown';
        }

        $actions = [
            'store' => 'create',
            'update' => 'update',
            'destroy' => 'delete',
            'login' => 'login',
            'logout' => 'logout',
        ];

        foreach ($actions as $key => $action) {
            if (str_contains($routeName, $key)) {
                return $action;
            }
        }

        return 'other';
    }

    /**
     * 从路由名称获取模块名称
     */
    private function getModuleFromRoute(?string $routeName): string
    {
        if (!$routeName || !str_starts_with($routeName, 'lpadmin.')) {
            return 'unknown';
        }

        $parts = explode('.', $routeName);
        return $parts[1] ?? 'unknown';
    }

    /**
     * 过滤敏感数据
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', '_token', '_method'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }

        return $data;
    }
}
