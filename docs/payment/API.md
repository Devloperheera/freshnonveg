# Payment API — Cart se Order Place tak (complete)

Base: `{APP_URL}/api/v1` · Overview: [README.md](./README.md) · SDK setup: [GATEWAYS.md](./GATEWAYS.md)

Har request: `Accept: application/json` + `Authorization: Bearer {token}`

Field-by-field details (cart item, quote, invoice object): [../cart/FIELDS.md](../cart/FIELDS.md)

---

## Step 1 — Add to cart

**`POST /cart/products/{product}`**

`{product}` = product id. `product_weight_id` = catalog `GET /products/{id}` ke `weights[].id`.

```json
{
  "product_weight_id": 14,
  "quantity": 1,
  "preference_note": "Skinless pieces"
}
```

**`201`**

```json
{
  "success": true,
  "message": "Product cart mein add ho gaya.",
  "data": { "item": { }, "is_empty": false, "items": [], "cart_count": 2, "wishlist_count": 1 }
}
```

Same product + weight + note dubara → quantity badhti hai (max 99). Inactive product → `422`.

**Cart manage:**

| Method | Path | Body |
|--------|------|------|
| PATCH | `/cart/{cartItem}` | `{ "quantity": 3 }` (`cartItem` = cart line id) |
| DELETE | `/cart/{cartItem}` | — |
| POST | `/cart/clear` | — |

---

## Step 2 — Cart dekho

**`GET /cart`**

```json
{
  "success": true,
  "data": {
    "is_empty": false,
    "items": [],
    "items_count": 2,
    "subtotal": 540.00,
    "shipping": 100,
    "shipping_label": "₹100.00",
    "delivery_schedule_type": "scheduled",
    "delivery_fee": 100,
    "instant_delivery_fee": 100,
    "instant_fee_applied": 0,
    "delivery_charge": 100,
    "free_delivery_min_order": 1100,
    "amount_for_free_delivery": 560,
    "discount": 0,
    "wallet_balance": 250.00,
    "payable": 640.00,
    "total": 640.00,
    "payment_method": "razorpay",
    "payment_gateway": "razorpay",
    "payment_gateway_label": "Razorpay",
    "payment_configured": true,
    "cart_count": 2,
    "wishlist_count": 1,
    "pending_paid_order": null
  }
}
```

`pending_paid_order` != `null` → pichla order (app band ho gaya tha) actually **paid** ho chuka hai — seedha success screen dikhao.

### Delivery charges (admin se dynamic)

Values admin panel (`Settings → Delivery charges`) se aati hain — **app me hardcode mat karo**, hamesha response se lo.

| Field | Matlab |
|-------|--------|
| `shipping` | Normal delivery charge. `subtotal >= free_delivery_min_order` → `0`, warna `delivery_charge` |
| `shipping_label` | `"Free"` ya `"₹100.00"` — seedha UI me dikhao |
| `instant_fee_applied` | Instant chuna to `instant_delivery_fee`, warna `0` (har order value par lagta hai) |
| `delivery_fee` | **Kul delivery** = `shipping` + `instant_fee_applied` (yahi total me judta hai) |
| `amount_for_free_delivery` | Itna aur add karo to delivery free — "₹560 aur add karein" banner ke liye (`0` = already free) |
| `free_delivery_min_order`, `delivery_charge`, `instant_delivery_fee` | Current admin settings |

Example (default: ₹1100+ free, warna ₹100, instant +₹100):

| Subtotal | Scheduled | Instant |
|----------|-----------|---------|
| ₹500 | ₹100 | ₹200 |
| ₹1100 | Free | ₹100 |
| ₹1500 | Free | ₹100 |

Free limit **subtotal** (coupon se pehle) par check hoti hai. Instant/scheduled badalne par `POST /checkout/quote` se naya total lo.

---

### Time slots — aaj ki date (India time)

`time_slots[]` me har slot ke saath `start_time`, `end_time` aur `available_today` aata hai, aur upar `today` (IST date, `Asia/Kolkata`).

