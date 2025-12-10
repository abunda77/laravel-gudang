# Production Deployment Checklist

Use this checklist to ensure all critical steps are completed before and after deploying to production.

## Pre-Deployment

### Server Setup

-   [ ] Server meets minimum requirements (PHP 8.2+, MySQL 8.0+, 2GB RAM)
-   [ ] All required PHP extensions installed
-   [ ] Web server (Nginx/Apache) installed and configured
-   [ ] MySQL/MariaDB installed and secured
-   [ ] Redis installed (for production performance)
-   [ ] Supervisor installed (for queue workers)
-   [ ] SSL certificate obtained and configured
-   [ ] Domain DNS configured and propagated
-   [ ] Firewall configured (ports 22, 80, 443)

### Application Setup

-   [ ] Repository cloned to `/var/www/warehouse-management-system`
-   [ ] Correct file permissions set (www-data:www-data)
-   [ ] Composer dependencies installed (`--no-dev --optimize-autoloader`)
-   [ ] NPM dependencies installed and built
-   [ ] `.env` file created from `.env.production.example`
-   [ ] Application key generated (`php artisan key:generate`)

### Environment Configuration

-   [ ] `APP_ENV=production`
-   [ ] `APP_DEBUG=false`
-   [ ] `APP_URL` set to production domain (HTTPS)
-   [ ] `FORCE_HTTPS=true` configured
-   [ ] Database credentials configured
-   [ ] Redis credentials configured
-   [ ] Mail server configured
-   [ ] Backup path configured
-   [ ] Sentry DSN configured (if using)
-   [ ] Rate limiting configured

### Database

-   [ ] Production database created
-   [ ] Database user created with appropriate privileges
-   [ ] Migrations executed (`php artisan migrate --force`)
-   [ ] Initial data seeded (`php artisan db:seed --force`)
-   [ ] Admin user created (`php artisan make:filament-user`)

### Queue Workers

-   [ ] Supervisor configuration copied to `/etc/supervisor/conf.d/`
-   [ ] Supervisor configuration paths updated
-   [ ] Supervisor reloaded (`supervisorctl reread && update`)
-   [ ] Queue workers started and running
-   [ ] Queue worker logs accessible

### Scheduled Tasks

-   [ ] Crontab configured for www-data user
-   [ ] Laravel scheduler running every minute
-   [ ] Backup directory created with correct permissions
-   [ ] Test backup command executed successfully

### Security

-   [ ] `.env` file permissions set to 600
-   [ ] Storage and cache directories writable
-   [ ] Directory listing disabled
-   [ ] SSL/TLS properly configured
-   [ ] HTTPS force enabled (`FORCE_HTTPS=true`)
-   [ ] Trusted proxies configured in `bootstrap/app.php`
-   [ ] Security headers configured in web server
-   [ ] Database password is strong and secure
-   [ ] Redis password configured (if exposed)

### Performance

-   [ ] OPcache enabled and configured
-   [ ] Configuration cached (`php artisan config:cache`)
-   [ ] Routes cached (`php artisan route:cache`)
-   [ ] Views cached (`php artisan view:cache`)
-   [ ] Events cached (`php artisan event:cache`)
-   [ ] Redis configured for cache and sessions

## Post-Deployment

### Verification

-   [ ] Application accessible via HTTPS
-   [ ] HTTP redirects to HTTPS
-   [ ] No mixed content warnings in browser console
-   [ ] All assets (CSS, JS, images) load via HTTPS
-   [ ] Admin panel accessible at `/admin`
-   [ ] Can login with admin credentials
-   [ ] All Filament resources load correctly
-   [ ] Filament components (date picker, select, textarea) working properly
-   [ ] Dashboard widgets display data
-   [ ] File uploads working
-   [ ] PDF generation working
-   [ ] Email sending working

### Functionality Tests

-   [ ] Create a product
-   [ ] Create a customer
-   [ ] Create a supplier
-   [ ] Create a purchase order
-   [ ] Process an inbound operation
-   [ ] Verify stock increased
-   [ ] Create a sales order
-   [ ] Process an outbound operation
-   [ ] Verify stock decreased
-   [ ] Generate a delivery order PDF
-   [ ] Generate an invoice PDF
-   [ ] Run a stock card report
-   [ ] Perform a stock opname

### System Health

-   [ ] Queue workers running (`supervisorctl status`)
-   [ ] No errors in application logs
-   [ ] No errors in queue worker logs
-   [ ] No errors in web server logs
-   [ ] Scheduled tasks listed (`php artisan schedule:list`)
-   [ ] Health check endpoint responding (`/up`)
-   [ ] Database connections working
-   [ ] Redis connections working

### Monitoring

-   [ ] Error tracking configured (Sentry)
-   [ ] Application logs being written
-   [ ] Queue worker logs being written
-   [ ] Backup logs being written
-   [ ] Disk space monitoring set up
-   [ ] Memory usage monitoring set up

### Backup & Recovery

-   [ ] Manual backup test successful
-   [ ] Automated backup scheduled
-   [ ] Backup retention policy configured
-   [ ] Backup restoration tested
-   [ ] Off-site backup configured (optional)

### Documentation

-   [ ] Deployment documentation reviewed
-   [ ] Admin credentials documented securely
-   [ ] Database credentials documented securely
-   [ ] API keys documented securely
-   [ ] Emergency contacts documented
-   [ ] Rollback procedure documented

## Ongoing Maintenance

### Daily

-   [ ] Monitor error logs
-   [ ] Check queue job status
-   [ ] Verify backup completion

### Weekly

-   [ ] Review application performance
-   [ ] Check disk space usage
-   [ ] Review failed jobs

### Monthly

-   [ ] Test backup restoration
-   [ ] Apply security updates
-   [ ] Optimize database tables
-   [ ] Review and rotate logs
-   [ ] Update dependencies (if needed)

### Quarterly

-   [ ] Security audit
-   [ ] Performance review
-   [ ] Capacity planning review
-   [ ] Disaster recovery test

## Emergency Contacts

```
System Administrator: _______________________
Database Administrator: _____________________
Application Developer: ______________________
Hosting Provider Support: ___________________
```

## Rollback Procedure

If deployment fails:

1. Enable maintenance mode:

    ```bash
    php artisan down
    ```

2. Restore previous version:

    ```bash
    git checkout <previous-commit>
    composer install --no-dev --optimize-autoloader
    ```

3. Restore database (if migrations were run):

    ```bash
    php artisan migrate:rollback
    ```

4. Clear caches:

    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

5. Restart queue workers:

    ```bash
    sudo supervisorctl restart warehouse-queue-worker:*
    ```

6. Disable maintenance mode:
    ```bash
    php artisan up
    ```

## Notes

Date Deployed: **\*\***\_\_\_**\*\***
Deployed By: **\*\*\*\***\_**\*\*\*\***
Version/Commit: **\*\***\_\_**\*\***
Issues Encountered: \***\*\_\_\*\***

---

---

---

**Keep this checklist updated with each deployment!**
