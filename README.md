# WHMCS PeakRack EPay Gateway

WHMCS payment gateway for EPay-compatible hosted payment providers using `submit.php`, V1/MD5 signatures, and V2/RSA compatible signatures.

中文说明见 [README.zh-CN.md](README.zh-CN.md).

Public example EPay provider: [互六鼎付](https://pay.idcli.com/) (`Submit URL`: `https://pay.idcli.com/`).

## Features

- Hosted payment form submission to an EPay `submit.php` endpoint
- Provider order creation only after the customer clicks one payment method
- V1/MD5 request signing and callback signature verification
- V2/RSA compatible-mode signing with `timestamp` and SHA256WithRSA
- Allows multiple enabled payment methods such as `alipay`, `wxpay`, `qqpay`, `bank`, provider cashier routing, and custom provider types
- WHMCS invoice payment callback integration
- CNY gateway amount verification for converted invoices
- Supports WHMCS `Convert To For Processing = CNY`
- Chinese/English gateway configuration labels and customer-facing messages

## Requirements

- WHMCS 9.x self-hosted installation
- EPay-compatible provider account
- Merchant ID / PID
- Merchant key for V1/MD5 signing
- PHP OpenSSL extension when V2/RSA mode is enabled
- Merchant RSA private key and platform public key when V2/RSA mode is enabled
- Public HTTPS WHMCS callback URL

V2 support in this module targets the hosted-payment compatible flow: it still submits the customer to `submit.php`, but signs the payload with RSA and adds `timestamp` for providers that enable MD5+RSA compatibility mode.

## Installation

Upload these files and directories to the matching WHMCS gateway paths:

```text
peakrack_epay.php            -> modules/gateways/peakrack_epay.php
peakrack_epay/               -> modules/gateways/peakrack_epay/
callback/peakrack_epay.php   -> modules/gateways/callback/peakrack_epay.php
```

Expected files after upload:

```text
modules/gateways/peakrack_epay.php
modules/gateways/peakrack_epay/lib.php
modules/gateways/peakrack_epay/redirect.php
modules/gateways/peakrack_epay/alipay-logo-icon.png
modules/gateways/peakrack_epay/whmcs.json
modules/gateways/callback/peakrack_epay.php
```

Then enable `PeakRack EPay (易支付)` in WHMCS payment gateways.

## Configuration

Fill in the gateway settings:

- `Submit URL`, for example `https://pay.idcli.com/`; a copied trailing slash may be kept and the module appends `submit.php`
- `Signature Mode`: use `V1 / MD5` by default, or `V2 / RSA` for RSA-compatible providers
- `Merchant ID / PID`
- `Merchant Key` for V1/MD5, and optionally for MD5 callback fallback in compatible mode
- `Merchant Private Key` for V2/RSA; paste the private key generated with your RSA key pair, not the merchant public key
- `Platform Public Key` for V2/RSA callback verification
- enabled payment methods, for example Alipay, WeChat Pay, QQ Wallet, online banking, or cashier
- `Custom Payment Types`, optional; separate multiple type codes with commas, for example `usdt,paypal`
- `Order Prefix`
- `Site Name`, optional

For USD stores or multi-currency WHMCS installs, set the gateway common setting:

```text
Convert To For Processing = CNY
```

WHMCS will convert the invoice amount to CNY before sending the customer to EPay. The callback verifies the returned CNY amount before applying the invoice payment.

When multiple payment methods are enabled, the invoice page only posts the customer's selected method back to the local module first. The module creates and submits the EPay order only after that click, so one WHMCS invoice does not create multiple unpaid provider orders just because multiple methods are visible.

## Callback URL

The module passes the asynchronous callback URL dynamically:

```text
https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php
```

The browser return URL is:

```text
https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php?return=1&invoiceid=INVOICE_ID
```

The WHMCS site must be publicly reachable by the EPay provider over HTTPS.

## Signature Notes

The module signs all non-empty request parameters except `sign` and `sign_type`, sorted by ASCII key order.

In `V1 / MD5` mode, the module appends the merchant key and calculates a lowercase MD5 hash.

In `V2 / RSA` mode, the module adds `timestamp`, signs the sorted parameter string with the merchant private key using SHA256WithRSA, base64-encodes the signature, and submits `sign_type=RSA`.

Callbacks are verified using the same selected signature type. RSA callbacks are verified with the platform public key; MD5 callbacks are verified with the merchant key. Only successful callbacks with `trade_status=TRADE_SUCCESS`, `TRADE_FINISHED`, or a compatible success status are applied.

## Release Notes

### 2.1.1

- Added a two-hour expiry to local payment-method selection tokens.
- Prevented stale or already-paid invoice pages from creating a new provider order.
- Hardened callbacks for additional common success statuses, transaction ID fields, and `total_amount`.
- Added invoice-aware browser return handling so payment returns can still reach the invoice page if the provider omits callback parameters in the browser redirect.

### 2.1.0

- Changed payment method buttons to post to a local module redirect endpoint first.
- EPay `out_trade_no`, request signing, and provider submission are now generated only after the customer clicks one concrete payment method.
- This avoids creating multiple unpaid EPay provider orders when Alipay, WeChat Pay, and other methods are shown together.

### 2.0.0

- Added `Signature Mode` with V1/MD5 and V2/RSA options.
- Added V2/RSA signing with `timestamp`, SHA256WithRSA, merchant private key support, and platform-public-key callback verification.
- Kept V1/MD5 as the default mode for existing installations.

### 1.0.2

- Restored the immediate Chinese/English admin switch buttons by attaching them to the `Submit URL` help row.
- Improved `Submit URL` handling so copied provider URLs such as `https://pay.idcli.com/` do not need the trailing slash removed.
- Improved customer payment and invoice-sidebar button layout with built-in SVG icons instead of first-character badges.

### 1.0.1

- Removed the saved Admin Language dropdown and kept the immediate Chinese/English switch buttons.
- Replaced the single payment type field with multiple admin toggles and a customer-facing payment method chooser.

### 1.0.0

- Initial PeakRack EPay gateway.
- Added hosted form payment, MD5 signing, callback verification, CNY amount checking, and duplicate transaction protection.

Detailed upgrade notes: [UPGRADE.md](UPGRADE.md).

## Disclaimer

This is an independent WHMCS payment gateway module. It is not affiliated with, endorsed by, or sponsored by WHMCS or any EPay provider. WHMCS and provider trademarks belong to their respective owners.

## License

MIT License. See [LICENSE](LICENSE).
