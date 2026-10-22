# Cart → Buy API — Endpoints (complete)

Base: `{APP_URL}/api/v1`

Field details: [FIELDS.md](./FIELDS.md)

Har request: `Accept: application/json` + `Authorization: Bearer {token}`

---

## 1. Get cart

**`GET /cart`** — Auth

Agar koi `awaiting_payment` order Cashfree par **PAID** ho chuka ho, `pending_paid_order` milega — app success screen pe le jaaye.

### Response `200`

```json
{
  "success": true,
  "data": {
    "is_empty": false,
    "items": [],
    "items_count": 2,
    "subtotal": 540.00,
    "tax": 0,
    "shipping": 0,
    "shipping_label": "Free",
    "delivery_schedule_type": "scheduled",
    "delivery_fee": 0,
    "instant_delivery_fee": 100,
    "discount": 0,
    "coupon": null,
    "wallet_balance": 250.00,
    "use_wallet": false,
    "wallet_used": 0,
    "wallet_max_usable": 249.00,
    "payable": 540.00,
    "total": 540.00,
    "minimum_online_pay": 1,
    "time_slots": [
      { "value": "10:00-12:00", "label": "10:00 AM – 12:00 PM" }
    ],
    "payment_method": "cashfree",
    "payment_configured": true,
    "cart_count": 2,
    "wishlist_count": 1,
    "pending_paid_order": null
  }
}
```

### cURL

```bash
curl -s -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN" \
  "http://127.0.0.1:8000/api/v1/cart"
```

---

## 2. Add to cart

**`POST /cart/products/{product}`** — Auth

`{product}` = catalog product id. Weight **catalog** `GET /products/{id}` ke `weights[].id` se.

### Request

```json
{
  "product_weight_id": 14,
  "quantity": 1,
  "preference_note": "Skinless pieces"
}
```

### Response `201`

```json
{
  "success": true,
  "message": "Product cart mein add ho gaya.",
  "data": {
    "item": { },
    "is_empty": false,
    "items": [],
    "cart_count": 2,
    "wishlist_count": 1
  }
}
```

Same `product_id` + `weight_label` + `preference_note` dubara add → **qty increase** (max 99).

Inactive product → `422` `Product available nahi hai.`

---

## 3. Update quantity

**`PATCH /cart/{cartItem}`** — Auth

`cartItem` = cart line `id` (not product id).

### Request

```json
{ "quantity": 3 }
```

### Response `200`

```json
{
  "success": true,
  "message": "Cart updated.",
  "data": { }
}
```

Dusre user ki line → `403`.

---

## 4. Remove cart line

**`DELETE /cart/{cartItem}`** — Auth

```json
{
  "success": true,
  "message": "Item cart se hata diya gaya.",
  "data": { }
}
```

---

## 5. Clear cart

**`POST /cart/clear`** — Auth

```json
{
  "success": true,
  "message": "Cart clear ho gaya.",
  "data": { }
}
```

---

## 6. Wishlist list

**`GET /wishlist`** — Auth

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 4,
        "product_id": 5,
        "product_name": "Chicken Curry Cut",
        "image": "http://127.0.0.1:8000/storage/products/chicken.jpg",
        "product": { },
        "created_at": "2026-10-21T10:00:00+00:00"
      }
    ],
    "cart_count": 2,
    "wishlist_count": 1
  }
}
```

`product` = catalog summary (null if product inactive/missing).

---

## 7. Add wishlist

**`POST /wishlist/products/{product}`** — Auth

Empty JSON body OK.

```json
{
  "success": true,
  "message": "Wishlist mein add ho gaya.",
  "data": {
    "item": { },
    "cart_count": 2,
    "wishlist_count": 1
  }
}
```

Duplicate → same row (firstOrCreate).

---

## 8. Remove wishlist

**`DELETE /wishlist/{wishlistItem}`** — by wishlist row id

**`DELETE /wishlist/products/{product}`** — by product id

```json
{
  "success": true,
  "message": "Wishlist se hata diya gaya.",
  "data": {
    "cart_count": 2,
    "wishlist_count": 0
  }
}
```

---

## 9. Checkout screen

**`GET /checkout`** — Auth

Query (optional): `delivery_schedule_type=instant|scheduled`, `coupon_code`, `use_wallet=1`

Empty cart → `422` `Pehle cart mein items add karein.`

Agar pending Cashfree order already paid:

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "already_paid": true,
  "data": {
    "order": { },
    "invoice": { }
  }
}
```

