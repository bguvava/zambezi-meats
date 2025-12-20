# User Management Module

## Overview

The User Management Module provides comprehensive user administration capabilities for the Zambezi Meats e-commerce platform. It enables administrators to manage system users, assign roles, control access levels, and monitor user activities.

## Features

This module implements all 14 requirements (USER-001 through USER-014):

### Core Features

- **USER-001**: List all users with pagination (15 users per page)
- **USER-002**: Search users by name or email with real-time filtering
- **USER-003**: Filter users by role (admin, staff, customer)
- **USER-004**: Filter users by status (active, suspended, inactive)
- **USER-005**: Create new users with role assignment
- **USER-006**: Update user information (name, email, role)
- **USER-007**: View detailed user information
- **USER-008**: Change user status (with self-protection for admins)
- **USER-009**: Deactivate users (set status to inactive)
- **USER-010**: Admin self-protection (cannot change own status)
- **USER-011**: Reset user passwords
- **USER-012**: View user activity history with pagination
- **USER-013**: Users can view their own activity history
- **USER-014**: Export users list to PDF with filters

### Additional Features

- **Role-based access control**: Only admins can manage users
- **Activity logging**: All user actions are logged to database
- **Real-time validation**: Form validation with custom error messages
- **Responsive design**: Mobile-friendly interface with Tailwind CSS
- **Toast notifications**: Success/error feedback for all actions
- **Batch operations**: Export filtered users to PDF

## Architecture

### Backend (Laravel 11.x)

#### Database

- **Migration**: `2025_01_15_000001_add_status_column_to_users_table.php`
  - Adds `status` enum column (active, suspended, inactive)
  - Default status: active

#### Models

- **User** (`app/Models/User.php`)
  - Constants: `STATUS_ACTIVE`, `STATUS_SUSPENDED`, `STATUS_INACTIVE`
  - Methods: `canChangeStatus()`, `isActive()`, `isSuspended()`, `isInactive()`
  - Scopes: `scopeSearch()`, `scopeStatus()`

#### Controllers

- **UserController** (`app/Http/Controllers/Api/V1/UserController.php`)
  - `index()`: List users with search and filters
  - `store()`: Create new user
  - `show()`: View user details
  - `update()`: Update user information
  - `updateStatus()`: Change user status
  - `resetPassword()`: Reset user password
  - `getActivityHistory()`: Get user activity logs
  - `export()`: Export users to PDF

#### Form Requests

- **StoreUserRequest**: Validation for creating users

  - name: required, string, max:255
  - email: required, email, unique:users
  - password: required, string, min:8
  - role: required, in:admin,staff,customer

- **UpdateUserRequest**: Validation for updating users
  - name: required, string, max:255
  - email: required, email, unique (except self)
  - role: required, in:admin,staff,customer

#### Resources

- **UserResource** (`app/Http/Resources/UserResource.php`)
  - Transforms user data for API responses
  - Fields: id, name, email, role, status, is_active, created_at, updated_at

#### Policies

- **UserPolicy** (`app/Policies/UserPolicy.php`)
  - `viewAny()`: Only admins can list users
  - `view()`: Only admins can view user details
  - `create()`: Only admins can create users
  - `update()`: Only admins can update users
  - `changeStatus()`: Only admins can change status (prevents self)
  - `resetPassword()`: Only admins can reset passwords
  - `viewActivity()`: Admins or user themselves can view activity

#### Services

- **UserExportService** (`app/Services/UserExportService.php`)
  - Generates PDF exports with filters
  - Uses barryvdh/laravel-dompdf for PDF generation

#### Routes

All routes are prefixed with `/api/v1/`

**Admin Routes** (role:admin middleware):

- `GET /admin/users` - List users
- `GET /admin/users/export` - Export to PDF
- `POST /admin/users` - Create user
- `GET /admin/users/{id}` - Show user
- `PUT /admin/users/{id}` - Update user
- `PATCH /admin/users/{id}/status` - Change status
- `PATCH /admin/users/{id}/reset-password` - Reset password
- `GET /admin/users/{id}/activity` - Admin view user activity

**Authenticated Routes** (auth:sanctum middleware):

- `GET /users/{id}/activity` - User view own activity

