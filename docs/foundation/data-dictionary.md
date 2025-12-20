# Zambezi Meats - Data Dictionary

## Tables Reference

### users

User accounts for customers, staff, and administrators.

| Column              | Type         | Nullable | Default    | Description                  |
| ------------------- | ------------ | -------- | ---------- | ---------------------------- |
| id                  | bigint       | No       | auto       | Primary key                  |
| name                | varchar(255) | No       | -          | User's full name             |
| email               | varchar(255) | No       | -          | Unique email address         |
| email_verified_at   | timestamp    | Yes      | NULL       | Email verification timestamp |
| password            | varchar(255) | No       | -          | Bcrypt hashed password       |
| role                | enum         | No       | 'customer' | Role: customer, staff, admin |
| phone               | varchar(20)  | Yes      | NULL       | Phone number                 |
| avatar              | varchar(255) | Yes      | NULL       | Avatar image path            |
| is_active           | boolean      | No       | true       | Account active status        |
| currency_preference | varchar(3)   | No       | 'AUD'      | Preferred currency: AUD, USD |
| remember_token      | varchar(100) | Yes      | NULL       | Laravel remember token       |
| created_at          | timestamp    | Yes      | NULL       | Creation timestamp           |
| updated_at          | timestamp    | Yes      | NULL       | Last update timestamp        |

**Indexes:** email (unique), role, is_active

---

### categories

Product categories with hierarchical support.

| Column      | Type         | Nullable | Default | Description           |
| ----------- | ------------ | -------- | ------- | --------------------- |
| id          | bigint       | No       | auto    | Primary key           |
| name        | varchar(255) | No       | -       | Category name         |
| slug        | varchar(255) | No       | -       | URL-friendly slug     |
| description | text         | Yes      | NULL    | Category description  |
| image       | varchar(255) | Yes      | NULL    | Category image path   |
| parent_id   | bigint       | Yes      | NULL    | Parent category FK    |
| sort_order  | int          | No       | 0       | Display order         |
| is_active   | boolean      | No       | true    | Category visibility   |
| created_at  | timestamp    | Yes      | NULL    | Creation timestamp    |
| updated_at  | timestamp    | Yes      | NULL    | Last update timestamp |

**Indexes:** slug (unique), parent_id, is_active, sort_order

---

### products

Meat products available for sale.

| Column            | Type          | Nullable | Default | Description            |
| ----------------- | ------------- | -------- | ------- | ---------------------- |
| id                | bigint        | No       | auto    | Primary key            |
| category_id       | bigint        | No       | -       | Category FK            |
| name              | varchar(255)  | No       | -       | Product name           |
| slug              | varchar(255)  | No       | -       | URL-friendly slug      |
| description       | text          | Yes      | NULL    | Full description       |
| short_description | text          | Yes      | NULL    | Brief description      |
| price_aud         | decimal(10,2) | No       | -       | Price in AUD           |
| sale_price_aud    | decimal(10,2) | Yes      | NULL    | Sale price in AUD      |
| stock             | int           | No       | 0       | Current stock quantity |
| sku               | varchar(50)   | No       | -       | Stock keeping unit     |
| unit              | varchar(10)   | No       | 'kg'    | Unit: kg, piece, pack  |
| weight_kg         | decimal(10,3) | Yes      | NULL    | Weight in kilograms    |
| is_featured       | boolean       | No       | false   | Featured product flag  |
| is_active         | boolean       | No       | true    | Product visibility     |
| meta              | json          | Yes      | NULL    | Additional metadata    |
| created_at        | timestamp     | Yes      | NULL    | Creation timestamp     |
| updated_at        | timestamp     | Yes      | NULL    | Last update timestamp  |
| deleted_at        | timestamp     | Yes      | NULL    | Soft delete timestamp  |

**Indexes:** slug (unique), sku (unique), category_id, is_featured, is_active, price_aud

---

### product_images

Product image gallery.

