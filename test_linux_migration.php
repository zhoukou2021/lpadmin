<?php

require_once 'vendor/autoload.php';

// 启动Laravel应用
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Linux环境迁移路径测试 ===\n";

use App\Services\LPadmin\ComponentManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

try {
    $componentName = 'SystemLog';
    
    echo "\n1. 检查路径构建...\n";
    
    // 检查不同的路径构建方式
    $windowsPath = 'app/Components/' . $componentName . '/database/migrations';
    $linuxPath = 'app' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . $componentName . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    $basePath = base_path('app' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . $componentName . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations');
    
    echo "Windows风格路径: {$windowsPath}\n";
    echo "跨平台路径: {$linuxPath}\n";
    echo "完整路径: {$basePath}\n";
    echo "目录分隔符: '" . DIRECTORY_SEPARATOR . "'\n";
    echo "操作系统: " . PHP_OS . "\n";
    
    // 检查目录是否存在
    echo "\n2. 检查目录存在性...\n";
    echo "目录存在: " . (File::exists($basePath) ? "✅ 是" : "❌ 否") . "\n";
    
    if (File::exists($basePath)) {
        $files = File::files($basePath);
        echo "迁移文件数量: " . count($files) . "\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
    }
    
    echo "\n3. 测试迁移执行...\n";
    
    // 检查表是否已存在
    $tableExists = Schema::hasTable('admin_logs');
    echo "admin_logs表存在: " . ($tableExists ? "✅ 是" : "❌ 否") . "\n";
    
    if ($tableExists) {
        echo "表已存在，先删除以测试迁移...\n";
        Schema::dropIfExists('admin_logs');
        echo "表已删除\n";
    }
    
    // 手动执行迁移
    echo "手动执行迁移...\n";
    try {
        $output = Artisan::call('migrate', [
            '--path' => $linuxPath,
            '--force' => true
        ]);
        
        echo "迁移命令返回值: {$output}\n";
        echo "迁移输出:\n" . Artisan::output() . "\n";
        
        // 检查表是否创建成功
        $tableExists = Schema::hasTable('admin_logs');
        echo "迁移后admin_logs表存在: " . ($tableExists ? "✅ 是" : "❌ 否") . "\n";
        
        if ($tableExists) {
            echo "✅ 迁移执行成功\n";
        } else {
            echo "❌ 迁移执行失败\n";
        }
        
    } catch (Exception $e) {
        echo "❌ 迁移执行异常: " . $e->getMessage() . "\n";
    }
    
    echo "\n4. 测试组件管理器...\n";
    
    // 删除可能存在的组件记录
    \App\Models\LPadmin\Component::where('name', $componentName)->delete();
    
    // 使用组件管理器安装
    echo "使用ComponentManager安装组件...\n";
    $result = ComponentManager::installComponent($componentName);
    echo "安装结果: " . ($result ? "✅ 成功" : "❌ 失败") . "\n";
    
    // 验证结果
    $component = \App\Models\LPadmin\Component::where('name', $componentName)->first();
    $tableExists = Schema::hasTable('admin_logs');
    
    echo "验证结果:\n";
    echo "  组件记录: " . ($component ? "✅ 存在" : "❌ 不存在") . "\n";
    echo "  数据表: " . ($tableExists ? "✅ 存在" : "❌ 不存在") . "\n";
    
} catch (Exception $e) {
    echo "\n❌ 测试过程中出现错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}

echo "\n=== 测试完成 ===\n";
