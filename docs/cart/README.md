# FreshNonVeg — Cart → Buy API (Mobile / App)

Website cart, wishlist, checkout, Cashfree payment aur **tax invoice** — same application logic.

Auth: Sanctum **Bearer token** (see [Auth](../auth/README.md)).

## Base URL

```
{APP_URL}/api/v1
```

**Examples**

| Environment | Base URL |
|-------------|----------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1` |

## Headers

| Header | Value | When |
|--------|--------|------|
| `Accept` | `application/json` | Always |
| `Content-Type` | `application/json` | POST / PATCH JSON body |
| `Authorization` | `Bearer {token}` | **Always** |

## App buy flow (suggested)

1. `POST /cart/products/{id}` — add item (`product_weight_id` catalog se)
2. `GET /cart` — review qty / totals
3. `GET /checkout` — slots, wallet, customer
4. `GET /checkout/pincode/{pincode}` — city/state + **delivery zone**
5. `POST /checkout/coupon` — optional
6. `POST /checkout/quote` — instant vs scheduled + wallet live total
7. `POST /checkout` — place order → Cashfree `payment_session_id`
8. Cashfree SDK se pay
9. `POST /checkout/verify` — confirm paid
10. Success screen: `GET /checkout/orders/{id}/success`
11. Bill: `GET /checkout/orders/{id}/invoice`

Delivery **sirf Noida / Greater Noida / Ghaziabad** zones. Bahar ka pincode → order block + Hindi/English coming-soon message.

Cashfree ko kam se kam **₹1** online chahiye — wallet poora amount cover nahi kar sakta.

## Response shape

**Cart / quote**

```json
{
  "success": true,
  "message": "Product cart mein add ho gaya.",
  "data": { }
}
```

**Unauthorized (401)**

```json
{ "message": "Unauthenticated." }
```

**Zone / validation (422)**

```json
{
  "message": "The pincode field is required.",
  "errors": {
    "pincode": ["The pincode field is required."]
  }
}
```

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Cart, wishlist, checkout, payment, invoice endpoints |
| [FIELDS.md](./FIELDS.md) | Har object / request field |

## Quick endpoint list

| Module | Method | Path | Description |
|--------|--------|------|-------------|
| **Cart** | GET | `/cart` | Cart + totals + pending paid order |
| | POST | `/cart/products/{product}` | Add (weight + qty) |
| | PATCH | `/cart/{cartItem}` | Quantity 1–99 |
| | DELETE | `/cart/{cartItem}` | Remove line |
| | POST | `/cart/clear` | Empty cart |
| **Wishlist** | GET | `/wishlist` | List |
| | POST | `/wishlist/products/{product}` | Add |
| | DELETE | `/wishlist/{id}` | Remove item |
| | DELETE | `/wishlist/products/{product}` | Remove by product |
| **Checkout** | GET | `/checkout` | Checkout screen data |
| | POST | `/checkout/quote` | Recalculate totals |
| | POST | `/checkout/coupon` | Apply coupon |
| | GET | `/checkout/pincode/{pincode}` | Lookup + zone |
| | GET | `/checkout/address-suggest?q=` | Address autocomplete |
| | POST | `/checkout` | Place order + payment session |
| **Pay** | POST | `/checkout/verify` | After Cashfree |
| | GET | `/checkout/orders/{id}/status` | Poll payment |
| | GET | `/checkout/orders/{id}/success` | Success page JSON |
| **Invoice** | GET | `/checkout/orders/{id}/invoice` | Full tax bill |
| | GET | `/orders/{id}/invoice` | Same invoice |

## Web parity

| Web | API |
|-----|-----|
| Add to cart / wishlist | `POST /cart/products/{id}`, `POST /wishlist/products/{id}` |
| `GET /cart` | `GET /api/v1/cart` |
| `GET /checkout` | `GET /api/v1/checkout` |
| Coupon apply | `POST /api/v1/checkout/coupon` |
| Pincode lookup | `GET /api/v1/checkout/pincode/{pincode}` |
| Place + Cashfree redirect | `POST /api/v1/checkout` → SDK `payment_session_id` |
| Payment return | `POST /api/v1/checkout/verify` |
| Success page | `GET /api/v1/checkout/orders/{id}/success` |
| Food bill | `GET /api/v1/checkout/orders/{id}/invoice` |

Webhook (`POST /checkout/cashfree/webhook`) server-side hai — app se nahi.

## Related

- [Auth](../auth/README.md)
- [Catalog](../catalog/README.md) — `product_weight_id`
- [User dashboard](../user/README.md) — orders after buy
