# FAQ API — Endpoint (complete)

Base: `{APP_URL}/api/v1/faqs`

Field details: [FIELDS.md](./FIELDS.md)

---

## Get home FAQs

**`GET /faqs`** — Public

Website home `#faq` accordion. Sirf active FAQs. Order: `sort_order`, then `id`.

Koi active FAQ na ho to `items` empty array hota hai. `success` phir bhi `true` rehta hai.

### Response `200`

```json
{
  "success": true,
  "data": {
    "section": {
      "title": "अक्सर पूछे गए <u>सवाल</u> ❓",
      "subtitle": "FAQ"
    },
    "items": [
      {
        "id": 1,
        "question": "क्या सब कुछ fresh होता है?",
        "answer": "हाँ, हम सीधे meat shop से ताज़ा सामान देते हैं, frozen नहीं।",
        "sort_order": 1
      },
      {
        "id": 2,
        "question": "Free delivery कब मिलती है?",
        "answer": "₹999 या उससे ऊपर के order पर delivery free है।",
        "sort_order": 2
      },
      {
        "id": 3,
        "question": "किन शहरों में delivery है?",
        "answer": "Noida, Greater Noida, Greater Noida West, Ghaziabad और Gautam Buddha Nagar।",
        "sort_order": 3
      },
      {
        "id": 4,
        "question": "Order कैसे करें?",
        "answer": "Website से या WhatsApp पर सीधे order करें।",
        "sort_order": 4
      }
    ]
  }
}
```

### cURL

```bash
curl -s -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/faqs"
```

XAMPP:

```bash
curl -s -H "Accept: application/json" \
  "http://localhost/fresh-non-veg/public/api/v1/faqs"
```

Live:

```bash
curl -s -H "Accept: application/json" \
  "https://brandbuildingmedia.in/api/v1/faqs"
```

---

## App mapping

| Website block | JSON path |
|---------------|-----------|
| Heading «अक्सर पूछे गए सवाल» | `data.section.title` |
| Small label «FAQ» | `data.section.subtitle` |
| Accordion rows | `data.items` |
| Question | `data.items[].question` |
| Answer | `data.items[].answer` |

Pehla item website par expanded dikhta hai. Baaki collapsed. App same order rakhe.

---

## Web parity

Same filter as home: `status = 1`, `orderBy sort_order`, `orderBy id`. Inactive FAQ API mein nahi aati. Admin screen: `/FreshNonVegAdmin/faqs`.
