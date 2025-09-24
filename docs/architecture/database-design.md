# LPadmin 数据库设计文档

本文档详细介绍LPadmin后台管理系统的数据库结构设计和关联关系。

## 📊 数据库概览

### 基本信息
- **数据库引擎**: InnoDB
- **字符集**: utf8mb4
- **排序规则**: utf8mb4_unicode_ci
- **表前缀**: lp_ (可配置)

### 设计原则
- 遵循第三范式设计
- 使用软删除而非物理删除
- 统一的时间戳字段
- 合理的索引设计
- 支持数据迁移和版本控制

## 🗂️ 核心数据表

### 1. 管理员表 (lp_admins)

管理后台登录用户的基本信息。

```sql
CREATE TABLE `lp_admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `nickname` varchar(40) NOT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `avatar` varchar(255) DEFAULT '/lpadmin/images/avatar.png' COMMENT '头像',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) DEFAULT NULL COMMENT '手机号',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '邮箱验证时间',
  `remember_token` varchar(100) DEFAULT NULL COMMENT '记住登录Token',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_admins_username_unique` (`username`),
  UNIQUE KEY `lp_admins_email_unique` (`email`),
  KEY `lp_admins_status_index` (`status`),
  KEY `lp_admins_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';
```

**字段说明**:
- `username`: 登录用户名，唯一
- `nickname`: 显示昵称
- `password`: 加密后的密码
- `avatar`: 头像路径
- `status`: 账户状态，支持启用/禁用
- `login_at`: 记录最后登录时间，用于统计
- `login_ip`: 记录登录IP，用于安全审计

### 2. 角色表 (lp_roles)

定义系统中的角色信息，支持层级结构。

```sql
CREATE TABLE `lp_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(80) NOT NULL COMMENT '角色名称',
  `description` text COMMENT '角色描述',
  `pid` bigint(20) unsigned DEFAULT '0' COMMENT '父级角色ID',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '角色层级',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `lp_roles_pid_index` (`pid`),
  KEY `lp_roles_status_index` (`status`),
  KEY `lp_roles_sort_index` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';
```

**字段说明**:
- `name`: 角色名称，如"超级管理员"、"编辑员"
- `description`: 角色描述信息
- `pid`: 父级角色ID，支持角色层级
- `level`: 角色层级深度
- `sort`: 排序权重，数值越大越靠前

### 3. 权限规则表 (lp_rules)

定义系统的权限规则，包括菜单和操作权限。

```sql
CREATE TABLE `lp_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) NOT NULL COMMENT '规则标题',
  `name` varchar(255) NOT NULL COMMENT '规则标识',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `pid` bigint(20) unsigned DEFAULT '0' COMMENT '父级规则ID',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型：0=目录，1=菜单，2=权限',
  `href` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `component` varchar(255) DEFAULT NULL COMMENT '组件路径',
  `method` varchar(20) DEFAULT 'GET' COMMENT 'HTTP方法',
  `condition` text COMMENT '权限条件',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `is_menu` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示在菜单：0=否，1=是',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_rules_name_unique` (`name`),
  KEY `lp_rules_pid_index` (`pid`),
  KEY `lp_rules_type_index` (`type`),
  KEY `lp_rules_status_index` (`status`),
  KEY `lp_rules_sort_index` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限规则表';
```

**字段说明**:
- `title`: 规则显示标题
- `name`: 规则唯一标识，用于权限验证
- `type`: 规则类型，0=目录，1=菜单，2=权限
- `href`: 菜单链接地址
- `component`: 前端组件路径
- `method`: HTTP请求方法
- `condition`: 额外的权限条件
- `is_menu`: 是否在菜单中显示

### 4. 管理员角色关联表 (lp_admin_roles)

管理员与角色的多对多关联表。

```sql
CREATE TABLE `lp_admin_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `admin_id` bigint(20) unsigned NOT NULL COMMENT '管理员ID',
  `role_id` bigint(20) unsigned NOT NULL COMMENT '角色ID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_admin_roles_admin_role_unique` (`admin_id`,`role_id`),
  KEY `lp_admin_roles_admin_id_foreign` (`admin_id`),
  KEY `lp_admin_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `lp_admin_roles_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `lp_admins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lp_admin_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `lp_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员角色关联表';
