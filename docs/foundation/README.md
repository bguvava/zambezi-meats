# Zambezi Meats - Phase 1: Foundation Documentation

## Overview

Zambezi Meats is a premium online butchery e-commerce platform built with Laravel 12 (backend API) and Vue.js 3 (frontend SPA). This documentation covers the foundation phase setup and architecture.

## Technology Stack

### Backend

- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Authentication**: Laravel Sanctum (cookie-based SPA)
- **Database**: MySQL/MariaDB (UTF8MB4)
- **API Versioning**: `/api/v1/` prefix

### Frontend

- **Framework**: Vue.js 3 (Composition API)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with custom theme
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios

## Project Structure

```
Zambezi-Meats/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/
│   │   │   │       └── V1/    # Versioned API controllers
│   │   │   └── Middleware/
│   │   └── Models/            # Eloquent models
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   └── seeders/           # Database seeders
│   └── routes/
│       └── api.php            # API routes
│
├── frontend/                # Vue.js SPA
│   ├── src/
│   │   ├── layouts/           # Layout components
│   │   ├── pages/             # Page components
│   │   ├── router/            # Vue Router config
│   │   ├── services/          # API services
│   │   └── stores/            # Pinia stores
│   └── vite.config.js
│
└── docs/
    └── foundation/            # This documentation
```

## Quick Start

### Prerequisites

- PHP 8.2+ with extensions: pdo_mysql, mbstring, openssl, xml, ctype, json, bcmath, gd
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ or MariaDB 10.6+
- XAMPP or similar local development environment

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_DATABASE=my_zambezimeats
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start development server
php artisan serve
```

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

### Default Credentials

| Role     | Email                     | Password |
| -------- | ------------------------- | -------- |
| Admin    | admin@zambezimeats.com.au | password |
| Staff    | staff@zambezimeats.com.au | password |
| Customer | customer@example.com      | password |

## Database Schema

See [data-dictionary.md](data-dictionary.md) for complete table documentation.

### Entity Relationship Diagram

See [erd.md](erd.md) for the visual database schema.

### Tables Overview

| Table                | Description                                       |
| -------------------- | ------------------------------------------------- |
| users                | User accounts with roles (customer, staff, admin) |
| categories           | Product categories with parent-child hierarchy    |
| products             | Meat products with pricing, stock, and metadata   |
| product_images       | Product image gallery                             |
| addresses            | Customer delivery addresses                       |
| delivery_zones       | Delivery zones with suburbs and fees              |
| orders               | Customer orders with status tracking              |
| order_items          | Individual items in an order                      |
| order_status_history | Order status change history                       |
| payments             | Payment transactions                              |
| inventory_logs       | Stock movement history                            |
| delivery_proofs      | Delivery confirmation with photos/signatures      |
| wishlists            | Customer wishlist items                           |
| notifications        | In-app notifications                              |
| activity_logs        | System activity audit logs                        |
| settings             | Application configuration                         |
| currency_rates       | AUD/USD exchange rates                            |
| promotions           | Discount codes and promotions                     |
| support_tickets      | Customer support tickets                          |
| ticket_replies       | Replies to support tickets                        |

## API Endpoints

### Base URL

```
http://localhost:8000/api/v1
```

### Authentication

Cookie-based Sanctum authentication for SPA.

### Endpoints Structure

```
/api/v1/health                  # Health check (public)
/api/v1/categories              # Categories (public)
/api/v1/products                # Products (public)

/api/v1/auth/login              # Login
/api/v1/auth/register           # Register
/api/v1/auth/logout             # Logout (auth required)

/api/v1/customer/*              # Customer endpoints (auth + customer role)
/api/v1/staff/*                 # Staff endpoints (auth + staff role)
/api/v1/admin/*                 # Admin endpoints (auth + admin role)
```

## Configuration

### Environment Variables

Key environment variables in `.env`:

```env
# Application
APP_NAME="Zambezi Meats"
APP_URL=http://localhost:8000

# Database
DB_DATABASE=my_zambezimeats
DB_USERNAME=root
DB_PASSWORD=

# Session (5 minute timeout)
SESSION_LIFETIME=5

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173
SESSION_DOMAIN=localhost

# Frontend URL
FRONTEND_URL=http://localhost:5173
```

### Tailwind Theme

Custom brand colors in `frontend/src/style.css`:

```css
--color-primary: #8b0000; /* Dark red */
--color-secondary: #2c1810; /* Dark brown */
--color-accent: #f5f5dc; /* Beige */
```

## Security Features

1. **CSRF Protection**: All state-changing requests require CSRF token
2. **Session Timeout**: 5-minute inactivity auto-logout
3. **Role-based Access**: Customer, Staff, Admin roles
4. **Input Validation**: Server-side validation on all inputs
5. **Password Hashing**: Bcrypt hashing via Laravel

## Business Rules

1. **Minimum Order**: $100 AUD minimum order value
2. **Currency**: AUD (default) and USD supported
3. **Delivery**: Zone-based delivery with free delivery thresholds
4. **Delivery Days**: Tuesday, Thursday, Saturday
5. **Time Slots**: 8AM-12PM, 12PM-4PM, 4PM-8PM

## Next Steps

After foundation setup, proceed to:

1. **Part 2**: Authentication API (login, register, password reset)
2. **Part 3**: Product Management API
3. **Part 4**: Order Management API
4. **Part 5**: Payment Integration (Stripe, PayPal)
5. **Part 6**: Delivery Management
6. **Part 7**: Admin Panel
7. **Part 8**: Customer Frontend
8. **Part 9**: Testing & Deployment

## Troubleshooting

### Common Issues

**CORS Errors**

- Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
- Check `config/cors.php` allows your frontend origin

**Session Issues**

- Clear browser cookies
- Run `php artisan config:clear` and `php artisan cache:clear`

**Database Connection**

- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists

**Vite Proxy Issues**

- Ensure backend is running on port 8000
- Check `vite.config.js` proxy configuration
