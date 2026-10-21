# Business Rules

- Tenant context comes only from authenticated affiliations and branch assignments; request-submitted IDs are revalidated server-side.
- Missing tenant context fails closed for non-platform users.
- Cross-tenant branch access returns `404` where revealing record existence would leak information.
- Branch-scoped permission checks require both a matching role permission and an active branch assignment unless the user has an organization-wide role.
- Organization and branch defaults use INR and `Asia/Kolkata`; timestamps remain UTC.
- Branch operating hours support multiple weekday intervals, overnight closing, effective dates, closures, and eligibility labels. Later booking modules must flag affected bookings when hours change.
- Member codes are unique within an organization; phone and email remain shareable for family members.
- Purchased memberships retain plan snapshots, explicit allowed branch IDs, integer-paise totals, and inclusive branch-local start/end dates.
- Fixed-day memberships end after the configured number of inclusive covered dates; calendar-month memberships use month-aware date arithmetic. Early renewals begin on the day after current coverage.
- Freeze days are inclusive, consume the plan's allowance once, and extend expiry without changing the original membership history. Session packs deduct once per idempotent attendance check-in.
- Attendance check-in validates organization, branch entitlement, active dates, remaining sessions, and overlapping open visits. Repeated idempotency keys return the original visit.
- Guardian access is explicit, revocable, organization-scoped, and creates an active organization affiliation for portal access. Trainer visibility is limited to assigned members unless a broader permission applies.
- Issued financial documents, health details, CCTV stream access, exports, and hardware integrations are not implemented in Phase 1 and must remain denied or unavailable until added with tests.
