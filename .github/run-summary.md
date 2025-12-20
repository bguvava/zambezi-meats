# Zambezi Meats - System Status

## ✅ System is Ready and Running

### Current Status (2025-12-18)

- **Backend API**: Running on http://127.0.0.1:8000
- **Frontend**: Running on http://localhost:5173
- **Database**: Fully migrated and seeded
- **Authentication**: Working correctly with session-based SPA auth

---

## Database Status

### Summary

| Entity     | Count | Status                             |
| ---------- | ----- | ---------------------------------- |
| Users      | 3     | ✅ Active                          |
| Products   | 162   | ✅ Seeded                          |
| Categories | 31    | ✅ Seeded (8 parent + 23 children) |

### User Accounts

All passwords are: `password`

#### Admin Account

```
Email: admin@zambezimeats.com.au
Role: admin
Access: Full system access
```

#### Staff Account

```
Email: staff@zambezimeats.com.au
Role: staff
Access: Order management, deliveries, stock
```

#### Customer Account

```
Email: customer@example.com
Role: customer
Access: Shopping, orders, profile management
```

---

## Data Consistency

### Idempotent Seeding

The database uses `updateOrCreate()` for all seeders, which means:

- ✅ Running `php artisan migrate:fresh --seed` will always give you the same data
- ✅ No duplicate products, categories, or users will be created
- ✅ Data is matched by unique keys (email for users, slug for products/categories)

### To Reset Database

```bash
cd c:\xampp\htdocs\Zambezi-Meats\backend
php artisan migrate:fresh --seed
```

This will:

1. Drop all tables
2. Run all migrations (36 migrations)
3. Seed 3 users
4. Seed 31 categories
5. Seed 162 products
6. Seed delivery zones and settings

**Time:** ~2.7 seconds

---

## API Testing

### Authentication Flow

Sanctum requires CSRF cookie before login:

```powershell
# 1. Get CSRF Cookie
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/sanctum/csrf-cookie' -Method GET -WebSession $session -UseBasicParsing | Out-Null

# 2. Login
$body = @{email='admin@zambezimeats.com.au';password='password'} | ConvertTo-Json
$headers = @{'Content-Type'='application/json';'Accept'='application/json';'X-Requested-With'='XMLHttpRequest'}
$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/auth/login' -Method POST -Body $body -Headers $headers -WebSession $session
$response | ConvertTo-Json
```

### Public Endpoints (No Auth Required)

```powershell
# Get all products (paginated)
curl.exe http://127.0.0.1:8000/api/v1/products

# Get all categories
curl.exe http://127.0.0.1:8000/api/v1/categories

# Get specific product
curl.exe http://127.0.0.1:8000/api/v1/products/coleslaw

# Search products
curl.exe "http://127.0.0.1:8000/api/v1/products/search?q=beef"
```

---

## Recent Fixes

### Authentication Issue (FIXED)

**Problem:** Login was returning 500 error with "Session store not set on request"

**Solution:** Added session middleware explicitly to API routes in [bootstrap/app.php](bootstrap/app.php):

```php
$middleware->api(prepend: [
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);
```

### Configuration Updates

1. **Session Lifetime**: Changed from 5 minutes to 120 minutes (2 hours)
2. **Session Domain**: Fixed duplicate declaration (set to `null` for localhost)
3. **Sanctum Domains**: Added `127.0.0.1:8000` to stateful domains
4. **Guest Middleware**: Removed from auth routes to allow session access

---

## Testing Checklist

### ✅ Completed Tests

- [x] Database migration and seeding
- [x] Products API (162 products accessible)
- [x] Categories API (31 categories accessible)
- [x] Admin login (admin@zambezimeats.com.au)
- [x] Staff login (staff@zambezimeats.com.au)
- [x] Customer login (customer@example.com)
- [x] Data persistence through fresh migrations
- [x] All 409 backend unit/feature tests passing

### 🔄 Ready for Manual Testing

- [ ] Frontend login integration
- [ ] Admin dashboard access
- [ ] Staff dashboard access
- [ ] Customer dashboard access
- [ ] Product browsing on shop page
- [ ] Cart functionality
- [ ] Checkout flow
- [ ] All new pages (delivery, FAQ, shipping, terms, privacy)

---

## Server Management

### Start Servers

```powershell
# Terminal 1 - Backend
cd c:\xampp\htdocs\Zambezi-Meats\backend
php artisan serve --port=8000

# Terminal 2 - Frontend
cd c:\xampp\htdocs\Zambezi-Meats\frontend
npm run dev
```

### Stop Servers

- Press `Ctrl+C` in each terminal window

### Check Backend Terminal

Look for: `INFO Server running on [http://127.0.0.1:8000]`

### Check Frontend Terminal

Look for: `ROLLDOWN-VITE v7.2.5 ready in XXX ms`
URL: `http://localhost:5173/`

---

## Product Categories

### Parent Categories (8)

1. Fresh Beef
2. Fresh Lamb
3. Fresh Chicken
4. Fresh Pork
5. Extras
6. Eggs & Dairy
7. Poultry
8. Seasonal & Specials

### Sample Products

- Beef: Rump Steak, Sirloin Steak, T-Bone Steak, Ribeye Steak, etc.
- Lamb: Lamb Chops, Lamb Cutlets, Lamb Shoulder, Leg of Lamb, etc.
- Chicken: Chicken Breast, Chicken Thighs, Whole Chicken, etc.
- Extras: BBQ Sauces, Marinades, Coleslaw, Potato Salad, etc.
- Eggs: Free Range Eggs, Organic Eggs, etc.

---

## Notes

### Color Palette

- Primary Red: #CF0D0F
- Accent Red: #F6211F
- Light Gray: #EFEFEF
- Medium Gray: #6F6F6F
- Dark Gray: #4D4B4C

### Deployment

- See [docs/deployment/README.md](docs/deployment/README.md) for production deployment steps
- Pre-launch checklist: [docs/checklist/pre-launch-checklist.md](docs/checklist/pre-launch-checklist.md)

### Test Coverage

- 409 tests passing
- 2 tests skipped (production-only checks)
- Test suite includes: Auth, Cart, Checkout, Products, Categories, Orders, Admin, Staff, Customer features

---

## Quick Access URLs

### Development

- Backend: http://127.0.0.1:8000
- Frontend: http://localhost:5173
- API Health: http://127.0.0.1:8000/api/v1/health

### Admin Dashboards (After Login)

- Admin: http://localhost:5173/admin/dashboard
- Staff: http://localhost:5173/staff/dashboard
- Customer: http://localhost:5173/customer/dashboard

### Public Pages

- Home: http://localhost:5173/
- Shop: http://localhost:5173/shop
- About: http://localhost:5173/about
- Contact: http://localhost:5173/contact
- Delivery Info: http://localhost:5173/delivery
- FAQ: http://localhost:5173/faq
- Shipping: http://localhost:5173/shipping
- Terms: http://localhost:5173/terms
- Privacy: http://localhost:5173/privacy

---

**Last Updated:** 2025-12-18 08:51 AEDT
**Status:** ✅ All Systems Operational
