# User Management API Endpoints

Complete reference for all User Management API endpoints with request/response examples.

## Base URL

All endpoints are prefixed with: `/api/v1/`

## Authentication

All endpoints require authentication using Laravel Sanctum cookies. Include the `X-XSRF-TOKEN` header with your requests.

## Authorization

Most endpoints require the `admin` role. See individual endpoints for specific requirements.

---

## Endpoints Overview

| Method | Endpoint                           | Description               | Auth Required | Role Required |
| ------ | ---------------------------------- | ------------------------- | ------------- | ------------- |
| GET    | `/admin/users`                     | List all users            | Yes           | admin         |
| GET    | `/admin/users/export`              | Export users to PDF       | Yes           | admin         |
| POST   | `/admin/users`                     | Create new user           | Yes           | admin         |
| GET    | `/admin/users/{id}`                | Get user details          | Yes           | admin         |
| PUT    | `/admin/users/{id}`                | Update user               | Yes           | admin         |
| PATCH  | `/admin/users/{id}/status`         | Change user status        | Yes           | admin         |
| PATCH  | `/admin/users/{id}/reset-password` | Reset user password       | Yes           | admin         |
| GET    | `/admin/users/{id}/activity`       | Get user activity (admin) | Yes           | admin         |
| GET    | `/users/{id}/activity`             | Get own activity          | Yes           | authenticated |

---

## 1. List Users

Get a paginated list of all users with optional search and filters.

### Endpoint

```
GET /api/v1/admin/users
```

### Query Parameters

| Parameter  | Type    | Required | Description                                         |
| ---------- | ------- | -------- | --------------------------------------------------- |
| `page`     | integer | No       | Page number (default: 1)                            |
| `per_page` | integer | No       | Items per page (default: 15)                        |
| `search`   | string  | No       | Search by name or email                             |
| `role`     | string  | No       | Filter by role: `admin`, `staff`, `customer`        |
| `status`   | string  | No       | Filter by status: `active`, `suspended`, `inactive` |

### Request Example

```bash
GET /api/v1/admin/users?page=1&search=john&role=customer&status=active
```

### Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "customer",
      "status": "active",
      "is_active": true,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "John Smith",
      "email": "john.smith@example.com",
      "role": "customer",
      "status": "active",
      "is_active": true,
      "created_at": "2025-01-14T08:15:00.000000Z",
      "updated_at": "2025-01-14T08:15:00.000000Z"
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 2,
  "last_page": 1,
  "from": 1,
  "to": 2
}
```

### Error Responses

**403 Forbidden** (Non-admin user):

```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "error": {
    "code": "UNAUTHORIZED"
  }
}
```

---

## 2. Export Users to PDF

Export the current users list (with filters) to a PDF file.

### Endpoint

```
GET /api/v1/admin/users/export
```

### Query Parameters

Same as list users endpoint (search, role, status) to apply filters to export.

### Request Example

```bash
GET /api/v1/admin/users/export?role=customer&status=active
```

### Response (200 OK)

Binary PDF file with headers:

```
Content-Type: application/pdf
Content-Disposition: attachment; filename="users-export-2025-01-15.pdf"
```

### Error Responses

**403 Forbidden**: Non-admin user  
**500 Server Error**: PDF generation failed

---

## 3. Create User

Create a new user with specified role and credentials.

### Endpoint

```
POST /api/v1/admin/users
```

### Request Body

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "securepassword123",
  "role": "staff"
}
```

### Validation Rules

| Field      | Rules                             |
| ---------- | --------------------------------- |
| `name`     | required, string, max:255         |
| `email`    | required, email, unique:users     |
| `password` | required, string, min:8           |
| `role`     | required, in:admin,staff,customer |

### Response (201 Created)

```json
{
  "success": true,
  "message": "User created successfully.",
  "data": {
    "id": 28,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "staff",
    "status": "active",
    "is_active": true,
    "created_at": "2025-01-15T14:20:00.000000Z",
    "updated_at": "2025-01-15T14:20:00.000000Z"
  }
}
```

### Error Responses

**422 Validation Error**:

```json
{
  "success": false,
  "message": "Validation failed.",
  "error": {
    "code": "VALIDATION_ERROR",
    "errors": {
      "email": ["The email has already been taken."],
      "password": ["The password must be at least 8 characters."]
    }
  }
}
```

---

## 4. Get User Details

Retrieve detailed information about a specific user.

### Endpoint