```

### 5. 角色权限关联表 (lp_role_rules)

角色与权限规则的多对多关联表。

```sql
CREATE TABLE `lp_role_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `role_id` bigint(20) unsigned NOT NULL COMMENT '角色ID',
  `rule_id` bigint(20) unsigned NOT NULL COMMENT '规则ID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_role_rules_role_rule_unique` (`role_id`,`rule_id`),
  KEY `lp_role_rules_role_id_foreign` (`role_id`),
  KEY `lp_role_rules_rule_id_foreign` (`rule_id`),
  CONSTRAINT `lp_role_rules_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `lp_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lp_role_rules_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `lp_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色权限关联表';
```

## 👥 用户相关表

### 6. 用户表 (lp_users)

前台用户信息表。

```sql
CREATE TABLE `lp_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `nickname` varchar(40) NOT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `email` varchar(128) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) DEFAULT NULL COMMENT '手机号',
  `gender` tinyint(4) DEFAULT '0' COMMENT '性别：0=未知，1=男，2=女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户等级',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `register_ip` varchar(45) DEFAULT NULL COMMENT '注册IP',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=正常',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '邮箱验证时间',
  `mobile_verified_at` timestamp NULL DEFAULT NULL COMMENT '手机验证时间',
  `remember_token` varchar(100) DEFAULT NULL COMMENT '记住登录Token',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_users_username_unique` (`username`),
  UNIQUE KEY `lp_users_email_unique` (`email`),
  UNIQUE KEY `lp_users_mobile_unique` (`mobile`),
  KEY `lp_users_status_index` (`status`),
  KEY `lp_users_level_index` (`level`),
  KEY `lp_users_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';
```

## 📁 系统功能表

### 7. 文件上传表 (lp_uploads)

管理系统中上传的文件信息。

```sql
CREATE TABLE `lp_uploads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NOT NULL COMMENT '文件名',
  `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
  `path` varchar(500) NOT NULL COMMENT '文件路径',
  `url` varchar(500) NOT NULL COMMENT '访问URL',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME类型',
  `size` bigint(20) NOT NULL COMMENT '文件大小(字节)',
  `extension` varchar(20) NOT NULL COMMENT '文件扩展名',
  `disk` varchar(50) NOT NULL DEFAULT 'local' COMMENT '存储磁盘',
  `category` varchar(50) DEFAULT NULL COMMENT '文件分类',
  `admin_id` bigint(20) unsigned DEFAULT NULL COMMENT '上传者ID',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联用户ID',
  `width` int(11) DEFAULT NULL COMMENT '图片宽度',
  `height` int(11) DEFAULT NULL COMMENT '图片高度',
  `md5` varchar(32) DEFAULT NULL COMMENT '文件MD5值',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=正常',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `lp_uploads_admin_id_index` (`admin_id`),
  KEY `lp_uploads_user_id_index` (`user_id`),
  KEY `lp_uploads_category_index` (`category`),
  KEY `lp_uploads_extension_index` (`extension`),
  KEY `lp_uploads_md5_index` (`md5`),
  KEY `lp_uploads_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件上传表';
```

### 8. 系统配置表 (lp_options)

存储系统的各种配置信息。

```sql
CREATE TABLE `lp_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(128) NOT NULL COMMENT '配置名称',
  `value` longtext COMMENT '配置值',
  `description` varchar(255) DEFAULT NULL COMMENT '配置描述',
  `type` varchar(20) NOT NULL DEFAULT 'string' COMMENT '数据类型',
  `group` varchar(50) DEFAULT 'system' COMMENT '配置分组',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序权重',
  `is_system` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否系统配置：0=否，1=是',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lp_options_name_unique` (`name`),
  KEY `lp_options_group_index` (`group`),
  KEY `lp_options_sort_index` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';
