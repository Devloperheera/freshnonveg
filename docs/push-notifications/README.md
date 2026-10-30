# FreshNonVeg — Push notifications & delete account (App)

Mobile app ke liye **Firebase push** register karna aur **account delete** API.

Admin se campaigns `FreshNonVegAdmin → Notification / Group` se bheje jate hain; app ko sirf **FCM token** bhejna aur push **payload** handle karna hota hai.

## Base URL (auth)

```
{APP_URL}/api/v1/auth
```

| Environment | Example |
|-------------|---------|
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/auth` |
| Live | `https://brandbuildingmedia.in/api/v1/auth` |

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Endpoints, FCM payload, cURL |
| [FIELDS.md](./FIELDS.md) | Har request/response field |

## Quick endpoints (app)

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/device` | Bearer | FCM token + device meta update |
| POST | `/login`, `/register`, `/otp/verify`, `/google` | — / Bearer | Optional `fcm_token` on same request |
| DELETE | `/account` | Bearer | Permanent account delete |

Device fields detail: [Auth FIELDS](../auth/FIELDS.md#common-device--app-fields-optional).

## Push flow (summary)

1. App Firebase se **FCM token** le.
2. Login / register / OTP verify / Google ke saath **`fcm_token`** bhejo, ya baad me **`POST /device`**.
3. User par **`notifications_enabled = true`** (default) aur valid **`fcm_token`** ho to admin group push deliver hota hai.
4. App **FCM notification + data** parse kare (`click_action` deep link).

## Delete account (summary)

- App: **`DELETE /api/v1/auth/account`** (JSON body — [FIELDS.md](./FIELDS.md)).
- Web: **`GET /delete`** + **`DELETE /delete`** (logged-in session).

Dono same business rules: wallet balance 0, open complaint nahi.

## Server setup (push)

- File: `storage/app/firebase/firebase-service-account.json`
- Config: `config/firebase.php`

## Related

- [Auth API](../auth/README.md) · [User dashboard](../user/README.md)
