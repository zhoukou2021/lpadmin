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
    }

    /**
     * 清理现有数据
     */
    private function cleanupExistingData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 清理关联表
            DB::table('admin_roles')->truncate();
            DB::table('role_rules')->truncate();

            // 清理主表（注意：有外键约束的表需要特殊处理）
            DB::table('admins')->truncate();
            DB::table('roles')->truncate();
            DB::table('rules')->truncate();
            DB::table('options')->truncate();
        } catch (\Exception) {
            // 如果TRUNCATE失败，使用DELETE方式
            $this->cleanupWithDelete();
        } finally {
            // 重新启用外键检查
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * 使用DELETE方式清理数据（备选方案）
     */
    private function cleanupWithDelete(): void
    {
        // 清理关联表
        DB::table('admin_roles')->delete();
        DB::table('role_rules')->delete();

        // 清理主表
        DB::table('admins')->delete();
        DB::table('roles')->delete();
        DB::table('rules')->delete();
        DB::table('options')->delete();

        // 重置自增ID（可选）
        DB::statement('ALTER TABLE admin_roles AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE role_rules AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE admin_logs AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE admins AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE rules AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE options AUTO_INCREMENT = 1');
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
            [
                'group' => 'system',
                'name' => 'lang',
                'title' => '多语言',
                'value' => 'zh_CN,en',
                'type' => 'checkbox',
                'options' => json_encode([
                    'zh_CN' => '中文',
                    'en' => '英文',
                ]),
                'description' => '多语言',
                'sort' => 60
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
}
