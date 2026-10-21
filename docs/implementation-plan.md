# Implementation Plan

## Architecture

This application is a Laravel 13 modular monolith using Blade, Tailwind CSS v4, Alpine.js, session authentication, Form Requests, policies, database queue/cache, and tenant-scoped Eloquent models. Tenant records live in one shared database with `organization_id` on organization-owned records and `branch_id` on branch-specific records.

## Assumptions

- Phase 1 uses SQLite for automated local tests and keeps MySQL validation as a Phase 6 task.
- Development seed users use `@example.test` email addresses and the local-only password `DevOnly!2026`.
- GST, CCTV live video, SMS/WhatsApp, payment gateway, PDF generation, exports, and hardware adapters are not configured until their phases.
- Application timestamps stay UTC. Branch business rules use explicit branch timezone fields, defaulting to `Asia/Kolkata`.

## Checklist

- Phase 1 Foundation: Laravel setup, organizations, branches, session login, tenant context, scoped RBAC, policies, settings, operating hours, shared UI, seed data, audit log, isolation tests.
- Phase 2 Core gym operations: leads, members, guardians, plans, memberships, freezes, renewals, batches, attendance, member portal. Complete.
- Phase 3 Finance: invoices, installments, payments, discounts, credits, refunds, GST configuration, financial reporting. Complete for invoice/payment/allocation, credit note, installment, offer, GST lookup, and collections report scope; statutory and gateway integrations remain pending.
- Phase 4 Fitness and engagement: goals, workouts, progress, meal plans, events, notifications. Complete for goals, versioned workouts, meal plans, events, notification delivery records, and authorized staff workflow.
- Phase 5 Operations: vendors, purchases, assets, transfers, maintenance, tasks, expenses, utilities, CCTV inventory and mock gateway state. Complete for the delivered operational workflow scope; live gateway remains unconfigured.
- Phase 6 Completion: exports, integration test modes, security review, MySQL concurrency tests, UI checks, performance, backups, deployment docs. Complete for application-level exports, security review, documentation, and automated verification; infrastructure-specific checks remain deployment prerequisites.
