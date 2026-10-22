# FreshNonVeg — Policy & Legal Pages API

Terms, Privacy, Refund, Contact aur Blog — admin **Pages** + **Page Content** se manage hote hain. Mobile app ke liye published HTML content JSON mein milta hai. **Auth not required.**

Web equivalent: `{APP_URL}/page/{slug}`

## Base URL

```
{APP_URL}/api/v1/pages
```

**Examples**

| Environment | Example |
|-------------|---------|
| XAMPP | `http://localhost/fresh-non-veg/public/api/v1/pages` |
| `php artisan serve` | `http://127.0.0.1:8000/api/v1/pages` |

## Headers

| Header | Value |
|--------|--------|
| `Accept` | `application/json` |

## Supported slugs

| Slug | Page name (admin) |
|------|-------------------|
| `terms-conditions` | Terms & Conditions |
| `privacy-policy` | Privacy Policy |
| `refund-policy` | Refund Policy |
| `contact` | Contact |
| `blog` | Blog |

Sirf **active** page + **active** latest page content return hota hai (web jaisa `SitePage::findPublishedContent`).

## Response shape

**List**

```json
{
  "success": true,
  "data": [ ]
}
```

**Detail**

```json
{
  "success": true,
  "data": { }
}
```

**Error (404)**

```json
{
  "success": false,
  "message": "Page not found or not published."
}
```

## Documentation files

| File | Contents |
|------|----------|
| [API.md](./API.md) | Endpoints + examples |
| [FIELDS.md](./FIELDS.md) | JSON fields |

## Quick endpoint list

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/pages` | No | Sab published pages (summary) |
| GET | `/pages/{slug}` | No | Full HTML content + meta |

## Admin

| Admin route | Purpose |
|-------------|---------|
| `/FreshNonVegAdmin/pages` | Page record (name, status) |
| `/FreshNonVegAdmin/page-contents` | Title, HTML body, SEO meta |

Default content: `php artisan db:seed --class=LegalPagesSeeder`

## Related docs

- [Catalog API](../catalog/README.md)
- [Auth API](../auth/README.md)
- [User dashboard](../user/README.md)
- [Cart → Buy](../cart/README.md)
