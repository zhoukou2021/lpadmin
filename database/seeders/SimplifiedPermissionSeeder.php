<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\LPadmin\Admin;
use App\Models\LPadmin\Role;
use App\Models\LPadmin\Rule;
use App\Models\LPadmin\Option;
use App\Models\LPadmin\User;

class SimplifiedPermissionSeeder extends Seeder
{
    /**
     * 获取后台路由前缀
     */
    private function getRoutePrefix(): string
    {
        return lpadmin_url_prefix();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清理现有数据
        $this->cleanupExistingData();

        // 创建统一的权限/菜单数据
        $this->createUnifiedRules();

        // 创建角色
        $this->createRoles();

        // 创建管理员
        $this->createAdmins();
        // 创建用户
        $this->createUsers();

        // 创建系统配置
        $this->createOptions();

        // 创建字典数据
        $this->createDictionaries();
    }

    /**
     * 清理现有数据
     */
    private function cleanupExistingData(): void
    {
        // 清理关联表
        DB::table('admin_roles')->truncate();
        DB::table('role_rules')->truncate();

        // 清理主表
        DB::table('admins')->truncate();
        DB::table('roles')->truncate();
        DB::table('rules')->truncate();
        DB::table('options')->truncate();
        DB::table('dictionaries')->truncate();
        DB::table('dictionary_items')->truncate();
    }

