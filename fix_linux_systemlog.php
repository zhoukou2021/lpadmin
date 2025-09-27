<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Linux环境SystemLog组件修复脚本 ===\n";

use App\Models\LPadmin\Component;
use App\Services\LPadmin\ComponentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

try {
    $componentName = 'SystemLog';
    
    echo "\n1. 环境信息检查...\n";
    echo "操作系统: " . PHP_OS . "\n";
    echo "目录分隔符: '" . DIRECTORY_SEPARATOR . "'\n";
    echo "Laravel版本: " . app()->version() . "\n";
    
    // 检查组件文件结构
    echo "\n2. 检查组件文件结构...\n";
    $componentPath = base_path('app' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . $componentName);
    $migrationsPath = $componentPath . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    
    echo "组件路径: {$componentPath}\n";
    echo "迁移路径: {$migrationsPath}\n";
    echo "组件目录存在: " . (File::exists($componentPath) ? "✅ 是" : "❌ 否") . "\n";
    echo "迁移目录存在: " . (File::exists($migrationsPath) ? "✅ 是" : "❌ 否") . "\n";
    
    if (File::exists($migrationsPath)) {
        $migrationFiles = File::files($migrationsPath);
        echo "迁移文件数量: " . count($migrationFiles) . "\n";
        foreach ($migrationFiles as $file) {
            echo "  - " . basename($file) . "\n";
        }
    }
    
    echo "\n3. 检查当前状态...\n";
    
    // 检查组件记录
    $component = Component::where('name', $componentName)->first();
    echo "组件记录存在: " . ($component ? "✅ 是 (状态: {$component->status})" : "❌ 否") . "\n";
    
    // 检查表状态
    $tableExists = Schema::hasTable('admin_logs');
    echo "admin_logs表存在: " . ($tableExists ? "✅ 是" : "❌ 否") . "\n";
    
    // 检查迁移记录
    $migrations = DB::table('migrations')->where('migration', 'like', '%admin_logs%')->get();
    echo "迁移记录数量: " . $migrations->count() . "\n";
    
    // 检查权限
    $permissions = \App\Models\LPadmin\Rule::where('name', 'like', '%system-log%')->count();
    echo "权限数量: {$permissions}\n";
    
    echo "\n4. 修复不一致状态...\n";
    
    // 如果迁移记录存在但表不存在，清理迁移记录
    if ($migrations->count() > 0 && !$tableExists) {
        echo "发现迁移记录与表状态不一致，清理迁移记录...\n";
        foreach ($migrations as $migration) {
            DB::table('migrations')->where('migration', $migration->migration)->delete();
            echo "删除迁移记录: {$migration->migration}\n";
        }
    }
    
    // 如果组件记录存在但表不存在，重新安装
    if ($component && !$tableExists) {
        echo "发现组件记录与表状态不一致，重新安装组件...\n";
        
        // 先卸载
        echo "卸载现有组件...\n";
        ComponentManager::uninstallComponent($componentName);
        
        sleep(1); // 等待一秒
        
        // 重新安装
        echo "重新安装组件...\n";
        $result = ComponentManager::installComponent($componentName);
        echo "安装结果: " . ($result ? "✅ 成功" : "❌ 失败") . "\n";
        
    } elseif (!$component) {
        echo "组件未安装，开始安装...\n";
        $result = ComponentManager::installComponent($componentName);
        echo "安装结果: " . ($result ? "✅ 成功" : "❌ 失败") . "\n";
        
    } else {
        echo "组件状态正常，无需修复\n";
    }
    
    echo "\n5. 验证修复结果...\n";
    
    // 重新检查状态
    $component = Component::where('name', $componentName)->first();
    $tableExists = Schema::hasTable('admin_logs');
    $permissions = \App\Models\LPadmin\Rule::where('name', 'like', '%system-log%')->count();
    $migrations = DB::table('migrations')->where('migration', 'like', '%admin_logs%')->get();
    
    echo "最终状态:\n";
    echo "  组件记录: " . ($component ? "✅ 存在 (状态: {$component->status})" : "❌ 不存在") . "\n";
    echo "  数据表: " . ($tableExists ? "✅ 存在" : "❌ 不存在") . "\n";
    echo "  权限数量: {$permissions}\n";
    echo "  迁移记录: " . $migrations->count() . "\n";
    
    if ($component && $component->status == Component::STATUS_INSTALLED && $tableExists && $permissions > 0) {
        echo "\n🎉 SystemLog组件修复成功！\n";
        echo "现在可以在管理后台中正常使用系统日志功能了。\n";
    } else {
        echo "\n❌ 修复可能未完全成功，请检查上述状态\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ 修复过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}

echo "\n=== 修复完成 ===\n";
