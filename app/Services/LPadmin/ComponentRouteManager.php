<?php

namespace App\Services\LPadmin;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * 组件路由管理器
 * 
 * 统一管理所有组件的路由注册，确保路由缓存时能正确包含组件路由
 */
class ComponentRouteManager
{
    /**
     * 组件路由配置缓存键
     */
    const ROUTE_CONFIG_CACHE_KEY = 'lpadmin_component_routes';
    
    /**
     * 已注册的组件路由配置
     */
    protected static array $componentRouteConfig = [];
    
    /**
     * 注册所有组件路由
     */
    public static function registerAllComponentRoutes(): void
    {
        $config = self::getComponentRouteConfig();
        
        foreach ($config as $componentDir => $componentConfig) {
            self::registerComponentRoute($componentDir, $componentConfig);
        }
        
        Log::info('所有组件路由注册完成', ['count' => count($config)]);
    }
    
    /**
     * 注册单个组件路由
     * 
     * @param string $componentDir
     * @param array $config
     */
    protected static function registerComponentRoute(string $componentDir, array $config): void
    {
        $routesPath = base_path("app/Components/{$componentDir}/routes/web.php");

        if (!File::exists($routesPath)) {
            Log::debug("组件路由文件不存在: {$routesPath}");
            return;
        }

        // 注册组件服务提供者
        self::registerComponentServiceProvider($componentDir);

        try {
            $middleware = $config['middleware'];
            
            // 如果需要检查组件启用状态，添加相应中间件
            if ($config['enabled_check']) {
                $middleware[] = 'component.enabled:' . $config['name'];
            }
            
            Route::middleware($middleware)
                ->prefix('lpadmin')
                ->name('lpadmin.')
                ->group(function () use ($routesPath, $componentDir) {
                    Log::debug("注册组件路由: {$componentDir}");
                    include $routesPath;
                });
            
            Log::info("组件路由注册成功: {$componentDir}");
            
        } catch (\Exception $e) {
            Log::error("组件路由注册失败: {$componentDir}", [
                'error' => $e->getMessage(),
                'file' => $routesPath
            ]);
        }
    }
    
    /**
     * 添加组件路由配置
     * 
     * @param string $componentDir
     * @param array $config
     */
    public static function addComponentRouteConfig(string $componentDir, array $config): void
    {
        $defaultConfig = [
            'middleware' => ['web', 'lpadmin.auth'],
            'enabled_check' => true
        ];
        
        self::$componentRouteConfig[$componentDir] = array_merge($defaultConfig, $config);
        
        // 保存到缓存
        self::saveComponentRouteConfig();
        
        Log::info("添加组件路由配置: {$componentDir}");
    }
    
    /**
     * 移除组件路由配置
     * 
     * @param string $componentDir
     */
    public static function removeComponentRouteConfig(string $componentDir): void
    {
        unset(self::$componentRouteConfig[$componentDir]);
        
        // 保存到缓存
        self::saveComponentRouteConfig();
        
        Log::info("移除组件路由配置: {$componentDir}");
    }
    
    /**
     * 获取组件路由配置
     * 
     * @return array
     */
    public static function getComponentRouteConfig(): array
    {
        if (empty(self::$componentRouteConfig)) {
            self::$componentRouteConfig = Cache::get(self::ROUTE_CONFIG_CACHE_KEY, []);
        }
        
        return self::$componentRouteConfig;
    }
    
    /**
     * 保存组件路由配置到缓存
     */
    protected static function saveComponentRouteConfig(): void
    {
        Cache::put(self::ROUTE_CONFIG_CACHE_KEY, self::$componentRouteConfig, 86400); // 24小时
    }
    
