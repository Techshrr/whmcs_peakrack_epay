# Changelog

All notable changes to this project are documented in this file.

This project follows Semantic Versioning where practical.

## [2.1.1] - 2026-05-26

### Fixed

- Added a two-hour expiry to local payment-method selection tokens.
- Prevented stale or already-paid invoice pages from creating a new provider order.
- Accepted additional common success statuses, transaction ID fields, and `total_amount` callback fields.
- Added invoice-aware browser return handling when providers omit callback parameters during browser redirect.

## [2.1.0] - 2026-05-26

### Changed

- Changed payment method buttons to post to a local redirect endpoint before submitting to EPay.
- Generated EPay order numbers, request signatures, and provider submissions only after the customer selects one payment method.

## [2.0.0] - 2026-05-26

### Added

- Added `Signature Mode` with V1/MD5 and V2/RSA options.
- Added V2/RSA request signing with `timestamp`, SHA256WithRSA, merchant private key support, and platform-public-key callback verification.

## [1.0.2] - 2026-05-26

### Fixed

- Restored immediate Chinese and English admin switch buttons in the `Submit URL` help row.
- Accepted provider URLs with a trailing slash and normalized them before appending `submit.php`.

## [1.0.1] - 2026-05-26

### Changed

- Replaced the single payment type field with separate payment method toggles and customer-facing payment method selection.
- Removed the saved admin language dropdown in favor of immediate language switch buttons.

## [1.0.0] - 2026-05-26

### Added

- Initial release.
- Added hosted form payment, MD5 signing, callback verification, CNY amount checking, and duplicate transaction protection.