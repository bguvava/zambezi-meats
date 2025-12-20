# Zambezi Meats MVP - Part 2: Authentication & Landing Module

## Module Overview

| Field             | Value                             |
| ----------------- | --------------------------------- |
| **Module Name**   | AUTH-LANDING                      |
| **Priority**      | P0 - Critical                     |
| **Dependencies**  | FOUNDATION                        |
| **Documentation** | `/docs/auth/`, `/docs/landing/`   |
| **Tests**         | `/tests/auth/`, `/tests/landing/` |

This module combines:

- Authentication & Authorization (AUTH)
- Landing Page (LANDING)

**Total Requirements: 40**

---

## 2.1 Authentication & Authorization

### Objectives

1. Implement secure user registration and login
2. Configure Laravel Sanctum for SPA authentication
3. Implement role-based access control (Guest, Customer, Staff, Admin)
4. Create 5-minute session timeout with auto-logout
5. Implement CSRF protection and secure cookie handling

### Success Criteria

| Criteria                | Target            |
| ----------------------- | ----------------- |
| Registration works      | ✓                 |
| Login/Logout works      | ✓                 |
| Session timeout (5 min) | ✓                 |
| Role-based guards       | 4 roles protected |
| CSRF protection         | Active            |
| Password reset works    | ✓                 |
| Test coverage           | 100%              |

### Requirements

| Requirement ID | Description                           | User Story                                                                | Expected Outcome                                                                        | Role      |
| -------------- | ------------------------------------- | ------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- | --------- |
| AUTH-001       | Create registration endpoint          | As a guest, I want to create an account so I can track my orders          | User can register with name, email, password; receives confirmation; redirected to shop | Guest     |
| AUTH-002       | Create login endpoint                 | As a user, I want to log in so I can access my dashboard                  | User can log in with email/password; session created; redirected based on role          | All       |
| AUTH-003       | Create logout endpoint                | As a user, I want to log out securely                                     | Session destroyed; cookies cleared; redirected to landing page                          | All       |
| AUTH-004       | Implement session timeout (5 minutes) | As a business owner, I want inactive sessions to auto-expire for security | After 5 minutes of inactivity, user is logged out and sees "Session expired" message    | All       |
| AUTH-005       | Create password reset flow            | As a user, I want to reset my password if I forget it                     | User requests reset; receives email with link; can set new password                     | Customer  |
| AUTH-006       | Implement role-based middleware       | As a developer, I need route protection by role                           | Middleware checks user role; unauthorized access returns 403                            | Developer |
| AUTH-007       | Create auth guards for Vue Router     | As a developer, I need frontend route protection                          | Navigation guards check auth status and role; redirect if unauthorized                  | Developer |
| AUTH-008       | Implement CSRF protection             | As a developer, I need CSRF protection for forms                          | CSRF token included in all requests; 419 error handled gracefully                       | Developer |
| AUTH-009       | Create current user endpoint          | As a frontend, I need to fetch logged-in user data                        | `/api/v1/user` returns current user with role and preferences                           | All       |
| AUTH-010       | Create auth store (Pinia)             | As a developer, I need frontend auth state management                     | Auth store manages user, token, isAuthenticated, role                                   | Developer |
| AUTH-011       | Implement "Remember Me" functionality | As a user, I want to stay logged in on trusted devices                    | Extended session duration when "Remember Me" checked                                    | Customer  |
| AUTH-012       | Create session activity tracker       | As a developer, I need to track user activity for timeout                 | Activity tracked on API calls and user interactions                                     | Developer |
| AUTH-013       | Handle 419 CSRF errors gracefully     | As a user, I want clear feedback when session expires                     | 419 errors show "Session expired" modal with re-login option                            | All       |
| AUTH-014       | Handle 401 unauthorized errors        | As a user, I want proper feedback for auth issues                         | 401 errors redirect to login with return URL preserved                                  | All       |
| AUTH-015       | Create admin-only seeder              | As a developer, I need initial admin account                              | Admin user seeded: admin@zambezimeats.com                                               | Developer |
| AUTH-016       | Create login page UI                  | As a user, I want a clean login form                                      | Modern login form with email, password, remember me, forgot password link               | All       |
| AUTH-017       | Create registration page UI           | As a guest, I want a simple registration form                             | Registration form with name, email, phone, password, confirm password                   | Guest     |
| AUTH-018       | Create forgot password page UI        | As a user, I want to request password reset                               | Email input form with clear instructions                                                | All       |
| AUTH-019       | Create reset password page UI         | As a user, I want to set a new password                                   | New password form with confirmation                                                     | All       |
| AUTH-020       | Write comprehensive auth tests        | As a developer, I need 100% test coverage                                 | Unit and feature tests for all auth endpoints and flows                                 | Developer |

