<?php

namespace App\Services\LPadmin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\LPadmin\Component;
use App\Models\LPadmin\Rule;
use App\Services\LPadmin\ComponentRouteManager;
use Exception;

/**
 * 组件管理器
 * 
 * 负责组件的扫描、安装、卸载、路由注册等核心功能
 */
class ComponentManager
{
    /**
     * 组件基础目录
     */
    const COMPONENTS_PATH = 'app/Components';
    
    /**
     * 组件缓存键
     */
    const CACHE_KEY = 'lpadmin_components';

    /**
     * 组件状态缓存前缀
     */
    const STATUS_CACHE_PREFIX = 'component_status_';

    /**
     * 缓存时间（秒）
     */
    const CACHE_TTL = 3600;

    /**
     * 检查组件是否启用
     *
     * @param string $componentName 组件名称
     * @return bool
     */
    public static function isEnabled(string $componentName): bool
    {
        // 如果组件名称为空，默认启用
        if (empty($componentName)) {
            return true;
        }

        $cacheKey = self::STATUS_CACHE_PREFIX . $componentName;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($componentName) {
            try {
                $component = Component::where('name', $componentName)->first();
                return $component && $component->status === Component::STATUS_INSTALLED;
            } catch (Exception $e) {
                Log::error('检查组件状态失败', [
                    'component' => $componentName,
                    'error' => $e->getMessage()
                ]);
                // 如果检查失败，默认启用（避免影响正常功能）
                return true;
            }
        });
    }
    
    /**
     * 组件状态常量
     */
    const STATUS_INSTALLED = 1;
    const STATUS_UNINSTALLED = 0;
    
    /**
     * 扫描本地组件目录
     * 
     * @return array
     */
    public static function scanComponents(): array
    {
        $componentsPath = base_path(self::COMPONENTS_PATH);
        
        if (!File::exists($componentsPath)) {
            File::makeDirectory($componentsPath, 0755, true);
            return [];
        }
        
        $components = [];
        $directories = File::directories($componentsPath);
        
        foreach ($directories as $directory) {
            $componentName = basename($directory);
            $componentInfo = self::getComponentInfo($directory);
            
            if ($componentInfo) {
                $components[$componentName] = $componentInfo;
            }
        }
        
        return $components;
    }
    
    /**
     * 获取组件信息
     * 
     * @param string $componentPath
     * @return array|null
     */
    public static function getComponentInfo(string $componentPath): ?array
    {
        $configFile = $componentPath . '/component.json';
        
        if (!File::exists($configFile)) {
            return null;
        }
        
        try {
            $config = json_decode(File::get($configFile), true);
            
            if (!$config || !isset($config['name'])) {
                return null;
            }
            
            // 检查必要的文件结构
            $hasController = File::exists($componentPath . '/Controllers');
            $hasRoutes = File::exists($componentPath . '/routes/web.php');
            $hasViews = File::exists($componentPath . '/resources/views');
            
            return array_merge($config, [
                'path' => $componentPath,
                'has_controller' => $hasController,
                'has_routes' => $hasRoutes,
                'has_views' => $hasViews,
                'is_complete' => $hasController && $hasRoutes,
                'installed_at' => self::getInstallTime($config['name']),
                'status' => self::getComponentStatus($config['name'])
            ]);
            
        } catch (Exception $e) {
            Log::error("读取组件配置失败: {$componentPath}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 安装组件
     *
     * @param string $componentName
     * @return bool
     */
    public static function installComponent(string $componentName): bool
    {
        try {
            $componentPath = base_path(self::COMPONENTS_PATH . '/' . $componentName);
            $componentInfo = self::getComponentInfo($componentPath);

            if (!$componentInfo) {
                throw new Exception("组件配置文件不存在或格式错误");
            }

            // 分步执行，每个步骤自己管理事务

            // 1. 运行数据库迁移（迁移命令自己管理事务）
            self::runMigrations($componentName);

            // 2. 执行组件安装钩子（包含权限创建等数据库操作）
            self::executeInstallHook($componentName);

            // 3. 更新组件状态（使用独立事务）
            DB::transaction(function () use ($componentName) {
                self::updateComponentStatus($componentName, self::STATUS_INSTALLED);
            });

            // 4. 注册组件路由（非数据库操作）
            self::registerComponentRoutes($componentName, $componentInfo);

            // 5. 注册服务提供者（非数据库操作）
            self::registerServiceProvider($componentName);

            // 6. 清除缓存
            Artisan::call('route:clear');
            Cache::forget(self::CACHE_KEY);

            Log::info("组件安装成功: {$componentName}");
            return true;

        } catch (Exception $e) {
            Log::error("组件安装失败: {$componentName}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // 组件安装失败时的回滚处理
            try {
                self::executeUninstallHook($componentName);
                self::deleteComponentRecord($componentName);
            } catch (Exception $rollbackException) {
                Log::error("组件安装回滚失败: {$componentName}", [
                    'error' => $rollbackException->getMessage()
                ]);
            }

            return false;
        }
    }
    
    /**
     * 卸载组件
     * 
     * @param string $componentName
     * @return bool
     */
    public static function uninstallComponent(string $componentName): bool
    {
        try {
            DB::beginTransaction();

            // 执行组件卸载钩子
            self::executeUninstallHook($componentName);

            // 回滚数据库迁移
            self::rollbackMigrations($componentName);

            // 注销组件路由
            self::unregisterComponentRoutes($componentName);

            // 删除组件记录（不使用软删除）
            self::deleteComponentRecord($componentName);
            
            // 清除路由缓存
            Artisan::call('route:clear');
            
            // 清除组件缓存
            Cache::forget(self::CACHE_KEY);
            
            DB::commit();
            
            Log::info("组件卸载成功: {$componentName}");
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("组件卸载失败: {$componentName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取已安装的组件列表
     * 
     * @return array
     */
    public static function getInstalledComponents(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $components = self::scanComponents();
            return array_filter($components, function ($component) {
                return $component['status'] === self::STATUS_INSTALLED;
            });
        });
    }
    
    /**
     * 运行组件数据库迁移
     * 
     * @param string $componentName
     */
    protected static function runMigrations(string $componentName): void
    {
        $migrationsPath = base_path(self::COMPONENTS_PATH . '/' . $componentName . '/database/migrations');
        
        if (File::exists($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => 'app/Components/' . $componentName . '/database/migrations'
            ]);
        }
    }
    
    /**
     * 回滚组件数据库迁移
     * 
     * @param string $componentName
     */
    protected static function rollbackMigrations(string $componentName): void
    {
        $migrationsPath = base_path(self::COMPONENTS_PATH . '/' . $componentName . '/database/migrations');
        
        if (File::exists($migrationsPath)) {
            // 这里可以实现更精确的回滚逻辑
            Log::info("回滚组件迁移: {$componentName}");
        }
    }
    
    /**
     * 注册组件路由
     *
     * @param string $componentName
     * @param array $componentInfo
     */
    protected static function registerComponentRoutes(string $componentName, array $componentInfo): void
    {
        $config = [
            'name' => $componentInfo['name'] ?? strtolower($componentName),
            'middleware' => ['web', 'lpadmin.auth'],
            'enabled_check' => true
        ];

        ComponentRouteManager::addComponentRouteConfig($componentName, $config);
        Log::info("注册组件路由: {$componentName}");
    }

    /**
     * 注销组件路由
     *
     * @param string $componentName
     */
    protected static function unregisterComponentRoutes(string $componentName): void
    {
        ComponentRouteManager::removeComponentRouteConfig($componentName);
        Log::info("注销组件路由: {$componentName}");
    }
    
    /**
     * 获取组件状态
     *
     * @param string $componentName
     * @return int
     */
    protected static function getComponentStatus(string $componentName): int
    {
        $component = Component::findByName($componentName);
        return $component ? $component->status : self::STATUS_UNINSTALLED;
    }

    /**
     * 更新组件状态
     *
     * @param string $componentName
     * @param int $status
     */
    protected static function updateComponentStatus(string $componentName, int $status): void
    {
        $component = Component::findByName($componentName);

        if ($component) {
            // 直接使用update方法，避免调用模型的install/uninstall方法
            $component->update([
                'status' => $status,
                'installed_at' => $status === self::STATUS_INSTALLED ? now() : null,
            ]);
        } else {
            // 如果组件不存在，创建新记录
            $componentPath = base_path(self::COMPONENTS_PATH . '/' . $componentName);
            $componentInfo = self::getComponentInfo($componentPath);

            if ($componentInfo) {
                Component::create([
                    'name' => $componentName,
                    'title' => $componentInfo['title'] ?? $componentName,
                    'description' => $componentInfo['description'] ?? '',
                    'version' => $componentInfo['version'] ?? '1.0.0',
                    'author' => $componentInfo['author'] ?? '',
                    'config' => $componentInfo,
                    'status' => $status,
                    'installed_at' => $status === self::STATUS_INSTALLED ? now() : null,
                ]);
            }
        }

        Log::info("更新组件状态: {$componentName} -> {$status}");
    }

    /**
     * 删除组件记录
     */
    protected static function deleteComponentRecord(string $componentName): void
    {
        $component = Component::findByName($componentName);

        if ($component) {
            $component->delete();
            Log::info("删除组件记录: {$componentName}");
        }
    }

    /**
     * 注册组件服务提供者
     */
    protected static function registerServiceProvider(string $componentName): void
    {
        $providerClass = "App\\Components\\{$componentName}\\Providers\\{$componentName}ServiceProvider";

        if (class_exists($providerClass)) {
            try {
                app()->register($providerClass);
                Log::info("组件服务提供者注册成功: {$providerClass}");
            } catch (Exception $e) {
                Log::warning("组件服务提供者注册失败: {$providerClass}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * 注册所有已安装组件的服务提供者
     */
    public static function registerAllInstalledProviders(): void
    {
        $installedComponents = Component::where('status', self::STATUS_INSTALLED)->get();

        foreach ($installedComponents as $component) {
            $componentName = str_replace('-', '', ucwords($component->name, '-'));
            self::registerServiceProvider($componentName);
        }
    }

    /**
     * 执行组件安装钩子
     */
    protected static function executeInstallHook(string $componentName): void
    {
        $componentClass = "App\\Components\\{$componentName}\\{$componentName}Component";

        if (class_exists($componentClass) && method_exists($componentClass, 'install')) {
            try {
                $componentClass::install();
            } catch (\Exception $e) {
                Log::error("组件安装钩子执行失败: {$componentName}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // 重新抛出异常，确保事务能够回滚
                throw $e;
            }
        }
    }

    /**
     * 执行组件卸载钩子
     */
    protected static function executeUninstallHook(string $componentName): void
    {
        try {
            $componentClass = "App\\Components\\{$componentName}\\{$componentName}Component";

            if (class_exists($componentClass) && method_exists($componentClass, 'uninstall')) {
                $componentClass::uninstall();
            }
        } catch (\Exception $e) {
            Log::warning("组件卸载钩子执行失败: {$componentName}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 获取组件安装时间
     *
     * @param string $componentName
     * @return string|null
     */
    protected static function getInstallTime(string $componentName): ?string
    {
        $component = Component::findByName($componentName);
        return $component && $component->installed_at ?
            (is_string($component->installed_at) ? $component->installed_at : $component->installed_at->format('Y-m-d H:i:s')) :
            null;
    }

    /**
     * 为组件创建菜单
     */
    public static function createComponentMenus(string $componentName, array $menuConfig): void
    {
        if (empty($menuConfig)) {
            return;
        }

        // 查找父级菜单
        $parentMenu = null;
        if (!empty($menuConfig['parent'])) {
            $parentMenu = Rule::where('name', $menuConfig['parent'])->where('type', Rule::TYPE_MENU)->first();
        }

        // 创建组件菜单
        $menuData = [
            'parent_id' => $parentMenu ? $parentMenu->id : 0,
            'title' => $menuConfig['title'] ?? $componentName,
            'name' => strtolower($componentName) . '.index',
            'icon' => $menuConfig['icon'] ?? 'layui-icon-component',
            'url' => lpadmin_url_prefix() . '/' . strtolower(str_replace('_', '-', $componentName)),
            'component' => $componentName,
            'type' => Rule::TYPE_MENU,
            'target' => '_self',
            'is_show' => Rule::SHOW_VISIBLE,
            'status' => Rule::STATUS_ENABLED,
            'sort' => $menuConfig['sort'] ?? 100,
            'remark' => $componentName . '组件菜单',
        ];

        // 检查菜单是否已存在
        $existingMenu = Rule::where('name', $menuData['name'])->where('type', Rule::TYPE_MENU)->first();
        if (!$existingMenu) {
            Rule::create($menuData);
            Log::info("Component menu created for {$componentName}");
        }
    }

    /**
     * 删除组件菜单
     */
    public static function deleteComponentMenus(string $componentName): void
    {
        $menuName = strtolower($componentName) . '.index';
        $menu = Rule::where('name', $menuName)->where('type', Rule::TYPE_MENU)->first();
        if ($menu) {
            $menu->delete();
            Log::info("Component menu deleted for {$componentName}");
        }
    }

    /**
     * 为组件创建权限
     */
    public static function createComponentPermissions(string $componentName, array $permissions): void
    {
        if (empty($permissions)) {
            return;
        }

        // 查找父级权限（系统管理）
        $parentRule = Rule::where('name', 'system')->first();

        // 创建组件权限组
        $groupName = strtolower(str_replace('_', '-', $componentName));
        $groupRuleData = [
            'parent_id' => $parentRule ? $parentRule->id : 0,
            'name' => $groupName,
            'title' => $componentName . '管理',
            'type' => Rule::TYPE_MENU,
            'icon' => 'layui-icon-component',
            'route_name' => 'lpadmin.' . $groupName . '.index',
            'url' => lpadmin_url_prefix() . '/' . $groupName,
            'component' => $componentName,
            'status' => Rule::STATUS_ENABLED,
            'sort' => 100,
            'remark' => $componentName . '组件权限组',
        ];

        // 检查是否有软删除的权限组，如果有则恢复
        $groupRule = Rule::withTrashed()->where('name', $groupRuleData['name'])->first();
        if ($groupRule) {
            if ($groupRule->trashed()) {
                $groupRule->restore();
                $groupRule->update($groupRuleData);
                Log::info("Component permission group restored for {$componentName}");
            }
        } else {
            $groupRule = Rule::create($groupRuleData);
            Log::info("Component permission group created for {$componentName}");
        }

        // 创建具体权限
        foreach ($permissions as $permission) {
            $ruleData = [
                'parent_id' => $groupRule->id,
                'name' => $permission,
                'title' => self::getPermissionTitle($permission),
                'type' => Rule::TYPE_BUTTON,
                'status' => Rule::STATUS_ENABLED,
                'sort' => 0,
                'remark' => $componentName . '组件权限',
            ];

            // 检查是否有软删除的权限，如果有则恢复
            $existingRule = Rule::withTrashed()->where('name', $permission)->first();
            if ($existingRule) {
                if ($existingRule->trashed()) {
                    $existingRule->restore();
                    $existingRule->update($ruleData);
                    Log::info("Component permission restored: {$permission}");
                }
            } else {
                Rule::create($ruleData);
                Log::info("Component permission created: {$permission}");
            }
        }

        Log::info("Component permissions created for {$componentName}");
    }

    /**
     * 删除组件权限
     */
    public static function deleteComponentPermissions(string $componentName, array $permissions): void
    {
        // 删除具体权限
        foreach ($permissions as $permission) {
            $rule = Rule::where('name', $permission)->first();
            if ($rule) {
                $rule->delete();
            }
        }

        // 删除权限组
        $groupName = strtolower(str_replace('_', '-', $componentName));
        $groupRule = Rule::where('name', $groupName)->first();
        if ($groupRule) {
            $groupRule->delete();
        }

        Log::info("Component permissions deleted for {$componentName}");
    }

    /**
     * 根据权限名称生成权限标题
     */
    protected static function getPermissionTitle(string $permission): string
    {
        $parts = explode('.', $permission);
        $action = end($parts);

        $actionMap = [
            'view' => '查看',
            'create' => '创建',
            'edit' => '编辑',
            'update' => '更新',
            'delete' => '删除',
            'export' => '导出',
            'import' => '导入',
            'index' => '列表',
            'show' => '详情',
            'store' => '保存',
            'destroy' => '删除',
        ];

        return $actionMap[$action] ?? ucfirst($action);
    }
}
