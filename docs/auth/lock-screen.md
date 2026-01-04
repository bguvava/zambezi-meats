# Lock Screen Feature

## Overview

The Lock Screen feature protects user sessions after 5 minutes of inactivity by displaying a lock screen that requires password confirmation to continue or allows the user to logout completely.

## Features

### Session Inactivity Detection

- **Timeout Duration**: 5 minutes of inactivity
- **Warning**: 30 seconds before lock (at 4:30)
- **Auto-Lock**: Triggers at 5:00 mark
- **Activity Tracking**: Mouse movements, clicks, keyboard inputs, scrolling

### Lock Screen Interface

- **User Display**: Shows current user's name, email, and initials
- **Password Input**: Secure password field with show/hide toggle
- **Options**:
  - **Unlock Session**: Verify password and continue
  - **Logout**: End session completely
- **Visual Design**: Premium branded interface with Zambezi Meats colors
- **User-Friendly Message**: Clear explanation of why session was locked

### Security Features

- **Password Verification**: Server-side validation
- **Session Regeneration**: Prevents session fixation attacks
- **Auto-Focus**: Password field automatically focused
- **Enter Key Support**: Submit with Enter key
- **Error Handling**: Clear feedback for incorrect passwords

## Implementation

### Backend API

#### Unlock Endpoint

```
POST /api/v1/auth/unlock
```

**Request:**

```json
{
  "email": "user@example.com",
  "password": "user_password"
}
```

**Response (Success - 200):**

```json
{
  "success": true,
  "message": "Session unlocked successfully.",
  "data": {
    "expires_at": "2024-12-20T10:30:00Z"
  }
}
```

**Response (Failure - 401):**

```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

**Implementation:**

```php
public function unlock(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }

    // Regenerate session to prevent session fixation
    $request->session()->regenerate();

    return response()->json([
        'success' => true,
        'message' => 'Session unlocked successfully.',
        'data' => [
            'expires_at' => now()->addMinutes(config('session.lifetime'))->toIso8601String(),
        ],
    ]);
}
```

### Frontend Components

#### LockScreen.vue

**Location:** `frontend/src/components/auth/LockScreen.vue`

**Props:**

- `show` (Boolean, required): Whether to display the lock screen

**Emits:**

- `unlock`: Emitted when session is successfully unlocked
- `logout`: Emitted when user chooses to logout

**Features:**

- Full-screen overlay with gradient background
- User avatar with initials
- Password input with toggle visibility
- Loading states during unlock
- Error handling with toast notifications
- Auto-focus on password field
- Enter key support

**Usage:**

```vue
<LockScreen
  :show="authStore.sessionLocked"
  @unlock="handleUnlock"
  @logout="handleLogout"
/>
```

### Auth Store Updates

#### New State

```javascript
const sessionLocked = ref(false);
```

#### New Actions

**unlockSession(password)**

- Calls `/api/v1/auth/unlock` endpoint
- Verifies password
- Unlocks session and restarts timer on success
- Returns success/failure with message

**lockSession()**

- Manually locks the session
- Stops session timers
- Sets `sessionLocked` to true

**Modified: startSessionTimer()**

- Changed from auto-logout to auto-lock
- Sets `sessionLocked = true` instead of calling `logout()`
- Maintains warning timer at 4:30 mark

### Activity Tracking

Monitored events for session activity:

- `mousemove`
- `mousedown`
- `keydown`
- `scroll`
- `touchstart`

Any of these events resets the inactivity timer.

## User Experience Flow

### 1. Normal Activity

```
User Active → Timer Resets → Continue Working
```

### 2. Approaching Inactivity

```
4:30 → Warning Modal → "Your session will expire soon"
        ↓
User Dismisses/Continues → Timer Resets
```

### 3. Session Lock

```
5:00 → Lock Screen Appears
        ↓
Option 1: Enter Password → Unlock → Continue Session
Option 2: Click Logout → End Session → Redirect to Home
```

### 4. Failed Unlock

```
Incorrect Password → Error Toast → Retry
```

## Configuration

### Session Timeout Settings

**File:** `frontend/src/stores/auth.js`

```javascript
// Session timeout in milliseconds (5 minutes)
const SESSION_TIMEOUT = 5 * 60 * 1000;

// Warning shown at 4:30 (30 seconds before timeout)
const SESSION_WARNING_TIME = 4.5 * 60 * 1000;
```

### Laravel Session Config

**File:** `backend/config/session.php`

```php
'lifetime' => env('SESSION_LIFETIME', 120), // 120 minutes server-side
```

Note: Frontend enforces stricter 5-minute timeout for security.

## Color Palette

Lock screen uses Zambezi Meats brand colors:

- Primary Red: `#CF0D0F`
- Secondary Red: `#F6211F`
- Light Gray: `#EFEFEF`
- Medium Gray: `#6F6F6F`
- Dark Gray: `#4D4B4C`

## Testing

### Manual Testing Steps

1. **Test Lock Screen Activation**

   - Login to application
   - Wait 5 minutes without activity
   - Verify lock screen appears
   - Confirm warning shown at 4:30

