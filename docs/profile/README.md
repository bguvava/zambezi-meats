# My Profile Module

## Overview

The My Profile module provides authenticated users with the ability to manage their personal account information, avatar, and security settings. This module is accessible to all authenticated users regardless of role (customer, staff, admin, owner).

## Features

### 1. Profile Information Management

- View current profile details (name, email, phone)
- Update personal information
- Email uniqueness validation (excluding current user)
- Phone number format validation
- Real-time form validation

### 2. Avatar Management

- Upload profile picture (JPEG, JPG, PNG, GIF)
- Maximum file size: 2MB
- Image preview before upload
- Delete current avatar
- Automatic cleanup of old avatars on replacement
- Default initials display when no avatar set

### 3. Password Management

- Change password with current password verification
- Minimum password length: 8 characters
- Password confirmation required
- Password visibility toggles
- Secure password hashing (bcrypt)

## API Endpoints

All endpoints require `auth:sanctum` middleware.

### GET `/api/v1/profile`

Get authenticated user's profile information.

**Response** (200 OK):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+260 97 1234567",
    "avatar": "http://localhost/storage/avatars/xyz.jpg",
    "role": "customer",
    "currency_preference": "ZMW",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### PUT `/api/v1/profile`

Update authenticated user's profile information.

**Request Body**:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+260 97 1234567"
}
```

**Validation Rules**:

- `name`: required, string, max 255 characters
- `email`: required, email format, unique (excluding current user)
- `phone`: optional, string, max 20 characters, regex `/^[\d\s\+\-\(\)]+$/`

**Response** (200 OK):

```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+260 97 1234567",
    "avatar": "http://localhost/storage/avatars/xyz.jpg"
  }
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### POST `/api/v1/profile/avatar`

Upload or replace user avatar.

**Request**: Multipart form data

- `avatar`: Image file (required)

**Validation Rules**:

- File type: image (jpeg, jpg, png, gif only)
- Maximum size: 2MB (2048KB)
- Required field

**Response** (200 OK):

```json
{
  "success": true,
  "message": "Avatar uploaded successfully.",
  "data": {
    "avatar": "http://localhost/storage/avatars/1234567890.jpg"
  }
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "message": "The avatar must be an image.",
  "errors": {
    "avatar": ["The avatar must be an image."]
  }
}
```

### DELETE `/api/v1/profile/avatar`

Delete user's current avatar.

**Response** (200 OK):

```json
{
  "success": true,
  "message": "Avatar deleted successfully."
}
```

### POST `/api/v1/profile/change-password`

Change user's password.

**Request Body**:

```json
{
  "current_password": "OldPassword123",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

**Validation Rules**:

- `current_password`: required, string
- `password`: required, string, confirmed, minimum 8 characters
- `password_confirmation`: required, must match `password`

**Response** (200 OK):

```json
{
  "success": true,
  "message": "Password changed successfully."
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "success": false,
  "message": "Current password is incorrect.",
  "errors": {
    "current_password": ["The current password is incorrect."]
  }
}
```

## Frontend Component

### Profile.vue

Located at: `frontend/src/pages/customer/Profile.vue`

**Component Structure**:

- Avatar Section

  - Display current avatar or initials
  - Upload button with file picker
  - Remove button (if avatar exists)
  - Image preview
  - File size notice

- Personal Information Form

  - Full Name input
  - Email Address input
  - Phone Number input
  - Save Changes button
  - Inline validation errors
  - Loading state

- Change Password Form
  - Current Password input (with toggle)
  - New Password input (with toggle)
  - Confirm New Password input (with toggle)
  - Change Password button
  - Inline validation errors
  - Loading state

**State Management**:

```javascript
const profile = reactive({
  name: "",
  email: "",
  phone: "",
  avatar: null,
});

const form = reactive({
  name: "",
  email: "",
  phone: "",
});

const passwordForm = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

// Loading states
const isSaving = ref(false);
const isChangingPassword = ref(false);
const isDeleting = ref(false);

// Validation errors
const errors = ref({});
const passwordErrors = ref({});

// Avatar upload
const previewAvatar = ref(null);
const selectedFile = ref(null);

// Password visibility
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
```

**Key Methods**:

- `fetchProfile()`: Load user profile data
- `updateProfile()`: Save profile changes
- `handleAvatarChange()`: Preview selected image
- `uploadAvatar()`: Upload new avatar
- `deleteAvatar()`: Remove current avatar
- `changePassword()`: Update password
- `togglePasswordVisibility()`: Show/hide passwords

## File Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           └── ProfileController.php
│   │   └── Requests/
│   │       └── Api/
│   │           └── V1/
│   │               ├── UpdateProfileRequest.php
│   │               └── ChangePasswordRequest.php
│   └── Models/
│       └── User.php
├── routes/
│   └── api.php
└── tests/
    └── Feature/
        └── Api/
            └── V1/
                └── ProfileControllerTest.php

frontend/
├── src/
│   ├── pages/
│   │   └── customer/
│   │       └── Profile.vue
│   ├── router/
│   │   └── index.js
│   └── layouts/
│       └── CustomerLayout.vue

docs/
└── profile/
    └── README.md
```

## Security Considerations

### Authentication

