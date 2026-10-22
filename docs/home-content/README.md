# FreshNonVeg — Home Content API

Website home par jo **dynamic sections** admin se aate hain — un sab ka ek hi API response:

| Section | Hindi / site label (example) |
|---------|------------------------------|
| **Testimonials** | Customer reviews |
| **Site stats** | Happy customers, cities, etc. |
| **Order way** | «Order करना है आसान» — 4 steps |
| **Delivery** | «हम Deliver करते हैं» — location list |
| **Advantages** | «Fresh खाने के फायदे!» — icon cards |

Banners, categories, products **alag** [Catalog API](../catalog/README.md) se aate hain.

## Base URL

```
{APP_URL}/api/v1/home-content
```

**Examples**

| Environment | URL |
|-------------|-----|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1/home-content` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/home-content` |

## Headers

| Header | Value |
|--------|--------|
| `Accept` | `application/json` |

Auth **not required**. Sirf **active** (`status = 1`) rows return hoti hain — web `HomeController` jaisa.

## Response shape

```json
{
  "success": true,
  "data": {
    "testimonials": [],
    "site_stats": [],
    "order_way": { "section": {}, "steps": [] },
    "delivery": { "section": {}, "locations": [] },
    "advantages": { "section": {}, "items": [] }
  }
}
```

Section headings (`title`, `subtitle`) mein HTML ho sakta hai (jaise `<u>highlight</u>`) — app WebView / HTML renderer use kare.

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Full endpoint + sample JSON |
| [FIELDS.md](./FIELDS.md) | Har object ke fields |

## Quick endpoint

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/home-content` | No | Saari home sections ek saath |

## Admin (CRUD)

| Admin menu | Model |
|------------|--------|
| Testimonials | `testimonials` |
| Site Stats | `site_stats` |
| Order Steps + section heading | `order_steps`, `home_sections` (`order_way`) |
| Delivery Locations + heading | `delivery_locations`, `home_sections` (`delivery`) |
| Advantage Items + heading | `advantage_items`, `home_sections` (`advantages`) |

Default seed:

```bash
php artisan db:seed --class=HomePageContentSeeder
php artisan db:seed --class=TestimonialSeeder
```

## Mobile home screen (suggested)

1. `GET /api/v1/banners`
2. `GET /api/v1/categories`
3. `GET /api/v1/products?per_page=20`
4. **`GET /api/v1/home-content`** — baaki sab sections
5. **`GET /api/v1/faqs`** — home FAQ accordion. Details: [FAQ](../faq/README.md)

## Related

- [FAQ](../faq/README.md) · [Catalog](../catalog/README.md) · [Policy](../policy/README.md) · [Auth](../auth/README.md) · [User](../user/README.md) · [Cart → Buy](../cart/README.md)
