<?php

namespace App\Components\SystemLog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

/**
 * 系统日志组件服务提供者
 */
class SystemLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 注册视图命名空间
        View::addNamespace('SystemLog', app_path('Components/SystemLog/resources/views'));

        // 注册事件监听器来记录日志
        $this->registerLogEventListener();
    }

    /**
     * 注册日志事件监听器
     */
    protected function registerLogEventListener(): void
    {
        // 监听路由匹配事件
        $this->app['events']->listen('Illuminate\Routing\Events\RouteMatched', function ($event) {
            // 在请求结束时记录日志
            $this->app->terminating(function () use ($event) {
                $request = $event->request;
                $response = app('Illuminate\Http\Response');

                // 使用组件内的日志服务记录操作
                \App\Components\SystemLog\Services\AdminLogService::log($request, $response);
            });
        });
    }
}
