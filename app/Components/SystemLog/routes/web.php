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

Route::prefix('system-log')->name('system-log.')->group(function () {
    // 日志列表
    Route::get('system-log', [SystemLogController::class, 'index'])->name('index');
    
    // 日志详情
    Route::get('{log}', [SystemLogController::class, 'show'])->name('show');
    
    // 获取统计信息
    Route::get('api/statistics', [SystemLogController::class, 'statistics'])->name('statistics');
    // 导出日志
    Route::post('export', [SystemLogController::class, 'export'])->name('export');
    // 删除单个日志
    Route::delete('{log}', [SystemLogController::class, 'destroy'])->name('destroy');
    
    // 批量删除日志
    Route::post('batch-delete', [SystemLogController::class, 'batchDelete'])->name('batch-delete');
    
    // 清空日志
    Route::post('clear', [SystemLogController::class, 'clear'])->name('clear');
});
