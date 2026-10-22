# Home Content API — Fields reference

Root object: `data` (inside success response).

---

## `testimonials[]`

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Row id |
| `name` | string | Customer name / location label |
| `star_rating` | integer | 1–5 |
| `short_description` | string | Review text |

Order: newest first (`id` desc).

---

## `site_stats[]`

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Row id |
| `value` | integer | Number shown (e.g. 5000) |
| `label` | string | Caption (emoji allowed) |
| `sort_order` | integer | Display order |

Order: `sort_order`, then `id`.

---

## `order_way`

### `order_way.section` (nullable)

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Section heading (HTML allowed) |
| `subtitle` | string \| null | Subheading |

Admin key: `home_sections.section_key` = `order_way`.

### `order_way.steps[]`

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Step id |
| `title` | string | Step title (e.g. चुनो 🛍️) |
| `description` | string | Short text |
| `sort_order` | integer | 1, 2, 3, 4… |

---

## `delivery`

### `delivery.section` (nullable)

Same shape as `order_way.section`. Admin key: `delivery`.

### `delivery.locations[]`

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Location id |
| `name` | string | Area / city label |
| `sort_order` | integer | List order |

---

## `advantages` («Fresh खाने के फायदे!»)

### `advantages.section` (nullable)

Same heading shape. Admin key: `advantages`.

Default title example: `Fresh खाने के <u>फायदे!</u>`

### `advantages.items[]`

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Item id |
| `icon` | string | Emoji or short icon text |
| `title` | string | Card title |
| `description` | string | Card body |
| `sort_order` | integer | Display order |

---

## Section heading null kab?

Agar admin ne `home_sections` row delete kar di ya DB empty hai → `section` = `null`, lekin `steps` / `locations` / `items` phir bhi active rows se aa sakte hain.

---

## Admin-only fields (API mein nahi)

Har table par `status`: `1` = show on site/API, `0` = hidden.

| Table | Main columns |
|-------|----------------|
| `testimonials` | name, star_rating, short_description, status |
| `site_stats` | value, label, sort_order, status |
| `order_steps` | title, description, sort_order, status |
| `delivery_locations` | name, sort_order, status |
| `advantage_items` | icon, title, description, sort_order, status |
| `home_sections` | section_key (`order_way`, `delivery`, `advantages`), title, subtitle |

---

## HTTP status

| Code | Meaning |
|------|---------|
| 200 | OK (empty arrays allowed) |
