# LPadmin 安装指南

本文档将指导您完成LPadmin后台管理系统的安装和配置。

## 📋 环境要求

### 基础环境
- **PHP**: >= 8.1 (推荐 8.2+)
- **Laravel**: >= 10.0
- **数据库**: MySQL >= 8.0 或 MariaDB >= 10.6
- **Web服务器**: Nginx >= 1.18 或 Apache >= 2.4
- **Composer**: >= 2.0
- **Node.js**: >= 16.0 (用于前端资源编译)
- **NPM**: >= 8.0

### PHP扩展要求
```bash
必需扩展
php-fpm
php-mysql
php-redis (可选，推荐)
php-gd
php-curl
php-zip
php-xml
php-mbstring
php-json
php-openssl
php-tokenizer
php-fileinfo
php-bcmath
```

### 系统要求
- **内存**: 最小512MB，推荐2GB+
- **存储**: 最小1GB可用空间
- **网络**: 需要访问外网下载依赖包

## 🚀 安装步骤

### 1. 获取源码

#### 方式一：Git克隆（推荐）
```bash
git clone https://gitee.com/xw54/lpadmin.git
cd lpadmin
```

#### 方式二：下载压缩包
```bash
wget https://gitee.com/xw54/lpadmin/archive/main.zip
unzip main.zip
cd lpadmin-main
```

### 2. 安装PHP依赖

```bash
 安装Composer依赖
composer install --optimize-autoloader --no-dev

 如果是开发环境，使用以下命令
composer install
```

### 3. 环境配置

#### 复制环境配置文件
```bash
cp .env.example .env
```

#### 生成应用密钥
```bash
php artisan key:generate
```

#### 编辑环境配置
编辑 `.env` 文件，配置以下关键参数：

```env
 应用配置
APP_NAME="LPadmin管理系统"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://lpadmin.a

 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lpadmin_a
DB_USERNAME=lpadmin_a
DB_PASSWORD=lpadmin_a
DB_PREFIX=lp_

 LPadmin配置
LPADMIN_ROUTE_PREFIX=lpadmin
LPADMIN_SYSTEM_NAME="LPadmin管理系统"
LPADMIN_CAPTCHA_ENABLED=true
LPADMIN_LOG_ENABLED=true

 缓存配置（可选）
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

 Redis配置（如果使用）
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

 邮件配置（可选）
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### 4. 数据库配置

#### env中配置好数据库后，运行数据库迁移
```bash
 运行迁移文件
php artisan migrate --seed

```

### 5. 发布资源文件

```bash
 发布配置文件
php artisan vendor:publish --tag=lpadmin-config

 发布静态资源
php artisan vendor:publish --tag=lpadmin-assets

 发布视图文件（可选，用于自定义）
php artisan vendor:publish --tag=lpadmin-views
```

### 7. 设置文件权限

```bash
 设置存储目录权限
chmod -R 775 storage
chmod -R 775 bootstrap/cache

 设置所有者（假设web服务器用户为www-data）
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
chown -R www-data:www-data public
```


## ✅ 安装验证

### 1. 检查系统状态
```bash
 检查Laravel状态
php artisan about

 检查数据库连接
php artisan migrate:status

 检查队列状态（如果配置了队列）
php artisan queue:work --once
```

### 2. 访问系统
1. 打开浏览器访问：`http://lpadmin.a/lpadmin`，账号密码：`admin/123456`
2. 使用创建的管理员账户登录
3. 检查各功能模块是否正常工作

### 3. 功能验证
- [ ] 管理员登录/退出
- [ ] 仪表盘数据显示
- [ ] 权限管理功能
- [ ] 用户管理功能
- [ ] 文件上传功能
- [ ] 系统配置功能

## 🚨 常见问题

### 1. 权限问题
```bash
 如果遇到权限错误
sudo chown -R www-data:www-data /path/to/lpadmin
sudo chmod -R 755 /path/to/lpadmin
sudo chmod -R 775 /path/to/lpadmin/storage
sudo chmod -R 775 /path/to/lpadmin/bootstrap/cache
```

### 2. 数据库连接失败
- 检查数据库服务是否启动
- 验证数据库连接参数
- 确认数据库用户权限

### 3. 静态资源404
```bash
 重新发布静态资源
php artisan vendor:publish --tag=lpadmin-assets --force
```

### 4. 缓存问题
```bash
 清除所有缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📞 获取帮助

如果在安装过程中遇到问题，可以通过以下方式获取帮助：

- 查看 [故障排查文档](TROUBLESHOOTING.md)
- 提交 [GitHub Issue](https://gitee.com/xw54/lpadmin/issues)
- 发送邮件：jiu-men@qq.com

## 🎉 安装完成

恭喜！您已成功安装LPadmin后台管理系统。接下来可以：

1. 阅读 [开发指南](DEVELOPMENT.md) 了解系统架构
2. 查看 [API文档](API.md) 进行二次开发
3. 参考 [部署指南](DEPLOYMENT.md) 进行生产环境部署

---

**下一步**: [快速上手指南](QUICKSTART.md)
