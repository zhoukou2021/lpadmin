<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LPadmin\AuthController;
use App\Http\Controllers\LPadmin\CaptchaController;
use App\Http\Controllers\LPadmin\DashboardController;
use App\Http\Controllers\LPadmin\AdminController;
use App\Http\Controllers\LPadmin\RoleController;
use App\Http\Controllers\LPadmin\RuleController;
use App\Http\Controllers\LPadmin\UserController;
use App\Http\Controllers\LPadmin\MenuController;
use App\Http\Controllers\LPadmin\ConfigController;
use App\Http\Controllers\LPadmin\CacheController;
use App\Http\Controllers\LPadmin\DocController;

/*
|--------------------------------------------------------------------------
| LPadmin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register LPadmin routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group and LPadmin configuration.
|
*/

// 获取路由配置
$routeConfig = config('lpadmin.route');

// 构建路由组配置
$groupConfig = [
    'prefix' => $routeConfig['prefix'],
    'as' => $routeConfig['name'],
    'middleware' => ['web'],
];

// 只有当域名不为空时才添加域名限制
if (!empty($routeConfig['domain'])) {
    $groupConfig['domain'] = $routeConfig['domain'];
}

// 应用路由配置
Route::group($groupConfig, function () {

    /*
    |--------------------------------------------------------------------------
    | 认证相关路由（不需要登录验证）
    |--------------------------------------------------------------------------
    */

    // 显示登录页面
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');

    // 处理登录请求
    Route::post('login', [AuthController::class, 'login']);

    // 处理退出登录请求
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // 生成验证码图片
    Route::get('captcha', [CaptchaController::class, 'generate'])->name('captcha');
    /*
    |----------------------------------------------------------------------
    | 文档管理
    |----------------------------------------------------------------------
    */
    Route::prefix('doc')->name('doc.')->group(function () {
        Route::get('/', [DocController::class, 'index'])->name('index'); // 文档列表页面
        Route::get('view', [DocController::class, 'show'])->name('view'); // 查看文档
        Route::get('download', [DocController::class, 'download'])->name('download'); // 下载文档
    });

    /*
    |--------------------------------------------------------------------------
    | 需要登录验证的路由
    |--------------------------------------------------------------------------
    */
    Route::middleware(['lpadmin.auth', 'format.time'])->group(function () {

        /*
        |----------------------------------------------------------------------
        | 主页面和仪表盘
        |----------------------------------------------------------------------
        */
        // 后台主页面（框架页面）
        Route::get('/', [AdminController::class, 'main'])->name('index');

        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index'); // 仪表盘首页
            Route::get('statistics', [DashboardController::class, 'statistics'])->name('statistics'); // 获取仪表盘统计数据
            Route::get('system-info', [DashboardController::class, 'systemInfo'])->name('system_info'); // 获取系统信息
            Route::get('recent-logins', [DashboardController::class, 'recentLogins'])->name('recent_logins'); // 获取最近登录记录
        });

        /*
        |----------------------------------------------------------------------
        | 用户信息和API接口
        |----------------------------------------------------------------------
        */

        // 获取当前登录用户信息
        Route::get('user', [AuthController::class, 'user'])->name('user');

        // 获取菜单树结构（用于前端显示）
        Route::get('api/menu', [\App\Http\Controllers\LPadmin\MenuApiController::class, 'getMenuTree'])->name('api.menu');

        // 获取权限树结构（用于权限分配）
        Route::get('api/permission-tree', [\App\Http\Controllers\LPadmin\MenuApiController::class, 'getPermissionTree'])->name('api.permission_tree');

        // 获取菜单树结构（用于菜单管理）
        Route::get('api/menu-tree', [MenuController::class, 'tree'])->name('api.menu_tree');

        // 获取当前用户权限列表
        Route::get('api/permission', [AuthController::class, 'permissions'])->name('api.permission');



        /*
        |----------------------------------------------------------------------
        | 个人资料管理
        |----------------------------------------------------------------------
        */

        // 显示个人资料页面
        Route::get('profile', [AuthController::class, 'profile'])->name('profile');

        // 更新个人资料
        Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');

        // 显示修改密码页面
        Route::get('change-password', [AuthController::class, 'showChangePassword'])->name('change_password');

        // 处理修改密码请求
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change_password.update');

        /*
        |----------------------------------------------------------------------
        | 管理员管理
        |----------------------------------------------------------------------
        */
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('/', AdminController::class)->parameters(['' => 'admin']); // 管理员资源路由
            Route::post('{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggle_status'); // 切换管理员状态
            Route::post('{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('reset_password'); // 重置管理员密码
            Route::post('batch-delete', [AdminController::class, 'batchDelete'])->name('batch_delete'); // 批量删除管理员
        });

        /*
        |----------------------------------------------------------------------
        | 角色管理
        |----------------------------------------------------------------------
        */
        Route::prefix('role')->name('role.')->group(function () {
            Route::resource('/', RoleController::class)->parameters(['' => 'role'])->except(['show']); // 角色资源路由
            Route::get('select', [RoleController::class, 'select'])->name('select'); // 获取角色选择列表
            Route::post('{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle_status'); // 切换角色状态
            Route::get('{role}/permissions', [RoleController::class, 'permissions'])->name('permissions'); // 获取角色权限
            Route::post('{role}/permissions', [RoleController::class, 'updatePermissions'])->name('update_permissions'); // 更新角色权限
        });

        /*
        |----------------------------------------------------------------------
        | 权限规则管理
        |----------------------------------------------------------------------
        */
        Route::prefix('rule')->name('rule.')->group(function () {
            Route::resource('/', RuleController::class)->parameters(['' => 'rule'])->except(['show']); // 权限规则资源路由
            Route::get('tree', [RuleController::class, 'tree'])->name('tree'); // 获取权限规则树结构
            Route::get('permission-tree', [RuleController::class, 'permissionTree'])->name('permission_tree'); // 获取权限树结构
            Route::get('select', [RuleController::class, 'select'])->name('select'); // 获取权限规则选择列表
            Route::post('{rule}/toggle-status', [RuleController::class, 'toggleStatus'])->name('toggle_status'); // 切换权限规则状态
        });

        /*
        |----------------------------------------------------------------------
        | 菜单管理
        |----------------------------------------------------------------------
        */
        Route::prefix('menu')->name('menu.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index'); // 菜单管理首页
            Route::get('select', [MenuController::class, 'select'])->name('select'); // 获取菜单选择列表
            Route::get('create', [MenuController::class, 'create'])->name('create'); // 显示创建菜单页面
            Route::post('/', [MenuController::class, 'store'])->name('store'); // 保存新菜单
            Route::get('{id}/edit', [MenuController::class, 'edit'])->name('edit'); // 显示编辑菜单页面
            Route::put('{id}', [MenuController::class, 'update'])->name('update'); // 更新菜单
            Route::patch('{id}/sort', [MenuController::class, 'updateSort'])->name('updateSort'); // 更新菜单排序
            Route::delete('{id}', [MenuController::class, 'destroy'])->name('destroy'); // 删除菜单
            Route::delete('batch', [MenuController::class, 'batchDestroy'])->name('batchDestroy'); // 批量删除菜单
        });

        /*
        |----------------------------------------------------------------------
        | 用户管理
        |----------------------------------------------------------------------
        */
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('select', [UserController::class, 'select'])->name('select'); // 获取用户选择列表
            Route::get('statistics', [UserController::class, 'statistics'])->name('statistics'); // 获取用户统计信息
            Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle_status'); // 切换用户状态
            Route::post('batch-delete', [UserController::class, 'batchDelete'])->name('batch_delete'); // 批量删除用户
            Route::resource('/', UserController::class)->parameters(['' => 'user']); // 用户资源路由
        });



        /*
        |----------------------------------------------------------------------
        | 系统配置管理
        |----------------------------------------------------------------------
        */
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('select', [ConfigController::class, 'select'])->name('select'); // 获取配置选择列表
            Route::delete('batch', [ConfigController::class, 'batchDestroy'])->name('batchDestroy'); // 批量删除配置
            Route::patch('batch-update', [ConfigController::class, 'batchUpdate'])->name('batchUpdate'); // 批量更新配置值
            Route::get('system/settings', [ConfigController::class, 'system'])->name('system'); // 系统设置页面
            Route::post('system/save', [ConfigController::class, 'saveSystem'])->name('saveSystem'); // 保存系统设置
            Route::get('export', [ConfigController::class, 'export'])->name('export'); // 配置导出
            Route::post('import', [ConfigController::class, 'import'])->name('import'); // 配置导入
            Route::get('import/page', [ConfigController::class, 'importPage'])->name('importPage'); // 配置导入页面

            // 配置分组管理
            Route::prefix('groups')->name('groups.')->group(function () {
                Route::get('page', [ConfigController::class, 'groupsPage'])->name('page'); // 配置分组管理页面
                Route::get('/', [ConfigController::class, 'groups'])->name('index'); // 获取配置分组列表
                Route::post('create-groups', [ConfigController::class, 'createGroup'])->name('create'); // 创建配置分组
                Route::put('{group}', [ConfigController::class, 'updateGroup'])->name('update'); // 更新配置分组
                Route::delete('del-groups/{group}', [ConfigController::class, 'deleteGroup'])->name('delete'); // 删除配置分组
                Route::delete('groups-batch', [ConfigController::class, 'batchDeleteGroups'])->name('batch_delete'); // 批量删除配置分组
                Route::get('get-group/{group}', [ConfigController::class, 'group'])->name('show'); // 获取指定分组的配置
            });
            Route::resource('/', ConfigController::class)->parameters(['' => 'config']); // 配置资源路由
        });

        /*
        |----------------------------------------------------------------------
        | 缓存管理
        |----------------------------------------------------------------------
        */
        Route::prefix('cache')->name('cache.')->group(function () {
            Route::get('/', [CacheController::class, 'index'])->name('index'); // 缓存管理首页
            Route::get('stats', [CacheController::class, 'stats'])->name('stats'); // 获取缓存统计信息
            Route::post('clear-type', [CacheController::class, 'clearByType'])->name('clearByType'); // 按类型清理缓存
            Route::post('clear-all', [CacheController::class, 'clearAll'])->name('clearAll'); // 清理所有缓存
            Route::post('clear-config', [CacheController::class, 'clearConfig'])->name('clearConfig'); // 清理配置缓存
            Route::post('warmup-config', [CacheController::class, 'warmupConfig'])->name('warmupConfig'); // 预热配置缓存
            Route::get('monitor', [CacheController::class, 'monitor'])->name('monitor'); // 缓存监控页面
            Route::get('monitor/data', [CacheController::class, 'monitorData'])->name('monitorData'); // 获取缓存监控数据
            Route::get('settings', [CacheController::class, 'settings'])->name('settings'); // 缓存设置页面
            Route::post('settings', [CacheController::class, 'updateSettings'])->name('updateSettings'); // 更新缓存设置
            Route::get('settings/data', [CacheController::class, 'getSettings'])->name('getSettings'); // 获取缓存设置数据
            Route::post('test-connection', [CacheController::class, 'testConnection'])->name('testConnection'); // 测试缓存连接
            Route::get('keys', [CacheController::class, 'keys'])->name('keys'); // 获取缓存键列表
            Route::delete('key', [CacheController::class, 'deleteKey'])->name('deleteKey'); // 删除缓存键
            Route::get('value', [CacheController::class, 'getValue'])->name('getValue'); // 获取缓存值
            Route::post('value', [CacheController::class, 'setValue'])->name('setValue'); // 设置缓存值
        });

    });
});


 