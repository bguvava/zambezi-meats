# Zambezi Meats - Deployment Documentation

## Overview

This document provides complete instructions for deploying the Zambezi Meats Online Butchery Store to a production environment on a CyberPanel VPS with OpenLiteSpeed.

**Table of Contents:**

1. [Server Requirements](#server-requirements)
2. [Initial Server Setup](#initial-server-setup)
3. [Application Deployment](#application-deployment)
4. [Configuration](#configuration)
5. [SSL & Security](#ssl--security)
6. [Database Setup](#database-setup)
7. [Monitoring & Alerts](#monitoring--alerts)
8. [Backup & Recovery](#backup--recovery)
9. [Maintenance](#maintenance)
10. [Troubleshooting](#troubleshooting)

---

## Server Requirements

| Component | Minimum          | Recommended      |
| --------- | ---------------- | ---------------- |
| OS        | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |
| RAM       | 4GB              | 8GB              |
| CPU       | 2 vCPUs          | 4 vCPUs          |
| Storage   | 50GB SSD         | 100GB SSD        |
| PHP       | 8.2+             | 8.3              |
| MySQL     | 8.0              | 8.0              |
| Node.js   | 18 LTS           | 20 LTS           |

### Required PHP Extensions

```bash
php -m | grep -E 'bcmath|ctype|curl|dom|fileinfo|gd|json|mbstring|openssl|pdo|pdo_mysql|redis|tokenizer|xml|zip'
```

Required:

- bcmath, ctype, curl, dom, fileinfo
- gd (or imagick), json, mbstring
- openssl, pdo, pdo_mysql, redis
- tokenizer, xml, zip

---

## Initial Server Setup

### 1. Provision VPS (DEP-001)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common
```

### 2. Install CyberPanel (DEP-002)

```bash
# Download and install CyberPanel
sh <(curl https://cyberpanel.net/install.sh || wget -O - https://cyberpanel.net/install.sh)

# Select options:
# - OpenLiteSpeed (option 1)
# - Full installation with all features
# - Include Redis
# - Set admin password
```

### 3. Configure Domain DNS (DEP-003)

Create DNS records pointing to your server IP:

| Type | Host | Value          | TTL |
| ---- | ---- | -------------- | --- |
| A    | @    | YOUR_SERVER_IP | 300 |
| A    | www  | YOUR_SERVER_IP | 300 |
| A    | api  | YOUR_SERVER_IP | 300 |

### 4. Create Website in CyberPanel

1. Login to CyberPanel (https://your-server-ip:8090)
2. Go to **Websites > Create Website**
3. Enter domain: `zambezimeats.com.au`
4. Select PHP 8.2
5. Enable SSL (Let's Encrypt)

---

## Application Deployment

### 5. Create Directory Structure (DEP-008)

```bash
# Create deployment structure
sudo mkdir -p /var/www/zambezimeats/{backend,frontend,shared,scripts,logs,backups}
sudo mkdir -p /var/www/zambezimeats/backend/releases
sudo mkdir -p /var/www/zambezimeats/frontend/releases
sudo mkdir -p /var/www/zambezimeats/shared/storage/{app/public,logs,framework/{cache,sessions,views}}
sudo mkdir -p /var/www/zambezimeats/backups/{daily,weekly,monthly}/{database,files}

# Set ownership
sudo chown -R www-data:www-data /var/www/zambezimeats
sudo chmod -R 775 /var/www/zambezimeats/shared/storage
```

### 6. Configure Git Access (DEP-007)

```bash
# Generate SSH key for deployment
sudo -u www-data ssh-keygen -t ed25519 -C "deploy@zambezimeats.com.au" -f /var/www/zambezimeats/.ssh/id_ed25519

# Add public key to GitHub deploy keys
cat /var/www/zambezimeats/.ssh/id_ed25519.pub
# Copy and add to GitHub > Repository > Settings > Deploy keys

# Test connection
sudo -u www-data ssh -T git@github.com
```

### 7. Deploy Application (DEP-010)

```bash
# Copy deployment scripts
sudo cp scripts/*.sh /var/www/zambezimeats/scripts/
sudo chmod +x /var/www/zambezimeats/scripts/*.sh

# First deployment
cd /var/www/zambezimeats/scripts
sudo -u www-data ./deploy.sh
```

---

## Configuration

### 8. Environment Variables (DEP-009)

```bash
# Copy production environment template
sudo cp /var/www/zambezimeats/backend/current/.env.production.example \
    /var/www/zambezimeats/shared/.env

# Edit with production values
sudo nano /var/www/zambezimeats/shared/.env
```

**Critical settings to update:**

```env
APP_KEY=base64:GENERATE_NEW_KEY
APP_URL=https://zambezimeats.com.au
APP_DEBUG=false

DB_PASSWORD=your_secure_password

STRIPE_KEY=pk_live_xxxxx
STRIPE_SECRET=sk_live_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx

MAIL_PASSWORD=your_mailgun_api_key

SENTRY_LARAVEL_DSN=https://xxxxx@sentry.io/xxxxx
```

Generate new APP_KEY:

```bash
cd /var/www/zambezimeats/backend/current
php artisan key:generate --show
```

### 9. PHP Configuration (DEP-005)

```bash
# Copy PHP configuration
sudo cp server/php/99-zambezimeats.ini /etc/php/8.2/fpm/conf.d/

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 10. OpenLiteSpeed Configuration (DEP-016)

1. Go to CyberPanel > Websites > your-domain > vHost Conf
2. Paste contents from `server/openlitespeed/vhconf.conf`
3. Restart LiteSpeed: `/usr/local/lsws/bin/lswsctrl restart`

### 11. Queue Workers (DEP-012)

```bash
# Install supervisor
sudo apt install -y supervisor

# Copy configuration
sudo cp server/supervisor/zambezimeats-worker.conf /etc/supervisor/conf.d/

# Start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start zambezimeats:*
```

### 12. Cron Jobs (DEP-013)

```bash
# Copy cron configuration
sudo cp server/cron/zambezimeats-cron /etc/cron.d/zambezimeats
sudo chmod 644 /etc/cron.d/zambezimeats
```

---

## SSL & Security

### 13. SSL Certificate (DEP-004)

CyberPanel handles this automatically with Let's Encrypt. To verify:

```bash
# Check SSL certificate
curl -vI https://zambezimeats.com.au 2>&1 | grep -E 'SSL|subject|expire'

# Test SSL grade (should be A+)
# Visit: https://www.ssllabs.com/ssltest/
```

### 14. Firewall & Security (DEP-027, DEP-028)

```bash
# Run security setup script
sudo bash /var/www/zambezimeats/scripts/setup-security.sh install

# Verify firewall
sudo ufw status verbose

# Verify fail2ban
sudo fail2ban-client status
```

---

## Database Setup

### 15. Create Database (DEP-006)

```bash
# Create database and user
sudo mysql -u root -p << EOF
CREATE DATABASE IF NOT EXISTS my_zambezimeats
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'zambezi_user'@'localhost'
  IDENTIFIED BY 'YOUR_SECURE_PASSWORD';

GRANT ALL PRIVILEGES ON my_zambezimeats.* TO 'zambezi_user'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 16. Run Migrations (DEP-017)

```bash
cd /var/www/zambezimeats/backend/current
php artisan migrate --force
```

### 17. Run Seeders (DEP-018)

```bash
php artisan db:seed --force

# Seed specific seeders if needed
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=DeliveryZoneSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
```

### 18. MySQL Optimization (DEP-006)

```bash
# Copy MySQL configuration
sudo cp server/mysql/99-zambezimeats.cnf /etc/mysql/mysql.conf.d/

# Restart MySQL
sudo systemctl restart mysql
```

---

## Monitoring & Alerts

### 19. Error Monitoring (DEP-021)

1. Create account at [Sentry.io](https://sentry.io)
2. Create new Laravel project
3. Copy DSN to `.env`: `SENTRY_LARAVEL_DSN=https://xxxxx@sentry.io/xxxxx`
4. Install Sentry SDK (already in composer.json)

### 20. Uptime Monitoring (DEP-022)

1. Create account at [UptimeRobot](https://uptimerobot.com)
2. Add monitors:
   - HTTP monitor: `https://zambezimeats.com.au`
   - API monitor: `https://zambezimeats.com.au/api/v1/health`

### 21. Log Rotation (DEP-020)

```bash
# Laravel logs are automatically rotated daily
# Additional logrotate config for other logs:

cat << 'EOF' | sudo tee /etc/logrotate.d/zambezimeats
/var/www/zambezimeats/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    sharedscripts
    postrotate
        /usr/local/lsws/bin/lswsctrl restart > /dev/null 2>&1 || true
    endscript
}
EOF
```

---

## Backup & Recovery

### 22. Configure Backups (DEP-023, DEP-024)

Backups are automated via cron. Manual backup:

```bash
# Run manual backup
/var/www/zambezimeats/scripts/backup.sh auto

# List available backups
/var/www/zambezimeats/scripts/backup.sh list
```

### 23. Test Backup Restore

```bash
# Test database restore (creates backup first)
/var/www/zambezimeats/scripts/backup.sh restore-db /path/to/backup.sql.gz

# Test file restore
/var/www/zambezimeats/scripts/backup.sh restore-files /path/to/backup.tar.gz
```

---

## Maintenance

### Deploying Updates

```bash
cd /var/www/zambezimeats/scripts
sudo -u www-data ./deploy.sh
```

### Rollback Deployment (DEP-026)

```bash
# List available releases
./rollback.sh list

# Rollback to previous release
./rollback.sh previous

# Rollback to specific release
./rollback.sh to 20241213120000
```

### Maintenance Mode

```bash
# Enable maintenance mode
cd /var/www/zambezimeats/backend/current
php artisan down --render="errors.503"

# Disable maintenance mode
php artisan up
```

### Cache Management

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize
```

---

## Troubleshooting

### Common Issues

#### 1. 502 Bad Gateway

```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

#### 2. Queue Jobs Not Processing

```bash
# Check supervisor status
sudo supervisorctl status zambezimeats:*

# Restart workers
sudo supervisorctl restart zambezimeats:*

# Check worker logs
tail -f /var/www/zambezimeats/logs/worker.log
```

#### 3. Storage Permission Issues

```bash
# Fix storage permissions
sudo chown -R www-data:www-data /var/www/zambezimeats/shared/storage
sudo chmod -R 775 /var/www/zambezimeats/shared/storage
```

#### 4. Database Connection Issues

```bash
# Test connection
mysql -u zambezi_user -p my_zambezimeats -e "SELECT 1"

# Check MySQL status
sudo systemctl status mysql
```

### Logs to Check

| Log           | Location                                                |
| ------------- | ------------------------------------------------------- |
| Laravel       | `/var/www/zambezimeats/shared/storage/logs/laravel.log` |
| PHP-FPM       | `/var/log/php8.2-fpm.log`                               |
| MySQL         | `/var/log/mysql/error.log`                              |
| OpenLiteSpeed | `/var/www/zambezimeats/logs/error.log`                  |
| Queue Workers | `/var/www/zambezimeats/logs/worker.log`                 |
| Deployment    | `/var/www/zambezimeats/logs/deploy-*.log`               |

### Health Check

```bash
# Quick health check
curl -s https://zambezimeats.com.au/api/v1/health

# Detailed health check
curl -s https://zambezimeats.com.au/api/v1/health/detailed | jq
```

---

## Deployment Checklist

Before going live, verify:

- [ ] SSL certificate installed and valid
- [ ] Environment variables configured
- [ ] Database migrated and seeded
- [ ] Queue workers running
- [ ] Cron jobs configured
- [ ] Backups configured and tested
- [ ] Firewall rules configured
- [ ] Fail2ban active
- [ ] Error monitoring configured
- [ ] Uptime monitoring configured
- [ ] Smoke tests passing

Run smoke tests:

```bash
cd /var/www/zambezimeats/backend/current
php artisan test --testsuite=Production
```

---

## Contact

For deployment support, contact:

- Developer: bguvava (www.bguvava.com)
- Project: Zambezi Meats Online Butchery Store
