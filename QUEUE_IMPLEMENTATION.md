# Queue Implementation Documentation

## Overview

This document describes the queue job implementation for generating monthly reports in the Warehouse Management System.

## Components Implemented

### 1. Job Class: `GenerateMonthlyReport`

**Location:** `app/Jobs/GenerateMonthlyReport.php`

**Purpose:** Generates comprehensive monthly reports in PDF format and stores them in the file system.

**Features:**

-   Implements `ShouldQueue` interface for background processing
-   Supports multiple report types: sales, purchase, stock_valuation, low_stock
-   Configurable retry attempts (3 times)
-   Timeout protection (300 seconds / 5 minutes)
-   Automatic failure logging
-   User notification upon completion

**Usage Example:**

```php
use App\Jobs\GenerateMonthlyReport;
use Illuminate\Support\Carbon;

// Dispatch a sales report for last month
GenerateMonthlyReport::dispatch(
    auth()->user(),
    Carbon::now()->subMonth(),
    'sales'
);
```

### 2. Notification Class: `MonthlyReportGenerated`

**Location:** `app/Notifications/MonthlyReportGenerated.php`

**Purpose:** Notifies users when their monthly report is ready for download.

**Channels:**

-   Database (in-app notifications)
-   Email (if mail is configured)

**Features:**

-   Contains download link
-   Includes report metadata (type, month)
-   Queued for asynchronous delivery

### 3. Filament Page: `Reports`

**Location:** `app/Filament/Pages/Reports.php`

**Purpose:** Provides a user interface for generating monthly reports.

**Features:**

-   Header action button for report generation
-   Form with report type and month selection
-   Immediate feedback notification
-   Organized in "Reports" navigation group

**View Location:** `resources/views/filament/pages/reports.blade.php`

### 4. PDF Templates

**Location:** `resources/views/reports/`

Created templates for all report types:

-   `monthly-sales.blade.php` - Sales report with customer and product details
-   `monthly-purchase.blade.php` - Purchase report with supplier and product details
-   `monthly-stock_valuation.blade.php` - Stock valuation with current inventory value
-   `monthly-low_stock.blade.php` - Low stock alert report

**Features:**

-   Professional PDF layout
-   Responsive tables
-   Summary sections
-   Branding and metadata

### 5. Supervisor Configuration

**Location:** `supervisor-queue-worker.conf`

**Purpose:** Production-ready supervisor configuration for queue workers.

**Features:**

-   Auto-restart on failure
-   Multiple worker processes (2 by default)
-   Proper logging
-   Graceful shutdown handling

### 6. Testing Command

**Location:** `app/Console/Commands/TestMonthlyReport.php`

**Purpose:** Command-line tool for testing report generation.

**Usage:**

```bash
# Interactive mode
php artisan report:test-monthly

# With parameters
php artisan report:test-monthly 1 --type=sales --month=2024-01

# Options
php artisan report:test-monthly --help
```

## File Structure

```
app/
├── Jobs/
│   └── GenerateMonthlyReport.php
├── Notifications/
│   └── MonthlyReportGenerated.php
├── Filament/
│   └── Pages/
│       └── Reports.php
└── Console/
    └── Commands/
        └── TestMonthlyReport.php

resources/
└── views/
    ├── filament/
    │   └── pages/
    │       └── reports.blade.php
    └── reports/
        ├── monthly-sales.blade.php
        ├── monthly-purchase.blade.php
        ├── monthly-stock_valuation.blade.php
        └── monthly-low_stock.blade.php

storage/
└── app/
    └── reports/
        ├── sales/
        ├── purchase/
        ├── stock_valuation/
        └── low_stock/

supervisor-queue-worker.conf
QUEUE_SETUP.md
QUEUE_IMPLEMENTATION.md
```

## Database Tables

The following tables are used by the queue system:

1. **jobs** - Stores pending queue jobs
2. **failed_jobs** - Stores failed job information
3. **notifications** - Stores user notifications

All migrations have been created and run.

## Configuration

### Environment Variables

```env
# Queue driver (database is default, redis recommended for production)
QUEUE_CONNECTION=database

# Mail configuration (for email notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourcompany.com
```

### Storage Configuration

Reports are stored in `storage/app/reports/{type}/` directory. Ensure proper permissions:

```bash
chmod -R 775 storage/app/reports
chown -R www-data:www-data storage/app/reports
```

## Usage Guide

### For End Users (via Filament)

1. Navigate to **Reports** page in the admin panel
2. Click **Generate Monthly Report** button
3. Select:
    - Report Type (Sales, Purchase, Stock Valuation, or Low Stock)
    - Month (defaults to last month)