    /**
     * 自动发现并注册组件路由
     */
    public static function autoDiscoverAndRegisterRoutes(): void
    {
        $componentsPath = base_path('app/Components');
        
        if (!File::exists($componentsPath)) {
            Log::info('组件目录不存在，跳过自动发现');
            return;
        }
        
        $componentDirs = File::directories($componentsPath);
        
        foreach ($componentDirs as $componentPath) {
            $componentDir = basename($componentPath);
            
            // 跳过已配置的组件
            if (isset(self::$componentRouteConfig[$componentDir])) {
                continue;
            }
            
            // 检查是否有路由文件
            $routesPath = $componentPath . '/routes/web.php';
            if (!File::exists($routesPath)) {
                continue;
            }
            
            // 尝试从 component.json 读取配置
            $componentConfig = self::getComponentConfig($componentPath);
            
            // 使用默认配置注册路由
            $config = [
                'name' => $componentConfig['name'] ?? strtolower($componentDir),
                'middleware' => ['web', 'lpadmin.auth'],
                'enabled_check' => true
            ];
            
            Log::info("自动发现组件: {$componentDir}");
            self::addComponentRouteConfig($componentDir, $config);
        }
        
        Log::info('自动发现组件路由完成');
    }
    
    /**
     * 获取组件配置
     * 
     * @param string $componentPath
     * @return array
     */
    protected static function getComponentConfig(string $componentPath): array
    {
        $configFile = $componentPath . '/component.json';
        
        if (!File::exists($configFile)) {
            return [];
        }
        
        try {
            $content = File::get($configFile);
            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            Log::warning("读取组件配置失败: {$configFile}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 获取路由统计信息
     * 
     * @return array
     */
    public static function getRouteStats(): array
    {
        $config = self::getComponentRouteConfig();
        $stats = [
            'total_components' => count($config),
            'enabled_components' => 0,
            'disabled_components' => 0,
            'components_with_routes' => 0,
            'components_without_routes' => 0
        ];
        
        foreach ($config as $componentDir => $componentConfig) {
            $routesPath = base_path("app/Components/{$componentDir}/routes/web.php");
            
            if (File::exists($routesPath)) {
                $stats['components_with_routes']++;
            } else {
                $stats['components_without_routes']++;
            }
            
            if ($componentConfig['enabled_check']) {
                $stats['enabled_components']++;
            } else {
                $stats['disabled_components']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * 清除组件路由缓存
     */
    public static function clearRouteCache(): void
    {
        Cache::forget(self::ROUTE_CONFIG_CACHE_KEY);
        self::$componentRouteConfig = [];
        
        Log::info('组件路由缓存已清除');
    }
    
    /**
     * 验证组件路由配置
     * 
     * @param string $componentDir
     * @return array
     */
    public static function validateComponentRoute(string $componentDir): array
    {
        $errors = [];
        $warnings = [];
        
        $componentPath = base_path("app/Components/{$componentDir}");
        $routesPath = $componentPath . '/routes/web.php';
        $configFile = $componentPath . '/component.json';
        
        // 检查组件目录
        if (!File::exists($componentPath)) {
            $errors[] = "组件目录不存在: {$componentPath}";
        }
        
        // 检查路由文件
        if (!File::exists($routesPath)) {
            $errors[] = "路由文件不存在: {$routesPath}";
        }
        
        // 检查配置文件
        if (!File::exists($configFile)) {
            $warnings[] = "配置文件不存在: {$configFile}";
        }
        
        // 检查控制器目录
        $controllersPath = $componentPath . '/Controllers';
        if (!File::exists($controllersPath)) {
            $warnings[] = "控制器目录不存在: {$controllersPath}";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * 注册组件服务提供者
     *
     * @param string $componentDir
     */
    protected static function registerComponentServiceProvider(string $componentDir): void
    {
        $providerClass = "App\\Components\\{$componentDir}\\Providers\\{$componentDir}ServiceProvider";

        if (class_exists($providerClass)) {
            try {
                app()->register($providerClass);
                Log::debug("组件服务提供者注册成功: {$providerClass}");
            } catch (Exception $e) {
                Log::warning("组件服务提供者注册失败: {$providerClass}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
