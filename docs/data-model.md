# Data Model

## Phase 1 Tables

- `users`: session-authenticated people, including optional platform super admin flag and future 2FA timestamp.
- `organizations`: stable tenant business identity with code, currency, default timezone, and settings.
- `branches`: gym branches scoped by `organization_id`, with branch code unique inside the organization.
- `organization_affiliations`: verified user-to-organization membership.
- `roles`, `permissions`, `permission_role`: seeded RBAC matrix.
- `role_assignments`: scoped role grants at organization or branch level.
- `branch_assignments`: active branch assignment for staff.
- `tenant_settings`: effective-dated organization or branch settings.
- `branch_operating_intervals`: weekday opening intervals, including overnight flag and eligibility label.
- `branch_closures`: special closures or holiday overrides.
- `audit_logs`: actor/time/organization/branch/auditable event trail.

## Phase 2 Tables

- `leads`: inquiry pipeline, source, follow-up, trial, conversion, and assignment fields.
- `members`: organization member profile, home branch, trainer, consent, goals, and status.
- `guardian_relationships`: explicit guardian-to-child access and payment permissions.
- `plans`: reusable catalog terms and branch/age/usage entitlements.
- `memberships`, `membership_freezes`: purchased historical snapshots, dates, pricing, sessions, and freeze extensions.
- `batches`, `batch_enrollments`: recurring class groups and member enrollment.
- `attendance_visits`: branch check-ins, operator/source metadata, idempotency, and session consumption.
- `member_qr_tokens`: revocable hashed QR identifiers without personal data.

Future phases add billing, vendors, assets, maintenance, CCTV inventory, notifications, exports, and integration logs without merging tenant records across organizations.

## Phase 3-5 Foundation Tables

- `invoices`, `invoice_lines`, `payments`, `payment_allocations`, `credit_notes`: issued documents, actual receipts, allocations, adjustments, and integer-paise balances.
- `installments`, `offers`, `gst_settings`: schedules, offer constraints, and effective-dated tax configuration.
- `fitness_goals`, `workout_assignments`, `meal_plans`, `gym_events`, `notification_deliveries`: versioned member programming, events, and delivery state.
- `vendors`, `assets`, `maintenance_work_orders`, `operational_tasks`, `expenses`, `cctv_cameras`: operational inventory and work records. Camera credentials/stream secrets are intentionally not stored in this inventory table.
