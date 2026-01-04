# Settings Module Documentation

## Overview

The Settings module provides comprehensive system configuration management for the Zambezi Meats e-commerce platform. It enables administrators to configure store information, payment gateways, email settings, delivery options, security policies, and more.

---

## Requirements

| ID      | Requirement                                           | Priority | Status      |
| ------- | ----------------------------------------------------- | -------- | ----------- |
| SET-001 | Store information management (name, address, contact) | High     | ✅ Complete |
| SET-002 | Store logo upload and management                      | High     | ✅ Complete |
| SET-003 | ABN and business registration display                 | Medium   | ✅ Complete |
| SET-004 | Operating hours configuration by day                  | High     | ✅ Complete |
| SET-005 | Holiday dates management                              | Medium   | ✅ Complete |
| SET-006 | Stripe payment gateway integration                    | High     | ✅ Complete |
| SET-007 | PayPal payment gateway integration                    | Medium   | ✅ Complete |
| SET-008 | Afterpay payment gateway integration                  | Medium   | ✅ Complete |
| SET-009 | Cash on delivery option                               | Low      | ✅ Complete |
| SET-010 | SMTP email configuration                              | High     | ✅ Complete |
| SET-011 | Email template management                             | High     | ✅ Complete |
| SET-012 | Test email functionality                              | Medium   | ✅ Complete |
| SET-013 | Default currency configuration                        | High     | ✅ Complete |
| SET-014 | Exchange rate API integration                         | Medium   | ✅ Complete |
| SET-015 | Manual exchange rate override                         | Low      | ✅ Complete |
| SET-016 | Minimum order amount setting                          | High     | ✅ Complete |
| SET-017 | Free delivery threshold                               | High     | ✅ Complete |
| SET-018 | Default delivery fee configuration                    | High     | ✅ Complete |
| SET-019 | Session timeout configuration                         | High     | ✅ Complete |
| SET-020 | Password policy settings                              | High     | ✅ Complete |
| SET-021 | Order notification email recipients                   | Medium   | ✅ Complete |
| SET-022 | Low stock alert email recipients                      | Medium   | ✅ Complete |
| SET-023 | Email/SMS notification toggles                        | Medium   | ✅ Complete |
| SET-024 | Feature flags (wishlist, reviews, guest checkout)     | Medium   | ✅ Complete |
| SET-025 | SEO meta tags configuration                           | Medium   | ✅ Complete |
| SET-026 | Social media URL configuration                        | Low      | ✅ Complete |
| SET-027 | Settings export functionality                         | Medium   | ✅ Complete |
| SET-028 | Settings import functionality                         | Medium   | ✅ Complete |
| SET-029 | Settings change history/audit log                     | High     | ✅ Complete |
| SET-030 | Public settings endpoint (no auth)                    | Medium   | ✅ Complete |

---

## API Endpoints

### Settings Management

| Method | Endpoint                      | Description                 | Auth  |
| ------ | ----------------------------- | --------------------------- | ----- |
| GET    | `/api/admin/settings`         | Get all settings            | Admin |
| GET    | `/api/admin/settings/{group}` | Get settings by group       | Admin |
| PUT    | `/api/admin/settings/{group}` | Update settings group       | Admin |
| POST   | `/api/admin/settings/logo`    | Upload store logo           | Admin |
| GET    | `/api/admin/settings/history` | Get settings change history | Admin |
| GET    | `/api/admin/settings/export`  | Export all settings         | Admin |
| POST   | `/api/admin/settings/import`  | Import settings from JSON   | Admin |
| GET    | `/api/settings/public`        | Get public settings         | None  |

### Email Templates

| Method | Endpoint                            | Description              | Auth  |
| ------ | ----------------------------------- | ------------------------ | ----- |
| GET    | `/api/admin/email-templates`        | List all email templates | Admin |
| GET    | `/api/admin/email-templates/{name}` | Get specific template    | Admin |
| PUT    | `/api/admin/email-templates/{name}` | Update email template    | Admin |
| POST   | `/api/admin/email-templates/test`   | Send test email          | Admin |

