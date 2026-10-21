# Security Review

- Tenant context is established from authenticated affiliations and branch assignments; missing context fails closed.
- Controllers and Form Requests revalidate organization and branch relationships. Cross-tenant detail access returns `404` where appropriate.
- Session authentication, CSRF protection, login throttling, escaped Blade output, hashed passwords, and authorization checks are enabled.
- Finance writes use transactions, row locks, integer paise, unique invoice/payment identifiers, and idempotency keys.
- Attendance check-in uses idempotency and prevents overlapping open visits.
- Member CSV export requires `exports.create` and prefixes spreadsheet formula characters with an apostrophe.
- CCTV inventory stores metadata only. No camera password, public stream URL, arbitrary SSRF probe, or face recognition feature is enabled.

Remaining production checks: run concurrency tests against MySQL 8, enable HTTPS and secure cookies, configure backups, add staff 2FA provider enforcement, and perform an infrastructure review for any approved media gateway.
