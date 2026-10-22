# Auth API — Fields reference

## Common device / app fields (optional)

Many endpoints accept these in JSON body. All optional unless noted.

| Field | Type | Max | Description |
|-------|------|-----|-------------|
| `fcm_token` | string | — | Firebase push token |
| `device_type` | string | 30 | e.g. `android`, `ios` |
| `device_id` | string | 255 | Unique device id |
| `device_name` | string | 255 | Sanctum token name (default `mobile-app`) |
| `device_brand` | string | 80 | e.g. Samsung |
| `device_model` | string | 80 | e.g. SM-G991B |
| `os_version` | string | 40 | e.g. 14 |
| `app_version` | string | 30 | e.g. 1.0.0 |
| `app_build_number` | string | 30 | e.g. 42 |
| `timezone` | string | 64 | e.g. Asia/Kolkata |
| `locale` | string | 20 | e.g. en_IN |

Used on: **register**, **login**, **otp/verify**, **google**, **device**.

---

## Register — `POST /register`

| Field | Required | Rules | Notes |
|-------|----------|-------|-------|
| `name` | Yes | string, max 255 | पूरा नाम |
| `phone` | Yes | string, max 20, unique | Normalized (spaces removed) |
| `email` | No | email, max 255, unique | Optional |
| `password` | Yes | string, min 6 | |
| `password_confirmation` | Yes | must match `password` | Laravel `confirmed` |
| + device fields | No | see above | |

---

## Login — `POST /login`

| Field | Required | Rules |
|-------|----------|-------|
| `login` | Yes | string, max 255 — email or phone |
| `password` | Yes | string |
| + device fields | No | |

---

## Send OTP — `POST /otp/send`

| Field | Required | Rules |
|-------|----------|-------|
| `login` | Yes | string, max 255 |
| `purpose` | No | `login` (default) or `reset` |

**Response extras:** `channel`, `login`, `debug_otp` (debug only).

---

## Verify OTP (login) — `POST /otp/verify`

| Field | Required | Rules |
|-------|----------|-------|
| `login` | Yes | string, max 255 |
| `otp` | Yes | string, size 6 |
| `fcm_token` | No | string |
| `device_type` | No | max 30 |
| `device_id` | No | max 255 |
| `device_name` | No | max 255 |

Returns `token` + `user`.

---

## Forgot password — send — `POST /forgot-password/send`

| Field | Required | Rules |
|-------|----------|-------|
| `login` | Yes | string, max 255 |

---

## Forgot password — reset — `POST /forgot-password/reset`

| Field | Required | Rules |
|-------|----------|-------|
| `login` | Yes | string, max 255 |
| `otp` | Yes | string, size 6 |
| `password` | Yes | string, min 6 |
| `password_confirmation` | Yes | match `password` |

---

## Google — `POST /google`

| Field | Required | Rules |
|-------|----------|-------|
| `id_token` | Yes | string — Google Sign-In ID token |
| `phone` | Conditional | max 20, unique — required if server returns `needs_phone` |
| + device fields | No | |

**422 with `needs_phone: true`** — send again with `phone`.

---

## Profile — read — `GET /me` or `GET /profile`

No body. Bearer token required.

---

## Profile — update — `PUT` or `PATCH /profile`

Web profile ke saath align (+ map coordinates for app).

| Field | Required | Rules |
|-------|----------|-------|
| `name` | Yes | string, max 255 |
| `phone` | Yes | string, max 20, unique (except self) |
| `email` | No | email, max 255, unique (except self) |
| `address` | No | string, max 500 | Delivery address |
| `latitude` | No | numeric | Optional GPS |
| `longitude` | No | numeric | Optional GPS |

---

## Profile — password — `PUT` or `PATCH /profile/password`

| Field | Required | Rules |
|-------|----------|-------|
| `password` | Yes | string, min 6 |
| `password_confirmation` | Yes | match `password` |
| `current_password` | If user already has password | string — **omit** for Google-only accounts setting first password |

---

## Complete profile (legacy) — `POST /complete-profile`

Prefer **`PUT /profile`** for new apps. Kept for Google phone completion flow.

| Field | Required | Rules |
|-------|----------|-------|
| `phone` | Yes | string, max 20, unique (except self) |
| `name` | No | string, max 255 |
| `address` | No | string |
| `latitude` | No | numeric |
| `longitude` | No | numeric |

---

## Device update — `POST /device`

Bearer token. Body: any **device fields** from common table (all optional). Updates `last_active_at`.

---

## Logout — `POST /logout`

Bearer token. No body.

---

## User object (`user` in responses)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | User id |
| `name` | string | Display name |
| `email` | string \| null | |
| `phone` | string \| null | |
| `profile_image` | string \| null | URL (e.g. Google avatar) |
| `latitude` | number \| null | |
| `longitude` | number \| null | |
| `address` | string \| null | |
| `status` | integer | 1 = active (typical) |
| `registration_source` | string \| null | e.g. `web`, `app`, `google_app`, `google_web` |
| `login_count` | integer | |
| `last_login_at` | ISO datetime \| null | |
| `web_last_login_at` | ISO datetime \| null | |
| `app_installed_at` | ISO datetime \| null | |
| `is_app_installed` | boolean | |
| `created_at` | ISO datetime | |

Password, OTP, and tokens are **never** returned.

---

## HTTP status codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created (register) |
| 401 | Unauthenticated |
| 422 | Validation / business error (e.g. invalid Google token, wrong OTP) |