---

## Settings Groups

### Store Settings (`store`)

Basic store information and branding.

| Setting      | Type   | Description                |
| ------------ | ------ | -------------------------- |
| `store_name` | string | Business name              |
| `tagline`    | string | Store tagline/slogan       |
| `logo`       | string | Logo file URL              |
| `address`    | string | Street address             |
| `suburb`     | string | Suburb/city                |
| `state`      | string | State/province             |
| `postcode`   | string | Postal/ZIP code            |
| `phone`      | string | Contact phone number       |
| `email`      | string | Contact email address      |
| `abn`        | string | Australian Business Number |

### Operating Hours (`operating`)

Store hours and holiday management.

| Setting              | Type    | Description                        |
| -------------------- | ------- | ---------------------------------- |
| `hours`              | object  | Operating hours per day of week    |
| `hours.{day}.open`   | string  | Opening time (HH:mm format)        |
| `hours.{day}.close`  | string  | Closing time (HH:mm format)        |
| `hours.{day}.closed` | boolean | Whether closed that day            |
| `holiday_dates`      | array   | List of holiday dates (YYYY-MM-DD) |

### Payment Settings (`payment`)

Payment gateway configurations.

| Setting                | Type    | Description                   |
| ---------------------- | ------- | ----------------------------- |
| `stripe_enabled`       | boolean | Enable Stripe payments        |
| `stripe_public_key`    | string  | Stripe publishable key        |
| `stripe_secret_key`    | string  | Stripe secret key (encrypted) |
| `paypal_enabled`       | boolean | Enable PayPal payments        |
| `paypal_client_id`     | string  | PayPal client ID              |
| `paypal_secret`        | string  | PayPal secret (encrypted)     |
| `afterpay_enabled`     | boolean | Enable Afterpay payments      |
| `afterpay_merchant_id` | string  | Afterpay merchant ID          |
| `afterpay_secret`      | string  | Afterpay secret (encrypted)   |
| `cod_enabled`          | boolean | Enable Cash on Delivery       |

### Email Settings (`email`)

SMTP and mail configuration.

| Setting             | Type    | Description                           |
| ------------------- | ------- | ------------------------------------- |
| `smtp_host`         | string  | SMTP server hostname                  |
| `smtp_port`         | integer | SMTP port (default: 587)              |
| `smtp_username`     | string  | SMTP username                         |
| `smtp_password`     | string  | SMTP password (encrypted)             |
| `smtp_encryption`   | string  | Encryption type: `tls`, `ssl`, `none` |
| `mail_from_name`    | string  | Sender display name                   |
| `mail_from_address` | string  | Sender email address                  |

### Currency Settings (`currency`)

Currency and exchange rate management.

| Setting                 | Type   | Description                                   |
| ----------------------- | ------ | --------------------------------------------- |
| `default_currency`      | string | Default currency code (e.g., AUD)             |
| `exchange_rate_api_key` | string | API key for exchange rate service             |
| `manual_exchange_rate`  | float  | Manual exchange rate override                 |
| `rate_update_frequency` | string | Update frequency: `hourly`, `daily`, `weekly` |

### Delivery Settings (`delivery`)

Delivery fees and thresholds.

| Setting                   | Type  | Description                   |
| ------------------------- | ----- | ----------------------------- |
| `minimum_order_amount`    | float | Minimum order value required  |
| `free_delivery_threshold` | float | Order value for free delivery |
| `default_delivery_fee`    | float | Standard delivery fee         |

### Security Settings (`security`)

Authentication and security policies.

| Setting               | Type    | Description                |
| --------------------- | ------- | -------------------------- |
| `session_timeout`     | integer | Session timeout in minutes |
| `password_min_length` | integer | Minimum password length    |
| `require_uppercase`   | boolean | Require uppercase letters  |
| `require_lowercase`   | boolean | Require lowercase letters  |
| `require_number`      | boolean | Require numeric characters |
| `require_special`     | boolean | Require special characters |

### Notification Settings (`notifications`)

