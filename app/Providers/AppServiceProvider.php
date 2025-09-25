<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\LPadmin\ComponentManager;

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

        // 注册已安装组件的服务提供者
        try {
            ComponentManager::registerAllInstalledProviders();
        } catch (\Exception $e) {
            // 忽略错误，避免在迁移时出现问题
        }
        $this->app['url']->forceRootUrl(config('app.url'));
    }
}
