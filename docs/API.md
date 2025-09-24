# LPadmin API 文档

本文档详细介绍LPadmin后台管理系统的API接口规范和使用方法。

## 📋 基础信息

- **基础URL**: `http://lpadmin.a/lpadmin/api`
- **认证方式**: Session认证 + CSRF Token
- **数据格式**: JSON
- **字符编码**: UTF-8
- **API版本**: v1.0

## 🔐 认证机制

### 1. Session认证
LPadmin使用Laravel的Session认证机制，需要先通过登录接口获取认证状态。

### 2. CSRF保护
所有POST、PUT、DELETE请求都需要包含CSRF Token：

```javascript
// 在页面中获取CSRF Token
var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// 在Ajax请求中包含Token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': token
    }
});
```

## 📊 通用响应格式

### 成功响应
```json
{
    "code": 200,
    "message": "success",
    "data": {
        // 具体数据内容
    },
    "timestamp": 1640995200
}
```

### 分页响应
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "current_page": 1,
        "data": [
            // 数据列表
        ],
        "first_page_url": "http://example.com/api/users?page=1",
        "from": 1,
        "last_page": 10,
        "last_page_url": "http://example.com/api/users?page=10",
        "next_page_url": "http://example.com/api/users?page=2",
        "path": "http://example.com/api/users",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 150
    },
    "timestamp": 1640995200
}
```

### 错误响应
```json
{
    "code": 400,
    "message": "请求参数错误",
    "errors": {
        "username": ["用户名不能为空"],
        "email": ["邮箱格式不正确"]
    },
    "timestamp": 1640995200
}
```

## 🚨 错误码说明

| 错误码 | 说明 | 描述 |
|--------|------|------|
| 200 | 成功 | 请求处理成功 |
| 201 | 创建成功 | 资源创建成功 |
| 400 | 请求错误 | 请求参数错误或格式不正确 |
| 401 | 未认证 | 用户未登录或认证失效 |
| 403 | 权限不足 | 用户没有执行该操作的权限 |
| 404 | 资源不存在 | 请求的资源不存在 |
| 422 | 验证失败 | 表单验证失败 |
| 429 | 请求过多 | 超出API调用频率限制 |
| 500 | 服务器错误 | 服务器内部错误 |

## 🔑 认证接口

### 登录
**POST** `/lpadmin/login`

#### 请求参数
```json
{
    "username": "admin",
    "password": "password",
    "captcha": "1234",
    "remember": true
}
```

#### 响应示例
```json
{
    "code": 200,
    "message": "登录成功",
    "data": {
        "admin": {
            "id": 1,
            "username": "admin",
            "nickname": "系统管理员",
            "email": "admin@example.com",
            "avatar": "/lpadmin/images/avatar.png"
        },
        "redirect_url": "/lpadmin/dashboard"
    }
}
```

### 退出登录
**POST** `/lpadmin/logout`

#### 响应示例
```json
{
    "code": 200,
    "message": "退出成功",
    "data": {
        "redirect_url": "/lpadmin/login"
    }
}
```

### 获取用户信息
**GET** `/lpadmin/api/user-info`

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "id": 1,
        "username": "admin",
        "nickname": "系统管理员",
        "email": "admin@example.com",
        "avatar": "/lpadmin/images/avatar.png",
        "roles": [
            {
                "id": 1,
                "name": "超级管理员"
            }
        ],
        "permissions": ["*"]
    }
}
```

### 验证码
**GET** `/lpadmin/captcha/{type}`

参数说明：
- `type`: 验证码类型，如 `login`、`register` 等

返回图片流，直接显示验证码图片。

## 📋 菜单接口

### 获取菜单树
**GET** `/lpadmin/api/menu`

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": [
        {
            "id": 1,
            "title": "系统管理",
            "icon": "layui-icon-set",
            "href": "",
            "type": 0,
            "children": [
                {
                    "id": 2,
                    "title": "管理员管理",
                    "icon": "layui-icon-username",
                    "href": "/lpadmin/admin",
                    "type": 1
                }
            ]
        }
    ]
}
```

## 👥 管理员管理接口

### 获取管理员列表
**GET** `/lpadmin/api/admin`

#### 请求参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码，默认1 |
| per_page | int | 否 | 每页数量，默认15 |
| username | string | 否 | 用户名搜索 |
| nickname | string | 否 | 昵称搜索 |
| status | int | 否 | 状态筛选 |

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "username": "admin",
                "nickname": "系统管理员",
                "email": "admin@example.com",
                "mobile": "13800138000",
                "avatar": "/lpadmin/images/avatar.png",
                "status": 1,
                "login_at": "2024-01-01 12:00:00",
                "created_at": "2024-01-01 00:00:00",
                "roles": [
                    {
                        "id": 1,
                        "name": "超级管理员"
                    }
                ]
            }
        ],
        "per_page": 15,
        "total": 1
    }
}
```

### 创建管理员
**POST** `/lpadmin/api/admin`

