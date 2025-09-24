<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LPadmin\Component;

/**
 * 组件启用检查中间件
 * 
 * 检查组件是否已安装和启用
 */
class ComponentEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $componentName): Response
    {
        // 检查组件是否已安装
        $component = Component::findByName($componentName);
        
        if (!$component || !$component->isInstalled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 403,
                    'message' => '组件未安装或已禁用',
                    'data' => null,
                    'timestamp' => time(),
                ], 403);
            }

            abort(403, '组件未安装或已禁用');
        }

        return $next($request);
    }
}
