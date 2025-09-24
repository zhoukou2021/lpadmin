# LPadmin 部署指南

本文档详细介绍LPadmin在生产环境中的部署方法和最佳实践。

## 🏗️ 部署架构

### 推荐架构
```
[负载均衡器] → [Web服务器] → [应用服务器] → [数据库服务器]
     ↓              ↓              ↓              ↓
   Nginx/ALB    Nginx/Apache   PHP-FPM/Laravel   MySQL/Redis
```

### 最小化部署
```
[单服务器]
    ├── Nginx (Web服务器)
    ├── PHP-FPM (应用服务器)
    ├── MySQL (数据库)
    └── Redis (缓存，可选)
```

## 🖥️ 服务器要求

### 硬件要求
| 配置项 | 最小要求 | 推荐配置 | 高负载配置 |
|--------|----------|----------|------------|
| CPU | 1核 | 2核+ | 4核+ |
| 内存 | 1GB | 4GB+ | 8GB+ |
| 存储 | 20GB | 50GB+ | 100GB+ |
| 带宽 | 1Mbps | 10Mbps+ | 100Mbps+ |

### 软件要求
- **操作系统**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **Web服务器**: Nginx 1.18+ / Apache 2.4+
- **PHP**: 8.1+ (推荐 8.2)
- **数据库**: MySQL 8.0+ / MariaDB 10.6+
- **缓存**: Redis 6.0+ (可选但推荐)

## 🚀 部署步骤

### 1. 服务器环境准备

#### Ubuntu/Debian系统
```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装基础软件
sudo apt install -y curl wget git unzip software-properties-common

# 安装PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis \
    php8.2-gd php8.2-curl php8.2-zip php8.2-xml php8.2-mbstring \
    php8.2-json php8.2-openssl php8.2-tokenizer php8.2-fileinfo \
    php8.2-bcmath php8.2-intl

# 安装Nginx
sudo apt install -y nginx

# 安装MySQL
sudo apt install -y mysql-server

# 安装Redis (可选)
sudo apt install -y redis-server

# 安装Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 安装Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

#### CentOS/RHEL系统
```bash
# 更新系统
sudo yum update -y

# 安装EPEL仓库
sudo yum install -y epel-release

# 安装Remi仓库
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# 启用PHP 8.2
sudo dnf module reset php -y
sudo dnf module enable php:remi-8.2 -y

# 安装PHP
sudo yum install -y php php-fpm php-mysql php-redis php-gd \
    php-curl php-zip php-xml php-mbstring php-json php-openssl \
    php-tokenizer php-fileinfo php-bcmath php-intl

# 安装Nginx
sudo yum install -y nginx

# 安装MySQL
sudo yum install -y mysql-server

# 安装Redis
sudo yum install -y redis

# 安装Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 安装Node.js
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo yum install -y nodejs
```

### 2. 数据库配置

#### MySQL配置
```bash
# 启动MySQL服务
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation

# 创建数据库和用户
sudo mysql -u root -p
```

```sql
-- 创建数据库
CREATE DATABASE lpadmin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户
CREATE USER 'lpadmin'@'localhost' IDENTIFIED BY 'your_secure_password';

-- 授权
GRANT ALL PRIVILEGES ON lpadmin.* TO 'lpadmin'@'localhost';
FLUSH PRIVILEGES;

-- 退出
EXIT;
```

#### MySQL优化配置
编辑 `/etc/mysql/mysql.conf.d/mysqld.cnf` 或 `/etc/my.cnf`：

```ini
[mysqld]
# 基础配置
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2

# 字符集配置
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# 查询缓存
query_cache_type = 1
query_cache_size = 128M

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 3. Redis配置（可选）

```bash
# 启动Redis服务
sudo systemctl start redis
sudo systemctl enable redis

# 配置Redis
sudo nano /etc/redis/redis.conf
```

```ini
# 绑定地址
bind 127.0.0.1

# 设置密码
requirepass your_redis_password

# 内存配置
maxmemory 512mb
maxmemory-policy allkeys-lru

# 持久化配置
save 900 1
save 300 10
save 60 10000
```

### 4. 应用部署

