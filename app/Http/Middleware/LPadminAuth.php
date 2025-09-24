<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * LPadmin认证中间件
 * 
 * 验证管理员是否已登录
 */
class LPadminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否已登录
        if (!Auth::guard('lpadmin')->check()) {
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

        // 检查管理员状态
        $admin = Auth::guard('lpadmin')->user();
        if (!$admin || $admin->status != 1) {
            Auth::guard('lpadmin')->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 403,
                    'message' => '账户已被禁用',
                    'data' => null,
                    'timestamp' => time(),
                ], 403);
            }

            return redirect()->route('lpadmin.login')->withErrors(['username' => '账户已被禁用']);
        }

        // 更新最后活动时间
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
