<?php

namespace App\Components\SystemLog\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Components\SystemLog\Services\AdminLogService;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员操作日志记录中间件
 * 
 * 此中间件属于SystemLog组件，随组件安装/卸载
 */
class LogAdminOperation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 异步记录日志，避免影响主要功能
        AdminLogService::log($request, $response);
        
        return $response;
    }
}
