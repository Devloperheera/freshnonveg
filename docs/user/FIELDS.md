# User Dashboard API — Fields reference

---

## User object (`data.user` / `data` on profile)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | User id |
| `name` | string | Full name |
| `email` | string \| null | Optional |
| `phone` | string | Normalized phone |
| `profile_image` | string \| null | URL if set |
| `address` | string \| null | Saved delivery address |
| `latitude` | number \| null | |
| `longitude` | number \| null | |
| `wallet_balance` | number | Current wallet INR |
| `status` | integer | `1` = active |
| `has_password` | boolean | `false` = Google-only, set-password flow |
| `created_at` | ISO 8601 \| null | |

---

## Stats (`GET /dashboard` — `data.stats`)

| Field | Type | Description |
|-------|------|-------------|
| `orders` | integer | Count of `paid` + `confirmed` |
| `spent` | number | Sum of those order `total` |
| `wallet` | number | Same as `wallet_balance` |
| `open_complaints` | integer | `open` + `in_progress` |

---

## Update profile — `PUT /dashboard/profile`

| Field | Required | Rules |
|-------|----------|-------|
| `name` | Yes | string, max 255 |
| `phone` | Yes | string, max 20, unique (ignore self) |
| `email` | No | email, max 255, unique (ignore self) |
| `address` | No | string, max 500 |
| `latitude` | No | numeric |
| `longitude` | No | numeric |

---

## Update password — `PUT /dashboard/profile/password`

| Field | Required | Rules |
|-------|----------|-------|
| `current_password` | If user already has password | string |
| `password` | Yes | string, min **8**, confirmed |
| `password_confirmation` | Yes | must match `password` |

---

## Order status

| `status` | `status_label` | `status_tone` |
|----------|----------------|---------------|
| `paid` | Paid | `ok` |
| `confirmed` | Confirmed | `ok` |
| `awaiting_payment` | Payment pending | `warn` |
| `payment_failed` | Payment failed | `bad` |
| `cancelled` | Cancelled | `muted` |

---

## Order summary (list / recent)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | |
| `order_number` | string | e.g. `FNV-20261021-AB12CD` |
| `status` | string | see table |
| `status_label` | string | UI badge text |
| `status_tone` | string | `ok` / `warn` / `bad` / `muted` |
| `total` | number | Online payable (after wallet) |
| `wallet_amount` | number | Wallet used on this order |
| `discount_amount` | number | Coupon discount |
| `delivery_fee` | number | Instant fee or `0` |
| `items_count` | integer | |
| `created_at` | ISO 8601 | |
| `created_at_label` | string | `d M Y, h:i A` |
| `can_cancel` | boolean | Cancel window still open |
| `items` | array | Max 3 lines on list; full list on detail |

---

## Order item

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | `order_items.id` |
| `product_id` | integer \| null | |
| `product_name` | string | Snapshot name |
| `weight_label` | string \| null | e.g. `500g` |
| `quantity` | integer | |
| `unit_price` | number | |
| `line_total` | number | `unit_price * quantity` |
| `image` | string \| null | Product first image URL |
| `preference_note` | string \| null | Detail / invoice only |
| `can_rate` | boolean | Paid + not yet rated (detail/list) |
| `my_rating` | integer \| null | 1–5 if already rated |

---

## Order detail (summary **plus**)

| Field | Type | Description |
|-------|------|-------------|
| `phone` | string | Delivery phone |
| `delivery_address` | string | Full composed address |
| `pincode` | string \| null | 6 digit |
| `city` | string \| null | |
| `state` | string \| null | |
| `post_office` | string \| null | |
| `delivery_schedule_type` | string | `instant` \| `scheduled` |
| `delivery_date` | date \| null | `Y-m-d` |
| `delivery_time` | string \| null | Slot key or `instant` |
| `delivery_label` | string \| null | Human slot text |
| `coupon_code` | string \| null | |
| `payment_method` | string | `cashfree` |
| `customer_notes` | string \| null | |
| `cancelled_at` | ISO 8601 \| null | |
| `cancel_reason` | string \| null | |
| `cancel_deadline` | ISO 8601 \| null | |
| `cancel_policy` | string | Hindi policy line |
| `refundable_amount` | number | `total + wallet_amount` |
| `is_paid` | boolean | |
| `is_cancelled` | boolean | |
| `bill_available` | boolean | Paid or cancelled |

Cancel window (web jaisa):

- Instant: place ke **10 minutes** andar
- Scheduled: slot start se **2 hours** pehle tak

---

## Cancel — `POST /dashboard/orders/{id}/cancel`

| Field | Required | Rules |
|-------|----------|-------|
| `cancel_reason` | Yes | string, max 500 |

---

## Wallet transaction

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | |
| `type` | string | `credit`, `refund`, `debit` |
| `is_credit` | boolean | `credit` + `refund` |
| `source` | string | `direct` \| `complaint` \| `refund` \| `used` |
| `source_label` | string | Admin/UI label |
| `amount` | number | Always positive |
| `balance_after` | number | Wallet after this row |
| `description` | string | |
| `complaint_id` | integer \| null | |
| `order_id` | integer \| null | |
| `created_at` | ISO 8601 | |
| `created_at_label` | string | |

Wallet list extras: `balance`, `total_credited`, `total_used`.

---

## Complaint

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | |
| `subject` | string | max 150 |
| `message` | string | max 3000 |
| `status` | string | `open` \| `in_progress` \| `resolved` \| `rejected` |
| `status_label` | string | Open / In progress / … |
| `admin_reply` | string \| null | |
| `credited_amount` | number | Wallet credit if approved |
| `order_id` | integer \| null | |
| `order_number` | string \| null | |
| `resolved_at` | ISO 8601 \| null | |
| `created_at` | ISO 8601 | |
| `created_at_label` | string | |

### Create complaint

| Field | Required | Rules |
|-------|----------|-------|
| `order_id` | No | Must belong to this user |
| `subject` | Yes | string, max 150 |
| `message` | Yes | string, max 3000 |

---

## Rating item (`pending[]` / `rated[]`)

| Field | Type | Description |
|-------|------|-------------|
| `product_id` | integer | |
| `product_name` | string | |
| `image` | string \| null | |
| `order_id` | integer | Latest paid line |
| `order_number` | string | |
| `purchased_at` | ISO 8601 \| null | |
| `has_rating` | boolean | |
| `rating` | integer \| null | |
| `review` | string \| null | |

### Submit rating — `POST /dashboard/ratings/{product}`

| Field | Required | Rules |
|-------|----------|-------|
| `rating` | Yes | integer, 1–5 |
| `review` | No | string, max 1000 |

---

## Pagination `meta`

| Field | Type |
|-------|------|
| `current_page` | integer |
| `last_page` | integer |
| `per_page` | integer |
| `total` | integer |

Invoice object: [Cart FIELDS — Invoice](../cart/FIELDS.md#invoice-object).