### Normal response `200`

```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "Rahul Sharma",
      "email": "rahul@example.com",
      "phone": "9876543210",
      "address": "Sector 62, Noida",
      "wallet_balance": 250.00
    },
    "is_empty": false,
    "items": [],
    "time_slots": [],
    "instant_delivery_fee": 100,
    "wallet_balance": 250.00,
    "payment_configured": true
  }
}
```

---

## 10. Live quote

**`POST /checkout/quote`** — Auth

Totals tabhi update jab user Instant tick kare, coupon type kare, wallet checkbox.

### Request

```json
{
  "delivery_schedule_type": "instant",
  "coupon_code": "FRESH50",
  "use_wallet": true
}
```

### Response `200`

Quote object (FIELDS). Invalid coupon yahan **silent ignore** (discount 0). Apply ke liye next endpoint.

---

## 11. Apply coupon

**`POST /checkout/coupon`** — Auth · throttle `checkout`

### Request

```json
{
  "code": "FRESH50",
  "delivery_schedule_type": "scheduled",
  "use_wallet": false
}
```

### Response `200`

```json
{
  "success": true,
  "message": "FRESH50 lag gaya. ₹50 off",
  "data": {
    "coupon": {
      "id": 2,
      "code": "FRESH50",
      "title": "Fresh 50",
      "discount_type": "fixed",
      "discount_value": 50,
      "min_order_amount": 299,
      "max_discount": null,
      "value_label": "₹50 off",
      "discount": 50
    },
    "subtotal": 540.00,
    "discount": 50.00,
    "total": 490.00
  }
}
```

Empty cart → `422` `Cart khali hai.`

Galat / expire / min-order fail → `422` + `errors.coupon_code`.

---

## 12. Pincode + delivery zone

**`GET /checkout/pincode/{pincode}`** — Auth · throttle `search`

`{pincode}` = 6 digits.

### Response `200` — in zone (e.g. Noida `201301`)

```json
{
  "success": true,
  "pincode": "201301",
  "state": "Uttar Pradesh",
  "city": "Gautam Buddha Nagar",
  "area": "Noida",
  "delivery": {
    "deliverable": true,
    "zone": "Noida & Greater Noida — Pincodes",
    "message": "Delivery available — Noida & Greater Noida — Pincodes",
    "message_hi": null,
    "message_en": null
  }
}
```

### Out of zone

`deliverable: false`. Order **place nahi** hoga.

```json
{
  "delivery": {
    "deliverable": false,
    "zone": null,
    "message": "Abhi is location par delivery nahi ho rahi. Jaldi hi wahan bhi delivery hogi.\nDelivery is not available at this location yet. We will start delivering here soon too.",
    "message_hi": "Abhi is location par delivery nahi ho rahi. Jaldi hi wahan bhi delivery hogi.",
    "message_en": "Delivery is not available at this location yet. We will start delivering here soon too."
  }
}
```

Invalid pincode → `422` `Valid 6-digit pincode enter karein.`

---

## 13. Address suggest

**`GET /checkout/address-suggest?q=sector%2062%20noida`** — Auth · throttle `search`

`q` kam se kam 3 characters.

```json
{
  "success": true,
  "suggestions": [
    {
      "label": "Sector 62, Noida, Uttar Pradesh, 201301",
      "address": "Sector 62",
      "pincode": "201301",
      "city": "Noida",
      "state": "Uttar Pradesh"
    }
  ]
}
```

---

## 14. Place order (buy)

**`POST /checkout`** — Auth · throttle `checkout`

Web checkout form ke **saare fields**.

### Request

```json
{
  "phone": "9876543210",
  "pincode": "201301",
  "state": "Uttar Pradesh",
  "city": "Gautam Buddha Nagar",
  "post_office": "Noida",
  "delivery_address": "Flat 12, Sector 62",
  "delivery_schedule_type": "scheduled",
  "delivery_date": "2026-10-22",
  "delivery_time": "10:00-12:00",
  "payment_method": "cashfree",
  "coupon_code": "FRESH50",
  "use_wallet": true,
  "order_notes": "Call on arrival"
}
```

`delivery_schedule_type = instant` → `delivery_date` / `delivery_time` skip. Instant fee add.

`delivery_time` = `GET /checkout` ke `time_slots[].value`.

### Response `201`

