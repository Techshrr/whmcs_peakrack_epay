# Upgrade Notes

## 2.1.1

No database migration is required.

Upload the updated files over the existing module files.

This release hardens the 2.1.0 local redirect flow. Payment-method selection tokens now expire after two hours, stale paid-invoice pages are redirected back to the invoice instead of creating a new provider order, callbacks accept additional common success and transaction fields, and browser returns include the invoice ID for safer customer redirection.

## 2.1.0

No database migration is required.

Upload the new file:

```text
peakrack_epay/redirect.php -> modules/gateways/peakrack_epay/redirect.php
```

This release changes when provider orders are created. Previous versions rendered one signed EPay form per enabled payment method, which could create multiple unpaid provider-side records for one WHMCS invoice in some EPay installations. Version 2.1.0 renders local method-selection forms first; the EPay order number, signature, and provider POST are generated only after the customer clicks one method.

## 2.0.0

No database migration is required.

Existing installations remain on `V1 / MD5` unless you switch the new `Signature Mode` setting.

To enable V2/RSA:

- Confirm PHP OpenSSL is enabled on the WHMCS server.
- Set `Signature Mode` to `V2 / RSA`.
- Fill `Merchant Private Key` with the private key generated from the EPay RSA key pair.
- Fill `Platform Public Key` with the platform public key from the EPay API information page.
- Keep `Merchant Key` if the EPay account is in MD5+RSA compatibility mode and may send MD5 callbacks.

The module still submits hosted payments to `submit.php`; V2/RSA mode adds `timestamp`, signs with SHA256WithRSA, and verifies RSA callbacks with the platform public key.

## 1.0.2

No database migration is required.

- The Chinese/English admin switch buttons now appear in the `Submit URL` help row.
- `Submit URL` accepts copied provider roots with a trailing slash, such as `https://pay.example.com/`.
- Customer-facing payment buttons use built-in SVG icons and a compact responsive layout.

## 1.0.1

The previous `Payment Type` field has been replaced by individual payment method toggles:

- `Enable Alipay`
- `Enable WeChat Pay`
- `Enable QQ Wallet`
- `Enable Online Banking`
- `Enable Cashier`
- `Custom Payment Types`

Customers now choose the concrete method on the invoice payment page. The old saved `paymentType` value is only used as a fallback when the new toggle settings are unavailable.

The saved Admin Language dropdown was removed. Use the Chinese/English buttons in the gateway header for immediate switching.

## 1.0.0

Initial release. Install all files into the matching WHMCS gateway paths:

```text
peakrack_epay.php            -> modules/gateways/peakrack_epay.php
peakrack_epay/               -> modules/gateways/peakrack_epay/
callback/peakrack_epay.php   -> modules/gateways/callback/peakrack_epay.php
```

After installation, enable `PeakRack EPay (易支付)` and configure:

- `Submit URL`
- `Merchant ID / PID`
- `Merchant Key`
- enabled payment method toggles
- `Order Prefix`
- `Convert To For Processing = CNY` when the WHMCS invoice currency is not CNY
