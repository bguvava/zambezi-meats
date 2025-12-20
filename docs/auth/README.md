# Authentication Module Documentation

## Overview

The Zambezi Meats authentication system provides secure user authentication using Laravel Sanctum with cookie-based SPA authentication. This module implements a 5-minute session timeout with a warning modal and role-based access control.

## Requirements Implemented

| ID       | Requirement                       | Status      |
| -------- | --------------------------------- | ----------- |
| AUTH-001 | AuthController with all endpoints | ✅ Complete |
| AUTH-002 | Registration with validation      | ✅ Complete |
| AUTH-003 | Login with Sanctum cookies        | ✅ Complete |
| AUTH-004 | 5-minute session timeout          | ✅ Complete |
| AUTH-005 | Session warning at 4:30           | ✅ Complete |
| AUTH-006 | Password reset flow               | ✅ Complete |
| AUTH-007 | Logout with session invalidation  | ✅ Complete |
| AUTH-008 | Form Request validation           | ✅ Complete |
| AUTH-009 | EnsureRole middleware             | ✅ Complete |
| AUTH-010 | Pinia auth store                  | ✅ Complete |
| AUTH-011 | Email availability check          | ✅ Complete |
| AUTH-012 | Password strength validation      | ✅ Complete |
| AUTH-013 | useAuth composable                | ✅ Complete |
| AUTH-014 | Session activity tracking         | ✅ Complete |
| AUTH-015 | Token refresh endpoint            | ✅ Complete |
| AUTH-016 | Login page component              | ✅ Complete |
| AUTH-017 | Register page component           | ✅ Complete |
| AUTH-018 | Forgot password page              | ✅ Complete |
| AUTH-019 | Reset password page               | ✅ Complete |
| AUTH-020 | Unit tests                        | ✅ Complete |

## Backend Architecture

### Directory Structure

```
backend/app/Http/
├── Controllers/Api/V1/
│   └── AuthController.php
├── Middleware/
│   └── EnsureRole.php
├── Requests/Api/V1/Auth/
│   ├── ForgotPasswordRequest.php
│   ├── LoginRequest.php
│   ├── RegisterRequest.php
│   └── ResetPasswordRequest.php
└── Resources/Api/V1/
    ├── AddressResource.php
    └── UserResource.php
```

### API Endpoints

All endpoints are prefixed with `/api/v1/auth/`

| Method | Endpoint           | Description               | Auth Required |
| ------ | ------------------ | ------------------------- | ------------- |
| POST   | `/register`        | Create new user account   | No            |
| POST   | `/login`           | Authenticate user         | No            |
| POST   | `/logout`          | End user session          | Yes           |
| GET    | `/user`            | Get authenticated user    | Yes           |
| POST   | `/refresh`         | Refresh session           | Yes           |
| POST   | `/forgot-password` | Request password reset    | No            |
| POST   | `/reset-password`  | Reset password with token | No            |
| POST   | `/check-email`     | Check email availability  | No            |

### AuthController Methods

```php
// Register a new user
public function register(RegisterRequest $request): JsonResponse

// Authenticate user and create session
public function login(LoginRequest $request): JsonResponse

// Logout and invalidate session
public function logout(Request $request): JsonResponse

// Get current authenticated user
public function user(Request $request): JsonResponse

// Refresh session timeout
public function refresh(Request $request): JsonResponse

// Send password reset email
public function forgotPassword(ForgotPasswordRequest $request): JsonResponse

// Reset password with token
public function resetPassword(ResetPasswordRequest $request): JsonResponse

// Check if email is available
public function checkEmail(Request $request): JsonResponse
```

### Validation Rules

**Registration (RegisterRequest)**

- `name`: required, string, max 255 characters
- `email`: required, valid email, unique in users table
- `phone`: optional, Australian phone format (04XX XXX XXX)
- `password`: required, min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char

**Login (LoginRequest)**

- `email`: required, valid email
- `password`: required, string
- `remember`: optional, boolean

**Reset Password (ResetPasswordRequest)**

- `token`: required, string
- `email`: required, valid email, exists in users table
- `password`: required, same rules as registration

### Role-Based Access Control

The `EnsureRole` middleware provides role checking:

```php
// Single role
Route::get('/admin/dashboard', ...)->middleware('role:admin');

// Multiple roles
Route::get('/orders', ...)->middleware('role:staff,admin');
```

**Available Roles:**

- `customer` - Regular customers
- `staff` - Staff members (delivery drivers, butchers)
- `admin` - System administrators

## Frontend Architecture

### Directory Structure

```
frontend/src/
├── stores/
│   └── auth.js          # Pinia auth store
├── composables/
│   └── useAuth.js       # Auth composable
├── components/auth/
│   └── SessionWarningModal.vue
└── pages/auth/
    ├── LoginPage.vue
    ├── RegisterPage.vue
    ├── ForgotPasswordPage.vue
    └── ResetPasswordPage.vue
```

### Pinia Auth Store

**State:**

```javascript
{
  user: null,                    // Current user object
  token: null,                   // Not used (cookie-based)
  isAuthenticated: false,        // Authentication status
  isLoading: false,              // Loading state
  error: null,                   // Error message
  lastActivity: null,            // Last activity timestamp
  sessionWarningShown: false,    // Warning modal visibility
  sessionTimeoutId: null,        // Timeout timer ID
  sessionWarningTimeoutId: null  // Warning timer ID
}
```

**Key Methods:**