| Column     | Type         | Nullable | Default | Description                |
| ---------- | ------------ | -------- | ------- | -------------------------- |
| id         | bigint       | No       | auto    | Primary key                |
| product_id | bigint       | No       | -       | Product FK                 |
| image_path | varchar(255) | No       | -       | Image file path            |
| alt_text   | varchar(255) | Yes      | NULL    | Alt text for accessibility |
| sort_order | int          | No       | 0       | Display order              |
| is_primary | boolean      | No       | false   | Primary image flag         |
| created_at | timestamp    | Yes      | NULL    | Creation timestamp         |
| updated_at | timestamp    | Yes      | NULL    | Last update timestamp      |

**Indexes:** product_id, sort_order, is_primary

---

### addresses

Customer delivery addresses.

| Column         | Type         | Nullable | Default     | Description                |
| -------------- | ------------ | -------- | ----------- | -------------------------- |
| id             | bigint       | No       | auto        | Primary key                |
| user_id        | bigint       | No       | -           | User FK                    |
| label          | varchar(50)  | No       | -           | Address label (Home, Work) |
| street_address | varchar(255) | No       | -           | Street address             |
| apartment      | varchar(50)  | Yes      | NULL        | Apartment/unit number      |
| suburb         | varchar(100) | No       | -           | Suburb name                |
| state          | varchar(50)  | No       | -           | State (NSW, VIC, etc.)     |
| postcode       | varchar(10)  | No       | -           | Postal code                |
| country        | varchar(50)  | No       | 'Australia' | Country                    |
| is_default     | boolean      | No       | false       | Default address flag       |
| created_at     | timestamp    | Yes      | NULL        | Creation timestamp         |
| updated_at     | timestamp    | Yes      | NULL        | Last update timestamp      |

**Indexes:** user_id, is_default

---

### delivery_zones

Delivery zones with pricing.

| Column                  | Type          | Nullable | Default | Description                 |
| ----------------------- | ------------- | -------- | ------- | --------------------------- |
| id                      | bigint        | No       | auto    | Primary key                 |
| name                    | varchar(255)  | No       | -       | Zone name                   |
| suburbs                 | json          | No       | -       | Array of suburb names       |
| delivery_fee            | decimal(10,2) | No       | 0       | Delivery fee in AUD         |
| free_delivery_threshold | decimal(10,2) | Yes      | NULL    | Min order for free delivery |
| estimated_days          | int           | No       | 1       | Estimated delivery days     |
| is_active               | boolean       | No       | true    | Zone availability           |
| created_at              | timestamp     | Yes      | NULL    | Creation timestamp          |
| updated_at              | timestamp     | Yes      | NULL    | Last update timestamp       |

**Indexes:** is_active

---

### orders

Customer orders.

| Column                | Type          | Nullable | Default   | Description             |
| --------------------- | ------------- | -------- | --------- | ----------------------- |
| id                    | bigint        | No       | auto      | Primary key             |
| order_number          | varchar(20)   | No       | -         | Unique order reference  |
| user_id               | bigint        | No       | -         | User FK                 |
| address_id            | bigint        | Yes      | NULL      | Delivery address FK     |
| delivery_zone_id      | bigint        | Yes      | NULL      | Delivery zone FK        |
| status                | enum          | No       | 'pending' | Order status            |
| subtotal              | decimal(10,2) | No       | -         | Subtotal before fees    |
| delivery_fee          | decimal(10,2) | No       | 0         | Delivery fee            |
| discount              | decimal(10,2) | No       | 0         | Discount amount         |
| total                 | decimal(10,2) | No       | -         | Final total             |
| currency              | varchar(3)    | No       | 'AUD'     | Currency code           |
| exchange_rate         | decimal(10,6) | No       | 1         | Exchange rate used      |
| promotion_code        | varchar(50)   | Yes      | NULL      | Applied promo code      |
| notes                 | text          | Yes      | NULL      | Customer notes          |
| delivery_instructions | text          | Yes      | NULL      | Delivery instructions   |
| scheduled_date        | date          | Yes      | NULL      | Scheduled delivery date |
| scheduled_time_slot   | varchar(50)   | Yes      | NULL      | Scheduled time slot     |
| created_at            | timestamp     | Yes      | NULL      | Creation timestamp      |
| updated_at            | timestamp     | Yes      | NULL      | Last update timestamp   |