#### 创建部署目录
```bash
# 创建应用目录
sudo mkdir -p /var/www/lpadmin
sudo chown -R $USER:$USER /var/www/lpadmin

# 克隆代码
cd /var/www
git clone https://gitee.com/xw54/lpadmin.git
cd lpadmin
```

#### 安装依赖
```bash
# 安装PHP依赖
composer install --optimize-autoloader --no-dev

# 安装前端依赖
npm install

# 编译前端资源
npm run build
```

#### 环境配置
```bash
# 复制环境配置
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 编辑环境配置
nano .env
```

```env
# 应用配置
APP_NAME="LPadmin管理系统"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lpadmin
DB_USERNAME=lpadmin
DB_PASSWORD=your_secure_password
DB_PREFIX=lp_

# 缓存配置
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis配置
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# LPadmin配置
LPADMIN_ROUTE_PREFIX=lpadmin
LPADMIN_SYSTEM_NAME="LPadmin管理系统"
```

#### 数据库迁移
```bash
# 运行迁移
php artisan migrate --force

# 填充初始数据
php artisan db:seed --class=LPadminSeeder --force

# 发布资源
php artisan vendor:publish --tag=lpadmin-config --force
php artisan vendor:publish --tag=lpadmin-assets --force
```

#### 优化配置
```bash
# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 优化自动加载
composer dump-autoload --optimize

# 创建存储链接
php artisan storage:link
```

### 5. Web服务器配置

#### Nginx配置
创建 `/etc/nginx/sites-available/lpadmin`：

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/lpadmin/public;
    index index.php index.html;

    # 安全配置
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript 
               application/javascript application/xml+rss 
               application/json image/svg+xml;

    # 静态文件缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Laravel路由
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 隐藏敏感文件
    location ~ /\.(ht|git|env) {
        deny all;
    }

    # 禁止访问敏感目录
    location ~ ^/(storage|bootstrap|config|database|resources|routes|tests)/ {
        deny all;
    }

    # 日志配置
    access_log /var/log/nginx/lpadmin_access.log;
    error_log /var/log/nginx/lpadmin_error.log;
}
```

启用站点：
```bash
# 创建软链接
sudo ln -s /etc/nginx/sites-available/lpadmin /etc/nginx/sites-enabled/

# 测试配置
sudo nginx -t

# 重启Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

#### SSL证书配置（推荐）
```bash
# 安装Certbot
sudo apt install -y certbot python3-certbot-nginx

# 获取SSL证书
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# 自动续期
sudo crontab -e
# 添加以下行
0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. PHP-FPM优化

编辑 `/etc/php/8.2/fpm/pool.d/www.conf`：

```ini
[www]
user = www-data
group = www-data

listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

# 进程管理
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

# 性能优化
request_terminate_timeout = 300
rlimit_files = 65536
rlimit_core = 0

# 慢日志
slowlog = /var/log/php8.2-fpm-slow.log
request_slowlog_timeout = 10s
```

编辑 `/etc/php/8.2/fpm/php.ini`：

```ini
# 内存限制
memory_limit = 256M

# 执行时间
max_execution_time = 300
max_input_time = 300

# 文件上传
upload_max_filesize = 20M
post_max_size = 20M
max_file_uploads = 20

# 会话配置
session.gc_maxlifetime = 7200

# OPcache配置
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1
```

重启PHP-FPM：
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### 7. 文件权限设置

```bash
# 设置应用目录所有者
sudo chown -R www-data:www-data /var/www/lpadmin

# 设置目录权限
sudo find /var/www/lpadmin -type d -exec chmod 755 {} \;
sudo find /var/www/lpadmin -type f -exec chmod 644 {} \;

# 设置可写目录权限
sudo chmod -R 775 /var/www/lpadmin/storage
sudo chmod -R 775 /var/www/lpadmin/bootstrap/cache
sudo chmod -R 775 /var/www/lpadmin/public/storage

# 设置执行权限
sudo chmod +x /var/www/lpadmin/artisan
```

### 8. 队列和定时任务配置

#### Supervisor配置
```bash
# 安装Supervisor
sudo apt install -y supervisor

