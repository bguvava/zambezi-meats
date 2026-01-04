# Session Persistence Testing Guide

This guide provides comprehensive testing procedures for validating the session persistence functionality in Zambezi Meats e-commerce platform.

## Overview

The session persistence feature ensures users remain logged in during active usage and are automatically logged out after 30 minutes of inactivity. This enhances user experience while maintaining security.

## Session Configuration

### Timeout Settings

- **Session Timeout**: 30 minutes of inactivity
- **Warning Display**: 29 minutes (1 minute before logout)
- **Activity Tracking**: Updates on every route navigation
- **Storage**: localStorage for session state persistence

### Session Lock Behavior

- Session lock screen appears after timeout
- User must re-authenticate to continue
- Shopping cart data is preserved
- Previously visited page is restored after re-login

## Test Environment Setup

### Prerequisites

- ✅ Local development server running (Vite frontend + Laravel backend)
- ✅ Clean browser profile (no cached sessions)
- ✅ Test user accounts (customer, staff, admin)
- ✅ Network throttling disabled (for accurate timing)
- ✅ Browser console open (to monitor debug logs)

### Test Browsers

Test on the following browsers to ensure cross-browser compatibility:

| Browser          | Version | Priority |
| ---------------- | ------- | -------- |
| Google Chrome    | Latest  | High     |
| Mozilla Firefox  | Latest  | High     |
| Microsoft Edge   | Latest  | Medium   |
| Safari (macOS)   | Latest  | Medium   |
| Safari (iOS)     | Latest  | Low      |
| Chrome (Android) | Latest  | Low      |

## Test Scenarios

### Scenario 1: Basic Login & Session Persistence

**Objective**: Verify user remains logged in during active usage

**Steps**:

1. Navigate to `http://localhost:5173`
2. Click "Login" in navigation
3. Enter credentials:
   - Email: `customer@example.com`
   - Password: `password123`
4. Click "Login" button
5. Navigate to different pages: Shop, About, Contact
6. Wait 2 minutes between navigations
7. Check profile dropdown in header

**Expected Results**:

- ✅ User successfully logs in
- ✅ Profile dropdown shows user name
- ✅ Session remains active during navigation
- ✅ User stays logged in after 2+ minutes of activity
- ✅ No session warnings appear
- ✅ Shopping cart persists across pages

**Pass Criteria**: Session remains active for at least 10 minutes of continuous navigation

---

### Scenario 2: Session Timeout After Inactivity

**Objective**: Verify automatic logout after 30 minutes of inactivity

**Steps**:

1. Log in as customer (Scenario 1)
2. Navigate to Dashboard or Shop
3. **Do not interact with the browser for 30 minutes**
4. After 30 minutes, try to navigate or click something

**Expected Results**:

- ✅ Session lock screen appears after 30 minutes
- ✅ Lock screen displays "Session Expired" message
- ✅ User must re-authenticate
- ✅ Shopping cart data is preserved
- ✅ After login, user returns to previous page

**Note**: Use browser automation or set `SESSION_TIMEOUT_MINUTES=1` in `.env` for faster testing

---

### Scenario 3: Session Warning Display

**Objective**: Verify warning appears 1 minute before logout

**Steps**:

1. Log in as customer
2. Navigate to any page
3. Wait 29 minutes without interaction
4. Observe the warning modal/notification

**Expected Results**:

- ✅ Warning modal appears after 29 minutes
- ✅ Warning message: "Your session will expire in 1 minute due to inactivity"
- ✅ "Continue Session" button is present
- ✅ Clicking "Continue Session" resets the timer
- ✅ Countdown shows remaining seconds

**Pass Criteria**: Warning appears exactly 1 minute before timeout

---

### Scenario 4: Activity Tracking

**Objective**: Verify activity resets the session timer

**Steps**:

1. Log in as customer
2. Navigate to Shop page
3. Wait 28 minutes (1 minute before warning)
4. Navigate to Products page (any navigation)
5. Wait another 28 minutes
6. Navigate to Cart page
7. Check session status

**Expected Results**:

- ✅ Session timer resets on each navigation
- ✅ No warning appears after first 28 minutes + navigation
- ✅ No warning appears after second 28 minutes + navigation
- ✅ User remains logged in throughout
- ✅ Session only expires after 30 minutes of **complete inactivity**