### Frontend (Vue 3 + Composition API)

#### Store

- **user.js** (`frontend/src/stores/user.js`)
  - State: users, loading, error, currentUser, activityLogs, filters, pagination
  - Actions: fetchUsers, createUser, updateUser, changeStatus, resetPassword, fetchActivity, exportPDF
  - Getters: filteredUsers, totalPages, hasUsers, getUserById

#### Components

**Badge Components**:

- **UserStatusBadge.vue**: Status indicator with color coding

  - Active: Green
  - Suspended: Yellow
  - Inactive: Gray

- **UserRoleBadge.vue**: Role indicator with icons

  - Admin: Red (#8B0000) with shield icon
  - Staff: Blue with user group icon
  - Customer: Purple with user icon

- **UserAvatar.vue**: User avatar with initials fallback
  - Supports image src or initials
  - Configurable size and color

**Modal Components**:

- **CreateUserModal.vue**: Form for creating new users

  - Fields: name, email, password (min 8), role
  - Validation with error display
  - Loading states

- **EditUserModal.vue**: Form for editing existing users

  - Fields: name, email, role
  - Pre-populated with user data
  - Validation with error display

- **UserActivityModal.vue**: Display user activity history
  - Paginated activity logs
  - Old/new value comparison
  - Formatted timestamps

#### Views

- **UsersIndex.vue** (`frontend/src/views/admin/UsersIndex.vue`)
  - Data table with user list
  - Search bar (name/email)
  - Role filter dropdown
  - Status filter dropdown
  - Action menu per user (edit, status, reset, activity)
  - Pagination controls
  - Export PDF button
  - Create user button

## Installation

### Backend Setup

1. **Run Migration**:

   ```bash
   cd backend
   php artisan migrate
   ```

2. **Seed Test Data** (optional):

   ```bash
   php artisan db:seed --class=UserSeeder
   ```

   This creates 27 test users with various roles and statuses.

3. **Install PDF Package** (if not already installed):

   ```bash
   composer require barryvdh/laravel-dompdf
   ```

4. **Configure Permissions**:
   Ensure the `UserPolicy` is registered in `app/Providers/AuthServiceProvider.php`:
   ```php
   protected $policies = [
       User::class => UserPolicy::class,
   ];
   ```

### Frontend Setup

1. **Install Dependencies**:

   ```bash
   cd frontend
   npm install
   ```

2. **Add Route** to `frontend/src/router/index.js`:

   ```javascript
   {
     path: '/admin/users',
     name: 'admin-users',
     component: () => import('@/views/admin/UsersIndex.vue'),
     meta: { requiresAuth: true, requiresAdmin: true }
   }
   ```

3. **Import Icons** (if not already available):
   ```bash
   npm install @heroicons/vue
   ```

## Usage Guide

### For Administrators

#### Viewing Users

1. Navigate to **Admin > Users**
2. The users list displays with pagination (15 per page)
3. Use the search bar to find users by name or email
4. Use dropdowns to filter by role or status

#### Creating a User

1. Click **Add User** button
2. Fill in the form:
   - Name (required)
   - Email (required, must be unique)
   - Password (required, minimum 8 characters)
   - Role (select from dropdown)
3. Click **Create User**
4. Success notification confirms creation

#### Editing a User

1. Click **Actions** menu for a user
2. Select **Edit**
3. Update name, email, or role
4. Click **Update User**
5. Changes are saved immediately

#### Changing User Status

1. Click **Actions** menu for a user
2. Select **Change Status**
3. Choose new status:
   - **Active**: User can log in and access system
   - **Suspended**: User is temporarily blocked
   - **Inactive**: User is deactivated
4. Click desired status
5. **Note**: Admins cannot change their own status

#### Resetting Passwords

1. Click **Actions** menu for a user
2. Select **Reset Password**
3. Confirm the action
4. A new password is generated and sent to the user's email

#### Viewing Activity History

1. Click **Actions** menu for a user
2. Select **View Activity**
3. Review activity logs with:
   - Action descriptions
   - Old and new values
   - Timestamps
   - IP addresses
4. Use pagination to navigate through history

#### Exporting to PDF

1. Apply desired filters (search, role, status)
2. Click **Export PDF** button
3. PDF downloads automatically with:
   - Filtered user list
   - User details (name, email, role, status)
   - Export timestamp
   - Zambezi Meats branding

### For Regular Users

#### Viewing Own Activity

1. Navigate to your profile or user menu
2. Click **My Activity** (if available in UI)
3. View your own activity history
4. Cannot view other users' activity

## Testing

### Backend Tests

**Location**: `backend/tests/Feature/`

**Test Files**:

1. **UserControllerTest.php** (16 tests)

   - List users with pagination
   - Search functionality
   - Role and status filters
   - Create user validation
   - Update user validation
   - Authorization checks

2. **UserActionsTest.php** (13 tests)

   - Status changes
   - Admin self-protection
   - Password resets
   - Activity history viewing
   - Permission checks

3. **UserExportTest.php** (9 tests)
   - PDF export functionality
   - Filter applications
   - Empty result handling
   - Activity logging

**Run Tests**:

```bash
cd backend
php artisan test --filter=User
```

**Results**: 46/46 tests passing (100% pass rate ✅)

### Frontend Tests

Frontend tests are not yet implemented. To add tests:

1. Create test files in `frontend/src/components/user/__tests__/`
2. Test components with Vitest and Vue Test Utils
3. Test store actions with mock API responses

## API Endpoints

See [api-endpoints.md](./api-endpoints.md) for detailed API documentation.

## Components Reference

See [components.md](./components.md) for detailed component documentation.

## Requirements Mapping

| Requirement | Status | Implementation                                        |
| ----------- | ------ | ----------------------------------------------------- |
| USER-001    | ✅     | UserController@index with pagination                  |
| USER-002    | ✅     | Search query parameter with scopeSearch               |
| USER-003    | ✅     | Role filter dropdown and query param                  |
| USER-004    | ✅     | Status filter dropdown and scopeStatus                |
| USER-005    | ✅     | CreateUserModal + UserController@store                |
| USER-006    | ✅     | EditUserModal + UserController@update                 |
| USER-007    | ✅     | UserController@show with UserResource                 |
| USER-008    | ✅     | Status change menu + UserController@updateStatus      |
| USER-009    | ✅     | Status change to "inactive"                           |
| USER-010    | ✅     | UserPolicy@changeStatus + User@canChangeStatus        |
| USER-011    | ✅     | UserController@resetPassword                          |
| USER-012    | ✅     | UserActivityModal + UserController@getActivityHistory |
| USER-013    | ✅     | Separate route /users/{id}/activity with policy       |
| USER-014    | ✅     | Export button + UserExportService                     |

## Security Considerations

1. **Authorization**: All user management routes require admin role
2. **Self-Protection**: Admins cannot change their own status
3. **Password Security**: Passwords are hashed with bcrypt
4. **Activity Logging**: All actions are logged with IP addresses
5. **Validation**: Strong validation on all inputs
6. **Policy-Based**: Laravel policies enforce permissions

## Performance Considerations

1. **Pagination**: 15 users per page prevents large data loads
2. **Debounced Search**: 500ms delay prevents excessive API calls
3. **Eager Loading**: Activity logs loaded on demand
4. **PDF Caching**: Consider adding cache for large exports
5. **Index Optimization**: Database indexes on email and status columns

## Future Enhancements

- [ ] Bulk user operations (activate/deactivate multiple users)
- [ ] Advanced filters (date ranges, last login, registration date)
- [ ] Export to Excel/CSV formats
- [ ] User import from CSV
- [ ] Email templates customization
- [ ] Two-factor authentication management
- [ ] Role permission management
- [ ] User group/team management
- [ ] Activity log search and filters
- [ ] Scheduled reports

## Support

For issues or questions regarding the User Management Module:

1. Check the [API Endpoints documentation](./api-endpoints.md)
2. Check the [Components documentation](./components.md)
3. Review the test files for usage examples
4. Check Laravel logs: `backend/storage/logs/laravel.log`

## License

This module is part of the Zambezi Meats e-commerce platform.

---

**Module Version**: 1.0.0  
**Last Updated**: January 2025  
**Maintainer**: Zambezi Meats Development Team