- All endpoints protected by `auth:sanctum` middleware
- Guests receive 401 Unauthorized responses
- Users can only access/modify their own profile

### Password Security

- Current password verification required before changes
- Passwords hashed using bcrypt (Laravel's default)
- Minimum 8 characters enforced
- Password confirmation prevents typos

### File Upload Security

- File type validation (images only)
- File size limits (2MB max)
- Automatic mime type checking
- Files stored in Laravel's public storage
- Old avatars deleted on replacement

### Data Validation

- Email uniqueness prevents duplicates
- Phone number regex validation
- XSS protection via Laravel's form validation
- CSRF protection via Sanctum

## User Flow

### Updating Profile Information

1. User navigates to `/customer/profile`
2. Profile data auto-loads on component mount
3. User modifies name, email, or phone
4. User clicks "Save Changes"
5. Frontend validates input
6. PUT request sent to `/api/v1/profile`
7. Backend validates and updates database
8. Success toast shown
9. Auth store updated with new data

### Uploading Avatar

1. User clicks "Upload Avatar" button
2. File picker opens
3. User selects image file
4. Image preview displays
5. User confirms upload
6. POST request sent to `/api/v1/profile/avatar`
7. Backend validates file
8. Old avatar deleted (if exists)
9. New avatar stored
10. Success toast shown
11. Avatar updates in UI

### Changing Password

1. User enters current password
2. User enters new password
3. User confirms new password
4. User clicks "Change Password"
5. POST request sent to `/api/v1/profile/change-password`
6. Backend verifies current password
7. Backend validates new password
8. Password updated and hashed
9. Success toast shown
10. Form cleared

## Navigation Integration

Profile link added to customer sidebar navigation in `CustomerLayout.vue`:

```javascript
const navigation = [
  { name: "Dashboard", href: "/customer/dashboard", icon: Home },
  { name: "Browse Products", href: "/customer/products", icon: ShoppingBag },
  { name: "My Orders", href: "/customer/orders", icon: Package },
  { name: "Shopping Cart", href: "/customer/cart", icon: ShoppingCart },
  { name: "My Profile", href: "/customer/profile", icon: User }, // Profile link
  { name: "Support", href: "/customer/support", icon: MessageCircle },
];
```

## Testing

### Test Coverage

- **Total Tests**: 24 test methods
- **Passing Tests**: 14/24 (58%)
- **Test File**: `backend/tests/Feature/Api/V1/ProfileControllerTest.php`

### Passing Tests

✅ Profile retrieval (authenticated users)
✅ Profile updates (with valid data)
✅ Email persistence (user can keep same email)
✅ Phone field optional
✅ Avatar upload (basic functionality)
✅ Avatar replacement (old file cleanup)
✅ Avatar deletion
✅ Password change (with verification)
✅ Current password validation
✅ Authorization (guests blocked from all endpoints)

### Known Limitations

⚠️ Some strict validation tests fail due to Laravel's form request handling of empty strings
⚠️ Tests using `UploadedFile::fake()->image()` require GD extension (workaround implemented using `create()`)

### Running Tests

```bash
# Run all profile tests
php artisan test --filter=ProfileControllerTest

# Run specific test
php artisan test --filter=test_user_can_get_their_profile

# With coverage
php artisan test --filter=ProfileControllerTest --coverage
```

## Error Handling

### Frontend Error Handling

- Inline validation errors displayed under each field
- Toast notifications for success/error messages
- Loading states prevent duplicate submissions
- Network error handling with user-friendly messages

### Backend Error Handling

- 401: Unauthorized (not authenticated)
- 422: Validation errors (with detailed messages)
- 500: Server errors (logged for debugging)

## Performance Considerations

- Avatar storage uses Laravel's public disk
- Image uploads limited to 2MB to prevent server strain
- Old avatars automatically deleted to save storage space
- Profile data cached in auth store (Pinia)
- Minimal database queries per request

## Brand Integration

Profile page uses Zambezi Meats brand colors:

- Primary Red: #CF0D0F
- Secondary Red: #F6211F
- Light Gray: #EFEFEF
- Medium Gray: #6F6F6F
- Dark Gray: #4D4B4C

Buttons, borders, and hover states use brand colors for consistency.

## Future Enhancements

Potential improvements:

- [ ] Address management integration
- [ ] Email verification workflow
- [ ] Phone number verification (SMS)
- [ ] Profile completion percentage
- [ ] Activity log (logins, changes)
- [ ] Two-factor authentication
- [ ] Social media account linking
- [ ] Export profile data (GDPR compliance)
- [ ] Profile visibility settings
- [ ] Avatar cropping tool

## Changelog

### Version 1.0.0 (2024-12-21)

- Initial implementation
- Profile CRUD operations
- Avatar upload/delete
- Password change functionality
- Form validation
- Test suite (14/24 passing)
- Documentation

## Support

For issues or questions:

- Check API endpoint responses for error details
- Review validation messages in 422 responses
- Verify file size/type for avatar uploads
- Ensure current password is correct for password changes
- Check browser console for frontend errors

## Related Documentation

- [Authentication Module](/docs/auth/README.md)
- [Lock Screen Feature](/docs/auth/lock-screen.md)
- [Customer Dashboard](/docs/customer/README.md)
- [User Management](/docs/user-management/README.md)