**Order Statuses:** pending, confirmed, processing, ready, out_for_delivery, delivered, cancelled

**Indexes:** order_number (unique), user_id, status, created_at

---

### order_items

Individual items within an order.

| Column       | Type          | Nullable | Default | Description                 |
| ------------ | ------------- | -------- | ------- | --------------------------- |
| id           | bigint        | No       | auto    | Primary key                 |
| order_id     | bigint        | No       | -       | Order FK                    |
| product_id   | bigint        | No       | -       | Product FK                  |
| product_name | varchar(255)  | No       | -       | Product name snapshot       |
| quantity     | int           | No       | -       | Quantity ordered            |
| unit_price   | decimal(10,2) | No       | -       | Unit price at time of order |
| total_price  | decimal(10,2) | No       | -       | Line total                  |
| created_at   | timestamp     | Yes      | NULL    | Creation timestamp          |
| updated_at   | timestamp     | Yes      | NULL    | Last update timestamp       |

**Indexes:** order_id, product_id

---

### order_status_history

Audit trail for order status changes.

| Column     | Type        | Nullable | Default | Description           |
| ---------- | ----------- | -------- | ------- | --------------------- |
| id         | bigint      | No       | auto    | Primary key           |
| order_id   | bigint      | No       | -       | Order FK              |
| status     | varchar(50) | No       | -       | New status            |
| notes      | text        | Yes      | NULL    | Status change notes   |
| changed_by | bigint      | Yes      | NULL    | User who made change  |
| created_at | timestamp   | Yes      | NULL    | Creation timestamp    |
| updated_at | timestamp   | Yes      | NULL    | Last update timestamp |

**Indexes:** order_id, created_at

---

### payments

Payment transactions.

| Column           | Type          | Nullable | Default   | Description                     |
| ---------------- | ------------- | -------- | --------- | ------------------------------- |
| id               | bigint        | No       | auto      | Primary key                     |
| order_id         | bigint        | No       | -         | Order FK                        |
| gateway          | varchar(50)   | No       | -         | Payment gateway: stripe, paypal |
| transaction_id   | varchar(255)  | No       | -         | Gateway transaction ID          |
| status           | enum          | No       | 'pending' | Payment status                  |
| amount           | decimal(10,2) | No       | -         | Payment amount                  |
| currency         | varchar(3)    | No       | 'AUD'     | Currency code                   |
| gateway_response | json          | Yes      | NULL      | Raw gateway response            |
| created_at       | timestamp     | Yes      | NULL      | Creation timestamp              |
| updated_at       | timestamp     | Yes      | NULL      | Last update timestamp           |

**Payment Statuses:** pending, completed, failed, refunded

**Indexes:** order_id, transaction_id, status

---

### inventory_logs

Stock movement history.

| Column       | Type      | Nullable | Default | Description                           |
| ------------ | --------- | -------- | ------- | ------------------------------------- |
| id           | bigint    | No       | auto    | Primary key                           |
| product_id   | bigint    | No       | -       | Product FK                            |
| type         | enum      | No       | -       | Type: addition, reduction, adjustment |
| quantity     | int       | No       | -       | Quantity changed                      |
| stock_before | int       | No       | -       | Stock level before                    |
| stock_after  | int       | No       | -       | Stock level after                     |
| reason       | text      | Yes      | NULL    | Reason for change                     |
| user_id      | bigint    | Yes      | NULL    | User who made change                  |
| created_at   | timestamp | Yes      | NULL    | Creation timestamp                    |
| updated_at   | timestamp | Yes      | NULL    | Last update timestamp                 |

