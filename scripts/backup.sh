#!/bin/bash
# ==============================================================================
# Zambezi Meats - Backup Script
# ==============================================================================
# DEP-023: Configure database backups
# DEP-024: Configure file storage backups
# Daily automated backups, 30-day retention, off-site storage
# ==============================================================================

set -e

# Configuration
APP_NAME="zambezimeats"
BASE_DIR="/var/www/${APP_NAME}"
BACKEND_DIR="${BASE_DIR}/backend/current"
SHARED_DIR="${BASE_DIR}/shared"
BACKUP_DIR="${BASE_DIR}/backups"
LOG_FILE="${BASE_DIR}/logs/backup-$(date +%Y%m%d-%H%M%S).log"

# Retention (days)
DAILY_RETENTION=7
WEEKLY_RETENTION=30
MONTHLY_RETENTION=365

# S3/Remote storage (optional)
S3_BUCKET="${S3_BUCKET:-}"
S3_PREFIX="${S3_PREFIX:-zambezimeats/backups}"

# Date stamps
DATE=$(date +%Y%m%d)
DATETIME=$(date +%Y%m%d-%H%M%S)
DAY_OF_WEEK=$(date +%u)  # 1=Monday, 7=Sunday
DAY_OF_MONTH=$(date +%d)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[✓]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[⚠]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[✗]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

# ==============================================================================
# Initialize
# ==============================================================================

init() {
    mkdir -p "${BACKUP_DIR}/daily/database"
    mkdir -p "${BACKUP_DIR}/daily/files"
    mkdir -p "${BACKUP_DIR}/weekly/database"
    mkdir -p "${BACKUP_DIR}/weekly/files"
    mkdir -p "${BACKUP_DIR}/monthly/database"
    mkdir -p "${BACKUP_DIR}/monthly/files"
    mkdir -p "${BASE_DIR}/logs"
    
    # Load environment variables
    if [ -f "${SHARED_DIR}/.env" ]; then
        set -a
        source "${SHARED_DIR}/.env"
        set +a
    else
        error ".env file not found at ${SHARED_DIR}/.env"
    fi
}

# ==============================================================================
# Database Backup
# ==============================================================================

backup_database() {
    local TYPE="${1:-daily}"
    local BACKUP_PATH="${BACKUP_DIR}/${TYPE}/database"
    local BACKUP_FILE="${BACKUP_PATH}/db-${DATETIME}.sql.gz"
    
    log "Creating ${TYPE} database backup..."
    
    # Use mysqldump with best practices
    mysqldump \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        --host="${DB_HOST:-127.0.0.1}" \
        --port="${DB_PORT:-3306}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --set-gtid-purged=OFF \
        "${DB_DATABASE}" 2>/dev/null | gzip > "$BACKUP_FILE"
    
    # Verify backup
    if [ -s "$BACKUP_FILE" ]; then
        SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        success "Database backup created: ${BACKUP_FILE} (${SIZE})"
    else
        error "Database backup failed - file is empty"
    fi
    
    echo "$BACKUP_FILE"
}

# ==============================================================================
# File Storage Backup
# ==============================================================================

backup_files() {
    local TYPE="${1:-daily}"
    local BACKUP_PATH="${BACKUP_DIR}/${TYPE}/files"
    local BACKUP_FILE="${BACKUP_PATH}/files-${DATETIME}.tar.gz"
    
    log "Creating ${TYPE} file storage backup..."
    
    # Backup storage/app/public (uploaded files, product images)
    tar -czf "$BACKUP_FILE" \
        -C "${SHARED_DIR}" \
        storage/app/public \
        2>/dev/null
    
    if [ -s "$BACKUP_FILE" ]; then
        SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        success "File backup created: ${BACKUP_FILE} (${SIZE})"
    else
        warning "File backup may be empty (no files to backup)"
    fi
    
    echo "$BACKUP_FILE"
}

# ==============================================================================
# Upload to S3
# ==============================================================================