Alert and notification configuration.

| Setting                      | Type    | Description                          |
| ---------------------------- | ------- | ------------------------------------ |
| `order_notification_emails`  | array   | Email addresses for order alerts     |
| `low_stock_emails`           | array   | Email addresses for low stock alerts |
| `enable_email_notifications` | boolean | Enable email notifications           |
| `enable_sms_notifications`   | boolean | Enable SMS notifications             |

### Feature Flags (`features`)

Toggle platform features.

| Setting                 | Type    | Description                   |
| ----------------------- | ------- | ----------------------------- |
| `enable_wishlist`       | boolean | Enable wishlist functionality |
| `enable_reviews`        | boolean | Enable product reviews        |
| `enable_guest_checkout` | boolean | Allow guest checkout          |
| `enable_multi_currency` | boolean | Enable multi-currency support |

### SEO Settings (`seo`)

Search engine optimization metadata.

| Setting            | Type   | Description              |
| ------------------ | ------ | ------------------------ |
| `meta_title`       | string | Default page title       |
| `meta_description` | string | Default meta description |
| `meta_keywords`    | string | Default meta keywords    |

### Social Media (`social`)

Social media profile URLs.

| Setting         | Type   | Description           |
| --------------- | ------ | --------------------- |
| `facebook_url`  | string | Facebook page URL     |
| `instagram_url` | string | Instagram profile URL |
| `twitter_url`   | string | Twitter/X profile URL |
| `youtube_url`   | string | YouTube channel URL   |

---

## Query Parameters

### History Endpoint

| Parameter   | Type    | Default      | Description                           |
| ----------- | ------- | ------------ | ------------------------------------- |
| `page`      | integer | 1            | Page number                           |
| `per_page`  | integer | 15           | Items per page (max: 100)             |
| `group`     | string  | null         | Filter by settings group              |
| `user_id`   | integer | null         | Filter by user who made change        |
| `from_date` | string  | null         | Filter changes from date (YYYY-MM-DD) |
| `to_date`   | string  | null         | Filter changes to date (YYYY-MM-DD)   |
| `sort`      | string  | `created_at` | Sort field                            |
| `order`     | string  | `desc`       | Sort order: `asc`, `desc`             |

---

## Email Template Variables

Available variables for email templates:

### Order Templates

| Variable               | Description              |
| ---------------------- | ------------------------ |
| `{{order_number}}`     | Order ID/number          |
| `{{order_date}}`       | Date order was placed    |
| `{{order_total}}`      | Total order amount       |
| `{{customer_name}}`    | Customer's full name     |
| `{{customer_email}}`   | Customer's email address |
| `{{delivery_address}}` | Full delivery address    |
| `{{order_items}}`      | List of ordered items    |
| `{{payment_method}}`   | Payment method used      |
| `{{tracking_number}}`  | Shipping tracking number |
| `{{tracking_url}}`     | Tracking URL             |

### Account Templates

| Variable                | Description             |
| ----------------------- | ----------------------- |
| `{{user_name}}`         | User's display name     |
| `{{user_email}}`        | User's email address    |
| `{{reset_link}}`        | Password reset link     |
| `{{verification_link}}` | Email verification link |
| `{{login_url}}`         | Login page URL          |

### Store Variables

| Variable            | Description         |
| ------------------- | ------------------- |
| `{{store_name}}`    | Store business name |
| `{{store_email}}`   | Store contact email |
| `{{store_phone}}`   | Store phone number  |
| `{{store_address}}` | Full store address  |
| `{{store_logo}}`    | Store logo URL      |
| `{{current_year}}`  | Current year        |

---

## Import/Export Format

### Export Format (JSON)

```json
{
  "version": "1.0",
  "exported_at": "2024-12-20T10:30:00Z",
  "exported_by": "admin@zambezi.com",
  "settings": {
    "store": {
      "store_name": "Zambezi Meats",
      "tagline": "Quality Meats",
      "logo": "logo.png"
    },
    "payment": {
      "stripe_enabled": true,
      "paypal_enabled": false
    }
  }
}
```

