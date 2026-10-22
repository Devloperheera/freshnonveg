# FAQ API — Fields reference

Root object: `data` (inside success response).

---

## `section`

Website home FAQ heading. Admin FAQ form se change nahi hoti.

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Heading. HTML allowed (`<u>`). Example: `अक्सर पूछे गए <u>सवाल</u> ❓` |
| `subtitle` | string | Small label under the heading. Example: `FAQ` |

---

## `items[]`

Ek accordion row. Sirf active FAQs.

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | FAQ id |
| `question` | string | Accordion title. Plain text. Max 500 chars in admin |
| `answer` | string | Accordion body. Plain text (line breaks ho sakte hain). Max 5000 chars in admin |
| `sort_order` | integer | Display order. Chhota number pehle |

Order: `sort_order` ascending, then `id` ascending.

`status` response mein nahi aata — inactive rows skip ho chuki hoti hain.

---

## Empty state

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

`items` khali ho to FAQ section hide kar sakte ho, jaise website karti hai.