```
GET /api/v1/admin/users/{id}
```

### Path Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| `id`      | integer | User ID     |

### Request Example

```bash
GET /api/v1/admin/users/5
```

### Response (200 OK)

```json
{
  "success": true,
  "message": "User retrieved successfully.",
  "data": {
    "id": 5,
    "name": "John Customer",
    "email": "john.customer@example.com",
    "role": "customer",
    "status": "active",
    "is_active": true,
    "created_at": "2025-01-10T09:00:00.000000Z",
    "updated_at": "2025-01-14T15:30:00.000000Z"
  }
}
```

### Error Responses

**404 Not Found**:

```json
{
  "success": false,
  "message": "User not found.",
  "error": {
    "code": "NOT_FOUND"
  }
}
```

---

## 5. Update User

Update user information (name, email, role).

### Endpoint

```
PUT /api/v1/admin/users/{id}
```

### Path Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| `id`      | integer | User ID     |

### Request Body

```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "role": "staff"
}
```

### Validation Rules

| Field   | Rules                                               |
| ------- | --------------------------------------------------- |
| `name`  | required, string, max:255                           |
| `email` | required, email, unique:users (except current user) |
| `role`  | required, in:admin,staff,customer                   |

### Response (200 OK)

```json
{
  "success": true,
  "message": "User updated successfully.",
  "data": {
    "id": 5,
    "name": "John Updated",
    "email": "john.updated@example.com",
    "role": "staff",
    "status": "active",
    "is_active": true,
    "created_at": "2025-01-10T09:00:00.000000Z",
    "updated_at": "2025-01-15T14:45:00.000000Z"
  }
}
```

### Error Responses

**422 Validation Error**: See Create User errors  
**404 Not Found**: User doesn't exist

---

## 6. Change User Status

Change a user's status (active, suspended, inactive).

### Endpoint

```
PATCH /api/v1/admin/users/{id}/status
```

### Path Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| `id`      | integer | User ID     |

### Request Body

```json
{
  "status": "suspended"
}
```

### Validation Rules

| Field    | Rules                                  |
| -------- | -------------------------------------- |
| `status` | required, in:active,suspended,inactive |

### Response (200 OK)

```json
{
  "success": true,
  "message": "User status updated successfully.",
  "data": {
    "id": 5,
    "name": "John Customer",
    "email": "john.customer@example.com",
    "role": "customer",
    "status": "suspended",
    "is_active": false,
    "created_at": "2025-01-10T09:00:00.000000Z",
    "updated_at": "2025-01-15T15:00:00.000000Z"
  }
}
```

### Error Responses

**403 Forbidden** (Changing own status):

```json
{
  "success": false,
  "message": "You cannot change your own status.",
  "error": {
    "code": "FORBIDDEN"
  }
}
```

**422 Validation Error**:

```json
{
  "success": false,
  "message": "Validation failed.",
  "error": {
    "code": "VALIDATION_ERROR",
    "errors": {
      "status": ["The selected status is invalid."]
    }
  }
}
```

### Important Notes

- Admins **cannot** change their own status (self-protection)
- Status is logged to activity_logs table
- Only admins can change user statuses

---

## 7. Reset User Password

Generate a new password for a user and send it via email.

### Endpoint

```
PATCH /api/v1/admin/users/{id}/reset-password
```

### Path Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| `id`      | integer | User ID     |

### Request Body

No request body required.

### Request Example

```bash
PATCH /api/v1/admin/users/5/reset-password
```

### Response (200 OK)

```json
{
  "success": true,
  "message": "Password reset successfully. New password sent to user's email.",
  "data": {
    "id": 5,
    "name": "John Customer",
    "email": "john.customer@example.com",
    "role": "customer",
    "status": "active",
    "is_active": true,
    "created_at": "2025-01-10T09:00:00.000000Z",
    "updated_at": "2025-01-15T15:10:00.000000Z"
  }
}
```

### Error Responses

**404 Not Found**: User doesn't exist  
**403 Forbidden**: Non-admin user

### Important Notes

- Password is randomly generated (minimum 8 characters)
- New password is sent to user's email
- Action is logged to activity_logs table
- Password is hashed before storage

---

## 8. Get User Activity History (Admin)

Retrieve activity logs for a specific user (admin access).

### Endpoint

```
GET /api/v1/admin/users/{id}/activity
```

### Path Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| `id`      | integer | User ID     |

### Query Parameters

