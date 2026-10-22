# FreshNonVeg — FAQ API

Website **home page** par jo accordion hai — «अक्सर पूछे गए सवाल» — wahi questions / answers app ko is API se milte hain.

Admin **FAQs** se manage hota hai. Sirf **active** (`status = 1`) rows aati hain, `sort_order` phir `id` ke order mein — web home jaisa.

Section heading website par fixed hai (admin form se nahi badalti). Questions aur answers dynamic hain.

## Base URL

```
{APP_URL}/api/v1/faqs
```

**Examples**

| Environment | URL |
|-------------|-----|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1/faqs` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/faqs` |
| Live | `https://brandbuildingmedia.in/api/v1/faqs` |

## Headers

| Header | Value |
|--------|--------|
| `Accept` | `application/json` |

Auth **not required**.

## Response shape

```json
{
  "success": true,
  "data": {
    "section": {
      "title": "अक्सर पूछे गए <u>सवाल</u> ❓",
      "subtitle": "FAQ"
    },
    "items": []
  }
}
```

`section.title` mein HTML ho sakta hai (`<u>`) — app WebView / HTML renderer use kare. `question` aur `answer` plain text hain.

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Endpoint + sample JSON + cURL |
| [FIELDS.md](./FIELDS.md) | Har field |

## Quick endpoint

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/faqs` | No | Home FAQ accordion (active only) |

## Admin (CRUD)

| Admin menu | Model |
|------------|--------|
| FAQs | `faqs` |

Default seed:

```bash
php artisan db:seed --class=FaqSeeder
```

## Mobile home screen (suggested)

1. `GET /api/v1/banners`
2. `GET /api/v1/categories`
3. `GET /api/v1/products?per_page=20`
4. `GET /api/v1/home-content`
5. **`GET /api/v1/faqs`** — FAQ accordion

## Related

- [Home content](../home-content/README.md) · [Catalog](../catalog/README.md) · [Policy](../policy/README.md) · [Auth](../auth/README.md) · [User](../user/README.md) · [Cart → Buy](../cart/README.md)