# 创建队列配置
sudo nano /etc/supervisor/conf.d/lpadmin-worker.conf
```

```ini
[program:lpadmin-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lpadmin/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lpadmin/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# 重新加载配置
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lpadmin-worker:*
```

#### 定时任务配置
```bash
# 编辑crontab
sudo crontab -e

# 添加Laravel调度任务
* * * * * cd /var/www/lpadmin && php artisan schedule:run >> /dev/null 2>&1
```

## 🔒 安全配置

### 1. 防火墙配置
```bash
# 安装UFW
sudo apt install -y ufw

# 配置防火墙规则
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# 启用防火墙
sudo ufw enable
```

### 2. 系统安全加固
```bash
# 禁用root远程登录
sudo nano /etc/ssh/sshd_config
# 设置 PermitRootLogin no

# 创建普通用户
sudo adduser deploy
sudo usermod -aG sudo deploy

# 配置SSH密钥认证
# 在本地生成密钥对，然后上传公钥到服务器
```

### 3. 应用安全配置
```bash
# 隐藏PHP版本信息
sudo nano /etc/php/8.2/fpm/php.ini
# 设置 expose_php = Off

# 隐藏Nginx版本信息
sudo nano /etc/nginx/nginx.conf
# 在http块中添加 server_tokens off;
```

## 📊 监控和日志

### 1. 日志配置
```bash
# 创建日志目录
sudo mkdir -p /var/log/lpadmin
sudo chown -R www-data:www-data /var/log/lpadmin

# 配置日志轮转
sudo nano /etc/logrotate.d/lpadmin
```

```
/var/www/lpadmin/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 2. 系统监控
```bash
# 安装htop
sudo apt install -y htop

# 安装iotop
sudo apt install -y iotop

# 安装netstat
sudo apt install -y net-tools
```

## 🚀 性能优化

### 1. 数据库优化
- 定期优化表结构
- 添加适当的索引
- 配置查询缓存
- 监控慢查询

### 2. 缓存优化
- 启用Redis缓存
- 配置OPcache
- 使用CDN加速静态资源
- 启用浏览器缓存

### 3. 服务器优化
- 调整PHP-FPM进程数
- 优化Nginx配置
- 启用Gzip压缩
- 配置HTTP/2

## 🔄 备份策略

### 1. 数据库备份
```bash
# 创建备份脚本
sudo nano /usr/local/bin/backup-lpadmin.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/lpadmin"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u lpadmin -p'your_password' lpadmin > $BACKUP_DIR/lpadmin_$DATE.sql

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/lpadmin/storage/app/lpadmin

# 删除7天前的备份
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

```bash
# 设置执行权限
sudo chmod +x /usr/local/bin/backup-lpadmin.sh

# 添加到定时任务
sudo crontab -e
# 添加：0 2 * * * /usr/local/bin/backup-lpadmin.sh
```

## 🆙 更新部署

### 1. 代码更新
```bash
# 进入应用目录
cd /var/www/lpadmin

# 备份当前版本
cp -r . ../lpadmin_backup_$(date +%Y%m%d)

# 拉取最新代码
git pull origin main

# 更新依赖
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 运行迁移
php artisan migrate --force

# 清除缓存
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 重启服务
sudo systemctl reload php8.2-fpm
sudo supervisorctl restart lpadmin-worker:*
```

### 2. 零停机部署
使用符号链接实现零停机部署：

```bash
# 部署脚本示例
#!/bin/bash
DEPLOY_DIR="/var/www/releases"
CURRENT_DIR="/var/www/lpadmin"
RELEASE_DIR="$DEPLOY_DIR/$(date +%Y%m%d_%H%M%S)"

# 创建新版本目录
mkdir -p $RELEASE_DIR
git clone https://gitee.com/xw54/lpadmin.git $RELEASE_DIR

# 安装依赖和配置
cd $RELEASE_DIR
composer install --optimize-autoloader --no-dev
npm install && npm run build
cp $CURRENT_DIR/.env .env
php artisan migrate --force

# 切换版本
ln -nfs $RELEASE_DIR $CURRENT_DIR

# 重启服务
sudo systemctl reload php8.2-fpm
```

---

**下一步**: 查看 [性能优化指南](PERFORMANCE.md) 和 [故障排查文档](TROUBLESHOOTING.md)
