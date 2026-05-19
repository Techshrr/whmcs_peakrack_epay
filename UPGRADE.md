# Upgrade Notes

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
