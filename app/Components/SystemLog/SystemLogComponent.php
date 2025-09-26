<?php

namespace App\Components\SystemLog;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\LPadmin\Rule;

/**
 * SystemLog组件主类
 * 
 * 处理组件的安装、卸载等生命周期事件
 */
class SystemLogComponent
{
    /**
     * 组件安装后的钩子
     */
    public static function install(): void
    {
        try {
            Log::info('SystemLog component installation started');

            // 注意：数据表迁移由ComponentManager统一处理，这里不需要重复执行

            // 创建权限
            Log::info('Creating SystemLog permissions...');
            self::createPermissions();

            // 注册服务提供者
            Log::info('Registering SystemLog service provider...');
            self::registerServiceProvider();

            Log::info('SystemLog component installed successfully');

        } catch (\Exception $e) {
            Log::error('SystemLog component installation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // 重新抛出异常，让ComponentManager的事务能够回滚
            throw $e;
        }
    }
    
    /**
     * 组件卸载前的钩子
     */
    public static function uninstall(): void
    {
        try {
            // 删除权限
            self::deletePermissions();

            // 注销服务提供者
            self::unregisterServiceProvider();

            // 可选：保留数据表，只清理配置
            // 如果需要完全删除，可以取消注释下面的代码
            // self::dropTables();

            Log::info('SystemLog component uninstalled successfully');

        } catch (\Exception $e) {
            Log::error('SystemLog component uninstallation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 创建数据表
     */
    protected static function createTables(): void
    {
        if (!Schema::hasTable('admin_logs')) {
            try {
                // 运行迁移
                Artisan::call('migrate', [
                    '--path' => 'app/Components/SystemLog/database/migrations',
                    '--force' => true
                ]);
                Log::info('SystemLog database tables created successfully');
            } catch (\Exception $e) {
                Log::error('Failed to create SystemLog tables: ' . $e->getMessage());
                throw $e;
            }
        } else {
            Log::info('SystemLog tables already exist, skipping creation');
        }
    }
    
    /**
     * 删除数据表（可选）
     */
    protected static function dropTables(): void
    {
        Schema::dropIfExists('admin_logs');
    }
    
    /**
     * 注册服务提供者
     */
    protected static function registerServiceProvider(): void
    {
        // 服务提供者会在组件路由注册时自动注册
        // 这里可以添加额外的注册逻辑
    }
    
    /**
     * 注销服务提供者
     */
    protected static function unregisterServiceProvider(): void
    {
        // 清除相关缓存
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
    }
    
    /**
     * 获取组件配置
     */
    public static function getConfig(): array
    {
        $configPath = __DIR__ . '/component.json';
        
        if (!file_exists($configPath)) {
            return [];
        }
        
        $config = json_decode(file_get_contents($configPath), true);
        
        return $config ?: [];
    }
    
    /**
     * 检查组件是否已安装
     */
    public static function isInstalled(): bool
    {
        return Schema::hasTable('admin_logs');
    }
    
    /**
     * 获取组件状态
     */
    public static function getStatus(): array
    {
        return [
            'installed' => self::isInstalled(),
            'table_exists' => Schema::hasTable('admin_logs'),
            'config' => self::getConfig(),
        ];
    }
    
    /**
     * 清理过期日志
     */
    public static function cleanupOldLogs(int $days = 30): int
    {
        if (!Schema::hasTable('admin_logs')) {
            return 0;
        }
        
        $cutoffDate = now()->subDays($days);
        
        return DB::table('admin_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * 创建权限
     */
    protected static function createPermissions(): void
    {
        try {
            $config = self::getConfig();
            $permissions = $config['permissions'] ?? [];

            if (empty($permissions)) {
                return;
            }

            // 使用独立事务处理权限创建
            DB::transaction(function () use ($permissions) {
                // 查找父级权限（系统管理）
                $parentRule = Rule::where('name', 'system')->first();

                // 创建系统日志权限组
                $groupRuleData = [
                    'parent_id' => $parentRule ? $parentRule->id : 0,
                    'name' => 'system-log',
                    'title' => '系统日志管理',
                    'type' => Rule::TYPE_MENU,
                    'icon' => 'layui-icon-file',
                    'route_name' => 'lpadmin.system-log.index',
                    'url' => lpadmin_url_prefix() . '/system-log',
                    'component' => 'SystemLog',
                    'status' => Rule::STATUS_ENABLED,
                    'sort' => 100,
                    'remark' => '系统日志管理权限组',
                ];

                // 检查是否有软删除的权限组，如果有则恢复
                $groupRule = Rule::withTrashed()->where('name', $groupRuleData['name'])->first();
                if ($groupRule) {
                    if ($groupRule->trashed()) {
                        $groupRule->restore();
                        $groupRule->update($groupRuleData);
                        Log::info('SystemLog permission group restored and updated');
                    }
                } else {
                    $groupRule = Rule::create($groupRuleData);
                    Log::info('SystemLog permission group created');
                }

                // 创建具体权限
                $permissionMappings = [
                    'system-log.view' => ['title' => '查看系统日志', 'type' => Rule::TYPE_API],
                    'system-log.export' => ['title' => '导出系统日志', 'type' => Rule::TYPE_API],
                    'system-log.delete' => ['title' => '删除系统日志', 'type' => Rule::TYPE_API],
                ];

                foreach ($permissions as $permission) {
                    if (isset($permissionMappings[$permission])) {
                        $mapping = $permissionMappings[$permission];
                        $ruleData = [
                            'parent_id' => $groupRule->id,
                            'name' => $permission,
                            'title' => $mapping['title'],
                            'type' => $mapping['type'],
                            'status' => Rule::STATUS_ENABLED,
                            'sort' => 0,
                            'remark' => '系统日志' . $mapping['title'] . '权限',
                        ];

                        // 检查是否有软删除的权限，如果有则恢复
                        $existingRule = Rule::withTrashed()->where('name', $permission)->first();
                        if ($existingRule) {
                            if ($existingRule->trashed()) {
                                $existingRule->restore();
                                $existingRule->update($ruleData);
                                Log::info("SystemLog permission restored: {$permission}");
                            }
                        } else {
                            Rule::create($ruleData);
                            Log::info("SystemLog permission created: {$permission}");
                        }
                    }
                }
            });

            Log::info('SystemLog permissions created successfully');

        } catch (\Exception $e) {
            Log::error('SystemLog permissions creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除权限
     */
    protected static function deletePermissions(): void
    {
        // 删除具体权限
        $config = self::getConfig();
        $permissions = $config['permissions'] ?? [];

        foreach ($permissions as $permission) {
            $rule = Rule::where('name', $permission)->first();
            if ($rule) {
                $rule->delete();
            }
        }

        // 删除权限组
        $groupRule = Rule::where('name', 'system-log')->first();
        if ($groupRule) {
            $groupRule->delete();
        }

        Log::info('SystemLog permissions deleted successfully');
    }
}
