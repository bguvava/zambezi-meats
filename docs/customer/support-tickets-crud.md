# Support Tickets CRUD - Cancel Functionality

## Overview

Customers can now cancel their own support tickets from the customer dashboard. This implements a soft delete mechanism that tracks when and who cancelled the ticket.

## Requirements

- **Requirement ID**: CUST-019
- **Feature**: Cancel support ticket
- **User Story**: As a customer, I want to be able to cancel my support tickets so that I can manage my support requests.

## Database Schema

### New Columns in `support_tickets` Table

| Column              | Type      | Nullable | Default | Description                                         |
| ------------------- | --------- | -------- | ------- | --------------------------------------------------- |
| `cancelled_at`      | timestamp | Yes      | NULL    | Timestamp when ticket was cancelled                 |
| `cancelled_by_user` | boolean   | No       | false   | Whether ticket was cancelled by customer (vs admin) |

### Index

- `cancelled_at` - Indexed for performance when querying cancelled tickets

## Backend Implementation

### 1. Model Updates (`SupportTicket.php`)

**New Status Constant:**

```php
public const STATUS_CANCELLED = 'cancelled';
```

**New Fillable Fields:**

```php
'cancelled_at',
'cancelled_by_user',
```

**New Methods:**

```php
/**
 * Cancel the ticket by user.
 */
public function cancelByUser(): void
{
    $this->update([
        'status' => self::STATUS_CANCELLED,
        'cancelled_at' => now(),
        'cancelled_by_user' => true,
    ]);
}

/**
 * Check if ticket is cancelled.
 */
public function isCancelled(): bool
{
    return $this->status === self::STATUS_CANCELLED;
}
```

### 2. Controller Endpoint (`CustomerController.php`)

**Method:** `cancelTicket(Request $request, int $id)`
**Route:** `DELETE /api/v1/customer/tickets/{id}`
**Middleware:** Sanctum auth (customer only)

**Business Rules:**

1. Only the ticket owner can cancel their ticket
2. Tickets can only be cancelled if status is `open` or `in_progress`
3. Cannot cancel tickets that are `resolved`, `closed`, or already `cancelled`
4. Sets `status` to `cancelled`, `cancelled_at` to current timestamp, and `cancelled_by_user` to `true`

**Validation Responses:**

| Scenario          | Status Code | Message                                      |
| ----------------- | ----------- | -------------------------------------------- |
| Already cancelled | 422         | "Ticket is already cancelled."               |
| Resolved/closed   | 422         | "Cannot cancel a resolved or closed ticket." |
| Success           | 200         | "Ticket cancelled successfully."             |

**Example Response (Success):**

```json
{
  "success": true,
  "message": "Ticket cancelled successfully."
}
```

**Example Response (Error):**

```json
{
  "success": false,
  "message": "Cannot cancel a resolved or closed ticket."
}
```

### 3. API Routes (`api.php`)

```php
Route::delete('/tickets/{id}', [CustomerController::class, 'cancelTicket'])
    ->name('api.v1.customer.tickets.cancel');
```

## Frontend Implementation

### 1. Store Updates (`tickets.js`)

**New Status Configuration:**

```javascript
cancelled: {
  label: "Cancelled",
  color: "red",
  bgColor: "bg-red-100 text-red-800",
}
```

**New Action:**

```javascript
async function cancelTicket(ticketId) {
  isSaving.value = true;
  saveError.value = null;

  try {
    const response = await api.delete(`/customer/tickets/${ticketId}`);

    if (response.data.success) {
      // Remove from local state
      tickets.value = tickets.value.filter((ticket) => ticket.id !== ticketId);

      // Clear current ticket if it's the one being cancelled
      if (currentTicket.value?.id === ticketId) {
        currentTicket.value = null;
      }
    }

    return {
      success: true,
      message: response.data.message || "Ticket cancelled successfully",
    };
  } catch (err) {
    saveError.value = err.response?.data?.message || "Failed to cancel ticket";
    return { success: false, message: saveError.value };
  } finally {
    isSaving.value = false;
  }
}
```

### 2. UI Updates (`SupportTicketsPage.vue`)

**Cancel Button:**

- Displayed in ticket detail view header
- Only shown for tickets with status: `open` or `in_progress`
- Opens confirmation modal on click

**Confirmation Modal:**

- Warning icon (red triangle)
- Clear messaging about action being irreversible
- Two actions: "Keep Ticket" (cancel) or "Yes, Cancel Ticket" (confirm)
- Shows loading spinner during API call
- Auto-closes on success and redirects to tickets list

**Helper Method:**

