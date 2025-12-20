#!/bin/bash
# ==============================================================================
# Zambezi Meats - Rollback Script
# ==============================================================================
# DEP-026: Create rollback procedure
# Documented rollback steps, previous version backup
# ==============================================================================

set -e

# Configuration
APP_NAME="zambezimeats"
BASE_DIR="/var/www/${APP_NAME}"
BACKEND_DIR="${BASE_DIR}/backend"
FRONTEND_DIR="${BASE_DIR}/frontend"
RELEASES_DIR="${BACKEND_DIR}/releases"
FRONTEND_RELEASES_DIR="${FRONTEND_DIR}/releases"
LOG_FILE="${BASE_DIR}/logs/rollback-$(date +%Y%m%d-%H%M%S).log"

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
# List Available Releases
# ==============================================================================

list_releases() {
    echo ""
    echo "Available releases:"
    echo "-------------------"
    
    CURRENT_RELEASE=$(readlink -f "${BACKEND_DIR}/current" | xargs basename)
    
    for release in $(ls -dt ${RELEASES_DIR}/*/); do
        RELEASE_NAME=$(basename "$release")
        if [ "$RELEASE_NAME" == "$CURRENT_RELEASE" ]; then
            echo -e "  ${GREEN}* ${RELEASE_NAME}${NC} (current)"
        else
            echo "    ${RELEASE_NAME}"
        fi
    done
    echo ""
}

# ==============================================================================
# Rollback to Previous Release
# ==============================================================================

rollback_to_previous() {
    log "Rolling back to previous release..."
    
    # Get current and previous release
    CURRENT_RELEASE=$(readlink -f "${BACKEND_DIR}/current" | xargs basename)
    RELEASES=($(ls -dt ${RELEASES_DIR}/*/ | head -5))
    
    if [ ${#RELEASES[@]} -lt 2 ]; then
        error "No previous release available for rollback"
    fi
    
    PREVIOUS_RELEASE=$(basename "${RELEASES[1]}")
    
    log "Current release: ${CURRENT_RELEASE}"
    log "Rolling back to: ${PREVIOUS_RELEASE}"
    
    rollback_to_release "$PREVIOUS_RELEASE"
}

# ==============================================================================
# Rollback to Specific Release
# ==============================================================================

rollback_to_release() {
    TARGET_RELEASE="$1"
    
    if [ -z "$TARGET_RELEASE" ]; then
        error "Please specify a release to rollback to"
    fi
    
    TARGET_DIR="${RELEASES_DIR}/${TARGET_RELEASE}"
    TARGET_FRONTEND_DIR="${FRONTEND_RELEASES_DIR}/${TARGET_RELEASE}"
    
    if [ ! -d "$TARGET_DIR" ]; then
        error "Release not found: ${TARGET_RELEASE}"
    fi
    
    log "Rolling back to release: ${TARGET_RELEASE}"
    
    # Create database backup before rollback
    log "Creating database backup before rollback..."
    backup_database
    
    # Update symlinks
    log "Updating symlinks..."
    ln -sfn "$TARGET_DIR" "${BACKEND_DIR}/current"
    
    if [ -d "$TARGET_FRONTEND_DIR" ]; then
        ln -sfn "$TARGET_FRONTEND_DIR" "${FRONTEND_DIR}/current"
    fi
    
    # Clear caches
    log "Clearing caches..."
    cd "${BACKEND_DIR}/current"
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Rebuild caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Restart queue workers
    log "Restarting queue workers..."
    php artisan queue:restart
    
    # Restart services
    log "Restarting services..."
    if systemctl is-active --quiet php8.2-fpm; then
        sudo systemctl reload php8.2-fpm
    fi
    
    if [ -f /usr/local/lsws/bin/lswsctrl ]; then
        sudo /usr/local/lsws/bin/lswsctrl restart
    fi
    
    success "Rollback to ${TARGET_RELEASE} completed"
}

# ==============================================================================
# Database Backup
# ==============================================================================

backup_database() {
    BACKUP_DIR="${BASE_DIR}/backups/rollback"
    BACKUP_FILE="${BACKUP_DIR}/pre-rollback-$(date +%Y%m%d-%H%M%S).sql"
    
    mkdir -p "$BACKUP_DIR"
    
    # Source environment variables
    source "${BASE_DIR}/shared/.env"
    
    mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"
    gzip "$BACKUP_FILE"
    
    success "Database backup created: ${BACKUP_FILE}.gz"
}

# ==============================================================================
# Migrate Database Rollback
# ==============================================================================

rollback_database() {
    STEPS="${1:-1}"
    
    warning "Rolling back database migrations (${STEPS} steps)..."
    
    cd "${BACKEND_DIR}/current"
    
    php artisan migrate:rollback --step="$STEPS" --force
    
    success "Database migrations rolled back"
}

# ==============================================================================
# Emergency Maintenance Mode
# ==============================================================================

enable_maintenance() {
    log "Enabling maintenance mode..."
    
    cd "${BACKEND_DIR}/current"
    php artisan down --render="errors.503" --retry=60
    
    success "Maintenance mode enabled"
}

disable_maintenance() {
    log "Disabling maintenance mode..."
    
    cd "${BACKEND_DIR}/current"
    php artisan up
    
    success "Maintenance mode disabled"
}

# ==============================================================================
# Main
# ==============================================================================

main() {
    echo ""
    echo "=============================================="
    echo "  Zambezi Meats - Rollback Tool"
    echo "=============================================="
    echo ""
    
    mkdir -p "${BASE_DIR}/logs"
    
    case "${1:-list}" in
        list)
            list_releases
            ;;
        previous)
            rollback_to_previous
            ;;
        to)
            rollback_to_release "$2"
            ;;
        db)
            rollback_database "${2:-1}"
            ;;
        down)
            enable_maintenance
            ;;
        up)
            disable_maintenance
            ;;
        --help|-h)
            echo "Usage: $0 [command] [options]"
            echo ""
            echo "Commands:"
            echo "  list              List available releases (default)"
            echo "  previous          Rollback to the previous release"
            echo "  to <release>      Rollback to a specific release"
            echo "  db [steps]        Rollback database migrations"
            echo "  down              Enable maintenance mode"
            echo "  up                Disable maintenance mode"
            echo ""
            exit 0
            ;;
        *)
            # If argument looks like a release (numbers only), roll back to it
            if [[ "$1" =~ ^[0-9]+$ ]]; then
                rollback_to_release "$1"
            else
                error "Unknown command: $1"
            fi
            ;;
    esac
}

main "$@"
