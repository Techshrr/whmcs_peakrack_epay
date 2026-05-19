# WHMCS PeakRack EPay Gateway

WHMCS payment gateway for EPay-compatible V1 hosted payment providers using `submit.php` and MD5 signatures.

中文说明见 [README.zh-CN.md](README.zh-CN.md).

## Features

- Hosted payment form submission to an EPay `submit.php` endpoint
- MD5 request signing and callback signature verification
- Allows multiple enabled payment methods such as `alipay`, `wxpay`, `qqpay`, `bank`, provider cashier routing, and custom provider types
- WHMCS invoice payment callback integration
- CNY gateway amount verification for converted invoices
- Supports WHMCS `Convert To For Processing = CNY`
- Chinese/English gateway configuration labels and customer-facing messages

## Requirements

- WHMCS 9.x self-hosted installation
- EPay-compatible V1 provider account
- Merchant ID / PID
- Merchant key for MD5 signing
- Public HTTPS WHMCS callback URL

This module targets the common V1 MD5 EPay contract. It does not implement newer RSA-based V2 APIs.

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
modules/gateways/peakrack_epay/alipay-logo-icon.png
modules/gateways/peakrack_epay/whmcs.json
modules/gateways/callback/peakrack_epay.php
```

Then enable `PeakRack EPay (易支付)` in WHMCS payment gateways.

## Configuration

Fill in the gateway settings:

- `Submit URL`, for example `https://pay.example.com/`; a copied trailing slash may be kept and the module appends `submit.php`
- `Merchant ID / PID`
- `Merchant Key`
- enabled payment methods, for example Alipay, WeChat Pay, QQ Wallet, online banking, or cashier
- `Custom Payment Types`, optional; separate multiple type codes with commas, for example `usdt,paypal`
- `Order Prefix`
- `Site Name`, optional

For USD stores or multi-currency WHMCS installs, set the gateway common setting:

```text
Convert To For Processing = CNY
```

WHMCS will convert the invoice amount to CNY before sending the customer to EPay. The callback verifies the returned CNY amount before applying the invoice payment.

## Callback URL

The module passes the asynchronous callback URL dynamically:

```text
https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php
```

The browser return URL is:

```text
https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php?return=1
```

The WHMCS site must be publicly reachable by the EPay provider over HTTPS.

## Signature Notes

The module signs all non-empty request parameters except `sign` and `sign_type`, sorted by ASCII key order, then appends the merchant key and calculates a lowercase MD5 hash.

Callbacks are verified using the same rule. Only successful callbacks with `trade_status=TRADE_SUCCESS` or a compatible success status are applied.

## Release Notes

### 1.0.2

- Restored the immediate Chinese/English admin switch buttons by attaching them to the `Submit URL` help row.
- Improved `Submit URL` handling so copied provider URLs such as `https://pay.example.com/` do not need the trailing slash removed.
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
