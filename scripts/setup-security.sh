#!/bin/bash
# ==============================================================================
# Zambezi Meats - Firewall & Security Setup
# ==============================================================================
# DEP-027: Configure firewall rules
# UFW configured: allow 80, 443, 22; deny others
#
# DEP-028: Set up fail2ban
# Fail2ban protecting SSH and web services
# ==============================================================================
#
# Run this script as root:
#   sudo bash setup-security.sh
#
# ==============================================================================

set -e

echo "=============================================="
echo "  Zambezi Meats - Security Setup"
echo "=============================================="
echo ""

# ==============================================================================
# UFW Firewall Configuration
# ==============================================================================

setup_firewall() {
    echo "Setting up UFW firewall..."
    
    # Install UFW if not present
    apt-get update
    apt-get install -y ufw
    
    # Reset to defaults
    ufw --force reset
    
    # Default policies
    ufw default deny incoming
    ufw default allow outgoing
    
    # Allow SSH (rate limited)
    ufw limit 22/tcp comment 'SSH - rate limited'
    
    # Allow HTTP and HTTPS
    ufw allow 80/tcp comment 'HTTP'
    ufw allow 443/tcp comment 'HTTPS'
    
    # Allow LiteSpeed WebAdmin (optional, comment out if not needed)
    # ufw allow 7080/tcp comment 'LiteSpeed WebAdmin'
    
    # Allow MySQL only from localhost (default, but explicit)
    # ufw allow from 127.0.0.1 to any port 3306 comment 'MySQL localhost only'
    
    # Allow Redis only from localhost
    # ufw allow from 127.0.0.1 to any port 6379 comment 'Redis localhost only'
    
    # Enable UFW
    ufw --force enable
    
    # Show status
    ufw status verbose
    
    echo "✓ Firewall configured"
}

# ==============================================================================
# Fail2ban Configuration
# ==============================================================================

setup_fail2ban() {
    echo ""
    echo "Setting up Fail2ban..."
    
    # Install fail2ban
    apt-get install -y fail2ban
    
    # Create local configuration
    cat > /etc/fail2ban/jail.local << 'EOF'
# ==============================================================================
# Zambezi Meats - Fail2ban Configuration
# ==============================================================================

[DEFAULT]
# Ban time: 10 minutes initially
bantime = 10m

# Increase ban time for repeat offenders
bantime.increment = true
bantime.factor = 2
bantime.maxtime = 1w

# Find time window: 10 minutes
findtime = 10m

# Max retries before ban
maxretry = 5

# Email notifications (configure your email)
# destemail = security@zambezimeats.com.au
# sender = fail2ban@zambezimeats.com.au
# action = %(action_mwl)s

# ==============================================================================
# SSH Protection
# ==============================================================================

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 1h

# ==============================================================================
# OpenLiteSpeed Protection
# ==============================================================================

[openlitespeed]
enabled = true
port = http,https
filter = openlitespeed
logpath = /var/www/zambezimeats/logs/error.log
maxretry = 10
bantime = 30m

# ==============================================================================
# Laravel Auth Protection (API rate limiting)
# ==============================================================================

[laravel-auth]
enabled = true
port = http,https
filter = laravel-auth
logpath = /var/www/zambezimeats/logs/laravel.log
maxretry = 5
bantime = 15m

# ==============================================================================
# Generic Bad Bot Protection
# ==============================================================================

[apache-badbots]
enabled = true
port = http,https
filter = apache-badbots
logpath = /var/www/zambezimeats/logs/access.log
maxretry = 2
bantime = 1d

# ==============================================================================
# Prevent DOS (too many requests)
# ==============================================================================

[http-dos]
enabled = true
port = http,https
filter = http-dos
logpath = /var/www/zambezimeats/logs/access.log
maxretry = 300
findtime = 1m
bantime = 5m
EOF

    # Create Laravel auth filter
    cat > /etc/fail2ban/filter.d/laravel-auth.conf << 'EOF'
# Fail2ban filter for Laravel authentication failures

[Definition]
failregex = ^.*"POST \/api\/v1\/auth\/login HTTP.*" 401.*$
            ^.*Failed login attempt from <HOST>.*$
            ^.*Authentication failed for.*from <HOST>.*$
ignoreregex =
EOF

    # Create OpenLiteSpeed filter
    cat > /etc/fail2ban/filter.d/openlitespeed.conf << 'EOF'
# Fail2ban filter for OpenLiteSpeed

[Definition]
failregex = ^<HOST> .* "(GET|POST|HEAD).*" (400|401|403|404|405|444) .*$
ignoreregex =
EOF

    # Create HTTP-DOS filter
    cat > /etc/fail2ban/filter.d/http-dos.conf << 'EOF'
# Fail2ban filter for HTTP DOS attacks

[Definition]
failregex = ^<HOST> .*"(GET|POST|HEAD).*
ignoreregex =
EOF

    # Restart fail2ban
    systemctl restart fail2ban
    systemctl enable fail2ban
    
    # Show status
    fail2ban-client status
    
    echo "✓ Fail2ban configured"
}