```json
"today": "2026-11-20",
"timezone": "Asia/Kolkata",
"time_slots": [
  { "value": "09:00-10:00", "label": "09:00 AM – 10:00 AM", "start_time": "09:00", "end_time": "10:00", "available_today": false },
  { "value": "15:00-16:00", "label": "03:00 PM – 04:00 PM", "start_time": "15:00", "end_time": "16:00", "available_today": true }
]
```

- User ne **`today`** wali date chuni → sirf `available_today: true` wale slots dikhao. Aage ki date → saare slots.
- Date picker ka minimum = `today` (phone ki date nahi).
- Server bhi check karta hai: beeta hua slot bheja to `422` → `errors.delivery_time`: `Ye time slot nikal chuka hai. Aage ka slot ya kal ki date chunein.`
- Screen der tak khuli rahe to "Place order" se pehle `GET /checkout` dobara call karke slots refresh kar lo.

---

## Step 3 — Checkout screen

**`GET /checkout`**

Query (optional): `delivery_schedule_type=instant|scheduled`, `coupon_code`, `use_wallet=1`

**`200`**

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
    "subtotal": 540.00,
    "total": 540.00,
    "time_slots": [
      { "value": "10:00-12:00", "label": "10:00 AM – 12:00 PM" }
    ],
    "instant_delivery_fee": 100,
    "wallet_balance": 250.00,
    "wallet_max_usable": 249.00,
    "minimum_online_pay": 1,
    "payment_method": "razorpay",
    "payment_gateway": "razorpay",
    "payment_gateway_label": "Razorpay",
    "payment_configured": true
  }
}
```

`payment_gateway_label` ko UI me dikha sakte ho: *"UPI, Card, Netbanking — Razorpay secure checkout"*.

**Pichla order already paid nikla:**

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "already_paid": true,
  "data": { "order": { }, "invoice": { } }
}
```

**Errors**

| Code | Message | Kab |
|------|---------|-----|
| `422` | `Pehle cart mein items add karein.` | Cart khali |
| `422` | `Online payment configure nahi hai. Checkout abhi available nahi.` | Admin ne koi gateway active nahi kiya |

---

## Step 4 — Pincode + delivery zone

**`GET /checkout/pincode/{pincode}`** — 6 digit

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

`deliverable: false` → "Place order" button disable karo, `message_hi` / `message_en` dikhao.

Address autocomplete (optional): **`GET /checkout/address-suggest?q=sector 62 noida`** (min 3 chars).

---

## Step 5 — Coupon (optional)

**`POST /checkout/coupon`**

```json
{ "code": "FRESH50", "delivery_schedule_type": "scheduled", "use_wallet": false }
```

**`200`**

```json
{
  "success": true,
  "message": "FRESH50 lag gaya. ₹50 off",
  "data": {
    "coupon": { "code": "FRESH50", "discount_type": "fixed", "discount_value": 50, "discount": 50 },
    "subtotal": 540.00,
    "discount": 50.00,
    "total": 490.00
  }
}
```

Galat / expired / min-order fail → `422` + `errors.coupon_code`.

---

## Step 6 — Live total (quote)

**`POST /checkout/quote`** — jab user Instant/Scheduled badle, coupon lagaye, wallet tick kare

```json
{ "delivery_schedule_type": "instant", "coupon_code": "FRESH50", "use_wallet": true }
```

Response = Step 2 jaisa quote object. Yahan galat coupon **silently ignore** hota hai (discount 0).
**`total`** = jo online pay hoga.

---

## Step 7 — ORDER PLACE ⭐