**Indexes:** product_id, type, created_at

---

### delivery_proofs

Delivery confirmation records.

| Column         | Type          | Nullable | Default | Description           |
| -------------- | ------------- | -------- | ------- | --------------------- |
| id             | bigint        | No       | auto    | Primary key           |
| order_id       | bigint        | No       | -       | Order FK              |
| photo_path     | varchar(255)  | Yes      | NULL    | Delivery photo path   |
| signature_path | varchar(255)  | Yes      | NULL    | Signature image path  |
| notes          | text          | Yes      | NULL    | Delivery notes        |
| latitude       | decimal(10,8) | Yes      | NULL    | GPS latitude          |
| longitude      | decimal(11,8) | Yes      | NULL    | GPS longitude         |
| delivered_by   | bigint        | Yes      | NULL    | Staff user FK         |
| delivered_at   | timestamp     | No       | -       | Delivery timestamp    |
| created_at     | timestamp     | Yes      | NULL    | Creation timestamp    |
| updated_at     | timestamp     | Yes      | NULL    | Last update timestamp |

**Indexes:** order_id, delivered_at

---

### wishlists

Customer wishlist items.

| Column     | Type      | Nullable | Default | Description           |
| ---------- | --------- | -------- | ------- | --------------------- |
| id         | bigint    | No       | auto    | Primary key           |
| user_id    | bigint    | No       | -       | User FK               |
| product_id | bigint    | No       | -       | Product FK            |
| created_at | timestamp | Yes      | NULL    | Creation timestamp    |
| updated_at | timestamp | Yes      | NULL    | Last update timestamp |

**Indexes:** user_id + product_id (unique)

---

### notifications

In-app user notifications.

| Column     | Type         | Nullable | Default | Description                              |
| ---------- | ------------ | -------- | ------- | ---------------------------------------- |
| id         | bigint       | No       | auto    | Primary key                              |
| user_id    | bigint       | No       | -       | User FK                                  |
| type       | varchar(50)  | No       | -       | Type: order, delivery, promotion, system |
| title      | varchar(255) | No       | -       | Notification title                       |
| message    | text         | No       | -       | Notification message                     |
| data       | json         | Yes      | NULL    | Additional data                          |
| is_read    | boolean      | No       | false   | Read status                              |
| read_at    | timestamp    | Yes      | NULL    | Read timestamp                           |
| created_at | timestamp    | Yes      | NULL    | Creation timestamp                       |
| updated_at | timestamp    | Yes      | NULL    | Last update timestamp                    |

**Indexes:** user_id, type, is_read, created_at

---

### activity_logs

System activity audit trail.

| Column     | Type         | Nullable | Default | Description               |
| ---------- | ------------ | -------- | ------- | ------------------------- |
| id         | bigint       | No       | auto    | Primary key               |
| user_id    | bigint       | Yes      | NULL    | User FK (null for system) |
| action     | varchar(255) | No       | -       | Action performed          |
| model_type | varchar(255) | Yes      | NULL    | Related model class       |
| model_id   | bigint       | Yes      | NULL    | Related model ID          |
| old_values | json         | Yes      | NULL    | Values before change      |
| new_values | json         | Yes      | NULL    | Values after change       |
| ip_address | varchar(45)  | Yes      | NULL    | Client IP address         |
| user_agent | text         | Yes      | NULL    | Client user agent         |
| created_at | timestamp    | Yes      | NULL    | Creation timestamp        |
| updated_at | timestamp    | Yes      | NULL    | Last update timestamp     |

**Indexes:** user_id, action, model_type, created_at

---

### settings

Application configuration key-value store.