**Pass Criteria**: Session remains active for 60+ minutes with periodic navigation

---

### Scenario 5: Tab Close & Reopen

**Objective**: Verify session persists when tab is closed and reopened

**Steps**:

1. Log in as customer
2. Navigate to Dashboard
3. Add item to shopping cart
4. Close the browser tab
5. Wait 5 minutes
6. Reopen browser
7. Navigate to `http://localhost:5173`

**Expected Results**:

- ✅ User is still logged in
- ✅ Shopping cart contains previous items
- ✅ Session timer continues from where it left off
- ✅ No re-authentication required

**Pass Criteria**: Session persists for at least 25 minutes after tab reopen

---

### Scenario 6: Multiple Tabs

**Objective**: Verify session syncs across multiple tabs

**Steps**:

1. Log in as customer in Tab 1
2. Open Tab 2 to same site
3. Navigate in Tab 1
4. Check login status in Tab 2
5. Log out in Tab 1
6. Check Tab 2 status

**Expected Results**:

- ✅ User appears logged in on Tab 2
- ✅ Navigation in Tab 1 updates session in Tab 2
- ✅ Logout in Tab 1 immediately logs out Tab 2
- ✅ Both tabs share same session state

**Pass Criteria**: Session state is synchronized across all tabs

---

### Scenario 7: Page Refresh During Active Session

**Objective**: Verify session survives page refresh

**Steps**:

1. Log in as customer
2. Navigate to Shop page
3. Add items to cart
4. Press F5 or click browser refresh
5. Check login status and cart

**Expected Results**:

- ✅ User remains logged in after refresh
- ✅ Shopping cart items persist
- ✅ Session timer continues
- ✅ No session lock screen appears

**Pass Criteria**: No data loss or session interruption on refresh

---

### Scenario 8: Session After Forced Logout

**Objective**: Verify clean logout behavior

**Steps**:

1. Log in as customer
2. Click "Logout" button
3. Observe redirect
4. Try to access protected route (e.g., `/dashboard`)
5. Navigate back to homepage
6. Log in again

**Expected Results**:

- ✅ User successfully logs out
- ✅ Redirected to homepage
- ✅ Shopping cart is cleared (or persisted for guest)
- ✅ Protected routes redirect to login
- ✅ Re-login works without issues
- ✅ New session starts with fresh timer

**Pass Criteria**: Clean session termination and restart

---

### Scenario 9: Cross-Role Session Handling

**Objective**: Verify different user roles have proper session handling

**Test Accounts**:

- Customer: `customer@example.com` / `password123`
- Staff: `staff@example.com` / `password123`
- Admin: `admin@zambezimeats.com` / `admin123`

**Steps**:

1. Log in as **Customer**
2. Test session timeout (30 min)
3. Log out
4. Log in as **Staff**
5. Test session timeout (30 min)
6. Log out
7. Log in as **Admin**
8. Test session timeout (30 min)

**Expected Results**:

- ✅ All roles have same timeout duration
- ✅ Session lock screen appears for all roles
- ✅ Role-specific dashboards accessible after re-auth
- ✅ No permission errors during session

**Pass Criteria**: Consistent session behavior across all user roles

---

### Scenario 10: Session During API Calls

**Objective**: Verify session remains active during long API operations

**Steps**:

1. Log in as customer
2. Navigate to Shop page
3. Start a file upload or large form submission
4. Wait for operation to complete
5. Check session status

**Expected Results**:

- ✅ Session remains active during API call
- ✅ No timeout during operation
- ✅ Session timer resets after successful API response
- ✅ No session lock screen during upload

**Pass Criteria**: API operations don't interfere with session

---

## Automated Testing Scripts

### Quick Session Test (1 minute timeout)

For rapid testing, modify `.env`:

```env
# Backend: backend/.env
SESSION_LIFETIME=1  # 1 minute timeout

# Frontend: frontend/.env
VITE_SESSION_TIMEOUT_MINUTES=1  # 1 minute timeout
```

Then restart both servers:

```bash
# Backend
cd backend
php artisan config:clear
php artisan serve

# Frontend
cd frontend
npm run dev
```

### Browser Console Tests

Open browser console and run:

