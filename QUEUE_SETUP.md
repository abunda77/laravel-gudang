# Queue Setup Guide

This document provides instructions for setting up and managing the queue system for the Warehouse Management System.

## Overview

The WMS uses Laravel's queue system to handle heavy operations like monthly report generation in the background. This ensures that time-consuming tasks don't block the user interface.

## Queue Configuration

### Database Driver (Default)

The system is configured to use the database driver for queues. This is already set in your `.env` file:

```env
QUEUE_CONNECTION=database
```

### Redis Driver (Recommended for Production)

For better performance in production, consider using Redis:

1. Install Redis on your server
2. Install the PHP Redis extension or predis package:
    ```bash
    composer require predis/predis
    ```
3. Update your `.env` file:
    ```env
    QUEUE_CONNECTION=redis
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```

## Running Queue Workers

### Development

For development, you can run the queue worker manually:

```bash
php artisan queue:work
```

Or with specific options:

```bash
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
```

Options explained:

-   `database`: The queue connection to use
-   `--sleep=3`: Sleep for 3 seconds when no jobs are available
-   `--tries=3`: Attempt each job up to 3 times before failing
-   `--max-time=3600`: Maximum execution time (1 hour)

### Production with Supervisor

For production environments, use Supervisor to keep the queue worker running:

1. **Install Supervisor** (if not already installed):

    ```bash
    sudo apt-get install supervisor
    ```

2. **Copy the configuration file**:

    ```bash
    sudo cp supervisor-queue-worker.conf /etc/supervisor/conf.d/warehouse-queue-worker.conf
    ```

3. **Update the configuration**:
   Edit `/etc/supervisor/conf.d/warehouse-queue-worker.conf` and update:

    - Replace `/path/to/your/project` with your actual project path
    - Update the `user` to match your web server user (www-data, nginx, apache, etc.)

4. **Reload Supervisor**:

    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start warehouse-queue-worker:*
    ```

5. **Check status**:

    ```bash
    sudo supervisorctl status warehouse-queue-worker:*
    ```

6. **Restart after code changes**:
    ```bash
    sudo supervisorctl restart warehouse-queue-worker:*
    ```

## Queue Jobs

### GenerateMonthlyReport

This job generates comprehensive monthly reports in PDF format.

**Supported Report Types:**

-   `sales`: Monthly sales report with customer orders and revenue
-   `purchase`: Monthly purchase report with supplier orders and costs
-   `stock_valuation`: Current inventory valuation
-   `low_stock`: Products below minimum stock threshold

**Usage:**

From the Filament admin panel:

1. Navigate to Reports page
2. Click "Generate Monthly Report"
3. Select report type and month
4. Click "Generate Report"

Programmatically:

```php
use App\Jobs\GenerateMonthlyReport;
use Illuminate\Support\Carbon;

GenerateMonthlyReport::dispatch(
    auth()->user(),
    Carbon::parse('2024-01-01'),
    'sales'
);
```

**Output:**

-   Reports are stored in `storage/app/reports/{type}/{type}-{YYYY-MM}.pdf`
-   Users receive a notification when the report is ready
-   Reports can be downloaded from the notification or storage

## Notifications

Users receive notifications when:

-   Report generation starts (immediate feedback)
-   Report generation completes (with download link)
-   Report generation fails (error details)

Notifications are sent via:

-   Database (in-app notifications)
-   Email (if configured)

## Monitoring

### View Failed Jobs

```bash
php artisan queue:failed
```

### Retry Failed Jobs

Retry a specific job:

```bash
php artisan queue:retry {job-id}
```

Retry all failed jobs:

```bash
php artisan queue:retry all
```

### Clear Failed Jobs

```bash
php artisan queue:flush
```

### Monitor Queue in Real-time

```bash
php artisan queue:listen
```

## Troubleshooting

### Queue Worker Not Processing Jobs

1. Check if the queue worker is running:

    ```bash
    ps aux | grep "queue:work"
    ```

2. Check supervisor status:

    ```bash
    sudo supervisorctl status warehouse-queue-worker:*
    ```

3. Check Laravel logs:
    ```bash
    tail -f storage/logs/laravel.log
    ```

### Jobs Failing

1. Check failed jobs table:

    ```bash
    php artisan queue:failed
    ```

2. Review error logs:

    ```bash
    tail -f storage/logs/laravel.log
    ```

3. Check job-specific logs for GenerateMonthlyReport failures

### Memory Issues

If jobs are failing due to memory limits:

1. Increase PHP memory limit in `php.ini`:

    ```ini
    memory_limit = 512M
    ```

2. Or set it in the queue worker command:
    ```bash
    php -d memory_limit=512M artisan queue:work
    ```

### Timeout Issues

For long-running reports, increase the timeout:

1. In the job class (`app/Jobs/GenerateMonthlyReport.php`):

    ```php
    public $timeout = 600; // 10 minutes
    ```

2. In supervisor configuration:
    ```ini
    stopwaitsecs=3600
    ```

## Best Practices

1. **Always restart queue workers after code changes**:

    ```bash
    php artisan queue:restart
    ```

2. **Use queue priorities for critical jobs**:

    ```php
    GenerateMonthlyReport::dispatch($user, $month)->onQueue('high');
    ```

3. **Monitor queue performance**:

    - Use Laravel Horizon for Redis queues
    - Set up alerts for failed jobs
    - Monitor queue length and processing time

4. **Handle failures gracefully**:

    - Implement the `failed()` method in jobs
    - Log errors for debugging
    - Notify administrators of critical failures

5. **Test queue jobs**:

    ```php
    Queue::fake();

    // Perform action that dispatches job

    Queue::assertPushed(GenerateMonthlyReport::class);
    ```

## Scheduled Reports (Optional)

To automatically generate monthly reports, add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generate monthly reports on the 1st of each month at 2 AM
    $schedule->call(function () {
        $users = User::role('warehouse_admin')->get();
        $lastMonth = now()->subMonth();

        foreach ($users as $user) {
            GenerateMonthlyReport::dispatch($user, $lastMonth, 'sales');
            GenerateMonthlyReport::dispatch($user, $lastMonth, 'purchase');
        }
    })->monthlyOn(1, '02:00');
}
```

Then ensure the scheduler is running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Additional Resources

-   [Laravel Queue Documentation](https://laravel.com/docs/queues)
-   [Supervisor Documentation](http://supervisord.org/)
-   [Laravel Horizon](https://laravel.com/docs/horizon) (for Redis queues)
