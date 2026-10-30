# Push notifications & delete account — API

Base: `{APP_URL}/api/v1/auth`

Field details: [FIELDS.md](./FIELDS.md)

---

## 1. Register FCM token (device update)

**`POST /device`** — Bearer token

### Request

```json
{
  "fcm_token": "dK3x...:APA91b...",
  "device_type": "android",
  "device_id": "a1b2c3d4-e5f6-7890",
  "device_brand": "Samsung",
  "device_model": "SM-G991B",
  "os_version": "14",
  "app_version": "1.0.0",
  "app_build_number": "10",
  "timezone": "Asia/Kolkata",
  "locale": "en_IN"
}
```

Sab fields optional; push ke liye **`fcm_token`** zaroor bhejein.

### Response `200`

```json
{
  "success": true,
  "message": "Device info updated.",
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
    "login_count": 5,
    "last_login_at": "2026-10-26T10:00:00.000000Z",
    "web_last_login_at": null,
    "app_installed_at": "2026-03-28T10:00:00.000000Z",
    "is_app_installed": true,
    "created_at": "2026-03-28T10:00:00.000000Z"
  }
}
```

`fcm_token` user object mein **return nahi** hota (security).

---

## 2. FCM token on login / register (alternative)

Same **`fcm_token`** + device fields body mein bhej sakte ho:

| Endpoint | When |
|----------|------|
| `POST /auth/register` | New user |
| `POST /auth/login` | Password login |
| `POST /auth/otp/verify` | OTP login |
| `POST /auth/google` | Google Sign-In |

Baad mein token refresh ho to **`POST /device`** dubara call karein.

---

## 3. Incoming push (app side — not HTTP)

Server admin campaign fire karta hai. App Firebase SDK se receive kare.

### Example payload shape

```json
{
  "notification": {
    "title": "Fresh offer today",
    "body": "Chicken 20% off — order now",
    "image": "https://example.com/storage/push-images/abc.jpg"
  },
  "data": {
    "title": "Fresh offer today",
    "body": "Chicken 20% off — order now",
    "click_action": "freshnonveg://shop"
  }
}
```

Foreground / background handlers mein `data.click_action` se screen navigate karein.

---

## 4. Delete account

**`DELETE /account`** — Bearer token

### Request

```json
{
  "confirm": true,
  "confirm_phrase": "DELETE MY ACCOUNT",
  "current_password": "yourPassword123"
}
```

Google-only account (password null) → **`current_password` omit**.

### Response `200`

```json
{
  "success": true,
  "message": "Your account has been deleted."
}
```

Current API token invalid — local storage se token hata dein.

### Response `422` — business rules

```json
{
  "message": "Wallet mein balance hai...",
  "errors": {
    "account": ["Wallet mein balance hai. Pehle order mein use karein ya support se contact karein, phir account delete karein."]
  }
}
```

Open complaint:

```json
{
  "errors": {
    "account": ["Open complaint pending hai. Pehle complaint resolve hone ka wait karein."]
  }
}
```

---

## cURL examples

```bash
BASE="http://127.0.0.1:8000/api/v1/auth"
TOKEN="1|yourSanctumToken"

# Update FCM token
curl -X POST "$BASE/device" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"fcm_token":"FIREBASE_TOKEN_HERE","device_type":"android"}'

# Delete account
curl -X DELETE "$BASE/account" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"confirm":true,"confirm_phrase":"DELETE MY ACCOUNT","current_password":"secret12"}'
```

---

## Web parity

| Web | API |
|-----|-----|
| — | `POST /device` |
| `GET /delete` + form `DELETE /delete` | `DELETE /account` |

Auth overview: [../auth/API.md](../auth/API.md)