# ==============================================================================
# Additional Security Hardening
# ==============================================================================

security_hardening() {
    echo ""
    echo "Applying additional security hardening..."
    
    # Secure shared memory
    if ! grep -q "tmpfs /run/shm" /etc/fstab; then
        echo "tmpfs /run/shm tmpfs defaults,noexec,nosuid 0 0" >> /etc/fstab
    fi
    
    # Disable root SSH login (ensure you have another sudo user!)
    # sed -i 's/^PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
    
    # Disable password authentication (ensure you have SSH keys!)
    # sed -i 's/^PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
    
    # Restrict SSH to specific users (uncomment and modify as needed)
    # echo "AllowUsers deploy admin" >> /etc/ssh/sshd_config
    
    # Set secure permissions on cron
    chmod 700 /etc/cron.d
    chmod 700 /etc/cron.daily
    chmod 700 /etc/cron.hourly
    chmod 700 /etc/cron.monthly
    chmod 700 /etc/cron.weekly
    
    # Secure /tmp
    if ! grep -q "tmpfs /tmp" /etc/fstab; then
        echo "tmpfs /tmp tmpfs defaults,noexec,nosuid,nodev 0 0" >> /etc/fstab
    fi
    
    echo "✓ Additional hardening applied"
}

# ==============================================================================
# Install Security Tools
# ==============================================================================

install_security_tools() {
    echo ""
    echo "Installing security tools..."
    
    apt-get install -y \
        rkhunter \
        chkrootkit \
        lynis \
        clamav \
        clamav-daemon
    
    # Update ClamAV database
    freshclam || true
    
    # Run initial scans (optional, can be time-consuming)
    # rkhunter --update
    # rkhunter --check --skip-keypress
    
    echo "✓ Security tools installed"
}

# ==============================================================================
# Main
# ==============================================================================

main() {
    # Check if running as root
    if [ "$EUID" -ne 0 ]; then
        echo "Please run as root (sudo)"
        exit 1
    fi
    
    setup_firewall
    setup_fail2ban
    security_hardening
    # install_security_tools  # Uncomment to install additional tools
    
    echo ""
    echo "=============================================="
    echo "  Security Setup Complete!"
    echo "=============================================="
    echo ""
    echo "Next steps:"
    echo "  1. Review /etc/fail2ban/jail.local"
    echo "  2. Test SSH access before logging out"
    echo "  3. Configure email notifications in fail2ban"
    echo "  4. Run: ufw status verbose"
    echo "  5. Run: fail2ban-client status"
    echo ""
}

# Parse arguments
case "${1:-install}" in
    install)
        main
        ;;
    firewall)
        setup_firewall
        ;;
    fail2ban)
        setup_fail2ban
        ;;
    status)
        echo "UFW Status:"
        ufw status verbose
        echo ""
        echo "Fail2ban Status:"
        fail2ban-client status
        ;;
    --help|-h)
        echo "Usage: $0 [install|firewall|fail2ban|status]"
        exit 0
        ;;
    *)
        echo "Unknown command: $1"
        exit 1
        ;;
esac
