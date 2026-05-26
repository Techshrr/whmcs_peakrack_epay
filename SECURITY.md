# Security Policy

## Reporting a vulnerability

Please do not open public GitHub issues for security vulnerabilities.

Report EPay request-signing, callback-verification, redirect, or amount-validation issues to:

security@peakrack.com

Please include:

- Affected gateway version, WHMCS version, PHP version, and signature mode
- Whether the issue affects hosted-payment submission, return handling, notify handling, or invoice crediting
- Description of the issue and reproduction steps
- Potential impact on invoice payment state or callback validation
- Suggested mitigation, if available

## Supported versions

| Version | Supported |
|---|---|
| 2.x | Yes |
| < 1.0 | No |

## Sensitive data

Do not include production merchant IDs, merchant keys, private keys, platform public keys, signed callback payloads, provider order numbers, transaction IDs, real invoice numbers, customer data, WHMCS license data, or production callback URLs in public reports.

## Public issues

Installation problems, provider compatibility reports, and documentation fixes may be submitted through GitHub Issues.

Security vulnerabilities must be reported privately by email.