**`POST /checkout`**

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
  "delivery_date": "2026-11-18",
  "delivery_time": "10:00-12:00",
  "coupon_code": "FRESH50",
  "use_wallet": true,
  "order_notes": "Call on arrival"
}
```

| Field | Required | Note |
|-------|----------|------|
| `phone` | ✅ | max 20 |
| `pincode` | ✅ | 6 digit, delivery zone me hona chahiye |
| `state`, `city` | ✅ | pincode lookup se |
| `post_office` | — | |
| `delivery_address` | ✅ | max 2000 |
| `delivery_schedule_type` | ✅ | `instant` / `scheduled` |
| `delivery_date` | scheduled me ✅ | `YYYY-MM-DD`, aaj ya baad |
| `delivery_time` | scheduled me ✅ | `time_slots[].value` |
| `payment_method` | — | **Bhejne ki zarurat nahi.** Server admin wala active gateway khud lagata hai. (Bhejoge to `cashfree` / `razorpay` / `online` allowed, par ignore hoga) |
| `coupon_code` | — | |
| `use_wallet` | — | `true` / `false` |
| `order_notes` | — | max 1000 |

### Response `201` — jab **Razorpay** active ho

```json
{
  "success": true,
  "message": "Payment start ho gaya. Razorpay se pay karein.",
  "data": {
    "order": {
      "id": 57,
      "order_number": "FNV-20261117-AB12CD",
      "status": "awaiting_payment",
      "payment_method": "razorpay",
      "total": 440.00
    },
    "payment": {
      "gateway": "razorpay",
      "environment": "SANDBOX",
      "order_id": "FNV-20261117-AB12CD",
      "order_amount": 440.00,
      "order_currency": "INR",
      "key_id": "rzp_test_xxxxxxxxxxxx",
      "razorpay_order_id": "order_PQxxxxxxxxxxxx",
      "amount_paise": 44000,
      "checkout_options": {
        "key": "rzp_test_xxxxxxxxxxxx",
        "amount": 44000,
        "currency": "INR",
        "name": "FreshNonVeg",
        "description": "Order FNV-20261117-AB12CD",
        "order_id": "order_PQxxxxxxxxxxxx",
        "prefill": {
          "name": "Rahul Sharma",
          "email": "rahul@example.com",
          "contact": "9876543210"
        },
        "notes": { "order_number": "FNV-20261117-AB12CD" },
        "theme": { "color": "#d62828" }
      },
      "payment_session_id": null,
      "cf_order_id": null,
      "payment_link": null,
      "checkout_url": null
    }
  }
}
```

👉 `checkout_options` ko **as-it-is** Razorpay SDK ke `open()` me de do. ([GATEWAYS.md → Razorpay](./GATEWAYS.md#razorpay))

### Response `201` — jab **Cashfree** active ho

```json
{
  "success": true,
  "message": "Payment start ho gaya. Cashfree se pay karein.",
  "data": {
    "order": {
      "id": 57,
      "order_number": "FNV-20261117-AB12CD",
      "status": "awaiting_payment",
      "payment_method": "cashfree",
      "total": 440.00
    },
    "payment": {
      "gateway": "cashfree",
      "environment": "PRODUCTION",
      "order_id": "FNV-20261117-AB12CD",
      "order_amount": 440.00,
      "order_currency": "INR",
      "payment_session_id": "session_xxxxxxxxxxxxxxxx",
      "cf_order_id": "4567891234",
      "payment_link": null,
      "checkout_url": "https://freshnonveg.com/pay/cashfree/FNV-20261117-AB12CD?expires=...&signature=..."
    }
  }
}
```

👉 **App:** seedha `checkout_url` browser / WebView me kholo (hamara hosted pay page + Cashfree JS).  
👉 Native Cashfree SDK optional; direct Cashfree `payments.cashfree.com/pg/view/...` URL **use mat karo** (404).

### Order place hone ke baad

- Order `awaiting_payment` status me ban jata hai.
- **Cart clear** ho jata hai.
- Wallet use kiya tha to wallet se amount kat jata hai (payment fail par wapas).

### Errors (`422`)

| Field (`errors.*`) | Message (example) | Kab |
|---------|-------------------|-----|
| `cart` | `Pehle cart mein items add karein.` | Cart khali |
| `pincode` | `Abhi is location par delivery nahi ho rahi…` | Zone ke bahar |
| `coupon_code` | `Coupon code galat hai.` / `Discount ke baad minimum ₹1 pay karna hoga.` | |
| `payment_method` | `Online payment configure nahi hai. Admin panel se payment gateway … active karein.` | Koi gateway active nahi |
| `payment_method` | `Payment start nahi ho payi. Cart safe hai, dubara try karein.` | Gateway API down |
| `payment_method` | `Razorpay keys galat hain (401)…` / `Cashfree ne payment block ki (403)…` | Admin keys galat |
| `delivery_date` / `delivery_time` | validation | Scheduled me missing |

```json
{
  "message": "Coupon code galat hai.",
  "errors": { "coupon_code": ["Coupon code galat hai."] }
}
```

---

## Step 8 — SDK se payment

`data.payment.gateway` ke hisaab se:

| `gateway` | Kya karna hai |
|-----------|---------------|
| `razorpay` | Razorpay SDK → `open(checkout_options)` |
| `cashfree` | Cashfree SDK → `payment_session_id` + `order_id` |

Poora code: [GATEWAYS.md](./GATEWAYS.md)

---

## Step 9 — Verify payment ⭐ (SDK ke baad hamesha)

**`POST /checkout/verify`**

SDK **success, failure ya user cancel** — teeno case me call karo.

### Request

```json
{ "order_number": "FNV-20261117-AB12CD" }
```

ya `{ "order_id": 57 }` (DB id).

Razorpay me SDK success ke fields bhi bhej sakte ho (optional, server ignore kar deta hai — wo khud Razorpay API se confirm karta hai):

```json
{
  "order_number": "FNV-20261117-AB12CD",
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_order_id": "order_xxxxx",
  "razorpay_signature": "xxxx"
}
```

### `200` — Paid ✅

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "data": {
    "order": { "id": 57, "order_number": "FNV-20261117-AB12CD", "status": "paid", "payment_method": "razorpay" },
    "invoice": { }
  }
}
```

