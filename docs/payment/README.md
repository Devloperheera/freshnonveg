# FreshNonVeg — Payment API (Cart → Order Place)

Mobile app ke liye **cart se lekar order place + payment + bill** tak ka poora flow.

Payment **2 gateway** se ho sakta hai — **Cashfree** ya **Razorpay**. Kaunsa chalega ye **admin panel** decide karta hai
(`Admin → Payment Gateways`). Ek time par sirf **ek** gateway active hota hai.

> **App ko gateway hardcode nahi karna hai.** `POST /checkout` ke response me `data.payment.gateway` aata hai —
> uske hisaab se Cashfree SDK ya Razorpay SDK kholo. Admin gateway badlega to app bina update ke chalti rahegi.

## Documentation files

| File | Kya hai |
|------|---------|
| [API.md](./API.md) | Har endpoint — request, response, errors (cart → order place → verify → invoice) |
| [GATEWAYS.md](./GATEWAYS.md) | Cashfree aur Razorpay SDK app me kaise lagayein + test details |

## Base URL

```
{APP_URL}/api/v1
```

| Environment | Base URL |
|-------------|----------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1` |

## Headers (har request)

| Header | Value |
|--------|-------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST / PATCH) |
| `Authorization` | `Bearer {token}` — login se mila Sanctum token ([Auth](../auth/README.md)) |

## Poora flow (step by step)

```
 1. POST /cart/products/{id}          → item cart me
 2. GET  /cart                        → cart review
 3. GET  /checkout                    → customer, slots, wallet, active gateway
 4. GET  /checkout/pincode/{pincode}  → city/state + delivery zone check
 5. POST /checkout/coupon             → (optional) coupon
 6. POST /checkout/quote              → instant/scheduled + wallet ke saath final total
 7. POST /checkout                    → ORDER PLACE  → data.payment milta hai
 8. data.payment.gateway dekho:
       "cashfree" → Cashfree SDK  (payment_session_id)
       "razorpay" → Razorpay SDK  (checkout_options)
 9. SDK success / fail / cancel — teeno case me →  POST /checkout/verify
10. Paid → GET /checkout/orders/{id}/success  (success screen)
11. Bill → GET /checkout/orders/{id}/invoice
```

### Diagram

```
 App                         Server (FreshNonVeg)                 Gateway
  │  POST /checkout  ───────────▶ order bana (awaiting_payment)
  │                               gateway order/session bana ───────▶ Cashfree / Razorpay
  │  ◀─────── data.payment ─────── cart clear
  │
  │  SDK open ─────────────────────────────────────────────────────▶ user pay karta hai
  │  ◀──────────────────────────────────────────── success / fail ──
  │
  │  POST /checkout/verify ─────▶ gateway API se status check ──────▶
  │  ◀──── paid (200) / not paid (422)
  │
  │                               (backup) webhook ◀──────────────── gateway
```

## Important rules

- **Final faisla hamesha server karta hai.** SDK "success" bole tab bhi `POST /checkout/verify` call karo — server gateway API
  se payment confirm karke hi order `paid` karta hai.
- **SDK cancel / fail par bhi verify call karo.** Unpaid order `payment_failed` mark hota hai aur wallet / coupon wapas ho jata hai.
- `POST /checkout` ke baad **cart server par clear** ho jata hai. Payment fail hua to user ko dobara cart bharna hoga.
- Online minimum **₹1** — wallet poora amount cover nahi kar sakta.
- Delivery sirf configured zones (Noida / Greater Noida / Ghaziabad). Zone ke bahar pincode → order place nahi hoga.
- COD nahi hai — sirf online payment.
- Agar koi gateway active nahi hai → `GET /checkout` par `payment_configured: false` / `422`. App "Payment abhi available nahi" dikhaye.

## Order status values

| `status` | Matlab |
|----------|--------|
| `awaiting_payment` | Order bana, payment pending |
| `paid` | Payment ho gaya ✅ |
| `confirmed` | Admin ne confirm kiya ✅ |
| `payment_failed` | Payment fail / cancel — wallet & coupon wapas |
| `cancelled` | Order cancel hua (refund wallet me) |

`paid` ya `confirmed` = paid order.

## Webhooks (app ke liye nahi)

Server-side backup — agar app beech me band ho jaye tab bhi order paid ho jata hai.

| Gateway | URL |
|---------|-----|
| Cashfree | `POST {APP_URL}/checkout/cashfree/webhook` |
| Razorpay | `POST {APP_URL}/checkout/razorpay/webhook` |

## Related

- [Auth](../auth/README.md) — token
- [Catalog](../catalog/README.md) — `product_weight_id`
- [Cart (purana doc)](../cart/README.md)
- [User dashboard](../user/README.md) — order history
