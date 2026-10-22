# Policy / Pages API — Fields reference

---

## Page summary (`GET /pages` — har `data[]` item)

| Field | Type | Description |
|-------|------|-------------|
| `slug` | string | URL slug (see README table) |
| `page_name` | string | Canonical admin page name |
| `title` | string | Content title (display heading) |
| `meta_title` | string \| null | SEO title |
| `meta_description` | string \| null | SEO description |
| `updated_at` | ISO 8601 \| null | Last content update |

List mein **`content` HTML nahi** hota — detail endpoint use karein.

---

## Page detail (`GET /pages/{slug}` — `data`)

Summary fields **plus**:

| Field | Type | Description |
|-------|------|-------------|
| `content` | string | HTML body (admin editor se) |
| `page_id` | integer | Linked `pages.id` |
| `content_id` | integer | `page_contents.id` (latest active row) |

App WebView ya HTML renderer se `content` dikhayein.

---

## Slug rules

- Allowed slugs fixed hain (`SitePage::SLUGS`).
- Galat slug → `404`, `message`: `Invalid page slug.`
- Page inactive / koi active content nahi → `404`, `message`: `Page not found or not published.`

---

## Admin fields (API mein nahi)

**Page (`pages`)**

| Field | Notes |
|-------|--------|
| `name` | Must match canonical name (e.g. `Privacy Policy`) |
| `status` | `1` = active |

**Page content (`page_contents`)**

| Field | Notes |
|-------|--------|
| `page_id` | Which page |
| `title` | Required |
| `content` | HTML |
| `meta_title` | Optional |
| `meta_description` | Optional |
| `status` | `1` = published |

Ek page par multiple content rows ho sakti hain; API **latest active** (`id` desc) wali leti hai.

---

## HTTP status codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 404 | Unknown slug or not published |