→ Success screen dikhao.

### `422` — Paid nahi ❌

```json
{
  "success": false,
  "message": "Payment complete nahi hui. Dubara checkout try karein.",
  "data": {
    "order": { "id": 57, "status": "payment_failed" }
  }
}
```

→ Order `payment_failed` ho jata hai, wallet + coupon wapas. User ko cart par bhejo.

`404` → order is user ka nahi / mila nahi.

> **Tip:** Agar SDK success aaya lekin verify `422` de (bahut kam hota hai, bank delay), to 3–5 second baad
> Step 10 ka status poll 2–3 baar try karo. Webhook se bhi order baad me `paid` ho jata hai.

---

## Step 10 — Payment status (poll, optional)

**`GET /checkout/orders/{order}/status`**

Gateway se sync karta hai, order **fail mark nahi** karta (safe for polling).

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

Paid nahi → `is_paid: false`, `invoice: null`.

---

## Step 11 — Success screen

**`GET /checkout/orders/{order}/success`**

```json
{
  "success": true,
  "message": "Order place ho gaya! / Your order has been placed.",
  "data": { "order": { }, "invoice": { } }
}
```

Pending → `422` `Payment abhi pending hai.`

---

## Step 12 — Bill / invoice

**`GET /checkout/orders/{order}/invoice`** (alias `GET /orders/{order}/invoice`)

Sirf `paid` / `confirmed` / `cancelled` orders. `payment` block me gateway info:

```json
{
  "success": true,
  "data": {
    "invoice_title": "TAX INVOICE / FOOD BILL",
    "stamp": "PAID",
    "order": { "order_number": "FNV-20261117-AB12CD" },
    "totals": { "subtotal": 540.00, "discount": 50.00, "wallet_amount": 50.00, "grand_total": 440.00 },
    "payment": {
      "method": "razorpay",
      "method_label": "Online",
      "gateway": "razorpay",
      "gateway_label": "Razorpay",
      "status": "paid",
      "stamp": "PAID",
      "is_paid": true,
      "is_cancelled": false,
      "payment_reference": "pay_PQxxxxxxxxxx",
      "cashfree_payment_id": null,
      "razorpay_order_id": "order_PQxxxxxxxxxx",
      "razorpay_payment_id": "pay_PQxxxxxxxxxx",
      "paid_online": 440.00,
      "wallet_used": 50.00
    }
  }
}
```

**`payment_reference`** use karo display ke liye — dono gateway me sahi ID deta hai
(Cashfree: `cf_order_id`, Razorpay: `pay_xxx`).

Not paid → `404` `Invoice abhi available nahi hai. Pehle payment complete karein.`

---

## Screens ↔ API

