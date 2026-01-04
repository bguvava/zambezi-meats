# Messages Module

## Overview

The Messages module allows admin and staff to manage customer contact form submissions and newsletter subscriptions through a centralized dashboard interface.

## Features

### Contact Messages

- **Capture & Store**: All contact form submissions are stored in the database
- **Spam Detection**: Honeypot field prevents automated spam submissions
- **Status Tracking**: Messages can be marked as new/read/replied/archived
- **IP Logging**: Records IP address and user agent for security
- **Reply Tracking**: Tracks which admin/staff member replied and when

### Newsletter Subscriptions

- **Email Collection**: Captures newsletter subscription requests
- **Unsubscribe System**: Token-based unsubscribe links
- **Resubscribe**: Supports reactivating previously unsubscribed emails
- **Status Management**: Active/Unsubscribed states
- **Export Capability**: Easily export subscriber lists

## Database Schema

### contact_messages Table

| Column     | Type         | Description                  |
| ---------- | ------------ | ---------------------------- |
| id         | bigint       | Primary key                  |
| name       | varchar(255) | Sender's full name           |
| email      | varchar(255) | Sender's email               |
| phone      | varchar(50)  | Optional phone number        |
| subject    | varchar(255) | Message subject              |
| message    | text         | Message content              |
| status     | enum         | new, read, replied, archived |
| ip_address | varchar(45)  | Sender's IP                  |
| user_agent | text         | Browser user agent           |
| honeypot   | varchar(255) | Spam detection field         |
| replied_by | bigint       | User ID who replied          |
| replied_at | timestamp    | Reply timestamp              |
| created_at | timestamp    | Submission time              |
| updated_at | timestamp    | Last update time             |
| deleted_at | timestamp    | Soft delete timestamp        |

### newsletter_subscriptions Table

| Column            | Type         | Description                      |
| ----------------- | ------------ | -------------------------------- |
| id                | bigint       | Primary key                      |
| email             | varchar(255) | Subscriber email (unique)        |
| name              | varchar(255) | Optional subscriber name         |
| status            | enum         | active, unsubscribed             |
| unsubscribe_token | varchar(64)  | Unique unsubscribe token         |
| ip_address        | varchar(45)  | Subscription IP                  |
| preferences       | json         | Future: subscription preferences |
| subscribed_at     | timestamp    | Subscription time                |
| unsubscribed_at   | timestamp    | Unsubscribe time                 |
| created_at        | timestamp    | Record creation                  |
| updated_at        | timestamp    | Last update                      |
| deleted_at        | timestamp    | Soft delete timestamp            |

## API Endpoints

### Public Endpoints

#### Submit Contact Form

```
POST /api/v1/contact
```

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+61 422 235 020",
  "subject": "Product Inquiry",
  "message": "I'd like to know more about...",
  "honeypot": ""
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Thank you! Your message has been received."
}
```

#### Subscribe to Newsletter

```
POST /api/v1/newsletter/subscribe
```

**Request Body:**

```json
{
  "email": "john@example.com",
  "name": "John Doe"
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Thank you for subscribing to our newsletter!",
  "data": {
    "id": 1
  }
}
```

#### Unsubscribe from Newsletter

```
GET /api/v1/newsletter/unsubscribe/{token}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "You have been unsubscribed from our newsletter."
}
```

### Admin/Staff Endpoints (Requires Authentication)

#### List Contact Messages

```
GET /api/v1/admin/messages
```

**Query Parameters:**

- `status`: Filter by status (new/read/replied/archived)
- `page`: Page number (default: 1)
- `per_page`: Results per page (default: 20)

**Response:**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+61 422 235 020",
        "subject": "Product Inquiry",
        "message": "I'd like to know more about...",
        "status": "new",
        "ip_address": "203.12.34.56",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "total": 45,
    "per_page": 20
  }
}
```

#### View Single Message

```
GET /api/v1/admin/messages/{id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+61 422 235 020",
    "subject": "Product Inquiry",
    "message": "I'd like to know more about...",
    "status": "read",
    "ip_address": "203.12.34.56",
    "user_agent": "Mozilla/5.0...",
    "replied_by": null,
    "replied_at": null,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

#### Update Message Status

```
PUT /api/v1/admin/messages/{id}
```

**Request Body:**

```json
{
  "status": "replied"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Message status updated successfully",
  "data": {
    "id": 1,
    "status": "replied",
    "replied_by": 2,
    "replied_at": "2024-01-15T11:00:00Z"
  }
}
```

#### Delete Message

```
DELETE /api/v1/admin/messages/{id}
```

**Response:**

```json
{
  "success": true,
  "message": "Message deleted successfully"
}
```

#### Get Message Statistics

```
GET /api/v1/admin/messages-stats
```

**Response:**

```json
{
  "success": true,
  "data": {
    "new": 12,
    "read": 8,
    "replied": 20,
    "archived": 5,
    "total": 45
  }
}
```

#### List Newsletter Subscriptions

```
GET /api/v1/admin/subscriptions
```

**Query Parameters:**

- `status`: Filter by status (active/unsubscribed)
- `page`: Page number
- `per_page`: Results per page

**Response:**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "email": "subscriber@example.com",
        "name": "Jane Smith",
        "status": "active",
        "subscribed_at": "2024-01-10T09:00:00Z",
        "created_at": "2024-01-10T09:00:00Z"
      }
    ],
    "total": 150
  }
}
```

