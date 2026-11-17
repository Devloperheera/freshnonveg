# Gateways — Cashfree & Razorpay (App integration)

API flow: [API.md](./API.md) · Overview: [README.md](./README.md)

App me **dono SDK** lagao. `POST /checkout` ke `data.payment.gateway` se decide karo kaunsa kholna hai.

```dart
final payment = res['data']['payment'];
final order   = res['data']['order'];

switch (payment['gateway']) {
  case 'razorpay': openRazorpay(payment, order); break;
  case 'cashfree': openCashfree(payment, order); break;
}
```

> Neeche examples **Flutter** me hain. Native Android / iOS / React Native me bhi same values use hoti hain —
> sirf SDK ka syntax alag hai. SDK version ke hisaab se official docs se method names match kar lena.

**App me kabhi bhi secret key mat rakhna.** App ko sirf server se aaya `key_id` (Razorpay) ya
`payment_session_id` (Cashfree) chahiye. Secret sirf admin panel → server me rehta hai.

---

## Razorpay

### Package

```yaml
# pubspec.yaml
dependencies:
  razorpay_flutter: ^1.3.7
```

Android: `minSdkVersion 19+`. iOS: `pod install`.

### Server se kya milta hai

| Field | Use |
|-------|-----|
| `payment.checkout_options` | **Seedha** `razorpay.open()` me do |
| `payment.key_id` | Public key (`rzp_test_` / `rzp_live_`) |
| `payment.razorpay_order_id` | Razorpay order id (`order_xxx`) |
| `payment.amount_paise` | Amount paise me (₹440 = `44000`) |
| `order.order_number` | Verify ke liye |

### Code

```dart
import 'package:razorpay_flutter/razorpay_flutter.dart';

late Razorpay _razorpay;
String? _orderNumber;

void openRazorpay(Map payment, Map order) {
  _orderNumber = order['order_number'];
  _razorpay = Razorpay();

  _razorpay.on(Razorpay.EVENT_PAYMENT_SUCCESS, (PaymentSuccessResponse r) {
    verifyPayment(_orderNumber!, extra: {
      'razorpay_payment_id': r.paymentId,
      'razorpay_order_id': r.orderId,
      'razorpay_signature': r.signature,
    });
  });

  _razorpay.on(Razorpay.EVENT_PAYMENT_ERROR, (PaymentFailureResponse r) {
    // user ne cancel kiya ya payment fail — tab bhi verify call karo
    verifyPayment(_orderNumber!);
  });

  _razorpay.on(Razorpay.EVENT_EXTERNAL_WALLET, (ExternalWalletResponse r) {});

  _razorpay.open(Map<String, dynamic>.from(payment['checkout_options']));
}

@override
void dispose() {
  _razorpay.clear();
  super.dispose();
}
```

### Test (Sandbox — `rzp_test_` key)

| Method | Value |
|--------|-------|
| Card | `4111 1111 1111 1111`, koi bhi future expiry, koi bhi CVV |
| UPI success | `success@razorpay` |
| UPI fail | `failure@razorpay` |
| Netbanking | koi bhi bank → "Success" dabao |

Test mode me paisa nahi katta. Live ke liye admin `rzp_live_` key lagayega — app me koi change nahi.

---

## Cashfree

### Android APK / debug build — `installer_package_not_approved`

Agar error aaye: *"NOT_AVAILABLE is not a trusted source … installer_package_not_approved"* — ye **native PG SDK** ka restriction hai (app Play Store se install nahi hai).

**Fix (app code):**

1. Har API checkout request par header bhejo: `X-Client: mobile-app` (ya `X-App-Platform: android`)
2. Response me `payment.integration.preferred === 'hosted_checkout_url'` ho to **native SDK mat chalao**
3. `payment.checkout_url` ab **`https://freshnonveg.com/pay/cashfree/{order_number}?...signature`** hai (signed). Ye page website jaisa Cashfree JS SDK use karta hai — **Cashfree ka direct `payments.cashfree.com/pg/view/...` URL mat kholo (404).**
4. Is URL ko **Chrome Custom Tabs** / **in-app WebView** se kholo
4. Payment ke baad user ko app me laao → `POST /checkout/verify` with `order_number`
5. Dubara URL chahiye ho to: `GET /api/v1/checkout/orders/{id}/payment`

Website wala flow change nahi hota — sirf mobile par hosted URL use karo.

### Package

```yaml
# pubspec.yaml
dependencies:
  flutter_cashfree_pg_sdk: ^2.2.0
```

### Server se kya milta hai

| Field | Use |
|-------|-----|
| `payment.payment_session_id` | SDK session |
| `payment.order_id` | Cashfree order id (= hamara `order_number`) |
| `payment.environment` | `SANDBOX` / `PRODUCTION` — SDK environment isi se set karo |
| `payment.checkout_url` | Fallback — WebView / browser me kholo |

### Code

