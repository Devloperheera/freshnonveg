# Auth API — Endpoints (complete)

Base: `{APP_URL}/api/v1/auth`

Field details: [FIELDS.md](./FIELDS.md)

---

## 1. Register

**`POST /register`** — Public

### Request

```json
{
  "name": "Rahul Sharma",
  "phone": "9876543210",
  "email": "rahul@example.com",
  "password": "secret12",
  "password_confirmation": "secret12",
  "device_type": "android",
  "device_name": "rahul-pixel-7",
  "fcm_token": "firebase-token-here"
}
```

### Response `201`

```json
{
  "success": true,
  "message": "Registration successful.",
  "token": "1|plainTextSanctumToken...",
  "user": {
    "id": 1,
    "name": "Rahul Sharma",
    "email": "rahul@example.com",
    "phone": "9876543210",
    "profile_image": null,
    "latitude": null,
    "longitude": null,
    "address": null,
    "status": 1,
    "registration_source": "app",
    "login_count": 1,
    "last_login_at": "2026-03-28T10:00:00.000000Z",
    "web_last_login_at": null,
    "app_installed_at": "2026-03-28T10:00:00.000000Z",
    "is_app_installed": true,
    "created_at": "2026-03-28T10:00:00.000000Z"
  }
}
```

---

## 2. Login (password)

**`POST /login`** — Public

### Request

```json
{
  "login": "9876543210",
  "password": "secret12",
  "device_name": "rahul-pixel-7"
}
```

`login` can be email: `"rahul@example.com"`.

### Response `200`

Same shape as register (without `201`): `success`, `message`, `token`, `user`.

---

## 3. Send OTP

**`POST /otp/send`** — Public

### Request (login OTP)

```json
{
  "login": "rahul@example.com",
  "purpose": "login"
}
```

### Request (reset OTP via same endpoint)

```json
{
  "login": "9876543210",
  "purpose": "reset"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "OTP sent successfully.",
  "channel": "email",
  "login": "rahul@example.com",
  "debug_otp": "482910"
}
```

`debug_otp` is `null` when `APP_DEBUG=false`.

---

## 4. Verify OTP (login)

**`POST /otp/verify`** — Public

### Request

```json
{
  "login": "9876543210",
  "otp": "482910",
  "device_name": "rahul-phone"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "OTP login successful.",
  "token": "2|...",
  "user": { }
}
```

---

## 5. Forgot password — send OTP

**`POST /forgot-password/send`** — Public

### Request

```json
{
  "login": "rahul@example.com"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Password reset OTP sent.",
  "channel": "email",
  "login": "rahul@example.com",
  "debug_otp": "591023"
}
```

---

## 6. Forgot password — reset

**`POST /forgot-password/reset`** — Public

### Request

```json
{
  "login": "rahul@example.com",
  "otp": "591023",
  "password": "newpass99",
  "password_confirmation": "newpass99"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Password updated successfully.",
  "user": { }
}
```

No new token; user should login again (password or OTP).

---

## 7. Google Sign-In

**`POST /google`** — Public

### Request

```json
{
  "id_token": "GOOGLE_ID_TOKEN_JWT",
  "phone": "9876543210",
  "device_type": "ios",
  "device_name": "rahul-iphone"
}
```

Omit `phone` on first call if unknown; if account needs phone, API responds with `needs_phone`.

### Response `200` (success)

```json
{
  "success": true,
  "message": "Google login successful.",
  "token": "3|...",
  "user": { }
}
```

### Response `422` (phone required)

```json
{
  "success": false,
  "needs_phone": true,
  "message": "Phone number required to complete registration.",
  "user": {
    "id": 5,
    "name": "Rahul Sharma",
    "email": "rahul@gmail.com"
  }
}
```

Retry **`POST /google`** with same `id_token` + `phone`, or use **`PUT /profile`** after obtaining token from a partial flow if your app handles tokens differently.

### Response `422` (invalid token)

```json
{
  "success": false,
  "message": "Invalid Google token."
}
```

`.env`: `GOOGLE_CLIENT_ID` must match token audience.

---

## 8. Current user

**`GET /me`** — Bearer token

### Response `200`

```json
{
  "success": true,
  "user": { }
}
```

---

## 9. Profile (read)

**`GET /profile`** — Bearer token

Identical to **`GET /me`**.

---

## 10. Profile (update)

**`PUT /profile`** or **`PATCH /profile`** — Bearer token

Web `/profile` form ke same fields + optional map.

### Request

```json
{
  "name": "Rahul Sharma",
  "phone": "9876543210",
  "email": "rahul@example.com",
  "address": "Tower A, Sector 62, Noida",
  "latitude": 28.628454,
  "longitude": 77.376945
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "user": { }
}
```

---

## 11. Profile (password)

**`PUT /profile/password`** or **`PATCH /profile/password`** — Bearer token

### Change password (existing password user)

```json
{
  "current_password": "secret12",
  "password": "newpass99",
  "password_confirmation": "newpass99"
}
```

### Set password (Google user, no password yet)

```json
{
  "password": "newpass99",
  "password_confirmation": "newpass99"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Password updated successfully.",
  "user": { }
}
```

### Response `422` (wrong current password)

```json
{
  "success": false,
  "message": "Current password is incorrect.",
  "errors": {
    "current_password": ["Current password is incorrect."]
  }
}
```

---

## 12. Complete profile (legacy)

**`POST /complete-profile`** — Bearer token

Use **`PUT /profile`** for new integrations. This endpoint focuses on adding **phone** after Google.

### Request

```json
{
  "phone": "9876543210",
  "name": "Rahul Sharma",
  "address": "Noida",
  "latitude": 28.62,
  "longitude": 77.37
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Profile completed.",
  "user": { }
}
```

---

## 13. Device / FCM update

**`POST /device`** — Bearer token

### Request

```json
{
  "fcm_token": "updated-firebase-token",
  "device_type": "android",
  "device_id": "abc-uuid",
  "app_version": "1.2.0",
  "os_version": "14"
}
```

### Response `200`

```json
{
  "success": true,
  "message": "Device info updated.",
  "user": { }
}
```

---

## 14. Logout

**`POST /logout`** — Bearer token

### Response `200`

```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

---

## cURL examples

Replace `BASE` and `TOKEN`.

```bash
# Login
curl -X POST "BASE/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"9876543210\",\"password\":\"secret12\"}"

# Profile update
curl -X PUT "BASE/profile" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d "{\"name\":\"Rahul\",\"phone\":\"9876543210\",\"email\":\"rahul@example.com\",\"address\":\"Noida\"}"
```

---

## Web parity

| Web route | API equivalent |
|-----------|----------------|
| `POST /register` | `POST /register` |
| `POST /login` | `POST /login` |
| OTP login | `POST /otp/send` + `POST /otp/verify` |
| Forgot password | `POST /forgot-password/send` + `POST /forgot-password/reset` |
| Google | `POST /google` |
| `GET /profile` | `GET /profile` or `GET /me` |
| `PUT /profile` | `PUT /profile` |
| `PUT /profile/password` | `PUT /profile/password` |
| `POST /logout` | `POST /logout` (token revoke) |
