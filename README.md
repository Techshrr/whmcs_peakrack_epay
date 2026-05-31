# PeakRack WHMCS EPay Gateway

> Official repository: https://github.com/Techshrr/whmcs_peakrack_epay
> License: Apache License 2.0

PeakRack WHMCS EPay Gateway is a WHMCS payment gateway for EPay-compatible hosted payment providers.

## Overview

The gateway submits customers to an EPay `submit.php` hosted payment endpoint and handles asynchronous notify callbacks and browser returns through the WHMCS gateway callback path.

It supports V1/MD5 signing and a V2/RSA compatible hosted-payment mode. The gateway can display multiple payment method choices, but the provider order is created only after the customer selects one method.

## Features

- Hosted EPay `submit.php` payment submission.
- V1/MD5 request signing and callback verification.
- V2/RSA compatible signing with `timestamp` and SHA256WithRSA.
- Alipay, WeChat Pay, QQ Wallet, online banking, cashier, and custom provider type options.
- Local redirect endpoint for selected payment method submission.
- WHMCS invoice callback handling with duplicate transaction checks and confirmed gateway amount application.
- Optional CNY amount verification before applying invoice payment.
- English, Simplified Chinese, and Hong Kong Traditional Chinese gateway configuration labels and client messages where applicable.

## Requirements

- WHMCS 9.0.x
- PHP 8.2 or later
- EPay-compatible merchant account
- Merchant ID / PID
- Merchant key for V1/MD5 mode
- PHP OpenSSL extension, merchant private key, and platform public key for V2/RSA mode
- Public HTTPS access to the WHMCS callback URL

## Installation

1. Download the latest release from the official repository.
2. Upload the gateway files to the matching WHMCS paths:

   `peakrack_epay.php` -> `modules/gateways/peakrack_epay.php`

   `peakrack_epay/` -> `modules/gateways/peakrack_epay/`

   `callback/peakrack_epay.php` -> `modules/gateways/callback/peakrack_epay.php`

3. Log in to the WHMCS admin area.
4. Enable **PeakRack EPay (易支付)** from **System Settings > Payment Gateways**.
5. Review the configuration options before using it in production.

## Configuration

| Option | Description | Default |
|---|---|---|
| Submit URL | EPay hosted payment endpoint; the module appends `submit.php` when needed | Empty |
| Signature Mode | Selects V1/MD5 or V2/RSA signing | V1 / MD5 |
| Merchant ID / PID | Merchant identifier from the provider | Empty |
| Merchant Key | MD5 signing key for V1 and fallback callback verification | Empty |
| Merchant Private Key | Private key used for V2/RSA request signing | Empty |
| Platform Public Key | Provider public key used for V2/RSA callback verification | Empty |
| Enable Alipay | Shows the Alipay payment option | On |
| Enable WeChat Pay | Shows the WeChat Pay payment option | On |
| Enable QQ Wallet | Shows the QQ Wallet payment option | Off |
| Enable Online Banking | Shows the online banking payment option | Off |
| Enable Cashier | Shows the provider cashier option | Off |
| Custom Payment Types | Comma-separated provider type values | Empty |
| Order Prefix | Prefix for provider merchant order numbers | PRK_ |
| Site Name | Site name sent to the provider | Empty |
| Verify Amount | Verifies callback amount against the expected CNY amount | On |

For multi-currency WHMCS installations, set the WHMCS gateway common option **Convert To For Processing** to `CNY` when the EPay provider settles in CNY.

## Usage

The administrator configures the merchant credentials, signature mode, enabled payment methods, order prefix, and amount verification setting.

When a customer views an invoice, the gateway shows the enabled payment options. After the customer selects a method, the local redirect endpoint signs the payment request and posts it to the provider. The callback file verifies the merchant ID, signature, payment status, transaction ID, and amount before applying payment to the WHMCS invoice.

## Callback URL

The asynchronous callback endpoint is:

`https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php`

The browser return URL includes `return=1` and the invoice ID so the customer can be redirected back to the invoice page.

## Upgrade

See [UPGRADE.md](UPGRADE.md).

## Chinese Documentation

See [README.zh-CN.md](README.zh-CN.md).

## Security

Do not commit production credentials, API keys, database passwords, payment secrets, WHMCS license data, customer data, identity documents, or private signing keys.

To report a security issue, see [SECURITY.md](SECURITY.md).

## License

This project is licensed under the Apache License 2.0. See [LICENSE](LICENSE) for details.

Additional project notices are available in [NOTICE](NOTICE).
