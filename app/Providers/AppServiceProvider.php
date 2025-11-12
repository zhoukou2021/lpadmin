<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 设置默认字符串长度，解决MySQL索引长度限制问题
        Schema::defaultStringLength(191);

        // 组件模块已移除：不再注册组件服务提供者
        $this->app['url']->forceRootUrl(config('app.url'));
    }
}
