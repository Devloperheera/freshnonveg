# User Dashboard API — Endpoints (complete)

Base: `{APP_URL}/api/v1/dashboard`

Field details: [FIELDS.md](./FIELDS.md)

Har request: `Accept: application/json` + `Authorization: Bearer {token}`

---

## 1. Overview

**`GET /dashboard`** — Auth

Website dashboard cards + recent orders + wallet activity.

### Response `200`

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Rahul Sharma",
      "email": "rahul@example.com",
      "phone": "9876543210",
      "profile_image": null,
      "address": "Sector 62, Noida",
      "latitude": 28.62,
      "longitude": 77.36,
      "wallet_balance": 250.00,
      "status": 1,
      "has_password": true,
      "created_at": "2026-03-28T10:00:00+00:00"
    },
    "stats": {
      "orders": 4,
      "spent": 2180.00,
      "wallet": 250.00,
      "open_complaints": 1
    },
    "recent_orders": [],
    "recent_transactions": []
  }
}
```

`recent_orders` = last 5 (order summary). `recent_transactions` = last 4.

### cURL

```bash
curl -s -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN" \
  "http://127.0.0.1:8000/api/v1/dashboard"
```

---

## 2. Profile

**`GET /dashboard/profile`** — Auth

### Response `200`

```json
{
  "success": true,
  "data": { }
}
```

`data` = user object (FIELDS.md).

---

## 3. Update profile

**`PUT /dashboard/profile`** or **`PATCH /dashboard/profile`** — Auth

### Request

```json
{
  "name": "Rahul Sharma",
  "phone": "9876543210",
  "email": "rahul@example.com",
  "address": "Flat 12, Sector 62, Noida",
  "latitude": 28.62,
  "longitude": 77.36
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Profile update ho gaya! / प्रोफाइल सेव हो गई।",
  "data": { }
}
```

---

## 4. Change / set password

**`PUT /dashboard/profile/password`** — Auth

Agar user ke paas pehle se password hai (email/phone login), `current_password` required.

Google-only user (password empty) — sirf naya password + confirm.

### Request

```json
{
  "current_password": "oldpass12",
  "password": "newpass12",
  "password_confirmation": "newpass12"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Password change ho gaya! / पासवर्ड बदल दिया गया।",
  "data": { }
}
```

### Response `422` — galat current password

```json
{
  "success": false,
  "message": "Purana password galat hai / वर्तमान पासवर्ड गलत है।",
  "errors": {
    "current_password": ["Purana password galat hai / वर्तमान पासवर्ड गलत है।"]
  }
}
```

---

## 5. Order list

**`GET /dashboard/orders`** — Auth

Query: `page`, `per_page` (1–50, default **10**).

### Response `200`

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "order_number": "FNV-20261021-AB12CD",
      "status": "paid",
      "status_label": "Paid",
      "status_tone": "ok",
      "total": 540.00,
      "wallet_amount": 50.00,
      "discount_amount": 20.00,
      "delivery_fee": 0,
      "items_count": 2,
      "created_at": "2026-10-21T12:00:00+00:00",
      "created_at_label": "21 Oct 2026, 12:00 PM",
      "can_cancel": true,
      "items": []
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 4
  }
}
```

List `items[]` max **3** lines (thumbs). Har item par `can_rate`, `my_rating`.

---

## 6. Order detail

**`GET /dashboard/orders/{id}`** — Auth

Dusre user ka order → `404`.

### Response `200`

```json
{
  "success": true,
  "data": {
    "id": 12,
    "order_number": "FNV-20261021-AB12CD",
    "status": "paid",
    "phone": "9876543210",
    "delivery_address": "Flat 12, Noida, Uttar Pradesh, 201301",
    "pincode": "201301",
    "city": "Gautam Buddha Nagar",
    "state": "Uttar Pradesh",
    "delivery_schedule_type": "scheduled",
    "delivery_date": "2026-10-22",
    "delivery_time": "10:00-12:00",
    "delivery_label": "Thu, 22 Oct 2026 · 10:00 AM – 12:00 PM",
    "coupon_code": "FRESH50",
    "payment_method": "cashfree",
    "can_cancel": true,
    "cancel_policy": "Scheduled order delivery slot shuru hone se 2 ghante pehle tak cancel ho sakta hai.",
    "refundable_amount": 590.00,
    "is_paid": true,
    "is_cancelled": false,
    "bill_available": true,
    "items": []
  }
}
```

---

## 7. Cancel order

**`POST /dashboard/orders/{id}/cancel`** — Auth

Web jaisa: paid/confirmed, cancel window ke andar. Refund wallet me.

### Request

```json
{
  "cancel_reason": "Wrong delivery time selected"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Order cancel ho gaya. ₹590.00 aapke wallet me refund kar diye gaye.",
  "data": { }
}
```

`data` = updated order detail.

### Response `422` — window nikal gayi