    /**
     * 创建统一的权限/菜单规则
     */
    private function createUnifiedRules(): void
    {
        $rules = [
            // ==================== 仪表盘 ====================
            [
                'name' => 'dashboard',
                'title' => '仪表盘',
                'type' => 'menu',
                'icon' => 'layui-icon-home',
                'url' => $this->getRoutePrefix() . '/dashboard',
                'is_show' => 1,
                'sort' => 1000,
                'children' => [
                    ['name' => 'dashboard.statistics', 'title' => '获取统计数据', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'dashboard.system_info', 'title' => '获取系统信息', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'dashboard.recent_logins', 'title' => '获取最近登录', 'type' => 'api', 'is_show' => 0],
                ]
            ],

            // ==================== 系统管理 ====================
            [
                'name' => 'system',
                'title' => '系统管理',
                'type' => 'menu',
                'icon' => 'layui-icon-set',
                'url' => '#', // 目录类型菜单
                'is_show' => 1,
                'sort' => 900,
                'children' => [
                    [
                        'name' => 'admin',
                        'title' => '管理员管理',
                        'type' => 'menu',
                        'icon' => 'layui-icon-username',
                        'url' => $this->getRoutePrefix() . '/admin',
                        'is_show' => 1,
                        'sort' => 900,
                        'children' => [
                            ['name' => 'admin.index', 'title' => '管理员列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'admin.create', 'title' => '创建管理员', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'admin.store', 'title' => '保存管理员', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'admin.edit', 'title' => '编辑管理员', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'admin.update', 'title' => '更新管理员', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'admin.destroy', 'title' => '删除管理员', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'admin.toggle_status', 'title' => '切换状态', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'admin.reset_password', 'title' => '重置密码', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'admin.batch_delete', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                        ]
                    ],
                    [
                        'name' => 'role',
                        'title' => '角色管理',
                        'type' => 'menu',
                        'icon' => 'layui-icon-group',
                        'url' => $this->getRoutePrefix() . '/role',
                        'is_show' => 1,
                        'sort' => 800,
                        'children' => [
                            ['name' => 'role.index', 'title' => '角色列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'role.create', 'title' => '创建角色', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'role.store', 'title' => '保存角色', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'role.edit', 'title' => '编辑角色', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'role.update', 'title' => '更新角色', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'role.destroy', 'title' => '删除角色', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'role.select', 'title' => '角色选择', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'role.toggle_status', 'title' => '切换状态', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'role.permissions', 'title' => '权限管理', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'role.update_permissions', 'title' => '更新权限', 'type' => 'api', 'is_show' => 0],
                        ]
                    ],
                    [
                        'name' => 'rule',
                        'title' => '权限规则',
                        'type' => 'menu',
                        'icon' => 'layui-icon-vercode',
                        'url' => $this->getRoutePrefix() . '/rule',
                        'is_show' => 1,
                        'sort' => 700,
                        'children' => [
                            ['name' => 'rule.index', 'title' => '权限列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'rule.create', 'title' => '创建权限', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'rule.store', 'title' => '保存权限', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'rule.edit', 'title' => '编辑权限', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'rule.update', 'title' => '更新权限', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'rule.destroy', 'title' => '删除权限', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'rule.select', 'title' => '权限选择', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'rule.toggle_status', 'title' => '切换状态', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'rule.tree', 'title' => '权限树', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'rule.permission_tree', 'title' => '权限树结构', 'type' => 'api', 'is_show' => 0],
                        ]
                    ],
                    [
                        'name' => 'menu',
                        'title' => '菜单管理',
                        'type' => 'menu',
                        'icon' => 'layui-icon-menu-fill',
                        'url' => $this->getRoutePrefix() . '/menu',
                        'is_show' => 1,
                        'sort' => 600,
                        'children' => [
                            ['name' => 'menu.index', 'title' => '菜单列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'menu.create', 'title' => '创建菜单', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'menu.store', 'title' => '保存菜单', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'menu.edit', 'title' => '编辑菜单', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'menu.update', 'title' => '更新菜单', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'menu.destroy', 'title' => '删除菜单', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'menu.select', 'title' => '菜单选择', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'menu.updateSort', 'title' => '更新排序', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'menu.batchDestroy', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                        ]
                    ],
                    [
                        'name' => 'component',
                        'title' => '组件管理',
                        'type' => 'menu',
                        'icon' => 'layui-icon-component',
                        'url' => $this->getRoutePrefix() . '/component',
                        'is_show' => 1,
                        'sort' => 550,
                        'children' => [
                            ['name' => 'component.index', 'title' => '组件列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'component.show', 'title' => '组件详情', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'component.install', 'title' => '安装组件', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'component.uninstall', 'title' => '卸载组件', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'component.refresh', 'title' => '刷新组件', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'component.statistics', 'title' => '组件统计', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'component.validate', 'title' => '验证组件', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'component.batch_action', 'title' => '批量操作', 'type' => 'button', 'is_show' => 0],
                        ]
                    ],
                ]
            ],

            // ==================== 用户管理 ====================
            [
                'name' => 'user',
                'title' => '用户管理',
                'type' => 'menu',
                'icon' => 'layui-icon-user',
                'url' => $this->getRoutePrefix() . '/user',
                'is_show' => 1,
                'sort' => 850,
                'children' => [
                    ['name' => 'user.index', 'title' => '用户列表', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'user.create', 'title' => '创建用户', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.store', 'title' => '保存用户', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'user.show', 'title' => '查看用户', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.edit', 'title' => '编辑用户', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.update', 'title' => '更新用户', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'user.destroy', 'title' => '删除用户', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.select', 'title' => '用户选择', 'type' => 'api', 'is_show' => 0],
                    ['name' => 'user.toggle_status', 'title' => '切换状态', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.batch_delete', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                    ['name' => 'user.statistics', 'title' => '用户统计', 'type' => 'api', 'is_show' => 0],
                ]
            ],

            // ==================== 文件管理 ====================
            [
                'name' => 'upload',
                'title' => '文件管理',
                'type' => 'menu',
                'icon' => 'layui-icon-upload',
                'url' => $this->getRoutePrefix() . '/upload',
                'is_show' => 1,
                'sort' => 800,
                'children' => [
                    ['name' => 'upload.index', 'title' => '文件列表', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/upload',
                    'children' => [
                        ['name' => 'upload.create', 'title' => '上传页面', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'upload.store', 'title' => '上传文件', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.file', 'title' => '上传普通文件', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.image', 'title' => '上传图片', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.avatar', 'title' => '上传头像', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.show', 'title' => '查看文件', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'upload.destroy', 'title' => '删除文件', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'upload.batch_delete', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'upload.download', 'title' => '下载文件', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.preview', 'title' => '预览文件', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.selector', 'title' => '文件选择器', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.statistics', 'title' => '文件统计', 'type' => 'api', 'is_show' => 0],
                    ]],
                    ['name' => 'upload.config_page', 'title' => '配置管理', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/upload/config-page',
                    'children' => [
                        ['name' => 'upload.config_update', 'title' => '更新上传配置', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'upload.config', 'title' => '上传配置', 'type' => 'api', 'is_show' => 0],
                    ]],
                    
                ]
            ],

            // ==================== 系统配置 ====================
            [
                'name' => 'config',
                'title' => '系统配置',
                'type' => 'menu',
                'icon' => 'layui-icon-set-sm',
                'url' => $this->getRoutePrefix() . '/config',
                'is_show' => 1,
                'sort' => 750,
                'children' => [
                    ['name' => 'config.system', 'title' => '系统设置', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/config/system/settings',
                    'children' => [
                        ['name' => 'config.saveSystem', 'title' => '保存系统设置', 'type' => 'api', 'is_show' => 0],
                    ]],             
                    ['name' => 'config.index', 'title' => '配置列表', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/config',
                    'children' => [
                        ['name' => 'config.create', 'title' => '创建配置', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.store', 'title' => '保存配置', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.show', 'title' => '查看配置', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.edit', 'title' => '编辑配置', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.update', 'title' => '更新配置', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.destroy', 'title' => '删除配置', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.select', 'title' => '配置选择', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.batchDestroy', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.batchUpdate', 'title' => '批量更新', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.export', 'title' => '配置导出', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'config.import', 'title' => '配置导入', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.importPage', 'title' => '导入页面', 'type' => 'button', 'is_show' => 0],
                    ]],
                    
                    ['name' => 'config.groups.page', 'title' => '分组管理', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/config/groups/page',
                    'children' => [
                        ['name' => 'config.groups.index', 'title' => '配置分组', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.groups.create', 'title' => '创建分组', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.groups.update', 'title' => '更新分组', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.groups.delete', 'title' => '删除分组', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.groups.batch_delete', 'title' => '批量删除分组', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'config.groups.show', 'title' => '查看分组', 'type' => 'api', 'is_show' => 0],
                    ]],
                    // ==================== 数据字典 ====================
                    [
                        'name' => 'dictionary',
                        'title' => '数据字典',
                        'type' => 'menu',
                        'icon' => 'layui-icon-template-1',
                        'url' => $this->getRoutePrefix() . '/dictionary',
                        'is_show' => 1,
                        'sort' => 700,
                        'children' => [
                            ['name' => 'dictionary.index', 'title' => '字典列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.create', 'title' => '创建字典', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.store', 'title' => '保存字典', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.show', 'title' => '查看字典', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.edit', 'title' => '编辑字典', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.update', 'title' => '更新字典', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.destroy', 'title' => '删除字典', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.select', 'title' => '字典选择', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.statistics', 'title' => '字典统计', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.data', 'title' => '字典数据', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.options', 'title' => '字典选项', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.clear_cache', 'title' => '清除缓存', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.usage', 'title' => '使用示例', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.toggle_status', 'title' => '切换状态', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.batch_destroy', 'title' => '批量删除', 'type' => 'button', 'is_show' => 0],
                            // 字典项管理
                            ['name' => 'dictionary.items.index', 'title' => '字典项列表', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.items.create', 'title' => '创建字典项', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.store', 'title' => '保存字典项', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.items.show', 'title' => '查看字典项', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.edit', 'title' => '编辑字典项', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.update', 'title' => '更新字典项', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.items.destroy', 'title' => '删除字典项', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.select', 'title' => '字典项选择', 'type' => 'api', 'is_show' => 0],
                            ['name' => 'dictionary.items.toggle_status', 'title' => '切换字典项状态', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.batch_destroy', 'title' => '批量删除字典项', 'type' => 'button', 'is_show' => 0],
                            ['name' => 'dictionary.items.batch_sort', 'title' => '批量排序', 'type' => 'api', 'is_show' => 0],
                        ]
                    ],
                ]
            ],

            

            // ==================== 缓存管理 ====================
            [
                'name' => 'cache',
                'title' => '缓存管理',
                'type' => 'menu',
                'icon' => 'layui-icon-engine',
                'url' => $this->getRoutePrefix() . '/cache',
                'is_show' => 1,
                'sort' => 650,
                'children' => [
                    ['name' => 'cache.index', 'title' => '缓存首页', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/cache',
                    'children' => [
                        ['name' => 'cache.stats', 'title' => '缓存统计', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.clearByType', 'title' => '按类型清理', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'cache.clearAll', 'title' => '清理所有', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'cache.clearConfig', 'title' => '清理配置缓存', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'cache.warmupConfig', 'title' => '预热配置缓存', 'type' => 'button', 'is_show' => 0],
                    ]],
                    
                    ['name' => 'cache.monitor', 'title' => '缓存监控', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/cache/monitor',
                    'children' => [
                         ['name' => 'cache.monitorData', 'title' => '监控数据', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.keys', 'title' => '缓存键列表', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.deleteKey', 'title' => '删除缓存键', 'type' => 'button', 'is_show' => 0],
                        ['name' => 'cache.getValue', 'title' => '获取缓存值', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.setValue', 'title' => '设置缓存值', 'type' => 'api', 'is_show' => 0],
                    ]],
                   
                    ['name' => 'cache.settings', 'title' => '缓存设置', 'type' => 'menu', 'is_show' => 1,'url' => $this->getRoutePrefix() . '/cache/settings',
                    'children' => [
                        ['name' => 'cache.updateSettings', 'title' => '更新设置', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.getSettings', 'title' => '获取设置', 'type' => 'api', 'is_show' => 0],
                        ['name' => 'cache.testConnection', 'title' => '测试连接', 'type' => 'api', 'is_show' => 0],
                    ]],
                    
                ]
            ],


            
        ];

        $this->insertRulesRecursively($rules);
    }

    /**
     * 递归插入权限规则
     */
    private function insertRulesRecursively($rules, $parentId = 0): void
    {
        foreach ($rules as $rule) {
            $children = $rule['children'] ?? [];
            unset($rule['children']);

            $rule['parent_id'] = $parentId;
            $rule['status'] = 1;
            $rule['target'] = $rule['target'] ?? '_self';
            $rule['is_show'] = $rule['is_show'] ?? 1;
            $rule['sort'] = $rule['sort'] ?? 0;
            $rule['created_at'] = now();
            $rule['updated_at'] = now();

            $ruleId = DB::table('rules')->insertGetId($rule);

            if (!empty($children)) {
                $this->insertRulesRecursively($children, $ruleId);
            }
        }
    }

    /**
     * 创建角色
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => '超级管理员',
                'description' => '拥有系统所有权限',
                'status' => 1,
                'sort' => 1000,
            ],
            [
                'name' => 'admin',
                'display_name' => '系统管理员',
                'description' => '拥有系统管理权限',
                'status' => 1,
                'sort' => 900,
            ],
            [
                'name' => 'operator',
                'display_name' => '操作员',
                'description' => '拥有基础操作权限',
                'status' => 1,
                'sort' => 800,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);

            // 根据角色分配权限
            if ($role->name === 'super_admin') {
                // 超级管理员分配所有权限
                $allRuleIds = Rule::pluck('id')->toArray();
                $role->rules()->sync($allRuleIds);
            } elseif ($role->name === 'admin') {
                // 系统管理员分配除了敏感操作外的所有权限
                $adminRuleIds = Rule::whereNotIn('name', [
                    'admin.destroy', 'admin.reset_password', 'role.destroy'
                ])->pluck('id')->toArray();
                $role->rules()->sync($adminRuleIds);
            } elseif ($role->name === 'operator') {
                // 操作员分配基础权限
                $operatorRuleIds = Rule::whereIn('name', [
                    'dashboard',
                    'user', 'user.create', 'user.update', 'user.toggle_status', 'user.statistics',
                    'config', 'config.select',
                    'dictionary',
                    'upload',
                ])->pluck('id')->toArray();
                $role->rules()->sync($operatorRuleIds);
            }
        }
    }

    /**
     * 创建管理员
     */
    private function createAdmins(): void
    {
        $admins = [
            [
                'username' => 'admin',
                'nickname' => '超级管理员',
                'email' => 'admin@lpadmin.com',
                'password' => Hash::make('123456'),
                'avatar' => '/static/admin/images/avatar.jpg',
                'status' => 1,
                'role_name' => 'super_admin',
            ],
            [
                'username' => 'operator',
                'nickname' => '操作员',
                'email' => 'operator@lpadmin.com',
                'password' => Hash::make('123456'),
                'avatar' => '/static/admin/images/avatar.jpg',
                'status' => 1,
                'role_name' => 'operator',
            ],
        ];

        foreach ($admins as $adminData) {
            $roleName = $adminData['role_name'];
            unset($adminData['role_name']);

            $admin = Admin::create($adminData);

            // 分配角色
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $admin->roles()->attach($role->id);
            }
        }
    }
    /**
     * 创建用户
     */
    private function createUsers(): void
    {
        $users = [
            [
                'username' => 'test1',
                'nickname' => '测试1',
                'email' => 'test@lpadmin.com',
                'password' => Hash::make('123456'),
                'avatar' => '/static/admin/images/avatar.jpg',
                'status' => 1,
                'gender' => 0,
                'phone' => '15737185100',
                'remark' => '测试测试',
            ],
            [
                'username' => 'test2',
                'nickname' => '测试2',
                'email' => 'test2@lpadmin.com',
                'password' => Hash::make('123456'),
                'avatar' => '/static/admin/images/avatar.jpg',
                'status' => 1,
                'gender' => 1,
                'phone' => '15737185101',
                'remark' => '测试测试2',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }

    /**
     * 创建系统配置
     */
    private function createOptions(): void
    {
        $options = [
            // ==================== 系统配置 ====================
            [
                'group' => 'system',
                'name' => 'system_name',
                'title' => '系统名称',
                'value' => 'LPadmin管理系统',
                'type' => 'text',
                'description' => '系统名称',
                'sort' => 100
            ],
            [
                'group' => 'system',
                'name' => 'system_version',
                'title' => '系统版本',
                'value' => '1.0.1',
                'type' => 'text',
                'description' => '系统版本',
                'sort' => 90
            ],
            [
                'group' => 'system',
                'name' => 'system_author',
                'title' => '系统作者',
                'value' => 'LPadmin Team',
                'type' => 'text',
                'description' => '系统作者',
                'sort' => 80
            ],
            [
                'group' => 'system',
                'name' => 'system_copyright',
                'title' => '版权信息',
                'value' => '© 2024 LPadmin. All rights reserved.',
                'type' => 'text',
                'description' => '版权信息',
                'sort' => 70
            ],

            // ==================== 文件上传配置 ====================
            [
                'group' => 'upload',
                'name' => 'upload_disk',
                'title' => '存储方式',
                'value' => 'public',
                'type' => 'select',
                'options' => json_encode([
                    'public' => '本地公共存储',
                    'local' => '本地私有存储'
                ]),
                'description' => '文件存储方式选择',
                'sort' => 100
            ],
            [
                'group' => 'upload',
                'name' => 'upload_path',
                'title' => '存储路径',
                'value' => 'lpadmin/uploads',
                'type' => 'text',
                'description' => '相对于存储磁盘的路径',
                'sort' => 90
            ],
            [
                'group' => 'upload',
                'name' => 'upload_max_size',
                'title' => '最大文件大小',
                'value' => '10240',
                'type' => 'number',
                'description' => '单位：KB，范围：1KB - 100MB',
                'sort' => 80
            ],
            [
                'group' => 'upload',
                'name' => 'upload_allowed_extensions',
                'title' => '允许的文件类型',
                'value' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar']),
                'type' => 'textarea',
                'description' => '允许上传的文件扩展名，JSON格式',
                'sort' => 70
            ],
            [
                'group' => 'upload',
                'name' => 'enable_security_check',
                'title' => '启用安全检查',
                'value' => '1',
                'type' => 'switch',
                'description' => '检查文件内容是否安全',
                'sort' => 60
            ],
            [
                'group' => 'upload',
                'name' => 'enable_duplicate_check',
                'title' => '启用重复检查',
                'value' => '1',
                'type' => 'switch',
                'description' => '检查是否上传重复文件',
                'sort' => 50
            ],

            // ==================== 缓存配置 ====================
            [
                'group' => 'cache',
                'name' => 'cache_driver',
                'title' => '缓存驱动',
                'value' => 'file',
                'type' => 'select',
                'options' => json_encode([
                    'file' => '文件缓存',
                    'redis' => 'Redis缓存',
                    'memcached' => 'Memcached缓存',
                    'database' => '数据库缓存',
                    'array' => '数组缓存（仅测试）'
                ]),
                'description' => '选择缓存存储驱动',
                'sort' => 100
            ],
            [
                'group' => 'cache',
                'name' => 'cache_ttl',
                'title' => '默认TTL',
                'value' => '3600',
                'type' => 'number',
                'description' => '缓存默认过期时间，单位：秒（60-86400秒）',
                'sort' => 90
            ],
            [
                'group' => 'cache',
                'name' => 'cache_prefix',
                'title' => '缓存前缀',
                'value' => 'lpadmin_',
                'type' => 'text',
                'description' => '用于区分不同应用的缓存，避免键名冲突',
                'sort' => 80
            ],
            [
                'group' => 'cache',
                'name' => 'cache_enable_compression',
                'title' => '启用压缩',
                'value' => '0',
                'type' => 'switch',
                'description' => '对缓存数据进行压缩存储',
                'sort' => 70
            ],
            [
                'group' => 'cache',
                'name' => 'cache_auto_clear',
                'title' => '自动清理',
                'value' => '1',
                'type' => 'switch',
                'description' => '自动清理过期缓存',
                'sort' => 60
            ],
            [
                'group' => 'cache',
                'name' => 'cache_clear_interval',
                'title' => '清理间隔',
                'value' => '3600',
                'type' => 'number',
                'description' => '自动清理缓存的间隔时间，单位：秒',
                'sort' => 50
            ],
        ];

        foreach ($options as $option) {
            Option::create($option);
        }
    }

    /**
     * 创建字典数据
     */
    private function createDictionaries(): void
    {
        $dictionaries = [
            [
                'name' => 'user_status',
                'title' => '用户状态',
                'description' => '用户账户状态字典',
                'status' => 1,
                'sort' => 100,
                'items' => [
                    ['label' => '正常', 'value' => '1', 'sort' => 1, 'status' => 1],
                    ['label' => '禁用', 'value' => '0', 'sort' => 2, 'status' => 1],
                ]
            ],
            [
                'name' => 'admin_status',
                'title' => '管理员状态',
                'description' => '管理员账户状态字典',
                'status' => 1,
                'sort' => 90,
                'items' => [
                    ['label' => '正常', 'value' => '1', 'sort' => 1, 'status' => 1],
                    ['label' => '禁用', 'value' => '0', 'sort' => 2, 'status' => 1],
                ]
            ],
            [
                'name' => 'gender',
                'title' => '性别',
                'description' => '性别字典',
                'status' => 1,
                'sort' => 80,
                'items' => [
                    ['label' => '男', 'value' => '1', 'sort' => 1, 'status' => 1],
                    ['label' => '女', 'value' => '0', 'sort' => 2, 'status' => 1],
                    ['label' => '保密', 'value' => '2', 'sort' => 3, 'status' => 1],
                ]
            ],
        ];

        foreach ($dictionaries as $dictData) {
            $items = $dictData['items'] ?? [];
            unset($dictData['items']);

            $dictData['created_at'] = now();
            $dictData['updated_at'] = now();

            $dictId = DB::table('dictionaries')->insertGetId($dictData);

            if (!empty($items)) {
                foreach ($items as $item) {
                    $item['dictionary_id'] = $dictId;
                    $item['created_at'] = now();
                    $item['updated_at'] = now();
                    DB::table('dictionary_items')->insert($item);
                }
            }
        }
    }
}
