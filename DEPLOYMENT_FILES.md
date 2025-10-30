# Deployment Files Overview

This document provides an overview of all deployment-related files in this repository.

## Configuration Files

### Environment Configuration

#### `.env.example`

-   Template for local development environment
-   Contains all required environment variables with development defaults
-   Copy to `.env` and customize for your local setup

#### `.env.production.example`

-   Template for production environment
-   Optimized settings for production deployment
-   Contains security-focused configurations
-   Copy to `.env` on production server and customize

### Web Server Configuration

#### `nginx.conf.example`

-   Complete Nginx configuration for production
-   Includes SSL/TLS setup
-   Security headers configured
-   Gzip compression enabled
-   Static asset caching configured
-   **Location**: Copy to `/etc/nginx/sites-available/warehouse`

### Process Management

#### `supervisor-queue-worker.conf`

-   Supervisor configuration for Laravel queue workers
-   Manages 2 worker processes by default
-   Auto-restart on failure
-   **Location**: Copy to `/etc/supervisor/conf.d/warehouse-queue-worker.conf`

### Scheduled Tasks

#### `crontab.example`

-   Crontab configuration for Laravel scheduler
-   Runs scheduled tasks every minute
-   Includes optional manual tasks
-   **Setup**: Add to www-data user crontab

## Scripts

### `deploy.sh`

Automated deployment script with the following commands:

-   `install` - Fresh installation
-   `update` - Update application (pull, install, migrate, cache)
-   `cache` - Clear and rebuild caches
-   `restart` - Restart queue workers and services
-   `backup` - Create database backup
-   `status` - Show application status
-   `logs` - Show application logs

**Usage**: `sudo ./deploy.sh [command]`

**Requirements**: Must be run as root or with sudo

## Documentation

### `DEPLOYMENT.md`

Comprehensive deployment guide covering:

-   Server requirements
-   Installation steps
-   Configuration details
-   Database setup
-   Queue worker setup
-   Scheduled tasks
-   Monitoring and error tracking
-   Backup strategy
-   Performance optimization
-   Security considerations
-   Troubleshooting

### `PRODUCTION_CHECKLIST.md`

Step-by-step checklist for production deployment:

-   Pre-deployment tasks
-   Post-deployment verification
-   Ongoing maintenance schedule
-   Emergency contacts
-   Rollback procedure

### `DEPLOYMENT_FILES.md` (this file)

Overview of all deployment-related files

## Application Commands

### Database Backup

#### `app/Console/Commands/BackupDatabase.php`

Custom Artisan command for database backups:

```bash
php artisan db:backup --keep-days=30
```

Features:

-   Creates compressed SQL dumps
-   Automatic cleanup of old backups
-   Configurable retention period

### Log Cleanup

#### `app/Console/Commands/ClearOldLogs.php`

Custom Artisan command for log cleanup:

```bash
php artisan log:clear --days=30
```

Features:

-   Removes old log files
-   Configurable retention period
-   Preserves current laravel.log

## Configuration Files

### `config/database.php`

Extended with backup configuration:

```php
'backup' => [
    'enabled' => env('BACKUP_ENABLED', true),
    'path' => env('BACKUP_PATH', storage_path('backups')),
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
]
```

### `config/services.php`

Extended with Sentry configuration:

```php
'sentry' => [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 1.0),
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 1.0),
]
```

### `config/telescope.php`

Laravel Telescope configuration for development monitoring:

-   Disabled by default
-   Configurable via `TELESCOPE_ENABLED` environment variable
-   Database storage driver

### `bootstrap/app.php`

Extended with rate limiting:

```php
$middleware->throttleApi(env('API_RATE_LIMIT_PER_MINUTE', 60));
```

### `routes/console.php`

Scheduled tasks configuration:

-   Daily database backup at 2:00 AM
-   Weekly cache cleanup
-   Queue monitoring every 5 minutes
-   Monthly log cleanup

## Quick Start Guide

### For Development

1. Copy environment file:

    ```bash
    cp .env.example .env
    ```

2. Configure database and other settings in `.env`

3. Install dependencies:

    ```bash
    composer install
    npm install
    npm run dev
    ```

4. Run migrations:
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

### For Production

1. Follow the complete guide in `DEPLOYMENT.md`

2. Use the checklist in `PRODUCTION_CHECKLIST.md`

3. Use deployment script for common tasks:
    ```bash
    sudo ./deploy.sh install
    ```

## Environment Variables Reference

### Required for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

### Optional but Recommended

```env
SENTRY_LARAVEL_DSN=your_sentry_dsn
BACKUP_ENABLED=true
BACKUP_PATH=/var/backups/warehouse
REDIS_PASSWORD=your_redis_password
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
```

## Security Notes

1. **Never commit `.env` or `.env.production` files**
2. **Always use strong passwords** for database and Redis
3. **Enable SSL/TLS** in production
4. **Configure firewall** to allow only necessary ports
5. **Set correct file permissions** (see DEPLOYMENT.md)
6. **Keep `.env` file permissions** at 600
7. **Regularly update** dependencies and system packages

## Support

For deployment issues:

1. Check `DEPLOYMENT.md` troubleshooting section
2. Review application logs: `storage/logs/laravel.log`
3. Check queue worker logs: `storage/logs/queue-worker.log`
4. Verify system status: `sudo ./deploy.sh status`

## Maintenance

### Daily

-   Monitor logs for errors
-   Check queue worker status
-   Verify backup completion

### Weekly

-   Review application performance
-   Check disk space

### Monthly

-   Test backup restoration
-   Apply security updates
-   Optimize database

See `PRODUCTION_CHECKLIST.md` for complete maintenance schedule.

---

**Last Updated**: 2024
**Version**: 1.0
