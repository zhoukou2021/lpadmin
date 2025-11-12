# Webman Admin 迁移到 Laravel 指南

## 概述
本指南将帮助您将 Webman Admin 插件迁移到独立的 Laravel 项目中运行。

## 步骤 1：创建 Laravel 项目

```bash
# 创建新的 Laravel 项目
composer create-project laravel/laravel webman-admin-laravel
cd webman-admin-laravel

# 安装必要的依赖包
composer require intervention/image
composer require guzzlehttp/guzzle
composer require mews/captcha
```

## 步骤 2：配置数据库

编辑 `.env` 文件：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webman_admin
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 步骤 3：创建数据库迁移文件

```bash
php artisan make:migration create_admin_tables
```

## 步骤 4：目录结构规划

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/           # 管理后台控制器
│   ├── Middleware/
│   │   └── AdminAuth.php    # 管理员认证中间件
│   └── Requests/
│       └── Admin/           # 表单验证请求
├── Models/
│   └── Admin/               # 管理后台模型
├── Services/
│   └── Admin/               # 业务逻辑服务
resources/
├── views/
│   └── admin/               # 管理后台视图
└── assets/
    └── admin/               # 前端资源
routes/
└── admin.php                # 管理后台路由
```

## 步骤 5：核心组件改造清单

### 5.1 模型层改造
- [ ] Admin.php - 管理员模型
- [ ] Role.php - 角色模型  
- [ ] Rule.php - 权限规则模型
- [ ] User.php - 用户模型
- [ ] Option.php - 系统配置模型

### 5.2 控制器层改造
- [ ] IndexController.php - 首页控制器
- [ ] AccountController.php - 账户管理
- [ ] AdminController.php - 管理员管理
- [ ] RoleController.php - 角色管理
- [ ] UserController.php - 用户管理
- [ ] UploadController.php - 文件上传

### 5.3 中间件改造
- [ ] AdminAuth.php - 管理员认证
- [ ] PermissionCheck.php - 权限检查

### 5.4 路由配置
- [ ] 管理后台路由组
- [ ] API 路由配置
- [ ] 中间件绑定

## 步骤 6：关键代码改造示例

### 原 Webman 控制器
```php
use support\Request;
use support\Response;

class AdminController extends Base
{
    public function index(Request $request): Response
    {
        return json(['code' => 0, 'data' => []]);
    }
}
```

### 改造后 Laravel 控制器
```php
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['code' => 0, 'data' => []]);
    }
}
```

## 步骤 7：配置文件迁移

### 创建管理后台配置
```php
// config/admin.php
return [
    'version' => '0.6.33',
    'debug' => env('APP_DEBUG', false),
    'route_prefix' => 'admin',
    'upload_path' => 'uploads/admin',
    'default_avatar' => '/images/default-avatar.png',
];
```

## 步骤 8：前端资源处理

1. 复制静态资源到 `public/admin/` 目录
2. 更新 HTML 模板中的资源路径
3. 配置 Laravel Mix 或 Vite 进行资源编译

## 步骤 9：功能组件适配

### 验证码组件
```php
// 使用 mews/captcha 替代 webman/captcha
use Mews\Captcha\Facades\Captcha;

public function captcha()
{
    return Captcha::create('default', true);
}
```

### 文件上传
```php
// 使用 Laravel 的文件上传处理
public function upload(Request $request)
{
    $file = $request->file('file');
    $path = $file->store('uploads/admin', 'public');
    return response()->json(['url' => Storage::url($path)]);
}
```

## 步骤 10：数据库初始化

1. 运行迁移文件创建表结构
2. 导入初始数据（管理员账户、权限配置等）
3. 配置数据库连接和模型关联

## 注意事项

1. **命名空间调整**：所有类的命名空间需要从 `plugin\admin\app` 改为 Laravel 标准命名空间
2. **依赖注入**：Laravel 的依赖注入方式与 Webman 不同，需要相应调整
3. **配置管理**：使用 Laravel 的配置系统替代 Webman 的配置方式
4. **中间件注册**：在 `app/Http/Kernel.php` 中注册自定义中间件
5. **路由缓存**：生产环境记得清除和重新缓存路由

## 测试验证

1. 访问管理后台登录页面
2. 测试管理员登录功能
3. 验证权限控制是否正常
4. 测试 CRUD 操作功能
5. 检查文件上传功能

完成以上步骤后，原 Webman Admin 插件就可以在 Laravel 框架中独立运行了。