| Screen | API |
|--------|-----|
| Product → Add | `POST /cart/products/{id}` |
| Cart | `GET /cart` |
| Checkout | `GET /checkout` |
| Pincode | `GET /checkout/pincode/{pincode}` |
| Coupon | `POST /checkout/coupon` |
| Total update | `POST /checkout/quote` |
| **Pay button** | `POST /checkout` → gateway SDK |
| SDK ke baad | `POST /checkout/verify` |
| Success | `GET /checkout/orders/{id}/success` |
| Bill | `GET /checkout/orders/{id}/invoice` |

## Purane app version ke liye (backward compatible)

- Response me `payment_session_id`, `checkout_url` keys pehle jaisi hain → Cashfree wala purana app code chalta rahega
  **jab tak admin Cashfree active rakhe**.
- Razorpay active hone par purane app me `payment_session_id = null` aayega → isliye **naya app version** (gateway switch wala) release karna hoga.

---

## Reorder / Pay again

**`POST /dashboard/orders/{order}/reorder`** — Auth (body nahi chahiye)

Purane order ke saare items **current price** par cart me daal deta hai. Uske baad normal flow: `GET /checkout` → slot/address → `POST /checkout` → payment.

- `payment_failed` order → button text **"Pay again"**
- `paid` / `confirmed` / `cancelled` → **"Reorder"**
- `awaiting_payment` → button mat dikhao

Purana failed order wahi rehta hai — naya order banta hai (slot, coupon, wallet dobara chunna hota hai).

**`200`**

```json
{
  "success": true,
  "message": "Items cart me wapas aa gaye. Slot chun ke dobara pay karein.",
  "data": {
    "added": 1,
    "skipped": ["Mutton Keema (500 g)"],
    "cart": { "items": [], "subtotal": 899.00, "total": 999.00, "cart_count": 1 }
  }
}
```

`skipped` = jo products ab inactive / weight hat gaya — user ko bata do. `cart` = `GET /cart` jaisa object.

**`422`** — koi bhi product available nahi: `Is order ke products abhi available nahi hain.`
**`404`** — order is user ka nahi.

---

## Saved addresses (multi address)

### Checkout par

`GET /checkout` ke `data.saved_addresses[]` me user ke saved address aate hain (default pehle):

```json
"saved_addresses": [
  {
    "id": 3, "label": "Home", "phone": "9818936520",
    "pincode": "201301", "state": "Uttar Pradesh", "city": "Noida", "post_office": null,
    "address": "Flat 12, Sector 62",
    "full_address": "Flat 12, Sector 62, Noida, Uttar Pradesh, 201301",
    "is_default": true
  }
]
```

App UI: saved address cards + **"Naya address"**. Default wala pehle se chuna ho.

- **Saved chuna** → uske fields `POST /checkout` me bhejo + `address_id`. `GET /checkout/pincode/{pincode}` se zone check zarur karo.
- **Naya address** → fields bhar ke bhejo + `"save_address": true` aur `"address_label": "Home" | "Work" | "Other"` → order ke baad address-book me save ho jata hai (same pincode + address dobara save nahi hota).

```json
{
  "address_id": 3,
  "phone": "9818936520", "pincode": "201301", "state": "Uttar Pradesh", "city": "Noida",
  "delivery_address": "Flat 12, Sector 62",
  "delivery_schedule_type": "instant"
}
```

`address_id` kisi aur user ka → `422`.

### Manage (Profile → My addresses)

| Method | Path | Body |
|--------|------|------|
| GET | `/dashboard/addresses` | — |
| POST | `/dashboard/addresses` | `label`, `phone`*, `pincode`* (6 digit), `state`*, `city`*, `address`*, `post_office`, `is_default` |
| PUT | `/dashboard/addresses/{id}` | same as POST |
| DELETE | `/dashboard/addresses/{id}` | — |
| PATCH | `/dashboard/addresses/{id}/default` | — |

Har response:

```json
{
  "success": true,
  "message": "Address save ho gaya.",
  "data": {
    "address": { },
    "addresses": [ ],
    "max_addresses": 10
  }
}
```

- Pehla address apne aap **default** banta hai. Default delete karo to sabse recent wala default ban jata hai.
- Max **10** address → 11th par `422` `errors.address`.
- Dusre user ka address → `404`.