upload_to_s3() {
    local FILE="$1"
    local TYPE="$2"
    
    if [ -z "$S3_BUCKET" ]; then
        log "S3 bucket not configured, skipping upload"
        return
    fi
    
    log "Uploading to S3..."
    
    local S3_PATH="s3://${S3_BUCKET}/${S3_PREFIX}/${TYPE}/$(basename $FILE)"
    
    aws s3 cp "$FILE" "$S3_PATH" --storage-class STANDARD_IA
    
    success "Uploaded to: ${S3_PATH}"
}

# ==============================================================================
# Cleanup Old Backups
# ==============================================================================

cleanup_backups() {
    log "Cleaning up old backups..."
    
    # Daily backups - keep for $DAILY_RETENTION days
    find "${BACKUP_DIR}/daily" -type f -mtime +${DAILY_RETENTION} -delete
    
    # Weekly backups - keep for $WEEKLY_RETENTION days
    find "${BACKUP_DIR}/weekly" -type f -mtime +${WEEKLY_RETENTION} -delete
    
    # Monthly backups - keep for $MONTHLY_RETENTION days
    find "${BACKUP_DIR}/monthly" -type f -mtime +${MONTHLY_RETENTION} -delete
    
    success "Old backups cleaned up"
}

# ==============================================================================
# Verify Backup
# ==============================================================================

verify_backup() {
    local BACKUP_FILE="$1"
    
    log "Verifying backup integrity..."
    
    if [[ "$BACKUP_FILE" == *.sql.gz ]]; then
        # Verify gzip integrity
        if gzip -t "$BACKUP_FILE" 2>/dev/null; then
            success "Backup integrity verified"
            return 0
        else
            error "Backup integrity check failed"
            return 1
        fi
    elif [[ "$BACKUP_FILE" == *.tar.gz ]]; then
        # Verify tar.gz integrity
        if tar -tzf "$BACKUP_FILE" > /dev/null 2>&1; then
            success "Backup integrity verified"
            return 0
        else
            error "Backup integrity check failed"
            return 1
        fi
    fi
}

# ==============================================================================
# Restore Database
# ==============================================================================

restore_database() {
    local BACKUP_FILE="$1"
    
    if [ -z "$BACKUP_FILE" ]; then
        error "Please specify backup file to restore"
    fi
    
    if [ ! -f "$BACKUP_FILE" ]; then
        error "Backup file not found: $BACKUP_FILE"
    fi
    
    warning "⚠️  This will overwrite the current database!"
    read -p "Are you sure you want to continue? (yes/no): " confirm
    
    if [ "$confirm" != "yes" ]; then
        log "Restore cancelled"
        return
    fi
    
    log "Restoring database from: $BACKUP_FILE"
    
    # Put application in maintenance mode
    cd "$BACKEND_DIR"
    php artisan down --render="errors.503"
    
    # Restore
    if [[ "$BACKUP_FILE" == *.gz ]]; then
        gunzip -c "$BACKUP_FILE" | mysql \
            --user="${DB_USERNAME}" \
            --password="${DB_PASSWORD}" \
            --host="${DB_HOST:-127.0.0.1}" \
            "${DB_DATABASE}"
    else
        mysql \
            --user="${DB_USERNAME}" \
            --password="${DB_PASSWORD}" \
            --host="${DB_HOST:-127.0.0.1}" \
            "${DB_DATABASE}" < "$BACKUP_FILE"
    fi
    
    # Clear caches
    php artisan cache:clear
    php artisan config:clear
    
    # Bring application back up
    php artisan up
    
    success "Database restored successfully"
}

# ==============================================================================
# Restore Files
# ==============================================================================

restore_files() {
    local BACKUP_FILE="$1"
    
    if [ -z "$BACKUP_FILE" ]; then
        error "Please specify backup file to restore"
    fi
    
    if [ ! -f "$BACKUP_FILE" ]; then
        error "Backup file not found: $BACKUP_FILE"
    fi
    
    warning "⚠️  This will overwrite existing files!"
    read -p "Are you sure you want to continue? (yes/no): " confirm
    
    if [ "$confirm" != "yes" ]; then
        log "Restore cancelled"
        return
    fi
    
    log "Restoring files from: $BACKUP_FILE"
    
    tar -xzf "$BACKUP_FILE" -C "${SHARED_DIR}"
    
    # Fix permissions
    chown -R www-data:www-data "${SHARED_DIR}/storage"
    chmod -R 775 "${SHARED_DIR}/storage"
    
    success "Files restored successfully"
}

