#!/bin/bash

# DocsFlow Deployment Script
# Automates the deployment process for production

# Configuration
APP_DIR="/var/www/docflow"
BACKUP_DIR="/backups/docflow/deployments"
DATE=$(date +%Y%m%d_%H%M%S)
MAINTENANCE_MODE=true

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    echo -e "${GREEN}$(date '+%Y-%m-%d %H:%M:%S')${NC} - $1"
}

error() {
    echo -e "${RED}$(date '+%Y-%m-%d %H:%M:%S')${NC} - ERROR: $1"
}

warning() {
    echo -e "${YELLOW}$(date '+%Y-%m-%d %H:%M:%S')${NC} - WARNING: $1"
}

# Function to check if command succeeded
check_status() {
    if [ $? -eq 0 ]; then
        log "$1 completed successfully"
    else
        error "$1 failed"
        exit 1
    fi
}

log "Starting DocsFlow deployment..."

# 1. Pre-deployment checks
log "Running pre-deployment checks..."

# Check if we're in the correct directory
if [ ! -f "$APP_DIR/artisan" ]; then
    error "artisan file not found. Are you in the correct directory?"
    exit 1
fi

# Check if git is available and repo is clean
cd $APP_DIR
if command -v git &> /dev/null; then
    if [ -n "$(git status --porcelain)" ]; then
        warning "Git working directory is not clean"
        git status --short
    fi
else
    warning "Git not available - skipping repository checks"
fi

# 2. Backup current state
log "Creating pre-deployment backup..."
mkdir -p $BACKUP_DIR

# Backup database
mysqldump --single-transaction -u docflow_user -p docflow_production > $BACKUP_DIR/pre_deploy_db_$DATE.sql
check_status "Database backup"

# Backup current application
tar --exclude='vendor' --exclude='node_modules' --exclude='storage/logs' \
    --exclude='storage/framework/cache' --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' --exclude='.git' \
    -czf $BACKUP_DIR/pre_deploy_app_$DATE.tar.gz -C $(dirname $APP_DIR) $(basename $APP_DIR)
check_status "Application backup"

# 3. Enable maintenance mode
if [ "$MAINTENANCE_MODE" = true ]; then
    log "Enabling maintenance mode..."
    php artisan down --retry=60 --secret="deployment-secret-$(date +%s)"
    check_status "Maintenance mode activation"
fi

# 4. Pull latest changes (if using git)
if command -v git &> /dev/null && [ -d ".git" ]; then
    log "Pulling latest changes from repository..."
    git pull origin main
    check_status "Git pull"
fi

# 5. Install/update dependencies
log "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
check_status "Composer install"

log "Installing NPM dependencies..."
npm ci --production
check_status "NPM install"

# 6. Build frontend assets
log "Building frontend assets..."
npm run build
check_status "Frontend build"

# 7. Run database migrations
log "Running database migrations..."
php artisan migrate --force
check_status "Database migrations"

# 8. Clear and cache configuration
log "Optimizing application..."

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for production
php artisan config:cache
check_status "Configuration caching"

php artisan route:cache
check_status "Route caching"

php artisan view:cache
check_status "View caching"

# 9. Warm up application cache
log "Warming up application cache..."
php artisan cache:manage warm
check_status "Cache warming"

# 10. Update search indexes
log "Updating search indexes..."
php artisan scout:import "App\Models\Document"
php artisan scout:import "App\Models\Task"
check_status "Search index update"

# 11. Restart queue workers
log "Restarting queue workers..."
php artisan queue:restart
check_status "Queue restart"

# If using systemd service
if systemctl is-active --quiet docflow-queue; then
    sudo systemctl restart docflow-queue
    log "DocsFlow queue service restarted"
fi

# 12. Set correct permissions
log "Setting file permissions..."
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache
check_status "Permission setup"

# 13. Health check
log "Running health checks..."

# Check if application is responding
if curl -f -s http://localhost/health > /dev/null; then
    log "Application health check passed"
else
    error "Application health check failed"
    
    # Rollback if health check fails
    warning "Rolling back deployment..."
    
    # Restore database
    mysql -u docflow_user -p docflow_production < $BACKUP_DIR/pre_deploy_db_$DATE.sql
    
    # Restore application files
    cd $(dirname $APP_DIR)
    tar -xzf $BACKUP_DIR/pre_deploy_app_$DATE.tar.gz
    
    error "Deployment rolled back due to health check failure"
    exit 1
fi

# 14. Disable maintenance mode
if [ "$MAINTENANCE_MODE" = true ]; then
    log "Disabling maintenance mode..."
    php artisan up
    check_status "Maintenance mode deactivation"
fi

# 15. Post-deployment tasks
log "Running post-deployment tasks..."

# Clean up old compiled views and cache
php artisan view:clear
php artisan cache:prune-stale-tags

# Run any pending scheduled tasks
php artisan schedule:run

# 16. Cleanup old backups (keep last 5 deployments)
log "Cleaning up old deployment backups..."
ls -t $BACKUP_DIR/pre_deploy_*.sql | tail -n +6 | xargs rm -f
ls -t $BACKUP_DIR/pre_deploy_*.tar.gz | tail -n +6 | xargs rm -f

# 17. Performance verification
log "Running performance checks..."

# Check cache statistics
php artisan cache:manage stats | grep -E "(Hit Rate|Total Size|Total Entries)"

# Check queue status
php artisan queue:monitor redis:default --max=100

log "Deployment completed successfully!"
log "Application is now running the latest version"

# Optional: Send deployment notification
# echo "DocsFlow deployment completed successfully at $(date)" | mail -s "DocsFlow Deployment Notification" admin@yourdomain.com

# Display deployment summary
echo ""
echo "=== DEPLOYMENT SUMMARY ==="
echo "Date: $(date)"
echo "Backup Location: $BACKUP_DIR"
echo "Application Directory: $APP_DIR"
echo "Status: SUCCESS"
echo "=========================="