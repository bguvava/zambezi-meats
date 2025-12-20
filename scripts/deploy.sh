#!/bin/bash
# ==============================================================================
# Zambezi Meats - Production Deployment Script
# ==============================================================================
# DEP-025: Create deployment script
# Shell script for pull, build, deploy, cache clear
# ==============================================================================

set -e  # Exit on any error

# Configuration
DEPLOY_USER="${DEPLOY_USER:-www-data}"
APP_NAME="zambezimeats"
BASE_DIR="/var/www/${APP_NAME}"
BACKEND_DIR="${BASE_DIR}/backend"
FRONTEND_DIR="${BASE_DIR}/frontend"
SHARED_DIR="${BASE_DIR}/shared"
RELEASES_DIR="${BACKEND_DIR}/releases"
FRONTEND_RELEASES_DIR="${FRONTEND_DIR}/releases"
SCRIPTS_DIR="${BASE_DIR}/scripts"
LOG_FILE="${BASE_DIR}/logs/deploy-$(date +%Y%m%d-%H%M%S).log"

# Repository
REPO_URL="${REPO_URL:-git@github.com:bguvava/Zambezi-Meats.git}"
BRANCH="${BRANCH:-main}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ==============================================================================
# Helper Functions
# ==============================================================================

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

check_command() {
    if ! command -v "$1" &> /dev/null; then
        error "$1 is required but not installed."
    fi
}

# ==============================================================================
# Pre-Deployment Checks
# ==============================================================================

pre_deploy_checks() {
    log "Running pre-deployment checks..."
    
    # Check required commands
    check_command git
    check_command php
    check_command composer
    check_command npm
    check_command mysql
    
    # Check PHP version
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    if (( $(echo "$PHP_VERSION < 8.2" | bc -l) )); then
        error "PHP 8.2+ required. Found: $PHP_VERSION"
    fi
    
    # Check Node version
    NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
    if (( NODE_VERSION < 18 )); then
        error "Node.js 18+ required. Found: v$NODE_VERSION"
    fi
    
    # Check directories exist
    if [ ! -d "$BASE_DIR" ]; then
        error "Base directory does not exist: $BASE_DIR"
    fi
    
    # Check .env exists
    if [ ! -f "${SHARED_DIR}/.env" ]; then
        error "Production .env file not found at ${SHARED_DIR}/.env"
    fi
    
    success "Pre-deployment checks passed"
}

# ==============================================================================
# Create Release
# ==============================================================================

create_release() {
    RELEASE_VERSION=$(date +%Y%m%d%H%M%S)
    RELEASE_DIR="${RELEASES_DIR}/${RELEASE_VERSION}"
    FRONTEND_RELEASE_DIR="${FRONTEND_RELEASES_DIR}/${RELEASE_VERSION}"
    
    log "Creating new release: ${RELEASE_VERSION}"
    
    # Create release directories
    mkdir -p "$RELEASE_DIR"
    mkdir -p "$FRONTEND_RELEASE_DIR"
    
    success "Release directories created"
}

# ==============================================================================
# Clone/Update Repository
# ==============================================================================

fetch_code() {
    log "Fetching latest code from ${BRANCH}..."
    
    REPO_DIR="${BASE_DIR}/repo"
    
    if [ -d "$REPO_DIR/.git" ]; then
        cd "$REPO_DIR"
        git fetch origin
        git checkout "$BRANCH"
        git reset --hard "origin/${BRANCH}"
    else
        git clone "$REPO_URL" "$REPO_DIR"
        cd "$REPO_DIR"
        git checkout "$BRANCH"
    fi
    
    # Copy to release directory
    rsync -a --exclude='.git' --exclude='node_modules' --exclude='vendor' \
        "${REPO_DIR}/backend/" "$RELEASE_DIR/"
    
    rsync -a --exclude='.git' --exclude='node_modules' \
        "${REPO_DIR}/frontend/" "$FRONTEND_RELEASE_DIR/"
    
    success "Code fetched and copied to release"
}

# ==============================================================================
# Backend Deployment
# ==============================================================================

deploy_backend() {
    log "Deploying Laravel backend..."
    
    cd "$RELEASE_DIR"
    
    # Link shared directories and files
    ln -sf "${SHARED_DIR}/.env" "${RELEASE_DIR}/.env"
    rm -rf "${RELEASE_DIR}/storage"
    ln -sf "${SHARED_DIR}/storage" "${RELEASE_DIR}/storage"
    
    # Install dependencies
    log "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Generate optimized autoload files
    composer dump-autoload --optimize --no-dev
    
    # Run migrations
    log "Running database migrations..."
    php artisan migrate --force
    
    # Clear and rebuild caches
    log "Optimizing Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Link storage
    php artisan storage:link
    
    success "Backend deployed"
}