| Parameter | Type    | Required | Description              |
| --------- | ------- | -------- | ------------------------ |
| `page`    | integer | No       | Page number (default: 1) |

### Request Example

```bash
GET /api/v1/admin/users/5/activity?page=1
```

### Response (200 OK)

```json
{
  "data": [
    {
      "id": 123,
      "action": "user.status_changed",
      "description": "User status changed from active to suspended",
      "old_value": "active",
      "new_value": "suspended",
      "ip_address": "192.168.1.100",
      "created_at": "2025-01-15T15:00:00.000000Z"
    },
    {
      "id": 122,
      "action": "user.updated",
      "description": "User information updated",
      "old_value": "{\"name\":\"John\",\"email\":\"john@example.com\"}",
      "new_value": "{\"name\":\"John Updated\",\"email\":\"john@example.com\"}",
      "ip_address": "192.168.1.100",
      "created_at": "2025-01-15T14:45:00.000000Z"
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 25,
  "last_page": 2
}
```

### Error Responses

**404 Not Found**: User doesn't exist  
**403 Forbidden**: Non-admin user

---

## 9. Get Own Activity History

Retrieve activity logs for the authenticated user.

### Endpoint

```
GET /api/v1/users/{id}/activity
```

### Path Parameters

| Parameter | Type    | Description                             |
| --------- | ------- | --------------------------------------- |
| `id`      | integer | User ID (must match authenticated user) |

### Query Parameters

Same as admin endpoint (page).

### Request Example

```bash
GET /api/v1/users/5/activity?page=1
```

### Response (200 OK)

Same format as admin endpoint.

### Error Responses

**403 Forbidden** (Viewing other user's activity):

```json
{
  "success": false,
  "message": "You can only view your own activity.",
  "error": {
    "code": "FORBIDDEN"
  }
}
```

### Important Notes

- Users can **only** view their **own** activity
- Admins can use either endpoint to view any user's activity
- Policy enforces: `$user->isAdmin() || $user->id === $model->id`

---

## Common Error Codes

| Code               | HTTP Status | Description               |
| ------------------ | ----------- | ------------------------- |
| `VALIDATION_ERROR` | 422         | Request validation failed |
| `UNAUTHORIZED`     | 401         | Not authenticated         |
| `FORBIDDEN`        | 403         | Insufficient permissions  |
| `NOT_FOUND`        | 404         | Resource not found        |
| `SERVER_ERROR`     | 500         | Internal server error     |

---

## Rate Limiting

All API endpoints are subject to Laravel's default rate limiting:

- **60 requests per minute** for authenticated users
- Exceeding limit returns `429 Too Many Requests`

---

## Activity Log Actions

The following actions are logged to `activity_logs` table:

| Action                | Description              |
| --------------------- | ------------------------ |
| `user.created`        | New user created         |
| `user.updated`        | User information updated |
| `user.status_changed` | User status changed      |
| `user.password_reset` | User password reset      |
| `user.exported`       | Users exported to PDF    |

Each log entry includes:

- `action`: Action type
- `description`: Human-readable description
- `old_value`: Previous value (if applicable)
- `new_value`: New value (if applicable)
- `ip_address`: IP address of requester
- `user_id`: Target user ID
- `performed_by`: Admin user ID who performed action

---

## Testing Endpoints

### Using cURL

**List Users**:

```bash
curl -X GET "http://localhost/api/v1/admin/users?page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Create User**:

```bash
curl -X POST "http://localhost/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "role": "customer"
  }'
```

**Change Status**:

```bash
curl -X PATCH "http://localhost/api/v1/admin/users/5/status" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"status": "suspended"}'
```

### Using Postman

1. Import collection from `docs/postman/user-management.json` (if available)
2. Set environment variables:
   - `base_url`: `http://localhost/api/v1`
   - `token`: Your authentication token
3. Run requests from collection

---

## Best Practices

1. **Always include** `Accept: application/json` header
2. **Handle errors** gracefully on the client side
3. **Validate inputs** before sending requests
4. **Use pagination** for large datasets
5. **Cache responses** when appropriate
6. **Implement retry logic** for network errors
7. **Log API errors** for debugging
8. **Use HTTPS** in production

---

## Changelog

### Version 1.0.0 (January 2025)

- Initial release with 9 endpoints
- Full CRUD operations
- Activity logging
- PDF export
- Self-protection for admins

---

**Last Updated**: January 15, 2025  
**API Version**: 1.0.0  
**Maintainer**: Zambezi Meats Development Team
