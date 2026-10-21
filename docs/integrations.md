# Integration Boundaries

## Payments

`PaymentGateway` is the application contract. The local binding is `FakePaymentGateway`; it returns simulated intents and never collects money. A Razorpay-compatible webhook endpoint validates the raw request body with HMAC-SHA256 and deduplicates the provider event ID before processing. Razorpay documents raw-body signature verification and event replay handling in its [webhook validation guidance](https://razorpay.com/docs/webhooks/validate-test/).

Configure `RAZORPAY_KEY`, `RAZORPAY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` only when a real adapter is installed and tested in provider test mode. Browser redirects are not treated as payment confirmation.

## SMS and WhatsApp

`MessagingProvider` is the application contract. `FakeMessagingProvider` records simulated email, SMS, and WhatsApp delivery states with idempotency keys. Provider credentials are environment variables and are never written into member or audit records.

## GST and e-invoice

`GstSetting` is effective-dated and invoices retain an e-invoice request/response snapshot. `FakeEInvoiceGateway` returns a simulated IRN and explicitly does not submit to an Invoice Registration Portal. The official e-invoice schema states that the IRP generates the IRN, so statutory submission must be implemented and verified against the current official portal/schema before enabling production use: [official e-invoice schema](https://einvoice6.gst.gov.in/content/wp-content/uploads/2022/07/notification-60-central-tax-english-2020.pdf).

## Concurrency

`MySqlConcurrencyTest` is skipped under SQLite and must be run with `DB_CONNECTION=mysql` against MySQL 8. Payment allocation, refund reversal, attendance session deduction, and offer redemption use database transactions and row locks. CI should run the MySQL test job against an isolated database.
