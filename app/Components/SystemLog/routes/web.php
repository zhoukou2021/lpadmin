<?php

use Illuminate\Support\Facades\Route;
use App\Components\SystemLog\Controllers\SystemLogController;

/*
|--------------------------------------------------------------------------
| 系统日志管理路由
|--------------------------------------------------------------------------
|
| 系统日志的查看、搜索、导出、删除等功能路由
|
*/

Route::middleware(['lpadmin.permission:system-log.view'])->group(function () {
    // 日志列表
    Route::get('system-log', [SystemLogController::class, 'index'])->name('system-log.index');
    
    // 日志详情
    Route::get('system-log/{log}', [SystemLogController::class, 'show'])->name('system-log.show');
    
    // 获取统计信息
    Route::get('system-log/api/statistics', [SystemLogController::class, 'statistics'])->name('system-log.statistics');
});

Route::middleware(['lpadmin.permission:system-log.export'])->group(function () {
    // 导出日志
    Route::post('system-log/export', [SystemLogController::class, 'export'])->name('system-log.export');
});

Route::middleware(['lpadmin.permission:system-log.delete'])->group(function () {
    // 删除单个日志
    Route::delete('system-log/{log}', [SystemLogController::class, 'destroy'])->name('system-log.destroy');
    
    // 批量删除日志
    Route::post('system-log/batch-delete', [SystemLogController::class, 'batchDelete'])->name('system-log.batch-delete');
    
    // 清空日志
    Route::post('system-log/clear', [SystemLogController::class, 'clear'])->name('system-log.clear');
});