```json
{
  "success": false,
  "message": "Cancel karne ka samay nikal gaya hai. Madad ke liye complaint karein."
}
```

---

## 8. Order bill / invoice

**`GET /dashboard/orders/{id}/bill`** — Auth

Available jab status `paid` | `confirmed` | `cancelled`.

Full invoice JSON — same as [Cart invoice](../cart/API.md#14-invoice--tax-bill).

### Response `404`

```json
{
  "success": false,
  "message": "Bill abhi available nahi hai."
}
```

---

## 9. Wallet

**`GET /dashboard/wallet`** — Auth

Query: `page`, `per_page` (1–50, default **15**).

### Response `200`

```json
{
  "success": true,
  "data": {
    "balance": 250.00,
    "total_credited": 800.00,
    "total_used": 550.00,
    "transactions": [
      {
        "id": 9,
        "type": "refund",
        "is_credit": true,
        "source": "refund",
        "source_label": "Order refund",
        "amount": 590.00,
        "balance_after": 250.00,
        "description": "Order FNV-20261021-AB12CD cancel — refund",
        "complaint_id": null,
        "order_id": 12,
        "created_at": "2026-10-21T12:30:00+00:00",
        "created_at_label": "21 Oct 2026, 12:30 PM"
      }
    ]
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 8
  }
}
```

---

## 10. Complaints list

**`GET /dashboard/complaints`** — Auth

Query: `page`, `per_page` (default **10**).

### Response `200`

```json
{
  "success": true,
  "data": [],
  "order_options": [
    {
      "id": 12,
      "order_number": "FNV-20261021-AB12CD",
      "total": 540.00,
      "created_at": "2026-10-21T12:00:00+00:00",
      "created_at_label": "21 Oct 2026"
    }
  ],
  "meta": { }
}
```

`order_options` — complaint form dropdown (last 30 orders).

---

## 11. Complaint detail

**`GET /dashboard/complaints/{id}`** — Auth

### Response `200`

```json
{
  "success": true,
  "data": {
    "id": 3,
    "subject": "Late delivery",
    "message": "Order 40 min late aaya.",
    "status": "open",
    "status_label": "Open",
    "admin_reply": null,
    "credited_amount": 0,
    "order_id": 12,
    "order_number": "FNV-20261021-AB12CD",
    "resolved_at": null,
    "created_at": "2026-10-21T13:00:00+00:00",
    "created_at_label": "21 Oct 2026, 01:00 PM"
  }
}
```

---

## 12. Create complaint

**`POST /dashboard/complaints`** — Auth

### Request

```json
{
  "order_id": 12,
  "subject": "Late delivery",
  "message": "Order 40 min late aaya."
}
```

`order_id` optional, lekin agar diya to **isi user** ka order hona chahiye.

### Response `201`

```json
{
  "success": true,
  "message": "Complaint register ho gayi. Team jald review karegi — sahi paaya gaya to wallet me amount credit hoga.",
  "data": { }
}
```

---

## 13. Ratings list

**`GET /dashboard/ratings`** — Auth

Sirf **paid/confirmed** orders ke unique products.

### Response `200`

```json
{
  "success": true,
  "data": {
    "pending": [
      {
        "product_id": 5,
        "product_name": "Chicken Curry Cut",
        "image": "http://127.0.0.1:8000/storage/products/chicken.jpg",
        "order_id": 12,
        "order_number": "FNV-20261021-AB12CD",
        "purchased_at": "2026-10-21T12:00:00+00:00",
        "has_rating": false,
        "rating": null,
        "review": null
      }
    ],
    "rated": []
  }
}
```

---

## 14. Submit rating

**`POST /dashboard/ratings/{product}`** — Auth

Sirf khareeda hua product, ek baar.

### Request

```json
{
  "rating": 5,
  "review": "Bahut fresh chicken tha."
}
```

### Response `201`

```json
{
  "success": true,
  "message": "Chicken Curry Cut ke liye rating dene ka shukriya!",
  "data": {
    "id": 8,
    "product_id": 5,
    "order_id": 12,
    "rating": 5,
    "review": "Bahut fresh chicken tha.",
    "status": 1,
    "created_at": "2026-10-21T14:00:00+00:00"
  }
}
```

### Response `422`

```json
{
  "success": false,
  "message": "Rating sirf khareede hue products par de sakte hain."
}
```

Ya: `Aap is product ko pehle hi rating de chuke hain.`

---

## Mobile screens (suggested)

| Screen | API |
|--------|-----|
| Dashboard home | `GET /dashboard` |
| My orders | `GET /dashboard/orders` |
| Order detail | `GET /dashboard/orders/{id}` |
| Print / PDF bill | `GET /dashboard/orders/{id}/bill` |
| Wallet | `GET /dashboard/wallet` |
| Complaints | `GET /dashboard/complaints` |
| Rate products | `GET /dashboard/ratings` |
| Profile settings | `GET /dashboard/profile` |
