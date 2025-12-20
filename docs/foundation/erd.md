# Zambezi Meats - Entity Relationship Diagram

## Visual Schema

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│     users       │         │   categories    │         │    products     │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ id              │◄──┐     │ id              │◄──┐     │ id              │
│ name            │   │     │ name            │   │     │ category_id     │──┐
│ email           │   │     │ slug            │   │     │ name            │  │
│ password        │   │     │ description     │   │     │ slug            │  │
│ role            │   │     │ image           │   │     │ description     │  │
│ phone           │   │     │ parent_id       │───┘     │ price_aud       │  │
│ avatar          │   │     │ sort_order      │         │ sale_price_aud  │  │
│ is_active       │   │     │ is_active       │         │ stock           │  │
│ currency_pref   │   │     │ timestamps      │         │ sku             │  │
│ timestamps      │   │     └─────────────────┘         │ unit            │  │
└─────────────────┘   │                                 │ weight_kg       │  │
        │             │                                 │ is_featured     │  │
        │             │                                 │ is_active       │  │
        │             │                                 │ meta            │  │
        │             │                                 │ timestamps      │  │
        │             │                                 │ deleted_at      │  │
        │             │                                 └─────────────────┘  │
        │             │                                         │            │
        │             │                                         │            │
        │             │     ┌─────────────────┐                 │            │
        │             │     │ product_images  │                 │            │
        │             │     ├─────────────────┤                 │            │
        │             │     │ id              │                 │            │
        │             │     │ product_id      │─────────────────┘            │
        │             │     │ image_path      │                              │
        │             │     │ alt_text        │                              │
        │             │     │ sort_order      │                              │
        │             │     │ is_primary      │                              │
        │             │     │ timestamps      │                              │
        │             │     └─────────────────┘                              │
        │             │                                                      │
        │             │     ┌─────────────────┐                              │
        │             │     │   wishlists     │                              │
        │             │     ├─────────────────┤                              │
        │             └─────┤ user_id         │                              │
        │                   │ product_id      │◄─────────────────────────────┘
        │                   │ timestamps      │
        │                   └─────────────────┘
        │
        │             ┌─────────────────┐
        │             │    addresses    │
        │             ├─────────────────┤
        └─────────────┤ user_id         │
        │             │ label           │
        │             │ street_address  │
        │             │ apartment       │
        │             │ suburb          │
        │             │ state           │
        │             │ postcode        │
        │             │ country         │
        │             │ is_default      │
        │             │ timestamps      │
        │             └─────────────────┘
        │                     │
        │                     │
        │             ┌───────▼─────────┐         ┌─────────────────┐
        │             │     orders      │         │ delivery_zones  │
        │             ├─────────────────┤         ├─────────────────┤
        └─────────────┤ user_id         │         │ id              │
                      │ order_number    │         │ name            │
                      │ address_id      │◄────────│ suburbs         │
                      │ delivery_zone_id│─────────┤ delivery_fee    │
                      │ status          │         │ free_delivery_  │
                      │ subtotal        │         │   threshold     │
                      │ delivery_fee    │         │ estimated_days  │
                      │ discount        │         │ is_active       │
                      │ total           │         │ timestamps      │
                      │ currency        │         └─────────────────┘
                      │ exchange_rate   │
                      │ promotion_code  │
                      │ notes           │
                      │ delivery_instrs │
                      │ scheduled_date  │
                      │ scheduled_time  │
                      │ timestamps      │
                      └─────────────────┘
                              │
          ┌───────────────────┼───────────────────┬───────────────────┐
          │                   │                   │                   │
          ▼                   ▼                   ▼                   ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│   order_items   │ │ order_status_   │ │    payments     │ │ delivery_proofs │
├─────────────────┤ │    history      │ ├─────────────────┤ ├─────────────────┤
│ id              │ ├─────────────────┤ │ id              │ │ id              │
│ order_id        │ │ id              │ │ order_id        │ │ order_id        │
│ product_id      │ │ order_id        │ │ gateway         │ │ photo_path      │
│ product_name    │ │ status          │ │ transaction_id  │ │ signature_path  │
│ quantity        │ │ notes           │ │ status          │ │ notes           │
│ unit_price      │ │ changed_by      │ │ amount          │ │ latitude        │
│ total_price     │ │ timestamps      │ │ currency        │ │ longitude       │
│ timestamps      │ └─────────────────┘ │ gateway_response│ │ delivered_by    │
└─────────────────┘                     │ timestamps      │ │ delivered_at    │
                                        └─────────────────┘ │ timestamps      │
                                                            └─────────────────┘

┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│ inventory_logs  │         │  notifications  │         │  activity_logs  │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ id              │         │ id              │         │ id              │
│ product_id      │         │ user_id         │         │ user_id         │
│ type            │         │ type            │         │ action          │
│ quantity        │         │ title           │         │ model_type      │
│ stock_before    │         │ message         │         │ model_id        │
│ stock_after     │         │ data            │         │ old_values      │
│ reason          │         │ is_read         │         │ new_values      │
│ user_id         │         │ read_at         │         │ ip_address      │
│ timestamps      │         │ timestamps      │         │ user_agent      │
└─────────────────┘         └─────────────────┘         │ timestamps      │
                                                        └─────────────────┘

┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│    settings     │         │ currency_rates  │         │   promotions    │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ id              │         │ id              │         │ id              │
│ key             │         │ base_currency   │         │ name            │
│ value           │         │ target_currency │         │ code            │
│ type            │         │ rate            │         │ type            │
│ group           │         │ fetched_at      │         │ value           │
│ timestamps      │         │ timestamps      │         │ min_order       │
└─────────────────┘         └─────────────────┘         │ max_uses        │
                                                        │ uses_count      │
                                                        │ start_date      │
                                                        │ end_date        │
                                                        │ is_active       │
                                                        │ timestamps      │
                                                        └─────────────────┘

┌─────────────────┐         ┌─────────────────┐
│ support_tickets │         │ ticket_replies  │
├─────────────────┤         ├─────────────────┤
│ id              │◄────────│ ticket_id       │
│ user_id         │         │ id              │
│ order_id        │         │ user_id         │
│ subject         │         │ message         │
│ message         │         │ timestamps      │
│ status          │         └─────────────────┘
│ priority        │
│ timestamps      │
└─────────────────┘
```

## Relationships Summary

### One-to-Many

| Parent          | Child                | Relationship                       |
| --------------- | -------------------- | ---------------------------------- |
| users           | addresses            | User has many addresses            |
| users           | orders               | User has many orders               |
| users           | wishlists            | User has many wishlist items       |
| users           | notifications        | User has many notifications        |
| users           | activity_logs        | User has many activity logs        |
| users           | support_tickets      | User has many support tickets      |
| users           | ticket_replies       | User has many ticket replies       |
| categories      | categories           | Category has many child categories |
| categories      | products             | Category has many products         |
| products        | product_images       | Product has many images            |
| products        | order_items          | Product has many order items       |
| products        | inventory_logs       | Product has many inventory logs    |
| products        | wishlists            | Product has many wishlist entries  |
| delivery_zones  | orders               | Delivery zone has many orders      |
| orders          | order_items          | Order has many items               |
| orders          | order_status_history | Order has many status entries      |
| support_tickets | ticket_replies       | Ticket has many replies            |

### One-to-One

| Parent | Child           | Relationship                 |
| ------ | --------------- | ---------------------------- |
| orders | payments        | Order has one payment        |
| orders | delivery_proofs | Order has one delivery proof |

### Self-Referential

| Table      | Relationship                        |
| ---------- | ----------------------------------- |
| categories | Category belongs to parent category |

## Foreign Key Constraints

All foreign keys use cascade delete or set null:

| Child Table          | Foreign Key      | References         | On Delete |
| -------------------- | ---------------- | ------------------ | --------- |
| categories           | parent_id        | categories.id      | SET NULL  |
| products             | category_id      | categories.id      | CASCADE   |
| product_images       | product_id       | products.id        | CASCADE   |
| addresses            | user_id          | users.id           | CASCADE   |
| orders               | user_id          | users.id           | CASCADE   |
| orders               | address_id       | addresses.id       | SET NULL  |
| orders               | delivery_zone_id | delivery_zones.id  | SET NULL  |
| order_items          | order_id         | orders.id          | CASCADE   |
| order_items          | product_id       | products.id        | CASCADE   |
| order_status_history | order_id         | orders.id          | CASCADE   |
| order_status_history | changed_by       | users.id           | SET NULL  |
| payments             | order_id         | orders.id          | CASCADE   |
| inventory_logs       | product_id       | products.id        | CASCADE   |
| inventory_logs       | user_id          | users.id           | SET NULL  |
| delivery_proofs      | order_id         | orders.id          | CASCADE   |
| delivery_proofs      | delivered_by     | users.id           | SET NULL  |
| wishlists            | user_id          | users.id           | CASCADE   |
| wishlists            | product_id       | products.id        | CASCADE   |
| notifications        | user_id          | users.id           | CASCADE   |
| activity_logs        | user_id          | users.id           | SET NULL  |
| support_tickets      | user_id          | users.id           | CASCADE   |
| support_tickets      | order_id         | orders.id          | SET NULL  |
| ticket_replies       | ticket_id        | support_tickets.id | CASCADE   |
| ticket_replies       | user_id          | users.id           | CASCADE   |