### Session Timeout Implementation

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                           SESSION TIMEOUT FLOW (5 Minutes)                          │
└─────────────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
  │  User   │────▶│  Activity   │────▶│   Timer     │────▶│   Warning   │
  │  Login  │     │  Detected   │     │   Reset     │     │   (4:30)    │
  └─────────┘     └─────────────┘     └─────────────┘     └──────┬──────┘
                                                                 │
                           ┌─────────────────────────────────────┘
                           │
                           ▼
                  ┌─────────────────┐        ┌─────────────────┐
                  │    No Activity  │───────▶│   Auto Logout   │
                  │    (5:00)       │        │  + Lock Screen  │
                  └─────────────────┘        └─────────────────┘
```

### API Endpoints

| Method | Endpoint                       | Description            | Auth |
| ------ | ------------------------------ | ---------------------- | ---- |
| POST   | `/api/v1/auth/register`        | Register new customer  | No   |
| POST   | `/api/v1/auth/login`           | User login             | No   |
| POST   | `/api/v1/auth/logout`          | User logout            | Yes  |
| POST   | `/api/v1/auth/forgot-password` | Request password reset | No   |
| POST   | `/api/v1/auth/reset-password`  | Reset password         | No   |
| GET    | `/api/v1/auth/user`            | Get current user       | Yes  |
| POST   | `/api/v1/auth/refresh`         | Refresh session        | Yes  |

---

## 2.2 Landing Page

### Objectives

1. Create modern, animated landing page as entry point
2. Showcase brand identity and premium quality
3. Provide clear navigation to shop and login
4. Display featured products and promotions
5. Build trust with social proof and quality indicators

### Success Criteria

| Criteria          | Target      |
| ----------------- | ----------- |
| Page load time    | < 2 seconds |
| Mobile responsive | 100%        |
| Animations smooth | 60fps       |
| CTA click-through | Measurable  |
| Lighthouse score  | > 90        |
| Test coverage     | 100%        |

### Requirements

| Requirement ID | Description                                  | User Story                                                                          | Expected Outcome                                                                                     | Role      |
| -------------- | -------------------------------------------- | ----------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- | --------- |
| LAND-001       | Create hero section with animated background | As a visitor, I want to see an impressive hero section that conveys premium quality | Full-width hero with animated meat imagery, compelling headline, and CTA button                      | Guest     |
| LAND-002       | Create navigation header                     | As a visitor, I want easy navigation                                                | Sticky header with logo, nav links (Shop, About, Contact), Login/Register buttons, currency selector | Guest     |
| LAND-003       | Implement smooth scroll animations           | As a visitor, I want engaging scroll experience                                     | Elements animate in on scroll using GSAP/Intersection Observer                                       | Guest     |
| LAND-004       | Create featured products section             | As a visitor, I want to see popular products                                        | Grid of 4-6 featured products with images, names, prices, "View" button                              | Guest     |
| LAND-005       | Create "Why Choose Us" section               | As a visitor, I want to know the benefits                                           | 3-4 benefit cards with icons: Premium Quality, Fresh Daily, Fast Delivery, Best Prices               | Guest     |
| LAND-006       | Create about section                         | As a visitor, I want to know about the company                                      | Brief company story with image, link to full about page                                              | Guest     |
| LAND-007       | Create testimonials/reviews section          | As a visitor, I want social proof                                                   | Carousel of customer reviews with ratings                                                            | Guest     |
| LAND-008       | Create delivery zones section                | As a visitor, I want to know if you deliver to my area                              | Map or list of delivery areas with "Check Your Area" feature                                         | Guest     |
| LAND-009       | Create contact section                       | As a visitor, I want to contact the store                                           | Contact form, address, phone, email, operating hours                                                 | Guest     |
| LAND-010       | Create footer                                | As a visitor, I want site-wide links                                                | Footer with links, social media, newsletter signup, developer credits                                | Guest     |
| LAND-011       | Implement currency selector                  | As a visitor, I want to see prices in my currency                                   | Dropdown to switch between AU$ and US$                                                               | Guest     |
| LAND-012       | Create login modal/section                   | As a visitor, I want quick access to login                                          | Login form accessible from header, can also be modal                                                 | Guest     |
| LAND-013       | Implement mobile hamburger menu              | As a mobile user, I want accessible navigation                                      | Responsive hamburger menu with slide-out navigation                                                  | Guest     |
| LAND-014       | Add micro-interactions                       | As a visitor, I want delightful interactions                                        | Button hovers, card elevations, smooth transitions                                                   | Guest     |
| LAND-015       | Implement lazy loading for images            | As a visitor, I want fast page loads                                                | Images lazy-loaded with blur placeholder                                                             | Guest     |
| LAND-016       | Create "Shop Now" CTA                        | As a visitor, I want to quickly access the shop                                     | Prominent CTA button that navigates to shop page                                                     | Guest     |
| LAND-017       | Add SEO meta tags                            | As a business, I want search engine visibility                                      | Title, description, Open Graph, Twitter cards configured                                             | Admin     |
| LAND-018       | Implement newsletter signup                  | As a visitor, I want to subscribe to updates                                        | Email input with validation, success feedback                                                        | Guest     |
| LAND-019       | Add developer credits                        | As the developer, I want attribution                                                | Footer text: "Developed with ❤️ by bguvava"                                                          | Developer |
| LAND-020       | Write landing page tests                     | As a developer, I need 100% test coverage                                           | Component tests, E2E tests for navigation and interactions                                           | Developer |

### Landing Page Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER (Sticky)                                                                    │
│  ┌──────┐                                           ┌────┐ ┌────────┐ ┌──────────┐  │
│  │ LOGO │  Shop  About  Contact             AU$/US$ │Cart│ │ Login  │ │ Register │  │
│  └──────┘                                           └────┘ └────────┘ └──────────┘  │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│                              HERO SECTION                                           │
│                     ┌─────────────────────────────────┐                             │
│                     │      Premium Quality Meats      │                             │
│                     │   Delivered Fresh to Your Door  │                             │
│                     │                                 │                             │
│                     │     ┌──────────────────┐        │                             │
│                     │     │    SHOP NOW      │        │                             │
│                     │     └──────────────────┘        │                             │
│                     └─────────────────────────────────┘                             │
│                        (Animated background)                                        │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           FEATURED PRODUCTS                                         │
│   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐                            │
│   │ Product │   │ Product │   │ Product │   │ Product │                            │
│   │  Image  │   │  Image  │   │  Image  │   │  Image  │                            │
│   │ $XX/kg  │   │ $XX/kg  │   │ $XX/kg  │   │ $XX/kg  │                            │
│   └─────────┘   └─────────┘   └─────────┘   └─────────┘                            │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           WHY CHOOSE US                                             │
│   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐    │
│   │   🥩 Premium  │   │   🌿 Fresh    │   │   🚚 Fast     │   │   💰 Best     │    │
│   │    Quality    │   │    Daily      │   │   Delivery    │   │    Prices     │    │
│   └───────────────┘   └───────────────┘   └───────────────┘   └───────────────┘    │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           TESTIMONIALS                                              │
│              ◀  "Best quality meat I've ever ordered!"  ▶                          │
│                         ⭐⭐⭐⭐⭐ - John D.                                          │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                              CONTACT                                                │
│   ┌─────────────────────────────────┐    📍 6/1053 Old Princes Highway            │
│   │     Contact Form                │       Engadine, NSW 2233                     │
│   │     Name: ________________      │    📞 XXXX XXX XXX                           │
│   │     Email: _______________      │    ✉️  info@zambezimeats.com                  │
│   │     Message: _____________      │    🕐 Mon-Sun: 7am - 6pm                     │
│   │     [SEND]                      │                                              │
│   └─────────────────────────────────┘                                              │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│  FOOTER                                                                             │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐                    │
│  │ Quick Links│  │  Products  │  │   Legal    │  │  Connect   │                    │
│  │ Shop       │  │ Beef       │  │ Privacy    │  │ Facebook   │                    │
│  │ About      │  │ Chicken    │  │ Terms      │  │ Instagram  │                    │
│  │ Contact    │  │ Lamb       │  │ Returns    │  │ Newsletter │                    │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘                    │
│                                                                                     │
│  ───────────────────────────────────────────────────────────────────────────────    │
│                    Developed with ❤️ by bguvava                                     │
│                © 2025 Zambezi Meats. All rights reserved.                          │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

### Company Information

| Field               | Value                                                     |
| ------------------- | --------------------------------------------------------- |
| **Company Name**    | Zambezi Meats                                             |
| **Address**         | 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia |
| **Operating Hours** | 7am - 6pm (Mon-Sun)                                       |
| **Logo**            | `.github/official_logo.png`                               |
| **Developer**       | bguvava (www.bguvava.com)                                 |

---

## Part 2 Summary

| Section                        | Requirements | IDs                  |
| ------------------------------ | ------------ | -------------------- |
| Authentication & Authorization | 20           | AUTH-001 to AUTH-020 |
| Landing Page                   | 20           | LAND-001 to LAND-020 |
| **Total**                      | **40**       |                      |

---

**Previous:** [Part 1: Foundation Module](part1-foundation.md)

**Next:** [Part 3: Shop & Cart Module](part3-shop-cart.md)