#### 请求参数
```json
{
    "username": "newadmin",
    "nickname": "新管理员",
    "password": "password123",
    "password_confirmation": "password123",
    "email": "newadmin@example.com",
    "mobile": "13800138001",
    "role_ids": [2, 3],
    "status": 1
}
```

#### 响应示例
```json
{
    "code": 201,
    "message": "创建成功",
    "data": {
        "id": 2,
        "username": "newadmin",
        "nickname": "新管理员",
        "email": "newadmin@example.com",
        "mobile": "13800138001",
        "status": 1,
        "created_at": "2024-01-01 12:00:00"
    }
}
```

### 获取管理员详情
**GET** `/lpadmin/api/admin/{id}`

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "id": 1,
        "username": "admin",
        "nickname": "系统管理员",
        "email": "admin@example.com",
        "mobile": "13800138000",
        "avatar": "/lpadmin/images/avatar.png",
        "status": 1,
        "login_at": "2024-01-01 12:00:00",
        "created_at": "2024-01-01 00:00:00",
        "roles": [
            {
                "id": 1,
                "name": "超级管理员",
                "rules": ["*"]
            }
        ]
    }
}
```

### 更新管理员
**PUT** `/lpadmin/api/admin/{id}`

#### 请求参数
```json
{
    "nickname": "更新的昵称",
    "email": "updated@example.com",
    "mobile": "13800138002",
    "role_ids": [2],
    "status": 1
}
```

### 删除管理员
**DELETE** `/lpadmin/api/admin/{id}`

#### 响应示例
```json
{
    "code": 200,
    "message": "删除成功"
}
```

### 切换管理员状态
**POST** `/lpadmin/api/admin/{id}/toggle-status`

#### 响应示例
```json
{
    "code": 200,
    "message": "状态更新成功",
    "data": {
        "status": 0
    }
}
```

## 🎭 角色管理接口

### 获取角色列表
**GET** `/lpadmin/api/role`

#### 请求参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码 |
| per_page | int | 否 | 每页数量 |
| name | string | 否 | 角色名搜索 |

### 创建角色
**POST** `/lpadmin/api/role`

#### 请求参数
```json
{
    "name": "编辑员",
    "description": "内容编辑员角色",
    "rule_ids": [1, 2, 3],
    "pid": 0
}
```

### 获取角色权限
**GET** `/lpadmin/api/role/{id}/permissions`

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "role": {
            "id": 2,
            "name": "编辑员"
        },
        "permissions": [
            {
                "id": 1,
                "title": "内容管理",
                "key": "content",
                "type": 0
            }
        ]
    }
}
```

## 📋 权限规则接口

### 获取权限树
**GET** `/lpadmin/api/rule/tree`

#### 请求参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| type | string | 否 | 权限类型，如 "0,1,2" |

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": [
        {
            "id": 1,
            "title": "系统管理",
            "key": "system",
            "icon": "layui-icon-set",
            "type": 0,
            "weight": 1000,
            "children": [
                {
                    "id": 2,
                    "title": "管理员管理",
                    "key": "admin",
                    "href": "/lpadmin/admin",
                    "type": 1,
                    "weight": 900
                }
            ]
        }
    ]
}
```

## 📁 文件上传接口

### 上传文件
**POST** `/lpadmin/api/upload`

#### 请求参数
- `file`: 文件对象
- `category`: 文件分类（可选）

#### 响应示例
```json
{
    "code": 200,
    "message": "上传成功",
    "data": {
        "id": 1,
        "name": "example.jpg",
        "url": "/storage/lpadmin/uploads/2024/01/01/example.jpg",
        "size": 102400,
        "mime_type": "image/jpeg",
        "category": "image"
    }
}
```

### 获取上传文件列表
**GET** `/lpadmin/api/upload`

#### 请求参数
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码 |
| per_page | int | 否 | 每页数量 |
| category | string | 否 | 文件分类 |
| name | string | 否 | 文件名搜索 |

## 📊 统计接口

### 获取仪表盘数据
**GET** `/lpadmin/api/dashboard/stats`

#### 响应示例
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "admin_count": 10,
        "user_count": 1000,
        "upload_count": 500,
        "login_count_today": 50,
        "recent_logins": [
            {
                "admin": "admin",
                "login_at": "2024-01-01 12:00:00",
                "ip": "127.0.0.1"
            }
        ]
    }
}
```

## 🔧 系统配置接口

### 获取系统配置
**GET** `/lpadmin/api/config`

### 更新系统配置
**POST** `/lpadmin/api/config`

#### 请求参数
```json
{
    "system_name": "我的管理系统",
    "system_logo": "/lpadmin/images/logo.png",
    "system_copyright": "© 2024 我的公司",
    "upload_max_size": 10240,
    "captcha_enabled": true
}
```

---

**更多接口**: 查看各功能模块的详细API文档
- [用户管理接口](api/user-management.md)
- [字典管理接口](api/dict-management.md)
- [操作日志接口](api/operation-log.md)