### Import Requirements

- File format: JSON
- Maximum file size: 1MB
- Required field: `version`
- Required field: `settings` (object)
- Sensitive fields (API keys, passwords) are NOT included in exports
- Import validates all settings before applying

### Import Validation Rules

1. Version compatibility check
2. Valid JSON structure
3. Valid setting keys only
4. Type validation per setting
5. Required fields present
6. No conflicting values

---

## Frontend Components

### Store Components

| Component                    | Location                | Description                       |
| ---------------------------- | ----------------------- | --------------------------------- |
| `SettingsLayout.vue`         | `views/admin/settings/` | Main settings layout with sidebar |
| `StoreSettings.vue`          | `views/admin/settings/` | Store information form            |
| `OperatingHoursSettings.vue` | `views/admin/settings/` | Hours and holidays editor         |
| `PaymentSettings.vue`        | `views/admin/settings/` | Payment gateway configuration     |
| `EmailSettings.vue`          | `views/admin/settings/` | SMTP and email configuration      |
| `EmailTemplateEditor.vue`    | `views/admin/settings/` | Email template editor             |
| `CurrencySettings.vue`       | `views/admin/settings/` | Currency configuration            |
| `DeliverySettings.vue`       | `views/admin/settings/` | Delivery fees and thresholds      |
| `SecuritySettings.vue`       | `views/admin/settings/` | Security policy settings          |
| `NotificationSettings.vue`   | `views/admin/settings/` | Notification preferences          |
| `FeatureSettings.vue`        | `views/admin/settings/` | Feature toggles                   |
| `SEOSettings.vue`            | `views/admin/settings/` | SEO meta configuration            |
| `SocialSettings.vue`         | `views/admin/settings/` | Social media links                |
| `SettingsHistory.vue`        | `views/admin/settings/` | Audit log viewer                  |
| `SettingsImportExport.vue`   | `views/admin/settings/` | Import/export interface           |

### Composables

| Composable              | Description                |
| ----------------------- | -------------------------- |
| `useSettings`           | Settings state and actions |
| `useSettingsValidation` | Form validation rules      |

### Store

| Store           | File                      | Description                         |
| --------------- | ------------------------- | ----------------------------------- |
| `adminSettings` | `stores/adminSettings.js` | Pinia store for settings management |

---

## Test Coverage Summary

### Unit Tests: `adminSettings.test.js`

| Category            | Tests   | Description                   |
| ------------------- | ------- | ----------------------------- |
| Initial State       | 8       | Verify default state values   |
| Getters             | 10      | Test computed properties      |
| fetchAllSettings    | 5       | All settings fetch action     |
| fetchSettingsGroup  | 5       | Group-specific fetch          |
| updateSettingsGroup | 6       | Settings update actions       |
| uploadStoreLogo     | 4       | Logo upload functionality     |
| Email Templates     | 6       | Template CRUD operations      |
| Import/Export       | 6       | Settings backup/restore       |
| History             | 4       | Audit log retrieval           |
| Utility Actions     | 4       | Helper action methods         |
| Edge Cases          | 5       | Error handling and boundaries |
| Additional Tests    | 10+     | Group-specific settings       |
| **Total**           | **73+** | Comprehensive coverage        |

### Test Categories

- **State Management**: Initial values, state mutations
- **Getters**: Computed values, derived state
- **Actions**: API calls, state updates
- **Error Handling**: Network errors, validation errors
- **Edge Cases**: Null values, empty responses

---

## Security Considerations

1. **Sensitive Data**: API keys and passwords are encrypted at rest
2. **Export Safety**: Sensitive fields excluded from exports
3. **Audit Trail**: All changes logged with user attribution
4. **Role-Based Access**: Admin-only endpoints
5. **Input Validation**: Server-side validation for all settings
6. **Rate Limiting**: API rate limits on settings endpoints

---

## Related Documentation

- [API Endpoints](../deployment/api-endpoints.md)
- [Authentication](../auth/README.md)
- [Admin Dashboard](../dashboard/README.md)
