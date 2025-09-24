# LPadmin 权限系统设计

本文档详细介绍LPadmin权限系统的设计架构、实现原理和使用方法。

## 🏗️ 权限系统架构

### RBAC模型
LPadmin采用基于角色的访问控制（RBAC）模型：

```
用户(Admin) ←→ 角色(Role) ←→ 权限(Rule)
     ↓              ↓              ↓
  管理员表      角色表        权限规则表
```

### 核心组件
1. **管理员(Admin)**: 系统用户，可分配多个角色
2. **角色(Role)**: 权限集合，支持层级结构
3. **权限规则(Rule)**: 具体的权限定义，包括菜单和操作权限
4. **关联表**: 管理员-角色、角色-权限的多对多关联

## 🔐 权限类型

### 1. 菜单权限
控制用户可以访问的菜单项。

```php
// 权限规则示例
[
    'type' => 0,        // 目录
    'title' => '系统管理',
    'name' => 'system',
    'icon' => 'layui-icon-set',
    'href' => '',
    'is_menu' => 1
]

[
    'type' => 1,        // 菜单
    'title' => '管理员管理',
    'name' => 'admin.index',
    'icon' => 'layui-icon-username',
    'href' => '/lpadmin/admin',
    'is_menu' => 1
]
```

### 2. 操作权限
控制用户可以执行的具体操作。

```php
// 操作权限示例
[
    'type' => 2,        // 权限
    'title' => '新增管理员',
    'name' => 'admin.create',
    'method' => 'POST',
    'href' => '/lpadmin/admin',
    'is_menu' => 0
]

[
    'type' => 2,        // 权限
    'title' => '删除管理员',
    'name' => 'admin.delete',
    'method' => 'DELETE',
    'href' => '/lpadmin/admin/*',
    'is_menu' => 0
]
```

### 3. 数据权限
控制用户可以访问的数据范围。

```php
// 数据权限条件示例
[
    'name' => 'user.view',
    'condition' => json_encode([
        'field' => 'created_by',
        'operator' => '=',
        'value' => '{admin_id}'
    ])
]
```

## 🎭 角色管理

### 角色层级
支持多层级角色结构，子角色继承父角色权限。

```php
// 角色层级示例
超级管理员 (level: 1)
├── 系统管理员 (level: 2)
│   ├── 用户管理员 (level: 3)
│   └── 内容管理员 (level: 3)
└── 财务管理员 (level: 2)
    └── 财务专员 (level: 3)
```

### 角色权限继承
```php
class Role extends Model
{
    // 获取角色的所有权限（包括继承的权限）
    public function getAllPermissions()
    {
        $permissions = $this->rules()->pluck('name')->toArray();
        
        // 获取父级角色权限
        if ($this->pid > 0) {
            $parent = self::find($this->pid);
            if ($parent) {
                $permissions = array_merge($permissions, $parent->getAllPermissions());
            }
        }
        
        return array_unique($permissions);
    }
}
```

## 🛡️ 权限验证

### 1. 中间件验证
通过中间件在路由层面进行权限验证。

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LPadminPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $admin = auth('lpadmin')->user();
        
        if (!$admin) {
            return redirect()->route('lpadmin.login');
        }

        // 超级管理员跳过权限检查
        if ($admin->id === 1) {
            return $next($request);
        }

        // 自动推断权限
        if (!$permission) {
            $permission = $this->inferPermission($request);
        }

        // 检查权限
        if ($permission && !$admin->hasPermission($permission)) {
            if ($request->ajax()) {
                return response()->json([
                    'code' => 403,
                    'message' => '权限不足'
                ], 403);
            }
            abort(403, '权限不足');
        }

        return $next($request);
    }

    protected function inferPermission(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        // 从路由名称推断权限
        $routeName = $route->getName();
        if ($routeName && str_starts_with($routeName, 'lpadmin.')) {
            return str_replace('lpadmin.', '', $routeName);
        }

        // 从控制器和方法推断权限
        $action = $route->getActionName();
        if (preg_match('/Controllers\\\\LPadmin\\\\(\w+)Controller@(\w+)/', $action, $matches)) {
            $controller = strtolower($matches[1]);
            $method = $matches[2];
            return "{$controller}.{$method}";
        }

        return null;
    }
}
```

### 2. 模型层验证
在模型中定义权限验证方法。

```php
<?php
namespace App\Models\LPadmin;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    // 检查是否有指定权限
    public function hasPermission(string $permission): bool
    {
        // 超级管理员拥有所有权限
        if ($this->id === 1) {
            return true;
        }

        // 检查角色权限
        return $this->roles()->whereHas('rules', function ($query) use ($permission) {
            $query->where('name', $permission)->where('status', 1);
        })->exists();
    }

    // 检查是否有任一权限
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    // 检查是否有所有权限
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    // 获取用户所有权限
    public function getPermissions(): array
    {
        if ($this->id === 1) {
            return ['*']; // 超级管理员拥有所有权限
        }

        return $this->roles()
            ->with('rules')
            ->get()
            ->pluck('rules')
            ->flatten()
            ->where('status', 1)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }
}
```

### 3. 视图层权限控制
在Blade模板中进行权限判断。

```blade
{{-- 检查单个权限 --}}
@can('admin.create')
    <button class="layui-btn" onclick="add()">
        <i class="layui-icon layui-icon-add-1"></i> 新增
    </button>
