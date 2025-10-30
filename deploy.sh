#!/bin/bash

# Warehouse Management System - Deployment Script
# This script automates common deployment tasks
# Usage: ./deploy.sh [command]
# Commands: install, update, cache, restart, backup

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_PATH="/var/www/warehouse-management-system"
WEB_USER="www-data"

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run as root or with sudo"
        exit 1
    fi
}

# Command: install
install() {
    print_info "Starting fresh installation..."
    
    cd "$PROJECT_PATH"
    
    print_info "Installing composer dependencies..."
    sudo -u "$WEB_USER" composer install --no-dev --optimize-autoloader
    print_success "Composer dependencies installed"
    
    print_info "Installing NPM dependencies..."
    sudo -u "$WEB_USER" npm install
    print_success "NPM dependencies installed"
    
    print_info "Building assets..."
    sudo -u "$WEB_USER" npm run build
    print_success "Assets built"
    
    print_info "Setting up environment..."
    if [ ! -f .env ]; then
        cp .env.production.example .env
        print_info "Created .env file - Please configure it before continuing"
        exit 0
    fi
    
    print_info "Generating application key..."
    sudo -u "$WEB_USER" php artisan key:generate
    print_success "Application key generated"
    
    print_info "Running migrations..."
    sudo -u "$WEB_USER" php artisan migrate --force
    print_success "Migrations completed"
    
    print_info "Creating storage link..."
    sudo -u "$WEB_USER" php artisan storage:link
    print_success "Storage link created"
    
    print_info "Setting permissions..."
    chown -R "$WEB_USER:$WEB_USER" "$PROJECT_PATH"
    chmod -R 755 "$PROJECT_PATH"
    chmod -R 775 "$PROJECT_PATH/storage"
    chmod -R 775 "$PROJECT_PATH/bootstrap/cache"
    chmod 600 "$PROJECT_PATH/.env"
    print_success "Permissions set"
    
    cache
    
    print_success "Installation completed!"
    print_info "Next steps:"
    print_info "1. Configure supervisor for queue workers"
    print_info "2. Configure crontab for scheduled tasks"
    print_info "3. Create admin user: php artisan make:filament-user"
}

# Command: update
update() {
    print_info "Starting application update..."
    
    cd "$PROJECT_PATH"
    
    print_info "Enabling maintenance mode..."
    sudo -u "$WEB_USER" php artisan down
    print_success "Maintenance mode enabled"
    
    print_info "Pulling latest changes..."
    sudo -u "$WEB_USER" git pull origin main
    print_success "Code updated"
    
    print_info "Installing composer dependencies..."
    sudo -u "$WEB_USER" composer install --no-dev --optimize-autoloader
    print_success "Composer dependencies updated"
    
    print_info "Installing NPM dependencies..."
    sudo -u "$WEB_USER" npm install
    print_success "NPM dependencies updated"
    
    print_info "Building assets..."
    sudo -u "$WEB_USER" npm run build
    print_success "Assets built"
    
    print_info "Running migrations..."
    sudo -u "$WEB_USER" php artisan migrate --force
    print_success "Migrations completed"
    
    cache
    restart
    
    print_info "Disabling maintenance mode..."
    sudo -u "$WEB_USER" php artisan up
    print_success "Maintenance mode disabled"
    
    print_success "Update completed!"
}

# Command: cache
cache() {
    print_info "Clearing and caching application..."
    
    cd "$PROJECT_PATH"
    
    print_info "Clearing caches..."
    sudo -u "$WEB_USER" php artisan cache:clear
    sudo -u "$WEB_USER" php artisan config:clear
    sudo -u "$WEB_USER" php artisan route:clear
    sudo -u "$WEB_USER" php artisan view:clear
    print_success "Caches cleared"
    
    print_info "Caching configuration..."
    sudo -u "$WEB_USER" php artisan config:cache
    sudo -u "$WEB_USER" php artisan route:cache
    sudo -u "$WEB_USER" php artisan view:cache
    sudo -u "$WEB_USER" php artisan event:cache
    print_success "Configuration cached"
}

# Command: restart
restart() {
    print_info "Restarting services..."
    
    print_info "Restarting queue workers..."
    if command -v supervisorctl &> /dev/null; then
        supervisorctl restart warehouse-queue-worker:*
        print_success "Queue workers restarted"
    else
        print_error "Supervisor not found - queue workers not restarted"
    fi
    
    print_info "Restarting PHP-FPM..."
    if systemctl is-active --quiet php8.2-fpm; then
        systemctl restart php8.2-fpm
        print_success "PHP-FPM restarted"
    else
        print_error "PHP-FPM service not found"
    fi
    
    print_info "Reloading Nginx..."
    if systemctl is-active --quiet nginx; then
        systemctl reload nginx
        print_success "Nginx reloaded"
    else
        print_error "Nginx service not found"
    fi
}

# Command: backup
backup() {
    print_info "Creating backup..."
    
    cd "$PROJECT_PATH"
    
    sudo -u "$WEB_USER" php artisan db:backup
    print_success "Database backup completed"
    
    print_info "Backup location: $(php artisan tinker --execute='echo config(\"database.backup.path\");')"
}

# Command: status
status() {
    print_info "Checking application status..."
    
    cd "$PROJECT_PATH"
    
    print_info "Queue workers:"
    if command -v supervisorctl &> /dev/null; then
        supervisorctl status warehouse-queue-worker:*
    else
        print_error "Supervisor not installed"
    fi
    
    print_info "\nScheduled tasks:"
    sudo -u "$WEB_USER" php artisan schedule:list
    
    print_info "\nApplication health:"
    curl -s http://localhost/up || print_error "Health check failed"
    
    print_info "\nDisk usage:"
    df -h "$PROJECT_PATH"
    
    print_info "\nRecent logs:"
    tail -n 10 "$PROJECT_PATH/storage/logs/laravel.log"
}

# Command: logs
logs() {
    print_info "Showing recent logs..."
    
    cd "$PROJECT_PATH"
    
    print_info "Application logs:"
    tail -f "$PROJECT_PATH/storage/logs/laravel.log"
}

# Main script
case "$1" in
    install)
        check_root
        install
        ;;
    update)
        check_root
        update
        ;;
    cache)
        check_root
        cache
        ;;
    restart)
        check_root
        restart
        ;;
    backup)
        check_root
        backup
        ;;
    status)
        status
        ;;
    logs)
        logs
        ;;
    *)
        echo "Warehouse Management System - Deployment Script"
        echo ""
        echo "Usage: sudo ./deploy.sh [command]"
        echo ""
        echo "Commands:"
        echo "  install   - Fresh installation"
        echo "  update    - Update application (pull, install, migrate, cache)"
        echo "  cache     - Clear and rebuild caches"
        echo "  restart   - Restart queue workers and services"
        echo "  backup    - Create database backup"
        echo "  status    - Show application status"
        echo "  logs      - Show application logs (tail -f)"
        echo ""
        exit 1
        ;;
esac
