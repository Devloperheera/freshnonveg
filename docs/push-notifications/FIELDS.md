# Push notifications & delete account — Fields reference

---

## Common device fields (push register)

`POST /auth/device` aur login/register flows par optional. Full list: [Auth FIELDS — device](../auth/FIELDS.md#common-device--app-fields-optional).

| Field | Required | Type | Description |
|-------|----------|------|-------------|
| `fcm_token` | Recommended | string | Firebase Cloud Messaging device token |
| `device_type` | No | string, max 30 | `android` \| `ios` |
| `device_id` | No | string, max 255 | Stable device id |
| `device_name` | No | string, max 255 | Sanctum token label (default `mobile-app`) |
| `device_brand` | No | string, max 80 | |
| `device_model` | No | string, max 80 | |
| `os_version` | No | string, max 40 | |
| `app_version` | No | string, max 30 | |
| `app_build_number` | No | string, max 30 | |
| `timezone` | No | string, max 64 | e.g. `Asia/Kolkata` |
| `locale` | No | string, max 20 | e.g. `en_IN` |

**Server effect:** `fcm_token` update par `fcm_token_updated_at` set hota hai; app login par `is_app_installed`, `app_installed_at` update ho sakta hai.

---

## User fields (push eligibility — read-only, DB)

App inhe directly set nahi karti (admin/user record). Push tabhi jata hai jab:

| Field | Type | Rule |
|-------|------|------|
| `fcm_token` | string \| null | Non-empty |
| `notifications_enabled` | boolean | `true` (default on register) |
| `status` | integer | Active user (`1`) |
| Group membership | — | User admin **Notification Group** mein ho |

---

## FCM message (app receives)

Server `FcmPushService` se bhejta hai (HTTP v1). App ko handle karna hai:

### `notification` (display)

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Campaign title |
| `body` | string | Campaign message text |
| `image` | string \| omitted | Full image URL (optional rich notification) |

### `data` (custom — sab string values)

| Key | Type | Description |
|-----|------|-------------|
| `title` | string | Same as notification title |
| `body` | string | Same as message body |
| `click_action` | string \| omitted | Deep link / route app ko open karna (admin se) |

### Platform extras (optional)

| Platform | Field | Description |
|----------|-------|-------------|
| Android | `android.notification.image` | Big picture URL |
| iOS (APNS) | `apns.fcm_options.image` | Rich notification image |

**`click_action` examples:** `freshnonveg://orders`, `/dashboard/orders`, ya full HTTPS URL — app convention document karein.

---

## Admin campaign record (reference — not app API)

Admin `POST FreshNonVegAdmin/notifications` se create. App list API nahi hai; sirf FCM se deliver.

| Field | Required | Rules | Description |
|-------|----------|-------|-------------|
| `notification_group_id` | Yes | integer, exists | Target group |
| `title` | Yes | string, max 200 | Push title |
| `message` | Yes | string, max 5000 | Push body |
| `image` | No | jpeg/jpg/png, max 5120 KB | Stored under `storage/…`; FCM par full URL |
| `click_action` | No | string, max 500 | Passed in FCM `data` |
| `schedule_later` | No | boolean | Schedule flag |
| `scheduled_at` | If scheduled | date, after now | Cron `push-notifications:send-due` |

### Push notification row (DB / admin UI)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | |
| `notification_group_id` | integer | FK group |
| `title` | string | |
| `message` | string | |
| `image` | string \| null | Relative storage path |
| `click_action` | string \| null | |
| `status` | string | `pending` \| `sent` \| `failed` |
| `recipients_count` | integer | Tokens attempted |
| `delivered_count` | integer | Successful FCM sends |
| `scheduled_at` | datetime \| null | |
| `sent_at` | datetime \| null | |
| `error_summary` | string \| null | Fail reason |

---

## Delete account — `DELETE /auth/account`

Bearer token required. Throttle: `login`.

| Field | Required | Rules | Notes |
|-------|----------|-------|-------|
| `confirm` | Yes | accepted | JSON: `true`, `1`, `"1"`, `"yes"`, `"on"` |
| `confirm_phrase` | Yes | string, exact `DELETE MY ACCOUNT` | Case-sensitive |
| `current_password` | If user has password | string | Google-only / no password → omit |

### Success response `200`

| Field | Type |
|-------|------|
| `success` | boolean `true` |
| `message` | string |

Token revoke ho jata hai; dubara login nahi hoga (account anonymized).

### Error `422` — validation

| Field | Example message |
|-------|-------------------|
| `confirm` | confirm required |
| `confirm_phrase` | exact phrase |
| `current_password` | wrong password |
| `account` | wallet balance \| open complaint |

### After delete (server)

User row anonymize: `name` → `Deleted User`, `email` null, `phone` → `deleted_{id}`, `fcm_token` cleared, `status` → `0`, cart/wishlist/tokens deleted.

---

## Delete account — Web (parity)

| Method | Path | Auth |
|--------|------|------|
| GET | `/delete` | Web session (form) |
| DELETE | `/delete` | Web session |

Form fields same as API: `confirm`, `confirm_phrase`, `current_password` (if password set).

---

## HTTP status codes

| Code | Meaning |
|------|---------|
| 200 | Device updated / account deleted |
| 401 | Missing or invalid Bearer token |
| 422 | Validation / cannot delete (wallet, complaint) |
| 429 | Throttle (delete account) |