| Column     | Type         | Nullable | Default   | Description                                       |
| ---------- | ------------ | -------- | --------- | ------------------------------------------------- |
| id         | bigint       | No       | auto      | Primary key                                       |
| key        | varchar(255) | No       | -         | Setting key                                       |
| value      | text         | Yes      | NULL      | Setting value                                     |
| type       | varchar(20)  | No       | 'string'  | Value type: string, integer, float, boolean, json |
| group      | varchar(50)  | No       | 'general' | Setting group                                     |
| created_at | timestamp    | Yes      | NULL      | Creation timestamp                                |
| updated_at | timestamp    | Yes      | NULL      | Last update timestamp                             |

**Groups:** general, store, delivery, payment, email

**Indexes:** key (unique), group

---

### currency_rates

Exchange rate cache.

| Column          | Type          | Nullable | Default | Description           |
| --------------- | ------------- | -------- | ------- | --------------------- |
| id              | bigint        | No       | auto    | Primary key           |
| base_currency   | varchar(3)    | No       | -       | Base currency code    |
| target_currency | varchar(3)    | No       | -       | Target currency code  |
| rate            | decimal(10,6) | No       | -       | Exchange rate         |
| fetched_at      | timestamp     | No       | -       | Rate fetch timestamp  |
| created_at      | timestamp     | Yes      | NULL    | Creation timestamp    |
| updated_at      | timestamp     | Yes      | NULL    | Last update timestamp |

**Indexes:** base_currency + target_currency (unique), fetched_at

---

### promotions

Discount codes and promotions.

| Column     | Type          | Nullable | Default | Description                     |
| ---------- | ------------- | -------- | ------- | ------------------------------- |
| id         | bigint        | No       | auto    | Primary key                     |
| name       | varchar(255)  | No       | -       | Promotion name                  |
| code       | varchar(50)   | No       | -       | Promo code                      |
| type       | enum          | No       | -       | Type: percentage, fixed         |
| value      | decimal(10,2) | No       | -       | Discount value                  |
| min_order  | decimal(10,2) | No       | 0       | Minimum order amount            |
| max_uses   | int           | Yes      | NULL    | Maximum uses (null = unlimited) |
| uses_count | int           | No       | 0       | Current usage count             |
| start_date | date          | No       | -       | Start date                      |
| end_date   | date          | No       | -       | End date                        |
| is_active  | boolean       | No       | true    | Active status                   |
| created_at | timestamp     | Yes      | NULL    | Creation timestamp              |
| updated_at | timestamp     | Yes      | NULL    | Last update timestamp           |

**Indexes:** code (unique), is_active, start_date, end_date

---

### support_tickets

Customer support tickets.

| Column     | Type         | Nullable | Default  | Description                                 |
| ---------- | ------------ | -------- | -------- | ------------------------------------------- |
| id         | bigint       | No       | auto     | Primary key                                 |
| user_id    | bigint       | No       | -        | User FK                                     |
| order_id   | bigint       | Yes      | NULL     | Related order FK                            |
| subject    | varchar(255) | No       | -        | Ticket subject                              |
| message    | text         | No       | -        | Initial message                             |
| status     | enum         | No       | 'open'   | Status: open, in_progress, resolved, closed |
| priority   | enum         | No       | 'medium' | Priority: low, medium, high, urgent         |
| created_at | timestamp    | Yes      | NULL     | Creation timestamp                          |
| updated_at | timestamp    | Yes      | NULL     | Last update timestamp                       |

**Indexes:** user_id, order_id, status, priority, created_at

---

### ticket_replies

Replies to support tickets.

| Column     | Type      | Nullable | Default | Description           |
| ---------- | --------- | -------- | ------- | --------------------- |
| id         | bigint    | No       | auto    | Primary key           |
| ticket_id  | bigint    | No       | -       | Support ticket FK     |
| user_id    | bigint    | No       | -       | User FK               |
| message    | text      | No       | -       | Reply message         |
| created_at | timestamp | Yes      | NULL    | Creation timestamp    |
| updated_at | timestamp | Yes      | NULL    | Last update timestamp |

**Indexes:** ticket_id, user_id, created_at
