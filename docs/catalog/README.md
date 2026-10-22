# FreshNonVeg — Catalog API (Banners, Categories, Products)

Mobile / app ke liye public catalog REST API. Sirf **active** (`status = 1`) banners, categories aur products return hote hain. Auth token **not required**.

## Base URL

```
{APP_URL}/api/v1
```

**Examples**

| Environment | Example |
|-------------|---------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1` |

`.env` mein `APP_URL` wahi hona chahiye jo app use karti hai.

## Headers

| Header | Value | When |
|--------|--------|------|
| `Accept` | `application/json` | Always |

## Images & media URLs

Sab image / video URLs **`APP_STORAGE_URL`** (ya `APP_URL` + `/storage`) se resolve hote hain — DB mein purana galat host ho to bhi sahi URL milega. Details: `app/Support/StorageUrl.php`.

## Response shape

**List / single resource (typical)**

```json
{
  "success": true,
  "data": [ ]
}
```

**Products list (paginated)**

```json
{
  "success": true,
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 24,
    "total": 52
  }
}
```

**Product detail**

```json
{
  "success": true,
  "data": { },
  "related": [ ]
}
```

**Not found (404)**

```json
{
  "success": false,
  "message": "Product not found."
}
```

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Saare endpoints, query params, examples |
| [FIELDS.md](./FIELDS.md) | Banner, category, product objects |

## Quick endpoint list

| Module | Method | Path | Auth | Description |
|--------|--------|------|------|-------------|
| **Banners** | GET | `/banners` | No | Home slider — active banners |
| **Categories** | GET | `/categories` | No | Active categories + product count |
| **Categories** | GET | `/categories/{id}` | No | Single category |
| **Products** | GET | `/products` | No | Filter / search / paginate |
| **Products** | GET | `/products/{id}` | No | Full detail + related products |

## Product list filters (`GET /products`)

Web shop jaisa logic (`ShopController::catalogQuery`):

| Query | Type | Description |
|-------|------|-------------|
| `category` | integer | `category_id` filter |
| `tag` | integer | Tag id |
| `product` | integer | Single product id (narrow list) |
| `q` | string | Name, meta keywords, category name search |
| `page` | integer | Pagination page (default 1) |
| `per_page` | integer | 1–100 (default 24) |

## Admin vs API

| | Admin (`/FreshNonVegAdmin/...`) | Catalog API |
|--|----------------------------------|-------------|
| Auth | Admin session | Public |
| Banners / categories / products | CRUD | Read-only, active only |
| Images | Upload multipart | Full URL in JSON |
