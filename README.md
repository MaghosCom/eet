# Maghos_Eet

Magento 2 module that sends sales receipts to the Czech Electronic Registration of Sales (EET 2.0, interface v4, valid from 1. 1. 2027).

- One sale receipt per invoice with the invoice total and invoice number.
- One refund receipt per credit memo with the (negative) credit memo total.
- Amounts are sent in CZK; other order or base currencies are converted with the store currency rates.
- The acknowledgement code (POK) returned by EET is stored and shown with the invoice and credit memo in the admin, in the customer account, on print pages (Luma and Hyvä) and in PDFs.
- Failed receipts are resent automatically by cron and can be resent manually from the admin grids.

## Requirements

- Magento 2.4.x / Mage-OS (tested up to Magento 2.4.9 / Mage-OS 3.x)
- PHP 7.4 – 8.4 with `curl`, `dom` and `openssl` extensions
- Cash register certificate (`.p12`) issued in the MOJE daně (DIS+) portal

## Installation

Copy the module to `app/code/Maghos/Eet` (or install the `maghos/eet` Composer package) and run:

```bash
bin/magento module:enable Maghos_Eet
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Configuration

**Stores → Configuration → Sales → EET → Eet Integration** (default, website and store view scope):

| Field | Description |
|-------|-------------|
| Enable Eet Integration | Turns receipt sending on. Default: No. |
| Test Environment | Use the EET playground (`pg.trzbyeet.gov.cz`) instead of production. Default: Yes. |
| EIC | Taxpayer's VAT ID (EIČ), e.g. `CZ12345678`. |
| Registrating Unit ID | Unit identifier assigned in the MOJE daně (DIS+) portal. |
| Checkout ID | Identifier of the checkout device the revenue is registered on. |
| Certificate File | `.p12` certificate. If OpenSSL 3 cannot read it (no legacy provider), convert it to PEM: `openssl pkcs12 -legacy -in cert.p12 -out cert.pem` and upload the PEM with the same password. |
| Certificate password | Password of the certificate. |
| Payment methods | Receipts are sent only for orders paid by these methods. |
| Test EET Config | Sends a verification receipt with the saved settings. In production it uses verification mode, so nothing is registered. Save the settings first. |

A playground certificate works only with the test environment and a production certificate only with production.

## How it works

1. **Invoice saved:** if EET is enabled for the order's store and the payment method is selected, a sale receipt is sent. It is sent only once per invoice.
2. **Credit memo saved:** if the order already has an EET record, a refund receipt is sent once per credit memo.
3. Each receipt is stored in the `maghos_eet_record` table with one of these statuses:
   - **Sent**: accepted, POK stored.
   - **Failed, will be resent**: connection problem or temporary EET error.
   - **Rejected**: EET refused the receipt permanently; see the log.
   - **Not sent yet**
4. **Cron** `imaghoseet_resend_receipts` (every hour at :30) resends all failed records, even if EET has been disabled since.
5. **Manual resend:** the *Sales → Invoices* and *Sales → Credit Memos* grids have an **EET** status column and a **Resend EET** mass action.

Errors and warnings are written to the Magento system/exception log with the `EET:` prefix.

## ACL

- `Maghos_Eet::manage_eet`: access to the EET configuration section.
- `Maghos_Eet::resend`: Resend EET mass action (under Sales → Operations).

## Translations

`en_US` and `cs_CZ` are included.

## Support

Maghos, support@maghos.com. Proprietary module, see http://www.maghos.com/business-license.
