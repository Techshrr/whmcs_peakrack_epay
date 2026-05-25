# Upgrade Guide

This guide explains how to upgrade this gateway from an older version.

## Before upgrading

1. Back up the WHMCS files.
2. Back up the WHMCS database.
3. Make copies of the current gateway file, support directory, and callback file.
4. Review [CHANGELOG.md](CHANGELOG.md).
5. Check whether the upgrade changes gateway settings.

## Upgrade steps

1. Download the latest release from the official repository:

   https://github.com/Techshrr/whmcs_peakrack_epay

2. Replace the gateway files in the WHMCS directories:

   `modules/gateways/peakrack_epay.php`

   `modules/gateways/peakrack_epay/`

   `modules/gateways/callback/peakrack_epay.php`

3. Keep existing merchant credentials and private keys in WHMCS gateway settings.
4. Log in to the WHMCS admin area.
5. Open the gateway settings and verify all options.
6. Clear the WHMCS template cache if invoice payment buttons do not update.

## Database migrations

This version does not require manual database migration.

## Version-specific notes

### Upgrade from 2.0.x to 2.1.x

- No database changes are required.
- The local redirect endpoint `modules/gateways/peakrack_epay/redirect.php` must be present.
- Existing V1/MD5 and V2/RSA credentials are preserved in WHMCS gateway settings.

### Upgrade from 1.x to 2.x

- Existing installations remain on `V1 / MD5` unless the administrator changes `Signature Mode`.
- To enable `V2 / RSA`, confirm PHP OpenSSL support and configure the merchant private key and platform public key.

## Rollback

To roll back:

1. Restore the previous gateway file, support directory, and callback file.
2. Restore the database backup if the upgrade changed WHMCS records.
3. Clear the WHMCS template cache.
4. Check the WHMCS gateway log and activity log for errors.

## Notes

Do not overwrite production credentials, local configuration files, custom templates, callback secrets, or payment credentials unless the upgrade notes explicitly require it.