#### Get Subscription Statistics

```
GET /api/v1/admin/subscriptions-stats
```

**Response:**

```json
{
  "success": true,
  "data": {
    "total": 150,
    "active": 145,
    "unsubscribed": 5
  }
}
```

#### Delete Subscription

```
DELETE /api/v1/admin/subscriptions/{id}
```

**Response:**

```json
{
  "success": true,
  "message": "Subscription deleted successfully"
}
```

## Frontend Components

### ContactPage.vue

**Location:** `frontend/src/pages/ContactPage.vue`

**Features:**

- Full contact form with validation
- Honeypot spam protection (hidden `website` field)
- Brand colors applied (#CF0D0F, #F6211F, #EFEFEF)
- Social media icons (Facebook, Instagram, TikTok)
- Company contact information
- Success state with option to send another message

**Form Fields:**

- Name (required)
- Email (required)
- Phone (optional)
- Subject (required)
- Message (required)
- Website (honeypot - hidden)

### MessagesPage.vue (Admin/Staff)

**Location:** `frontend/src/pages/admin/MessagesPage.vue`

**Features:**

- Two tabs: "Contact Messages" and "Newsletter Subscriptions"
- Statistics cards showing counts by status
- Data tables with pagination
- Filter by status
- View, update status, and delete actions
- Responsive design for mobile/tablet

## Spam Protection

### Honeypot Field

The contact form includes a hidden field named `website`. This field is:

- Hidden from legitimate users using CSS (`position: absolute; left: -9999px`)
- Has `tabindex="-1"` and `aria-hidden="true"`
- Ignored by legitimate users but often filled by spam bots
- Triggers silent rejection when filled (bot thinks submission succeeded)

**Implementation:**

```vue
<input
  v-model="form.website"
  type="text"
  name="website"
  autocomplete="off"
  tabindex="-1"
  class="absolute opacity-0 pointer-events-none"
  style="position: absolute; left: -9999px;"
  aria-hidden="true"
/>
```

**Backend Detection:**

```php
if (!empty($request->honeypot)) {
    // Spam detected - silently accept
    return response()->json([
        'success' => true,
        'message' => 'Thank you! Your message has been received.'
    ], 201);
}
```

## Color Palette

- Primary Red: `#CF0D0F`
- Secondary Red: `#F6211F`
- Light Gray: `#EFEFEF`
- Medium Gray: `#6F6F6F`
- Dark Gray: `#4D4B4C`

## Testing

### Contact Form Submission

```bash
curl -X POST http://localhost:8000/api/v1/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "subject": "Test Subject",
    "message": "Test message content",
    "honeypot": ""
  }'
```

### Newsletter Subscription

```bash
curl -X POST http://localhost:8000/api/v1/newsletter/subscribe \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "name": "Test User"
  }'
```

### Spam Detection (Honeypot)

```bash
curl -X POST http://localhost:8000/api/v1/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Spam Bot",
    "email": "spam@bot.com",
    "subject": "Spam",
    "message": "Spam content",
    "honeypot": "http://spamsite.com"
  }'
```

This should return success but not create a database record.

## Sidebar Navigation

Both admin and staff dashboards should include a "Messages" menu item:

```javascript
{
  id: 'messages',
  name: 'Messages',
  icon: 'ChatBubbleLeftRightIcon',
  path: '/admin/messages', // or '/staff/messages'
  badge: unreadCount // Show count of new/unread messages
}
```

## Future Enhancements

1. **Email Notifications**: Notify admin/staff of new messages
2. **Quick Reply**: Send email responses directly from dashboard
3. **Templates**: Pre-written reply templates
4. **Tags/Categories**: Categorize messages (inquiry, complaint, feedback)
5. **Assignment**: Assign messages to specific staff members
6. **Newsletter Campaigns**: Send bulk emails to active subscribers
7. **Subscription Preferences**: Allow subscribers to choose categories
8. **Export/Import**: CSV export of messages and subscriptions
9. **Analytics**: Track message volume, response times, conversion rates

## Issue Reference

- **Issue #4**: "add honeypot on the contact us page, add social media icons, add colors to the contact us page, remove the title next to the form, remove the contact us section on home page and keep the one on contact us page only. The contact us messages must be captured and stored in a database. Capture all subscription emails and store them in the database. Also capture requests to unsubscribe. create a Messages module for admin and staff to view all messages recieved on their dashboards. Update the sidebar menu to add this module. It must have 2 tabs: 1 - for contact messages, 2 - for newsletter subscriptions."

## Completion Status

- ✅ Database migrations created
- ✅ Models created with relationships and scopes
- ✅ ContactMessageController with honeypot detection
- ✅ NewsletterSubscriptionController with unsubscribe
- ✅ API routes registered
- ✅ ContactPage.vue updated with honeypot and social icons
- ✅ Brand colors applied to ContactPage
- ⏳ Messages admin UI component (next)
- ⏳ Sidebar menu updates (next)
- ⏳ Remove contact section from HomePage (next)