# ==============================================================================
# Frontend Deployment
# ==============================================================================

deploy_frontend() {
    log "Deploying Vue.js frontend..."
    
    cd "$FRONTEND_RELEASE_DIR"
    
    # Install dependencies
    log "Installing NPM dependencies..."
    npm ci --production=false
    
    # Build for production
    log "Building frontend..."
    npm run build
    
    # Copy built files to public directory
    if [ -d "${RELEASE_DIR}/public/build" ]; then
        rm -rf "${RELEASE_DIR}/public/build"
    fi
    
    cp -r "${FRONTEND_RELEASE_DIR}/dist/"* "${RELEASE_DIR}/public/"
    
    success "Frontend deployed"
}

# ==============================================================================
# Activate Release
# ==============================================================================

activate_release() {
    log "Activating release ${RELEASE_VERSION}..."
    
    # Update symlinks
    ln -sfn "$RELEASE_DIR" "${BACKEND_DIR}/current"
    ln -sfn "$FRONTEND_RELEASE_DIR" "${FRONTEND_DIR}/current"
    
    # Restart queue workers
    log "Restarting queue workers..."
    php "${BACKEND_DIR}/current/artisan" queue:restart
    
    # Restart PHP-FPM (if using)
    if systemctl is-active --quiet php8.2-fpm; then
        sudo systemctl reload php8.2-fpm
    fi
    
    # Restart LiteSpeed
    if [ -f /usr/local/lsws/bin/lswsctrl ]; then
        sudo /usr/local/lsws/bin/lswsctrl restart
    fi
    
    success "Release activated"
}

# ==============================================================================
# Cleanup Old Releases
# ==============================================================================

cleanup_releases() {
    log "Cleaning up old releases..."
    
    KEEP_RELEASES=5
    
    # Backend releases
    cd "$RELEASES_DIR"
    ls -dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
    
    # Frontend releases
    cd "$FRONTEND_RELEASES_DIR"
    ls -dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
    
    success "Cleaned up old releases (keeping last $KEEP_RELEASES)"
}

# ==============================================================================
# Post-Deployment
# ==============================================================================

post_deploy() {
    log "Running post-deployment tasks..."
    
    cd "${BACKEND_DIR}/current"
    
    # Run any pending queue jobs
    php artisan queue:work --once --queue=high,default
    
    # Clear any remaining caches
    php artisan cache:clear
    
    # Send deployment notification (optional)
    # php artisan notify:deployment --release="$RELEASE_VERSION"
    
    success "Post-deployment tasks complete"
}

# ==============================================================================
# Smoke Tests
# ==============================================================================

run_smoke_tests() {
    log "Running smoke tests..."
    
    cd "${BACKEND_DIR}/current"
    
    # Run the production smoke test
    php artisan test --testsuite=Production --stop-on-failure
    
    if [ $? -ne 0 ]; then
        error "Smoke tests failed! Consider rolling back."
    fi
    
    success "Smoke tests passed"
}

# ==============================================================================
# Main Deployment Flow
# ==============================================================================

main() {
    echo ""
    echo "=============================================="
    echo "  Zambezi Meats - Production Deployment"
    echo "=============================================="
    echo ""
    
    # Create log directory
    mkdir -p "${BASE_DIR}/logs"
    
    log "Starting deployment..."
    log "Branch: ${BRANCH}"
    
    # Record start time
    START_TIME=$(date +%s)
    
    # Run deployment steps
    pre_deploy_checks
    create_release
    fetch_code
    deploy_backend
    deploy_frontend
    activate_release
    cleanup_releases
    post_deploy
    run_smoke_tests
    
    # Calculate duration
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    echo ""
    echo "=============================================="
    success "Deployment completed successfully!"
    log "Release: ${RELEASE_VERSION}"
    log "Duration: ${DURATION} seconds"
    echo "=============================================="
    echo ""
}

# ==============================================================================
# Script Entry Point
# ==============================================================================

# Parse arguments
case "${1:-deploy}" in
    deploy)
        main
        ;;
    rollback)
        "${SCRIPTS_DIR}/rollback.sh" "$2"
        ;;
    --help|-h)
        echo "Usage: $0 [deploy|rollback <release>]"
        echo ""
        echo "Commands:"
        echo "  deploy      Deploy the latest code (default)"
        echo "  rollback    Rollback to a previous release"
        echo ""
        exit 0
        ;;
    *)
        error "Unknown command: $1"
        ;;
esac