```javascript
function canCancelTicket(ticket) {
  return (
    ticket &&
    ticket.status !== "resolved" &&
    ticket.status !== "closed" &&
    ticket.status !== "cancelled"
  );
}
```

## User Flow

1. **View Ticket**

   - Customer navigates to Support Tickets page
   - Clicks on a ticket to view details

2. **Initiate Cancellation**

   - If ticket is cancellable (open or in-progress), "Cancel Ticket" button appears
   - Customer clicks "Cancel Ticket" button
   - Confirmation modal appears

3. **Confirm Cancellation**

   - Modal warns: "This action cannot be undone"
   - Customer clicks "Yes, Cancel Ticket"
   - API call is made with loading spinner

4. **Post-Cancellation**
   - Success toast notification appears
   - Customer is redirected back to tickets list
   - Cancelled ticket is removed from view
   - Tickets list is refreshed

## Testing Checklist

### Backend Tests

- [ ] Can cancel open ticket
- [ ] Can cancel in-progress ticket
- [ ] Cannot cancel resolved ticket (422 error)
- [ ] Cannot cancel closed ticket (422 error)
- [ ] Cannot cancel already cancelled ticket (422 error)
- [ ] Cannot cancel another user's ticket (404 error)
- [ ] `cancelled_at` timestamp is set correctly
- [ ] `cancelled_by_user` is set to `true`
- [ ] `status` is changed to `cancelled`

### Frontend Tests

- [ ] Cancel button appears for open tickets
- [ ] Cancel button appears for in-progress tickets
- [ ] Cancel button does NOT appear for resolved tickets
- [ ] Cancel button does NOT appear for closed tickets
- [ ] Cancel button does NOT appear for cancelled tickets
- [ ] Confirmation modal opens on click
- [ ] Modal can be dismissed with "Keep Ticket"
- [ ] Modal can be dismissed by clicking backdrop
- [ ] Loading spinner shows during API call
- [ ] Success toast appears on successful cancellation
- [ ] Error toast appears on failed cancellation
- [ ] User is redirected to tickets list after cancellation
- [ ] Tickets list refreshes after cancellation
- [ ] Cancelled status badge displays correctly (red)

### Integration Tests

- [ ] End-to-end: Create ticket → Cancel ticket → Verify in database
- [ ] Multiple tickets: Cancel one, verify others remain
- [ ] Concurrent requests: Handle rapid cancel button clicks gracefully

## Security Considerations

1. **Authorization**: Only ticket owner can cancel
2. **Validation**: Strict status checks prevent invalid state transitions
3. **Audit Trail**: `cancelled_at` and `cancelled_by_user` provide accountability
4. **Rate Limiting**: Standard API rate limiting applies
5. **CSRF Protection**: Sanctum token required

## Database Migration

**Migration File:** `2026_01_03_100000_add_cancellation_to_support_tickets.php`

**Run Migration:**

```bash
cd backend
php artisan migrate
```

**Rollback (if needed):**

```bash
cd backend
php artisan migrate:rollback --step=1
```

## API Documentation

### Cancel Ticket

**Endpoint:** `DELETE /api/v1/customer/tickets/{id}`

**Authentication:** Required (Sanctum token)

**Path Parameters:**

- `id` (integer, required) - Ticket ID

**Success Response (200):**

```json
{
  "success": true,
  "message": "Ticket cancelled successfully."
}
```

**Error Response (422 - Already Cancelled):**

```json
{
  "success": false,
  "message": "Ticket is already cancelled."
}
```

**Error Response (422 - Cannot Cancel):**

```json
{
  "success": false,
  "message": "Cannot cancel a resolved or closed ticket."
}
```

**Error Response (404 - Not Found):**

```json
{
  "message": "Not found."
}
```

## Future Enhancements

1. **Admin View**: Show cancelled tickets in admin dashboard with filter
2. **Reopen**: Allow admins to reopen cancelled tickets if needed
3. **Analytics**: Track cancellation rates and reasons
4. **Cancellation Reason**: Add optional reason field for customer feedback
5. **Email Notification**: Send email confirmation when ticket is cancelled
6. **Restore**: Allow customers to restore recently cancelled tickets (within X hours)

## Related Documentation

- [Support Tickets Overview](./README.md)
- [API Endpoints](../deployment/api-endpoints.md)
- [Customer Dashboard](./README.md)

## Changelog

### Version 1.0.0 (2026-01-03)

- Initial implementation of ticket cancellation feature
- Added `cancelled_at` and `cancelled_by_user` columns
- Created cancel API endpoint
- Implemented frontend cancel button with confirmation modal
- Added comprehensive validation and error handling
