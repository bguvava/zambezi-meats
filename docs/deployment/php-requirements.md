# PHP Requirements and Extensions

## Overview

This document outlines the PHP requirements and necessary extensions for running the Zambezi Meats application.

## PHP Version

- **Minimum PHP Version**: 8.2.0
- **Recommended PHP Version**: 8.3.x

## Required PHP Extensions

The following PHP extensions are required for the application to function correctly:

### Core Extensions

1. **BCMath** - For arbitrary precision mathematics

   - Used for: Currency calculations, precise decimal operations

2. **Ctype** - Character type checking

   - Used for: String validation, data sanitization

3. **cURL** - Client URL library

   - Used for: External API calls, payment gateway integration

4. **DOM** - Document Object Model

   - Used for: XML processing, PDF generation

5. **Fileinfo** - File information

   - Used for: File type detection, MIME type validation

6. **GD** - Graphics Drawing library ⚠️ **CRITICAL FOR IMAGE PROCESSING**

   - Used for: Image uploads, thumbnail generation, image optimization
   - **Impact if missing**:
     - Product image uploads will fail
     - Category image uploads will fail
     - Image validation tests will fail (7 tests)
   - **Installation**:

     ```bash
     # Ubuntu/Debian
     sudo apt-get install php8.2-gd

     # CentOS/RHEL
     sudo yum install php82-gd

     # macOS (Homebrew)
     brew install php@8.2-gd

     # Windows (XAMPP)
     # Edit php.ini and uncomment:
     extension=gd
     ```

   - **Verification**:
     ```bash
     php -m | grep -i gd
     ```

7. **JSON** - JavaScript Object Notation

   - Used for: API responses, data serialization

8. **Mbstring** - Multibyte string functions

   - Used for: UTF-8 string handling, internationalization

9. **OpenSSL** - Secure Sockets Layer

   - Used for: HTTPS connections, encryption, secure sessions

10. **PDO** - PHP Data Objects

    - Used for: Database abstraction layer
    - **Required PDO Drivers**:
      - `pdo_mysql` - MySQL database driver

11. **Tokenizer** - PHP tokenizer

    - Used for: Code parsing, Laravel internals

12. **XML** - XML processing
    - Used for: XML parsing, SOAP services

## Optional But Recommended Extensions

1. **Redis** - In-memory data structure store

   - Used for: Session storage, cache, queue driver
   - **Installation**:

     ```bash
     # Ubuntu/Debian
     sudo apt-get install php8.2-redis

     # CentOS/RHEL
     sudo yum install php82-redis

     # PECL
     pecl install redis
     ```

2. **OPcache** - Opcode cache

   - Used for: PHP performance optimization
   - **Configuration** (php.ini):
     ```ini
     opcache.enable=1
     opcache.memory_consumption=128
     opcache.interned_strings_buffer=8
     opcache.max_accelerated_files=10000
     opcache.revalidate_freq=2
     ```

3. **Imagick** - ImageMagick PHP extension (alternative to GD)
   - Used for: Advanced image processing
   - **Note**: GD is sufficient for current requirements

## PHP Configuration

### Required php.ini Settings

```ini
# Memory limit
memory_limit = 256M

# Maximum execution time
max_execution_time = 60

# Upload limits
upload_max_filesize = 10M
post_max_size = 12M

# Session settings
session.driver = cookie
session.lifetime = 120

# Timezone
date.timezone = Africa/Harare
```

### Development Environment

```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /path/to/php-error.log
```

### Production Environment

```ini
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php/error.log
```

## Verification Script

Create a `check-requirements.php` file to verify all extensions are installed:

```php
<?php

$required = [
    'bcmath',
    'ctype',
    'curl',
    'dom',
    'fileinfo',
    'gd',
    'json',
    'mbstring',
    'openssl',
    'pdo',
    'pdo_mysql',
    'tokenizer',
    'xml',
];

$optional = [
    'redis',
    'Zend OPcache',
    'imagick',
];

echo "=== Required PHP Extensions ===\n\n";

foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '✗';
    echo sprintf("%s %s\n", $status, $ext);

    if (!$loaded) {
        echo "   ERROR: Required extension '$ext' is not loaded!\n";
    }
}

echo "\n=== Optional PHP Extensions ===\n\n";

foreach ($optional as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '-';
    echo sprintf("%s %s\n", $status, $ext);
}

echo "\n=== PHP Version ===\n\n";
echo "PHP Version: " . PHP_VERSION . "\n";

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "✗ ERROR: PHP 8.2.0 or higher is required!\n";
} else {
    echo "✓ PHP version is compatible\n";
}

echo "\n=== PHP Configuration ===\n\n";

$config = [
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'date.timezone' => ini_get('date.timezone'),
];

foreach ($config as $key => $value) {
    echo sprintf("%s = %s\n", $key, $value);
}
```

## Testing Image Processing

After installing GD extension, run the following tests to verify image processing:

```bash
# Test product image upload
php artisan test --filter="test_admin_can_create_product_with_images"

# Test category image upload
php artisan test --filter="test_admin_can_create_category_with_image"

# Test image validation
php artisan test --filter="test_create_product_validates_image"

# Run all image-related tests
php artisan test --group=images
```

## Troubleshooting

### GD Extension Not Loading

1. **Check if extension is installed**:

   ```bash
   php -m | grep gd
   ```

2. **Check php.ini location**:

   ```bash
   php --ini
   ```

3. **Verify extension path**:

   ```bash
   php -i | grep extension_dir
   ```

4. **Check php.ini for extension**:

   ```bash
   grep -i "extension=gd" /path/to/php.ini
   ```

5. **Restart web server**:

   ```bash
   # Apache
   sudo systemctl restart apache2

   # Nginx with PHP-FPM
   sudo systemctl restart php8.2-fpm
   sudo systemctl restart nginx
   ```

### Image Upload Failures

If image uploads fail even with GD installed:

1. Check storage permissions:

   ```bash
   chmod -R 775 storage/
   chown -R www-data:www-data storage/
   ```

2. Verify upload directory exists:

   ```bash
   mkdir -p storage/app/public/products
   mkdir -p storage/app/public/categories
   ```

3. Check Laravel storage link:

   ```bash
   php artisan storage:link
   ```

4. Review logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Deployment Checklist

Before deploying to production, verify:

- [ ] PHP 8.2+ is installed
- [ ] All required extensions are loaded
- [ ] GD extension is enabled and working
- [ ] php.ini settings are configured correctly
- [ ] Image processing tests pass
- [ ] Storage directories have correct permissions
- [ ] Storage link is created
- [ ] OPcache is enabled in production
- [ ] Redis is configured (optional)
- [ ] Error logging is properly configured

## References

- [Laravel 12.x Server Requirements](https://laravel.com/docs/12.x/deployment#server-requirements)
- [PHP GD Documentation](https://www.php.net/manual/en/book.image.php)
- [Laravel File Storage](https://laravel.com/docs/12.x/filesystem)

## Support

For issues related to PHP extensions or configuration, please:

1. Check the troubleshooting section above
2. Review Laravel logs (`storage/logs/laravel.log`)
3. Review PHP error logs
4. Contact the development team with:
   - PHP version (`php -v`)
   - Loaded extensions (`php -m`)
   - php.ini configuration
   - Error messages from logs