```javascript
// Check current session state
console.log("Session:", localStorage.getItem("user"));
console.log("Last activity:", localStorage.getItem("lastActivity"));

// Check auth store
console.log("Auth store:", window.$app?.config?.globalProperties?.$auth);

// Manually clear session (test logout)
localStorage.removeItem("user");
localStorage.removeItem("lastActivity");
window.location.reload();

// Check session age (milliseconds since last activity)
const lastActivity = localStorage.getItem("lastActivity");
const now = Date.now();
const age = now - parseInt(lastActivity);
console.log("Session age:", Math.floor(age / 1000), "seconds");
console.log(
  "Minutes until timeout:",
  Math.floor((30 * 60 * 1000 - age) / 1000 / 60)
);
```

## Bug Reporting Template

If you find session-related bugs, use this template:

````markdown
**Bug Title**: [Brief description]

**Environment**:

- Browser: [Chrome 120 / Firefox 121 / etc.]
- OS: [Windows 11 / macOS 14 / etc.]
- User Role: [Customer / Staff / Admin]

**Steps to Reproduce**:

1. [First step]
2. [Second step]
3. [etc.]

**Expected Behavior**:
[What should happen]

**Actual Behavior**:
[What actually happened]

**Session State** (from localStorage):

```json
{
  "user": { ... },
  "lastActivity": "1234567890"
}
```
````

**Screenshots**:
[Attach screenshots if applicable]

**Console Errors**:
[Paste any browser console errors]

```

## Performance Metrics

Track these metrics during testing:

| Metric | Target | Actual | Pass/Fail |
|--------|--------|--------|-----------|
| Login time | < 2s | | |
| Session check time | < 100ms | | |
| Logout time | < 1s | | |
| Session persistence across refresh | 100% | | |
| Warning display accuracy | ±5s | | |
| Session sync across tabs | < 500ms | | |

## Known Issues & Workarounds

### Issue 1: Session Not Persisting in Safari
**Problem**: Safari's strict cookie policies may prevent session persistence
**Workaround**: Ensure cookies are enabled and not blocked
**Status**: Under investigation

### Issue 2: Session Timeout Too Aggressive
**Problem**: Users complain about frequent logouts
**Workaround**: Increase timeout to 60 minutes in production
**Status**: Monitor user feedback

### Issue 3: Warning Modal Doesn't Appear
**Problem**: Some users don't see warning before timeout
**Workaround**: Ensure browser notifications are enabled
**Status**: Fixed in v1.2

## Accessibility Testing

Ensure session lock screen is accessible:

- ✅ **Keyboard Navigation**: Can tab through all elements
- ✅ **Screen Reader**: Announces "Session Expired" message
- ✅ **Color Contrast**: Text meets WCAG AA standards
- ✅ **Focus Management**: Focus moves to password field on lock
- ✅ **ARIA Labels**: Proper labels on all interactive elements

## Security Testing

Verify session security:

- ✅ **Session Token**: Properly encrypted and stored
- ✅ **CSRF Protection**: Token validated on each request
- ✅ **XSS Prevention**: No script injection in session data
- ✅ **Session Fixation**: New token generated on login
- ✅ **Concurrent Sessions**: Only one active session per user

## Regression Testing Checklist

After any session-related code changes, test:

- [ ] Scenario 1: Basic login & persistence
- [ ] Scenario 2: Timeout after inactivity
- [ ] Scenario 3: Warning display
- [ ] Scenario 5: Tab close & reopen
- [ ] Scenario 6: Multiple tabs sync
- [ ] Scenario 7: Page refresh
- [ ] Scenario 8: Forced logout

## Production Deployment Checklist

Before deploying session changes:

- [ ] All test scenarios pass
- [ ] Performance metrics meet targets
- [ ] Security testing completed
- [ ] Accessibility verified
- [ ] Cross-browser testing done
- [ ] Mobile testing completed
- [ ] Session timeout configured for production (30 min)
- [ ] Error logging enabled
- [ ] Session analytics tracking set up

## Related Documentation

- [Authentication Implementation](../auth/README.md)
- [Frontend Session Management](../../frontend/src/stores/auth.js)
- [Backend Session Configuration](../../backend/config/session.php)
- [Security Best Practices](../security/session-security.md)

## Change Log

| Date | Version | Changes |
|------|---------|---------|
| 2024-01-03 | 1.0 | Initial testing guide created |
| 2024-12-19 | 1.1 | Session timeout fixed to 30 minutes |
| 2024-12-19 | 1.2 | Removed aggressive checkSession from router |
```
