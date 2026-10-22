# Policy / Pages API — Endpoints (complete)

Base: `{APP_URL}/api/v1/pages`

Field details: [FIELDS.md](./FIELDS.md)

---

## 1. List published pages

**`GET /pages`** — Public

Har supported slug jiska published content hai — summary only (no HTML body).

### Response `200`

```json
{
  "success": true,
  "data": [
    {
      "slug": "privacy-policy",
      "page_name": "Privacy Policy",
      "title": "Privacy Policy",
      "meta_title": "Privacy Policy | FreshNonVeg",
      "meta_description": "We respect your privacy...",
      "updated_at": "2026-03-28T12:00:00+00:00"
    },
    {
      "slug": "terms-conditions",
      "page_name": "Terms & Conditions",
      "title": "Terms & Conditions",
      "meta_title": "Terms & Conditions | FreshNonVeg",
      "meta_description": "Welcome to Fresh Non Veg...",
      "updated_at": "2026-03-28T12:00:00+00:00"
    }
  ]
}
```

### cURL

```bash
curl -s -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/pages"
```

---

## 2. Page detail (policy / legal HTML)

**`GET /pages/{slug}`** — Public

### Valid slugs

`terms-conditions` | `privacy-policy` | `refund-policy` | `contact` | `blog`

### Example — Privacy Policy

**`GET /pages/privacy-policy`**

### Response `200`

```json
{
  "success": true,
  "data": {
    "slug": "privacy-policy",
    "page_name": "Privacy Policy",
    "title": "Privacy Policy",
    "meta_title": "Privacy Policy | FreshNonVeg",
    "meta_description": "We respect your privacy. Personal information collected during registration or checkout is used only to process orders and improve our service.",
    "updated_at": "2026-03-28T12:00:00+00:00",
    "content": "<p>We respect your privacy. Personal information collected during registration or checkout is used only to process orders and improve our service.</p><p>We do not sell your data to third parties.</p>",
    "page_id": 2,
    "content_id": 5
  }
}
```

### Example — Refund Policy

**`GET /pages/refund-policy`**

Same shape; `content` mein refund rules ka HTML.

### Response `404` — invalid slug

**`GET /pages/unknown-page`**

```json
{
  "success": false,
  "message": "Invalid page slug."
}
```

### Response `404` — not published

```json
{
  "success": false,
  "message": "Page not found or not published."
}
```

---

## Mobile usage

| Screen | API |
|--------|-----|
| Settings → Policies list | `GET /pages` — filter client-side: `privacy-policy`, `terms-conditions`, `refund-policy` |
| Policy detail WebView | `GET /pages/{slug}` — render `data.content` |
| Contact | `GET /pages/contact` |
| Blog placeholder | `GET /pages/blog` |

Register / checkout par links:

- Terms: `/pages/terms-conditions`
- Privacy: `/pages/privacy-policy`

---

## Web parity

| Web | API |
|-----|-----|
| `GET /page/privacy-policy` | `GET /api/v1/pages/privacy-policy` |
| `GET /legal/{slug}` (301) | Same slugs |
