<?php

namespace App\Components\SystemLog\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员操作日志服务
 * 
 * 此服务属于SystemLog组件，提供日志记录功能
 */
class AdminLogService
{
    /**
     * 记录操作日志
     */
    public static function log(Request $request, Response $response): void
    {
        try {
            // 检查表是否存在
            if (!Schema::hasTable('admin_logs')) {
                return;
            }
            
            // 检查是否为后台操作
            if (!$request->is('lpadmin/*')) {
                return;
            }
            
            // 排除不需要记录的路由
            if (self::shouldExclude($request)) {
                return;
            }
            
            // 获取管理员信息
            $admin = Auth::guard('lpadmin')->user();
            $adminId = $admin ? $admin->id : null;
            $adminUsername = $admin ? $admin->username : 'system';
            
            // 获取操作信息
            $action = self::getActionType($request);
            $module = self::getModuleName($request);
            $routeName = $request->route() ? $request->route()->getName() : '';
            
            // 过滤敏感数据
            $requestData = self::filterSensitiveData($request->all());
            
            // 插入日志记录
            DB::table('admin_logs')->insert([
                'admin_id' => $adminId,
                'admin_username' => $adminUsername,
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
            
        } catch (\Exception $e) {
            // 静默处理错误，不影响主要功能
            Log::warning('SystemLog component - Admin operation log failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查是否应该排除记录
     */
    protected static function shouldExclude(Request $request): bool
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        
        // 排除的路由模式
        $excludePatterns = [
            'lpadmin.system-log.*',  // 系统日志相关
            '*.statistics',          // 统计接口
            '*.heartbeat',          // 心跳检测
            '*.select',             // 下拉选择接口
        ];
        
        foreach ($excludePatterns as $pattern) {
            if (preg_match(shellToRegex($pattern), $routeName)) {
                return true;
            }
        }
        
        // 只记录重要操作（POST, PUT, PATCH, DELETE）
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取操作类型
     */
    protected static function getActionType(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route() ? $request->route()->getName() : '';
        
        // 根据路由名称判断
        if (str_contains($routeName, 'login')) {
            return 'login';
        }
        if (str_contains($routeName, 'logout')) {
            return 'logout';
        }
        if (str_contains($routeName, 'create') || str_contains($routeName, 'store')) {
            return 'create';
        }
        if (str_contains($routeName, 'edit') || str_contains($routeName, 'update')) {
            return 'update';
        }
        if (str_contains($routeName, 'destroy') || str_contains($routeName, 'delete')) {
            return 'delete';
        }
        
        // 根据HTTP方法判断
        switch ($method) {
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'unknown';
        }
    }
    
    /**
     * 获取模块名称
     */
    protected static function getModuleName(Request $request): string
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        $path = $request->path();
        
        // 从路由名称提取模块
        if (preg_match('/lpadmin\.([^.]+)/', $routeName, $matches)) {
            return $matches[1];
        }
        
        // 从路径提取模块
        if (preg_match('/lpadmin\/([^\/]+)/', $path, $matches)) {
            return $matches[1];
        }
        
        return 'system';
    }
    
    /**
     * 过滤敏感数据
     */
    protected static function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            '_token',
            'api_token',
            'access_token',
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }
        
        return $data;
    }
    
    /**
     * 清理过期日志
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        try {
            if (!Schema::hasTable('admin_logs')) {
                return 0;
            }
            
            $cutoffDate = now()->subDays($days);
            
            return DB::table('admin_logs')
                ->where('created_at', '<', $cutoffDate)
                ->delete();
                
        } catch (\Exception $e) {
            Log::warning('SystemLog component - Clean old logs failed: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * 获取日志统计信息
     */
    public static function getStatistics(): array
    {
        try {
            if (!Schema::hasTable('admin_logs')) {
                return [
                    'total_logs' => 0,
                    'today_logs' => 0,
                    'week_logs' => 0,
                    'month_logs' => 0,
                ];
            }
            
            $now = now();
            
            return [
                'total_logs' => DB::table('admin_logs')->count(),
                'today_logs' => DB::table('admin_logs')->whereDate('created_at', $now->toDateString())->count(),
                'week_logs' => DB::table('admin_logs')->where('created_at', '>=', $now->copy()->subWeek())->count(),
                'month_logs' => DB::table('admin_logs')->where('created_at', '>=', $now->copy()->subMonth())->count(),
            ];
            
        } catch (\Exception $e) {
            Log::warning('SystemLog component - Get log statistics failed: ' . $e->getMessage());
            return [
                'total_logs' => 0,
                'today_logs' => 0,
                'week_logs' => 0,
                'month_logs' => 0,
            ];
        }
    }
}