```javascript
// Authentication
await authStore.login(email, password, remember);
await authStore.register(userData);
await authStore.logout();
await authStore.checkSession();

// Password Reset
await authStore.forgotPassword(email);
await authStore.resetPassword(data);

// Session Management
authStore.refreshSession();
authStore.resetSessionTimer();
authStore.updateLastActivity();
```

**Getters:**

```javascript
authStore.isAuthenticated; // Boolean
authStore.isAdmin; // Boolean
authStore.isStaff; // Boolean
authStore.isCustomer; // Boolean
authStore.userName; // String
authStore.userEmail; // String
authStore.userRole; // String
```

### useAuth Composable

Provides convenient auth methods:

```javascript
import { useAuth } from '@/composables/useAuth'

const {
  login,
  register,
  logout,
  isAuthenticated,
  canAccess,
  getDefaultRedirect
} = useAuth()

// Login with automatic redirect
await login(email, password, remember)

// Check role access
if (canAccess(['admin', 'staff'])) { ... }
```

### Session Timeout Configuration

- **Session Duration:** 5 minutes (300 seconds)
- **Warning Display:** 30 seconds before timeout (at 4:30)
- **Activity Events:** mousemove, keydown, click, scroll, touchstart

The `SessionWarningModal` component displays a countdown when 30 seconds remain.

## Security Features

1. **CSRF Protection:** Sanctum requires CSRF token for all state-changing requests
2. **Cookie Security:** HTTP-only, secure, same-site cookies
3. **Session Regeneration:** Session ID regenerated on login
4. **Rate Limiting:** Login attempts are rate-limited
5. **Password Hashing:** bcrypt with cost factor 12
6. **XSS Prevention:** Vue's template escaping prevents XSS

## Configuration

### Backend (.env)

```env
SESSION_LIFETIME=5
SESSION_DRIVER=cookie
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8000
```

### Frontend (api.js)

```javascript
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
```

## Error Handling

**Backend Errors:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

**Frontend Error Display:**

- Form validation errors displayed inline
- API errors displayed in toast notifications
- Session expiry redirects to login with message

## Testing

### Backend Tests (PHPUnit)

**Run all auth tests:**

```bash
cd backend
vendor\bin\phpunit tests/Feature/Auth --testdox
```

**Expected output:**

```
OK (77 tests, 249 assertions)
```

**Test Files:**

| Test File                 | Tests  | Description                |
| ------------------------- | ------ | -------------------------- |
| RegistrationTest.php      | 11     | User registration flows    |
| LoginTest.php             | 13     | Login/authentication flows |
| LogoutTest.php            | 9      | Session termination        |
| PasswordResetTest.php     | 12     | Forgot/reset password      |
| UserEndpointTest.php      | 7      | /auth/user endpoint        |
| SessionRefreshTest.php    | 8      | Session refresh endpoint   |
| EmailAvailabilityTest.php | 6      | Email check endpoint       |
| RoleMiddlewareTest.php    | 12     | Role-based access control  |
| **Total**                 | **77** | **100% Pass Rate**         |

### Frontend Tests (Vitest)

**Run all auth tests:**

```bash
cd frontend
npm run test -- --run src/tests/auth
```

**Test Files:**

| Test File                   | Description                   |
| --------------------------- | ----------------------------- |
| authStore.spec.js           | Pinia auth store tests        |
| useAuth.spec.js             | useAuth composable tests      |
| LoginPage.spec.js           | Login page component tests    |
| RegisterPage.spec.js        | Register page component tests |
| ForgotPasswordPage.spec.js  | Forgot password page tests    |
| ResetPasswordPage.spec.js   | Reset password page tests     |
| SessionWarningModal.spec.js | Session warning modal tests   |

### Test Coverage

**Backend Test Categories:**

- User can register with valid data
- Registration fails with invalid email
- Registration fails with weak password
- User can login with valid credentials
- Login fails with wrong password
- Login is case-insensitive for email
- Authenticated user can logout
- Multiple logouts handled gracefully
- Session expires after 5 minutes
- Password reset email sends successfully
- Password can be reset with valid token
- Invalid tokens are rejected
- Role middleware protects admin routes
- Staff cannot access admin-only routes
- Customer cannot access staff routes

**Frontend Test Categories:**

- Form validation (all fields)
- Password visibility toggle
- Password strength indicator
- Email availability checking
- Form submission flows
- Error message display
- Session warning countdown
- Activity listener setup
- Role-based redirects

## Usage Examples

### Logging In (Frontend)

```vue
<script setup>
import { useAuth } from "@/composables/useAuth";

const { login } = useAuth();

const handleSubmit = async () => {
  await login(email.value, password.value, remember.value);
  // Automatically redirects based on role
};
</script>
```

### Protecting API Routes (Backend)

```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});
```

### Checking Authentication (Frontend)

```vue
<script setup>
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

// In template
// <div v-if="authStore.isAuthenticated">Welcome, {{ authStore.userName }}</div>
</script>
```

## Troubleshooting

### Common Issues

1. **CORS Errors:** Ensure SANCTUM_STATEFUL_DOMAINS includes frontend URL
2. **CSRF Token Missing:** Call `/sanctum/csrf-cookie` before login
3. **Session Not Persisting:** Check cookie domain settings
4. **Role Check Failing:** Verify user has role in `role_user` pivot table

### Debug Mode

Enable debug logging in auth store:

```javascript
// In auth.js
const DEBUG = true;

if (DEBUG) console.log("Auth action:", action, data);
```
