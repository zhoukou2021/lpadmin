<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LPadmin\ComponentManager;
use App\Services\LPadmin\ComponentRouteManager;

/**
 * 组件管理命令
 */
class ComponentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lpadmin:component 
                            {action : 操作类型 (list|install|uninstall|refresh|stats)}
                            {name? : 组件名称 (install/uninstall时必需)}
                            {--force : 强制执行操作}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'LPadmin组件管理命令';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $name = $this->argument('name');

        switch ($action) {
            case 'list':
                return $this->listComponents();
                
            case 'install':
                if (!$name) {
                    $this->error('安装组件时必须指定组件名称');
                    return 1;
                }
                return $this->installComponent($name);
                
            case 'uninstall':
                if (!$name) {
                    $this->error('卸载组件时必须指定组件名称');
                    return 1;
                }
                return $this->uninstallComponent($name);
                
            case 'refresh':
                return $this->refreshComponents();
                
            case 'stats':
                return $this->showStatistics();
                
            default:
                $this->error('不支持的操作: ' . $action);
                $this->line('支持的操作: list, install, uninstall, refresh, stats');
                return 1;
        }
    }

    /**
     * 列出所有组件
     */
    protected function listComponents(): int
    {
        $components = ComponentManager::scanComponents();

        if (empty($components)) {
            $this->info('没有找到任何组件');
            return 0;
        }

        $this->info('组件列表:');
        $this->line('');

        $headers = ['名称', '标题', '版本', '作者', '状态', '完整性'];
        $rows = [];

        foreach ($components as $name => $component) {
            $rows[] = [
                $name,
                $component['title'] ?? $name,
                $component['version'] ?? '1.0.0',
                $component['author'] ?? '',
                $component['status'] === ComponentManager::STATUS_INSTALLED ? '已安装' : '未安装',
                $component['is_complete'] ? '完整' : '不完整'
            ];
        }

        $this->table($headers, $rows);
        return 0;
    }

    /**
     * 安装组件
     */
    protected function installComponent(string $name): int
    {
        $this->info("正在安装组件: {$name}");

        if (!$this->option('force')) {
            if (!$this->confirm("确定要安装组件 '{$name}' 吗？")) {
                $this->info('操作已取消');
                return 0;
            }
        }

        $result = ComponentManager::installComponent($name);

        if ($result) {
            $this->info("组件 '{$name}' 安装成功");
            return 0;
        } else {
            $this->error("组件 '{$name}' 安装失败");
            return 1;
        }
    }

    /**
     * 卸载组件
     */
    protected function uninstallComponent(string $name): int
    {
        $this->info("正在卸载组件: {$name}");

        if (!$this->option('force')) {
            if (!$this->confirm("确定要卸载组件 '{$name}' 吗？这可能会删除相关数据！")) {
                $this->info('操作已取消');
                return 0;
            }
        }

        $result = ComponentManager::uninstallComponent($name);

        if ($result) {
            $this->info("组件 '{$name}' 卸载成功");
            return 0;
        } else {
            $this->error("组件 '{$name}' 卸载失败");
            return 1;
        }
    }

    /**
     * 刷新组件
     */
    protected function refreshComponents(): int
    {
        $this->info('正在刷新组件列表...');

        // 清除组件缓存
        \Illuminate\Support\Facades\Cache::forget(ComponentManager::CACHE_KEY);

        // 自动发现组件路由
        ComponentRouteManager::autoDiscoverAndRegisterRoutes();

        // 清除路由缓存
        $this->call('route:clear');

        $this->info('组件列表刷新完成');
        return 0;
    }

    /**
     * 显示统计信息
     */
    protected function showStatistics(): int
    {
        $components = ComponentManager::scanComponents();
        $routeStats = ComponentRouteManager::getRouteStats();

        $totalComponents = count($components);
        $installedComponents = count(array_filter($components, function($c) {
            return $c['status'] === ComponentManager::STATUS_INSTALLED;
        }));
        $completeComponents = count(array_filter($components, function($c) {
            return $c['is_complete'];
        }));

        $this->info('组件统计信息:');
        $this->line('');
        
        $this->table(['项目', '数量'], [
            ['总组件数', $totalComponents],
            ['已安装组件', $installedComponents],
            ['未安装组件', $totalComponents - $installedComponents],
            ['完整组件', $completeComponents],
            ['不完整组件', $totalComponents - $completeComponents],
            ['已注册路由组件', $routeStats['components_with_routes']],
            ['无路由组件', $routeStats['components_without_routes']],
        ]);

        return 0;
    }
}