2. **Test Unlock with Correct Password**

   - Enter correct password
   - Click "Unlock Session" or press Enter
   - Verify session continues
   - Confirm timer resets

3. **Test Unlock with Incorrect Password**

   - Enter wrong password
   - Verify error message displayed
   - Confirm password field clears
   - Try again with correct password

4. **Test Logout from Lock Screen**

   - Click "Logout" button
   - Verify redirect to homepage
   - Confirm session ended

5. **Test Activity Reset**

   - Start timer countdown
   - Move mouse or type before 5 minutes
   - Verify timer resets
   - Confirm lock screen doesn't appear

6. **Test Warning Dismissal**

   - Wait for warning at 4:30
   - Dismiss warning modal
   - Verify timer continues
   - Confirm lock screen still appears at 5:00 if no activity

7. **Test Enter Key**

   - Lock screen appears
   - Enter password
   - Press Enter key
   - Verify unlock triggered

8. **Test Password Visibility Toggle**
   - Click eye icon
   - Verify password visible/hidden
   - Confirm toggle works both ways

### API Testing

**Test Unlock Endpoint:**

```bash
curl -X POST http://localhost:8000/api/v1/auth/unlock \
  -H "Content-Type: application/json" \
  -H "Cookie: XSRF-TOKEN=..." \
  -d '{
    "email": "admin@zambezimeats.com.au",
    "password": "password"
  }'
```

**Expected Response:**

```json
{
  "success": true,
  "message": "Session unlocked successfully.",
  "data": {
    "expires_at": "2024-12-20T10:30:00Z"
  }
}
```

### Automated Tests

**Backend Test:**

```php
public function test_unlock_with_valid_password()
{
    $user = User::factory()->create([
        'password' => Hash::make('password')
    ]);

    $response = $this->post('/api/v1/auth/unlock', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);
}

public function test_unlock_with_invalid_password()
{
    $user = User::factory()->create([
        'password' => Hash::make('password')
    ]);

    $response = $this->post('/api/v1/auth/unlock', [
        'email' => $user->email,
        'password' => 'wrong'
    ]);

    $response->assertUnauthorized()
        ->assertJson(['success' => false]);
}
```

**Frontend Test (Vitest):**

```javascript
import { describe, it, expect, vi } from "vitest";
import { mount } from "@vue/test-utils";
import LockScreen from "@/components/auth/LockScreen.vue";
import { useAuthStore } from "@/stores/auth";

describe("LockScreen", () => {
  it("displays user information correctly", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.find(".user-name").text()).toBe("John Doe");
    expect(wrapper.find(".user-email").text()).toBe("john@example.com");
  });

  it("emits unlock event on successful unlock", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    await wrapper.find('input[type="password"]').setValue("password");
    await wrapper.find("button.unlock").trigger("click");

    expect(wrapper.emitted("unlock")).toBeTruthy();
  });

  it("emits logout event when logout clicked", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    await wrapper.find("button.logout").trigger("click");

    expect(wrapper.emitted("logout")).toBeTruthy();
  });
});
```

## Troubleshooting

### Issue: Lock screen appears immediately after login

**Solution:** Check that `startSessionTimer()` is called after successful login and user fetch.

### Issue: Lock screen doesn't appear after 5 minutes

**Solution:**

- Verify activity listeners are properly set up
- Check browser console for JavaScript errors
- Ensure `setupActivityListeners()` is called on mount

### Issue: Unlock fails with correct password

**Solution:**

- Check CSRF token is valid
- Verify session cookie is present
- Check Laravel logs for backend errors
- Ensure email matches authenticated user

### Issue: Timer doesn't reset on activity

**Solution:**

- Verify activity event listeners are attached
- Check `resetSessionTimer()` is called on activity
- Inspect browser console for errors

### Issue: Multiple login attempts fail after lock screen

**Solution:**

- Ensure session is properly regenerated on unlock
- Clear browser cookies and try again
- Check Laravel session driver configuration

## Issue Reference

**Issue #6:** "Logout and Inactivity Timeout"

Requirements:

- ✅ 5-minute automatic lock after inactivity
- ✅ Lock screen with current user display
- ✅ Password confirmation to continue session
- ✅ Option to logout completely
- ✅ User-friendly message about inactivity
- ✅ Clear message instead of automatic redirect
- ✅ Session continues after dismissing warning (until lock)
- ✅ Fixed login issues after previous user locked out

## Completion Status

- ✅ Backend unlock endpoint created
- ✅ LockScreen.vue component implemented
- ✅ Auth store updated with lock functionality
- ✅ App.vue integrated with LockScreen
- ✅ Session timer modified to lock instead of logout
- ✅ Activity tracking maintained
- ✅ Documentation completed

## Next Steps

1. Test lock screen functionality thoroughly
2. Verify no regression in existing session management
3. Test with all user roles (admin, staff, customer)
4. Ensure mobile responsiveness
5. Add analytics tracking for lock events
