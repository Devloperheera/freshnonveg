# Home Content API — Endpoint (complete)

Base: `{APP_URL}/api/v1/home-content`

Field details: [FIELDS.md](./FIELDS.md)

---

## Get all home sections

**`GET /home-content`** — Public

Ek call mein: testimonials, site stats, order way (heading + steps), delivery (heading + locations), advantages («Fresh खाने के फायदे!» heading + items).

### Response `200`

```json
{
  "success": true,
  "data": {
    "testimonials": [
      {
        "id": 1,
        "name": "Rahul, Noida",
        "star_rating": 5,
        "short_description": "Mutton एकदम fresh था, बाजार से बेहतर!"
      },
      {
        "id": 2,
        "name": "Priya, Greater Noida",
        "star_rating": 5,
        "short_description": "Prawns jumbo size ke the, delivery bhi fast!"
      }
    ],
    "site_stats": [
      {
        "id": 1,
        "value": 5000,
        "label": "Happy Customers 😍",
        "sort_order": 1
      },
      {
        "id": 2,
        "value": 25,
        "label": "Items Fresh Daily 🥩",
        "sort_order": 2
      },
      {
        "id": 3,
        "value": 5,
        "label": "Cities Covered 📍",
        "sort_order": 3
      },
      {
        "id": 4,
        "value": 100,
        "label": "100% Fresh, 0% Frozen ❄️",
        "sort_order": 4
      }
    ],
    "order_way": {
      "section": {
        "title": "Order करना है <u>आसान</u>",
        "subtitle": "बस 4 steps में ताज़ा गोश्त आपके दरवाज़े पर 🚪"
      },
      "steps": [
        {
          "id": 1,
          "title": "चुनो 🛍️",
          "description": "अपना पसंदीदा item चुनें",
          "sort_order": 1
        },
        {
          "id": 2,
          "title": "कटवाओ 🔪",
          "description": "हमारे कसाई ताज़ा काटते हैं",
          "sort_order": 2
        },
        {
          "id": 3,
          "title": "पैक करो 📦",
          "description": "Hygienic पैकिंग",
          "sort_order": 3
        },
        {
          "id": 4,
          "title": "खाओ 😋",
          "description": "घर पर फटाफट delivery",
          "sort_order": 4
        }
      ]
    },
    "delivery": {
      "section": {
        "title": "हम <u>Deliver</u> करते हैं 🛵",
        "subtitle": "आपके शहर में भी!"
      },
      "locations": [
        { "id": 1, "name": "📍 Noida", "sort_order": 1 },
        { "id": 2, "name": "📍 Greater Noida", "sort_order": 2 },
        { "id": 3, "name": "📍 Greater Noida West", "sort_order": 3 },
        { "id": 4, "name": "📍 Ghaziabad", "sort_order": 4 },
        { "id": 5, "name": "📍 Gautam Buddha Nagar", "sort_order": 5 }
      ]
    },
    "advantages": {
      "section": {
        "title": "Fresh खाने के <u>फायदे!</u>",
        "subtitle": "ताज़ा गोश्त क्यों है बेहतर? 🤔"
      },
      "items": [
        {
          "id": 1,
          "icon": "💪",
          "title": "ज्यादा प्रोटीन",
          "description": "Fresh meat में protein और nutrients ज्यादा होते हैं",
          "sort_order": 1
        },
        {
          "id": 2,
          "icon": "😋",
          "title": "बेहतर स्वाद",
          "description": "Frozen नहीं, इसलिए taste असली और लाजवाब!",
          "sort_order": 2
        },
        {
          "id": 3,
          "icon": "🩺",
          "title": "सेहत के लिए अच्छा",
          "description": "कोई chemicals नहीं, बस pure freshness!",
          "sort_order": 3
        },
        {
          "id": 4,
          "icon": "⚡",
          "title": "तुरंत एनर्जी",
          "description": "Fresh nutrients से instant energy मिलती है",
          "sort_order": 4
        }
      ]
    }
  }
}
```

### cURL

```bash
curl -s -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/home-content"
```

---

## App mapping (website sections)

| Website block | JSON path |
|---------------|-----------|
| Reviews carousel | `data.testimonials` |
| Counter / stats strip | `data.site_stats` |
| How to order | `data.order_way.section` + `data.order_way.steps` |
| Delivery areas | `data.delivery.section` + `data.delivery.locations` |
| Fresh advantages | `data.advantages.section` + `data.advantages.items` |
| Home FAQ accordion | Alag endpoint: `GET /api/v1/faqs` — [FAQ docs](../faq/README.md) |

---

## Web parity

Same queries as `App\Http\Controllers\Website\HomeController` (testimonials through delivery/advantages). Banners/categories/products ke liye catalog endpoints use karein.
