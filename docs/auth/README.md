# FreshNonVeg — Auth API (Mobile / App)

Customer authentication REST API. Web login/register same business rules; yahan Sanctum **Bearer token** use hota hai.

## Base URL

```
{APP_URL}/api/v1/auth
```

**Examples**

| Environment | Base URL |
|-------------|----------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1/auth` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/auth` |

`APP_URL` in `.env` should match how the app calls the server.

## Headers

| Header | Value | When |
|--------|--------|------|
| `Accept` | `application/json` | Always |
| `Content-Type` | `application/json` | POST / PUT / PATCH with JSON body |
| `Authorization` | `Bearer {token}` | Protected routes (after login/register) |

## Response shape

**Success (typical)**

```json
{
  "success": true,
  "message": "...",
  "token": "...",
  "user": { }
}
```

**Validation error (422)**

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

**Unauthorized (401)** — missing/invalid token on protected routes.

## Login identifier (`login`)

- **Email** — valid email format (case normalized to lowercase).
- **Phone** — digits/`+`/spaces; stored without spaces after normalize.

Same field as web: **ईमेल या मोबाइल**.

## OTP

- 6 digits, expires per server config (stored on user record).
- When `APP_DEBUG=true`, responses may include `debug_otp` for testing.
- Channel: `email` or `phone` (auto from `login`).

## Token

- Returned on: register, password login, OTP verify, Google login (success).
- Sanctum personal access token; send as `Authorization: Bearer ...`.
- Logout deletes **current** token only.
- New login with same `device_name` replaces previous token for that name.

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | All endpoints, request/response examples |
| [FIELDS.md](./FIELDS.md) | Every request & user object field |

## Quick endpoint list

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/register` | No | New account |
| POST | `/login` | No | Email/phone + password |
| POST | `/otp/send` | No | Send login or reset OTP |
| POST | `/otp/verify` | No | OTP login → token |
| POST | `/forgot-password/send` | No | Reset OTP |
| POST | `/forgot-password/reset` | No | OTP + new password |
| POST | `/google` | No | Google `id_token` (+ phone if needed) |
| GET | `/me` | Yes | Current user |
| GET | `/profile` | Yes | Same as `/me` |
| PUT/PATCH | `/profile` | Yes | Update profile (web jaisa + lat/long) |
| PUT/PATCH | `/profile/password` | Yes | Change/set password |
| POST | `/complete-profile` | Yes | Legacy: phone + address (Google flow) |
| POST | `/device` | Yes | FCM & device metadata |
| POST | `/logout` | Yes | Revoke current token |

## Related

- [Push notifications & delete account](../push-notifications/README.md)

- [Catalog](../catalog/README.md)
- [User dashboard](../user/README.md)
- [Cart → Buy](../cart/README.md)
- [Policy](../policy/README.md)
- [FAQ](../faq/README.md)
- [Home content](../home-content/README.md)
