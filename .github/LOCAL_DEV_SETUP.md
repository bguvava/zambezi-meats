# Zambezi Meats - Local Development Setup Guide

## 🚀 Complete Setup Instructions

This guide provides step-by-step instructions to run the Zambezi Meats application on your local development environment.

---

## ✅ Prerequisites

Before starting, ensure you have:

- **XAMPP** installed with:
  - PHP 8.2+
  - MySQL 8.0
  - Apache/OpenLiteSpeed
- **Node.js** 20 LTS
- **Composer** (latest)
- **Git** (for version control)

---

## 📋 Step-by-Step Setup

### Step 1: Start MySQL Server

```powershell
# Start MySQL through XAMPP Control Panel
# OR via command line:
C:\xampp\mysql_start.bat
```

**Verify MySQL is running:**

```powershell
tasklist | findstr mysql
# Should show: mysqld.exe
```

### Step 2: Navigate to Project Directory

```powershell
cd C:\xampp\htdocs\Zambezi-Meats
```

### Step 3: Clear All Caches (Backend)

```powershell
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Step 4: Setup Database

**Option A: Fresh Install (Recommended for first time)**

```powershell
# This will drop all tables and reseed
php artisan migrate:fresh --seed
```

**Option B: Just Run Seeders**

```powershell
# If database already exists
php artisan db:seed
```

**Option C: Reseed Products Only**

```powershell
# To update products without affecting other data
php artisan db:seed --class=ProductSeeder
```

### Step 5: Clear Frontend Cache & Install Dependencies

```powershell
cd ../frontend

# Clear node_modules and build artifacts
if (Test-Path node_modules) { Remove-Item -Recurse -Force node_modules }
if (Test-Path dist) { Remove-Item -Recurse -Force dist }
if (Test-Path .vite) { Remove-Item -Recurse -Force .vite }

# Install dependencies
npm install
```

### Step 6: Build Frontend

```powershell
# Development build
npm run build