@endcan

{{-- 检查多个权限（任一） --}}
@canany(['admin.edit', 'admin.delete'])
    <div class="layui-btn-group">
        @can('admin.edit')
            <button class="layui-btn layui-btn-sm" onclick="edit({{ $admin->id }})">编辑</button>
        @endcan
        @can('admin.delete')
            <button class="layui-btn layui-btn-sm layui-btn-danger" onclick="delete({{ $admin->id }})">删除</button>
        @endcan
    </div>
@endcanany

{{-- 自定义权限指令 --}}
@permission('admin.view')
    <a href="{{ route('lpadmin.admin.show', $admin->id) }}">查看详情</a>
@endpermission
```

### 4. 前端JavaScript权限控制
```javascript
// 全局权限检查函数
window.LPadmin.hasPermission = function(permission) {
    var permissions = window.LPadmin.permissions || [];
    return permissions.includes('*') || permissions.includes(permission);
};

// 使用示例
if (LPadmin.hasPermission('admin.create')) {
    // 显示新增按钮
    $('.btn-add').show();
} else {
    $('.btn-add').hide();
}

// 表格操作列权限控制
function getTableActions(row) {
    var actions = [];
    
    if (LPadmin.hasPermission('admin.edit')) {
        actions.push('<button class="layui-btn layui-btn-xs" onclick="edit(' + row.id + ')">编辑</button>');
    }
    
    if (LPadmin.hasPermission('admin.delete')) {
        actions.push('<button class="layui-btn layui-btn-xs layui-btn-danger" onclick="del(' + row.id + ')">删除</button>');
    }
    
    return actions.join(' ');
}
```

## 🔧 权限配置

### 1. 权限规则定义
```php
// database/seeders/RuleSeeder.php
class RuleSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            // 系统管理目录
            [
                'title' => '系统管理',
                'name' => 'system',
                'type' => 0,
                'icon' => 'layui-icon-set',
                'sort' => 1000,
                'children' => [
                    // 管理员管理菜单
                    [
                        'title' => '管理员管理',
                        'name' => 'admin.index',
                        'type' => 1,
                        'icon' => 'layui-icon-username',
                        'href' => '/lpadmin/admin',
                        'sort' => 900,
                        'children' => [
                            ['title' => '查看管理员', 'name' => 'admin.view', 'type' => 2],
                            ['title' => '新增管理员', 'name' => 'admin.create', 'type' => 2],
                            ['title' => '编辑管理员', 'name' => 'admin.edit', 'type' => 2],
                            ['title' => '删除管理员', 'name' => 'admin.delete', 'type' => 2],
                        ]
                    ],
                    // 角色管理菜单
                    [
                        'title' => '角色管理',
                        'name' => 'role.index',
                        'type' => 1,
                        'icon' => 'layui-icon-group',
                        'href' => '/lpadmin/role',
                        'sort' => 800,
                        'children' => [
                            ['title' => '查看角色', 'name' => 'role.view', 'type' => 2],
                            ['title' => '新增角色', 'name' => 'role.create', 'type' => 2],
                            ['title' => '编辑角色', 'name' => 'role.edit', 'type' => 2],
                            ['title' => '删除角色', 'name' => 'role.delete', 'type' => 2],
                            ['title' => '分配权限', 'name' => 'role.permission', 'type' => 2],
                        ]
                    ]
                ]
            ]
        ];

        $this->createRules($rules);
    }

    protected function createRules($rules, $pid = 0)
    {
        foreach ($rules as $rule) {
            $children = $rule['children'] ?? [];
            unset($rule['children']);
            
            $rule['pid'] = $pid;
            $model = Rule::create($rule);
            
            if (!empty($children)) {
                $this->createRules($children, $model->id);
            }
        }
    }
}
```

### 2. 默认角色配置
```php
// database/seeders/RoleSeeder.php
class RoleSeeder extends Seeder
{
    public function run()
    {
        // 超级管理员角色
        $superAdmin = Role::create([
            'name' => '超级管理员',
            'description' => '拥有系统所有权限',
            'level' => 1,
            'sort' => 1000
        ]);

        // 系统管理员角色
        $systemAdmin = Role::create([
            'name' => '系统管理员',
            'description' => '系统管理相关权限',
            'pid' => $superAdmin->id,
            'level' => 2,
            'sort' => 900
        ]);

        // 分配权限
        $systemRules = Rule::whereIn('name', [
            'admin.index', 'admin.view', 'admin.create', 'admin.edit',
            'role.index', 'role.view', 'role.create', 'role.edit'
        ])->pluck('id');

        $systemAdmin->rules()->attach($systemRules);
    }
}
```

## 🎯 权限最佳实践

### 1. 权限命名规范
```php
// 推荐的权限命名格式：{模块}.{操作}
'admin.index'    // 管理员列表
'admin.view'     // 查看管理员
'admin.create'   // 新增管理员
'admin.edit'     // 编辑管理员
'admin.delete'   // 删除管理员