# ==============================================================================
# List Backups
# ==============================================================================

list_backups() {
    echo ""
    echo "=============================================="
    echo "  Available Backups"
    echo "=============================================="
    
    echo ""
    echo "Daily Backups:"
    echo "--------------"
    ls -lh "${BACKUP_DIR}/daily/database/" 2>/dev/null || echo "  No database backups"
    ls -lh "${BACKUP_DIR}/daily/files/" 2>/dev/null || echo "  No file backups"
    
    echo ""
    echo "Weekly Backups:"
    echo "---------------"
    ls -lh "${BACKUP_DIR}/weekly/database/" 2>/dev/null || echo "  No database backups"
    ls -lh "${BACKUP_DIR}/weekly/files/" 2>/dev/null || echo "  No file backups"
    
    echo ""
    echo "Monthly Backups:"
    echo "----------------"
    ls -lh "${BACKUP_DIR}/monthly/database/" 2>/dev/null || echo "  No database backups"
    ls -lh "${BACKUP_DIR}/monthly/files/" 2>/dev/null || echo "  No file backups"
    
    echo ""
}

# ==============================================================================
# Main Backup Routine
# ==============================================================================

run_backup() {
    local TYPE="${1:-daily}"
    
    log "Starting ${TYPE} backup..."
    
    # Database backup
    DB_BACKUP=$(backup_database "$TYPE")
    verify_backup "$DB_BACKUP"
    
    # File backup
    FILES_BACKUP=$(backup_files "$TYPE")
    verify_backup "$FILES_BACKUP"
    
    # Upload to S3 if configured
    upload_to_s3 "$DB_BACKUP" "$TYPE"
    upload_to_s3 "$FILES_BACKUP" "$TYPE"
    
    success "${TYPE} backup completed"
}

# ==============================================================================
# Auto Backup (called by cron)
# ==============================================================================

auto_backup() {
    log "Running automated backup..."
    
    # Always do daily
    run_backup "daily"
    
    # Weekly on Sunday
    if [ "$DAY_OF_WEEK" == "7" ]; then
        run_backup "weekly"
    fi
    
    # Monthly on the 1st
    if [ "$DAY_OF_MONTH" == "01" ]; then
        run_backup "monthly"
    fi
    
    # Cleanup old backups
    cleanup_backups
    
    success "Automated backup completed"
}

# ==============================================================================
# Main
# ==============================================================================

main() {
    echo ""
    echo "=============================================="
    echo "  Zambezi Meats - Backup Tool"
    echo "=============================================="
    echo ""
    
    init
    
    case "${1:-auto}" in
        auto)
            auto_backup
            ;;
        daily)
            run_backup "daily"
            ;;
        weekly)
            run_backup "weekly"
            ;;
        monthly)
            run_backup "monthly"
            ;;
        database)
            backup_database "${2:-daily}"
            ;;
        files)
            backup_files "${2:-daily}"
            ;;
        restore-db)
            restore_database "$2"
            ;;
        restore-files)
            restore_files "$2"
            ;;
        list)
            list_backups
            ;;
        cleanup)
            cleanup_backups
            ;;
        verify)
            verify_backup "$2"
            ;;
        --help|-h)
            echo "Usage: $0 [command] [options]"
            echo ""
            echo "Commands:"
            echo "  auto              Run automated backup (default, for cron)"
            echo "  daily             Run daily backup"
            echo "  weekly            Run weekly backup"
            echo "  monthly           Run monthly backup"
            echo "  database [type]   Backup database only"
            echo "  files [type]      Backup files only"
            echo "  restore-db <file> Restore database from backup"
            echo "  restore-files <f> Restore files from backup"
            echo "  list              List all available backups"
            echo "  cleanup           Remove old backups"
            echo "  verify <file>     Verify backup integrity"
            echo ""
            exit 0
            ;;
        *)
            error "Unknown command: $1"
            ;;
    esac
}

main "$@"
