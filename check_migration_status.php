<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 迁移状态检查 ===\n";

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // 检查迁移记录
    echo "\n1. 检查迁移记录:\n";
    $migrations = DB::table('migrations')->where('migration', 'like', '%admin_logs%')->get();
    
    if ($migrations->count() > 0) {
        echo "找到SystemLog迁移记录:\n";
        foreach ($migrations as $migration) {
            echo "  - {$migration->migration} (batch: {$migration->batch})\n";
        }
    } else {
        echo "未找到SystemLog迁移记录\n";
    }
    
    // 检查表状态
    echo "\n2. 检查表状态:\n";
    $tableExists = Schema::hasTable('admin_logs');
    echo "admin_logs表存在: " . ($tableExists ? "✅ 是" : "❌ 否") . "\n";
    
    // 如果迁移记录存在但表不存在，删除迁移记录
    if ($migrations->count() > 0 && !$tableExists) {
        echo "\n3. 修复不一致状态:\n";
        echo "迁移记录存在但表不存在，删除迁移记录...\n";
        
        foreach ($migrations as $migration) {
            DB::table('migrations')->where('migration', $migration->migration)->delete();
            echo "删除迁移记录: {$migration->migration}\n";
        }
        
        echo "✅ 迁移记录已清理\n";
    }
    
    // 重新执行迁移
    echo "\n4. 重新执行迁移:\n";
    $output = \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'app/Components/SystemLog/database/migrations',
        '--force' => true
    ]);
    
    echo "迁移命令返回值: {$output}\n";
    echo "迁移输出:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    
    // 再次检查表状态
    $tableExists = Schema::hasTable('admin_logs');
    echo "迁移后admin_logs表存在: " . ($tableExists ? "✅ 是" : "❌ 否") . "\n";
    
    if ($tableExists) {
        echo "✅ 迁移修复成功\n";
    } else {
        echo "❌ 迁移修复失败\n";
    }
    
} catch (Exception $e) {
    echo "❌ 检查过程中出现错误: " . $e->getMessage() . "\n";
}

echo "\n=== 检查完成 ===\n";