```dart
import 'package:flutter_cashfree_pg_sdk/api/cfpayment/cfwebcheckoutpayment.dart';
import 'package:flutter_cashfree_pg_sdk/api/cfpaymentgateway/cfpaymentgatewayservice.dart';
import 'package:flutter_cashfree_pg_sdk/api/cfsession/cfsession.dart';
import 'package:flutter_cashfree_pg_sdk/utils/cfenums.dart';

final _cf = CFPaymentGatewayService();

void openCashfree(Map payment, Map order) {
  final orderNumber = order['order_number'];

  _cf.setCallback(
    (String orderId) => verifyPayment(orderNumber),                 // success
    (CFErrorResponse e, String orderId) => verifyPayment(orderNumber), // fail / cancel
  );

  final session = CFSessionBuilder()
      .setEnvironment(payment['environment'] == 'PRODUCTION'
          ? CFEnvironment.PRODUCTION
          : CFEnvironment.SANDBOX)
      .setOrderId(payment['order_id'])
      .setPaymentSessionId(payment['payment_session_id'])
      .build();

  final cfPayment = CFWebCheckoutPaymentBuilder().setSession(session).build();
  _cf.doPayment(cfPayment);
}
```

### Test (Sandbox)

| Method | Value |
|--------|-------|
| UPI success | `testsuccess@gocash` |
| UPI fail | `testfailure@gocash` |
| Card / Netbanking | Cashfree sandbox page par jo test details dikhti hain wo use karo, simulator me "Success" / "Failure" chuno |

**Note:** Live (PRODUCTION) me Cashfree dashboard par app ka package name / website domain whitelist hona chahiye,
warna `403` aata hai.

---

## React Native (recommended — March 2026)

**Base URL:** `https://freshnonveg.com/api/v1`  
**Auth:** `Authorization: Bearer {sanctum_token}` · `Accept: application/json`

### Zaroori headers (har checkout API call)

```javascript
headers: {
  Accept: 'application/json',
  Authorization: `Bearer ${token}`,
  'X-Client': 'mobile-app',
  'X-App-Platform': Platform.OS, // android | ios
}
```

### Cashfree — native SDK mat use karo (APK / sideload)

`installer_package_not_approved` → **`payment.checkout_url`** WebView ya browser me kholo.

```bash
npm install react-native-inappbrowser-reborn
# ya: npm install react-native-webview react-native-linking
```

```javascript
import { Linking, Platform } from 'react-native';
import InAppBrowser from 'react-native-inappbrowser-reborn';

async function openCashfreeHosted(payment, orderNumber) {
  const url = payment.checkout_url || payment.payment_link;
  if (!url) throw new Error('checkout_url missing');

  if (await InAppBrowser.isAvailable()) {
    await InAppBrowser.open(url, {
      dismissButtonStyle: 'close',
      showTitle: true,
      enableUrlBarHiding: true,
    });
  } else {
    await Linking.openURL(url);
  }

  await verifyPayment(orderNumber);
}

async function placeOrderAndPay(checkoutBody) {
  const res = await api.post('/checkout', checkoutBody, { headers: mobileHeaders() });
  const { order, payment } = res.data.data;

  if (payment.gateway === 'cashfree') {
  if (
    payment.integration?.use_native_sdk === false ||
    payment.integration?.preferred === 'hosted_checkout_url'
  ) {
      await openCashfreeHosted(payment, order.order_number);
      return;
    }
  }

  if (payment.gateway === 'razorpay') {
    // react-native-razorpay — payment.checkout_options use karo
    openRazorpay(payment, order);
  }
}
```

### Verify + retry

```javascript
async function verifyPayment(orderNumber, extra = {}) {
  const res = await api.post('/checkout/verify', { order_number: orderNumber, ...extra });
  if (res.data?.success) navigation.navigate('OrderSuccess', { id: res.data.data.order.id });
  else navigation.navigate('Cart');
}

// payment dubara khulwana ho
const retry = await api.get(`/checkout/orders/${orderId}/payment`, { headers: mobileHeaders() });
```

Deep link (payment return page): `freshnonveg://payment/return?order_number=...&status=paid`  
Server `.env`: `APP_MOBILE_PAYMENT_DEEP_LINK` (optional).

Poora API flow: [API.md](./API.md)

---

## Common — verify function

```dart
Future<void> verifyPayment(String orderNumber, {Map<String, dynamic>? extra}) async {
  final res = await api.post('/checkout/verify', {
    'order_number': orderNumber,
    ...?extra,
  });

  if (res.statusCode == 200 && res.data['success'] == true) {
    goToSuccess(res.data['data']['order']['id']);   // GET /checkout/orders/{id}/success
  } else {
    showError(res.data['message']);                 // "Payment complete nahi hui…"
    goToCart();
  }
}
```

## Edge cases

| Case | Kya hota hai / kya karein |
|------|---------------------------|
| App payment ke beech band ho gaya | Next time `GET /cart` / `GET /checkout` → `pending_paid_order` / `already_paid` → success dikhao |
| SDK success, verify `422` | Rare (bank delay). 3–5 sec baad `GET /checkout/orders/{id}/status` 2–3 baar poll karo |
| User ne SDK band kiya | Verify call karo → order `payment_failed`, wallet/coupon wapas |
| Admin ne gateway badla | Naye orders naye gateway se. Pending purane orders apne purane gateway se hi verify hote hain — app ko kuch nahi karna |
| `payment_configured: false` | Koi gateway active nahi — "Online payment abhi available nahi" dikhao |

## Admin side (app dev ko info ke liye)

`Admin → Payment Gateways`:

- Cashfree: App ID + Secret + Environment (Sandbox / Production)
- Razorpay: Key ID + Key Secret + Webhook Secret + Environment
- **Test connection** button se keys check
- **Activate** — ek time par ek gateway

Keys DB me encrypted rehti hain. App ko koi key hardcode nahi karni.
