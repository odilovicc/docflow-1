#!/bin/bash

# DocsFlow Backup Script
# Run daily via cron: 0 2 * * * /var/www/docflow/scripts/backup.sh

# Configuration
APP_DIR="/var/www/docflow"
BACKUP_DIR="/backups/docflow"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Database Configuration (from .env)
DB_NAME="docflow_production"
DB_USER="docflow_user"
DB_PASSWORD="your_secure_database_password_here"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Function to log messages
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a $BACKUP_DIR/backup.log
}

log "Starting backup process..."

# 1. Database Backup
log "Backing up database..."
mysqldump --single-transaction --routines --triggers \
    -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/db_$DATE.sql

if [ $? -eq 0 ]; then
    log "Database backup completed successfully"
    gzip $BACKUP_DIR/db_$DATE.sql
    log "Database backup compressed"
else
    log "ERROR: Database backup failed"
    exit 1
fi

# 2. Storage Files Backup
log "Backing up storage files..."
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C $APP_DIR storage/app/public/

if [ $? -eq 0 ]; then
    log "Storage backup completed successfully"
else
    log "ERROR: Storage backup failed"
    exit 1
fi

# 3. Configuration Backup
log "Backing up configuration..."
cp $APP_DIR/.env $BACKUP_DIR/env_$DATE
cp $APP_DIR/.env.production $BACKUP_DIR/env_production_$DATE

# 4. Application Code Backup (optional - only if not using version control)
log "Backing up application code..."
tar --exclude='vendor' --exclude='node_modules' --exclude='storage/logs' \
    --exclude='storage/framework/cache' --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' --exclude='.git' \
    -czf $BACKUP_DIR/app_$DATE.tar.gz -C $(dirname $APP_DIR) $(basename $APP_DIR)

# 5. Cleanup old backups
log "Cleaning up old backups (older than $RETENTION_DAYS days)..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "env_*" -mtime +$RETENTION_DAYS -delete

# 6. Backup verification
log "Verifying backup integrity..."
if [ -f "$BACKUP_DIR/db_$DATE.sql.gz" ] && [ -f "$BACKUP_DIR/storage_$DATE.tar.gz" ]; then
    # Test gzip files
    gzip -t $BACKUP_DIR/db_$DATE.sql.gz
    if [ $? -eq 0 ]; then
        log "Database backup integrity verified"
    else
        log "ERROR: Database backup is corrupted"
        exit 1
    fi
    
    tar -tzf $BACKUP_DIR/storage_$DATE.tar.gz > /dev/null
    if [ $? -eq 0 ]; then
        log "Storage backup integrity verified"
    else
        log "ERROR: Storage backup is corrupted"
        exit 1
    fi
    
    log "Backup completed successfully"
    
    # Calculate backup sizes
    DB_SIZE=$(du -h $BACKUP_DIR/db_$DATE.sql.gz | cut -f1)
    STORAGE_SIZE=$(du -h $BACKUP_DIR/storage_$DATE.tar.gz | cut -f1)
    APP_SIZE=$(du -h $BACKUP_DIR/app_$DATE.tar.gz | cut -f1)
    
    log "Backup sizes - Database: $DB_SIZE, Storage: $STORAGE_SIZE, Application: $APP_SIZE"
else
    log "ERROR: One or more backup files are missing"
    exit 1
fi

# 7. Optional: Upload to remote storage (uncomment and configure)
# log "Uploading backups to remote storage..."
# rsync -av --delete $BACKUP_DIR/ user@backup-server:/backups/docflow/
# aws s3 sync $BACKUP_DIR/ s3://your-backup-bucket/docflow/

log "Backup process completed successfully"

# Send notification email (optional)
# echo "DocsFlow backup completed successfully on $(date)" | mail -s "DocsFlow Backup Report" admin@yourdomain.com