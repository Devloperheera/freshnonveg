# Catalog API — Fields reference

Object shapes for **banners**, **categories**, and **products**. Image fields are always **full URLs** (not storage paths).

---

## Banner object (`data[]` item — `GET /banners`)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Banner id |
| `name` | string | Admin label / display name |
| `image` | string | Banner image URL |

Inactive banners API mein **nahi** aate.

---

## Category object (`GET /categories`, `/categories/{id}`)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Category id |
| `name` | string | Category name |
| `image` | string | Category image URL |
| `products_count` | integer | Active products in category |

---

## Tag object (nested in products)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Tag id |
| `name` | string | Tag label |

Sirf active tags products par attach hote hain (admin se).

---

## Product weight object (detail + weights array)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Weight row id |
| `weight_label` | string | e.g. `500g`, `1 kg` |
| `price` | number | INR, 2 decimal |
| `sort_order` | integer | Display order |

---

## Product image object (detail `images[]`)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Image row id |
| `url` | string | Full image URL |
| `sort_order` | integer | Gallery order |

---

## Product summary (`GET /products`, `related[]`, list cards)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product id |
| `name` | string | Product name |
| `is_featured` | boolean | Featured flag |
| `excerpt` | string \| null | Short text (meta or description) |
| `from_price` | number \| null | Minimum weight price |
| `image` | string \| null | First gallery image URL |
| `category` | object \| null | Nested category (without `products_count`) |
| `tags` | array | Tag objects |

---

## Product detail (`GET /products/{id}` — `data`)

Summary fields **plus**:

| Field | Type | Description |
|-------|------|-------------|
| `description` | string \| null | Full HTML/text description |
| `video_url` | string \| null | Product video URL if uploaded |
| `banner_id` | integer \| null | Linked banner id (admin) |
| `meta_title` | string \| null | SEO |
| `meta_description` | string \| null | SEO |
| `meta_keywords` | string \| null | SEO |
| `images` | array | All product image objects |
| `weights` | array | All weight/price options |
| `created_at` | ISO 8601 \| null | |
| `updated_at` | ISO 8601 \| null | |

**`related`:** array of **product summary** objects (same category, max 4).

---

## Pagination meta (`GET /products`)

| Field | Type | Description |
|-------|------|-------------|
| `current_page` | integer | Current page |
| `last_page` | integer | Total pages |
| `per_page` | integer | Items per page |
| `total` | integer | Total matching products |

---

## HTTP status codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 404 | Inactive or missing category / product |
| 422 | Invalid query (Laravel validation if added later) |

---

## Admin fields (API mein nahi)

Admin panel se manage hota hai; API read-only:

| Resource | Admin fields (DB) |
|----------|-------------------|
| Banner | `name`, `image` (file), `status` |
| Category | `name`, `image` (file), `status` |
| Product | `category_id`, `banner_id`, `name`, `description`, `video`, images, weights, tags, `is_featured`, `status`, meta fields |

Upload paths (storage): `banners/`, `categories/`, `products/images/`, `products/videos/`.
