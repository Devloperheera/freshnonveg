# Catalog API — Endpoints (complete)

Base: `{APP_URL}/api/v1`

Field details: [FIELDS.md](./FIELDS.md)

---

## 1. List banners

**`GET /banners`** — Public

Active banners, newest first (`id` desc).

### Response `200`

```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Weekend Offer",
      "image": "http://127.0.0.1:8000/storage/banners/abc.jpg"
    },
    {
      "id": 1,
      "name": "Fresh Meat Home",
      "image": "http://127.0.0.1:8000/storage/banners/hero.png"
    }
  ]
}
```

### cURL

```bash
curl -s -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/banners"
```

---

## 2. List categories

**`GET /categories`** — Public

Active categories, name A–Z. Har category par active `products_count`.

### Response `200`

```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "name": "Chicken",
      "image": "http://127.0.0.1:8000/storage/categories/chicken.png",
      "products_count": 12
    },
    {
      "id": 1,
      "name": "Mutton",
      "image": "http://127.0.0.1:8000/storage/categories/mutton.png",
      "products_count": 8
    }
  ]
}
```

---

## 3. Single category

**`GET /categories/{id}`** — Public

### Response `200`

```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Chicken",
    "image": "http://127.0.0.1:8000/storage/categories/chicken.png",
    "products_count": 12
  }
}
```

### Response `404` (inactive or missing)

```json
{
  "success": false,
  "message": "Category not found."
}
```

---

## 4. List products

**`GET /products`** — Public

### Query parameters

| Param | Example | Description |
|-------|---------|-------------|
| `category` | `3` | Filter by category id |
| `tag` | `2` | Filter by tag id |
| `product` | `15` | Only this product id |
| `q` | `boneless` | Search |
| `page` | `2` | Page number |
| `per_page` | `12` | 1–100, default 24 |

### Example request

```
GET /api/v1/products?category=3&q=leg&page=1&per_page=12
```

### Response `200`

```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "name": "Chicken Leg Piece",
      "is_featured": true,
      "excerpt": "Fresh hygienic cut, ready to cook.",
      "from_price": 189,
      "image": "http://127.0.0.1:8000/storage/products/images/leg.jpg",
      "category": {
        "id": 3,
        "name": "Chicken",
        "image": "http://127.0.0.1:8000/storage/categories/chicken.png"
      },
      "tags": [
        { "id": 1, "name": "Bestseller" }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 12,
    "total": 14
  }
}
```

Sort: `is_featured` desc, then `id` desc (web shop jaisa).

---

## 5. Product detail

**`GET /products/{id}`** — Public

### Response `200`

```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Chicken Leg Piece",
    "is_featured": true,
    "excerpt": "Fresh hygienic cut, ready to cook.",
    "from_price": 189,
    "image": "http://127.0.0.1:8000/storage/products/images/leg.jpg",
    "category": {
      "id": 3,
      "name": "Chicken",
      "image": "http://127.0.0.1:8000/storage/categories/chicken.png"
    },
    "tags": [{ "id": 1, "name": "Bestseller" }],
    "description": "<p>Full description...</p>",
    "video_url": null,
    "banner_id": null,
    "meta_title": "Chicken Leg",
    "meta_description": "Buy fresh chicken leg",
    "meta_keywords": "chicken, leg",
    "images": [
      {
        "id": 21,
        "url": "http://127.0.0.1:8000/storage/products/images/leg.jpg",
        "sort_order": 0
      },
      {
        "id": 22,
        "url": "http://127.0.0.1:8000/storage/products/images/leg-2.jpg",
        "sort_order": 1
      }
    ],
    "weights": [
      {
        "id": 30,
        "weight_label": "500g",
        "price": 189,
        "sort_order": 0
      },
      {
        "id": 31,
        "weight_label": "1 kg",
        "price": 359,
        "sort_order": 1
      }
    ],
    "created_at": "2026-03-20T08:00:00+00:00",
    "updated_at": "2026-03-25T10:30:00+00:00"
  },
  "related": [
    {
      "id": 11,
      "name": "Chicken Breast",
      "is_featured": false,
      "excerpt": "Boneless breast",
      "from_price": 220,
      "image": "http://127.0.0.1:8000/storage/products/images/breast.jpg",
      "category": {
        "id": 3,
        "name": "Chicken",
        "image": "http://127.0.0.1:8000/storage/categories/chicken.png"
      },
      "tags": []
    }
  ]
}
```

### Response `404`

```json
{
  "success": false,
  "message": "Product not found."
}
```

---

## Mobile flow (suggested)

1. **Home:** `GET /banners` + `GET /categories` + `GET /products?per_page=20`
2. **Category screen:** `GET /products?category={id}`
3. **Search:** `GET /products?q={term}`
4. **PDP:** `GET /products/{id}` — weights se price; cart ke liye auth APIs alag (web: login required)

---

## Related docs

- Auth (login / token): [../auth/README.md](../auth/README.md)
- User dashboard: [../user/README.md](../user/README.md)
- Cart → Buy: [../cart/README.md](../cart/README.md)