# OR for development mode with hot reload
npm run dev
```

### Step 7: Start Backend Server

**Open a NEW PowerShell terminal:**

```powershell
cd C:\xampp\htdocs\Zambezi-Meats\backend
php artisan serve --host=127.0.0.1 --port=8000
```

**Expected Output:**

```
INFO  Server running on [http://127.0.0.1:8000].

Press Ctrl+C to stop the server
```

### Step 8: Start Frontend Server

**Open ANOTHER NEW PowerShell terminal:**

```powershell
cd C:\xampp\htdocs\Zambezi-Meats\frontend
npm run dev
```

**Expected Output:**

```
ROLLDOWN-VITE v7.2.5 ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help
```

---

## 🌐 Access URLs

Once both servers are running:

| Service         | URL                                 | Description                      |
| --------------- | ----------------------------------- | -------------------------------- |
| **Frontend**    | http://localhost:5173               | Main customer-facing application |
| **Backend API** | http://127.0.0.1:8000/api/v1        | REST API endpoints               |
| **API Health**  | http://127.0.0.1:8000/api/v1/health | Health check endpoint            |

---

## 👥 Test User Credentials

Use these credentials to test different user roles:

### 1. Admin User (Full Access)

```
Email:    admin@zambezimeats.com.au
Password: password
Access:   All dashboards, user management, settings, reports
```

### 2. Staff User (Operations)

```
Email:    staff@zambezimeats.com.au
Password: password
Access:   Order management, inventory, deliveries
```

### 3. Customer User (Shopping)

```
Email:    customer@example.com
Password: password
Access:   Shop, cart, orders, profile, wishlist
```

---

## 📊 Database Information

| Setting           | Value                         |
| ----------------- | ----------------------------- |
| **Database Name** | `my_zambezimeats`             |
| **Host**          | `127.0.0.1`                   |
| **Port**          | `3306`                        |
| **Username**      | `root`                        |
| **Password**      | _(empty by default in XAMPP)_ |

---

## 🔧 Troubleshooting

### Issue: Port Already in Use

**Frontend (Port 5173):**

```powershell
# Vite will automatically use port 5174 if 5173 is busy
# You'll see: "Port 5173 is in use, trying another one..."
```

**Backend (Port 8000):**

```powershell
# Find process using port 8000
netstat -ano | findstr :8000

# Kill the process (replace PID with actual process ID)
taskkill /PID <PID> /F
```

### Issue: Database Connection Failed

```powershell
# Verify MySQL is running
tasklist | findstr mysql

# Check .env file in backend/ directory
# Make sure DB_DATABASE=my_zambezimeats
# Make sure DB_USERNAME=root
# Make sure DB_PASSWORD= (empty for XAMPP)
```

### Issue: CSRF Token Mismatch (419 Error)

```powershell
# Clear Laravel caches
cd backend
php artisan cache:clear
php artisan config:clear

# Restart both servers
```

### Issue: Products Not Showing

```powershell
# Reseed products
cd backend
php artisan db:seed --class=ProductSeeder

# Verify products in database
php artisan tinker
>>> \App\Models\Product::count()
# Should return 162
```

### Issue: Session Expired Loop

This was fixed in the latest update. If you still encounter it:

1. Clear browser cookies and cache
2. Restart frontend server
3. Hard refresh browser (Ctrl + Shift + R)

---

## 🔄 Daily Development Workflow

### Starting Work

```powershell
# Terminal 1: Backend
cd C:\xampp\htdocs\Zambezi-Meats\backend
php artisan serve --port=8000

# Terminal 2: Frontend
cd C:\xampp\htdocs\Zambezi-Meats\frontend
npm run dev
```

### Making Changes

**Backend Changes (Laravel):**

- Edit PHP files in `backend/app/`
- Changes apply immediately (no rebuild needed)
- Run `php artisan optimize:clear` if config changes

**Frontend Changes (Vue.js):**

- Edit Vue files in `frontend/src/`
- Vite hot-reloads automatically
- Refresh browser if changes don't appear

### Running Tests

**Backend Tests:**

```powershell
cd backend
php artisan test
```

**Frontend Tests:**

```powershell
cd frontend
npm run test
```

---

## 📦 Database Seeding Details

The database is seeded with:

- **3 Users** (Admin, Staff, Customer)
- **8 Main Categories** with subcategories
- **162 Products** from official product list
- **Delivery Zones** for Sydney area
- **Currency Rates** (AUD/USD)
- **System Settings**

### Product Categories Seeded:

| Category | Products | Subcategories                     |
| -------- | -------- | --------------------------------- |
| Beef     | 29       | Steaks, Roasts, Mince, Specialty  |
| Lamb     | 25       | Chops/Cutlets, Roasts, Mince      |
| Pork     | 18       | Chops/Steaks, Roasts, Ribs, Bacon |
| Veal     | 7        | Steaks, Chops, Specialty          |
| Poultry  | 18       | Whole, Breast, Thigh/Drums, Wings |
| Sausages | 14       | Beef, Pork, Gourmet               |
| Deli     | 11       | Sliced Meats, Smallgoods          |
| Meals    | 32       | Pies, Soups, Sauces, Ready Meals  |
| Other    | 8        | Bones, Eggs, Salads, Grocery      |

---

## 🎨 Branding Assets

### Logo

- **Official Logo:** `.github/official_logo.png`
- **Format:** PNG with transparent background
- **Usage:** Header, login pages, emails

### Brand Colors

| Color       | Hex Code  | Usage                    |
| ----------- | --------- | ------------------------ |
| Dark Red    | `#8B0000` | Primary buttons, headers |
| Dark Brown  | `#2C1810` | Secondary elements       |
| Beige/Cream | `#F5F5DC` | Backgrounds, accents     |

---

## 📝 Environment Variables

### Backend (.env)

```env
APP_NAME="Zambezi Meats"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_zambezimeats
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=cookie
SESSION_LIFETIME=5
SESSION_DOMAIN=localhost

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
```

### Frontend (.env)

```env
VITE_APP_NAME="Zambezi Meats"
VITE_API_URL=http://127.0.0.1:8000
VITE_APP_URL=http://localhost:5173
```

---

## 🚪 Stopping the Application

### Graceful Shutdown

**Stop Backend Server:**

```powershell
# In backend terminal, press: Ctrl + C
```

**Stop Frontend Server:**

```powershell
# In frontend terminal, press: Ctrl + C
```

### Force Stop (if needed)

```powershell
# Kill all Node processes
taskkill /F /IM node.exe

# Kill PHP artisan serve
taskkill /F /IM php.exe
```

---

## ✅ Verification Checklist

After setup, verify:

- [ ] MySQL is running (`tasklist | findstr mysql`)
- [ ] Backend server accessible at http://127.0.0.1:8000/api/v1/health
- [ ] Frontend loads at http://localhost:5173
- [ ] Can browse products on shop page
- [ ] Can login with test credentials
- [ ] Products display with images
- [ ] Cart functionality works
- [ ] No session expired errors on public pages

---

## 📞 Quick Reference Commands

### Backend

```powershell
# Clear caches
php artisan optimize:clear

# Reset database
php artisan migrate:fresh --seed

# Start server
php artisan serve --port=8000

# Run tests
php artisan test
```

### Frontend

```powershell
# Install dependencies
npm install

# Development server
npm run dev

# Production build
npm run build

# Run tests
npm run test
```

---

## 🔍 API Testing

### Using Browser

```
Health Check: http://127.0.0.1:8000/api/v1/health
Products: http://127.0.0.1:8000/api/v1/products
Categories: http://127.0.0.1:8000/api/v1/categories
```

### Using cURL

```powershell
# Get all products
curl http://127.0.0.1:8000/api/v1/products

# Get specific product
curl http://127.0.0.1:8000/api/v1/products/rump-steak

# Get categories
curl http://127.0.0.1:8000/api/v1/categories
```

---

## 📚 Additional Resources

- **MVP Documentation:** `.github/prompts/product_mvp.md`
- **Coding Standards:** `.github/prompts/coding_style.json`
- **Project Settings:** `.github/prompts/settings.yml`
- **API Documentation:** `docs/`

---

**Last Updated:** December 14, 2025  
**Version:** 1.0.0-mvp

For issues or questions, refer to the troubleshooting section or check the project documentation.