```json
{
  "success": true,
  "message": "Payment start ho gaya. Cashfree se pay karein.",
  "data": {
    "order": { },
    "payment": {
      "gateway": "cashfree",
      "environment": "SANDBOX",
      "order_id": "FNV-20261021-AB12CD",
      "order_amount": 440.00,
      "order_currency": "INR",
      "payment_session_id": "session_xxxxx",
      "cf_order_id": "123456",
      "payment_link": null,
      "checkout_url": "https://sandbox.cashfree.com/pg/view/sessions/checkout/web/session_xxxxx?order_id=FNV-20261021-AB12CD"
    }
  }
}
```

App: Cashfree drop-in / SDK with `payment_session_id`. WebView fallback: `checkout_url` / `payment_link`.

Iske baad **cart clear** ho jata hai (web jaisa). Payment fail → wallet/coupon rollback.

Out of zone → `422` `errors.pincode` bilingual message.

Empty cart → `422` `Pehle cart mein items add karein.`

Cashfree keys missing → `422` payment_method message.

---

## 15. Verify payment (after SDK)

**`POST /checkout/verify`** — Auth · throttle `checkout`

`order_id` (numeric DB id) **ya** `order_number` (`FNV-...`).

### Request

```json
{
  "order_number": "FNV-20261021-AB12CD"
}
```

### Response `200` — paid

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "data": {
    "order": { },
    "invoice": { }
  }
}
```

### Response `422` — not paid

```json
{
  "success": false,
  "message": "Payment complete nahi hui. Dubara checkout try karein.",
  "data": {
    "order": { }
  }
}
```

Unpaid `awaiting_payment` verify fail par **payment_failed** mark + wallet refund (web return jaisa).

---

## 16. Payment status (poll)

**`GET /checkout/orders/{order}/status`** — Auth

Cashfree se sync try karta hai.

```json
{
  "success": true,
  "data": {
    "order": { },
    "is_paid": true,
    "invoice": { }
  }
}
```

Agar abhi paid nahi: `invoice` = `null`.

---

## 17. Order success

**`GET /checkout/orders/{order}/success`** — Auth

Website success page ka JSON. Pending payment → `422` `Payment abhi pending hai.`

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "data": {
    "order": { },
    "invoice": { }
  }
}
```

---

## 18. Invoice / tax bill

**`GET /checkout/orders/{order}/invoice`** — Auth  
Alias: **`GET /orders/{order}/invoice`**  
Dashboard: **`GET /dashboard/orders/{order}/bill`**

Status must be `paid` | `confirmed` | `cancelled`.

### Response `200`

Poora object [FIELDS.md — Invoice](./FIELDS.md#invoice-object).

```json
{
  "success": true,
  "data": {
    "invoice_title": "TAX INVOICE / FOOD BILL",
    "invoice_type": "tax_invoice",
    "stamp": "PAID",
    "company": {
      "brand": "Fresh Non Veg",
      "legal_name": "SET INDIA BUSINESS PRIVATE LIMITED",
      "gstin": "07AAGCN2084A1ZT",
      "pan": "AAGCN2084A"
    },
    "order": { "order_number": "FNV-20261021-AB12CD" },
    "customer": { },
    "delivery": { },
    "items": [],
    "totals": {
      "subtotal": 540.00,
      "tax": 0,
      "discount": 50.00,
      "coupon_code": "FRESH50",
      "delivery_fee": 0,
      "wallet_amount": 50.00,
      "grand_total": 440.00
    },
    "payment": {
      "method": "cashfree",
      "stamp": "PAID",
      "is_paid": true
    },
    "footer": [
      "This is a computer generated invoice of SET INDIA BUSINESS PRIVATE LIMITED.",
      "GSTIN 07AAGCN2084A1ZT · Prices inclusive of taxes as applicable.",
      "Dhanyavaad! FreshNonVeg — ghar tak fresh."
    ]
  }
}
```

### Response `404`

```json
{
  "success": false,
  "message": "Invoice abhi available nahi hai. Pehle payment complete karein."
}
```

---

## Mobile screens (suggested)

| Screen | API |
|--------|-----|
| Product → Add | `POST /cart/products/{id}` |
| Heart | `POST /wishlist/products/{id}` |
| Cart | `GET /cart` |
| Checkout | `GET /checkout` |
| Pincode | `GET /checkout/pincode/{pincode}` |
| Pay | `POST /checkout` → Cashfree SDK |
| Success | `GET /checkout/orders/{id}/success` |
| Invoice | `GET /checkout/orders/{id}/invoice` |
