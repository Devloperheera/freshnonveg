# Cart → Buy API — Fields reference

Catalog product summary nested objects: [Catalog FIELDS](../catalog/FIELDS.md).

---

## Cart item (`data.items[]` / `data.item`)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Cart line id (`cartItem` in URLs) |
| `product_id` | integer \| null | |
| `product_name` | string | Snapshot |
| `weight_label` | string \| null | e.g. `500g` |
| `preference_note` | string \| null | Cutting / packing note |
| `quantity` | integer | 1–99 |
| `unit_price` | number | Weight price at add time |
| `line_total` | number | `unit_price * quantity` |
| `image` | string \| null | First product image URL |
| `product` | object \| null | Catalog product summary |
| `created_at` | ISO 8601 \| null | |
| `updated_at` | ISO 8601 \| null | |

---

## Add to cart — `POST /cart/products/{product}`

| Field | Required | Rules |
|-------|----------|-------|
| `product_weight_id` | Yes | integer — `weights[].id` from product detail |
| `quantity` | Yes | integer, min 1, max 99 |
| `preference_note` | No | string, max 1000 |

---

## Update qty — `PATCH /cart/{cartItem}`

| Field | Required | Rules |
|-------|----------|-------|
| `quantity` | Yes | integer, 1–99 |

---

## Wishlist item

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Wishlist row id |
| `product_id` | integer | |
| `product_name` | string | |
| `image` | string \| null | |
| `product` | object \| null | Active catalog summary |
| `created_at` | ISO 8601 \| null | |

Cart/wishlist responses often include:

| Field | Type | Description |
|-------|------|-------------|
| `cart_count` | integer | Sum of cart quantities |
| `wishlist_count` | integer | Wishlist rows |

---

## Quote / checkout totals (`GET /cart`, `GET /checkout`, `POST /checkout/quote`)

| Field | Type | Description |
|-------|------|-------------|
| `is_empty` | boolean | |
| `items` | array | Cart items |
| `items_count` | integer | Sum of qty |
| `subtotal` | number | |
| `tax` | number | Always `0` (prices inclusive) |
| `shipping` | number | Always `0` |
| `shipping_label` | string | `Free` |
| `delivery_schedule_type` | string | `instant` \| `scheduled` |
| `delivery_fee` | number | Instant fee or `0` |
| `instant_delivery_fee` | number | Config (default 100) |
| `discount` | number | Coupon amount |
| `coupon` | object \| null | See coupon object |
| `wallet_balance` | number | |
| `use_wallet` | boolean | Quote input echo |
| `wallet_used` | number | Applied on this quote |
| `wallet_max_usable` | number | `min(balance, payable - 1)` |
| `payable` | number | After discount + delivery, before wallet |
| `total` | number | Online Cashfree amount (`>= 1`) |
| `minimum_online_pay` | number | `1` |
| `time_slots` | array | `{ value, label }` admin slots |
| `payment_method` | string | `cashfree` |
| `payment_configured` | boolean | Cashfree keys present |
| `customer` | object | Only on `GET /checkout` |
| `pending_paid_order` | object \| null | `{ id, order_number }` on `GET /cart` |
| `already_paid` | boolean | Top-level on `GET /checkout` if pending order paid |

### Customer (`GET /checkout` — `data.customer`)

| Field | Type |
|-------|------|
| `id` | integer |
| `name` | string |
| `email` | string \| null |
| `phone` | string \| null |
| `address` | string \| null |
| `wallet_balance` | number |

### Time slot

| Field | Type | Description |
|-------|------|-------------|
| `value` | string | Send as `delivery_time` |
| `label` | string | UI text |

---

## Coupon object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | |
| `code` | string | Uppercase |
| `title` | string \| null | |
| `discount_type` | string | `percent` \| `fixed` |
| `discount_value` | number | |
| `min_order_amount` | number | |
| `max_discount` | number \| null | Percent cap |
| `value_label` | string | e.g. `10% off (max ₹100)` |
| `discount` | number | Amount applied on current subtotal |

### Apply coupon — `POST /checkout/coupon`

| Field | Required | Rules |
|-------|----------|-------|
| `code` | Yes | string, max 40 |
| `delivery_schedule_type` | No | `instant` \| `scheduled` |
| `use_wallet` | No | boolean |

### Quote — `POST /checkout/quote`

| Field | Required | Rules |
|-------|----------|-------|
| `delivery_schedule_type` | No | `instant` \| `scheduled` |
| `coupon_code` | No | max 40 (invalid = ignored) |
| `use_wallet` | No | boolean |

---

## Pincode lookup

Success extras (plus `delivery`):

| Field | Type |
|-------|------|
| `pincode` | string |
| `state` | string |
| `city` | string |
| `area` | string \| omitted | Post office name |

### `delivery` object

| Field | Type | Description |
|-------|------|-------------|
| `deliverable` | boolean \| null | `false` = block order |
| `zone` | string \| null | Zone name |
| `message` | string | Hindi + English if blocked |
| `message_hi` | string \| null | |
| `message_en` | string \| null | |

### Address suggestion

| Field | Type |
|-------|------|
| `label` | string | Full display |
| `address` | string | Street / house line |
| `pincode` | string | May be empty |
| `city` | string | |
| `state` | string | |

---

## Place order — `POST /checkout`