```

### 9. 操作日志表 (lp_operation_logs)

记录管理员的操作日志。

```sql
CREATE TABLE `lp_operation_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `admin_id` bigint(20) unsigned DEFAULT NULL COMMENT '操作者ID',
  `admin_name` varchar(50) DEFAULT NULL COMMENT '操作者用户名',
  `module` varchar(50) NOT NULL COMMENT '操作模块',
  `action` varchar(50) NOT NULL COMMENT '操作动作',
  `description` varchar(255) DEFAULT NULL COMMENT '操作描述',
  `url` varchar(500) DEFAULT NULL COMMENT '请求URL',
  `method` varchar(10) DEFAULT NULL COMMENT '请求方法',
  `ip` varchar(45) DEFAULT NULL COMMENT '操作IP',
  `user_agent` text COMMENT '用户代理',
  `request_data` json DEFAULT NULL COMMENT '请求数据',
  `response_data` json DEFAULT NULL COMMENT '响应数据',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '操作状态：0=失败，1=成功',
  `execution_time` int(11) DEFAULT NULL COMMENT '执行时间(毫秒)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `lp_operation_logs_admin_id_index` (`admin_id`),
  KEY `lp_operation_logs_module_index` (`module`),
  KEY `lp_operation_logs_action_index` (`action`),
  KEY `lp_operation_logs_ip_index` (`ip`),
  KEY `lp_operation_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';
```

## 🔗 关联关系图

```
lp_admins (管理员)
    ├── 1:N → lp_operation_logs (操作日志)
    ├── 1:N → lp_uploads (上传文件)
    └── M:N → lp_roles (角色) 
                └── M:N → lp_rules (权限规则)

lp_users (用户)
    └── 1:N → lp_uploads (上传文件)

lp_options (系统配置)
    └── 独立表，无外键关联
```

## 📈 索引设计

### 主要索引
1. **主键索引**: 所有表的`id`字段
2. **唯一索引**: 用户名、邮箱等唯一字段
3. **普通索引**: 状态、时间、外键等查询字段
4. **复合索引**: 多字段组合查询

### 索引优化建议
- 根据查询频率调整索引
- 定期分析慢查询日志
- 避免过多索引影响写入性能
- 使用覆盖索引提高查询效率

## 🔄 数据迁移

### Laravel迁移文件
```php
// 示例迁移文件结构
database/migrations/
├── 2024_01_01_000001_create_lp_admins_table.php
├── 2024_01_01_000002_create_lp_roles_table.php
├── 2024_01_01_000003_create_lp_rules_table.php
├── 2024_01_01_000004_create_lp_admin_roles_table.php
├── 2024_01_01_000005_create_lp_role_rules_table.php
├── 2024_01_01_000006_create_lp_users_table.php
├── 2024_01_01_000007_create_lp_uploads_table.php
├── 2024_01_01_000008_create_lp_options_table.php
└── 2024_01_01_000009_create_lp_operation_logs_table.php
```

### 数据填充
```php
// 初始数据填充
database/seeders/
├── LPadminSeeder.php          // 主要填充器
├── AdminSeeder.php            // 管理员数据
├── RoleSeeder.php             // 角色数据
├── RuleSeeder.php             // 权限规则数据
└── OptionSeeder.php           // 系统配置数据
```

## 🛡️ 数据安全

### 安全措施
1. **密码加密**: 使用Laravel的Hash门面
2. **软删除**: 重要数据使用软删除
3. **外键约束**: 保证数据完整性
4. **字段验证**: 模型层数据验证
5. **SQL注入防护**: 使用Eloquent ORM

### 备份策略
- 定期全量备份
- 增量备份重要表
- 异地备份存储
- 备份数据验证

---

**相关文档**: 
- [权限系统设计](permission-system.md)
- [API接口文档](../API.md)
- [开发指南](../DEVELOPMENT.md)
