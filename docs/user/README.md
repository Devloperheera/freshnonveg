# FreshNonVeg — User Dashboard API (Mobile / App)

Website **My Dashboard** jaisi customer account APIs. Orders, wallet, complaints, ratings, profile — Sanctum **Bearer token** required.

Web equivalent: `{APP_URL}/dashboard`, `/profile`

## Base URL

```
{APP_URL}/api/v1/dashboard
```

**Examples**

| Environment | Base URL |
|-------------|----------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1/dashboard` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/dashboard` |

`.env` mein `APP_URL` wahi hona chahiye jo app use karti hai.

## Headers

| Header | Value | When |
|--------|--------|------|
| `Accept` | `application/json` | Always |
| `Content-Type` | `application/json` | POST / PUT / PATCH with JSON body |
| `Authorization` | `Bearer {token}` | **Always** (after [Auth](../auth/README.md) login) |

## Response shape

**Success (typical)**

```json
{
  "success": true,
  "data": { }
}
```

**Paginated list**

```json
{
  "success": true,
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 14
  }
}
```

**Unauthorized (401)** — missing/invalid token.

```json
{
  "message": "Unauthenticated."
}
```

**Validation / business error (422)**

```json
{
  "success": false,
  "message": "Cancel karne ka samay nikal gaya hai. Madad ke liye complaint karein."
}
```

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Saare endpoints, request/response examples |
| [FIELDS.md](./FIELDS.md) | User, order, wallet, complaint, rating fields |

## Quick endpoint list

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/dashboard` | Yes | Overview — stats, recent orders, wallet activity |
| GET | `/dashboard/profile` | Yes | Profile (wallet balance included) |
| PUT/PATCH | `/dashboard/profile` | Yes | Name, phone, email, address, lat/long |
| PUT/PATCH | `/dashboard/profile/password` | Yes | Change / set password (min 8) |
| GET | `/dashboard/orders` | Yes | Order list (paginated) |
| GET | `/dashboard/orders/{id}` | Yes | Order detail + cancel/rate flags |
| POST | `/dashboard/orders/{id}/cancel` | Yes | Cancel + wallet refund |
| GET | `/dashboard/orders/{id}/bill` | Yes | Full tax invoice JSON |
| GET | `/dashboard/wallet` | Yes | Balance + transactions |
| GET | `/dashboard/complaints` | Yes | Complaints + order dropdown |
| POST | `/dashboard/complaints` | Yes | New complaint |
| GET | `/dashboard/complaints/{id}` | Yes | Complaint detail |
| GET | `/dashboard/ratings` | Yes | Pending + already rated products |
| POST | `/dashboard/ratings/{product}` | Yes | Submit 1–5 rating |

Invoice same payload: [Cart / Checkout](../cart/README.md) `GET /checkout/orders/{id}/invoice`.

## Web parity

| Web | API |
|-----|-----|
| `GET /dashboard` | `GET /api/v1/dashboard` |
| `GET /dashboard/orders` | `GET /api/v1/dashboard/orders` |
| `GET /dashboard/orders/{id}` | `GET /api/v1/dashboard/orders/{id}` |
| `POST /dashboard/orders/{id}/cancel` | `POST /api/v1/dashboard/orders/{id}/cancel` |
| `GET /orders/{id}/bill` | `GET /api/v1/dashboard/orders/{id}/bill` |
| `GET /dashboard/wallet` | `GET /api/v1/dashboard/wallet` |
| `GET/POST /dashboard/complaints` | `/api/v1/dashboard/complaints` |
| `GET/POST /dashboard/ratings` | `/api/v1/dashboard/ratings` |
| `GET/PUT /profile` | `/api/v1/dashboard/profile` |

Logic web aur app **same** hai (`CustomerAccountService`).

## Related

- [Auth](../auth/README.md) — token lene ke liye
- [Cart → Buy](../cart/README.md) — cart, checkout, Cashfree, invoice
- [Catalog](../catalog/README.md)
