# Warehouse Management System - Deployment Guide

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Database Setup](#database-setup)
6. [Queue Workers](#queue-workers)
7. [Scheduled Tasks](#scheduled-tasks)
8. [Monitoring and Error Tracking](#monitoring-and-error-tracking)
9. [Backup Strategy](#backup-strategy)
10. [Performance Optimization](#performance-optimization)
11. [Security Considerations](#security-considerations)
12. [Troubleshooting](#troubleshooting)

## Server Requirements

### Minimum Requirements

-   **PHP**: 8.2 or higher
-   **Web Server**: Nginx or Apache
-   **Database**: MySQL 8.0+ or MariaDB 10.3+
-   **Memory**: 2GB RAM minimum (4GB recommended)
-   **Storage**: 20GB minimum
-   **Redis**: 6.0+ (recommended for production)

### Required PHP Extensions

```bash
php -m | grep -E 'bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|gd|zip|redis'
```

Required extensions:

-   BCMath
-   Ctype
-   Fileinfo
-   JSON
-   Mbstring
-   OpenSSL
-   PDO
-   Tokenizer
-   XML
-   GD
-   Zip
-   Redis (for production)

### System Dependencies

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip \
    php8.2-gd nginx mysql-server redis-server supervisor composer

# CentOS/RHEL
sudo yum install -y php82 php82-cli php82-fpm php82-mysqlnd php82-redis \
    php82-mbstring php82-xml php82-bcmath php82-curl php82-zip \
    php82-gd nginx mysql-server redis supervisor composer
```

## Pre-Deployment Checklist

-   [ ] Server meets minimum requirements
-   [ ] Domain name configured and DNS propagated
-   [ ] SSL certificate obtained (Let's Encrypt recommended)
-   [ ] Database server installed and secured
-   [ ] Redis server installed (for production)
-   [ ] Backup storage configured
-   [ ] Email service configured (SMTP)
-   [ ] Monitoring tools ready (optional: Sentry, New Relic)

## Installation Steps

### 1. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/yourusername/warehouse-management-system.git
cd warehouse-management-system
```

### 2. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/warehouse-management-system
sudo chmod -R 755 /var/www/warehouse-management-system
sudo chmod -R 775 /var/www/warehouse-management-system/storage
sudo chmod -R 775 /var/www/warehouse-management-system/bootstrap/cache
```

### 3. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 4. Environment Configuration

```bash
cp .env.production.example .env
php artisan key:generate
```

Edit `.env` file with your production settings:

```bash
nano .env
```

## Configuration

### Environment Variables

#### Application Settings

```env
APP_NAME="Warehouse Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://warehouse.yourdomain.com
```

#### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=warehouse_production
DB_USERNAME=warehouse_user
DB_PASSWORD=your_secure_password
```

#### Cache and Session (Production)

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Redis Configuration

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
```

#### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
```

#### Backup Configuration

```env
BACKUP_ENABLED=true
BACKUP_PATH=/var/backups/warehouse
BACKUP_RETENTION_DAYS=30
```

#### Error Tracking (Sentry)

```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

## Database Setup

### 1. Create Database and User

```sql
mysql -u root -p

CREATE DATABASE warehouse_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'warehouse_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON warehouse_production.* TO 'warehouse_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Run Migrations

```bash
php artisan migrate --force
```

### 3. Seed Initial Data

```bash
php artisan db:seed --force
```

### 4. Create Admin User

```bash
php artisan make:filament-user
```

## Queue Workers

### Setup Supervisor for Queue Workers

1. Copy supervisor configuration:

```bash
sudo cp supervisor-queue-worker.conf /etc/supervisor/conf.d/warehouse-queue-worker.conf
```

2. Edit the configuration:

```bash
sudo nano /etc/supervisor/conf.d/warehouse-queue-worker.conf
```

Update paths:

```ini
[program:warehouse-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/warehouse-management-system/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/warehouse-management-system/storage/logs/queue-worker.log
stopwaitsecs=3600
```

3. Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start warehouse-queue-worker:*
```

4. Check status:

```bash
sudo supervisorctl status warehouse-queue-worker:*
```

### Queue Worker Commands

```bash
# Restart workers after deployment
sudo supervisorctl restart warehouse-queue-worker:*

# Stop workers
sudo supervisorctl stop warehouse-queue-worker:*

# View logs
tail -f storage/logs/queue-worker.log
```

## Scheduled Tasks

### Setup Cron Job

Add Laravel scheduler to crontab:

```bash
sudo crontab -e -u www-data
```

Add this line:

```cron
* * * * * cd /var/www/warehouse-management-system && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks Overview

The following tasks are automatically scheduled:

-   **Database Backup**: Daily at 2:00 AM
-   **Cache Cleanup**: Weekly
-   **Queue Monitoring**: Every 5 minutes
-   **Log Cleanup**: Monthly

### Manual Backup

```bash
php artisan db:backup
```

### Manual Log Cleanup

```bash
php artisan log:clear --days=30
```

## Monitoring and Error Tracking

### Sentry Setup (Recommended)

1. Install Sentry SDK:

```bash
composer require sentry/sentry-laravel
```

2. Publish configuration:

```bash
php artisan sentry:publish --dsn=your-sentry-dsn
```

3. Test Sentry:

```bash
php artisan sentry:test
```

### Laravel Telescope (Development Only)

For development/staging environments:

1. Install Telescope:

```bash
composer require laravel/telescope --dev
```

2. Publish assets:

```bash
php artisan telescope:install
php artisan migrate
```

3. Enable in `.env`:

```env
TELESCOPE_ENABLED=true
TELESCOPE_PATH=telescope
```

**Important**: Never enable Telescope in production!

### Application Monitoring

Monitor these metrics:

-   Queue job failures
-   Database query performance
-   Cache hit rates
-   Memory usage
-   Response times

## Backup Strategy

### Automated Backups

Database backups run automatically daily at 2:00 AM via cron.

Backup location: `/var/backups/warehouse/` (or as configured in `BACKUP_PATH`)

### Manual Backup

```bash
# Database backup
php artisan db:backup --keep-days=30

# Full application backup
tar -czf warehouse-backup-$(date +%Y%m%d).tar.gz \
    /var/www/warehouse-management-system \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs'
```

### Backup Verification

Regularly test backup restoration:

```bash
# Test database restore
gunzip < backup_warehouse_production_2024-01-01_02-00-00.sql.gz | mysql -u warehouse_user -p warehouse_test
```

### Off-site Backup

Configure off-site backup storage:

-   AWS S3
-   Google Cloud Storage
-   Dedicated backup server

## Performance Optimization

### 1. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 2. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Enable OPcache

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 4. Configure Redis

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### 5. Database Optimization

```sql
-- Add indexes for frequently queried columns
-- Already included in migrations

-- Optimize tables monthly
OPTIMIZE TABLE products, stock_movements, purchase_orders, sales_orders;
```

## Security Considerations

### 1. File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/warehouse-management-system

# Secure permissions
sudo find /var/www/warehouse-management-system -type f -exec chmod 644 {} \;
sudo find /var/www/warehouse-management-system -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/warehouse-management-system/storage
sudo chmod -R 775 /var/www/warehouse-management-system/bootstrap/cache
```

### 2. Environment File Security

```bash
sudo chmod 600 /var/www/warehouse-management-system/.env
```

### 3. Disable Directory Listing

Nginx configuration:

```nginx
autoindex off;
```

### 4. SSL/TLS Configuration

Use Let's Encrypt for free SSL:

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d warehouse.yourdomain.com
```

### 5. Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 6. Database Security

-   Use strong passwords
-   Limit database user privileges
-   Enable MySQL slow query log
-   Regular security updates

### 7. Rate Limiting

Already configured in `bootstrap/app.php`:

-   API: 60 requests per minute (configurable via `API_RATE_LIMIT_PER_MINUTE`)

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/warehouse`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name warehouse.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name warehouse.yourdomain.com;
    root /var/www/warehouse-management-system/public;

    ssl_certificate /etc/letsencrypt/live/warehouse.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/warehouse.yourdomain.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/warehouse /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Troubleshooting

### Common Issues

#### 1. Permission Denied Errors

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Queue Jobs Not Processing

```bash
# Check supervisor status
sudo supervisorctl status warehouse-queue-worker:*

# Restart workers
sudo supervisorctl restart warehouse-queue-worker:*

# Check logs
tail -f storage/logs/queue-worker.log
```

#### 3. Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

#### 4. Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 5. Storage Link Missing

```bash
php artisan storage:link
```

### Log Files

Monitor these log files:

-   Application: `storage/logs/laravel.log`
-   Queue Workers: `storage/logs/queue-worker.log`
-   Nginx Access: `/var/log/nginx/access.log`
-   Nginx Error: `/var/log/nginx/error.log`
-   PHP-FPM: `/var/log/php8.2-fpm.log`

### Health Check

```bash
# Check application health
curl https://warehouse.yourdomain.com/up

# Check queue status
php artisan queue:monitor database --max=100

# Check scheduled tasks
php artisan schedule:list
```

## Post-Deployment Checklist

-   [ ] Application accessible via HTTPS
-   [ ] Admin user created and can login
-   [ ] Database migrations completed
-   [ ] Queue workers running
-   [ ] Cron jobs configured
-   [ ] Backups working
-   [ ] SSL certificate valid
-   [ ] Error tracking configured
-   [ ] Email sending working
-   [ ] File uploads working
-   [ ] PDF generation working
-   [ ] All Filament resources accessible
-   [ ] Role-based permissions working

## Maintenance

### Regular Tasks

**Daily:**

-   Monitor error logs
-   Check queue job status
-   Verify backup completion

**Weekly:**

-   Review application performance
-   Check disk space usage
-   Update dependencies (if needed)

**Monthly:**

-   Review and rotate logs
-   Test backup restoration
-   Security updates
-   Database optimization

### Updating the Application

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Pull latest changes
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Run migrations
php artisan migrate --force

# 5. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers
sudo supervisorctl restart warehouse-queue-worker:*

# 7. Disable maintenance mode
php artisan up
```

## Support

For issues or questions:

-   Documentation: [Link to your docs]
-   Issue Tracker: [Link to GitHub issues]
-   Email: support@yourdomain.com

---

**Last Updated**: 2024
**Version**: 1.0