4. Click **Generate Report**
5. Receive immediate confirmation notification
6. Wait for completion notification (usually 1-5 minutes)
7. Download report from notification link

### For Developers (Programmatically)

```php
use App\Jobs\GenerateMonthlyReport;
use App\Models\User;
use Illuminate\Support\Carbon;

// Get the user
$user = User::find(1);

// Generate sales report for January 2024
GenerateMonthlyReport::dispatch(
    $user,
    Carbon::parse('2024-01-01'),
    'sales'
);

// Generate with custom queue
GenerateMonthlyReport::dispatch($user, $month, 'sales')
    ->onQueue('reports');

// Generate with delay
GenerateMonthlyReport::dispatch($user, $month, 'sales')
    ->delay(now()->addMinutes(5));
```

### For System Administrators (CLI)

```bash
# Test report generation
php artisan report:test-monthly 1 --type=sales --month=2024-01

# Process queue jobs
php artisan queue:work

# Process one job and stop
php artisan queue:work --once

# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all
```

## Monitoring

### Check Queue Status

```bash
# View jobs in queue
php artisan queue:monitor

# View failed jobs
php artisan queue:failed

# Check queue worker status (with supervisor)
sudo supervisorctl status warehouse-queue-worker:*
```

### Logs

-   **Application logs:** `storage/logs/laravel.log`
-   **Queue worker logs:** `storage/logs/queue-worker.log` (when using supervisor)
-   **Failed job details:** Available via `php artisan queue:failed`

## Performance Considerations

### Report Generation Time

Typical generation times:

-   Sales Report: 10-30 seconds (depends on data volume)
-   Purchase Report: 10-30 seconds
-   Stock Valuation: 5-15 seconds
-   Low Stock: 2-5 seconds

### Optimization Tips

1. **Use Redis for queues** in production for better performance
2. **Run multiple queue workers** to process jobs in parallel
3. **Add indexes** to frequently queried columns
4. **Cache report data** if generating the same report multiple times
5. **Implement pagination** for very large datasets

### Scaling

For high-volume environments:

```bash
# Run multiple workers
php artisan queue:work --queue=high,default,low --sleep=3 --tries=3 &
php artisan queue:work --queue=high,default,low --sleep=3 --tries=3 &
php artisan queue:work --queue=high,default,low --sleep=3 --tries=3 &
```

Or use supervisor with `numprocs=5` in the configuration.

## Troubleshooting

### Job Not Processing

1. Ensure queue worker is running:

    ```bash
    ps aux | grep "queue:work"
    ```

2. Check for errors in logs:

    ```bash
    tail -f storage/logs/laravel.log
    ```

3. Verify database connection

### PDF Generation Fails

1. Check DomPDF is installed:

    ```bash
    composer show barryvdh/laravel-dompdf
    ```

2. Verify storage permissions:

    ```bash
    ls -la storage/app/reports
    ```

3. Check memory limit in php.ini

### Notifications Not Sent

1. Verify notifications table exists:

    ```bash
    php artisan migrate:status
    ```

2. Check mail configuration in .env

3. Test mail connection:
    ```bash
    php artisan tinker
    Mail::raw('Test', function($msg) { $msg->to('test@example.com'); });
    ```

## Security Considerations

1. **File Access:** Reports contain sensitive business data

    - Stored in `storage/app` (not publicly accessible)
    - Access controlled through authentication
    - Download links include user verification

2. **Queue Jobs:** Can be viewed in database

    - Avoid storing sensitive data in job properties
    - Use encrypted database connections

3. **Notifications:** May contain business information
    - Email notifications should use secure SMTP
    - Consider data retention policies

## Future Enhancements

Potential improvements:

1. **Scheduled Reports:** Auto-generate reports monthly
2. **Report Templates:** Customizable report layouts
3. **Export Formats:** Add Excel, CSV export options
4. **Report History:** Track all generated reports
5. **Batch Processing:** Generate multiple reports at once
6. **Report Sharing:** Share reports with other users
7. **Dashboard Integration:** Display recent reports on dashboard
8. **API Endpoints:** Generate reports via API

## Related Documentation

-   [QUEUE_SETUP.md](QUEUE_SETUP.md) - Detailed queue setup instructions
-   [Laravel Queue Documentation](https://laravel.com/docs/queues)
-   [Filament Documentation](https://filamentphp.com/docs)
-   [DomPDF Documentation](https://github.com/barryvdh/laravel-dompdf)

## Support

For issues or questions:

1. Check the troubleshooting section
2. Review Laravel logs
3. Consult the development team
4. Refer to Laravel and Filament documentation
