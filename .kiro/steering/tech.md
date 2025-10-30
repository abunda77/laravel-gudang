# Technology Stack

## Backend

-   **Framework**: Laravel 12
-   **PHP Version**: ^8.2
-   **Admin Panel**: Filament 4.0
-   **Database**: MySQL/PostgreSQL (configurable), SQLite for development
-   **ORM**: Eloquent
-   **Queue System**: Database driver (default), Redis for production
-   **Cache**: Database driver (default), Redis for production

## Frontend

-   **Build Tool**: Vite 6
-   **CSS Framework**: Tailwind CSS 4
-   **JavaScript**: Vanilla JS with Axios

## Key Packages

-   **spatie/laravel-permission**: Role and permission management
-   **barryvdh/laravel-dompdf**: PDF generation for documents
-   **laravel/tinker**: REPL for Laravel
-   **laravel/pail**: Log viewer

## Development Tools

-   **Testing**: PHPUnit 11.5
-   **Code Style**: Laravel Pint
-   **Debugging**: Laravel Telescope (optional)
-   **Mocking**: Mockery
-   **Fake Data**: Faker

## Common Commands

### Development

```bash
# Start development server with queue, logs, and vite
composer dev

# Or start individually
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Database

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code with Pint
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

### Cache & Optimization

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Management

```bash
# Process queue jobs
php artisan queue:work

# Listen for new jobs
php artisan queue:listen

# Restart queue workers
php artisan queue:restart
```

### Filament

```bash
# Create Filament resource
php artisan make:filament-resource ModelName

# Create Filament page
php artisan make:filament-page PageName

# Create Filament widget
php artisan make:filament-widget WidgetName

# Upgrade Filament
php artisan filament:upgrade
```

## Build Process

### Development

```bash
npm run dev
```

### Production

```bash
npm run build
```

## Environment Configuration

-   Development: `.env` (use `.env.example` as template)
-   Production: `.env.production.example` as reference
-   Database: SQLite for dev, MySQL/PostgreSQL for production
-   Queue: Database driver for dev, Redis recommended for production
-   Cache: Database for dev, Redis recommended for production