| Field | Required | Rules |
|-------|----------|-------|
| `phone` | Yes | string, max 20 |
| `pincode` | Yes | regex `^[0-9]{6}$` |
| `state` | Yes | string, max 100 |
| `city` | Yes | string, max 100 |
| `post_office` | No | string, max 150 |
| `delivery_address` | Yes | string, max 2000 (house/street) |
| `delivery_schedule_type` | Yes | `instant` \| `scheduled` |
| `delivery_date` | If scheduled | date, `after_or_equal:today` |
| `delivery_time` | If scheduled | must be an active slot `value` |
| `payment_method` | Yes | `cashfree` only |
| `coupon_code` | No | max 40 |
| `use_wallet` | No | boolean |
| `order_notes` | No | string, max 1000 |

Server `delivery_address` ko city/state/pincode se compose karta hai.

Wallet: `min(balance, payable - 1)` — Cashfree **₹1** minimum.

---

## Payment object (`data.payment` after place)

| Field | Type | Description |
|-------|------|-------------|
| `gateway` | string | `cashfree` |
| `environment` | string | `SANDBOX` \| `PRODUCTION` |
| `order_id` | string | Merchant order number (`FNV-...`) |
| `order_amount` | number | Cashfree amount |
| `order_currency` | string | `INR` |
| `payment_session_id` | string \| null | **SDK ke liye** |
| `cf_order_id` | mixed \| null | Cashfree order id |
| `payment_link` | string \| null | If gateway returns link |
| `checkout_url` | string | Hosted checkout WebView fallback |

### Verify — `POST /checkout/verify`

| Field | Required | Rules |
|-------|----------|-------|
| `order_id` | One of two | integer DB id |
| `order_number` | One of two | string max 40 |

---

## Invoice object

`GET /checkout/orders/{id}/invoice`, `/orders/{id}/invoice`, `/dashboard/orders/{id}/bill`

| Field | Type | Description |
|-------|------|-------------|
| `invoice_title` | string | `TAX INVOICE / FOOD BILL` |
| `invoice_type` | string | `tax_invoice` |
| `stamp` | string | `PAID` / `CANCELLED` / status |
| `company` | object | GST invoice header |
| `order` | object | Number, status, timestamps |
| `customer` | object | Name, email, phone |
| `delivery` | object | Slot + full address parts |
| `items` | array | Bill lines |
| `totals` | object | Subtotal → grand total |
| `payment` | object | Method, paid flags, Cashfree id |
| `notes` | string \| null | Customer notes |
| `bill_emailed` | boolean | |
| `bill_emailed_at` | ISO 8601 \| null | |
| `footer` | string[] | Legal footer lines |

### `company`

| Field | Type |
|-------|------|
| `brand` | string | Fresh Non Veg |
| `legal_name` | string | SET INDIA BUSINESS PRIVATE LIMITED |
| `trade_name` | string | |
| `gstin` | string | `07AAGCN2084A1ZT` |
| `pan` | string | `AAGCN2084A` |
| `constitution` | string | |
| `registration_type` | string | Regular |
| `state` | string | Delhi |
| `state_code` | string | `07` |
| `pincode` | string | |
| `address` | string | One-line |
| `address_parts` | object | floor, building, street, locality, city, district, state, pincode |
| `tagline` | string | `FRESH MEAT · NEVER FROZEN` |

### `order` (invoice)

| Field | Type |
|-------|------|
| `id` | integer |
| `order_number` | string | `FNV-...` |
| `status` | string | `paid` / `confirmed` / `cancelled` / … |
| `status_label` | string | Paid / Cancelled / … |
| `created_at` | ISO 8601 \| null |
| `created_at_label` | string \| null | `d M Y, h:i A` |
| `cancelled_at` | ISO 8601 \| null |
| `cancel_reason` | string \| null |

### `customer` (invoice)

| Field | Type |
|-------|------|
| `id` | integer |
| `name` | string |
| `email` | string \| null |
| `phone` | string \| null | Order phone |

### `delivery` (invoice)

| Field | Type |
|-------|------|
| `schedule_type` | string | `instant` \| `scheduled` |
| `date` | string \| null | `Y-m-d` |
| `time` | string \| null | Slot value |
| `label` | string \| null | Instant delivery / `22 Oct 2026 10:00 AM – 12:00 PM` |
| `fee` | number |
| `address` | string \| null | Full composed address |
| `pincode` | string \| null |
| `city` | string \| null |
| `state` | string \| null |
| `post_office` | string \| null |

### Invoice `items[]`

| Field | Type |
|-------|------|
| `id` | integer | Order line id |
| `product_id` | integer \| null |
| `product_name` | string | Snapshot |
| `weight_label` | string \| null |
| `preference_note` | string \| null |
| `quantity` | integer |
| `unit_price` | number |
| `line_total` | number |
| `image` | string \| null |

### `totals`

| Field | Type | Description |
|-------|------|-------------|
| `subtotal` | number | |
| `tax` | number | `0` |
| `tax_label` | string | Inclusive note |
| `shipping` | number | `0` |
| `shipping_label` | string | `Free` |
| `discount` | number | |
| `coupon_code` | string \| null | |
| `delivery_fee` | number | |
| `delivery_label` | string | `Free` or `₹100.00` |
| `wallet_amount` | number | |
| `grand_total` | number | Online paid |
| `refundable_amount` | number | Cancel refund (`total + wallet`) |

### `payment` (invoice)

| Field | Type |
|-------|------|
| `method` | string | `cashfree` |
| `method_label` | string | Online |
| `gateway` | string | cashfree |
| `status` | string | Order status |
| `stamp` | string | PAID / CANCELLED |
| `is_paid` | boolean |
| `is_cancelled` | boolean |
| `cashfree_payment_id` | string \| null |
| `paid_online` | number |
| `wallet_used` | number |

Order detail fields after buy: [User FIELDS — Order](../user/FIELDS.md#order-summary-list--recent).