'user.index'     // 用户列表
'user.export'    // 导出用户
'user.import'    // 导入用户

'system.config'  // 系统配置
'system.log'     // 系统日志
```

### 2. 角色设计原则
- **最小权限原则**: 只分配必要的权限
- **职责分离**: 不同职责使用不同角色
- **层级管理**: 合理设计角色层级
- **权限继承**: 充分利用继承机制

### 3. 权限验证策略
```php
// 在控制器构造函数中统一设置权限
public function __construct()
{
    $this->middleware('auth:lpadmin');
    $this->middleware('lpadmin.permission:admin.index')->only('index');
    $this->middleware('lpadmin.permission:admin.create')->only(['create', 'store']);
    $this->middleware('lpadmin.permission:admin.edit')->only(['edit', 'update']);
    $this->middleware('lpadmin.permission:admin.delete')->only('destroy');
}

// 在方法中进行细粒度权限控制
public function update(Request $request, $id)
{
    $admin = Admin::findOrFail($id);
    
    // 检查是否可以编辑该管理员
    if (!$this->canEditAdmin($admin)) {
        abort(403, '无权编辑该管理员');
    }
    
    // 执行更新逻辑
}

protected function canEditAdmin(Admin $admin): bool
{
    $currentAdmin = auth('lpadmin')->user();
    
    // 不能编辑自己
    if ($currentAdmin->id === $admin->id) {
        return false;
    }
    
    // 不能编辑更高级别的管理员
    if ($admin->level <= $currentAdmin->level) {
        return false;
    }
    
    return true;
}
```

## 🔍 权限调试

### 1. 权限检查工具
```php
// 创建权限检查命令
php artisan make:command LPadmin\\CheckPermission

class CheckPermission extends Command
{
    protected $signature = 'lpadmin:check-permission {admin} {permission}';
    protected $description = '检查管理员权限';

    public function handle()
    {
        $adminId = $this->argument('admin');
        $permission = $this->argument('permission');
        
        $admin = Admin::find($adminId);
        if (!$admin) {
            $this->error("管理员不存在: {$adminId}");
            return;
        }
        
        $hasPermission = $admin->hasPermission($permission);
        
        $this->info("管理员: {$admin->username}");
        $this->info("权限: {$permission}");
        $this->info("结果: " . ($hasPermission ? '有权限' : '无权限'));
        
        if (!$hasPermission) {
            $this->info("用户权限列表:");
            foreach ($admin->getPermissions() as $perm) {
                $this->line("  - {$perm}");
            }
        }
    }
}
```

### 2. 权限日志记录
```php
// 在权限验证中间件中记录权限检查日志
class LPadminPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $admin = auth('lpadmin')->user();
        
        if ($permission && !$admin->hasPermission($permission)) {
            // 记录权限拒绝日志
            Log::warning('权限验证失败', [
                'admin_id' => $admin->id,
                'admin_name' => $admin->username,
                'permission' => $permission,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            abort(403, '权限不足');
        }
        
        return $next($request);
    }
}
```

---

**相关文档**:
- [数据库设计](database-design.md)
- [开发指南](../DEVELOPMENT.md)
- [API文档](../API.md)
